<?php
/**
 * alltosun.com  clear.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王德康 (wangdk@alltosun.com) $
 * $Date: 2014-6-10 上午10:58:05 $
 * $Id$
 */

class Action
{
    /**
     * 清理数据
     * @param unknown_type $action
     * @param unknown_type $params
     */
    public function __call($action = '', $params = array())
    {
        var_dump(_widget('tools.ip')->get_client_city());
    }
    
    public function clear_app_stat()
    {
        $day   = 'TRUNCATE screen_app_stat_day';
        $week  = 'TRUNCATE screen_app_stat_week';
        $month = 'TRUNCATE screen_app_stat_month';
        $year  = 'TRUNCATE screen_app_stat_year';
        
        _model("screen_app_stat_day")->getAll($day);
        _model("screen_app_stat_week")->getAll($week);
        _model("screen_app_stat_month")->getAll($month);
        _model("screen_app_stat_year")->getAll($year);
        
        echo "ok";
    }
}
?>