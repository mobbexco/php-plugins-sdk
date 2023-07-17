<?php

/**
 * Mobbex Log table definition. 
 */
return [
    ['Field' => 'log_id',        'Type' => 'int(11)',  'Null' => 'NO', 'Key' => 'PRI', 'Extra' => 'auto_increment', 'Default' => null],
    ['Field' => 'type',          'Type' => 'text',     'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'message',       'Type' => 'text',     'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'data',          'Type' => 'text',     'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'creation_date', 'Type' => 'datetime', 'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
];