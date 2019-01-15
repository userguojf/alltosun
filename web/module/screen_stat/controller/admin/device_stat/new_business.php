<?php

/**
 * alltosun.com  new_business.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年12月20日 上午11:56:35 $
 * $Id$
 */


class Action
{
    private $p_filter         = array();
    private $next_res_name    = '';
    private $next_res_name_id = '';
    private $nick_name        = '';
    private $member_id   = 0;
    private $member_info = array();
    private $res_name    = 'group';
    private $res_id      = 0;
    public  $per_page    = 15;
    public  $start_time;
    public  $end_time;

    //日期类型，分别对应：待分配（时间段）、今天按小时、本周按天、本月按天, 时间段按小时、时间段按天、时间段按月
    private static $date_types     = array(
        0, 2, 3, 4, 5, 6, 7
    );

    //当前搜索的日期类型， 默认天
    private $date_type      = 0;


    public function __construct()
    {
        //获取管理员身份
        $this->member_id   = member_helper::get_member_id();
        $this->member_info = member_helper::get_member_info($this->member_id);
        $this->res_name    = $this->member_info['res_name'];
        $this->res_id      = $this->member_info['res_id'];

        if (!$this->member_info) {
            return $this->error_info;
        }

        Response::assign('res_name', $this->res_name);
        Response::assign('res_id', $this->res_id);
        Response::assign('member_info', $this->member_info);

        switch($this->member_info['res_name'])
        {
            case 'group':
                $this->nick_name  = '全国';
                $this->next_res_name = 'province';
                $this->next_res_name_id = 'province_id';
                break;
            case 'province':
                $this->nick_name        = _uri($this->member_info['res_name'],$this->member_info['res_id'],'name');
                $this->p_filter         = array('province_id' => $this->member_info['res_id']);
                $this->next_res_name    = 'city';
                $this->next_res_name_id = 'city_id';
                break;
            case 'city':
                $this->nick_name        = _uri($this->member_info['res_name'],$this->member_info['res_id'],'name');
                $this->p_filter         = array('city_id' => $this->member_info['res_id']);
                $this->next_res_name    = 'area';
                $this->next_res_name_id = 'area_id';
                break;
            case 'area':
                $this->nick_name     = _uri($this->member_info['res_name'],$this->member_info['res_id'],'name');
                $this->p_filter      = array('area_id' => $this->member_info['res_id']);
                $this->next_res_name = 'business_hall';
                break;
            case 'business_hall':
                $this->nick_name        = _uri('business_hall',$this->member_info['res_id'],'title');
                $this->p_filter         = array('business_id' => $this->member_info['res_id']);
                $this->res_name         = 'business';
                $this->next_res_name    = 'business';
                $this->next_res_name_id = 'business_id';
                break;
        }

    }

