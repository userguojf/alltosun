<?php
/**
  * alltosun.com 数据中心widget data_center.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年12月19日 下午2:59:40 $
  * $Id$
  */
class data_center_widget
{
    /**
     * 获取设备留存量按日期段统计数据 （表3）
     * @param unknown $params
     */
    public function get_screen_keep_date_stat($params)
    {

        $date_type = array(
                '1' => '1-3天', '2' => '1-3天', '3' => '1-3天',
                '4' => '4-7天', '5' => '4-7天', '6' => '4-7天', '7' => '4-7天',
                '8' => '8-14天', '9' => '8-14天', '10' => '8-14天', '11' => '8-14天', '12' => '8-14天', '13' => '8-14天', '14' => '8-14天',
                '15' => '15-21天', '16' => '15-21天', '17' => '15-21天', '18' => '15-21天', '19' => '15-21天', '20' => '15-21天', '21' => '15-21天',
                '22' => '22-28天', '23' => '22-28天', '24' => '22-28天', '25' => '22-28天', '26' => '22-28天', '27' => '22-28天', '28' => '22-28天',
                '29' => '29-35天', '30' => '29-35天', '31' => '29-35天', '32' => '29-35天', '33' => '29-35天', '34' => '29-35天', '35' => '29-35天',
                '36' => '36-42天', '37' => '36-42天', '38' => '36-42天', '39' => '36-42天', '40' => '36-42天', '41' => '36-42天', '42' => '36-42天',
                '43' => '43-49天', '44' => '43-49天', '45' => '43-49天', '46' => '43-49天', '47' => '43-49天', '48' => '43-49天', '49' => '43-49天',
                '50' => '50-56天', '51' => '50-56天', '52' => '50-56天', '53' => '50-56天', '54' => '50-56天', '55' => '50-56天', '56' => '50-56天',
                '57' => '57-63天', '58' => '57-63天', '59' => '57-63天', '60' => '57-63天', '61' => '57-63天', '62' => '57-63天', '63' => '57-63天',
                '64' => '64-70天', '65' => '64-70天', '66' => '64-70天', '67' => '64-70天', '68' => '64-70天', '69' => '64-70天', '70' => '64-70天',
                '71' => '70天以上'
        );

        $wangjf_debug = tools_helper::Get('wangjf_debug', 0);

        if ( isset($params['end_time']) && $params['end_time']) {
            $filter['day <='] = date('Ymd', strtotime($params['end_time']));
        }

        if (isset($params['start_time']) && $params['start_time']) {
            $filter['day >='] = date('Ymd', strtotime($params['start_time']));
        }

        $where = screen_stat_helper::to_where_sql($filter);

        //因为存在换厅的情况， 本该根据设备、天分组，为了处理换厅之前的设备，加上按照营业厅分组
        $sql = " SELECT *, count(*) as day_count FROM `screen_device_online_stat_day` {$where} GROUP BY device_unique_id";

        $tmp_list = _model('screen_device_online_stat_day')->getAll($sql);

        if ($wangjf_debug) {
            p(count($tmp_list));
        }

        $list = array();
        $tmp  = array();
        $online_list = array();

        if (!$wangjf_debug) {
            //去除换厅的设备
//             foreach ($tmp_list as $k => $v) {
//                 $online_list[$v['device_unique_id']] = $v;
//             }
        }

        if ($wangjf_debug) {
            p(count($tmp_list));
        }

        foreach ($tmp_list as $k => $v) {

            if (!$v['day_count']) {
                continue;
            }

            if ($v['day_count'] > 70) {
                $key = $date_type['71'];
            } else {
                $key = $date_type[$v['day_count']];
            }
            //拼接数据
            if (empty($list[$key])) {
                $list[$key] = array(
                    'value'     => 1,
                    'devices'   => array($v['device_unique_id'])  //详情页所需
                );
            } else {
                $list[$key]['value'] += 1;
                $list[$key]['devices'][] = $v['device_unique_id'];
            }

        }

        return $list;
    }

