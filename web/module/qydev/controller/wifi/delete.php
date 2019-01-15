<?php
/**
 * alltosun.com  delete.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-3-8 下午6:08:49 $
 * $Id$
 */
class Action
{
    public function __call($action = '', $param = array())
    {

        api_helper::check_token('post');

        $user_phone = tools_helper::post('user_phone', 0);
        $id         = tools_helper::post('id', '');


        if ( !$user_phone || !$id ) {
            api_helper::return_data(1, '参数不全', array());
        }

        _model('public_contact_user')->delete(
            array('id' => $id, 'user_phone' => $user_phone),
            " LIMIT 1 "
        );

        api_helper::return_data(0, '', array());
    }
    
    public function small()
    {
        _model('public_contact_user')->delete(
        array( 'user_phone' => 18813044687),
        " LIMIT 1 "
                );
    }
}