    public function new_business()
    {
        $search_filter    = Request::Get('search_filter', array());
        $page             = tools_helper::get('page_no', 1);
        $hall_title       = Request::Get('hall_title', '');

        $order_dir     = tools_helper::get('order_dir', 'desc');
        $order_field   = tools_helper::get('order_field', 'experience_times');

        $default_filter   = _widget('screen')->default_search_filter($this->member_info);
        $business_id      = tools_helper::get('business_id', 0);

        $is_export        = tools_helper::get('is_export', 0);

        $date_time_filter = $this->search_filter();

        $filter = $default_filter;

        //营业厅权限跳过标题搜索
        if ($this->next_res_name != 'business_hall' && $hall_title) {
            $business_hall_list = _model('business_hall')->getList(array('title' => $hall_title));
            $business_hall_ids = array();
            foreach ($business_hall_list as $k => $v) {
                //非集团管理员并且搜索的营业厅不在本身权限之内则跳过
                if ($this->next_res_name != 'group' && $v["{$this->next_res_name}_id"] != $this->next_res_name_id) {
                    continue;
                }
                $business_hall_ids[] = $v['id'];
            }

            if (!$business_hall_ids) {
                $business_hall_ids = 0;
            }
            $filter['business_id'] = $business_hall_ids;
        }

        //搜索判断

        if (isset($search_filter['region_id']) && $search_filter['region_id']) {
            $region_id = $search_filter['region_id'];
        } else {
            $region_id   = 0;
            $region_type = 'group';
        }

        if ((isset($search_filter['region_id']) && $search_filter['region_id']) && (isset($search_filter['region_type']) && in_array($search_filter['region_type'], array('province', 'city', 'area', 'business_hall')))) {
            $region_type = $search_filter['region_type'];
            if ($region_id) {
                if ($region_type == 'business_hall') {
                    $filter['business_id'] = $region_id;
                    $last_business_filter['business_id'] = $region_id;
                } else {
                    $filter["{$region_type}_id"] = $region_id;
                    $last_business_filter["{$region_type}_id"] = $region_id;
                }
            }

        }

        //省
        if (!empty($search_filter['province_id']) ) {
            $filter['province_id'] = $search_filter['province_id'];
            $last_business_filter['province_id'] = $search_filter['province_id'];
            $province                = array('province_id' => $search_filter['province_id']);
            Response::assign('where1' , $province);
        }
        //市
        if (!empty($search_filter['city_id'])) {
            $filter['city_id'] = $search_filter['city_id'];
            $last_business_filter['city_id'] = $search_filter['city_id'];
            $city                = array('city_id' => $search_filter['city_id']);
            Response::assign('where2' , $city);
        }
        //区
        if (!empty($search_filter['area_id'])) {
            $filter['area_id']               = $search_filter['area_id'];
            $last_business_filter['area_id'] = $search_filter['area_id'];
        }

        if ($business_id) {
            $filter['business_id'] = $business_id;
        }

        if ($hall_title) {
            $filter['business_id'] = _uri('business_hall', array('title'=>$hall_title), 'id');
        }

        $filter           = array_merge($filter, $this->p_filter);

        $active_filter    = $filter;
        $e_filter         = $filter;

        $filter['status'] = 1;

        $last_business_filter = $new_num_filter   = $filter;

        $filter           = array_merge($filter, $date_time_filter);

        $keys             = array();
        $count_time       = array();

        if (isset($filter['day'])) {
            $e_filter['day']   = $filter['day'];

            // 新增设备数条件
            $new_num_filter['add_time >=']  = date('Y-m-d', strtotime($filter['day']))." 00:00:00";
            $new_num_filter['add_time <=']  = date('Y-m-d', strtotime($filter['day']))." 23:59:59";
        }
        if (isset($filter['day >=']) && isset($filter['day <'])) {
            // 体验时长条件
            $e_filter['day >='] = $filter['day >='];
            $new_num_filter['add_time >=']  = date('Y-m-d', strtotime($filter['day >=']))." 00:00:00";
            $new_num_filter['add_time <=']  = date('Y-m-d', strtotime($filter['day <']))." 23:59:59";
        }
        if (isset($filter['day >=']) && isset($filter['day <='])) {
            $e_filter['day <'] = $filter['day <='];
            $new_num_filter['add_time >=']  = date('Y-m-d', strtotime($filter['day >=']))." 00:00:00";
            $new_num_filter['add_time <=']  = date('Y-m-d', strtotime($filter['day <=']))." 23:59:59";
        }
        if (isset($filter['day <'])) {
            $e_filter['day <'] = $filter['day <'];
        }
        if (isset($date_time['day <='])) {
            $e_filter['day <='] = $filter['day <='];
            $new_num_filter['add_time >=']  = date('Y-m-d', strtotime($filter['day >=']))." 00:00:00";
            $new_num_filter['add_time <=']  = date('Y-m-d', strtotime($filter['day <=']))." 23:59:59";
        }


        $last_business_filter['add_time <']      = $new_num_filter['add_time >='];

        $last_business_filter['status']          = $new_num_filter['status']  = 1;
        //今日新增设备厅店
        $business_ids      = _model('screen_device')->getFields('business_id', $new_num_filter, ' GROUP BY business_id ');
        //今日之前已安装设备厅店
        $last_business_ids = _model('screen_device')->getFields('business_id', $last_business_filter, ' GROUP BY business_id ');

        $new_business_num  = count(array_diff($business_ids, $last_business_ids));
        if ($business_ids || $last_business_ids) {
            $business_ids = array_diff($business_ids, $last_business_ids);
            if ($business_ids) {
                $filter['business_id'] = $business_ids;
            } else {
                $filter['business_id'] = 0;
            }
        } else {
            $filter['business_id'] = 0;
        }
        $list = get_data_list('screen_device', $filter, ' GROUP BY `business_id` ORDER BY `id` DESC ', $page, $this->per_page);

        foreach ($list as $k => $v) {
            $e_time = array();
            $new_num_filter['business_id']       = $v['business_id'];

            $new_device_ids                    = _model('screen_device')->getFields('device_unique_id', $new_num_filter, ' GROUP BY device_unique_id ');

            $e_filter['device_unique_id']      = $new_device_ids;
            $e_filter['business_id']           = $v['business_id'];
            if (!$active_filter) {
                $active_filter['1'] = 1;
            }

            $active_num                        = count(_model('screen_device_online_stat_day')->getFields('device_unique_id',$active_filter, ' GROUP BY device_unique_id '));

            $e_count = _mongo('screen', 'screen_action_record')->aggregate(array(
                array('$match' => get_mongodb_filter($e_filter)),
                array('$group' => array(
                    '_id'               => array(
                        'device_unique_id'       => '$device_unique_id',
                    ),
                    'count'    => array('$sum'=>'$experience_time'),
                ),
                )));

            foreach ($e_count as $v) {
                $e_time[]     = $v['count'];

                $count_time[] = $v['count'];
            }

            $list[$k]['new_device_num']        = count($new_device_ids);
            $list[$k]['active_num']            = $active_num;
            $list[$k]['e_time']                = array_sum($e_time);

            if ($order_field == 'experience_times') {
                $keys[$k] = array_sum($e_time);
            }

        }

        if ($keys) {
            if ($order_dir == 'desc') {
                array_multisort ($keys, SORT_DESC, $list);
            } else {
                array_multisort ($keys, SORT_ASC, $list);
            }
        }

        if ($is_export == 1) {
            $this->is_export($list);
        }

        Response::assign('search_filter' , $search_filter);
        Response::assign('hall_title', $hall_title);
        Response::assign('list', $list);
        Response::assign('order_dir', $order_dir);
        Response::assign('order_field', $order_field);
        Response::assign('region_id', $region_id);
        Response::assign('region_type', $region_type);
        Response::assign('count_experience_time', array_sum($count_time));

        Response::display('admin/device_stat/new_business_list.html');
    }

