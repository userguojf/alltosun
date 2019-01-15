<?php
/**
 * alltosun.com 测试接口 dm.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-5-11 下午3:53:08 $
 * $Id$
 */

class Action
{
  
    public function __call($action = '', $param = array())
    {


        $appid   = 'wifi_shujdt_awzdxhyadrtggbrd';
        $app_key = 'd1cb99814ddc2d11cdd8c099b6e5c6e8';
        $timestamp = time();

        $data = array(
                'appid'       => $appid,
                'timestamp'   => $timestamp,
                'token'       => md5($appid.'_'.$app_key.'_'.$timestamp),

                'phone'       => 15701651914,//13301163580,//15701651914,//18801235362,// 13301163580,//'13659928088',//'18310925147',
                'user_number' => '8201010329006',
        );
// p($data);exit();
        $url =Anurl('voice/dm');
//         p($url);exit();
        $result = curl_post($url, $data);
        p($result);
    }
}