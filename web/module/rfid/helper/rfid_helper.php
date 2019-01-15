<?php
/**
  * alltosun.com rfid_helper.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年5月10日 下午1:59:07 $
  * $Id$
  */
class rfid_helper
{

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
     * 获取手机某个字段信息
     * @param string $field
     * @param array  $param
     * @return boolean|multitype:
     */
    public static function get_phone_field_info($field , $name , $version)
    {
        if (!$field) {
            return false;
        }

        $filter = array();

        if (!$name && !$version) {
            $filter = array(1 => 1);
        }

        if ($name && !$version) {
            $filter = array('name' => $name);
        }

        if ($name && $version) {
            $filter = array('name' => $name , 'version' => $version);
        }

        $phone_name_info = _model('rfid_phone')->getFields($field , $filter);

        if ($phone_name_info) {
            return array_unique($phone_name_info);
        }

        return false;
    }

    /**
     * @param int $procince_id
     * @param int $city_id
     * @param int $area_id
     * @return array
     */
    public static function get_business_title($province_id , $city_id , $area_id)
    {
        if (!$province_id || !$city_id || !$area_id) {
            return false;
        }

        $business_hall_info = _model('business_hall')->getList(
                                    array(
                                        'province_id' => $province_id,
                                        'city_id'  => $city_id,
                                        'area_id'  => $area_id,
                                    )
                             );

        if (!$business_hall_info) {
            return false;
        }
        return $business_hall_info;
    }


    public static function get_shoppe_info($business_hall_id)
    {
        if (!$business_hall_id) {
            return false;
        }

        $shoppe_info = _model('rfid_shoppe')->getList(array('business_id' =>$business_hall_id, 'status' => 1));

        if (!$shoppe_info) {
            return false;
        }

        return $shoppe_info;
    }


    /**
     * 根据开始日期和结束日期获取月份列表
     * @param unknown $start_date 开始日期
     * @param unknown $end_date   结束日期
     */
    public static function get_month_list($search_filter)
    {
        //月时间戳
        $month_start_timestamp       = strtotime($search_filter['start_date']);
        $month_end_timestamp         = strtotime($search_filter['start_date']);

        $i      = $month_start_timestamp;
        while($i <= $month_end_timestamp){
            $date_list[] = date('Ym', $i);
            //加一个月
            $i = strtotime('+1 months', $i);
        }

        return $date_list;

    }

    /**
     * 根据每周对日期进行分组，格式：xxxx/xx/xx - xxxx/xx/xx
     * @param unknown $start_date 开始日期
     * @param unknown $end_date   结束日期
     */
    public static function get_group_week_list($search_filter)
    {

        //时间戳处理
        $start_timestamp = strtotime($search_filter['start_date']);
        $end_timestamp   = strtotime($search_filter['end_date']);

        //获取起始和结束日期的第一个周一日期和周日日期
        $start_week_info            = rfid_helper::get_day_by_week(date('Y'), date('W',$start_timestamp));
        $end_week_info              = rfid_helper::get_day_by_week(date('Y'), date('W',$end_timestamp));

        //处理日期显示
        $i                          = $start_week_info['start'];
        $dates = '';
        while($i <= $end_week_info['end']){
            //周一
            if (date('w', $i) == 1) {
                $dates .= date('Y/m/d', $i);
                //周日
            } else if (date('w', $i) == 0) {
                $dates .= '-'.date('Y/m/d', $i).',';
            }
            $i = strtotime('+1 day', $i);
        }

        $dates      = rtrim($dates, ',');
        $date_list  = explode(',',$dates);
        return $date_list;
    }

    /**
     * 根据开始日期和结束日期获取日期列表
     * @param unknown $start_date 开始日期
     * @param unknown $end_date   结束日期
     */
    public static function get_day_list($search_filter)
    {
        //月时间戳
        $start_timestamp       = strtotime($search_filter['start_date']);
        $end_timestamp         = strtotime($search_filter['end_date']);

        $i               = $start_timestamp;
        while($i <= $end_timestamp){
            $date_list[] = date('Ymd', $i);
            $i = strtotime('+1 day', $i);
        }
        return $date_list;
    }

    /**
     * 根据开始日期获取小时列表
     * @param unknown $start_date 开始日期
     */
    public static function get_hour_list($search_filter)
    {
        $start_date = $search_filter['start_date'];
        $i = strtotime($start_date);
        p($i);
        while($i < strtotime('+1 days', strtotime($start_date))){
            $date_list[] = date('H', $i);
            $i = strtotime('+1 hours', $i);
        }

        return $date_list;
    }