    public function new_device()
    {
        $search_filter    = Request::Get('search_filter', array());
        $page             = tools_helper::get('page_no', 1);
        $hall_title       = Request::Get('hall_title', '');

        $order_dir     = tools_helper::get('order_dir', 'desc');
        $order_field   = tools_helper::get('order_field', 'experience_times');

        $default_filter   = _widget('screen')->default_search_filter($this->member_info);
        $business_id      = tools_helper::get('business_id', 0);

        $is_export        = tools_helper::get('is_export', 0);

        $date_time        = $this->search_filter();

        $filter = $default_filter;

        //营业厅权限跳过标题搜索
        if ($this->next_res_name != 'business_hall' && $hall_title) {
            $business_hall_list = _model('business_hall')->getList(array('title' => $hall_title));
            $business_hall_ids = array();
            foreach ($business_hall_list as $k => $v) {
                //非集团管理员并且搜索的营业厅不在本身权限之内则跳过
                if ($this->next_res_name != 'group' && $v["{$this->next_res_name}_id"] != $this->next_res_name_id) {
                    continue;
                }
                $business_hall_ids[] = $v['id'];
            }

            if (!$business_hall_ids) {
                $business_hall_ids = 0;
            }
            $filter['business_id'] = $business_hall_ids;
        }

        //搜索判断

        if (isset($search_filter['region_id']) && $search_filter['region_id']) {
            $region_id = $search_filter['region_id'];
        } else {
            $region_id   = 0;
            $region_type = 'group';
        }

       if ((isset($search_filter['region_id']) && $search_filter['region_id']) && (isset($search_filter['region_type']) && in_array($search_filter['region_type'], array('province', 'city', 'area', 'business_hall')))) {
            $region_type = $search_filter['region_type'];
            if ($region_id) {
                if ($region_type == 'business_hall') {
                    $filter['business_id'] = $region_id;
                    $last_business_filter['business_id'] = $region_id;
                } else {
                    $filter["{$region_type}_id"] = $region_id;
                    $last_business_filter["{$region_type}_id"] = $region_id;
                }
            }

        }

        //省
        if (!empty($search_filter['province_id']) ) {
            $filter['province_id'] = $search_filter['province_id'];
            $last_business_filter['province_id'] = $search_filter['province_id'];
            $province                = array('province_id' => $search_filter['province_id']);
            Response::assign('where1' , $province);
        }
        //市
        if (!empty($search_filter['city_id'])) {
            $filter['city_id'] = $search_filter['city_id'];
            $last_business_filter['city_id'] = $search_filter['city_id'];
            $city                = array('city_id' => $search_filter['city_id']);
            Response::assign('where2' , $city);
        }
        //区
        if (!empty($search_filter['area_id'])) {
            $filter['area_id']               = $search_filter['area_id'];
            $last_business_filter['area_id'] = $search_filter['area_id'];
        }

        if ($business_id) {
            $filter['business_id'] = $business_id;
        }

        if ($hall_title) {
            $filter['business_id'] = _uri('business_hall', array('title'=>$hall_title), 'id');
        }

        $filter           = array_merge($filter, $this->p_filter);

        $active_filter    = $filter;
        $e_filter         = $filter;

        $filter['status'] = 1;

        $keys             = array();
        $count_time       = array();

        if (isset($date_time['day'])) {
            $e_filter['day']   = $date_time['day'];

            // 新增设备数条件
            $filter['add_time >=']  = date('Y-m-d', strtotime($date_time['day']))." 00:00:00";
            $filter['add_time <=']  = date('Y-m-d', strtotime($date_time['day']))." 23:59:59";
        }
        if (isset($date_time['day >=']) && isset($date_time['day <'])) {
            // 体验时长条件
            $e_filter['day >='] = $date_time['day >='];
            $filter['add_time >=']  = date('Y-m-d', strtotime($date_time['day >=']))." 00:00:00";
            $filter['add_time <=']  = date('Y-m-d', strtotime($date_time['day <']))." 23:59:59";
            $this->end_time         = $date_time['day <'];
            $this->start_time       = $date_time['day >='];
        }
        if (isset($date_time['day >=']) && isset($date_time['day <='])) {
            $e_filter['day <'] = $date_time['day <='];
            $filter['add_time >=']  = date('Y-m-d', strtotime($date_time['day >=']))." 00:00:00";
            $filter['add_time <=']  = date('Y-m-d', strtotime($date_time['day <=']))." 23:59:59";
            $this->end_time          = $date_time['day <='];
            $this->start_time        = $date_time['day >='];
        }
        if (isset($date_time['day <'])) {
            $e_filter['day <'] = $date_time['day <'];
            $this->end_time    = $date_time['day <'];
        }
        if (isset($date_time['day <='])) {
            $e_filter['day <='] = $date_time['day <='];
            $new_num_filter['add_time >=']  = date('Y-m-d', strtotime($date_time['day >=']))." 00:00:00";
            $new_num_filter['add_time <=']  = date('Y-m-d', strtotime($date_time['day <=']))." 23:59:59";
        }

        $filter['status'] = 1;

        $i    = 0;
        $keys = array();

        if ($is_export == 1) {
            $this->is_export(_model('screen_device')->getList($filter, ' ORDER BY `id` DESC '), 1, $filter);
        }


        $list = get_data_list('screen_device', $filter, ' ORDER BY `id` DESC ', $page, $this->per_page);

        foreach ($list as $k => $v) {

            $active_filter['device_unique_id'] = $v['device_unique_id'];
            $active_filter['province_id']      = $v['province_id'];

            // 体验时长
            $exper_time = _mongo('screen', 'screen_action_record')->find(get_mongodb_filter($active_filter), array('projection'=>['experience_time'=>1]));

            $t_count = array();

            foreach ($exper_time as $vv) {

                $t_count[$i] = $vv['experience_time'];
                $count_time[]= $vv['experience_time'];
                $i++;
            }

            $list[$k]['experience_time'] = array_sum($t_count);

            // 是否在线过
            $online_info = _model('screen_device_online_stat_day')->read($active_filter);

            if (isset($search_filter['date_type']) && $search_filter['date_type'] !=2) {

                //最后活跃时间
                $last_active = _model('screen_device_online_stat_day')->read(array('device_unique_id'=>$v['device_unique_id']), ' ORDER BY `id` DESC ');

                // 活跃 不活跃天数
                $res = $this->get_time_diff($this->start_time, $this->end_time, $v['add_time'], $active_filter);

                $list[$k]['unonline_day'] = $res['unactive_count'];
                $list[$k]['active_day']   = $res['active_count'];

                if ($last_active) {
                    $list[$k]['last_active'] = $last_active['update_time'];
                }
            }

            // 是否活跃
            $list[$k]['active_status']   = $online_info ? 1 : 0;

            if ($order_field == 'experience_times') {
                $keys[$k] = array_sum($t_count);
            }
        }

        if ($keys) {
            if ($order_dir == 'desc') {
                array_multisort ($keys, SORT_DESC, $list);
            } else {
                array_multisort ($keys, SORT_ASC, $list);
            }
        }

        Response::assign('search_filter' , $search_filter);
        Response::assign('hall_title', $hall_title);
        Response::assign('list', $list);
        Response::assign('order_dir', $order_dir);
        Response::assign('order_field', $order_field);
        Response::assign('region_id', $region_id);
        Response::assign('region_type', $region_type);
        Response::assign('count_experience_time', array_sum($count_time));

        Response::display('admin/device_stat/new_device_list.html');

    }

