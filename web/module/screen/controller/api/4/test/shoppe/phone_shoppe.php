<?php

/**
 * alltosun.com 更新手机专木工已 phone_shoppe.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年9月4日 下午12:09:44 $
 * $Id$
 */

class Action
{
    public function update_shoppe()
    {
        $shoppe_id          = tools_helper::get('shoppe_id', 645);
        $device_unique_id   = tools_helper::get('device_unique_id','ecd09fac8c69');

        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);

        $post_data['shoppe_id']             = $shoppe_id;
        $post_data['device_unique_id']      = $device_unique_id;

        an_dump($post_data);

        $api_url = SITE_URL.'/screen/api/2/shoppe/phone_shoppe/update_shoppe';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;

        an_dump(json_decode($res, true));

    }

    /**
     * 是否绑定柜台
     */
    public function is_bind()
    {
        $device_unique_id     = tools_helper::get('device_unique_id', 'ecd09fac8c69');
        // 验证接口
        $check_params = array(
                //'user_number'  => $user_number
        );

        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);

        $post_data['device_unique_id']      = $device_unique_id;

        an_dump($post_data);


        $api_url = SITE_URL.'/screen/api/2/shoppe/phone_shoppe/is_bind';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;

        an_dump(json_decode($res, true));

    }
}