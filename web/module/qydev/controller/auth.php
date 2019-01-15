<?php
/**
 * alltosun.com  auth.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-4-9 上午11:11:16 $
 * $Id$
 */
class Action
{
    private $appid          = '';
    private $redirect_uri   = '';
    private $response_type  = 'code';
    private $scope          = 'snsapi_base';
    private $state          = '';
    private $site_url       = '';

    public function __construct()
    {
        $this->appid    = qydev_config::$corp_id;
        $this->site_url = SITE_URL;
    }

    public function __call($action = '', $params = array())
    {
        $return_url = urldecode(tools_helper::get('return_url', ''));
        if (!$return_url) $return_url = $this->site_url;

        //授权
        $url  = qydev_config::$auth_url.'appid='.$this->appid;
        $url .= '&redirect_uri='.urlencode($this->site_url.'/qydev/auth/callback');
        $url .= "&response_type=code&scope=snsapi_base&state=".$return_url ."#wechat_redirect";

        qydev_helper::redirect($url);
    }

    public function callback()
    {
        $code  = tools_helper::get('code', '');
        $state = tools_helper::get('state', '');
// var_dump($state);
// exit();
        if ( !$code ) qydev_helper::redirect($state);

        $access_token = _widget('qydev.token')->get_access_token('work');

        if ( !$access_token ) qydev_helper::redirect($state);

        $url = qydev_config::$get_user_id_url.'access_token='.$access_token.'&code='.$code;

        $info = json_decode(curl_get($url), true);

        if ( !isset($info['UserId']) || !$info['UserId']) qydev_helper::redirect($state);

        //登录
        $result = qydev_helper::qydev_user_login($info);
 
        qydev_helper::redirect($state);
    }
}
