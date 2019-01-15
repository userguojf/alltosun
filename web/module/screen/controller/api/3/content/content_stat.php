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
    // 轮播图统计接口
    public function add_content_stat()
    {
        $user_number      = tools_helper::post ( 'user_number', '' );
        $device_unique_id = tools_helper::post ( 'device_unique_id', '' );
        $res_info         = tools_helper::post ( 'info', '' );

        // 验证接口
        $check_params = array ();

        $api_log_id = api_helper::check_sign ( $check_params, 0 );

        //wangjf 2018-01-08 暂时关闭此接口
        api_helper::return_api_data(1000, 'success', array('info'=>'ok'), $api_log_id);

        if (! $device_unique_id) {
            api_helper::return_api_data ( 1003, '设备唯一标识不能为空' );
        }

        if (! $user_number) {
            api_helper::return_api_data ( 1003, '请输入营业厅的视图编码', array (), $api_log_id );
        }

        if (! $device_unique_id) {
            api_helper::return_api_data ( 1003, '设备唯一标识不能为空', array (), $api_log_id );
        }

        // 营业停信息
        $business_info = business_hall_helper::get_business_hall_info ( array (
                'user_number' => $user_number
        ) );

        if (! $business_info) {
            api_helper::return_api_data ( 1003, '营业厅不存在', array (), $api_log_id );
        }

        //wangjf add 2017-12-22
        $device_info = screen_device_helper::get_device_info_by_device($device_unique_id);

        if ( !$device_info ) {
            api_helper::return_api_data(1003, '管理员不存在', array(), $api_log_id);
        }

        // 公共条件
        $info = array (
                'device_unique_id'  => $device_unique_id,
                'province_id' => ( int ) $business_info ['province_id'],
                'city_id'     => ( int ) $business_info ['city_id'],
                'area_id'     => ( int ) $business_info ['area_id'],
                'business_id' => ( int ) $business_info ['id'],
             // 'day'         => ( int ) date ( "Ymd" ),
        );

        $new_info = json_decode ( htmlspecialchars_decode ( $res_info ), true );

        //wanjf add 取后50条
        $max = 50;
        //取后50条
        $count = count($new_info);
        if ($count > $max) {
            $new_info = array_slice($new_info, $count-$max);
        }

        //循环多条轮播数据
        foreach ( $new_info as $k => $v ) {

            if ( empty($v ['content_id']) ) {
                continue;
            }

            // android毫秒改成php秒时间戳记录
            $time_stamp = ( int ) substr($v ['time'], 0, 10);

            $stat_day_info = $record_info = $info;

            // 按天统计
            $stat_day_info['content_id'] = ( int ) $v ['content_id'];
            $stat_day_info['day']        = ( int ) date("Ymd", $time_stamp);

            // 记录条件
            $record_info['content_id']  = ( int ) $v ['content_id'];
            $record_info['day']         = ( int ) date('Ymd');
            $record_info ['click_time'] = $time_stamp;
            $record_info ['roll_sum']   = ( int ) $v['roll_sum'];
            $record_info ['add_time']   = date('Y-m-d H:i:s');

            // 轮播记录
            _mongo ('screen', 'screen_content_click_record')->insertOne ($record_info);

            // 上报时间大于今天统计略过
            if ( $stat_day_info['day'] > date('Ymd') ) {
                continue;
            }

            //添加天统计
            $this->stat_day($v, $stat_day_info);

            // 添加设备的轮播统计
            $this->device_stat($device_info, $v['content_id'], $v['roll_sum'], $time_stamp);

        }

        api_helper::return_api_data ( 1000, 'success', array ( 'info' => 'ok' ), $api_log_id );
    }

    /**
     * 按天统计
     * @param unknown $info
     * @param unknown $stat_day_filter
     * @return boolean
     */
    private function stat_day($info, $stat_day_filter)
    {

//         $stat_result = _mongo ( 'screen', 'screen_content_click_stat_day' )->findOne ( $stat_day_filter );

        $stat_result = _mongo ( 'screen', 'screen_content_click_stat_day' )->findOne (
            array(
                    'device_unique_id' => $stat_day_filter['device_unique_id'],
                    'business_id'      => ( int )$stat_day_filter['business_id'],
                    'content_id'       => ( int )$stat_day_filter['content_id'],
                    'day'              => ( int )$stat_day_filter['day']
            )
         );

        //更新
        if ( $stat_result ) {
            _mongo ( 'screen', 'screen_content_click_stat_day' )->updateOne (
                    array (
                            '_id' => $stat_result ['_id']
                    ),
                    array (
                            '$set' => array (
                                    'action_num'  => ( int )($stat_result ['action_num'] + $info['roll_sum']),
                                    'update_time' => date ( 'Y-m-d H:i:s' ) //更新更新时间
                            )
                    ) );
        } else {
            //添加
            $stat_day_filter['status']      = ( int ) 1;
            $stat_day_filter['action_num']  = ( int ) $info['roll_sum'];
            $stat_day_filter['add_time']    = date ( 'Y-m-d H:i:s' );
            $stat_day_filter['update_time'] = date ( 'Y-m-d H:i:s' );

            _mongo ( 'screen', 'screen_content_click_stat_day' )->insertOne ( $stat_day_filter );
        }

        return true;
    }

    /**
     * 按设备统计
     * @param unknown $device_info
     * @param unknown $content_id
     * @param unknown $roll_sum
     * @param unknown $time_stamp
     * @return boolean
     */
    private function device_stat( $device_info, $content_id, $roll_sum, $time_stamp)
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
            _mongo ( 'screen', 'screen_roll_device_stat' )->updateOne ( array (
                    '_id' => $stat_info ['_id']
            ), array (
                    '$set' => array (
                            'roll_num' => ( int ) $stat_info ['roll_num'] + ( int ) $roll_sum,
                            'update_time' => date ( 'Y-m-d H:i:s' )  // 更新更新时间
                                     )
            ) );
        }
    }
}