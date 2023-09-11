<?php

/**
 * Mobbex Transaction table definition.
 * 
 * The format used in the definition of the mobbex tables corresponds to the format of "show columns" statement returned data.
 * @see https://dev.mysql.com/doc/refman/8.0/en/show-columns.html
 */
return [
    ['Field' => 'id',                 'Type' => 'int(11)',       'Null' => 'NO', 'Key' => 'PRI', 'Default' => null, 'Extra' => 'auto_increment'],
    ['Field' => 'order_id',           'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'parent',             'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'childs',             'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'operation_type',     'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'payment_id',         'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'description',        'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'status_code',        'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'status_message',     'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'source_name',        'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'source_type',        'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'source_reference',   'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'source_number',      'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'source_expiration',  'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'source_installment', 'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'installment_name',   'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'installment_amount', 'Type' => 'decimal(18,2)', 'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'installment_count',  'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'source_url',         'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'cardholder',         'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'entity_name',        'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'entity_uid',         'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'customer',           'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'checkout_uid',       'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'total',              'Type' => 'decimal(18,2)', 'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'currency',           'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'risk_analysis',      'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'data',               'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'created',            'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
    ['Field' => 'updated',            'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Default' => null, 'Extra' => ''],
];