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
    public function __call($action = '', $param = array())
    {
//         $search_filter = Request::get('search_filter', array());

        $start_time  = Request::Get('start_time', date('Y-m-d',time() - 7*24*3600));
        $end_time    = Request::Get('end_time', date('Y-m-d'));

        $filter = $list = array();
        //搜索
        $start_date = date('Ymd', strtotime($start_time) );
        $filter['start_date'] = $start_date;

        $end_date = date('Ymd', strtotime($end_time) );
        $filter['end_date'] = $end_date;

        if ( $start_date > $end_date) return '开始时间不能大于结束时间';

        $sql  = " SELECT device_unique_id, province_id, city_id, area_id, business_id ,COUNT(*) AS num";
        $sql .= " FROM `screen_device_online_stat_day`";
        $sql .= " WHERE day >= {$filter['start_date']} AND day <= {$filter['end_date']}";
        $sql .= " GROUP BY device_unique_id ORDER BY `num` DESC";
// p($sql);exit();
        $device_active_list = _model('screen_device_online_stat_day')->getAll($sql);

        $active_num = 0;

        $list = $yyt_ids = [];

        foreach ($device_active_list as $k => $v) {

            if ( 15 > $v['num'] ) {
                // 引用
                // unset($device_active_list[$k]);
                continue;
            }

            if ( !in_array($v['business_id'], $yyt_ids) ) {
                array_push($yyt_ids, $v['business_id']);
            }

            // 设备总数 start
            ++ $active_num;
        }

        $month_total = _model('screen_device')->getTotal(
                array(
                        'day <=' => $filter['end_date'],
                        'status' => 1
                )
        );

        $list['active_device_total'] = $active_num;
        $list['device_total'] = $month_total;
        $list['active_yyt']   = count($yyt_ids);
        $list['percent'] = number_format($list['active_device_total'] / $list['device_total'], 2) * 100 . '%';

        Response::assign('list', $list);
        Response::assign('start_time', $start_time);
        Response::assign('end_time', $end_time);
        Response::display('admin/valid_list.html');
    }

    public function detail()
    {

        $start_date = 20180201;
        $end_date   = 20180228;

        $sql  = " SELECT device_unique_id, province_id, city_id, area_id, business_id ,COUNT(*) AS num";
        $sql .= " FROM `screen_device_online_stat_day`";
        $sql .= " WHERE day >={$start_date}  AND day <= {$end_date}";
        $sql .= " GROUP BY device_unique_id ORDER BY `num` DESC";

        $device_active_list_2 = _model('screen_device_online_stat_day')->getAll($sql);

        $start_date = 20180301;
        $end_date   = 20180331;

        $sql  = " SELECT device_unique_id, province_id, city_id, area_id, business_id ,COUNT(*) AS num";
        $sql .= " FROM `screen_device_online_stat_day`";
        $sql .= " WHERE day >={$start_date}  AND day <= {$end_date}";
        $sql .= " GROUP BY device_unique_id ORDER BY `num` DESC";

        $device_active_list_3 = _model('screen_device_online_stat_day')->getAll($sql);

        $active_num = 0;

        $list = $yyt_2_ids = $yyt_3_ids = $list_2 = $list_3 = [];

        foreach ($device_active_list_2 as $k => $v) {
            if ( 15 > $v['num'] ) continue;

            if ( !in_array($v['business_id'], $yyt_2_ids) ) {
                array_push($yyt_2_ids, $v['business_id']);

                if ( !isset($list_2[$v['province_id']]) ) {
                    $list_2[$v['province_id']] = 1;
                } else {
                    ++ $list_2[$v['province_id']];
                }
            }
        }

        foreach ($device_active_list_3 as $k => $v) {
            if ( 15 > $v['num'] ) continue;

            if ( !in_array($v['business_id'], $yyt_3_ids) ) {
                array_push($yyt_3_ids, $v['business_id']);

                if ( !isset($list_3[$v['province_id']]) ) {
                    $list_3[$v['province_id']] = 1;
                } else {
                    ++ $list_3[$v['province_id']];
                }
            }
        }

        $data = [];

        for ( $i = 1; $i < 35; $i ++ ) {
            $month_2 = isset($list_2[$i]) ? $list_2[$i] : 0;
            $month_3 = isset($list_3[$i]) ? $list_3[$i]: 0;

            if ( !$month_2 && !$month_3 ) continue;

            $data[$i]['month_2'] = $month_2;
            $data[$i]['month_3'] = $month_3;
        }
//         p($data);
//         $i = 0;
        foreach ($data as $k => $v) {
            $list[$i]['province'] = screen_helper::by_id_get_field($k, 'province', 'name');
            $list[$i]['Feb']      = $v['month_2'];
            $list[$i]['Mar']      = $v['month_3'];

            ++ $i;
        }

        $head = array( '省', '2月份', '3月');

        $params['filename'] = '省活跃门店数';

        $params['data']     = $list;
        $params['head']     = $head;

        Csv::getCvsObj($params)->export();   
    }

}