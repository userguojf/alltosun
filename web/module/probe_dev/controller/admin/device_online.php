<?php
/**
  * alltosun.com  device_online.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2018年2月26日 下午2:50:18 $
  * $Id$
  */
probe_helper::load('func');
class Action
{
    /**
     * 设备在线详情（页面版）
     */
    public function __call($action='', $param=array()) {

        $search_filter = tools_helper::Get('search_filter', array());
        $province_id   = tools_helper::Get('province_id', 0);

        if ($province_id) {
            $filter = array(
                    'province_id' => $province_id
            );
        } else {
            $filter = array(1=>1);
        }

        //查询所有设备
        $device_list  = _model('probe_device')->getList(array(1=>1));

        //营业厅设备数
        $business_hall_device_count = array();

        //排序
        $sort = array();

        foreach ($device_list as $k => $v) {
            $sort[] = $v['business_id'];
            if (empty($business_hall_device_count[$v['business_id']])) {
                $business_hall_device_count[$v['business_id']] = 1;
            } else {
                ++$business_hall_device_count[$v['business_id']];
            }
        }

        if ($device_list) {
            array_multisort($sort, SORT_ASC, $device_list);
        }

        if (!empty($search_filter['start_time'])){
            $start_time = $search_filter['start_time'];
        } else {
            $start_time = date('Y-m-d 00:00:00');
        }

        if (!empty($search_filter['end_time'])){
            $end_time = $search_filter['end_time'];
        } else {
            $end_time = date('Y-m-d 00:00:00');
        }

        $date = array();
        for ($i=$start_time; $i <= $end_time; $i=date('Y-m-d H:i:s', strtotime($i)+3600*24)) {
            $date[] = $i;
        }
        $data_list = array();

        foreach ($device_list as $k => $v) {

            $business_hall_info = _model('business_hall')->read($v['business_id']);

            if (!$business_hall_info) {
                continue;
            }

            try{
                $db = get_db($business_hall_info['id']);
            }catch(Exception $e){
                $db = false;
            }

            if (!$db) {
                continue;
            }

            //查询省市区
            $province_name = business_hall_helper::get_info_name('province', $business_hall_info['province_id'], 'name');
            $city_name     = business_hall_helper::get_info_name('city', $business_hall_info['city_id'], 'name');

            $tmp = array(
                    'province_name'       => $province_name,
                    'city_name'           => $city_name,
                    'business_hall_title' => $business_hall_info['title'],
                    'user_number'         => $business_hall_info['user_number'],
                    'device_count'        => $business_hall_device_count[$v['business_id']],
                    'device'              => $v['device'],
            );

            foreach ($date as $day) {
                //查询表
                $filter = array(
                        'date' => date('Ymd', strtotime($day)),
                        'dev'  => $v['device']
                );

                $stat_info = $db->read($filter);

                if ($stat_info) {
                    $status = '在线';
                } else {
                    $status = '离线';
                }

                $tmp[$day] = $status;
            }
            $data_list[] = $tmp;
        }

        $hand = array(
                '省',
                '市',
                '厅',
                '渠道编码',
                '本厅设备总数',
                '设备',
        );
        foreach ($date as $day) {
            array_push($hand, date('Y年m月d日', strtotime($day)));
        }
//p($data_list);
        Response::assign('search_filter', $search_filter);
        Response::assign('hand', $hand);
        Response::assign('data_list', $data_list);
        Response::display('admin/device_online/index.html');
    }
}