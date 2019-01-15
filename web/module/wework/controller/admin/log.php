<?php
/**
 * alltosun.com  log.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-9-5 下午2:38:16 $
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

        if (isset($search_filter['app_id']) && $search_filter['app_id']) {
            $filter['app_id'] = trim($search_filter['app_id']);
        }

        if (isset($search_filter['type']) && $search_filter['type']) {
            $filter['type'] = trim($search_filter['type']);
        }

        if (isset($search_filter['param']) && $search_filter['param']) {
            $filter['param LIKE'] = '%'.trim($search_filter['param']).'%';
        }

        if (isset($search_filter['response']) && $search_filter['response']) {
            $filter['response LIKE']    = '%'.trim($search_filter['response']).'%';
        }

        if (!$filter ) {
            $filter = array( 1 => 1 );
        }

        $count = _model('qydev_api_dm_operation_log')->getTotal($filter);
        if ($count) {
            $pager  = new Pager($this->per_page);
            $list   = _model('qydev_api_dm_operation_log')->getList($filter ,  " ORDER BY `id` DESC " . $pager->getLimit($page));

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
        }

        Response::assign('list' , $list);
        Response::assign('count' , $count);

        Response::assign('page' , $page);
        Response::assign('search_filter' , $search_filter );

        Response::display('admin/log_list.html');
    }
}