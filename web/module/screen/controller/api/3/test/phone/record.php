<?php
/**
 * alltosun.com  record.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年7月19日 上午9:59:35 $
 * $Id$
 */

class Action
{
//     public function add_device_record()
//     {

//         $time = time()
//         $ex1 = rand(1, 10);
//         $ex2 = rand(1, 10);
//         $ex3 = rand(1, 10);

//         //$info           = '[{"device_unique_id":"ecd09fac8c69","experience_time":'.$time.',"phone_mac":"ec:d0:9f:ac:8c:69","type":1},{"device_unique_id":"ecd09fac8c69","experience_time":'.$time3.',"phone_mac":"ec:d0:9f:ac:8c:69","type":2}]';
//         $info           =   '[{"device_unique_id":"c40bcb1d0666","experience_time":'.$ex1.',"add_time":"'.$time.'"},{"device_unique_id":"c40bcb1d0666","experience_time":'.$ex2.',"add_time":"'.$time.'"},{"device_unique_id":"c40bcb1d0666","experience_time":'.$ex3.',"add_time":"'.$time.'"}]';
//         $post_data = api_helper::make_test_base_data();

//         // 加密
//         $post_data['sign'] = api_helper::encode_sign($post_data);

//         $post_data['info']  = $info;


//         $api_url = SITE_URL.'/screen/api/3/phone/record/add_device_record';
//         $res = an_curl($api_url, $post_data, 0, 0);
//         p($post_data);
//         echo $res;

//         an_dump(json_decode($res, true));
//     }

    public function add_device_record()
    {

        $time = time();
        $ex1 = 10;
        $device_unique_id = tools_helper::Get( "device_unique_id", "102ab3eda203");

        //$info           =   '[{"experience_time":'.$ex1.',"add_time":"'.($time+100).'"},{"experience_time":'.$ex2.',"add_time":"'.($time+200).'"}]';
        $info = '[{"add_time":'.$time.',"experience_time":'.$ex1.'}]';
        $post_data = api_helper::make_test_base_data();

        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);
        $post_data['device_unique_id']  = $device_unique_id;
        $post_data['info']  = $info;

        $api_url = SITE_URL.'/screen/api/4/phone/record/add_device_record';
        $res = an_curl($api_url, $post_data, 0, 0);
        p($post_data);
        echo $res;

        an_dump(json_decode($res, true));
    }
}