<?php

/**
 * alltosun.com  index.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王敬飞 (wangjf@alltosun.com) $
 * $Date: 2017年7月3日 下午5:38:31 $
 * $Id$
 */
class Action
{

    public function get_content()
    {
        $user_number                    = tools_helper::post('user_number', '');
        $device_unique_id               = tools_helper::post('device_unique_id', '');
        $check_params = array(
                //'user_number'  => $user_number
        );

        $api_log_id = api_helper::check_sign($check_params, 0);

        if (!$user_number) {
            api_helper::return_api_data(1003, '请输入营业厅的视图编码', array(), $api_log_id);
        }

        if (!$device_unique_id) {
            api_helper::return_api_data(1003, '设备唯一标识不能为空', array(), $api_log_id);
        }

        $filter['user_number'] = $user_number;

        $business_info = _model('business_hall')->read($filter);

        if (!$business_info) {
            api_helper::return_api_data(1003, '营业厅不存在', array(), $api_log_id);
        }

        $device_info = screen_device_helper::get_device_info_by_device($device_unique_id);
        if ( !$device_info ) {
            api_helper::return_api_data(1003, '未知的设备信息', array(), $api_log_id);
        }

        $content_list = _widget('screen_content')->get_device_roll_content($device_info);

        api_helper::return_api_data(1000, 'success', $content_list, $api_log_id);
    }

//     /**
//      * 轮播图接口
//      */
//     public function get_content2()
//     {
//         $user_number                    = tools_helper::post('user_number', '');
//         $device_unique_id               = tools_helper::post('device_unique_id', '');
//         $check_params = array(
//             //'user_number'  => $user_number
//         );

//         $api_log_id = api_helper::check_sign($check_params, 0);

//         if (!$user_number) {
//             api_helper::return_api_data(1003, '请输入营业厅的视图编码', array(), $api_log_id);
//         }

//         if (!$device_unique_id) {
//             api_helper::return_api_data(1003, '设备唯一标识不能为空', array(), $api_log_id);
//         }

//         $filter['user_number'] = $user_number;

//         $business_info = _uri('business_hall', $filter);

//         if (!$business_info) {
//             api_helper::return_api_data(1003, '营业厅不存在', array(), $api_log_id);
//         }

//         $device_info = screen_device_helper::get_device_info_by_device($device_unique_id);

//         if ( !$device_info ) {
//             api_helper::return_api_data(1003, '未知的设备信息', array(), $api_log_id);
//         }

//         if ($device_info['business_id'] != $business_info['id']) {
//             api_helper::return_api_data(1003, '营业厅不存在此设备', array(), $api_log_id);
//         }

//         //为提升查询速度，先查出上线的内容
//         $content_filter = array(
//                 'start_time  <= '   => date('Y-m-d H:i:s'),
//                 'end_time >= '      => date('Y-m-d H:i:s'),
//                 'status'            => 1
//         );

//         $ids = _model('screen_content')->getFields('id', $content_filter, ' ORDER BY `id` DESC');
//         if (!$ids) {
//             return api_helper::return_api_data(1000, 'success', array(), $api_log_id);
//         }

//         $content_ids_region = array();  //根据地区发布的内容
//         $content_ids_device = array();  //根据设备发布的内容

//         $return_data = array();

//         foreach (screen_content_config::$content_put_type as $k => $v) {

//             if (!in_array($k, array('group', 'province', 'city', 'business_hall'))) {
//                 continue;
//             }

//             $content_res_filter = array(
//                     'content_id'        => $ids,
//                     'issuer_res_name'   => $k,  //投放者res_name
//                     'issuer_res_id'     => 0    //投放者res_id
//             );

//             if ($k != 'group') {
//                 if ($k == 'business_hall') {
//                     $content_res_filter['issuer_res_id'] = $business_info['id'];
//                 } else {
//                     $content_res_filter['issuer_res_id'] = $business_info["{$k}_id"];
//                 }
//             }

//             //根据权限查内容id
//             $content_infos = _widget('screen')->get_content_by_power($content_res_filter, $device_info);

//             if (is_array($content_infos) && $content_infos) {
//                 arsort($content_infos['content_ids_region']);
//                 arsort($content_infos['content_ids_device']);
//                 $content_ids_region = array_merge($content_ids_region, $content_infos['content_ids_region']);
//                 $content_ids_device = array_merge($content_ids_device, $content_infos['content_ids_device']);
//             }

//         }

//         if (!$content_ids_region && !$content_ids_device) {
//             api_helper::return_api_data(1000, 'success', array(), $api_log_id);
//         }

//         //去除重复的内容
//         $content_ids_region = array_unique($content_ids_region);
//         $content_ids_device = array_unique($content_ids_device);

//         $content_list = array();

//             //归属地单独处理
//         foreach ($content_ids_region as $v) {
//             $content_info = _uri('screen_content', $v);

//             // added by guojf start
//             if ( !ONDEV ) {
//                 $except_user_number = screen_content_config::$except_user_number;
//                 $except_content_id  = screen_content_config::$except_content_id;
//             } else {
//                 $except_user_number = array('1101081002052');
//                 $except_content_id  = 243;
//             }

//             if ( in_array($user_number, $except_user_number) && $except_content_id == $content_info['id'] ) {
//                 continue;
//             }
//             // added end

//             //添加默认轮播间隔
//             if ($content_info['type'] == 3) {

//                 //轮播间隔为0，则默认轮播间隔为10秒
//                 if ($content_info['roll_interval'] < 1) $content_info['roll_interval'] = 10;

//             } else if ($content_info['type'] == 1) {

//                 //轮播间隔为0并且为静图，则默认轮播间隔为10秒
//                 if ($content_info['roll_interval'] < 1 && !screen_content_helper::is_animated_gif(UPLOAD_PATH.'/'.$content_info['link'])) {
//                     $content_info['roll_interval'] = 10;
//                 }

//                 $content_info['link'] = _image($content_info['link']);
//             //视频
//             } else if ($content_info['type'] == 2) {
//                 $content_info['link'] = _widget('screen_content.video')->_video($content_info['link'], 1);
//             }

//             $content_list[] = $content_info;
//         }

//         //机型图单独处理
//         foreach ( $content_ids_device as $k => $v ) {

//             $content_info = _uri('screen_content', $v);

//             if ($content_info['type'] != 4){
//                 continue;
//             }

//             //需要自助合成机型信息的内容，务必验证昵称是否被审核
//             if (!$content_info['is_specify'] && !screen_device_helper::nickname_is_verify($device_info['phone_name'], $device_info['phone_version'])) {
//                 continue;
//             }

//             //轮播间隔为0并且为静图，则默认轮播间隔为10秒
//             if ($content_info['roll_interval'] < 1 && !screen_content_helper::is_animated_gif(UPLOAD_PATH.'/'.$content_info['link'])) {
//                 $content_info['roll_interval'] = 10;
//             }

//             //查询营业厅处理后的图片链接地址
//             $content_info['link'] = _widget('screen')->get_type4_new_image3($content_info, $device_unique_id);
//             $content_list[] = $content_info;
//             break;
//         }

//         api_helper::return_api_data(1000, 'success', $content_list, $api_log_id);
//     }

