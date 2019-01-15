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
 * $Date: 2017年7月19日 上午9:56:07 $
 * $Id$
 */

class Action
{
    public function add()
    {
        $user_number      = tools_helper::get('user_number', '1101021002051');
        $device_unique_id = tools_helper::get('device_unique_id', 'ecd09fac8c69');


        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        //$post_data['user_number'] = $user_number;
        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);

        $post_data['user_number']  = $user_number;
        $post_data['device_unique_id']   = $device_unique_id;


        an_dump($post_data);
        $api_url = SITE_URL."/screen/api/2/online/add";
        //$api_url = 'http://wifi.pzclub.cn/screen/api/2/online/add';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;

        an_dump(json_decode($res, true));
    }
}