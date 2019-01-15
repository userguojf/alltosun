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

    public function zimo_1_1()
    {

        $filter = array(1 => 1);
        $rfid_list   = _model('rfid_lebal')->getList($filter);
        $screen_list = _model('screen_device')->getList($filter);

        $rfid_info = $screen_info = [];

        foreach ( $rfid_list as $k => $v ) {
            if ( !isset($rfid_info[$v['business_hall_id']]) ) {
                $rfid_info[$v['business_hall_id']] = array();
            }

            array_push($rfid_info[$v['business_hall_id']] , $v);
        }

        foreach ( $screen_list as $k => $v ) {
            if ( !isset($screen_info[$v['business_id']]) ) {
                $screen_info[$v['business_id']] = array();
            }

            array_push($screen_info[$v['business_hall_id']] , $v);
        }
p($rfid_info);
p($screen_info);
exit();
        $j = 0;
        foreach ( $data as $kk => $vv ) {
            $nick_info = _uri('screen_device_nickname', array('id' => $kk));

            if ( !$nick_info ) continue;

            $list[$j]['name']    = $nick_info['name_nickname'] ? $nick_info['name_nickname'] : $nick_info['phone_name'];
            $list[$j]['version'] = $nick_info['version_nickname'] ? $nick_info['version_nickname'] : $nick_info['phone_version'];

            $device_total_num         = count($vv);
            $list[$j]['installl_num'] = $device_total_num;

            for ($i = 1; $i < 8; $i ++) {
                $field   = $i.'day_offline';
// P('第'.$i.'条');
// p('安装后第几天:'.$field);
                $day_field_arr = _model('screen_after_installing_offline_record')->getFields(
                        $field, array('device_unique_id' => $vv)
                );
// p($day_field_arr);
                $device_offline_num = array_sum($day_field_arr);
// p('离线设备数'.$device_offline_num);
                $list[$j][$i.'day_offline'] = number_format($device_offline_num / $device_total_num, 2) * 100;
// p('百分比:'.$list[$j][$i.'day_offline']);
            }
            ++ $j;
        }
// exit();
        $head = array('终端品牌', '终端型号','新增安装数量(2017.9.1-2017.12.31)');
        for ($i = 1; $i < 8; $i ++) {
            array_push($head, '安装后第'.$i.'天离线率(%)');
        }

        $params['filename'] = '终端安装后7天离线率';
        $params['data']     = $list;
        $params['head']     = $head;

        Csv::getCvsObj($params)->export();
    }

    public function zimo_1()
    {
        global $mc_wr;

        $load_list = $mc_wr->set('load_list');
        $install_data = $mc_wr->set('install_data');
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