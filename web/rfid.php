<?php
/**
  * alltosun.com rfid数据接收 rfid.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年5月11日 下午2:43:46 $
  * $Id$
  */
session_start();
define('DEBUG', isset($_GET['debug'])?1:0);
define('ROOT_PATH',  __DIR__.'/module/rfid/server');



require ROOT_PATH.'/config.php';

//数据库操作类
require ROOT_PATH.'/lib/DB.php';

//缓存类
require ROOT_PATH.'/lib/RedisCache.php';

//数据处理基类
require ROOT_PATH.'/lib/BaseHandle.php';
//secret 处理 helper
require ROOT_PATH.'/src/secret_helper.php';

$redis_cache = RedisCache::content();

require ROOT_PATH.'/lib/AutoLoad.php';

//公共函数
require ROOT_PATH.'/common/function.php';

require ROOT_PATH.'/core/route.php';

echo '<pre>';

$result = $redis_cache->redis->keys('phone*');
var_dump($result);
if ($result) {
    foreach ($result as $v) {
        echo $v.'<br />';
        $redis_cache->delete($v);
        var_dump($redis_cache->get($v));
    }
}

exit;
///////////////////////////////////// test  /////////////////////////////////////////////////////
if (isset($_GET['type'])) {

                $string="ID:03005555
MAC:E47444248CF9
RSSI:-66
F-TYPE:02
MOV:01
BAT:00
SN:125";
        $start = time()*1000;
        $end = (time()+4)*1000;


//          $string = "MessageId:1500884666549
// ID:--
// MAC:0001
// START:{$start}
// END:{$end}
// DURATION:4
// RSSI:-63
// MOV:00
// BAT:3.068
// G_Data_X:0068
// G_Data_Y:FFF5
// G_Data_Z:0F08
// A_Data_X:FE41
// A_Data_Y:00C7
// A_Data_Z:0042";
        $start = time();
        $end = time()+4;
        $dur  = $start-$end;
//         $string = "ID:11.0.4.80
// MAC:11.0.4.80
// START:{$start}
// END:{$end}
// DURATION:{$dur}
// RSSI:255
// MOV:01
// BAT:01
// G_Data_X:0000
// G_Data_Y:0000
// G_Data_Z:0000
// A_Data_X:-6
// A_Data_Y:253
// A_Data_Z:3";
//         $string = "ID:0000006B
// START:{$start}
// END:{$end}
// DURATION:{$dur}
// RSSI:-95
// MOV:0{$_GET['type']}
// BAT:01";
//         $time = date('YmdHis');
//         $string = "ID:03003C2D
// MAC:E47444248CF9
// DATE:{$time}
// RSSI:-66
// F-TYPE:02
// MOV:0{$_GET['type']}
// BAT:00";
// $time = time();
//         $string = "action_id:9286622
// label_id:000000EC
// mac:000000EC
// type:{$_GET['type']}
// timestamp:{$time}
// rssi:-52
// bat:1
// g_data_x:FEFC
// g_data_y:0428
// g_data_z:4214
// a_data_x:00AA
// a_data_y:0017
// a_data_z:00D1";

        $string = "action_id:203
label_id:label180110e0
mac:1105a1470fe5
type:1
timestamp:1517541757
rssi:-67
bat:1";
        route::get_instance()->parse(trim($string), NULL, NULL);
        exit();

$date = date('YmdHis');
$id   = '1797001A';

$string = "ID:{$id}
        MAC:2F54014BAEC9
        DATE:{$date}
        RSSI:-53
        F-TYPE:02
        MOV:01
        BAT:00";

        route::get_instance()->parse(trim($string), NULL, NULL);

        $date = date('YmdHis', time() + rand(3, 60));
        $string = "ID:{$id}
        MAC:2F54014BAEC9
        DATE:{$date}
        RSSI:-53
        F-TYPE:02
        MOV:00
        BAT:00";


        route::get_instance()->parse(trim($string), NULL, NULL);
}


// if (isset($_GET['type'])) {

//     $redis_cache = new Redis();         //创建Redis对象
//     $redis_cache->pconnect('127.0.0.1');  //连接服务

//     $j = $redis_cache->get('wangjf_test4');
//     if (!$j) {
//         $redis_cache->set('wangjf_test4', 1);
//     } else {
//         $redis_cache->set('wangjf_test4', $j+1);
//     }

//     echo $redis_cache->get('wangjf_test4');

//     unset($redis_cache);
// }
