<?php

/**
 * alltosun.com 主页面 index.php
 * ============================================================================
 * 版权所有 (C) 2009-2018 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 赵高举 (zhaogj@alltosun.com) $
 * $Date: 2018/4/18 10:16 $
 * $Id$
 */
class Action
{
    private $per_page = 20;
    private $member_id = 0;
    private $member_res_name = '';
    private $member_res_id = 0;
    private $member_info = array();
    private $ranks = 0;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->member_id = member_helper::get_member_id();

        $this->member_info = member_helper::get_member_info($this->member_id);

        if ($this->member_info) {
            $this->member_res_name = $this->member_info['res_name'];
            $this->member_res_id = $this->member_info['res_id'];
            $this->ranks = $this->member_info['ranks'];
        }

        Response::assign('curr_member_ranks', $this->ranks);
    }


    /**
     * 所有版本列表
     * @param string $action
     * @param array $params
     */
    public function __call($action = '', $params = array())
    {
        $page = tools_helper::get('page_no', 1);
        // 所有版本列表数量
        $version_count = _model('screen_device')->getCol('select count(distinct version_no) as version_count from screen_device');
        // 分页
        $version_list = array();
        $count = $version_count[0];
        if ($count) {
            $pager = new Pager($this->per_page);
            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }

            Response::assign('count', $count);
            $limit_start = ($page - 1) * $this->per_page;
            // 所有版本列表
            $version_list = _model('screen_device')->getCol(" select distinct version_no from screen_device  ORDER BY `version_no` asc  LIMIT {$limit_start}, {$this->per_page}");
        }
        Response::assign('version_list', $version_list);
        Response::assign('count', $count);
        Response::display('admin/version_list.html');

    }

    /**`
     * 版本号下面的省份列表province_list
     */
    public function province_list()
    {
        $search_filter = Request::Get('search_filter', array());
        // 版本下面有设备的省份列表
        $pro_version_list = _model('screen_device')->getCol(" select distinct province_id from screen_device where version_no='" . $search_filter['version_no'] . "' ORDER BY `province_id` ");
        // 版本下面有设备的省份数量
        $pro_version_list_count = _model('screen_device')->getCol(" select count(distinct province_id) from screen_device where version_no='" . $search_filter['version_no'] . "' ORDER BY `province_id` ");

        Response::assign('search_filter', $search_filter);
        Response::assign('pro_version_list', $pro_version_list);
        Response::assign('pro_version_list_count', $pro_version_list_count[0]);
        Response::display('admin/province_list.html');
    }

    /**
     * 某个省份下面的营业厅列表
     */
    public function business_list()
    {
        $page = tools_helper::get('page_no', 1);

        $search_filter = Request::Get('search_filter', array());

        // 营业厅名称搜索
        $business_title = trim(tools_helper::get('business_title', ''));
        if (!empty($business_title) && isset($business_title)) {
            $business_hall_id = _uri('business_hall', array('title' => $business_title), 'id');
            if ($business_hall_id) {
                $search_filter['business_id'] = $business_hall_id;
            }
        }

        $filter = $this->to_where($search_filter);
        $business_count = _model('screen_device')->getCol(" select count(distinct business_id) from screen_device {$filter} ORDER BY `province_id` ");

        $count = $business_count[0];
        $business_list = array();
        // 分页
        if ($count) {
            $pager = new Pager($this->per_page);
            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }

            Response::assign('count', $count);
            $limit_start = ($page - 1) * $this->per_page;
            // 有设备的营业厅列表
            $business_list = _model('screen_device')->getAll(" select distinct business_id,city_id,province_id,area_id from screen_device {$filter} ORDER BY `province_id` LIMIT {$limit_start}, {$this->per_page} ");
        }


        Response::assign('search_filter', $search_filter);
        Response::assign('business_title', $business_title);
        Response::assign('business_list', $business_list);
        Response::assign('count', $count);
        Response::display('admin/business_list.html');
    }

    /**
     * 营业厅下面的设备列表
     */
    public function device_list()
    {
        // 搜索条件
        $search_filter = Request::Get('search_filter', array());
        $page = tools_helper::get('page_no', 1);
        $search_device_unique_id = trim(tools_helper::get('device_unique_id', ''));

        $order = " ORDER BY `id`  DESC ";
        $filter = $search_filter;

        if ($search_device_unique_id) {
            $filter['device_unique_id'] = $search_device_unique_id;
        }
        if (isset($search_filter['online_status']) && $search_filter['online_status'] == 1) {
            $filter['status'] = 1;
        } elseif (isset($search_filter['online_status']) && $search_filter['online_status'] == 2) {
            $filter['status'] = 0;
        }
        unset($filter['online_status']);

        $count = _model('screen_device')->getTotal($filter);
        $device_list = array();
        if ($count) {
            $pager = new Pager($this->per_page);
            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }

            Response::assign('count', $count);
            // 某个营业厅下面的所有设备列表
            $device_list = _model('screen_device')->getList($filter, $order . $pager->getLimit($page));
        }

        Response::assign('device_unique_id', $search_device_unique_id);
        Response::assign('search_filter', $search_filter);
        Response::assign('device_list', $device_list);
        Response::assign('count', $count);
        Response::display('admin/device_list.html');
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

                if (!$where) {
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

                if (strpos($k, '<') || strpos($k, '>')) {
                    $where .= " {$k}{$v} AND";
                } else {

                    //an_dump($k, $v);
                    if (is_array($v)) {
                        foreach ($v as $sk => $sv) {
                            $where .= " {$k}='{$sv}' AND";
                        }
                        continue;
                    } else {
                        $where .= " {$k}='{$v}' AND";
                    }

                }

            }

            $where = rtrim($where, 'AND');

        } else {

            if (!$where) {
                $where = " WHERE ";
            }

            $where .= "id={$filter} ";
        }

        return $where;
    }

}
