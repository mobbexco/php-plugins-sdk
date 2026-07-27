<?php

namespace Mobbex\Integrity;

/**
 * Client for the integrity challenge endpoint.
 *
 * Uses its own cURL handle instead of \Mobbex\Api::request() for two reasons,
 * both of which would otherwise turn a degradation into a broken payment:
 *
 *   - Api::request() sets CURLOPT_TIMEOUT to 30. Thirty seconds stalled inside
 *     checkout creation is worse than the problem attestation solves.
 *   - Api::request() throws \Mobbex\Exception on any failure, and it is called
 *     from inside the header provider, where nothing may escape.
 *
 * There is also no recursion risk: this request does not go through
 * Api::request(), so it never triggers the header providers.
 *
 * Compatible with PHP 5.6.
 */
class Challenge
{
    /** Endpoint, relative to \Mobbex\Api::$apiUrl (which already ends in /p/). */
    const ENDPOINT = 'integrity/challenge';

    /** Hard budget for the round trip, in seconds. */
    const TIMEOUT = 3;

    /** Connection share of that budget, in seconds. */
    const CONNECT_TIMEOUT = 2;

    /**
     * Request a challenge for this installation.
     *
     * @param string $platform Repo name of the plugin, e.g. "prestashop".
     * @param string $version  Plugin version.
     *
     * @return array|null ['id', 'nonce', 'files', 'globalFiles'] or null on any failure.
     */
    public static function fetch($platform, $version)
    {
        if (!function_exists('curl_init'))
            return null;

        $settings    = \Mobbex\Platform::$settings;
        $apiKey      = isset($settings['api_key']) ? $settings['api_key'] : null;
        $accessToken = isset($settings['access_token']) ? $settings['access_token'] : null;

        if (empty($apiKey) || empty($accessToken))
            return null;

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => \Mobbex\Api::$apiUrl . self::ENDPOINT,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => self::TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_POSTFIELDS     => json_encode([
                'platform' => $platform,
                'version'  => $version,
            ]),
            CURLOPT_HTTPHEADER     => [
                'cache-control: no-cache',
                'content-type: application/json',
                'x-api-key: ' . $apiKey,
                'x-access-token: ' . $accessToken,
                'x-ecommerce-agent: ' . \Mobbex\Platform::toString(),
            ],
        ]);

        $response = curl_exec($curl);
        $status   = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // Required up to PHP 7.x, a no-op since 8.0 and deprecated from 8.5.
        if (PHP_VERSION_ID < 80000)
            curl_close($curl);

        if ($response === false || $status < 200 || $status >= 300)
            return null;

        return self::parse(json_decode($response, true));
    }

    /**
     * Normalize the challenge response.
     *
     * Accepts the payload bare or wrapped in the {result, data} envelope the
     * Mobbex API uses elsewhere, so a change on that side degrades instead of
     * silently attesting nothing.
     *
     * @param mixed $result Decoded response.
     *
     * @return array|null
     */
    protected static function parse($result)
    {
        if (!is_array($result))
            return null;

        if (isset($result['data']) && is_array($result['data']))
            $result = $result['data'];

        if (empty($result['id']) || empty($result['nonce']) || empty($result['spec']))
            return null;

        // Documented shape is {v, files}; tolerate a bare list of files.
        $files = isset($result['spec']['files']) ? $result['spec']['files'] : $result['spec'];

        // The global file list is mandatory: without it the protocol cannot be
        // completed, and a request carrying only the spec MAC reads to the
        // server as a broken plugin rather than as the degradation it is.
        $globalFiles = isset($result['global']['files']) ? $result['global']['files'] : null;

        if (!is_array($files) || !is_array($globalFiles))
            return null;

        // No bindings travel through the plugin, by design. The verification service ties each
        // challenge to an identifier Payments derives from the credentials; a
        // value the client controls would not bind anything.
        return [
            'id'          => $result['id'],
            'nonce'       => $result['nonce'],
            'files'       => $files,
            'globalFiles' => $globalFiles,
        ];
    }
}
