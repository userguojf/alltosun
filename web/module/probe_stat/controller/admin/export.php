<?php
/**
  * alltosun.com 导出 export.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年11月9日 上午3:05:09 $
  * $Id$
  */
set_time_limit(0);

probe_helper::load('func');

class Action
{

    public function export_user_mac_all()
    {
        $start_time = tools_helper::Get('start_time', date('Y-m-d 00:00:00'));
        $end_time   = tools_helper::Get('end_time', date('Y-m-d 23:59:59'));
        $province_id   = tools_helper::Get('province_id', 1);

        $days = array();
        do{
            $days[] = date('Ymd',strtotime($start_time));
            $start_time = date('Y-m-d 00:00:00', strtotime($start_time) + 3600*24);
        }while($start_time <= $end_time);

        $new_list = array();
        foreach ( $days as $k => $v ) {
            $stat_list  = _model('probe_stat_day')->getList(array('date_for_day' => $v, 'province_id' => $province_id));
            foreach ( $stat_list as $k1 => $v1 ){
                $business_info = business_hall_helper::get_business_hall_info($v1['business_id']);
                if ( !$business_info ) {
                    continue;
                }

                $new_list[] = array(
                        'day'   => date('Y-m-d', strtotime($v)),
                        'title' => $business_info['title'],
                        'user_number' => $business_info['user_number'],
                        'indoor'   => $v1['indoor'],
                        'outdoor'  => $v1['outdoor']
                );
            }
        }

        $params['filename'] = $start_time.'至'.$end_time.'室内室外数据';
        $params['data']     = $new_list;


        $params['head']     = array('日期', '营业厅名称', '渠道码' ,'室内', '室外');

        Csv::getCvsObj($params)->export();

    }

    /**
     * 导出室内室外用户
     * @return boolean
     */
    public function export_user_mac($b_id=0, $type=1, $start_time='', $end_time='') {
        if (!$b_id) {
            $b_id       = tools_helper::Get('b_id', 0);
        }

        if (!$start_time) {
            $start_time = tools_helper::Get('start_time', date('Y-m-d 00:00:00'));
        }

        if (!$end_time) {
            $end_time = tools_helper::Get('end_time', date('Y-m-d 23:59:59'));
        }

        if (!$type) {
            $type       = tools_helper::Get('type', 1);
        }

        if (!$b_id) {
            return false;
        }

        $business_hall_info = _model('business_hall')->read($b_id);

        if (!$business_hall_info) {
            return false;
        }

        $db = get_db($business_hall_info['id']);

        $filter = array(
                'date >=' => date('Ymd', strtotime($start_time)),
                'date <=' => date('Ymd', strtotime($end_time)),
        );

        // 获取规则
        $rule   = probe_rule_helper::get_rules($business_hall_info['id']);

        $list = $db->getList($filter);

        $inner_mac_list = array();
        $oudoor_mac_list = array();
        $macs            = array();
        foreach ($list as $k => $v) {

            //规则
            $continued  = $v['continued'];
            //是否为室内
            $is_indoor  = $v['is_indoor'];
            //停留时长
            $remain     = $v['remain_time'];

            // 如果设置了continued规则，则判断是否满足
            //$rule['continued'][1] ： 连续活跃N天以上，不计入客流量（
            //$rule['continued'][0] ： 连续驻留N小时以上， 不计入客流量)（营业厅工作人员）
            if ( !empty($rule['continued'][1]) ) {
                if ( $continued >= $rule['continued'][1] ) {
                    continue;
                }
            }

            // 如果设置了minute规则，则判断是否满足 （连续驻留N分钟以下，不计入厅内）
            if ( !empty($rule['minute']) ) {
                if ( $remain < ($rule['minute'] * 60) ) {
                    $is_indoor = false;
                }
            }

            $mac = probe_helper::mac_encode($v['mac']);

            // 室内
            if ($is_indoor) {
                $key = 'indoor'.$v['date'].$mac;
                if (empty($inner_mac_list[$key])) {
                    $info = array(
                            'title' => $business_hall_info['title'],
                            'user_number' => $business_hall_info['user_number'],
                            'mac'   => $mac,
                            'time'   => date('Y-m-d H:i:s', $v['frist_time'])
                    );
                    $inner_mac_list[$key] = $info;
                }
            //室外
            } else {
                $key = 'oudoor'.$v['date'].$mac;
                if (empty($oudoor_mac_list[$key])) {
                    $info = array(
                            'title' => $business_hall_info['title'],
                            'user_number' => $business_hall_info['user_number'],
                            'mac'   => $mac,
                            'time'   => date('Y-m-d H:i:s', $v['frist_time'])
                    );
                    $oudoor_mac_list[$key] = $info;
                }
            }
        }

        if ($type == 1) {
            $params['filename'] = $business_hall_info['title'].'用户mac列表(室内)';
            $params['data']     = $inner_mac_list;
        } else {
            $params['filename'] = $business_hall_info['title'].'用户mac列表(室外)';
            $params['data']     = $oudoor_mac_list;
        }

        $params['head']     = array('营业厅名称', '渠道码' ,'用户MAC', '当天首次探测时间');

        Csv::getCvsObj($params)->export();
    }

