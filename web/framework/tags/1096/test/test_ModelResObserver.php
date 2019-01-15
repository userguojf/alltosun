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

if (Request::Get("cache", 1) == 0) {
    echo "关闭缓存\n<br>";
    define('CACHE', 0);
} else {
    echo "打开缓存\n<br>";
    define('CACHE', Request::Get("cache", 1));
}
define('D_BUG', 1);

function an_ModelResObserver($param)
{
    // var_dump(_uri($param[0], $param[1]));
    $param[2]['title'] = array();
    //  debug_print_backtrace();
    //var_dump($param);
    // die();
}

class testObserver
{
    static $update_id = 0;
    static $delete_id = 0;

    static public function createBEF($param)
    {
        $param[2]['title'] = 'AnPHP';
        // debug_print_backtrace();
        // var_dump($param);
        // die();
    }
    
    static public function updateBEF($param)
    {
        if (is_array($param[2])) $param[2]['title'] = 'AnPHP';
        // debug_print_backtrace();
        // var_dump($param);
        // die();
    }

    static public function update($param)
    {
        testObserver::$update_id = $param[1];
        // debug_print_backtrace();
        // var_dump($param);
        // die();
    }

    static public function deleteBEF($param)
    {
        testObserver::$delete_id = $param[1];
        // debug_print_backtrace();
        // var_dump($param);
        // die();
    }
}


//var_dump(_model('table')->pk('ad'));
//var_dump(_model('table'));

class TestOfModel extends UnitTestCase
{
    public $ad_m = NULL;

