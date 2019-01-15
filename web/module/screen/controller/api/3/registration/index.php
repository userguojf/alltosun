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
}