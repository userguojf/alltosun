<?php

/**
 * alltosun.com 设备统计 record.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年6月29日 下午3:35:21 $
 * $Id$
 */

class Action
{
    public function add_device_record()
    {
        $info            = tools_helper::post('info', '');
        $check_params = array(
            //'user_number'  => $user_number
        );

        $api_log_id = api_helper::check_sign($check_params, 0);

        if (!$info) {
            api_helper::return_api_data(1003, '请上传动作相关信息');
        }

        $new_info        = json_decode(htmlspecialchars_decode($info), true);

        foreach ($new_info as $k => $v) {

            if (!$v['device_id']) {
                api_helper::return_api_data(1003, '缺少设备信息');
            }

            if (!$v['type']) {
                api_helper::return_api_data(1003, '请输入动作类型');
            }

            $add_info           = array();
            $imei               = screen_helper::device_decode($v['device_id']);
            $phone_info         = _uri('screen_device', array('imei' => $imei));

            if (!$phone_info) {
                api_helper::return_api_data(1003, '设备不存在');
            }

            $add_info['imei']               = $phone_info['imei'];
            $add_info['phone_name']         = $phone_info['phone_name'];
            $add_info['phone_version']      = $phone_info['phone_version'];
            $add_info['province_id']        = $phone_info['province_id'];
            $add_info['city_id']            = $phone_info['city_id'];
            $add_info['area_id']            = $phone_info['area_id'];
            $add_info['business_id']        = $phone_info['business_id'];

            $add_info['type']         = $v['type'];

            //添加统计记录
            screen_helper::add_device_record($add_info);
        }

        $result = array('info'=>'ok');
        api_helper::return_api_data(1000, 'success', $result, $api_log_id);
    }
}