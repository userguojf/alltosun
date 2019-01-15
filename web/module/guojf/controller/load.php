<?php
/**
 * alltosun.com  install_load.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-11-8 下午4:03:32 $
 * $Id$
 */
class Action
{

    public function mc()
    {
        global $mc_wr;

        $mc_wr->set();
        $mc_wr->set();
    }

    public function index()
    {

//         $phone_arr = _model('wework_user')->getFields('mobile',array('id >' => 17825));

//         $list = _model('wework_zj_record')->update(
//                 array('phone' => $phone_arr),
//                 array('status' => 3, 'errmsg' => '')
//         );
//         exit();
        $list = _model('wework_zj_record')->getList(array(1 => 1), " ORDER BY `status` DESC ");

        foreach ( $list as  $k => $v ) {
            $data[$k]['province']  = $v['province'];
            $data[$k]['city']  = $v['city'];
            $data[$k]['name']  = $v['name'];
            $data[$k]['phone'] = "\t".$v['phone'];
            $data[$k]['rank']  = $v['rank'] == 1 ? '省级' : "市级";
            $data[$k]['errmsg'] = $v['errmsg'] ?: '请求成功';
        }

        $head = array( '省', '市','姓名', '手机号', '账号级别', '备注信息');

        $params['filename'] = '成功导入的成员详情';

        $params['data']     = $data;
        $params['head']     = $head;

        Csv::getCvsObj($params)->export();
    }

    public function top()
    {
//         $a = array ( 0 => '45785', 1 => '46267', 2 => '46495', 3 => '46085', 4 => '109998', 5 => '46105', 6 => '46146', 7 => '45256', 8 => '20429', 9 => '20382', 10 => '45126', 11 => '46016', 12 => '46222', 13 => '91232', 14 => '93080', 15 => '45418', 16 => '46457', 17 => '91114', 18 => '46319', 19 => '91235', 20 => '93066', 21 => '93066', 22 => '93066', 23 => '93066', 24 => '93080', 25 => '93066', 26 => '93080', 27 => '93080', 28 => '46036', 29 => '93080', 30 => '91235', 31 => '45971', 32 => '46120', 33 => '20430', 34 => '20584', 35 => '29045', 36 => '45191', 37 => '19807', 38 => '20953', 39 => '46222', 40 => '49637', );
//         p($a);
//         p(count(array_unique($a)));exit();
        global $mc_wr;
        $exit_names = $mc_wr->get('top10');
// p($exit_names);exit();
        $filter = array(1 => 1);
        $rfid_list   = _model('rfid_label')->getList($filter);
        $screen_list = _model('screen_device')->getList($filter);

        $screen_names = $rfid_names = $top = $top_yyt = [];

        $j = 0;

        foreach ( $screen_list as $k => $v ) {

            $nickname_info = _model('screen_device_nickname')->read(array('id' => $v['device_nickname_id']));

            $screen_names[$j]['name']    = $nickname_info['name_nickname'] ? $nickname_info['name_nickname'] : $nickname_info['phone_name'];
            $screen_names[$j]['version'] = $nickname_info['version_nickname'] ? $nickname_info['version_nickname'] : $nickname_info['phone_version'];
            $screen_names[$j]['imei']    = $v['imei'];
            $screen_names[$j]['business_hall_id'] = $v['business_id'];
            ++ $j;
        }
// p($screen_names);exit();
        foreach ( $rfid_list as $kk => $vv ) {
            $rfid_names[$j]['name']    = $vv['name'];
            $rfid_names[$j]['version'] = $vv['version'];
            $rfid_names[$j]['imei']    = $vv['imei'];

            $rfid_names[$j]['business_hall_id'] = $vv['business_hall_id'];
            ++ $j;
        }

        $phone_info = array_merge($screen_names, $rfid_names);
// p($phone_info);exit();
        if ( !$phone_info) return '暂无数据';

        $yyt_ids = [];
        foreach ($phone_info as $key => $val) {
            if ( $val['version'] == 'mete10' || $val['version'] == 'mate10') {
                $val['version'] = 'Mate 10';
            }

            $phone_name_version = $val['name'].'/'.$val['version'];

            if ( !isset($top[$phone_name_version]) ) {
                $top[$phone_name_version]['num'] = 1;
                $top[$phone_name_version]['business_num'] = [];
            } else {
                ++ $top[$phone_name_version]['num'];
            }

            array_push( $top[$phone_name_version]['business_num'], $val['business_hall_id']);
            
        }
// p($yyt_ids);exit();
        arsort($top);
// p($top);exit();
        $device_num = $yyt_num = 0;
        foreach ( $top as $k => &$v) {
            if ( isset($exit_names[$k]) ) {
                $v['num'] = $v['num'] - $exit_names[$k];
                if ( $v <= 0 ) {
                    unset($top[$k]);
                }
            }
            $v['business_num'] = count(array_unique($v['business_num']));
            $device_num += $v['num'];
            $yyt_num    += $v['business_num'];
        }

        arsort($top);
//         p($top);
//         exit();

        $i = 0;

        foreach ( $top as $kk => $vv ) {
            $list[$i]['top']     = $i + 1;
            $phone = explode('/', $kk);
            $list[$i]['name']    = $phone[0];
            $list[$i]['version'] = $phone[1];
            $list[$i]['num']     = $vv['num'];
            $list[$i]['yyt_num'] = $vv['business_num'];
            $list[$i]['device_percent'] = number_format($vv['num'] / $device_num * 100, 2);
            $list[$i]['yyt_percent']    = number_format($vv['business_num'] / $yyt_num * 100, 2);

            ++$i;
        }
// // p($list);exit();
        $head = array( '排名','终端品牌', '终端型号', '设备数量', '门店量', '设备百分比/%', '门店量百分比/%');

        $params['filename'] = '终端排行';
        $params['data']     = $list;
        $params['head']     = $head;

        Csv::getCvsObj($params)->export();
    }

