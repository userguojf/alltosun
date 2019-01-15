<?php
/**
  * alltosun.com 终端接口 terminal.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年12月15日 下午4:12:54 $
  * $Id$
  */
class Action
{
    // private $member_id   = 0;
    private $member_info = array();
    private $user_number = '';

    public function __construct()
    {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

        if(in_array($origin, api_config::$white_list)){
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Headers: content-type');
            header('Access-Control-Allow-Methods: GET,POST,OPTIONS');
            header('Access-Control-Allow-Origin:'.$origin);
        }

        //验证secret
        api_helper::check_token('post', $_POST);

        // $this->member_id = member_helper::get_member_id();
        // 获取渠道码
        $this->user_number = tools_helper::post('business_code', '');

        if ( !$this->user_number ) {
            api_helper::return_data(1, '请上传营业厅渠道码');
        }

        $this->member_info =_model('member')->read(array('member_user' => $this->user_number));

        if ( !$this->member_info ) {
            api_helper::return_data(1, '未找到对应渠道码的账号信息');
        }
    }

    /**
     * 获取设备排行
     */
    public function get_terminal_info()
    {

        if ( !$this->member_info ) {
            api_helper::return_data(1, '未找到对应渠道码的账号信息');
        }

        //初始化数据
        $offline_data = $this->get_offline_data($this->member_info['res_name'], $this->member_info['res_id']);
//         $offline_data = array();
        $data = array(
                'offline_data'      => $offline_data,
                'device_data'       => array(),
                'device_data_from'  => 2 //1-亮屏 2-RFID
        );

        $first = tools_helper::Get('first', '');

        //厅权限先取RFID, 没有再取亮屏
        if ($this->member_info['res_name'] == 'business_hall') {

            //RFID排行
            $device_data = $this->get_rfid_device_top();

            if (!$device_data) {
                $data['device_data_from'] = 1;
                //亮屏排行
                $device_data = $this->get_screen_device_top();
            }

        //亮屏排行
        } else {
            $data['device_data_from'] = 1;
            $device_data = $this->get_screen_device_top();
        }

        $data['device_data'] = $device_data;

        api_helper::return_data(0, 'success', $data);
    }

    /**
     * 获取亮屏区域排行
     */
    private function get_screen_device_top()
    {
        //初始化搜索条件
        $filter = _widget('screen')->default_search_filter($this->member_info);
        $filter['day >']    = date('Ymd', strtotime('-7 days'));
        $filter['day <=']   = date('Ymd');

        $result       = _mongo('screen', 'screen_device_stat_day')->aggregate(array(
                array('$match' => get_mongodb_filter($filter)),
                array('$group' => array(
                        '_id'       => array('device_unique_id'  => '$device_unique_id'),
                        'experience_times'     => array('$sum' => '$experience_time'),
                        'unique_id' => array('$first' => '$device_unique_id'),
                )),

        ));
        $res_name   = $this->member_info['res_name'];
        $tmp        = array();

        foreach ($result as $k => $v) {

            $v = (array)$v;

            //查询品牌
            $device_info = screen_device_helper::get_device_info_by_device($v['unique_id']);

            if ( !$device_info ){
                continue;
            }

            $key = $device_info['phone_name'].'|@|'.$device_info['phone_version'];

            if (isset($tmp[$key]['experience_times'])) {
                $tmp[$key]['experience_times'] += $v['experience_times'];
                $tmp[$key]['device_num']++;
            } else {
                $tmp[$key]['experience_times'] = $v['experience_times'];
                $tmp[$key]['device_num']       = 1;
            }
        }

        //重新组装
        foreach ($tmp as $k => $v) {
            if ($res_name != 'business_hall') {
                $value = floor($v['experience_times']/$v['device_num']);
            } else {
                $value = $v['experience_times'];
            }
            $tmp[$k] = $value;
        }

        if ($tmp) {
            arsort($tmp);
        } else {
            return array();
        }

        //取前三条
        $new_data = array_slice($tmp, 0, 3);

        //组装数据
        $device_data = array();
        foreach ($new_data as $k => $v) {

            //取昵称
            list($phone_name, $phone_version) = explode('|@|', $k);
            $info = _model('screen_device_nickname')->read(array('phone_name' => $phone_name, 'phone_version' => $phone_version));

            if ($info) {
                if (!empty($info['name_nickname'])) {
                    $phone_name = $info['name_nickname'];
                }

                if (!empty($info['version_nickname'])) {
                    $phone_version = $info['version_nickname'];
                }
            }

            $device_data[] = array(
                    'phone_name'        => $phone_name,
                    'phone_version'     => $phone_version,
                    'experience_time'   => $v,
                    'up'                => 0,
                    'down'              => 0
            );
        }

        return $device_data;
    }