    /**
     * 是否存在机型宣传图
     */
    public function is_exist_type4()
    {
        $device_unique_id   = tools_helper::post ( 'device_unique_id', '' );
        $api_log_id         = api_helper::check_sign ( array (), 0 );

        if (!$device_unique_id) {
            api_helper::return_api_data ( 1003, 'device_unique_id不能为空', array (), $api_log_id );
        }

        // 读出设备信息
        $device_info = screen_device_helper::get_device_info_by_device($device_unique_id);

        if (! $device_info) {
            api_helper::return_api_data ( 2003, '设备不存在或已被下架', array (), $api_log_id );
        }

        //确定有机型宣传图
        $content_info = _widget ( 'screen' )->get_type4_content_by_device ( $device_info ['business_id'], $device_info ['phone_name'], $device_info ['phone_version'], $device_info);
        //wangjf add 新增返回价格
        if ($content_info) {
            api_helper::return_api_data ( 1000, 'success', array ( 'info' => 'ok', 'exists' => 1, 'price' => $device_info['price']), $api_log_id );
        } else {
            api_helper::return_api_data ( 1000, 'success', array ( 'info' => 'ok', 'exists' => 0, 'price' => $device_info['price']), $api_log_id );
        }
    }

    /**
     * test
     */
    public function test()
    {
        $api_log_id         = api_helper::check_sign ( array (), 0 );

        //$content = var_export($_POST,true);
        //$content = var_export($_SERVER,true);
        api_helper::return_api_data ( 1000, 'success', array ( 'info' => $content), $api_log_id );

    }

    //public function
}