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

class qqweiboAuthorize
{
    private $callback;
    private $code;

    public function __construct($code = '')
    {
        $this->callback      = AnOpenApiAbstract::$callback;
        $this->code          = $code;

        OAuth::init(AnOpenApiAbstract::$akey, AnOpenApiAbstract::$skey);
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
            $result['url'] = OAuth::getAuthorizeURL($this->callback);
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

        $openid  = Request::Get('openid');
        $openkey = Request::Get('openkey');

        //获取授权token
        $url = OAuth::getAccessToken($this->code, $this->callback);
        $r = Http::request($url);
        // $out = array('access_token', 'expires_in', 'refresh_token', 'name', 'nick');
        // openid=C095CADD4E5CC688CDBC1CCDC7BC712B&openkey=7DC90FB41F4B71290554B6D9A19956B1
        parse_str($r, $token);

    	if (!$token) {
    	    throw new AnException('authorize_fail');
    	}

    	$token['openid']  = $openid;
    	$token['openkey'] = $openkey;
    	$token['code']    = $this->code;

    	// 调用回调函数的方法
    	$openapi_connect = AnOpenApiConnect::connect();
    	return $openapi_connect->saveAuth($token);
    }
}
?>