<?php
/**
  * alltosun.com 探针接口 probe.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2018年3月23日 上午11:12:43 $
  * $Id$
  */
probe_helper::load('stat', 'trait');

class Action
{
    use stat;
    private $member_info = array();
    private $user_number = '';
    private $toilet_type = 0;

    public function __construct()
    {
//         $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

//         if(in_array($origin, api_config::$white_list)){
//             header('Access-Control-Allow-Credentials: true');
//             header('Access-Control-Allow-Headers: content-type');
//             header('Access-Control-Allow-Methods: GET,POST,OPTIONS');
//             header('Access-Control-Allow-Origin:'.$origin);
//         }
//         global $mc_wr;
//         $mc_wr->set('wangjf_test', json_encode($_POST), 120);
        //验证secret
        api_helper::check_token('post', $_POST);

        // $this->member_id = member_helper::get_member_id();

        //排队那边目前仅提供了 toiletId 为  7b1d8ec4-dc77-45d8-9f69-6787b536f9b3
        // 获取渠道码
        $this->user_number = '1111111111001';  //暂时定死

        if ( !$this->user_number ) {
            api_helper::return_data(1, '请上传营业厅渠道码');
        }

        $toiletId        = tools_helper::Post('toiletId', '');

        //截取最后一位 0-男 1-女
        if (substr($toiletId, -1) != 0 ) {
            $this->toilet_type = 1;
        }

    }

    /**
     * 获取停留时长接口
     */
    public function get_remain_time()
    {
        $start_time = tools_helper::Post('start_time', '');
        $end_time   = tools_helper::Post('end_time', '');
        $mac        = tools_helper::Post('mac', '');

        if ($mac == '00:1c:c2:2f:b6:9c') {
            $this->user_number = 1101062001955;
            $device = '7t5a4dbf59f2813';
        }

        if ($mac == '00:1c:c2:2f:b8:f8') {
            $this->user_number = 1111111111001;
            $device = 'mz5a4dbf59f1915';
        }

        if ($mac == '00:1c:c2:2f:b8:f6') {
            $this->user_number = 1111111111001;
            $device = 'mz5a4dbf59d97f3';
        }

        //查询营业厅
        $business_hall_info = business_hall_helper::get_business_hall_info(array('user_number' => $this->user_number));
        if (!$business_hall_info) api_helper::return_data(1, '门店不存在');

        //转换mac为纯数字
        $mac = probe_helper::mac_decode($mac);

        if (!$start_time) api_helper::return_data(1, '起始时间不能为空');
        if (!$end_time) api_helper::return_data(1, '截止时间不能为空');
        if (!$mac) api_helper::return_data(1, '用户mac地址不能为空');

        $db = get_db($business_hall_info['id'], 'hour');

        if (!$db) api_helper::return_data(1, '暂不支持此厅');

        $start_date = date('Ymd', strtotime($start_time));
        $end_date   = date('Ymd', strtotime($end_time));

        // 小时开始时间
        $start = strtotime(date('Y-m-d H:00:00', strtotime($start_time)));
        // 小时结束时间戳
        $end   = strtotime(date('Y-m-d H:59:59', strtotime($end_time)));

        $filter = array(
                'date >=' => $start_date,
                'date <=' => $end_date,
                'dev'     => $device,
                'mac'     => $mac,
                'frist_time >=' => $start,
                'frist_time <=' => $end,
                'is_indoor' => 1,
        );

        $remain_time = $db->getFields('remain_time', $filter);
        $remain_time = array_sum($remain_time);
        api_helper::return_data(0, 'success', array('remain_time' => $remain_time));
    }

}