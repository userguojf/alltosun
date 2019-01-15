<?php
/**
 * alltosun.com  screen_helper.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王敬飞 (wangdk@alltosun.com) $
 * $Date: 2017-6-28 下午2:57:48 $
 * $Id$
 */

class screen_helper
{

    public static function get_field_info($table, $filter , $field)
    {
        if (!$table || !$filter) return false;
        !is_array($filter) ? $filter : array( 'id' => $filter);

        if ( $field )
            return _uri( $table, $filter, $field );
        else
            return _uri( $table, $filter );
    }

    /**
     *获取 点击量的具体详情
     * @param int $id
     * @param str $table
     */
    public static function get_click_detail($id,$table)
    {
        if (!$id || !$table) {
            return false;
        }
        //拼接查询数据详情表
        $record_table = 'record_'.$table;
        //取出这条数据
        $table_info = _model($record_table)->getList(array('res_id' => $id));

        return $table_info;
    }
    /**
     *获取单条数据的点击量
     * @param int $id
     * @param str $table
     */
    public static function get_alone_clcik_num($id,$table)
    {
        if (!$id || !$table) {
            return false;
        }
        //拼接查询数据详情表
        $record_table = 'record_'.$table;
        //取出这条数据
        $num = _model($record_table)->getTotal(array('res_id' => $id));

        if ($num) {
            return $num;
        } else {
            return 0;
        }
    }

    /**
     * 传给数字地图的数据 记录日志
     * @param array $param
     * @return string|boolean
     */
    public static function dm_create_app_log($param)
    {
        if (!is_array($param)) {
            return '数据数数组形式';
        }

        if (!in_array($param['type'], screen_config::$dm_type)) {
            return false;
        }

        if (!isset($param['user_number']) || !$param['user_number']) {
            return false;
        }

        if (!isset($param['device_unique_id'])  || !$param['device_unique_id']) {
            return false;
        }

        if (!isset($param['brand']) || !$param['brand']) {
            return false;
        }

        if (!isset($param['version'])  || !$param['version']) {
            return false;
        }

        if (!isset($param['shoppe_id']) || !$param['shoppe_id']) {
            return false;
        }

        //日志记录
        $log_info = array();

        $log_info['res_name']         = $param['type'];
        $log_info['device_unique_id'] = $param['device_unique_id'];

        //传给数字地图方法
        $response_json = self::send_dm_data($param);

        $response_info = json_decode($response_json['response_body'] , true);

        if ($response_info && isset($response_info['httpStatus'])) {
            $log_info['response_code'] = $response_info['httpStatus'];
        }

        $log_info['request_body'] = $response_json['request_bean'];

        //json数据
        if ($response_info) {
            $log_info['response_body'] = $response_json['response_body'];
        } else {
            $log_info['response_body'] = '接口返回空';
        }

        //创建日志
        _model('screen_api_log')->create($log_info);
    }

    /**
     * 发送label_id给数字地图方面
     * @param array $param
     * @return json $response_info
     */
    public static function send_dm_data($param)
    {
        //app_id和app_key
        $dm_param = screen_config::$dm_param;

        //创建接口
        if ('create' == $param['type']) {
            $request_url = screen_helper::dm_create_url_choice();
        }

        //删除接口
        if ('delete' == $param['type']){
            $request_url = screen_helper::dm_delete_url_choice();
        }

        $request_bean = '{ "appId"         : "'.$dm_param['app_id'].'",
                            "brand"        : "'.$param['brand'].'",
                            "businessCode" : "'.$param['user_number'].'",
                            "probeNo"      : "'.$param['device_unique_id'].'",
                            "shoppeId"     : "'.$param['shoppe_id'].'",
                            "probeType"    : 3,
                            "token"        : "'.md5($param['device_unique_id'].$dm_param['app_key']).'",
                            "version"      : "'.$param['version'].'"}';

        $response_info = self::dm_myself_post($request_url , $request_bean);

