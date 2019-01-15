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
define('PROJECT_NS',    'test.alltosun.net');                            // 定义项目自有的namespace（已用于MC）
define('D_BUG', 1);
define('ONDEV', 1);

// require_once '/data/framework/trunk/init.php';
require_once '../init.php';
require_once './simpletest/autorun.php';

// set_exception_handler('AnException::echoMsg');

//require_once '../config/config.php';
//require_once '../helper/setup.php';

Config::set('db3', array('mysql', 'localhost', 'test_anphp', 'FNT9KacFp9eNcZjS', 'test_anphp'));
Config::set('db30', array(array('mysql', 'localhost', 'test_anphp', 'FNT9KacFp9eNcZjS', 'test_anphp'), array('mysql', 'localhost', 'test_anphp2', 'E2CNKcHrN58TAMUs', 'test_anphp')));
// 不同驱动
Config::set('db31', array(array('mysql', 'localhost', 'test_anphp', 'FNT9KacFp9eNcZjS', 'test_anphp'), array('mysqli', 'localhost', 'test_anphp2', 'E2CNKcHrN58TAMUs', 'test_anphp')));
// 不同库
Config::set('db32', array(array('mysql', 'localhost', 'test_anphp', 'FNT9KacFp9eNcZjS', 'test_anphp'), array('mysql', 'localhost', 'test_anphp2', 'E2CNKcHrN58TAMUs', 'test_test.com')));
Config::set('db', array('mysql', 'localhost', 'test_anphp', 'FNT9KacFp9eNcZjS', 'test_anphp'));
Config::set('db4', array('db'));
// Config::set('db', array(array('mysql', 'localhost', 'test_anphp', 'FNT9KacFp9eNcZjS', 'test_anphp'), array('mysqli', 'localhost', 'test_anphp', 'FNT9KacFp9eNcZjS', 'test_anphp')));
Config::set('mc', array('127.0.0.1', 11211));



header( 'Content-Type:   text/html;   charset=utf-8');
echo "<pre>";

// 连接MC
$mc_wr = MemcacheWrapper::connect('mc');

if (Request::Get("cache", 1) == 0) {
    echo "关闭缓存\n<br>";
    define('CACHE', 0);
} else {
    echo "打开缓存\n<br>";
    define('CACHE', Request::Get("cache", 1));
}

// 测试对象复制、引用、传递的关系
class a {
    public $a = 'a';
}
class c {
    public $c = 'c';
}

$a = new a();

$b = $a;  // 对象直接赋值
$c = $a; // 对象引用传值
$b->a = 'b'; // 改变对象的属性
debug_zval_dump($a==$b, $a===$c); // 三者表现都一样
debug_zval_dump($a, $b, $c); // 三者表现都一样

$c = new c(); // 将$c 重新赋值, copy on write，$c 已经与 $a $b 没有关系
debug_zval_dump($a, $b, $c); // a b 一样,c一样

// 
$b = $a;  // 对象直接赋值
$c = &$a; // 对象引用传值
$b->a = 'b'; // 改变对象的属性
debug_zval_dump($a, $b, $c); // 三者表现都一样

$c = new c(); // 将$c 重新赋值
debug_zval_dump($a, $b, $c); // $c 和 $a 一致，原有 $a被$b保留
$d = clone($a);

/**
 * 示例：分表
 * @author anr
 *
 */
class ad_super_model extends Model
{

	public function hook_pre_call($name, $params)
	{
		if (!$this->hookCheck($name)) {
			return NULL;
		}

		static $t = array();
// var_dump($name, $params, $t);

		// 3、判断参数
		$filter = $params[0];
		if (!isset($filter['position_id'])) {
			throw new AnException('没有 position_id 参数。');
		}

		// 4、设定，参数直接跟表名，可以使用空字符串对原表进行操作
		$table_name = 'ad' . $filter['position_id'];
		/* unset($filter['position_id']);
		if (empty($filter)) {
			$filter = array('1' => 1);
		} */

		// 表存在，设定表后返回
		if (!isset($t[$table_name])) {
			$t[$table_name] = _model('table', $this->db_op)->tableExists($table_name);
		}
		if (!$t[$table_name]) {
			// 如果表不存在，创建
			// 原始结构再继续优化
			$t[$table_name] = TRUE;
			echo
			$sql = "CREATE TABLE IF NOT EXISTS `{$table_name}` (
	  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
	  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '广告标题',
	  `position_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '广告位id',
	  `type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '1-图片 2-文字 3-视频 4-flash 5-代码',
	  `content` text NOT NULL,
	  `link` varchar(255) NOT NULL DEFAULT '' COMMENT '广告链接',
	  `hits` int(10) NOT NULL DEFAULT '0' COMMENT '广告点击数',
	  `status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '0-关闭 1-开启',
	  PRIMARY KEY (`id`),
	  KEY `position_id` (`position_id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2892";
	//die();
			if (!_model('table', $this->db_op)->query($sql)) {
				throw new AnException('创建表' . $table_name . '失败');
			}

			// 导入数据
			$msg_list = _model('ad')->getList(array('position_id'=>$filter['position_id']));
			foreach ($msg_list as $v) {
				// unset($v['position_id'], $v['position_id']);
				$this->db->create($table_name, $v);
			}
		}

		$this->setTable($table_name);
		return NULL;
	}
}