    /**
     时间总天数
     */
    private function diff_betweentwo_days ($s_time, $e_time)
    {
        if ($s_time < $e_time) {
            $tmp = $e_time;
            $e_time = $s_time;
            $s_time = $tmp;
        }

        $test = $s_time - $e_time;

        //return ($s_time - $e_time) / 86400;

        return date("d", $s_time - $e_time);
    }

    /**
     * 计算活跃 不活跃天数
     * @param diff_start_time 搜索开始时间
     * @param diff_end_time   搜索结束时间
     * @param add_time        设备添加时间
     * @param filter          搜索活跃天数条件
     */
    private function get_time_diff($diff_start_time, $diff_end_time, $add_time, $filter)
    {
        if (!$diff_start_time || !$diff_end_time || !$add_time) {
            return array('active_count' => 0, 'unactive_count'=>0);
        }

        $start_time = strtotime($diff_start_time);
        $end_time   = strtotime($diff_end_time);
        $time       = strtotime(date("Ymd", strtotime($add_time)));
        $day        = strtotime(date("Ymd"));
        $time_count = 0;

        // 添加时间在 搜索时间区间内
        if ($time >= $start_time && $time <= $end_time) {

            $active_filter['day >=']    = date("Ymd", strtotime($add_time));

            if ($end_time > $day) {
                $time_count              = $this->diff_betweentwo_days($time, $day);

                $active_filter['day <='] = date("Ymd");
            } else {
                $time_count              = $this->diff_betweentwo_days($time, $end_time);

                $active_filter['day <='] = $diff_end_time;
            }
        }

            // 添加时间 小于 搜索时间区间
        if ($time < $start_time && $time < $end_time) {
            $time_count                   = $this->diff_betweentwo_days($start_time, $end_time);

            $active_filter['day >=']      = $diff_start_time;

            if ($end_time > $day) {
                $active_filter['day <=']      = date("Ymd");
            } else {

                $active_filter['day <=']      = $diff_end_time;
            }
        }


        // 添加时间 大于 搜索时间区间
        if ($time > $start_time && $time > $end_time) {

            return array('active_count' => 0, 'unactive_count'=>0);
        }

        $active_filter['province_id']      = $filter['province_id'];
        $active_filter['device_unique_id'] = $filter['device_unique_id'];

        // 活跃数
        $active_list       = _model('screen_device_online_stat_day')->getList($active_filter, ' GROUP BY `day` ');


        // 不活跃数
        $unactive_count     = $time_count-count($active_list);

        if ($unactive_count <= 0) {
            $unactive_count = 0;
        }

        return array('active_count' => count($active_list), 'unactive_count'=>$unactive_count);
    }
    /**
     * 导出
     */
    public function is_export($list, $type = 0, $active_filter=array())
    {
        if (!$list) {
            return '暂无数据';
        }

        if ($type == 1) {
            unset($active_filter['status']);
            $i = 0;
            foreach ($list as $k=>$v) {

                $active_filter['device_unique_id'] = $v['device_unique_id'];

                $active_info = _model('screen_device_online_stat_day')->read($active_filter);

                $info[$k]['proinvce_id']      = business_hall_helper::get_info_name('province', $v['province_id'],  'name');
                $info[$k]['city_id']          = business_hall_helper::get_info_name('city', $v['city_id'], 'name');
                $info[$k]['area_id']          = business_hall_helper::get_info_name('area', $v['area_id'], 'name');
                $info[$k]['business_hall_id'] = business_hall_helper::get_info_name('business_hall', $v['business_id'], 'title');
                $info[$k]['phone_name']       = $v['phone_name_nickname'] ? $v['phone_name_nickname'] : $v['phone_name'];
                $info[$k]['phone_version']    = $v['phone_version_nickname']? $v['phone_version_nickname'] : $v['phone_version'];
                $info[$k]['device_unique_id'] = $v['device_unique_id'];
                $info[$k]['imei']             = $v['imei'] ? $v['imei'] : '手机无imei';
                //$info[$k]['add_time']         = substr($v['add_time'], 0, 10);
                //$experience_time              = array_sum(_model('screen_action_record')->getFields('experience_time', array('device_unique_id'=>$v['device_unique_id'])));
                //             $experience_time              = array_sum(_model('screen_action_record')->getFields('experience_time', array('device_unique_id'=>$v['device_unique_id'], 'business_id'=>$v['business_id'])));
                //             $info[$k]['experience_time']  = screen_helper::format_timestamp_text($v['experience_time']);

                $active_filter['business_id'] = $v['business_id'];

                $exper_time = _mongo('screen', 'screen_action_record')->find(get_mongodb_filter($active_filter), array('projection'=>['experience_time'=>1]));

                $t_count = array();

                foreach ($exper_time as $vv) {

                    $t_count[$i] = $vv['experience_time'];
                    $i++;
                }

                //$device_list[$k]['experience_time'] = array_sum(_model('screen_action_record')->getFields('experience_time', array('device_unique_id'=>$v['device_unique_id'])));
                $info[$k]['experience_time'] = screen_helper::format_timestamp_text(array_sum($t_count));

                $online                       = screen_helper::get_online_status($v['device_unique_id']);

                $info[$k]['online']           = $online ? '在线' : '不在线';
                $info[$k]['active']           = $active_info ? '活跃' : '不活跃';

                //if ($date_type != 2) {

                    $last_active = _model('screen_device_online_stat_day')->read(array('device_unique_id'=>$v['device_unique_id']), ' ORDER BY `id` DESC ');

                    // 活跃 不活跃天数
                    $res = $this->get_time_diff($this->start_time, $this->end_time, $v['add_time'], $active_filter);

                    $info[$k]['active_day']   = $res['active_count'];
                    $info[$k]['unonline_day'] = $res['unactive_count'];


                    $info[$k]['add_time']     = $v['add_time'];
                    if ($last_active) {
                        $info[$k]['last_active'] = $last_active['update_time'];
                    }
                //}
            }
        } else {
            foreach ($list as $k=>$v) {
                $info[$k]['proinvce_id']      = business_hall_helper::get_info_name('province', $v['province_id'],  'name');
                $info[$k]['city_id']          = business_hall_helper::get_info_name('city', $v['city_id'], 'name');
                $info[$k]['area_id']          = business_hall_helper::get_info_name('area', $v['area_id'], 'name');
                $info[$k]['business_hall_id'] = business_hall_helper::get_info_name('business_hall', $v['business_id'], 'title');
                $info[$k]['device_num']       = $v['new_device_num'];
                $info[$k]['active_num']       = $v['active_num'];
                $info[$k]['e_time']           = screen_helper::format_timestamp_text($v['e_time']);
            }
        }

        //an_dump($info); exit;

        $params['filename'] = '亮屏设备';
        $params['data']     = $info;
        if ($type == 1) {
            $params['head'] = array('所属省', '所属市', '所属区县', '营业厅名称', '手机品牌', '手机型号', '标识ID', 'IMEI', '体验时长', '在线状态', '活跃状态', '活跃天数', '离线天数', '添加时间', '最后活跃时间');
        } else {
            $params['head']     = array('所属省', '所属市', '所属区县', '营业厅名称', '新增设备数', '活跃数', '体验时长');
        }


        Csv::getCvsObj($params)->export();
    }

