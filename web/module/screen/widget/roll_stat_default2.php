<?php
/**
 * alltosun.com 轮播图的统计接口 index.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-9-17 下午12:55:11 $
 * $Id$
 */
class roll_stat_widget
{
    /**
     * 时间 门店量 设备量 轮播量
     */
    public function screen_roll_stat() 
    {
        set_time_limit ( 0 );

        $date = date ( 'Ymd' );
        $table = 'screen_content_click_record';

        // 获取记录结束ID
        $mg_id = $this->mg_id_get ( $table, $date );

        // 分页处理数据
        $page_size = 300;

        // 获取记录结束ID
        $mg_id = $this->mg_id_get ( $table, $date );

        // 拼装条件
        if ( !$mg_id ) {
            $filter = array( 'day'   => ( int ) $date );
        } else {
            $filter = array( 
                    'day' => ( int ) $date,
                    '_id' => array('$gt' => new  MongoDB\BSON\ObjectId($mg_id))
             );
        }

        $list = _mongo ( 'screen', 'screen_content_click_record' )->find (
                    $filter ,array( 'limit' => $page_size )
                );

        // 转化位数组
        $data = $list->toArray();

        // 没有终止计划任务
        if ( empty($data) ) exit ('数据为空');

        // 循环里注意字段roll_sum
        foreach ( $data as $k => $v ) {

            $this->count_stat_roll_num( $v['content_id'], $date, $v['roll_sum']); 
            // 设备统计表存储
            $this->device_stat ( $v, $date );

            // 设备统计
            $stat_record_info = _mongo ( 'screen', 'screen_content_click_record' )->findOne ( 
                array (
                    '_id'              => array('$lt' => new  MongoDB\BSON\ObjectId($v['_id'])),
                    'content_id'       => (int)$v ['content_id'],
                    'business_id'      => (int)$v ['business_id'],
                    'device_unique_id' => $v ['device_unique_id'],
                    'day'              => (int)$date
                ),
                 array('sort' => array('_id'=> -1))
             );

            if (!empty($stat_record_info->toArray())) {
                $is_eq_device = true;
            } else {
                $is_eq_device = false;
            }

            // 营业厅统计表存储
            $this->yyt_stat ( $v, $date, $is_eq_device );
            // 最后一条的_id
            $mg_id = $v['_id'];
        }

        // 更新ID记录表
        $this->mg_id_record($table, $date, $mg_id, '/screen/widget/roll_stat.php');
    }

    /**
     * 营业厅统计
     * 营业厅 设备数 轮播数 时间
     */
    public function yyt_stat($v, $date, $is_eq_device)
    {
        $yyt_stat_info = _model ( 'screen_roll_business_stat' )->read ( array (
                'content_id'       => $v ['content_id'],
                'business_hall_id' => $v ['business_id'],
                'date'             => $date 
        ) );
        
        if (! $yyt_stat_info) {
            _model ( 'screen_roll_business_stat' )->create ( array (
                    'content_id'  => $v ['content_id'],
                    'province_id' => $v ['province_id'],
                    'city_id'     => $v ['city_id'],
                    'area_id'     => $v ['area_id'],
                    'business_hall_id' => $v ['business_id'],
                    'device_num'       => 1,
                    'roll_num'         => $v['roll_sum'],
                    'date'             => $date 
            ) );
            // 总量统计表：门店量
            $this->count_stat ( $v ['content_id'], 'business_hall_num', $date, $v['roll_sum'] );
        } else {
            // 营业厅统计
            if (! $is_eq_device) {
                _model ( 'screen_roll_business_stat' )->update ( $yyt_stat_info ['id'], 
                "SET `roll_num` = roll_num + {$v['roll_sum']} ,`device_num` = device_num + 1" );
            } else {
                $roll_num = $v['roll_sum'];

                _model ( 'screen_roll_business_stat' )->update ( $yyt_stat_info ['id'], 
                    "SET `roll_num` = roll_num + {$roll_num} " );
            }
        }

        return true;
    }

