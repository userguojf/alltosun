<?php
/**
* alltosun.com action.php
================================================
* 版权所有 (C) 2009-2014 北京互动阳光科技有限公司，并保留所有权利。
* 网站地址: http://www.alltosun.com
* ----------------------------------------------------------------------------
* 许可声明: 这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
* ============================================================================
* @author: 祝利柯 (zhulk@alltosun.com)
* @date:2015-2-26
* $$Id: action.php 211225 2015-02-28 09:29:32Z zhulk $$
*/

class Action
{
    private $per_page = 20; 

    /**
     * 所有控制权限
     * @param unknown_type $action
     * @param unknown_type $params
     */
    public function __call($action = '', $params = array())
    {

        $pid           = tools_helper::get('pid', 0);
        $page          = Request::get('page_no' , 1) ;
        $search_filter = tools_helper::get('search_filter', array());

        $filter = $list = array();
        $filter['pid'] = $pid;

        // ID
        if (isset($search_filter['id']) && $search_filter['id']) {
            $filter['id'] = $search_filter['id'];
        }

        // 控制器名称
        if (isset($search_filter['name']) && $search_filter['name']) {
            $filter['name'] = $search_filter['name'];
        }

        // 控制器方法名
        if (isset($search_filter['action_name']) && $search_filter['action_name']) {
            $filter['action_name'] = $search_filter['action_name'];
        }

        $order = " ORDER BY `view_order` ASC ";

        $count = _model('action')->getTotal($filter);

        if ($count) {
            $pager = new Pager($this->per_page);
            $list = _model('action')->getList($filter, $order.$pager->getLimit($page));

            if ($pager->generate($count, $page)) {
                Response::assign('pager', $pager);
            }
        }

        Response::assign('pid', $pid);
        Response::assign('list', $list);
        Response::assign('total', count($list));
        Response::assign('search_filter', $search_filter);
        Response::display('admin/action/list.html');
    }

    /**
     * 添加权限
     */
    public function add()
    {
        // 父级别
        $pid  = tools_helper::get('pid', 0);
        $id   = tools_helper::get('id', 0);

        if ($pid) {
            $p_info = _uri('action', $pid);
            Response::assign('p_info', $p_info);
        }

        // 编辑
        if ($id) {
            $info = _uri('action', $id);
            Response::assign('pid', $info['pid']);
            Response::assign('info', $info);
        }

        Response::display('admin/action/add.html');
    }

    /**
     * 保存权限控制
     */
    public function save()
    {
        $id   = tools_helper::post('id', 0);
        $info = tools_helper::post('info', array());

        if (!$info) {
            return '数据不能为空！';
        }

        if (!isset($info['is_auth'])) {
            $info['is_auth'] = 1;
        }

        if (!$id) {
            _model('action')->create($info);
        } else {
            _model('action')->update($id, $info);
        }

        return array( '保存成功', 'success','action/admin/action');
    }

    /**
     * 彻底删除
     */
    public function delete()
    {
        $id = Request::getParam('id');

        if (!$id) {
            return "要删除的ID不存在!";
        }

        if (!_uri('action', $id, 'id')) {
            return "权限选项不存在!";
        }

        $p_list = _model('action')->getTotal(array('pid'=>$id));
        if ($p_list) {
            return '请先删除子权限!';
        }

        $result = _model('action')->delete(array('id'=>$id));
        if ($result) {
            _widget('log')->record('action', $id, '删除');
        }
        return 'ok';
    }
    /**
     * ajax修改数据
     */
    public function ajax_update()
    {

        $id = tools_helper::post('id',0);
        $value = tools_helper::post('value',-1);

        if(!$id) {
            return array('info'=>'failed','msg'=>'未选择数据');
        }

        if($value=='-1') {
            return array('info'=>'failed','msg'=>'请填写修改的值');
        }

        $result = _model('action')->update($id,array('view_order'=>$value));
        if($result) {
            return array('info'=>'ok','msg'=>'修改成功');
        }

        return array('info'=>'failed','msg'=>'未修改数据');
    }

}
?>