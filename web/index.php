<?php
/**
 * alltosun.com 入口控制器 index.php
 * ============================================================================
 * 版权所有 (C) 2009-2011 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 李微微 $
 * $Date: 2012-7-25 下午04:28:23 $
 * $Id: index.php 77342 2012-07-26 04:34:08Z duwh $
 */
/**
 * 网站根目录
 */
define('ROOT_PATH', dirname(__FILE__));

/*
 * 程序根目录
 */
define('SCRIPT_PATH', ROOT_PATH);

/**
 * 定义是否在开发服务器上
 */
defined('ONDEV') || define('ONDEV', stripos($_SERVER['HTTP_HOST'], 'alltosun.net'));

/**
 * 模块根目录
 */
define('MODULE_PATH', SCRIPT_PATH . '/module');
/**
 * 核心模块根目录
 */
define('MODULE_CORE', MODULE_PATH . '/core');
/**
 * 网站产生的数据目录
 */
define('DATA_PATH', SCRIPT_PATH . '/data');

/**
 * 定义是否将程序放在sae上
 */
define('SAE', stripos($_SERVER['HTTP_HOST'], 'sinaapp.com'));

/**
 * AnPHP框架根目录
 *
 */
define('ANPHP_PATH', __DIR__ . '/framework/tags/1096');

require_once ANPHP_PATH . '/init.php';

require_once ROOT_PATH . '/config/config.php';

require_once ROOT_PATH . "/vendor/autoload.php";

require_once ROOT_PATH . '/helper/setup.php';

if (D_BUG) {
    include_once ANPHP_PATH . '/Debug.php';
    if (class_exists('AnDebug'))
        AnDebug::echoMsg();
}