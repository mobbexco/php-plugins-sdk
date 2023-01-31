<?php

namespace Mobbex;

class Api
{
    public static $ready = false;

    /** Mobbex API base URL */
    public static $apiUrl = 'https://api.mobbex.com/p/';

    /** Commerce API Key */
    private static $apiKey;

    /** Commerce Access Token */
    private static $accessToken;

    /**
     * Set Mobbex credentails.
     * 
     * @param string $apiKey Commerce API Key.
     * @param string $accessToken Commerce Access Token.
     */
    public static function init($apiKey = null, $accessToken = null)
    {
        self::$apiKey      = $apiKey      ?: \Mobbex\Platform::$settings['api_key'];
        self::$accessToken = $accessToken ?: \Mobbex\Platform::$settings['access_token'];
        self::$ready       = self::$apiKey && self::$accessToken;
    }

    /**
     * Make a request to Mobbex API.
     * 
     * @param array $data 
     * 
     * @return mixed Result status or data if exists.
     * 
     * @throws \Mobbex\Exception
     */
    public static function request($data)
    {
        if (!self::$ready)
            return false;

        if (empty($data['method']) || empty($data['uri']))
            throw new \Mobbex\Exception('Mobbex request error: Missing arguments', 0, $data);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => self::$apiUrl . $data['uri'] . (!empty($data['params']) ? '?' . http_build_query($data['params']) : null),
            CURLOPT_HTTPHEADER     => self::getHeaders(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => $data['method'],
            CURLOPT_POSTFIELDS     => !empty($data['body']) ? json_encode($data['body']) : null,
        ]);

        $response = curl_exec($curl);

        // Throw curl errors before close session
        if (curl_error($curl))
            throw new \Mobbex\Exception('cURL error in Mobbex request: ' . curl_error($curl), curl_errno($curl), $data);

        curl_close($curl);

        $result = json_decode($response, true);

        // Throw request errors
        if (!$result)
            throw new \Mobbex\Exception('Mobbex request error: Invalid response format', 0, $data);

        if (!$result['result'])
            throw new \Mobbex\Exception('Mobbex request error #' . $result['code'] . ': ' . $result['error'], 0, $data);

        return isset($result['data']) ? $result['data'] : $result['result'];
    }

    /**
     * Get headers to connect with Mobbex API.
     * 
     * @return string[] 
     */
    private static function getHeaders()
    {
        return [
            'cache-control: no-cache',
            'content-type: application/json',
            'x-api-key: ' . self::$apiKey,
            'x-access-token: ' . self::$accessToken,
            'x-ecommerce-agent: ' . \Mobbex\Platform::toString(),
        ];
    }
}