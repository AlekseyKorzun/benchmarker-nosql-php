<?php
/**
 * You must run `composer install` order to generate autoloader
 */
require dirname(__DIR__) . '/vendor/autoload.php';

use \Exception;
use \Benchmarker\Factory;

try {
    // Retrieve passed parameters
    $client = isset($_GET['client']) ? $_GET['client'] : false;
    $test = isset($_GET['test']) ? $_GET['test'] : false;

    // Handle multiple tests passed with : delimiter
    if ($test) {
        if (strpos($test, ':') !== false) {
            $strings = explode(':', $test);
            if ($strings) {
                $test = array();
                foreach ($strings as $string) {
                    $test[] = $string;
                }
            }
        }
    }

    // Initialize test scope and attempt to run requested tests
    $benchmarker = Factory::instance($client);
    $benchmarker->test($test);
} catch (Exception $exception) {
    // Watch for non-200 responses when doing AB test!
    header('HTTP/1.1 500 Internal Server Error');
    exit(1);
}
