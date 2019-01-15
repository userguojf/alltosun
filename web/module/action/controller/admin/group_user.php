<?php
use JMessage\Cross\Member;
/**
* alltosun.com group_user.php
================================================
* 版权所有 (C) 2009-2014 北京互动阳光科技有限公司，并保留所有权利。
* 网站地址: http://www.alltosun.com
* ----------------------------------------------------------------------------
* 许可声明: 这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
* ============================================================================
* @author: 祝利柯 (zhulk@alltosun.com)
* @date:2015-2-26
* $$Id: group_user.php 222074 2015-04-08 08:13:32Z zhulk $$
*/

class Action
{
    private $page_no = 30;

    public function __construct()
    {
        $this->time        = date('Y-m-d H:i:s',time());
        $this->member_id   = member_helper::get_member_id();

        $member_info = member_helper::get_member_info($this->member_id);

        if ($member_info) {
            $this->member_res_name = $member_info['res_name'];
            $this->member_res_id   = $member_info['res_id'];
            $this->ranks           = $member_info['ranks'];
        }
    }

    public function __call($action = '', $params = array())
    {
        $group_id = Request::Get('gid', 0);
        $load    = Request::Get('load', 0);

        /** 验证分组 **/
        if ( !$group_id ) {
            return '请选择分组';
        }
        $g_info = _model('group')->read(array('id'=>$group_id));
        if ( !$g_info ) {
            return '分组不存在';
        }
        /** end **/


        /** 注：第三方管理员列表跟站内管理员列表区别对待 **/
        if ( $g_info['ranks'] > 100 ) {
            // 第三方
            $this->third_party( $g_info );
        } else {
            // 站内
            $this->local( $group_id, $load);
        }
        /** end **/

        
        Response::assign('group_info', $g_info);

        Response::display('admin/group_user/list.html');
    }
    /**
     * 只导出市级数据 临时
     * */

