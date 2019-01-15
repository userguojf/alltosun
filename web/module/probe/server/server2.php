<?php

if (PHP_SAPI !== 'cli')
{
    exit('error');
}

//$argv
define('DEBUG', 1);
define('ROOT_PATH', __DIR__);


require ROOT_PATH.'/config.php';
// require ROOT_PATH.'/lib/function.php';
// require ROOT_PATH.'/lib/DB.php';
// require ROOT_PATH.'/lib/RedisCache.php';

// $redis_cache = RedisCache::content();

require ROOT_PATH.'/src/server2.php';
// require ROOT_PATH.'/src/handle_data.php';
// require ROOT_PATH.'/src/helper.php';

// $action = $argv[1];

$config = array(
        'user' => 'www',
        'group' => 'www',
        'worker_num' => 8,
        'max_request' => 512,
        'dispatch_mode' => 1,
        'debug_mode'=> 1,
        'log_file' => '/data/log/swoole/access.log'
);

new Server($config);