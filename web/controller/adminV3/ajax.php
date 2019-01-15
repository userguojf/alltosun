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
 * $Date: 2016-8-30 下午4:36:55 $
 * $Id$
 */
class Action
{
    public function login_auth()
    {
        $username = tools_helper::post('username', '');
        $password = trim(tools_helper::post('password', ''));
        $vcode    = tools_helper::post('vcode', '');

        //验证账号是否已被锁定
        $is_lock = $this->verify_is_lock($username);
        if ($is_lock) {
            return array('info' => 'error', 'msg' => '此账号异常已被锁定20分钟');
        }

        if (!$vcode || !Captcha::check($vcode)) {
            return array('info' => 3);
        }

        $member_info = _model("member")->read(array("member_user"=>$username));

        //用户名
        if (!$member_info || $member_info['status'] == 0) {

            $error = '登录失败，你的帐户不存在';
            //记录错误信息
            $this->record_login_error($username, $password, $error);

            return array('info' => 1);
        }

        //密码
        if ($member_info['member_pass'] != md5($password)) {

            $error = '登录失败，用户名或密码错误';
            //记录错误信息
            $this->record_login_error($username, $password, $error);

            return array('info' => 2);
        }

        return array('info' => 'ok');
    }

    /**
     * 记录登录错误信息
     * @param unknown $user_number
     * @param unknown $password
     * @param unknown $error
     */
    private function record_login_error($user_number, $password, $error)
    {
        $ip = get_user_ip();
        //记录
        _model('login_error_record')->create(array(
                'user_number' => $user_number,
                'password' => $password,
                'error' => $error,
                'ip' => $ip
        ));

        return true;
    }

    /**
     * 校验错误次数
     */
    private function verify_is_lock($user_number)
    {
        //如果没有渠道编码， 暂未想到适合的处理方法
        if (!$user_number) {
            return false;
        }

        //查询错误次数 默认2小时之内
        $max = 7200;
        $filter = array(
                'user_number' => $user_number,
                'add_time >' => date('Y-m-d H:i:s', time() - 7200)
        );

        $error_list = _model('login_error_record')->getList( $filter );

        //没有登录错误的
        if ( !$error_list ) {
            return false;
        }

        $new_arr =array();

        foreach ($error_list as $k => $v) {
            $new_arr[$v['user_number'].'_'.$v['ip']] = isset($new_arr[$v['user_number'].'_'.$v['ip']]) ? ++$new_arr[$v['user_number'].'_'.$v['ip']] : 1;
        }

        $error_times = max($new_arr);

        if ($error_times >= 5) {
            return true;
        }

        return false;
    }
}