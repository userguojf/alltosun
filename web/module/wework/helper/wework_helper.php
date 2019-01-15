<?php
/**
 * alltosun.com  wework_helper.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-1-20 下午4:32:17 $
 * $Id$
 */
class wework_helper
{

    // OAuth2.0接入流程
    public static function check_wework_auth($return_url, $agent_id)
    {
        if ( !is_weixin() ) return '请微信登录或者企业微信APP';

        $url = AnUrl("wework/auth?return_url=".urlencode($return_url).'&agent_id='.$agent_id);
        self::redirect($url);
    }

    /**
     *  代码块
     *  重定向
     */
    public static function redirect($state)
    {
        Response::redirect($state);
        Response::flush();
        exit();
    }

    /**
     * 添加新的规则的判断 added by guojf
     * @param array $info
     * 代码块
     */
    public static function wework_user_login($info, $state, $agent_id)
    {

        if ( !isset($info['UserId']) || !$info['UserId'] ) return '成员信息不正确';

        //菜单统计需要知道是谁点击
        $_SESSION['wework_user_id'] = $info['UserId'];

        //根据唯一账号查询本地数据库
        $wework_user_info = _model('wework_user')->read(array('user_id' => $info['UserId']));

        // 如果本地未存储 取扩展字段
        if ( !$wework_user_info ) {
            // 本地创建并且返回登录账号（ 防止那种企业微信直接添加而后没有数据的用户  登录就给他添加本地后台  ）
            $wework_user_info['an_id'] = wework_user_helper::loacal_create($agent_id, $info['UserId']);
        }

        if ( !$wework_user_info['an_id'] ) {
            if ( strpos($info['UserId'], '_') ) {
                $wework_user_info['an_id'] = explode('_', $info['UserId'])[0];
            } else {
                self::redirect($state);
            }
        }

        $member_info  = _model('member')->read(array('member_user' => $wework_user_info['an_id']));

        if ( !$member_info ) self::redirect($state);

        member_helper::remember_me_set($member_info);
    }

}