/**
 * 示例：设定独立的缓存空间，针对于列表即可
 * @author anr
 *
 */
class a_hook_model extends Model
{
    public $new = 1;

    public function hook_pre_call($name, $params)
    {
    	if (!$this->hookCheck($name)) {
			return NULL;
		}
    	

        // 3、判断参数
        $filter = $params[0];
        if (!isset($filter['position_id'])) {
        	throw new AnException('没有 position_id 参数。');
        }

        // 4、设定 通过__get()方法生成新命名空间的mc对象
        $mc_name = 'mc_table_list_ns' . $filter['position_id'];

        // 5、重新指定
        // $params[0] 为表名，$params[1] 为参数$filter或$info
        if ($this->mc_table_list_ns !== $this->$mc_name) {
            echo "不等；";
            $this->mc_table_list_ns = &$this->$mc_name;
        }
    }

    public function hook_back_call($name, $params, $r)
    {
// var_dump(__METHOD__, $name, $params, $r);
    }
}

/**
 * 示例：设定独立的缓存空间，针对于列表即可
 * @author anr
 *
 */
class a_ModerRes_hook_model extends ModelRes
{
    public $new = 1;
    public $table = 'ad';

    public function hook_pre_call($name, $params)
    {
        if (!$this->hookCheck($name)) {
            return NULL;
        }

        // 判断参数
        $filter = $params[0];
        if (!isset($filter['position_id'])) {
            throw new AnException('没有 position_id 参数。');
        }

        // 4、设定 通过__get()方法生成新命名空间的mc对象
        $mc_name = 'mc_table_list_ns' . $filter['position_id'];

        // 5、重新指定
        // $params[0] 为表名，$params[1] 为参数$filter或$info
        if ($this->mc_table_list_ns !== $this->$mc_name) {
            echo "不等；";
            $this->mc_table_list_ns = &$this->$mc_name;
        }
    }

    public function hook_back_call($name, $params, $r)
    {
        // var_dump(__METHOD__, $name, $params, $r);
    }
}

/////////////////////////////

class TestOfModel extends UnitTestCase
{
    public $ad_m = NULL;

    function __construct()
    {

    }
    // 'read', 'gettotal', 'getlist', 'getfields'
    function testad_super()
    {
try {
        AnDebug::$op[] = array('type' => 'db', 'info' => array(
                         'sql'      => __METHOD__,
                         'sql_info' => '','sql_real' => '','time'     => 0,'info'     => 0,'explain'  => 0,'db'       => '__METHOD__',
                    ));
    		$this->ad_m = _model('ad_super');

    		// 1、
    		$ad_info = $this->ad_m->read(array('id'=>1, 'position_id'=>'10001'));
    		$this->ad_m->create(array('id'=>1, 'position_id'=>'10001', 'link'=>'test', 'content'=>'alltosun'), 'REPLACE');
    		//return;
    		$ad_info = $this->ad_m->read(array('id'=>1, 'position_id'=>'10001'));
    		$this->assertEqual($ad_info['id'], 1);
    		// 表是否正确
    		$this->assertEqual('ad10001', $this->ad_m->table_org);

    		// 2、列表操作
    		$ad_info = $this->ad_m->getList(array('position_id'=>'1'));
    		// 表是否正确
    		$this->assertEqual('ad1', $this->ad_m->table_org);
    		$this->assertEqual('title', $ad_info[0]['title']);
    		$this->assertEqual('1192', $ad_info[0]['id']);
var_dump($ad_info);

            // 3 不同表操作
    		$ad_info = $this->ad_m->read(array('id'=>1, 'position_id'=>'10001'));
var_dump($ad_info);
    		$this->assertEqual($ad_info['id'], 1);
    		// 表是否正确
    		$this->assertEqual('ad10001', $this->ad_m->table_org);
    		$ad_info = $this->ad_m->read(array('id'=>1192, 'position_id'=>'1'));
    		$this->assertEqual($ad_info['id'], 1192);
    		// 表是否正确
    		$this->assertEqual('ad1', $this->ad_m->table_org);

    		// 3、
    		$ad_info = $this->ad_m->read(array('position_id'=>10001));
    		$this->assertEqual($ad_info['id'], 1);
    		$ad_info = $this->ad_m->read(array('id'=>1, 'position_id'=>'10001'));
    		$this->assertEqual($ad_info['position_id'], 10001);
    		// 表是否正确
    		$this->assertEqual('ad10001', $this->ad_m->table_org);

debug_zval_dump($this->ad_m);
} catch (AnException $e) {
    AnException::echoMsg($e);
} catch (Exception $e) {
    throw $e;
}
    }

