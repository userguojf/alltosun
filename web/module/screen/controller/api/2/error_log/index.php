<?php

/**
 * alltosun.com 错误日志 index.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年9月24日 下午1:04:49 $
 * $Id$
 */

class Action
{
    public function index()
    {
        api_helper::return_api_data(1000, 'success', array());
        
        $user_number = tools_helper::post('user_number', '');
        $device_unique_id = tools_helper::post('device_unique_id', '');
        $date = tools_helper::post('date', '');
        $content = tools_helper::post('content', '');

        $api_log_id = api_helper::check_sign(array(), 0);

        if (!$user_number) {
            api_helper::return_api_data(1003, '请输入营业厅编码', array(), $api_log_id);
        }

        if (!$device_unique_id) {
            api_helper::return_api_data(1003, '请输入设备唯一标识', array(), $api_log_id);
        }

        $device_info = screen_device_helper::get_device_info_by_device($device_unique_id);

        if (!$device_info) {
            api_helper::return_api_data(1003, '管理员已下架该设备', array(), $api_log_id);
        }

        $business_info = _uri('business_hall', array('user_number' => $user_number));

        if (!$business_info) {
            api_helper::return_api_data(1003, '营业厅不存在', array(), $api_log_id);
        }

        // 查找条件
        $info = array(
            'business_id'      => $business_info['id'],
            'device_unique_id' => $device_unique_id,
            'en_content'       => md5($content)
        );

        // 添加条件
        $error_info = array(
            'province_id'      => $business_info['province_id'],
            'city_id'          => $business_info['city_id'],
            'area_id'          => $business_info['area_id'],
            'business_id'      => $business_info['id'],
            'device_unique_id' => $device_unique_id,
            'time'             => $date,
            'last_time'        => $date,
            'content'          => $content,
            'en_content'       => md5($content),
            'num'              => 1
        );

        global $mc_wr;
        $i = 1;
        $id = 0;

        // 添加缓存 
        $con = $mc_wr->get(md5($content));

        if ($con) {
            $mc_wr->set(md5($content), $con + 1);

        } else {
            $mc_wr->set(md5($content), $i);

            $id = _model('screen_error_log')->create($error_info);
        }

        // 每五次 更新库
        if ($con % 5 == 0) {
            $error_info = _model('screen_error_log')->read($info, ' ORDER BY `id` DESC ');
            $id = _model('screen_error_log')->update($error_info['id'], array('last_time' => $date, 'num' => $con));

            api_helper::return_api_data(1000, 'success', array('log_id' => $error_info['id']), $api_log_id);
        }

//         if (!$id) {
//             api_helper::return_api_data(1003, '添加失败，请稍侯重试', array(), $api_log_id);
//         }

        api_helper::return_api_data(1000, 'success', array('log_id' => $id), $api_log_id);
    }
}