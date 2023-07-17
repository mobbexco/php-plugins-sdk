<?php

/**
 * Mobbex Subscriber table definition. 
 */
return [
    ['Field' => 'order_id',         'Type' => 'int(11)',  'Null' => 'NO', 'Key' => 'PRI', 'Extra'  => 'auto_increment', 'Default' => null],
    ['Field' => 'uid',              'Type' => 'text',     'Null' => 'NO', 'Key' => '',    'Extra'  => '', 'Default' => null],
    ['Field' => 'subscription_uid', 'Type' => 'text',     'Null' => 'NO', 'Key' => '',    'Extra'  => '', 'Default' => null],
    ['Field' => 'state',            'Type' => 'int(11)',  'Null' => 'NO', 'Key' => '',    'Extra'  => '', 'Default' => null],
    ['Field' => 'test',             'Type' => 'tinyint',  'Null' => 'NO', 'Key' => '',    'Extra'  => '', 'Default' => null],
    ['Field' => 'name',             'Type' => 'text',     'Null' => 'NO', 'Key' => '',    'Extra'  => '', 'Default' => null],
    ['Field' => 'email',            'Type' => 'text',     'Null' => 'NO', 'Key' => '',    'Extra'  => '', 'Default' => null],
    ['Field' => 'phone',            'Type' => 'text',     'Null' => 'NO', 'Key' => '',    'Extra'  => '', 'Default' => null],
    ['Field' => 'identification',   'Type' => 'text',     'Null' => 'NO', 'Key' => '',    'Extra'  => '', 'Default' => null],
    ['Field' => 'customer_id',      'Type' => 'int(11)',  'Null' => 'NO', 'Key' => '',    'Extra'  => '', 'Default' => null],
    ['Field' => 'source_url',       'Type' => 'text',     'Null' => 'NO', 'Key' => '',    'Extra'  => '', 'Default' => null],
    ['Field' => 'control_url',      'Type' => 'text',     'Null' => 'NO', 'Key' => '',    'Extra'  => '', 'Default' => null],
    ['Field' => 'register_data',    'Type' => 'text',     'Null' => 'NO', 'Key' => '',    'Extra'  => '', 'Default' => null],
    ['Field' => 'start_date',       'Type' => 'datetime', 'Null' => 'NO', 'Key' => '',    'Extra'  => '', 'Default' => null],
    ['Field' => 'last_execution',   'Type' => 'datetime', 'Null' => 'NO', 'Key' => '',    'Extra'  => '', 'Default' => null],
    ['Field' => 'next_execution',   'Type' => 'datetime', 'Null' => 'NO', 'Key' => '',    'Extra'  => '', 'Default' => null],
];