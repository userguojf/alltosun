<?php
/**
 * alltosun.com 微信oauth2授权基础类 weixin_oauth2.php
 * ============================================================================
 * 版权所有 (C) 2009-2014 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址:   http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 李维 (liw@alltosun.com) $
 * $Date: 2014-1-14 上午10:50:43 $
 * $Id: weixin_oauth2.php 756 2014-01-14 06:06:56Z liw $
*/

/**
 * 公众平台oauth2接口说明
 * @see http://mp.weixin.qq.com/wiki/index.php?title=%E7%BD%91%E9%A1%B5%E6%8E%88%E6%9D%83%E8%8E%B7%E5%8F%96%E7%94%A8%E6%88%B7%E5%9F%BA%E6%9C%AC%E4%BF%A1%E6%81%AF
 */
class weixin_oauth2
{
    private $appid;
    private $appsecret;

    private $err_msg = '';

    private $scope_list = array(
                    'snsapi_base',
                    'snsapi_userinfo'
    );

    /**
     * print the debug info
     * @ignore
     */
    private $debug = FALSE;

    /**
     * http code
     */
    public $http_code = NULL;

    /**
     * 上次请求的http信息
     */
    public $http_info = array();

    /**
     * Set timeout default.
     */
    public $timeout = 30;

    /**
     * Set connect timeout.
     * @ignore
     */
    public $connecttimeout = 30;

    /**
     * Verify SSL Cert.
     * @ignore
     */
    public $ssl_verifypeer = FALSE;

    /**
     * Set the useragnet.
     * @ignore
     */
    public $useragent = 'WEIXIN OAuth2 v0.1';
    /**
     * boundary of multipart
     * @ignore
     */
    public static $boundary = '';


    public function __construct($appid, $appsecret)
    {
        $this->appid     = $appid;
        $this->appsecret = $appsecret;

        if(!$this->appid || !$this->appsecret) {
            throw new Exception("weixin_oauth2 init failed: appid or appsecret empty");
        }
    }

    /**
     * 获取access_token
     * @param string $code_or_token 要么是code 要么是refresh_token
     * @param string $type 'code' or 'refresh_token'
     * @return boolean|string 成功返回openid 失败返回false
     */
    public function getAccessToken($code_or_token, $type = 'code')
    {
        $response = $this->getAccessTokenInfo($code_or_token, $type);
        if(!$response) {
            return false;
        }

        return $response['access_token'];
    }

    /**
     * 获取access_token_info
     * @param string $code_or_token 要么是code 要么是refresh_token
     * @param string $type 'code' or 'refresh_token'
     * @return boolean|array
     */
    public function getAccessTokenInfo($code_or_token, $type = 'code')
    {
        if(!$code_or_token) {
            $this->err_msg = "getAccessTokenInfo failed: code empty";
            return false;
        }

        if($type == 'code') {

            $url = "https://api.weixin.qq.com/sns/oauth2/access_token";
            $response = $this->get($url, array(
                            'appid'      => $this->appid,
                            'secret'     => $this->appsecret,
                            'code'       => $code_or_token,
                            'grant_type' => 'authorization_code'
            ), true);

        } else if($type = 'refresh_token') {

            $url = "https://api.weixin.qq.com/sns/oauth2/refresh_token";
            $response = $this->get($url, array(
                            'appid'         => $this->appid,
                            'refresh_token' => $code_or_token,
                            'grant_type'    => 'refresh_token'
            ), true);

        } else {

            $this->err_msg = "getAccessTokenInfo failed: param type is not supported";
            return false;

        }

        if(!$response) {
            $this->err_msg = "getAccessTokenInfo failed: http response empty";
            return false;
        }

        // 状态码错误
        if(!empty($response['errcode']) && $response['errcode'] != 0) {
            $this->err_msg = !empty($response['errmsg']) ? $response['errmsg'] . $response['errcode'] : $response['errcode'];
            $this->err_msg = 'getAccessTokenInfo failed: ' . $this->err_msg;
            return false;
        }

        return $response;
    }

    /**
     * 刷新access_token
     * @param string $refresh_token 刷新token
     * @return bool|array 成功返回数组
     */
    public function refreshToken($refresh_token)
    {
        if(!$refresh_token) {
            $this->err_msg = "refresh_token failed: refresh_token param empty";
            return false;
        }

        $access_token_info = $this->getAccessTokenInfo($refresh_token, 'refresh_token');
        if(!$access_token_info) {
            return false;
        }

        return $access_token_info;
    }

    /**
     * 获取openid
     * @param string $code_or_token 要么是code 要么是Access_token
     * @param string $type 'code' or 'refresh_token'
     * @return boolean|string 成功返回openid 失败返回false
     */
    public function getOpenid($code_or_token, $type = 'code')
    {
        $access_token_info = $this->getAccessTokenInfo($code_or_token, $type);
        if(!$access_token_info) {
            return false;
        }
        return $access_token_info['openid'];
    }

    /**
     * 获取用户信息
     */
    public function getUserInfo($access_token, $openid, $lang = 'zh_CN')
    {
        $url = 'https://api.weixin.qq.com/sns/userinfo';
        $response = $this->get($url, array(
                        'access_token' => $access_token,
                        'openid'       => $openid,
                        'lang'         => $lang
        ), true);

        if(!$response) {
            $this->err_msg = "getUserInfo failed: httprequest failed";
            return false;
        }

        // 状态码错误
        if(!empty($response['errcode']) && $response['errcode'] != 0) {
            $this->err_msg = !empty($response['errmsg']) ? $response['errmsg'] : $response['errcode'];
            $this->err_msg = 'getUserInfo failed: ' . $this->err_msg;
            return false;
        }

        return $response;
    }

