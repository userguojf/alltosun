<?php

/**
 * alltosun.com 用户模块公共函数库 user_helper.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: Shenxn 申小宁 (shenxn@alltosun.com) $
 * $Date: Sep 6, 2013 4:13:09 PM $
 * $Id$
 */

class api_helper
{
    private static $api_obj  = '';
    private static $api_path = '';
    private static $request_time = '';

    /**
     * 统一的App接口验证
     * @param array $params
     */
    public static function check_sign($check_params, $need_token = 0)
    {
        // 接口地址
        $api_path = Request::get('url', '', 'post');
        if (!$api_path && isset($_GET['anu'])) {
            $api_path = $_GET['anu'];
        }

        // 请求时间
        $time = tools_helper::post('time', '');
        // 来源
        $source = tools_helper::post('source', '');
        // 版本号
        $version = tools_helper::post('version', '');
        // 手机设备id
        $rid = tools_helper::post('rid', '');
        // 用户身份
        //$token  = tools_helper::post('token', '');
        // 加密串
        $sign = tools_helper::post('sign', '');

        // 测试用
        $do_test = tools_helper::get('do_test', 0);

        if ($do_test != 1) {
            if (!$time || !$source || !$version || !$sign) {
                api_helper::return_api_data(1002, '签名错误[1]');
            }
        }

        self::$request_time = $time;

        // =========== start 记接口请求的log ===============================
        $res_params = array(
            'post' => $_POST,
            'get'  => $_GET
        );

        $imei             = tools_helper::post('rid', '');
        $device_unique_id = tools_helper::post('device_unique_id', '');

        $stat_info = array(
            'source'         => $source,
            'api_path'       => $api_path,
            'request_params' => json_encode($res_params),
            'imei'           => $imei,
            'device_unique_id' => $device_unique_id,
            'date'             => date('Ymd')
        );
        
        self::$api_path = $api_path;
        
        $api_log_id     = _widget('api_log')->record($stat_info);

        MyLogger::apiLog()->info(var_export($res_params, true), array('api_path' => self::$api_path));

        // =========== end 记接口请求的log =================================
        if ($do_test != 1) { 
            if (!array_key_exists($source, api_config::$source)) {
                api_helper::return_api_data(1002, '签名错误[2]');
            }

            // 签名验证
            $params = array(
                'time'    => $time,
                'source'  => $source,
                'rid'     => $rid,
                'version' => $version,
                //'token'  => $token,
                'key'     => api_config::$source_key
            );
            //an_dump($params);
            $params = array_merge($params, $check_params);

            $real_sign = self::encode_sign($params);
            //an_dump($params, $real_sign, $sign);
            //api_dump($sign, $real_sign, $params);
            if ($real_sign != $sign) {
                api_helper::return_api_data(1003, '签名错误[3]');
            }
        }

        // 部分接口需登录才能使用
//          if ($need_token) {
//              if (!$token) {
//                  api_helper::return_api_data(1001, '用户未登录');
//              }

//              $user_info = user_helper::get_user_info_by_access_token($token);
//              if (!$user_info || $user_info['status'] != 1) {
//                  api_helper::return_api_data(1001, '该用户不存在或已禁用');
//              }

//              // 更新rid
//              if (in_array($source, array(1001, 1002)) && $rid) {
//                  update_res_field('user', $user_info['id'], array('rid'=>$rid));
//              }
//          }

        return $api_log_id;
    }

    /**
     * 加密 App 接口sign
     * @param array $params
     * @return string
     */
    public static function encode_sign($params)
    {
        if (!isset($params['key'])) {
            $params['key'] = api_config::$source_key;
        }
        if (isset($params['rid'])) {
            //unset($params['rid']);
        }

        ksort($params, SORT_STRING);
        $params_str = join($params, '');

        //an_dump($params_str);
        $real_sign = md5($params_str);

        return $real_sign;
    }

