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

Config::set('db0', array('mysql', 'localhost', 'test_test.com', 'AxAbbZDQDLJJBhcB', 'test_test.com'));
Config::set('db2', array(array('mysql', 'localhost', 'test_test.com', 'AxAbbZDQDLJJBhcB', 'test_test.com'), array('mysql', 'localhost', 'alltosun_cms', 'f6TDpHURU8w2CcG6', 'alltosun_cms')));
Config::set('db21', array(array('mysql', 'localhost', 'test_test.com', 'AxAbbZDQDLJJBhcB', 'test_test.com'), array('mysql', 'localhost', 'alltosun_cms', 'f6TDpHURU8w2CcG6', 'alltosun_cms'), array('mysqli', 'localhost', 'alltosun_cms', 'f6TDpHURU8w2CcG6', 'alltosun_cms')));
Config::set('db3', array('mysql', 'localhost', 'alltosun_cms', 'f6TDpHURU8w2CcG6', 'alltosun_cms'));
Config::set('db', array('mysql', 'localhost', 'alltosun_cms', 'f6TDpHURU8w2CcG6', 'alltosun_cms'));
Config::set('db4', array('db'));
// Config::set('db', array(array('mysql', 'localhost', 'alltosun_cms', 'f6TDpHURU8w2CcG6', 'alltosun_cms'), array('mysqli', 'localhost', 'alltosun_cms', 'f6TDpHURU8w2CcG6', 'alltosun_cms')));
Config::set('mc', array('localhost', 11211));
// $a = Cache::connect('memcache', array('127.0.0.1', 11211)); // 连接 memcache2
//$a = Cache::connect('memcache', Config::get('mc')); // 连接 memcache2


// 连接MC
/*$mc = new Memcache();
list($host, $port) = Config::get('mc');
$mc->connect($host, $port);

$mc_wr = new MemcacheWrapper($mc);*/
//$mc_wr = Cache::connect('memcache', array('127.0.0.1', 11211));
//$mc_wr = Cache::connect('memcache', array('db01', 11211));
$mc_wr = MemcacheWrapper::connect('mc');
Session::start($mc_wr);

define('D_BUG', Request::Get("debug", 0));
define('CACHE', Request::Get("cache", 1));
define('PROJECT_NS', 'test');

class TestOfModel extends UnitTestCase
{
    public function testSession()
    {
        global $mc_wr;
        $mc_wr_init = clone $mc_wr;
//        var_dump($mc_wr_init);
        $_SESSION['test'] = 1;
        $this->assertTrue($mc_wr == $mc_wr_init, 'SESSION changed mc_wr');
        $m = _model('blog');
//        var_dump($mc_wr_init);
        $this->assertTrue($mc_wr == $mc_wr_init, 'Model changed mc_wr');
        $_SESSION['test2'] = 2;
//        var_dump($r);
    }

    private function testGetListCreate()
    {
        $list = _model('flower_record')->getFields('id', 'ORDER BY id DESC');

        $id1 = _model('flower_record')->create(array('num'=>1));
        $list1 = _model('flower_record')->getFields('id', 'ORDER BY id DESC');
        $this->assertTrue(in_array($id1, $list1), var_export($list1, true));

        $id2 = _model('flower_record')->create(array('num'=>1));
        $list2 = _model('flower_record')->getFields('id', 'ORDER BY id DESC');
        $this->assertTrue(in_array($id2, $list2), var_export($list2, true));

        $id3 = _model('flower_record')->create(array('num'=>1));
        $list3 = _model('flower_record')->getFields('id', 'ORDER BY id DESC');
        $this->assertTrue(in_array($id3, $list3), var_export($list3, true));

        if (D_BUG) include('/data/framework/trunk/Debug.php');
    }


    private function testModel()
	{
        $m = _model('ad');
        var_dump($m);
        $data = $m->read(1);
        var_dump($data);
		$this->assertTrue(is_object($m));
		$this->assertTrue(is_array($data));
    }

    private function testDB()
    {
        $db = DB::connect('db');
        var_dump($db);
        $db = DB::connect('db2');
        var_dump($db);
        $db = DB::connect('db21');
        var_dump($db);
    }

    private function test_U()
    {
        $m = _U('ad');
        var_dump($m);
        $data = $m->read(1);
        var_dump($data);
    }

    private function test_U_read()
    {
        $data = _U('ad', 1);
        $this->assertTrue(is_array($data));
        $this->assertTrue(!empty($data['id']));
        var_dump($data);
    }

    /*function testExitStatusZeroIfTestsPass() {

        $this->assertEqual($exit_status, 0);
    }*/
}
?>