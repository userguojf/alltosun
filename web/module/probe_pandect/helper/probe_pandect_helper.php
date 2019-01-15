<?php 
/**
 * alltosun.com  business_hall.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * szy: 宋志宇 (songzy@alltosun.com)
 * 2017年12月29日: 
 * Id
 */

class probe_pandect_helper
{   
    
    
    /**
     * 解析单个设备统计所需的数据
     * @param unknown $list
     * @param unknown $date_type
     * @param unknown $start_date
     * @param unknown $end_date
     */
    public static function parse_stat($time, $search_filter)
    {
        
        $date_type = $search_filter['date_type'];
    
        if ( $date_type == 'hour') {
            $date_list = probe_pandect_helper::get_hour_list($search_filter);
        } else if ( $date_type == 'day' ) {
            $date_list = probe_pandect_helper::get_day_list($search_filter);
        } else if ( $date_type == 'week' ) {
            $date_list = probe_pandect_helper::get_group_week_list($search_filter);
        } else if ( $date_type == 'month' ) {
            $date_list = probe_pandect_helper::get_month_list($search_filter);
        } 
            return $date_list;
    }
    
    public static function get_device_time($device_type,$province_id,$city_id,$create_time)
    {
        if(!$device_type || !$province_id || !$city_id || !$create_time){
            return false;
        }
        $filter = array(
                'device_type' => $device_type,
                'create_time'     => $create_time,
        );
        //所有符合查询条件的设备数量集合
        $list = _model('device_application')->read($filter, 'GROUP BY `create_time` ');
        $create_time = $list['create_time'];
//         foreach ($list as $k => $v){
//             $
//         }
        return $create_time;
    }
    
    
    /**
     * 根据开始日期获取小时列表
     * @param unknown $start_date 开始日期
     */
    public static function get_hour_list($search_filter)
    {
        $start_date = $search_filter['date'];
        $i = strtotime($start_date);
        p($i);
        while($i < strtotime('+1 days', strtotime($start_date))){
            $date_list[] = date('H', $i);
            $i = strtotime('+1 hours', $i);
        }
    
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
        $start_timestamp       = strtotime($search_filter['date']);
        $end_timestamp         = strtotime($search_filter['date']);
    
        $i               = $start_timestamp;
        while($i <= $end_timestamp){
            $date_list[] = date('Ymd', $i);
            $i = strtotime('+1 day', $i);
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
        $start_timestamp = strtotime($search_filter['date']);
        $end_timestamp   = strtotime($search_filter['date']);
    
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
     * 根据开始日期和结束日期获取月份列表
     * @param unknown $start_date 开始日期
     * @param unknown $end_date   结束日期
     */
    public static function get_month_list($search_filter)
    {
        //月时间戳
        $month_start_timestamp       = strtotime($search_filter['date']);
        $month_end_timestamp         = strtotime($search_filter['date']);
    
        $i      = $month_start_timestamp;
        while($i <= $month_end_timestamp){
            $date_list[] = date('Ym', $i);
            //加一个月
            $i = strtotime('+1 months', $i);
        }
    
        return $date_list;
    }
    
    public static function get_device_num($device_type,$province_id,$city_id,$create_time)
    {
        if(!$device_type || !$province_id || !$city_id){
            return false;
        }
        $filter = array(
                'device_type' => $device_type,
                'province_id' => $province_id,
                'city_id'     => $city_id,
                'create_time' => $create_time,
                'error_type' => '正确数据',
        );
        //所有符合查询条件的设备数量集合
        $num_list = _model('device_application')->getFields('device_num',$filter);
        //计算符合设备总数
        $num = array_sum($num_list);
        
        return $num;
    }
    /**
     * 查询设备列表
     *
     * @param   Array   查询条件
     * @param   String  查询字段
     * @param   String  排序
     *
     * @return  Array
     */
    static public function get_list($filter, $field = '', $order = '')
    {
        if ( !$filter ) {
            return array();
        }

        if ( $field ) {
            return _model('probe_device')->getFields($field, $filter, $order);
        } else {
            return _model('probe_device')->getList($filter, $order);
        }
    }

    /**
     * 获取权限范围内，有探针的营业厅id
     *
     * @param   String  资源名
     * @param   Int     权限ID
     *
     * @return  Array
     */
    static public function get_business_ids($res_name, $res_id)
    {
        // @todo 和func.php中get_filter函数中代码重复
        if ( $res_name == 'group' ) {
            $filter = array('status'=>1);
        } else if ( $res_name == 'province' ) {
            $filter = array('province_id'=>$res_id, 'status'=>1);
        } else if ( $res_name == 'city' ) {
            $filter = array('city_id'=>$res_id, 'status'=>1);
        } else if ( $res_name == 'area' ) {
            $filter = array('area_id'=>$res_id, 'status'=>1);
        } else if ( $res_name == 'business_hall' ) {
            $filter = array('business_id'=>$res_id, 'status'=>1);
        } else {
            return array();
        }

        return _model('probe_device')->getFields('business_id', $filter, ' GROUP BY `business_id` ');
    }

    /**
     * 查询设备信息
     *
     * @param   Array   查询条件
     *
     * @return  array
     */
    static public function get_info($filter)
    {
        if ( !$filter ) {
            return array();
        }

        // 如果查询条件为字符串，则当成设备编号
        if ( is_string($filter) ) {
            $dev = $filter;

            $filter = array(
            	'device'   =>  $dev,
                'status'   =>  1
            );
        }

        return _model('probe_device')->read($filter);
    }

    /**
     * 获取营业厅下设备信息，并拼成一定格式
     *
     * @param   Int 营业厅ID
     *
     * @return  Array
     */
    static public function get_devs($b_id)
    {
        if ( !$b_id ) {
            return array();
        }

        // 查询营业厅下设备列表
        $list = self::get_list(array('business_id' => $b_id, 'status' => 1));
        // 最后返回
        $res  = array();

        foreach ($list as $k => $v) {
            $dev        = $v['device'];
            $res[$dev]  = $v['rssi'];
        }

        return $res;
    }

    /**
     * 创建日志
     * @param array $param
     */
    public static function sync_dm_data($param)
    {
        if (!is_array($param)) {
            return '数据数数组形式';
        }

        if (!isset($param['user_number']) || !$param['user_number']) {
            return false;
        }

        if (!isset($param['device_num']) || !$param['device_num']) {
            return false;
        }

        if (!isset($param['type']) || !$param['type'] || !isset(probe_dev_config::$probe_operation[$param['type']])) {
            return false;
        }

        if (!isset($param['c_url']) || !$param['c_url'] || !isset(probe_dev_config::$dm_url[$param['c_url']])) {
            return false;
        }

        //app_id和app_key
        $dm_param = probe_dev_config::$dm_param;

        //数字地图的地址
        $dm_api_url    = probe_dev_config::$dm_url[$param['c_url']];
        //操作方式
        $operation_way = probe_dev_config::$probe_operation[$param['type']];

        $request_url   = $dm_api_url.$operation_way;

        $request_bean = '{ "appId"        : "'.$dm_param['app_id'].'",
                           "businessCode" : "'.$param['user_number'].'",
                           "probeNo"      : "'.$param['device_num'].'",
                           "probeType"    : 1,
                           "token"        : "'.md5($param['device_num'].$dm_param['app_key']).'"}';

        //传给数字地图
        $response_json = self::dm_myself_post($request_url , $request_bean);

        //记录日志
        self::create_api_logs($param['type'], $response_json, $request_bean, $param['device_num']);
    }

    //记录日志
    public static function create_api_logs($res_name, $response_json, $request_data, $device_num)
    {
        $log_info = [];

        $log_info['res_name']         = 'probe_dev_'.$res_name;
        $log_info['request_body']     = $request_data;
        $log_info['device_unique_id'] = $device_num;

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
        _model('screen_api_log')->create($log_info);

        return true;
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
     * 验证手机号
     * @param unknown $phone
     * @return boolean
     * @author songzy
     */
    public static function auto_phone($phone)
    {
        if (preg_match('/(^(13|15|17|18|14)(\d){9}$)|(^189\d{8}$)/', $phone)){
            return true;
        }
    
        return false;
    }
    
  /**
    * 验证邮箱
    * @param unknown $phone
    * @return boolean
    * @author songzy
    */
    public static function auto_email($email)
    {
        if (preg_match('/^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/', $email)){
            return true;
        }
    
        return false;
    }
    
    /**
     * 验证渠道编码
     * @param unknown $phone
     * @return boolean
     */
    public static function auto_user_number($user_number)
    {
        if($user_number == " "){
            return false;
        }
        $info = _model('business_hall')->read(array('user_number' => $user_number));
        if($info){
            return true;
        }
        return false;
    }
    
    
    /**
     * 数组中去除指定键为空数组 返回剩余数组
     * @param unknown $list
     * @param unknown $key
     * @return array
     */
    public static function diff_array($list,$key)
    {   
        if(!is_array($list) || $key == ''){
            return false;
        }
        foreach ($list as $k => $v){
            if($v[$key] == '正确数据'){
                unset($list[$k]);
            }
        }
        
        return $list;
    }
    
    /**
     * 小时统计
     *
     * @param   Int 营业厅ID
     * @param   Int 日期
     *
     * @return  Array
     */
    public static function hour_stat($b_id, $date)
    {
        if ( !$b_id || !$date ) {
            return array();
        }
    
        // 格式化时间，将y-m-d类的时间变成ymd类
        if ( !is_numeric($date) ) {
            $date = (int)date('Ymd', strtotime($date));
        }
    
        // 获取数据库操作对象
        $db   = get_db($b_id, 'hour');
        // 查询sql
        $sql  = "SELECT `dev`, `mac`, `remain_time`, `continued`, `is_indoor`, `is_oldcustomer`, `frist_time`, `up_rssi` FROM `{$db -> table}` WHERE `date` = {$date} AND `b_id` = {$b_id}";
        // 查询营业厅下某天的数据
        $list = $db->getAll($sql);
    
        $data = array();
    
        // 注：将列表按小时分组
        foreach ($list as $k => $v) {
            // 当前数据在哪个小时段
            $h = date('H', $v['frist_time']);
    
            if ( isset($data[$h]) ) {
                array_push($data[$h], $v);
            } else {
                $data[$h] = array($v);
            }
        }
    
        $list = array();
    
        // 遍历24小时，按小时统计数量
        for ( $i = 0; $i < 24; $i ++ ) {
            $key = $i < 10 ? "0{$i}" : "{$i}";
    
            $list[$key] = array();
    
            if ( isset($data[$key]) ) {
                $list[$key] = self::each_list($data[$key], $b_id);
            } else {
                $list[$key] = self::each_list(array(), $b_id);
            }
        }
    
        return $list;
    }
    
    /**
     * 遍历列表
     *
     * @param   Array   列表
     * @param   Int     营业厅ID
     *
     * @return  Array
     */
    private static function each_list($list, $b_id)
    {
        // 初始化返回数据
        $data = self::init_data($b_id);
    
        if ( !$data ) {
            return array();
        }
    
        // 获取规则
        $rule   = probe_rule_helper::get_rules($b_id);
        // 室外人数
        $indoor = array();
        // 室内人数
        $oudoor = array();
        // 较近人数
        $near   = array();
    
        // 遍历室内
        foreach ($list as $k => $v) {
            $dev        = strtolower($v['dev']);
            $continued  = $v['continued'];
            $remain     = $v['remain_time'];
            $mac        = $v['mac'];
            $is_indoor  = $v['is_indoor'];
    
            // 如果设置了continued规则，则判断是否满足
            //$rule['continued'][1] ： 连续活跃N天以上，不计入客流量（
            //$rule['continued'][0] ： 连续驻留N小时以上， 不计入客流量)（营业厅工作人员）
            if ( !empty($rule['continued'][1]) ) {
                if ( $continued >= $rule['continued'][1] ) {
                    continue;
                }
            }
    
            // 如果设置了minute规则，则判断是否满足 （连续驻留N分钟以下，不计入厅内）
            if ( !empty($rule['minute']) ) {
                if ( $remain < ($rule['minute'] * 60) ) {
                    $is_indoor = false;
                }
            }
    
            // 较近人数，暂时定死为60
            if ( abs($v['up_rssi']) < 60 && (time() - $v['up_time'] <= 10 * 60) ) {
                // 去重
                if ( !isset($near[$mac]) ) {
    
                    // 分设备统计
                    $data['dev']['near'][$dev] ++;
    
                    $near[$mac] = 1;
                }
            }
    
            // 室内
            if ( $is_indoor ) {
                // 去重
                if ( !isset($indoor[$mac]) ) {
                    $data['indoor'] ++;
                    $indoor[$mac] = 1;
    
                    // 分新老顾客统计
                    if ( $v['is_oldcustomer'] ) {
                        $data['old_num'] ++;
                    } else {
                        $data['new_num'] ++;
                    }
    
                    // 累加室内停留时长
                    $data['remain'] += $remain;
                }
    
                // 分设备统计
                $data['dev']['indoor'][$dev] ++;
                // 室外
            } else {
                // 去重
                if ( !isset($oudoor[$mac]) ) {
                    $data['oudoor'] ++;
                    $oudoor[$mac] = 1;
                }
                // 分设备统计
                $data['dev']['oudoor'][$dev] ++;
            }
        }
    
        return $data;
    }
    
    /**
     * 初始化统计返回数据
     *
     * @param   Int 营业厅ID
     *
     * @return  Array
     */
    private function init_data($b_id)
    {
        if ( !$b_id ) {
            return array();
        }
    
        // 取营业厅下设备
        $devs = probe_dev_helper::get_devs($b_id);
    
        if ( !$devs ) {
            return array();
        }
    
        // 返回数据格式
        $data = array(
                'dev'       =>  array(),
                'indoor'    =>  0,
                'oudoor'    =>  0,
                'new_num'   =>  0,
                'old_num'   =>  0,
                'remain'    =>  0
        );
    
        // 注：由于有些地方分设备统计，为了兼容，所有地方的统计都分设备
        foreach ( $devs as $dev => $rssi ) {
            $data['dev']['indoor'][$dev] = 0;
            $data['dev']['oudoor'][$dev] = 0;
            $data['dev']['near'][$dev]   = 0;
        }
    
        return $data;
    }
    
    
    /**
     * 加密
     * @param unknown $txt
     * @param string $key
     * @return string
     * @author songzy
     */
    public static function lock_url($txt,$key='songzy')
    {
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-=+";
        $nh = rand(0,64);
        $ch = $chars[$nh];
        $mdKey = md5($key.$ch);
        $mdKey = substr($mdKey,$nh%8, $nh%8+7);
        $txt = base64_encode($txt);
        $tmp = '';
        $i=0;$j=0;$k = 0;
        for ($i=0; $i<strlen($txt); $i++) {
            $k = $k == strlen($mdKey) ? 0 : $k;
            $j = ($nh+strpos($chars,$txt[$i])+ord($mdKey[$k++]))%64;
            $tmp .= $chars[$j];
        }
        return urlencode($ch.$tmp);
    }
    
    /**
     * 解密
     * @param unknown $txt
     * @param string $key
     * @return string
     * @author songzy
     */
    public static function unlock_url($txt,$key='songzy')
    {
        $txt = urldecode($txt);
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-=+";
        $ch = $txt[0];
        $nh = strpos($chars,$ch);
        $mdKey = md5($key.$ch);
        $mdKey = substr($mdKey,$nh%8, $nh%8+7);
        $txt = substr($txt,1);
        $tmp = '';
        $i=0;$j=0; $k = 0;
        for ($i=0; $i<strlen($txt); $i++) {
            $k = $k == strlen($mdKey) ? 0 : $k;
            $j = strpos($chars,$txt[$i])-$nh - ord($mdKey[$k++]);
            while ($j<0) $j+=64;
            $tmp .= $chars[$j];
        }
        return base64_decode($tmp);
    }
    
    
    public static function get_right_or_err_list($filter=array(),$str='1')
    {
        if(empty($filter) || !$str){
            return false;
        }
        if($str == 1){
            $filter['error_type'] = '正确数据';
        }else{
            $filter['error_type !='] = '正确数据';
        }
        
        $list  = _model('device_application')->getFields('id',$filter);
        
        $str = implode(',',$list);
        
        return $str;
    }
    
    //$param =array(
//     'province_id' => $province_id,  
//     'city_id' => $city_id,
//     'device_type' => $device_type, 申请设备类型
//     'factory_account' => $factory_account, 工厂账号
//     'flag' => $flag 0拒绝 1通过
    //  );
    public static function push_email_info($param,$filter)
    {
        if(empty($param) || empty($filter)){
            return false;
        }
        $str = $res = $send_content = '';
        //查询厂商信息
        $factory_user= _model('member')->read(array('member_user' => $param['factory_account']));
        $factory_info = _model('factory_res')->read(array('f_id' => $factory_user['id']));
        //厂商的电话和email
        $phone = $factory_info['phone'];
        //厂商email
        $email = $factory_info['email'];
        $email_list = array();
        //申报人账号email列表
        foreach ($filter as $k => $v){
            $info = _model('device_application')->read(array('id' => $v));
            $email_list[] = $info['email'];
        }
        //获取省市
        
        $province_name = business_hall_helper::get_info_name('province', $param['province_id'], 'name');
        $city_name     = business_hall_helper::get_info_name('city', $param['city_id'], 'name');
        if($param['flag'] == 0){
            $str = '很抱歉您申请的';
            $res = '未通过';
        }else{
            $str = '恭喜您申请的';
            $res = '通过';
            array_push($email_list,$email);
        }
        $send_content = $str .' '.$province_name .' '.$city_name.'的'.$param['num'].'台'.$param['device_type'].'设备审核'.$res;
        
        //数组去重
        $email_list = array_unique($email_list);
        $res = _widget('email')->send_email($email_list,"审核结果",$send_content);
        return $res;
        
    }
    
    /**
     * 获得订单联系人的第一条联系方式
     * @param unknown $param
     * @return boolean|unknown
     */
    public static function get_information($param)
    {
        if(empty($param)){
            return false;
        }
        $name = $param[0];
       
        return $name;
    }
}