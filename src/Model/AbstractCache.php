<?php

namespace Mobbex\Model;

abstract class AbstractCache
{
    /**
     * Identifier key of the stored data. 
     * 
     * @var string 
     */
    public $key;
    
    /** 
     * Stored data.
     * 
     * @var string 
     */
    public $data;
    
    /** 
     * Stored date. 
     * 
     * @var string
     */
    public $date;

    /**
     * Table structure definition.
     * 
     * @var array 
     */
    public static $definition = [
        'table'   => 'mobbex_cache',
        'columns' => [
            'key'  => ['type' => 'varchar(11)', 'primary' => true, 'not_null' => true],
            'data' => ['type' => 'varchar(11)', 'not_null' => true],
            'date' => ['type' => 'date', 'not_null' => true],
        ]
    ];

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