    /**
     * 获取设备周活跃与安装统计数据
     * @param unknown $params
     */
    public function get_screen_device_week_stat($params)
    {

        if ( isset($params['end_time']) && $params['end_time']) {
            $end_day = date('Ymd', strtotime($params['end_time']));
        } else {
            $end_day = date('Ymd');
        }

        if (isset($params['start_time']) && $params['start_time']) {
            $start_day = date('Ymd', strtotime($params['start_time']));
        } else {
            $start_day = date('Ymd');
        }

        //获取到结束日期的所有周
        $start_week = date('YW', strtotime($start_day));
        $end_week   = date('YW', strtotime($end_day));
        $weeks      = array();
        $tmp_list   = array();
        $days       = array();

        //获取开始日期最后一天
        $end_year_day = date('Y-12-31', strtotime($start_day));
        //获取开始日期年的最后一周
        $end_year_week = date('YW', strtotime($end_year_day.' 00:00:00'));

        //最后一周假如等于01， 则为跨年周， 跨年周计算上一周的时间为最后一周
        if ($end_year_week ==  date('Y', strtotime($end_year_day.' 00:00:00')).'01') {
            $end_year_week = date('YW', strtotime($end_year_day.' 00:00:00')-7*3600*24);
        }

        //初始化
        do {
            $weeks[] = $start_week;
            $tmp_list[$start_week]    = array(
                    'install_num'   => 0, //周安装量
                    'active_num'    => 0, //周活跃量
                    'active_rate'   => 0, //周活跃率
                    'average_active' => 0 //周平均活跃天数
            );
            //最后一周
            if ($end_year_week == $start_week) {
                $start_week = date('YW', strtotime($end_year_day)+3600*24);

            } else {
                $start_week ++;
            }

        } while ($start_week <= $end_week);
        //数据组装处理
        foreach ( $tmp_list as $k => $v ) {
            //获取本周的开始和结束时间
            $year = substr($k, 0, 4);
            $week = (int)(str_replace($year, '', $k));
            $time_info = screen_stat_helper::get_day_by_week($year, $week);

            //开始周（第一周）兼容
            if ($k == $weeks[0]) {
                //避免搜索开始日期和开始周的开始日期不符
                $start = $start_day;
            } else {
                $start   = date('Ymd', $time_info['start']);
            }

            //结束周兼容
            if ($k == $weeks[count($weeks) -1]) {
                //避免搜索结束日期和开始周的开始日期不符
                $end = $end_day;
            } else {
                $end   = date('Ymd', $time_info['end']);
            }

            $days[$k]['start']  = $start;
            $days[$k]['end']    = $end;

            //获取周设备安装量
            $install_num = _model('screen_device')->getTotal( array('day <=' => $end));

            $filter = array(
                    'day >=' => $start,
                    'day <=' => $end,
                    'is_online' => 1
            );

            //获取周活跃量数据
            $where = screen_stat_helper::to_where_sql($filter);

            //为预防换厅的设备导致统计混乱， 故此按营业厅分组
            $sql            = " SELECT COUNT(*) AS `online_num`, `device_unique_id`, `business_id` FROM `screen_device_online_stat_day` {$where} GROUP BY  `device_unique_id`, `business_id` ";
            $online_list    = _model('screen_device_online_stat_day')->getAll($sql);

            $tmp            = array();
            $online_days    = array();


            //去除换厅或重新安装的设备
            foreach ($online_list as $k2 => $v2) {
                $tmp_filter = array('device_unique_id' => $v2['device_unique_id'], 'business_id' => $v2['business_id'], 'day <=' => $end);
                $device_info = _model('screen_device')->read($tmp_filter);
                //换厅
                if (!$device_info) {
                    continue;
                }
                $online_days[$v2['device_unique_id']]  = $v2['online_num'];
                $tmp[$v2['device_unique_id']]          = $v2;
            }

            $active_num     = count($tmp);
            $active_days    = array_sum($online_days);

            //周活跃
            $tmp_list[$k]['active_num'] = $active_num;

            if ($install_num < 1 || $active_num < 1) {
                $tmp_list[$k]['active_rate']    = '0%';
                $tmp_list[$k]['average_active'] = '0天';
            } else {
                //周活跃率
                $tmp_list[$k]['active_rate']    = (round($active_num/$install_num, 2)*100).'%';
                //周平均活跃天数
                $tmp_list[$k]['average_active'] = round($active_days/$active_num, 1).'天';
            }
            //活跃天数
            $tmp_list[$k]['install_num'] = $install_num;
            $tmp_list[$k]['active_days'] = $active_days;
        }

        $list = array();

        //反转键值，用于模板匹配第几周
        $weeks = array_flip($weeks);

        $list  = array('weeks' => $weeks, 'data_list' => $tmp_list, 'days' => $days);
        return $list;

    }

