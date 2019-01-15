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

    public $start_time;
    public $end_time;

    public function device()
    {
        if (!$this->member_info) {
            return '您无权访问此页面';
        }

        //搜索条件
        $search_filter  = Request::Get('search_filter', array());
        $page_no        = tools_helper::get('page_no', 1);
        $type           = tools_helper::get('type', 0);
        $order_dir     = tools_helper::get('order_dir', 'desc');
        $order_field   = tools_helper::get('order_field', 'experience_times');
        $hall_title    = tools_helper::get('hall_title', '');
        $device_unique_id = tools_helper::get('device_unique_id', '');
        $is_export        = tools_helper::get('is_export', 0);
        $phone_name       = tools_helper::get('phone_name', '');
        $phone_version    = tools_helper::get('phone_version', '');
        $is_group         = tools_helper::get('is_group', 0);
        $debug            = tools_helper::get('debug', 0);

        $date_time        = $this->search_filter();
        //p();
        $region_type   = '';
        $region_id     = 0;

        $filter = array();
        $active_filter = $filter = $this->region_filter();
        $filter['province_id'] = 0;

        if ($is_group == 1) {
            if ($this->res_name == 'business_hall') {
                $filter['business_id'] = $this->res_id;
            } else if ($this->res_name != 'group') {
                $filter[$this->res_name.'_id'] = $this->res_id;
            }
        } else {
            if (isset($search_filter['region_id']) && $search_filter['region_id']) {
                $region_id = $search_filter['region_id'];
            }

            if (!empty($search_filter['region_type']) && in_array($search_filter['region_type'], array('province', 'city', 'area', 'business_hall'))) {
                $region_type = $search_filter['region_type'];
                if ($region_id) {
                    if ($region_type == 'business_hall') {
                        $filter['business_id'] = $region_id;
                    } else {
                        $filter["{$region_type}_id"] = $region_id;
                    }
                }
            }
        }

        if ($hall_title) {
            $business_id = _uri('business_hall', array('title LIKE '=> '%'.$hall_title.'%' ), 'id');

            $filter['business_id']  = $business_id;
        }

        if (!empty($search_filter['province_id'])) {
            $filter['province_id'] = $search_filter['province_id'];
        }

        if (!empty($search_filter['city_id'])) {
            $filter['city_id'] = $search_filter['city_id'];
        }

        if (isset($search_filter['area']) && !empty($search_filter['area'])) {
            $filter['area_id'] = $search_filter['area'];
        }

        if ($filter['province_id'] == 0) {
            unset($filter['province_id']);
        }
        if (isset($search_filter['business_id']) && !empty($search_filter['business_id'])) {
            $filter['business_id']       = $search_filter['business_id'];
            $active_filter['business_id'] = $search_filter['business_id'];
            $action_filter['business_id'] = $search_filter['business_id'];
        }

        if ($phone_name) {
            $filter['phone_name'] = $phone_name;
        }

        if ($phone_version) {
            $info = _uri('screen_device', array('phone_version_nickname'=>$phone_version));
            if ($info) {
                $filter['phone_version_nickname'] = $phone_version;
            } else {
                $filter['phone_version'] = $phone_version;
            }
        }
        $device_unique_ids = array();

        if ((!empty($search_filter['online_status']) && in_array($search_filter['online_status'], array(1, 2))) || $type == 3){
            $online_filter['day']       = date('Ymd');
            $online_filter['update_time >='] = date('Y-m-d H:i:s', time()-1800);

            $device_unique_ids = _model('screen_device_online_stat_day')->getFields('device_unique_id', $online_filter, ' GROUP BY `device_unique_id`');
        }

        if (isset($date_time['day'])) {
            $active_filter['day'] = $date_time['day'];
        }
        if (isset($date_time['day >='])) {
            $active_filter['day >='] = $date_time['day >='];
            $this->start_time               = $date_time['day >='];
        }
        if (isset($date_time['day <'])) {
            $active_filter['day <'] = $date_time['day <'];
            $this->end_time         = $date_time['day <'];
        }
        if (isset($date_time['day <='])) {
            $active_filter['day <='] = $date_time['day <='];
            $this->end_time          = $date_time['day <='];
        }
        if (isset($filter['province_id']) && $filter['province_id'] != 0) {
            $active_filter['province_id'] = $filter['province_id'];
        }

        if ((!empty($search_filter['active_status']) && in_array($search_filter['active_status'], array(1, 2))) || $type == 2){
            //$active_filter['day']  = date('Ymd');
            $device_unique_ids     = _model('screen_device_online_stat_day')->getFields('device_unique_id', $active_filter, ' GROUP BY `device_unique_id` ');
        }

        if ((!empty($search_filter['active_status']) && $search_filter['active_status'] == 2) || (!empty($search_filter['online_status']) && $search_filter['online_status'] == 2)) {
            $filter['device_unique_id !=']  = $device_unique_ids;
        } else {
            $filter['device_unique_id']     = $device_unique_ids;
        }

        $filter['status'] = 1;

        //$device_list = get_data_list('screen_device', $filter, ' ORDER BY `province_id` ASC, `id` DESC ', $page_no, $this->per_page);
        $device_list = array();
        //单独处理离线
        if ( !empty($search_filter['active_status']) && $search_filter['active_status'] == 2 || !empty($search_filter['online_status']) && $search_filter['online_status'] == 2){
            //if ($device_unique_ids) {

            $sql_filter = $this->to_where($filter);

            $limit_start = ($page_no-1)*$this->per_page;

            $device_count_info = _model('screen_device')->getAll(" SELECT count(*) as count_total FROM `screen_device` {$sql_filter} ");
            $list = _model('screen_device')->getAll(" select * from screen_device {$sql_filter} ");

            $count = $device_count_info[0]['count_total'];
            if ($count) {
                $pager = new Pager($this->per_page);
                if ($pager->generate($count)) {
                    Response::assign('pager', $pager);
                }

                $limit = $pager->getLimit($page_no);

                if ($is_export == 1) {
                    $export_list = _model('screen_device')->getAll(" SELECT * FROM `screen_device` {$sql_filter}");

                }

                $device_list = _model('screen_device')->getAll(" SELECT * FROM `screen_device` {$sql_filter} $limit");

                Response::assign('count', $count);
                //}

            }
        } else {
            //p($device_filter);
            if ($is_export == 1) {
                $device_list = _model('screen_device')->getList($filter, ' ORDER BY `province_id` ASC, `id` DESC ');
            } else {
                $device_list = get_data_list('screen_device', $filter, ' ORDER BY `province_id` ASC, `id` DESC ', $page_no, $this->per_page);
            }

            //$device_list = get_data_list('screen_device', $filter, ' ORDER BY `province_id` ASC, `id` DESC ', $page_no, $this->per_page);
        }

        //筛选总天数
        $day_count = 0;

        $new_filter = array();
        $keys       = array();
        $t_count    = array();
        $i          = 0;
        $k          = 0;
        $ct_count   = array();
        $count_ex_time = _mongo('screen', 'screen_action_record')->find(get_mongodb_filter($active_filter), array('projection'=>['experience_time'=>1]));
        foreach ($count_ex_time as $cxv) {

            $ct_count[$k] = $cxv['experience_time'];
            $k++;
        }

        foreach ($device_list as $k => $v) {

            $active_filter['device_unique_id'] = $v['device_unique_id'];
            $action_filter = $active_filter;
            $action_filter['type'] = 2;
            // 体验时长
            $exper_time = _mongo('screen', 'screen_action_record')->find(get_mongodb_filter($action_filter), array('projection'=>['experience_time'=>1]));
            $t_count = array();

            foreach ($exper_time as $vv) {

                $t_count[$i] = $vv['experience_time'];
                $i++;
            }

            $device_list[$k]['experience_time'] = array_sum($t_count);
            // 是否在线过
            $online_info = _model('screen_device_online_stat_day')->read($active_filter);

            if (isset($search_filter['date_type']) && $search_filter['date_type'] !=2) {

                //最后活跃时间
                $last_active = _model('screen_device_online_stat_day')->read(array('device_unique_id'=>$v['device_unique_id']), ' ORDER BY `id` DESC ');

                // 活跃 不活跃天数
                $res = $this->get_time_diff($this->start_time, $this->end_time, $v['add_time'], $active_filter, $debug);

                $device_list[$k]['unonline_day'] = $res['unactive_count'];
                $device_list[$k]['active_day']   = $res['active_count'];

                if ($last_active) {
                    $device_list[$k]['last_active'] = $last_active['update_time'];
                } else {
                    $device_list[$k]['last_active'] = '';
                }
            }

            // 是否活跃
            $device_list[$k]['active_status']   = $online_info ? 1 : 0;

            if ($order_field == 'experience_times') {
                $keys[$k] = array_sum($t_count);
            }
        }

        if ($is_export == 1) {
            $this->is_export($device_list, $search_filter['date_type'], $active_filter);
        }

        //排序
        $experience_times_count = array_sum($keys);

        if ($keys && $device_list) {

            if ($order_dir == 'desc') {
                array_multisort ($keys, SORT_DESC, $device_list);
            } else {
                array_multisort ($keys, SORT_ASC, $device_list);
            }

        }

        Response::assign('search_filter' , $search_filter);
        Response::assign('hall_title', $hall_title);
        Response::assign('device_list', $device_list);
        Response::assign('order_dir', $order_dir);
        Response::assign('order_field', $order_field);
        Response::assign('region_id', $region_id);
        Response::assign('region_type', $region_type);
        Response::assign('type', $type);
        Response::assign('phone_name', $phone_name);
        Response::assign('phone_version', $phone_version);
        Response::assign('is_group', $is_group);
        Response::assign('count_experience_time', array_sum($ct_count));

        if (isset($active_filter['day <'])) {
            Response::assign('active_stop_time', $active_filter['day <']);
        }
        if (isset($active_filter['day <='])) {
            Response::assign('active_stop_time', $active_filter['day <=']);
        }
        if (isset($active_filter['day >='])) {
            Response::assign('active_start_time', $active_filter['day >=']);
        }

        Response::display('admin/device_stat/device_list.html');
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
    private function get_time_diff($diff_start_time, $diff_end_time, $add_time, $filter, $debug=0)
    {
        if (!$diff_start_time || !$diff_end_time || !$add_time) {
            return array('active_count' => 0, 'unactive_count'=>0);
        }

        $start_time = strtotime($diff_start_time);
        $end_time   = strtotime($diff_end_time);
        $time       = strtotime(date("Ymd", strtotime($add_time)));
        $day        = strtotime(date("Ymd"));
        $time_count = 0;
        $active_filter = array();

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

        //$active_filter['province_id']      = $filter['province_id'];
        $active_filter['device_unique_id'] = $filter['device_unique_id'];

        // 活跃数
        $active_list       = _model('screen_device_online_stat_day')->getList($active_filter, ' GROUP BY `day` ');

        if ($debug == 1) {
            p($active_filter, $active_list, $start_time, $end_time);
        }

        // 不活跃数
        $unactive_count     = $time_count-count($active_list);

        if ($unactive_count <= 0) {
            $unactive_count = 0;
        }

        return array('active_count' => count($active_list), 'unactive_count'=>$unactive_count);
    }
    /**
     * 数组条件转换where语句
     * @param unknown $filter
     * @return string
     */
    private function to_where($filter)
    {
        if (!$filter) {
            return '';
        }

        $where = '';

        if (is_array($filter)) {

            foreach ($filter as $k => $v) {

                if ( !$where ) {
                    $where = " WHERE ";
                }

                if (is_array($v) && strpos($k, '!=') !== false) {
                    foreach ($v as $v2) {
                        $where .= " {$k}'{$v2}' AND";
                    }

                    continue;
                }

                if (strpos($k, '!=') !== false) {
                    $where .= " {$k}'{$v}' AND";
                    continue;
                }

                if ( strpos($k, '<') || strpos($k, '>') ) {
                    $where .= " {$k}{$v} AND";
                } else {

                    //an_dump($k, $v);
                    if (is_array($v)) {
                        foreach ($v as $sk => $sv) {
                            $where .= " {$k}='{$sv}' AND";
                        }
                        continue;
                    } else {
                        $where .= " {$k}={$v} AND";
                    }

                }

            }

            $where = rtrim($where, 'AND');

        } else {

            if ( !$where ) {
                $where = " WHERE ";
            }

            $where .= "id={$filter} ";
        }

        return $where;
    }

    /**
     * 导出
     */
    public function is_export($list, $date_type, $active_filter = array())
    {
        if (!$list) {
            return '暂无数据';
        }

        //筛选总天数
        $day_count = 0;
        $i         = 0;

        if (isset($active_filter['day <']) && isset($active_filter['day >='])) {
            $day_count = date("d", strtotime($active_filter['day <'])-strtotime($active_filter['day >=']));
        }

        //an_dump($list, $active_filter);
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

//             $exper_time = _mongo('screen', 'screen_action_record')->find(get_mongodb_filter($active_filter), array('projection'=>['experience_time'=>1]));

//             $t_count = array();

//             foreach ($exper_time as $vv) {

//                 $t_count[$i] = $vv['experience_time'];
//                 $i++;
//             }

            //$device_list[$k]['experience_time'] = array_sum(_model('screen_action_record')->getFields('experience_time', array('device_unique_id'=>$v['device_unique_id'])));
            //$info[$k]['experience_time'] = screen_helper::format_timestamp_text(array_sum($t_count));
            $info[$k]['experience_time']  = $v['experience_time'];
            $online                       = screen_helper::get_online_status($v['device_unique_id']);

            $info[$k]['online']           = $online ? '在线' : '不在线';
            $info[$k]['active']           = $v['active_status'] ? '活跃' : '不活跃';

            if ($date_type != 2) {

//                 $last_active = _model('screen_device_online_stat_day')->read(array('device_unique_id'=>$v['device_unique_id']), ' ORDER BY `id` DESC ');

//                 // 活跃 不活跃天数
//                 $res = $this->get_time_diff($this->start_time, $this->end_time, $v['add_time'], $active_filter);

//                 $info[$k]['active_day']   = $res['active_count'];
//                 $info[$k]['unonline_day'] = $res['unactive_count'];

                $info[$k]['active_day']   = $v['active_day'];
                $info[$k]['unonline_day'] = $v['unonline_day'];
                $info[$k]['add_time']     = $v['add_time'];

                $info[$k]['last_active'] = $v['last_active'];

            }
        }
//         p($info); exit;
        $params['filename'] = '亮屏设备';
        $params['data']     = $info;
        $params['head']     = array('所属省', '所属市', '所属区县', '营业厅名称', '手机品牌', '手机型号', '标识ID', 'IMEI', '体验时长', '在线状态', '活跃状态');
        if ($date_type != 2) {
            $params['head'] = array('所属省', '所属市', '所属区县', '营业厅名称', '手机品牌', '手机型号', '标识ID', 'IMEI', '体验时长', '在线状态', '活跃状态', '活跃天数', '离线天数', '添加时间', '最后活跃时间');
        }
        Csv::getCvsObj($params)->export();
    }
}