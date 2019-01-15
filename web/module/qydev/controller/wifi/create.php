<?php
/**
 * alltosun.com  create.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-3-29 下午7:53:51 $
 * $Id$
 */
class Action
{
    public function __call($action = '', $param = array())
    {

        api_helper::check_token('post');

        $info = tools_helper::post('info', '');


        if ( !$info ) {
            api_helper::return_data(1, '参数不全', array());
        }

        _model('public_contact_user')->create(
            json_decode(htmlspecialchars_decode($info), true)
        );

        api_helper::return_data(0, '', array());
    }
}