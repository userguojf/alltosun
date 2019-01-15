<?php
/**
 * alltosun.com  business_hall_binding.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-4-8 上午11:37:32 $
 * $Id$
 */

class Action
{
    private $per_page  = 30;
    private $order_by  = " ORDER BY `id` DESC " ;
    private $member_id = 0;

    public function __construct()
    {
        $this->member_id   = member_helper::get_member_id();
        $member_info = member_helper::get_member_info($this->member_id);

        Response::assign('member_info', $member_info);
    }

    //审核
    public function __call($action = '' , $param = array()) 
    {
        //页码
        $page          = Request::get('page_no' , 1) ;

        //搜索条件
        $search_filter = Request::get('search_filter' , array());

        $filter = $list = $tmp_list=  array();

        if (isset($search_filter['user_number']) && !empty($search_filter['user_number'])) {
            $filter['user_number'] = $search_filter['user_number'];
        }

        if (isset($search_filter['user_name']) && !empty($search_filter['user_name'])) {
            $filter['user_name'] = $search_filter['user_name'];
        }

        if (isset($search_filter['user_phone']) && !empty($search_filter['user_phone'])) {
            $filter['user_phone'] = $search_filter['user_phone'];
        }

        if (!$filter) {
            $filter = array(1 => 1);
        }

        $count = _model('business_hall_binding_apply')->getTotal($filter);

        if ($count) {
            $pager  = new Pager($this->per_page);
            $list   = _model('business_hall_binding_apply')->getList($filter , $pager->getLimit($page));

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
        }

        Response::assign('list' , $list);
        Response::assign('page' , $page);

        Response::assign('search_filter' , $search_filter);

        Response::display('admin/business_hall_binding_list.html');
    }

    //填写绑定信息
    public function binding_address()
    {
        //页码
        $page          = Request::get('page_no' , 1) ;

        //搜索条件
        $search_filter = Request::get('search_filter' , array());

        $filter = $info_list = array();

        if (isset($search_filter['business_hall_title']) && !empty($search_filter['business_hall_title'])) {
            $filter['business_hall_title'] = $search_filter['business_hall_title'];
        }

        //省市区
        if (isset($search_filter['province']) && !empty($search_filter['province'])) {
            $filter['province_id'] = $search_filter['province'];

            $province = array('province_id' => $search_filter['province']);
            Response::assign('where1' , $province);
        }

        if (isset($search_filter['city']) && !empty($search_filter['city'])) {
            $filter['city_id'] = $search_filter['city'];

            $city = array('city_id' => $search_filter['city']);
            Response::assign('where2' , $city);
        }

        if (isset($search_filter['area']) && !empty($search_filter['area'])) {
            $filter['area_id'] = $search_filter['area'];
        }

        if (!$filter) {
            $filter = array(1 => 1);
        }

        $count = _model('business_hall_binding_address')->getTotal($filter);

        if ($count) {
            $pager  = new Pager($this->per_page);
            $info_list   = _model('business_hall_binding_address')->getList($filter , $pager->getLimit($page));

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
        }

        Response::assign('info_list' , $info_list);
        Response::assign('page' , $page);

        Response::assign('search_filter' , $search_filter);

        Response::display('admin/business_hall_binding_address.html');
    }

    //审核下载
    public function apply_export()
    {

        $filter = $search_filter = $tmp_list=  array();

        $search_filter['user_number'] = Request::get('user_number' , '');
        $search_filter['user_name']   = Request::get('user_name' , '');
        $search_filter['user_phone']  = Request::get('user_phone' , '');

        if ($search_filter['user_number']) {
            $filter['user_number'] = $search_filter['user_number'];
        }

        if ($search_filter['user_name']) {
            $filter['user_name'] = $search_filter['user_name'];
        }

        if ($search_filter['user_phone']) {
            $filter['user_phone'] = $search_filter['user_phone'];
        }

        if (!$filter) {
            $filter = array(1 => 1);
        }

        $info_list   = _model('business_hall_binding_apply')->getList($filter);

        if (!$info_list) {
            return '未找到导出的相关信息';
        }

        foreach ($info_list as $k => $v) {
            $tmp_list[$k]['user_number'] = $v['user_number'];
            $tmp_list[$k]['user_mac']    = $v['user_mac'];
            $tmp_list[$k]['user_name']   = $v['user_name'];
            $tmp_list[$k]['user_phone']  = $v['user_phone'];
            $tmp_list[$k]['add_time']    = $v['add_time'];
            $tmp_list[$k]['status']      = $v['status'] ? '是' : '否 ' ;
        }

        $params['filename'] = '营业厅绑定设备审核表';
        $params['data']     = $tmp_list;
        $params['head']     = array('营业厅渠道号', 'MAC地址' ,'联系人','手机号','添加时间' , '是否审核' );
        Csv::getCvsObj($params)->export();
    }

    //填写绑定信息的下载
    public function address_export()
    {

        $filter = $search_filter = $info_list = $tmp_list = array();

        //搜索条件
        $search_filter['business_hall_title'] = Request::get('business_hall_title' , '');
        $search_filter['province_id']         = Request::get('province_id' , 0);
        $search_filter['city_id']             = Request::get('city_id' , 0);
        $search_filter['area_id']             = Request::get('area_id' , 0);

        if (!empty($search_filter['business_hall_title'])) {
            $filter['business_hall_title'] = $search_filter['business_hall_title'];
        }

        //省市区
        if (!empty($search_filter['province_id'])) {
            $filter['province_id'] = $search_filter['province_id'];
        }

        if (!empty($search_filter['city_id'])) {
            $filter['city_id'] = $search_filter['city_id'];
        }

        if (!empty($search_filter['area_id'])) {
            $filter['area_id'] = $search_filter['area_id'];
        }

        if (!$filter) {
            $filter = array(1 => 1);
        }

        $info_list   = _model('business_hall_binding_address')->getList($filter);

        if (!$info_list) {
            return '未找到导出的相关信息';
        }

        foreach ($info_list as $k => $v) {
            $tmp_list[$k]['business_hall_title'] = $v['business_hall_title'];
            $tmp_list[$k]['province_id']         = focus_helper::get_field_info($v['province_id'], 'province', 'name');
            $tmp_list[$k]['city_id']             = focus_helper::get_field_info($v['city_id'], 'city', 'name');
            $tmp_list[$k]['area_id']             = focus_helper::get_field_info($v['area_id'], 'area', 'name');
            $tmp_list[$k]['address']             = $v['address'];
        }

        $params['filename'] = '营业厅绑定申请信息表';
        $params['data']     = $tmp_list;
        $params['head']     = array('营业厅名称','省','市','地区' , '详细地址' );
        Csv::getCvsObj($params)->export();
    }
}