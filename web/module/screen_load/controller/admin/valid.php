<?php
/**
 * alltosun.com  valid.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-3-3 下午2:58:24 $
 * $Id$
 */
class Action
{
    /**
     * 1、有效门店数：至少有一台设备在一个月内累计活跃15天，即算该门店活跃，需提取该类门店总和
     * 有效门店数提取时间范围：1、2017/9/1-2018/1/31 和2017/9/1-2018/2/28
     */
    public function index()
    {
        $arr = [];

        $arr[1]['start_date'] = 20170901;
        $arr[1]['end_date']   = 20170930;

        $arr[2]['start_date'] = 20171001;
        $arr[2]['end_date']   = 20171031;

        $arr[3]['start_date'] = 20171101;
        $arr[3]['end_date']   = 20171130;

        $arr[4]['start_date'] = 20171201;
        $arr[4]['end_date']   = 20171231;

        $arr[5]['start_date'] = 20180101;
        $arr[5]['end_date']   = 20180131;

        $arr[6]['start_date'] = 20180201;
        $arr[6]['end_date']   = 20180228;

        $active_yyt['2017/9/1-2018/1/31'] = [];
        $active_yyt['2017/9/1-2018/2/28'] = [];

        echo '<left>
                数据需求：<br>

共提取3个字段，分别为有效门店数、终端月活跃率、有效终端数
<br>
1、有效门店数：至少有一台设备在一个月内累计活跃15天，即算该门店活跃，需提取该类门店总和
有效门店数提取时间范围：1、2017/9/1-2018/1/31 和2017/9/1-2018/2/28
<br>
2、终端月活跃率：当月累计活跃15天以上（含15天）的设备/总有效终端数
终端月活跃率提取时间范围：1、2018/1/1-2018/1/31  和  2018/2/1-2018/2/28
<br>
3、有效终端数：安装设备数-下柜设备（本次下柜设备暂定为0）
有效终端数提取时间范围：1、2017/9/1-2018/1/31 和2017/9/1-2018/2/28

                </left>';

        echo '<br><br><center><b>数据如下</b></center>';
        for ( $i = 1; $i <= 6; $i ++  ) {

            $sql  = " SELECT device_unique_id, business_id ,COUNT(*) AS num FROM `screen_device_online_stat_day`";
            $sql .= " WHERE day >= {$arr[$i]['start_date']} AND day <= {$arr[$i]['end_date']}";
            $sql .= " GROUP BY device_unique_id ORDER BY `num` DESC";

            $device_active_list = _model('screen_device_online_stat_day')->getAll($sql);

            $month_active_num = 0;

            foreach ($device_active_list as $k => $v) {

                if ( 15 > $v['num'] ) continue;


                // 设备总数 start
                ++ $month_active_num;

                // 设备总数 end

                // 有效营业厅 start
                if ( $i < 6 && !in_array($v['business_id'], $active_yyt['2017/9/1-2018/1/31'])) {
                    array_push($active_yyt['2017/9/1-2018/1/31'], $v['business_id']);
                }

                if ( !in_array($v['business_id'], $active_yyt['2017/9/1-2018/2/28']) ) {
                     array_push($active_yyt['2017/9/1-2018/2/28'], $v['business_id']);
                }
                // 有效营业厅 end
            }

            $month_total = _model('screen_device')->getTotal(
                    array(
                            'day <=' => $arr[$i]['end_date'],
                            'status' => 1
                    )
            );
            echo '<hr>';
            echo "<b>{$arr[$i]['start_date']}—{$arr[$i]['end_date']}终端月活跃率</b>";
            p("当月累计活跃15天以上（含15天）的设备数：".$month_active_num);
            P("总有效终端数：".$month_total);

            $percent = number_format($month_active_num / $month_total, 2) * 100;
            p("月活跃率：" . $percent .'%');
        }

        $active_yyt['2017/9/1-2018/1/31'] = count($active_yyt['2017/9/1-2018/1/31']);
        $active_yyt['2017/9/1-2018/2/28'] = count($active_yyt['2017/9/1-2018/2/28']);

        echo '<hr>';
        echo '<b>有效门店数</b>';
        p('2017/9/1-2018/1/31有效门店数：'.$active_yyt['2017/9/1-2018/1/31']);

        p('2017/9/1-2018/2/28有效门店数：'.$active_yyt['2017/9/1-2018/2/28']);

        echo '<hr>';
        echo '<b>有效终端数</b>';
        $device_1_total = _model('screen_device')->getTotal(
                array('day >=' =>20170901, 'day <=' => 20180131, 'status' => 1 ));

        $device_2_total = _model('screen_device')->getTotal(
                array('day >=' =>20170901, 'day <=' => 20180228, 'status' => 1 ));
        p('2017/9/1-2018/1/31有效终端数：'.$device_1_total);
        
        p('2017/9/1-2018/2/28有效终端数：'.$device_2_total);

    }
    public function version()
    {
        $arr = [];
    
        $arr[1]['start_date'] = 20170901;
        $arr[1]['end_date']   = 20170930;
    
        $arr[2]['start_date'] = 20171001;
        $arr[2]['end_date']   = 20171031;
    
        $arr[3]['start_date'] = 20171101;
        $arr[3]['end_date']   = 20171130;
    
        $arr[4]['start_date'] = 20171201;
        $arr[4]['end_date']   = 20171231;
    
        $arr[5]['start_date'] = 20180101;
        $arr[5]['end_date']   = 20180131;
    
        $arr[6]['start_date'] = 20180201;
        $arr[6]['end_date']   = 20180228;
    
        $device_num['2017/9/1-2018/1/31'] = 0;
        $device_num['2017/9/1-2018/2/28'] = 0;
    
    
        for ( $i = 1; $i <= 6; $i ++  ) {
    
            $sql  = " SELECT device_unique_id, COUNT(*) AS num FROM `screen_device_online_stat_day`";
            $sql .= " WHERE day >= {$arr[$i]['start_date']} AND day <= {$arr[$i]['end_date']}";
            $sql .= " GROUP BY device_unique_id ORDER BY `num` DESC";
            p($sql);
            $device_active_list = _model('screen_device_online_stat_day')->getAll($sql);
    
            foreach ($device_active_list as $k => $v) {
    
                if ( 15 > $v['num'] ) continue;
    
                if ( $i < 6 ) {
                    ++ $device_num['2017/9/1-2018/1/31'];
                }
    
                ++ $device_num['2017/9/1-2018/2/28'];
            }
            p($device_num['2017/9/1-2018/1/31']);
            p($device_num['2017/9/1-2018/2/28']);
        }
    
        p('2017/9/1-2018/1/31有效门店数:'.$device_num['2017/9/1-2018/1/31']);
    
        p('2017/9/1-2018/2/28有效门店数:'.$device_num['2017/9/1-2018/2/28']);
    
    }
}