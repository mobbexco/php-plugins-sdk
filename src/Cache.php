<?php

namespace Mobbex;

class Cache
{
    /**
     * Generates a unique key for values to store.
     * 
     * @param string $key Initial name for de key.
     * @param array $params A list of values to add hashed to the key.
     * 
     * @return string
     */
    public static function generateKey($key, ...$params)
    {
        return $key . md5(\Mobbex\Platform::$settings['api_key'] . \Mobbex\Platform::$settings['access_token'] . implode($params));
    }
}