    /**
     *  get_offline_data_for_business_hall 获取离线数据表，导出表格
     * @param array $params
     * @return array
     */
    public function get_offline_data_for_business_hall($params)
    {
        $time               = time();
        $start_date = date('Ymd',$time - 3600 * 24 * 7);
        $end_date   = date('Ymd',$time - 3600 * 24);

        isset($params['start_time']) && $params['start_time'] ? $start_date = str_replace('-', '', $params['start_time']) : '';
        isset($params['end_time']) && $params['end_time'] ? $end_date   = str_replace('-', '', $params['end_time']) : '';

        $sql  = " SELECT `business_hall_id`,`date`,count(*) as offline_num ";
        $sql .= " FROM `screen_everyday_offline_record` ";
        $sql .= " WHERE date >='{$start_date}' AND date <='{$end_date}' AND all_day = 1 ";
        $sql .= " GROUP BY `business_hall_id`,`date` ORDER BY `date` ASC ";

        $list = _model('screen_everyday_offline_record')->getAll($sql);

        $data = $cache_data = $device_filter = $result = array();

        $count = 0;
        //组装数据
        foreach ($list as $k => $v) {
            $data[$v['business_hall_id']]['data'][$v['date']]['offline_num'] = $v['offline_num'];

            //查询营业厅总数截止到当前时间点数据总数
            $device_filter['business_id'] = $v['business_hall_id'];
            $device_filter['add_time <='] = $end_date;
            $device_filter['status']      = 1;

            $device_install_count = _model('screen_device')->getTotal($device_filter);

            $data[$v['business_hall_id']]['data'][$v['date']]['install_num']  = $device_install_count;

            if (!$device_install_count) {
                $data[$v['business_hall_id']]['data'][$v['date']]['install_rate'] = 0;
            } else {
                $data[$v['business_hall_id']]['data'][$v['date']]['install_rate'] = number_format($v['offline_num']/$device_install_count,2)*100 . '%';
            }

            $data[$v['business_hall_id']]['offline_total'] = $v['offline_num'];

            $count = count($data[$v['business_hall_id']]['data']) > $count ? count($data[$v['business_hall_id']]['data']) : $count;
        }

        $result['count'] = $count;
        $result['data'] = $data;
        return $result;
    }

    /**
     *  get_offline_data_for_business_hall 获取离线数据表，导出表格
     * @param array $params
     * @return array
     */
    public function get_offline_data_for_version($params)
    {
        $time               = time();
        $start_date = date('Ymd',$time - 3600 * 24 * 7);
        $end_date   = date('Ymd',$time - 3600 * 24);

        isset($params['start_time']) && $params['start_time'] ? $start_date = str_replace('-', '', $params['start_time']) : '';
        isset($params['end_time']) && $params['end_time'] ? $end_date   = str_replace('-', '', $params['end_time']) : '';

        $sql  = " SELECT `device_nickname_id`,`date`,count(*) as offline_num ";
        $sql .= " FROM `screen_everyday_offline_record` ";
        $sql .= " WHERE date >='{$start_date}' AND date <='{$end_date}' AND all_day = 1 ";
        $sql .= " GROUP BY `device_nickname_id`,`date` ORDER BY `date` ASC ";

        $list = _model('screen_everyday_offline_record')->getAll($sql);

        $data = $cache_data = $device_filter = $result = array();

        $count = 0;

        //组装数据
        foreach ($list as $k => $v) {
            if (empty($v['device_nickname_id'])) {
                continue;
            }

            $data["{$v['device_nickname_id']}"]['data'][$v['date']]['offline_num'] = $v['offline_num'];

            //查询营业厅总数截止到当前时间点数据总数
            $device_filter['device_nickname_id'] = $v['device_nickname_id'];
            $device_filter['add_time <='] = $end_date;
            $device_filter['status']      = 1;

            $device_install_count = _model('screen_device')->getTotal($device_filter);

            $data["{$v['device_nickname_id']}"]['data'][$v['date']]['install_num']  = $device_install_count;

            if (!$device_install_count) {
                $data["{$v['device_nickname_id']}"]['data'][$v['date']]['install_rate'] = 0;
            } else {
                $data["{$v['device_nickname_id']}"]['data'][$v['date']]['install_rate'] = number_format($v['offline_num']/$device_install_count,2)*100 . '%';
            }

            $data["{$v['device_nickname_id']}"]['offline_total'] = $v['offline_num'];

            $count = count($data["{$v['device_nickname_id']}"]['data']) > $count ? count($data["{$v['device_nickname_id']}"]['data']) : $count;
        }

        $result['count'] = $count;
        $result['data'] = $data;
        return $result;
    }

