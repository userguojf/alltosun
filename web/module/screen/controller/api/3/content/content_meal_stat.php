<?php

/**
 * alltosun.com  content_meal_stat.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2018年1月17日 下午3:07:19 $
 * $Id$
 */

class Action
{
    public function index()
    {
        $user_number      = tools_helper::post ( 'user_number', '' );
        $device_unique_id = tools_helper::post ( 'device_unique_id', '' );
        $content_meal_id  = tools_helper::post('content_meal_id', 0);
        //$click_time       = tools_helper::post('click_time', '');
        $res_info         = tools_helper::post('res_info', ''); //[{click_time:""}, {click_time:""}]

        // 验证接口
        $check_params = array ();

        $api_log_id = api_helper::check_sign ( $check_params, 0 );

        if (! $user_number) {
            api_helper::return_api_data ( 1003, '请输入营业厅的视图编码', array (), $api_log_id );
        }

        if (! $device_unique_id) {
            api_helper::return_api_data ( 1003, '设备唯一标识不能为空', array (), $api_log_id );
        }

        // 营业停信息
        $business_info = business_hall_helper::get_business_hall_info ( array (
            'user_number' => $user_number
        ) );

        if (! $business_info) {
            api_helper::return_api_data ( 1003, '营业厅不存在', array (), $api_log_id );
        }

        $device_info = screen_device_helper::get_device_info_by_device($device_unique_id);

        if ( !$device_info ) {
            api_helper::return_api_data(1003, '管理员不存在', array(), $api_log_id);
        }

        $stat_info = $info = array (
            'device_unique_id'  => $device_unique_id,
            'content_meal_id'   => $content_meal_id,
            'province_id' => $business_info ['province_id'],
            'city_id'     => $business_info ['city_id'],
            'area_id'     => $business_info ['area_id'],
            'business_id' => $business_info ['id'],
            'day'         => date ( "Ymd" ),
        );

        $new_info = json_decode ( htmlspecialchars_decode ( $res_info ), true );

        foreach ($new_info as $k => $v) {
            $info['click_time'] = date("Y-m-d H:i:s", $v['time']);
            if (isset($v['run_time']) && $v['run_time']) {
                $info['run_time']   = $v['run_time'];
            }

            // p($info);
            _model('screen_content_meal_record')->create($info);

            // 添加点击统计
            $stat = _model('screen_content_meal_stat_day')->read($stat_info);

            if ($stat) {

                _model('screen_content_meal_stat_day')->update($stat['id'], "set action_num=action_num+1");
                _model('screen_content_meal_stat_day')->update($stat['id'], "set run_time=run_time+{$v['run_time']}");
            } else {
                //wangjf add 为预防循环时改变 stat_info
                $tmp_stat_info = $stat_info;

                $tmp_stat_info['action_num'] = 1;
                $tmp_stat_info['run_time']   = $v['run_time'];
                _model("screen_content_meal_stat_day")->create($tmp_stat_info);
            }
        }

        api_helper::return_api_data(1000, 'success', array('info'=>'ok'), $api_log_id);
    }

    public function add_pop_num()
    {
        $user_number      = tools_helper::post ( 'user_number', '' );
        $device_unique_id = tools_helper::post ( 'device_unique_id', '' );
        $content_meal_id  = tools_helper::post('content_meal_id', 0);
        //$click_time       = tools_helper::post('click_time', '');
        $res_info         = tools_helper::post('res_info', ''); //[{pop_time:""}, {pop_time:""}]

        // 验证接口
        $check_params = array ();

        $api_log_id = api_helper::check_sign ( $check_params, 0 );

        if (! $user_number) {
            api_helper::return_api_data ( 1003, '请输入营业厅的视图编码', array (), $api_log_id );
        }

        if (! $device_unique_id) {
            api_helper::return_api_data ( 1003, '设备唯一标识不能为空', array (), $api_log_id );
        }

        // 营业停信息
        $business_info = business_hall_helper::get_business_hall_info ( array (
            'user_number' => $user_number
        ) );

        if (! $business_info) {
            api_helper::return_api_data ( 1003, '营业厅不存在', array (), $api_log_id );
        }

        $device_info = screen_device_helper::get_device_info_by_device($device_unique_id);

        if ( !$device_info ) {
            api_helper::return_api_data(1003, '管理员不存在', array(), $api_log_id);
        }

        $stat_info = $info = array (
            'device_unique_id'  => $device_unique_id,
            'content_meal_id'   => $content_meal_id,
            'province_id' => $business_info ['province_id'],
            'city_id'     => $business_info ['city_id'],
            'area_id'     => $business_info ['area_id'],
            'business_id' => $business_info ['id'],
            'day'         => date ( "Ymd" ),
        );

        $new_info = json_decode ( htmlspecialchars_decode ( $res_info ), true );

        foreach ($new_info as $k => $v) {
            $info['pop_time'] = date("Y-m-d H:i:s", $v['time']);
            _model('screen_content_meal_pop_record')->create($info);

            // 添加点击统计
            $stat = _model('screen_content_meal_pop_stat_day')->read($stat_info);

            if ($stat) {

                _model('screen_content_meal_pop_stat_day')->update($stat['id'], "set pop_num=pop_num+1");
            } else {
                //wangjf add 为预防循环时改变 stat_info
                $tmp_stat_info = $stat_info;

                $tmp_stat_info['pop_num'] = 1;

                _model("screen_content_meal_pop_stat_day")->create($tmp_stat_info);
            }
        }

        api_helper::return_api_data(1000, 'success', array('info'=>'ok'), $api_log_id);
    }
}