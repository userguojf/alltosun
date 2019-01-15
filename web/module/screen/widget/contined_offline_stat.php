<?php
/**
 * alltosun.com  contined_offline_stat.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-12-6 下午4:17:40 $
 * $Id$
 */
class contined_offline_stat_widget 
{
    public function stat()
    {
        set_time_limit ( 0 );

        // 分页处理数据
        $page_size = 1000;

        $date  = date ( 'Ymd' );
        $table = 'screen_device_online_stat_day';

        // 获取记录结束ID
        $id = $this->id_get ( $table, $date );
        // 条件
        $filter = array( 'online_time' => 0, 'OR offline_of_time >' => 0, 'AND date' => $date );
        // 获取设备的列表
        $list = _model ( $table )->getList ( $filter, " LIMIT $page_size" );

        // 没有终止计划任务
        if (! $list) {
            exit ('暂无数据');
        }

        foreach ( $list as $k => $v ) {
            $param =  array (
                    'province_id' => $v ['province_id'],
                    'city_id'     => $v ['city_id'],
                    'area_id'     => $v ['area_id'],
                    'business_hall_id' => $v ['business_id'],
                    'device_unique_id' => $v ['device_unique_id'],
                    'day' => $date 
            );

            if ( !$list['offline_of_time']  ) {
                $param['time'] = strtotime(date('Y-m-d H:i:s'));
            } else {
                $param['time'] = $list['offline_of_time'];
            }

            // 在线统计
            $stat_info = _model ( 'screen_continued_offline_record' )->create ( $param );
            // 最后一条要更新的ID
            $op_id = $v ['id'];
        }

        // 更新ID记录表
        $this->id_record ( $table, $date, $op_id );
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

    /**
     * ID更新操作表
     * @param string $table
     * @param string $date
     * @param int $id
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
                    'date' => $date 
            ) );
        }

        return true;
    }
}