<?php

namespace Mobbex;

class CurrencyHandler
{
    /**
     * Get total converted from one currency to another
     * 
     * @param float|int $total Value to convert
     * @param string $from Initial currency
     * @param string $to Currency to convert
     * 
     * @return float
     */
    public static function convert($total, $from, $to)
    {
        if (empty($from) || $from === $to)
            return (float) $total;

        $response = \Mobbex\Api::request([
            'method' => 'GET',
            'uri'    => "currency/convert?from=$from&to=$to&total=$total"
        ]) ?: [];

        return (float) $response['result'];
    }

    /**
     * Get all supported currencies
     * 
     * @return array
     */
    public static function get()
    {
        $response = \Mobbex\Api::request([
            'method' => 'GET',
            'uri'    => 'entity/currencies'
        ]) ?: [];

        return $response['data'];
    }
}