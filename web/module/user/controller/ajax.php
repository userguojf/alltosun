<?php
/**
 * alltosun.com  ajax.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王德康 (wangdk@alltosun.com) $
 * $Date: 2014-6-23 下午3:15:12 $
 * $Id$
 */

class Action
{
    /**
     * 登录
     */
    public function login()
    {
        if (!tools_helper::is_safe(true)) {
            return '非法请求';
        }

        // 验证码
        $code  = tools_helper::post('code', '');
        // 短信
        $vocde = tools_helper::post('vcode', '');
        // 手机号
        $tel   = tools_helper::post('tel', '');
        // 是否是后台登陆
        $admin = tools_helper::post('admin', 0);

        return _widget('user')->login($code, $vocde, $tel, $admin);
    }

    public function send_vcode()
    {
        if (!tools_helper::is_safe(true)) {
            return '非法请求';
        }

        // 手机号
        $tel   = tools_helper::post('tel', '');
        $code  = tools_helper::post('code', '');
        return _widget('user')->send_vcode($tel, $code);
    }
    
    public function verify_phone()
    {
        if (!tools_helper::is_safe(true)) {
            return '非法请求';
        }

        // 手机号
        $tel   = tools_helper::post('tel', '');

        //获取是否为4g用户
        if (true) {
            return 'ok';
        }

        return 'on';
    }

    public function send_vcode_4g()
    {
        if (!tools_helper::is_safe(true)) {
            return '非法请求';
        }
    
        // 手机号
        $tel   = tools_helper::post('tel', '');
        return _widget('user')->send_vcode_4g($tel);
    }
    
    public function login_4g()
    {
        if (!tools_helper::is_safe(true)) {
            return '非法请求';
        }
        // 短信
        $vocde = tools_helper::post('vcode', '');
        // 手机号
        $tel   = tools_helper::post('tel', '');
    
        return _widget('user')->login_4g($vocde, $tel, 0);
    }
}
?>