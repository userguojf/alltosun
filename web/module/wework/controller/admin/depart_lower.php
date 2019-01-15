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
 * $Date: 2018-3-12 下午7:08:42 $
 * $Id$
 */

class Action
{
    private $per_page = 20;
    private $table    = 'wework_department';
    private $pid      = 0;
    private $p_info   = array();
    public function __construct()
    {
        $this->pid = Request::get('pid' , 0) ;
        if ( $this->pid ) {
            $this->p_info = _model($this->table)->read(array('id' => $this->pid));
        }
    }

    public function __call($action = '' , $param = '')
    {
        if ( !$this->pid ) return '请传参数';

        if ( !$this->p_info ) return '数据已不存在';

        $page          = Request::get('page_no' , 1) ;
        $search_filter = Request::get('search_filter' , array());

        $list = $filter = array();

        if (isset($search_filter['name']) && $search_filter['name']) {
            $filter['name'] = trim($search_filter['name']);
        }

        $filter['type'] = 2;
        $filter['pid']  = $this->pid;

        $count = _model($this->table)->getTotal($filter);

        if ($count) {
            $pager  = new Pager($this->per_page);
            $list   = _model($this->table)->getList($filter , $pager->getLimit($page));

            foreach ( $list as $k => &$v ) {
                $name =  _uri($this->table, array('work_depart_id' => $v['work_pid']), 'name') ;
                $v['pname']= $name ? $name : '--';
            }

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
        }

        Response::assign('list' , $list);
        Response::assign('count' , $count);
        
        Response::assign('page' , $page);
        Response::assign('info' , $this->p_info);
        Response::assign('search_filter' , $search_filter );

        Response::display('admin/depart_lower_list.html');
    }

    //添加数据
    public function add()
    {
        if ( !$this->pid || !$this->p_info) return '请传参数';

        $id  = Request::get('id' , 0);

        if ( $id ) {
            $department_info = _uri($this->table , array('id'=>$id));

            Response::assign('department_info' , $department_info);
        }

        Response::assign('info' , $this->p_info);
        Response::display('admin/add_department_3.html');

    }

    //保存
    public function save()
    {
        $department_info = Request::post('department_info' , array());

        //判断
        if (!isset($department_info['name']) || !$department_info['name'] ) {
            return '部门名称为空';
        }

        if (!isset($department_info['pid']) || !$department_info['pid'] ) {
            return '非法操作';
        }

        $this->p_info = _model($this->table)->read(array('id' => $department_info['pid']));

        if ( !$this->p_info ) return '数据已经不存在';

        if ( $department_info['id'] ) {
            $info = _model($this->table)->read(array('id' => $department_info['id']));

            if ( !$info ) return '数据已经存在';
            // 调接口
            $result = wework_department_helper::update_api($info['work_depart_id'], $department_info['name']);

            if ( isset($result['errcode']) && $result['errcode'] ) {
                return $result['errmsg'];
            }

            _model($this->table)->update($department_info['id'] , $department_info);

        } else {
            //账号唯一的判断
//             $is_have_info = _model($this->table)->read(array('name' => $department_info['name']));

//             if ($is_have_info) return '部门名称已存在!';

            $result = wework_department_helper::create_api($department_info['name'], $this->p_info['work_depart_id']);

            if ( isset($result['errcode']) && $result['errcode'] ) {
                return $result['errmsg'];
            }

            // 中国电信股份有限公司增值业务运营中心 下一级部门
            $department_info['work_pid']       = $this->p_info['work_depart_id'];
            $department_info['work_depart_id'] = $result['id'];
            $department_info['pid']  = $this->p_info['id'];
            $department_info['type'] = 2;

            _model($this->table)->create($department_info);
        }

        return array('操作成功', 'success', AnUrl("wework/admin/depart_lower?pid={$department_info['pid']}"));
    }


}