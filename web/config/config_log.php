<?php

//require_once ROOT_PATH . '/helper/AnQConf.php';


/**
 * elk 安装/依赖 说明
 *
 * - 360's qconf (配置管理,如果不用,可把配置写在php文件中)
 * - 安装monolog php第三方库
 *
 * - 配置elk: 包括 redis + elk套件
 */

if (!defined('DATA_PATH')) {
    define('DATA_PATH', __DIR__ . '/../data');
}

if (!defined('PROJECT_NS')) {
    define('PROJECT_NS', '201711awifiprobe');
}

//配置 Logger
$loggerConfig = array(
    'redisHandler' => array(
        //'host'         => ONDEV ? '192.168.2.21' : '',
        //'port'         => ONDEV ? '6381' : 6381,
        'host'         => ONDEV ? '192.168.2.21' : '192.168.1.21',
        'port'         => ONDEV ? 6381 : 6381,
        'pwd'          => ONDEV ? 'qPvJNf3fbX1GnhPr77ZQDkssxqiVUD5gnrm7' : '',
        'db'           => 0,

        //REDIS的key，List类型。logstash 会从这个list中获取日志信息
        'redis_key'    => strtoupper('logstash-phplogs'),

        //定义type，会记录为logstash的@type字段。同时会用作Elastic的Index名的一部分。完整定义见logstash的配置
        'elastic_type' => strtolower(PROJECT_NS),
    ),

    'log_base' => DATA_PATH . '/log',
    'log_ns'   => PROJECT_NS, //日志命名空间
);

/*
if (class_exists('AnLogger')) {
    AnLogger::init($loggerConfig);
}
*/


/**
 * slog 安装/依赖 说明
 * https://github.com/luofei614/SocketLog
 *
 * - 程序员安装 chrome插件
 *
 * - 服务器安装npm, 并运行socketlog-server
 *
 * - 程序中包含socketLog的PHP库文件
 */

/*
$slogConfig = array(
    'enable' => true,//是否打印日志的开关
    //'host'   => 'slog.anphp.com',//slog服务器地址，默认localhost
    'host'   => '192.168.2.231',    //slog服务器地址，默认localhost
    //'port'                => '1116', //内部通讯的端口 。程序写死了。外部端口是1229

    'optimize'            => true,//是否显示利于优化的参数，如果运行时间，消耗内存等，默认为false
    'show_included_files' => false,//是否显示本次程序运行加载了哪些文件，默认为false
    'error_handler'       => true,//是否接管程序错误，将程序错误显示在console中，默认为false
    'force_client_id'     => '',//日志强制记录到配置的client_id,默认为空
    'allow_client_ids'    => array( ////限制允许读取日志的client_id，默认为空,表示所有人都可以获得日志。
        'liudh_lC4aNm',
        'gaoz_lC4aNm',
        'zhaoxy_lC4aNm',
        'weihw_lC4aNm',
    )
);
*/


