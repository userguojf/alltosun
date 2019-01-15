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
 * $Date: 2017年8月25日 下午5:44:12 $
 * $Id$
 */

class Action
{
    public function add_spitslot()
    {
        $user_number  = tools_helper::post('user_number', '');
        $device_unique_id = tools_helper::post('device_unique_id', '');
        $content      = tools_helper::post('content', '');

        // 验证接口
        $check_params = array(
            //'user_number'  => $user_number
        );

        $api_log_id = api_helper::check_sign($check_params, 0);

        if (!$user_number) {
            api_helper::return_api_data(1003, '请输入营业厅的视图编码', array(), $api_log_id);
        }

        if (!$device_unique_id) {
            api_helper::return_api_data(1003, '请输入设备唯一标识', array(), $api_log_id);
        }

        if (!$content) {
            api_helper::return_api_data(1003, '请输入内容', array(), $api_log_id);
        }

        $device_info = screen_device_helper::get_device_info_by_device($device_unique_id);
        if (!$device_info) {
            api_helper::return_api_data(2003, '设备不存在或已被下架', array(), $api_log_id);
        }

        $business_info  = business_hall_helper::get_info('business_hall', array('user_number'=>$user_number));

        if (!$business_info) {
            api_helper::return_api_data(1003, '营业厅不存在', array(), $api_log_id);
        }
        $info  = array(
            'province_id'   => $business_info[0]['province_id'],
            'city_id'       => $business_info[0]['city_id'],
            'area_id'       => $business_info[0]['area_id'],
            'business_id'   => $business_info[0]['id'],
            'device_unique_id' => $device_unique_id,
            'content'       => $content
        );

         _model('screen_spitslot')->create($info);

        api_helper::return_api_data(1000, 'success', array('info' => 'ok',), $api_log_id);
    }
}