<?php

/**
 * Mobbex Execution table definition. 
 * 
 * The format used in the definition of the mobbex tables corresponds to the format in which "show columns" returns data in sql.
 * @see https://dev.mysql.com/doc/refman/8.0/en/show-columns.html
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