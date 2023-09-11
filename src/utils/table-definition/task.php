<?php

/**
 * Mobbex Task table definition.
 * 
 * The format used in the definition of the mobbex tables corresponds to the format in which "show columns" returns data in sql.
 * @see https://dev.mysql.com/doc/refman/8.0/en/show-columns.html
 */
return [
    ['Field' => 'id',             'Type' => 'int(11)', 'Null' => 'NO', 'Key' => 'PRI', 'Default' => null, 'Extra' => 'auto_increment'],
    ['Field' => 'name',           'Type' => 'text',    'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'args',           'Type' => 'text',    'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'interval',       'Type' => 'text',    'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'period',         'Type' => 'text',    'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'limit',          'Type' => 'text',    'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'executions',     'Type' => 'text',    'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'start_date',     'Type' => 'text',    'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'last_execution', 'Type' => 'text',    'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'next_execution', 'Type' => 'text',    'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
];