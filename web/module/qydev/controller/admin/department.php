<?php
/**
 * alltosun.com  department.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-4-17 下午12:17:03 $
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

        $count = _model('public_contact_department')->getTotal($filter);

        if ($count) {
            $pager  = new Pager($this->per_page);
            $list   = _model('public_contact_department')->getList($filter , $pager->getLimit($page));

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
        }

        Response::assign('count' , $count);
        Response::assign('list' , $list);
        Response::assign('page' , $page);
        Response::assign('search_filter' , $search_filter );
        Response::display('admin/department_list.html');
    }

    //添加数据
    public function add()
    {
        $id = Request::get('id' , 0);

        if ( $id ) {
            $department_info = _uri('public_contact_department' , array('id'=>$id));

            Response::assign('department_info' , $department_info);
        }

        Response::display('admin/add_department.html');

    }

    //保存
    public function save()
    {
        $department_info = Request::post('department_info' , array());

        //判断
        if (!isset($department_info['name']) || empty($department_info['name']) ) {
            return '部门名称不能为空';
        }

        if (!isset($department_info['department_id']) || empty($department_info['department_id']) ) {
            return '部门ID不能为空';
        }

        if ($department_info['id']) {
            _model('public_contact_department')->update($department_info['id'] , $department_info);

        } else {
            //账号唯一的判断
            $is_have_department_id = _model('public_contact_department')->read(array('department_id' => $department_info['department_id']));

            if ($is_have_department_id) {
                return '部门已经存在！注：请与企业号部门ID对应';
            }

            _model('public_contact_department')->create($department_info);
        }

        return array('操作成功' , 'success' ,AnUrl("qydev/admin/department"));

    }

}