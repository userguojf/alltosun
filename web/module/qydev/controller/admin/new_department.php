<?php
/**
 * alltosun.com  new_depart.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-4-25 下午5:00:16 $
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
        
        if (isset($search_filter['department_id']) && $search_filter['department_id']) {
            $filter['department_id'] = trim($search_filter['department_id']);
        }

        if (isset($search_filter['name']) && $search_filter['name']) {
            $filter['name'] = trim($search_filter['name']);
        }

        if (!$filter) {
            $filter = array( 1 => 1 );
        }
        /**
         *注：只拿二级部门
         *即中国电信营业厅 department_id = 1
         */
        $filter['parent_id <='] = 1;

        $count = _model('public_contact_department')->getTotal($filter);

        if ($count) {
            $pager  = new Pager($this->per_page);
            $list   = _model('public_contact_department')->getList($filter , $pager->getLimit($page));

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
        }

        Response::assign('list' , $list);
        Response::assign('page' , $page);
        Response::assign('search_filter' , $search_filter );
        Response::display('admin/new_department_list.html');
    }
}