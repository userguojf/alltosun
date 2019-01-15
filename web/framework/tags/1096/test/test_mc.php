<?php

/**
 * alltosun.com 统一入口 index.php
 * ============================================================================
 * 版权所有 (C) 2007-2009 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 高竞竞 (gaojj@alltosun.com) $
 * $Date: 2010-3-7 下午06:40:44 $
*/
error_reporting(E_ALL);
define('ROOT_PATH',     substr(dirname(__FILE__), 0, -7));  // 定义网站根目录

require_once '/data/framework/trunk/init.php';
require_once './simpletest/autorun.php';

//require_once '../config/config.php';
//require_once '../helper/setup.php';

Config::set('mc', array('127.0.0.1', 11211));
// $a = Cache::connect('memcache', array('127.0.0.1', 11211)); // 连接 memcache2
//$a = Cache::connect('memcache', Config::get('mc')); // 连接 memcache2


// 连接MC
/*$mc = new Memcache();
list($host, $port) = Config::get('mc');
$mc->connect($host, $port);

$mc_wr = new MemcacheWrapper($mc);*/
$mc_wr = Cache::connect('memcache', array('127.0.0.1', 11211));

class TestOfModel extends UnitTestCase
{

    function testCacheIs()
	{
		global $mc_wr;
		$mc_wr->set('an', 'alltosun.com');
		$this->assertTrue('alltosun.com' == $mc_wr->get('an'));

		$mc_wr->set('an', 'alltosun.com', 0);
		$this->assertTrue('alltosun.com' == $mc_wr->get('an'));

		$mc_wr->delete('an');
		$this->assertFalse($mc_wr->get('an'));

		$mc_wr->flush();
		$mc_wr->PS('anhaha');
		$mc_wr->set('an', 'alltosun.com');
		$this->assertTrue('alltosun.com' == $mc_wr->get('an'));

		$mc_wr->flush();
		$mc_wr->PS('anhaha');
		$mc_wr->NS('anran');
		$mc_wr->set('an', 'alltosun.com');
		$mc_wr->set('zyj', 'alltosun.com');
		$this->assertTrue('alltosun.com' == $mc_wr->get('an'));

		$mc_wr->flush();
		$mc_wr->PS('anhaha');
		$mc_wr->NS('anran');
		$mc_wr->set('an', 'alltosun.com');
		$mc_wr->set('zyj', 'alltosun.com');
		$mc_wr->deleteNS();
		$this->assertFalse($mc_wr->get('an'));
		$this->assertFalse($mc_wr->get('zyj'));

		$mc_wr->flush();
		$mc_wr->PS('anhaha');
		$mc_wr->NS('anran');
		$mc_wr->set('an', 'alltosun.com');
		$mc_wr->set('zyj', 'alltosun.com');
		$mc_wr->deletePS();
		echo $mc_wr->get('an');
		$this->assertFalse($mc_wr->get('an'));
		$this->assertFalse($mc_wr->get('zyj'));

		var_dump($mc_wr);
    }

}
?>