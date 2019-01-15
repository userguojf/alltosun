<?php
/**
 * alltosun.com  ajax.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-3-14 上午11:53:11 $
 * $Id$
 */

class Action
{
    /**
     * 大连锁删除部门
     */
    public function delete_department()
    {
        $id  = Request::Post('id', 0);

        if ( !$id ) return array('info' => '请传参数');

        $info = _uri('wework_department',$id);

        if (!$info) return array('info' => '通讯录信息不存在');

        $result = wework_department_helper::delete('work', $info['work_depart_id']);

        if ( isset($result['errcode']) && $result['errcode'] ) {
            $errmsg = _widget('wework.errmsg')->get_errmsg($result['errmsg']['errcode']);
            return array('info' => $errmsg);
        }

        $delete_res = _model('wework_department')->delete(array('id' => $id), ' LIMIT 1 ');

        if( $delete_res ) return array('info' => 'ok');

        return array('info' => 'error');
    }

    /**
     * 大连锁删除用户
     */
    public function delete_user()
    {
        $id  = Request::Post('id', 0);

        if ( !$id ) return array('info' => '请传参数');

        $info = _uri('wework_user',$id);

        if ( !$info ) return array('info' => '通讯录信息不存在');

        $result = wework_user_helper::delete('work', $info['user_id']);

        if ( isset($result['errcode']) && $result['errcode'] ) {
            $errmsg = _widget('wework.errmsg')->get_errmsg($result['errmsg']['errcode']);
            return array('info' => $errmsg);
        }

        $delete_res = _model('wework_user')->delete(array('id' => $id), ' LIMIT 1 ');

        if( $delete_res ) return array('info' => 'ok');

        return array('info' => 'error');
    }

    /**
     * 目前只是删除
     * @return string
     */
    public function delete_log()
    {
        $qy_id  = Request::Post('id', 0);
        $status = Request::Post('status', 0);

        if (!$qy_id) {
            return array('info' => '信息错误');
        }

        $info = _uri('qydev_api_dm_operation_log',$qy_id);

        if (!$info) {
            return array('info' => '信息不存在');
        }

        if ($status  == 2) {
            _model('qydev_api_dm_operation_log')->delete(array('id' => $qy_id));
        }

        return array('info' => 'ok');
    }

    /**
     * 部门的删除和更新
     * @return string
     */
    public function update_department_status()
    {
        $id              = Request::Post('id', 0);
        $status          = Request::Post('status', 0);
        $department_name = Request::Post('res_name', '');

//         return array('info' => '信息错误');
        
        if (!$id || !$department_name) {
            return array('info' => '信息错误');
        }

        $department_info = _model('public_contact_department')->read(array('id' => $id));

        if (!$department_info) {
            return array('info' => '该部门信息不存在');
        }

        if ($status  == 2) {
            if ($department_info['status']) {
                return array('info' => '该部门的通讯录信息已经匹配，确定删除，请联系管理员');
            }
            //删除
            _model('public_contact_department')->delete(array('id' => $id));
        }

        if ($status  == 1) {
            //获取通讯录成员详细信息
            $info = _widget('qydev.get_user_info')->user_info($department_name);

            if (!$info) {
                return array('info' => '请联系管理员查看获取通讯录接口日志');
            }

            //更新
            _model('public_contact_department')->update(array('id' => $id) ,array('status' => $status));
        }

        return array('info' => 'ok');
    }

    /**
     * 企业号部门的追加拼接
     * @return multitype:string
     */
    public function get_department_info()
    {
        
        $department_id = Request::post('department_id' , 0);

        $department_info = _model('public_contact_department')->read(array('department_id' => $department_id));
        
//         p($department_id);
        $result = _model('public_contact_department')->getList(array('parent_id' => $department_id));

        if (!$result) {
            return array('info' => '目前没有下属部门');
        }

//         $html = '<table class="table table-bordered">'; 
//         $html .= '<thead>';
//             $html .= '<tr>';
//                 $html .= '<th>部门'.$department_info['name'].'</th>';
//                 $html .= '<th width="50%";>操作</th>';
//             $html .= '</tr>';
//         $html .= '</thead>';
//         $html .= '<tbody>';
//         $html .= '<tr style="background-color:pink";>';
//         $html .= '<th style="background-color:pink"; width="50%">所属'.$department_info['name'].'</th>';
//         $html .= '<th style="background-color:pink"; width="50%">操作</th>';
//         $html .= '</tr>';
        $html  = '';

        foreach ($result as $k => $v) {
            $html .= '<tr class="get_department dataList'.$department_info['department_id'].'";  resId="'.$v['department_id'].'"  style="background-color:pink";>';
                $html .= '<td  style="cursor:pointer;background-color:pink;" >'.$v['name'].'</td>';
                $html .= '<td style="background-color:pink";  class="txtleft " resId='.$v['id'].'" res_name="'.$v['name'].'">';
                    $html .= '<a class="btn btn-xs btn-success" href="JavaScript:;">添加子部门</a> &nbsp;';
                    $html .= '<a class="btn btn-xs btn-success" href="JavaScript:;">修改部门</a>&nbsp;';
                    $html .= '<a class="btn btn-xs btn-success" href="JavaScript:;">上移</a>&nbsp;';
                    $html .= '<a class="btn btn-xs btn-success" href="JavaScript:;">下移</a>&nbsp;';
                    $html .= '<a class="btn btn-xs btn-danger delete_hot" href="javascript:;">删除</a>';
                $html .= '</td>';
             $html .= '</tr>';
        }

//         $html .= '</table>';

        if ($html) {
            return array('info' => 'ok' , 'html' => $html);
        }
    
    }
    
    /**
     * 审核企业号的申请人
     */
    public function update_apply_status()
    {
        $id              = Request::Post('id', 0);
        $status          = Request::Post('status', 0);

        if (!$id) {
            return array('info' => '信息错误');
        }

        $apply_info = _model('qydev_apply')->read(array('id' => $id));

        if (!$apply_info) {
            return array('info' => '未找到申请信息');
        }

        if (1 == $status) {
            $result = qydev_helper::apply_user_create($apply_info['user_number'] , $apply_info['user_name'] , $apply_info['phone'] , $apply_info['depart']);
            if (!$result) {
                return array('info' => '审核失败，请联系管理员');
            }
            //通过审核
            _model('qydev_apply')->update(array('id' => $id) ,array('status' => 1));

        } else if (2 == $status) {
            //未审核
            _model('qydev_apply')->update(array('id' => $id) ,array('status' => 0));

        } else {
            return array('info' => '非法操作');
        }

        return array('info' => 'ok');
    }
}