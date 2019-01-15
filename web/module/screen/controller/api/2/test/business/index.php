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
 * $Date: 2017年6月28日 下午6:29:01 $
 * $Id$
 */

class Action
{
    /**
     * 获取列表
     */
    public function get_business()
    {

        $lat     = tools_helper::get('lat', '40.048639');
        $lng     = tools_helper::get('lng', '116.421617');
        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);

        $post_data['lat'] = $lat;
        $post_data['lng'] = $lng;

        an_dump($post_data);

        $api_url = SITE_URL.'/screen/api/2/business/get_business';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;

        an_dump(json_decode($res, true));
    }

    public function get_info()
    {
        $title  = tools_helper::get('title', '河北');

        // 生成接口基础数据
        $post_data           = api_helper::make_test_base_data();

        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);

        $post_data['title'] = $title;

        an_dump($post_data);

        $api_url = SITE_URL.'/screen/api/2/business/index/get_business_list';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;
    }

    /**
     * 获取列表
     */
    public function get_business_list()
    {

        $title    = tools_helper::get('title', '河北');
        $page     = tools_helper::get('page', 1);
        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);

        $post_data['title'] = $title;
        $post_data['page'] = $page;

        an_dump($post_data);

        $api_url = SITE_URL.'/screen/api/2/business/get_business_list';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;

        an_dump(json_decode($res, true));
    }
}