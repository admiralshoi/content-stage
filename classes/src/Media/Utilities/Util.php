<?php
namespace classes\src\Media\Utilities;

class Util {



    public static function ensureValidObjectResponse(string|array $data): ?array {
        if(empty($data)) return null;
        if(!is_array($data)) $data = json_decode($data,true);
        return !is_array($data) ? null : $data;
    }


    public static function nestedArray(array $targetObject, array $keys, mixed $defaultReturnKey = null): mixed {
        if(empty($keys) || empty($targetObject)) return $defaultReturnKey;

        $loop = $targetObject;
        foreach ($keys as $key) {
            if(!is_array($loop) || !array_key_exists($key, $loop)) return $defaultReturnKey;
            $loop = $loop[$key];
        }

        return $loop;
    }


    public static function daysBetweenTimestamps(string|int $timestamp_1, string|int $timestamp_2): int {
        $diff = (int)$timestamp_1 >= (int)$timestamp_2 ? (int)$timestamp_1 - (int)$timestamp_2 : (int)$timestamp_2 - (int)$timestamp_1;
        return round($diff / (60 * 60 * 24));
    }

}