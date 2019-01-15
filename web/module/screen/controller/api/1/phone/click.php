<?php

class Action
{
    public function add_click()
    {

        $user_number  = tools_helper::post('user_number', '');
        $phone_imei   = tools_helper::post('phone_imei', '');
        $res_id       = tools_helper::post('res_id', 0);
        $res_title    = tools_helper::post('res_title', '');
        
        // 验证接口
        $check_params = array(
        );

        $api_log_id = api_helper::check_sign($check_params, 0);

        if (!$user_number) {
            api_helper::return_api_data(1003, '请输入营业厅的视图编码');
        }

        if (!$phone_imei) {
            api_helper::return_api_data(1003, '请输入手机imei');
        }

        $imei    = screen_helper::device_decode($phone_imei);

        $phone_info = _model('screen_device')->read(array('imei'=>$imei));
        if (!$phone_info) {
            api_helper::return_api_data(1003, '暂无手机信息');
        }

        $business_info  = business_hall_helper::get_info('business_hall', array('user_number'=>$user_number));

        if (!$business_info) {
            api_helper::return_api_data(1003, '营业厅不存在');
        }

        $info  = array(
            'province_id'  => $business_info[0]['province_id'],
            'city_id'      => $business_info[0]['city_id'],
            'area_id'      => $business_info[0]['area_id'],
            'business_id'  => $business_info[0]['id'],
            'imei'         => $phone_info['imei'],
            'res_id'       => $res_id,
            'res_title'    => $res_title,
            'day'           => date("Ymd")
        );
        
        _model('screen_click_record')->create($info);

        api_helper::return_api_data(1000, 'success', array('info' => 'ok'), $api_log_id);
    }
}