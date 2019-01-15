<?php
/**
  * alltosun.com 探针接口 probe.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2018年3月23日 上午11:12:43 $
  * $Id$
  */
class Action
{
private $secret = array(
            'appid'  => 'wifi_paidui_e084485fu23h7hf8',
            'appkey' =>  'b9aca85c819a8d59b1277d71da49b068',
    );

    //public $pzclub_url = SITE_URL;
    public $pzclub_url = 'http://mac.pzclub.cn';

    public function get_remain_time()
    {
        $user_number    = tools_helper::Get('business_code', '1101081002057');
        $timestamp = time();
        $request_data = array(
                'appid'       => $this->secret['appid'],
                'token'       => $this->get_secret($timestamp),
                'timestamp'   => $timestamp,
                'start_time'    => date('Y-m-d 06:00:00'),
                'end_time'    => date('Y-m-d 22:00:00'),
                'mac'          => 'f8:23:b2:ab:b1:27',
                'toiletId'     => 'c6d019c1-90df-42ae-897a-7e97c89b93481',
        );

        $url = $this->pzclub_url."/api/paidui/probe/get_remain_time";

        $result = curl_post($url, $request_data);
        //p(json_encode($request_data));
        p($result);
    }
    public function test()
    {
        $mac = '4c:49:e3:f5:bb:b7';
        echo probe_helper::mac_decode($mac);
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