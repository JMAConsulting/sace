<?php # redis config with reasonable default caching
$settings['redis.connection']['interface'] = 'PhpRedis'; 
$settings['redis.connection']['host']      = 'vredis';
$settings['cache']['default'] = 'cache.backend.redis';
$settings['container_yamls'][] = 'sites/all/modules/contrib/redis/blackfly.services.yml';
$settings['container_yamls'][] = 'sites/all/modules/minimal/redis/blackfly.services.yml';
#$conf['cache_class_cache'] = 'Redis_Cache';
#$conf['cache_class_cache_bootstrap'] = 'Redis_Cache';
#$conf['cache_class_cache_menu'] = 'Redis_Cache';
#$conf['cache_class_cache_variable'] = 'Redis_Cache';
