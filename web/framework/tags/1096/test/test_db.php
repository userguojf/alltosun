<?php

/**
 * 数据库测试
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

Config::set('db0', array('mysql', 'localhost', 'test_test.com', 'AxAbbZDQDLJJBhcB', 'test_test.com'));
Config::set('db2', array(array('mysql', 'localhost', 'test_test.com', 'AxAbbZDQDLJJBhcB', 'test_test.com'), array('mysql', 'localhost', 'alltosun_cms', 'f6TDpHURU8w2CcG6', 'alltosun_cms')));
Config::set('db21', array(array('mysql', 'localhost', 'test_test.com', 'AxAbbZDQDLJJBhcB', 'test_test.com'), array('mysql', 'localhost', 'alltosun_cms', 'f6TDpHURU8w2CcG6', 'alltosun_cms'), array('mysqli', 'localhost', 'alltosun_cms', 'f6TDpHURU8w2CcG6', 'alltosun_cms')));
Config::set('db3', array('mysql', 'localhost', 'alltosun_cms', 'f6TDpHURU8w2CcG6', 'alltosun_cms'));
Config::set('db31', array(array('mysql', 'localhost', 'alltosun_cms', 'f6TDpHURU8w2CcG6', 'alltosun_cms'), array('mysql', 'localhost', 'anr', 'WJj2J6JFwYaydG5C', 'alltosun_cms')));
Config::set('db', array('mysql', 'localhost', 'alltosun_cms', 'f6TDpHURU8w2CcG6', 'alltosun_cms'));
Config::set('db4', array('db'));
// Config::set('db', array(array('mysql', 'localhost', 'alltosun_cms', 'f6TDpHURU8w2CcG6', 'alltosun_cms'), array('mysqli', 'localhost', 'alltosun_cms', 'f6TDpHURU8w2CcG6', 'alltosun_cms')));
Config::set('mc', array('127.0.0.1', 11211));
// $a = Cache::connect('memcache', array('127.0.0.1', 11211)); // 连接 memcache2
//$a = Cache::connect('memcache', Config::get('mc')); // 连接 memcache2


// 连接MC
/*$mc = new Memcache();
list($host, $port) = Config::get('mc');
$mc->connect($host, $port);

$mc_wr = new MemcacheWrapper($mc);*/
$mc_wr = Cache::connect('memcache', array('127.0.0.1', 11211));

echo "<pre>";

class TestOfModel extends UnitTestCase
{
    function testDB()
    {
    	// 测试数据库读写分离
		// $db = DB::connect(array('mysql', '10.219.8.11', 'yagm', 'jjaYnUwTJWPJwT8f', 'yagm'), array('mysqli', '10.219.8.11', 'yagm', 'jjaYnUwTJWPJwT8f', 'yagm'));
		$db = DB::connect(Config::get('db31'));
		$db->db_select = 's';
		debug_zval_dump($db);
		var_dump(count($db));
		$data = $db->read('ad', 1);
		debug_zval_dump($data);
		// $this->assertTrue(!is_resource($db->link) && is_resource($db->db_slaves[0]->link));
		//$db->query();
		debug_zval_dump($db);
    }

    /*function testExitStatusZeroIfTestsPass() {

        $this->assertEqual($exit_status, 0);
    }*/
}
?>