<?php

/**
 * alltosun.com 错误日志 error_log.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年9月29日 上午11:05:12 $
 * $Id$
 */

class Action
{
    private $per_page = 10;
    private $member_id  = 0;
    private $member_res_name = '';
    private $member_res_id   = 0;
    private $ranks           = 0;
    private $time;

    public function __construct()
    {
        $this->member_id   = member_helper::get_member_id();
        $this->time        = date('Y-m-d H:i:s');
        $member_info = member_helper::get_member_info($this->member_id);

        if ($member_info) {
            $this->member_res_name = $member_info['res_name'];
            $this->member_res_id   = $member_info['res_id'];
            $this->ranks           = $member_info['ranks'];
        }

        Response::assign('curr_member_ranks', $this->ranks);
    }

   public function __call($action = '', $params = array())
    {
        $search_filter  = $filter = array();
        $page_no        = Request::Get('page_no',1);
        $search_filter  = Request::Get('search_filter', array());
        $hall_title     = tools_helper::get('hall_title', '');

        if (isset($search_filter['start_add_time']) && $search_filter['start_add_time']) {
            $filter['add_time >='] = $search_filter['start_add_time'].' 00:00:00';
        }

        if (isset($search_filter['end_add_time']) && $search_filter['end_add_time']) {
            $filter['add_time <'] = $search_filter['end_add_time'].' 23:59:59';
        }

        if ($this->member_res_name != 'group' && $this->member_res_name) {
            $filter['business_id'] = screen_helper::get_business_id_by_member($this->member_res_name, $this->member_res_id);

            if (!$filter['business_id']) {
                $filter['business_id'] = 0;
            }
        }

        if (!empty($search_filter['province_id'])) {
            $filter['province_id'] = $search_filter['province_id'];
        }

        if (!empty($search_filter['city_id'])) {
            $filter['city_id']  = $search_filter['city_id'];
        }

        if (!empty($search_filter['area_id'])) {
            $filter['area_id']  = $search_filter['area_id'];
        }

        if ($hall_title) {
            $business_id = _uri('business_hall', array('title LIKE '=> '%'.$hall_title.'%' ), 'id');

            $filter['business_id']  = $business_id;
        }

        $order = ' ORDER BY `num` DESC, `last_time` DESC ';

        if (empty($filter)) {
            $filter = array( 1 => 1);
        }

//         if ($is_export == 1) {
//             $list = _model('screen_error_log')->getList($filter, $order);
//             $this->export_excel($list);
//         } else {
        $error_list = get_data_list('screen_error_log',$filter, $order, $page_no, $this->per_page);
//         }
        foreach ($error_list as $k => $v) {

            $device_info = screen_device_helper::get_device_info_by_device($v['device_unique_id']);
            //$device_info = _uri('screen_device', array('device_unique_id'=>$v['device_unique_id']));

            if (!$device_info) {
                continue;
            }

            $error_list[$k]['version_no']              = $device_info['version_no'];
            $error_list[$k]['imei']                    = $device_info['imei'] ? $device_info['imei'] : 0;
            $error_list[$k]['phone_name']              = $device_info['phone_name'];
            $error_list[$k]['phone_version']           = $device_info['phone_version'];
            $error_list[$k]['phone_name_nickname']     = $device_info['phone_name_nickname'];
            $error_list[$k]['phone_version_nickname']  = $device_info['phone_version_nickname'];

        }

        Response::assign('error_list',$error_list);
        Response::assign('search_filter', $search_filter);
        Response::assign('page', $page_no);

        Response::display("admin/error_log/error_log.html");
    }
}
