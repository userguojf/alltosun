<?php
/**
 * alltosun.com  new_device.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-4-13 下午2:59:32 $
 * $Id$
 */

class Action
{
    public function __call($action = '', $param = array())
    {
        $start_date       = Request::Get('start_date', '');
        $end_date         = Request::Get('end_date', '');
        $business_hall_id = Request::Get('business_hall_id', '');


        if ( !$start_date || !$end_date || !$business_hall_id ) {
            return '请传正确的参数';
        }

        $filter = $list =array();

        $filter = array(
                'day >=' => date('Ymd', strtotime($start_date)),
                'day <=' => date('Ymd', strtotime($end_date)),
                'business_id' => $business_hall_id,
                'status'      => 1
        );

        $device_list = _model('screen_device')->getList($filter, " ORDER BY `day` DESC ");

        $th = array(
            0 => 'first',
            1 => 'second',
            2 => 'third',
            3 => 'fourth',
            4 => 'fifth',
            5 => 'sixth',
            6 => 'seventh'
        );

        $list = [];

        foreach ($device_list as $k => $v) {

            if ( !isset($list[$v['device_unique_id']]) ) {
                    $list[$v['device_unique_id']] = array(
                            'business_hall_id' => 0,
                            'device_unique_id' => '',
                            'first' => 99, // 未上报
                            'second' => 99,
                            'third' => 99,
                            'fourth' => 99,
                            'fifth' => 99,
                            'sixth' => 99,
                            'seventh' => 99,
                            'type'    => 2, // 默认是异常
                            'success_num' => 0,
                            'no_report' => 0
                        );
            }

            $reset_auto_info = _model('screen_auto_start')->read(
                    array('device_unique_id' => $v['device_unique_id'], 'reset_report' => 1 ,'status' => 1)
            );
            

            $list[$v['device_unique_id']]['reset_day'] = isset( $reset_auto_info['opreate_day'] )? $reset_auto_info['opreate_day'] : 0;
            $list[$v['device_unique_id']]['business_hall_id'] = $v['business_id'];
            $list[$v['device_unique_id']]['device_unique_id'] = $v['device_unique_id'];
            $list[$v['device_unique_id']]['day']              = $v['day'];
    
            $auto_list = _model('screen_auto_start')->getList(
                    array(
                            'business_hall_id' => $v['business_id'],
                            'device_unique_id' => $v['device_unique_id'],
                            'operate_date >='  => isset( $reset_auto_info['opreate_day'] )? $reset_auto_info['opreate_day'] : $v['day'],
                            'status'           => 1
                    )
            );
    
            if ( !$auto_list ) {
                // 异常 未上报的设备
                $list[$v['device_unique_id']]['type'] = 2;
                continue;
            }

            foreach ( $auto_list as $key => $val ) {

                // 循环一星期数据
                for ( $i = 0; $i < 7; $i ++ ) {
                    $day = date('Ymd', strtotime($v['day']) + $i * 24 * 60 * 60);

                    if ( $day != $val['operate_date'] ) continue;

                    $list[$v['device_unique_id']][$th[$i]] = $val['auto_start'];
                    // 判断是否为异常
                    if ( 1 == $val['auto_start'] ) {
                        ++ $list[$v['device_unique_id']]['success_num'];

                        if ( 7 == $list[$v['device_unique_id']]['success_num'] ) {
                            $list[$v['device_unique_id']]['type'] = 1;
                        }
                    } else {
                        $list[$v['device_unique_id']]['type'] = 2;
                    }
    
                     $list[$v['device_unique_id']]['no_report'] = 1;
                }

            }
        }

        $action = '新增';

        // p($data);//exit();
        Response::assign('list', $list);
        Response::assign('new_device_filter', 1);
        Response::assign('module', '自启动');
        Response::assign('action', $action.'详情');
        Response::display("admin/detail_list.html");
        
    }
}