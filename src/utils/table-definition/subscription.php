<?php

/**
 * Mobbex Subscription table definition. 
 */
return [
    ['Field' => 'product_id',  'Type' => 'int(11)',       'Null' => 'NO', 'Key' => 'PRI', 'Default' => null, 'Extra' => 'auto_increment'],
    ['Field' => 'uid',         'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'type',        'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'state',       'Type' => 'int(11)',       'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'interval',    'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'name',        'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'description', 'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'total',       'Type' => 'decimal(18,2)', 'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'limit',       'Type' => 'int(11)',       'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'free_trial',  'Type' => 'int(11)',       'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'signup_fee',  'Type' => 'decimal(18,2)', 'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'data',        'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
];