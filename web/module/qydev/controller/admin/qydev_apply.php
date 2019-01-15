<?php
/**
 * alltosun.com  qydev_apply.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-6-25 上午11:48:41 $
 * $Id$
 */

class Action
{
    private $per_page  = 20;

    public function __call($action = '' , $param = '')
    {
        $page = Request::get('page_no' , 1) ;

        $search_filter = Request::get('search_filter' , array());

        $list = $filter = array();

        if (isset($search_filter['business_hall_title']) && $search_filter['business_hall_title']) {
            $filter['business_hall_title'] = trim($search_filter['business_hall_title']);
        }

        if (isset($search_filter['user_number']) && $search_filter['user_number']) {
            $filter['user_number'] = trim($search_filter['user_number']);
        }

        if (isset($search_filter['user_name']) && $search_filter['user_name']) {
            $filter['user_name'] = trim($search_filter['user_name']);
        }

        if (!$filter) {
            $filter = array( 1 => 1 );
        }

        $count = _model('qydev_apply')->getTotal($filter);
        if ($count) {
            $pager  = new Pager($this->per_page);
            $list   = _model('qydev_apply')->getList($filter , $pager->getLimit($page));

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
        }

        Response::assign('list' , $list);
        Response::assign('page' , $page);
        Response::assign('search_filter' , $search_filter );
        Response::display('admin/qydev_apply_list.html');
    }
}