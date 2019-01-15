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
    public function add_device_record()
    {
        //an_dump(date("Y-m-d H:i:s", 1500453610)); exit;
        $user_number    = tools_helper::get('user_number', '3611301173837');
        $time = time();
        $time3 = time()+3;
//         $info           = '[{"phone_imei":"142ac22faf32e","experience_time":1500453610110,"phone_mac":"00:ae:fa:7d:f9:53","type":1},{"phone_imei":"142ac22faf32e","experience_time":1500453625122,"phone_mac":"00:ae:fa:7d:f9:53","type":2},{"phone_imei":"142ac22faf32e","experience_time":1500453632665,"phone_mac":"00:ae:fa:7d:f9:53","type":1},{"phone_imei":"142ac22faf32e","experience_time":1500453636914,"phone_mac":"00:ae:fa:7d:f9:53","type":2},{"phone_imei":"142ac22faf32e","experience_time":1500453639701,"phone_mac":"00:ae:fa:7d:f9:53","type":1},{"phone_imei":"142ac22faf32e","experience_time":1500453642534,"phone_mac":"00:ae:fa:7d:f9:53","type":2}]';
        $info           = '[{"device_unique_id":"ec:d0:9f:ac:8c:67","experience_time":'.$time.',"phone_mac":"ec:d0:9f:ac:8c:67","type":1},{"device_unique_id":"ec:d0:9f:ac:8c:67","experience_time":'.$time3.',"phone_mac":"ec:d0:9f:ac:8c:67","type":2}]';
        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        //$post_data['user_number']  = $user_number;

        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);

        $post_data['user_number'] = $user_number;
        $post_data['info']  = $info;


        $api_url = SITE_URL.'/screen/api/2/phone/record/add_device_record';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;


        an_dump(json_decode($res, true));
    }
}