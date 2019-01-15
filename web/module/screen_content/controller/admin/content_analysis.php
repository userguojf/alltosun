<?php

/**
 * alltosun.com 内容轮播分析 content_analysis.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: Wangjf (wangjf@alltosun.com) $
 * $Date: Jun 13, 2014 6:02:25 PM $
 * $Id$
 */

class Action
{
    private $per_page = 20;

    public function __call($action = '', $params = array())
    {
        $start_time =  date('Y-m-d H:i:s');
        $end_time =  date('Y-m-d H:i:s');
        //查询已上线的内容
        $filter = array(
                'start_time <=' => $start_time,
                'end_time >=' => $end_time,
                'status'        => 1
        );

        $content_list = array();

        $order = ' ORDER BY `id` DESC ';

        $content_count = _model('screen_content')->getTotal($filter);

        if ($content_count) {
            $pager = new Pager($this->per_page);
            $content_list = _model('screen_content')->getList($filter, $order.$pager->getLimit());
            if ($pager->generate($content_count)) {
                Response::assign('pager', $pager);
            }
        }

        //内容分析
        foreach ($content_list as $k => $v) {
            //获取内容的投放归属地
            $region_put = $this->get_content_put_region($v);

            //获取内容的今日轮播设备
            $roll_devices = $this->get_content_roll_device($v, $start_time, $end_time);

            //获取应轮播的设备
            $all_roll_devices = $this->get_content_roll_all_device($v);

            $content_list[$k]['region_put'] = $region_put;
            //已轮播
            $content_list[$k]['roll_device_num'] = count($roll_devices);
            //应轮播
            $content_list[$k]['all_roll_device_num'] = count($all_roll_devices);
            //未轮播
            $not_roll_device_num = count($all_roll_devices) - count($roll_devices);

            $content_list[$k]['not_roll_device_num'] = $not_roll_device_num < 1 ? 0 : $not_roll_device_num ;

            //未轮设备
            $not_roll_device = array_diff($all_roll_devices, $roll_devices);

            //获取未轮播设备的离线设备
            $not_roll_offonline_device = $this->get_offonline_device($not_roll_device);

            //指定设备的在线设备
            $not_roll_online_device = array_diff($not_roll_device, $not_roll_offonline_device);

            //未轮播离线设备数
            $content_list[$k]['not_roll_offonline_device_num']  = count($not_roll_offonline_device);

            //机型宣传图并且非指定机型（自动合图），需验证设备昵称是否被审核
            if ($v['type'] == 4 && $v['is_specify'] == 0) {
                //审核未通过设备
                $not_check_device = $this->get_not_check_device($not_roll_online_device);
            } else {
                $not_check_device = array();
            }

            //未轮播设备并且昵称未通过审核的设备数
            $content_list[$k]['not_roll_online_device_not_check_num'] = count($not_check_device);

            //未轮播异常设备数
            $content_list[$k]['not_roll_unusual_device_num']     = count(array_diff($not_roll_online_device, $not_check_device));
        }
        Response::assign('content_list', $content_list);
        Response::display("admin/content_analysis/content_list.html");
    }

    /**
     * 设备轮播
     */
    public function device_roll()
    {
        //0-指定设备 1-已轮播设备 2-未轮播设备 3-应轮播设备
        $type       = tools_helper::Get('type', 0);
        $content_id = tools_helper::Get('content_id', 0);
        $device_unique_id = tools_helper::Get('device_unique_id', '');
        $page       = tools_helper::Get('page_no', 1);

        $content_info = array();

        if ($type != 0) {

            if (!$content_id) {
                return '内容不存在';
            }
            //内容详情
            $content_info = _model('screen_content')->read($content_id);

            if (!$content_info) {
                return '内容不存在';
            }

            Response::assign('content_info', $content_info);
        }

        //指定设备
        if ($type == 0) {
            $devices[] = $device_unique_id;
        //已轮播
        } else if ($type == 1) {
            $devices = $this->get_content_roll_device($content_info, date('Y-m-d H:i:s'), date('Y-m-d H:i:s'));

        //应轮播
        } else if ($type == 2) {
            //应轮播
            $devices = $this->get_content_roll_all_device($content_info);
       //未轮播
        } else {
            //已轮播
            $roll_devices = $this->get_content_roll_device($content_info,date('Y-m-d H:i:s'), date('Y-m-d H:i:s'));
            //应轮播
            $all_roll_devices = $this->get_content_roll_all_device($content_info);
            //未轮播
            $not_roll_device = array_diff($all_roll_devices, $roll_devices);

            //未轮播离线设备
            if ($type == 4) {
                //获取未轮播设备的离线设备
                $devices = $this->get_offonline_device($not_roll_device);

            //未轮播异常设备 5-未知原因为轮播设备 6-未审核未轮播设备
            } else if ($type == 5 || $type == 6) {

                //获取未轮播设备的离线设备
                $offonline_devices = $this->get_offonline_device($not_roll_device);

                //指定设备的在线设备
                $not_roll_online_device = array_diff($not_roll_device, $offonline_devices);

                //机型宣传图并且非指定机型（自动合图），需验证设备昵称是否被审核
                if ($content_info && $content_info['type'] == 4 && $content_info['is_specify'] == 0) {
                    //审核未通过设备
                    $not_check_device = $this->get_not_check_device($not_roll_online_device);
                } else {
                    $not_check_device = array();
                }

                if ($type == 6) {
                    $devices = $not_check_device;
                } else {
                    $devices = array_diff($not_roll_online_device, $not_check_device);
                }

            } else {
                $devices = $not_roll_device;
            }
        }

        $count = count($devices);

        if ($count) {
            $pager = new Pager($this->per_page);
            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }

            Response::assign('count', $count);
            $pager->getLimit($page);

            $limit = ($page-1)*$this->per_page;
            $devices = array_slice($devices, $limit, $this->per_page);
        }

