<?php

namespace Mobbex;

final class Repository
{
    /**
     * This method sort a list based in the order of a list of keys.
     * 
     * @param $sort List of keys
     * @param $listToSort List to sort based in kys array.
     * 
     * @return array
     */
    public static function sortList($sort, $listToSort)
    {
        $sorted = [];

        foreach ($sort as $key)
            if(array_key_exists($key, $listToSort))
                $sorted[$key] = $listToSort[$key];

        return array_merge($sorted, $listToSort);
    }

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
        // Try to get sources from cache memory
        $key  = \Mobbex\Model\Cache::generateKey('mobbex_sources_', $total, json_encode($installments));
        $data = \Mobbex\Platform::$cache->get($key);

        // Return sources from cache memory
        if($data)
            return $data;

        // Get sources from mobbex API
        $query = self::getInstallmentsQuery($total, $installments);

        $sources = \Mobbex\Api::request([
            'method' => 'GET',
            'uri'    => "sources" . ($query ? "?$query" : '')
        ]) ?: [];

        // Save sources in mobbex cache table with literal coding if there is any
        if($sources)
            \Mobbex\Platform::$cache->store($key, json_encode($sources, JSON_UNESCAPED_UNICODE));
        
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
        $commonFields = $advancedFields = $sourceNames = $sourceGroups = [];
        
