<?php

namespace App\Http\Controllers;

use App\Models\Film;
use App\Models\Planet;
use App\Models\Specie;
use App\Models\Vehicle;
use App\Models\Character;
use App\Traits\StringTrait;
use App\Services\SwapiService;
use App\Traits\EntiteableTrait;
use App\Traits\ConsultableTrait;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class CharactersController extends Controller
{
    use StringTrait, ConsultableTrait, EntiteableTrait;

    protected $swapiService;
    protected $character;
    protected $characterSwapiData;


    public function __construct(SwapiService $swapiService)
    {
        $this->swapiService = $swapiService;
    }

    public function show(int $id)
    {   
        /* Save query history */
        $this->saveConsult();

        $redisKey = "people-{$id}";

        $characterRedis = Redis::get($redisKey);
        // If character exist in Redis
        if( $characterRedis ){
            $characterArray = json_decode($characterRedis, true);
            return response()->json($characterArray, Response::HTTP_OK);
        }

        /* If character not in Redis, look for in DB and save in Redis */
        $this->character = Character::with(['planet', 'films', 'vehicles', 'species'])->find($id);
        if($this->character){
            $this->characterSwapiData = $this->uncompressJsonStringToArray(stream_get_contents($this->character['original_json']));
            $this->setCharacterRelationships();
            Redis::set($redisKey, $this->character->toJson());
            return response()->json($this->character->toArray(), Response::HTTP_OK);
        }

        /* Search character in swapi */
        $this->characterSwapiData = $this->swapiService->getData("/api/people/{$id}");
        if(!$this->characterSwapiData){
            return response()->json([
                "message" => "Character ID {$id} does not exist"
            ], Response::HTTP_NOT_FOUND);
        }

        /* Save new character in postgresql */
        $this->characterSwapiData['id'] = $id;
        $this->characterSwapiData['original_json'] = $this->compressObjectToString($this->characterSwapiData);
        $this->character = new Character();
        $this->character->fill($this->characterSwapiData);
        $this->character->save();

        $this->setCharacterRelationships();

        /* Save in redis with relationships */
        Redis::set($redisKey, $this->character->toJson());

        return response()->json($this->character->toArray(), Response::HTTP_OK);
    }

    protected function setCharacterRelationships(): void
    {
        /* Search and save planet */
        if(!$this->character->planet_id){
            $planetId = $this->getIdFromSwapiUrl($this->characterSwapiData['homeworld']);
            $planet = Planet::find($planetId);
            if(!$planet){
                $this->fechtAndSaveEntity($this->swapiService, $this->characterSwapiData['homeworld'], Planet::class);
            }
            $this->character->planet_id = $planetId;
            $this->character->update();
        }

        /* Create relations with films */
        if(!empty($this->characterSwapiData['films'])){
            $filmIds = $this->fetchAndSaveEntities($this->swapiService, $this->characterSwapiData['films'], Film::class);
            $this->character->films()->sync($filmIds);
        }

        /* Create relations with vehicles */
        if(!empty($this->characterSwapiData['vehicles'])){
            $vechicleIds = $this->fetchAndSaveEntities($this->swapiService, $this->characterSwapiData['vehicles'], Vehicle::class);
            $this->character->vehicles()->sync($vechicleIds);
        }

        /* Create relations with species */
        if(!empty($this->characterSwapiData['species'])){
            $specieIds = $this->fetchAndSaveEntities($this->swapiService, $this->characterSwapiData['species'], Specie::class);
            $this->character->species()->sync($specieIds);
        }

        /* Load relationships */
        $this->character->load(['planet', 'films', 'vehicles', 'species']);
    }
}
