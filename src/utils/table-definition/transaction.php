<?php

/**
 * Mobbex Transaction table definition. 
 */
return [
    ['Field' => 'id',                 'Type' => 'int(11)',       'Null' => 'NO', 'Key' => 'PRI', 'Extra' => 'auto_increment', 'Default' => null],
    ['Field' => 'order_id',           'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'parent',             'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'childs',             'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'operation_type',     'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'payment_id',         'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'description',        'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'status_code',        'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'status_message',     'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'source_name',        'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'source_type',        'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'source_reference',   'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'source_number',      'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'source_expiration',  'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'source_installment', 'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'installment_name',   'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'installment_amount', 'Type' => 'decimal(18,2)', 'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'installment_count',  'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'source_url',         'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'cardholder',         'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'entity_name',        'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'entity_uid',         'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'customer',           'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'checkout_uid',       'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'total',              'Type' => 'decimal(18,2)', 'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'currency',           'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'risk_analysis',      'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'data',               'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'created',            'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
    ['Field' => 'updated',            'Type' => 'text',          'Null' => 'NO', 'Key' => '',    'Extra' => '', 'Default' => null],
];