<?php
/**
  * alltosun.com 专柜操作 shoppe.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年8月16日 下午6:59:10 $
  * $Id$
  */
class Action
{

    /**
     * 来源
     * @var String 0-未知 1 - RFID 2 - 数字地图 3 - 亮屏
     */
    private static $add_from=1;

    public function __construct()
    {
        //验证secret
        //$this->check_secret();

    }

    /**
     * 添加专柜
     */
    public function add()
    {

        //接收post数据
        $request_data = $this->receive_post();

        if (isset($request_data['user_number']) && $request_data['user_number']) {
            $user_number = $request_data['user_number'];
        } else {
            $this->return_data(1, '参数为空:user_number');
        }

        if (isset($request_data['phone_name']) && $request_data['phone_name']) {
            $phone_name = $request_data['phone_name'];
        } else {
            $this->return_data(1, '参数为空:phone_name');
        }

        if (isset($request_data['shoppe_name']) && $request_data['shoppe_name']) {
            $shoppe_name = $request_data['shoppe_name'];
        } else {
            $this->return_data(1, '参数为空:shoppe_name');
        }

        $business_hall_info = business_hall_helper::get_business_hall_info(array('user_number' => $user_number));

        if (!$business_hall_info) {
            $this->return_data(1, '此用户编码不存在');
        }

        //查询专柜
        $shoppe_info = shoppe_helper::get_shoppe_info(array('business_id' => $business_hall_info['id'], 'status' => 1, 'shoppe_name' => $shoppe_name));

        if ($shoppe_info) {
            $this->return_data(1, '此专柜已存在');
        }

        $new_data = array();

        $new_data = array(
                'province_id'   => $business_hall_info['province_id'],
                'city_id'       => $business_hall_info['city_id'],
                'area_id'       => $business_hall_info['area_id'],
                'business_id'   => $business_hall_info['id'],
                'phone_name'    => $phone_name,
                'shoppe_name'   => $shoppe_name,
                'add_from'      => self::$add_from
        );

        //1-RFID 2-数字地图 3-亮屏
        $result = _widget('shoppe')->add_shoppe($new_data, self::$add_from);

        if ($result === false) {
            $this->return_data(1, '专柜添加失败');
        }

        $shoppe_info = _model('rfid_shoppe')->read($result);

        if (!$shoppe_info) {
            $this->return_data(1, '专柜获取失败');
        }

        $this->return_data(0, 'success', array('shoppe_info' => $shoppe_info));

    }

    /**
     * 删除专柜
     */
    public function delete()
    {
        //接收post数据
        $request_data = $this->receive_post();

        if (isset($request_data['shoppe_id']) && $request_data['shoppe_id']) {
            $shoppe_id = $request_data['shoppe_id'];
        } else {
            $this->return_data(1, '参数为空:shoppe_id');
        }

        $get_info_filter = array('id' => $shoppe_id, 'status' => 1);

        if ( !shoppe_helper::get_shoppe_info($get_info_filter) ) {
            $this->return_data(1, '专柜信息不存在或已被删除');
        }

        $label_info = _model('screen_device')->read(array('shoppe_id' => $shoppe_id, 'status' => 1));

        if ($label_info) {
            $this->return_data(1, '专柜无法删除：此专柜存在亮屏设备');
        }

        $result = _widget('shoppe')->delete_shoppe($shoppe_id, self::$add_from);

        if ($result === false) {
            $this->return_data(1, '专柜删除失败');
        }

        $this->return_data(0, 'success');
    }





    /**
     * 查询专柜
     */
    public function query()
    {

        //接收post数据
        $request_data = $this->receive_post();

        if (isset($request_data['shoppe_id']) && $request_data['shoppe_id']) {
            $shoppe_id = $request_data['shoppe_id'];
        } else {
            $this->return_data(1, '参数为空:shoppe_id');
        }

        $get_info_filter = array('id' => $shoppe_id, 'status' => 1);

        $info = shoppe_helper::get_shoppe_info($get_info_filter);

        if ($info === false) {
            $this->return_data(1, '专柜查询失败');
        }

        $new_info = $this->handle_shoppe_data($info);

        $this->return_data(0, 'success', $new_info);

    }