    /**
     * 获取RFID排行数据
     */
    private function get_rfid_device_top()
    {

        $start_date = date('Y-m-d 00:00:00', strtotime('-7 days'));
        $end_date   = date('Y-m-d 00:00:00');

//         $start_date = '2017-12-01 00:00:00';
//         $end_date = '2017-12-26 23:59:59';

        //先查本周前三
        $list = rfid_helper::get_terminal_top($this->user_number, $start_date, $end_date);

        $new_data = array();
        foreach ($list as $k => $v) {
            $tmp = array(
                    'phone_name'           => $v['phone_name'],
                    'phone_version'        => $v['phone_version'],
                    'experience_time'      => $v['experience_time_sum'],
                    'up'                   => 0,
                    'down'                 => 0
            );

            //             $week = 7*3600*24;
            //             //上一个七天
            //             $start_date = strtotime($start_date) - $week;
            //             $end_date =   strtotime($end_date) - $week;
            //             $last_experience_time = rfid_helper::get_terminal_experience_time($user_number, $v['phone_name'], $v['phone_version'], $start_date, $end_date);

            //             $tmp['last_exprience_time'] = $last_experience_time;

            $new_data[] = $tmp;
        }

        return $new_data;
    }

    private function get_offline_data($res_name, $res_id)
    {
        $date = date('Ymd', time() - 24 * 3600);

        if ( $res_name == 'group' ) {
            $filter = array( 'type' => 1, 'date'=> $date );
        } else {
            $filter = array( 'type' => 1, 'date'=> $date , "{$res_name}_id" => $res_id);
        }

        $offline_info = _model('screen_offline_series_stat')->read( $filter, " ORDER BY `offline_num` DESC " );

        // 该账号下没有离线设备
        if ( !$offline_info ) return array();

        $phone_name = screen_helper::get_phone_nickname('name', $offline_info['device_unique_id']);
        $phone_version = screen_helper::get_phone_nickname('version', $offline_info['device_unique_id']);

        $data = array('phone_name' => $phone_name, 'phone_version' => $phone_version, 'days' => $offline_info['offline_num']);

        return $data;
    }

//     public function get_device_top()
//     {
//         $user_number = tools_helper::Post('business_code', '');

//         if (!$user_number) {
//             api_helper::api_return_data(1, '营业厅渠道编码不能为空');
//         }

//         $user_number = $user_number;

//         $business_info = business_hall_helper::get_business_hall_info(array('user_number' => $user_number));

//         if (!$business_info) {
//             api_helper::api_return_data(1, '此渠道编码不存在');
//         }

//         $filter = array(
//                 'day >'             => date('Ymd', strtotime('-7 days')),
//                 'day <='            => date('Ymd'),
//                 'business_id'       => $business_info['id']
//         );

//         $result       = _mongo('screen', 'screen_device_stat_day')->aggregate(array(
//                 array('$match' => get_mongodb_filter($filter)),
//                 array('$group' => array(
//                         '_id'       => array('device_unique_id'  => '$device_unique_id'),
//                         'experience_times'     => array('$sum' => '$experience_time'),
//                         'unique_id' => array('$first' => '$device_unique_id'),
//                 )),
//                 array('$sort'=>array('count'=>-1)),
//                 array('$limit'=>3)
//         ));

//         $data = array();

//         foreach ($result as $k => $v) {

//             $v = (array)$v;

//             $device_info = _model('screen_device')->read(array('device_unique_id'=>$v['unique_id']));

//             if ( !$device_info ){
//                 continue;
//             }

//             $data[] = array(
//                     'experience_time'   => $v['experience_times'],
//                     'phone_name'        => $device_info['phone_name_nickname'] ? $device_info['phone_name_nickname'] : $device_info['phone_name'] ,
//                     'phone_version'     => $device_info['phone_version_nickname'] ? $device_info['phone_version_nickname'] : $device_info['phone_version']
//             );
//         }

//         if (count($data) > 0) {
//             api_helper::return_data(0, 'success', $data);
//         }

//         //查询rfid
//         if (ONDEV) {
//             $api_url = 'http://201512awifi.alltosun.net/api/mac/rfid/get_terminal_top';
//         } else {
//             $api_url = 'http://wifi.pzclub.cn/api/mac/rfid/get_terminal_top';
//         }

//         $res     = curl_post($api_url, array('user_number' => $user_number));

//         $arr = json_decode($res, true);

//         if (!isset($arr['result']) || !$arr['result']) {
//             api_helper::return_data(0, 'success', array());
//         }

//         api_helper::return_data(0, 'success', $arr['result']);
//     }

    /**
     * 接收post发送的json数据，并转换为数组
     */
    private function receive_post()
    {
        $request_data      = file_get_contents('php://input');

        if (!$request_data ) {
            api_helper::return_api_data(1003, '数据为空');
        }

        $request_data = json_decode($request_data, true);

        if (!is_array($request_data) || !$request_data) {
            api_helper::return_api_data(1003, '数据格式错误');
        }

        return $request_data;

    }
}