    /**
     * 获取设备活跃统计， 有效门店数，终端月活跃率，有效终端数
     * 1、有效门店数：至少有一台设备在一个月内累计活跃15天，即算该门店活跃，需提取该类门店总和
     * 2、终端月活跃率：当月累计活跃15天以上（含15天）的设备/总有效终端数
     * 3、有效终端数：安装设备数-下柜设备（本次下柜设备暂定为0）
     * @param unknowtype
     * @return return_type
     * @author 王敬飞 (wangjf@alltosun.com)
     * @date 2018年3月26日上午10:49:10
     */
    public function get_active_device_stat($params)
    {
        if ( isset($params['end_time']) && $params['end_time']) {
            $filter['day <='] = date('Ymd', strtotime($params['end_time']));
        }

        if (isset($params['start_time']) && $params['start_time']) {
            $filter['day >='] = date('Ymd', strtotime($params['start_time']));
        }

        $where = screen_stat_helper::to_where_sql($filter);

        //按月按设备获取活跃天数 （最多截止上一年1月1日）
        $last_year = date('Y-01-01', strtotime('-1 year'));
        $months    = array();

        do{
            //遍历出每月的开始日期和结束日期
            $months[$last_year]['start_date'] = date('Ym01', strtotime($last_year));
            $months[$last_year]['end_date'] = date('Ymd', strtotime('+1 month', strtotime($last_year)) - 3600 * 24 );
            $last_year = date('Y-m-d', strtotime('+1 month', strtotime($last_year)));
        }while($last_year <= date('Y-m-d'));


        $availability_business_hall = array();
        //查出每月的活跃天数大于 14天的设备 , 并且按厅去重
        foreach ($months as $k => $v) {
            $where = to_where_sql(array('day >=' => $v['start_date'], 'day <=' => $v['end_date'], 'is_online' => 1));
            $sql = " SELECT business_id, days FROM (SELECT count(*) as days, device_unique_id, business_id FROM `screen_device_online_stat_day` {$where} GROUP BY device_unique_id, business_id) as sql_str where days >14 GROUP BY business_id";

            $res = _model('screen_device_online_stat_day')->getAll($sql);

            if (!$res) {
                continue;
            }

            foreach ( $res as $k1 => $v1 ) {
                $availability_business_hall[$v1['business_id']] = $v1['days'];
            }

        }

        //查出本月的活跃设备
        $start_date = date('Ym01', strtotime($params['start_time']));
        $end_date = date('Ymd', strtotime('+1 month', strtotime($start_date)) - 24*3600 );

        //获取累计活跃15天以上的设备
        p($start_date, $end_date);
        $where = to_where_sql(array('day >=' => $start_date, 'day <=' => $end_date, 'is_online' => 1));
        $sql = " SELECT business_id, device_unique_id days FROM (SELECT count(*) as days, device_unique_id, business_id FROM `screen_device_online_stat_day` {$where} GROUP BY device_unique_id, business_id) as sql_str where days >14 GROUP BY business_id, device_unique_id";

        $res = _model('screen_device_online_stat_day')->getAll($sql);


        $availability_device = array();
        foreach ( $res as $k1 => $v1 ) {
            $availability_device[$v1['business_id'].'_'.$v1['device_unique_id']] = $v1['device_unique_id'];
        }

    }
}