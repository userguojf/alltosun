<?php

/**
 * alltosun.com 更新手机专木工已 phone_shoppe.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年9月4日 下午12:09:44 $
 * $Id$
 */

class Action
{
    public function update_shoppe()
    {
        $shoppe_id          = tools_helper::post('shoppe_id', '');
        $device_unique_id   = tools_helper::post('device_unique_id', '');

        // 验证接口
        $check_params = array(
            //'user_number'  => $user_number
        );

        $api_log_id = api_helper::check_sign($check_params, 0);

        if (!$device_unique_id) {
            api_helper::return_api_data(1003, '设备唯一标示不能为空', array(), $api_log_id);
        }

        //wangjf add 2017-12-22
        $device_info = screen_device_helper::get_device_info_by_device($device_unique_id);

        if (!$device_info) {
            api_helper::return_api_data(1003, '此设备不存在', array(), $api_log_id);
        }

        _model('screen_device')->update($device_info['id'], array('shoppe_id'=>$shoppe_id));

        //数字地图创建
        $user_number = _uri('business_hall', array('id'=>$device_info['business_id']), 'user_number');

        screen_helper::dm_create_app_log(array(
            'type'        => 'create',
            'user_number' => $user_number,
            'brand'       => $device_info['phone_name'],
            'version'     => $device_info['phone_version'],
            'shoppe_id'   => $shoppe_id,
            'device_unique_id'   => $device_unique_id
        ));

        api_helper::return_api_data(1000, 'success', array('info'=>'ok'), $api_log_id);
    }

    /**
     * 是否绑定柜台
     */
    public function is_bind()
    {
        $device_unique_id     = tools_helper::post('device_unique_id', '');
        // 验证接口
        $check_params = array(
            //'user_number'  => $user_number
        );

        $api_log_id = api_helper::check_sign($check_params, 0);

        if (!$device_unique_id) {
            api_helper::return_api_data(1003, '设备唯一标示不能为空', array(), $api_log_id);
        }

        $device_info = screen_device_helper::get_device_info_by_device($device_unique_id);
        //$device_info = _uri('screen_device', array('device_unique_id'=>$device_unique_id));

        if (!$device_info) {
            api_helper::return_api_data(1003, '设备不存在或已被下架', array(), $api_log_id);;
        }

        if ($device_info['shoppe_id']) {
            $is_bind  = 1;
        } else {
            $is_bind  = 0;
        }

        api_helper::return_api_data(1000, 'success', array('is_bind'=>$is_bind), $api_log_id);
    }
}