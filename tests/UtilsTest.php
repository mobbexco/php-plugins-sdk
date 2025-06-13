<?php

namespace Mobbex\Tests;

class UtilsTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        date_default_timezone_set('UTC');
    }

    /**
     * @dataProvider validDateProvider
     */
    public function testValidDateToTime($date, $expectedTime)
    {
        $this->assertEquals(\Mobbex\dateToTime($date), $expectedTime);
    }

    /**
     * @dataProvider invalidDateProvider
     */
    public function testInvalidDateToTime($date)
    {
        $this->assertNull(\Mobbex\dateToTime($date));
    }

    public function validDateProvider(): array
    {
        return [
            ['2023-10-01 12:00:00',      1696161600000],
            ['2023-10-01 12:00:00.091',  1696161600000],
            ['2023-10-01T12:00:00.064Z', 1696161600000],
            ['2023-10-01',               1696118400000],
            ['March 10, 2022',           1646870400000],
        ];
    }

    public function invalidDateProvider(): array
    {
        return [
            [null],
            [false],
            [true],
            [''],
            ['not-a-date'],
            [123456],
            [[]],
            [new \stdClass()],
            ['0000-00-00 00:00:00']
        ];
    }
}