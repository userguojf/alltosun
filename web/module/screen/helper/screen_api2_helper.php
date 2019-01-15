<?php
/**
 * alltosun.com  screen_api2_helper.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 申小宁 (shenxn@alltosun.com) $
 * $Date: 2017-6-28 下午2:57:48 $
 * $Id$
 */

class screen_api2_helper
{
        //父级方法，处理基础数据
        public static function get_screen_device_info($filter = array(), $order = ' ORDER BY `id` DESC ')
        {
            !isset($filter['status']) ? $filter['status'] = 1 : '';
            return _model('screen_device')->read($filter , $order);
        }

        //子方法派生
        public static function get_screen_info_by_device_unique_id($device_unique_id)
        {
            if (!$device_unique_id) {
                return false;
            }

            return self::get_screen_device_info(['device_unique_id' => $device_unique_id]);
        }
}
?>