<?php
/**
 * alltosun.com  qydev_user_helper.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-4-9 下午12:23:15 $
 * $Id$
 */

class qydev_news_helper
{

    public static function content_zan($res_id, $type)
    {
        $member_id = member_helper::get_member_id();
        $member_info = member_helper::get_member_info($member_id);

        if ( !$res_id || !in_array($type, array(2, 3)) ) return array();

        $member_id = member_helper::get_member_id();
        $member_info = member_helper::get_member_info($member_id);

        $info = _uri('qydev_news_content_zan_record', 
            array(
                  'res_id'      => $res_id,
                  'user_number' => $member_info ? $member_info['member_user'] : '',
                  'unique_id'   => isset($_SESSION['qydev_user_id']) && $_SESSION['qydev_user_id'] ? $_SESSION['qydev_user_id'] : '',
                  'type'        => 2,
             )
        );

        return $info;
    }

}