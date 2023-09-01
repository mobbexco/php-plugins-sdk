<?php

/**
 * Mobbex Cache table definition.
 * 
 * The format used in the definition of the mobbex tables corresponds to the format in which "show columns" returns data in sql.
 * @see https://dev.mysql.com/doc/refman/8.0/en/show-columns.html 
 */
return [
    ['Field' => 'cache_key', 'Type' => 'varchar(255)', 'Null' => 'NO', 'Key' => 'PRI', 'Default' => null,                  'Extra' => ''],
    ['Field' => 'data',      'Type' => 'text',         'Null' => 'NO', 'Key' => '',    'Default' => null,                  'Extra' => ''],
    ['Field' => 'date',      'Type' => 'timestamp',    'Null' => 'NO', 'Key' => '',    'Default' => 'current_timestamp()', 'Extra' => ''],
];