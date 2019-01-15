<?php

/**
 * 将表文件放在/dev/shm的性能测试
 * ============================================================================
 * 版权所有 (C) 2007-2015 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 安然 (anr@alltosun.com) $
 * $Date: 2010-12-29 下午06:40:44 $
 * $Id$
*/

error_reporting(E_ALL);
define('ROOT_PATH',     substr(dirname(__FILE__), 0, -7));  // 定义网站根目录
define('PROJECT_NS',    'test.alltosun.net');                            // 定义项目自有的namespace（已用于MC）
define('D_BUG', 0); // 关闭debug
define('ONDEV', 1);

require_once '../init.php';
// require_once './simpletest/autorun.php';


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

// 数据库位于 /dev/shm/heap_wxscreen
Config::set('db5', array('mysql', 'localhost', 'test_anphp', 'FNT9KacFp9eNcZjS', 'heap_wxscreen'));


set_time_limit(0);
ini_set('memory_limit','512M');

header( 'Content-Type:   text/html;   charset=utf-8');
echo "<pre>";

// 连接MC
/*
$mc_wr = MemcacheWrapper::connect('mc');

if (Request::Get("cache", 1) == 0) {
    echo "关闭缓存\n<br>";
    define('CACHE', 0);
} else {
    echo "打开缓存\n<br>";
    define('CACHE', Request::Get("cache", 1));
}
*/

class test_shm_table
{
    public $ad_d = '';
    public $ad_s = '';
    public $cache = 0;
    public $count = 0;

    public function __construct()
    {
        $this->ad_d = _model('ad');
        $this->ad_s = _model('ad', 'db5');
        $this->count = 100000;
    }
    
    public function creat_data()
    {
        $i = 0;
        while ($i++ < $this->count) {
            $this->ad_d->create(array('id'=>$i, 'position_id'=>$i, 'link'=>'test', 'content'=>'alltosun'.$i), 'REPLACE');
            $this->ad_s->create(array('id'=>$i, 'position_id'=>$i, 'link'=>'test', 'content'=>'alltosun'.$i), 'REPLACE');
        }
    }
    
    public function do_disk()
    {
        $i = rand(1, $this->count);
        var_dump($i, $this->ad_d->read(array('id'=>$i)));
        $this->ad_d->update(array('id'=>$i), array('link'=>'test', 'content'=>'alltosun' . ($i+1)));
        var_dump($this->ad_d->limit(10)->getList(array('id >' => $i)));
    }
    
    public function do_shm()
    {
        $i = rand(1, $this->count);
        var_dump($i, $this->ad_s->read(array('id'=>$i)));
        $this->ad_s->update(array('id'=>$i), array('link'=>'test', 'content'=>'alltosun' . ($i+1)));
        var_dump($this->ad_s->limit(10)->getList(array('id >' => $i)));
    }

}

try {
    $t = new test_shm_table();

    $act = (isset($_GET['act'])) ? ($_GET['act']) : 'do_disk';

    if ('create_data' === $act) {
        $t->creat_data();
    } elseif ('do_disk' === $act) {
        $t->do_disk();
    } elseif ('do_shm' === $act) {
        $t->do_shm();
    } else {
       echo '?act=create_data|do_disk|do_shm';
    }
    
    /*echo $t1 = AnPHP::lastRunTime();
     echo '-';
     $i = 0;
     $count = 10000;
     while ($i++ < $count) {
     $ad_m->read(array('id'=>1));
     $ad_m->update(array('id'=>1), array('position_id'=>$i, 'link'=>'test', 'content'=>'alltosun'));
     }
     echo $t2 = AnPHP::lastRunTime();
     echo '=';
     echo "平均每个循环时间：";
     echo ($t2 / $count);
    
    
     echo "<br> \n";
     echo "/dev/shm";
     $ad_m->setDB('db5');
     echo $t1 = AnPHP::lastRunTime();
     echo '-';
     $i = 0;
     $count = 10000;
     while ($i++ < $count) {
     $ad_m->read(array('id'=>1));
     $ad_m->update(array('id'=>1), array('position_id'=>$i, 'link'=>'test', 'content'=>'alltosun'));
     }
     echo $t2 = AnPHP::lastRunTime();
     echo '=';
     echo "平均每个循环时间：";
     echo ($t2 / $count);*/
    
    /*
    $ad_m->setDB('db5');
    $i = rand(1, 10000);
    var_dump($ad_m->read(array('id'=>1)));
    $ad_m->update(array('id'=>1), array('position_id'=>$i, 'link'=>'test', 'content'=>'alltosun'));
    */

} catch (AnException $e) {
    AnException::echoMsg($e);
} catch (Exception $e) {
    throw $e;
}

?>