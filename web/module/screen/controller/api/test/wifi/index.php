<?php
/**
 * alltosun.com  index.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年7月31日 下午5:30:12 $
 * $Id$
 */

class Action
{
    public function add_wifi()
    {
        $user_number    = tools_helper::get('user_number', '1101081002058');
        $device_unique_id     = tools_helper::get('device_unique_id', 'ec:d0:9f:ac:8c:67');
        $wifi_user_name = tools_helper::get('wifi_user_name', 'alltosun.com');
        $wifi_pwd       = tools_helper::get('wifi_pwd', 'alltosun2015');

        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        //$post_data['user_number'] = $user_number;
        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);

        $post_data['user_number'] = $user_number;
        $post_data['device_unique_id'] = $device_unique_id;
        $post_data['wifi_user_name'] = $wifi_user_name;
        $post_data['wifi_pwd'] = $wifi_pwd;

        p($post_data);

        $api_url = SITE_URL.'/screen/api/2/wifi/add_wifi';
        $res = an_curl($api_url, $post_data, 0, 0);
//         echo $res;

        p(json_decode($res, true));
    }

    public function get_device_wifi_info()
    {
        $user_number    = tools_helper::get('user_number', '1101081002058');
        $device_unique_id     = tools_helper::get('device_unique_id', 'ec:d0:9f:ac:8c:67');

        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        //$post_data['user_number'] = $user_number;
        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);

        $post_data['user_number'] = $user_number;
        $post_data['device_unique_id'] = $device_unique_id;

        $api_url = SITE_URL.'/screen/api/2/wifi/get_device_wifi_info';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;

        an_dump(json_decode($res, true));
    }
}