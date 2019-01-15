<?php

/**
 * alltosun.com  dbWrapper.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 宁海新 (ninghx@alltosun.com) $
 * $Date: 2012-9-15 下午9:10:24 $
 * $Id: session.php 776 2014-02-15 07:14:21Z liw $
*/

class AnOpenApiSessionWrapper extends AnOpenApiConnectAbstract implements AnOpenApiConnectWrapper
{
    public function checkAuth()
    {
        if ($this->site_name == 'qqweibo') {
            return $this->qqweiboCheckAuth();
        } else if($this->site_name == 'weixin') {
            return $this->weixinCheckAuth();
        } else {
            return $this->commonCheckAuth();
        }
    }

    private function qqweiboCheckAuth()
    {
        if (!$this->user_id) {
            return false;
        }

        if ($_SESSION['t_access_token'] && time() < $_SESSION['t_expires_time']) {
            return true;
        } else {
            return false;
        }
    }

    private function weixinCheckAuth()
    {
        if (!$this->user_id) {
            return false;
        }

        if (!empty($_SESSION['weixin_access_token']) && time() < $_SESSION['weixin_expires_time']) {
            return true;
        } else {
            return false;
        }
    }

    private function commonCheckAuth()
    {
        if (!$this->user_id) {
            return false;
        }

        if (isset($_SESSION[$this->site_name]['token']['access_token']) && isset($_SESSION[$this->site_name]['token']['expires_time']) && time() < $_SESSION[$this->site_name]['token']['expires_time']) {
            return true;
        } else {
            return false;
        }
    }

    public function saveAuth($token)
    {
        if ($this->site_name == 'qqweibo') {
            return $this->qqweiboSaveAuth($token);
        } else if($this->site_name == 'weixin') {
            return $this->weixinSaveAuth($token);
        } else {
            return $this->commonSaveAuth($token);
        }
    }

    private function qqweiboSaveAuth($token)
    {
        $_SESSION['t_openid']        = $token['openid'];
        $_SESSION['t_access_token']  = $token['access_token'];
        $_SESSION['t_refresh_token'] = $token['refresh_token'];
        $_SESSION['t_expire_in']     = $token['expires_in'];
        $_SESSION['t_expires_time']  = $token['expires_in'] + time();
    }

    private function weixinSaveAuth($token)
    {
        $_SESSION['weixin_openid']        = $token['openid'];
        $_SESSION['weixin_access_token']  = $token['access_token'];
        $_SESSION['weixin_refresh_token'] = $token['refresh_token'];
        $_SESSION['weixin_expires_in']    = $token['expires_in'];
        $_SESSION['weixin_expires_time']  = $token['expires_in'] + $time;
        // add scope
        $_SESSION['weixin_scope']         = $token['scope'];
    }

    private function commonSaveAuth($token)
    {
        $token['expires_time'] = $token['expires_in']+time();

        $_SESSION[$this->site_name]['token']['expires_time'] = $token['expires_time'];
        $_SESSION[$this->site_name]['token']['access_token'] = $token['access_token'];
    }

    public function getAccessToken()
    {
        $access_token = '';

        $access_token = $_SESSION[$this->site_name]['token']['access_token'];

        return $access_token;
    }
}
?>