    /**
     * 查询专柜列表
     */
    public function query_list()
    {

        //接收post数据
        $request_data = $this->receive_post();

        if (isset($request_data['user_number']) && $request_data['user_number']) {
            $user_number = $request_data['user_number'];
        } else {
            $this->return_data(1, '参数为空:user_number');
        }

        $business_hall_info = business_hall_helper::get_business_hall_info(array('user_number' => $user_number));

        if (!$business_hall_info) {
            $this->return_data(1, '此用户编码不存在');
        }

        $member_info = _uri('member', array('member_user' => $user_number));

        $filter = array(
                'status'        => 1
        );

        if (in_array($member_info['res_name'], array('area', 'city', 'province'))) {
            $filter["{$member_info['res_name']}_id"] =$member_info['res_id'];
        } else if ($member_info['res_name'] == 'business_hall'){
            $filter["business_id"] = $member_info['res_id'];
        } else if ($member_info['res_name'] == 'group') {

        } else {
            $this->return_data(0, '未知的管理员权限');
        }

        $shoppe_list = _widget('shoppe')->get_shoppe_list($filter);

        if ($shoppe_list === false) {
            $this->return_data(1, '专柜查询失败');
        }

        $new_list = array();

        foreach ($shoppe_list as $k => $v) {
            $new_list[] = $this->handle_shoppe_data($v);
        }

        $this->return_data(0, 'success', $new_list);
    }

    /**
     * 更新专柜
     */
    public function update()
    {
        //接收post数据
        $request_data = $this->receive_post();

        if (isset($request_data['user_number']) && $request_data['user_number']) {
            $user_number = $request_data['user_number'];
        } else {
            $this->return_data(1, '参数为空:user_number');
        }

        if (isset($request_data['phone_name']) && $request_data['phone_name']) {
            $phone_name = $request_data['phone_name'];
        } else {
            $this->return_data(1, '参数为空:phone_name');
        }

        if (isset($request_data['shoppe_name']) && $request_data['shoppe_name']) {
            $shoppe_name = $request_data['shoppe_name'];
        } else {
            $this->return_data(1, '参数为空:shoppe_name');
        }

        if (isset($request_data['shoppe_id']) && $request_data['shoppe_id']) {
            $shoppe_id = $request_data['shoppe_id'];
        } else {
            $this->return_data(1, '参数为空:shoppe_id');
        }

        $business_hall_info = business_hall_helper::get_business_hall_info(array('user_number' => $user_number));

        if (!$business_hall_info) {
            $this->return_data(1, '此用户编码不存在');
        }

        $filter = array(
                'shoppe_name' => $shoppe_name,
                'status'      => 1,
                'business_id' => $business_hall_info['id']
        );

        if (_uri('rfid_shoppe', $filter)) {
            $this->return_data(1, "本厅已存在[{$shoppe_name}]");
        }

        if (!shoppe_helper::get_shoppe_info($shoppe_id)) {
            $this->return_data(1, '此专柜不存在或已被删除');
        }

        $update_data = array(
                'phone_name'    => $phone_name,
                'shoppe_name'   => $shoppe_name,
        );

        $filter = array(
                'id' => $shoppe_id,
        );

        $result = _widget('shoppe')->update_shoppe($filter, $update_data);

        if ($result === false) {
            $this->return_data(1, '专柜更新失败');
        }

        $this->return_data(0, 'success', array('shoppe_id' => $shoppe_id));

    }

    /**
     * 查询专柜RFID列表
     */
    public function query_shoppe_rfid_list()
    {

        //接收post数据
        $request_data = $this->receive_post();

        if (isset($request_data['user_number']) && $request_data['user_number']) {
            $user_number = $request_data['user_number'];
        } else {
            $this->return_data(1, '参数为空:user_number');
        }

        if (isset($request_data['shoppe_id']) && $request_data['shoppe_id']) {
            $shoppe_id = $request_data['shoppe_id'];
        } else {
            $this->return_data(1, '参数为空:shoppe_id');
        }

        //营业厅表不存在admin， 要替换为 JT_YYT
        if ($user_number == 'admin'){
            $user_number = 'JT_YYT';
        }

        $business_hall_info = business_hall_helper::get_business_hall_info(array('user_number' => $user_number));

        if (!$business_hall_info) {
            $this->return_data(1, '此用户编码不存在');
        }

        $filter = array(
                'shoppe_id'     => $shoppe_id
        );

        //账号级别 0-集团 1-省 2-市 3- 区县 4、5 营业厅
        if (in_array($business_hall_info['type'], array(4, 5))) {
            $filter['business_hall_id'] = $business_hall_info['id'];
        } else if ($business_hall_info['type'] == 3) {
            $filter['area_id'] = $business_hall_info['area_id'];
        } else if ($business_hall_info['type'] == 2) {
            $filter['city_id'] = $business_hall_info['city_id'];
        } else if ($business_hall_info['type'] == 1) {
            $filter['province_id'] = $business_hall_info['province_id'];
        }

        $rfid_list = _model('rfid_label')->getList($filter);


        $new_list = array();

        foreach ($rfid_list as $k => $v) {
            $new_list[] = $this->handle_rfid_data($v);
        }

        $this->return_data(0, 'success', $new_list);

    }

