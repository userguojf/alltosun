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
 * $Date: 2017-4-17 上午9:45:23 $
 * $Id$
 */
class Action
{
    /**
     * 目前只是删除
     * @return string
     */
    public function update_res_status()
    {
        $qy_id  = Request::Post('id', 0);
        $status = Request::Post('status', 0);

        if (!$qy_id) {
            return array('info' => '信息错误');
        }

        $info = _uri('public_contact_user',$qy_id);

        if (!$info) {
            return array('info' => '通讯录信息不存在');
        }

        if ($status  == 2) {
            _model('public_contact_user')->delete(array('id' => $qy_id));
        }

        return array('info' => 'ok');
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
    
    /**
     * 改变赞
     * @return string[]
     */
    public function change_zan()
    {
        $type_id = Request::Post('type_id',0);
        $res_name = Request::Post('res_name','');
        $user_id = Request::Post('user_id',0);
        $type = Request::Post('type',0);//1文章 2 评论
        $user_name = Request::Post('user_name','');
        $time = date('Y-m-d H:i:s',time());
        $like_info = _model('like')->read(array('type' => $type,'user_id'=>$user_id,'user_name'=>$user_name,'res_name'=>$res_name,'type_id'=>$type_id));
        //如果存在取消
        if($like_info){
            if($like_info['status']==0){
                $status = 1;
            }else{
                $status = 0;
            }
            _model('like')->update(array('id'=>$like_info['id']),array('status'=>$status));
             
            if($status){
                return array('info'=>'ok','msg'=>'成功');
            }else{
                return array('info'=>'jian','msg'=>'成功');
            }
    
        }else{
            $id = _model('like')->create(array('type' => $type,'user_id'=>$user_id,'user_name'=>$user_name,'res_name'=>$res_name,'type_id'=>$type_id,'status'=>1,'add_time'=>$time));
            return array('info'=>'ok','msg'=>'成功');
        }
    
    }
}