    /**
     * 解析统计所需的数据
     * @param unknown $list
     * @param unknown $date_type
     * @param unknown $start_date
     * @param unknown $end_date
     */
    public static function parse_stat($list, $search_filter)
    {

        $stat_list          = array();
        $stat_info          = array(
                'date_list'             => array(),
                'number_count_list'     => array(),
                'time_count_list'     => array(),
                'list'                  => array()
        );

        $date_type = $search_filter['date_type'];

        if ( $date_type == 'hour') {
            $date_list = rfid_helper::get_hour_list($search_filter);
        } else if ( $date_type == 'day' ) {
            $date_list = rfid_helper::get_day_list($search_filter);
        } else if ( $date_type == 'week' ) {
            $date_list = rfid_helper::get_group_week_list($search_filter);
        } else if ( $date_type == 'month' ) {
            $date_list = rfid_helper::get_month_list($search_filter);
        } else {
            return $stat_info;
        }

        //周的单独处理
        if ($date_type == 'week') {

            foreach ($list as $key => $value) {
                $week_date = '';
                foreach ($date_list as $date_k => $date_v) {

                    list($start, $end) = explode( '-',  $date_v);
                    $week       = substr($start, 0 , strpos($start, '/'));
                    $week       .= date('W', strtotime($start));
                    if ($week == $value['date_for_week']) {
                        if (isset($stat_list[$date_v]['count'])){
                            $stat_list[$date_v]['count'] += $value['device_num'];;
                        } else {
                            $stat_list[$date_v]['count'] = $value['device_num'];
                        }

                        if (isset($stat_list[$date_v]['time_count'])) {
                            $stat_list[$date_v]['time_count'] += $value['experience_time'];
                        } else {
                            $stat_list[$date_v]['time_count'] = $value['experience_time'];
                        }
                        break;
                    }
                }
            }

        } else {
            $field = 'date_for_'.$date_type;
            foreach ($list as $key => $value) {
                $date_key = array_search($value[$field], $date_list);

                if ($date_key === false) {
                    continue;
                }

                $date_time = $date_list[$date_key];

                if (isset($stat_list[$date_time]['count'])){
                    $stat_list[$date_time]['count'] += $value['device_num'];
                } else {
                    $stat_list[$date_time]['count'] = $value['device_num'];
                }

                if ($value[$field] == $date_time) {

                    if (isset($stat_list[$date_time]['time_count'])) {
                        $stat_list[$date_time]['time_count'] += $value['experience_time'];
                    } else {
                        $stat_list[$date_time]['time_count'] = $value['experience_time'];
                    }

                }

            }

        }

        ksort($stat_list);

        //组装日期和数据
        foreach ($stat_list as $k => $v) {
            if ($date_type == 'hour') {
                $k .= ":00";
            }
            $stat_info['date_list'][]           = $k;
            $stat_info['number_count_list'][]   = $v['count'];
            $stat_info['time_count_list'][]     = self::format_timestamp_i($v['time_count']);
            $v['time']                          = $k;
            $stat_info['list'][]                = $v;
        }

        return $stat_info;

    }




    /**
     * 格式化时间戳， 处理后的格式：00:00:00
     * @param unknown $timestamp
     */
    public static function format_timestamp($timestamp)
    {
        if (!$timestamp) {
            return '00:00:00';
        }
        $date = '';
        //计算小时
        $h = floor($timestamp/3600);
        if ($h < 10) {
            $h = '0'.$h;
        }

        //计算分钟
        $i_timestamp = $timestamp%3600;
        $i = floor($i_timestamp/60);
        if ($i < 10) {
            $i = '0'.$i;
        }

        //计算秒
        $s = $i_timestamp%60;
        if ($s < 10){
            $s = '0'.$s;
        }

        return $h.':'.$i.':'.$s;

    }

    /**
     * 格式化时间戳，处理后的格式：0.5
     * @param unknown $timestamp
     */
    public static function format_timestamp_text($timestamp)
    {
        if (!$timestamp) {
            return '0秒';
        }

        if ($timestamp >= 3600) {
            return round($timestamp/3600, 1).'小时';
        } else if ($timestamp >= 60) {
            return round($timestamp/60, 1).'分钟';
        } else {
            return $timestamp.'秒';
        }

    }



    /**
     * 解析为小时
     * @param unknown $timestamp
     */
    public static function format_timestamp_hour($timestamp)
    {
        if (!$timestamp) {
            return 0;
        }
        return round($timestamp/3600, 1);
    }

