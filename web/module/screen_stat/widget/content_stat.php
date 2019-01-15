<?php
/**
  * alltosun.com 内容统计widget content_stat.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2018年5月4日 下午4:45:11 $
  * $Id$
  */
class content_stat_widget
{
    /**
     * 内容按天统计
     * @param unknown $info
     * @param unknown $stat_day_filter
     * @return boolean
     */
    public function stat_content_day($param)
    {

        if (!isset($param['info']) || !isset($param['stat_day_filter'])) return false;

        $info               = $param['info'];
        $stat_day_filter    = $param['stat_day_filter'];
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
                                    'update_time' => date ( 'Y-m-d H:i:s' ) //更新更新时间
                            ),
                            '$inc' => array('action_num' => (int)$info['roll_sum'])
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
     * 内容按设备统计
     * @param unknown $device_info
     * @param unknown $content_id
     * @param unknown $roll_sum
     * @param unknown $time_stamp
     * @return boolean
     */
    public function stat_content_device($param)
    {
        if (!isset($param['device_info']) || !isset($param['content_id']) || !isset($param['roll_sum']) || !isset($param['time_stamp'])) return false;

        $device_info    = $param['device_info'];
        $content_id     = $param['content_id'];
        $roll_sum       = $param['roll_sum'];
        $time_stamp     = $param['time_stamp'];

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
                            'update_time' => date ( 'Y-m-d H:i:s' )  // 更新更新时间
                    ),
                    '$inc' => array('roll_num' => ( int ) $roll_sum)
            ) );
        }
    }
}