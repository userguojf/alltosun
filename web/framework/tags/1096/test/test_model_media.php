<?php

/**
 * model 的单元测试
 * ============================================================================
 * 版权所有 (C) 2007-2009 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 安然 (anr@alltosun.com) $
 * $Date: 2010-12-29 下午06:40:44 $
*/
error_reporting(E_ALL);
define('ROOT_PATH',     substr(dirname(__FILE__), 0, -7));  // 定义网站根目录

require_once '/data/framework/trunk/init.php';
require_once './simpletest/autorun.php';

//require_once '../config/config.php';
//require_once '../helper/setup.php';

Config::set('db0', array('mysql', 'localhost', 'test_test.com', 'AxAbbZDQDLJJBhcB', 'test_test.com'));
Config::set('db2', array(array('mysql', 'localhost', 'test_test.com', 'AxAbbZDQDLJJBhcB', 'test_test.com'), array('mysql', 'localhost', 'an_cms', '3wbT6xKx9bP7rUhR', 'alltosun_cms')));
Config::set('db21', array(array('mysql', 'localhost', 'test_test.com', 'AxAbbZDQDLJJBhcB', 'test_test.com'), array('mysql', 'localhost', 'alltosun_cms', 'f6TDpHURU8w2CcG6', 'alltosun_cms'), array('mysqli', 'localhost', 'alltosun_cms', 'f6TDpHURU8w2CcG6', 'alltosun_cms')));

Config::set('db3', array('mysql', 'localhost', 'alltosun_cms', 'f6TDpHURU8w2CcG6', 'alltosun_cms'));
Config::set('db30', array(array('mysql', 'localhost', 'alltosun_cms', 'f6TDpHURU8w2CcG6', 'alltosun_cms'), array('mysql', 'localhost', 'an_cms', '3wbT6xKx9bP7rUhR', 'alltosun_cms')));
// 不同驱动
Config::set('db31', array(array('mysql', 'localhost', 'alltosun_cms', 'f6TDpHURU8w2CcG6', 'alltosun_cms'), array('mysqli', 'localhost', 'an_cms', '3wbT6xKx9bP7rUhR', 'alltosun_cms')));
// 不同库
Config::set('db32', array(array('mysql', 'localhost', 'alltosun_cms', 'f6TDpHURU8w2CcG6', 'alltosun_cms'), array('mysql', 'localhost', 'an_cms', '3wbT6xKx9bP7rUhR', 'test_test.com')));
Config::set('db', array('mysql', 'localhost', 'alltosun_cms', 'f6TDpHURU8w2CcG6', 'alltosun_cms'));
Config::set('db4', array('db'));
// Config::set('db', array(array('mysql', 'localhost', 'alltosun_cms', 'f6TDpHURU8w2CcG6', 'alltosun_cms'), array('mysqli', 'localhost', 'alltosun_cms', 'f6TDpHURU8w2CcG6', 'alltosun_cms')));
Config::set('mc', array('127.0.0.1', 11211));
// $a = Cache::connect('memcache', array('127.0.0.1', 11211)); // 连接 memcache2
//$a = Cache::connect('memcache', Config::get('mc')); // 连接 memcache2


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

class TestOfModel extends UnitTestCase
{
    public $m = NULL;

    function __construct()
    {
		global $mc_wr;
        // 单库、多库（读写分离）、热备库
        if (isset($_GET['s'])) {
        	// 多库配置
            $this->m = _model('media', 'db31');
            $this->m->db_select = 's';
        } elseif (isset($_GET['a'])) {
            // 多库配置
            $this->m = _model('media', 'db31');
            $this->m->db_select = 'a';
        } else {
            // 默认配置
            $this->m = _model('media');
        }
        // $this->m; // 没有res_type
        // @todo 带res_type的测试

        if (false) {
            // @todo 表前缀
            // 表前缀，sql中已写
        }
    }

    // 'read', 'gettotal', 'getlist', 'getfields'
    function testRead()
    {
        global $g;
        $g['sql'][] = array(
             'sql'      => __METHOD__,
             'time'     => 0,'info'     => 0,'explain'  => 0,'db'  => 0,
        );

        // 1、id
		/*$m_info = $this->m->read(31901);
		$this->assertEqual($m_info['id'], 31901);
		var_dump($m_info);*/
		
		/*$m_info = _uri('media', 31901, 91);
		$this->assertEqual($m_info['id'], 31901);
		var_dump($m_info);*/
		
		$i = 0;
		while($r = _uri('media', 31901, 91) && 500>$i++) {
			$this->assertTrue(!empty($r));
		}
		
		$r = _uri('media', 31901, 91);
		$this->assertTrue(!empty($r));
		
		$r = _uri('media', 31901, '91');
		$this->assertTrue(!empty($r));
	}
	
	function testVarDump()
    {
    	global $g;
    	global $model_list;
    	//var_dump($g);
    	//var_dump($model_list);
    	if (D_BUG) include('/data/framework/trunk/Debug.php');
    }
}

?>