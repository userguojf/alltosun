<?php

/**
 * alltosun.com api index.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: Shenxn 申小宁 (shenxn@alltosun.com) $
 * $Date: May 16, 2014 12:55:33 PM $
 * $Id: index.php 231213 2015-04-28 07:39:57Z leijx $
 */
class Action
{
    private $app_id = 'af93510d';
    private $app_key = '2e76277aae45';

    private function api_return($code)
    {
        Response::assign('code', $code);
        Response::display('api_return.html');
    }

    public function login()
    {
        $params   = tools_helper::get('params', '');
        $app_id   = tools_helper::get('appid', '');
        $all_msgs = $_GET;
        /* 
            Array
            (
                [anu]  => /equip/admin/check/time_test
                [id]   => 123
                [user] => user
            )
        */
        _model('first_login_logs')->create(
                        array(
                            'params'     => $params,
                            'app_id'     => $app_id,
                            'accept_msg' => json_encode($all_msgs)
                        )
        );
        
        $client_ip = Request::getClientIp();
        $client_rf = Request::getRf();
        $timestamp = time();

        if ($app_id == 'wifi_js_kyql40ekax9bxvqu') {
            $timestamp = $timestamp.'000';
            $token = strtoupper(md5($timestamp.'|'.'10DCDCDC6CB60E4457195E7EAB91A193'));

            if (ONDEV) {
                $url = 'http://60.191.53.35:8760/shopportal/openapi/getShopInfo';
            } else {
                $url = 'http://portal.awifi.cn/shopportal/openapi/getShopInfo';
            }

            $data['appId']     = '10DCDCDC6CB60E4457195E7EAB91A193';
            $data['timestamp'] = $timestamp;
            $data['token']     = $token;
            $data['params']    = $params;

            $result_json = curl_post($url, $data);
        } else if ($app_id == 'wifi_fj_kyql40ekax9bxbso') {
            $timestamp = $timestamp.'000';
            $token = strtoupper(md5("{$app_id}{$timestamp}"));

            $url = 'http://fj.iwififree.com:8010/wifiPortal/eapi/app/paramdecrypt.jhtml';

            $data['appid']     = $app_id;
            $data['timestamp'] = $timestamp;
            $data['token']     = $token;
            $data['params']    = $params;

            $result_json = curl_get($url, $data);

        } else if ($app_id == 'wifi_sh_xi0Gz7hb7yuwtTo9') {

            //$url= 'http://101.95.34.50:8006/grouphallquery';  wangjf 替换正式地址
            $url = 'http://101.95.34.49:9006/grouphallquery';
            $data['appid']     = $app_id;
            $appkey = '4c6726764d191f10';
            $data['timestamp'] = $timestamp;
            $token = strtoupper(md5($app_id.'_'.$appkey.'_'.$timestamp));

            $data['token']     = $token;
            $data['params']    = $params;

            $result_json = curl_get($url, $data);

        } else {
            $token   = md5($this->app_id.'_'.$this->app_key.'_'.$timestamp);
            $url = api_config::$login_url;
            $url .= "?token={$token}&timestamp={$timestamp}&appid={$this->app_id}&params={$params}";

            $result_json = curl_get($url);
        }

        $result = json_decode($result_json, true);

        //默认的记录日志的数组
        $return_filter = array(
                'type'      => 1,
                'app_id'    => $app_id,
                'params'    => $params,
                'content'   => $result_json,
                'client_ip' => $client_ip,
                'client_rf' => $client_rf,
                'status'    => 0
        );

        //$result['type'] = 2;
        //_widget('monitor')->record_api_log($result);

        if (!isset($result['result']) || $result['result'] != 'OK') {
            //记录失败日志
            _model('api_login_logs')->create($return_filter);
            $this->api_return(10019);
            return ;
        } else {
            //记录成功日志
            $return_filter['status'] = 1;
            //创建
            _model('api_login_logs')->create($return_filter);
        }

        if (!isset($result['params']) || !$result['params']) {
            $this->api_return(10020);
            return ;
        }

        if (!isset($result['params']['customerId']) || !$result['params']['customerId']) {
            $this->api_return(10021);
            return ;
        }

        if (!isset($result['params']['userPhone']) || !$result['params']['userPhone']) {
            $this->api_return(10022);
            return ;
        }

        $user_login_data =  $user_res_date =  array();

        $user_login_data['phone']             = $result['params']['userPhone'];
        $wifi_res_id                          = $result['params']['customerId'];

        //检测营业厅
        $filter = array('wifi_res_id' => $wifi_res_id);
        $business_hall_info = business_hall_helper::get_business_hall_info($filter);

        if (!$business_hall_info) {
            $this->api_return(10023);
            return ;
        }

        $user_login_data['business_hall_id']  = $business_hall_info['id'];
        $user_res_date['ip']                  = isset($result['params']['userIp']) ? $result['params']['userIp'] : '';
        $user_res_date['mac']                 = $result['params']['devMac'];
        $user_res_date['terminal_type']       = $result['params']['terminalType'];
        $user_res_date['device_id']           = $result['params']['deviceId'];

        //成功,可以登录
        $user_id = user_helper::create_user_info($user_login_data, $user_res_date);

        if (!$user_id) {
            $this->api_return(10024);
            return ;
        }
//

        integral_helper::api_get_integral($user_login_data['phone']);
        //integral_helper::api_get_integral($phone);

        //清除8小时自动登录
        user_helper::remember_me_expire();

        user_helper::set_mc_login_info(
                array('user_id' => $user_login_data['phone'], 'business_hall_title' => $business_hall_info['title'])
        );

        //登录
        $user_info  = user_helper::get_user_info($user_id);
        user_helper::remember_me_set($user_info);

        //        青海果洛226 云南怒江314 辽宁阜新201、朝阳198、本溪197  山西大同252  河北衡水93

        Response::redirect(AnUrl());

        //         if (in_array($business_hall_info['city_id'], array('226','314','201','198','197','252','93'))) {
        //             Response::redirect(AnUrl());
        //         } else {
        //             Response::redirect(AnUrl('wheel/ggk'));
        //         }
        //         Response::flush();
        //         return;
    }

