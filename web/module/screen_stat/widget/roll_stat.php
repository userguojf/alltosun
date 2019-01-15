<?php
/**
 * alltosun.com  roll_stat.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-12-14 上午11:38:13 $
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

// 今天取昨天的
        $date = date ( 'Ymd',time() - 3600*24 );
        $table = 'screen_roll_device_stat';

        // 获取记录结束ID
        $mg_id = $this->mg_id_get ( $table, $date );
// p($mg_id);exit();
        // 每次处理数据
        $limit_num = 500;

        // 拼装查询条件
        $match = array( 
                '_id'      => array( '$gt' => new  MongoDB\BSON\ObjectId( $mg_id ) ),
                'add_time' => array( '$lt' => date('Y-m-d 00:00:00') )
         );

        $list = _mongo ( 'screen', $table )->find ( $match, array( 'limit' => ( int )$limit_num ));

        // 转化位数组
        $data = $list->toArray();

        // 没有终止计划任务
        if ( empty($data) ) exit ('数据为空');

        // 循环里注意字段roll_sum
        foreach ( $data as $k => $v ) {

            // 总体统计
            $this->count_stat( $v );

            // 营业厅统计表存储
            $this->yyt_stat ( $v );

            // 按省统计
            $this->province_stat( $v );

            // 按市统计
            $this->city_stat( $v );

            // 最后一条的_id
            $mg_id = $v['_id'];
        }

        // 更新ID记录表
        $this->mg_id_record($table, $date, $mg_id, '/screen/widget/roll_stat.php');

        echo '完成';
    }

    /**
     * 营业厅统计
     * 营业厅 设备数 轮播数 时间
     */
    public function yyt_stat( $v )
    {

        $yyt_stat_info = _model ( 'screen_roll_business_stat' )->read ( array (
                'content_id'       => $v ['content_id'],
                'business_hall_id' => $v ['business_hall_id'],
                'date'             => $v['date']
        ) );

        if (! $yyt_stat_info) {
            _model ( 'screen_roll_business_stat' )->create ( array (
                    'content_id'  => $v ['content_id'],
                    'province_id' => $v ['province_id'],
                    'city_id'     => $v ['city_id'],
                    'area_id'     => $v ['area_id'],
                    'business_hall_id' => $v ['business_hall_id'],
                    'device_num'       => 1,
                    'roll_num'         => $v['roll_num'],
                    'date'             => $v['date']
            ) );
            // 总量统计表：门店量
            $this->count_business_hall_stat ( $v ['content_id'], $v['date'] );
        } else {
            // 营业厅统计
            _model ( 'screen_roll_business_stat' )->update ( $yyt_stat_info ['id'], 
                "SET `roll_num` = roll_num + {$v['roll_num']} , `device_num` = device_num + 1" );
        }

        return true;
    }

    /**
     * 总体统计
     * 营业厅数量的统计
     */
    public function count_business_hall_stat($content_id, $date)
    {
        $count_stat_info = _model ( 'screen_roll_count_stat' )->read ( array (
                'content_id' => $content_id,
                'date'       => $date
        ) );

        if (! $count_stat_info) {
            $count_stat_info = _model ( 'screen_roll_count_stat' )->create ( array (
                    'content_id'        => $content_id,
                    'business_hall_num' => 1,
//                     'device_num'        => 1,
//                     "roll_num"          => $v['roll_num'],
                    'date'              => $date
            ) );

        } else {
            _model ( 'screen_roll_count_stat' )->update ( $count_stat_info['id'],
             "SET `business_hall_num` = business_hall_num  + 1 " );
        }

        return true;
    }

    /**
     * 总体统计
     * 轮播数 设备数的统计
     */
    public function count_stat($v)
    {
        $count_stat_info = _model ( 'screen_roll_count_stat' )->read ( array (
                'content_id' => $v['content_id'],
                'date'       => $v['date']
        ) );

        if (! $count_stat_info) {
           $count_stat_info = _model ( 'screen_roll_count_stat' )->create ( array (
                'content_id'        => $v['content_id'],
                // 'business_hall_num' => 1,
                'device_num'        => 1,
                "roll_num"          => $v['roll_num'],
                'date'              => $v['date']
            ) );

        } else {
            _model ( 'screen_roll_count_stat' )->update ( $count_stat_info ['id'],
                "SET `roll_num` = roll_num + {$v['roll_num']}, `device_num` = device_num + 1 "  );
        }

        return true;
    }

    ///////////////////////////////////////// 按省、市统计start  //////////////////////////////////
    public function province_stat($v)
    {
        $table = 'screen_roll_province_stat';

        $province_stat_info = _model ( $table )->read ( array (
                'content_id'  => $v['content_id'],
                'province_id' => $v['province_id'],
                'date'        => $v['date']
        ) );

        if (! $province_stat_info) {
             _model ( $table )->create ( array (
                    'content_id'  => $v['content_id'],
                    'province_id' => $v['province_id'],
                    'device_num'  => 1,
                    "roll_num"    => $v['roll_num'],
                    'date'        => $v['date']
            ) );

        } else {
            _model ( $table )->update ( $province_stat_info ['id'],
            "SET `roll_num` = roll_num + {$v['roll_num']}, `device_num` = device_num + 1 "  );
        }
        return true;
    }

    public function city_stat($v)
    {
        $table = 'screen_roll_city_stat';

        $city_stat_info = _model ( $table )->read ( array (
                'content_id'  => $v['content_id'],
                'province_id' => $v['province_id'],
                'city_id'     => $v['city_id'],
                'date'        => $v['date']
        ) );

        if (! $city_stat_info) {
            _model ( $table )->create ( array (
                'content_id'  => $v['content_id'],
                'province_id' => $v['province_id'],
                'city_id'     => $v['city_id'],
                'device_num'  => 1,
                "roll_num"    => $v['roll_num'],
                'date'        => $v['date']
                ) );

        } else {
            _model ( $table )->update ( $city_stat_info ['id'],
            "SET `roll_num` = roll_num + {$v['roll_num']}, `device_num` = device_num + 1 "  );
        }

        return true;
    }
    ///////////////////////////////////////// 按省、市统计end  //////////////////////////////////

    ///////////////////////////////////////// 分页记录start  //////////////////////////////////
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
    ///////////////////////////////////////// 分页记录end  //////////////////////////////////
}
