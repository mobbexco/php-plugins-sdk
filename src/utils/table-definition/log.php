<?php

/**
 * Mobbex Log table definition. 
 */
return [
    ['Field' => 'log_id',        'Type' => 'int(11)',  'Null' => 'NO', 'Key' => 'PRI', 'Default' => null, 'Extra' => 'auto_increment'],
    ['Field' => 'type',          'Type' => 'text',     'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'message',       'Type' => 'text',     'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'data',          'Type' => 'text',     'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'creation_date', 'Type' => 'datetime', 'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
];