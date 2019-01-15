<?php

/**
 * alltosun.com 玩应用 app_stat.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2018年2月6日 下午4:17:28 $
 * $Id$
 */

class Action
{
    public $per_page = 15;
    
    public function index()
    {
        $type       = tools_helper::get('type', 1);
        $page_no    = tools_helper::get('page_no', 1);
        $start_time = tools_helper::get('start_time', '');
        $end_time   = tools_helper::get('end_time', '');
        
        $filter = array();

        if ($type == 1) {
            $table = 'screen_app_stat_day';
            $filter['day'] = date("Ymd");
        } else if ($type == 2) {
            $table = 'screen_app_stat_day';
            $week  = $this->get_week();
            $filter['day >='] = date("Y").$week[0];
            $filter['day <='] = date("Y").$week[1];
            
        } else if ($type == 3) {
            $table = 'screen_app_stat_month';
            $filter['day'] = date("Ym");
        } else if ($type == 4) {
            $table = 'screen_app_stat_year';
            $filter['day'] = date("Y");
        }

        if ($start_time && $end_time) {
            unset($filter['day']);
            
            $type  = 5;
            $table = 'screen_app_stat_day';
            $filter['day >='] = date("Ymd", strtotime($start_time));
            $filter['day <='] = date("Ymd", strtotime($end_time));
            
        }
        
        $list  = array();

        $count = _model($table)->getTotal($filter);
        
        if ($count) {
            $pager = new Pager($this->per_page);
        
            $order = 'ORDER BY `id` DESC ';
            $limit = $pager->getLimit($page_no);
        
            $list = _model($table)->getList($filter, $order.' '.$limit);
        
            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }
        }
        
        foreach ($list as $k => $v) {
            $device_info = screen_device_helper::get_device_info_by_device($v['device_unique_id']);
            if (!$device_info) {
                continue;
            }
            
            $list[$k]['imei']          = $device_info['imei'];
            $list[$k]['phone_name']    = $device_info['phone_name_nickname'] ? $device_info['phone_name_nickname'] : $device_info['phone_name'];
            $list[$k]['phone_version'] = $device_info['phone_version_nickname']? $device_info['phone_version_nickname'] : $device_info['phone_version'];
        }

        Response::assign('list', $list);
        Response::assign('type', $type);
        Response::assign('start_time', $start_time);
        Response::assign('end_time', $end_time);
        
        Response::display('admin/app_stat/app_stat.html');
    }
    
    public function week()
    {
        $type    = tools_helper::get('type', 2);
        $page_no = tools_helper::get('page_no', 1);
        $start_time = tools_helper::get('start_time', '');
        $end_time   = tools_helper::get('end_time', '');
        
        $week  = $this->get_week();
        $filter['day >='] = date("Y").$week[0];
        $filter['day <='] = date("Y").$week[1];
        
        $list     = array();
        $new_list = array();
        $count = count(_model('screen_app_stat_day')->getList($filter, ' GROUP BY `business_id`, `device_unique_id`, `app_name`'));

        if ($count) {
            $pager = new Pager($this->per_page);
        
            $order = ' GROUP BY `business_id`, `device_unique_id`, `app_name` ORDER BY `id` DESC ';
            $limit = $pager->getLimit($page_no);
        
            $list = _model('screen_app_stat_day')->getList($filter, $order.' '.$limit);
        
            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }
        }

        foreach ($list as $k => $v) {
            $device_info = screen_device_helper::get_device_info_by_device($v['device_unique_id']);
            if (!$device_info) {
                continue;
            }
            
            $list[$k]['imei']          = $device_info['imei'];
            $list[$k]['phone_name']    = $device_info['phone_name_nickname'] ? $device_info['phone_name_nickname'] : $device_info['phone_name'];
            $list[$k]['phone_version'] = $device_info['phone_version_nickname']? $device_info['phone_version_nickname'] : $device_info['phone_version'];
            
            $filter['device_unique_id'] = $v['device_unique_id'];
            $filter['app_name']         = $v['app_name'];

            $run_times   = _model('screen_app_stat_day')->getFields('run_time', $filter);
            $open_counts = _model('screen_app_stat_day')->getFields('open_count', $filter);
            
            $list[$k]['run_time']   = array_sum($run_times);
            $list[$k]['open_count'] = array_sum($open_counts);
        }
        
        Response::assign('list', $list);
        Response::assign('type', $type);
        Response::assign('start_time', $start_time);
        Response::assign('end_time', $end_time);
        
        Response::display('admin/app_stat/app_stat.html');
        
    }
    
    public function get_week($type = 1)
    {
        $week = date('w');

        $date=new DateTime();
        $date->modify('this week');
        $weeks[]=$date->format('md');
//         $date->modify('this week +1 days');
//         $weeks[]=$date->format('m'.$symbol.'d');
//         $date->modify('this week +2 days');
//         $weeks[]=$date->format('m'.$symbol.'d');
//         $date->modify('this week +3 days');
//         $weeks[]=$date->format('m'.$symbol.'d');
//         $date->modify('this week +4 days');
//         $weeks[]=$date->format('m'.$symbol.'d');
//         $date->modify('this week +5 days');
//         $weeks[]=$date->format('m'.$symbol.'d');
        $date->modify('this week +6 days');
        $weeks[]=$date->format('md');
    
        return $weeks;
    }
}