<?php

/**
 * alltosun.com DB单元测试 DB.php
 * ============================================================================
 * 版权所有 (C) 2009-2010 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址:  http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 秦艳青 (qinyq@alltosun.com) $
 * $Date: 2010-11-23 下午12:34:43 $
*/

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once '/data/framework/trunk/init.php';

class DBTest extends UnitTestCase
{

    public function testFileExists ()
    {
        echo "<pre>";
        $this->assertTrue(file_exists('data/framework/trunk/DB.php'),
        'DBTest.php is not exits!');
    }

    public function testConnect ()
    {
        try {    //直接参数测试
            $db = DB::connect('mysql', 'localhost', 'root', '123456', 'cntv_photo');
            if ($db->db_driver == 'mysql') {
                $this->assertEqual($db->db_driver, 'mysql');
                $this->assertIsA($db,'mysqlWrapper');
            } elseif ($db->db_driver == 'mysqli') {
                $this->assertEqual($db->db_driver, 'mysqli');
                $this->assertIsA($db,'mysqliWrapper');
            }

            $this->assertEqual($db->db_host, 'localhost');
            $this->assertEqual($db->db_name, 'cntv_photo');
        } catch (Exception $e) {
            $this->assertNotEqual('Error Empty DB Config.', $e->getMessage());  //正确的配置，断言不会报错
        }

//        set_error_handler($error_handler)
        try {
            Config::set('db', array('mysql', 'localhost', 'root', '123456', 'test'));
            $db = DB::connect('db');
//            print_r($db);
        } catch (Exception $e) {
            $this->assertEqual('Error Empty DB Config.', $e->getMessage());
        }


        try {    //一个一维数组参数测试
//            DB::connect(array());
            $db = DB::connect(array('mysql', 'localhost', 'username', 'password', 'test1'));
            $this->assertEqual($db->db_driver, 'mysql');
            $this->assertIsA($db,'mysqlWrapper');
            $this->assertEqual($db->db_host, 'localhost');
            $this->assertEqual($db->db_name, 'test1');
        } catch (Exception $e) {
            $this->assertEqual('Error Empty DB Config.', $e->getMessage(), 'method contains one empty array!');
        }


        try {    //两个一维数组为参数
            $db = DB::connect(array('mysql', 'localhost', 'username', 'password', 'test2'), array('mysql', 'localhost', 'username', 'password', 'test02'));
        } catch (Exception $e) {
            $this->assertEqual('Error Empty DB Config.', $e->getMessage(), 'method contains two empty array!');
        }

        try {    //多维数组为参数
            $db = DB::connect(array(array('mysql', 'localhost', 'username', 'password', 'dbname'), array('mysql', 'localhost', 'username', 'password', 'dbname')));
        } catch (Excetion $e) {
            $this->assertEqual('Error Empty DB Config.', $e->getMessage(), 'method array contains array');
        }


    }


}



$db_test = new DBTest();
$db_test->run(new HtmlReporter());

?>