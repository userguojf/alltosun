<?php

/**
 * alltosun.com 设备统计（营业厅列表页） business_hall_list.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年9月16日 下午9:23:00 $
 * $Id$
 */

load_file('screen_stat','trait', 'stat');

class Action
{
    use stat;

    public function __construct()
    {
        $this->member_id   = member_helper::get_member_id();
        $this->member_info = member_helper::get_member_info($this->member_id);
        $this->res_name    = $this->member_info['res_name'];
        $this->res_id      = $this->member_info['res_id'];

        switch($this->member_info['res_name'])
        {
            case 'group':
                $this->nick_name  = '全国';
                $this->next_res_name = 'province';
                $this->next_res_name_id = 'province_id';
                break;
            case 'province':
                $this->nick_name  = _uri($this->res_name,$this->res_id,'name');
                $filter           = array('province_id' => $this->res_id);
                $this->next_res_name = 'city';
                $this->next_res_name_id = 'city_id';
                break;
            case 'city':
                $this->nick_name  = _uri($this->res_name,$this->res_id,'name');
                $filter           = array('city_id' => $this->res_id);
                $this->next_res_name = 'area';
                $this->next_res_name_id = 'area_id';
                break;
            case 'area':
                $this->nick_name  = _uri($this->res_name,$this->res_id,'name');
                $filter           = array('area_id' => $this->res_id);
                $this->next_res_name = 'business_hall';
                break;
            case 'business_hall':
                $this->nick_name  = _uri('business_hall',$this->res_id,'title');
                $filter           = array('business_id' => $this->res_id);
                $this->res_name = 'business';
                $this->next_res_name = 'business';
                $this->next_res_name_id = 'business_id';
                break;
        }
    }

    public function business_hall_list()
    {
        if (!$this->member_info) {
            return '您无权访问此页面';
        }

        //区域条件
        $filter    = $this->region_filter();
        $is_export = tools_helper::get('is_export', 0);

        //搜索条件
        $search_filter = $this->search_filter();

        if ($search_filter === false) {
            return $this->error_info;
        }

        $filter = array_merge($filter, $search_filter);

        //处理本页面特有的条件
        $search_filter = tools_helper::Get('search_filter', array());
        $type          = tools_helper::Get('type', 0);
        $order_dir     = tools_helper::get('order_dir', '');
        $order_field   = tools_helper::get('order_field', '');
        $page_no       = tools_helper::get('page_no', 1);
        $is_group      = tools_helper::get('is_group', 0);
        $per_page      = 15;

        $region_id      = 0;
        $region_type    = '';


        if (isset($search_filter['region_id']) && $search_filter['region_id']) {
            $region_id = $search_filter['region_id'];
        }

        if (isset($search_filter['region_type']) && in_array($search_filter['region_type'], array('province', 'city', 'area', 'business_hall'))) {
            $region_type = $search_filter['region_type'];
        }

        $device_filter = $filter;

        $device_filter['status'] = 1;

        $new_filter = array();
        $new_filter = $this->region_filter();

        if ($region_type && $region_id) {
            if ($region_type == 'business_hall') {
                $device_filter['business_id'] = $region_id;
            } else {
                $device_filter["{$region_type}_id"] = $region_id;
            }
        }

        //搜索判断
        if (isset($search_filter['province']) && !empty($search_filter['province'])) {
            $new_filter['province_id'] = $filter['province_id'] = $device_filter['province_id'] = $search_filter['province'];
            $province = array('province_id' => $search_filter['province']);
            Response::assign('where1' , $province);
        }

        if (isset($search_filter['city']) && !empty($search_filter['city'])) {
            $new_filter['city_id'] = $filter['city_id'] = $device_filter['city_id'] = $search_filter['city'];

            $city = array('city_id' => $search_filter['city']);
            Response::assign('where2' , $city);
        }

        if (isset($search_filter['area']) && !empty($search_filter['area'])) {

            $new_filter['area_id'] = $filter['area_id'] = $device_filter['area_id'] = $search_filter['area'];
        }

        if (isset($search_filter['search_type_value']['title']) && $search_filter['search_type_value']['title']) {
            $business_id = _uri('business_hall', array('title LIKE '=> '%'.$search_filter['search_type_value']['title'].'%' ), 'id');

            $new_filter['business_id'] = $device_filter['business_id'] = $filter['business_id']  = $business_id;
        }

        if (isset($filter['hour'])) {
            unset($filter['hour']);
        }


        if (isset($device_filter['hour'])) {
            unset($device_filter['hour']);
            unset($device_filter['day']);
        }

        $device_buiness_hall_list = array();
        //$new_filter = array('province_id'=>$device_filter['province_id'], 'status'=>1);

        if (isset($device_filter['province_id']) && $device_filter['province_id']) {
            $new_filter['province_id'] = $device_filter['province_id'];

            $order = ' GROUP BY `business_id`  ORDER BY `id` ASC ';
        } else {

            $order = ' GROUP BY `business_id` ORDER BY `province_id` ASC ';
        }

        $new_filter['status']  = 1;

        //$device_buiness_hall_list = get_data_list('screen_device', array('status'=>1), ' ORDER BY `id` DESC ' , $page_no, 1);
        if (!empty($device_filter['day >='])) $new_filter['day >='] = $device_filter['day >='];
        if (!empty($device_filter['day <='])) $new_filter['day <='] = $device_filter['day <='];

        $business_hall_ids = _model('screen_device')->getFields('business_id', $new_filter, ' GROUP BY `business_id` ');
        $count = count($business_hall_ids);

        if ($count) {
            $pager = new Pager($per_page);

            $device_buiness_hall_list = _model('screen_device')->getList($new_filter, ' GROUP BY `business_id` '.$pager->getLimit($page_no));

            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }

            Response::assign('count', $count);
        }

