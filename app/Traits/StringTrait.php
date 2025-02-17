<?php

namespace App\Traits;

trait StringTrait
{
    public function compressObjectToString(array $jsonObject): string
    {
        return base64_encode(gzcompress(json_encode($jsonObject), 9));
    }

    public function uncompressJsonStringToArray(string $string): array
    {
        /* If your column is a stream, you must covert to string whit this code example:
         stream_get_contents($originnal_json) */
        return json_decode(gzuncompress(base64_decode($string)), true);
    }

    public function getIdFromSwapiUrl(string $swapitUrl): int
    {
        $swapitUrlParts = explode('/', trim($swapitUrl));
        return $swapitUrlParts[count($swapitUrlParts) - 2];
    }
}