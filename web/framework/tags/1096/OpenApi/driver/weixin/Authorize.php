<?php

/**
 * alltosun.com 微信网页版oauth2授权类 Authorize.php
 * ============================================================================
 * 版权所有 (C) 2009-2014 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址:   http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 李维 (liw@alltosun.com) $
 * $Date: 2014-1-14 上午11:06:54 $
 * $Id$
*/

/*
--
-- 表的结构 `connect_weixin`
--

CREATE TABLE IF NOT EXISTS `connect_weixin` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `connect_open_id` varchar(255) NOT NULL DEFAULT '0' COMMENT '接口返回的open_id',
  `scope` varchar(255) NOT NULL COMMENT '授权作用域',
  `connect_site_id` int(10) unsigned NOT NULL DEFAULT '0',
  `connect_user_name` varchar(255) NOT NULL COMMENT '唯一用户名',
  `connect_nick_name` varchar(255) NOT NULL COMMENT '昵称（可重复）',
  `access_token` varchar(255) NOT NULL DEFAULT '',
  `refresh_token` varchar(255) NOT NULL DEFAULT '',
  `expires_time` varchar(255) NOT NULL DEFAULT '',
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

 */

class weixinAuthorize
{
    private $callback;
    private $o;
    private $code;

    public function __construct($code = '')
    {
        $this->code = $code;
        $this->callback = AnOpenApiAbstract::$callback;
        $this->o = new weixin_oauth2(AnOpenApiAbstract::$akey, AnOpenApiAbstract::$skey);
    }

    /**
     * 授权方法
     * @return string 授权地址
     */
    public function authorize($scope, $state, $redirect_hash)
    {
        $result = array();

        $openapi_connect = AnOpenApiConnect::connect();
        $auth_status = $openapi_connect->checkAuth($scope);
        $result['status'] = $auth_status;

        if ($auth_status != 'ok') {
            $result['url'] = $this->o->getAuthorizeURL($this->callback, $scope, $state, $redirect_hash);
            if(!$result['url']) {
                throw new AnException($this->o->errorMsg());
            }
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

        $token = $this->o->getAccessTokenInfo($this->code, 'code');
        if(!$token) {
            throw new AnException($this->o->errorMsg());
        }
        if (empty($token['access_token'])) {
            throw new AnException('authorize_fail');
        }

        // 如果是基础授权（只能获取openid）
        /*if($token['scope'] == 'snsapi_base') {
            // @todo 等待接口进一步开放
        } else {
            $user_info = $this->o->getUserInfo($token['access_token'], $token['openid']);
            if ($user_info) {
                $token['user_info'] = $user_info;
            }
        }*/

        // 调用回调函数的方法
        $openapi_connect = AnOpenApiConnect::connect();
        return $openapi_connect->saveAuth($token);
    }
}
?>