        //获取所有有设备的营业厅列表
        //$device_buiness_hall_list = _model('screen_device')->getList($new_filter, ' GROUP BY `business_id` ');

        $device_sum         = 0;
        $active_sum         = 0;
        $experience_sum     = 0;
        $online_num         = 0;
        $online_device_num  = 0;
        $device_business_hall_list = array();
        $keys               = array();
        $res_id             = 0;

        $active_filter = $filter;

        foreach ($device_buiness_hall_list as $k => $v){
            $res_id  = $v[$this->next_res_name_id];

            $device_business_hall_list[$k]['province']      = _uri('province', $v['province_id'], 'name');
            $device_business_hall_list[$k]['city']          = _uri('city', $v['city_id'], 'name');
            $device_business_hall_list[$k]['area']          = _uri('area', $v['area_id'], 'name');
            $device_business_hall_list[$k]['business_hall'] = _uri('business_hall', $v['business_id'], 'title');
            $device_business_hall_list[$k]['user_number']   = _uri('business_hall', $v['business_id'], 'user_number');
            $device_business_hall_list[$k]['business_id']   = $v['business_id'];
            //查询设备总量
            $device_business_hall_list[$k]['device_num']    = _model('screen_device')->getTotal(array('status' => 1, 'business_id' => $v['business_id']));
            //查询活跃量， 此处拼接上上级的时间
            $active_filter['business_id'] = $v['business_id'];

            $device_unique_ids = _model('screen_device_online_stat_day')->getFields('device_unique_id', $active_filter, ' GROUP BY `device_unique_id` ');

            $device_business_hall_list[$k]['active_num']    = count($device_unique_ids);

            //体验时长， 查询条件和活跃一样，所以引用活跃的filter, 为减少操作，取体验统计表的数据
            $experience_time_list = _model('screen_business_stat_day')->getFields('experience_time', $active_filter);

            $device_business_hall_list[$k]['experience_time'] = array_sum($experience_time_list);

            if (isset($search_filter['date_type']) && $search_filter['date_type'] == 2) {
                $online_filter=array(
                    'province_id' => $v['province_id'],
//                     'city_id'     => $v['city_id'],
//                     'area_id'     => $v['area_id'],
                    'business_id' => $v['business_id'],
                    'update_time >=' => date('Y-m-d H:i:s', time()-1800)
                );
                $online_num = count(_model('screen_device_online_stat_day')->getList($online_filter, ' GROUP BY device_unique_id, business_id '));

                $device_business_hall_list[$k]['online_num'] = $online_num;
            }

            if ($order_field == 'experience_times') {
                $keys[$k] = array_sum($experience_time_list);
            } elseif ($order_field == 'active') {
                $keys[$k] = count($device_unique_ids);
            } elseif ($order_field == 'device') {
                $keys[$k]  = _model('screen_device')->getTotal(array('status' => 1, 'business_id' => $v['business_id']));
            } elseif ($order_field == 'online') {
                $keys[$k]  = $online_num;
            }

        }

