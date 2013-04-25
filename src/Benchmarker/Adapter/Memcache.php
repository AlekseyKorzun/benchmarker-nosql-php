<?php
namespace Benchmarker\Adapter;

use \Benchmarker\Factory;

/**
 * Benchmarker for Memcache based clients
 *
 * @author Aleksey Korzun <al.ko@webfoundation.net>
 * @link http://www.alekseykorzun.com
 */
class Memcache extends Factory
{
    /**
     * Increase test
     */
    protected function test_increase()
    {
        self::$client->set(self::KEY_INCREASE, 1);
        self::$client->increment(self::KEY_INCREASE);
        self::$client->increment(self::KEY_INCREASE);
        self::$client->increment(self::KEY_INCREASE);
        self::$client->decrement(self::KEY_INCREASE);
        self::$client->decrement(self::KEY_INCREASE);
        self::$client->decrement(self::KEY_INCREASE);
    }
}