    /**
     * 解析为分钟
     * @param unknown $timestamp
     */
    public static function format_timestamp_i($timestamp)
    {
        if (!$timestamp) {
            return 0;
        }
        return round($timestamp/60, 1);
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
     * 创建日志
     * @param array $param
     */
    public static function create_api_log($param)
    {
        if (!is_array($param)) {
            return '数据数数组形式';
        }

        if (!in_array($param['type'], rfid_config::$dm_type)) {
            return false;
        }

        if (!isset($param['user_number']) || !$param['user_number']) {
            return false;
        }

        if (!isset($param['label_id']) || !$param['label_id']) {
            return false;
        }

        if (!isset($param['phone_name']) || !$param['phone_name']) {
            return false;
        }

        if (!isset($param['phone_version']) || !$param['phone_version']) {
            return false;
        }

        if (!isset($param['shoppe_id']) || !$param['shoppe_id']) {
            return false;
        }

        //app_id和app_key
        $dm_param = rfid_config::$dm_param;

        //创建接口
        if ('create' == $param['type']) {
            $request_url = rfid_helper::dm_create_url_choice();
        }

        //删除接口
        if ('delete' == $param['type']){
            $request_url = rfid_helper::dm_delete_url_choice();
        }

        $request_bean = '{"appId"         : "'.$dm_param['app_id'].'",
                           "brand"        : "'.$param['phone_name'].'",
                           "businessCode" : "'.$param['user_number'].'",
                           "probeNo"      : "'.$param['label_id'].'",
                           "shoppeId"     : "'.$param['shoppe_id'].'",
                           "probeType"    : 2,
                           "token"        : "'.md5($param['label_id'].$dm_param['app_key']).'",
                           "version"      : "'.$param['phone_version'].'"}';

        //传给数字地图
        $response_json = rfid_helper::dm_myself_post($request_url , $request_bean);

        //记录日志
        self::rfid_api_logs($param['type'], $response_json, $request_bean, $param['label_id']);
    }


    //记录日志
    public static function rfid_api_logs($res_name, $response_json, $request_data, $label_id)
    {
        $log_info = [];

        $log_info['res_name']     = $res_name;
        $log_info['request_data'] = $request_data;
        $log_info['label_id']     = $label_id;

        $response_info            = json_decode($response_json , true);

        if ($response_info && isset($response_info['httpStatus'])) {
            $log_info['response_code'] = $response_info['httpStatus'];
        } else {
            $log_info['response_code'] = 'No';
        }

        //json数据
        if ($response_info) {
            $log_info['response_body'] = $response_json;
        } else {
            $log_info['response_body'] = '接口请求超时（或没有返回）';
        }

        //创建日志
        _model('rfid_api_logs')->create($log_info);

        return false;
    }

    /**
     * 自定义的post请求
     * @param str $url
     * @param array $data
     */
    public static function dm_myself_post($url, $data)
    {
        $ch = curl_init();

        //对方要求header头
        $header = array(
                'Content-Type: application/json'
        );
        //设置cURL允许执行的最长毫秒数。
        curl_setopt($ch, CURLOPT_TIMEOUT_MS,2000);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch,  CURLOPT_FOLLOWLOCATION, 1);