        // Create common plan fields
        foreach (self::getSources() as $source) {
            // Only if have installments
            if (empty($source['installments']['list']))
                continue;

            // Create field array data
            foreach ($source['installments']['list'] as $plan) {
                $commonFields[$plan['reference']] = [
                    'id'          => 'common_plan_' . $plan['reference'],
                    'value'       => !in_array($plan['reference'], $checkedCommonPlans),
                    'label'       => $plan['name'],
                    'description' => $plan['description'],
                ];

                $sourceGroups[$plan['name']][] = $source['source']['reference'];
                $sourceGroups[$plan['name']]   = array_unique($sourceGroups[$plan['name']]);
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
                    'id'          => 'advanced_plan_' . $plan['uid'],
                    'value'       => in_array($plan['uid'], $checkedAdvancedPlans),
                    'label'       => $plan['name'],
                    'description' => $plan['description'],
                ];
            }
        }

        return compact('commonFields', 'advancedFields', 'sourceNames', 'sourceGroups');
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
        $countries = include __DIR__. '/utils/iso-3166/country-codes.php';

        return isset($countries[$code]) ? $countries[$code] : null;
    }

    /**
     * Generate a token using current credentials configured.
     * 
     * @return string 
     */
    public static function generateToken()
    {
        $apiKey      = \Mobbex\Platform::$settings['api_key'];
        $accessToken = \Mobbex\Platform::$settings['access_token'];

        return password_hash(
            "{$apiKey}|{$accessToken}",
            PASSWORD_DEFAULT
        );
    }

    /**
     * Validate a token generated from credentials configured.
     * 
     * @param mixed $token
     * 
     * @return bool True if token is valid.
     */
    public static function validateToken($token)
    {
        $apikey = \Mobbex\Platform::$settings['api_key'];
        $accessToken = \Mobbex\Platform::$settings['access_token'];

        return password_verify(
            "{$apikey}|{$accessToken}",
            $token
        );
    }

    /**
     * Get the last operation from mobbex filtering by reference.
     * 
     * @param string $reference
     * 
     * @return array|null
     * 
     * @throws Exception 
     */
    public static function getOperationFromReference($reference)
    {
        $result = \Mobbex\Api::request([
            'method' => 'GET',
            'url'    => 'https://api.mobbex.com/2.0/',
            'uri'    => "transactions/coupons/$reference",
        ]) ?: [];

        return $result ? reset($result) : null;
    }


    /** NEW PLANS FILTER */

    /**
     * Gets sources from API sources & return it formated.
     * If there are stored sources updates his values.
     * 
     * @param array $storedSources
     * @param array $sort
     * 
     * @return array
     */
    public static function getFormatedSources($storedSources = [], $sort = [])
    {   
        $sources = self::formatSources(
            array_column($storedSources, null, 'reference'),
            $sort
        );

        //Sort the sources
        if ($sort)
            $sources = self::sortList(array_keys($sort), $sources);

        return array_values($sources);
    }

    /**
     * Returns the sources formatted for being used in the new plans filter template.
     * 
     * @param array $srcList A list with the stored sources formatted.
     * @param array $sort A list with the plans sort order.
     * 
     * @return array
     */
    public static function formatSources($storedSources, $sort)
    {
        $formatedSources = $instList  = array();

        foreach (array_merge(self::getSources(), self::getSourcesAdvanced()) as $source) {

            $reference = $source['source']['reference'];

            // Add source to list if not exists
            if(!isset($formatedSources[$reference])) {
                $formatedSources[$reference] = [
                    'reference'    => $reference,
                    'name'         => $source['source']['name'],
                    'installments' => []
                ];
            }
            
            //Continue if source didn't have plans
            if (isset($source['installments']['enabled']) && !$source['installments']['enabled'])
                continue;

            // Detect type of installments
            $advanced           = !isset($source['installments']['list']);
            $installments       = $advanced ? $source['installments'] : $source['installments']['list'];

            //Get the installments stored in db for this source
            $storedInstallments = isset($storedSources[$reference]) ? array_column($storedSources[$reference]['installments'], null, 'uid') : [];

            //Format the installments list for being used in new plans filter
            foreach ($installments as $installment) {

                //Get installment uid
                $uid = $installment['uid'];

                //Get installment data
                $instList[$reference][$uid] = [
                    'uid'         => $uid,
                    'reference'   => $advanced ? null : $installment['reference'],
                    'name'        => $installment['name'],
                    'description' => $installment['description'],
                    'advanced'    => $advanced,
                    'active'      => !$advanced,
                ];

                //Update installment with stored data.
                if(isset($storedInstallments[$uid]))
                    $instList[$uid]['active'] = $storedInstallments[$uid]['active'];
            }

            // Sort the plans
            if($sort && isset($sort[$reference]))
                $instList[$reference] = self::sortList($sort[$reference], $instList[$reference]);

            // Add installments formated to sources list
            $formatedSources[$reference]['installments'] = array_values($instList[$reference]);
        }

        return $formatedSources;
    }

    /**
     * Returns a sorted list of sources with their installments.
     * 
     * @param array $configuredPlans A list of sources configured for a product/category
     * 
     * @return array
     */
    public static function getPlansSortOrder($configuredPlans)
    {
        $sortedPlans = [];

        foreach ($configuredPlans as $source) {
            $sortedPlans[$source['reference']] = [];

            if (!isset($source['installments']))
                continue;

            foreach ($source['installments'] as $installment)
                $sortedPlans[$source['reference']][] = $installment['uid'];
        }

        return $sortedPlans;
    }

    /**
     * Sort in the given order a list of sources.
     * 
     * @param array $sources List of sources.
     * @param array $sortOrder Order to sort sources & their installments.
     * 
     * @return array 
     */
    public static function sortSources($sources, $sortOrder)
    {
        $sortedSources = [];

        //Sort sources
        foreach ($sortOrder as $sourceRef => $uids) {
            foreach ($sources as $source){
                if ($source['source']['reference'] === $sourceRef && !isset($sortedSources[$sourceRef])){
                    $sortedSources[$sourceRef] = $source;
                } elseif($source['source']['reference'] === $sourceRef && isset($sortedSources[$sourceRef]['installments']['list'])) {
                    $sortedSources[$sourceRef]['installments']['list'] = array_merge(
                        $sortedSources[$sourceRef]['installments']['list'],
                        $source['installments']['list']
                    );
                }

            }
        }

        //Sort their installments
        foreach ($sortedSources as $sourceRef => &$source) {
            $sortedPlans = [];

            foreach ($sortOrder[$sourceRef] as $uid) {
                $index = array_search(
                    $uid,
                    array_column($source['installments']['list'], 'uid')
                );
                
                if ($index !== false)
                    $sortedPlans[] = $source['installments']['list'][$index];
            }

            $source['installments'] = $sortedPlans;
        }

        return $sortedSources;
    }

}
