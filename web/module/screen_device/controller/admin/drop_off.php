<?php
/**
 * alltosun.com 下架统计 drop_off.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-5-30 下午2:46:33 $
 * $Id$
 */

class Action
{
    private $per_page = 20;
    private $table    = '';

    private $member_info = array();

    public function __call($action = '', $param = array())
    {

        $this->table = 'screen_device_offline_record';

        $type          = Request::Get('type', 0);
        $search_filter = Request::Get('search_filter', array());
        $start_time    = Request::Get('start_time', '');
        $end_time      = Request::Get('end_time', '');
        $page          = Request::get('page_no' , 1) ;

        $filter = $list = $business_ids = $device_info = array();

        if ( isset($search_filter['province_id']) && $search_filter['province_id'] ) {
            $filter['province_id'] = $search_filter['province_id'];
        }

        if ( isset($search_filter['type']) && $search_filter['type'] ) {
            $filter['type'] = $search_filter['type'];
        }

        if (isset($search_filter['business_hall_title']) && $search_filter['business_hall_title']) {
            $business_hall_info = _model('business_hall')->read(array('title' => trim($search_filter['business_hall_title'])));
            //
            if (!$business_hall_info) return '请输入正确的营业厅名称';

            $filter['business_id'] = $business_hall_info['id'];
        }

        if ( $start_time ) {
            $start_date = strtotime($start_time);
            if ( $start_date > time() ) $start_date = time();

            $filter['date >='] = date('Ymd', $start_date);
        }

        if ( $end_time ) {
            $end_time = strtotime($end_time);
            if ( $end_time < $start_date ) $end_time = time();

            $filter['date <='] = date('Ymd', $end_time);
        }

        if ( !$filter ) $filter = [ 1 => 1];

        $order = " ORDER BY `id` DESC ";
        $count = _model($this->table)->getTotal($filter);

        if ($count) {
            $pager = new Pager($this->per_page);
            $list = _model($this->table)->getList($filter, $order.$pager->getLimit($page));

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
        }

        Response::assign('count' , $count);
        Response::assign('page' , $page);
        Response::assign('list', $list);
        Response::assign('module', '下架');
        Response::assign('action', '记录');

        Response::assign('start_time' , $start_time);
        Response::assign('end_time' , $end_time);
        Response::assign('search_filter' , $search_filter);
        Response::display("admin/drop_off_list.html");
        
    }
}