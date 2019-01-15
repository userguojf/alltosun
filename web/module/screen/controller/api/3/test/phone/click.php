<?php

/**
 * alltosun.com  click.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年7月19日 下午12:04:48 $
 * $Id$
 */

class Action
{
    public function add_device_info()
    {
        $user_number            = tools_helper::get('user_number', '1101081002058');
        $device_unique_id       = tools_helper::get('device_unique_id', 'ec:d0:9f:ac:8c:67');
        $res_title              = tools_helper::get('res_title', '20170918204644000000_1_231902_96.png');
        $res_id                 = tools_helper::get('res_id', 63);

        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        //$post_data['user_number']  = $user_number;

        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);

        $post_data['user_number']       = $user_number;
        $post_data['device_unique_id']  = $device_unique_id;
        $post_data['res_id']            = $res_id;
        $post_data['res_title']          = $res_title;

        an_dump($post_data);

        $api_url = SITE_URL.'/screen/api/2/phone/click/add_click';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;

        an_dump(json_decode($res, true));
    }
}