<?php

/**
 * alltosun.com 个人中心 index.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: Shenxn 申小宁 (shenxn@alltosun.com) $
 * $Date: Jul 25, 2014 3:15:29 PM $
 * $Id$
 */

class Action
{
    private $user_id = 0;

    public function __construct()
    {
        $this->user_id = user_helper::get_user_id();
    }

    public function __call($action = '', $params = array())
    {
        if (!$this->user_id) {
            Response::redirect(AnUrl('user/login'));
            Response::flush();
            return;
        }

        $user_phone = user_helper::get_user_phone();
        var_dump($user_phone);exit;

        // 用户领取优惠券的记录
        $params = array(
            'per_page' => 30,
            'source'   => 2
         );

        $record = _widget('coupon')->get_user_coupon($params);

        // 根据coupon_id查询coupon的信息
        if (isset($record['list']) && $record['list']) {
            $coupon_list = array();

            foreach ($record['list'] as $k=>$v) {
                $info            = array();
                $info            = $v;
                $info['title']   = coupon_helper::get_coupon_field($v['coupon_id'],'title');
                $info['code']    = coupon_helper::get_coupon_code($v['coupon_id']);
                $coupon_list[$k] = $info;
            }

            Response::assign('user_phone', $user_phone);
            Response::assign('coupon_list', $coupon_list);
        }

        Response::display('center.html');
    }
}