    /*
     * =====================================处理搜索条件=====================================================
     */
    /**
     * 处理搜索条件
     */
    private function search_filter()
    {
        $search_filter  = tools_helper::get('search_filter', array());
        $filter         = array();
        $start_time     = '';
        $end_time       = '';

        //搜索的日期类型
        if (isset($search_filter['date_type']) && in_array($search_filter['date_type'], self::$date_types)) {
            $this->date_type        = $search_filter['date_type'];
        } else {
            //0待分配
            $this->date_type = 0;
        }

        //搜索条件字符串，用于子页面继承
        $search_filter_str  = '?search_filter[date_type]='.$this->date_type;

        //日期类型为0，待分配
        if ($this->date_type == 0) {

            //按指定时间段搜索
            if (isset($search_filter['start_time']) && $search_filter['start_time']) {
                $search_filter_str  .= '&search_filter[start_time]='.$search_filter['start_time'];

                $start_time = $search_filter['start_time'].' 00:00:00';
            }

            if (isset($search_filter['end_time']) && $search_filter['end_time']) {

                $search_filter_str  .= '&search_filter[end_time]='.$search_filter['end_time'];
                $end_time = $search_filter['end_time'].' 23:59:59';
            }

            //按照时间段规则设置日期类型
            if ($this->set_date_type($start_time, $end_time) === false) {
                return false;
            }
        }

        //兼容首次进来
        if ($this->date_type == 2) {
            $search_filter['date_type'] = $this->date_type;
        }

        Response::assign('search_filter_str', $search_filter_str);
        Response::assign('search_filter', $search_filter);

        return $this->get_date_type_filter($start_time, $end_time);

    }

