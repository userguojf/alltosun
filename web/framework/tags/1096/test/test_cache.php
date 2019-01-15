<?php

/**
 * alltosun.com Cache类的测试
 * ============================================================================
 * 版权所有 (C) 2007-2009 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 安然 (anr@alltosun.com) $
 * $Date: 2011-5-4 下午03:40:44 $
*/
error_reporting(E_ALL);
define('ROOT_PATH',     substr(dirname(__FILE__), 0, -7));  // 定义网站根目录

define('D_BUG', 1);

require_once '/data/framework/trunk/init.php';
require_once './simpletest/autorun.php';

//require_once '../config/config.php';
//require_once '../helper/setup.php';

Config::set('mc', array('127.0.0.1', 11211));
$a = Cache::connect('memcache', array('127.0.0.1', 11211)); // 连接 memcache2
$a = Cache::connect('memcache', Config::get('mc')); // 连接 memcache2

$mc_wr = Cache::connect('memcache', array('127.0.0.1', 11211));

echo '<pre>';

class TestOfModel extends UnitTestCase
{
    function testSet()
    {
        global $mc_wr;
        $mc_wr->set('an', 'alltosun.com');
        $this->assertTrue('alltosun.com' == $mc_wr->get('an'));

    }

    function testCacheIs()
	{
		global $mc_wr;
		global $g;
        $g['sql'][] = array(
             'sql'      => __METHOD__,
             'time'     => 0,'info'     => 0,'explain'  => 0,'db'  => 0,
        );

		// 空PS\NS test set
		$mc_wr->set('an', 'alltosun.com');
		$info = $mc_wr->get('an');
		$this->assertTrue('alltosun.com' == $info);
		var_dump($mc_wr->delete('an'));
		$info = $mc_wr->get('an');
		$this->assertFalse($info);

		// 空PS\NS test set
		$mc_wr->set('an', 'alltosun.com', 0);
		$info = $mc_wr->get('an');
		$this->assertTrue('alltosun.com' == $info);
		$mc_wr->delete('an');
		$info = $mc_wr->get('an');
		$this->assertFalse($info);

		// PS
		$mc_wr->PS('anhaha')->set('an', 'alltosun.com');
		$info = $mc_wr->get('an');
		$this->assertTrue('alltosun.com' == $info);
		$info = $mc_wr->PS('anhaha')->get('an');
		$this->assertTrue('alltosun.com' == $info);
		$mc_wr->delete('an');
		$info = $mc_wr->get('an');
		$this->assertFalse($info);

		// NS
		$mc_wr->PS('anhaha')->NS('anran')->set('an', 'alltosun.com');
var_dump($mc_wr);
var_dump($GLOBALS['g']['cache_sqs']);
		$info = $mc_wr->get('an');
		$this->assertTrue('alltosun.com' == $info);
		$info = $mc_wr->PS('anhaha')->NS('anran')->get('an');
		$this->assertTrue('alltosun.com' == $info);
		$mc_wr->PS('anhaha')->NS('anran')->delete('an');
		$info = $mc_wr->get('an');
		$this->assertFalse($info);

		// 多取，按顺序返回值测试，不存在值测试
		$mc_wr->PS('anhaha')->NS('anran')->set('an', 'alltosun.com');
		$mc_wr->PS('anhaha')->NS('anran')->set('ab', 'www.alltosun.com');
		$mc_wr->PS('anhaha')->NS('anran')->set('ac', 'http://www.alltosun.com');
		$mc_wr->PS('anhaha')->NS('anran')->set('ad', 'http://www.alltosun.com/index');
		$info = $mc_wr->PS('anhaha')->NS('anran')->get(array('an','ab', 'al', 'ac', 'ad', 'am'));
        // var_dump($info);
		$this->assertTrue(!isset($info['al']));
		$this->assertTrue(!isset($info['am']));
		$item = array_shift($info);
		$this->assertTrue('alltosun.com' == $item);
		$item = array_shift($info);
		$this->assertTrue('www.alltosun.com' == $item);
		$item = array_shift($info);
		$this->assertTrue('http://www.alltosun.com' == $item);
		$item = array_shift($info);
		$this->assertTrue('http://www.alltosun.com/index' == $item);

		$mc_wr->PS('anhaha')->deleteNS('anran');
		$info = $mc_wr->PS('anhaha')->NS('anran')->get('an');
		$this->assertFalse($info);
		$info = $mc_wr->PS('anhaha')->NS('anran')->get('ab');
		$this->assertFalse($info);
		$info = $mc_wr->PS('anhaha')->NS('anran')->get('ac');
		$this->assertFalse($info);
    }

    function testCall_user_func_array()
    {
        global $mc_wr;

        $info = $mc_wr->PS('local:db')->NS('table')->call_user_func_array('file_get_contents', 'http://alltosun.com/');
        // $this->assertEqual($info, 'alltosun.com');
        $this->assertTrue(strlen($info) > 1024);
    }

    function testFlush()
    {
        global $mc_wr;
return ;

		$mc_wr->flush();
		$mc_wr->PS('anhaha');
		$mc_wr->NS('anran');
		$mc_wr->set('an', 'alltosun.com');
		$mc_wr->set('zyj', 'alltosun.com');
		$info = $mc_wr->get('an');
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

		$mc_wr->delete('an');
		$info = $mc_wr->get('an');
		$this->assertFalse($mc_wr->get('an'));

		var_dump($mc_wr);
    }

    function testVarDump()
    {
    	global $g;
    	global $model_list;
    	global $mc_wr;
    	//var_dump($g);
    	//var_dump($model_list);
    	var_dump($mc_wr);
    	var_dump($g);
    	if (D_BUG) include('/data/framework/trunk/Debug.php');
    }
}
?>