    /**
     * 接口通用的返回数据
     * @param int code 1000：正确 非1000：错误
     * @param string $message 错误提示
     * @param array $info 回传给接口的信息
     * @return $string
     */
    public static function return_api_data($code, $message, $info = array(), $api_log_id = array(), $do_des = 0)
    {
        $return_data = array(
            'status' => array(
                'code'    => $code,
                'message' => $message
            ),
            'result' => array()
        );

        if ($info) {
            $return_data['result'] = $info;
        }

//          if ($code == 1000) {
        MyLogger::apiLog()->info("msg:{$code}".var_export($return_data, true), array('api_path' => substr( str_replace('/', '_', self::$api_path), 1 ) ));
//          } else {
//              MyLogger::apiLog()->error("msg:{$code}".var_export($return_data, true), array('api_path' => self::$api_path));
//          }

        // 转成json数据
        //$return_data = json_encode($return_data);
        $return_data = json_encode($return_data, JSON_UNESCAPED_UNICODE);

        if ($do_des) {
            // 加密
            //$des = new DesComponent('12345678');
            //$return_data = $des->encrypt($return_data);
        }

        if ($api_log_id) {

            _widget('api_log')->update_record($api_log_id, array('response' => $return_data));
        }

        //p(self::$request_time);
//         $log_info = _mongo ( 'screen', 'call_back_log' )->insertOne (
//                 array(
//                     'time' => (int)self::$request_time,
//                     'request_path' => self::$api_path
//                 )
//             );

        exit($return_data);
    }

    /**
     * 拼装App接口必传参数，供测试时使用
     * @return array
     */
    public static function make_test_base_data()
    {
        $time = tools_helper::get('time', time());
        $source = tools_helper::get('source', 1010);
        $version = tools_helper::get('version', '1.1.0');
        $rid = tools_helper::get('rid', '6E60C59E-FAED-4F8Bcc');
        //$token    = an_request('token', 'ecada87f428b19a9e9c0e8898efcd8bf');

        $base_data = array(
            'time'    => $time,
            'source'  => $source,
            'rid'     => $rid,
            'version' => $version,
            //'token'     => $token
        );
        return $base_data;
    }

    /**
     * 拼上传视频的查看地址
     *
     * @param   string  地址
     */
    public static function get_upload_url($path)
    {
        if (!$path) {
            return '';
        }
        return SITE_URL . '/static/upload' . $path;
    }

    /**
     * 后端统一接口token验证
     * @param string $action
     * @param array $params
     * @return boolean|multitype:unknown multitype:string
     */

    public static function check_token($action = '', $params = array())
    {
        if (!$action) {
            return false;
        }

        $action = strtolower($action);

        if (!in_array($action, array('post', 'get'))) {
            return false;
        }

        if (empty($params['appid'])) {
            if ($action == 'get') $appid = tools_helper::get('appid', '');
            if ($action == 'post') $appid = tools_helper::post('appid', '');

        } else {
            $appid = $params['appid'];
        }

        if (empty($params['timestamp'])) {
            if ($action == 'get') $timestamp = tools_helper::get('timestamp', '');
            if ($action == 'post') $timestamp = tools_helper::post('timestamp', '');

        } else {
            $timestamp = $params['timestamp'];
        }

        if (empty($params['token'])) {
            if ($action == 'get') $token = tools_helper::get('token', '');
            if ($action == 'post') $token = tools_helper::post('token', '');

        } else {
            $token = $params['token'];
        }

        if (!$appid || empty(api_config::$appid_list_by_login[$appid])) {
            api_helper::return_data(1, 'Appid error.');
        }

        $appkey = api_config::$appid_list_by_login[$appid];

        if (!$timestamp) {
            api_helper::return_data(1, 'Timestamp error.');
        }

        if (empty($token)) {
            api_helper::return_data(1, 'Token error.');
        }

        //验证token
        if ($token != md5($appid . '_' . $appkey . '_' . $timestamp)) {
            api_helper::return_data(1, 'Token verification error.');
        }

        return true;
        //返回appkey 和 apppid
        //return array( 'appid' => $appid, 'appkey' => $appkey );
    }

    /**
     * 后端统一接口信息返回
     * @param unknown $errcode 错误号 返回错误信息时错误号必须大于0
     * @param unknown $errmsg 错误信息 没有错误则为'success'
     * @param array $data
     */
    public static function return_data($errcode = 0, $errmsg = 'success', $data = array())
    {
        $return_data = array(
            'errcode' => $errcode,
            'errmsg'  => $errmsg,
            'data'    => $data
        );

        echo json_encode($return_data);
        exit();
    }

    /**
     * 生成token
     * @param unknown $app_id
     * @param unknown $app_key
     * @param unknown $time
     */
    public static function get_token($app_id, $app_key, $time)
    {
        return md5($app_id . '_' . $app_key . '_' . $time);
    }
}

?>