<?php
/**
 * alltosun.com 提供数字地图接口（今日摘机体验次数-前三名） experience.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-3-19 下午3:07:31 $
 * $Id$
 */

class Action
{
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

    public function __call($action = '', $param = array())
    {
        $date = tools_helper::post('date', 0);

        $business_hall_info = _model('business_hall')->read(array('user_number' => $this->user_number));

        if ( !$business_hall_info ) api_helper::return_data(1, '未找到对应营业厅信息');

        $status_data = $this->get_status_data($business_hall_info['id']);

        $filter['business_id'] = ( int )$business_hall_info['id'];
        $filter['day']         = $date ? $date : ( int )date('Ymd');

        $sort = [
            'limit' => 3,
            'sort'  => ['action_num' => -1],
        ];

        $device_experience_list = [];

        $list = _mongo('screen', 'screen_device_stat_day')->find($filter, $sort);

        $list = $list->toArray();
// p($list);exit();
//         if ( $list )  {
            foreach ($list as $k => $v) {
                $device_info = _uri('screen_device',
                        array('device_unique_id' => $v['device_unique_id'], 'status' => 1)
                );

                // 跳过下架的设备  和 统计上来次数为0的过滤
                if ( !$device_info || !$v['action_num'] ) {
                    continue;
                } else {
                    $phone_name    = $device_info['phone_name_nickname'] ? $device_info['phone_name_nickname'] : $device_info['phone_name'];
                    $phone_version = $device_info['phone_version_nickname'] ? $device_info['phone_version_nickname'] : $device_info['phone_version'];
                }

                $device_experience_list[] = array(
                        'phone_name'    => $phone_name,
                        'phone_version' => $phone_version,
                        'num'           => $v['action_num'],
                );
            }
//         }

        $data = array(
                'status' => $status_data,
                'rank'   => $device_experience_list
        );

        api_helper::return_data(0, 'success', $data);
    }

    // 代码块
    public function get_status_data($business_id)
    {
        $filter['business_id']    = $business_id;
        $filter['day']            = date('Ymd');
        $filter['update_time >='] = date('Y-m-d H:i:s', time()-1800);

        $device_unique_ids = $device_list = [];

        $device_unique_ids  = _model('screen_device_online_stat_day')->getFields(
                'device_unique_id', $filter, ' GROUP BY `device_unique_id`');

        $device_list = _model('screen_device')->getList(array('business_id' => $business_id, 'status' => 1));

        $online_num = count($device_unique_ids);

        return array(
            'online_num'  => $online_num,
            'offline_num' => count($device_list) - $online_num
        );
    }
}