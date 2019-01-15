<?php
/**
 * alltosun.com  
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * szy: 宋志宇 (songzy@alltosun.com)
 * 2018年3月23日: 2016-7-26 下午3:05:10
 * Id
 */

class Action
{
    /**
     * 获取列表
     */
    public function add_gps()
    {
        $lat     = tools_helper::get('lat', '40.048639');
        $lng     = tools_helper::get('lng', '120.421617');
        $device_unique_id   = tools_helper::get('device_unique_id', 'e44790aae687');//设备唯一标识
        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);

        $post_data['lat'] = $lat;
        $post_data['lng'] = $lng;
        $post_data['device_unique_id'] = $device_unique_id;

        //an_dump($post_data);

        $api_url = SITE_URL.'/screen/api/3/phone/gps/add_gps?powerby=alltosun&debug=1&cache=0';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;

        an_dump(json_decode($res, true));
    }

}