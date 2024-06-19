<?php

namespace Mobbex\Model;

class Cache
{
    /**
     * Generates a unique key for values to store.
     * 
     * @param string $key Initial name for de key.
     * @param array  $params A list of values to add hashed to the key.
     * 
     * @return string
     * 
     */
    public static function generateKey($key, ...$params)
    {
        return $key . md5(\Mobbex\Platform::$settings['api_key'] . \Mobbex\Platform::$settings['access_token'] . implode($params));
    }

    /**
     * Store data in mobbex cache table.
     * 
     * @param string $key Identifier key for data to store.
     * @param string $data Data to store.
     * 
     * @return boolean
     * 
     */
    public function store($key, $data){}

    /**
     * Get data stored in mobbex chache table.
     * 
     * @param string $key Identifier key for cache data.
     * @param int    $interval Interval to check if data is expired
     * 
     * @return string|bool $data Data to store.
     * 
     */
    public function get($key, $interval = 0){}

    /**
     * Delete expired stored data in cache table.
     */
    public function deleteExpiredCache(){}
}