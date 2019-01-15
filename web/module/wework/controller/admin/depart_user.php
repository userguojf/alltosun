<?php
/**
 * alltosun.com  depart_user.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-3-14 上午11:52:57 $
 * $Id$
 */
class Action
{
    private $per_page  = 20;

    public function __call($action = '' , $param = '')
    {
        $page = Request::get('page_no' , 1) ;
        $id   = Request::get('id' , 0);
        $search_filter = Request::get('search_filter' , array());

        if ( !$id ) return '请携带合法参数';

        $info = _model('wework_department')->read(array('id' => $id));

        if ( !$info ) return '部门已经不存在';

        $list = $filter = array();

        if (isset($search_filter['account']) && $search_filter['account']) {
            $filter['account'] = trim($search_filter['account']);
        }

        if (isset($search_filter['mobile']) && $search_filter['mobile']) {
            $filter['mobile'] = trim($search_filter['mobile']);
        }

        if (isset($search_filter['name']) && $search_filter['name']) {
            $filter['name'] = trim($search_filter['name']);
        }

        if (isset($search_filter['user_id']) && $search_filter['user_id']) {
            $filter['user_id'] = trim($search_filter['user_id']);
        }

        $filter['department LIKE '] = '%'.$info['work_depart_id'];

        $count = _model('wework_user')->getTotal($filter);

        if ($count) {
            $pager  = new Pager($this->per_page);
            $list   = _model('wework_user')->getList($filter , $pager->getLimit($page));

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
        }
        
        Response::assign('list' , $list);
        Response::assign('count' , $count);

        Response::assign('info' , $info);
        Response::assign('page' , $page);
        Response::assign('id' , $id);
        Response::assign('search_filter' , $search_filter );

        Response::display('admin/depart_user_list.html');
    }
}