    // 'read', 'gettotal', 'getlist', 'getfields'
    function testad()
    {
try {
        AnDebug::$op[] = array('type' => 'db', 'info' => array(
                         'sql'      => __METHOD__,
                         'sql_info' => '','sql_real' => '','time'     => 0,'info'     => 0,'explain'  => 0,'db'       => '',
                    ));
        //var_dump(AnPHP::$dir);
        $this->ad_m = _model('a_hook');
        $this->ad_m->setTable('ad');
        //$this->ad_m->hookPreCall = true;
        //$this->ad_m->hookBackCall = true;
        //var_dump($this->ad_m->mc_table_ns198283);
        //var_dump($this->ad_m->mc_table_nsabdkeckek);
//var_dump($this->ad_m);

        // 1
// var_export($this->ad_m);
        $this->ad_m->create(array('id'=>1, 'position_id'=>'10001', 'link'=>'test', 'content'=>'alltosun'), 'REPLACE');
        $ad_info = $this->ad_m->read(array('id'=>1, 'position_id'=>'10001'));
        $this->assertEqual($ad_info['position_id'], 10001);
        $this->assertTrue(isset($this->ad_m->mc_table_list_ns10001));
        $this->assertEqual($this->ad_m->mc_table_list_ns, $this->ad_m->mc_table_list_ns10001);
var_dump($this->ad_m->mc_table_list_ns, $this->ad_m->mc_table_list_ns10001);
        
        $this->ad_m->create(array('id'=>2, 'position_id'=>'10002', 'link'=>'test', 'content'=>'alltosun'), 'REPLACE');
        $ad_info = $this->ad_m->read(array('id'=>2, 'position_id'=>'10002'));
        $this->assertEqual($ad_info['id'], 2);
        $this->assertEqual($this->ad_m->mc_table_list_ns, $this->ad_m->mc_table_list_ns10002);
        var_dump($this->ad_m->mc_table_list_ns, $this->ad_m->mc_table_list_ns10002);

        // 2、1维array()
        $ad_info = $this->ad_m->read(array('position_id'=>10001));
        $this->assertEqual($ad_info['id'], 1);
        $ad_info = $this->ad_m->read(array('id'=>1, 'position_id'=>'10001'));
        $this->assertEqual($ad_info['position_id'], 10001);

        // debug_zval_dump(DB::$connections);
        // 测试设置数据库
        // $ad_list = $this->ad_m->setDB('');
echo "------------------------------<br>\n";
debug_zval_dump($this->ad_m);
} catch (AnException $e) {
    AnException::echoMsg($e);
} catch (Exception $e) {
    throw $e;
}
    }


    function testa_ModerRes_hook_model()
    {
try {
        AnDebug::$op[] = array('type' => 'db',  'info' => array(
                         'sql'      => __METHOD__,
                         'sql_info' => '','sql_real' => '','time'     => 0,'info'     => 0,'explain'  => 0,'db'       => '',
                    ));
    $this->ad_m = $ad = _model('a_ModerRes_hook');
    $ad->initialization();
debug_zval_dump($ad);
    var_dump($ad->read(array('id'=>2, 'position_id'=>'10002')));
    
    
echo __METHOD__ . "------------------------------<br>\n";
debug_zval_dump($this->ad_m);
} catch (AnException $e) {
    AnException::echoMsg($e);
} catch (Exception $e) {
    throw $e;
}
    }


    function testVarDump()
    {
        if (D_BUG) AnDebug::echoMsg();
    }

    /*function testExitStatusZeroIfTestsPass() {

        $this->assertEqual($exit_status, 0);
    }*/
}

?>