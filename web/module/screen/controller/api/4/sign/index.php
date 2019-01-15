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
 * $Date: 2017年8月9日 下午12:32:30 $
 * $Id$
 */

class Action
{
    public function get_content()
    {
        $user_number     = tools_helper::post('user_number', '');
        $device_unique_id = tools_helper::post('device_unique_id', '');
        
        $check_params = array(
        );

        $api_log_id = api_helper::check_sign($check_params, 0);

        if (!$user_number) {
            api_helper::return_api_data(1003, '请输入营业厅的视图编码', array(), $api_log_id);
        }

        if (!$device_unique_id) {
            api_helper::return_api_data(1003, '请输入设备唯一标识', array(), $api_log_id);
        }

        $filter['user_number'] = $user_number;

        $business_info = _uri('business_hall', array('user_number'=>$user_number));

        if (!$business_info) {
            api_helper::return_api_data(1003, '营业厅不存在', array(), $api_log_id);
        }

        //为提升查询速度， 先查出上线的内容
        $content_filter = array(
            'start_time  <= '   => date('Y-m-d H:i:s'),
            'end_time >= '      => date('Y-m-d H:i:s'),
            'status'            => 1
        );

        $ids = _model('screen_sign')->getFields('id', $content_filter, ' ORDER BY `id` DESC ');

        if (!$ids) {
            return api_helper::return_api_data(1000, 'success', '', $api_log_id);
        }

        $content_ids = array();

        foreach (screen_config::$content_put_type as $k => $v) {

            $content_res_filter = array(
                'content_id' => $ids,
                'res_name'   => $k,
                //'content_device_res_id' => 0
            );

            if ($k != 'group') {
                if ($k == 'business_hall') {
                    $content_res_filter['res_id'] = $business_info['id'];
                } else {
                    $content_res_filter['res_id'] = $business_info["{$k}_id"];
                }
            }

            //根据权限查内容id
            $content_id = $this->get_content_by_power_for_h5($content_res_filter, $device_unique_id, 'screen_sign_res');

            if (is_array($content_id) && $content_id) {
                $content_ids = array_merge($content_ids, $content_id);
            }

            if (count($content_ids) >= 2) {
                break;
            }
        }

        if (!$content_ids) {
            api_helper::return_api_data(1000, 'success', array(), $api_log_id);
        }

        //查询内容详情
        $content_info = _model('screen_sign')->read($content_ids[0], 'LIMIT 1');

        api_helper::return_api_data(1000, 'success', $content_info, $api_log_id);
    }
    
    /**
     * 根据权限获取内容
     * @param unknown $filter
     */
    private function get_content_by_power_for_h5($filter, $device_unique_id, $table='screen_content_res')
    {
    
        //查询机型
        $device_info = screen_device_helper::get_device_info_by_device($device_unique_id);

        if (!$device_info) {
            api_helper::return_api_data(1003, '未知的设备信息', array());
        }
    
        //查询所有此权限下的发布内容, 包含机型的
        $content_res_list = _model($table)->getList($filter);
        $content_ids = array();
        foreach ($content_res_list as $k => $v) {
            //如果有发布品牌和发布型号
            if ($v['phone_name'] && $v['phone_version']) {
                if ($v['phone_name'] == $device_info['phone_name'] && $v['phone_version'] == $device_info['phone_version']) {
                    $content_ids[] = $v['content_id'];
                }
                continue;
                //如果有发布品牌并且没有发布型号
            } else if ($v['phone_name'] && !$v['phone_version']) {
                if ($v['phone_name'] == $device_info['phone_name']) {
                    $content_ids[] = $v['content_id'];
                }
                continue;
            } else {
                $content_ids[] = $v['content_id'];
            }
        }
    
        //取前两条
        if (count($content_ids) >= 2) {
            $content_ids = array_slice($content_ids, 0, 2);
        }
    
        return $content_ids;
    }
}