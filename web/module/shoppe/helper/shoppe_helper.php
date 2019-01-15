<?php
/**
  * alltosun.com 专柜helper shoppe_helper.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年8月23日 下午3:53:28 $
  * $Id$
  */

class shoppe_helper
{
    /**
     * 获取专柜信息
     * @param unknown $filter
     * @param string $field
     */
    public static function get_shoppe_info($filter, $field='')
    {

        if (!$filter) {
            return '';
        }

        if (!$field) {
            return _uri('rfid_shoppe', $filter);
        }

        return _uri('rfid_shoppe', $filter, $field);
    }

    /**
     * 获取专柜的rfid 数量
     * @param unknown $business_hall_id
     * @param unknown $shoppe_id
     */
    public static function get_shoppe_rfid_count($business_hall_id, $shoppe_id)
    {

        if (!$business_hall_id || !$shoppe_id) {
            return 0;
        }

        $filter = array(
                'shoppe_id' => $shoppe_id,
                'business_hall_id' => $business_hall_id
        );

        return _model('rfid_label')->getTotal($filter);

    }

    /**
     * 获取专柜的亮屏 数量
     * @param unknown $business_hall_id
     * @param unknown $shoppe_id
     */
    public static function get_shoppe_screen_count($business_hall_id, $shoppe_id)
    {

        if (!$business_hall_id || !$shoppe_id) {
            return 0;
        }

        $filter = array(
                'shoppe_id'         => $shoppe_id,
                'business_id'       => $business_hall_id,
                'status'            => 1
        );

        return _model('screen_device')->getTotal($filter);

    }

    /**
     * 获取营业厅专柜列表
     * @param unknown $res_name
     * @param unknown $res_id
     */
    public static function get_business_hall_shoppe($res_name, $res_id)
    {
        if ($res_name != 'business_hall') {
            return false;
        }

        $filter = array(
                'business_id' => $res_id,
                'status'      => 1
        );

        return _model('rfid_shoppe')->getList($filter, ' ORDER BY `id` DESC ');
    }

    /**
     * 营业厅是否存在专柜
     * @param unknown $res_name
     * @param unknown $res_id
     */
    public static function business_hall_is_exists_shoppe($res_name, $res_id)
    {
        if ($res_name != 'business_hall') {
            return false;
        }

        $filter = array(
                'business_id' => $res_id,
                'status'      => 1
        );

        if (_uri('rfid_shoppe', $filter)){
            return true;
        }

        return false;
    }

    /**
     * 生成数字地图接口token
     */
    public static function generate_dm_api_token($timestamp)
    {
        $api_config = shoppe_config::$dm_api_config;

        return md5($api_config['appid'].'_'.$api_config['appkey']."_".$timestamp);
    }



//     /**
//      * 生成中文专柜号
//      */
//     public static function generate_shoppe_ch_postfix($phone_name, $shoppe_name, $business_id)
//     {

//         if (!$shoppe_name || !$phone_name || !$business_id) {
//             return false;
//         }

//         //查询最后一条专柜信息
//         $filter = array(
//                 'shoppe_name LIKE ' => "{$shoppe_name}%",
//                 'phone_name'        => $phone_name,
//                 'business_id'       => $business_id,
//                 'status'            => 1
//         );
//         $shoppe_info_list = _model('rfid_shoppe')->getList($filter, ' ORDER BY `id` DESC ');

//         //没有此系列专柜，则默认为后缀 “一”
//         if (count($shoppe_info_list) == 0) {
//             return '一';
//         }

//         //中文后缀数组
//         $ch      = array('一','二','三','四','五','六','七','八','九', '十');

//         //当前最大后缀在数组中的key，默认为-1
//         $max_ch_key = -1;

//         //array_flip 后缀的键值互换
//         $flip_ch = array_flip($ch);

//         //循环出已有后缀的最大后缀下标
//         foreach ($shoppe_info_list as $k => $v) {
//             //截取后缀（最后一位）
//             $postfix = mb_substr($v['shoppe_name'],-1, 1, 'utf-8');
//             if (!isset($flip_ch[$postfix])) {
//                 continue;
//             }

//             if ($max_ch_key < $flip_ch[$postfix]) {
//                 $max_ch_key = $flip_ch[$postfix];
//             }
//         }

//         //有可能存在其他系列后缀，比如 “华为专柜1” 或 “华为专柜其他”， 直接返回 “一”
//         if ($max_ch_key < 0) {
//             return '一';
//         }

//         //超出后缀 “十”则返回false, 否则返回正确的后缀
//         return isset($ch[$max_ch_key + 1]) ? $ch[$max_ch_key + 1 ] : false;

//     }
    /**
     * 生成中文专柜号v2
     */
    public static function generate_shoppe_ch_postfix($phone_name, $shoppe_name, $business_id)
    {
        if (!$shoppe_name || !$phone_name || !$business_id) {
            return false;
        }

        //查询最后一条专柜信息
        $filter = array(
                'shoppe_name LIKE ' => "{$shoppe_name}%",
                'phone_name'        => $phone_name,
                'business_id'       => $business_id,
                'status'            => 1
        );

        $shoppe_info_list = _model('rfid_shoppe')->getList($filter, ' ORDER BY `id` DESC ');

        //没有此系列专柜，则默认为后缀 “一”
        if (count($shoppe_info_list) == 0) {
            return '一';
        }

        //中文后缀数组
        $ch      = array(
                '一','二','三','四','五','六','七','八','九', '十',
                '十一','十二','十三','十四','十五','十六','十七','十八','十九', '二十',
        '');

        //当前最大后缀在数组中的key，默认为-1
        $max_ch_key = -1;

        //array_flip 后缀的键值互换
        $flip_ch = array_flip($ch);

        //循环出已有后缀的最大后缀下标
        foreach ($shoppe_info_list as $k => $v) {
            //截取后缀（最后两位）
            $postfix = mb_substr($v['shoppe_name'],-2, 2, 'UTF-8');

            if (!isset($flip_ch[$postfix])) {
                //截取后缀（最后一位）
                $postfix = mb_substr($v['shoppe_name'],-1, 1, 'UTF-8');

                if (!isset($flip_ch[$postfix])) {
                    continue;
                }
            }

            if ($max_ch_key < $flip_ch[$postfix]) {
                $max_ch_key = $flip_ch[$postfix];
            }
        }

        //有可能存在其他系列后缀，比如 “华为专柜1” 或 “华为专柜其他”， 直接返回 “一”
        if ($max_ch_key < 0) {
            return '一';
        }

        //超出后缀 “二十”则返回false, 否则返回正确的后缀
        return isset($ch[$max_ch_key + 1]) ? $ch[$max_ch_key + 1 ] : false;

    }

    /**
     * 写入api日志
     * @param unknown $table
     * @param unknown $data
     */
    public static function write_api_log($action, $respons_data, $request_data='')
    {

        $api_data = array(
                'res_name'      => $action,
                'response_body' => $respons_data,
                'request_data'  => $request_data
        );
        if ($respons_data) {
            $result = json_decode($respons_data, true);

            if (!empty($result['httpStatus'])) {
                $api_data['response_code'] = $result['httpStatus'];
            }

        } else {
            $api_data['response_body'] = '接口返回无数据';
        }
        return _model('rfid_api_logs')->create($api_data);
    }
    /**
     * post请求需要加上 'application/json'
     * @param str $url
     * @param array $data
     */
    public static function dm_curl_post($url, $data)
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
}