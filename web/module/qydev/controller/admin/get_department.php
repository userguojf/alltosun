<?php
/**
 * alltosun.com  get_department.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-4-17 下午3:52:57 $
 * $Id$
 */
class Action
{
    public function index()
    {

        //获取部门
        $department_info = _widget('qydev.department')->get_department_list($params = array());

        if (!$department_info) {
            return '接口出现错误，请查看';
        }

        if (empty($department_info)) {
            return '获取部门为空';
        }

        $filter = array();

        foreach ($department_info as $k => $v) {
            $filter['department_id'] = $v['id'];
            $filter['name']          = $v['name'];
            $filter['parent_id']     = $v['parentid'];
            $filter['qy_order']      = $v['order'];

            //创建数据
            _model('public_contact_department')->create($filter);
        }
        echo '获取完成';
//         //账户都重定向
//         Response::redirect('qydev/admin/department');
    }

}