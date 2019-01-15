<?php

/**
 * alltosun.com  app_stat.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2018年1月25日 下午6:44:27 $
 * $Id$
 */

class Action
{
    public function add_stat()
    {
//         p(date("s", '201361'), 201361/1000, ceil(201361/1000));
//         exit;
        $user_number  = tools_helper::get('user_number', '1101081002052');
        $device_unique_id         = tools_helper::get('device_unique_id', '1cda279f216b');
        $info = '[{"content":[{"app_name":"亮靓","open_count":35,"run_time":6318197},{"app_name":"系统桌面","open_count":18,"run_time":201361},{"app_name":"设置","open_count":1,"run_time":3967},{"app_name":"天气","open_count":2,"run_time":2337},{"app_name":"安全中心","open_count":16,"run_time":1422}],"record_time":1517973885,"type":1},{"content":[{"app_name":"亮靓","open_count":2,"run_time":1763438},{"app_name":"设置","open_count":1,"run_time":3443},{"app_name":"Android 系统","open_count":1,"run_time":54},{"app_name":"系统桌面","open_count":1,"run_time":7}],"record_time":1517887487,"type":1},{"content":[],"record_time":1517801088,"type":1}]';
        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();
    
        //$post_data['user_number'] = $user_number;
        // 加密
        $post_data['sign']              = api_helper::encode_sign($post_data);
    
        $post_data['user_number']       = $user_number;
        $post_data['device_unique_id']  = $device_unique_id;
        $post_data['content']              = $info;
        
        an_dump($post_data);

        $api_url = SITE_URL.'/screen/api/3/app_stat/app_stat/add';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;
    
        an_dump(json_decode($res, true));
    }
}