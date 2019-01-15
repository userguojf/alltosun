<?php

/**
 * alltosun.com 微信公众平台接口实现类 weixin.php
 * ============================================================================
 * 版权所有 (C) 2009-2014 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址:   http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 李维 (liw@alltosun.com) $
 * $Date: 2014-1-16 下午12:48:04 $
 * $Id$
*/

require_once AnPHP::$dir_3rd.'/OpenApi/weixin/weixin_oauth2.php';

class weixinWrapper extends AnOpenApiAbstract
{
    public static $o;

    public function __construct($driver, $params)
    {
        parent::__construct($driver, $params);

        if(!self::$o) {
            self::$o = new weixin_oauth2(AnOpenApiAbstract::$akey, AnOpenApiAbstract::$skey);
        }
    }

    /**
     * 授权方法
     */
    public function authorize($scope = 'snsapi_base', $state = '', $redirect_hash = '#wechat_redirect')
    {
        require_once 'Authorize.php';
        $auth_instance = new weixinAuthorize();
        return $auth_instance->authorize($scope, $state, $redirect_hash);
    }

    /**
     * 授权的回调函数
     */
    public function callback($code)
    {
        if (!$code) {
            return false;
        }

        require_once 'Authorize.php';
        $auth_instance = new weixinAuthorize($code);
        return $auth_instance->callback();
    }

    /**
     * 获取用户信息
     * @param string $info
     * @param string $fields
     */
    public function getUserInfo($access_token, $openid)
    {
        return self::$o->getUserInfo($access_token, $openid);
    }

    /**
     * 错误信息
     */
    public function errorMsg()
    {
        return self::$o->errorMsg();
    }

}
?>