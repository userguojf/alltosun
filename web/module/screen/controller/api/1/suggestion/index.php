<?php

/**
 * alltosun.com 吐槽 index.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年7月12日 下午4:11:53 $
 * $Id$
 */

class Action
{
    public function add_suggestion()
    {
        $user_number  = tools_helper::post('user_number', '');
        $phone_imei   = tools_helper::post('phone_imei', '');
        $content      = tools_helper::post('content', '');

        // 验证接口
        $check_params = array(
            //'user_number'  => $user_number
        );

        $api_log_id = api_helper::check_sign($check_params, 0);

        if (!$user_number) {
            api_helper::return_api_data(1003, '请输入营业厅的视图编码');
        }

        if (!$phone_imei) {
            api_helper::return_api_data(1003, '请输入手机imei');
        }

        if (!$content) {
            api_helper::return_api_data(1003, '请输入内容');
        }

        $business_info  = business_hall_helper::get_info('business_hall', array('user_number'=>$user_number));

        if (!$business_info) {
            api_helper::return_api_data(1003, '营业厅不存在');
        }

        $info  = array(
            'province_id'   => $business_info[0]['province_id'],
            'city_id'       => $business_info[0]['city_id'],
            'area_id'       => $business_info[0]['area_id'],
            'business_id'   => $business_info[0]['id'],
            'imei'          => screen_helper::device_decode($phone_imei),
            'content'       => $content
        );

         _model('screen_spitslot')->create($info);

        $result = array(
            'info' => 'ok',
        );
        api_helper::return_api_data(1000, 'success', $result, $api_log_id);
    }
}