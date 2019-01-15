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
 * $Date: 2017年9月19日 下午4:02:52 $
 * $Id$
 */

class Action
{
    public function get_info()
    {
        $registration_id    = Request::get('registration_id', '100d8559097c8b299aa');
        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        //$post_data['user_number']  = $user_number;

        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);

        $post_data['registration_id'] = $registration_id;

        an_dump($post_data);

        $api_url = SITE_URL.'/screen/api/2/registration/get_info';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;

        an_dump(json_decode($res, true));
    }

    /**
     * 绑定registration_id接口
     * v4.0使用
     */
    public function bind_registration_id()
    {
        //13065ffa4e30c3d5bfb
        $registration_id    = tools_helper::Get('registration_id', '13065ffa4e30c3d5bfb');
        $device_unique_id   = tools_helper::Get('device_unique_id', 'c40bcb1d0666');
        $user_number        = tools_helper::Get('user_number', '1101081002058');

        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        //$post_data['user_number']  = $user_number;

        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);

        $post_data['registration_id'] = $registration_id;
        $post_data['device_unique_id'] = $device_unique_id;
        $post_data['user_number'] = $user_number;

        an_dump($post_data);
        $api_url = SITE_URL.'/screen/api/4/registration/bind_registration_id';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;

        an_dump(json_decode($res, true));
    }
}