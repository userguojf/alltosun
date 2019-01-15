<?php

/**
 * alltosun.com 焦点图点击统计 content_stat.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年7月31日 下午4:35:35 $
 * $Id$
 */
class Action
{

    public function add_content_stat()
    {
        $user_number        = tools_helper::post('user_number', '');
        $device_unique_id   = tools_helper::post('device_unique_id', '');
        $res_info           = tools_helper::post('info', '');

        // 验证接口
        $check_params = array(
        );

        $api_log_id = api_helper::check_sign($check_params, 0);

        //2018-01-08 暂时关闭此接口
        //api_helper::return_api_data(1000, 'success', array('info'=>'ok'), $api_log_id);

        if (!$device_unique_id) {
            api_helper::return_api_data(1003, '设备唯一标识不能为空');
        }

        if (!$user_number) {
            api_helper::return_api_data(1003, '请输入营业厅的视图编码', array(), $api_log_id);
        }

        if (!$device_unique_id) {
            api_helper::return_api_data(1003, '设备唯一标识不能为空', array(), $api_log_id);
        }

        //营业停信息
        $business_info  = business_hall_helper::get_business_hall_info(array('user_number' => $user_number));
        //an_dump($business_info);

        if (!$business_info) {
            api_helper::return_api_data(1003, '营业厅不存在', array(), $api_log_id);
        }

        //wangjf add 2017-12-22
        $device_info = screen_device_helper::get_device_info_by_device($device_unique_id);

        if ( !$device_info ) {
            api_helper::return_api_data(1003, '管理员已下架该设备', array(), $api_log_id);
        }

        // 公共条件
        $info  = array(
                    'device_unique_id' => $device_unique_id,
                    'province_id'      => (int)$business_info['province_id'],
                    'city_id'          => (int)$business_info['city_id'],
                    'area_id'          => (int)$business_info['area_id'],
                    'business_id'      => (int)$business_info['id'],
                 // 'day'              => (int)date("Ymd")
        );

        $new_info = json_decode(htmlspecialchars_decode($res_info), true);

        //wanjf add 取后50条
        $max = 50;
        //取后50条
        $count = count($new_info);
        if ($count > $max) {
            $new_info = array_slice($new_info, $count-$max);
        }

        foreach ($new_info as $k => $v) {
            if ( $v['res_id'] ) {
                $info['content_id'] = (int)$v['res_id'];
            } else {
                continue;
            }

            if ( $v['res_name'] ) {
                $info['res_name'] = $v['res_name'];
            } else {
                continue;
            }
// p($v);
// p($info);exit();
            if ( !$v['click_time'] ) continue;

            $stat_filter = $record_filter = $info;

            // 转化为时间戳
            $time_stamp = strtotime($v['click_time']);

            // 记录条件
            $record_filter['click_time'] = $time_stamp;
            $record_filter['add_time'] = date('Y-m-d H:i:s');
            $record_filter['roll_sum'] = (int) 1;
            $record_filter['day']      = date('Ymd');

            // 记录表
            _mongo('screen', 'screen_content_click_record')->insertOne($record_filter);

            // 统计条件
            $stat_filter['day'] = date('Ymd', $time_stamp);

            // 上报时间大于今天统计略过
            if ( $stat_filter['day'] > date('Ymd') ) {
                continue;
            }

            // 统计方法
            $this->roll_stat_day($stat_filter);

            // 添加设备的轮播统计
            $this->device_stat( $device_info, $v['res_id'], 1, $time_stamp);

        }

        api_helper::return_api_data(1000, 'success', array('info'=>'ok'), $api_log_id);
    }

    /**
     *
     * @param unknown $stat_filter
     */
    public function roll_stat_day($stat_filter)
    {
        // 按天统计
        $stat_res = _mongo ( 'screen', 'screen_content_click_stat_day' )->findOne ( $stat_filter );

        if ( $stat_res ) {
            $id = _mongo ( 'screen', 'screen_content_click_stat_day' )->updateOne (
                    array (
                            '_id' => $stat_res ['_id']
                    ),
                    array (
                            '$set' => array (
                                    'action_num'  => $stat_res ['action_num'] + 1,
                                    'update_time' => date ( 'Y-m-d H:i:s' ) //更新更新时间
                            )
                    ) );
        } else {
            // 状态添加和更新时间
            $stat_filter ['status']     = ( int ) 1;
            //创建时间和更新时间
            $stat_filter ['add_time']    =  date ( 'Y-m-d H:i:s' );
            $stat_filter ['update_time'] = date ( 'Y-m-d H:i:s' );
            $stat_filter ['action_num']  = 1;

            _mongo ( 'screen', 'screen_content_click_stat_day' )->insertOne ( $stat_filter );
        }
    }
    /**
     * 添加轮播的设备统计表
     * @param array $device_info
     * @param int   $content_id
     * @param int   $roll_sum
     * @return boolean
     */
    public function device_stat($device_info, $content_id, $roll_sum, $time_stamp)
    {
        if ( !$content_id || !$roll_sum ) return false;

        $stat_info = _mongo('screen', 'screen_roll_device_stat')->findOne(
                array(
                        'content_id'       => ( int )$content_id,
                        'business_hall_id' => ( int )$device_info['business_id'],
                        'device_unique_id' => $device_info['device_unique_id'],
                        'date'             => ( int )date('Ymd', $time_stamp)
                    )
        );

        if ( !$stat_info ) {
            $stat_info = _mongo ( 'screen', 'screen_roll_device_stat' )->insertOne (
                    array(
                            'content_id'       => ( int )$content_id,
                            'province_id'      => ( int )$device_info['province_id'],
                            'city_id'          => ( int )$device_info['city_id'],
                            'area_id'          => ( int )$device_info['area_id'],
                            'business_hall_id' => ( int )$device_info['business_id'],
                            'device_unique_id' => $device_info['device_unique_id'],
                            'roll_num'         => ( int )$roll_sum,
                            'date'             => ( int )date('Ymd', $time_stamp),
                            'add_time'         => date('Y-m-d H:i:s'),
                            'update_time'      => date('Y-m-d H:i:s')
                    )
            );
        } else {
            _mongo ( 'screen', 'screen_roll_device_stat' )->updateOne ( array ( '_id' => $stat_info ['_id'] ),
                    array (
                            '$set' => array (
                                    'roll_num'    => ( int )$stat_info ['roll_num'] + ( int )$roll_sum,
                                    'update_time' => date ( 'Y-m-d H:i:s' ) //更新更新时间
                            )
                    )
            );
        }
    }
}