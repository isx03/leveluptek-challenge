<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Exception\GuzzleException;

class SwapiService {
    protected $client;
    protected $hostSwapi;

    public function __construct()
    {
        $this->hostSwapi = env('HOST_SWAPI');
        $this->client = new Client([
            'timeout'  => 3.0,
        ]);
    }

    public function getData(string $endpoint, bool $useHost = true): ?array
    {
        $url = $useHost ? "{$this->hostSwapi}{$endpoint}" : $endpoint;
        try {
            $response = $this->client->request('GET', $url);
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            return null;
        }
    }

    public function getPoolData(array $enpoints)
    {
        $promises = [];

        foreach ($enpoints as $url) {
            $promises[$url] = $this->client->getAsync($url);
        }

        $responses = Promise\Utils::settle($promises)->wait();

        $data = [];
        foreach ($responses as $url => $result) {
            if ($result['state'] === 'fulfilled') {
                $response = $result['value'];
                $data[$url] = json_decode($response->getBody()->getContents(), true);
            }
        }

        return $data;
    }
}