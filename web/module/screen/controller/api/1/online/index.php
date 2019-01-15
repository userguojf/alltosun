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
 * $Date: 2017年7月4日 下午7:15:24 $
 * $Id$
 */

class Action
{
    public function add()
    {
        $user_number = tools_helper::post('user_number', '');
        $imei        = tools_helper::post('phone_imei', '');
        $mac         = tools_helper::post('mac', '');

        $check_params = array(
            //'user_number' => $user_number
        );

        $api_log_id = api_helper::check_sign($check_params, 0);

        if (!$user_number) {
            api_helper::return_api_data(1003, '请输入营业厅编码');
        }
//         if (!$imei) {
//             api_helper::return_api_data(1003, '请输入手机id');
//         }

        $filter = array();
        if ($imei) {
            $imei  = screen_helper::device_decode($imei);
            $filter['imei'] = $imei;
        } 
        
        if ($mac) {
            $filter['mac']  = $mac;
        }

        $device_info = _uri('screen_device', $filter);
        if (!$device_info) {
            api_helper::return_api_data(1003, '暂无相关信息');
        }

        $business_info  = business_hall_helper::get_info('business_hall', array('user_number'=>$user_number));

        if (!$business_info) {
            api_helper::return_api_data(1003, '营业厅不存在');
        }

        //an_dump($business_info);
        $info  = array(
            'province_id'  => $business_info[0]['province_id'],
            'city_id'      => $business_info[0]['city_id'],
            'area_id'      => $business_info[0]['area_id'],
            'business_id'  => $business_info[0]['id'],
            'imei'         => $device_info['imei'],
            'day'          => date("Ymd")
        );

        $online_id        = _model('screen_device_online')->create($info);

        screen_helper::add_device_online_stat_day($info);

        $result = array(
            'online_id' => $online_id
        );
        api_helper::return_api_data(1000, 'success', $result, $api_log_id);
    }
}