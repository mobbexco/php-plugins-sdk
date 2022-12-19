<?php

namespace Mobbex;

final class Platform
{
    /** Name of current platform */
    public static $name;

    /** Version of Mobbex plugin */
    public static $version;

    /** Domain name of current site */
    public static $domain;

    /** Key-Value array with current extensions and their versions */
    public static $extensions = [];

    /** Default settings values */
    public static $settings = [
        'api_key'      => null,
        'access_token' => null,
        'entity_data'  => null,
        'test'         => false,
        'embed'        => true,
        'wallet'       => false,
        'payment_mode' => 'payment.v2',
        'multicard'    => false,
        'multivendor'  => false,
        'theme'        => 'light',
        'color'        => null,
        'background'   => null,
        'header_name'  => null,
        'header_logo'  => null,
        'timeout'      => 5,
    ];

    /** Hook execution callback */
    public static $hook;

    /**
     * Set current platform information.
     * 
     * @param string $name Name of current platform.
     * @param string $version Version of Mobbex plugin.
     * @param string $domain Domain name or URL of current site.
     * @param array $extensions Current extensions and their versions.
     * @param array $settings Plugin settings values.
     * @param callable $hook Hook execution callback. @see Description at \Mobbex\Platform::hook()
     */
    public static function init($name, $version, $domain, $extensions = [], $settings = [], $hook = null)
    {
        self::$name       = $name;
        self::$version    = $version;
        self::$domain     = str_replace('www.', '', parse_url($domain, PHP_URL_HOST) ?: $domain);
        self::$extensions = $extensions;
        self::$settings   = array_merge(self::$settings, $settings);
        self::$hook       = $hook;
    }

    /**
     * Retrieve platform versions info formatted as array.
     * 
     * @return array 
     */
    public static function toArray()
    {
        return [
            'name'      => self::$name,
            'version'   => self::$version,
            'ecommerce' => self::$extensions,
        ];
    }

    /**
     * Retrieve platform versions info formatted as string.
     * 
     * @return string 
     */
    public static function toString()
    {
        return str_replace('=', '/', http_build_query(self::$extensions + ['Plugin' => self::$version], '', ' '));
    }

    /**
     * Register a hook using the callback passed on construct.
     * 
     * @param string $name The hook name (in camel case).
     * @param bool $filter Filter first arg in each execution.
     * @param mixed ...$args Arguments to pass.
     * 
     * @return mixed Last execution response or value filtered. Null on exceptions.
     */
    public static function hook($name, $filter, ...$args)
    {
        if (is_callable(self::$hook))
            return call_user_func(self::$hook, $name, $filter, ...$args);

        return $filter ? $args[0] : false;
    }
}