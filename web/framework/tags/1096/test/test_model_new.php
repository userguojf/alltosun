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

header( 'Content-Type:   text/html;   charset=utf-8 ');
echo "<pre>";

// 连接MC
$mc = new Memcache();
list($host, $port) = Config::get('mc');
$mc->connect($host, $port);
$mc_wr = new MemcacheWrapper($mc);
$mc_wr = Cache::connect('memcache', 'localhost'); // 连接memcache;

if (Request::Get("cache", 1) == 0) {
	echo "关闭缓存\n<br>";
    define('CACHE', 0);
} else {
	echo "打开缓存\n<br>";
    define('CACHE', Request::Get("cache", 1));
}
define('D_BUG', 1);



//var_dump(_model('table')->pk('ad'));
//var_dump(_model('table'));

class TestOfModel extends UnitTestCase
{
    public $ad_m = NULL;

    function __construct()
    {
        // modelres model
        if (isset($_GET['modelresext'])) {
            _model('resource')->delete(99999);
			echo "modelresext模型\n<br>";
			_model('resource')->create(array('id'=>99999, 'name'=>'ad', 'table_name'=>'ad', 'description'=>'测试使用'));

            _model('attribute')->delete('WHERE 1');
            _model('attribute')->create(array('name'=>'textarea_name', 'type'=>'textarea', 'is_system'=>0, 'value'=>'')); // input
            _model('attribute')->create(array('name'=>'input_name', 'type'=>'input', 'is_system'=>0, 'value'=>'')); // input
            _model('attribute')->create(array('name'=>'radio_name', 'type'=>'radio', 'is_system'=>0, 'value'=>'')); // input
            _model('attribute')->create(array('name'=>'checkbox_name', 'type'=>'checkbox', 'is_system'=>0, 'value'=>'')); // input
            _model('attribute')->create(array('name'=>'select_name', 'type'=>'select', 'is_system'=>0, 'value'=>'')); // input

            _model('attribute_relation')->delete('WHERE 1');
            _model('attribute_relation')->create(array('res_name'=>'ad', 'attribute_name'=>'textarea_name')); // input
            _model('attribute_relation')->create(array('res_name'=>'ad', 'attribute_name'=>'input_name'));  // checkbox
            _model('attribute_relation')->create(array('res_name'=>'ad', 'attribute_name'=>'radio_name'));  // date
            _model('attribute_relation')->create(array('res_name'=>'ad', 'attribute_name'=>'checkbox_name'));  // select
            _model('attribute_relation')->create(array('res_name'=>'ad', 'attribute_name'=>'select_name'));  // select

            $this->ad_m = _model('ad');
        } elseif (isset($_GET['modelres'])) {
            _model('resource')->delete(99999);
			echo "modelres模型\n<br>";
			// 创建模型
			echo "attribute:创建护展属性\n<br>";
            $this->ad_m = _model('ad');
        } else {
            // 创建模型，将model模型强制使用在ad_model表上
			echo "model模型\n<br>";
        	$this->ad_m = _model('ad_model');
        }

        // 单库、多库（读写分离）、热备库
        if (isset($_GET['s'])) {
        	// 多库配置
			echo "多库(s)：强制选择从库\n<br>";
            $this->ad_m->setDB('db31');
            $this->ad_m->db_select = 'a';
        } elseif (isset($_GET['a'])) {
            // 多库配置
			echo "多库(a)：自动选择从库\n<br>";
            $this->ad_m->setDB('db31');
            $this->ad_m->db_select = 'a';
        } else {
			echo "单库";
		}

        _model('ad')->delete('WHERE 1');
        var_dump( _model('ad')->db_master->tableExists('ad%'));

        // 表前缀
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

// var_dump($this->ad_m);
//var_dump($this->ad_m->db_slave);
// die();
// var_dump($this->ad_m->db_master);
        // 1、id
        $r = $this->ad_m->delete(array('position_id'=>'10001'));
        // $this->ad_m->delete('WHERE 1');

		$this->ad_m->create(array('id'=>1, 'position_id'=>'10001', 'link'=>'test', 'content'=>'alltosun'), 'REPLACE');
		$this->ad_m->create(array('id'=>2, 'position_id'=>'10002', 'link'=>'test2', 'content'=>'alltosun2'), 'REPLACE');
		$ad_info = $this->ad_m->read(array('id'=>1));
// var_dump($ad_info);
// var_dump($this->ad_m);
		$this->assertEqual($ad_info['id'], 1);

		// 2、1维array()
		$ad_info = $this->ad_m->read(array('position_id'=>10001));
        $this->assertEqual($ad_info['id'], 1);
        $ad_info = $this->ad_m->read(array('id'=>1));
        $this->assertEqual($ad_info['position_id'], 10001);

        // 1
        $ad_list = $this->ad_m->read('LIMIT 10');
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 2
        $ad_list = $this->ad_m->read('ORDER BY `id` LIMIT 5');
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 3
        $ad_list = $this->ad_m->read('WHERE id<10  LIMIT 5');
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 4
        $ad_list = $this->ad_m->read('WHERE id<10', 'LIMIT 5');
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 5
        $ad_list = $this->ad_m->read('ORDER BY `id` LIMIT 5');
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 6
        // 不支持第1参数为空，getList()支持
        //$ad_list = $this->ad_m->read('', "ORDER BY position_id LIMIT 5");
        //$this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 7
        $ad_list = $this->ad_m->read(array('position_id' => 10002), "LIMIT 10");
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 8
        $ad_list = $this->ad_m->read(array('position_id' => 10001), "ORDER BY `id` LIMIT 10");
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 9
        $ad_list = $this->ad_m->read(array('link LIKE' => '%test%'));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 10
        $ad_list = $this->ad_m->read(array('position_id' => 10001 ,'link LIKE' => '%te%'));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 11
        $ad_list = $this->ad_m->read(array('position_id' => 10002 ,'link LIKE' => '%te%', 'content LIKE' => '%a%'));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 12
        $ad_list = $this->ad_m->read("2,3,4,10,20", "LIMIT 5");
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 13
        $ad_list = $this->ad_m->read(array(1,2,3,4,10,20), "LIMIT 5");
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 14
        $ad_list = $this->ad_m->read(array('id'=>array(1,2,3,4,10,20)), "LIMIT 5");
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));

        //@todo 更多条件
        // 15 条件key中带操作符 <
        $ad_list = $this->ad_m->read(array('position_id <=' => 10001 ,'link LIKE' => '%te%', 'content LIKE' => '%a%'));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));

        // 16 条件key中带操作符 >
        $ad_list = $this->ad_m->read(array('position_id >' => 1 ,'link LIKE' => '%te%', 'content LIKE' => '%a%'));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 17 条件key中带操作符 !=
        $ad_list = $this->ad_m->read(array('position_id !=' => 1 ,'link LIKE' => '%te%', 'content LIKE' => '%a%'));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 18 条件key中带操作符  <>
        $ad_list = $this->ad_m->read(array('position_id <>' => 1 ,'link LIKE' => '%te%', 'content LIKE' => '%a%'));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 19 条件key中带操作符  =
        $ad_list = $this->ad_m->read(array('position_id =' => 10001));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 20 条件key中带操作符  and
        $ad_list = $this->ad_m->read(array('position_id <>' => 2 ,'AND link LIKE' => '%te%', 'content LIKE' => '%a%'));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 21 条件key中带操作符  and or
        $ad_list = $this->ad_m->read(array('position_id <>' => 10 ,'OR link LIKE' => '%te%'));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 22 条件key中带操作符  like
        $ad_list = $this->ad_m->read(array('position_id <>' => 1 ,'link LIKE' => 'te%', 'content LIKE' => '%a%'));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));

    }


    /**
     * 测试ModelRes中特殊的操作
     */
    function testReadModelRes()
    {
        if (!($this->ad_m instanceof ModelRes || $this->ad_m instanceof ModelResExt)) {
        	return false;
        }

        global $g;
        $g['sql'][] = array(
             'sql'      => __METHOD__,
             'time'     => 0,'info'     => 0,'explain'  => 0,'db'  => 0,
        );

        // 创建数据
		$r = $this->ad_m->delete(array('position_id'=>10001));
		$id = $this->ad_m->create(array('position_id'=>'10002', 'link'=>'test2', 'content'=>'alltosun2'), 'REPLACE');
		$id = $this->ad_m->create(array('position_id'=>'10001', 'link'=>'test', 'content'=>'alltosun'), 'REPLACE');

		// 1、id
		$ad_info = $this->ad_m->read($id);
		$this->assertEqual($ad_info['id'], $id);
		$this->assertEqual($ad_info['position_id'], 10001);

		// 2、1维array()
        $ad_info = $this->ad_m->read(array('id'=>$id));
        $this->assertEqual($ad_info['id'], $id);
        $this->assertEqual($ad_info['position_id'], 10001);

        // @NEW 测试参数 array(array('id'=>$id, 'position_id'=>'1000100000'))
		$ad_info = $this->ad_m->read(array('id'=>$id, 'position_id'=>'1000100000'));
		$this->assertTrue(is_array($ad_info) && empty($ad_info));

        // 13
        $ad_info = $this->ad_m->read(array($id,1,2,3,4,10,20), "LIMIT 5");
        $this->assertTrue(is_array($ad_info) && !empty($ad_info));
        $this->assertTrue($ad_info['position_id'] == 10001 || $ad_info['position_id'] == 10002);

        // 14
        $ad_info = $this->ad_m->read(array('id'=>array($id,1,2,3,4,10,20)), "LIMIT 5");
        $this->assertTrue(is_array($ad_info) && !empty($ad_info));
        $this->assertTrue($ad_info['position_id'] == 10001 || $ad_info['position_id'] == 10002);

        // 22 条件key中带操作符  like
        $ad_info = $this->ad_m->read(array('id' => $id ,'link LIKE' => 'te%', 'content LIKE' => '%a%'));
        $this->assertTrue(is_array($ad_info) && !empty($ad_info));
        $this->assertEqual($ad_info['position_id'], 10001);

        // 22 条件key，2个条件
        $ad_info = $this->ad_m->read(array('id' => $id , 'position_id'=>'10001'));
        $this->assertTrue(is_array($ad_info) && !empty($ad_info));
        $this->assertEqual($ad_info['position_id'], 10001);

        // 22 条件key，2个条件，带操作符
        $ad_list = $this->ad_m->read(array('id' => $id , 'position_id<>'=>'10001'));
        $this->assertTrue(is_array($ad_list) && empty($ad_list));

		// 删除
        $r = $this->ad_m->delete($id);
        $this->assertTrue($r);
        $ad_info = $this->ad_m->read($id);
        $this->assertTrue(is_array($ad_info) && empty($ad_info));
    }

    function testGetlist()
    {
        global $g;
        $g['sql'][] = array(
             'sql'      => __METHOD__,
             'time'     => 0,'info'     => 0,'explain'  => 0,'db'  => 0,
        );

        // 创建数据
		$r = $this->ad_m->delete(array('position_id'=>10001));
		$id = $this->ad_m->create(array('position_id'=>'10001', 'link'=>'test', 'content'=>'alltosun'), 'REPLACE');
		$this->ad_m->create(array('id'=>1, 'position_id'=>'10001', 'link'=>'test', 'content'=>'alltosun'), 'REPLACE');
		$this->ad_m->create(array('id'=>10, 'position_id'=>'10', 'link'=>'test', 'content'=>'alltosun'), 'REPLACE');

        // 1
        $ad_list = $this->ad_m->getList('LIMIT 10');
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));

        // 2
        $ad_list = $this->ad_m->getList('ORDER BY `id` LIMIT 5');
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 3
        $ad_list = $this->ad_m->getList('WHERE id<10  LIMIT 5');
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 4
        $ad_list = $this->ad_m->getList('WHERE id<10', 'LIMIT 5');
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 5
        $ad_list = $this->ad_m->getList('ORDER BY `id` LIMIT 5');
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 6
        $ad_list = $this->ad_m->getList('', "ORDER BY position_id LIMIT 5");
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 7
        $ad_list = $this->ad_m->getList(array('position_id' => 10001), "LIMIT 10");
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 8
        $ad_list = $this->ad_m->getList(array('position_id' => 10001), "ORDER BY `id` LIMIT 10");
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 9
        $ad_list = $this->ad_m->getList(array('link LIKE' => '%te%'));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 10
        $ad_list = $this->ad_m->getList(array('position_id' => 10001 ,'link LIKE' => '%te%'));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 11
        $ad_list = $this->ad_m->getList(array('position_id' => 10001 ,'link LIKE' => '%te%', 'content LIKE' => '%a%'));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 12
        $ad_list = $this->ad_m->getList("1,2,3,4,10,20");
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        if (isset($_GET['modelres'])) {
        	$this->assertEqual($ad_list[1]['position_id'], 10001);
            $this->assertEqual($ad_list[10]['position_id'], 10);
        }

        // 13
        $ad_list = $this->ad_m->getList(array(1,2,3,4,10,20));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        if (isset($_GET['modelres'])) {
        	$this->assertEqual($ad_list[1]['position_id'], 10001);
            $this->assertEqual($ad_list[10]['position_id'], 10);
        }
        // 14
        $ad_list = $this->ad_m->getList(array('id'=>array(1,2,3,4,10,20)));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        if (isset($_GET['modelres'])) {
        	$this->assertEqual($ad_list[1]['position_id'], 10001);
            $this->assertEqual($ad_list[10]['position_id'], 10);
        }

		// 删除
        $r = $this->ad_m->delete($id);
		$this->assertTrue($r);
		$ad_info = $this->ad_m->read($id);
        $this->assertTrue(is_array($ad_info) && empty($ad_info));

        $r = $this->ad_m->delete(1);
		// $this->assertTrue($r);
		$ad_info = $this->ad_m->read(1);
        $this->assertTrue(is_array($ad_info) && empty($ad_info));

        $r = $this->ad_m->delete(10);
		$this->assertTrue($r);
		$ad_info = $this->ad_m->read(10);
        $this->assertTrue(is_array($ad_info) && empty($ad_info));

        // 条件key中带运算符
        // 条件key中带sql注入
        // 条件中带sql注入
    }

    function testGetfields()
    {
        global $g;
        $g['sql'][] = array(
             'sql'      => __METHOD__,
             'time'     => 0,'info'     => 0,'explain'  => 0,'db'  => 0,
        );

		// 创建数据
		$r = $this->ad_m->delete(array('position_id'=>10001));
		$id = $this->ad_m->create(array('position_id'=>'10001', 'link'=>'test', 'content'=>'alltosun'), 'REPLACE');
		$this->ad_m->create(array('id'=>1, 'position_id'=>'10001', 'link'=>'test', 'content'=>'alltosun'), 'REPLACE');
		$this->ad_m->create(array('id'=>10, 'position_id'=>'10001', 'link'=>'test', 'content'=>'alltosun'), 'REPLACE');

        $ad_list = $this->ad_m->getfields('position_id', "limit 5");
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        //var_dump($ad_list);

        // 1
        $ad_list = $this->ad_m->getfields('position_id','LIMIT 10');
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 2
        $ad_list = $this->ad_m->getfields('position_id','ORDER BY `id` LIMIT 5');
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 3
        $ad_list = $this->ad_m->getfields('position_id','WHERE id<10  LIMIT 5');
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 4
        $ad_list = $this->ad_m->getfields('position_id','WHERE id<10', 'LIMIT 5');
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 5
        $ad_list = $this->ad_m->getfields('position_id','ORDER BY `id` LIMIT 5');
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 6
        $ad_list = $this->ad_m->getfields('position_id','', "ORDER BY position_id LIMIT 5");
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 7
        $ad_list = $this->ad_m->getfields('position_id',array('position_id' => 10001), "LIMIT 10");
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 8
        $ad_list = $this->ad_m->getfields('position_id',array('position_id' => 10001), "ORDER BY `id` LIMIT 10");
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 9
        $ad_list = $this->ad_m->getfields('position_id',array('link LIKE' => '%te%'));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 10
        $ad_list = $this->ad_m->getfields('position_id',array('position_id' => 10001 ,'link LIKE' => '%te%'));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 11
        $ad_list = $this->ad_m->getfields('position_id',array('position_id' => 10001 ,'link LIKE' => '%te%', 'content LIKE' => '%a%'));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 12
        $ad_list = $this->ad_m->getfields('position_id',"2,3,4,10,20,$id", "LIMIT 5");
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 13
        $ad_list = $this->ad_m->getfields('position_id',array(1,2,3,4,10,20,$id), "LIMIT 5");
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 14
        $ad_list = $this->ad_m->getfields('position_id',array('id'=>array(1,2,3,4,10,20,$id)), "LIMIT 5");
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));

		// 删除
        $r = $this->ad_m->delete(1);
		$this->assertTrue($r);
		$ad_info = $this->ad_m->read(1);
        $this->assertTrue(is_array($ad_info) && empty($ad_info));

        $r = $this->ad_m->delete(10);
		$this->assertTrue($r);
		$ad_info = $this->ad_m->read(10);
        $this->assertTrue(is_array($ad_info) && empty($ad_info));

    }

    // 'create', 'update', 'delete'
    function testCreateModelRes()
    {
        if (!($this->ad_m instanceof ModelRes)) {
        	return false;
        }

        global $g;
        $g['sql'][] = array(
             'sql'      => __METHOD__,
             'time'     => 0,'info'     => 0,'explain'  => 0,'db'  => get_parent_class($this->ad_m).":".get_class($this->ad_m),
        );

        // 1、正常
        $id = $this->ad_m->create(array('position_id'=>'100', 'link'=>'test'));
        // $this->assertTrue(is_int($id) && !empty($id));
        $ad_info = $this->ad_m->read(array('id'=>$id));
        $this->assertEqual($ad_info['position_id'], 100);

        // 2、带UPDATE，简写
        $r = $this->ad_m->create(array('id'=>$id, 'position_id'=>'100', 'link'=>'test'), 'UPDATE position_id=position_id+1');
        $this->assertTrue($r);
        $ad_info = $this->ad_m->read(array('id'=>$id));
        $this->assertEqual($ad_info['position_id'], 101);

        // 3、带UPDATE
        $r = $this->ad_m->create(array('id'=>$id, 'position_id'=>'102', 'link'=>'test'), 'ON DUPLICATE KEY UPDATE position_id=103');
        $this->assertTrue($r);
        $ad_info = $this->ad_m->read(array('id'=>$id));
        $this->assertEqual($ad_info['position_id'], 103);

        // 3、带REPLACE
        $r = $this->ad_m->create(array('id'=>$id, 'position_id'=>'102', 'link'=>'test'), 'REPLACE');
        $this->assertTrue($r);
        $ad_info = $this->ad_m->read(array('id'=>$id));
        $this->assertEqual($ad_info['position_id'], 102);

        // 4、错误的参数，参数中包含数组
        /*$r = $this->ad_m->create(array('id'=>$id, 'position_id'=>'102', 'link'=>array()), 'REPLACE');
        $this->assertTrue($r);
        $r = $this->ad_m->create(array('id'=>$id, 'position_id'=>'102', 'link'=>$this->ad_m), 'REPLACE');
        $this->assertTrue($r);
        $r = $this->ad_m->create(array('id'=>$id, 'position_id'=>'102', 'link'=>array(1, 2, 3)), 'REPLACE');
        $this->assertTrue($r);*/

        // 删除
        $r = $this->ad_m->delete($id);
        $this->assertTrue($r);
        $ad_info = $this->ad_m->read($id);
        $this->assertTrue(is_array($ad_info) && empty($ad_info));
    }

    function testUpdateModelRes()
    {
        if (!($this->ad_m instanceof ModelRes)) {
        	return false;
        }

        global $g;
        $g['sql'][] = array(
             'sql'      => __METHOD__,
             'time'     => 0,'info'     => 0,'explain'  => 0,'db'  => 0,
        );

        // 生成
        $id = $this->ad_m->create(array('position_id'=>'104', 'link'=>'test'));
        $this->assertTrue(is_int($id) && !empty($id));

        // 1、简写
        $r = $this->ad_m->update(array('id'=>$id), array('position_id'=>105));
        $this->assertTrue($r);
        $ad_info = $this->ad_m->read(array('id'=>$id));
        $this->assertEqual($ad_info['position_id'], 105);

        // 2、更新2个字段
        $r = $this->ad_m->update(array('id'=>$id), array('position_id'=>105, 'link'=>'test2'));
        $this->assertTrue($r);
        $ad_info = $this->ad_m->read(array('id'=>$id));
        $this->assertEqual($ad_info['position_id'], 105);
        $this->assertEqual($ad_info['link'], 'test2');

        // 3、使用sql语句来更新
        $r = $this->ad_m->update(array('id'=>$id), 'SET position_id=position_id+1');
        $this->assertTrue($r);
        $ad_info = $this->ad_m->read(array('id'=>$id));
        $this->assertEqual($ad_info['position_id'], 106);
        $this->assertEqual($ad_info['link'], 'test2');

        // 删除
        $r = $this->ad_m->delete($id);
        $this->assertTrue($r);
        $ad_info = $this->ad_m->read($id);
        $this->assertTrue(is_array($ad_info) && empty($ad_info));
    }

    function testDeleteModelRes()
    {
        if (!($this->ad_m instanceof ModelRes)) {
        	return false;
        }

        global $g;
        $g['sql'][] = array(
             'sql'      => __METHOD__,
             'time'     => 0,'info'     => 0,'explain'  => 0,'db'  => 0,
        );

        // 1、删除条件数组
        $id = $this->ad_m->create(array('position_id'=>'105', 'link'=>'test'));
        $r = $this->ad_m->delete(array('id'=>$id));
        $this->assertTrue($r);
        $ad_info = $this->ad_m->read(array('id'=>$id));
        $this->assertTrue(is_array($ad_info) && empty($ad_info));

        // 2、删除id
        $id = $this->ad_m->create(array('position_id'=>'105', 'link'=>'test'));
        $r = $this->ad_m->delete(array('id'=>$id));
        $this->assertTrue($r);
        $ad_info = $this->ad_m->read(array('id'=>$id));
        $this->assertTrue(is_array($ad_info) && empty($ad_info));

        // 3、删除条件数组
        $id = $this->ad_m->create(array('position_id'=>'105', 'link'=>'test'));
        $r = $this->ad_m->delete(array('position_id'=>'105'));
        $this->assertTrue($r);
        $ad_info = $this->ad_m->read(array('id'=>$id));
        $this->assertTrue(is_array($ad_info) && empty($ad_info));


        // 4、删除条件数组，带运算符
        $id = $this->ad_m->create(array('position_id'=>'105', 'link'=>'test'));
        $r = $this->ad_m->delete(array('id >='=>$id));
        $this->assertTrue($r);
        $ad_info = $this->ad_m->read(array('id'=>$id));
        $this->assertTrue(is_array($ad_info) && empty($ad_info));

        // 5、删除条件数组，部分sql语句
        $id = $this->ad_m->create(array('position_id'=>'105', 'link'=>'test'));
        $r = $this->ad_m->delete("WHERE `id`=$id");
        $this->assertTrue($r);
        $ad_info = $this->ad_m->read(array('id'=>$id));
        $this->assertTrue(is_array($ad_info) && empty($ad_info));
    }


    // 'pk', 'tableexists', 'describe', 'fieldexists'
    function testPKModelRes()
    {
        if (!($this->ad_m instanceof ModelRes)) {
        	return false;
        }

        global $g;
        $g['sql'][] = array(
             'sql'      => __METHOD__,
             'time'     => 0,'info'     => 0,'explain'  => 0,'db'  => 0,
        );

    	$pk = $this->ad_m->pk();
    	$this->assertEqual($pk, 'id');

    	$pk = _model('table', 'db31')->pk('ad');
    	$this->assertEqual($pk, 'id');

    	$pk = _model('table')->pk('ad');
    	$this->assertEqual($pk, 'id');

    	//@todo 非 id的PK
    }


    // 'getrow', 'getall', 'getone', 'getcol'
    function testGetRowModelRes()
    {
        if (!($this->ad_m instanceof ModelRes)) {
        	return false;
        }

        global $g;
        $g['sql'][] = array(
             'sql'      => __METHOD__,
             'time'     => 0,'info'     => '1、要自动补LIMIT 1','explain'  => 0,'db'  => 0,
        );

    	$id = $this->ad_m->create(array('position_id'=>'102', 'link'=>'test'), 'REPLACE');
        $this->assertTrue(is_int($id));

        $ad_info = $this->ad_m->getRow("SELECT * FROM `{$this->ad_m->table}` WHERE `id`='{$id}'");
// var_dump($ad_info);
        $this->assertEqual($ad_info['id'], $id);

        // 会自动变成SELECT *
        $ad_info = $this->ad_m->getRow("SELECT id,position_id FROM `{$this->ad_m->table}` WHERE `id`='{$id}'");
        $this->assertEqual($ad_info['id'], $id);


        // 在主库在执行
        $ad_info = $this->ad_m->db->getRow("SELECT * FROM `{$this->ad_m->table}` WHERE `id`='{$id}'");
        $this->assertEqual($ad_info['id'], $id);

        // 删除
        $r = $this->ad_m->exec("DELETE FROM `{$this->ad_m->table}` WHERE `id`='{$id}'");
        $this->assertEqual($r, 1);

        // 没有记录删除
        $r = $this->ad_m->exec("DELETE FROM `{$this->ad_m->table}` WHERE `id`='{$id}'");
        $this->assertEqual($r, 0);
    }

    /**
     * 测试扩展属性
     */
    function testAttribute_value()
    {
        global $g;
        $g['sql'][] = array(
             'sql'      => __METHOD__,
             'time'     => 0,'info'     => 0,'explain'  => 0,'db'  => 0,
        );
        $resource_info = _model('resource')->read(99999);
        if (!($this->ad_m instanceof ModelResExt)) {
        	return false;
        }


        // 创建模型
        /*
            _model('attribute_relation')->create(array('res_name'=>'ad', 'attribute_name'=>'textarea_name')); // input
            _model('attribute_relation')->create(array('res_name'=>'ad', 'attribute_name'=>'input_name'));  // checkbox
            _model('attribute_relation')->create(array('res_name'=>'ad', 'attribute_name'=>'radio_name'));  // date
            _model('attribute_relation')->create(array('res_name'=>'ad', 'attribute_name'=>'checkbox_name'));  // select
            _model('attribute_relation')->create(array('res_name'=>'ad', 'attribute_name'=>'select_name'));  // select
         */
        // create & read
        // $id = $this->ad_m->create(array('position_id'=>'105', 'link'=>'test', 'textarea_name'=>'textarea', 'input_name'=>'input', 'radio_name'=>"radio1\nradio2\nradio3", 'checkbox_name'=>"checkbox1\ncheckbox2\ncheckbox3", 'select_name'=>"select1\nselect2\nselect3"));
        $id = $this->ad_m->create(array('position_id'=>'105', 'link'=>'test', 'textarea_name'=>'textarea', 'input_name'=>'input', 'radio_name'=>array("radio1", "radio2", "radio3"), 'checkbox_name'=>array("checkbox1", "checkbox2", "checkbox3"), 'select_name'=>array("select1", "select2", "select3")));
        $ad_info = $this->ad_m->read($id);
var_dump($ad_info);
        $this->assertTrue(is_array($ad_info) && !empty($ad_info));
        $this->assertEqual($ad_info['textarea_name'], 'textarea');
        $this->assertEqual($ad_info['input_name'], 'input');
        $this->assertEqual($ad_info['radio_name'][0], 'radio1');
        $this->assertEqual($ad_info['radio_name'][1], 'radio2');
        $this->assertEqual($ad_info['radio_name'][2], 'radio3');
        $this->assertEqual($ad_info['checkbox_name'][0], 'checkbox1');
        $this->assertEqual($ad_info['checkbox_name'][1], 'checkbox2');
        $this->assertEqual($ad_info['checkbox_name'][2], 'checkbox3');
        $this->assertEqual($ad_info['select_name'][0], 'select1');
        $this->assertEqual($ad_info['select_name'][1], 'select2');
        $this->assertEqual($ad_info['select_name'][2], 'select3');

        // update
        $this->ad_m->update(array('id'=>$id), array('position_id'=>'106', 'link'=>'test2', 'textarea_name'=>'textarea', 'input_name'=>'0', 'radio_name'=>array("1", "2", "3"), 'checkbox_name'=>array("1", "2", "3"), 'select_name'=>array("1", "2", "3")));
        $ad_info = $this->ad_m->read($id);
var_dump($ad_info);
        $this->assertTrue(is_array($ad_info) && !empty($ad_info));
        $this->assertEqual($ad_info['textarea_name'], 'textarea');
        $this->assertEqual($ad_info['input_name'], '0');
        $this->assertEqual($ad_info['radio_name'][0], '1');
        $this->assertEqual($ad_info['radio_name'][1], '2');
        $this->assertEqual($ad_info['radio_name'][2], '3');
        $this->assertEqual($ad_info['checkbox_name'][0], '1');
        $this->assertEqual($ad_info['checkbox_name'][1], '2');
        $this->assertEqual($ad_info['checkbox_name'][2], '3');
        $this->assertEqual($ad_info['select_name'][0], '1');
        $this->assertEqual($ad_info['select_name'][1], '2');
        $this->assertEqual($ad_info['select_name'][2], '3');

        // delete
        // $r = $this->ad_m->delete($id);
        // $this->assertTrue($r);
    }

    // 'query', 'exec'

    function testVarDump()
    {
    	global $g;
    	global $model_list;
    	//var_dump($g);
    	//var_dump($model_list);
    	if (D_BUG) include('/data/framework/trunk/Debug.php');
    }

    /*function testExitStatusZeroIfTestsPass() {

        $this->assertEqual($exit_status, 0);
    }*/
}

?>