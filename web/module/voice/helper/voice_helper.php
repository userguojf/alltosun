<?php
/**
 * alltosun.com  voice_helper.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-5-11 下午12:26:42 $
 * $Id$
 */

class voice_helper
{
    public static function post_curl($headers, )
    {
        $ch = curl_init();
        
        //对方要求header头
        $headers = array(
                "Accept: application/json",
                'application/x-www-form-urlencoded; charset=UTF-8',
                'Authorization:CaaS2.0?'
        );

        //设置cURL允许执行的最长毫秒数。
        //         curl_setopt($ch, CURLOPT_TIMEOUT_MS,2000);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // 用来指定连接端口
        curl_setopt($ch, CURLOPT_PORT, '10443');

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE );
        //         curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

        return curl_exec($ch);
    }
}