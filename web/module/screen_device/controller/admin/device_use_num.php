<?php

/**
 * alltosun.com  device_use_num.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年12月23日 下午6:38:18 $
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

    public function __construct()
    {
        $this->member_id   = member_helper::get_member_id();
        $this->member_info = member_helper::get_member_info($this->member_id);
        $this->res_name    = $this->member_info['res_name'];
        $this->res_id      = $this->member_info['res_id'];

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

    public function bunsiness_num()
    {
        $search_filter    = Request::Get('search_filter', array());
        $page             = tools_helper::get('page_no', 1);
        $hall_title       = Request::Get('hall_title', '');

        $order_dir     = tools_helper::get('order_dir', 'desc');
        $order_field   = tools_helper::get('order_field', 'experience_times');

        $phone_name     = tools_helper::get('phone_name', '');
        $phone_version  = tools_helper::get('phone_version', '');

        $default_filter   = _widget('screen')->default_search_filter($this->member_info);
        $business_id      = tools_helper::get('business_id', 0);

        $is_export        = tools_helper::get('is_export', 0);

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

        if ($phone_name) {
            $filter['phone_name']        = $phone_name;
            $search_filter['phone_name'] = $phone_name;
            //$new_filter['phone_name']    = $phone_name;
        }

        if ($phone_version) {
            $info = _uri('screen_device', array('phone_version_nickname'=>$phone_version));
            if ($info) {
                $filter['phone_version_nickname']     = $phone_version;
                //$new_filter['phone_version_nickname'] = $phone_version;
            } else {
                $filter['phone_version'] = $phone_version;
                //$new_filter['phone_version'] = $phone_version;
            }
            $search_filter['phone_version'] = $phone_version;
        }

        if (!empty($search_filter['start_date'])) {
            //filer[add_time] 营业厅不加时间搜索
            //$filter['add_time >='] = $search_filter['start_date']." 00:00:00";
            $last_filter['add_time <'] = $search_filter['start_date']." 00:00:00";
            $new_filter['add_time >='] = $search_filter['start_date']." 00:00:00";
        } else {
            $new_filter['add_time >=']  = date("Y-m-d")." 00:00:00";
        }

        if (!empty($search_filter['end_date'])) {
            //$filter['add_time <='] = $search_filter['end_date']." 23:59:59";
            $new_filter['add_time <='] = $search_filter['end_date']." 23:59:59";
        } else {
            $new_filter['add_time <=']  = date("Y-m-d")." 23:59:59";
        }

        $filter['status'] = 1;

        //$new_device_filter = $new_filter;

        $new_device_filter['phone_name']    = $filter['phone_name'];
        $new_device_filter['phone_version'] = $filter['phone_version'];
        $new_device_filter['status']        = 1;

        //$list = get_data_list('screen_device', $filter, ' GROUP BY `business_id` ORDER BY `id` DESC ', $page, $this->per_page);
        $list = array();

        if ($is_export == 1) {

            $list = _model('screen_device')->getList($filter, ' GROUP BY `business_id` ORDER BY `id` DESC ');
        } else {

            $count = count(_model('screen_device')->getFields('device_unique_id', $filter, ' GROUP BY `business_id` '));

            if ($count) {
                $pager = new Pager($this->per_page);

                $order = ' GROUP BY `business_id` ORDER BY `id` DESC ';
                $limit = $pager->getLimit($page);

                $list = _model('screen_device')->getList($filter, $order.' '.$limit);

                if ($pager->generate($count)) {
                    Response::assign('pager', $pager);
                }
            }

        }

        foreach ($list as $k => $v) {
            $new_device_filter['business_id'] = $filter['business_id'] = $new_filter['business_id'] = $v['business_id'];

            $new_device_ids                  = _model('screen_device')->getFields('device_unique_id', $new_device_filter, ' GROUP BY device_unique_id ');

            $device_ids                      = _model('screen_device')->getFields('device_unique_id', $filter, ' GROUP BY device_unique_id ');

            if ($device_ids) {
                $new_filter['device_unique_id']  = $device_ids;

                //p($new_filter);
                $active_num                      = count(_model('screen_device_online_stat_day')->getFields('device_unique_id', $new_filter, ' GROUP BY device_unique_id, day '));
            } else {
                $active_num = 0;
            }

            //$active_day                       = count(_model('screen_device_online_stat_day')->getFields('device_unique_id', $new_filter, ' GROUP BY day '));

            $list[$k]['new_device_num']      = count($new_device_ids);
            $list[$k]['active_num']          = $active_num;
            //$list[$k]['active_day']           = $active_day;
        }

        if ($is_export == 1) {
            $this->is_export($list, 1);
        }

        Response::assign('search_filter' , $search_filter);
        Response::assign('list', $list);
        Response::assign('order_dir', $order_dir);
        Response::assign('order_field', $order_field);
        Response::assign('phone_name', $phone_name);
        Response::assign('phone_version', $phone_version);

        Response::display('admin/device_use/new_business_list.html');
    }

    public function device_num_list()
    {
        $search_filter    = Request::Get('search_filter', array());
        $page             = tools_helper::get('page_no', 1);
        $hall_title       = Request::Get('hall_title', '');

        $order_dir     = tools_helper::get('order_dir', 'desc');
        $order_field   = tools_helper::get('order_field', 'experience_times');

        $phone_name     = tools_helper::get('phone_name', '');
        $phone_version  = tools_helper::get('phone_version', '');

        $default_filter   = _widget('screen')->default_search_filter($this->member_info);
        $business_id      = tools_helper::get('business_id', 0);

        $is_export        = tools_helper::get('is_export', 0);

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

        if ($phone_name) {
            $filter['phone_name']        = $phone_name;
            $search_filter['phone_name'] = $phone_name;
            //$new_filter['phone_name']    = $phone_name;
        }

        if ($phone_version) {
            $info = _uri('screen_device', array('phone_version_nickname'=>$phone_version));
            if ($info) {
                $filter['phone_version_nickname']     = $phone_version;
                //$new_filter['phone_version_nickname'] = $phone_version;
            } else {
                $filter['phone_version'] = $phone_version;
                //$new_filter['phone_version'] = $phone_version;
            }
            $search_filter['phone_version'] = $phone_version;
        }

//         if (!empty($search_filter['start_date'])) {
//             $filter['add_time >='] = $search_filter['start_date'];
//             $last_filter['add_time <'] = $search_filter['start_date'];
//             $new_filter['add_time >='] = $search_filter['start_date'];
//         } else {
//             $new_filter['add_time >=']  = date("Y-m-d")." 00:00:00";
//         }

//         if (!empty($search_filter['end_date'])) {
//             $filter['add_time <='] = $search_filter['end_date'];
//             $new_filter['add_time <='] = $search_filter['end_date'];
//         } else {
//             $new_filter['add_time <=']  = date("Y-m-d")." 23:59:59";
//         }

        $filter['status'] = 1;

        $new_device_filter['phone_name']    = $filter['phone_name'];
        if (!empty($filter['phone_version'])) {
            $new_device_filter['phone_version'] = $filter['phone_version'];
        }

        $new_device_filter['status']        = 1;

        //$list = get_data_list('screen_device', $filter, ' GROUP BY `device_unique_id` ORDER BY `id` DESC ', $page, $this->per_page);

        if ($is_export == 1) {

            $list = _model('screen_device')->getList($filter, ' GROUP BY `device_unique_id` ORDER BY `id` DESC ');
        } else {
            $count = count(_model('screen_device')->getFields('device_unique_id', $filter, ' GROUP BY `device_unique_id` '));

            if ($count) {
                $pager = new Pager($this->per_page);

                $order = ' GROUP BY `device_unique_id` ORDER BY `id` DESC ';
                $limit = $pager->getLimit($page);

                $list = _model('screen_device')->getList($filter, $order.' '.$limit);

                if ($pager->generate($count)) {
                    Response::assign('pager', $pager);
                }
            }
        }

        $i = 0;

        foreach ($list as $k => $v) {

            $active_filter['device_unique_id'] = $v['device_unique_id'];

            // 体验时长
            $exper_time = _mongo('screen', 'screen_action_record')->find(get_mongodb_filter($active_filter), array('projection'=>['experience_time'=>1]));

            $t_count = array();

            foreach ($exper_time as $vv) {

                $t_count[$i] = $vv['experience_time'];
                $i++;
            }

            $list[$k]['experience_time'] = array_sum($t_count);

            // 是否在线过
            $online_info = _model('screen_device_online_stat_day')->read(array('day'=>date("Ymd"), 'device_unique_id'=>$v['device_unique_id']));
            //$online_info = screen_helper::get_online_status($v['device_unique_id']);

            //最后活跃时间
            $last_active = _model('screen_device_online_stat_day')->read(array('device_unique_id'=>$v['device_unique_id']), ' ORDER BY `id` DESC ');

            if ($last_active) {
                $update_time             = $last_active['update_time'];
                $list[$k]['last_active'] = $last_active['update_time'];
            } else {
                $update_time             = '';
                $list[$k]['last_active'] = '';
            }
            // 活跃 不活跃天数
            $res = $this->get_time_diff($search_filter['start_date'], $search_filter['end_date'], $v['add_time'], $active_filter);


            $list[$k]['unonline_day'] = $res['unactive_count'];
            $list[$k]['active_day']   = $res['active_count'];

            // 是否活跃
            $list[$k]['active_status']   = $online_info ? 1 : 0;

            if ($order_field == 'experience_times') {
                $keys[$k] = array_sum($t_count);
            }
        }

        if ($is_export == 1) {
            $this->is_export($list);
        }

        Response::assign('search_filter' , $search_filter);
        Response::assign('list', $list);
        Response::assign('order_dir', $order_dir);
        Response::assign('order_field', $order_field);
//         Response::assign('region_id', $region_id);
//         Response::assign('region_type', $region_type);
//         Response::assign('type', $type);
        Response::assign('phone_name', $phone_name);
        Response::assign('phone_version', $phone_version);

        Response::display('admin/device_use/new_device_list.html');
    }

    public function is_export($list, $type = 0)
    {
        if (!$list) {
            return array();
        }

        if ($type == 1) {
            foreach ($list as $k => $v) {
                $info[$k]['proinvce_id']      = business_hall_helper::get_info_name('province', $v['province_id'],  'name');
                $info[$k]['city_id']          = business_hall_helper::get_info_name('city', $v['city_id'], 'name');
                $info[$k]['business_hall_id'] = business_hall_helper::get_info_name('business_hall', $v['business_id'], 'title');
                $info[$k]['device_num']       = $v['new_device_num'];
                $info[$k]['active_num']       = $v['active_num'];
            }
        } else {
            foreach ($list as $k=>$v) {
                $info[$k]['proinvce_id']      = business_hall_helper::get_info_name('province', $v['province_id'],  'name');
                $info[$k]['city_id']          = business_hall_helper::get_info_name('city', $v['city_id'], 'name');
                $info[$k]['area_id']          = business_hall_helper::get_info_name('area', $v['area_id'], 'name');
                $info[$k]['business_hall_id'] = business_hall_helper::get_info_name('business_hall', $v['business_id'], 'title');
                $info[$k]['phone_name']       = $v['phone_name_nickname'] ? $v['phone_name_nickname'] : $v['phone_name'];
                $info[$k]['phone_version']    = $v['phone_version_nickname']? $v['phone_version_nickname'] : $v['phone_version'];
                $info[$k]['device_unique_id'] = $v['device_unique_id'];
                $info[$k]['imei']             = $v['imei'] ? $v['imei'] : '手机无imei';

                $online                       = screen_helper::get_online_status($v['device_unique_id']);
                $info[$k]['online']           = $online ? '在线' : '不在线';

                $info[$k]['active']           = $v['active_status'] ? '活跃' : '不活跃';
                $info[$k]['active_day']       = $v['active_day'];
                $info[$k]['unonline_day']     = $v['unonline_day'];

                $info[$k]['add_time']         = $v['add_time'];

                $info[$k]['last_active']      = $v['last_active'];
                $info[$k]['e_time']           = screen_helper::format_timestamp_text($v['experience_time']);
            }
        }

        $params['filename'] = '亮屏设备';
        $params['data']     = $info;

        if ($type == 1) {
            $params['head'] = array('所属省', '所属市', '营业厅名称', '设备量', '活跃量');

        } else {
            $params['head'] = array('所属省', '所属市', '所属区县', '营业厅名称', '手机品牌', '手机型号', '标识ID', 'IMEI',  '在线状态', '活跃状态', '活跃天数', '离线天数', '添加时间', '最后活跃时间', '体验时长');
        }

        Csv::getCvsObj($params)->export();
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
//         if (!$diff_start_time || !$diff_end_time || !$add_time) {
//             return array('active_count' => 0, 'unactive_count'=>0);
//         }

        if (!$diff_end_time) {
            $diff_end_time = date("Ymd");
        }

        if (!$diff_start_time) {
            $diff_start_time = date("Ymd", strtotime($add_time));
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

                $active_filter['day <='] = date("Ymd", strtotime($diff_end_time));
            }
        }

        // 添加时间 小于 搜索时间区间
        if ($time < $start_time && $time < $end_time) {
            $time_count                   = $this->diff_betweentwo_days($start_time, $end_time);

            $active_filter['day >=']      = date("Ymd", strtotime($diff_start_time));

            if ($end_time > $day) {
                $active_filter['day <=']  = date("Ymd");
            } else {

                $active_filter['day <=']  = date("Ymd", strtotime($diff_end_time));
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

        // 不活跃数
        $unactive_count     = $time_count-count($active_list);

        if ($unactive_count <= 0) {
            $unactive_count = 0;
        }

        return array('active_count' => count($active_list), 'unactive_count'=>$unactive_count);
    }
}