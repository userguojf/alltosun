<?php

/**
 * alltosun.com qq微博授权类 Authorize.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 宁海新 (ninghx@alltosun.com) $
 * $Date: 2012-9-18 上午10:38:28 $
 * $Id: Authorize.php 643 2013-02-07 12:16:41Z anr $
*/

class qqsnsAuthorize
{
    private $callback;
    private $code;
    private $base_url;

    public function __construct($code = '')
    {
        $this->callback      = AnOpenApiAbstract::$callback;
        $this->code          = $code;
        $this->base_url      = 'https://graph.qq.com/oauth2.0/authorize?';
    }

    /**
     * 授权方法
     * @return string 授权地址
     */
    public function authorize()
    {
        $code_url = $this->base_url;
        $openapi_connect = AnOpenApiConnect::connect();
        $has_valid_auth = $openapi_connect->checkAuth();
        if (!$has_valid_auth) {
            $code_url .= http_build_query(array('client_id'=>AnOpenApiAbstract::$akey, 'response_type'=>'code', 'redirect_uri'=>$this->callback));
        }
        return $code_url;
    }

    /**
     * 授权的回调函数
     */
    public function callback()
    {
        if (!$this->code) {
            return false;
        }

        // 获取token
        $token_url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&".http_build_query(array('client_id'=>AnOpenApiAbstract::$akey, 'client_secret'=>AnOpenApiAbstract::$skey, 'redirect_uri'=>$this->callback))."&code=".$this->code;
        $response = file_get_contents($token_url);

        if (!$response) {
            throw new AnException('authorize_fail');
        }
        parse_str($response, $params);

        $access_token = $params['access_token'];
        $expires_in = $params['expires_in'];

        // 获取open_id
        $graph_url = "https://graph.qq.com/oauth2.0/me?access_token=".$access_token;

        $tmp_str  = file_get_contents($graph_url);

        if (strpos($tmp_str, "callback") !== false) {
            $lpos = strpos($tmp_str, "(");
            $rpos = strrpos($tmp_str, ")");
            $str  = substr($tmp_str, $lpos + 1, $rpos - $lpos -1);
        }

        $user = json_decode($str);
        $open_id = $user->openid;

        if (!$open_id) {
            throw new AnException('authorize_fail');
        }

        // 获取用户信息
        $user_params = http_build_query(array('oauth_consumer_key'=>AnOpenApiAbstract::$akey, 'openid'=>$open_id, 'access_token'=>$access_token, 'format'=>'json'));

        $user_info_url = "https://graph.qq.com/user/get_user_info?".$user_params;

        $user_info = file_get_contents($user_info_url);
        $user_info = json_decode($user_info, true);

        $token = array('access_token'    => $access_token,
                       'expires_in'      => $expires_in,
                       'name'            => $user_info['nickname'],
                       'nick'            => $user_info['nickname'],
                       'openid'          => $open_id,
                       'refresh_token'   => ''
                );

        // 调用回调函数的方法
    	$openapi_connect = AnOpenApiConnect::connect();
    	return $openapi_connect->saveAuth($token);
    }
}
?>