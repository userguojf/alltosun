<?php
/**
 * alltosun.com 测试方法 test.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-1-20 下午6:09:01 $
 * $Id$
 */

class Action
{
    public function index()
    {
//         if ( ONDEV ) {
//             $url = AnUrl('api/dm/user');
//         } else {
        $url = 'http://mac.pzclub.cn/api/dm/user';
//         }
        $appid     = 'wifi_shujdt_awzdxhyadrtggbrd';

        $app_key   = 'd1cb99814ddc2d11cdd8c099b6e5c6e8';
        $timestamp = time();

        $data = array(
                'operation'   => 'delete',
                'appid'       => $appid,
                'timestamp'   => $timestamp,
                'token'       => md5($appid.'_'.$app_key.'_'.$timestamp),
                'province'    => '北京',
                'business_hall_title' => '西单营业厅',
                'user_number' => 1101021002051,
                'name'        => '占文',
                'phone'       => 18101386264,
//                 'weixin_id'   => '',
                'depart_ids'  => '2,30274'
        );
//         p($url);exit();
        $result = curl_post($url, $data);
        echo $result;
    }
}