    public function export()
    {
        //$order              = ' ORDER BY `id` DESC ';
        $group_id           = 24;
        //$search_filter      = tools_helper::get('search_filter', array());
        //$page               = Request::get('page_no', 1);
        $filter             = array();
        $res_name           = action_config::$admin_res_name[$group_id];//根据分组id获取res_name 方便查询

        if (!$group_id) {
            return '参数错误!';
        }
        $filter['res_name'] = $res_name;

        $province_list = _model('province')->getList(array('id >=' => 1));
        if($province_list) { //查询用
            $options = array_to_option($province_list,'name');
        }

        $business_hall_filter = array();
        //if (isset($search_filter['title']) && $search_filter['title']) {
          //  $business_hall_filter['`title` LIKE'] = '%'.$search_filter['title'].'%';
        //}

        // 2016-07-25 leijx 省市营业厅搜索
//         if (isset($search_filter['province_id']) && $search_filter['province_id']) {
//             $business_hall_filter['`province_id`'] = $search_filter['province_id'];
//             // city_list
//             $city_list = _model('city')->getList(array('province_id'=>$search_filter['province_id']));
//             if($city_list) {
//                 $city_options = array_to_option($city_list, 'name');

//                 if (isset($search_filter['city_id']) && $search_filter['city_id']) {

//                     if (isset($search_filter['area_id']) && $search_filter['area_id']) {
//                         $business_hall_filter['`area_id`'] = $search_filter['area_id'];
//                     }
//                 }
//             }
//         }

//         if (isset($search_filter['member_user']) && $search_filter['member_user']) {
//             $filter['`member_user` LIKE'] = '%'.$search_filter['member_user'].'%';
//         }

//         //省份
//         if(isset($search_filter['res_id']) && $search_filter['res_id']) {
//             $filter['`res_id`'] = $search_filter['res_id'];
//         }

//         //城市
//         if(isset($search_filter['cit']) && $search_filter['cit']) {
//             $filter['`res_id`']   = $search_filter['cit'];
//             $data_list = city_helper::get_area_list_by_id($res_name,$search_filter['cit']);
//             $city_list = array_to_option($data_list['city_list'],'name');

//         }

//         //地区
//         if(isset($search_filter['dit']) && $search_filter['dit']) {
//             $filter['`res_id`']   = $search_filter['dit'];
//             $data_list = city_helper::get_area_list_by_id($res_name,$search_filter['dit']);
//             $city_list = array_to_option($data_list['city_list'],'name');
//             $area_list = array_to_option($data_list['area_list'],'name');
//         }


//         // 时间
//         if (isset($search_filter['start_time']) && $search_filter['start_time']) {
//             $filter['add_time >='] = $search_filter['start_time'] . ' 00:00:00';
//         }
//         if (isset($search_filter['end_time']) && $search_filter['end_time']) {
//             $filter['add_time <='] = $search_filter['end_time'] . ' 23:59:59';
//         }
        //分级别进行显示，山东省进来只能显示自己的不能显示其他的
        if($this->member_res_name !='group'){
            $filter['res_id'] = $this->member_res_id;
        }

        $user_list = $business_hall_ids = array();

        if ($business_hall_filter) {
            $count = _model('business_hall')->getTotal($business_hall_filter);
            $business_hall_ids = _model('business_hall')->getFields('id', $business_hall_filter);
            $filter['res_id'] = $business_hall_ids;
        }
//         else {
//             $count = _model('member')->getTotal($filter);
//         }

//         if ($count) {
//             $pager = new Pager($this->page_no);

//             Response::assign('count', $count);
            $order = ' ORDER BY `id` DESC ';
            $user_list = _model('member')->getList($filter , $order);
//         }

        if ($user_list) {
            foreach($user_list as $key=>$value) {
                $user_list[$key]['auther']      = city_helper::get_area_path($value['res_name'],$value['res_id']);
            }

        }

        foreach ($user_list as $k => $v) {
            $list[$k]['member_user']       = $v['member_user'];
            $name = explode("->",$v['auther']);
            $list[$k]['province'] = $name[0];
            $list[$k]['city']     = $name[1];
        }

        $params['data'] = $list;
        $params['head'] = array('账号名称','省' , '市');
        Csv::getCvsObj($params)->export();
    }
    /**
     * 添加成员
     */
    public function add()
    {
        //分组
        $gid        = tools_helper::get('gid', 0);
        //资源  group 、province、city、area、business_hall
        if ( isset(action_config::$admin_res_name[$gid]) ) {
            $res_name = action_config::$admin_res_name[$gid];
        } else {
            $res_name = '';
        }

        //用户id
        $uid        = tools_helper::get('uid',0);
        //编辑
        if($uid) {
            $info = _uri('member',$uid);
            if(empty($info)) {
                return array('没有找到该用户,您不能在编辑','error',AnUrl('action/admin/action'));
            }

            city_helper::get_area_list_by_id($info['res_name'],$info['res_id']);
            Response::assign('info',$info);

        } else {
            if (!$gid) {
                return '参数错误!';
            }
        }
        if ($this->member_res_name == 'group') {
            $province_list = array_to_option(city_helper::get_province_list(),'name');
            Response::assign('province_list', $province_list);
        } elseif ($this->member_res_name == 'province') {
            $city_list = array_to_option(city_helper::get_city_list_by_province_id($this->member_res_id),'name');
            Response::assign('province_id', $this->member_res_id);
            Response::assign('city_list', $city_list);
        } elseif ($this->member_res_name == 'city') {
            $area_list = array_to_option(city_helper::get_area_list_by_city_id($this->member_res_id), 'name');
            $city_info = _uri('city', $this->member_res_id);
            Response::assign('city_info', $city_info);
            Response::assign('province_id', $city_info['province_id']);
            Response::assign('city_id', $this->member_res_id);
            Response::assign('area_list', $area_list);
        } elseif ($this->member_res_name == 'area') {
            $business_hall_list = array_to_option(city_helper::get_business_hall_list_by_area_id($this->member_res_id));
            $area_info = _uri('area', $this->member_res_id);
            Response::assign('area_info', $area_info);
            Response::assign('province_id', $area_info['province_id']);
            Response::assign('city_id', $area_info['city_id']);
            Response::assign('area_id', $this->member_res_id);
            Response::assign('business_hall_list', $business_hall_list);
        } else {
            $business_hall_info = _uri('business_hall', $this->member_res_id);
            Response::assign('province_id', $business_hall_info['province_id']);
            Response::assign('city_id', $business_hall_info['city_id']);
            Response::assign('area_id', $business_hall_info['area_id']);
            Response::assign('business_hall_info', $business_hall_info);
        }
         Response::assign('member_res_name',$this->member_res_name);//当前登录人的等级
         Response::assign('gid', $gid);
         Response::assign('res_name', $res_name);
         Response::display('admin/group_user/add.html');
    }
    /**
     * 只导出河北的数据   临时
     */
    public function Hebei_province_export()
    {
        //获取城市id
        $city_id = Request::get('city_id' , 0);
        //判断
        if (!$city_id) exit('请输入城市id,例如填写最后的城市id<br>'.AnUrl('action/admin/group_user/Hebei_province_export').'?city_id=');
        //要组装的数组
        $list = array();
        //查表可得河北省id
        $province_id = 9;
        //拿出全部河北省营业厅的全部id
        $all_business_info = _model('business_hall')->getList(array('city_id' => $city_id));
        //拿出全部的渠道吗
        foreach ($all_business_info as $k => $v) {
            $member_user = _uri('member' , array('res_id' => $v['id']),'member_user');
            $list[$k]['member_user'] = $member_user;
            $list[$k]['province']    = '河北';
            $list[$k]['city']        = focus_helper::get_field_info($v['city_id'],'city', 'name');
            $list[$k]['area']        = focus_helper::get_field_info($v['area_id'],'area', 'name');
            $list[$k]['business']    = $v['title'];
        }
        //导出
        foreach ($list as $k => $v) {
            $info[$k]['member_user'] = $v['member_user'];
            $info[$k]['province']    = $v['province'];
            $info[$k]['city']        = $v['city'];
            $info[$k]['area']        = $v['area'];
            $info[$k]['business']    = $v['business'];
            
        }
        
        $params['data'] = $info;
        $params['head'] = array('渠道吗','省' , '市' , '地区' , '营业厅');
        Csv::getCvsObj($params)->export();
        
        
    }

