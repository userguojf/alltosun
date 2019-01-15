<?php

/**
 * alltosun.com 数据中心 data_center.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 申小宁 (shenxn@alltosun.com) $
 * $Date: 2017年7月11日 下午2:42:42 $
 * $Id$
 */

class Action
{
    private $per_page        = 20;
    private $member_id       = 0;
    private $member_res_name = '';
    private $member_res_id   = 0;
    private $member_info     = array();
    private $ranks           = 0;
    private $time            = 0;

    public $mongodb;

    public function __construct()
    {

        $this->member_id   = member_helper::get_member_id();
        $this->member_info = member_helper::get_member_info($this->member_id);
        $this->time        = time();

        if ($this->member_info) {
            $this->member_res_name = $this->member_info['res_name'];
            $this->member_res_id   = $this->member_info['res_id'];
            $this->ranks           = $this->member_info['ranks'];
        }

        Response::assign('curr_member_ranks', $this->ranks);
    }

    public function __call($action = '', $params = array())
    {
        //默认只查第一张表的内容
        $start_time  = Request::Get('start_time', date('Y-m-d',$this->time - 7*24*3600));
        $end_time    = Request::Get('end_time', date('Y-m-d'));
        $table_type  = Request::Get('table_type', 1);
        $is_export   = Request::Get('is_export', 0);
        $data        = array();

        if ($is_export == 1) {
            $this -> export($table_type, $start_time, $end_time);
            exit();
        }


        Response::assign('data', $data);
        Response::assign('start_time', $start_time);
        Response::assign('end_time', $end_time);
        Response::assign('table_type', $table_type);
        Response::display('admin/data_center.html');
    }

    /**
     * 表3详情
     */
    public function table_3_detail() {

        $start_time  = Request::Get('start_time', date('Y-m-d',$this->time - 7*24*3600));
        $end_time    = Request::Get('end_time', date('Y-m-d'));
        $date_type   = Request::Get('date_type', '');
        $table_type  = Request::Get('table_type', 3);
        $is_export   = Request::Get('is_export', 0);
        $data        = array();

        if (!$date_type || $table_type != 3) {
            return '非法参数';
        }

        $data_list = _widget('screen_stat.data_center')->get_screen_keep_date_stat(array('start_time' => $start_time, 'end_time' => $end_time, 'date_type' => $date_type));

        $devices = array();
        if (!empty($data_list[$date_type])) {
            $devices = $data_list[$date_type]['devices'];
        }

        $device_list = array();

        foreach ($devices as $k => $v) {
            //查询设备详情
            $device_info = screen_device_helper::get_device_info_by_device($v);
            if (!$device_info) {
                $province_name = '--';
                $city_name     = '--';
                $business_name = '--';
                $name_nickname = '--';
                $version_nickname = '--';
            } else {
                //归属地
                $province_name      = business_hall_helper::get_info_name('province', $device_info['province_id'], 'name');
                $city_name          = business_hall_helper::get_info_name('city', $device_info['city_id'], 'name');
                $business_name      = business_hall_helper::get_info_name('business_hall', $device_info['business_id'], 'title');
                //机型
                $device_nickname_info = screen_device_helper::get_device_nickname_info($device_info['device_nickname_id']);
                $name_nickname      = !empty($device_nickname_info['name_nickname']) ? $device_nickname_info['name_nickname'] : $device_info['phone_name'];
                $version_nickname   = !empty($device_nickname_info['version_nickname']) ? $device_nickname_info['version_nickname'] : $device_info['phone_version'];
            }

            $device_list[] = array(
                    'date_type'         => $date_type,
                    'province_name'     => $province_name,
                    'city_name'         => $city_name,
                    'business_name'     => $business_name,
                    'device_unique_id'  => $v,
                    'name_nickname'     => $name_nickname,
                    'version_nickname'  => $version_nickname,
            );

        }

        if ($is_export == 1) {
            $params = array();
            $params['head']     = array('在线时间段', '省', '市', '厅', '设备', '品牌', '型号');
            $params['filename'] = $date_type.'统计表3数据详情';
            $params['data']     = $device_list;

            Csv::getCvsObj($params)->export();

            exit();
        }

        Response::assign('device_list', $device_list);
        Response::assign('start_time', $start_time);
        Response::assign('end_time', $end_time);
        Response::assign('table_type', $table_type);
        Response::assign('date_type', $date_type);
        Response::display('admin/data_center_detail/table_3_detail.html');
    }
    /**
     * 导出
     */
    public function export($table_type, $start_time, $end_time)
    {
        $params = array(
                'start_time' => $start_time,
                'end_time'   => $end_time
        );

        $data = array();

        if ($table_type == 1) {
            $data = _widget('screen_stat.data_center')->get_offline_data_for_business_hall($params);
        } else if ($table_type == 2) {
            $data = _widget('screen_stat.data_center')->get_offline_data_for_version($params);
        //设备留存统计 表3
        } else if ($table_type == 3) {
            $data = _widget('screen_stat.data_center')->get_screen_keep_date_stat($params);
        //周活跃统计 表4
        } else if ($table_type == 4) {
            $data = _widget('screen_stat.data_center')->get_screen_device_week_stat($params);
        } else {
            return false;
        }

        $func_name =  'table_'.$table_type.'_export_data';

        $new_data = $this -> $func_name($data);
        //p($new_data);exit;
        Csv::getCvsObj($new_data)->export();
    }

