<?php

/**
 * alltosun.com  content_meal.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2018年1月16日 上午11:42:50 $
 * $Id$
 */

class Action
{
    public function get_content_meal()
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

        // 营业停信息
        //$business_info = business_hall_helper::get_business_hall_info ( $filter );
        $business_info = _model('business_hall')->read( $filter );

        if (! $business_info) {
            api_helper::return_api_data ( 1003, '营业厅不存在', array (), $api_log_id );
        }

        $device_info = screen_device_helper::get_device_info_by_device($device_unique_id);

        if ( !$device_info ) {
            api_helper::return_api_data(1003, '未知的设备信息', array(), $api_log_id);
        }

        if ($device_info['business_id'] != $business_info['id']) {
            api_helper::return_api_data(1003, '营业厅不存在此设备', array(), $api_log_id);
        }

        $return_info  = array(
            'content_meal_id' => 0,
            'title'           => '',
            'link'            => ''
        );

        //为提升查询速度，先查出上线的内容
        $content_filter = array(
            'start_time  <= '   => date('Y-m-d H:i:s'),
            'end_time >= '      => date('Y-m-d H:i:s'),
            'status'            => 1
        );

        $ids = _model('screen_content_meal')->getFields('id', $content_filter, ' ORDER BY `id` DESC');
        //p($ids, $content_filter);
        if (!$ids) {
            return api_helper::return_api_data(1000, 'success', $return_info, $api_log_id);
        }

        $content_ids_region = array();  //根据地区发布的内容
        $content_ids_device = array();  //根据设备发布的内容

        $return_data = array();

        foreach (screen_content_config::$content_put_type as $k => $v) {

            if (!in_array($k, array('group', 'province', 'city', 'business_hall'))) {
                continue;
            }

            $content_res_filter = array(
                'content_id'        => $ids,
                'issuer_res_name'   => $k,  //投放者res_name
                'issuer_res_id'     => 0    //投放者res_id
            );

            if ($k != 'group') {
                if ($k == 'business_hall') {
                    $content_res_filter['issuer_res_id'] = $business_info['id'];
                } else {
                    $content_res_filter['issuer_res_id'] = $business_info["{$k}_id"];
                }
            }

            //根据权限查内容id
            $content_infos = $this->get_content_by_power($content_res_filter, $device_info);

            if (is_array($content_infos) && $content_infos) {
                arsort($content_infos['content_ids_region']);
                arsort($content_infos['content_ids_device']);
                $content_ids_region = array_merge($content_ids_region, $content_infos['content_ids_region']);
                $content_ids_device = array_merge($content_ids_device, $content_infos['content_ids_device']);
            }

        }

        if (!$content_ids_region && !$content_ids_device) {
            api_helper::return_api_data(1000, 'success', $return_info, $api_log_id);
        }

        //去除重复的内容
        $content_ids_region = array_unique($content_ids_region);
        $content_ids_device = array_unique($content_ids_device);

        $content_list = array();
        $content_info = array();

        //归属地单独处理
//         foreach ($content_ids_region as $v) {
//             $content_info = _uri('screen_content_meal', $v);

//             $content_list[] = $content_info;
//         }

        $content_info = _uri('screen_content_meal', $content_ids_region[0]);

        if ($content_info) {
            $return_info['content_meal_id'] = $content_info['id'];
            $return_info['title']           = $content_info['title'];
            if ($content_info['type'] == 1) {
                $link = SITE_URL.'/screen_content/admin/content_meal/detail?content_meal_id='.$content_info['id'];
            } else {
                $link = $content_info['ext_link'];
            }

            $return_info['link']            = $link;
        }

        api_helper::return_api_data(1000, 'success', $return_info, $api_log_id);
    }

    /**
     * 根据地区权限和设备获取内容
     * @param unknown $filter
     */
    public function get_content_by_power($filter, $device_info, $table='screen_meal_res')
    {
        //查询所有此权限下的发布内容, 包含机型的, 注：因为要使用 array_slice 取两条，所以一定要倒序查
        $content_res_list = _model($table)->getList($filter, ' ORDER BY `content_id` DESC ');
        //p($content_res_list);
        $content_ids_region = array();  //地区的
        $content_ids_device = array();  //设备的

        //p($content_res_list);
        foreach ($content_res_list as $k => $v) {

            //验证发布范围权限， 全国直接略过
            if ($v['res_name'] != 'group') {

                //验证省
                if ($v['res_name'] == 'province' && ($v['res_id'] != $device_info['province_id'])) {
                    continue;
                }

                //验证市
                if ($v['res_name'] == 'city' && ($v['res_id'] != $device_info['city_id'])) {
                    continue;
                }

                //验证区
                if ($v['res_name'] == 'area' && ($v['res_id'] != $device_info['area_id'])) {
                    continue;
                }

                //验证厅
                if ($v['res_name'] == 'business_hall' && ($v['res_id'] != $device_info['business_id'])) {
                    continue;
                }

            }
//p($v);
            //如果有发布品牌和发布型号
            if ($v['phone_name'] && $v['phone_version']) {
                if (($v['phone_name'] == $device_info['phone_name'] && $v['phone_version'] == $device_info['phone_version']) || ( $v['phone_name'] == 'all' && $v['phone_version'] == 'all' )) {

                    //查询内容详情
                    $content_info = _model('screen_content_meal')->read($v['content_id']);

                    if (!$content_info) {
                        continue;
                    }

                    $content_ids_region[] = $v['content_id'];
                }
                continue;
                //如果有发布品牌并且没有发布型号
            } else if ($v['phone_name'] && !$v['phone_version']) {
                if ($v['phone_name'] == $device_info['phone_name']) {
                    $content_ids_region[] = $v['content_id'];
                }
                continue;
                //按地区
            } else {

                $content_ids_region[] = $v['content_id'];
            }
        }

        //取前两条（集团、省、市、厅）
        if (count($content_ids_region) >= 2) {
            $content_ids_region = array_slice($content_ids_region, 0, 1);
        }

        //取前一条 （设备）  此处不能过滤， 因可能后期不符合权限
        //if (count($content_ids_device) >= 1) {
        $content_ids_device = $content_ids_device;
        //}

        return array('content_ids_region' => $content_ids_region, 'content_ids_device' => $content_ids_device);

        }
}