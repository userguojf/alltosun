<?php
/**
  * alltosun.com 终端接口 terminal.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年12月15日 下午4:12:54 $
  * $Id$
  */
class Action
{
    private $secret = array(
            'appid'  => 'wifi_shujdt_awzdxhyadrtggbrd',
            'appkey' =>  'd1cb99814ddc2d11cdd8c099b6e5c6e8',
    );

    public $pzclub_url = SITE_URL;
    //public $pzclub_url = 'http://mac.pzclub.cn';

    public function get_terminal_info()
    {
        $timestamp = time();
        $token =

        $user_number    = tools_helper::Get('business_code', 'beijing_yyt');

        $request_data = array(
                'appid'       => $this->secret['appid'],
                'token'       => $this->get_secret($timestamp),
                'timestamp'   => $timestamp,
                'business_code' => $user_number,
        );

        $url = $this->pzclub_url."/api/dm/terminal/get_terminal_info";

        $result = curl_post($url, $request_data);
        //p(json_encode($request_data));
        p($result);
    }

    /**
     * 生成秘钥
     * @param int    $appid
     * @param string $timestamp
     * @param string $token
     */
    private function get_secret($timestamp)
    {
        //判断token
        $token = md5($this->secret['appid'].'_'.$this->secret['appkey'].'_'.$timestamp);
        return $token;

    }
}