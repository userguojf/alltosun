<?php
/**
  * alltosun.com 亮屏统计helper screen_stat_helper.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年10月19日 上午11:37:21 $
  * $Id$
  */
class screen_stat_helper
{
    /**
     * 统计内容播放次数
     * @param int $res_name
     * @param int $res_id
     * return int
     */
    public static function get_content_stat_num($content_id, $res_name, $res_id)
    {
        if (!$content_id || !$res_name) {
            return 0;
        }

        if ( !in_array($res_name, screen_stat_config::$res_name_arr) ) return 0;

        $date = ( int )date('Ymd');

        if ( $res_name == 'group' ) {
            $match = array('content_id' => (int)$content_id , 'day' => $date);
        } else if ( $res_name == 'province' || $res_name == 'city' ) {
            $match =  array(
                    'content_id'    => (int)$content_id,
                    $res_name."_id" => (int)$res_id,
                    'day'           => $date
            );
        } else if ( $res_name == 'business_hall' ) {
            $match =  array(
                    'content_id'  => (int)$content_id,
                    "business_id" => (int)$res_id,
                    'day'         => $date
            );
        } else {
            return 0;
        }

        $filter = array(
                array(
                        '$match' => $match
                ),
                array(
                        '$group' => array(
                                '_id'  => array('content_id' => '$content_id'),
                                'num'  => array('$sum' => '$action_num'),
                        )
                )
        );
// p($filter);
        $stat_info = _mongo('screen', 'screen_content_click_stat_day')->aggregate( $filter );

        $info = $stat_info->toArray();
// p($info);exit();
        if ( !$info ) return 0;

        return $info[0]['num'];
        //edited by guojf
    }

    /**
     * 取屏幕资源的点击统计
     * @param int $res_id
     * return int
     */
    public static function get_content_res_click_total($res_id)
    {
        if (!$res_id) {
            return 0;
        }

        $result       = _mongo('screen', 'screen_click_record')->aggregate(array(
                array('$match' => array('res_id'=>(int)$res_id)),
                array('$group' => array(
                        '_id'               => array(
                            'device_unique_id'  => '$device_unique_id',
                        ),

                        'count'  => array('$sum' => '$click_num'),
                )),
                array('$sort' => array('day'=>-1))
        ));

        $count = 0;
        foreach ($result as $k => $v) {
            $v = (array)$v;
            $count += $v['count'];
        }
        return $count;
        //return _mongo('screen', 'screen_click_record')->count(array('res_id'=>(int)$res_id));
        //return _model('screen_click_record')->getTotal(array('res_id'=>$res_id));
    }

    // 今日设备量统计
    public static function get_today_count_num($content_id)
    {
        if (!$content_id)  return 0;

        $list = _mongo('screen', 'screen_roll_device_stat')->find(
                array(
                        'content_id' => (int)$content_id,
                        'date'       => (int)date('Ymd')
                    )
        );

        $list = $list->toArray();

        if ( !$list ) return false;

        $arr['date']         = date('Ymd');
        $arr['roll_num']     = 0;
        $arr['device_num']   = 0;
        $arr['business_num'] = 0;

        $b_target = array();

        foreach ( $list as $k => $v ) {
            $arr['roll_num'] = $arr['roll_num'] + $v['roll_num'];
            $arr['device_num'] = $arr['device_num'] + 1;

            if ( !in_array($v['business_hall_id'], $b_target) ) {
                $arr['business_num'] = $arr['business_num'] + 1;

                array_push($b_target, $v['business_hall_id']) ;
            }

        }

        return $arr;
    }

    // 今日设备量统计
    public static function get_today_business_num($content_id, $date)
    {
        if (!$content_id || !date)  return array();

        if ($date != date('Ymd')) return array();

        if (!$content_id)  return array();

        $list = _mongo('screen', 'screen_roll_device_stat')->find(
                array(
                        'content_id' => (int)$content_id,
                        'date'       => (int)$date
                    )
        );

        $list = $list->toArray();

        if ( !$list ) return false;

        $arr = array();

        foreach ( $list as $k => $v ) {
            if (!isset($arr[$v['business_hall_id']])) {
                $arr[$v['business_hall_id']]['roll_num'] = 0;
                $arr[$v['business_hall_id']]['device_num'] = 0;

                $arr[$v['business_hall_id']]['roll_num']   = $v['roll_num'];
                $arr[$v['business_hall_id']]['device_num'] = 1;
            } else {
                $arr[$v['business_hall_id']]['roll_num']   +=  $v['roll_num'];
                $arr[$v['business_hall_id']]['device_num'] += 1;
            }
        }
// p($arr);exit();
        return $arr;
    }

