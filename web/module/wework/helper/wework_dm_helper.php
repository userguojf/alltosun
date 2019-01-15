<?php
/**
 * alltosun.com 数字地图相关接口 wework_dm_helper.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-3-16 上午11:06:13 $
 * $Id$
 */

class wework_dm_helper
{
    public static $appid  = '3902262776528707';
    public static $appkey = 'lShKEqLpYIVzb4tVaxt0dF48RQxQ40Wd';

    /**
     * 展陈接口
     * @param unknown $user_number
     * @return boolean
     */
    public static function exhibition($user_number)
    {
        if ( !$user_number ) return false;

        $request_url = 'http://market-mng-fe-temp.obaymax.com:30000/api/awifi/login';

//         $request_bean  = '{';
//         $request_bean .= '"username":"'.$user_number.'",';
//         $request_bean .= '"token": "'.md5($user_number.self::$appkey).'",';
//         $request_bean .= '"appId": "'.self::$appid.'"';
//         $request_bean .= '}';

        $request_bean = array(
            'username' => $user_number,
            'token'    => md5($user_number.self::$appkey),
            'appId'    => self::$appid
        );
        $response_json = curl_get($request_url, $request_bean);

        //记录日志
        self::dm_api_log(1, $request_url, json_encode($request_bean), $response_json);
    }

    /**
     * 数字地图请求方法
     * @param unknown $url
     * @param unknown $data
     * @return mixed
     */
    public static function dm_post($url, $data)
    {
        $ch = curl_init();

        //对方要求header头
        $header = array(
                'Content-Type: application/json'
        );
        //设置cURL允许执行的最长毫秒数。
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 2000);

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
     * 
     * @param unknown $type  type  1 为展陈接口
     * @param unknown $request_url
     * @param unknown $request_data
     * @param unknown $response_json
     * @return boolean
     */
    public static function dm_api_log($type, $request_url, $request_data, $response_json)
    {
        if (!$type || !$request_url || !$request_data || !$response_json ) return false;
        $log_info = [];

        $log_info['type']         = $type;
        $log_info['request_data'] = $request_data;
        $log_info['request_url']  = $request_url;
        $log_info['date']         = date('Ymd');

        $response_info            = json_decode($response_json, true);

        if ( $response_info && isset($response_info['httpStatus']) ) {
            $log_info['response_code'] = $response_info['httpStatus'];
        } else {
            $log_info['response_code'] = 'No';
        }

        //json数据
        if ( $response_info ) {
            $log_info['response_body'] = $response_json;
        } else {
            $log_info['response_body'] = 'api time out';
        }

        //创建日志
        _model('dm_api_log')->create($log_info);

        return false;
    }
}