    public function get_background_res()
    {
        header("Content-type: text/html; charset=utf-8");

        $params = AnFilter::filter_string(Request::getParam('params', ''));
        $app_id     = tools_helper::get('appid', '');
        $client_ip = Request::getClientIp();
        $client_rf = Request::getRf();
        $timestamp = time();

        if ($app_id == 'wifi_js_kyql40ekax9bxvqu') {
            $timestamp = $timestamp.'000';
            $token = strtoupper(md5($timestamp.'|'.'10DCDCDC6CB60E4457195E7EAB91A193'));

            if (ONDEV) {
                $url = 'http://60.191.53.35:8760/shopportal/openapi/getShopInfo';
            } else {
                $url = 'http://portal.awifi.cn/shopportal/openapi/getShopInfo';
            }

            $data['appId']     = '10DCDCDC6CB60E4457195E7EAB91A193';
            $data['timestamp'] = $timestamp;
            $data['token']     = $token;
            $data['params']    = $params;

            $result_json = curl_post($url, $data);
        } else if ($app_id == 'wifi_fj_kyql40ekax9bxbso') {
                $timestamp = $timestamp.'000';
                $token = strtoupper(md5("{$app_id}{$timestamp}"));

                $url = 'http://fj.iwififree.com:8010/wifiPortal/eapi/app/paramdecrypt.jhtml';

                $data['appid']     = $app_id;
                $data['timestamp'] = $timestamp;
                $data['token']     = $token;
                $data['params']    = $params;

                $result_json = curl_get($url, $data);
        }else if ($app_id == 'wifi_sh_xi0Gz7hb7yuwtTo9') {
                //$url= 'http://101.95.34.50:8006/grouphallquery';  wangjf 替换为正式地址
                $url = 'http://101.95.34.49:9006/grouphallquery';
                $data['appid']     = $app_id;
                $appkey = '4c6726764d191f10';
                $data['timestamp'] = $timestamp;
                $token = strtoupper(md5($app_id.'_'.$appkey.'_'.$timestamp));

                $data['token']     = $token;
                $data['params']    = $params;

                $result_json = curl_get($url, $data);
        } else {
            $token   = md5($this->app_id.'_'.$this->app_key.'_'.$timestamp);

            if (ONDEV){
                $url = "http://beta-toe.51awifi.com/eapi/app/paramdecrypt?token={$token}&timestamp={$timestamp}&appid={$this->app_id}&params={$params}";
            } else {
                $url = "http://toe.51awifi.com/eapi/app/paramdecrypt?token={$token}&timestamp={$timestamp}&appid={$this->app_id}&params={$params}";
            }

            $result_json = curl_get($url);
        }

        $result = json_decode($result_json,true);

//edited by guojf start
        //默认的记录日志的数组
        $return_filter = array(
                'type'      => 2,
                'app_id'    => $app_id,
                'params'    => $params,
                'content'   => $result_json,
                'client_ip' => $client_ip,
                'client_rf' => $client_rf,
                'status'    => 0
        );
        //判断
        if (!isset($result['result']) || $result['result'] != 'OK') {
            //记录失败日志
            _model('api_login_logs')->create($return_filter);
        } else {
            //记录成功日志
            $return_filter['status'] = 1;
            //创建
            _model('api_login_logs')->create($return_filter);
        }
//end
        $exit_result = array(
            'result'  => 'FAIL',
            'message' => '',
            'data'    => ''
        );

        //$result = json_decode($result_json,true);

        //customerId
        if (!isset($result['params']['customerId']) || !$result['params']['customerId']) {
            $exit_result['message'] = 'customerId NOT EXIST!';

            exit(json_encode($exit_result));
        }

        $wifi_res_id = $result['params']['customerId'];

        //检测营业厅
        $filter = array('wifi_res_id' => $wifi_res_id);
        $business_hall_info = business_hall_helper::get_business_hall_info($filter);

        if (!$business_hall_info) {
            $exit_result['message'] = 'customerInfo NOT EXIST!';

            exit(json_encode($exit_result));
        }

        //全国
        $bgmap_ids = _model('bgmap_res')->getFields(
            'bgmap_id',
            array(
                'res_name' => 'group'
            )
        );

        $bgmap_info = $this->get_bgmap_info($bgmap_ids);

        if ($bgmap_info) {
            $bg_image = _image($bgmap_info['cover']);


            global $mc_wr;

            $image_data = $mc_wr->get('bg_image');

            if (!$image_data) {
                $image_data = file_get_contents($bg_image);
                $mc_wr ->set('bg_image', $image_data, 600);
            }

//             echo '{"result":"OK","message":"","data":"'.$data.'"}';
            exit($image_data);
        }

        //全国
        $bgmap_ids = _model('bgmap_res')->getFields(
                'bgmap_id',
                array(
                    'res_name' => 'province',
                    'res_id'   => $business_hall_info['province_id']
                )
        );

        $bgmap_info = $this->get_bgmap_info($bgmap_ids);

        if ($bgmap_info) {
            $bg_image = _image($bgmap_info['cover']);

//             echo '{"result":"OK","message":"","data":"'.$data.'"}';
            exit(file_get_contents($bg_image));
        }

        $exit_result['result']  = 'FAIL';
        $exit_result['message'] = 'NOT BGIMAGE !';
        $exit_result['data']    = '';

        //报警
        exit(json_encode($exit_result));
    }

