<?php

/**
 * alltosun.com 新浪微博授权类 Authorize.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 宁海新 (ninghx@alltosun.com) $
 * $Date: 2012-9-16 上午11:23:58 $
 * $Id: Authorize.php 792 2014-07-06 03:43:03Z qianym $
*/

class sinaweiboAuthorize
{
    private $callback;
    private $o;
    private $code;

    public function __construct($code = '')
    {
        $this->code = $code;
        $this->callback = AnOpenApiAbstract::$callback;
        $this->o = new SaeTOAuthV2(AnOpenApiAbstract::$akey, AnOpenApiAbstract::$skey);
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
            $result['url'] = $this->o->getAuthorizeURL($this->callback);
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

        $keys = array();
        $keys['code'] = $this->code;
        $keys['redirect_uri'] = $this->callback;

        $token = $this->o->getAccessToken('code', $keys);
        if (empty($token['access_token'])) {
            throw new AnException('authorize_fail');
        }
        $wb = new SaeTClientV2(AnOpenApiAbstract::$akey, AnOpenApiAbstract::$skey, $token['access_token']);
        $user_info = $wb->show_user_by_id($token['uid']);
        if ($user_info) {
            if (isset($user_info['screen_name'])) {
                $token['user_name'] = $user_info['screen_name'];
            } else if (isset($user_info['name'])) {
                $token['user_name'] = $user_info['name'];
            } else {
                throw new Exception('授权失败，请检查是否已加测试账号');
            }
        }
        // 调用回调函数的方法
        $openapi_connect = AnOpenApiConnect::connect();
        return $openapi_connect->saveAuth($token);
    }
}
?>