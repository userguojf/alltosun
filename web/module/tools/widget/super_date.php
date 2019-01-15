<?php
/**
 * alltosun.com  super_date.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王德康 (wangdk@alltosun.com) $
 * $Date: 2015-9-8 下午10:53:14 $
 * $Id$
 */
class super_date_widget
{
    /**
     * 按照时间返回对应的日，周，月，统计用
     * @param unknown_type $type
     * @param unknown_type $time
     * @return string
     */
    public function get_date($type, $time = '')
    {
        if (!$time)  $time = time();

        if ($type == 'day') {
            $date = date('Y-m-d', $time);
        } else if ($type == 'week') {
            // 第几周
            $date = strftime('%Y%W', $time);
        } else if ($type == 'month') {
            $date = date('Ym', $time);
        }
        return $date;
    }
}
?>