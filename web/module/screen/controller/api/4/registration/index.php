<?php

/**
 * alltosun.com 注册 index.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年9月19日 下午3:46:48 $
 * $Id$
 */

class Action
{
    public function get_info()
    {
        $registration_id  = tools_helper::post('registration_id', '');

        $api_log_id = api_helper::check_sign(array(), 0);

        if (!$registration_id) {
            api_helper::return_api_data(1003, '参数不全', array(), $api_log_id);
        }

        $device_unique_ids = _model('screen_device')->getFields('device_unique_id', array('registration_id'=>$registration_id, 'status'=>1));

        $new_list  = array();

        foreach ($device_unique_ids as $k => $v) {

            $business_id = $device_info = screen_device_helper::get_device_info_by_device($v, 'business_id');
            //$business_id = _uri('screen_device', array('device_unique_id'=>$v), 'business_id');
            $user_number = _uri('business_hall', $business_id, 'user_number');

            $new_list[$k]['device_unique_id']        = $v;
            $new_list[$k]['user_number'] = $user_number;
        }

        api_helper::return_api_data(1000, 'success', $new_list, $api_log_id);
    }

    /**
     * 绑定registration_id接口
     * v4.0使用
     */
    public function bind_registration_id()
    {
        $registration_id    = tools_helper::post('registration_id', '');
        $device_unique_id   = tools_helper::post('device_unique_id', '');
        $user_number        = tools_helper::post('user_number', '');

        $api_log_id = api_helper::check_sign(array(), 0);

        if (!$registration_id) {
            api_helper::return_api_data(1010, 'registration_id不能为空', array(), $api_log_id);
        }

        if (!$device_unique_id) {
            api_helper::return_api_data(1004, 'device_unique_id不能为空', array(), $api_log_id);
        }

        if (!$user_number) {
            api_helper::return_api_data(1004, 'user_number不能为空', array(), $api_log_id);
        }

        $business_info  = _uri('business_hall', array('user_number'=>$user_number));

        if (!$business_info) {
            api_helper::return_api_data(2100, '营业厅不存在', array(), $api_log_id);
        }

        $filter = array('device_unique_id' => $device_unique_id, 'business_id' => $business_info['id']);

        $device_info = screen_device_helper::get_device_info($filter);

        if (!$device_info) {
            api_helper::return_api_data(2003, '设备不存在或已被下架', array(), $api_log_id);
        }

        //注册id存在， 且相同， 直接返回
        if ($device_info['registration_id'] == $registration_id) {
            api_helper::return_api_data(1000, 'success', array(), $api_log_id);
        }

        //更新registration_id
        $res = _model('screen_device')->update($device_info['id'], array('registration_id' => $registration_id));

        //注册id变了， 应删除原有标签
        if ($device_info['registration_id'] != $registration_id) {
            //获取待删除的标签
            $exists_ids = _model('screen_device_tag_res')->getFields('id', array(
                    'registration_id' => $device_info['registration_id'],
            ));
            //删除原有标签
            if ($exists_ids) {
                $del_res = push_helper::remove_tag_by_registration_id($device_info['registration_id'], $exists_ids);
            }
        }

        //重新绑定极光推标签
        $tags = array();
        $tags[] = push_helper::get_business_hall_tag($business_info['id']); //厅
        $tags[] = push_helper::get_area_tag($business_info['area_id']); //区
        $tags[] = push_helper::get_city_tag($business_info['city_id']); //市
        $tags[] = push_helper::get_province_tag($business_info['province_id']); //省
        $tags[] = push_helper::get_phone_name_version_tag($device_info['phone_name'], $device_info['phone_version']); //机型

        $res = push_helper::binding_tag($registration_id, $tags);

        api_helper::return_api_data(1000, 'success', array(), $api_log_id);
    }
}