<?php # redis config with minimal caching
$settings['container_yamls'][] = 'sites/all/modules/contrib/modules/redis/blackfly.services.yml';
$settings['redis.connection']['interface'] = 'PhpRedis'; // Can be "Predis".
$settings['redis.connection']['host']      = 'localhost';  // Your Redis instance hostname.
$settings['cache']['default'] = 'cache.backend.redis';
$conf['cache_class_cache'] = 'Redis_Cache';