    /**
     * 设备统计
     * 设备 营业厅 轮播数 时间
     */
    public function device_stat($v, $date)
    {
        $device_stat_info = _model ( 'screen_roll_device_stat' )->read ( array (
                'content_id'       => $v ['content_id'],
                'business_hall_id' => $v ['business_id'],
                'device_unique_id' => $v ['device_unique_id'],
                'date'             => $date
        ) );

        if (! $device_stat_info) {
            _model ( 'screen_roll_device_stat' )->create ( array (
                    'content_id'  => $v ['content_id'],
                    'province_id' => $v ['province_id'],
                    'city_id'     => $v ['city_id'],
                    'area_id'     => $v ['area_id'],
                    'business_hall_id' => $v ['business_id'],
                    'device_unique_id' => $v ['device_unique_id'],
                    'roll_num'         => $v['roll_sum'],
                    'date'             => $date 
            ) );

            // 总量统计表：设备量
            $this->count_stat ( $v ['content_id'], 'device_num', $date);
        } else {
            // 营业厅统计
            $roll_num = $v['roll_sum'];

            _model ( 'screen_roll_device_stat' )->update ( $device_stat_info ['id'],
                " SET `roll_num` = roll_num + {$roll_num} " );
        }
        
        return true;
    }

    /**
     * 总体统计
     * 时间   轮播数
     */
    public function count_stat_roll_num($content_id, $date, $roll_num)
    {
        $count_stat_info = _model ( 'screen_roll_count_stat' )->read ( array (
                'content_id' => $content_id,
                'date'       => $date 
        ) );

        if (! $count_stat_info) {
            _model ( 'screen_roll_count_stat' )->create ( array (
                    'content_id' => $content_id,
                    "roll_num"   => $roll_num,
                    'date'       => $date 
            ) );
        } else {
            _model ( 'screen_roll_count_stat' )->update ( $count_stat_info ['id'], 
                "SET `roll_num` = roll_num + {$roll_num} " );
        }
        
        return true;
    }

    /**
     * 总体统计
     * 时间  设备数  门店数 
     */
    public function count_stat($content_id, $field, $date)
    {
        $count_stat_info = _model('screen_roll_count_stat')->read(array('content_id' => $content_id, 'date' => $date));
    
        if (!$count_stat_info) {
            _model('screen_roll_count_stat')->create(
                array(
                    'content_id'        => $content_id,
                    "{$field}"          => 1,
                    'date'              => $date
                    )
                );

        } else {
             _model('screen_roll_count_stat')->update($count_stat_info['id'], 
                "SET `{$field}` = {$field} + 1 ");
        }

        return true;
    }

    // /////////////////////////////////////////////////////////////////////////
    /**
     * 
     * @param unknown $table
     * @param unknown $date
     * @param unknown $mg_id
     * @param unknown $file_name
     * @return boolean
     */
    public function mg_id_record($table, $date, $mg_id, $file_name)
    {
        $mg_id_info = _model ( 'screen_page_record' )->read ( array (
                'table_name' => $table,
                'file_name'  => $file_name ,
                'date'       => $date 
        ) );

        if ($mg_id_info && $mg_id_info ['mg_id']) {
            _model ( 'screen_page_record' )->update ( $mg_id_info ['id'], array ( 'mg_id' => $mg_id  ) );
        } else {
            _model ( 'screen_page_record' )->create ( array (
                    'table_name' => $table,
                    'date'       => $date,
                    'mg_id'      => $mg_id,
                    'file_name'  => $file_name 
            ) );
        }

        return true;
    }

    /**
     * 记录ID获取
     * 
     * @param string $table
     * @param string $date
     * @return unknown number
     */
    public function mg_id_get($table, $date)
    {
        $mg_id_info = _model ( 'screen_page_record' )->read ( array ( 'table_name' => $table, 'date' => $date ) );

        if ($mg_id_info) {
            return $mg_id_info['mg_id'];
        }

        return '';
    }
}
