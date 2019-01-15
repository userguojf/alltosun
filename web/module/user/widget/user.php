<?php

/**
 * alltosun.com 用户widget user.php
 * ============================================================================
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: Shenxn 申小宁 (shenxiaoninglove@sina.com) $
 * $Date: Nov 7, 2013 3:25:56 PM $
 * $Id: user.php 83005 2013-11-08 08:57:18Z shenxn $
 */

require_once ROOT_PATH .'/module/user/helper/user_api.php';


class user_widget
{
    /**
     * 登录操作
     * @param unknown_type $code
     * @param unknown_type $vcode
     * @param unknown_type $tel
     * @param unknown_type $remember_me
     * @param unknown_type $admin
     * @return string
     */
    public function login($code, $vcode, $tel, $admin)
    {

        if (!$code) {
            return '请填写验证码!';
        }

        if (!$vcode) {
            return '请填写短信验证码!';
        }

        if (!$tel) {
            return '请填写手机号!';
        }

        // 判断手机号码类型
        $type = _widget("message")->check_mobile_type($tel);

        if ($type == 0) {
            return '手机号码格式有误';
        } else if ($type == 1) {
            $user_api = new user_api();
            $result = $user_api->check($tel, $vcode);

            if ($result['info'] != 'ok' || !$result['user_id']) {
                $msg = '巨惠通登录失败';
                $con = var_export($result, true);
                _widget('email')->mail($msg, $con);
                return  array('info'=>'登录失败，请稍后重试!');
            }
        } else {
            $result = _widget('message.login')->check_check_code($tel, $vcode);
            if ($result !== 'ok') {
                return array('info'=>$result);
            }
        }

        $user_info = _model('user')->read(array('phone'=>$tel));
        if (!$user_info) {

            $user_arr['phone']           = $tel;
            $user_arr['hash']            = uniqid();
            $user_arr['last_login_time'] = date('Y-m-d H:i:s');
            $user_arr['last_login_ip']   = tools_helper::get_client_ip();

            $insert_id = _model('user')->create($user_arr);
            $_SESSION['user_id'] = $insert_id;

            $user_info = $user_arr;
        } else {
            _model('user')->update($user_info['id'] , array('last_login_time' => date('Y-m-d H:i:s'), 'last_login_ip'=>tools_helper::get_client_ip()));
            $_SESSION['user_id'] = $user_info['id'];
        }

        $_SESSION['is_phone'] = 1;


        // @wangdk调试权限
//         if ($user_info['id'] == 1117) {
//             $user_info['id'] = 66045;
//             $_SESSION['user_id'] = 66045;
//         }

        // 后台登录有获取一个有权限的控制器进入
        $url = '';

        if ($admin) {
            $action_info = action_helper::get_user_action($user_info['id']);

            if ($action_info) {
                $url = AnUrl($action_info['url']);
            }
        }

        return array('info'=>'ok', 'url'=>$url, 'admin'=>$admin);
    }

    /**
     * 发送短信
     * @param unknown_type $tel
     * @param unknown_type $code
     */
    public function send_vcode($tel, $code)
    {
        if (!$tel) {
            return '请填写手机号!';
        }

        if (!$code) {
            return '请填写验证码!';
        }

        if (!isset($_SESSION['securimage_code_value']) && !$_SESSION['securimage_code_value']) {
            return '请填写验证码!';
        }

        if (strtolower($_SESSION['securimage_code_value']) != strtolower($code)) {
//             $msg = '巨惠通验证码失败,'.strtolower($_SESSION['securimage_code_value']).'---'.strtolower($code);
//             _widget('email')->mail($msg, $msg);
            return '验证码不正确!';
        }

        $_SESSION['securimage_code_value'] = '';

        // 判断一下手机号码类型
        $type = _widget("message")->check_mobile_type($tel);

        if (!$type) {
            return '手机号码格式有误';
        }

        if ($type == 1) {
            $user_api = new user_api();
            return $user_api->send_vcode($tel);
        } else {

            // 发送短信
            return _widget('message.login')->send_message($tel);
        }
        //return array('info'=>'ok', '下发成功!');
    }

    /**
     * 登录操作
     * @param unknown_type $code
     * @param unknown_type $vcode
     * @param unknown_type $tel
     * @param unknown_type $remember_me
     * @param unknown_type $admin
     * @return string
     */
    public function login_4g($vcode, $tel, $admin)
    {
        if (!$vcode) {
            return '请填写短信验证码!';
        }

        if (!$tel) {
            return '请填写手机号!';
        }

        $type = _widget("message")->check_mobile_type($tel);

        if ($type == 0) {
            return '手机号码格式有误';
        } else if ($type == 1) {
            $user_api = new user_api();

            $result = $user_api->check($tel, $vcode);

            if ($result['info'] != 'ok' || !$result['user_id']) {
                return  array('info'=>'登录失败，请稍后重试!');
            }
        } else {

            $result = _widget('message.login')->check_check_code($tel, $vcode);

            if ($result != 'ok') {
                return array('info'=>'登录失败，请稍后重试!');
            }
        }

        $user_info = _model('user')->read(array('phone'=>$tel));
        if (!$user_info) {

            $user_arr['phone']           = $tel;
            $user_arr['hash']            = uniqid();
            $user_arr['last_login_time'] = date('Y-m-d H:i:s');
            $user_arr['last_login_ip']   = tools_helper::get_client_ip();

            $insert_id = _model('user')->create($user_arr);
            $_SESSION['user_id'] = $insert_id;

            $user_info = $user_arr;
        } else {
            _model('user')->update($user_info['id'] , array('last_login_time' => date('Y-m-d H:i:s'), 'last_login_ip'=>tools_helper::get_client_ip()));
            $_SESSION['user_id'] = $user_info['id'];
        }

        $_SESSION['is_phone'] = 1;

        $url = '';
        if ($admin) {
            $action_info = action_helper::get_user_action($user_info['id']);

            if ($action_info) {
                $url = AnUrl($action_info['url']);
            }
        }

        return array(
                'info'      => 'ok',
                'url'       => $url,
                'admin'     => $admin,
                'user_id'   => $_SESSION['user_id'],
                'user_type' => special_helper::check_special_user()
        );
    }

    /**
     * 发送短信
     * @param unknown_type $tel
     * @param unknown_type $code
     */
    public function send_vcode_4g($tel)
    {
        if (!$tel) {
            return '请填写手机号!';
        }

        $type = _widget("message")->check_mobile_type($tel);

        if ($type == 0) {
            return '手机号码格式有误';
        } else if ($type == 1) {
            $user_api = new user_api();
            return $user_api->send_vcode($tel);
        } else {
            // 发送短信验证码
            return _widget('message.login')->send_message($tel);
        }
    }
}
?>