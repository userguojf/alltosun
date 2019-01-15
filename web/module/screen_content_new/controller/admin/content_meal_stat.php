<?php

/**
 * alltosun.com 套餐统计 content_meal_stat.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2018年1月22日 下午5:58:50 $
 * $Id$
 */

class Action
{
    private $per_page        = 18;
    private $member_id       = 0;
    private $res_name = '';
    private $res_id          = 0;
    private $member_info     = array();
    private $ranks           = 0;
    private $next_res_name    = '';
    private $next_res_name_id = '';
    private $nick_name        = '';
    private $p_filter         = array();

    public function __construct()
    {

        $this->member_id   = member_helper::get_member_id();
        $this->mongodb     = _mongo('screen', 'screen_click_record');
        $this->member_info = member_helper::get_member_info($this->member_id);

        if ($this->member_info) {
            $this->res_name = $this->member_info['res_name'];
            $this->res_id   = $this->member_info['res_id'];
            $this->ranks           = $this->member_info['ranks'];
        }

        if (!$this->member_info) {
            return $this->error_info;
        }

        switch($this->member_info['res_name'])
        {
            case 'group':
                $this->nick_name  = '全国';
                $this->next_res_name = 'province';
                $this->next_res_name_id = 'province_id';
                break;
            case 'province':
                $this->nick_name        = _uri($this->res_name,$this->res_id,'name');
                $this->p_filter         = array('province_id' => $this->res_id);
                $this->next_res_name    = 'city';
                $this->next_res_name_id = 'city_id';
                break;
            case 'city':
                $this->nick_name        = _uri($this->res_name,$this->res_id,'name');
                $this->p_filter         = array('city_id' => $this->res_id);
                $this->next_res_name    = 'area';
                $this->next_res_name_id = 'area_id';
                break;
            case 'area':
                $this->nick_name     = _uri($this->res_name,$this->res_id,'name');
                $this->p_filter      = array('area_id' => $this->res_id);
                $this->next_res_name = 'business_hall';
                break;
            case 'business_hall':
                $this->nick_name        = _uri('business_hall',$this->res_id,'title');
                $this->p_filter         = array('business_id' => $this->res_id);
                $this->res_name         = 'business';
                $this->next_res_name    = 'business';
                $this->next_res_name_id = 'business_id';
                break;
        }

        Response::assign('curr_member_ranks', $this->ranks);
    }

    /**
     * 列表
     */
    public function pop_stat()
    {
        $new_filter    = $this->get_search_filter();

        $search_filter = $new_filter['search_filter'];
        $filter        = $new_filter['filter'];
        $page          = $new_filter['page'];
        $type          = $new_filter['type'];
        $table         = $new_filter['table'];
        $field         = $new_filter['field'];
        $res_id        = $new_filter['res_id'];
        $run_time      = tools_helper::get('run_time', '');

        $list     = array();
        $new_list = array();

        $devices   = _model($table)->getFields('device_unique_id', $filter, ' GROUP BY `day`, `device_unique_id`, `business_id` ');
        $count     = count($devices);
        if ($count) {
            $pager = new Pager($this->per_page);

            $group = '  GROUP BY `day`, `device_unique_id`, `business_id` ';
            $order = ' ORDER BY `id` DESC ';
            $limit = $pager->getLimit($page);

            $where = to_where_sql($filter);

            $sql = " SELECT * FROM ( SELECT *, SUM({$field}) as nums FROM `{$table}` {$where} {$group} ) as sql_str {$order} {$limit}";

            $list = _model($table)->getAll($sql);

            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }
        }

        foreach ($list as $k => $v) {
            $new_list[$v['day']]['business_id'][$v['business_id']]  = 1;
            $new_list[$v['day']]['device_unique_id'][]              = $v['device_unique_id'];
            $new_list[$v['day']]['pop_num'][]                       = $v['nums'];
        }

        Response::assign('res_id', $res_id);
        Response::assign('new_list', $new_list);
        Response::assign('search_filter', $search_filter);
        Response::assign('type', $type);
        Response::assign('field', $field);

        if ($field == 'run_time') {
            Response::assign('run_time', $field);
        }

