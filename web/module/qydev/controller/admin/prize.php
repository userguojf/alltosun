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


        $filter = array(1 =>1);

        $count = _model('qydev_prize_record')->getTotal($filter);
        if ($count) {
            $pager  = new Pager($this->per_page);
            $list   = _model('qydev_prize_record')->getList($filter , $pager->getLimit($page));

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
        }

        Response::assign('list' , $list);
        Response::assign('count' , $count);

        Response::assign('page' , $page);

        Response::display('admin/prize_list.html');
    }
}