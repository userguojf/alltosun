<?php

/**
 * alltosun.com 人人授权类 Authorize.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 宁海新 (ninghx@alltosun.com) $
 * $Date: 2012-9-18 上午12:09:05 $
 * $Id: Authorize.php 643 2013-02-07 12:16:41Z anr $
*/

class renrenAuthorize
{
    private $oauth;
    private $code;

    public function __construct($code = '')
    {
        $this->code      = $code;
        $this->oauth     = new RenRenOauth();
    }

    /**
     * 授权方法
     * @return string 授权地址
     */
    public function authorize()
    {
        $result = array();

        $openapi_connect = AnOpenApiConnect::connect();
        $auth_status = $openapi_connect->checkAuth();
        $result['status'] = $auth_status;

        if ($auth_status != 'ok') {
            $result['url'] = $this->oauth->getAuthorizeUrl();
        }

        return $result;
    }

    /**
     * 授权的回调函数
     */
    public function callback()
    {
        if (!$this->code) {
            return false;
        }
        $tmp_token = $this->oauth->getAccessToken($this->code);
        if (!$tmp_token) {
            throw new AnException('authorize_fail');
        }

        $token = array();
        $token['uid'] = $tmp_token['user']['id'];
        $token['access_token'] = $tmp_token['access_token'];
        $token['expires_in'] = $tmp_token['expires_in'];
        $token['refresh_token'] = $tmp_token['refresh_token'];
        $token['user_name'] = $tmp_token['user']['name'];

        // 调用回调函数的方法
        $openapi_connect = AnOpenApiConnect::connect();
        return $openapi_connect->saveAuth($token);
    }
}
?>