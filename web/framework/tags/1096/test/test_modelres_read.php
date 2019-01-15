<?php

/**
 * alltosun.com modelres->read的缓存测试 test_modelres_read.php
 * ============================================================================
 * 版权所有 (C) 2009-2011 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 高竞竞 (gaojj@alltosun.com) $
 * $Date: 2011-3-6 下午08:09:00 $
*/

error_reporting(E_ALL);
define('ROOT_PATH',     substr(dirname(__FILE__), 0, -7));  // 定义网站根目录

require_once '/data/framework/trunk/init.php';
require_once './simpletest/autorun.php';

//require_once '../config/config.php';
//require_once '../helper/setup.php';

Config::set('db', array('mysql', 'localhost', 'supaijob_com', 'fxTtFJDaDbhDuNA9', 'supaijob_com'));
Config::set('table_pre', 'an_');

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

class TestOfModel extends UnitTestCase
{
    public function testCache()
    {

//        $company_service_stat_model = _model('company_service_stat');

        $filter = array(
            'num >' => 0
        );
        $info = _model('company_service_stat')->read($filter, 'ORDER BY `add_time` DESC');

		$this->assertTrue(MemcacheWrapper::$count == 0);

        _model('company_service_stat')->update(array('id'=>$info['id']), 'SET num=num-1');

		
        $new_info = _model('company_service_stat')->read($filter, 'ORDER BY `add_time` DESC');
		$this->assertTrue(MemcacheWrapper::$count == 1);

        $this->assertTrue($new_info['num'] == $info['num'] - 1);




        $select_arr = array('company_id' => 208, 'product_id' => 12, 'end_time>' => date("Y-m-d H:i:s"), 'num>' => 0);
        $stat_info = _model('company_service_stat')->read($select_arr, "ORDER BY `add_time` ASC LIMIT 1");

        if (!empty($stat_info) && $stat_info['num'] > 0) {
            _model('company_service_stat')->update(array('id' => $stat_info['id']), "SET num = num - '1'");
        }

        $new_stat_info = _model('company_service_stat')->read($select_arr, "ORDER BY `add_time` ASC LIMIT 1");

        $this->assertTrue($new_stat_info['num'] == $stat_info['num'] - 1);

		$this->assertTrue(MemcacheWrapper::$count == 2);
    }
}

?>