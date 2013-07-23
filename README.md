Benchmarker NoSQL (v1.0.0)
==========================

Benchmarking 'framework' used to analyze performance of PHP based NoSQL clients such as
memcache, memcached, predis and redis.

Written to support the following blog post:

http://alekseykorzun.com/post/53283070010/benchmarking-memcached-and-redis-clients

Installation
-----

If you have your own autoloader, simply update namespaces and drop the files
into your frameworks library.

For people that do not have that setup, you can visit http://getcomposer.org to install
composer on your system. After installation simply run `composer install` in parent
directory of this distribution to generate vendor/ directory with a cross system autoloader.
