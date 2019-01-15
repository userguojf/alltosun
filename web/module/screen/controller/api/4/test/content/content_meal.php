<?php

/**
 * alltosun.com  content_meal.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2018年1月16日 上午11:56:36 $
 * $Id$
 */

class Action
{
    public function get_content_meal()
    {
        $user_number  = tools_helper::get('user_number', '1404001606110');
        $device_unique_id         = tools_helper::get('device_unique_id', '8c9f3b9915a2');
        //$info               = '[{"content_id":"6","id":3,"roll_sum":59,"time":1512638425698}]';

        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        //$post_data['user_number'] = $user_number;
        // 加密
        $post_data['sign']              = api_helper::encode_sign($post_data);

        $post_data['user_number']       = $user_number;
        $post_data['device_unique_id']  = $device_unique_id;

        an_dump($post_data);

        $api_url = SITE_URL.'/screen/api/4/content/content_meal/get_content_meal';
        //$api_url = 'http://mac.pzclub.cn/screen/api/3/content/content_meal/get_content_meal';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;

        an_dump(json_decode($res, true));
    }
}