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
     * 近七日数据
     */
    public function near_seven_days_data()
    {
        //查询营业厅
        $business_hall_info = business_hall_helper::get_business_hall_info(array('user_number' => $this->user_number));

        if (!$business_hall_info) {
            api_helper::return_data(1, '门店不存在');
        }

        //初始化数据
        $date = date('Y-m-d');
        $return_data = array();
        $num_of_days = 1;

        do{
            $return_data[$date] = array(
                    'indoor' => 0,  //室内
                    'oudoor' => 0,  //室外
            );
            ++$num_of_days;
            $date = date('Y-m-d', strtotime($date) - 3600*24);

        } while($num_of_days <= 7);

        //查询本厅下设备
        $device_info = _model('probe_device')->read(array('business_id' => $business_hall_info['id']));

        if (!$device_info) {
            api_helper::return_data(0, 'success', $return_data);
        }
        //获取近七日(包含今日)的室内室外数据
        foreach ($return_data as $k => $v) {
            //取数据
            $tmp_data  = $this -> day_stat($device_info['business_id'], date('Ymd', strtotime($k)));
            $return_data[$k]['indoor'] = $tmp_data['indoor']; //室内
            $return_data[$k]['oudoor'] = $tmp_data['oudoor']; //室外
        }

        api_helper::return_data(0, 'success', $return_data);

    }
}