        Response::display('admin/content_meal_stat/stat_count.html');
    }

    /**
     * 营业厅
     */
    public function business()
    {
        $new_filter    = $this->get_search_filter();

        $search_filter = $new_filter['search_filter'];
        $filter        = $new_filter['filter'];
        $page          = $new_filter['page'];
        $type          = $new_filter['type'];
        $table         = $new_filter['table'];
        $field         = $new_filter['field'];
        $res_id        = $new_filter['res_id'];
        $run_time      = tools_helper::get('run_time', '');


        $list     = array();
        $new_list = array();

        $count   = _model($table)->getTotal($filter, ' GROUP BY `business_id`, `device_unique_id` ');
        if ($count) {
            $pager = new Pager($this->per_page);

            $order = '  GROUP BY `business_id`, `device_unique_id`  ORDER BY `id` DESC ';
            $limit = $pager->getLimit($page);

            $list = _model($table)->getList($filter, $order.' '.$limit);

            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }
        }

        foreach ($list as $k => $v) {
            $new_list[$v['business_id']]['day']                                      = $v['day'];
            $new_list[$v['business_id']]['device_unique_id'][$v['device_unique_id']] = 1;
            $new_list[$v['business_id']]['pop_num'][]                                = $v[$field];
        }

        Response::assign('new_list', $new_list);
        Response::assign('search_filter', $search_filter);
        Response::assign('res_id', $res_id);
        Response::assign('type', $type);
        Response::assign('field', $field);
        Response::assign('run_time', $run_time);

        Response::display('admin/content_meal_stat/stat_business.html');
    }

    /**
     * 设备
     */
    public function stat_device()
    {
        $new_filter    = $this->get_search_filter();

        $search_filter = $new_filter['search_filter'];
        $filter        = $new_filter['filter'];
        $page          = $new_filter['page'];
        $type          = $new_filter['type'];
        $table         = $new_filter['table'];
        $field         = $new_filter['field'];
        $res_id        = $new_filter['res_id'];
        $run_time      = tools_helper::get('run_time', '');


        $list     = array();
        $new_list = array();

        $count   = _model($table)->getTotal($filter, ' GROUP BY `business_id` ');
        if ($count) {
            $pager = new Pager($this->per_page);

            $order = '  GROUP BY `business_id`, `device_unique_id`  ORDER BY `id` DESC ';
            $limit = $pager->getLimit($page);

            $list = _model($table)->getList($filter, $order.' '.$limit);

            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }
        }

        Response::assign('list', $list);
        Response::assign('search_filter', $search_filter);
        Response::assign('res_id', $res_id);
        Response::assign('type', $type);
        Response::assign('field', $field);

        if ($field == 'run_time') {
            Response::assign('run_time', $field);
        }

        Response::display('admin/content_meal_stat/stat_device.html');
    }

    /**
     * 记录
     */
    public function stat_record()
    {
        $new_filter    = $this->get_search_filter();

        $search_filter = $new_filter['search_filter'];
        $filter        = $new_filter['filter'];
        $page          = $new_filter['page'];
        $type          = $new_filter['type'];
        $res_id        = $new_filter['res_id'];
        $run_time      = tools_helper::get('run_time', '');

        // 弹出
        if ($type == 1) {
            $table = 'screen_content_meal_pop_record';
            $field = 'pop_num';
        } else {
            // 点击
            $table = 'screen_content_meal_record';
            $field = 'action_num';
        }

        if ($run_time) {
            $field = $run_time;
        }

        $list     = array();
        $new_list = array();
        $i        = 0;

        $count   = _model($table)->getTotal($filter);
        if ($count) {
            $pager = new Pager($this->per_page);

            $order = ' ORDER BY `id` DESC ';
            $limit = $pager->getLimit($page);

            $list = _model($table)->getList($filter, $order.' '.$limit);

            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }
        }

        foreach ($list as $k => $v) {
            $phone_info = screen_device_helper::get_device_info_by_device($v['device_unique_id']);
            //$phone_info = _uri('screen_device', array('device_unique_id' => $v['device_unique_id']));
            if (!$phone_info) {
                continue;
            }

            //获取设备昵称
            $nickname_info = screen_device_helper::get_device_nickname($phone_info['phone_name'], $phone_info['phone_version']);
            $phone_name    = $nickname_info['name_nickname'];
            $phone_version = $nickname_info['version_nickname'];

            if ($phone_name) {
                $nike_name = $phone_name;
            } else {
                $nike_name = $phone_info['phone_name'];
            }

            if ($phone_version) {
                $version = $phone_version;
            } else {
                $version = $phone_info['phone_version'];
            }

            $new_list[$i]['province_id'] = $v['province_id'];
            $new_list[$i]['city_id'] = $v['city_id'];
            $new_list[$i]['area_id'] = $v['area_id'];
            $new_list[$i]['business_id'] = $v['business_id'];
            $new_list[$i]['device_unique_id'] = $v['device_unique_id'];
            $new_list[$i]['content_meal_id'] = $v['content_meal_id'];
            //$list[$i]['res_title'] = $v['res_title'];
            $new_list[$i]['res_title'] = _uri('screen_content_meal', $v['content_meal_id'], 'title');
            $new_list[$i]['day'] = $v['day'];
            $new_list[$i]['phone_name'] = $nike_name;
            $new_list[$i]['phone_version'] = $version;
            $new_list[$i]['phone_imei'] = $phone_info['imei'];
            $new_list[$i]['add_time'] = $v['add_time'];
            $i++;
        }

        Response::assign('new_list', $new_list);
        Response::assign('search_filter', $search_filter);
        Response::assign('res_id', $res_id);
        Response::assign('count', $count);
        Response::assign('type', $type);
        Response::assign('field', $field);

        Response::display('admin/content_meal_stat/stat_record.html');
    }

    /**
     * 处理搜索条件
     * @return string[]|unknown[]|number[]|mixed[]|array[]
     */

    public function get_search_filter()
    {
        $search_filter  = Request::Get('search_filter', array());
        $page           = tools_helper::get('page_no', 1);
        $type           = tools_helper::get('type', 1);
        $default_filter = _widget('screen')->default_search_filter($this->member_info);

        $res_id         = tools_helper::get('res_id', 0);
        $run_time       = tools_helper::get('run_time', '');
        $date           = tools_helper::get('date', '');

        //var_dump($res_id);
        $filter = $default_filter;

        if (isset($search_filter['date_type']) && $search_filter['date_type']) {
            if (1 == $search_filter['date_type']) {
                $search_filter['start_time'] = $search_filter['end_time'] = date('Y-m-d');

            } else if (2 == $search_filter['date_type']) {
                $search_filter['start_time'] = date('Y-m-d',time() - 7 * 24 * 3600);
                $search_filter['end_time']   = date('Y-m-d');

            } else if (3 == $search_filter['date_type']) {
                $search_filter['start_time'] = date('Y-m-d',time() - 30 * 24 * 3600);
                $search_filter['end_time']   = date('Y-m-d');

            }
        }

        if (isset($search_filter['start_time']) && $search_filter['start_time']) {
            $filter['day >='] = date('Ymd', strtotime($search_filter['start_time']));
        } else {
            $filter['day >='] = date('Ymd', time() - 30 * 24 * 3600);
            $search_filter['start_time'] = date('Y-m-d', time() - 30 * 24 * 3600);
        }

        if (isset($search_filter['end_time']) && $search_filter['end_time']) {
            $filter['day <='] = date('Ymd', strtotime($search_filter['end_time']));
        } else {
            $filter['day <='] = $search_filter['end_time'] = date('Ymd', time());
            $search_filter['end_time'] = date('Y-m-d', time());
        }

        if (strtotime($search_filter['start_time']) + 30 * 24 * 3600 == strtotime($search_filter['end_time'])) {
            $search_filter['date_type'] = 3;
        }

        if ($search_filter['start_time'] == $search_filter['end_time']) {
            $search_filter['date_type'] = 1;
        }

        if (isset($search_filter['start_date']) && $search_filter['start_date']) {
            $filter['day >='] = date('Ymd', strtotime($search_filter['start_date']));
            $search_filter['start_time'] = $search_filter['start_date'];
        } else {
            $filter['day >='] = date('Ymd', time() - 30 * 24 * 3600);
            $search_filter['start_time'] = date('Y-m-d', time() - 30 * 24 * 3600);
        }

        if (isset($search_filter['end_date']) && $search_filter['end_date']) {
            $filter['day <='] = date('Ymd', strtotime($search_filter['end_date']));
            $search_filter['end_time'] = $search_filter['end_date'];
        } else {
            $filter['day <='] = $search_filter['end_time'] = date('Ymd', time());
            $search_filter['end_time'] = date('Y-m-d', time());
        }
        if ($date) {
            $filter['day <='] = $date;
            $filter['day >='] = $date;
            //$search_filter['end_time'] = $search_filter['end_date'];
        }


        //权限控制
        if ('group' == $this->res_name) {

        } else if ('province' == $this->res_name) {
            $filter['province_id'] = $this->res_id;

        } else if ('city' == $this->res_name) {
            $filter['city_id'] = $this->res_id;

        } else if ('area' == $this->res_name) {
            $filter['area_id'] = $this->res_id;

        } else if ('business_hall' == $this->res_name) {
            $filter['business_id'] = $this->res_id;
        }

        //搜索判断
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

        if (!empty($search_filter['area_id'])) {
            $filter['area_id'] = $search_filter['area_id'];
        }

        if ($res_id) {
            $filter['content_meal_id'] = $res_id;
        }

        $list     = array();
        $new_list = array();

        // 弹出
        if ($type == 1) {
            $table = 'screen_content_meal_pop_stat_day';
            $field = 'pop_num';
        } else {
            // 点击
            $table = 'screen_content_meal_stat_day';
            $field = 'action_num';
        }

        if ($run_time) {
            $field = $run_time;
        }

        return array('search_filter'=>$search_filter, 'filter'=>$filter, 'type'=>$type, 'page'=>$page, 'table'=>$table, 'field'=>$field, 'res_id'=>$res_id);
    }
}