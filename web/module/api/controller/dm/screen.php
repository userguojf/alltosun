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
            api_helper::return_data('20001', api_config::$error_code['20001']);
        }

        $this->member_info =_model('member')->read(array('member_user' => $this->user_number));

        if ( !$this->member_info ) {
            api_helper::return_data('20002', api_config::$error_code['20002']);
        }
    }

    /**
     * 获取设备信息统计接口
     */
    public function device_stat()
    {

        if ( !$this->member_info ) {
            api_helper::return_data('20002', api_config::$error_code['20002']);
        }

        //获取已覆盖门店
        $cover_business_ids     = $this->get_cover_business_hall();
        $cover_business_count   = count($cover_business_ids);

        //获取上月有效门店
        $pre_valid_business_ids     = $this->get_pre_valid_business_hall();
        $pre_valid_business_count   = count($valid_business_ids);

        //获取所有终端
        $devices                = $this->get_device();
        $device_count           = count($devices);

        //获取今日离线设备
        $offonline_devices      = $this->get_offonline_device();
        $offonline_device_count = count($offonline_devices);

        //计算离线率
        $offonline_device_rate = round($offonline_device_count / $device_count * 100, 2).'%';

        //获取终端体验次数排行
        $experience_data = $this->get_brand_experience('action_num');

        $data = array(
                'cover_business_hall_count'         => $cover_business_count, //覆盖厅店数
                'pre_valid_business_hall_count'     => $pre_valid_business_count, //上月有效厅店数
                'device_count'                      => $device_count, //终端总量
                'offonline_device_count'            => $offonline_device_count, //今日离线设备量
                'offonline_device_rate'             => $offonline_device_rate, //今日离线设备率
                'experience_data'                   => $experience_data, //今日体验数据 前十名
        );

        api_helper::return_data(0, 'success', $data);
    }

    /**
     * 获取品牌动作数
     * @return array[]
     */
    private function get_brand_experience($field)
    {

        $filter = array();

        if ($this->member_info['res_name'] == 'business_hall') {
            $filter['business_id'] = $this->member_info['res_id'];
        } else if ($this->member_info['res_name'] != 'group') {
            $filter[$this->member_info['res_name'].'_id'] = $this->member_info['res_id'];
        }

        $filter['day'] = date('Ymd');

        $result       = _mongo('screen', 'screen_device_stat_day')->aggregate(array(
                array('$match' => get_mongodb_filter($filter)),
                array('$group' => array(
                        '_id'       => array('device_unique_id'  => '$device_unique_id', 'business_id' => '$business_id'),
                        $field      => array('$sum' =>'$'.$field),
                        'device_unique_id'  => array('$first' => '$device_unique_id'),
                        'business_id'       => array('$first' => '$business_id'),
                )),
                //一定要排序，因为设备换厅后，一定是在最后面的
                array('$sort'=>array('_id'=>-1)),
        ));

        //去除已下柜的设备
        unset($filter['day']);
        $filter['status'] = 0;
        $where  = to_where_sql($filter);
        $off_device = _model('screen_device')->getAll(' SELECT business_id, device_unique_id FROM `screen_device` '.$where);
        $new_off_device = array();
        foreach ($off_device as $k => $v) {
            $new_off_device[$v['business_id'].'_'.$v['device_unique_id']] = $v['device_unique_id'];
        }

        $new_result = array();
        foreach ($result as $k => $v) {
            $v = (array)$v;
            if (!isset($new_off_device[$v['business_id'].'_'.$v['device_unique_id']])) {
                $new_result[$v['device_unique_id']] = $v;
            }
        }

        //将体验数据按品牌型号分组
        $arr = array();
        foreach ($new_result as $k => $v) {
            $v = (array)$v;
            //获取机型id
            $device_nickname_id = screen_device_helper::get_device_info_by_device($v['device_unique_id'], 'device_nickname_id');
            if (!$device_nickname_id) {
                continue;
            }

            if (empty($arr[$device_nickname_id])) {
                $arr[$device_nickname_id][$field]  = $v[$field];
                $arr[$device_nickname_id]['device_num']        = 1;
            } else {
                $arr[$device_nickname_id][$field]  += $v[$field];
                $arr[$device_nickname_id]['device_num']        += 1;
            }
        }

        //将device_nickname_id 放到数组值中
        $new_data = array();
        foreach ( $arr as $k => $v ) {

            if ($field == 'experience_time') {
                //转换为分钟
                $v['data']               = round($v[$field] / 60, 1); //分钟
            } else {
                $v['data']               = $v[$field];
            }

            $v['device_nickname_id']        = $k;

            $sorts[]                        = $v['data'];
            $new_data[]                     = $v;
        }

        if ($sorts) {
            array_multisort($sorts, SORT_DESC, $new_data);
        }

        $new_data = array_slice($new_data, 0, 10);  //取前10

        //拼接最终数据
        $data = array();
        foreach ($new_data as $k => $v) {
            //获取设备昵称
            $nickname_info = screen_device_helper::get_device_nickname_info($v['device_nickname_id']);

            if ( !$nickname_info ) {
                continue;
            }

            $brand_name = empty($nickname_info['name_nickname']) ? $nickname_info['phone_name'] : $nickname_info['name_nickname'];
            $brand_name .= ' ';
            $brand_name .= empty($nickname_info['version_nickname']) ? $nickname_info['phone_version'] : $nickname_info['version_nickname'];

            $data[]     = array('brand_name' => $brand_name, 'data' => $v['data']);
        }

        return $data;
    }

    /**
     * 获取离线终端
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_offonline_device()
    {
        $filter = array();

        if ($this->member_info['res_name'] == 'business_hall') {
            $filter['business_id'] = $this->member_info['res_id'];
        } else if ($this->member_info['res_name'] != 'group') {
            $filter[$this->member_info['res_name'].'_id'] = $this->member_info['res_id'];
        }

        $filter['day']          = date('Ymd');

        //获取今日在线设备
        $online_device = _model('screen_device_online_stat_day')->getFields('device_unique_id', $filter, 'GROUP BY device_unique_id');

        unset($filter['day']);
        $filter['status']       = 1;

        //获取归属地内所有设备
        $all_device = _model('screen_device')->getFields('device_unique_id', $filter, 'GROUP BY device_unique_id');

        $offonline = array_diff($all_device, $online_device);

        return $offonline;

    }

    /**
     * 获取设备量
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_device()
    {
        //获取有效设备
        $filter['status'] = 1;

        if ($this->member_info['res_name'] == 'business_hall') {
            $filter['business_id'] = $this->member_info['res_id'];
        } else if ($this->member_info['res_name'] != 'group') {
            $filter[$this->member_info['res_name'].'_id'] = $this->member_info['res_id'];
        }

        //取出所有设备取出
        $devices = _model('screen_device')->getFields('device_unique_id', $filter, 'GROUP BY `device_unique_id`');

        return $devices;
    }
    /**
     * 获取已覆盖门店
     */
    private function get_cover_business_hall()
    {
        $filter = array();

        if ($this->member_info['res_name'] == 'business_hall') {
            $filter['business_id'] = $this->member_info['res_id'];
        } else if ($this->member_info['res_name'] != 'group') {
            $filter[$this->member_info['res_name'].'_id'] = $this->member_info['res_id'];
        }

        $filter['status'] = 1;

        //获取已覆盖门店
        return _model('screen_device')->getFields('business_id', $filter, ' GROUP BY `business_id` ');
    }

    /**
     * 获取上月有效厅店
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_pre_valid_business_hall()
    {
        $filter = array();

        if ($this->member_info['res_name'] == 'business_hall') {
            $filter['business_id'] = $this->member_info['res_id'];
        } else if ($this->member_info['res_name'] != 'group') {
            $filter[$this->member_info['res_name'].'_id'] = $this->member_info['res_id'];
        }

        if (!$filter) {
            $filter = array(1=>1);
        }

        $filter['month']            = date('Ym', strtotime('-1 month'));
        $filter['active_days >=']   = 15;       //有效门店定义：活跃15天以上的设备门店

        //有效厅店
        $business_id = _model('screen_device_active_stat_month')->getFields('business_id',$filter, ' GROUP BY `business_id` ');

        return $business_id;
    }


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