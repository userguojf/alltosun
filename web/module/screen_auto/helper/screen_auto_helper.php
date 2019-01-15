<?php
/**
 * alltosun.com  screen_auto_helper.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-3-1 上午11:20:52 $
 * $Id$
 */

class screen_auto_helper
{
    /**
     * 获取昵称方法  没有通过返回最初型号
     * @param unknown $phone_name
     * @param unknown $phone_version
     * @return boolean|multitype:unknown |multitype:unknown Ambigous <>
     */
    public static function get_device_nikename($phone_name, $phone_version)
    {
        if ( !$phone_name || !$phone_version )  return false;

        $nickname_data = array(
                'name_nickname'    => $phone_name,
                'version_nickname' => $phone_version,
        );

        $filter = array('phone_name' => $phone_name, 'phone_version' => $phone_version);

        $nickname_info  = _model('screen_device_nickname')->read($filter);

        if ( !$nickname_info ) return $nickname_data;

        //昵称审核通过后重新赋值
        if ( 1 == $nick_info['status'] ) {
            $nickname_data['name_nickname']    = $nickname_info['name_nickname'];
            $nickname_data['version_nickname'] = $nickname_info['version_nickname'];
        }

        return $nickname_data;
    }

    public static function get_status_data($business_hall_id)
    {
        if ( !$business_hall_id ) return false;

        $data = [
                'normal'   => 0,
                'abnormal' => 0
        ];

        $list = _model('screen_auto_start')->getList(array('status' => 1));

        if ( !$llist  ) return $data;

        foreach ( $list as $k => $v ) {
//             if (  ) {
                
//             }
        }

        
    }
}