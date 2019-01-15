<?php
/**
 * alltosun.com  test_by_shenxn.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 申小宁 (shenxn@alltosun.com) $
 * $Date: 2016-6-4 下午3:05:33 $
 * $Id$
 */

class Action
{
    private  $app_id     = '100108475';
    private  $app_secret = '8ea452a0dc77bdc703ff7f8b8380657b';
    private  $token_url  = 'https://login.cloud.huawei.com/oauth2/v2/token';

    /**
access_token=accessToken&                
nsp_svc=openpush.message.api.send&                
nsp_ts=1472020575&                
device_token_list=%5B%2200000000000000000000000000000000%22%2C%2200000000000000000000000000000000%22%5D&                
payload=JSON_FORMAT_STRING&                
expire_time=2013-09-30T19%3A55
     */
    public function push()
    {
        $url ="https://api.push.hicloud.com/pushsend.do?";
        $time = time();

        $data = array(
            'access_token' => $this->get_token(),
            'nsp_svc'      => 'openpush.message.api.sen',
            'nsp_ts'       => $time,
            'device_token_list' => '[12345xxxxxxxxxxxxx23456]',
            'expire_time'       => date('Y-m-d T H:i', $time),
        );

        p($data);
    }

    public function get_token()
    {
        $data = array(
            'grant_type'=> 'client_credentials',
            'client_secret' => $this->app_secret ,
            'client_id' => $this->app_id
        );

        $opt = array(
            CURLOPT_USERAGENT=>'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.47 Safari/536.11'
        );

        $r =curl_post($this->token_url,http_build_query($data),$opt);

        return $r;
    }
}