    /**
     * 获取日期条件
     */
    private function get_date_type_filter($start_time='', $end_time='')
    {
        if (!$start_time) {
            $start_time = date('Y-m-d 00:00:00');
        }

        if (!$end_time) {
            $end_time = date('Y-m-d 23:59:59');
        }

        $filter = array();

        //按今天 或 按指定时间段（小时展示）
        if ($this->date_type == 2 || $this->date_type == 5) {

            list($ymd, $his) = explode(' ', $start_time);
            if (!$ymd || !$his) {
                $this->error_info = '日期格式错误';
                return false;
            }

            $filter['day'] = str_replace('-', '', $ymd);

            //按本周
        } else if ($this->date_type == 3) {

            //获取本周开始日期和结束日期
            $date_info          = screen_helper::get_day_by_time($start_time);

            list($start)        = explode(' ', $date_info['start']);
            list($end)          = explode(' ', $date_info['end']);
            $filter['day >='] = str_replace('-', '', $start);
            $filter['day <='] = str_replace('-', '', $end);

            //按本月
        } else if ($this->date_type == 4) {
            $timestamp = strtotime($start_time);
            $filter['day >='] = date('Ym01', $timestamp);
            $filter['day <'] =  date('Ym01', strtotime('+1 month', $timestamp));

            //按时间段（天展示），注：当 date_type == 7时， 不能查询月表，因为选择的时间段不一定是整月
        } else if ($this->date_type == 6 || $this->date_type == 7) {
            $start_timestamp = strtotime($start_time);
            $end_timestamp   = strtotime($end_time);
            $filter['day >='] = date('Ymd', $start_timestamp);
            $filter['day <='] =  date('Ymd', $end_timestamp);

        } else {
            $this->error_info = '不存在的日期类型';
            return false;
        }

        return $filter;

    }

