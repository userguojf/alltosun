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
 * $Date: 2017年7月18日 上午11:40:20 $
 * $Id$
 */

class Action
{
    public function add_device_info()
    {
        $user_number        = tools_helper::post('user_number', '');
        $phone_imei         = tools_helper::post('phone_imei', 0);
        $phone_name         = tools_helper::post('phone_name', '');
        $phone_version      = tools_helper::post('phone_version', '');
        $phone_mac          = tools_helper::post('phone_mac', '');
        $shoppe_id          = tools_helper::post('shoppe_id', 0);
        $registration_id    = tools_helper::post('registration_id', '');
        $version_no         = tools_helper::post('version', '');

        $api_log_id = api_helper::check_sign(array(), 0);

        if (!$user_number) {
            api_helper::return_api_data(1003, '请输入营业厅的视图编码', array(), $api_log_id);
        }

        if (!$phone_name) {
            api_helper::return_api_data(1003, '请输入手机品牌', array(), $api_log_id);
        }
        $phone_name = strtolower($phone_name);
        if (!$phone_version) {
            api_helper::return_api_data(1003, '请输入手机型号', array(), $api_log_id);
        }
        $phone_version = strtolower($phone_version);
        if (!$phone_imei && !$phone_mac) {
            api_helper::return_api_data(1003, '暂不支持imei和mac都不存在的机型', array(), $api_log_id);
        }

        $business_info  = _uri('business_hall', array('user_number'=>$user_number));

        if (!$business_info) {
            api_helper::return_api_data(1003, '营业厅不存在', array(), $api_log_id);
        }

        //获取设备唯一id
        $device_unique_id = screen_helper::get_device_unique_id($phone_mac, $phone_imei);

        if (!$device_unique_id) {
            api_helper::return_api_data(1003, '唯一ID生成失败', array(), $api_log_id);
        }

        //获取设备昵称
        $nickname_info = screen_device_helper::get_device_nickname($phone_name, $phone_version);

        //添加设备表
        $info  = array(
            'registration_id' => $registration_id,
            'device_unique_id' => $device_unique_id,
            'shoppe_id'     => $shoppe_id,
            'province_id'   => $business_info['province_id'],
            'city_id'       => $business_info['city_id'],
            'area_id'       => $business_info['area_id'],
            'business_id'   => $business_info['id'],
            'version_no'    => $version_no,
            'imei'          => $phone_imei,
            'mac'           => $phone_mac,
            'phone_name'    => $phone_name,
            'phone_version' => $phone_version,
            'day'           => date("Ymd"),
            'phone_name_nickname'    => $nickname_info['name_nickname'],
            'phone_version_nickname' => $nickname_info['version_nickname'],
            'device_nickname_id'     => $nickname_info['device_nickname_id']
        );
        $flag  = screen_helper::add_screen_device($info);

        if ($flag != 'ok') {
            api_helper::return_api_data(1003, $flag, array(), $api_log_id);
        }

        //需返回柜台数量
        $shoppe_list = shoppe_helper::get_business_hall_shoppe('business_hall', $business_info['id']);
        if ($shoppe_list === false) {
            $shoppe_count = 0;
        } else {
            $shoppe_count = count($shoppe_list);
        }

        //wangjf add: 绑定极光推标签
        $tags = array();
        $tags[] = push_helper::get_business_hall_tag($business_info['id']); //厅
        $tags[] = push_helper::get_area_tag($business_info['area_id']); //区
        $tags[] = push_helper::get_city_tag($business_info['city_id']); //市
        $tags[] = push_helper::get_province_tag($business_info['province_id']); //省
        $tags[] = push_helper::get_phone_name_version_tag($phone_name, $phone_version); //机型

        $res = push_helper::binding_tag($registration_id, $tags);

        $result = array(
            'info'=>'ok',
            'device_unique_id' => $device_unique_id,
            'shoppe_count' => $shoppe_count
        );
        api_helper::return_api_data(1000, 'success', $result, $api_log_id);
    }



    /**
     * 获取手机唯一标识id
     * @return $string
     */
    public function get_device_unique_id()
    {
        $phone_imei    = tools_helper::post('phone_imei', '');
        $phone_mac     = tools_helper::post('phone_mac', '');

        // 验证接口
        $check_params = array(
        );

        $api_log_id = api_helper::check_sign($check_params, 0);

        if (!$phone_imei && !$phone_mac) {
            api_helper::return_api_data(1003, '暂不支持imei和mac都不存在的机型', array(), $api_log_id);
        }

        $device_unique_id = screen_helper::get_device_unique_id($phone_mac, $phone_imei);

        if (!$device_unique_id) {
            return api_helper::return_api_data(1003, '获取设备标识失败', array(), $api_log_id);
        }

        //wangjf add 2017-12-22
        $device_info = screen_device_helper::get_device_info_by_device($device_unique_id);

        if (!$device_info) {
            api_helper::return_api_data(2003, '设备不存在或已被下架', array(), $api_log_id);
        }

        api_helper::return_api_data(1000, 'success', array('device_unique_id'=>$device_info['device_unique_id']), $api_log_id);
    }

    public function update_version()
    {
        $device_unique_id       = tools_helper::post('device_unique_id', '');
        $version_no             = tools_helper::post('version_no', '');

        $api_log_id = api_helper::check_sign(array(), 0);

        if (!$device_unique_id) {
            api_helper::return_api_data(1003, '设备唯一标识不能为空', array(), $api_log_id);
        }

        if (!$version_no) {
            api_helper::return_api_data(1003, '请输入版本号', array(), $api_log_id);
        }

        $update = _model('screen_device')->update(array('device_unique_id'=>$device_unique_id), array('version_no'=>$version_no));
        if ($update === false) {
            api_helper::return_api_data(1003, '更新失败', array(), $api_log_id);
        }

        api_helper::return_api_data(1000, 'success', array('info'=>'ok'), $api_log_id);
    }
}