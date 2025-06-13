<?php

namespace Mobbex\Modules;

class Checkout
{
    /** Platform unique identifier for payment */
    public $reference;

    /** Module configured options */
    public $settings = [];

    /** Array with all mobbex response data */
    public $response = [];

    /** Mobbex unique identifier for payment */
    public $id;

    /** URL to go to pay */
    public $url;

    /** Available payment methods */
    public $methods;

    /** Cards saved from this customer */
    public $cards;

    /** Token to make payment through a transparent checkout */
    public $token;

    /**
     * Constructor.
     * 
     * @param int|string $id Identifier to generate reference and relate mobbex with platform.
     * @param int|string $total Amount to pay.
     * @param string $returnUrl Post-payment redirect URL.
     * @param string $webhookUrl URL that recieve the Mobbex payment response.
     * @param string $currency ISO 4217 currency code.
     * @param array $items {
     *     @type int|string $total Total amount to pay for this item.
     *     @type int $quantity Quantity of items. Does not modify the displayed total.
     *     @type string|null $description
     *     @type string|null $image Image URL to show in checkout.
     *     @type string|null $entity Entity configured to receive payment for this item.
     *     @type string|null $reference UID of related subscription. Use to enable subscription mode.
     * }
     * @param string[] $installments Use +uid:<uid> to include and -<reference> to exclude.
     * @param array $customer {
     *     @type string $name
     *     @type string $email
     *     @type string $identification
     *     @type string|null $phone
     *     @type string|int|null $uid
     *     @type string|null $createdAt Creation date of the customer (Unix timestamp in ms).
     * }
     * @param array $addresses [
     *  {
     *      @type string|null $type Address Type.
     *      @type string|null $country Country ISO 3166-1 alpha-3 code.
     *      @type string|null $state 
     *      @type string|null $city
     *      @type string|null $zipCode Postal|ZIP code.
     *      @type string|null $street
     *      @type string|null $streetNumber
     *      @type string|null $streetNotes
     *  }
     * ]
     * @param string $webhooksType Type of webhooks to send. Can be "all" | "none" | "final" | "intermediateAndFinal"
     * @param string $hookName Name of hook to execute when body is filtered.
     * @param string $description Allow to modify the default operation description in the console.
     * @param string $description Allow to modify the default operation description in the console.
     * @param string $fromCurrency Currency code of the actual total.
     * @param string $reference for the checkout.
     */
    public function __construct(
        $id,
        $total,
        $returnUrl,
        $webhookUrl,
        $currency,
        $items = [],
        $installments = [],
        $customer = [],
        $addresses = [],
        $webhooksType = 'all',
        $hookName = 'mobbexCheckoutRequest',
        $description = null,
        $reference = ''
    ) {
        $this->settings  = \Mobbex\Platform::$settings;
        $this->reference = $reference ?: self::generateReference($id);

        foreach ($items as &$item) {
            // Set subscription type if corresponds and update total
            if (isset($item['reference'])) {
                $item['type'] = 'subscription';

                if (isset($item['total']))
                    $total -= $item['total'];
            }
            // Forces float type to item total
            $item['total'] = isset($item['total']) ? (float) $item['total'] : null;

            // Get merchants from items
            if (isset($item['entity']))
                $merchants[] = ['uid' => $item['entity']];
        }

        // Make request and set response data as properties
        $this->setResponse(\Mobbex\Api::request([
            'uri'    => 'checkout',
            'method' => 'POST',
            'body'   => \Mobbex\Platform::hook($hookName, true, [
                'total'          => $this->settings['final_currency'] ? \Mobbex\Repository::convertCurrency($total, $currency, $this->settings['final_currency']) : $total,
                'webhook'        => $webhookUrl,
                'return_url'     => $returnUrl,
                'reference'      => $this->reference,
                'description'    => $description ?: "Pedido #$id",
                'intent'         => $this->settings['payment_mode'],
                'test'           => (bool) $this->settings['test'],
                'multicard'      => (bool) $this->settings['multicard'],
                'multivendor'    => $this->settings['multivendor'],
                'wallet'         => (bool) $this->settings['wallet'] && isset($customer['uid']),
                'timeout'        => (int) $this->settings['timeout'],
                'items'          => $items,
                'merchants'      => isset($merchants) ? $merchants : [],
                'installments'   => $installments,
                'customer'       => $customer,
                'addresses'      => $addresses,
                'webhooksType'   => $webhooksType,
                'currency'       => $this->settings['final_currency'] ?: $currency,
                'paymentMethods' => (bool) $this->settings['payment_methods'],
                'options'        => [
                    'embed'                           => (bool) $this->settings['embed'],
                    'embedVersion'                    => $this->settings['embed_version'],
                    'domain'                          => \Mobbex\Platform::$domain,
                    'platform'                        => \Mobbex\Platform::toArray(),
                    'emitNotifications'               => (bool) $this->settings['emit_notifications'],
                    'emitCustomerSuccessNotification' => (bool) $this->settings['emit_customer_success_notification'],
                    'emitCustomerFailureNotification' => (bool) $this->settings['emit_customer_failure_notification'],
                    'emitCustomerWaitingNotification' => (bool) $this->settings['emit_customer_waiting_notification'],
                    'closeOrReturnTimeout'            => $this->settings['return_timeout'],
                    'showNoInterestLabels'            => (bool) $this->settings['show_no_interest_labels'],
                    'redirect'                        => [
                        'success' => true,
                        'failure' => false,
                    ],
                    'theme'                           => [
                        'type'       => $this->settings['theme'],
                        'background' => $this->settings['background'],
                        'colors'     => [
                            'primary' => $this->settings['color'],
                        ],
                        'header'     => [
                            'name' => $this->settings['header_name'],
                            'logo' => $this->settings['header_logo'],
                        ],
                    ],
                ],
            ], $id)
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
        $this->id       = isset($this->response['id'])              ? $this->response['id']              : null;
        $this->url      = isset($this->response['url'])             ? $this->response['url']             : null;
        $this->methods  = isset($this->response['paymentMethods'])  ? $this->response['paymentMethods']  : [];
        $this->cards    = isset($this->response['wallet'])          ? $this->response['wallet']          : [];
        $this->token    = isset($this->response['intent']['token']) ? $this->response['intent']['token'] : null;
    }

    /**
     * Generate a reference.
     * 
     * @param string|int $id Unique ID of the instance that will be related to the checkout.
     */
    public static function generateReference($id)
    {
        $reference = [
            \Mobbex\Platform::$name . '_id:' . $id,
        ];

        // Add site id
        if (!empty(\Mobbex\Platform::$settings['site_id']))
            $reference[] = 'site_id:' . str_replace(' ', '-', trim(\Mobbex\Platform::$settings['site_id']));

        // Add reseller id
        if (!empty(\Mobbex\Platform::$settings['reseller_id']))
            $reference[] = 'reseller:' . str_replace(' ', '-', trim(\Mobbex\Platform::$settings['reseller_id']));

        return implode('_', $reference);
    }
}