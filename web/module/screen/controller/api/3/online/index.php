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
 * $Date: 2017年7月18日 下午12:39:41 $
 * $Id$
 */

class Action
{
    public function add()
    {
        $user_number      = tools_helper::post('user_number', '');
        $device_unique_id = tools_helper::post('device_unique_id', '');

        $check_params = array(
            //'user_number' => $user_number
        );

        $api_log_id = api_helper::check_sign($check_params, 0);

        if (!$user_number) {
            api_helper::return_api_data(1003, '请输入营业厅编码', array(), $api_log_id);
        }

        if (!$device_unique_id) {
            api_helper::return_api_data(1003, '请输入设备唯一标识', array(), $api_log_id);
        }

        $device_info = screen_device_helper::get_device_info_by_device($device_unique_id);

        if (tools_helper::post('wangjf_debug', 0) == 1) {
            p($device_info);
        }

        if ( !$device_info ) {
            api_helper::return_api_data(2003, '设备不存在或已被下架', array(), $api_log_id);
        }

//         $business_info  = business_hall_helper::get_business_hall_info(array('user_number'=>$user_number));

        $business_info  = _model('business_hall')->read(array('user_number' => $user_number));

        if (!$business_info) {
            api_helper::return_api_data(1003, '营业厅不存在',  array(), $api_log_id);
        }

        $stat_info = $info  = array(
            'province_id'  => (int)$business_info['province_id'],
            'city_id'      => (int)$business_info['city_id'],
            'area_id'      => (int)$business_info['area_id'],
            'business_id'  => (int)$business_info['id'],
            'device_unique_id' => $device_unique_id,
            'day'          => (int)date("Ymd"),
            //'add_time'     => date("Y-m-d H:i:s")
        );

//         $id = get_mongodb_last_id(_mongo('screen', 'screen_device_online'));

//         if (!$id) {
//             $info['id'] = (int)1;
//         } else {
//             $info['id'] = $id+1;
//         }

        $info['add_time'] = date("Y-m-d H:i:s");

        $online_id = _mongo('screen', 'screen_device_online')->insertOne($info);

        //wangjf add 新增字段nickname_id
        $stat_info['device_nickname_id'] = $device_info['device_nickname_id'];
        screen_helper::add_device_online_stat_day($stat_info, 'v3');

        $result = array(
                'online_id' => $online_id
        );

        api_helper::return_api_data(1000, 'success', $result, $api_log_id);
    }
}