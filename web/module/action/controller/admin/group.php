<?php
/**
 * alltosun.com  group.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 祝利柯 (zhulk@alltosun.com) $
 * $$Id: group.php 217468 2015-03-19 06:34:39Z sunxs $$
 */

class Action
{
    public function __construct()
    {
        $this->member_id   = member_helper::get_member_id();

        $member_info = member_helper::get_member_info($this->member_id);

        if ($member_info) {
            $this->member_res_name = $member_info['res_name'];
            $this->member_res_id   = $member_info['res_id'];
            $this->ranks           = $member_info['ranks'];
        }
    }

    /**
     * 角色组列表
     * @param unknown_type $action
     * @param unknown_type $params
     */
    public function __call($action = '', $params = array())
    {

        $filter['`id` >']       = 0;
        $filter['`ranks` >=']    = $this->ranks;
        $order = ' ORDER BY `ranks` ASC';

        $list = _model('group')->getList($filter,$order);

        Response::assign('list', $list);

        Response::assign('total', count($list));

        Response::display('admin/group/list.html');
    }

    /**
     * 添加.编辑角色组
     */
    public function add()
    {
        $group_action = '';

        $id = tools_helper::get('gid', 0);

        if ($id) { //编辑

            Response::assign('action', 'edit');

            $info = _uri('group', $id);

            $group_info = _model('group_action')->getList(array('group_id'=>$id));


            if ($group_info) {

                foreach ($group_info as $k=>$v) {

                    $group_action[] = $v['action_id'];

                }
            }

            if ($info['is_root'] != 1) {

                $info['group_action'] = $group_action;

            }

            Response::assign('info', $info);

        }else {

            Response::assign('action', 'add');

        }

        $action_list = action_helper::get_all_action_list();
        Response::assign('action_list', $action_list);
        Response::display('admin/group/add.html');
    }

    /**
     * 保存角色
     */
    public function save()
    {

        $id   = tools_helper::post('id', 0);  //id为分组id

        $data = tools_helper::post('info', array());
        
        $is_admin = action_helper::is_admin_group($id);
        if (!array_key_exists('sub_category', $data) && !$is_admin) {  //不是管理员修改时候才验证

            return array('没有选择权限!', 'error', 'action/admin/group');

        }

        if(!$is_admin) {

            $category_list = $data['category'];

            unset($data['category']);

            $sub_category_list = $data['sub_category'];
            unset($data['sub_category']);

        }



        if (!$id) {
            $id = _model('group')->create($data);
            if ($id) {
//                 _widget('log')->record('group', $id, '新增');
            }
            action_helper::create_category($id, $category_list, $sub_category_list);
            // 添加分类
            return array('保存成功!', 'success', 'action/admin/group');
        } else {
            $res = _model('group')->update($id, $data);

            if ($res) {
//                 _widget('log')->record('group', $res, '修改');
            }


            if(!$is_admin) {
                action_helper::create_category($id, $category_list, $sub_category_list);
            }
            // 更新分类
            return array('更新成功!', 'success', 'action/admin/group');
        }
    }

    /**
     * 删除组
     * @return string
     */
    public function delete()
    {
        $gid = Request::Get('gid', 0);

        if (!$gid) {
            return "要删除的ID不存在!";
        }

        $info = _uri('group', $gid);

        if (!$info) {
            return "角色不存在!";
        }

        if ($info['is_root'] == 1) {
            return '超级管理员不能被删除!';
        }

        $user_total = _model('group_user')->getTotal(array('group_id'=>$gid));

        if ($user_total) {
            return '请先删除当前角色下的用户!';
        }

        _model('group_action')->delete(array('group_id'=>$gid));

        _model('group')->delete(array('id'=>$gid));

//         _widget('log')->record(array('group_action','group'), array($info['id'],$gid), array('删除','删除'));

        return 'ok';
    }
}
?>