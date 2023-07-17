<?php

/**
 * Mobbex Task table definition. 
 */
return [
    ['Field' => 'id',             'Type' => 'int(11)', 'Null' => 'NO', 'Key' => 'PRI', 'Extra' => 'auto_increment', 'Default' => null],
    ['Field' => 'name',           'Type' => 'text',    'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'args',           'Type' => 'text',    'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'interval',       'Type' => 'text',    'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'period',         'Type' => 'text',    'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'limit',          'Type' => 'text',    'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'executions',     'Type' => 'text',    'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'start_date',     'Type' => 'text',    'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'last_execution', 'Type' => 'text',    'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'next_execution', 'Type' => 'text',    'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
];