    /**
     * 生成专柜后缀
     */
    public function generate_postfix()
    {
        //接收post数据
        $request_data = $this->receive_post();

        if (isset($request_data['user_number']) && $request_data['user_number']) {
            $user_number = $request_data['user_number'];
        } else {
            $this->return_data(1, '参数为空:user_number');
        }

        if (isset($request_data['phone_name']) && $request_data['phone_name']) {
            $phone_name = $request_data['phone_name'];
        } else {
            $this->return_data(1, '参数为空:phone_name');
        }

        if (isset($request_data['shoppe_name']) && $request_data['shoppe_name']) {
            $shoppe_name = $request_data['shoppe_name'];
        } else {
            $this->return_data(1, '参数为空:shoppe_name');
        }

        $business_hall_info = business_hall_helper::get_business_hall_info(array('user_number' => $user_number));

        if (!$business_hall_info) {
            $this->return_data(1, '此用户编码不存在');
        }

        $postfix = shoppe_helper::generate_shoppe_ch_postfix($phone_name, $shoppe_name, $business_hall_info['id']);

        if (!$postfix) {
            $this->return_data(1, "[{$shoppe_name}]系列在本厅已超出最大限制[二十]", array());
        };

        $this->return_data(0, 'success', array('postfix' => $postfix));

    }

    /**
     * 检测秘钥
     * @param int    $appid
     * @param string $timestamp
     * @param string $token
     */
    private function check_secret()
    {
        $appid      = tools_helper::Get('appid', '');
        $timestamp  = tools_helper::Get('timestamp', '');
        $token      = tools_helper::Get('token', '');

        if (!$appid || !isset(api_config::$appid_list_by_login[$appid])) {
            $this->return_data(1, '参数错误:appid');
        }

        //判断时间戳
        if (!$timestamp) {
            $this->return_data(1, '参数错误:timestamp');
        }

        $appkey = api_config::$appid_list_by_login[$appid];

        //判断token
        if ($token != md5($appid.'_'.$appkey.'_'.$timestamp)) {
            $this->return_data(1, '参数错误:token');
        }
    }

    /**
     * 接收post发送的json数据，并转换为数组
     */
    private function receive_post()
    {
        $request_data      = file_get_contents('php://input');

        if (!$request_data ) {
            $this->return_data(1, '数据为空');
        }

        $request_data = json_decode($request_data, true);

        if (!is_array($request_data) || !$request_data) {
            $this->return_data(1, '数据格式错误');
        }

        return $request_data;

    }


    /**
     * 返回数据
     * @param unknown $code
     * @param unknown $message
     * @param unknown $data
     */
    private function return_data($code=0, $message='', $data=array())
    {
        $return_data = array(
                'errcode'   => $code,
                'errmsg'  => $message,
                'data'      => $data
        );

        if ($data) {
            $return_data['data'] = $data;
        }

        echo json_encode($return_data);
        exit;
    }

    /**
     * 处理专柜数据
     * @param unknown $data
     */
    private function handle_shoppe_data($data)
    {
        if (!$data) {
            return array();
        }
        $new_data = array();
        $new_data['phone_name']  = $data['phone_name'];
        $new_data['shoppe_name'] = $data['shoppe_name'];
        $new_data['shoppe_id']   = $data['id'];
        $new_data['user_number'] = business_hall_helper::get_business_hall_info($data['business_id'], 'user_number');

        return $new_data;
    }

    /**
     * 处理RFID标签数据
     * @param unknown $data
     */
    private function handle_rfid_data($data)
    {
        if (!$data) {
            return array();
        }

        //动作数
        $action_num_filter = array(
                'date'              => date('Ymd'),
                'label_id'          => $data['label_id'],
                'status'            => 1,
                'business_id'       => $data['business_hall_id'],
                'phone_name'        => $data['name'],
                'phone_version'     => $data['version'],
                'end_timestamp >'   => 0
        );

        $action_num =  _model('rfid_record_detail')->getTotal($action_num_filter);

        //体验时长
        $experience_time_filter = array(
                'date'              => date('Ymd'),
                'label_id'          => $data['label_id'],
                'business_id'       => $data['business_hall_id'],
                'phone_name'        => $data['name'],
                'phone_version'     => $data['version']
        );

        $experience_time = _uri('rfid_record', $experience_time_filter, 'experience_time');

        $new_data = array();
        $new_data['label_id']           = $data['label_id'];
        $new_data['IMEI']               = $data['imei'];
        $new_data['action_num']         = $action_num;
        $new_data['experience_time']    = $experience_time;
        $new_data['phone_brand']        = $data['name'];
        $new_data['phone_version']      = $data['version'];


        return $new_data;
    }
}