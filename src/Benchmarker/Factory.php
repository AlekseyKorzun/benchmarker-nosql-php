<?php
namespace Benchmarker;

use \ArrayObject;
use \Exception;
use \Redis;
use \Memcache;
use \Memcached;
use \Predis\Client as Predis;
use \Benchmarker\Adapter\Memcache as Adapter_Memcache;
use \Benchmarker\Adapter\Memcached as Adapter_Memcached;
use \Benchmarker\Adapter\Redis as Adapter_Redis;

/**
 * Benchmarker
 *
 * @author Aleksey Korzun <al.ko@webfoundation.net>
 * @link http://www.alekseykorzun.com
 */
abstract class Factory
{
    /**
     * Redis client identifier
     *
     * @var string
     */
    const CLIENT_REDIS = 'redis';

    /**
     * Predis client identifier
     *
     * @var string
     */
    const CLIENT_PREDIS = 'predis';

    /**
     * Memcached client identifier
     *
     * @var string
     */
    const CLIENT_MEMCACHED = 'memcached';

    /**
     * Memcache client identifier
     *
     * @var string
     */
    const CLIENT_MEMCACHE = 'memcache';

    /**
     * PHP serializer
     *
     * @var string
     */
    const SERIALIZER_PHP = 'php';

    /**
     * Igbinary serializer
     *
     * @var string
     */
    const SERIALIZER_IGBINARY = 'igbinary';

    /**
     * Key space for increase test
     *
     * @var string
     */
    const KEY_INCREASE = 'increase';

    /**
     * Key space for append test
     *
     * @var string
     */
    const KEY_APPEND = 'append';

    /**
     * Key space for list test
     *
     * @var string
     */
    const KEY_LIST = 'list';

    /**
     * Key space for hash test
     *
     * @var string
     */
    const KEY_HASH = 'hash';

    /**
     * Key set
     *
     * @var string
     */
    const KEY_SET = 'set';

    /**
     * Key for set number two
     *
     * @var string
     */
    const KEY_SET_TWO = 'settwo';

    /**
     * Key value array used for testing operations
     *
     * @var string[]
     */
    protected $keys = array(
        'key:1' => 'value:1',
        'key:2' => 'value:2',
        'key:3' => 'value:3',
        'key:4' => 'value:4'
    );

    /**
     * String array used for testing operations
     *
     * @var string[]
     */
    protected $strings = array(
        'string:1',
        'string:2',
        'string:3',
        'string:4'
    );

    /**
     * Instance of a client
     *
     * @var object
     */
    protected static $client;

    /**
     * Current serializer (used with object based tests)
     *
     * @var string
     */
    protected static $serializer;

    /**
     * Get instance of client benchmarker
     *
     * @throws Exception
     * @param string $client
     * @param string $serializer
     * @return Benchmarker_Memcache|Benchmarker_Memcached|Benchmarker_Redis
     */
    public static function instance($client, $serializer = self::SERIALIZER_PHP)
    {
        switch ($serializer) {
            case self::SERIALIZER_IGBINARY:
            case self::SERIALIZER_PHP:
                self::$serializer = $serializer;
                break;
            default:
                throw new Exception(
                    'Serializer you passed is currently not supported.'
                );
        }

        switch ($client) {
            case self::CLIENT_PREDIS:
                self::$client = new Predis();
                return new Adapter_Redis();
            case self::CLIENT_REDIS:
                self::$client = new Redis();
                self::$client->connect('127.0.0.1');
                return new Adapter_Redis();
            case self::CLIENT_MEMCACHED:
                self::$client = new Memcached();
                self::$client->addServer('127.0.0.1', 11211);
                return new Adapter_Memcached();
            case self::CLIENT_MEMCACHE:
                self::$client = new Memcache();
                self::$client->addServer('127.0.0.1', 11211);
                return new Adapter_Memcache();
        }

        throw new Exception(
            'Client you passed is currently not supported.'
        );
    }

    /**
     * Serialize passed data
     *
     * @param object $data
     * @return string
     */
    protected function serialize($data)
    {
        if (is_object($data)) {
            if (self::$serializer == self::SERIALIZER_IGBINARY) {
                return igbinary_serialize($data);
            }

            return serialize($data);
        }

        return $data;
    }

    /**
     * Unserialize data
     *
     * @param string $data
     * @return object
     */
    protected function unserialize($data)
    {
        if (is_string($data)) {
            if (self::$serializer == self::SERIALIZER_IGBINARY) {
                return igbinary_unserialize($data);
            }

            return unserialize($data);
        }

        return $data;
    }

    /**
     * Key test with larger objects
     */
    protected function test_keys_large()
    {
        if ($this->keys) {
            // Create 'large' object
            $object = new ArrayObject();

            while ($object->count() < 15) {
                $object->append($object->getArrayCopy());
            }

            foreach (array_keys($this->keys) as $key) {
                self::$client->set($key, $this->serialize($object));
            }

            $this->unserialize(self::$client->get($key));
        }
    }

    /**
     * Key test
     */
    protected function test_keys()
    {
        if ($this->keys) {
            foreach ($this->keys as $key => $value) {
                self::$client->set($key, $value);
                self::$client->get($key);
            }
        }
    }

    /**
     * Append test
     */
    protected function test_append()
    {
        if ($this->strings) {
            self::$client->set(self::KEY_APPEND, array_pop($this->strings));
            foreach ($this->strings as $string) {
                self::$client->append(self::KEY_APPEND, $string);
            }
        }
    }

    /**
     * Run a specific number of tests
     *
     * @param string[]|string $name
     */
    public function test($names)
    {
        $names = (array)$names;

        if ($names) {
            foreach ($names as $name) {
                $method = 'test_' . $name;
                if (method_exists($this, $method)) {
                    $this->$method();
                    continue;
                }

                // Fail all of the tests if we are attempting to test something
                // that does not exist
                throw new Exception(
                    'One or more of requested tests does not exists'
                );
            }
        }
    }

    /**
     * Execute all of the test methods for current profile
     */
    public function all()
    {
        $methods = get_class_methods($this);
        if ($methods) {
            foreach ($methods as $method) {
                if (strpos($method, 'test_') !== false) {
                    $this->$method();
                }
            }
        }
    }
}
