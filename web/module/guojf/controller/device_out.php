<?php
/**
 * alltosun.com  myself test index.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-11-4 上午11:49:40 $
 * $Id$
 */
class Action
{

    public  function index()
    {
        exit();
        $list = _model('screen_device')->getList(array('business_id' => 110375));

        foreach ($list as $k => $v) {
//             p($v);
            $info  = array(
                    'registration_id'  => $v['registration_id'],
                    'device_unique_id' => $v['device_unique_id'],
                    'shoppe_id'        => $v['shoppe_id'],
                    'province_id'      => $v['province_id'],
                    'city_id'          => $v['city_id'],
                    'area_id'          => $v['area_id'],
                    'business_id'      => 46435,
                    'version_no'       => $v['version_no'],
                    'imei'             => $v['imei'],
                    'mac'              => $v['mac'],
                    'phone_name'       => $v['phone_name'],
                    'phone_version'    => $v['phone_version'],
                    'day'              => date("Ymd"),
                    'phone_name_nickname'    => $v['phone_name_nickname'],
                    'phone_version_nickname' => $v['phone_version_nickname'],
                    'device_nickname_id'     => $v['device_nickname_id'],
            );
            $result = screen_helper::add_screen_device($info);
            p($result);
        }
    }
}