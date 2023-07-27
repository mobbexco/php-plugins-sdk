<?php

/**
 * Mobbex Cache table definition. 
 */
return [
    ['Field' => 'cache_key', 'Type' => 'varchar(255)', 'Null' => 'NO', 'Key' => 'PRI', 'Default' => null,                  'Extra' => ''],
    ['Field' => 'data',      'Type' => 'text',         'Null' => 'NO', 'Key' => '',    'Default' => null,                  'Extra' => ''],
    ['Field' => 'date',      'Type' => 'timestamp',    'Null' => 'NO', 'Key' => '',    'Default' => 'current_timestamp()', 'Extra' => ''],
];