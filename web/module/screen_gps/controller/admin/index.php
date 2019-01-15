<?php

/**
 * alltosun.com  亮屏gps统计
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * szy: 宋志宇 (songzy@alltosun.com)
 * 2018年3月23日: 2016-7-26 下午3:05:10
 * Id
 */

class Action
{
    private $per_page = 20;
    private $member_id  = 0;
    private $member_res_name = '';
    private $member_res_id   = 0;
    private $member_info;
    private $ranks           = 0;
    private $time;

    public function __construct()
    {
        $this->member_id   = member_helper::get_member_id();
        $this->time        = date('Y-m-d H:i:s');
        $this->member_info = member_helper::get_member_info($this->member_id);

        if ($this->member_info) {
            $this->member_res_name = $this->member_info['res_name'];
            $this->member_res_id   = $this->member_info['res_id'];
            $this->ranks           = $this->member_info['ranks'];
        }
            Response::assign('member_info', $this->member_info);
    }

    public function __call($action = '', $params = array())
    {
        // 内容展示必须符合各省的条件
        $search_filter = Request::Get('search_filter', array());
        $page             = tools_helper::get('page_no', 1);
        $search_device_unique_id = tools_helper::get('device_unique_id', '');
        $is_export = tools_helper::get('is_export', 0);
        $filter = array();
        //省市区搜索
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
        
        if (!empty($search_filter['business_hall_title'])) {
            $search_filter['business_hall_title'] = trim($search_filter['business_hall_title']);
            $business_hall_id = _uri('business_hall', array('title' => $search_filter['business_hall_title']), 'id');
            if ($business_hall_id) {
                $filter['business_hall_id'] = $business_hall_id;
            }
        }
        
        if (!empty($filter['business_hall_id']) && $filter['business_id'] = $filter['business_hall_id']) {
            unset($filter['business_hall_id']);
        }
        //时间搜索
        if (!empty($search_filter['start_date'])) {
            $filter['date >=']  = str_replace('-', '', $search_filter['start_date']);
        }
        
        if (!empty($search_filter['end_date'])) {
            $filter['date <=']  = str_replace('-', '', $search_filter['end_date']);
        }
        
        if ($search_device_unique_id) {
            $filter['device_unique_id'] = $search_device_unique_id;
        }
        if (!$filter) {
            $filter = array(1=>1);
        }        
        $gps_list = array();
        
        $count = _model('gps_record')->getTotal($filter);
        
        if ($count) {
            $pager = new Pager($this->per_page);
            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }
            Response::assign('count', $count);
            $gps_list =  _model('gps_record')->getList($filter, ' ORDER BY `id` DESC '.$pager->getLimit());
        }
//         $gps_list = get_data_list('gps_record', $filter, ' ORDER BY `id` DESC ', $page, $this->per_page);
        Response::assign('gps_list',$gps_list);
        Response::assign('count',$count);
        Response::assign('search_filter', $search_filter);
        Response::assign('device_unique_id', $search_device_unique_id);
        Response::display("admin/gps_list.html");
    }

}
?>