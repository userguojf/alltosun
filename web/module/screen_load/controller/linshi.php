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

        $arr[7]['start_date'] = 20180301;
        $arr[7]['end_date']   = 20180331;

        $arr[8]['start_date'] = 20180401;
        $arr[8]['end_date']   = 20180430;

        $list = [];
        for ( $i = 1; $i <= 8; $i ++  ) {

            $sql  = " SELECT device_unique_id,  province_id, business_id ,COUNT(*) AS num FROM `screen_device_online_stat_day`";
            $sql .= " WHERE day >= {$arr[$i]['start_date']} AND day <= {$arr[$i]['end_date']}";
            $sql .= " GROUP BY device_unique_id ORDER BY `num` DESC";

            $device_active_list = _model('screen_device_online_stat_day')->getAll($sql);

            $month_active_num = 0;

            foreach ($device_active_list as $k => $v) {
                if ( !isset($list[$i][$v['province_id']]) ) {
                    $list[$i][$v['province_id']] = array(
                            'yyt_ids'    => [],
                            'device_num' => 0,
                            'device_all_num' => 0
                    );
                }

                // 当月的有效设备总数
                if ( !$list[$i][$v['province_id']]['device_all_num'] ) {
                    $month_total = _model('screen_device')->getTotal(
                            array(
                                    'province_id' => $v['province_id'],
                                    'day <=' => $arr[$i]['end_date'],
                                    'status' => 1
                            )
                    );

                    $list[$i][$v['province_id']]['device_all_num'] = $month_total;
                }

                if ( $v['num'] < 15 ) continue;

                // 活跃设备数累加
                ++ $list[$i][$v['province_id']]['device_num'];

                // 有效营业厅 start
                if ( !in_array($v['business_id'], $list[$i][$v['province_id']]['yyt_ids']) ) {
                    array_push($list[$i][$v['province_id']]['yyt_ids'], $v['business_id']);
                }

            }

        }

        $j = 0;
        $data = [];

        foreach ( $list as  $k => $v ) {

            $date = substr($arr[$k]['start_date'], 0, 6 );
            foreach ( $v as $key => $val ) {

                $data[$j]['date']     = $date;
                $data[$j]['province'] = screen_helper::by_id_get_field($key, 'province', 'name');
                $data[$j]['yyt_ids']  = count($val['yyt_ids']);
                $data[$j]['device_num']     = $val['device_num'];
                $data[$j]['device_all_num'] = $val['device_all_num'];

                ++ $j;
            }
        }

        $head = array( '日期', '省份', '手机亮屏有效门店数', '终端月活跃数', '有效累计终端数');

        $params['filename'] = '亮屏个月详情';

        $params['data']     = $data;
        $params['head']     = $head;
// p($params);exit();
        Csv::getCvsObj($params)->export();
    }

}