    /**
     * 整天离线和连续5小时离线的统计
     * @param array $param  设备表的字段
     * @param int   $type   类型
     * @return boolean
     *
     */
    public static function device_offline_stat($param , $type, $date)
    {
        if ( !$param || !is_array($param) || !$type || !$date ) return false;

        $yesterday = date('Ymd', strtotime($date) - 24 * 60);
        $today     = $date;

        $stat_info = _model('screen_offline_series_stat')->read(array(
                'device_unique_id' => $param['device_unique_id'],
                'install_date'     => $param['day'],
                'type'             => $type,
                'date'             => $yesterday
        ));

        if ( !$stat_info ) {
            _model('screen_offline_series_stat')->create( array(
                'province_id'      => $param['province_id'],
                'city_id'          => $param['city_id'],
                'area_id'          => $param['area_id'],
                'business_hall_id' => $param['business_id'],
                'device_unique_id' => $param['device_unique_id'],
                'install_date'     => $param['day'],
                'type'             => $type,
                'date'             => $today,
                'offline_num'      => 1
            ) );
        } else {
            _model('screen_offline_series_stat')->create( array(
                'province_id'      => $param['province_id'],
                'city_id'          => $param['city_id'],
                'area_id'          => $param['area_id'],
                'business_hall_id' => $param['business_id'],
                'device_unique_id' => $param['device_unique_id'],
                'install_date'     => $param['day'],
                'type'             => $type,
                'date'             => $today,
                'offline_num'      => $stat_info['offline_num'] + 1
            ) );
        }

        return true;
    }

    /**
     * 以营业厅维度的统计
     * @param unknown $param
     * @param unknown $field
     * @return boolean
     */
    public static function business_hall_device_stat($param, $field, $date )
    {
        if ( !$param || !is_array($param) || !in_array($field, array('offline_num', 'online_num')) || !$date) {
            return false;
        }

        $yesterday = date('Ymd', strtotime($date) - 24 * 60);
        $today     = $date;
        $table     = 'screen_business_device_num_stat';

        $business_stat_info = _model($table)->read( array(
                'business_hall_id' => $param['business_id'],
                'date'             => $today
        ));

        if ( !$business_stat_info ) {
            if ( $date == $param['day'] ) {
                $filter = array(
                    'province_id'      => $param['province_id'],
                    'city_id'          => $param['city_id'],
                    'area_id'          => $param['area_id'],
                    'business_hall_id' => $param['business_id'],
                    'all_num'          => 1,
                    "{$field}"         => 1,
                    'install_num'      => 1,
                    'date'             => $today,
                );
            } else {
                 $filter = array(
                    'province_id'      => $param['province_id'],
                    'city_id'          => $param['city_id'],
                    'area_id'          => $param['area_id'],
                    'business_hall_id' => $param['business_id'],
                    'all_num'          => 1,
                    "{$field}"         => 1,
                    'date'             => $today,
                );
            }
            _model($table)->create($filter);
        } else {
            if ( $date == $param['day'] ) {
                $update_filter = array(
                    'all_num'  => $business_stat_info['all_num'] +1,
                    "{$field}" => $business_stat_info[$field] +1,
                    'install_num' => $business_stat_info['install_num'] +1,
                );
            } else {
                $update_filter = array(
                        'all_num'  => $business_stat_info['all_num'] +1,
                        "{$field}" => $business_stat_info[$field] +1,
                );
            }
            _model($table)->update(
                array('id' => $business_stat_info['id']),
                $update_filter
            );
        }

        return true;
    }

