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
 * $Date: 2017年8月31日 下午12:58:48 $
 * $Id$
 */

class Action
{
    public function get_shoppe_list()
    {
        $user_number    = tools_helper::get('user_number', '1101142001192');

        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);

        $post_data['user_number'] = $user_number;

        an_dump($post_data);

        $api_url = SITE_URL.'/screen/api/2/shoppe/get_shoppe_list';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;

        an_dump(json_decode($res, true));
    }

    public function get_shoppe_brand()
    {
        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);

        an_dump($post_data);

        $api_url = SITE_URL.'/screen/api/2/shoppe/get_shoppe_brand';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;

        an_dump(json_decode($res, true));
    }

    public function get_series_number()
    {
        $user_number = tools_helper::get('user_number', '1101142001192');
        $shoppe_brand  = tools_helper::get('shoppe_brand', '华为');

        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);

        $post_data['user_number'] = $user_number;
        $post_data['shoppe_brand'] = $shoppe_brand;

        an_dump($post_data);

        $api_url = SITE_URL.'/screen/api/2/shoppe/get_series_number';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;

        an_dump(json_decode($res, true));
    }

    public function create_shoppe()
    {
        $user_number   = tools_helper::get('user_number', '1101142001192');
        $shoppe_brand  = tools_helper::get('shoppe_brand', '华为');
        $shoppe_name   = tools_helper::get('shoppe_name', '华为test专柜');

        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);

        $post_data['user_number'] = $user_number;
        $post_data['shoppe_brand'] = $shoppe_brand;
        $post_data['shoppe_name'] = $shoppe_name;

        an_dump($post_data);

        $api_url = SITE_URL.'/screen/api/2/shoppe/create_shoppe';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;

        an_dump(json_decode($res, true));
    }
}