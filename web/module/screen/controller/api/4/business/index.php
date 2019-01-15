<?php

/**
 * alltosun.com  index.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年6月28日 下午6:24:14 $
 * $Id$
 */

class Action
{
    /**
     * 取营业厅 一条
     */
    public function get_info()
    {
        $title       = tools_helper::post('title', '');
        $user_number = tools_helper::post('user_number', '');
        //an_dump($title);
        // 验证接口
        $check_params = array(
        );

        $api_log_id = api_helper::check_sign($check_params, 0);

        $filter = array();
        if (!empty($title)) {
            $filter['title like ']  = $title;
        }

        if (!empty($lat) && !empty($log)) {
            $filter['lat']  = $lat;
            $filter['log']  = $log;
        }

        if (!empty($user_number)) {
            $filter['user_number'] = $user_number;
        }

        $business_info = _model('business_hall')->read($filter);

        if (!$business_info) {
            api_helper::return_api_data(1003, '营业厅不存在', array(), $api_log_id);
        }

        $result_info = array();

        if ($business_info) {
            $result_info['id']  = $business_info['id'];
            $result_info['title'] = $business_info['title'];
            $result_info['user_number'] = $business_info['user_number'];
            $result_info['address']     = $business_info['address'];
        }

        api_helper::return_api_data(1000, 'success', $result_info, $api_log_id);
    }

    /**
     * 搜索取列表
     */
    public function get_business()
    {
        $lat      = tools_helper::post('lat', '');
        $lng      = tools_helper::post('lng', '');

        // 验证接口
        $check_params = array(
        );

        $api_log_id = api_helper::check_sign($check_params, 0);

        if (!$lat || !$lng) {
            api_helper::return_api_data(1003, '请输入经纬度', array(), $api_log_id);
        }

        /*
        * _model('map')->getAll(
            'SELECT id, pname, address, lon, lat,city,
            (ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((lat * 3.1415) / 180 )
            + COS(('.$lat.' * 3.1415) / 180 ) * COS((lat * 3.1415) / 180 )
            *COS(('.$lon.' * 3.1415) / 180 - (lon * 3.1415) / 180 ) ) * 6380) as dis
            FROM map '.$filter.' ORDER BY dis ASC LIMIT '.$limit);
          */
        $business_list = _model('business_hall')->getAll(
            'SELECT id, title, user_number, address, blng, blat,
                    (ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((blat * 3.1415) / 180 )
                    + COS(('.$lat.' * 3.1415) / 180 ) * COS((blat * 3.1415) / 180 )
                    *COS(('.$lng.' * 3.1415) / 180 - (blng * 3.1415) / 180 ) ) * 6380) as dis
                    FROM business_hall ORDER BY dis ASC limit 10');

        $new_list = array();
        $i = 0;
        foreach ($business_list as $k => $v) {
            if ($v['user_number']) {
                $new_list[$i]['user_number']  = $v['user_number'];
                $new_list[$i]['title']        = $v['title'];
                $new_list[$i]['address']      = $v['address'];
                $i++;
            }
        }


        api_helper::return_api_data(1000, 'success', $new_list, $api_log_id);
    }

    /**
     * 取多条
     */
    public function get_business_list()
    {
        $title    = tools_helper::post('title', '');
        $page     = tools_helper::post('page', 1);

        // 验证接口
        $check_params = array(
            //'title'  => $title
        );

        $api_log_id = api_helper::check_sign($check_params, 0);

        if (!$title) {
            api_helper::return_api_data(1003, '请输入营业厅名称', array(), $api_log_id);
        }

        $filter['title LIKE '] = "%".$title."%";
        $filter['type'] = array(4, 5);

        $list = get_app_data_list('business_hall', $filter, ' ORDER BY `id` DESC ', $page, 15);

        //新增 如果没有则按渠道编码查找 wangjf add 2018-04-25
        if ( !$list ) {
            unset($filter['title LIKE ']);
            $filter['user_number LIKE '] = "%".$title."%";
            $list = get_app_data_list('business_hall', $filter, ' ORDER BY `id` DESC ', $page, 15);

            if (!$list) {
                api_helper::return_api_data(1003, '营业厅不存在', array(), $api_log_id);
            }
        }

        //an_dump($title, $page, $filter, $list);
        $new_list = [];

        foreach ($list['data'] as $k => $v) {
            $new_list[$k]['title']        = $v['title'];
            $new_list[$k]['user_number']  = $v['user_number'];
            $new_list[$k]['address']      = $v['address'];
        }

        $list['data']    = $new_list;
        api_helper::return_api_data(1000, 'success', $list, $api_log_id);
    }
}