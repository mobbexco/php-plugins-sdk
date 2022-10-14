<?php

namespace Mobbex\Modules;

class Subscription
{
    /** Platform identifier for subscription */
    public $reference;

    /** Module configured options */
    public $settings = [];

    /** Raw mobbex response data */
    public $response = [];

    /** Mobbex identifier for subscription */
    public $uid;

    /** URL to create subscriber */
    public $url;

    /** Short URL to create subscriber */
    public $shortUrl;

    /**
     * Constructor.
     * 
     * @param int|string $id Identifier to generate reference and relate mobbex with platform.
     * @param string $uid Id generated by mobbex.
     * @param string $type "manual" | "dynamic"
     * @param string $returnUrl Post-payment redirect URL.
     * @param string $webhookUrl URL that recieve the Mobbex payment response.
     * @param int|float $total Amount to charge.
     * @param string $name
     * @param string $description
     * @param string $interval Interval between executions.
     * @param int $limit Maximum number of executions.
     * @param int $freeTrial Number of free periods.
     * @param int|float $signupFee Different initial amount.
     * @param string $hookName Name of hook to execute when body is filtered.
     */
    public function __construct(
        $id,
        $uid,
        $type,
        $returnUrl,
        $webhookUrl,
        $total,
        $name,
        $description,
        $interval,
        $limit = 0,
        $freeTrial = 0,
        $signupFee = null,
        $hookName = 'mobbexSubscriptionRequest'
    ) {
        $this->settings = \Mobbex\Platform::$settings;

        // Make request and set response data as properties
        $this->setResponse(\Mobbex\Api::request([
            'uri'    => 'subscriptions/' . $uid,
            'method' => 'POST',
            'body'   => \Mobbex\Platform::hook($hookName, true, [
                'total'       => $total,
                'type'        => $type,
                'webhook'     => $webhookUrl,
                'return_url'  => $returnUrl,
                'currency'    => 'ARS',
                'reference'   => $this->reference = \Mobbex\Platform::$name . '_id:' . $id,
                'name'        => $name,
                'description' => $description,
                'limit'       => $limit,
                'setupFee'    => $signupFee,
                'interval'    => $interval,
                'trial'       => $freeTrial,
                'options'     => [
                    'embed'    => $this->settings['embed'],
                    'domain'   => \Mobbex\Platform::$domain,
                    'theme'    => [
                        'type'       => $this->settings['theme'],
                        'background' => $this->settings['background'],
                        'header'     => [
                            'name' => $this->settings['header_name'],
                            'logo' => $this->settings['header_logo'],
                        ],
                        'colors'     => [
                            'primary' => $this->settings['color'],
                        ]
                    ],
                    'platform' => \Mobbex\Platform::toArray(),
                    'redirect' => [
                        'success' => true,
                        'failure' => false,
                    ],
                ],
            ], $id),
        ]));
    }

    /**
     * Set response data as class properties.
     * 
     * @param array $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
        $this->uid      = isset($this->response['uid'])         ? $this->response['uid']         : null;
        $this->url      = isset($this->response['url'])         ? $this->response['url']         : null;
        $this->shortUrl = isset($this->response['shorten_url']) ? $this->response['shorten_url'] : [];
    }
}