    /**
     * 导出设备在线情况
     * @return boolean
     */
    public function export_device_online_info() {

        $start_time = tools_helper::Get('start_time', date('Y-m-d 00:00:00'));
        $end_time   = tools_helper::Get('end_time', date('Y-m-d 23:59:59'));

        //查询所有设备
        $device_list  = _model('probe_device')->getList(array(1=>1));

        $date = array();

        for ($i=$start_time; $i <= $end_time; $i=date('Y-m-d H:i:s', strtotime($i)+3600*24)) {
            //28号去除
            if (date('Ymd', strtotime($i)) == 20180128) {
                continue;
            }
            $date[] = $i;
        }

        $data_list = array();
        foreach ($device_list as $k => $v) {

            $business_hall_info = _model('business_hall')->read($v['business_id']);

            if (!$business_hall_info) {
                continue;
            }

            try{
                $db = get_db($business_hall_info['id']);
            }catch(Exception $e){
                $db = false;
            }

            if (!$db) {
                continue;
            }

            foreach ($date as $day) {

                $tmp = array();
                //查询表
                $filter = array(
                        'date' => date('Ymd', strtotime($day)),
                        'dev'  => $v['device']
                );

                $stat_info = $db->read($filter);

                if ($stat_info) {
                    $status = '在线';
                } else {
                    $status = '离线';
                }

                $tmp = array(
                        'business_hall_title' => $business_hall_info['title'],
                        'user_number'         => $business_hall_info['user_number'],
                        'device'              => $v['device'],
                        'date'                => $day,
                        'status'              => $status
                );

                $data_list[] = $tmp;
            }

        }

        $params['filename'] = date('Y-m-d', strtotime($start_time)).'至'.date('Y-m-d', strtotime($end_time)).'设备状态表';
        $params['data']     = $data_list;
        $params['head']     = array('营业厅名称', '渠道码' ,'探针设备', '日期', '在线状态');
        Csv::getCvsObj($params)->export();
    }

    /**
     * 导出设备在线状态列表
     */
    public function export_device_online_list() {

        $search_filter = tools_helper::Get('search_filter', array());
        $province_id   = tools_helper::Get('province_id', 0);

        if ($province_id) {
            $filter = array(
                    'province_id' => $province_id
            );
        } else {
            $filter = array(1=>1);
        }

        //查询所有设备
        $device_list  = _model('probe_device')->getList($filter);

        //营业厅设备数
        $business_hall_device_count = array();

        //排序
        $sort = array();

        foreach ($device_list as $k => $v) {
            $sort[] = $v['business_id'];
            if (empty($business_hall_device_count[$v['business_id']])) {
                $business_hall_device_count[$v['business_id']] = 1;
            } else {
                ++$business_hall_device_count[$v['business_id']];
            }
        }

        if ($device_list) {
            array_multisort($sort, SORT_ASC, $device_list);
        }

        if (!empty($search_filter['start_time'])){
            $start_time = $search_filter['start_time'];
        } else {
            $start_time = date('Y-m-d 00:00:00');
        }

        if (!empty($search_filter['end_time'])){
            $end_time = $search_filter['end_time'];
        } else {
            $end_time = date('Y-m-d 00:00:00');
        }

        $date = array();
        for ($i=$start_time; $i <= $end_time; $i=date('Y-m-d H:i:s', strtotime($i)+3600*24)) {
            $date[] = $i;
        }

        $data_list = array();

        foreach ($device_list as $k => $v) {

            $business_hall_info = _model('business_hall')->read($v['business_id']);

            if (!$business_hall_info) {
                continue;
            }

            try{
                $db = get_db($business_hall_info['id']);
            }catch(Exception $e){
                $db = false;
            }

            if (!$db) {
                continue;
            }

            //查询省市区
            $province_name = business_hall_helper::get_info_name('province', $business_hall_info['province_id'], 'name');
            $city_name     = business_hall_helper::get_info_name('city', $business_hall_info['city_id'], 'name');

            $tmp = array(
                    'province_name'       => $province_name,
                    'city_name'           => $city_name,
                    'business_hall_title' => $business_hall_info['title'],
                    'user_number'         => $business_hall_info['user_number'],
                    'device_count'        => $business_hall_device_count[$v['business_id']],
                    'device'              => $v['device'],
            );

            foreach ($date as $day) {
                //查询表
                $filter = array(
                        'date' => date('Ymd', strtotime($day)),
                        'dev'  => $v['device']
                );

                $stat_info = $db->read($filter);

                if ($stat_info) {
                    $status = '在线';
                } else {
                    $status = '离线';
                }

                $tmp[$day] = $status;
            }
            $data_list[] = $tmp;
        }

        $hand = array(
                '省',
                '市',
                '厅',
                '渠道编码',
                '本厅设备总数',
                '设备',
        );
        foreach ($date as $day) {
            array_push($hand, date('Y年m月d日', strtotime($day)));
        }

        $params['filename'] = date('Y-m-d', strtotime($start_time)).'至'.date('Y-m-d', strtotime($end_time)).'设备状态列表';
        $params['data']     = $data_list;
        $params['head']     = $hand;
        Csv::getCvsObj($params)->export();
    }

