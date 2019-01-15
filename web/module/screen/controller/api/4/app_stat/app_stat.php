<?php

/**
 * alltosun.com 用户玩应用统计 app_stat.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2018年1月25日 下午6:00:47 $
 * $Id$
 */

class Action
{

    public function add_stat()
    {
        //api_helper::return_api_data(1000, 'success', array());
        $user_number        = tools_helper::post('user_number', '');
        $device_unique_id   = tools_helper::post('device_unique_id', '');
        $info               = tools_helper::post('content', '');
        $check_params = array(
            //'user_number'  => $user_number
        );

        $api_log_id = api_helper::check_sign($check_params, 0);

        if (!$user_number) {
            api_helper::return_api_data(1003, '请输入营业厅的视图编码', array(), $api_log_id);
        }

        if (!$device_unique_id) {
            api_helper::return_api_data(1003, '设备 标识不能为空', array(), $api_log_id);
        }

        $filter['user_number'] = $user_number;

        $business_info = _uri('business_hall', $filter);

        if (!$business_info) {
            api_helper::return_api_data(1003, '营业厅不存在', array(), $api_log_id);
        }

        $device_info = screen_device_helper::get_device_info_by_device($device_unique_id);

        if ( !$device_info ) {
            api_helper::return_api_data(1003, '未知的设备信息', array(), $api_log_id);
        }

        if ($device_info['business_id'] != $business_info['id']) {
            api_helper::return_api_data(1003, '营业厅不存在此设备', array(), $api_log_id);
        }

        $n_info = array (
            'device_unique_id'  => $device_unique_id,
            'province_id' => $business_info ['province_id'],
            'city_id'     => $business_info ['city_id'],
            'area_id'     => $business_info ['area_id'],
            'business_id' => $business_info ['id'],
        );

        $record_time = '';
        $new_info    = json_decode ( htmlspecialchars_decode ( $info ), true );
        //exit;
        foreach ($new_info as $k => $v) {
            if (!$v['content']) {
                continue;
            }

            if ($v['record_time']) {
                $record_time      = $v['record_time'];
            }

            foreach ($v['content'] as $kk => $vv) {

                $this->get_day_data($n_info, $record_time, $vv['app_name'], ceil($vv['run_time']/1000), $vv['open_count']);
                $this->get_year_data($n_info, $record_time, $vv['app_name'], ceil($vv['run_time']/1000), $vv['open_count']);
                $this->get_month_data($n_info, $record_time, $vv['app_name'], ceil($vv['run_time']/1000), $vv['open_count']);

                if ($kk >= 50) {
                    break;
                }
            }

            if ($k >= 50) {
                break;
            }
        }

        api_helper::return_api_data(1000, 'success', array('info'=>'ok'), $api_log_id);
    }


    // 处理天
    public function get_day_data($info, $record_time, $app_name, $run_time, $open_count)
    {
        if (!$info || !$app_name || !$run_time || !$open_count) {
            return false;
        }

        $n_day_info = $day_info = $info;

        $day_info['day']      = date("Ymd", $record_time);

        $day_info['app_name'] = $n_day_info['app_name'] = $app_name;

        $new_day_info = _model("screen_app_stat_day")->read($day_info);

        if (!$new_day_info) {
            $day_info['open_count'] = $open_count;
            $day_info['run_time']   = $run_time;

            _model('screen_app_stat_day')->create($day_info);
        } else {

            _model('screen_app_stat_day')->update($new_day_info['id'], array('open_count'=>$open_count, 'run_time'=>$run_time));

        }

        return true;
    }

    // 处理月
    public function get_month_data($info, $record_time, $app_name, $run_time, $open_count)
    {
        if (!$info || !$app_name || !$run_time || !$open_count) {
            return false;
        }

        $n_month_info = $month_info = $info;

        $month_info['day']      = date("Ym", $record_time);
        $n_month_info['day >='] = date("Ym", $record_time)."01";
        $n_month_info['day <='] = date("Ym", $record_time)."31";
        $month_info['app_name'] = $n_month_info['app_name'] = $app_name;

        $new_month_info = _model("screen_app_stat_month")->read($month_info);
        if (!$new_month_info) {
            $month_info['open_count'] = $open_count;
            $month_info['run_time']   = $run_time;

            _model('screen_app_stat_month')->create($month_info);
        } else {
            $open_counts = _model("screen_app_stat_day")->getFields('open_count', $n_month_info);
            $run_times   = _model("screen_app_stat_day")->getFields('run_time', $n_month_info);

            _model("screen_app_stat_month")->update($new_month_info['id'], array('open_count'=>array_sum($open_counts), 'run_time'=>array_sum($run_times)));

        }

        return true;
    }

    // 处理年
    public function get_year_data($info, $record_time, $app_name, $run_time, $open_count)
    {
        if (!$info || !$run_time || !$open_count) {
            return false;
        }

        $n_year_info = $year_info = $info;

        $year_info['day']      = date("Y", $record_time);
        $n_year_info['day >='] = date("Y", $record_time)."0101";
        $n_year_info['day <='] = date("Y", $record_time)."1231";
        $year_info['app_name'] = $n_year_info['app_name'] = $app_name;

        // 年
        $new_year_info = _model("screen_app_stat_year")->read($year_info);
        if (!$new_year_info) {
            $year_info['open_count'] = $open_count;
            $year_info['run_time']   = $run_time;

            _model('screen_app_stat_year')->create($year_info);
        } else {
            $open_counts = _model("screen_app_stat_day")->getFields('open_count', $n_year_info);
            $run_times   = _model("screen_app_stat_day")->getFields('run_time', $n_year_info);

            _model("screen_app_stat_year")->update($new_year_info['id'], array('open_count'=>array_sum($open_counts), 'run_time'=>array_sum($run_times)));
        }

        return true;
    }
}