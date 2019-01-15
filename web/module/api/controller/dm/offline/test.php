<?php
/**
 * alltosun.com  test.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-12-15 下午4:58:17 $
 * $Id$
 */
class Action
{
    
    public function index()
    {
//         _model('screen_everyday_offline_record')->create(
//             array(
//                     'province_id'      => 1,
//                     'city_id'          => 17,
//                     'area_id'          => 120,
//                     'business_hall_id' => 45971,
//                     'device_unique_id' => 'dcd91681053e',
//                     'date'         => 20171213,
                    
//                     'all_day'         => 1,
//             )
//         );exit();
        $appid     = 'wifi_shujdt_awzdxhyadrtggbrd';
        $app_key   = 'd1cb99814ddc2d11cdd8c099b6e5c6e8';
        $timestamp = time();
    
        $data = array(
                'appid'       => $appid,
                'timestamp'   => $timestamp,
                'token'       => md5($appid.'_'.$app_key.'_'.$timestamp),
                'business_code' => '1101081002058',
        );
    
        $res  = curl_post(AnUrl('api/dm/terminal/get_terminal_info'), $data);
    
        echo $res;
    }
}