    /**
     * 获取离线厅店
     */
    public static function get_screen_off_line_data($start_time,$end_time)
    {

    }

    /**
     * 数组条件转换where语句
     * @param unknown $filter
     * @return string
     */
    public static function to_where_sql($filter)
    {
        if (!$filter) {
            return '';
        }

        $where = '';

        if (is_array($filter)) {

            foreach ($filter as $k => $v) {

                if ( !$where ) {
                    $where = " WHERE ";
                }

                if ( strpos($k, '<') || strpos($k, '>') ) {
                    $where .= " {$k}{$v} AND";
                } else {
                    $where .= " {$k}={$v} AND";
                }

            }

            $where = rtrim($where, 'AND');
        } else {

            if ( !$where ) {
                $where = " WHERE ";
            }

            $where .= "id={$filter} ";
        }

        return $where;
    }

    /**
     * 获取某年第几周的开始日期和结束日期
     * @param int $year
     * @param int $week 第几周;
     */
    public static function get_day_by_week($year,$week=1){
        $year_start = mktime(0,0,0,1,1,$year);
        $year_end = mktime(0,0,0,12,31,$year);

        // 判断第一天是否为第一周的开始
        if (intval(date('W',$year_start))===1){
            $start = $year_start;//把第一天做为第一周的开始
        }else{
            $start = strtotime('+1 monday',$year_start);//把第一个周一作为开始
        }

        // 第几周的开始时间
        if ($week===1){
            $weekday['start'] = $start;
        }else{
            $weekday['start'] = strtotime('+'.($week-0).' monday',$start);
        }

        // 第几周的结束时间
        $weekday['end'] = strtotime('+1 sunday',$weekday['start']);
        if (date('Y',$weekday['end'])!=$year){
            $weekday['end'] = $year_end;
        }
        return $weekday;
    }

    /**
     * 导出今天之前的营业厅安装量
     * 备注：今天下载昨天的
     * @param int $date
     */
    public static function export_busienss_device($date, $res_name, $res_id )
    {
        if ( !$date ) $date = date('Ymd', time() - 24 * 3600 );
        if ( !$res_name ) return false;

        if ( $res_name == 'group' ) {
            $filter = array('date' => $date);
            $order  = 'province_id';
        } else if ( $res_name == 'province' ) {
            $filter = array('date' => $date, 'province_id' => $res_id);
            $order  = 'city_id';
        } else if ( $res_name == 'city' ) {
            $filter = array('date' => $date, 'city_id' => $res_id);
            $order  = 'area_id';
        } else if ( $res_name == 'area' ) {
            $filter = array('date' => $date, 'area_id' => $res_id);
            $order  = 'business_hall_id';
        } else if ( $res_name == 'business_hall' ) {
            $filter = array('date' => $date, 'business_hall_id' => $res_id);
            $order  = 'id';
        } else {
            return 'member表字段';
        }

        $business_device_info = _model('screen_business_device_num_stat')->getList($filter, " ORDER BY {$order} ASC ");

        if ( !$business_device_info )  exit('暂无统计信息');

        foreach ($business_device_info as $k => $v) {
            $list[$k]['province'] = screen_helper::by_id_get_field($v['province_id'], 'province', 'name');
            $list[$k]['city']     = screen_helper::by_id_get_field($v['city_id'], 'city', 'name');
            $list[$k]['area']     = screen_helper::by_id_get_field($v['area_id'], 'area', 'name');

            $list[$k]['business_hall'] = screen_helper::by_id_get_field($v['business_hall_id'],'business_hall', 'title');
            $list[$k]['user_number']   = screen_helper::by_id_get_field($v['business_hall_id'],'business_hall', 'user_number');
            $list[$k]['install_num']   = $v['all_num'];
        }

        $params['filename'] = '营业厅设备安装量';
        $params['data']     = $list;
        $params['head']     = array('省', '市', '地区', '营业厅', '渠道码', '安装量');

        Csv::getCvsObj($params)->export();
    }

