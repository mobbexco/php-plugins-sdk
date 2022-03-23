<?php

namespace Mobbex;

defined('ABSPATH') || exit;

final class Repository
{
    /**
     * Get sources from Mobbex.
     * 
     * @param int|float $total Amount to calculate payment methods.
     * @param array $installments Use +uid:<uid> to include and -<reference> to exclude.
     * 
     * @return array Mobbex raw response.
     */
    public static function getSources($total = null, $installments = [])
    {
        $entity = self::getEntity();

        if (empty($entity['countryReference']) || empty($entity['tax_id']))
            return [];

        return \Mobbex\Api::request([
            'method' => 'POST',
            'uri'    => "sources/list/$entity[countryReference]/$entity[tax_id]" . ($total ? "?total=$total" : ''),
            'body'   => compact('installments'),
        ]) ?: [];
    }

    /**
     * Get sources with advanced rule installments from mobbex.
     * 
     * @param string $rule
     * 
     * @return array Mobbex raw response.
     */
    public static function getSourcesAdvanced($rule = 'externalMatch')
    {
        return \Mobbex\Api::request([
            'method' => 'GET',
            'uri'    => "sources/rules/$rule/installments",
        ]) ?: [];
    }

    /**
     * Get entity data from Mobbex or db if possible.
     * 
     * @return string[] Mobbex raw response.
     */
    public static function getEntity()
    {
        // First, try to get from settings
        $entity = \Mobbex\Platform::$settings['entity_data'];

        if ($entity)
            return is_string($entity) ? json_decode($entity, true) : $entity;

        return \Mobbex\Api::request([
            'method' => 'GET',
            'uri'    => 'entity/validate',
        ]);
    }
}