    private function get_bgmap_info($bgmap_ids)
    {
        if (!$bgmap_ids) {
            return false;
        }

        $time = date('Y-m-d H:i:s');

        //查询
        $bgmap_info = _model('bgmap')->read(
            array(
                'id' => $bgmap_ids,
                'start_time <=' => $time,
                'end_time >=' => $time,
                'status'   => 1
                ),
            ' ORDER BY `id` DESC '
        );

        return $bgmap_info;
    }

    public function get_background_css_type()
    {
        $result = array(
                'css_type' => '1'
        );

        exit(json_encode($result));
    }

    public function get_background_css()
    {
        $data = file_get_contents(SITE_URL.'/css/m-login.css');
        $data1 = file_get_contents(SITE_URL.'/css/base.css');

        $result = array(
                'result'   => 'OK',
                'message'  => '',
                'data'     => $data.$data1
        );

        exit(json_encode($result));
    }

    public function get_data()
    {
        $page = Request::Get('page', 1);

        $url = 'http://toe.51awifi.com/eapi/customer/list?';
        $serarch_filter['appid']      = $this->app_id;
        $serarch_filter['timestamp']  = time();
        $serarch_filter['token']      = md5($this->app_id.'_'.$this->app_key.'_'.$serarch_filter['timestamp']);
        $serarch_filter['projectId']  = '30bc9ef333b30763';
        $serarch_filter['pageNo']     = $page;
        $serarch_filter['pageSize']   = 50;

        $url .= http_build_query($serarch_filter);

        $result = curl_get($url);

        $result = json_decode($result, true);

        $list = $result['records'];

        if (!$list) {
            echo '已经全部抓取！';
            exit();
        }

      foreach ($list as $v) {
          _model('business_hall')->update(
                array('wifi_res_id' => $v['customerId']),
                array('address' => $v['address'])
          );
      }

        ++$page;

       $url = AnUrl("api/get_data?page={$page}");
       echo "<script> window.location.href = '{$url}';</script>";
       exit();
    }