    /**
     * 表1导出数据拼接
     */
    private function table_1_export_data($data)
    {

        if (empty($data['data'])) {
            return false;
        }

        $new_data = array();

        foreach ($data['data'] as $k => $v) {

            $business_hall_info =  business_hall_helper::get_business_hall_info($k);
            $city_name = $business_hall_info['city_id'] ? _uri('city',$business_hall_info['city_id'],'name') : '';
            $province_name =  $business_hall_info['province_id'] ? _uri('city',$business_hall_info['province_id'],'name') : '';

            $tmp_data = array();
            $tmp_data['province_name'] = $province_name;
            $tmp_data['city_name'] = $city_name;
            $tmp_data['business_hall_name'] = $business_hall_info['title'];
            $tmp_data['offline_total']   = $v['offline_total'];
            $i = 0;
            foreach( $v['data'] as $kk => $vv ){
                $i ++;
                $tmp_data['install_rate'.$i] = $vv['install_rate'].'('.$kk.')';
            }

            $new_data[] = $tmp_data;
        }

        $params['head']     = array('省', '市', '厅', '设备离线量');

        for ($i = 1; $i <= $data['count']; $i ++) {
            $params['head'][] = '第'.$i.'天离线率';
        }

        $params['filename'] = '统计表1';
        $params['data']     = $new_data;

        return $params;
    }

    /**
     * 表2导出数据拼接
     */
    private function table_2_export_data($data)
    {

        if (empty($data['data'])) {
            return false;
        }

        $new_data = array();

        foreach ($data['data'] as $k => $v) {
            $tmp_data = array();
            $info = screen_device_helper::get_device_nickname_info($k);
            $tmp_data['device_name'] = $info['name_nickname'] ? $info['name_nickname']:$info['phone_name'];
            $tmp_data['device_version'] = $info['version_nickname']? $info['version_nickname']:$info['phone_version'];
            $tmp_data['offline_total']   = $v['offline_total'];

            $i = 0;
            foreach( $v['data'] as $kk => $vv ){
                $i ++;
                $tmp_data['install_rate'.$i] = $vv['install_rate'].'('.$kk.')';
            }

            $new_data[] = $tmp_data;
        }

        $params['head']     = array('设备品牌', '设备型号', '设备离线量');

        for ($i = 1; $i <= $data['count']; $i ++) {
            $params['head'][] = '第'.$i.'天离线率';
        }

        $params['filename'] = '统计表2';
        $params['data']     = $new_data;

        return $params;
    }

    /**
     * 表3导出数据拼接
     */
    private function table_3_export_data($data)
    {
        if (!$data) {
            return false;
        }

        $new_data = array();

        foreach ($data as $k => $v) {
            $tmp_data = array();
            $tmp_data['online_times'] = $k;
            $tmp_data['online_num']   = $v['value'];
            $new_data[] = $tmp_data;
        }

        $params['filename'] = '统计表3';
        $params['data']     = $new_data;
        $params['head']     = array('在线时间段', '设备量');
        return $params;
    }

    /**
     * 表4导出数据拼接
     */
    private function table_4_export_data($data)
    {

         if (empty($data['data_list'])) {
            return false;
        }

        $new_data = array();
        foreach ($data['data_list'] as $k => $v) {
            //第几周
            $tmp_data = array();
            $tmp_data['weeks'] = '第'.($data['weeks'][$k] + 1).'周'.$data['days'][$k]['start'].'-'.$data['days'][$k]['end'];
            $tmp_data['install_num'] = $v['install_num'];
            $tmp_data['active_num']  = $v['active_num'];
            $tmp_data['active_rate'] = $v['active_rate'];
            $tmp_data['average_active'] = $v['average_active'];
            $new_data[] = $tmp_data;
        }

        $params['filename'] = '统计表4';
        $params['data']     = $new_data;
        $params['head']     = array('周', '设备累计安装量', '周活跃设备量', '设备周活跃率', '周平均活跃天数');
        return $params;
    }
}