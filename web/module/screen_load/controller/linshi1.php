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
        set_time_limit(0);

        $start_day = tools_helper::get('start_day', 0);//'20180501';
        $end_day   = tools_helper::get('end_day', 0);//'20180531';

        if ( !$start_day || !$end_day ) {
            return '参数错误';
        }

        $device_sql  = "SELECT province_id,COUNT(*) as device_num FROM `screen_device` ";
        $device_sql .= " WHERE `status`=1 AND `day` <= '{$end_day}' GROUP BY `province_id`";

        $province_list = _model('screen_device')->getAll($device_sql);

        $list = array();

        foreach ( $province_list as $k => $v) {
            //查询有效终端
//             $list[$v['province_id']] = $v;

            $list[$v['province_id']]['device_month_activie_num'] = 0;
            $list[$v['province_id']]['device_month_valid_num']   = $v['device_num'];
            $list[$v['province_id']]['yyt_valid_num']  = 0;
            $list[$v['province_id']]['yyt_list'] = [];
        }

        $stat_sql  = " SELECT device_unique_id,  province_id, business_id ,COUNT(*) AS num FROM ";
        $stat_sql .= "`screen_device_online_stat_day`";
        $stat_sql .= " WHERE day >={$start_day} AND day <={$end_day}" ;
        $stat_sql .= " GROUP BY device_unique_id ORDER BY `num` DESC";

        $stta_list = _model('screen_device_online_stat_day')->getAll($stat_sql);

        foreach ($stta_list as $k => $v) {

            if ($v['num'] >= 15) {
                $list[$v['province_id']]['device_month_activie_num'] += 1;

                if (isset($list[$v['province_id']]['yyt_list']) && in_array($v['business_id'], $list[$v['province_id']]['yyt_list'])) {
                    continue;
                }

                $list[$v['province_id']]['yyt_list'][] = $v['business_id'];
                $list[$v['province_id']]['yyt_valid_num'] += 1;
            }
        }

        $j = 0;
        $data = [];

        foreach ( $list as $key => $val ) {
            $data[$j]['date']     = $start_day . '—' . $end_day;
            $data[$j]['province'] = screen_helper::by_id_get_field($key, 'province', 'name');
            $data[$j]['yyt_valid_num'] = $val['yyt_valid_num'];
            $data[$j]['device_month_activie_num'] = $val['device_month_activie_num'];
            $data[$j]['device_month_valid_num']   = $val['device_month_valid_num'];

            ++ $j;
        }

// p($data);
// exit();
        $head = array( '日期', '省份', '手机亮屏有效门店数', '终端月活跃数', '有效累计终端数');

        $params['filename'] = '亮屏个月详情';

        $params['data']     = $data;
        $params['head']     = $head;

        Csv::getCvsObj($params)->export();
    }

}