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
     * @param string[] $installments Use +uid:<uid> to include and -<reference> to exclude.
     * @param array $items {
     *     @type int|string $total Total amount to pay for this item.
     *     @type int $quantity Quantity of items. Does not modify the displayed total.
     *     @type string|null $description
     *     @type string|null $image Image URL to show in checkout.
     *     @type string|null $entity Entity configured to receive payment for this item.
     *     @type string|null $reference UID of related subscription. Use to enable subscription mode.
     * }
     * @param array $customer {
     *     @type string $name
     *     @type string $email
     *     @type string $identification
     *     @type string|null $phone
     *     @type string|int|null $uid
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
     * @param string $hookName Name of hook to execute when body is filtered.
     */
    public function __construct(
        $id,
        $total,
        $returnUrl,
        $webhookUrl,
        $items = [],
        $installments = [],
        $customer = [],
        $addresses = [],
        $hookName = 'mobbexCheckoutRequest'
    ) {
        $this->settings = \Mobbex\Platform::$settings;

        foreach ($items as &$item) {
            // Set subscription type if corresponds
            if (isset($item['reference']))
                $item['type'] = 'subscription';

            // Get merchants from items
            if (isset($item['entity']))
                $merchants[] = ['uid' => $item['entity']];
        }

        // Make request and set response data as properties
        $this->setResponse(\Mobbex\Api::request([
            'uri'    => 'checkout',
            'method' => 'POST',
            'body'   => \Mobbex\Platform::hook($hookName, true, [
                'total'        => $total,
                'webhook'      => $webhookUrl,
                'return_url'   => $returnUrl,
                'reference'    => $this->reference = $this->generateReference($id),
                'description'  => 'Pedido #' . $id,
                'intent'       => $this->settings['payment_mode'],
                'test'         => (bool) $this->settings['test'],
                'multicard'    => (bool) $this->settings['multicard'],
                'multivendor'  => $this->settings['multivendor'],
                'wallet'       => (bool) $this->settings['wallet'] && isset($customer['uid']),
                'timeout'      => isset($this->settings['timeout']) ? (int) $this->settings['timeout'] : 5,
                'items'        => $items,
                'merchants'    => isset($merchants) ? $merchants : [],
                'installments' => $installments,
                'customer'     => $customer,
                'addresses'    => $addresses,
                'options'      => [
                    'embed'    => (bool) $this->settings['embed'],
                    'domain'   => \Mobbex\Platform::$domain,
                    'platform' => \Mobbex\Platform::toArray(),
                    'redirect' => [
                        'success' => true,
                        'failure' => false,
                    ],
                    'theme'    => [
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
    public function generateReference($id)
    {
        $reference = [
            \Mobbex\Platform::$name . '_id:' . $id,
        ];

        // Add reseller id
        if (!empty($this->settings['reseller_id']))
            $reference[] = 'reseller:' . str_replace(' ', '-', trim($this->settings['reseller_id']));

        return implode('_', $reference);
    }
}