    /**
     * 根据省导出设备统计
     */
    public function export_device_stat_by_province()
    {
        $sql = " SELECT COUNT(*) as device_num, province_id FROM `probe_device` WHERE `status` = 1 GROUP BY `province_id` ";

        $date = strtotime('-1 month');

        //根据省获取设备量
        $counts = _model('probe_device')->getAll($sql);
        $data = array();
        foreach ($counts as $k => $v) {
            $province_name = business_hall_helper::get_info_name('province', $v['province_id'], 'name');
            if (!$province_name) {
                continue;
            }

            //查询本月活跃设备
            $active_count = _model('probe_device_status_stat_day')->getTotal(array(
                    'province_id' => $v['province_id'],
                    'status' => 1,
                    'date >=' => date('Ym01', $date),
                    'date <' => date('Ym01', strtotime( '+1 month', strtotime( date('Ym01', $date) ) ) ),
            ));

            //日均活跃率
            $active_rote = round($active_count / 30 / $v['device_num']*100, 4);

            $data[] = array(
                    'data' => date('Y-m'),
                    'province' => $province_name,
                    'count'     => $v['device_num'],
                    'active_rote' => $active_rote.'%',
            );
        }

        $hand = array(
                '账期',
                '省份',
                '门店Wi-Fi探针设备数（台）',
                '门店Wi-Fi探针日均活跃率',
        );

        $params['filename'] = '探针统计';
        $params['data']     = $data;
        $params['head']     = $hand;
        Csv::getCvsObj($params)->export();
    }

