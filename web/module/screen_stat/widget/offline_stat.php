<?php
/**
 * alltosun.com  offline_stat.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-12-14 下午3:04:42 $
 * $Id$
 */
/**
 * 注：每天7点分页跑设备表 screen_device
 * 统计表
 * screen_everyday_offline_record
 * screen_business_device_num_stat 连续离线活跃2  离线离线不活跃1
 * screen_offline_series_stat      连续离线活跃2  离线离线不活跃1
 * @author 郭剑峰
 *
 */

class offline_stat_widget
{
    // 每天轮询设备表
    public function roll_poling_device()
    {
        $table = 'screen_device';
        $date  = date('Ymd'); // - 24 * 3600

        $id = $this->id_get($table, $date);
        // 每次出来数据量
        $limit_num = 500;

        $list = _model($table)->getList( array( 'status' => 1, 'id >'=> $id,  ), " LIMIT {$limit_num} " );

        if ( !$list ) exit('暂无数据');

        foreach ( $list as $k => $v ) {
            // 是否活跃
            $this->find_online_stat_day( $v, $date );
            // 记录最后一条ID值
            $id = $v['id'];
        }

        // 记录ID
        $this->id_record($table, $date, $id);

        return true;
    }

    /**
     * 查看在线统计表
     * @param string $device_unique_id
     * @return boolean
     */
    public function find_online_stat_day( $v, $date )
    {
        if ( !is_array($v) || !$v || !$date ) return false;

        $online_info = _model('screen_device_online_stat_day')->read(
                array(
                    'business_id'      => $v['business_id'],
                    'device_unique_id' => $v['device_unique_id'],
                    'day'              => $date,
                )
        );

        // 离线三种情况
        // 1、今日根本没有上报的设备
        if ( !$online_info ) {
            $this->create_all_day_offline_record( $v, $date );
            // 以营业厅的设备全量统计
            screen_stat_helper::business_hall_device_stat($v, 'offline_num', $date);
            return true;
        }

        // 2、接口已经统计出的离线5小时以上
        if ( $online_info['offline_of_time'] ) {
            $this->create_5_hours_offline_record($v, $online_info['offline_time'], $online_info['offline_of_time'], $date);
            // 以营业厅的设备全量统计
            screen_stat_helper::business_hall_device_stat($v, 'offline_num', $date);
            return true;
        }

        // 3、计划任务跑的时候   允许设备这样
        $offline_time = strtotime($date.' 19:30:00') - strtotime( $online_info['update_time'] );
        // 五个个小时间隔没有上报更新
        if ( !$online_info['offline_of_time'] && $offline_time > 5 * 60 *60 ) {
            $this->create_5_hours_offline_record( $v, $offline_time, strtotime($online_info['update_time']), $date );
            // 以营业厅的设备全量统计
            screen_stat_helper::business_hall_device_stat($v, 'offline_num', $date);
            return true;
        }

        // 以营业厅的设备全量统计
        screen_stat_helper::business_hall_device_stat($v, 'online_num', $date);

        return true;
    }

    /**
     * 整体离线记录
     * @param array $v 设备信息
     */
    public function create_all_day_offline_record( $v, $date )
    {

        _model('screen_everyday_offline_record')->create( array(
                'province_id'      => $v['province_id'],
                'city_id'          => $v['city_id'],
                'area_id'          => $v['area_id'],
                'business_hall_id' => $v['business_id'],
                'device_unique_id' => $v['device_unique_id'],
                'phone_name'       => $v['phone_name'],
                'phone_version'    => $v['phone_version'],
                'device_nickname_id' => $v['device_nickname_id'],
                'all_day'          => 1,
                'date'             => $date,
                'offline_time'     => 8 * 60 * 60,
                'offline_of_time'  => strtotime(date('Ymd').' 08:00:00')
        ) );

        // 统计
        screen_stat_helper::device_offline_stat($v, 1, $date);
    }
 
    /**
     * 连续5小时离线记录
     * @param array $v
     * @param int   $offline_time
     * @param int   $offline_of_time
     */
    public function create_5_hours_offline_record( $v, $offline_time, $offline_of_time, $date)
    {
        _model('screen_everyday_offline_record')->create( array(
                'province_id'      => $v['province_id'],
                'city_id'          => $v['city_id'],
                'area_id'          => $v['area_id'],
                'business_hall_id' => $v['business_id'],
                'device_unique_id' => $v['device_unique_id'],
                'phone_name'       => $v['phone_name'],
                'phone_version'    => $v['phone_version'],
                'device_nickname_id' => $v['device_nickname_id'],
                'date'             => $date,
                'offline_time'     => $offline_time,
                'offline_of_time'  => $offline_of_time
        ) );
        // 统计
        screen_stat_helper::device_offline_stat($v, 2, $date);
    }


    ////////////////////////////////////ID记录//////////////////////////////////////
    /**
     * ID更新操作表
     * 
     * @param string $table
     * @param string $date
     * @param int    $id
     * @return boolean
     */
    public function id_record($table, $date, $id)
    {
        $id_info = _model ( 'screen_id_record' )->read ( array (
                'data_table' => $table,
                'date'       => $date 
        ) );

        if ($id_info && $id_info ['data_table_id']) {
            _model ( 'screen_id_record' )->update ( $id_info ['id'], array (
                    'data_table_id' => $id 
            ) );
        } else {
            _model ( 'screen_id_record' )->create ( array (
                    'data_table'    => $table,
                    'data_table_id' => $id,
                    'date'          => $date 
            ) );
        }

        return true;
    }

    /**
     * 记录ID获取
     * @param string $table 
     * @param string $date
     * @return unknown number
     */
    public function id_get($table, $date)
    {
        $id_info = _model ( 'screen_id_record' )->read ( array (
                'data_table' => $table,
                'date'       => $date 
        ) );

        if ($id_info) {
            return $id_info ['data_table_id'];
        }

        return 0;
    }
}