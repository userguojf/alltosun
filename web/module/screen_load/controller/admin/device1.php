<?php
/**
 * alltosun.com  ting_device.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-6-19 下午5:34:11 $
 * $Id$

规则：
亮屏覆盖门店数：指有终端安装亮屏的门店总数
安装手机数≥5门店数：指安装终端数量大于或等于5个的门店数
安装手机总数：指该省所有门店安装终端的总数
设备在线数量：指搜索时间段内 有过在线行为的终端的总数

 */
class Action
{
    public function count()
    {
        $day = Request::Get('end_time', date('Ymd'));
        $count = _model('screen_device')->getTotal(array('day <=' => $day, 'status' => 1));
        p('截止到'.$day.'设备总量是：'.$count);
    }

    
    public function index()
    {
        set_time_limit(0);

        $start_time = Request::Get('start_time', date('Ymd',time() - 30*24*3600));
        $end_time   = Request::Get('end_time', date('Ymd'));
        $standard   = Request::Get('standard', 3);

        $list = $yyt = [];

        $sql  = " SELECT device_unique_id, business_id ,COUNT(*) AS num FROM `screen_device_online_stat_day`";
        $sql .= " WHERE day >= {$start_time} AND day <= {$end_time}";
        $sql .= " GROUP BY device_unique_id ORDER BY `num` DESC";

        $device_active_list = _model('screen_device_online_stat_day')->getAll($sql);

        $device_num = 0;

        foreach ($device_active_list as $k => $v) {

            if ( $v['num'] < $standard ) continue;

            // 设备总数 start
            ++ $device_num;
            // 营业厅ID
            if ( !in_array($v['business_id'], $yyt) ) {
                array_push($yyt, $v['business_id']);
            }
        }

        p($start_time.'——'.$end_time.'活跃'.$standard.'天以上（含'.$standard.'天）的总数统计如下：');
        p('设备总数：'.$device_num);
        P('有效门店数：'. count($yyt));
        //SELECT device_unique_id,count(*) FROM `screen_device_online_stat_day` WHERE day >=20180701 AND day <=20180731 GROUP BY device_unique_id ORDER BY count(*) DESC
        
    }

}