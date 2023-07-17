<?php

/**
 * Mobbex Execution table definition. 
 */
return [
    ['Field' => 'uid',              'Type' => 'varchar(255)',  'Null' => 'NO', 'Key' => 'PRI', 'Extra' => '', 'Default' => null],
    ['Field' => 'subscription_uid', 'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'subscriber_uid',   'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'status',           'Type' => 'int(11)',       'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'total',            'Type' => 'decimal(18,2)', 'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'date',             'Type' => 'datetime',      'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'data',             'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
];