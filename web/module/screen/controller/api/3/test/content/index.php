<?php

/**
 * alltosun.com  index.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王敬飞 (wangjf@alltosun.com) $
 * $Date: 2017年7月3日 下午6:02:50 $
 * $Id$
 */

class Action
{
    public function get_content()
    {
        $user_number            = tools_helper::get('user_number', '1101081002052');
        $device_unique_id       = tools_helper::get('device_unique_id', '3ca616845766');
        //$phone_imei      = tools_helper::get('phone_imei', '861579039336145');
        $user_number            = tools_helper::get('user_number', '1101081002058');
        $device_unique_id       = tools_helper::get('device_unique_id', 'c40bcb1d0666');

        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

//         $post_data['user_number'] = $user_number;
        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);

        $post_data['user_number'] = $user_number;
        $post_data['device_unique_id']  = $device_unique_id;

        //$api_url = SITE_URL.'/screen/api/3/content/get_content';
        //$api_url = 'http://mac.pzclub.cn/screen/api/3/content/get_content';
        //$res = an_curl($api_url, $post_data, 0, 0);

        //p($post_data);
        //p(json_decode($res, true));
        //echo $res;

        echo '<hr>';

        $api_url = SITE_URL.'/screen/api/3/content/get_content';
        //$api_url = 'http://mac.pzclub.cn/screen/api/3/content/get_content';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;
        p(json_decode($res, true));
    }

    public function is_exist_type4()
    {
        $device_unique_id       = tools_helper::get('device_unique_id', 'c40bcb1d0666');
        //$phone_imei      = tools_helper::get('phone_imei', '861579039336145');

        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        //         $post_data['user_number'] = $user_number;
        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);
        $post_data['device_unique_id']  = $device_unique_id;

        $api_url = SITE_URL.'/screen/api/3/content/is_exist_type4';
        //$api_url = 'http://mac.pzclub.cn/screen/api/3/content/get_content';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;

        var_dump(json_decode($res, true));
    }
}