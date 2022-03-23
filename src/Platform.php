<?php

namespace Mobbex;

defined('ABSPATH') || exit;

final class Platform
{
    /** Name of current platform */
    public static $name;

    /** Version of Mobbex plugin */
    public static $version;

    /** Key-Value array with current extensions and their versions */
    public static $extensions = [];

    /** Default settings values */
    public static $settings = [
        'api_key'      => null,
        'access_token' => null,
        'test_mode'    => false,
        'embed'        => true,
        'wallet'       => false,
        'payment_mode' => 'payment.v2',
        'multicard'    => false,
        'multivendor'  => false,
        'title'        => null,
        'logo'         => null,
        'theme'        => 'light',
        'background'   => null,
        'color'        => null,
    ];

    /** Hook execution callback */
    public static $hook;

    /**
     * Set current platform information.
     * 
     * @param string $name Name of current platform.
     * @param string $version Version of Mobbex plugin.
     * @param array $extensions Current extensions and their versions.
     * @param array $settings Plugin settings values.
     * @param callable $hook Hook execution callback. @see \Mobbex\Platform::defaultHook()
     */
    public static function init($name, $version, $extensions = [], $settings = [], $hook = null)
    {
        self::$name       = $name;
        self::$version    = $version;
        self::$extensions = $extensions;
        self::$settings   = array_merge(self::$settings, $settings);
        self::$hook       = $hook ?: [self::class, 'defaultHook'];
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
     * Default callback and description of \Mobbex\Platform::$hook callable.
     * 
     * @param string $name The hook name (in camel case).
     * @param bool $filter Filter first arg in each execution.
     * @param mixed ...$args Arguments to pass.
     * 
     * @return mixed Last execution response or value filtered. Null on exceptions.
     */
    public static function defaultHook($name, $filter, ...$args)
    {
        return $filter ? $args[0] : false;
    }
}