<?php

/**
 * Mobbex Execution table definition. 
 */
return [
    ['Field' => 'uid',              'Type' => 'varchar(255)',  'Null' => 'NO', 'Key' => 'PRI', 'Default' => null, 'Extra' => ''],
    ['Field' => 'subscription_uid', 'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'subscriber_uid',   'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'status',           'Type' => 'int(11)',       'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'total',            'Type' => 'decimal(18,2)', 'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'date',             'Type' => 'datetime',      'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'data',             'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
];