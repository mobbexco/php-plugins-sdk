<?php

namespace Mobbex\Tests;

class SubscriptionTest extends \PHPUnit\Framework\TestCase
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

    public function testCanBeCreatedFromSimpleData()
    {
        $subscription = new \Mobbex\Modules\Subscription(
            'php-plugins-sdk-test-' . rand(1, 1000000),
            null,
            'manual',
            $_ENV['SERVER_URL'] .'/return',
            $_ENV['SERVER_URL'] .'/webhook',
            100,
            'Subscription Test',
            'Subscription PHP Plugins SDK Test',
            '1m'
        );

        $this->assertNotEmpty($subscription->uid);
        $this->assertNotEmpty($subscription->url);
        $this->assertNotEmpty($subscription->shortUrl);

        return $subscription;
    }
}