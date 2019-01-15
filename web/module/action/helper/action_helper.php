<?php
/**
 * alltosun.com  action_helper.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王德康 (wangdk@alltosun.com) $
 * $Date: 2014-7-8 下午2:51:28 $
 * $Id$
 */


class action_helper
{

    /**
     * 获取所有角色的所有权限
     * @param unknown_type $group_id
     */
    public static function get_group_all_action($group_id)
    {
        _model('group_action')->getList(array('group_id'=>$group_id, 'is_root'=>1));
    }

    /**
     * 创建分类权限
     * @param $id 要创建权限的角色ID
     * @param $action_list
     * @param $sub_action_list
     */
    public static function create_category($id, $action_list, $sub_action_list)
    {
        _model('group_action')->delete(array('group_id'=>$id));

        foreach ($action_list as $key => $val) {
            $arr['group_id']  = $id;
            $arr['action_id'] = $val;
            $arr['is_root'] = 1;
            _model('group_action')->create($arr);
        }

        foreach ($sub_action_list as $key => $val) {
            $arr['group_id']  = $id;
            $arr['action_id'] = $val;
            _model('group_action')->create($arr);
        }

    }

    /**
     * 获取权限分类
     * @param $pid 父级分类
     * @param unknown_type $pid
     */
    public static function get_action_list($pid) {
        $list = _model('action')->getList(array('pid'=>$pid));
        return $list;
    }

    /**
     * 获取所有模块和权限
     * @return bool
     */
    public static function get_all_action_list()
    {
        $list = self::get_action_list(0);

        foreach ($list as $key => $val) {
            $list[$key]['sub_action_list'] = self::get_action_list($val['id']);
        }
        return $list;
    }

    /**
     * 获取用户的所有权限
     * @param unknown_type $user_id
     */
    public static function user_action_list($member_id)
    {
        if (!$member_id) {
            return '参数错误!';
        }

        // 用户组的角色
        $user_role = _model('group_user')->read(array('member_id' => $member_id));

        if (!$user_role) {
            return false;
        }

        $group_role = _model('group')->read(array('id' => $user_role['group_id']));

        // 获取用户的所有权限
        $user_action_list = _model('group_action')->getList(array('group_id' => $user_role['group_id']));

        if ($group_role) {
            // 超级管理员
            $user_action_list['is_root'] = $group_role['is_root'];
        }

        // 返回用户的信息
        return $user_action_list;
    }

    /**
     * 获取用户是否具有该模块的权限
     * @param unknown_type $user_id
     * @param unknown_type $action_name
     */
    public static function is_user_allowed($user_id, $action_name)
    {
        if ($action_name === 'liangliang') {
            return 'ok';
        }

        $user_action_list = self::user_action_list($user_id);

        if (!$user_action_list) {
            return 'no';
        }

        $action_list = array();

        foreach ($user_action_list as $k=>$v) {
            if (is_array($v)) {
                $action_list[] = $v['action_id'];
            }
        }

        // 获取模块id
        $action_info = _model('action')->read(array('url'=>$action_name));

        //@todo 为了防止后续权限忘记添加  二级模块忘记填写的均可正常访问
        if (!$action_info && preg_match("/\s*([admin|liangliang]\/\\S)/i", $action_name)) {
            return 'ok';
        } elseif (!$action_info) {
            return 'no';
        }

        if (!$action_info) {
            return 'no';
        }

        if (!!$action_info['is_auth']) {
            return 'ok';
        }

        if (in_array($action_info['id'], $action_list)) {
            // 拥有该模块的权限
            return 'ok';
        } else {
            // 不拥有该模块的权限
            return 'no';
        }
    }

    /**
     * 用户登录以后进行跳转
     * 当前函数获取用户可以跳转到哪个模块下
     * 要具体权限才行
     */
    public static function get_user_action($user_id)
    {
        $user_action_list = self::user_action_list($user_id);

        if (!$user_action_list) {
            return array();
        }

        if (isset($user_action_list['is_root']) && $user_action_list['is_root'] == 1) {
            return array('url'=>'user/admin');
        }

        foreach ($user_action_list as $v) {
            $info = _model('action')->read(array('id'=>$v['action_id'], 'is_ajax'=>0));
            if ($info['pid'] > 0) {
                return $info;
            }
        }

        return array();
    }

    /**
     * 权限控制
     * $action_url权限路径
     */
    public static function action_controller($action_url)
    {

        $order = ' ORDER BY `view_order` ASC ';

        $is_root = 0;
        $action_module = array();

        // 用户未登录失败!
        $user_id = member_helper::get_member_id();
        if (!$user_id) {
            exit('Not Found！');
        }

        // 获取用户的权限
        $user_action_list = self::user_action_list($user_id);

        if ($user_action_list['is_root'] == 1) {
            $is_root = 1;
        }

    //读取样式
        $icons=action_config::$icon_res_name;

        if ($is_root) {
            // 超级管理员权限
            $action_module = _model('action')->getList(array('pid' => 0),$order);
            $action_s      = _model('action')->getList(array('pid !=' => 0),$order);
        } else {
            if ($user_action_list) {
                $action_ids = array();
                foreach ($user_action_list as $k => $v) {
                    if (is_array($v)) {
                        $action_ids[] = $v['action_id'];
                    }
                }

                $action_module = _model('action')->getList(array('id'=>$action_ids, 'pid'=>0),$order);
                $action_s = _model('action')->getList(array('id'=>$action_ids, 'pid !='=>0),$order);
            }
        }


        // 后台退出登录的标识用
        Response::assign('admin_id', 1);
        Response::assign('super_admin', 0);
        Response::assign('country_admin', 0);

        if ($is_root == 1) {
            Response::assign('super_admin', 1);
        }

        //混合样式
        foreach ($action_module as $k=>$v) {
            foreach($icons as $kkk=>$vvv) {
                if($v['action_name']==$kkk) {
                    $action_module[$k]['icon'] = $vvv;
                }
            }
        }

        // 混合主菜单和子菜单
        foreach ($action_module as $k=>$v) {
            foreach ($action_s as $key=>$val) {
                if ($v['id'] == $val['pid'] ) {
                    $action_module[$k]['action'][] = $val;
                }
            }
        }

        $action_name = $action_url;

        // 是否具有权限
        $is_allowed = self::is_user_allowed($user_id, $action_name);

        if ($is_allowed == 'no' && $is_root != 1 ) {
            echo 'Not Access!';
            exit() ;
        }
        //$curr_module = $module

        Response::assign('action_module', $action_module);
    }

    /**判断是否是管理员组
     * @param $group_id
     */
     public static function is_admin_group($group_id)
     {
        $result =   _uri('group',$group_id,'is_root');

         if($result) {
             return true;
         }

         return false;
     }

    /**根据分组获取到成员的数量
     * @param $group_id
     * @return int
     * @throws AnException
     */
    public static function get_member_nums($group_id)
    {
        if(!$group_id) {
            return 0;
        }

        $filter = array(
            'group_id' => $group_id,
        );

        //根据不同的登录账号获取不同的数据
        //全国是这样写，但是山东省进来了呢？咋写呢？如果是山东省
        return _model('group_user')->getTotal($filter);
    }

    public static function check_third_party( $member_info = array() )
    {
        if ( !$member_info ) {
            // 获取登录用户信息
            $member_info = member_helper::get_member_info();
        }
//p($member_info);exit;
        if (array_key_exists($member_info['ranks'], action_config::$allow)) {
            Response::redirect(AnUrl(action_config::$allow[$member_info['ranks']]));
            Response::flush();
            exit();
        }

        return '';
    }
}
?>