<?php
/**
 * alltosun.com 测试轮播图 index.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-9-17 下午4:17:05 $
 * $Id$
 */

class Action
{
    public function index()
    {
        $user_number = '3308221349940';//tools_helper::post('yyt_user_number', '');
        $imei        = '862841038919647';//= tools_helper::post('imei', '');
        $content_id = 54;//  = tools_helper::post('content_id', 54);

        $imei = screen_helper::device_encode($imei);
        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);

        $post_data['user_number'] = $user_number;
        $post_data['imei']        = $imei;
        $post_data['content_id']  = $content_id;

        an_dump($post_data);

        $api_url = SITE_URL.'/screen/api/2/roll';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;

        an_dump(json_decode($res, true));
    }
}