        return array('response_body' => $response_info, 'request_bean' => $request_bean);
    }

    /**
     * 自定义的post请求
     * @param string $url
     * @param array  $data
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
        //处理头部信息
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
     * 添加手机设备
     * @param array $info
     * @return string
     */
    public static function add_screen_device($info)
    {
        if (!$info) {
            return '';
        }

        //wangjf 新增系统版本
        if (!isset($info['sys_version'])) {
            $info['sys_version'] = '';
        }
//         $device_info = _model("screen_device")->read(array('device_unique_id'=>$info['device_unique_id']));

//         if ($device_info) {
//             //更新数据
//             $update_data = array(
//                     'registration_id' => $info['registration_id'],
//                     'shoppe_id'     => $info['shoppe_id'],
//                     'province_id'   => $info['province_id'],
//                     'city_id'       => $info['city_id'],
//                     'area_id'       => $info['area_id'],
//                     'business_id'   => $info['business_id'],
//                     'mac'           => $info['mac'],
//                     'version_no'    => $info['version_no'], //更新版本号
//                     'day'           => date('Ymd')
//             );

// //             if ($device_info['business_id'] != $info['business_id']) {
// //                 $update_data['add_time']      = date('Y-m-d H:i:s');
// //             }

//             _model("screen_device")->update(array('id'=>$device_info['id']), $update_data);
//         } else {
//             _model('screen_device')->create($info);
//         }

//         return 'ok';

        //同一个设备下的所有营业厅ID
        $yyt_ids = _model("screen_device")->getFields(
                'business_id',
                 array('device_unique_id' => $info['device_unique_id'])
        );

        if ( $yyt_ids ) {
            // 先把原来的这个设备全部下架
            _model("screen_device")->update(
                array('device_unique_id' => $info['device_unique_id'], 'business_id' => $yyt_ids),
                array('status' => 0)
            );

            if ( in_array($info['business_id'], $yyt_ids) ) {
                $data = array(
                                'shoppe_id'  => $info['shoppe_id'],
                                'version_no' => $info['version_no'],
                                'sys_version' => $info['sys_version'],
                                'phone_name_nickname'    => $info['phone_name_nickname'],
                                'phone_version_nickname' => $info['phone_version_nickname'],
                                'day'                    => date('Ymd'),
                                'status'                 => 1
                            );
                $filter = array(
                        'device_unique_id' => $info['device_unique_id'],
                        'business_id'      => $info['business_id']
                );
                // 更新以前存在的
                _model("screen_device")->update($filter , $data);
            } else {
                // 创建新的
                _model('screen_device')->create($info);
            }
        } else {
            _model('screen_device')->create($info);
        }

        return 'ok';
    }

    /**
     * wangjf 2017-12-26 还原
     */
    public static function by_device_unique_id_get_device_info($device_unique_id)
    {
        if (!$device_unique_id) return false;


        $device_info = _model('screen_device')->read(array( 'device_unique_id' => $device_unique_id, 'status' => 1), ' ORDER BY `id` DESC ');

        if (!$device_info) return false;

        return $device_info;
    }

    /**
     * wangjf 2017-12-26 还原
     * 显示手机品牌昵称
     * @param unknown $phone_name
     * @return unknown
     */
    public static function display_phone_name_nickname($phone_name)
    {
        if (!$phone_name) {
            return '';
        }

        return _uri('screen_device_name_nickname', array('phone_name' => strtolower($phone_name)), 'name_nickname');

    }

    /**
     * wangjf 2017-12-26 还原
     * 显示手机型号昵称
     * @param unknown $phone_name
     * @return unknown
     */
    public static function display_phone_version_nickname($phone_name, $phone_version)
    {
        if (!$phone_name || !$phone_version) {
            return '';
        }

        return _uri('screen_device_version_nickname', array('phone_name' => strtolower($phone_name), 'phone_version' => strtolower($phone_version)), 'version_nickname');
    }

    /**
     * wangjf 2017-12-26 还原
     * 获取手机型号昵称
     * @param unknown $phone_name
     * @return unknown
     */
    public static function display_version_nickname($phone_version, $version_nickname)
    {
        if (!$phone_version || !$version_nickname) {
            return '';
        }

        return _uri('screen_device_version_nickname', array('phone_name' => strtolower($phone_name), 'phone_version' => strtolower($phone_version)), 'version_nickname');
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
     * 处理时间（s） for example return 格式
     * @param int $time
     * @return boolean|string
     */
    public static function handle_hours_mins_secs($time)
    {

        if ($time/3600 >= 1) {
            //计算小时数
            $hours = intval($time/3600);

            //计算分钟数
            $remain = $time%3600;
            $mins   = intval($remain/60);

            //计算秒数
            $secs = $remain%60;

            return "{$hours}时{$mins}分{$secs}秒";
        } else if ($time/3600 < 1 && $time/60 >= 1) {
            //计算分钟数
            $mins = intval($time/60);

            //计算秒数
            $secs = $time%60;

            return "{$mins}分{$secs}秒";
        } else if ($time/60 < 1) {
            return "{$time}秒";
        } else if (!$time) {
            return '0秒';
        }

    }

    /**
     * mac转换imei
     * @param unknown $hall_code
     * @param unknown $phone_name
     * @param unknown $phone_version
     */
    public static function mac_to_imei($mac)
    {
        if (!$mac) {
            return 0;
        }

        $mac_arr        = explode(':', $mac);
        $number         = '';

        if (count($mac_arr) != 6){
            return 0;
        }

        foreach ($mac_arr as $k =>$v) {
            $ary =  base_convert($v, 16, 2);
            //补全八位
            $number .= sprintf('%08u', $ary);
        }

        //补1位
        return "1".base_convert($number, 2, 10);
    }

    /**
     * imei转换mac
     * @param unknown $phone_version
     */
    public static function imei_to_mac($imei)
    {
        //去除补1位， 并转换为二进制
        $ary = base_convert(substr($imei, 1, strlen($imei)), 10, 2);

        $ary_len = strlen($ary);

        //将二进制补全48位
        if ($ary_len < 48) {
            $j = 48 - $ary_len;
            for ($i=0; $i < $j; $i++) {
                $ary = '0'.$ary;
            }
        }

        //每八位转换为16进制
        $mac = '';
        for ($i=0; $i < 6; $i++) {
            $str = substr($ary, $i*8, 8);

            //转换16进制
            $hex = base_convert($str, 2, 16);

            //不够2位并用0补全
            $hex = strlen($hex) < 2 ? '0'.$hex : $hex;
            $mac .=  $hex.":";
        }

        return rtrim($mac, ':');
    }


    /**
     * 根据member获取营业厅信息
     * @param int $member_res_name
     * @param int $member_res_id
     */
    public static function get_business_id_by_member($member_res_name, $member_res_id) {
        $table         ='';
        $rule_area_id  = 0;
        $business_ids  = 0;

        //获取当前登录用户res_name权限

        //省级级
        if ($member_res_name == 'province') {
            $table = 'province';
            $field = 'province_id';
            $rule_area_id = $member_res_id;
            //城市级
        } else if ($member_res_name == 'city') {
            $table = 'city';
            $field = 'city_id';
            //查寻对应权限区域id
            $rule_area_id = _uri($table, array('province_id' => $member_res_id), 'id');
            //区县级
        } else if ($member_res_name == 'area') {
            $table = 'area';
            $field = 'area_id';
            //厅级
        } else if ($member_res_name == 'business_hall') {
            return  $member_res_id;
        } else {
            return array();
        }

        //查询对应的营业厅id
        if ($table !='business_hall') {
            $business_filter      = array($field => $rule_area_id);
            $business_hall_id     = _model('business_hall')->getFields('id', $business_filter);
        }
        return $business_hall_id;
    }

    /**
     * 获取在线状态
     */
    public static function get_online_status($device_unique_id)
    {
        if (!$device_unique_id ) {
            return false;
        }

        $info = _model('screen_device')->read(
            array(
                    'device_unique_id' => $device_unique_id,
                    'status'           => 1
            )
        );

        if ( !$info ) return false;

        $filter = array(
                'business_id'      => $info['business_id'],
                'device_unique_id' => $device_unique_id,
                'day'              => date('Ymd'),
                'update_time >='   => date('Y-m-d H:i:s', time()-1800)
        );

        //查询最后一条在线记录
        $result = _model('screen_device_online_stat_day')->read($filter);

        return $result ? true : false;
    }

    /**
     *
     * @param array $list
     * @param string $field
     * @param string $sort
     * @return boolean|unknown
     * 根据字段重新排序数组
     */
    public static function myself_sort($list , $field ,$sort)
    {
        /* if (!is_array($list)) {
            return false;
        }

        if (!$field || !$sort) {
            return false;
        } */

        if (!$list) {
            return $list;
        }

        $arrSort = array();

        foreach($list as $k => $v){
            foreach($v as $key => $value){
                $arrSort[$key][$k] = $value;
            }
        }

        if ($sort == 'desc') {
            array_multisort($arrSort[$field], SORT_DESC, $list);
        } else {
            array_multisort($arrSort[$field], SORT_ASC, $list);
        }

        return $list;
    }

    /**
     * 根据时间获取本周周的开始日期和结束日期
     * @param int $year
     * @param int $week 第几周;
     */
    public static function get_day_by_time($date_time)
    {
        $timestamp = strtotime($date_time);

        $year = date('Y', $timestamp);
        $week = date('W', $timestamp);

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

        $weekday['end']     = date('Y-m-d 23:59:59', $weekday['end']);
        $weekday['start']   = date('Y-m-d 00:00:00',$weekday['start']);

        return $weekday;

    }
    /**
     * 添加在线统计
     * 离线说明
     * 如果记录上线数据，但是离线了，下次接口被请求了一并更离线字段；
     * 如果一直不更新超过5小时，计划任务会统计到；
     * 如果不够5小时，前端展示离线时间是当前时间 - 最后一次更新时间
     * @param array $info
     * return int
     */
    public static function add_device_online_stat_day($info, $version='v2')
    {
        if (!$info) {
            return 0;
        }

        if ( $version == 'v2' ) {
            $api_time = 60;
        } else if ( $version == 'v3' ) {
            $api_time = 5 * 60;
        } else if ( $version == 'v4' ) {
            $api_time = 10 * 60;
        }

        $info['is_online'] = 1;

        $online_stat_info = _model('screen_device_online_stat_day')->read(
                array(
                        'device_unique_id' => $info['device_unique_id'],
                        'day'              => date('Ymd'),
                        'business_id'      => $info['business_id']
                        )
        );

        //今日上午八点的时间戳和晚上19点的时间戳
        $today_8_time  = strtotime(date('Y-m-d 08:00:00'));
        $today_19_time = strtotime(date('Y-m-d 19:00:00'));

        if ( $online_stat_info ) {
            //上一次的更新时间戳
            $before_time   = strtotime($online_stat_info['update_time']);
            //离线5小时时刻
            $offline_the_time = $online_stat_info['offline_of_time'];
            //更新sql
            $update_sql = '';

            //首先是在统计范围之间
            if ( time() > $today_8_time && time() < $today_19_time ) {
                //上一次更新时间小于在8点
                if ( $before_time < $today_8_time && time() - $today_8_time < 5 * 60 * 60 ) {
                    //离线时间从八点开始算
                    $offline_time = time() - $today_8_time;
                    $update_sql   = " SET `offline_time` = `offline_time` + $offline_time ,";
//                     p($offline_time);
//                     exit();
                }
                if ( $before_time < $today_8_time && time() - $today_8_time > 5 * 60 * 60 ) {
                    //离线时间从八点开始算
                     $offline_time = 5 * 60 * 60;
                     $update_sql   = " SET `offline_time` = `offline_time` + $offline_time ,
                     `offline_of_time` = `offline_of_time` + $today_8_time , ";
                }
                //上次时间也在统计范围内的
                if ( $before_time > $today_8_time && $before_time < $today_19_time ) {
                    //离线时刻不存在并且这次时间和上次时间间隔大于5小时
                    if ( !$offline_the_time && time() - $before_time > 5 * 60 * 60 ) {
                        $offline_time = time() - $before_time;

                        $update_sql   = " SET `offline_time` = `offline_time` + $offline_time ,
                        `offline_of_time` = `offline_of_time` + $before_time , ";
                    }
                    //和上一次时间间隔小于5小时，并且上一次更新时间不在半个小时之内
                    if ( time() - $before_time < 5 * 60 * 60 && $before_time + 30 * 60 < time() ) {
                        $offline_time = time() - $before_time;
                        $update_sql   = " SET `offline_time` = `offline_time` + $offline_time ,";
                    }
                }
            }

            //当前时间大于19点，但是上次时间在统计范围的情况
            if ( time() > $today_19_time && $today_8_time < $before_time && $before_time < $today_19_time ) {
                //离线时刻不存在并且这次时间和上次时间间隔大于5小时
                if ( !$offline_the_time && $today_19_time - $before_time > 5 * 60 * 60 ) {
                    $offline_time = $today_19_time - $before_time;

                    $update_sql   = " SET `offline_time` = `offline_time` + $offline_time ,
                    `offline_of_time` = `offline_of_time` + $before_time , ";
                }

                //和上一次时间间隔小于5小时，并且上一次更新时间不在半个小时之内
                if ( $today_19_time - $before_time < 5 * 60 * 60 && $before_time + 30 * 60 < $today_19_time ) {
                    $offline_time = $today_19_time - $before_time;
                    $update_sql   = " SET `offline_time` = `offline_time` + $offline_time ,";
                }
            }

            if ($update_sql) {
                $update_sql .= " `online_time` = `online_time` + $api_time ";

            } else {
                $update_sql = " SET `online_time` = `online_time` + $api_time ";

            }
            // 更新在线时长
            _model('screen_device_online_stat_day')->update($online_stat_info['id'], $update_sql);

        } else {
            //在下午1点后上线
            if ( time() > $today_8_time && time() < $today_19_time  && time() - $today_8_time > 5 * 60 * 60 ) {
                $info['offline_time']    = time() - $today_8_time;
                $info['offline_of_time'] = $today_8_time;
            }

            //在上午8点后下午1点之间上线
            if ( time() > $today_8_time && time() < $today_19_time  && time() - $today_8_time < 5 * 60 * 60 ) {
                $info['offline_time'] = time() - $today_8_time;
            }

            //19点后上线
            if ( time() > $today_19_time ) {
                $info['offline_time']    = 8 * 60 * 60;
                $info['offline_of_time'] = $today_8_time;
            }
            //第一次上报默认个60秒，不然看第一次数据时会出现0的问题
            $info['online_time'] = $api_time;

            _model('screen_device_online_stat_day')->create($info);
        }

        return 1;
    }


    /**
     * 返回单个亮屏app的详情数据
     * @param array $screen_info
     * @param int   $date
     * @return multitype:boolean number unknown
     */
    public static function get_screen_app_detail($screen_info, $date)
    {
        //返回信息的数组
        $data = [];

        //返回信息 1手机信息和所属柜台
        //         $data['shoppe_id']     = $screen_info['shoppe_id'];
        $data['device_code']   = $screen_info['device_unique_id'];
        $data['phone_name']    = $screen_info['phone_name'];
        $data['phone_version'] = $screen_info['phone_version_nickname'] ? $screen_info['phone_version_nickname'] : $screen_info['phone_version'];

        //在线时长
        $device_info = _model('screen_device_online_stat_day')->read(array('device_unique_id' => $screen_info['device_unique_id'], 'day' => $date));

        //体验次数
        $screen_data = _model('screen_device_stat_day')->read(array('device_unique_id' => $screen_info['device_unique_id'], 'day' => $date));

        //返回信息 2在线数据
        if ($device_info) {
            $is_online = (strtotime($device_info['update_time']) + 300) > time() ? true : false;

            //更新在线状态
            if (!$is_online) {
                _model('screen_device_online_stat_day')->update(array('id' => $device_info['id']), array('is_online' => 0));
            }

            $data['is_online']   = $is_online;
            $data['online_time'] = $device_info['online_time'];

        } else {
            $data['is_online']   = false;
            $data['online_time'] = 0;
        }

        //返回信息 3体验数据
        if ($screen_data) {
            $data['action_num']      = $screen_data['action_num'];
            $data['experience_time'] = $screen_data['experience_time'];
        } else {
            $data['action_num']      = 0;
            $data['experience_time'] = 0;
        }

        return $data;
    }

    /**
     * 亮屏机型合成图片
     * @param string $image_url 图片链接
     * @param string $title     文字内容
     * @param number $color_type  颜色类型
     * @return boolean | string
     */
    public static  function  compose_screen_image($image_url , $price = 0 , $color_type = 1 , $title = 'RMB')
    {

        if (!$image_url) {
            return false;
        }

        $hash = self::generate_show_pic_hash($image_url, $price, $color_type);
        $link = self::get_screen_show_pic_cache($hash);
        if ($link) {
            return $link;
        }

        if (!array_key_exists($color_type, screen_config::$screen_color_type)) {
            return false;
        }

        $image_conf  = array(
            'pink_color'  => screen_config::$screen_color_type[$color_type],
            'font_file'   => STATIC_DIR."/font/Avenir-Medium.otf",
            'title'       => array(25, 0, 235, 650),
            'price'       => array(50, 0, 330, 650)
        );

        $file_info = pathinfo($image_url);
        $type      = $file_info['extension'];

        switch($type){
            case 'jpg':
                //背景模版
                $image = imagecreatefromjpeg(_image($image_url));
                break;
            case 'png':
                $image = imagecreatefrompng(_image($image_url));
                break;
            default:
                exit("请上传规定的图片类型");
                break;
        }

        //文字类型
        $pink  = ImageColorAllocate($image,$image_conf['pink_color'][0],$image_conf['pink_color'][1],$image_conf['pink_color'][2]);


        //合成title
        if ($title) {
            imagettftext($image,$image_conf['title'][0],$image_conf['title'][1],$image_conf['title'][2],$image_conf['title'][3],$pink,$image_conf['font_file'],$title);//写文字
        }

        //合成title
        if ($price) {
            imagettftext($image,$image_conf['price'][0],$image_conf['price'][1],$image_conf['price'][2],$image_conf['price'][3],$pink,$image_conf['font_file'],$price);//写文字
        }

        ob_start();

        //将带有文字的图片保存到文件
        $result = imagejpeg($image,null,100);
        //将带有文字的图片保存到文件
        switch($type){
            case 'jpg':
                //背景模版
                $result = imagejpeg($image,null,100);
                //                     header('Content-Type: image/jpeg');
                //                     imagejpeg($image);
                //                     imagedestroy($image);
                //                     exit();
                break;
            case 'png':
                $result = imagepng($image);
//                                 header('Content-Type: image/png');
//                                 imagejpeg($image);
//                                 imagedestroy($image);
//                                 exit();
                break;
            default:
                exit("请上传规定的图片类型");
                break;
        }

        imagedestroy($image);
        $ob_image = ob_get_contents();

        ob_clean();

        //二进制图片转链接
        $link =  tools_helper::save_binary_image($image_url, $ob_image);

        if ($link) {
            self::set_screen_show_pic_cache($hash,$link);
        }

        return $link;
    }


    /**
     * 获取营业厅机型图片缓存
     * @return boolean|string
     */
    public static function get_screen_show_pic_cache($hash)
    {
        if (!$hash) {
            return false;
        }

        return  _uri('screen_show_pic_cache', array('hash' => $hash), 'link');
    }

    /**
     * 设置营业厅机器图片缓存
     * @return boolean|string
     */
    public static function set_screen_show_pic_cache($hash , $link)
    {
        if (!$hash || !$link) {
            return false;
        }

        $pic_cache_info = _model('screen_show_pic_cache')->read(array('hash' => $hash));

        if ($pic_cache_info) {
            _model('screen_show_pic_cache')->update($pic_cache_info['id'], array('link' => $link));
        } else {
            _model('screen_show_pic_cache')->create(
                array('hash' => $hash, 'link' => $link)
            );
        }

        return true;
    }

    /**
     * 生成hash
     * @param string $image_url
     * @param string $price
     * @param string $color_type
     * @return string
     */
    public static function generate_show_pic_hash($image_url, $price, $color_type)
    {
        return md5($image_url.$price.$color_type);
    }


    /**
     * 更新show_pic的状态
     * @param int $content_id
     * @param int $status
     */
    public static function update_show_pic_info($content_id, $link, $font_color_type)
    {
        if (!$content_id || !$link || !$font_color_type) return false;

        $list = _model('screen_show_pic')->getList(array('content_id' => $content_id));

        if (!$list) return true;

        foreach ($list as $k => $v) {
            $new_link = screen_helper::compose_screen_image($link, $v['price'], $font_color_type);

            if ($new_link) {
                _model('screen_show_pic')->update($v['id'], array('link' => $new_link));
            }
        }
    }

    /**
     * 通过unique_id获取价格
     * @param string $imei
     * @return number|unknown
     */
    public static function by_unique_id_get_price($device_unique_id)
    {
        if (!$device_unique_id) {
            return 0;
        }

        $info = _model('screen_show_pic')->read(array('device_unique_id' => $device_unique_id));

        if ($info) {
            return $info['price'];
        }

        return 0;
    }

    /**
     *
     * @param int $business_id
     * @param string $phone_name
     * @param string $phone_version
     * @return boolean
     */
    public static function is_show_price($business_id, $phone_name, $phone_version)
    {

        if (!$business_id || !$phone_name || !$phone_version) {
            return false;
        }

        $business_info = _uri('business_hall', $business_id);

        if ( !$business_info ){
            return false;
        }

        //先获取所有在线内容
        $content_filter = array(
                'type'              => 4,
                'start_time <='     => date('Y-m-d H:i:s'),
                'end_time >='       => date('Y-m-d H:i:s'),
                'status'            => 1
        );

        $content_ids = _model('screen_content')->getFields('id', $content_filter);

        if (!$content_ids){
            return false;
        }

        //发布类型，根据四级管理权限倒序
        $put_type = array_reverse(screen_content_config::$content_put_type, true);

        foreach ($put_type as $k => $v) {
            //获取宣传内容
            $content_res_filter = array(
                    'content_id' => $content_ids,
                    'res_name'   => $k,
                    'phone_name'    => $phone_name,
                    'phone_version' => $phone_version
            );

            if ($k == 'business_hall') {
                $content_res_filter['res_id'] = $business_id;
            } else if ($k != 'group'){
                $content_res_filter["{$k}_id"] = $business_info["{$k}_id"];
            }

            $content_res_info = _model('screen_content_res')->read($content_res_filter, 'ORDER BY `content_id`');

            if ($content_res_info) {
                return true;
            }

        }

        return false;
    }

    /**
     * 表中有unique_id字段，通过unique_id获取信息
     * @param string $table
     * @param string $unique_id
     * @param string $field
     * @return boolean|Ambigous <multitype:, string, unknown, Obj, mixed>
     */
    public static function by_unique_id_get_field($table, $device_unique_id, $field)
    {
        if (!$table || !$device_unique_id) {
            return false;
        }

        if (!$field) {
            $info = _uri($table , array('device_unique_id' => $device_unique_id));
        } else {
            $info = _uri($table , array('device_unique_id' => $device_unique_id), $field);
        }

        if ($info) {
            return $info;
        }

        return false;
    }

    /**
     * 字节转兆
     * @param int $num
     * @return string
     */
    public static function get_filesize($num){
        $p = 0;
        $format='bytes';
        if($num>0 && $num<1024){
            $p = 0;
            return number_format($num).' '.$format;
        }
        if($num>=1024 && $num<pow(1024, 2)){
            $p = 1;
            $format = 'KB';
        }
        if ($num>=pow(1024, 2) && $num<pow(1024, 3)) {
            $p = 2;
            $format = 'MB';
        }
        if ($num>=pow(1024, 3) && $num<pow(1024, 4)) {
            $p = 3;
            $format = 'GB';
        }
        if ($num>=pow(1024, 4) && $num<pow(1024, 5)) {
            $p = 3;
            $format = 'TB';
        }
        $num /= pow(1024, $p);
        return number_format($num, 2).' '.$format;
    }

    /**
     * 获取设备唯一id 注：mac 或 imei 必须有一种不能为空， 优先mac
     * @param unknown $mac 设备mac
     * @param unknown $imei 设备imei
     * @return string|unknown|boolean
     */
    public static function get_device_unique_id($mac, $imei)
    {

        if ($mac) {
            return strtolower(str_replace(':', '', $mac));
        }

        if ($imei) {
            return $imei;
        }

        return false;

    }

    /**
     * 通过唯一ID获取imei
     * @param string $device_unique_id
     * @return boolean|string|unknown
     */
    public static function by_device_unique_id_get_imei($device_unique_id)
    {
        if (!$device_unique_id) return false;

        $device_info = _model('screen_device')->read(array('device_unique_id' => $device_unique_id));

        if (!$device_info) return false;

        if (!$device_info['imei']) return '手机无imei';

        return $device_info['imei'];
    }

    /**
     * 通过内容ID获取该内容的轮播数量
     * @param string $content_id
     * @param int    $date
     * @return boolean|number|unknown
     */
    public static function by_content_id_get_roll_num($content_id, $date = '')
    {
        if (!$content_id) return false;

        if (!$date) {
            $date = date('Ymd');
        }

        $count_roll_info = _model('screen_roll_count_stat')->read(array('content_id' => $content_id, 'date' => $date));

        if (!$count_roll_info) {
            return 0;
        }

        return $count_roll_info['roll_num'];
    }

    /**
     * 通过设备唯一ID判断是否为新安装
     * @param string $device_unique_id
     * @param string $date
     * @return boolean
     */
    public static function by_device_unique_id_judge_new_install($device_unique_id, $date = '')
    {
        if (!$device_unique_id) return false;

        if (!$date) $date = date('Y-m-d');

        $device_info = _model('screen_device')->read(
                        array(
                                'device_unique_id' => $device_unique_id,
                                'add_time >='      => $date.' 00:00:00',
                                'add_time <'       => $date.' 23:59:59'
                        )
        );

        if (!$device_info) return false;

        return true;
    }

    /**
     * 通过设备唯一ID获取离线时间方法
     * @param array  $stat_info   screen_device_online_stat_day表数据
     * @param string $device_unique_id
     * @param string $date
     * @return boolean|string
     */
    public static function by_device_unique_id_get_offline_time($stat_info, $device_unique_id, $date = '')
    {
        if (!$device_unique_id) return false;

        if (!$date) $date = date('Ymd', time() - 24 *3600);

        $timestamp = 0;

        if (!$stat_info) {
            $offline_info = _model('screen_offline_series_stat')->read(
                    array(
                            'device_unique_id' => $device_unique_id,
                            'date'             => $date
                        )
            );
//             $offline_record = _model( 'screen_everyday_offline_record' )->getList(
//                     array(
//                             'device_unique_id' => $device_unique_id,
//                             'date <'           => $date
//                         )
//              );
            if ( !$offline_info ) {
                return 0;
            } else {
                return $offline_info['offline_num'];
            }
        }

        // 在线的
        $timestamp = $stat_info['offline_time'] + time() - strtotime($stat_info['update_time']);

        return self::format_timestamp_text($timestamp);
    }

    /**
     * 通过一个表的ID获取该表的某个字段
     * @param int    $id
     * @param string $table
     * @param string $field
     * */
    public static function by_id_get_field($id, $table, $field = '')
    {
        if (!$id || !$table) {
            return false;
        }

        if (!$field) {
            $info = _uri($table, array('id' => $id));
        } else {
            $info = _uri($table, array('id' => $id), $field);
        }

        if ($info) {
            return $info;
        }
// p($info);exit();
        return false;
    }

    /**
     * 调用数字地图创建地址
     * @return string
     */
    public static function dm_create_url_choice()
    {
        if (!ONDEV) {
            return screen_config::$dm_create_api_url;
        } else {
            return screen_config::$test_dm_create_api_url;
        }
    }
    /**
     * 调用数字地图删除地址
     * @return string
     */
    public static function dm_delete_url_choice()
    {
        if (!ONDEV) {
            return screen_config::$dm_delete_api_url;
        } else {
            return screen_config::$test_dm_delete_api_url;
        }
    }

    public static function get_phone_nickname($filed, $device_unique_id, $target = false)
    {
        if ( !$filed || !$device_unique_id || !in_array($filed, array('name', 'version', 'all'))) return false;

        $device_info = _model('screen_device')->read(array('device_unique_id' => $device_unique_id) );

        if (!$device_info) {
            return false;
        }

        $nick_name = [];
        $nick_name['name'] = $device_info['phone_name_nickname'] ? $device_info['phone_name_nickname'] : $device_info['phone_name'];
        $nick_name['version'] = $device_info['phone_version_nickname'] ? $device_info['phone_version_nickname'] : $device_info['phone_version'];

        if ( 'all' == $filed && $target ) return $nick_name;

        if ($filed == 'version') return $nick_name['version'];

        if ($filed == 'name') return $nick_name['name'];
    }

    /**
     * 根据渠道获取次数
     * @param $source
     */
    public static function get_count_by_source($source, $type)
    {
        $count = _model('screen_version_record')->getTotal(['source' => $source, 'type' => $type]);
        return $count;
    }
}
?>