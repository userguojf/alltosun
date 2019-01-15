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
 * $Date: 2017年7月19日 上午9:57:08 $
 * $Id$
 */

class Action
{
    public function add_device_info()
    {
        $user_number    = tools_helper::get('user_number', '5113211151633');
        $phone_imei     = tools_helper::get('phone_imei', '123456654321');
        $phone_name     = tools_helper::get('phone_name', 'Xiaomi667');
        $phone_version  = tools_helper::get('phone_version', 'MI 5667');
        $phone_mac      = tools_helper::get('phone_mac', 'ec:d0:9f:ac:86:68');

        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        //$post_data['user_number']  = $user_number;

        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);

        $post_data['user_number'] = $user_number;
        $post_data['phone_imei']  = $phone_imei;
        $post_data['phone_name']  = $phone_name;
        $post_data['phone_version'] = $phone_version;
        $post_data['phone_mac']    = $phone_mac;
        $post_data['registration_id']    = '100d8559097c8b299aa';


        an_dump($post_data);

        $api_url = SITE_URL.'/screen/api/3/phone/add_device_info';
        //$api_url = 'http://mac.pzclub.cn/screen/api/2/phone/add_device_info';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;

        an_dump(json_decode($res, true));
    }

    public function get_device_unique_id()
    {
        $phone_imei     = tools_helper::get('phone_imei', '123456654321');
        $phone_mac      = tools_helper::get('phone_mac', 'ec:d0:9f:ac:8c:69');
        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        //$post_data['user_number']  = $user_number;

        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);

        $post_data['phone_imei']  = $phone_imei;
        $post_data['phone_mac']     = $phone_mac;

        an_dump($post_data);

        $api_url = SITE_URL.'/screen/api/2/phone/get_device_unique_id';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;

        an_dump(json_decode($res, true));
    }

    public function update_version()
    {
        $imei         = Request::get('device_unique_id', 'ecd09fac8c69');
        $version_no   = Request::get('version_no', '4.0');


        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        //$post_data['user_number']  = $user_number;

        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);

        $post_data['device_unique_id'] = $imei;
        $post_data['version_no']  = $version_no;

        an_dump($post_data);

        $api_url = SITE_URL.'/screen/api/2/phone/update_version';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;

        an_dump(json_decode($res, true));
    }
}