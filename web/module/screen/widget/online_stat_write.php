<?php
/**
 * alltosun.com  online_stat_write.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-12-6 下午3:25:02 $
 * $Id$
 */
class online_stat_write_widget 
{
    public function write()
    {
        set_time_limit ( 0 );

        // 分页处理数据
        $page_size = 1000;

        $date  = date ( 'Ymd' );
        $table = 'screen_device';

        // 获取记录结束ID
        $id = $this->id_get ( $table, $date );
// p($id);exit();
        // 获取设备的列表
        $list = _model ( $table )->getList ( array ( 'id >' => $id, 'status' => 1 ), " LIMIT $page_size" );

        // 没有终止计划任务
        if (! $list) exit ('暂无数据');

        $op_id = 0;

        foreach ( $list as $k => $v ) {
            $filter       = array ( 'device_unique_id' => $v['device_unique_id'], 'day' => $date );
            $is_have_stat = _model ( 'screen_device_online_stat_day' )->read ( $filter );

            // 最后一条要更新的ID
            $op_id = $v ['id'];

            if ( $is_have_stat ) {
                continue;
            }
            // 在线统计
            $stat_info = _model ( 'screen_device_online_stat_day' )->create ( array (
                    'province_id' => $v ['province_id'],
                    'city_id'     => $v ['city_id'],
                    'area_id'     => $v ['area_id'],
                    'business_id' => $v ['business_id'],
                    'device_unique_id' => $v ['device_unique_id'],
                    'day'              => $date 
            ) );
        }

        if ($op_id) {
            // 更新ID记录表
            $this->id_record ( $table, $date, $op_id );
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
                    'date'          => $date 
            ) );
        }

        return true;
    }
}