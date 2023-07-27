<?php

/**
 * Mobbex Subscriber table definition. 
 */
return [
    ['Field' => 'order_id',         'Type' => 'int(11)',  'Null' => 'NO', 'Key' => 'PRI', 'Default' => null, 'Extra'  => 'auto_increment'],
    ['Field' => 'uid',              'Type' => 'text',     'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra'  => ''],
    ['Field' => 'subscription_uid', 'Type' => 'text',     'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra'  => ''],
    ['Field' => 'state',            'Type' => 'int(11)',  'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra'  => ''],
    ['Field' => 'test',             'Type' => 'tinyint',  'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra'  => ''],
    ['Field' => 'name',             'Type' => 'text',     'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra'  => ''],
    ['Field' => 'email',            'Type' => 'text',     'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra'  => ''],
    ['Field' => 'phone',            'Type' => 'text',     'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra'  => ''],
    ['Field' => 'identification',   'Type' => 'text',     'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra'  => ''],
    ['Field' => 'customer_id',      'Type' => 'int(11)',  'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra'  => ''],
    ['Field' => 'source_url',       'Type' => 'text',     'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra'  => ''],
    ['Field' => 'control_url',      'Type' => 'text',     'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra'  => ''],
    ['Field' => 'register_data',    'Type' => 'text',     'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra'  => ''],
    ['Field' => 'start_date',       'Type' => 'datetime', 'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra'  => ''],
    ['Field' => 'last_execution',   'Type' => 'datetime', 'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra'  => ''],
    ['Field' => 'next_execution',   'Type' => 'datetime', 'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra'  => ''],
];