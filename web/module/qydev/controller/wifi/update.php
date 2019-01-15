<?php
/**
 * alltosun.com  update.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-2-1 下午6:52:32 $
 * $Id$
 */
class Action
{
    public function __call($action = '', $param = array())
    {

        api_helper::check_token('post');

        $user_phone = tools_helper::post('user_phone', 0);
        $info       = tools_helper::post('info', '');


        if ( !$user_phone || !$info ) {
            api_helper::return_data(1, '参数不全', array());
        }

        _model('public_contact_user')->update(
            array('user_phone' => $user_phone),
            json_decode(htmlspecialchars_decode($info), true)
        );

        api_helper::return_data(0, '', array());
    }
}