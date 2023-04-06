<?php

namespace Mobbex\Model;

abstract class AbstractCache
{
    /**
     *  @var string 
     * Identifier key of the stored data. 
     */
    public $key;
    
    /** 
     * @var string 
     * Stored data.
     */
    public $data;
    
    /** 
     * @var timestamp
     * Stored date. 
     */
    public $date;

    /**
     * @var array Table structure definition.
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