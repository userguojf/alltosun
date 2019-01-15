<?php
/**
  * alltosun.com rfid导出文件 export.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2018年5月2日 下午6:18:45 $
  * $Id$
  */
class Action
{
    /**
     * 根据省导出设备统计
     */
    public function export_device_stat_by_province()
    {
        $sql = " SELECT COUNT(*) as device_num, province_id FROM `rfid_label` GROUP BY `province_id` ";
        //根据省获取设备量
        $counts = _model('rfid_label')->getAll($sql);

        $date = strtotime('-1 month');
        $date = strtotime('-1 month', strtotime('2018-01-01 00:00:00'));
        $start_date = date('Ym01', $date);


        $data = array();
        foreach ($counts as $k => $v) {
            $province_name = business_hall_helper::get_info_name('province', $v['province_id'], 'name');
            if (!$province_name) {
                continue;
            }

            //查询总营业厅数
            $business_total = _model('rfid_label')->getFields('business_hall_id', array(
                    'province_id' => $v['province_id'],
            ), 'GROUP BY business_hall_id');


            //查询本月活跃设备
            $active_count = _model('rfid_online_stat_day')->getTotal(array(
                    'province_id' => $v['province_id'],
                    'day >=' => $start_date,
                    'day <' => date('Ym01', strtotime( '+1 month', strtotime( date('Ym01', $date) ) ) ),
            ));

            //查询本月活跃营业厅数
            $business_count = _model('rfid_online_stat_day')->getFields('business_id', array(
                    'province_id' => $v['province_id'],
                    'day >=' => date('Ym01', $date),
                    'day <' => date('Ym01', strtotime( '+1 month', strtotime( date('Ym01', $date) ) ) ),
            ), 'GROUP BY business_id');

            //查询本月每天活跃营业厅数
            $business_count_active = _model('rfid_online_stat_day')->getFields('business_id', array(
                    'province_id' => $v['province_id'],
                    'day >=' => date('Ym01', $date),
                    'day <' => date('Ym01', strtotime( '+1 month', strtotime( date('Ym01', $date) ) ) ),
            ), ' GROUP BY `business_id`, `day` ');


            //日均设备活跃率
            $active_rote = round($active_count / 30 / $v['device_num'] * 100, 4);

            //日均营业厅活跃率
            $active_business_rote = round(count($business_count_active) / 30 / count($business_total) * 100, 4);

            $data[] = array(
                    'data' => date('Y/m/01', $date),
                    'province' => $province_name,
                    'total'    => count($business_total),
                    'active'   => count($business_count),
                    'count'     => $v['device_num'],
                    'active_business_rote' => $active_business_rote,
                    'active_rote' => $active_rote.'%',

            );
        }

        $hand = array(
                '账期',
                '省份',
                'RFID总厅店数',
                'RFID活跃厅店数',
                'RFID标签数（台）',
                'RFID日均门店活跃率',
                'RFID日均设备活跃率',

        );
        $params['filename'] = 'RFID统计';
        $params['data']     = $data;
        $params['head']     = $hand;
        Csv::getCvsObj($params)->export();
    }

    /**
     * 根据省导出设备统计
     */
    public function export_device_stat_by_province2()
    {

        $sql = " SELECT COUNT(*) as device_num, province_id FROM `rfid_label` GROUP BY `province_id` ";
        //根据省获取设备量
        $counts = _model('rfid_label')->getAll($sql);

        $date = strtotime('-1 month');

        $start_date = date('Ym01', $date);

        $data = array();
        foreach ($counts as $k => $v) {
            $province_name = business_hall_helper::get_info_name('province', $v['province_id'], 'name');
            if (!$province_name) {
                continue;
            }

            //查询总营业厅数
            $business_total = _model('rfid_label')->getFields('business_hall_id', array(
                    'province_id' => $v['province_id'],
            ), 'GROUP BY business_hall_id');

            //查询本月活跃营业厅数
            $business_active_count = _model('rfid_online_stat_day')->getFields('business_id', array(
                    'province_id' => $v['province_id'],
                    'day >=' => date('Ym01', $date),
                    'day <' => date('Ym01', strtotime( '+1 month', strtotime( $start_date ) ) ),
            ), 'GROUP BY business_id');

            //查询设备
            $device_list = _model('rfid_label')->getList(array(
                    'province_id' => $v['province_id'],
            ));

            $device_active_day_reate = 0;
            $business_list = array();
            foreach ($device_list as $k1 => $v1) {
                $a = 30;
                $add_day = date('Ymd', strtotime($v1['add_time']));
                if ( $add_day > $start_date ) {
                    $a = date('Ymd', strtotime( '+1 month', strtotime( $start_date ) ) - 24*3600)  - $add_day + 1;
                }
                //计算设备日均活跃率
                $filter = array(
                        'province_id' => $v1['province_id'],
                        'day >=' => $start_date,
                        'day <' => date('Ym01', strtotime( '+1 month', strtotime( $start_date ) ) ),
                        'label_id' => $v1['label_id'],
                );
                //获取设备活跃天数
                $device_active_day_count = _model('rfid_online_stat_day')->getTotal($filter);
                //设备日均活跃率
                $device_active_day_reate += round($device_active_day_count / $a * 100, 2);

                //营业厅列表
                if (empty($business_list[$v1['business_hall_id']]) || $business_list[$v1['business_hall_id']] > $v1['add_time']) {
                    $business_list[$v1['business_hall_id']] = $v1['add_time'];
                }
            }

            //日均设备活跃率
            $device_active_rote = round($device_active_day_reate / $v['device_num'], 2);

            $business_active_day_reate = 0;
            foreach ($business_list as $k1 => $v1) {
                $a = 30;
                $add_day = date('Ymd', strtotime($v1));
                if ( $add_day > $start_date ) {
                    $a = date('Ymd', strtotime( '+1 month', strtotime( $start_date ) ) - 24*3600)  - $add_day + 1;
                }
                //活跃总天数
                $business_active_day_count = count(_model('rfid_online_stat_day')->getFields('business_id', array(
                        'business_id' => $k1,
                        'day >=' => $start_date,
                        'day <' => date('Ym01', strtotime( '+1 month', strtotime( $start_date ) ) ),
                ), ' GROUP BY `business_id`, `day` '));
                $business_active_day_reate += round($business_active_day_count / $a * 100, 2);

            }

//p($device_active_day_reate, $business_active_day_reate);
            //日均营业厅活跃率
            $business_active_rote = round($business_active_day_reate / count($business_total), 2);

            $data[] = array(
                    'data' => date('Y/m/01', $date),
                    'province' => $province_name,
                    'total'    => count($business_total),
                    'active'   => count($business_active_count),
                    'count'     => $v['device_num'],
                    'business_active_rote' => $business_active_rote.'%',
                    'device_active_rote' => $device_active_rote.'%',

            );
        }
        $hand = array(
                '账期',
                '省份',
                'RFID总厅店数',
                'RFID活跃厅店数',
                'RFID标签数（台）',
                'RFID日均门店活跃率',
                'RFID日均设备活跃率',

        );
        $params['filename'] = 'RFID统计';
        $params['data']     = $data;
        $params['head']     = $hand;
        Csv::getCvsObj($params)->export();
    }
}
