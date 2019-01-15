#!/usr/local/php/bin/php
<?php

/**
 * alltosun.com 更新亮屏设备的图片    update_screen_show_pic_info.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 申小宁 (shenxn@alltosun.com) $
 * $Date: 2017-9-12 上午9:25:52 $
 * $Id$
 */

if (php_sapi_name() != 'cli') {
    echo "This script can not be executed through web server.";
    exit(1);
}

// 命令行参数一，程序名
$script_filename = array_shift($_SERVER['argv']);

// 命令行参数二，显示帮助信息
if (in_array('--help', $_SERVER['argv'])) {
    echo <<<EOF

Execute cron script for AnCMS

Usage: /usr/local/php/bin/php {$script_filename} [options] <HTTP_HOST> <URL>
Example: /usr/local/php/bin/php {$script_filename} --ondev "cms.alltosun.net" admin/fix_data/staff_friend

   --ondev        Execute this script on develop environment, which will use development config.
   HTTP_HOST      The domain for AnCMS to run with.
   URL            The url for the script to visit, temporarily only for path1/pathX/controller/action

EOF;
    exit();
}

// 命令行参数二，定义运行于开发机，采用Config中开发机的相关定义
if (in_array('--ondev', $_SERVER['argv'])) {
    array_shift($_SERVER['argv']);
    define('ONDEV', true);
} else {
    define('ONDEV', false);
}

// 命令行参数三，定义运行网站使用的域名
$http_host = !empty($_SERVER['argv']) ? array_shift($_SERVER['argv']) : '';

// 默认设置
$log_file                   = '/data/logs/wifi_cron.log';           // 计划任务执行信息记录到的log文件默认地址
$log_email                  = 'shenxn@alltosun.com';                  // 定义计划任务发生错误时报告错误日志的默认邮箱
$_SERVER['HTTP_HOST']       = 'mac.pzclub.cn';                    // 运行网站使用的域名
$_SERVER['REMOTE_ADDR']     = '127.0.0.1';
$_SERVER['SERVER_SOFTWARE'] = 'PHP CLI';
$_SERVER['REQUEST_METHOD']  = 'GET';
$_SERVER['QUERY_STRING']    = '';
$_SERVER['PHP_SELF']        = $_SERVER['REQUEST_URI'] = '/cron.php';

// 如果命令行传入http_host参数的话，则采用命令行的http_host参数
if (!empty($http_host)) $_SERVER['HTTP_HOST'] = $http_host;

// @wangdk php5.5 提示mysql扩展即将被弃用的提示
error_reporting(E_ALL & ~E_DEPRECATED);
define('ANPHP_PATH', '/data/framework/tags/1.1.1');

// 初始化框架
require '/data/www/wifi/web/framework/tags/1096/init.php';

define('ROOT_PATH', substr(dirname(__FILE__), 0, -14));
$a = substr(dirname(__FILE__), 0, -14);
define('SCRIPT_PATH', ROOT_PATH);
define('DATA_PATH', SCRIPT_PATH.'/data');
define('MODULE_PATH', SCRIPT_PATH.'/module');
define('MODULE_CORE', MODULE_PATH.'/core');

require ROOT_PATH.'/config/config.php';
//require_once ROOT_PATH.'/setup.php';

require_once ROOT_PATH . "/vendor/autoload.php";

if (defined('CRON_QUEUE_SMS_LOG')) $log_file = RUN_LOG;
if (defined('SCRIPT_EMAIL')) $log_email = SCRIPT_EMAIL;
// 连接MC
$mc_wr = MemcacheWrapper::connect('mc');
// 加载公用函数库
require_once MODULE_CORE.'/helper/common.php';
require_once ROOT_PATH.'/helper/common.php';

$start_time = date('Y-m-d H:i:s');

$logs = array();
$exception = false;

try {
    // 加载所有模块
    AnModule::loadAll();

    // 离线统计
    _widget('screen_stat.offline_stat')->roll_poling_device();

   # 0 10-18 * * * /usr/local/php/bin/php /data/svn_data/open/trunk/web/public/live189/helper/script/lottery.php

} catch (Exception $e) {
    $logs[] = $e;
    $exception = true;
}

$end_time = date('Y-m-d H:i:s');

$log = $start_time.' - '.$end_time.' @'.$http_host.': '.implode('|', $logs)."\n";

// 自动生成log文件
if (!empty($log_file) && !file_exists($log_file)) {
    $dirname = dirname($log_file);
    if (!is_dir($dirname)) @mkdir($dirname);
    $log_header = "#Generated by AnSNS for cron script log. Any problems please connect {$log_email}\n";
    @file_put_contents($log_file, $log_header);
}

// 记录log
if (!empty($log_file) && is_readable($log_file)) {
    @error_log($log, 3, $log_file);
    // 如果发生报错信息时，同时进行email通知
    if ($exception) @error_log($log, 1, $log_email);
} else {
   // 如果log文件不可写，进行email通知
   $log = "Log file:'$log_file' is unreadable!\n$log";
   @error_log($log, 1, $log_email);
}
exit(1);
?>