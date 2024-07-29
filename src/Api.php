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

        //Log request data
        \Mobbex\Platform::log('debug', 'Api > Request | Request Data:', $data);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER     => self::getHeaders(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => $data['method'],
            CURLOPT_POSTFIELDS     => !empty($data['body']) ? json_encode($data['body']) : null,
            CURLOPT_URL            => implode([
                !empty($data['url']) ? $data['url'] : self::$apiUrl,
                $data['uri'],
                !empty($data['params']) ? '?' . http_build_query($data['params']) : ''
            ])
        ]);

        $response    = curl_exec($curl);
        $error       = curl_error($curl);
        $errorNumber = curl_errno($curl);

        curl_close($curl);

        // Throw curl errors
        if ($error)
            throw new \Mobbex\Exception('Curl error in Mobbex request #:' . $error, $errorNumber, $data);

        $result = json_decode($response, true);

        // Throw request errors
        if (!$result)
            throw new \Mobbex\Exception('Mobbex request error: Invalid response format', 0, $data);

        if (!$result['result'])
            throw new \Mobbex\Exception(sprintf(
                'Mobbex request error #%s: %s %s',
                isset($result['code']) ? $result['code'] : 'NOCODE',
                isset($result['error']) ? $result['error'] : 'NOERROR',
                isset($result['status_message']) ? $result['status_message'] : 'NOMESSAGE'
            ), 0, $data);

        // Return raw response if requested
        if (!empty($data['raw']))
            return $result;

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
