<?php

/**
 * alltosun.com 柜台 index.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年8月31日 上午11:33:11 $
 * $Id$
 */

class Action
{
    /**
     * 取柜台列表
     */
    public function get_shoppe_list()
    {
        $user_number  = tools_helper::post('user_number', '');
        // 验证接口
        $check_params = array(
            //'user_number'  => $user_number
        );

        $api_log_id = api_helper::check_sign($check_params, 0);

        if (!$user_number) {
            api_helper::return_api_data(1003, '请输入营业厅的视图编码', array(), $api_log_id);
        }

        $business_info  = business_hall_helper::get_info('business_hall', array('user_number'=>$user_number));
        //an_dump($business_info);

        if (!$business_info) {
            api_helper::return_api_data(1003, '营业厅不存在', array(), $api_log_id);
        }

        $shoppe_list = shoppe_helper::get_business_hall_shoppe('business_hall', $business_info[0]['id']);

        $new_list   = array();

        foreach ($shoppe_list as $k => $v) {
            $device_num = _model('screen_device')->getTotal(array('shoppe_id'=>$v['id'], 'status' => 1,  'business_id'=>$business_info[0]['id']));
            $new_list[$k]['shoppe_id']   = $v['id'];
            $new_list[$k]['shoppe_name'] = $v['shoppe_name'];
            $new_list[$k]['device_num']  = $device_num;
        }

        api_helper::return_api_data(1000, 'success', $new_list, $api_log_id);
    }

    /**
     * 专柜名称
     */
    public function get_shoppe_brand()
    {
        // 验证接口
        $check_params = array(
            //'user_number'  => $user_number
        );

        $api_log_id = api_helper::check_sign($check_params, 0);

        $data = array('混合', '华为', '三星', 'VIVO', 'OPPO', '酷派', '小米', '苹果', '魅族');

        api_helper::return_api_data(1000, 'success', $data, $api_log_id);
    }

    /**
     * 生成柜专序号
     */
    public function get_series_number()
    {
        $user_number = tools_helper::post('user_number', '');
        $shoppe_brand  = tools_helper::post('shoppe_brand', '');

        // 验证接口
        $check_params = array(
            //'user_number'  => $user_number
        );

        $api_log_id = api_helper::check_sign($check_params, 0);

        if (!$user_number) {
            api_helper::return_api_data(1003, '请输入营业厅的视图编码', array(), $api_log_id);
        }

        $business_hall_info = _uri('business_hall', array('user_number'=>$user_number));
        if (!$business_hall_info) {
            api_helper::return_api_data(1003, '营业厅不存在', array(), $api_log_id);
        }

        $series_number = shoppe_helper::generate_shoppe_ch_postfix($shoppe_brand, $shoppe_brand.'专柜', $business_hall_info['id']);

        api_helper::return_api_data(1000, 'success', array('shoppe_name'=>$shoppe_brand.'专柜'.$series_number), $api_log_id);
    }

    /**
     * 添加专柜
     */
    public function create_shoppe()
    {
        $user_number   = tools_helper::post('user_number', '');
        $shoppe_brand  = tools_helper::post('shoppe_brand', '');
        $shoppe_name   = tools_helper::post('shoppe_name', '');

        // 验证接口
        $check_params = array(
            //'user_number'  => $user_number
        );

        //wangjf add:添加为空验证
        if (!$shoppe_brand) {
            api_helper::return_api_data(1003, '专柜品牌不能为空');
        }

        if (!$shoppe_name) {
            api_helper::return_api_data(1003, '专柜名称不能为空');
        }

        $api_log_id = api_helper::check_sign($check_params, 0);

        if (!$user_number) {
            api_helper::return_api_data(1003, '请输入营业厅的视图编码', array(), $api_log_id);
        }

        $business_hall_info = _uri('business_hall', array('user_number'=>$user_number));
        if (!$business_hall_info) {
            api_helper::return_api_data(1003, '营业厅不存在', array(), $api_log_id);
        }

        $add_shoppe = array(
            'province_id'  => $business_hall_info['province_id'],
            'city_id'      => $business_hall_info['city_id'],
            'area_id'      => $business_hall_info['area_id'],
            'business_id'  => $business_hall_info['id'],
            'phone_name'   => $shoppe_brand,
            'shoppe_name'  => $shoppe_name,
            'add_from'     => 3
        );

        $id = _widget('shoppe')->add_shoppe($add_shoppe, 3);

        //api_helper::return_api_data(1000, 'success', array('info'=>'ok'), $api_log_id);

        //wangjf add: 需返回 柜台id 和 柜台名称

        if (!$id) {
            api_helper::return_api_data(1003, '专柜创建失败', array(), $api_log_id);
        }

        $result = array(
                'info'              => 'ok',
                'shoppe_id'         => $id,
                'shoppe_name'       => $shoppe_name
        );

        api_helper::return_api_data(1000, 'success', $result, $api_log_id);
    }
}
