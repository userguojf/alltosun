<?php
/**
 * alltosun.com  person_num.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-8-9 上午11:23:33 $
 * $Id$
 * 需求分析
 * 数字地图-万能
 *一个是给你传时间范围，你给我按小时为粒度返回探针厅内人数数据，
 *一个是传时间范围，你给我按天为粒度返回探针厅内人数数据
 */
probe_helper::load('stat', 'trait');

class Action
{
    use stat;

    private $member_info = [];
    private $user_number = '';
    private $business_hall_info = [];
    private $type = '';

    public function __construct()
    {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

        if(in_array($origin, api_config::$white_list)){
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Headers: content-type');
            header('Access-Control-Allow-Methods: GET,POST,OPTIONS');
            header('Access-Control-Allow-Origin:'.$origin);
        }

        // 验证secret
        api_helper::check_token('post', $_POST);

        // $this->member_id = member_helper::get_member_id();
        // 获取渠道码
        $this->user_number = tools_helper::post('business_code', '');

        if ( !$this->user_number ) api_helper::return_data(1, '请上传营业厅渠道码');

        $this->member_info =_model('member')->read(array('member_user' => $this->user_number));
        if ( !$this->member_info ) api_helper::return_data(1, '未找到对应渠道码的账号信息');

        //查询营业厅
        $this->business_hall_info = _model('business_hall')->read(array('user_number' => $this->user_number));

        if (!$this->business_hall_info) api_helper::return_data(1, '门店不存在');

        // 按时间取数据的类型
        $this->type = tools_helper::post('type', 'day');

    }

    public function __call($action = '', $param = array())
    {
        if ( 'day' == $this->type ) {
            $this->day_data();
        } else if ( 'hour' == $this->type ){
            $this->hour_data();
        } else {
            api_helper::return_data(1, '获取数据的时间类型错误');
        }
    }

    public function day_data()
    {
        $start_day = tools_helper::post('start_day', '');
        $end_day   = tools_helper::post('end_day', '');

        if ( !$start_day || !$end_day) api_helper::return_data(1, '请确定时间区间才能获取数据');

        $start_time = strtotime($start_day);
        $end_time   = strtotime($end_day);

        if ( $end_time < $start_time ) api_helper::return_data(1, '请获取正确的时间区间');

        // 初始化数据
        $return_data = [];
        $break_time = 0;

        for ( $i = 1; $break_time <= $end_time; $i ++ ) {
            $return_data[$start_day] = array(
                    'indoor' => 0,  //室内
                    'oudoor' => 0,  //室外
            );
            $break_time = $start_time + $i*3600*24;
            $start_day = date('Y-m-d', $break_time);
        }

        //查询本厅下设备
        $device_info = _model('probe_device')->read(array('business_id' => $this->business_hall_info['id']));
        if (!$device_info) api_helper::return_data(0, 'success', $return_data);

        //获取近七日(包含今日)的室内室外数据
        foreach ($return_data as $k => $v) {
            //取数据
            $tmp_data  = $this -> day_stat($device_info['business_id'], date('Ymd', strtotime($k)));
            $return_data[$k]['indoor'] = $tmp_data['indoor']; //室内
            $return_data[$k]['oudoor'] = $tmp_data['oudoor']; //室外
        }

        api_helper::return_data(0, 'success', $return_data);
    }

    public function hour_data()
    {
        $date       = tools_helper::post('date', 0);
        $start_hour = tools_helper::post('start_hour', 0);
        $end_hour   = tools_helper::post('end_hour', 0);

        if ( !$start_hour || !$end_hour || !$date ) api_helper::return_data(1, '请确定时间区间才能获取数据');

        if ( !is_numeric($date) || 6 != strlen($date) ) $date = date('Ymd');
        if ( !is_numeric($start_hour) || !is_numeric($end_hour) ) {
            api_helper::return_data(1, '请传正确的时间类型');
        }
        if ( $start_hour < 0 || $end_hour > 24 ||  $start_hour > $end_hour ) {
            api_helper::return_data(1, '请获取正确的时间区间');
        }

        // probe_1_17_118_23_hour
        // $tmp_data  = $this -> hour_stat($device_info['business_id'], $date);
        // $tmp_data  = $this -> hour_stat(23, 20170602);

        //初始化数据
        // 初始化数据
        $return_data = [];
        $break_time = 0;

        for ( $i = 1; $start_hour <= $end_hour; $i ++ ) {
            1 == strlen($start_hour) ? $k = 0 . $start_hour : $k =  $start_hour ;

            $return_data[$k] = array(
                    'indoor' => 0,  //室内
                    'oudoor' => 0,  //室外
            );

            $start_hour = $start_hour + 1;
        }

        //查询本厅下设备
        $device_info = _model('probe_device')->read(array('business_id' => $this->business_hall_info['id']));
        if (!$device_info) api_helper::return_data(0, 'success', $return_data);

        $tmp_data  = $this -> hour_stat($device_info['business_id'], $date);
        if ( !$tmp_data ) api_helper::return_data(0, 'success', $return_data);

        //获取近七日(包含今日)的室内室外数据
        foreach ($return_data as $k => $v) {
            //取数据
            $return_data[$k]['indoor'] = $tmp_data[$k]['indoor']; //室内
            $return_data[$k]['oudoor'] = $tmp_data[$k]['oudoor']; //室外
        }

        api_helper::return_data(0, 'success', $return_data);
    }
}