    public function set_business_hall_pass()
    {
    }

    public function get_business_hall_pass()
    {
        set_time_limit(0);
        $page = Request::Get('page', 0);
        $page_num = $page * 3000;

        $business_hall_list = _model('business_halls')->getList(
                array('id >'=> 0),
                " ORDER BY `id` ASC LIMIT {$page_num},3000 "
        );

        foreach ($business_hall_list as $v) {
            _model('business_hall')->update(
                array('wifi_res_id' => $v['wifi_res_id']),
                array('user_number' => $v['user_number'])
            );
        }

        ++$page;

        $url = AnUrl("api/get_business_hall_pass?page={$page}");
        echo "<script> window.location.href = '{$url}';</script>";
        exit();
    }

    public function get_business_hall_member()
    {
        set_time_limit(0);

        $page = Request::Get('page', 0);
        $page_num = $page * 3000;

        $business_hall_list = _model('business_hall')->getList(
                array('id >'=> 0),
                " ORDER BY `id` ASC LIMIT {$page_num},3000 "
        );

        foreach ($business_hall_list as $v) {
            $member_id = _model('member')->create(
                array(
                    'member_user' => $v['user_number'],
                    'member_pass' => md5(123456),
                    'ranks' => 5,
                    'res_name' => 'business_hall',
                    'res_id' => $v['id'],
                    'hash'   => uniqid()
                )
            );

            _model('group_user')->create(array(
                'member_id' => $member_id,
                'group_id'  => 26
            ));
        }

        ++$page;

        $url = AnUrl("api/get_business_hall_member?page={$page}");
        echo "<script> window.location.href = '{$url}';</script>";
        exit();
    }

    public static function login_test_2()
    {
        $params = 'eyJkZXZJcCI6IjQ5LjY3LjYwLjI0MiIsImRldk1hYyI6IjcwOjcyOkNGOkU0OjE0OkUyIiwidXNlck1hYyI6IkI4NzYzRkQyOEQwNyAiLCJjdXN0b21lcklkIjoiMTA0MTQxIiwidGVybWluYWxUeXBlIjoiaW9zIiwidXNlclBob25lIjoiMTMzMDExNjM1ODAgIiwiZGV2aWNlSWQiOiI3MDcyQ0ZFNDE0RTIifQ==';
        $appid = 'wifi_js_kyql40ekax9bxvqu';

        p(curl_post('201512awifi.alltosun.net/api/login',array('params' => $params, 'appId' => $appid)));

    }

}
?>