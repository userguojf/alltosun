<?php
/**
 * alltosun.com message index.php
 * ============================================================================
 * 版权所有 (C) 2009-2014 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 石武浩 (shiwh@alltosun.com) $
 * $Date:  2014-6-30 下午12:54:07 $
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
     * 删除
     */
    public function __call($action, $params = array())
    {
        $message_id = (int)$action;

        if (!$message_id) {
            return '请选择您要删除的信息';
        }

        $filter = array(
            'message_id' => $message_id,
            'to_user_id' => $this->user_id
        );
        $message_info        = _model('message')->read($message_id);
        $message_member_info = _model('message_member')->read($filter);
        if (!$message_info || !$message_member_info) {
            return '您要删除的消息不存在';
        }

        _model('message_member')->update($filter, array('status' => 0));

        return 'ok';
    }


}