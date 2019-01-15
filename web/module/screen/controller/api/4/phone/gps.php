<?php

/**
 * alltosun.com  亮屏经纬度信息
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * szy: 宋志宇 (songzy@alltosun.com)
 * 2018年3月23日: 2016-7-26 下午3:05:10
 * Id
 */

class Action
{
    public function add_gps()
    {
        $lat      = tools_helper::post('lat', ''); //纬度
        $lng      = tools_helper::post('lng', '');//经度
        $device_unique_id   = tools_helper::post('device_unique_id', '');//设备唯一标识
        // 验证接口
        $check_params = array(
        );

        $api_log_id = api_helper::check_sign($check_params, 0);

        if (!$device_unique_id) {
            api_helper::return_api_data(1003, '请输入设备唯一标识', array(), $api_log_id);
        }
        
        if (!$lat || !$lng) {
            api_helper::return_api_data(1003, '请输入经纬度', array(), $api_log_id);
        }

        $device_info = screen_device_helper::get_device_info_by_device($device_unique_id);
        
        if (!$device_info) {
            api_helper::return_api_data(2003, '设备不存在或已被下架', array(), $api_log_id);
        }
        
        $info  = array(
                'lat' => $lat,
                'lng' => $lng,
                'province_id'   => $device_info['province_id'],
                'city_id'       => $device_info['city_id'],
                'area_id'       => $device_info['area_id'],
                'business_id'   => $device_info['business_id'],
                'device_unique_id' => $device_unique_id,
                'date' => date('Ymd')
        );
        
        //更新device表设备经纬度
         _model('screen_device')->update(array('device_unique_id' => $device_unique_id), array('lat' => $lat,'lng' => $lng));
        
        //gps表添加记录
        $id = _model('gps_record')->create($info);
        if (!$id) {
            api_helper::return_api_data(2003, '上报失败', array(), $api_log_id);
        }
        
        api_helper::return_api_data(1000, 'success', array('info'=>'ok'), $api_log_id);
    }
}