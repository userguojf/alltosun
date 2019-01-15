<?php

/**
 * alltosun.com  index.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年6月29日 上午11:22:30 $
 * $Id$
 */

class Action
{
    public function add_device_info()
    {
        $user_number  = tools_helper::post('user_number', '');
        $phone_imei   = tools_helper::post('phone_imei', '');
        $phone_name   = tools_helper::post('phone_name', '');
        $phone_version = tools_helper::post('phone_version', '');

        // 验证接口
        $check_params = array(
            //'user_number'  => $user_number
        );

        $api_log_id = api_helper::check_sign($check_params, 0);

        if (!$user_number) {
            api_helper::return_api_data(1003, '请输入营业厅的视图编码');
        }
//         if (!$phone_imei) {
//             api_helper::return_api_data(1003, '请输入手机imei');
//         }
        if (!$phone_name) {
            api_helper::return_api_data(1003, '请输入手机品牌');
        }
        if (!$phone_version) {
            api_helper::return_api_data(1003, '请输入手机型号');
        }

        $business_info  = business_hall_helper::get_info('business_hall', array('user_number'=>$user_number));
        //an_dump($business_info);

        if (!$business_info) {
            api_helper::return_api_data(1003, '营业厅不存在');
        }

        if (!$phone_imei) {
            $phone_imei = screen_helper::build_imei($user_number, $phone_name, $phone_version);
        }

        $info  = array(
            'province_id'   => $business_info[0]['province_id'],
            'city_id'       => $business_info[0]['city_id'],
            'area_id'       => $business_info[0]['area_id'],
            'business_id'   => $business_info[0]['id'],
            'imei'          => $phone_imei,
            'phone_name'    => $phone_name,
            'phone_version' => $phone_version,
            'day'           => date("Ymd"),
            'phone_name_nickname'    => screen_helper::display_phone_name_nickname($phone_name),
            'phone_version_nickname' => screen_helper::display_phone_version_nickname($phone_name, $phone_version)
        );

        $param  = array(
            'type'        => 'create',
            'user_number' => $user_number,
            'imei'        => $phone_imei
        );
        //an_dump($info); exit;
        $flag  = screen_helper::add_screen_device($info);

        screen_helper::dm_create_app_log($param);
        if ($flag != 'ok') {
            api_helper::return_api_data(1003, $flag);
        }

        $result = array(
            'info'=>'ok',
            'imei' => screen_helper::device_encode($phone_imei)
        );
        api_helper::return_api_data(1000, 'success', $result, $api_log_id);
    }

    public function get_device_id()
    {
        $user_number  = tools_helper::post('user_number', '');
        $phone_imei   = tools_helper::post('phone_imei', '');
        $phone_name   = tools_helper::post('phone_name', '');
        $phone_version = tools_helper::post('phone_version', '');

        // 验证接口
        $check_params = array(
            //'user_number'  => $user_number
        );

        $api_log_id = api_helper::check_sign($check_params, 0);

        if (!$user_number) {
            api_helper::return_api_data(1003, '请输入营业厅的视图编码');
        }
        if (!$phone_name) {
            api_helper::return_api_data(1003, '请输入手机品牌');
        }
        if (!$phone_version) {
            api_helper::return_api_data(1003, '请输入手机型号');
        }

        $business_info  = _uri('business_hall', array('user_number'=>$user_number));

        if (!$business_info) {
            api_helper::return_api_data(1003, '营业厅不存在');
        }


        if (!$phone_imei) {
            $phone_imei = screen_helper::build_imei($user_number, $phone_name, $phone_version);
        }

        $info  = array(
            'province_id'  => $business_info['province_id'],
            'city_id'      => $business_info['city_id'],
            'area_id'      => $business_info['area_id'],
            'business_id'  => $business_info['id'],
            'imei'         => $phone_imei,
            'phone_name'   => $phone_name,
            'phone_version' => $phone_version,
        );

        $device_info   = _model('screen_device')->read($info);
        //an_dump($device_info);
        $imei    = '';
        if ($device_info) {
            $imei  = screen_helper::device_encode($device_info['imei']);
        }

        $result = array('device_id'=>$imei);

        api_helper::return_api_data(1000, 'success', $result, $api_log_id);
    }

    /**
     * 获取设备详情
     */
    public function get_device_info()
    {
        $device_unique_id = tools_helper::post('device_unique_id', '');

        // 验证接口
        $check_params = array(
                //'user_number'  => $user_number
        );

        $api_log_id = api_helper::check_sign($check_params, 0);

        if (!$device_unique_id) {
            api_helper::return_api_data(1003, '设备唯一标识不能为空', array(), $api_log_id);
        }

        //未下架的设备
        $device_info = screen_device_helper::get_device_info_by_device($device_unique_id);

        if (!$device_info) {
            //包含下架的设备
            $device_info = _model('screen_device')->read(array('device_unique_id' => $device_unique_id), ' ORDER BY `id` DESC LIMIT 1 ');
        }


        if (!$device_info) {
            api_helper::return_api_data(2003, '设备不存在', array(), $api_log_id);
        }

        $user_number = business_hall_helper::get_business_hall_info($device_info['business_id'], 'user_number');

        if (!$user_number) {
            api_helper::return_api_data(1003, '营业厅不存在', array(), $api_log_id);
        }


        $device_info['user_number'] = $user_number;

        api_helper::return_api_data(1000, 'success', $device_info, $api_log_id);

    }
}