    public function zimo_2()
    {
        $filter['day >='] = 20171204;
        $filter['day <='] = 20171231;

        $device_info = _model('screen_device')->getList($filter, " ORDER BY `day` ASC ");

        if (!$device_info) exit('暂无统计信息');

        foreach ($device_info as $k => $v) {
            $list[$k]['business_hall'] = screen_helper::by_id_get_field($v['business_id'],'business_hall', 'title');
            $list[$k]['user_number']   = screen_helper::by_id_get_field($v['business_id'],'business_hall', 'user_number');
            $list[$k]['unique']        = $v['device_unique_id'];
            $list[$k]['phone_name']    = $v['phone_name_nickname'] ? $v['phone_name_nickname'] : $v['phone_name'];
            $list[$k]['phone_version'] = $v['phone_version_nickname'] ? $v['phone_version_nickname'] : $v['phone_version'];
    
            $list[$k]['date']          = $v['day'];

            $filter['day >='] = $v['day'];
            $filter['device_unique_id'] = $v['device_unique_id'];
            $filter['business_id']      = $v['business_id'];
            $online_day_num = _model('screen_device_online_stat_day')->getTotal($filter);
            $list[$k]['num']            = $online_day_num ? $online_day_num : 1;
        }

        if (!$list) {
            exit('暂无数据');
        }

        $params['filename'] = '新增设备详情';
        $params['data']     = $list;
        $params['head']     = array('营业厅', '渠道码','唯一标识','手机品牌','手机型号', '安装时间' ,'活跃天数');

        Csv::getCvsObj($params)->export();
    }

    // 帮忙提一下17年10月-12月的亮屏数据：月活跃率=活跃天数累计在7天或7天以上的设备数/总设备数；
    // 安装不足7天的 如果安装这些天持续活跃 ，算作活跃
    public function count()
    {
        $end_day   = tools_helper::get('end', 0);

        if ( !$end_day ) return '请输入截止时间';

        $filter = array();
        $filter['day <='] = $end_day;

        $device_list = _model('screen_device')->getList($filter);
        p(count($device_list));
        p($device_list);
    }

    public function seven_days_activity()
    {
        $year  = tools_helper::get('year', 0);
        $month = tools_helper::get('month', 0);

        $province = tools_helper::get('province', '北京');

        if ( $province ) {
            $province_id = _uri('province', array('name' => $province), 'id');
            if ( !$province_id ) {
                return '参数省名称不正确';
            }
        }

        $filter = [];
        $range  = '全国';

        if ( $province_id ) {
            $filter['province_id'] = $province_id;
            $range = $province;
        }

        if ( $year != 2017 ) {
            $year = date('Y');
        }

        $month_arr = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12);
        if ( !$month || !in_array($month, $month_arr)) {
            $month = date('m');
        }

        $day = date('t', strtotime($year.$month));

        $date = $year.$month.$day;
        $filter['day <='] = $date;

        $device_list = _model('screen_device')->getList($filter, ' ORDER BY `day` ASC ');

        $count = count($device_list);
        $active_nums = 0;

        foreach ($device_list as $k => $v) {
            $short_of_days = ( strtotime($date) - strtotime($v['day']) ) / 24 / 3600 + 1;
                // 某月
                $online_nums = _model('screen_device_online_stat_day')->getTotal(
                                array(
                                        'device_unique_id' => $v['device_unique_id'],
                                        'day >='           => $v['day'],
                                        'day <='           => $date
                                )
                );

                if ( $short_of_days >= 7 ) {
                    if ( $online_nums < 7 ) {
                        continue;
                    }
                } else {
                    if ( $online_nums < $short_of_days ){
                        continue;
                    }
                }

                ++ $active_nums;
        }

        
        p($range.$year.'年'.$month.'月'.'活跃情况（单位/7天）：');
        p('设备总数：'.$count);
        p('活跃设备数量：'.$active_nums);
        $percent = number_format($active_nums / $count * 100, 2);
        p( '活跃率：'.$percent.'%');
    }
}