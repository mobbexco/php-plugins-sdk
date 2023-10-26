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
        'api_key'                            => null,
        'access_token'                       => null,
        'entity_data'                        => null,
        'test'                               => false,
        'embed'                              => true,
        'wallet'                             => false,
        'payment_mode'                       => 'payment.v2',
        'multicard'                          => false,
        'multivendor'                        => false,
        'theme'                              => 'light',
        'color'                              => null,
        'background'                         => null,
        'header_name'                        => null,
        'header_logo'                        => null,
        'timeout'                            => 5,
        'site_id'                            => null,
        'emit_notifications'                 => true,
        'emit_customer_success_notification' => true,
        'emit_customer_failure_notification' => true,
        'emit_customer_waiting_notification' => true,
        'embedVersion'                       => true,
    ];

    /** @var \Mobbex\Model\Cache */
    public static $cache;

    /** @var \Mobbex\Model\Db */
    public static $db;

    /** Hook execution callback */
    public static $hook;

    /** Log execution callback */
    public static $log;

    /** Only log if debug mode is enabled */
    const LOG_MODE_DEBUG = 'debug';

    /** Log and continue execution */
    const LOG_MODE_ERROR = 'error';

    /** Log and stop execution printing message */
    const LOG_MODE_FATAL = 'fatal';

    /**
     * Set current platform information.
     * 
     * @param string $name Name of current platform.
     * @param string $version Version of Mobbex plugin.
     * @param string $domain Domain name or URL of current site.
     * @param array $extensions Current extensions and their versions.
     * @param array $settings Plugin settings values.
     * @param callable $hook Hook execution callback. @see Description at \Mobbex\Platform::hook()
     * @param callable $log Log execution callback. @see Description at \Mobbex\Platform::log()
     */
    public static function init($name, $version, $domain, $extensions = [], $settings = [], $hook = null, $log = null)
    {
        self::$name       = $name;
        self::$version    = $version;
        self::$domain     = str_replace('www.', '', parse_url($domain, PHP_URL_HOST) ?: $domain);
        self::$extensions = $extensions;
        self::$settings   = array_merge(self::$settings, $settings);
        self::$hook       = $hook;
        self::$log        = $log;
    }

    /**
     * Load plugin models to sdk.
     * 
     * @param \Mobbex\Model\Cache|null $cache Optional Mobbex cache model. If not provided, a new Cache instance will be created.
     * @param object $db Model to manage the db connection.
     */
    public static function loadModels($cache = null, $db = null)
    {
        self::$cache = $cache ?: new \Mobbex\Model\Cache();
        self::$db    = $db    ?: new \Mobbex\Model\Db;
    }

    /**
     * Retrieve platform versions info formatted as array.
     * 
     * @return array 
     */
    public static function toArray()
    {
        return [
            'php'       => PHP_VERSION,
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

    /**
     * Log a message with the given data.
     * 
     * @param string $mode @see \Mobbex\Platform::LOG_MODE_*
     * @param string $message Main log message.
     * @param array $data Optional. All data related.
     */
    public static function log($mode, $message, $data = [])
    {
        if (is_callable(self::$log))
            call_user_func(self::$log, $mode, $message, $data);
    }
}