        $count_filter   = $filter;
        $count_e_filter = $filter;

        //if (!$region_id) {
        if ($this->res_name == 'group') {
            $device_uniques = _model('screen_device')->getFields('device_unique_id', array('status'=>1), ' GROUP BY `device_unique_id` ');
            $count_e_filter[1] = 1;

        } else {

            if ($this->res_name == 'business_hall') {
                $field = 'business_id';
            } else {
                $field = $this->res_name.'_id';
            }

            $device_uniques = _model('screen_device')->getFields('device_unique_id', array($field=>$this->res_id, 'status'=>1), ' GROUP BY `device_unique_id` ');

            $count_e_filter[$field] = $this->res_id;
        }

        $device_sum     = count($device_uniques);
        $count_filter['device_unique_id'] = $device_uniques;
        $active_sum     = count(_model('screen_device_online_stat_day')->getFields('device_unique_id', $count_filter, ' GROUP BY `device_unique_id` '));

        $experience_sum = array_sum(_model('screen_business_stat_day')->getFields('experience_time', $count_e_filter));

        if (isset($search_filter['date_type']) && $search_filter['date_type'] == 2) {

            if ($this->res_name == 'group') {
                $count_online_filter=array(
                    'update_time >=' => date('Y-m-d H:i:s', time()-1800)
                );
            } else {
                if ($this->res_name == 'business_hall') {
                    $field = 'business_id';
                } else {
                    $field = $this->res_name.'_id';
                }

                $count_online_filter=array(
                    $field => $this->res_id,
                    'update_time >=' => date('Y-m-d H:i:s', time()-1800)
                );
            }

            $online_device_num = count(_model('screen_device_online_stat_day')->getList($count_online_filter, ' GROUP BY device_unique_id, business_id '));
        }

        $experience_times_count = array_sum($keys);

        if ($keys && $device_business_hall_list) {

            if ($order_dir == 'desc') {
                array_multisort ($keys, SORT_DESC, $device_business_hall_list);
            } else {
                array_multisort ($keys, SORT_ASC, $device_business_hall_list);
            }

        }

        Response::assign('device_sum', $device_sum);
        Response::assign('active_sum', $active_sum);

        Response::assign('experience_sum', $experience_sum);

        if ($search_filter['date_type'] == 2) {
            Response::assign('online_num', $online_device_num);
        }

        Response::assign('type', $type);
        Response::assign('region_type', $region_type);
        Response::assign('region_id', $region_id);
        Response::assign('order_dir', $order_dir);
        Response::assign('order_field', $order_field);
        Response::assign('date_type', $search_filter['date_type']);
        Response::assign('business_hall_list', $device_business_hall_list);
        Response::assign('business_hall_count', count($device_business_hall_list));
        Response::display('admin/device_stat/business_hall_list.html');

    }

    public function is_export()
    {
        $is_export = tools_helper::get('is_export', 0);
        if (!$is_export) {
            return '';
        }

        screen_stat_helper::export_busienss_device('', $this->member_info['res_name'], $this->member_info['res_id']);
    }
}