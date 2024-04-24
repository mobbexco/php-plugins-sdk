<?php

/**
 * Mobbex Custom Fields table definition. 
 * 
 * The format used in the definition of the mobbex tables corresponds to the format in which "show columns" returns data in sql.
 * @see https://dev.mysql.com/doc/refman/8.0/en/show-columns.html 
 */
return [
    ['Field' => 'id',         'Type' => 'int(11)',      'Null' => 'NO', 'Key' => 'PRI', 'Default' => null, 'Extra' => 'auto_increment'],
    ['Field' => 'row_id',     'Type' => 'varchar(255)', 'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'object',     'Type' => 'text',         'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'field_name', 'Type' => 'text',         'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'data',       'Type' => 'text',         'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
];