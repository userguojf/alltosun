<?php

/**
 * alltosun.com  test_model_create.php
 * ============================================================================
 * 版权所有 (C) 2009-2011 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 宁海新 (ninghx@alltosun.com) $
 * $Date: 2011-4-11 上午09:57:56 $
*/

error_reporting(E_ALL);
define('ROOT_PATH',     substr(dirname(__FILE__), 0, -7));  // 定义网站根目录

require_once '/data/framework/trunk/init.php';
require_once './simpletest/autorun.php';

Config::set('db', array('mysql', 'localhost', 'alltosun_cms', 'f6TDpHURU8w2CcG6', 'alltosun_cms'));

Config::set('mc', array('127.0.0.1', 11211));

// 连接MC
$mc = new Memcache();
list($host, $port) = Config::get('mc');
$mc->connect($host, $port);
$mc_wr = new MemcacheWrapper($mc);

if (Request::Get("cache", 1) == 0) {
    define('CACHE', 0);
} else {
    define('CACHE', Request::Get("cache", 1));
}
define('D_BUG', 1);

echo "<pre>";

if (D_BUG) include('/data/framework/trunk/Debug.php');

class TestOfModel extends UnitTestCase
{
    public function testCreate()
    {
        $create_arr = array('tag_name'=>'ninghx', 'update_time'=>date("Y-m-d h:i:s"), 'num'=>microtime(true));
        $id = _model('test')->create($create_arr);

		$this->assertTrue($id == 1);
    }
}

?>