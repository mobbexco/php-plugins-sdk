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
    public static function generateKey($key, $params = [])
    {
        //Add mobbex credentials to the hash
        $hash = \Mobbex\Platform::$settings['api_key'] . \Mobbex\Platform::$settings['access_token'];
        //Add the params to the hash
        foreach ($params as $param)
            $hash .= ($param ?: '');

        return $key . md5($hash);
    }
}