<?php

namespace App\Http\Controllers;

use App\Models\Film;
use App\Models\Planet;
use App\Models\Character;
use App\Traits\StringTrait;
use App\Services\SwapiService;
use App\Traits\EntiteableTrait;
use App\Traits\ConsultableTrait;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class FilmsController extends Controller
{
    use StringTrait, ConsultableTrait, EntiteableTrait;

    protected $swapiService;
    protected $film;
    protected $filmSwapiData;

    public function __construct(SwapiService $swapiService)
    {
        $this->swapiService = $swapiService;
    }

    public function show(int $id)
    {
        /* Save query history */
        $this->saveConsult();

        $redisKey = "film-{$id}";

        $filmRedis = Redis::get($redisKey);
        // If film exist in Redis
        if( $filmRedis ){
            $filmArray = json_decode($filmRedis, true);
            return response()->json($filmArray, Response::HTTP_OK);
        }

        /* If film not in Redis, look for in DB and save in Redis */
        $this->film = Film::with(['characters', 'planets'])->find($id);
        if($this->film){
            $this->filmSwapiData = $this->uncompressJsonStringToArray(stream_get_contents($this->film['original_json']));
            $this->setFilmRelationships();
            Redis::set($redisKey, $this->film->toJson());
            return response()->json($this->film->toArray(), Response::HTTP_OK);
        }

        /* Search film in swapi */
        $this->filmSwapiData = $this->swapiService->getData("/api/films/{$id}");
        if(!$this->filmSwapiData){
            return response()->json([
                "message" => "Film ID {$id} does not exist"
            ], Response::HTTP_NOT_FOUND);
        }

        /* Save new film in postgresql */
        $this->filmSwapiData['id'] = $id;
        $this->filmSwapiData['original_json'] = $this->compressObjectToString($this->filmSwapiData);
        $this->film = new Film();
        $this->film->fill($this->filmSwapiData);
        $this->film->save();

        $this->setFilmRelationships();

        /* Save in redis with relationships */
        Redis::set($redisKey, $this->film->toJson());

        return response()->json($this->film->toArray(), Response::HTTP_OK);
    }

    protected function setFilmRelationships(): void
    {
        /* Create relations with characters */
        if(!empty($this->filmSwapiData['characters'])){
            $charactersIds = $this->fetchAndSaveEntities($this->swapiService, $this->filmSwapiData['characters'], Character::class);
            $this->film->characters()->sync($charactersIds);
        }

        /* Create relations with planets */
        if(!empty($this->filmSwapiData['planets'])){
            $planetsIds = $this->fetchAndSaveEntities($this->swapiService, $this->filmSwapiData['planets'], Planet::class);
            $this->film->planets()->sync($planetsIds);
        }

        /* Load relationships */
        $this->film->load(['characters', 'planets']);
    }
}