    function __construct()
    {
        _model('resource')->delete(99999);
        _model('attribute_relation')->delete(array('res_type'=>99999));

        // 扩展属性
        if (isset($_GET['attribute'])) {
            // 创建模型
            echo "attribute:创建护展属性\n<br>";
            _model('resource')->create(array('id'=>99999, 'name'=>'ad', 'model'=>'ModelRes', 'table'=>'ad', 'description'=>'测试使用'));
            _model('attribute_relation')->create(array('res_type'=>99999, 'attribute_id'=>1)); // input
            _model('attribute_relation')->create(array('res_type'=>99999, 'attribute_id'=>16));  // checkbox
            _model('attribute_relation')->create(array('res_type'=>99999, 'attribute_id'=>84));  // date
            _model('attribute_relation')->create(array('res_type'=>99999, 'attribute_id'=>1103));  // select
        }

        // modelres model
        if (isset($_GET['modelres'])) {
            echo "modelres模型\n<br>";
            $this->ad_m = _model('ad');
        } else {
            // 创建模型，将model模型强制使用在ad_model表上
            echo "model模型\n<br>";
            _model('resource')->create(array('id'=>99999, 'name'=>'ad_model', 'model'=>'Model', 'table'=>'ad', 'description'=>'测试使用'));
            $this->ad_m = _model('ad_model');
        }

        // 单库、多库（读写分离）、热备库
        if (isset($_GET['a'])) {
            // 多库配置
            echo "多库(a)：自动选择从库\n<br>";
            $this->ad_m->setDB('db31');
            $this->ad_m->db_select = 'a';
        } else {
            echo "单库";
        }

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

        
        // 1、id
        $this->ad_m->create(array('id'=>1, 'position_id'=>'10001', 'link'=>'test', 'content'=>'alltosun'), 'REPLACE');

        ModelResObserver::attach('ad', 'read', 'an_ModelResObserver');
 var_dump(ModelResObserver::$Observers);
        $ad_info = $this->ad_m->read(array('id'=>1));
 var_dump($ad_info);
        $this->assertTrue(ModelResObserver::$Observers['ad']['read'][0][1] === 'an_ModelResObserver');
        $this->assertEqual($ad_info['id'], 1);
        $this->assertTrue($ad_info['title'] === array());

        ModelResObserver::detach('ad', 'read', 'an_ModelResObserver');
// var_dump(ModelResObserver::$Observers);
        $ad_info = $this->ad_m->read(array('id'=>1));
        $this->assertTrue(ModelResObserver::$Observers['ad'] === array());
        $this->assertEqual($ad_info['id'], 1);
        $this->assertTrue($ad_info['title'] === '');
        
        ModelResObserver::attach('ad', 'read', 'an_ModelResObserver');
        
        // 2、1维array()
        $ad_info = $this->ad_m->read(array('position_id'=>10001));
        $ad_info = $this->ad_m->read(array('position_id'=>10001));
        $this->assertEqual($ad_info['id'], 1);
        $ad_info = $this->ad_m->read(array('id'=>1));
        $this->assertEqual($ad_info['position_id'], 10001);

        // 1
        $ad_list = $this->ad_m->read('LIMIT 10');
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        $this->assertTrue($ad_info['title'] === array());
        // 2
        $ad_list = $this->ad_m->read('ORDER BY `id` LIMIT 5');
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        $this->assertTrue($ad_info['title'] === array());
        // 3
        $ad_list = $this->ad_m->read('WHERE id>10  LIMIT 5');
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        $this->assertTrue($ad_info['title'] === array());
        // 4
        $ad_list = $this->ad_m->read('WHERE id>10', 'LIMIT 5');
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        $this->assertTrue($ad_info['title'] === array());
        // 5
        $ad_list = $this->ad_m->read('ORDER BY `id` LIMIT 5');
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        $this->assertTrue($ad_info['title'] === array());
        // 6
        // 不支持第1参数为空，getList()支持
        //$ad_list = $this->ad_m->read('', "ORDER BY position_id LIMIT 5");
        //$this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 7
        $ad_list = $this->ad_m->read(array('position_id' => 16), "LIMIT 10");
        $this->assertTrue(is_array($ad_list) && empty($ad_list));
        $this->assertTrue($ad_info['title'] === array());
        // 8
        $ad_list = $this->ad_m->read(array('position_id' => 16), "ORDER BY `id` LIMIT 10");
        $this->assertTrue(is_array($ad_list) && empty($ad_list));
        $this->assertTrue($ad_info['title'] === array());
        // 9
        $ad_list = $this->ad_m->read(array('link like' => '%test%'));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        $this->assertTrue($ad_info['title'] === array());
        // 10
        $ad_list = $this->ad_m->read(array('position_id' => 10001 ,'link like' => '%te%'));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        $this->assertTrue($ad_info['title'] === array());
        // 11
        $ad_list = $this->ad_m->read(array('position_id' => 10001 ,'link like' => '%te%', 'content  like' => '%a%'));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        $this->assertTrue($ad_info['title'] === array());
        // 12
        $ad_list = $this->ad_m->read("2,3,4,10,20", "LIMIT 5");
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        $this->assertTrue($ad_info['title'] === array());
        // 13
        $ad_list = $this->ad_m->read(array(1,2,3,4,10,20), "LIMIT 5");
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        $this->assertTrue($ad_info['title'] === array());
        // 14
        $ad_list = $this->ad_m->read(array('id'=>array(1,2,3,4,10,20)), "LIMIT 5");
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        $this->assertTrue($ad_info['title'] === array());
        
        //@todo 更多条件
        // 15 条件key中带操作符 <
        $ad_list = $this->ad_m->read(array('position_id <=' => 10001 ,'link like' => '%te%', 'content like' => '%a%'));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        $this->assertTrue($ad_info['title'] === array());
        
        // 16 条件key中带操作符 >
        $ad_list = $this->ad_m->read(array('position_id >' => 1 ,'link like' => 'te%', 'content like' => '%a%'));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        $this->assertTrue($ad_info['title'] === array());
        
        // 17 条件key中带操作符 !=
        $ad_list = $this->ad_m->read(array('position_id !=' => 1 ,'link like' => '%st', 'content like' => '%a%'));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        $this->assertTrue($ad_info['title'] === array());
        // 18 条件key中带操作符  <>
        $ad_list = $this->ad_m->read(array('position_id <>' => 1 ,'link like' => '%te%', 'content like' => '%a%'));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        $this->assertTrue($ad_info['title'] === array());
        // 19 条件key中带操作符  =
        $ad_list = $this->ad_m->read(array('position_id =' => 10001));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        $this->assertTrue($ad_info['title'] === array());
        // 20 条件key中带操作符  and
        $ad_list = $this->ad_m->read(array('position_id <>' => 1 ,'AND link like' => 'te%', 'content like' => 'a%'));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        $this->assertTrue($ad_info['title'] === array());
        // 21 条件key中带操作符  and or
        $ad_list = $this->ad_m->read(array('position_id <>' => 1 ,'OR link like' => '%te%'));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        $this->assertTrue($ad_info['title'] === array());
        // 22 条件key中带操作符  like
        $ad_list = $this->ad_m->read(array('position_id <>' => 1 ,'link LIKE' => 'te%', 'content like' => '%a%'));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));

    }


    /**
     * 测试ModelRes中特殊的操作
     */
    function testReadModelRes()
    {
        if (!($this->ad_m instanceof ModelRes)) {
            return false;
        }

        global $g;
        $g['sql'][] = array(
             'sql'      => __METHOD__,
             'time'     => 0,'info'     => 0,'explain'  => 0,'db'  => 0,
        );

        // 创建数据
        $r = $this->ad_m->delete(array('position_id'=>10001));
        $id = $this->ad_m->create(array('position_id'=>'10001', 'link'=>'test', 'content'=>'alltosun'), 'REPLACE');

        // 1、id
        $ad_info = $this->ad_m->read($id);
        $this->assertEqual($ad_info['id'], $id);
        $this->assertEqual($ad_info['position_id'], 10001);
        $this->assertTrue($ad_info['title'] === array());
        
        // 2、1维array()
        $ad_info = $this->ad_m->read(array('id'=>$id));
        $this->assertEqual($ad_info['id'], $id);
        $this->assertEqual($ad_info['position_id'], 10001);

        // @NEW 测试参数 array(array('id'=>$id, 'position_id'=>'1000100000'))
        $ad_info = $this->ad_m->read(array('id'=>$id, 'position_id'=>'1000100000'));
        $this->assertTrue(is_array($ad_info) && empty($ad_info));

        // 13
        $ad_info = $this->ad_m->read(array($id,1,2,3,4,10,20), "ORDER BY `id` DESC LIMIT 5");
        $this->assertTrue(is_array($ad_info) && !empty($ad_info));
        $this->assertEqual($ad_info['position_id'], 10001);

        // 14
        $ad_info = $this->ad_m->read(array('id'=>array($id,1,2,3,4,10,20)), "ORDER BY `id` DESC LIMIT 5");
        $this->assertTrue(is_array($ad_info) && !empty($ad_info));
        $this->assertEqual($ad_info['position_id'], 10001);

        // 22 条件key中带操作符  like
        $ad_info = $this->ad_m->read(array('id' => $id ,'link LIKE' => 'te%', 'content like' => '%a%'));
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
        $this->assertTrue($ad_list[0]['title'] === array());
// var_dump(ModelResObserver::$Observers);
// var_dump($ad_list);
        
        // 2
        $ad_list = $this->ad_m->getList('ORDER BY `id` LIMIT 5');
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        $this->assertTrue($ad_list[0]['title'] === array());
        // 3
        $ad_list = $this->ad_m->getList('WHERE id>10  LIMIT 5');
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        $this->assertTrue($ad_list[0]['title'] === array());
        // 4
        $ad_list = $this->ad_m->getList('WHERE id>10', 'LIMIT 5');
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        $this->assertTrue($ad_list[0]['title'] === array());
        // 5
        $ad_list = $this->ad_m->getList('ORDER BY `id` LIMIT 5');
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        $this->assertTrue($ad_list[0]['title'] === array());
        // 6
        // $ad_list = $this->ad_m->getList('', "ORDER BY position_id LIMIT 5");
        $ad_list = $this->ad_m->getList('WHERE 1', "ORDER BY position_id LIMIT 5");
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        $this->assertTrue($ad_list[0]['title'] === array());
        // 7
        $ad_list = $this->ad_m->getList(array('position_id' => 10001), "LIMIT 10");
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        $this->assertTrue($ad_list[0]['title'] === array());
        // 8
        $ad_list = $this->ad_m->getList(array('position_id' => 10001), "ORDER BY `id` LIMIT 10");
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        $this->assertTrue($ad_list[0]['title'] === array());
        // 9
        $ad_list = $this->ad_m->getList(array('link like' => '%te%'));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        $this->assertTrue($ad_list[0]['title'] === array());
        // 10
        $ad_list = $this->ad_m->getList(array('position_id' => 10001 ,'link like' => '%te%'));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        $this->assertTrue($ad_list[0]['title'] === array());
        // 11
        $ad_list = $this->ad_m->getList(array('position_id' => 10001 ,'link like' => '%te%', 'content like' => '%a%'));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        $this->assertTrue($ad_list[0]['title'] === array());
        // 12
        $ad_list = $this->ad_m->getList("1,2,3,4,10,20", "LIMIT 5");
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        if (isset($_GET['modelres'])) {
            foreach ($ad_list as $v) {
                if (1 === $v['id']) $this->assertEqual($v['position_id'], 10001);
                if (10 === $v['id']) $this->assertEqual($v['position_id'], 10);
                $this->assertTrue($v['title'] === array());
            }
        }

        // 13
        $ad_list = $this->ad_m->getList(array(1,2,3,4,10,20), "LIMIT 5");
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        if (isset($_GET['modelres'])) {
            foreach ($ad_list as $v) {
                if (1 === $v['id']) $this->assertEqual($v['position_id'], 10001);
                if (10 === $v['id']) $this->assertEqual($v['position_id'], 10);
                $this->assertTrue($v['title'] === array());
            }
        }
        // 14
        $ad_list = $this->ad_m->getList(array('id'=>array(1,2,3,4,10,20)), "LIMIT 5");
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        if (isset($_GET['modelres'])) {
            foreach ($ad_list as $v) {
                if (1 === $v['id']) $this->assertEqual($v['position_id'], 10001);
                if (10 === $v['id']) $this->assertEqual($v['position_id'], 10);
                $this->assertTrue($v['title'] === array());
            }
        }

        // 删除
        $r = $this->ad_m->delete($id);
        $this->assertTrue($r);
        $ad_info = $this->ad_m->read($id);
        $this->assertTrue(is_array($ad_info) && empty($ad_info));

        $r = $this->ad_m->delete(1);
        $this->assertTrue($r);
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
        $ad_list = $this->ad_m->getfields('position_id','WHERE id>10  LIMIT 5');
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 4
        $ad_list = $this->ad_m->getfields('position_id','WHERE id>10', 'LIMIT 5');
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 5
        $ad_list = $this->ad_m->getfields('position_id','ORDER BY `id` LIMIT 5');
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 6
        // $ad_list = $this->ad_m->getfields('position_id','', "ORDER BY position_id LIMIT 5");
        $ad_list = $this->ad_m->getfields('position_id','WHERE 1', "ORDER BY position_id LIMIT 5");
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 7
        $ad_list = $this->ad_m->getfields('position_id',array('position_id' => 10001), "LIMIT 10");
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 8
        $ad_list = $this->ad_m->getfields('position_id',array('position_id' => 10001), "ORDER BY `id` LIMIT 10");
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 9
        $ad_list = $this->ad_m->getfields('position_id',array('link like' => '%te%'));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 10
        $ad_list = $this->ad_m->getfields('position_id',array('position_id' => 10001 ,'link like' => '%te%'));
        $this->assertTrue(is_array($ad_list) && !empty($ad_list));
        // 11
        $ad_list = $this->ad_m->getfields('position_id',array('position_id' => 10001 ,'link like' => '%te%', 'content like' => '%a%'));
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
        $r = $this->ad_m->delete($id);
        $this->assertTrue($r);
        $ad_info = $this->ad_m->read($id);
        $this->assertTrue(is_array($ad_info) && empty($ad_info));

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
    function testCreate()
    {
        global $g;
        $g['sql'][] = array(
             'sql'      => __METHOD__,
             'time'     => 0,'info'     => 0,'explain'  => 0,'db'  => get_parent_class($this->ad_m).":".get_class($this->ad_m),
        );
        
        ModelResObserver::detach('ad');
        
        ModelResObserver::attach('ad', 'createBEF', array('testObserver', 'createBEF'));
        // ModelResObserver::attach('ad', 'create', 'testObserver::create');
//var_dump(ModelResObserver::$Observers);
        
        // 1、正常
        $id = $this->ad_m->create(array('position_id'=>'100', 'link'=>'test'));
        $this->assertTrue(is_int($id) && !empty($id));
        $ad_info = $this->ad_m->read(array('id'=>$id));
//var_dump($ad_info);
        $this->assertEqual($ad_info['position_id'], 100);
        $this->assertEqual($ad_info['title'], 'AnPHP');

        // 2、带UPDATE，简写
        $r = $this->ad_m->create(array('id'=>$id, 'position_id'=>'100', 'link'=>'test'), 'UPDATE position_id=position_id+1');
        $this->assertTrue($r);
        $ad_info = $this->ad_m->read(array('id'=>$id));
        $this->assertEqual($ad_info['position_id'], 101);
        $this->assertEqual($ad_info['title'], 'AnPHP');

        // 3、带UPDATE
        $r = $this->ad_m->create(array('id'=>$id, 'position_id'=>'102', 'link'=>'test'), 'ON DUPLICATE KEY UPDATE position_id=103');
        $this->assertTrue($r);
        $ad_info = $this->ad_m->read(array('id'=>$id));
        $this->assertEqual($ad_info['position_id'], 103);
        $this->assertEqual($ad_info['title'], 'AnPHP');

        // 3、带REPLACE
        $r = $this->ad_m->create(array('id'=>$id, 'position_id'=>'102', 'link'=>'test'), 'REPLACE');
        $this->assertTrue($r);
        $ad_info = $this->ad_m->read(array('id'=>$id));
        $this->assertEqual($ad_info['position_id'], 102);
        $this->assertEqual($ad_info['title'], 'AnPHP');

        // 4、错误的参数，参数中包含数组
        /*$r = $this->ad_m->create(array('id'=>$id, 'position_id'=>'102', 'link'=>array()), 'REPLACE');
        $this->assertTrue($r);
        $r = $this->ad_m->create(array('id'=>$id, 'position_id'=>'102', 'link'=>$this->ad_m), 'REPLACE');
        $this->assertTrue($r);
        $r = $this->ad_m->create(array('id'=>$id, 'position_id'=>'102', 'link'=>array(1, 2, 3)), 'REPLACE');
        $this->assertTrue($r);*/

        // 5、 add_time 字段测试
// var_dump($this->ad_m);

        // 删除
        $r = $this->ad_m->delete($id);
        $this->assertTrue($r);
        $ad_info = $this->ad_m->read($id);
        $this->assertTrue(is_array($ad_info) && empty($ad_info));
    }

    function testUpdate()
    {
        global $g;
        $g['sql'][] = array(
             'sql'      => __METHOD__,
             'time'     => 0,'info'     => 0,'explain'  => 0,'db'  => 0,
        );

        ModelResObserver::detach('ad');
        
        ModelResObserver::attach('ad', 'updateBEF', array('testObserver', 'updateBEF'));
        ModelResObserver::attach('ad', 'update', array('testObserver', 'update'));

        // 生成
        $id = $this->ad_m->create(array('position_id'=>'104', 'link'=>'test'));
        $this->assertTrue(is_int($id) && !empty($id));

        // 1、简写
        $r = $this->ad_m->update(array('id'=>$id), array('position_id'=>105));
        $this->assertTrue($r);
        $ad_info = $this->ad_m->read(array('id'=>$id));
        $this->assertEqual($ad_info['position_id'], 105);
        $this->assertEqual($ad_info['title'], 'AnPHP');
        $this->assertEqual(testObserver::$update_id, $id);

        // 2、更新2个字段
        $r = $this->ad_m->update(array('id'=>$id), array('position_id'=>105, 'link'=>'test2'));
        $this->assertTrue($r);
        $ad_info = $this->ad_m->read(array('id'=>$id));
        $this->assertEqual($ad_info['position_id'], 105);
        $this->assertEqual($ad_info['link'], 'test2');
        $this->assertEqual($ad_info['title'], 'AnPHP');

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

    function testDelete()
    {
        global $g;
        $g['sql'][] = array(
             'sql'      => __METHOD__,
             'time'     => 0,'info'     => 0,'explain'  => 0,'db'  => 0,
        );

        ModelResObserver::detach('ad');
        
        ModelResObserver::attach('ad', 'deleteBEF', array('testObserver', 'deleteBEF'));

        // 1、删除条件数组
        $id = $this->ad_m->create(array('position_id'=>'105', 'link'=>'test'));
        $r = $this->ad_m->delete(array('id'=>$id));
        $this->assertTrue($r);
        $ad_info = $this->ad_m->read(array('id'=>$id));
        $this->assertTrue(is_array($ad_info) && empty($ad_info));
        $this->assertEqual(testObserver::$delete_id, $id);

        // 2、删除id
        $id = $this->ad_m->create(array('position_id'=>'105', 'link'=>'test'));
        $r = $this->ad_m->delete($id);
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
    function testPK()
    {
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
    function testGetRow()
    {
        global $g;
        $g['sql'][] = array(
             'sql'      => __METHOD__,
             'time'     => 0,'info'     => '1、要自动补LIMIT 1','explain'  => 0,'db'  => 0,
        );

        $id = $this->ad_m->create(array('position_id'=>'102', 'link'=>'test'), 'REPLACE');
        $this->assertTrue(is_int($id));

        $ad_info = $this->ad_m->getRow("SELECT * FROM `{$this->ad_m->table}` WHERE `id`='{$id}'");
        $this->assertEqual($ad_info['id'], $id);

        // 会自动变成SELECT *
        $ad_info = $this->ad_m->getRow("SELECT id,position_id FROM `{$this->ad_m->table}` WHERE `id`='{$id}'");
        $this->assertEqual($ad_info['id'], $id);

        // 在主库在执行
        $ad_info = $this->ad_m->selectDB('m')->getRow("SELECT * FROM `{$this->ad_m->table}` WHERE `id`='{$id}'");
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
        if (empty($resource_info)) {
            return false;
        }
        if (!($this->ad_m instanceof ModelRes)) {
            return false;
        }

        // 创建模型
        /*$resource_info = _model('resource')->read(99999);
        if (empty($resource_info)) {
            _model('resource')->create(array('id'=>99999, 'name'=>'ad', 'model'=>'ModelRes', 'table'=>'ad', 'description'=>'测试使用'));
            _model('attribute_relation')->create(array('res_type'=>99999, 'attribute_id'=>1)); // input
            _model('attribute_relation')->create(array('res_type'=>99999, 'attribute_id'=>16));  // checkbox
            _model('attribute_relation')->create(array('res_type'=>99999, 'attribute_id'=>84));  // date
            _model('attribute_relation')->create(array('res_type'=>99999, 'attribute_id'=>1103));  // select
        }*/

        // create & read
        $id = $this->ad_m->create(array('position_id'=>'105', 'link'=>'test', 1=>'input', 16=>'checkbox', 84=>'date', 1103=>'select'));
        $ad_info = $this->ad_m->read(array('id'=>$id));
var_dump($ad_info);

        $this->assertTrue(is_array($ad_info) && !empty($ad_info));
        $this->assertEqual($ad_info[1], 'input');
        $this->assertEqual($ad_info[16][0], 'checkbox');
        $this->assertEqual($ad_info[84], 'date');
        $this->assertEqual($ad_info[1103][0], 'select');

        // update
        $this->ad_m->update(array('id'=>$id), array('position_id'=>'106', 'link'=>'test', 1=>'inputinput', 16=>'checkboxcheckbox', 84=>'datedate', 1103=>'selectselect'));
        $ad_info = $this->ad_m->read(array('id'=>$id));
        $this->assertTrue(is_array($ad_info) && !empty($ad_info));
        $this->assertEqual($ad_info['position_id'], 106);
        $this->assertEqual($ad_info[1], 'inputinput');
        $this->assertEqual($ad_info[16][0], 'checkboxcheckbox');
        $this->assertEqual($ad_info[84], 'datedate');
        $this->assertEqual($ad_info[1103][0], 'selectselect');

        // delete
        $r = $this->ad_m->delete(array('id'=>$id));
        $this->assertTrue($r);
    }

    function __TestSafe()
    {
        $bad_model_name = array(' ', '`', "'", "'", ',', ';', '*', '#', '/', '\\', '%');
        foreach ($bad_model_name as $value) {
            ;
        }
        
        // 1
        $ad_list = $this->ad_m->read(array('    '));
        $this->assertTrue(is_array($ad_list) && empty($ad_list));
        // 1
        // $ad_list = $this->ad_m->read('    ');
        $this->assertTrue(is_array($ad_list) && empty($ad_list));
                // 1
        $ad_list = $this->ad_m->read('""""""');
        $this->assertTrue(is_array($ad_list) && empty($ad_list));
                // 1
        $ad_list = $this->ad_m->read(array('    '));
        $this->assertTrue(is_array($ad_list) && empty($ad_list));
                // 1
        $ad_list = $this->ad_m->read(array('    '));
        $this->assertTrue(is_array($ad_list) && empty($ad_list));
                // 1
        $ad_list = $this->ad_m->read(array('    '));
        $this->assertTrue(is_array($ad_list) && empty($ad_list));
        
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