<?php

namespace App\Traits;

use App\Traits\StringTrait;
use App\Services\SwapiService;

trait EntiteableTrait
{
    use StringTrait;

    protected function fechtAndSaveEntity(SwapiService $swapiService, string $url, string $entityNamespace): object
    {
        $apiData = $swapiService->getData($url, false);

        if(!$apiData){
            throw new \Exception("URL {$url} not found data");
        }

        $apiData['id'] = $this->getIdFromSwapiUrl($url);
        $apiData['original_json'] = $this->compressObjectToString($apiData);

        $entity = app($entityNamespace);
        $entity->fill($apiData);
        $entity->save();

        return $entity;
    }

    protected function fetchAndSaveEntities(SwapiService $swapiService, array $endpoints, string $entityNamespace): array
    {
        $entityIds = [];
        $entityData = [];

        $poolData = $swapiService->getPoolData($endpoints);
        $entity = app($entityNamespace);

        foreach($poolData as $url => $apiData){
            $apiData['id'] = $this->getIdFromSwapiUrl($url);
            $apiData['original_json'] = $this->compressObjectToString($apiData);

            $entity->fill($apiData);
            $entityAttributes = $entity->getAttributes();
            $entityData[] = $entityAttributes;

            $entityIds[] = $apiData['id'];
        }

        $entity->upsert($entityData, ['id']);

        return $entityIds;
    }
}