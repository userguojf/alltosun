<?php
/**
 * alltosun.com  dm.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-12-13 下午7:17:11 $
 * $Id$
 */
class Action
{
    public function __call($action = '', $param = array()) 
    {
        $account      = tools_helper::get('user_number', '1101051002033');
        $redirect_url = tools_helper::get('redirect_url', 'screen_dm');

        $app_id  = 'wifi_dxawifi_j29sod9dawfe29d2';
        $app_key = '83136817debff9b6ab2e5b0269695137';
        $action  = 'redirect_uri';
        $redirect_uri = 'http://mac.pzclub.cn/screen_dm/device';
//         $redirect_uri = AnUrl($redirect_url);

//         $url  = AnUrl('api/member/login');
        $url = 'http://mac.pzclub.cn/api/member/login';
        $url .= '?timestamp='.time();
        $url .= '&token='.md5($app_id.'_'.$app_key.'_'.time());
        $url .= '&account='.$account;
        $url .= '&appid='.$app_id;
        $url .= '&action='.$action;
        $url .= '&redirect_uri='.urlencode($redirect_uri);

//         $url .= '&device_code';
//         $url .= '&is_mobile='.$is_mobile;
echo $url;
    }
}