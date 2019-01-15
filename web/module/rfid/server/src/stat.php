<?php

/**
 * alltosun.com 统计模块 stat.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王敬飞 (wangjf@alltosun.com) $
 * $Date: 2017年6月7日 上午11:01:15 $
 * $Id$
 */
class stat
{
    /**
     * 更新记录表
     * @param unknown $rfid_record_detail 记录详情
     */
    public function update_record($rfid_record_detail)
    {

        if (!$rfid_record_detail) {
            return array('errno' => 1001, 'msg' => '更新记录时详情不存在[1]');
        }


        $filter = array(
            'business_id'   => $rfid_record_detail['business_id'],
            'area_id'       => $rfid_record_detail['area_id'],
            'city_id'       => $rfid_record_detail['city_id'],
            'province_id'   => $rfid_record_detail['province_id'],
            'phone_name'    => "'" . $rfid_record_detail['phone_name'] . "'",
            'phone_version' => "'" . $rfid_record_detail['phone_version'] . "'",
            'phone_color'   => "'" . $rfid_record_detail['phone_color'] . "'",
            'label_id'      => "'" . $rfid_record_detail['label_id'] . "'",
            'date'          => date('Ymd', $rfid_record_detail['start_timestamp'])
        );

        $rfid_record_info = AutoLoad::instance('model')->get_info('rfid_record', $filter);

        //为提高容错率，取消叠加方式, 直接按天统计体验时长
        $detail_filter = $filter;
        $detail_filter['status'] = 1;
        $detail_filter['end_timestamp >'] = 0;


        $remain_times = AutoLoad::instance('model')->get_fields_sum('rfid_record_detail', 'remain_time', $detail_filter);

        //存在当日的记录
        if ($rfid_record_info) {
            $update_data = array(
                'experience_time' => $remain_times
            );

            //查询设备数
            return AutoLoad::instance('model')->update('rfid_record', $rfid_record_info['id'], $update_data);
            //不存在当日的记录
        } else {
            $new_data = $filter;
            $new_data['experience_time'] = $remain_times;
            $new_data['add_time'] = "'" . date('Y-m-d H:i:s') . "'";
            return AutoLoad::instance('model')->create('rfid_record', $new_data);
        }

    }

    /**
     * 实时更新统计
     * @param unknown $rfid_record_detail 记录详情
     * @return number[]|string[]|unknown|boolean
     */
    public function update_stat($rfid_record_detail)
    {

        if (!$rfid_record_detail) {
            return array('errno' => 1001, 'msg' => 'update_stat error: rfid_record_detail not null.');
        }

        $filter = array(
            'date_for_day' => date('Ymd', $rfid_record_detail['start_timestamp']),
            'business_id'  => $rfid_record_detail['business_id']
        );

        //stat表
        $stat_info = AutoLoad::instance('model')->get_info('rfid_stat', $filter);

        //为提高容错率，取消叠加方式, 直接按天统计体验时长
        $record_filter = array(
            'date'        => $filter['date_for_day'],
            'business_id' => $rfid_record_detail['business_id'],
        );

        $new_experience_time = AutoLoad::instance('model')->get_fields_sum('rfid_record', 'experience_time', $record_filter);
        $device_num = AutoLoad::instance('model')->get_total('rfid_record', $record_filter);

        if (!$stat_info) {
            //创建
            $new_data = $filter;
            $new_data['province_id'] = $rfid_record_detail['province_id'];
            $new_data['city_id'] = $rfid_record_detail['city_id'];
            $new_data['area_id'] = $rfid_record_detail['area_id'];
            $new_data['date_for_week'] = (int)(date('Y', $rfid_record_detail['start_timestamp']) . date('W', $rfid_record_detail['start_timestamp']));
            $new_data['date_for_month'] = (int)(date('Ym', $rfid_record_detail['start_timestamp']));
            $new_data['device_num'] = $device_num;
            $new_data['experience_time'] = $new_experience_time;
            $new_data['add_time'] = "'" . date('Y-m-d H:i:s') . "'";
            $result = AutoLoad::instance('model')->create('rfid_stat', $new_data);

        } else {
            //更新
            $update_data['device_num'] = $device_num;
            $update_data['experience_time'] = $new_experience_time;
            $result = AutoLoad::instance('model')->update('rfid_stat', $stat_info['id'], $update_data);
        }

        if ($result === false) {
            return array('errno' => 1001, 'msg' => 'update_stat error:statistical write failure.');
        }


        return $result;


    }

    /**
     * 更新按小时的统计
     * @param unknown $rfid_record_detail
     */
    public function update_stat_hour($rfid_record_detail)
    {

        if (!$rfid_record_detail) {
            return array('errno' => 1001, 'msg' => 'update_stat_hour error:rfid_record_detail not null.');
        }

        $filter = array(
            'date_for_day'  => (int)date('Ymd', $rfid_record_detail['start_timestamp']),
            'date_for_hour' => (int)date('H', $rfid_record_detail['start_timestamp']),
            'business_id'   => (int)$rfid_record_detail['business_id']
        );

        //为提高容错率，取消叠加方式, 直接按天统计体验时长
        $detail_filter = array(
            'date'               => $filter['date_for_day'],
            'business_id'        => $rfid_record_detail['business_id'],
            'start_timestamp >=' => strtotime(date('Y-m-d H:00:00', $rfid_record_detail['start_timestamp'])),
            'start_timestamp <=' => strtotime(date('Y-m-d H:59:59', $rfid_record_detail['start_timestamp'])),
            'status'             => 1,
            'end_timestamp >'    => 0
        );

        $remain_times = AutoLoad::instance('model')->get_fields_sum('rfid_record_detail', 'remain_time', $detail_filter);

        //hour表
        $hour_stat_info = AutoLoad::instance('model')->get_info('rfid_stat_hour', $filter);
        //创建或更新hour统计表
        if (!$hour_stat_info) {
            //创建
            $new_data = $filter;
            $new_data['province_id'] = $rfid_record_detail['province_id'];
            $new_data['city_id'] = $rfid_record_detail['city_id'];
            $new_data['area_id'] = $rfid_record_detail['area_id'];
            $new_data['date_for_week'] = date('Y', $rfid_record_detail['start_timestamp']) . date('W', $rfid_record_detail['start_timestamp']);
            $new_data['date_for_month'] = date('Ym', $rfid_record_detail['start_timestamp']);
            $new_data['device_num'] = 1;
            $new_data['experience_time'] = $remain_times;
            $new_data['add_time'] = "'" . date('Y-m-d H:i:s') . "'";

            $result = AutoLoad::instance('model')->create('rfid_stat_hour', $new_data);

        } else {


            //查询设备数
            $count = AutoLoad::instance('model')->get_total('rfid_record_detail', $detail_filter, '*', ' GROUP BY label_id, phone_name, phone_version, phone_color');

            //更新
            $update_data = array();
            $update_data['device_num'] = $count;
            $update_data['experience_time'] = $remain_times;
            $result = AutoLoad::instance('model')->update('rfid_stat_hour', $hour_stat_info['id'], $update_data);
        }

        if ($result === false) {
            return array('errno' => 1001, 'msg' => 'update_stat_hour error:stat write fail');
        }


        return $result;

    }
}