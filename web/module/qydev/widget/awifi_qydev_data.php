<?php
/**
 * alltosun.com 从WiFi平台同步过来企业号的成员信息接口 awifi_qydev_data.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-10-17 下午4:42:01 $
 * $Id$
 */
require ROOT_PATH.'/helper/AnCurl.php';

class awifi_qydev_data_widget
{
    private $appid  = 'wifi_mac_awzdxhyakax9b1rw';
    private $appkey = 'qra4s9w2badfde0f3665dsf28322b4b2';

    public function get_awifi_qydev_data()
    {
        set_time_limit(0);

        $qydev_info = _model('public_contact_user')->read(array(1 => 1 ), " ORDER BY `id` DESC ");

        $new_data   = $this->get_create_data_api($qydev_info['id']);

        if (isset($new_data['errcode']) && $new_data['errcode']) {
            p($new_data);
            exit();
        }
        //防止添加了字段报错
        $param = [];

        foreach ($new_data['data'] as $k => $v) {
            $param = array(
                         'res_name'    => $v['res_name'],
                         'type'        => $v['type'], 
                         'user_number' => $v['user_number'],
                         'user_name'   => $v['user_name'],
                         'user_phone'  => $v['user_phone'],
                         'from_id'     => $v['from_id'],
                         'api_from'    => $v['api_from'],
                         'user_type'   => $v['tuser_typeype'],
                         'unique_id'   => $v['unique_id'],
                         'analog_i'   => $v['analog_i'],
                         'an_id'       => $v['an_id'],
                         'extra'       => $v['extra'],
            );

            _model('public_contact_user')->create($param);
        }

        exit('本次更新结束');
    }

    public function get_create_data_api($id)
    {

        if (ONDEV) {
            $url = 'http://201512awifi.alltosun.net/qydev/mac';
        } else {
            $url = "http://wifi.pzclub.cn/qydev/mac";
        }

        $timestamp = time();

        $data = array(
                'appid'     => $this->appid,
                'timestamp' => $timestamp,
                'token'     =>  md5($this->appid.'_'.$this->appkey.'_'.$timestamp),
                'id'        => $id,
        );

        $curl = new AnCurl();
        $json = $curl -> post($url, $data);

        return json_decode($json, true);
    }

    public function get_wifi_delete_data()
    {
        $delete_data = $this->get_delete_data_api();

        foreach ($delete_data as $k => $v) {
            _model('public_contact_user')->delete(array('user_name' => $v['mobile'], 'unique_id' => $v['userid']));
        }
    }

    public function get_delete_data_api()
    {
        if (ONDEV) {
            $url = 'http://201512awifi.alltosun.net/qydev/mac/supply_mac_delete_info';
        } else {
            $url = "http://wifi.pzclub.cn/qydev/mac/supply_mac_delete_info";
        }

        $timestamp = time();

        $data = array(
                'appid'     => $this->appid,
                'timestamp' => $timestamp,
                'token'     => md5($this->appid.'_'.$this->appkey.'_'.$timestamp),
        );

        $curl = new AnCurl();
        $json = $curl -> post($url, $data);

        return json_decode($json, true);
    }

    public function test()
    {
        require ROOT_PATH.'/helper/AnCurl.php';

        $id = 53133;

        $timestamp = time();

        $data = array(
                'appid'     => $this->appid,
                'timestamp' => $timestamp,
                'token'     => md5($this->appid.'_'.$this->appkey.'_'.$timestamp),
                'id'        => $id,
        );

        $curl = new AnCurl();
        $res  = $curl -> post('http://wifi.pzclub.cn/qydev/mac', $data);

        echo $res;
    }
}