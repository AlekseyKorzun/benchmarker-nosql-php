<?php
namespace Benchmarker\Adapter;

use \Benchmarker\Factory;

/**
 * Benchmarker for Memcached based clients
 *
 * @author Aleksey Korzun <al.ko@webfoundation.net>
 * @link http://www.alekseykorzun.com
 */
class Memcached extends Factory
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

    /**
     * Multiple key test
     */
    protected function test_multiple_keys()
    {
        self::$client->setMulti($this->keys);
        self::$client->getMulti(array_keys($this->keys));
    }

    /**
     * Append test
     */
    protected function test_append()
    {
        $client = self::$client;

        $client->setOption($client::OPT_COMPRESSION, false);

        parent::test_append();

        $client->setOption($client::OPT_COMPRESSION, true);
    }
}
