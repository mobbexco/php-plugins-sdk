<?php

namespace Mobbex;

/**
 * Sort an multidimensional array using an index map as guide.
 * 
 * @param array &$array The array to sort.
 * @param array $indexes The index map.
 * @param string $indexPath The position to find the index in first array. Use dots on multidimensional arrays.
 * 
 * @see \Mobbex\arrayAt() to more info on paths.
 */
function multisortByIndex(&$array, $indexes, $indexPath) {
    if (!$array || !$indexes)
        return;

    usort($array, function($a, $b) use($indexes, $indexPath) {
        $indexA = array_search(arrayAt($a, $indexPath), $indexes);
        $indexB = array_search(arrayAt($b, $indexPath), $indexes);

        // If an index is not found, consider it infinite
        if ($indexA === false) $indexA = PHP_INT_MAX;
        if ($indexB === false) $indexB = PHP_INT_MAX;

        return $indexA - $indexB;
    });
}

/**
 * Access to an array using a path (dots for multidimensional access).
 * 
 * @param array $array The array to access.
 * @param string $path The path with the positions to access into the array.
 * 
 * @return mixed The array value obtained.
 */
function arrayAt($array, $path) {
    $currentPos = $array;

    foreach (explode('.', $path) as $value) {
        if (!array_key_exists($value, $currentPos))
            return null;

        $currentPos = $currentPos[$value];
    }

    return $currentPos;
}

/**
 * Try to decode a json value.
 * 
 * @param mixed $value
 * 
 * @return mixed If the value is json returns an associative array.
 */
function maybeDecodeJson(&$value) {
    if (!is_string($value))
        return $value;

    $jsonDecoded = json_decode($value, true);

    return json_last_error() === JSON_ERROR_NONE ? $jsonDecoded : $value;
}
