<?php

namespace Mobbex\Model;

abstract class AbstractCache
{
    /**
     * Identifier key of the stored data. 
     * @var string 
     */
    public $key;
    
    /** 
     * Stored data.
     * @var string 
     */
    public $data;
    
    /** 
     * Stored date. 
     * @var string
     */
    public $date;

    /**
     * Table structure definition.
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
    
}