<?php
/**
 * alltosun.com  behave_stat.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-7-25 下午5:09:47 $
 * $Id$
 */
class Action
{
    private $per_page  = 25;

    public function __call($action = '', $param = array())
    {

        $type          = Request::Get('type', 0);
        $search_filter = Request::Get('search_filter', array());
        $page          = Request::get('page_no' , 1) ;

        $filter = $list = array();
        $behave_type = '';

        // 默认数据库
        $db_type     = 'mysql';

        if (isset($search_filter['date']) && $search_filter['date']) {
            $filter['record_day'] = date('Ymd', strtotime($search_filter['date']));
        }

        if (isset($search_filter['behave_type']) && $search_filter['behave_type'] != '') {
            if ( 'all' != $search_filter['behave_type'] )  {
                $search_filter['behave_type'] = $behave_type = (int) $search_filter['behave_type'];
                $filter['behave_type'] = $search_filter['behave_type'];
            }
        }

        if (isset($search_filter['business_hall_title']) && $search_filter['business_hall_title']) {
            $business_hall_info = _model('business_hall')->read(array('title' => trim($search_filter['business_hall_title'])));
 
            if (!$business_hall_info) return '请输入正确的营业厅名称';

            $filter['business_hall_id'] = $business_hall_info['id'];
        } 

        if (isset($search_filter['device_unique_id']) && $search_filter['device_unique_id']) {
            $filter['device_unique_id'] = $search_filter['device_unique_id'];
        }

        Response::assign('count' , $count);
        Response::assign('page' , $page);
        Response::assign('list', $list);
        Response::assign('type', $type);

        Response::assign('search_filter' , $search_filter);
        Response::display('admin/daily_behave_list.html');
    }

    public function get_device_info($filter, $page)
    {
        unset($filter['behave_type']);

        if ( !$filter ) $filter = [1 => 1];

        $list = _model('screen_device')->getList($filter ,  " ORDER BY `day` DESC " . $pager->getLimit($page));

        
    }

}