    /**
     * 保存角色成员
     */
    public function save()
    {
        $info       = Request::getParam('info',array());
        $uid        = tools_helper::post('uid',0);
        $group_id   = tools_helper::post('gid',0);
        
        /** 验证分组 **/
        if ( !$group_id ) {
            return '请选择分组';
        }
        $g_info = _model('group')->read(array('id'=>$group_id));
        if ( !$g_info ) {
            return '分组不存在';
        }
        /** end **/

        $data           = array();

        if(empty($info['member_user']) || !isset($info['member_user'])) {
            return '请填写添加的用户账号';
        }

        if ( $g_info['ranks'] > 100 ) {
            $res_name       = '';
            $data['res_id'] = 0;
        } else {
            $res_name = action_config::$admin_res_name[$group_id];
            $res_info = action_config::$admin_res_id[$group_id];

            if($res_info != 'supper' && $this->member_res_name=='group') {
                $data['res_id']  = $info[$res_info];
            }else{
                $data['res_id']  = $this->member_res_id;
            }
        }
        // 修改用户名、省份之类的
        if($uid) { 
            if(isset($info['member_user']) && !empty($info['member_user'])) {
                $data['member_user'] = $info['member_user'];
            }
            if(isset($info['member_pass']) && !empty($info['member_pass'])) {
                $data['member_pass'] = md5($info['member_pass']);
            }

            _model('member')->update($uid,$data);
            return array('修改成功','success',AnUrl('action/admin/group_user','?gid='.$group_id));
        } else {
            if(empty($info['member_pass']) || !isset($info['member_pass'])) {
                return '请填写用户密码';
            }

            $user_info =_uri('member',array('member_user' => $info['member_user']));

            if($user_info) {
                return array('保存失败，已经存在相同用户名','error',AnUrl('action/admin/group'));
            }

            $data['member_user'] = $info['member_user'];
            $data['member_pass'] = md5($info['member_pass']);
            $data['hash']        = uniqid();
            $data['res_name']    = $res_name;
            $data['ranks']       = _uri('group',$group_id,'ranks');
            $result = _model('member')->create($data);
            if($result) {
                $filter = array(
                    'member_id'     => $result,
                    'group_id'      => $group_id,
                );
                $info = _model('group_user')->create($filter);
                if ($info) {
                    return array('保存成功','success',AnUrl('action/admin/group_user','?gid='.$group_id));
                }
            }
        }
        return array('保存失败','error',AnUrl('action/admin/group_user','/?gid='.$group_id));
    }

    /**
     * 删除组成员
     * @return array
     */
    public function delete()
    {

        $id = Request::getParam('uid');

        if (!$id) {
            return array('info'=>"要删除的ID不存在!");
        }

        $member_info =_uri('group_user', array('member_id' => $id));

        if (empty($member_info)) {
            return array('info'=>"角色不存在!");
        }

        /**
         * 判断是否是超极管理员
         */
        if($member_info['member_id']== 1 ) {

            return array('info'=>"管理员不可以删除!");

        }
        //角色组删除
        $res = _model('group_user')->delete($member_info['id']);

        if ($res) {

            //真正的角色执行删除

            _model('member')->delete($id);

//             _widget('log')->record(array('group_user','member'), array($member_info['id'],$id), array('删除','删除'));
        }

        return array('info'=>'ok');
    }

