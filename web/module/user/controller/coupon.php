<?php
/**
 * alltosun.com 我的优惠券 coupon.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 申小宁 (shenxn@alltosun.com) $
 * $Date: 2016-4-7 下午5:03:19 $
 * $Id$
 */

class Action
{
    public function __call($action = '', $params = array())
    {
        $coupon_list =  $coupon_ids = array();

        $user_id = user_helper::get_user_id();

//         if (!$user_id) {
//             return '请登录后查看！';
//         }

        $coupon_record_list = _model('coupon_record')->getList(
                array('user_id' => $user_id),
                ' ORDER BY `id` DESC '
        );

        foreach ($coupon_record_list as $k => $v)
        {
            $coupon_info = _uri('coupon', $v['coupon_id']);
            $coupon_relation_info = _uri('coupon_relation', $v['coupon_relation_id']);

            $coupon_list[$k]['coupon_id'] = $v['coupon_id'];
            $coupon_list[$k]['coupon_relation_id'] = $v['coupon_relation_id'];
            $coupon_list[$k]['title'] = $coupon_info['title'];
            $coupon_list[$k]['use_address'] = $coupon_info['use_address'];
            $coupon_list[$k]['code'] = $coupon_relation_info['code'];
            $end_time = strtotime($coupon_info['end_time']);
            $coupon_list[$k]['end_time'] = date('Y-m-d', $end_time);
            $coupon_list[$k]['is_use'] = 1;

            if ($end_time < time()) {
                $coupon_list[$k]['is_use'] = 0;
            }
        }

        Response::assign('coupon_list', $coupon_list);
        Response::display('user_coupon_list.html');
    }
}