<?php

namespace Mobbex;

final class Repository
{
    /**
     * Get sources from Mobbex.
     * 
     * @param int|float $total Amount to calculate payment methods.
     * @param string[] $installments Use +uid:<uid> to include and -<reference> to exclude.
     * 
     * @return array Mobbex raw response.
     */
    public static function getSources($total = null, $installments = [])
    {
        //try to get sources from cache memory
        $key  = \Mobbex\Cache::generateKey('mobbex_sources_', [$total, json_encode($installments)]);
        $data = \Mobbex\Platform::$cache->get($key);

        //return sources from cache memory
        if($data)
            return $data;

        //get sources from mobbex API
        $query = self::getInstallmentsQuery($total, $installments);

        $sources = \Mobbex\Api::request([
            'method' => 'GET',
            'uri'    => "sources" . ($query ? "?$query" : '')
        ]) ?: [];

        if($sources)
            \Mobbex\Platform::$cache->store($key, json_encode($sources));
        
        return $sources;
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
     * Returns a query param with the installments of the product.
     * @param int $total
     * @param array $installments
     * @return string $query
     */
    public static function getInstallmentsQuery($total, $installments = [])
    {
        // Build query params and replace special chars
        return preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', http_build_query(compact('total', 'installments')));
    }

    /**
     * Retrieve plans filter fields data for product/category settings.
     * 
     * @param int|string $id
     * @param string[] $checkedCommonPlans
     * @param string[] $checkedAdvancedPlans
     * 
     * @return array
     */
    public static function getPlansFilterFields($id, $checkedCommonPlans = [], $checkedAdvancedPlans = [])
    {
        $commonFields = $advancedFields = $sourceNames = [];
        
        // Create common plan fields
        foreach (self::getSources() as $source) {
            // Only if have installments
            if (empty($source['installments']['list']))
            continue;

            // Create field array data
            foreach ($source['installments']['list'] as $plan) {
                $commonFields[$plan['reference']] = [
                    'id'    => 'common_plan_' . $plan['reference'],
                    'value' => !in_array($plan['reference'], $checkedCommonPlans),
                    'label' => $plan['name'] ?: $plan['description'],
                ];
            }
        }

        // Create plan with advanced rules fields
        foreach (self::getSourcesAdvanced() as $source) {
            // Only if have installments
            if (empty($source['installments']))
            continue;

            // Save source name
            $sourceNames[$source['source']['reference']] = $source['source']['name'];

            // Create field array data
            foreach ($source['installments'] as $plan) {
                $advancedFields[$source['source']['reference']][] = [
                    'id'      => 'advanced_plan_' . $plan['uid'],
                    'value'   => in_array($plan['uid'], $checkedAdvancedPlans),
                    'label'   => $plan['name'] ?: $plan['description'],
                ];
            }
        }

        return compact('commonFields', 'advancedFields', 'sourceNames');
    }

    /**
     * Retrieve installments checked on plans filter of each item.
     * 
     * @param array $items
     * @param array $commonPlans
     * @param array $advancedPlans
     * 
     * @return array
     */
    public static function getInstallments($items, $commonPlans, $advancedPlans)
    {
        $installments = [];

        // Add inactive (common) plans to installments
        foreach ($commonPlans as $plan)
            $installments[] = '-' . $plan;

        // Add active (advanced) plans to installments only if the plan is active on all products
        foreach (array_count_values($advancedPlans) as $plan => $reps) {
            if ($reps == count($items))
                $installments[] = '+uid:' . $plan;
        }

        // Remove duplicated plans and return
        return array_values(array_unique($installments));
    }

    /**
     * Converts 2-letter country codes to 3-letter ISO codes.
     * 
     * @param string $code 2-Letter ISO code.
     * 
     * @return string|null
     */
    public static function convertCountryCode($code)
    {
        $countries = include ('iso-3166/country-codes.php') ?: [];

        return isset($countries[$code]) ? $countries[$code] : null;
    }
}