    private function third_party( $group_info )
    {
        if ( !$group_info ) {
            return array();
        }

        $page  = Request::Get('page_no', 1);

        /** 获取某个组下的用户列表 **/
        $ids = _model('group_user')->getFields('member_id', array('group_id' => $group_info['id']));
        if ( $ids ) {
            $filter = array(
                'id'    =>  $ids
            );
            $count = _model('member')->getTotal($filter);
        } else {
            $count = 0;
        }
        /** end **/

        $user_list = array();

        if ( $count ) {
            $pager = new Pager($this->page_no);
            if ($pager->generate($count ,$page)) {
                Response::assign('pager', $pager);
            }
            Response::assign('count', $count);

            $user_list = _model('member')->getList($filter, ' ORDER BY `id` DESC '.$pager->getLimit($page));

            foreach( $user_list as $key => $value ) {
                $user_list[$key]['auther']      = city_helper::get_area_path($value['res_name'], $value['res_id']);
            }
        }

        Response::assign('user_list', $user_list);

        Response::assign('gid', $group_info['id']);
        Response::assign('page' , $page);
    }

    /**
     * 站内用户列表
     *
     * @return string
     */
    private function local( $group_id, $load )
    {
        if ( !$group_id ) {
            return array();
        }

        /** 初始化变量 **/ 
        $page          = Request::Get('page_no', 1);
        $search_filter = Request::Get('search_filter', array());
        $order         = ' ORDER BY `id` DESC ';
        $filter        = array();
        /** end **/

        // 根据分组id获取res_name 方便查询
        if ( isset(action_config::$admin_res_name[$group_id]) ) {
            $res_name = action_config::$admin_res_name[$group_id];
        } else {
            $res_name           = '';
        }

        $filter['res_name'] = $res_name;

        /** 省列表 **/
        $province_list = _model('province')->getList(array('id >=' => 1));
        if( $province_list ) { //查询用
            $options = array_to_option($province_list,'name');
            Response::assign('options', $options);
        }
        /** end **/

        $business_hall_filter = array();
        if (isset($search_filter['title']) && $search_filter['title']) {
            $business_hall_filter['`title` LIKE'] = '%'.$search_filter['title'].'%';
        }

        // 2016-07-25 leijx 省市营业厅搜索
        if (isset($search_filter['province_id']) && $search_filter['province_id']) {
            // city_list
            $city_list = array();
            if ($filter['res_name'] == 'province') {
                $filter['res_id'] = $search_filter['province_id'];
            } else if ($filter['res_name'] == 'city') {
                if (!isset($search_filter['city_id']) || !$search_filter['city_id']) {
                    $city_ids = _model('city')->getFields('id', array('province_id' => $search_filter['province_id']));
                } else {
                    $city_ids = _model('city')->getFields('id', array('id' => $search_filter['city_id']));
                }
                $filter['res_id'] = $city_ids;
            } else if ($filter['res_name'] == 'area') {
                if (!isset($search_filter['city_id']) || !$search_filter['city_id']) {
                    $city_ids = _model('city')->getFields('id', array('province_id' => $search_filter['province_id']));
                    $area_ids = _model('area')->getFields('id', array('city_id' => $city_ids));
                } else {
                    $area_ids = _model('area')->getFields('id', array('city_id' => $search_filter['city_id']));
                }
                $filter['res_id'] = $area_ids;
            } else if ($filter['res_name'] == 'business_hall') {
                $business_hall_filter['`province_id`'] = $search_filter['province_id'];

            }

            $city_list = _model('city')->getList(array('province_id'=>$search_filter['province_id']));
            if($city_list) {
                $city_options = array_to_option($city_list, 'name');
                Response::assign('city_options', $city_options);

                if (isset($search_filter['city_id']) && $search_filter['city_id']) {
                    $business_hall_filter['`city_id`'] = $search_filter['city_id'];
//                     if ($business_hall_filter && $filter['res_name'] != 'business_hall') {
//                         $filter['res_id'] = $search_filter['city_id'];
//                         $filter['res_name'] = 'city';
//                     }
                    if (isset($search_filter['area_id']) && $search_filter['area_id']) {
                        $business_hall_filter['`area_id`'] = $search_filter['area_id'];
                    }
                }
            }
        }
//         p($filter);
//         exit();

        if (isset($search_filter['member_user']) && $search_filter['member_user']) {
            $filter['`member_user` LIKE'] = '%'.$search_filter['member_user'].'%';
        }

        //省份
        if(isset($search_filter['res_id']) && $search_filter['res_id']) {
            $filter['`res_id`'] = $search_filter['res_id'];
            Response::assign('pro_selectid',$search_filter['res_id']);
        }

        //城市
        if(isset($search_filter['cit']) && $search_filter['cit']) {
            $filter['`res_id`']   = $search_filter['cit'];
            $data_list = city_helper::get_area_list_by_id($res_name,$search_filter['cit']);
            $city_list = array_to_option($data_list['city_list'],'name');
            Response::assign('city_list',$city_list);
            Response::assign('city_selectid',$search_filter['cit']);

        }

        //地区
        if(isset($search_filter['dit']) && $search_filter['dit']) {
            $filter['`res_id`']   = $search_filter['dit'];
            $data_list = city_helper::get_area_list_by_id($res_name,$search_filter['dit']);
            $city_list = array_to_option($data_list['city_list'],'name');
            $area_list = array_to_option($data_list['area_list'],'name');

            Response::assign('city_list',$city_list);
            Response::assign('area_list',$area_list);
            Response::assign('area_selectid',$search_filter['dit']);
        }


        // 时间
        if (isset($search_filter['start_time']) && $search_filter['start_time']) {
            $filter['add_time >='] = $search_filter['start_time'] . ' 00:00:00';
        }
        if (isset($search_filter['end_time']) && $search_filter['end_time']) {
            $filter['add_time <='] = $search_filter['end_time'] . ' 23:59:59';
        }
        //分级别进行显示，山东省进来只能显示自己的不能显示其他的
        if($this->member_res_name !='group'){
            $filter['res_id'] = $this->member_res_id;
        }

        $user_list = $business_hall_ids = array();
        if ($business_hall_filter && $filter['res_name'] == 'business_hall') {
            $count = _model('business_hall')->getTotal($business_hall_filter);
            $business_hall_ids = _model('business_hall')->getFields('id', $business_hall_filter);
            $filter['res_id'] = $business_hall_ids;
        } else {
            $count = _model('member')->getTotal($filter);
        }

        if ($count) {
            $pager = new Pager($this->page_no);
            if ($pager->generate($count ,$page)) {
                Response::assign('pager', $pager);
            }

            Response::assign('count', $count);
            $order = ' ORDER BY `id` DESC ';
            $user_list = _model('member')->getList($filter, ' '.$order.' '.$pager->getLimit($page));
        }
        
        if ($user_list) {
            foreach($user_list as $key=>$value) {
                $user_list[$key]['auther'] = city_helper::get_area_path($value['res_name'],$value['res_id']);
            }

            Response::assign('user_list', $user_list);
        }

        if (Request::getParam('testdebug', 0)) {
            p($search_filter, $filter, $user_list);
        }

        $yyt_list = array();

        if ( $load ) {
            $member_list = _model('member')->getList($filter);

            $user_numbers = array();
            foreach ($member_list as $k => $v) {
                array_push($user_numbers, $v['member_user']);
            }

            $yyt_list = _model('business_hall')->getList(array('user_number' => $user_numbers));
        }

//         if ( $load && $yyt_list && $group_id == 24 ) {
// // p($yyt_list);exit();
//             foreach ($yyt_list as $k => $v) {
//                 $list[$k]['member_user']  = "\t".$v['user_number'];
//                 $list[$k]['province'] = screen_helper::by_id_get_field($v['province_id'], 'province', 'name');
//                 $list[$k]['city']     = screen_helper::by_id_get_field($v['city_id'],'city', 'name');
//             }

//             $params['data'] = $list;
//             $params['head'] = array('账号名称','省' , '市');
//             Csv::getCvsObj($params)->export();
//         }

        if ( $load && $yyt_list && $group_id == 26 ) {

            foreach ($yyt_list as $k => $v) {
                $list[$k]['yyt']      = $v['title'];
                $list[$k]['member_user'] = "\t".$v['user_number'];
                $list[$k]['province'] = screen_helper::by_id_get_field($v['province_id'], 'province', 'name');
                $list[$k]['city']     = screen_helper::by_id_get_field($v['city_id'],'city', 'name');
                $list[$k]['area']     = screen_helper::by_id_get_field($v['area_id'],'area', 'name');
            }

            $params['data'] = $list;
            $params['head'] = array('营业厅名称', '渠道码','省' , '市', '地区');
            Csv::getCvsObj($params)->export();
        }
        Response::assign('gid', $group_id);
        //前台做判断：$res_name='province' 显示省  $res_name='city' 显示省份、城市  $res_name='area' 显示三级
        Response::assign('res_name', $res_name);
        Response::assign('page' , $page);
        Response::assign('search_filter', $search_filter);
    }
}
?>