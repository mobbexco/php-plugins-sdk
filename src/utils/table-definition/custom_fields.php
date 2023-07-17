<?php

/**
 * Mobbex Custom Fields table definition. 
 */
return [
    ['Field' => 'id',         'Type' => 'int(11)', 'Null' => 'NO', 'Key' => 'PRI', 'Extra' => 'auto_increment', 'Default' => null],
    ['Field' => 'row_id',     'Type' => 'int(11)', 'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'object',     'Type' => 'text',    'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'field_name', 'Type' => 'text',    'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'data',       'Type' => 'text',    'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
];