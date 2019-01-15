<?php

/**
 * alltosun.com 管理后台控制器 index.php
 * ============================================================================
 * 版权所有 (C) 2009-2011 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 李微微 $
 * $Date: 2012-11-21 下午04:38:22 $
 * $Id$
*/

class Action
{
    private $member_id = 0;

    public function __construct()
    {
        $this->member_id = member_helper::get_member_id();
    }

    /**
     * 登录主页面
     * @param string $action
     * @param array $params
     */
    public function __call($action = '', $params = array())
    {
        if (!$this->member_id) {
            // 登录页面
            Response::display("admin/login.html");
            return;
        }

        Response::redirect(AnUrl('probe_brand/admin'));
    }

    public function e_login()
    {

        $redirect_url = tools_helper::get('redirect_url', '');

        if (!$redirect_url) {
            $redirect_url = AnUrl('e/admin/probe');
        }

        if ($this->member_id) {
            Response::redirect($redirect_url);
        }

        $path       = parse_url($redirect_url);
        $path_info  = explode('/', trim($path['path'],'/'));

        if (!empty($path_info[2]) && !empty($path_info[2]) && $path_info[2] == 'rfid') {
            Response::assign('msg', '已检测到您的RFID设备');
            Response::assign('msg1','ID:'.$path_info[4]);
        }

        // 登录页面
        Response::assign('redirect_url', $redirect_url);
        Response::display("admin/e_login.html");
    }

    /**
     * 移动端登录页面
     * @return string
     */
    public function e_login_auth()
    {
        $username       = tools_helper::post('username', '');
        $password       = trim(tools_helper::post('password', ''));
        $vcode          = tools_helper::post('vcode','');

        if (!Request::isAjax()) {
            return '访问失败！';
        }

        if ($this->member_id) {
            return 'ok';
        }

        if (!$username || !$password) {
            return '用户名或密码不能为空';
        }

        if (!$vcode || !isset($_SESSION['securimage_code_value']) || (strtolower($vcode) != $_SESSION['securimage_code_value'])) {
            return '验证码不正确';
        }

        $member_info = _model("member")->read(array('member_user' => $username));

        if (!$member_info || $member_info['status'] == 0) {
            return '登录失败，你的帐户不存在';
        }

        if ($member_info['member_pass'] != md5($password)) {
            return '登录失败，用户名或密码错误';
        }

        //超极管理员仅有$user_action_list['is_root']==1
        $user_action_list = action_helper::user_action_list($member_info['id']);

        if ( (!isset($user_action_list['is_root']) && !$user_action_list['is_root']) || !$user_action_list) {
            return '您没有访问的权限，需要管理员才可访问';
        }

        /** 新加：如果营业厅管理员登录，判断是否有区id, 如果没有则不能登录 **/
        if ( $member_info['res_name'] == 'business_hall' ) {
            $b_info = _model('business_hall')->read(array('id'=>$member_info['res_id']));
            if ( !$b_info || !$b_info['area_id'] ) {
                return '无法登录，营业厅信息不完整';
            }
        }

        member_helper::remember_me_set($member_info);

        return 'ok';
    }

    public function login_auth()
    {
        if ($this->member_id) {

            if (Request::isAjax()) {
                Response::redirect(AnUrl('e/admin/probe'));
                return false;
            }

            action_helper::check_third_party();

            Response::redirect(AnUrl('admin'));
            return false;
        }

        $username = tools_helper::post('username', '');
        $password = trim(tools_helper::post('password', ''));
        $vcode    = tools_helper::post('vcode','');

        if (!$username || !$password) {
            return array('用户名或密码不能为空', 'error');
        }

        //if (!$vcode || !isset($_SESSION['securimage_code_value']) || (strtolower($vcode) != $_SESSION['securimage_code_value'])) {
        if (!$vcode || !Captcha::check($vcode)) {
            return array('验证码不正确', 'error');
        }

        $member_info = _model("member")->read(array("member_user"=>$username));

        if (!$member_info || $member_info['status'] == 0) {
            return '登录失败，你的帐户不存在';
        }

        if ($member_info['member_pass'] != md5($password)) {
            return '登录失败，用户名或密码错误';
        }

        //超极管理员仅有$user_action_list['is_root']==1
        $user_action_list = action_helper::user_action_list($member_info['id']);

        if ( (!isset($user_action_list['is_root']) && !$user_action_list['is_root']) || !$user_action_list) {
            return array('您没有访问的权限，需要管理员才可访问', 'error');
        }

        /** 新加：如果营业厅管理员登录，判断是否有区id, 如果没有则不能登录 **/
        if ( $member_info['res_name'] == 'business_hall' ) {
            $b_info = _model('business_hall')->read(array('id'=>$member_info['res_id']));
            if ( !$b_info || !$b_info['area_id'] ) {
                return '无法登录，营业厅信息不完整';
            }
        }

        member_helper::remember_me_set($member_info);

        // @FIXME 登录后的跳转地址
        if (!Request::isAjax()) {
            action_helper::check_third_party($member_info);
            Response::redirect(AnUrl('admin'));
        } else {
            return 'ok';
        }
    }

    public function logout()
    {
        $is_elog = tools_helper::get('is_elog', 0);  //是否是e模块的退出

        $_SESSION['member_id'] = 0;

        member_helper::remember_me_expire();

        if ($is_elog) {
            Response::redirect(AnUrl('e/admin'));
        } else {
            Response::redirect(AnUrl('admin'));
        }
    }

}
?>
