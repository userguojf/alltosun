<?php

/**
 * alltosun.com 启动文件 setup.php
 * ============================================================================
 * 版权所有 (C) 2009-2011 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 高竞竞 (gaojj@alltosun.com) $
 * $Date: 2010-12-28 下午03:06:56 $
 */

if (ANPOWER) {
    error_reporting(E_ALL);
    ini_set('display_errors','On');
} else {
    error_reporting(E_ALL & ~E_NOTICE);
    ini_set('display_errors','Off');
}

// 加载公用函数库 根据项目需要具体加载
require_once MODULE_CORE . '/helper/common.php';
require_once MODULE_CORE . '/helper/upload.php';
require_once MODULE_CORE . '/helper/Filter.php'; //filter没有
require_once MODULE_CORE . '/helper/smarty.php'; //smarty

require_once ROOT_PATH . '/helper/common.php';
require_once ROOT_PATH . '/helper/csv.php';
require_once ROOT_PATH . '/helper/AnDOMDocument.php';
require_once ROOT_PATH . '/helper/AnDES.php';
require_once ROOT_PATH . '/helper/des.php';
require_once ROOT_PATH . '/helper/aes.php';
require_once ROOT_PATH . '/helper/MongoDBPager.php';
require_once ROOT_PATH . '/helper/JiGuangPush.php';  //wangjf 引入极光推送类，2018-03-05


require_once ROOT_PATH . '/helper/AnLogger.php';
require_once ROOT_PATH . '/helper/MyLogger.php';
AnLogger::init($loggerConfig);

//require_once ROOT_PATH.'/helper/common.php';
//require_once MODULE_PATH.'/openapi/helper/AnWeiboV2.php';

// 用户级别错误
set_error_handler("an_error_handler");

// 系统级别错误处理
// register_shutdown_function("shutdown_an_error_handler");

// 加载Smarty
$view = new Smarty3();
$view->setTemplateDir(Config::get('template_dir'));
$view->setCompileDir(Config::get('compile_dir'));
if (D_BUG && Request::Get('smarty', 0)) {
    $view->setDebugging(true);
} elseif (ANPOWER) {
    $view->error_reporting = E_ALL & ~E_NOTICE;
}
Response::setView($view);

// 连接MC
// sae上的memcache链接有所不同
if (SAE) {
    $mc = memcache_init();
    $mc_wr = MemcacheWrapper::connect($mc);
} else {
    $mc_wr = MemcacheWrapper::connect('mc');
}

try {
    AnModule::loadAll();
    // Session @sae sae上只能使用 session_start()
    //
    session_set_cookie_params(time() + 3600 * 24, Config::get('cookie_path'), Config::get('cookie_domain'));
    session_cache_limiter("private, must-revalidate");
    Session::start($mc_wr);

    member_helper::remember_me();

    // 加载项目自有的Controller
    require_once ROOT_PATH . '/Controller.php';

    Controller::dispatch();


} catch (AnFormParseException $e) {
    AnMessage::show($e->getMessage());

} catch (AnMessageException $e) {
    AnMessage::show($e->getMessageExt());
} catch (AnException $e) {
    AnException::echoMsg($e);
} catch (Exception $e) {
    throw $e;
}

// 禁止浏览器缓存
header("Expires: 0");
if(!ONDEV){
    header('X-Frame-Options: deny');
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private", false);
}

header('Content-Type:text/html; charset=' . Config::get('head_meta_charset'));
header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
Response::flush();

