<?php 
/**
 * alltosun.com 设备管理helper probe_dev_helper.php
 * ============================================================================
 * 版权所有 (C) 2009-2015 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明: 这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王磊 (wangl@alltosun.com) $
 * $Date: 2017-8-3 上午11:33:06 $
*/

class probe_dev_helper
{
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
     * 根据mac地址获取device信息
     */
    public static function get_device_for_mac($mac)
    {
        if (!filter_var($mac, FILTER_VALIDATE_MAC)) {
            return $mac;
        }

        $device = _uri('probe_device_mac_res', ['mac' => $mac], 'device');
        
        if (!$device) {
            return $mac;
        }

        return $device;
    } 
}