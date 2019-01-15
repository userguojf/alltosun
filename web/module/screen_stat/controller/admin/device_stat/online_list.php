<?php

/**
 * alltosun.com 设备列表 device_list.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年9月16日 下午9:33:05 $
 * $Id$
 */

load_file('screen_stat','trait', 'stat');

class Action
{
    use stat;

    public function online_list()
    {
        if (!$this->member_info) {
            return '您无权访问此页面';
        }

        //区域条件
        $filter = $this->region_filter();

        //搜索条件
        $search_filter = $this->search_filter();

        if ($search_filter === false) {
            return $this->error_info;
        }

        $filter = array_merge($filter, $search_filter);

        //搜索条件
        $search_filter  = Request::Get('search_filter', array());
        $page_no        = Request::Get('page_no', 1);
        $is_active      = Request::Get('is_active', 0);

        unset($filter['hour']);


        if (isset($search_filter['business_id']) && !empty($search_filter['business_id']) && $business_info = _uri('business_hall', $search_filter['business_id'])) {
            $hall_title            = $business_info['title'];
            $filter['business_id'] = $business_info['id'];

            //补全条件
            $search_filter['province_id']   = $business_info['province_id'];
            $search_filter['city_id']   = $business_info['city_id'];
            $search_filter['area_id']   = $business_info['area_id'];

        } else {

            if (isset($search_filter['region_id']) && $search_filter['region_id']) {
                $region_id = $search_filter['region_id'];
            }

            if (isset($search_filter['region_type']) && in_array($search_filter['region_type'], array('province', 'city', 'area', 'business_hall'))) {
                $region_type = $search_filter['region_type'];
            }

            if (!$region_type || !$region_id) {
                return '不合法的地区信息';
            }

            $region_info = _uri($region_type, $region_id);

            if (!$region_info) {
                return '地区不存在';
            }

            $device_filter = array(
                'status' => 1
            );

            if ($region_id) {
                if ($region_type == 'business_hall') {
                    $filter['business_id'] = $region_id;
                } else {
                    $filter["{$region_type}_id"] = $region_id;
                }
            }

        }

        if (!empty($search_filter['province_id'])) {
            $filter['province_id'] = $search_filter['province_id'];
            $province = array('province_id' => $search_filter['province_id']);
            Response::assign('where1' , $province);
        }

        if (!empty($search_filter['city_id'])) {
            $filter['city_id'] = $search_filter['city_id'];
            $city = array('city_id' => $search_filter['city_id']);
            Response::assign('where2' , $city);
        }

        if (isset($search_filter['area']) && !empty($search_filter['area'])) {

            $filter['area_id'] = $device_filter['area_id'] = $search_filter['area'];
        }

        $filter['add_time >='] = date('Y-m-d H:i:s', time()-1800);

        $device_online_list = get_data_list('screen_device_online', $filter, ' GROUP BY device_unique_id,business_id ORDER BY `id` DESC ', $page_no, $this->per_page);

        foreach ($device_online_list as $k => $v) {

            $device_info = _uri('screen_device', array('device_unique_id'=>$v['device_unique_id']));
            if (!$device_info) {
                continue;
            }

            $device_online_list[$k]['phone_name'] = $device_info['phone_name'];
            $device_online_list[$k]['phone_name_nickname'] = $device_info['phone_name_nickname'];
            $device_online_list[$k]['phone_version_nickname'] = $device_info['phone_version_nickname'];
            $device_online_list[$k]['phone_version'] = $device_info['phone_version'];
            $device_online_list[$k]['online_status'] = screen_helper::get_online_status($v['imei']);
            $device_online_list[$k]['active_status'] = _uri('screen_device_online', array('device_unique_id'=>$v['device_unique_id'], 'day'=>date("Ymd")));
            $device_online_list[$k]['active_time'] = _uri('screen_device_online_stat_day', array('device_unique_id'=>$v['device_unique_id'], 'day'=>date("Ymd")), 'online_time');
        }
        Response::assign('search_filter' , $search_filter);
        //Response::assign('hall_title', $hall_title);
        Response::assign('device_online_list', $device_online_list);
        Response::display('admin/device_stat/online_list.html');
    }

    public function unonline_day_list()
    {
        $device_unique_id = tools_helper::get('device_unique_id', '');
        $province_id      = tools_helper::get('province_id', 0);
        $city_id          = tools_helper::get('city_id', 0);
        $business_id      = tools_helper::get('business_id', 0);

        $start_time       = tools_helper::get('start_time', '');
        $stop_time        = tools_helper::get('stop_time', '');

        $is_export        = tools_helper::get('is_export', 0);
        $filter  = array(
            'device_unique_id' => $device_unique_id,
            'day >='           => $start_time,
            'day <'            => $stop_time
        );

        $active_days = _model('screen_device_online_stat_day')->getFields('day', $filter);

        $days        = $this->get_date_from_range($start_time, $stop_time);

        if ($is_export == 1) {
            $this->is_export(array_diff($days, $active_days), array('province_id'=>$province_id, 'city_id'=>$city_id, 'business_id'=>$business_id));
        }
        //p($days, $start_time, $stop_time, $active_days, array_diff($days, $active_days));

        Response::assign('province_id', $province_id);
        Response::assign('city_id', $city_id);
        Response::assign('business_id', $business_id);
        Response::assign('diff_days', array_diff($days, $active_days));
        Response::assign('active_start_time', $start_time);
        Response::assign('active_stop_time', $stop_time);

        Response::display('admin/device_stat/unonline_list.html');
    }

    /**
     * 获取指定日期段内每一天的日期
     * @param  Date  $startdate 开始日期
     * @param  Date  $enddate   结束日期
     * @return Array
     */
    public function get_date_from_range($startdate, $enddate){

        $stimestamp = strtotime($startdate);
        $etimestamp = strtotime($enddate);

        // 计算日期段内有多少天
        $days = ($etimestamp-$stimestamp)/86400+1;

        // 保存每天日期
        $date = array();

        for($i=0; $i<$days; $i++){
            $date[] = date('Ymd', $stimestamp+(86400*$i));
        }

        return $date;
    }

    /**
     * 导出
     */
    public function is_export($list, $filter)
    {
        if (!$list) {
            return '暂无数据';
        }


        foreach ($list as $k => $v) {
            $info[$k]['proinvce_id']      = business_hall_helper::get_info_name('province', $filter['province_id'],  'name');
            $info[$k]['city_id']          = business_hall_helper::get_info_name('city', $filter['city_id'], 'name');
            $info[$k]['business_hall_id'] = business_hall_helper::get_info_name('business_hall', $filter['business_id'], 'title');
            $info[$k]['user_name']        = _uri('business_hall', $filter['business_id'], 'user_number');
            $info[$k]['unonline_day']     = date("Y-m-d", strtotime($v));
        }

        $params['filename'] = '离线列表';
        $params['data']     = $info;
        $params['head']     = array('所属省', '所属市', '营业厅名称', '渠道编码', '离线时间');

        Csv::getCvsObj($params)->export();
    }
}