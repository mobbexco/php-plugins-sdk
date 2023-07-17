<?php

/**
 * Mobbex Cache table definition. 
 */
return [
    ['Field' => 'cache_key', 'Type' => 'varchar(255)', 'Null' => 'NO', 'Key' => 'PRI', 'Extra' => '', 'Default' => null],
    ['Field' => 'data',      'Type' => 'text',         'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'date',      'Type' => 'timestamp',    'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => 'current_timestamp()'],
];