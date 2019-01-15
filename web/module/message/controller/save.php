<?php
/**
 * alltosun.com  save.php
 * ============================================================================
 * 版权所有 (C) 2009-2014 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 石武浩 (shiwh@alltosun.com) $
 * $Date:  2014-7-26 下午12:00:35 $
 * $Id$
*/

class Action
{
    private $user_id;

    public function __construct()
    {
        $this->user_id = user_helper::get_user_id();

        if (!$this->user_id) {
            if (!Request::isAjax()) {
                throw new AnMessageException('对不起，请登录！', 'permission', 'user/login');
            }
        }
    }

    /**
     * 保存
     * @param unknown $action
     * @param unknown $params
     */
    public function __call($action, $params = array())
    {
        return _widget('message')->create();
    }
}