        return curl_exec($ch);
    }


    /**
     * 删除字段的空格
     * @param  string  $field
     * @return string
     */
    public static function delete_blank($field)
    {
        if (!$field) {
            return false;
        }

        //替换空格
        if (strpos($field , " ")) {
            return  str_replace(" ", '', $field);
        }

        return $field;
    }

    /**
     * 要转换的字符串
     */
    public static $decode_str = array(
            '+' => '。'
    );

    /**
     * 转换url参数值， 主要手机型号字符中的 +号
     * @param unknown $str
     */
    public static function url_params_encode($str)
    {

        foreach ( self::$decode_str as $k => $v ) {
            $str = str_replace($k, $v, $str);
        }

        return $str;

    }

    /**
     * 解析转换后的url参数值， 主要手机型号字符中的 +号
     * @param unknown $str
     * @return mixed
     */
    public static function url_params_decode($str)
    {
        foreach ( self::$decode_str as $k => $v ) {
            $str = str_replace($v, $k, $str);
        }

        return $str;
    }

    /**
     * 根据标签获取imei
     */
    public static function get_imei_by_label($label_id)
    {
        return _uri('rfid_label', array('label_id' => $label_id), 'imei');
    }


    /**
     * 发生动作是否探测到用户
     */
    public static function is_probe_user($detail_id)
    {
        if (!$detail_id) {
            return false;
        }

        $detail_info = _uri('rfid_record_detail', $detail_id);

        if (!$detail_info) {
            return false;
        }

        $filter = self::get_probe_user_filter($detail_info);

        if (_uri('rfid_probe_user_record', $filter)){
            return true;
        }

        return false;


    }

    /**
     * 拼接获取探测到用户的filter条件
     * @param unknown $detail_info
     * @return unknown[]|number[]
     */
    public static function get_probe_user_filter($detail_info)
    {
        return  array(
                'up_time <=' => $detail_info['end_timestamp'],
                'up_time >' => strtotime(date('Y-m-d H:i', $detail_info['end_timestamp'])) - 60
        );
    }
    /**
     * 调用数字地图创建地址
     * @return string
     */
    public static function dm_create_url_choice()
    {
        if (!ONDEV) {
            return rfid_config::$dm_create_api_url;
        } else {
            return rfid_config::$test_dm_create_api_url;
        }
    }
    /**
     * 调用数字地图删除地址
     * @return string
     */
    public static function dm_delete_url_choice()
    {
        if (!ONDEV) {
            return rfid_config::$dm_delete_api_url;
        } else {
            return rfid_config::$test_dm_delete_api_url;
        }
    }

    /**
     * 获取营业厅覆盖量
     * @param unknown $member_info
     * @return number
     */
    public static function get_business_bestrow($member_info)
    {
        $filter = _widget('rfid')->default_search_filter($member_info);

        if (empty($filter)) {
            $filter[1] = 1;
        }

        $business_hall_ids = _model('rfid_label')->getFields('business_hall_id', $filter, " GROUP BY `business_hall_id` ");

        return count($business_hall_ids);
    }


    /**
     * 获取读写器状态码
     * @param unknown $count_num
     * @param unknown $online_num
     */
    public static function get_rwtool_status_code($count_num, $online_num)
    {
        if ($online_num >= $count_num) {
            //读写器状态 1 读写器正常(标签正常)
            return 1;
        } else if ($online_num > 0 && $online_num < $count_num) {
            //读写器状态 2 读写器正常(部分标签离线)
            return 2;
        } else if ($online_num == 0) {
            //读写器状态 6 读写器异常(全部标签离线)
            return 6;
        } else {
            return 0;
        }
    }

    /**
     * 获取终端体验排行
     * @param unknown $user_number 营业厅渠道编码
     * @param unknown $start_time  开始时间
     * @param unknown $end_time    结束时间
     * @param number $limit        取前多少个设备
     */
    public static function get_terminal_top($user_number, $start_date, $end_date, $limit=3)
    {

        $business_hall_info = business_hall_helper::get_business_hall_info(array('user_number' => $user_number));

        if (!$business_hall_info) {
            return array();
        }

        $member_info = _model('member')->read(array('member_user' => $user_number));

        if (!$member_info) {
            return array();
        }

        $filter = array(
                'date >'      => date('Ymd', strtotime($start_date)),
                'date <='      => date('Ymd', strtotime($end_date))
        );

        if ($member_info['res_name'] != 'group') {
            if ($member_info['res_name'] == 'business_hall') {
                $filter['business_id'] = $member_info['res_id'];
            } else {
                $filter[$member['res_name'].'_id'] = $member_info['res_id'];
            }
        }

        $where = self::to_where_sql($filter);

        $sql = "SELECT *, SUM(experience_time) AS experience_time_sum FROM rfid_record {$where} GROUP BY `phone_name`, `phone_version` ORDER BY experience_time_sum DESC LIMIT {$limit}";

        $list = _model('rfid_record')->getAll($sql);

        return $list;
    }

    /**
     * 获取指定机型的体验时长
     * @param unknown $user_number 营业厅渠道编码
     * @param unknown $start_time  开始时间
     * @param unknown $end_time    结束时间
     */
    public static function get_terminal_experience_time($user_number, $phone_name, $phone_version, $start_date, $end_date)
    {

        $business_hall_info = business_hall_helper::get_business_hall_info(array('user_number' => $user_number));

        if (!$business_hall_info) {
            return array();
        }

        $member_info = _model('member')->read(array('member_user' => $user_number));

        if (!$member_info) {
            return array();
        }

        $filter = array(
                        'phone_name'    => $phone_name,
                        'phone_version' => $phone_version,
                        'date >'        => date('Ymd', strtotime($start_date)),
                        'date <='        => date('Ymd', strtotime($end_date))
                );

        if ($member_info['res_name'] != 'group') {
            if ($member_info['res_name'] == 'business_hall') {
                $filter['business_id'] = $member_info['res_id'];
            } else {
                $filter[$member['res_name'].'_id'] = $member_info['res_id'];
            }
        }

        $experience_times = _model('rfid_record')->getFields('experience_time', $filter);

        if ( !$experience_times ) {
            return 0;
        }

        return array_sum($experience_times);
    }

    /**
     * 获取设备昵称详情
     */
    public function get_phone_nickname_info($filter, $param='')
    {
        if($param) {
            return _uri('screen_device_nickname', $filter, $param);
        } else {
            return _uri('screen_device_nickname', $filter);
        }
    }

    /**
     * 获取设备状态
     */
    public static function get_label_status($label_id, $day=0)
    {
        if ($day == 0) {
            $day = date('Ymd');
        }

        $filter = array('day' => $day, 'label_id' => $label_id);
        //查询在线统计表
        $online_info = _model('rfid_online_stat_day')->read($filter);

        //在线
        if ($online_info) {
            return true;
        }

        return false;
    }
}