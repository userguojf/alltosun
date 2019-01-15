<?php

/**
 * alltosun.com  Authorize.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 宁海新 (ninghx@alltosun.com) $
 * $Date: 2012-9-18 下午5:42:36 $
 * $Id: Authorize.php 643 2013-02-07 12:16:41Z anr $
*/

class kaixinAuthorize
{
    private $code;
    private $oauth;

    public function __construct($code = '')
    {
        $this->code = $code;
        $this->oauth = new KXClient($code, AnOpenApiAbstract::$config);
    }

    public function authorize()
    {
        $result = array();

        $openapi_connect = AnOpenApiConnect::connect();
        $auth_status = $openapi_connect->checkAuth();
        $result['status'] = $auth_status;

        if ($auth_status != 'ok') {
            $result['url'] = $this->oauth->getAuthorizeURL('code', 'create_records');
        }

        return $result;
    }

    public function callback()
    {
        if (!$this->code) {
            return false;
        }

        $token = $this->oauth->getAccessTokenFromCode($this->code);
        if (empty($token['access_token'])) {
            throw new AnException('authorize_fail');
        }

        $connection = new KXClient($token['access_token'], AnOpenApiAbstract::$config);

        $user_info = $connection->users_me();

        if ($user_info) {
            $token['user_name'] = $user_info['response']['name'];
            $token['uid'] = $user_info['response']['uid'];
        }

        // 调用回调函数的方法
        $openapi_connect = AnOpenApiConnect::connect();

        return $openapi_connect->saveAuth($token);
    }
}
?>