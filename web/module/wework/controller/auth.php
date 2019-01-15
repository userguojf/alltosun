<?php
/**
 * alltosun.com 企业微信授权 auth.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-3-1 下午4:56:12 $
 * $Id$
 */
class Action
{
    private $agent_id       = '';
    private $appid          = '';
    private $redirect_uri   = '';
    private $response_type  = 'code';
    private $scope_type     = 'snsapi_base';
    private $state          = '';
    private $site_url       = '';

    public function __construct()
    {
        $this->appid    = wework_config::$wework_corpid;

        $this->site_url = SITE_URL;
    }

    public function __call($action = '', $params = array())
    {
        $return_url     = tools_helper::get('return_url', '');
        $this->agent_id = tools_helper::get('agent_id', 0);

        $_SESSION['wework_agent_id'] = $this->agent_id;

        if ( !$return_url ) $return_url = $this->site_url;

        //授权地址拼接
        $url  = wework_config::$auth_url . 'appid=' . $this->appid;
        $url .= '&redirect_uri=' .urlencode($this->site_url.'/wework/auth/callback');
        $url .= '&response_type=code&scope='. $this->scope_type;
        $url .= '&state='. urlencode($return_url) .'#wechat_redirect';

        wework_helper::redirect($url);
    }


    public function callback()
    {
        $code  = tools_helper::get('code', '');
        $state = tools_helper::get('state', '');

        if ( !$code ) wework_helper::redirect($state);

        if ( !isset($_SESSION['wework_agent_id']) || !$_SESSION['wework_agent_id'] ) {
            wework_helper::redirect($state);
        }

        // token必要参数
        $this->agent_id = $_SESSION['wework_agent_id'];

        $access_token = _widget('wework.token')->get_access_token($this->agent_id);

        if ( !$access_token ) wework_helper::redirect($state);

        $url = wework_config::$get_user_id_url.'access_token='.$access_token.'&code='.$code;

        $info = json_decode(curl_get($url), true);

        if ( !isset($info['errcode']) || $info['errcode'] ) wework_helper::redirect($state);

        // 登录
        wework_helper::wework_user_login($info, $state, $this->agent_id);

        wework_helper::redirect($state);
    }
}