    /**
     * 设置日期类型
     * @param unknown $start_time
     * @param unknown $end_time
     */
    private function set_date_type($start_time, $end_time)
    {
        if ($this->date_type != 0) {
            return true;
        }

        //没有时间段条件则默认为今日
        if (!$start_time && !$end_time) {
            $this->date_type = 2;
            return true;
        }

        if (!$start_time) {
            $start_time = date('Y-m-d 00:00:00');
        }

        if (!$end_time) {
            $end_time = date('Y-m-d 23:59:59');
        }

        //时间转换为Ymd格式
        $start_Ymd = date('Ymd', strtotime($start_time));
        $end_Ymd   = date('Ymd', strtotime($end_time));

        //同一天, 此时间段内按小时展示
        if ($end_Ymd == $start_Ymd) {
            $this->date_type = 5;
            return true;
        }

        //32天内，此时间段内按天展示
        //         if ($end_Ymd - $start_Ymd <= 32) {
        //             $this->date_type = 6;
            //             return true;
            //         }

            //按照当前的统计规则，按月展示有问题，
            if ($end_Ymd - $start_Ymd >= 1) {
                $this->date_type = 6;
                return true;
            }

            //大于32天，按月展示
            //         if ($end_Ymd - $start_Ymd > 32) {
            //             $this->date_type = 7;
            //             return true;
            //         }

            $this->error_info= '系统错误';
            return false;


        }
}