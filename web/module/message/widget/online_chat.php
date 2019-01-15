<?php
/**
 * alltosun.com 在线聊天系统 online_chat.php
 * ============================================================================
 * 版权所有 (C) 2009-2014 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 石武浩 (shiwh@alltosun.com) $
 * $Date:  2014-7-30 上午9:51:18 $
 * $Id$
*/

class online_chat_widget
{
    private $user_id;

    public function __construct()
    {
        $this->user_id = user_helper::get_user_id();
    }

    /**
     * 调取IM
     */
    public function show()
    {
        $colleague_list = $customer_list = $friends_list = array();
        // 同事列表 没有分组
        $colleague_list = _widget('user.address_list')->get_address_list(array('type' => 1, 'per_page' => '100000'));

        // 组装客户列表
        // 分组列表
        $customer_group_list = _widget('user.address_list')->get_address_list_group(2);
        $colleague_list['默认分组'] =  _widget('user.address_list')->get_group_user_list(-4, 100000);
        foreach ($customer_group_list as $k => $v) {
            $customer_list[$v['title']] = _widget('user.address_list')->get_group_user_list($v['id'], 100000);
        }

        // 组装好友列表
        // 分组列表
        $friends_group_list = _widget('user.address_list')->get_address_list_group(3);

        $friends_list['互粉'] = _widget('user.address_list')->get_group_user_list(-1, 100000);
        $friends_list['关注'] = _widget('user.address_list')->get_group_user_list(-2, 100000);
        $friends_list['粉丝'] = _widget('user.address_list')->get_group_user_list(-3, 100000);
        foreach ($friends_group_list as $k => $v) {
            $friends_list[$v['title']] = _widget('user.address_list')->get_group_user_list($v['id'], 100000);
        }

        Response::assign('colleague_list', $colleague_list);
        Response::assign('customer_list', $customer_list);
        Response::assign('friends_list', $friends_list);
    }
}