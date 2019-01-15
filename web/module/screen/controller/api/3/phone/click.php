<?php
/**
 * alltosun.com  click.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年7月19日 上午10:16:45 $
 * $Id$
 */

class Action
{
    public function add_click()
    {
        $user_number        = tools_helper::post('user_number', '');
        $device_unique_id   = tools_helper::post('device_unique_id', '');
        $res_id             = tools_helper::post('res_id', 0);
//         $res_title          = tools_helper::post('res_title', '');

        // 验证接口
        $check_params = array(
        );

        $api_log_id = api_helper::check_sign($check_params, 0);

        if (!$user_number) {
            api_helper::return_api_data(1003, '请输入营业厅的视图编码', array(), $api_log_id);
        }

        if (!$device_unique_id) {
            api_helper::return_api_data(1003, '设备唯一标识不能为空', array(), $api_log_id);
        }

         //wangjf add 2017-12-22
        $phone_info = screen_device_helper::get_device_info_by_device($device_unique_id);

        if (!$phone_info) {
            api_helper::return_api_data(1003, '暂无手机信息', array(), $api_log_id);
        }

        $res_title = _uri('screen_content',$res_id,'title');
        $business_info  = business_hall_helper::get_business_hall_info(array('user_number' => $user_number));

        if (!$business_info) {
            api_helper::return_api_data(1003, '营业厅不存在', array(), $api_log_id);
        }

        $info  = array(
            'province_id'       => (int)$business_info['province_id'],
            'city_id'           => (int)$business_info['city_id'],
            'area_id'           => (int)$business_info['area_id'],
            'business_id'       => (int)$business_info['id'],
            'device_unique_id'  => $phone_info['device_unique_id'],
            'res_id'            => (int)$res_id,
            //'res_title'         => $res_title,
            'day'               => (int)date("Ymd"),
            'click_num'         => 1, //wangjf add 新增点击数量
            'add_time'          => date("Y-m-d H:i:s"),
            'data_add_time'     => date('Y-m-d H:i:s'),  //内容点击
        );

//        $id = get_mongodb_last_id(_mongo('screen', 'screen_click_record'));

        //$online_id = _mongo('screen', 'screen_device_online')->insertOne($info);

//         if (!$id) {
//             $info['id'] = (int)1;
//         } else {
//             $info['id'] = $id+1;
//         }

        //_model('screen_click_record')->create($info);

        _mongo('screen', 'screen_click_record')->insertOne($info);

        $result = array(
            'info' => 'ok'
        );
        api_helper::return_api_data(1000, 'success', $result, $api_log_id);
    }
}