        $new_list = array();
        foreach ( $devices as $v ) {
            $device_info = screen_device_helper::get_device_info_by_device($v);
            if (!$device_info) {
                continue;
            }
            $device_info['business_hall_name'] = business_hall_helper::get_info_name('business_hall', $device_info['business_id'], 'title');
            $device_info['city_name'] = business_hall_helper::get_info_name('city', $device_info['city_id'], 'name');
            $device_info['province_name'] = business_hall_helper::get_info_name('province', $device_info['province_id'], 'name');

            //查询当前设备是否在线
            $is_online = screen_helper::get_online_status($v);
            if ($is_online) {
                //查询设备当前在轮播的内容
                $device_info['roll_content'] = $roll_content = _widget('screen_content')->get_device_roll_content($device_info);
            } else {
                $device_info['roll_content'] = array();
            }

            $device_info['is_online'] = $is_online;

            $new_list[] = $device_info;
        }

        Response::assign('device_list', $new_list);
        Response::assign('type', $type);
        Response::assign('type', $type);
        Response::display('admin/content_analysis/device_roll.html');
    }

    /**
     * 获取审核未通过或未审核的设备
     * @param unknown $not_roll_online_device 未轮播的在线设备
     */
    private function get_not_check_device($not_roll_online_device)
    {
        if (!$not_roll_online_device) {
            return array();
        }
        //获取设备信息
        $device_list = screen_device_helper::get_device_list_by_filter(array('device_unique_id' => $not_roll_online_device));

        $not_check_device = array();

        //未轮播在线设备的昵称id
        $nickname_ids = array();

        //未轮播在线设备， 此数组包含device_nickname_id值
        $not_roll_online_device2 = array();

        foreach ($device_list as $k => $v) {
            $nickname_ids[$v['device_nickname_id']] = $k;
            $not_roll_online_device2[$v['device_unique_id']] = $v['device_nickname_id'];
        }

        if (!$nickname_ids) {
            return array();
        }

        $nickname_ids = array_flip($nickname_ids);

        //查询未通过审核的在线机型昵称id
        $not_roll_nickname_ids = _model('screen_device_nickname')->getFields('id', array('id' => $nickname_ids, 'status' => 0));

        if (!$not_roll_nickname_ids) {
            return array();
        }
        //p($device_list);
        //循环未轮播的在线设备， 过滤通过审核的设备
        $not_check_device = array();
        foreach ( $not_roll_online_device2 as $k => $v ) {

            if ( in_array($v, $not_roll_nickname_ids) ) {
                $not_check_device[] = $k;
            }
        }

        return $not_check_device;
    }

    /**
     * 设备轮播
     */
    public function device_not_roll()
    {
        //0-指定设备 1-已轮播设备 2-未轮播设备 3-应轮播设备
        $type       = tools_helper::Get('type', 0);
        $content_id = tools_helper::Get('content_id', 0);
        $device_unique_id = tools_helper::Get('device_unique_id', '');
        $page       = tools_helper::Get('page_no', 1);

        $content_info = array();

        if ($type != 0) {

            if (!$content_id) {
                return '内容不存在';
            }
            //内容详情
            $content_info = _model('screen_content')->read($content_id);

            if (!$content_info) {
                return '内容不存在';
            }
        }



        $count = count($devices);

        if ($count) {
            $pager = new Pager($this->per_page);
            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }

            Response::assign('count', $count);
            $pager->getLimit($page);

            $limit = ($page-1)*$this->per_page;
            $devices = array_slice($devices, $limit, $this->per_page);
        }

        $new_list = array();
        foreach ( $devices as $v ) {
            $device_info = screen_device_helper::get_device_info_by_device($v);
            if (!$device_info) {
                continue;
            }
            $device_info['business_hall_name'] = business_hall_helper::get_info_name('business_hall', $device_info['business_id'], 'title');
            $device_info['city_name'] = business_hall_helper::get_info_name('city', $device_info['city_id'], 'name');
            $device_info['province_name'] = business_hall_helper::get_info_name('province', $device_info['province_id'], 'name');

            //查询当前设备是否在线
            $is_online = screen_helper::get_online_status($v);
            if ($is_online) {
                //查询设备当前在轮播的内容
                $device_info['roll_content'] = $roll_content = _widget('screen_content')->get_device_roll_content($device_info);
            } else {
                $device_info['roll_content'] = array();
            }

            $device_info['is_online'] = $is_online;

            $new_list[] = $device_info;
        }

        Response::assign('device_list', $new_list);
        Response::display('admin/content_analysis/device_roll.html');
    }

    /**
     * 获取离线设备
     */
    private function get_offonline_device($not_roll_device)
    {
        $filter = array(
                'device_unique_id'      => $not_roll_device,
                'day'                   => date('Ymd'),
                'update_time >='        => date('Y-m-d H:i:s', time()-1800)
        );

        //查询在线的设备
        $online_list = _model('screen_device_online_stat_day')->getFields('device_unique_id', $filter, ' GROUP BY `device_unique_id` ');

        return array_diff($not_roll_device, $online_list);
    }

    /**
     * 获取内容投放地区
     * @param unknown $content_info
     */
    private function get_content_put_region($content_info)
    {
        $filter = array('content_id' => $content_info['id']);

        $res_list = _model('screen_content_res')->getList($filter);

        $put = array();
        foreach ($res_list as $k => $v) {
            if (empty($put[$v['res_name']])) {
                $put[$v['res_name']][] = $v['res_id'];
            }
        }
        return $put;
    }

    /**
     * 获取内容轮播设备
     * @param unknown $content_info
     * @param unknown $end_time
     * @param unknown $start_time
     */
    private function get_content_roll_device($content_info, $start_time, $end_time)
    {
        $start_day = date('Ymd', strtotime($start_time));
        $end_day = date('Ymd', strtotime($end_time));

        $filter = array(
                'content_id' => $content_info['id'],
                'day >='     => $start_day,
                'day <='     => $end_day
        );

        $filter = get_mongodb_filter($filter);

        $stat_list       = _mongo('screen', 'screen_content_click_stat_day')->aggregate(
                array(
                        array('$match' => $filter),
                        array('$group' => array(
                                '_id'               => array(
                                        'device_unique_id'  => '$device_unique_id',
                                ),
                                'device_unique_id'  => array('$first' => '$device_unique_id'),
                        )
                        )
                )
        );

        $devices = array();
        foreach ($stat_list as $k => $v){
            $v = (array)$v;
            $devices[] = $v['device_unique_id'];
        }

        return $devices;
    }

    /**
     * 获取内容应轮播设备量
     */
    private function get_content_roll_all_device($content_info)
    {
        $filter = array('content_id' => $content_info['id']);

        $res_list = _model('screen_content_res')->getList($filter);

        $devices = array();

        foreach ($res_list as $k => $v) {
            $filter = array('status' => 1);

            //按品牌
            if (!empty($v['phone_name']) && $v['phone_name'] != 'all') {
                $filter['phone_name'] = $v['phone_name'];
            }

            //按机型
            if (!empty($v['phone_version']) && $v['phone_version'] != 'all') {
                $filter['phone_version'] = $v['phone_version'];
            }

            //指定厅
            if ($v['res_name'] == 'business_hall') {
                $filter['business_id'] = $v['res_id'];

            //指定归属地
            } else if ($v['res_name'] != 'group') {
                $filter["{$v['res_name']}_id"] = $v['res_id'];
            }
            $device_list = _model('screen_device')->getFields('device_unique_id', $filter);
            $devices = array_merge($devices, $device_list);
        }
        if ($devices) {
            $devices = array_unique($devices);
        }
        return $devices;
    }


}
?>