<?php

namespace Mobbex\Tests;

class SubscriberTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp() : void
    {
        \Mobbex\Platform::init('tests', '1.0.0', $_ENV['SERVER_URL'], [], [
            'api_key'      => $_ENV['API_KEY'],
            'access_token' => $_ENV['ACESS_TOKEN'],
            'test'         => true,
            'embed'        => false,
        ]);
        \Mobbex\Api::init();
    }

    /**
     * @depends Mobbex\Tests\SubscriptionTest::testCanBeCreatedFromSimpleData
     */
    public function testCanBeCreatedFromSimpleData($subscription)
    {
        $subscriber = new \Mobbex\Modules\Subscriber(
            'php-plugins-sdk-test-' . rand(1, 1000000),
            null,
            $subscription->uid,
            date('Y-m-d H:i:s'),
            [
                'name'           => 'Test Name',
                'email'          => 'test@mobbex.com',
                'identification' => '12123123',
                'phone'          => '1122223333',
                'uid'            => 1,
            ]
        );

        $this->assertNotEmpty($subscriber->uid);
        $this->assertNotEmpty($subscriber->sourceUrl);
        $this->assertNotEmpty($subscriber->controlUrl);
    }
}