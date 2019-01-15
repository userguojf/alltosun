<?php

if (PHP_SAPI !== 'cli') {
    exit('error');
}

// 命令行参数二，定义运行于开发机，采用Config中开发机的相关定义
if (in_array('--ondev', $_SERVER['argv'])) {
    define('ONDEV', true);
} else {
    define('ONDEV', false);
}


//$argv
define('DEBUG', 1);
define('ROOT_PATH', __DIR__);


require __DIR__ . '/../../../helper/setup_cli.php';

require ROOT_PATH . '/config.php';
require ROOT_PATH . '/lib/DB.php';
require ROOT_PATH . '/lib/RedisCache.php';
require ROOT_PATH . '/lib/AutoLoad.php';
require ROOT_PATH . '/lib/BaseHandle.php';
require ROOT_PATH . '/src/server.php';
require ROOT_PATH . '/core/route.php';
require ROOT_PATH . '/common/function.php';

// $action = $argv[1];

$date = date('Ymd');
$config = array(
    'user'                     => 'www',
    'group'                    => 'www',
    'worker_num'               => 32,
    'daemonize'                => 1,
    'max_request'              => 1024,
    'dispatch_mode'            => 2,
    'debug_mode'               => 1,
    'log_file'                 => '/data/log/swoole/access_all_' . $date . '.log',
    'heartbeat_check_interval' => 10,
    'heartbeat_idle_time'      => 60
);

//连接redis
$redis_cache = RedisCache::content();

new Server($config);