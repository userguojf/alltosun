<?php

/**
 * alltosun.com  content_stat.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年7月31日 下午5:33:33 $
 * $Id$
 */

class Action
{
    public function add_content_stat()
    {
        $user_number        = tools_helper::get('user_number', '1101081002058');
        $device_unique_id   = tools_helper::get('device_unique_id', '00aefa7df953');
        $info               = '[{"res_id":"21","res_name":"photo", "click_time":"'.date('Y-m-d H:i:s').'"}, {"res_id":"22","res_name":"photo", "click_time":"'.date('Y-m-d H:i:s').'"}, {"res_id":"23","res_name":"photo", "click_time":"'.date('Y-m-d H:i:s').'"}, {"res_id":"24","res_name":"photo", "click_time":"'.date('Y-m-d H:i:s').'"}, {"res_id":"25","res_name":"photo", "click_time":"'.date('Y-m-d H:i:s').'"}]';

        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        // $post_data['user_number'] = $user_number;
        // 加密
        $post_data['sign']              = api_helper::encode_sign($post_data);

        $post_data['user_number']       = $user_number;
        $post_data['device_unique_id']  = $device_unique_id;
        $post_data['info']              = $info;

//         an_dump($post_data);

        $api_url = SITE_URL.'/screen/api/2/content/content_stat/add_content_stat';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;

//         an_dump(json_decode($res, true));

    }
}