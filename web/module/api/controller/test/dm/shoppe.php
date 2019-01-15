<?php
/**
  * alltosun.com 专柜操作 shoppe.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年8月16日 下午6:59:10 $
  * $Id$
  */
class Action
{
    public $pzclub_url = SITE_URL;
    //public $pzclub_url = 'http://wifi.pzclub.cn';

    //秘钥 复制的

    private $secret = array(
            'appid'  => 'wifi_shujdt_awzdxhyadrtggbrd',
            'appkey' =>  'd1cb99814ddc2d11cdd8c099b6e5c6e8',
    );

    /**
     * 添加专柜
     */
    public function add()
    {
        $timestamp = time();
        $token = $this->get_secret($timestamp);

        $user_number    = tools_helper::Get('user_number', '1101021002051');
        $phone_name     = tools_helper::Get('phone_name', '三星');
        $shoppe_name    = tools_helper::Get('shoppe_name', '三星专柜二十一');

        $request_data = array(
                'user_number' => $user_number,
                'phone_name' => $phone_name,
                'shoppe_name' => $shoppe_name
        );

        $url = $this->pzclub_url."/api/dm/shoppe/add?appid={$this->secret['appid']}&token={$token}&timestamp={$timestamp}";

        $result = curl_post($url, json_encode($request_data));
        //p(json_encode($request_data));
        p($result);
    }

    /**
     * 删除专柜
     */
    public function delete()
    {

        //$user_number    = tools_helper::Get('user_number', '1101021002051');
        $shoppe_id      = tools_helper::Get('shoppe_id', 0);

        $timestamp = time();

        $token = $this->get_secret($timestamp);

        $url = $this->pzclub_url."/api/dm/shoppe/delete?appid={$this->secret['appid']}&token={$token}&timestamp={$timestamp}";

        $request_data = array(
                'shoppe_id'         => $shoppe_id
        );

        $result = curl_post($url, json_encode($request_data));

        p($result);
    }

    /**
     * 更新专柜
     */
    public function update()
    {
        $user_number = tools_helper::get('user_number', '1101021002051');
        $phone_name  = tools_helper::get('phone_name', '华为');
        $shoppe_name = tools_helper::get('shoppe_name', '华为专柜二');
        $shoppe_id   = tools_helper::get('shoppe_id', 5);

        $timestamp = time();

        $token = $this->get_secret($timestamp);

        $url = $this->pzclub_url."/api/dm/shoppe/update?appid={$this->secret['appid']}&token={$token}&timestamp={$timestamp}";

        $request_data = array(
                'shoppe_id'     => $shoppe_id,
                'user_number'   => $user_number,
                'phone_name'    => $phone_name,
                'shoppe_name'   => $shoppe_name,
        );

        $result = curl_post($url, json_encode($request_data));
        p(json_encode($request_data));
        p($result);

    }



    /**
     * 查询专柜
     */
    public function query()
    {

        //$user_number    = tools_helper::Get('user_number', '1101021002051');
        $shoppe_id      = tools_helper::Get('shoppe_id', 0);

        $timestamp = time();

        $token = $this->get_secret($timestamp);

        $url = $this->pzclub_url."/api/dm/shoppe/query?appid={$this->secret['appid']}&token={$token}&timestamp={$timestamp}";

        $request_data = array(
                'shoppe_id'         => $shoppe_id
        );

        $result = curl_post($url, json_encode($request_data));

        p($result);
    }

    /**
     * 查询专柜列表
     */
    public function query_list()
    {

        $user_number    = tools_helper::Get('user_number', '1101021002051');

        $timestamp = time();

        $token = $this->get_secret($timestamp);

        $url = $this->pzclub_url."/api/dm/shoppe/query_list?appid={$this->secret['appid']}&token={$token}&timestamp={$timestamp}";

        $request_data = array(
                'user_number'         => $user_number
        );

        $result = curl_post($url, json_encode($request_data));
        p($result);
        //p(json_decode($result, true));
    }

    /**
     * 查询专柜列表
     */
    public function query_shoppe_rfid_list()
    {

        $user_number    = tools_helper::Get('user_number', '1101021002051');
        $shoppe_id    = tools_helper::Get('shoppe_id', 18);

        $timestamp = time();

        $token = $this->get_secret($timestamp);

        $url = $this->pzclub_url."/api/dm/shoppe/query_shoppe_rfid_list?appid={$this->secret['appid']}&token={$token}&timestamp={$timestamp}";

        $request_data = array(
                'user_number'         => $user_number,
                'shoppe_id'           => $shoppe_id
        );

        $result = curl_post($url, json_encode($request_data));

        p($result);
    }

    /**
     * 生成专柜后缀
     */
    public function generate_postfix()
    {

        $user_number = tools_helper::get('user_number', '1101021002051');
        $phone_name  = tools_helper::get('phone_name', '华为');
        $shoppe_name = tools_helper::get('shoppe_name', '华为专柜');

        $timestamp = time();

        $token = $this->get_secret($timestamp);

        $url = SITE_URL."/api/dm/shoppe/generate_postfix?appid={$this->secret['appid']}&token={$token}&timestamp={$timestamp}";

        $request_data = array(
                'user_number'   => $user_number,
                'phone_name'    => $phone_name,
                'shoppe_name'   => $shoppe_name,
        );

        $result = curl_post($url, json_encode($request_data));
        p(json_encode($request_data));
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