    /**
     * 根据省导出设备统计
     */
    public function export_device_stat_by_province2()
    {
        $sql = " SELECT COUNT(*) as device_num, province_id FROM `probe_device` WHERE `status` = 1 GROUP BY `province_id` ";

        $date = strtotime('-1 month');
        $start_date = date('Ym01', $date);

        //根据省获取设备量
        $counts = _model('probe_device')->getAll($sql);
        $data = array();
        foreach ($counts as $k => $v) {
            $province_name = business_hall_helper::get_info_name('province', $v['province_id'], 'name');
            if (!$province_name) {
                continue;
            }

            //查询设备
            $device_list = _model('probe_device')->getList(array(
                    'province_id' => $v['province_id'],
                    'status'      => 1,
            ));

            $device_active_day_reate = 0;
            $business_list = array();
            foreach ($device_list as $k1 => $v1) {
                $a = 30;
                $add_day = date('Ymd', strtotime($v1['add_time']));

                if ( $add_day > $start_date ) {
                    $a = date('Ymd', strtotime( '+1 month', strtotime( $start_date ) ) )  - $add_day + 1;
                }

                //计算设备日均活跃率
                $filter = array(
                        'province_id' => $v1['province_id'],
                        'date >=' => $start_date,
                        'date <' => date('Ym01', strtotime( '+1 month', strtotime( $start_date ) ) ),
                        'device' => $v1['device'],
                        'business_id' => $v1['business_id'],
                        'status'    => 1,
                );
                //获取设备活跃天数
                $device_active_day_count = _model('probe_device_status_stat_day')->getTotal($filter);
                //设备日均活跃率
                $device_active_day_reate += round($device_active_day_count / $a * 100, 2);
            }

            //日均设备活跃率
            $device_active_rote = round($device_active_day_reate / $v['device_num'], 2);


            $data[] = array(
                    'data' => date('Y-m'),
                    'province' => $province_name,
                    'count'     => $v['device_num'],
                    'active_rote' => $device_active_rote.'%',
            );
        }

        $hand = array(
                '账期',
                '省份',
                '门店Wi-Fi探针设备数（台）',
                '门店Wi-Fi探针日均活跃率',
        );
        $params['filename'] = '探针统计';
        $params['data']     = $data;
        $params['head']     = $hand;
        Csv::getCvsObj($params)->export();
    }

//     /**
//      * 导出5省13家市试点厅的用户数据
//      */
//     public function export_user_mac_by_business_hall()
//     {

//         $user_number_list = array(
//                 '3101151000620',
//                 '3401001113737',
//                 '3401001113735',
//                 '5101011138413',
//                 '3210021007141',
//                 '3205041063342',
//                 '4403041102464',
//                 '3101151000609',
//                 '5101011107715',
//                 '3201021044590',
//                 '3401001113738',
//         );

// //         $user_number_list = array(
// //                 //'beijing_YYT',
// // //                 '1101111001246',
// //                 //'1101021002051',
// //                 '4601001001337'
// //         );

//         //获取所有探针
//         foreach ($user_number_list as $user_number) {
//             //查询营业厅
//             $business_info = _model('business_hall')->read(array('user_number' => $user_number));
//             if (!$business_info) {
//                 continue;
//             }

//             $db = get_db($business_info['id']);

//             //查询所有用户数据
//             $user_macs = $db->getList(array(1=>1));

//             $export_data = array();
//             foreach ($user_macs as $k => $v) {
//                 $is = $v['is_indoor'] == 1 ? '厅内' : '厅外';
//                 $export_data[] = array(
//                         'mac' => probe_helper::mac_encode($v['mac']),
//                         'date' => date('Y-m-d', strtotime($v['date'])),
//                         'first' => date('Y-m-d H:i:s', $v['frist_time']),
//                         'last'  => date('Y-m-d H:i:s', $v['up_time']),
//                         'remain_time' => screen_helper::format_timestamp_text($v['remain_time']),
//                         'is'    => $is
//                 );
//             }

//             $hand = array(
//                     'MAC地址',
//                     '日期',
//                     '首次出现时间',
//                     '最后探测时间',
//                     '驻留时长',
//                     '厅内/厅外'
//             );
//             $params['filename'] = $business_info['title'];
//             $params['data']     = $export_data;
//             $params['head']     = $hand;
//             Csv::getCvsObj($params)->export();
//         }

//     }

    /**
     * 导出5省13家市试点厅的用户数据
     * 分页
     */
    public function export_user_mac_by_business_hall()
    {

        $user_number    = tools_helper::Get('user_number', '');
        $page           = tools_helper::Get('page', 1);
        $limit          = tools_helper::Get('limit', 50000);
        //查询营业厅
        $business_info = _model('business_hall')->read(array('user_number' => $user_number));
        if (!$business_info) {
            echo '不存在的厅';exit;
        }

        $db = get_db($business_info['id']);

        $start_limit = ($page - 1) * $limit;
        //查询所有用户数据
        $user_macs = $db->getList(array(1=>1), " LIMIT {$start_limit},{$limit}");

        $export_data = array();
        foreach ($user_macs as $k => $v) {
            $is = $v['is_indoor'] == 1 ? '厅内' : '厅外';
            $export_data[] = array(
                    'mac' => probe_helper::mac_encode($v['mac']),
                    'date' => date('Y-m-d', strtotime($v['date'])),
                    'first' => date('Y-m-d H:i:s', $v['frist_time']),
                    'last'  => date('Y-m-d H:i:s', $v['up_time']),
                    'remain_time' => screen_helper::format_timestamp_text($v['remain_time']),
                    'is'    => $is
            );
        }

        $hand = array(
                'MAC地址',
                '日期',
                '首次出现时间',
                '最后探测时间',
                '驻留时长',
                '厅内/厅外'
        );

        $params['filename'] = $business_info['title'];
        if ($page > 1) {
            $params['filename'] .= $page - 1;
        }
        $params['data']     = $export_data;
        $params['head']     = $hand;
        Csv::getCvsObj($params)->export();
    }
}