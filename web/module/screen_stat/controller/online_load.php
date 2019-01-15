<?php
/**
 * alltosun.com  online_load.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任 何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-2-26 上午11:14:52 $
 * $Id$
 */
class Action
{
    public function __call($action = '', $param = array())
    {
//         $filter['day >=']      = 20180201;

        $geq = tools_helper::get('geq', 0);
        // 安装时间小于提取时间
        $filter['day <']       = 20180226;
        $filter['status']      = 1;
        $filter['province_id'] = 1;

        $device_info = _model('screen_device')->getList($filter, " ORDER BY `business_id` ASC ");

        if (!$device_info) exit('暂无统计信息');

        unset($filter['status']);

        foreach ($device_info as $k => $v) {
            $load_filter['day >=']      = 20180201;
            $load_filter['device_unique_id'] = $v['device_unique_id'];
            $load_filter['business_id']      = $v['business_id'];

            $online_day_num  = _model('screen_device_online_stat_day')->getTotal($load_filter);

            if ( !$online_day_num && $geq > 0 ) continue;

            $list[$k]['province'] = screen_helper::by_id_get_field($v['province_id'],'province', 'name');
            $list[$k]['city']     = screen_helper::by_id_get_field($v['city_id'],'city', 'name');
            $list[$k]['area']     = screen_helper::by_id_get_field($v['area_id'],'area', 'name');
            $list[$k]['business_hall'] = screen_helper::by_id_get_field($v['business_id'],'business_hall', 'title');
            $list[$k]['user_number']   = "\t".screen_helper::by_id_get_field($v['business_id'],'business_hall', 'user_number');
            $list[$k]['unique']        = "\t".$v['device_unique_id'];
//             $list[$k]['phone_name']    = $v['phone_name_nickname'] ? $v['phone_name_nickname'] : $v['phone_name'];
//             $list[$k]['phone_version'] = $v['phone_version_nickname'] ? $v['phone_version_nickname'] : $v['phone_version'];

            $list[$k]['date']          = $v['day'];

           

            $list[$k]['num'] = $online_day_num ? $online_day_num : 0;
        }

        if (!$list) exit('暂无数据');

        $params['filename'] = '设备2月份在线天数'.time();
        $params['data']     = $list;
        $params['head']     = array( '省', '市（/直辖市）', '区（/县）', '营业厅', '渠道码','唯一标识', '安装时间' ,'2月份在线天数');

        Csv::getCvsObj($params)->export();
    }

    public function seven()
    {
        global $mc_wr;

        $date = 20180226;

//         $lt_seven = array( 
//                 1 => 20180225, 
//                 2 => 20180224, 
//                 3 => 20180223, 
//                 4 => 20180222, 
//                 5 => 20180221, 
//                 6 => 20180219, 
//                 7 => 20180218 
//         );

        $filter['day <=']       = $date;
        $filter['status']      = 1;
        $filter['province_id'] = 1;

        $device_list = _model('screen_device')->getList($filter, ' ORDER BY `business_id` ASC ');

        // 组装数组
        $data = [];

        foreach ($device_list as $k => $v) {
            $short_of_days = ( strtotime($date) - strtotime($v['day']) ) / 24 / 3600 + 1;
            // 2月份在线天数
            $online_nums = _model('screen_device_online_stat_day')->getTotal(
                    array(
                            'device_unique_id' => $v['device_unique_id'],
                            'day >='           => 20180201,
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

            if ( !isset($data[$v['business_id']]) ) {
                $data[$v['business_id']] = [];
            }

            array_push($data[$v['business_id']], $v['device_unique_id']);
        }

        $mc_wr->set('seven', $data, 100);
        p($data);
//         p($range.$year.'年'.$month.'月'.'活跃情况（单位/7天）：');
//         p('设备总数：'.$count);
//         p('活跃设备数量：'.$active_nums);
//         $percent = number_format($active_nums / $count * 100, 2);
//         p( '活跃率：'.$percent.'%');
    }

    public function seven_load()
    {
        global $mc_wr;
        $mc_data = $mc_wr->get('seven');

        if ( $mc_data ) return '缓存无数据';

        $business_ids = _model('screen_device')->getFields(
                                                'business_id',
                                                array(
                                                        'province_id' => 1,
                                                        'status'      => 1
                                                ));
        $data = [];
        $data_business_ids = [];

        foreach ($mc_data as $k => $v) {
                $data[$k] = count($v);
                array_push($data_business_ids, $k);
        }

        $no_data_business_ids = array_diff($business_ids, $data_business_ids);

        foreach ($no_data_business_ids as $value) {
            $data[$value] = 0;
        }

        $i = 0;

        foreach ($data as $key => $val) {
            $info = screen_helper::by_id_get_field($key, 'business_hall');

            $list[$i]['province'] = screen_helper::by_id_get_field($info['province_id'],'province', 'name');
            $list[$i]['city']     = screen_helper::by_id_get_field($info['city_id'],'city', 'name');
            $list[$i]['area']     = screen_helper::by_id_get_field($info['area_id'],'area', 'name');
            $list[$i]['user_number']   = "\t".$info['user_number'];
            $list[$i]['business_hall'] = $info['title'];

            $count = _model('screen_device')->getTotal(
                    array(
                            'business_id' => $key,
                            'status'      => 1
                        )
            );

            $list[$i]['percent'] = number_format($val / $count * 100, 2);

            ++ $i;
        }

        if (!$list) exit('暂无数据');

        $params['filename'] = '设备2月份营业厅活跃率'.time();
        $params['data']     = $list;
        $params['head']     = array( '省', '市（/直辖市）', '区（/县）', '渠道码 ', '营业厅','活跃率/%');
        
        Csv::getCvsObj($params)->export();
    }
    
}