    /**
     * 获取授权地址
     * @param string $scope 授权作用域权限
     * @return boolean|string 成功返回true,失败返回false
     */
    public function getAuthorizeURL($redirect_uri, $scope = 'snsapi_base', $state = '', $redirect_hash = '#wechat_redirect')
    {
        if(!in_array($scope, $this->scope_list, true)) {
            $this->err_msg = "get_authorize_url: scope not supported($scope)";
            return false;
        }

        $redirect_uri = urlencode($redirect_uri);
        return "https://open.weixin.qq.com/connect/oauth2/authorize"
                . "?appid={$this->appid}"
                . "&redirect_uri={$redirect_uri}"
                . "&response_type=code"
                . "&scope={$scope}"
                . "&state={$state}"
                . "{$redirect_hash}";
    }

    /**
     * GET wrappwer for oauthRequest.
     *
     * @return mixed
     */
    private function get($url, $parameters = array(), $is_json = true)
    {
        $response = $this->oauthRequest($url, 'GET', $parameters);
        if($is_json) {
            return json_decode($response, true);
        }
        return $response;
    }

    /**
     * POST wreapper for oauthRequest.
     *
     * @return mixed
     */
    private function post($url, $parameters = array(), $multi = false, $is_json = true)
    {
        $response = $this->oauthRequest($url, 'POST', $parameters, $multi );
        if($is_json) {
            return json_decode($response, true);
        }
        return $response;
    }

    /**
     * DELTE wrapper for oAuthReqeust.
     *
     * @return mixed
     */
    private function delete($url, $parameters = array(), $is_json = true)
    {
        $response = $this->oauthRequest($url, 'DELETE', $parameters);
        if($is_json) {
            return json_decode($response, true);
        }
        return $response;
    }

    /**
     * Format and sign an OAuth / API request
     *
     * @return string
     * @ignore
     */
    private function oauthRequest($url, $method, $parameters, $multi = false)
    {
        switch ($method) {
            case 'GET':
                $url = $url . '?' . http_build_query($parameters);
                return $this->http($url, 'GET');
            default:
                $headers = array();
                if (!$multi && (is_array($parameters) || is_object($parameters)) ) {
                    $body = http_build_query($parameters);
                } else {
                    $body = self::buildHttpQueryMulti($parameters);
                    $headers[] = "Content-Type: multipart/form-data; boundary=" . self::$boundary;
                }
                return $this->http($url, $method, $body, $headers);
        }
    }

    /**
     * Make an HTTP request
     *
     * @return string API results
     * @ignore
     */
    private function http($url, $method, $postfields = NULL, $headers = array())
    {
        $this->http_info = array();
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
        curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ci, CURLOPT_ENCODING, "");
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
        curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
        curl_setopt($ci, CURLOPT_HEADER, FALSE);

        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                }
                break;
            case 'DELETE':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($postfields)) {
                    $url = "{$url}?{$postfields}";
                }
        }

        curl_setopt($ci, CURLOPT_URL, $url );
        if($headers) {
            curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
        }
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );

        $response = curl_exec($ci);
        $this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        $this->http_info = array_merge($this->http_info, curl_getinfo($ci));

        if ($this->debug) {
            echo "=====post data======\r\n";
            var_dump($postfields);

            echo '=====info====='."\r\n";
            print_r( curl_getinfo($ci) );

            echo '=====$response====='."\r\n";
            print_r( $response );
        }
        curl_close ($ci);
        return $response;
    }

    /**
     * Get the header info to store.
     *
     * @return int
     * @ignore
     */
    private function getHeader($ch, $header)
    {
        $i = strpos($header, ':');
        if (!empty($i)) {
            $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
            $value = trim(substr($header, $i + 2));
        }
        return strlen($header);
    }

    /**
     * @ignore
     */
    private static function buildHttpQueryMulti($params)
    {
        if (!$params) return '';

        uksort($params, 'strcmp');

        $pairs = array();

        self::$boundary = $boundary = uniqid('------------------');
        $MPboundary = '--'.$boundary;
        $endMPboundary = $MPboundary. '--';
        $multipartbody = '';

        foreach ($params as $parameter => $value) {

            if( in_array($parameter, array('pic', 'image')) && $value{0} == '@' ) {
                $url = ltrim( $value, '@' );
                $content = file_get_contents( $url );
                $array = explode( '?', basename( $url ) );
                $filename = $array[0];

                $multipartbody .= $MPboundary . "\r\n";
                $multipartbody .= 'Content-Disposition: form-data; name="' . $parameter . '"; filename="' . $filename . '"'. "\r\n";
                $multipartbody .= "Content-Type: image/unknown\r\n\r\n";
                $multipartbody .= $content. "\r\n";
            } else {
                $multipartbody .= $MPboundary . "\r\n";
                $multipartbody .= 'content-disposition: form-data; name="' . $parameter . "\"\r\n\r\n";
                $multipartbody .= $value."\r\n";
            }

        }

        $multipartbody .= $endMPboundary;
        return $multipartbody;
    }

    /**
     * 获取错误信息
     */
    public function errorMsg()
    {
        return $this->err_msg;
    }

    /**
     * 设置debug
     */
    public function setDebug($type)
    {
        if($type == true) {
            $this->debug = true;
        } else {
            $this->debug = false;
        }

        return $this->debug;
    }
}
?>