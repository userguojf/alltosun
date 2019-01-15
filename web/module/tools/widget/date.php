<?php
/**
 * alltosun.com  date.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王德康 (wangdk@alltosun.com) $
 * $Date: 2014-6-17 下午5:12:05 $
 * $Id$
 */
class date_widget {

    /**
     * 倒计时
     * @param $end_time
     */
    public function time_count_down($end_time) {
        $time = strtotime($end_time) - time();
        if ($time > 0) {

            $day  = floor($time / (60 * 60 * 24));
            $hour =  floor(($time % (60 * 60 * 24)) / 3600);
            $min  = floor($time % (60 * 60) / 60);

            $day  = $day  < 10 ? '0'.$day  : $day;
            $hour = $hour < 10 ? '0'.$hour : $hour;
            $min  = $min  < 10 ? '0'.$min  : $min;

            return "{$day}天{$hour}时{$min}分";
        } else {
            return '00天00时00分';
        }
    }

    /**
     * 显示距离当前时间的字符串
     * @param $time int 时间戳
     * @return string
     * @author gaojj@alltosun.com
     */
    function time_past($time)
    {
        $now        = time();
        $time_past  = $now - strtotime($time);

        // 如果小于1分钟（60s），则显示"刚刚"
        if ($time_past < 60) {
            return '1分钟前';
        }

        $time_mapping = array(
                '分钟' => '60',
                '小时' => '24',
                '天'   => '7',
                '周'   => '4',
                '月'   => '12',
                '年'   => '100'
        );

        $time_past = floor($time_past/60);

        foreach($time_mapping as $k=>$v) {
            if ($time_past < $v) return floor($time_past).$k.'前';
            $time_past = $time_past/$v;
        }

        // 如果小于1小时（60*60s），则显示N分钟前
        // 如果小于24个小时（60*60*24s），则显示N小时前
        // 如果大于24个小时（60*60*24s），则显示N天前
    }


    /**
     * 根据类型返回日，月，周
     * @param unknown_type $time
     * @return string
     */
    public static function get_date($time)
    {
        if ($time == 'day') {
            $date = date('Ymd');

        } else if ($time == 'week') {
            $date =  strftime("%Y%W", time());

        } else if ($time == 'month') {
            $date = date('Ym');
        }

        return $date;
    }
}

?>