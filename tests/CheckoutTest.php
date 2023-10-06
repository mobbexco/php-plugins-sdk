<?php

namespace Mobbex\Tests;

class CheckoutTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp() : void
    {
        \Mobbex\Platform::init('tests', '1.0.0', '1.1.0', $_ENV['SERVER_URL'], [], [
            'api_key'      => $_ENV['API_KEY'],
            'access_token' => $_ENV['ACESS_TOKEN'],
            'test'         => true,
            'embed'        => false,
        ]);
        \Mobbex\Api::init();
    }

    public function testCanBeCreatedFromSimpleData()
    {
        $checkout = new \Mobbex\Modules\Checkout(
            rand(1, 10000),
            100,
            $_ENV['SERVER_URL'] .'/return',
            $_ENV['SERVER_URL'] .'/webhook',
            [],
            [
                'ahora_3'
            ],
            [
                'name'           => 'Test Name',
                'email'          => 'test@mobbex.com',
                'identification' => '12123123',
                'phone'          => '1122223333',
                'uid'            => 1,
            ]
        );

        $this->assertNotEmpty($checkout->id);
        $this->assertNotEmpty($checkout->url);
    }

    public function testCanBeCreatedUsingItems()
    {
        $checkout = new \Mobbex\Modules\Checkout(
            rand(1, 10000),
            100,
            $_ENV['SERVER_URL'] .'/return',
            $_ENV['SERVER_URL'] .'/webhook',
            [
                [
                    'total'       => 100,
                    'quantity'    => 2,
                    'description' => 'Item 1',
                    'image'       => $_ENV['SERVER_URL'] . '/img/items/1.jpg',
                ],
            ],
            [
                'ahora_3'
            ],
            [
                'name'           => 'Test Name',
                'email'          => 'test@mobbex.com',
                'identification' => '12123123',
                'phone'          => '1122223333',
                'uid'            => 1,
            ]
        );

        $this->assertNotEmpty($checkout->id);
        $this->assertNotEmpty($checkout->url);
    }

    public function testCanBeCreatedUsingMultivendor()
    {
        $checkout = new \Mobbex\Modules\Checkout(
            rand(1, 10000),
            100,
            $_ENV['SERVER_URL'] .'/return',
            $_ENV['SERVER_URL'] .'/webhook',
            [
                [
                    'total'       => 50,
                    'quantity'    => 2,
                    'description' => 'Item 1',
                    'image'       => $_ENV['SERVER_URL'] . '/img/items/1.jpg',
                ],
                [
                    'total'       => 50,
                    'quantity'    => 4,
                    'description' => 'Item 2 (with merchant)',
                    'image'       => $_ENV['SERVER_URL'] . '/img/items/2.jpg',
                    'entity'      => $_ENV['MERCHANT_UID']
                ],
            ],
            [
                'ahora_3'
            ],
            [
                'name'           => 'Test Name',
                'email'          => 'test@mobbex.com',
                'identification' => '12123123',
                'phone'          => '1122223333',
                'uid'            => 1,
            ]
        );

        $this->assertNotEmpty($checkout->id);
        $this->assertNotEmpty($checkout->url);
    }

    /**
     * @depends Mobbex\Tests\SubscriptionTest::testCanBeCreatedFromSimpleData
     */
    public function testCanBeCreatedUsingSubscriptionItems($subscription)
    {
        $checkout = new \Mobbex\Modules\Checkout(
            rand(1, 10000),
            100,
            $_ENV['SERVER_URL'] .'/return',
            $_ENV['SERVER_URL'] .'/webhook',
            [
                [
                    'total'       => 50,
                    'quantity'    => 2,
                    'description' => 'Item 1',
                    'image'       => $_ENV['SERVER_URL'] . '/img/items/1.jpg',
                ],
                [
                    'total'       => 50,
                    'quantity'    => 1,
                    'description' => 'Item 2 (with subscription)',
                    'image'       => $_ENV['SERVER_URL'] . '/img/items/2.jpg',
                    'reference'   => $subscription->uid,
                ],
            ],
            [
                'ahora_3'
            ],
            [
                'name'           => 'Test Name',
                'email'          => 'test@mobbex.com',
                'identification' => '12123123',
                'phone'          => '1122223333',
                'uid'            => 1,
            ]
        );

        $this->assertNotEmpty($checkout->id);
        $this->assertNotEmpty($checkout->url);
    }
}