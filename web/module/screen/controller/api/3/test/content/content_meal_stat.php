<?php

/**
 * alltosun.com  content_meal_stat.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2018年1月17日 下午4:52:53 $
 * $Id$
 */

class Action
{
    public function index()
    {
        $user_number        = tools_helper::get('user_number', '1101051002015');
        $device_unique_id   = tools_helper::get('device_unique_id', '94fe2291bc83');
        //$click_time         = tools_helper::get('click_time', '');
        $res_info           = '[{"time":1516640252},{"time":1516640558},{"time":1516640670},{"time":1516672663}]';
        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();
        
        //$post_data['user_number'] = $user_number;
        // 加密
        $post_data['sign']              = api_helper::encode_sign($post_data);
        
        $post_data['user_number']       = $user_number;
        $post_data['device_unique_id']  = $device_unique_id;
        $post_data['res_info']        = $res_info;
        
        an_dump($post_data);
        
        $api_url = SITE_URL.'/screen/api/3/content/content_meal_stat';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;
        
        an_dump(json_decode($res, true));
    }
}