    /**
     * 取套餐弹出量和点击量
     * @param int $res_id
     * @param int $type 1 弹出量 2点击量
     * return int
     */
    public static function get_meal_stat_num($res_id, $type=1, $start_date='', $end_date='', $run_time= '')
    {
        if (!$res_id || !$type) {
            return 0;
        }

        // 弹出
        if ($type == 1) {
            $table = 'screen_content_meal_pop_stat_day';
            $field = 'pop_num';
        } else {
            // 点击
            $table = 'screen_content_meal_stat_day';
            $field = 'action_num';
        }

        if ($run_time) {
            $field = $run_time;
        }

        $filter['content_meal_id'] = $res_id;
        if ($start_date && $end_date) {
            $filter['day >='] = date('Ymd', strtotime($start_date));
            $filter['day <='] = date('Ymd', strtotime($end_date));
        }
        return array_sum(_model($table)->getFields($field, $filter));
    }

    public static function get_history_date($id)
    {
        if ( !$id ) return '';
        $date_arr = _model('screen_daily_hebave_report_date_record')->getFields(
                'date', array('daily_behave_id' => $id));

        if ( !$date_arr ) return '无上报日志';

        return implode('<br>', $date_arr);
    }
    
    public static function get_device_version($device_unique_id, $type)
    {
        if ( !$device_unique_id || !$type) return false;

        $version_no_arr = _model('screen_device_version_record')->read(
                array(
                    'device_unique_id' => $device_unique_id,
                    'type' => $type
                    )
        );

        if ( !$version_no_arr ) return false;
    
        return $version_no_arr;
    }
    
    public static function get_app_version($business_id, $device_unique_id, $type)
    {
        if ( !$business_id || !$device_unique_id ) return false;

        $info = _model('screen_device')->read(
            array(
                    'business_id' => $business_id,
                    'device_unique_id' => $device_unique_id,
            )
        );

        if ( !$info ) return false;
        
        if ( 1 == $type ) {
            return $info['version_no'];
        } elseif( 2 == $type ) {
            return $info['sys_version'];
        } else {
            return false;
        }
    }

    public static function  get_last_activity_time($business_id, $device_unique_id)
    {
        if ( !$business_id || !$device_unique_id ) return false;

        $filter = [
            'business_id'      => (int)$business_id,
            'device_unique_id' => $device_unique_id,
        ];

        $sort = [
            'sort' => ['_id' => -1]
        ];

        $list = _mongo('screen', 'screen_device_online')->findOne($filter, $sort);

        if ( $list && $list['add_time'] ) {
            return $list['add_time'];
        }

        return false;
    }

    public static function get_seven_date($device_unique_id)
    {
        if ( !$device_unique_id ) return false;

        $date_arr = [];
        $time = time() -  8 * 24 * 3600;

        for ( $i = 0; $i < 7; $i++ ) {
            $date = date('Ymd', $time + $i * 24 * 3600);
            $everyday_info = _model('screen_daily_behave_happening_record')->read(
                    array(
                            'device_unique_id' => $device_unique_id,
                            'record_day' => $date,
                         )
            );

            if ( !$everyday_info ) {
                $date_arr[$date] = '异常';
                continue;
            }

            if ( !$everyday_info['auto_start'] && ( $everyday_info['heart_hour_num'] > 7 || $everyday_info['heart_hour_num'] > $seven_list['time_num']) ) {
                $date_arr[$date] = '正常';
            } else {
                $date_arr[$date] = '异常';
            }
        }

        return $date_arr;
    }

    public static function get_device_info($device_unique_id)
    {
        if ( !$device_unique_id ) return false;

        $info = _model('screen_device')->read(array('device_unique_id' => $device_unique_id),
                " ORDER BY `id` DESC ");

        if (!$info) return false;

        $arr = [];
        $arr['name'] = $info['phone_name_nickname']?:$info['phone_name'];
        $arr['version'] = $info['phone_version_nickname']?:$info['phone_version'];
        $yyt_info = _model('business_hall')->read(array('id' => $info['business_id']));
        $arr['title'] = $yyt_info ? $yyt_info['title'] : '营业厅信息不存在';
        $arr['install_date'] = $info['day'];
        $arr['active_time']  = self::get_last_activity_time($info['business_id'], $device_unique_id);

        return $arr;
    }

}