<?php

namespace App\Http\Controllers;

use App\Models\Planet;
use App\Models\Character;
use App\Traits\StringTrait;
use App\Services\SwapiService;
use App\Traits\EntiteableTrait;
use App\Traits\ConsultableTrait;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class PlanetsController extends Controller
{
    use StringTrait, ConsultableTrait, EntiteableTrait;

    protected $swapiService;
    protected $planet;
    protected $planetSwapiData;

    public function __construct(SwapiService $swapiService)
    {
        $this->swapiService = $swapiService;
    }

    public function show(int $id)
    {
        /* Save query history */
        $this->saveConsult();

        $redisKey = "planet-{$id}";

        $planetRedis = Redis::get($redisKey);
        // If planet exist in Redis
        if( $planetRedis ){
            $planetArray = json_decode($planetRedis, true);
            return response()->json($planetArray, Response::HTTP_OK);
        }

        /* If planet not in Redis, look for in DB and save in Redis */
        $this->planet = Planet::with(['characters'])->find($id);
        if($this->planet){
            $this->planetSwapiData = $this->uncompressJsonStringToArray(stream_get_contents($this->planet['original_json']));
            $this->setPlanetRelationships();
            Redis::set($redisKey, $this->planet->toJson());
            return response()->json($this->planet->toArray(), Response::HTTP_OK);
        }

        /* Search planet in swapi */
        $this->planetSwapiData = $this->swapiService->getData("/api/planets/{$id}");
        if(!$this->planetSwapiData){
            return response()->json([
                "message" => "Planet ID {$id} does not exist"
            ], Response::HTTP_NOT_FOUND);
        }

        /* Save new planet in postgresql */
        $this->planetSwapiData['id'] = $id;
        $this->planetSwapiData['original_json'] = $this->compressObjectToString($this->planetSwapiData);
        $this->planet = new Planet();
        $this->planet->fill($this->planetSwapiData);
        $this->planet->save();

        $this->setPlanetRelationships();

        /* Save in redis with relationships */
        Redis::set($redisKey, $this->planet->toJson());

        return response()->json($this->planet->toArray(), Response::HTTP_OK);
    }

    protected function setPlanetRelationships(): void
    {
        /* Create relations with characters */
        if(!empty($this->planetSwapiData['residents'])){
            $charactersIds = $this->fetchAndSaveEntities($this->swapiService, $this->planetSwapiData['residents'], Character::class);
            Character::whereIn('id', $charactersIds)->update(['planet_id'=>$this->planet->id]);
        }

        /* Load relationships */
        $this->planet->load(['characters']);
    }
}
