<?php

namespace Mobbex\Modules;

class Pos
{
    /** Platform unique identifier for payment */
    public $reference;

    /** Module configured options */
    public $settings = [];

    /** Array with all mobbex response data */
    public $response = [];

    /**
     * Constructor.
     * 
     * @param int|string $id Identifier to generate reference and relate mobbex with platform.
     * @param int|string $posId POS id.
     * @param int|string $total Amount to pay.
     * @param string $webhookUrl URL that recieve the Mobbex payment response.
     * @param string[] $sources .
     * @param string[] $installments Use +uid:<uid> to include and -<reference> to exclude.
     * @param array $customer {
     *     @type string $name
     *     @type string $email
     *     @type string $identification
     *     @type string|null $phone
     *     @type string|int|null $uid
     * }
     * @param string $hookName Name of hook to execute when body is filtered.
     * @param string $description Allow to modify the default operation description in the console.
     * @param string $reference for the checkout.
     */
    public function __construct(
        $id,
        $posId,
        $total,
        $webhookUrl,
        $sources = [],
        $installments = [],
        $customer = [],
        $hookName = 'mobbexPosRequest',
        $description = null,
        $reference = ''
    ) {
        $this->settings  = \Mobbex\Platform::$settings;
        $this->reference = $reference ?: \Mobbex\Repository::generateReference($id);

        $pos = \Mobbex\Api::request([
            'method' => 'GET',
            'uri'    => "pos/$posId",
        ]);

        $terminalPos = array_values(array_filter($pos['terminals'], function ($t) {
            return $t['subtype'] === 'smartpos';
        }))[0] ?? null;

        if(!$terminalPos)
            throw new \Mobbex\Exception("No physical terminals found for the UID given", 1, $pos);

        $posRef = $terminalPos['reference'];

        // Make request and set response data as properties
        $this->setResponse(\Mobbex\Api::request([
            'uri'    => "pos/$posRef/operation",
            'method' => 'POST',
            'body'   => \Mobbex\Platform::hook($hookName, true, [
                'reference'    => $this->reference,
                'intent'       => $this->settings['payment_mode'],
                'total'        => $total,
                'currency'     => 'ARS',
                'description'  => $description ?: "Pedido #$id",
                'test'         => (bool) $this->settings['test'],
                'webhook'      => $webhookUrl,
                'customer'     => $customer,
                'installments' => $installments,
                'sources'      => $sources,
                'timeout'      => (int) $this->settings['timeout'],
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
    }
}