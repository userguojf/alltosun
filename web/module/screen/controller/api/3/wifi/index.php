<?php

/**
 * alltosun.com 营业停wifi密码 index.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年7月31日 下午4:13:07 $
 * $Id$
 */

class Action
{
    public function add_wifi()
    {
        $user_number        = tools_helper::post('user_number', '');
        $device_unique_id   = tools_helper::post('device_unique_id', '');
        $wifi_user_name     = tools_helper::post('wifi_user_name', '');
        $wifi_pwd           = tools_helper::post('wifi_pwd', '');

        // 验证接口
        $check_params = array(
        );

        $api_log_id = api_helper::check_sign($check_params, 0);

        if (!$user_number) {
            //api_helper::return_api_data(1003, '请输入营业厅的视图编码');
            api_helper::return_api_data(1003, '请输入营业厅的视图编码', array(), $api_log_id);
        }

        if (!$device_unique_id) {
            //api_helper::return_api_data(1003, '请输入营业厅的视图编码', array(), $api_log_id);api_helper::return_api_data(1003, '手机唯一标识不能为空');
            api_helper::return_api_data(1003, '请输入营业厅的视图编码', array(), $api_log_id);
        }

        if (!$wifi_user_name) {
            //api_helper::return_api_data(1003, '请输入wifi账号');
            api_helper::return_api_data(1003, '请输入wifi账号', array(), $api_log_id);
        }
        if (!$wifi_pwd) {
            //api_helper::return_api_data(1003, '请输入wifi密码');
            api_helper::return_api_data(1003, '请输入wifi密码', array(), $api_log_id);
        }

        $business_info  = business_hall_helper::get_info('business_hall', array('user_number'=>$user_number));
        //an_dump($business_info);

        if (!$business_info) {
            //api_helper::return_api_data(1003, '营业厅不存在');
            api_helper::return_api_data(1003, '营业厅不存在', array(), $api_log_id);
        }

        $device_info = screen_device_helper::get_device_info_by_device($device_unique_id);
        if (!$device_info) {
            api_helper::return_api_data(2003, '设备不存在或已被下架', array(), $api_log_id);
        }

        $info  = array(
            'province_id'   => $business_info[0]['province_id'],
            'city_id'       => $business_info[0]['city_id'],
            'area_id'       => $business_info[0]['area_id'],
            'business_id'   => $business_info[0]['id'],
            'device_unique_id' => $device_unique_id,
            'status'        => 1
        );

        $wifi_info = _uri('screen_business_wifi_pwd', $info);

        if ($wifi_info) {
            _model('screen_business_wifi_pwd')->update($wifi_info['id'], array('user_name' => $wifi_user_name, 'password' => $wifi_pwd));
        } else {

            $info['user_name'] = $wifi_user_name;
            $info['password']  = $wifi_pwd;
            _model('screen_business_wifi_pwd')->create($info);
        }

        api_helper::return_api_data(1000, 'success', array('info'=>'ok'), $api_log_id);
    }

//     /**
//      * 获取设备wifi信息
//      */
//     public function get_device_wifi_info()
//     {
//         $user_number    = tools_helper::post('user_number', '');
//         $phone_imei     = tools_helper::post('phone_imei', '');

//         // 验证接口
//         $check_params = array(
//         );

//         $api_log_id = api_helper::check_sign($check_params, 0);

//         if (!$user_number) {
//             api_helper::return_api_data(1003, '请输入营业厅的视图编码');
//         }

//         if (!$phone_imei) {
//             api_helper::return_api_data(1003, '设备imei不能为空');
//         }

//         $imei  = screen_helper::device_decode($phone_imei);

//         $business_info  = business_hall_helper::get_info('business_hall', array('user_number'=>$user_number));
//         //an_dump($business_info);

//         if (!$business_info) {
//             api_helper::return_api_data(1003, '营业厅不存在');
//         }

//         $wifi_info = _model('screen_business_wifi_pwd')->read(array('imei' => $imei, 'business_id' => $business_info[0]['id']), ' ORDER BY id ');

//         if ($wifi_info) {
//             api_helper::return_api_data(1000, 'success', array('user_name' => $wifi_info['user_name'], 'password' => $wifi_info['password']), $api_log_id);
//         }

//         api_helper::return_api_data(1004, 'wifi信息不存在');
//     }

    public function get_device_wifi_info()
    {
        $user_number    = tools_helper::post('user_number', '');
        // 验证接口
        $check_params = array(
        );

        $api_log_id = api_helper::check_sign($check_params, 0);

        if (!$user_number) {
            //api_helper::return_api_data(1003, '请输入营业厅的视图编码');
            api_helper::return_api_data(1003, '请输入营业厅的视图编码', array(), $api_log_id);
        }

        $business_info  = business_hall_helper::get_info('business_hall', array('user_number'=>$user_number));

        if (!$business_info) {
            //api_helper::return_api_data(1003, '营业厅不存在');
            api_helper::return_api_data(1003, '营业厅不存在', array(), $api_log_id);
        }

        $info  = array(
            'business_id'   => $business_info[0]['id'],
            'status'        => 1
        );

        $wifi_list = _model('screen_business_wifi_pwd')->getList($info);

        $result  = array();

        $wifi_name = array();
        foreach ($wifi_list as $v)
        {
            if (!isset($wifi_name[$v['user_name']])) {
                $result[] = array(
                        'user_name' => $v['user_name'],
                        'password'  => $v['password']
                );
                $wifi_name[$v['user_name']] = $v['password'];
            }
        }

        if ($result) {
            api_helper::return_api_data(1000, 'success', $result, $api_log_id);
        }

        //api_helper::return_api_data(1004, 'wifi信息不存在');
        api_helper::return_api_data(1003, 'wifi信息不存在', array(), $api_log_id);
    }
}