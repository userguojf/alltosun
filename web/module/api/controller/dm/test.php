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
        $appid     = 'wifi_shujdt_awzdxhyadrtggbrd';
        $app_key   = 'd1cb99814ddc2d11cdd8c099b6e5c6e8';
        $timestamp = time();
    
        $data = array(
                'appid'       => $appid,
                'timestamp'   => $timestamp,
                'token'       => md5($appid.'_'.$app_key.'_'.$timestamp),
                'business_code' => '1101081002052', //beijing_YYT
//                 'date'          => 20171215,
        );

        $url = 'http://mac.pzclub.cn';
        $url .= '/api/dm/experience';
// p($url);
// p(AnUrl('api/dm/experience'));
// exit();
//         $url = AnUrl('api/dm/experience');
        $res  = curl_post($url, $data);
    
        echo $res;
    }
}