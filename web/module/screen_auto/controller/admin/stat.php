<?php
/**
 * alltosun.com  stat.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-2-27 下午6:54:59 $
 * $Id$
 */

class Action
{
    private $per_page    = 20;
    private $member_info = array();
    private $table = '';

    public function __call($action = '', $params = array())
    {
        $this->table = 'screen_auto_start_business_stat';

        $type          = Request::Get('type', 0);
        $search_filter = Request::Get('search_filter', array());
        $start_time    = Request::Get('start_time', '');
        $end_time      = Request::Get('end_time', '');
        $page          = Request::get('page_no' , 1) ;

        $filter = $list = $business_ids = $device_info = array();

        if ( isset($search_filter['province_id']) && $search_filter['province_id'] ) {
            $filter['province_id'] = $search_filter['province_id'];
        }

        if (isset($search_filter['business_hall_title']) && $search_filter['business_hall_title']) {
            $business_hall_info = _model('business_hall')->read(array('title' => trim($search_filter['business_hall_title'])));
            // 
            if (!$business_hall_info) return '请输入正确的营业厅名称';

            $filter['business_hall_id'] = $business_hall_info['id'];
        } 

        $start_date = strtotime($start_time);
        $end_date   = strtotime($end_time);

        if ( isset($search_filter['type']) && $search_filter['type'] && $start_date && $end_date) {
            if ( $start_date > $end_date ) $start_date = $end_date;

            $start_date = date('Ymd', $start_date);
            $end_date   = date('Ymd', $end_date);

            $param = array(
                    'day >=' => $start_date,
                    'day <=' => $end_date,
                    'status' => 1
            );

            if (isset($filter['business_hall_id']) && $filter['business_hall_id']) {
                $param['business_id'] = $filter['business_hall_id'];
            }

            $business_ids = _model('screen_device')->getFields('business_id', $param);
        }

        if ( $business_ids ) {
            foreach ( $business_ids as $k => $v ) {
                if ( !isset($device_info[$v]) ) {
                    $device_info[$v] = 1;
                } else {
                    ++ $device_info[$v];
                }
            }
            $filter['business_hall_id'] = $business_ids;
        }

        // 过滤掉我谷歌测试的
        if ( !$filter ) $filter = array( 1 => 1);
// p($filter);
        $order = " ORDER BY `id` DESC ";

        $count = _model($this->table)->getTotal($filter);

        if ($count) {
            $pager = new Pager($this->per_page);
            $list = _model($this->table)->getList($filter, $order.$pager->getLimit($page));

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
        }

        if ( isset($search_filter['type']) && $search_filter['type'] ) {
            foreach ($list as $key => &$val) {
                if ( isset($device_info[$val['business_hall_id']]) ) {
                    $val['new_device_num'] = $device_info[$val['business_hall_id']];
                    if ( $val['new_device_num'] > $val['device_all_num'] ) {
                        $val['device_all_num'] = $val['device_all_num'].'（未上报）';
                    }
                } else {
                    $val['new_device_num'] = 0; 
                }
            }
    
            unset($val);
        }

        Response::assign('count' , $count);
        Response::assign('page' , $page);
        Response::assign('list', $list);
        Response::assign('module', '自启动');
        Response::assign('action', '统计');

        Response::assign('start_time' , $start_time);
        Response::assign('end_time' , $end_time);
        Response::assign('search_filter' , $search_filter);
        Response::display("admin/stat_list.html");
    }

    
    public function detail()
    {

        // 1 为正常 2为异常 3为全部
        $type  = Request::Get('type', 0);

        $types = array(1, 2, 3);

        $search_filter = Request::Get('search_filter', array());

        $filter = $list =array();

        if ( !in_array($type, $types) ) return '请传正确的参数';

        if (isset($search_filter['business_hall_title']) && $search_filter['business_hall_title']) {
            $business_hall_info = _model('business_hall')->read(array('title' => $search_filter['business_hall_title']));
            //
            if (!$business_hall_info) return '请输入正确的营业厅名称';

            $filter['business_id'] = $business_hall_info['id'];
        } else {
            $business_hall_id = Request::Get('business_hall_id', 0);
            if ( !$type && !$business_hall_id) return '请传正确的参数';

            $filter['business_id'] = $business_hall_id;

            $search_filter['business_hall_title'] = _uri('business_hall', array('id' => $business_hall_id), 'title');
        }

        if ( isset($search_filter['device_unique_id']) && $search_filter['device_unique_id'] ) {
             $filter['device_unique_id'] = trim($search_filter['device_unique_id']);
        }

        $filter['status'] = 1;

//         var_dump(_model('screen_device')->getList(array(1 => 1)));exit();

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
            $list[$v['device_unique_id']]['day'] = $v['day'];
            $list[$v['device_unique_id']]['business_hall_id'] = $v['business_id'];
            $list[$v['device_unique_id']]['device_unique_id'] = $v['device_unique_id'];

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

        $data = [];

        if ( $type == 2 ) {
            $action = '异常';
        } else if ( $type == 1  ){
            $action = '正常';
        } else if ( $type = 3 ) {
            $action = '全部';
        }

        if ( $type != 3) {
            foreach ( $list as $kk => $val ) {
                if ( $type == $val['type'] ) {
                    array_push($data, $val);
                }
            }
        } else {
            $data = $list;
        }
// p($data);//exit();
        Response::assign('list', $data);
        Response::assign('type', $type);
        Response::assign('module', '自启动');
        Response::assign('action', $action.'详情');
        Response::assign('search_filter' , $search_filter);
        Response::display("admin/detail_list.html");
    }
}