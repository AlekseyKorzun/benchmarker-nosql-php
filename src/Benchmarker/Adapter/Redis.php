<?php
namespace Benchmarker\Adapter;

use \Benchmarker\Factory;

/**
 * Benchmarker for Redis based clients
 *
 * @author Aleksey Korzun <al.ko@webfoundation.net>
 * @link http://www.alekseykorzun.com
 */
class Redis extends Factory
{
    /**
     * Hash test
     */
    protected function test_hash()
    {
        if ($this->keys) {
            self::$client->del(self::KEY_HASH);

            self::$client->hmset(self::KEY_HASH, $this->keys);

            foreach ($this->keys as $key => $value) {
                if (self::$client->hexists(self::KEY_HASH, $key)) {
                    self::$client->hget(self::KEY_HASH, $key);
                }
            }
        }
    }

    /**
     * Multiple key test
     */
    protected function test_multiple_keys()
    {
        self::$client->mset($this->keys);
        self::$client->mget(array_keys($this->keys));
    }

    /**
     * Increase test
     */
    protected function test_increase()
    {
        self::$client->set(self::KEY_INCREASE, 1);
        self::$client->incr(self::KEY_INCREASE);
        self::$client->incr(self::KEY_INCREASE);
        self::$client->incr(self::KEY_INCREASE);
        self::$client->decr(self::KEY_INCREASE);
        self::$client->decr(self::KEY_INCREASE);
        self::$client->decr(self::KEY_INCREASE);
    }

    /**
     * List test
     */
    protected function test_list()
    {
        if ($this->strings) {
            self::$client->del(self::KEY_LIST);

            foreach ($this->strings as $string) {
                self::$client->rpush(self::KEY_LIST, $string);
                self::$client->lpush(self::KEY_LIST, $string);
                self::$client->lpop(self::KEY_LIST);
            }
        }
    }

    /**
     * Set test
     */
    protected function test_set()
    {
        if ($this->strings) {
            self::$client->del(self::KEY_SET);
            self::$client->del(self::KEY_SET_TWO);

            foreach ($this->strings as $string) {
                self::$client->sadd(self::KEY_SET, $string);

                if (self::$client->sismember(self::KEY_SET, $string)) {
                    self::$client->smove(self::KEY_SET, self::KEY_SET_TWO, $string);
                }

                self::$client->srandmember(self::KEY_SET_TWO);
            }
        }
    }
}
