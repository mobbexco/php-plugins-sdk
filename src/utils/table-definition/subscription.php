<?php

/**
 * Mobbex Subscription table definition. 
 */
return [
    ['Field' => 'product_id',  'Type' => 'int(11)',       'Null' => 'NO', 'Key' => 'PRI', 'Extra' => 'auto_increment', 'Default' => null],
    ['Field' => 'uid',         'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'type',        'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'state',       'Type' => 'int(11)',       'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'interval',    'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'name',        'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'description', 'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'total',       'Type' => 'decimal(18,2)', 'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'limit',       'Type' => 'int(11)',       'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'free_trial',  'Type' => 'int(11)',       'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'signup_fee',  'Type' => 'decimal(18,2)', 'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'data',        'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
];