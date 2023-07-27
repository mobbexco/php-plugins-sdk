<?php

/**
 * Mobbex Custom Fields table definition. 
 */
return [
    ['Field' => 'id',         'Type' => 'int(11)', 'Null' => 'NO', 'Key' => 'PRI', 'Default' => null, 'Extra' => 'auto_increment'],
    ['Field' => 'row_id',     'Type' => 'int(11)', 'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'object',     'Type' => 'text',    'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'field_name', 'Type' => 'text',    'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'data',       'Type' => 'text',    'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
];