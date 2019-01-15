<?php

/**
 * alltosun.com 设备列表 device_list.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年9月16日 下午9:33:05 $
 * $Id$
 */

load_file('screen_stat','trait', 'stat');

class Action
{
    //public $per_page = 15;

    use stat;


    public function experience_list()
    {
        if (!$this->member_info) {
            return '您无权访问此页面';
        }

        $filter         = array();
        $new_filter     = array();
        $start_time     = 0;
        $end_time       = 0;

        //区域条件
        $filter = $this->region_filter();

        $search_filter  = Request::Get('search_filter', array());
        $page           = tools_helper::get('page_no', 1);
        $device_unique_id  = tools_helper::get('device_unique_id', '');
        $is_export         = tools_helper::get('is_export', 0);
        $hall_title     = tools_helper::get('hall_title', '');
        $phone_name     = tools_helper::get('phone_name', '');
        $phone_version  = tools_helper::get('phone_version', '');
        $debug          = tools_helper::get('debug_h', 0);
        $type           = tools_helper::get('type', 0);
        $s_filter       = $this->search_filter();


        if (isset($search_filter['start_date']) && isset($search_filter['end_date'])) {
            $start_time     = $search_filter['start_date'];
            $end_time       = $search_filter['end_date'];
        }

        $region_type   = '';
        $region_id     = 0;
        $business_id   = 0;

        if (isset($search_filter['business_id']) && !empty($search_filter['business_id']) && $business_info = _uri('business_hall', $search_filter['business_id'])) {
            $hall_title            = $business_info['title'];
            $new_filter['business_id'] = $business_info['id'];

            //补全条件
            $new_filter['province_id']   = $business_info['province_id'];
            $new_filter['city_id']   = $business_info['city_id'];
            $new_filter['area_id']   = $business_info['area_id'];

        } else {

            if (isset($search_filter['region_id']) && $search_filter['region_id']) {
                $region_id = $search_filter['region_id'];
            }

            if (isset($search_filter['region_type']) && in_array($search_filter['region_type'], array('province', 'city', 'area', 'business_hall'))) {
                $region_type = $search_filter['region_type'];
            }

            if (!$region_type || !$region_id) {
                //return '不合法的地区信息';
            }

            $region_info = _uri($region_type, $region_id);

            if (!$region_info) {
                //return '地区不存在';
            }

            $device_filter['status'] = 1;

            if ($region_id) {
                if ($region_type == 'business_hall') {
                    $new_filter['business_id'] = $region_id;
                } else {
                    $new_filter["{$region_type}_id"] = $region_id;
                }
            }
        }

        if (!empty($search_filter['province_id'])) {
            $new_filter['province_id'] = $search_filter['province_id'];
            $province = array('province_id' => $search_filter['province_id']);
            Response::assign('where1' , $province);
        }

        if (!empty($search_filter['city_id'])) {
            $new_filter['city_id'] = $search_filter['city_id'];
            $city = array('city_id' => $search_filter['city_id']);
            Response::assign('where2' , $city);
        }

        if (isset($search_filter['area']) && !empty($search_filter['area'])) {

            $new_filter['area_id'] = $device_filter['area_id'] = $search_filter['area'];
        }
        //         an_dump($this->search_filter());
        //         exit;

        if (!empty($search_filter['start_time']) && !empty($search_filter['end_time'])) {
            //按照时间段规则设置日期类型
            if ($this->set_date_type($search_filter['start_time'], $search_filter['end_time']) === false) {
                return false;
            }

            $search_filter = $this->get_date_type_filter($search_filter['start_time'], $search_filter['end_time']);
        } else {
            //搜索条件
            $search_filter = $this->search_filter();

            if ($search_filter === false) {
                return $this->error_info;
            }
        }
        $new_filter = array_merge($new_filter, $search_filter);
        $new_filter['type'] = 2;
        unset($new_filter['hour']);
        if ($device_unique_id) {
            $new_filter['device_unique_id'] = $device_unique_id;
        }

        if ($is_export || $phone_name) {
            unset($new_filter['day']);
        }

        $time_count = 0;

        if (isset($new_filter['province_id']) && isset($new_filter['business_id']) && isset($new_filter['city_id'])) {
            $time_filter = array(
                //'day'          => $new_filter['day'],
                'business_id'  => (int)$new_filter['business_id'],
                'province_id'  => (int)$new_filter['province_id'],
                'city_id'      => (int)$new_filter['city_id'],
                //'device_unique_id' => $new_filter['device_unique_id']

            );
        }

        if ($device_unique_id) {
            $time_filter['device_unique_id']  = $device_unique_id;
        }

        if ($new_filter['type'] == 2) {
            $time_filter['type'] = $new_filter['type'];
        }

        if ($type == 2) {
            unset($new_filter['_id']);
            if ($start_time) {
                $new_filter['add_time >=']  = $start_time." 00:00:00 ";
                $time_filter['add_time >='] = $start_time." 00:00:00 ";
            }

            if ($end_time) {
                $new_filter['add_time <=']  = $end_time. " 23:59:59 ";
                $time_filter['add_time <='] = $end_time. " 23:59:59 ";
                //             $active_filter['add_time <='] = $search_filter['end_date']. " 23:59:59 ";
                //             $new_business_filter['add_time <='] = $search_filter['end_date']. " 23:59:59 ";
                //             $end_time  = $search_filter['end_date'];
            }

            if (!$start_time && !$end_time) {
                $new_filter['add_time <=']  =  date("Y-m-d"). " 23:59:59 ";
                $time_filter['add_time <='] = date("Y-m-d"). " 23:59:59 ";

                $new_filter['add_time >=']  =  date("Y-m-d"). " 00:00:00 ";
                $time_filter['add_time >='] =  date("Y-m-d"). " 00:00:00 ";
            }
        } else {
            if (!empty($s_filter['day'])) {
                $time_filter['day'] = $s_filter['day'];
                $new_filter['day'] = $s_filter['day'];
            }
            if (!empty($s_filter['day >='])) {
                $time_filter['day >=']  = $s_filter['day >='];
                $new_filter['day >=']   = $s_filter['day >='];
            }

            if (!empty($s_filter['day <='])) {
                $time_filter['day <=']  = $s_filter['day <='];
                $new_filter['day <=']   = $s_filter['day <='];
            }

            if (!empty($s_filter['day <'])) {
                $time_filter['day <']  = $s_filter['day <'];
                $new_filter['day <']   = $s_filter['day <'];
            }
        }
        //$active_filter = $new_filter;
        if (empty($search_filter['start_date']) || empty($search_filter['end_date'])) {
            $last_filter['add_time <'] = date("Y-m-d")." 00:00:00 ";
            $new_business_filter['add_time >='] = date("Y-m-d"). " 00:00:00 ";
            $new_business_filter['add_time <='] = date("Y-m-d"). " 23:59:59 ";
            $active_filter['add_time >='] = date("Y-m-d"). " 00:00:00 ";
            $active_filter['add_time <='] = date("Y-m-d"). " 23:59:59 ";
        }
        //获取所有有设备的营业厅列表
        //$d_filter = get_mongodb_filter($new_filter);
        //查询count
        $count = _mongo('screen', 'screen_action_record')->count(get_mongodb_filter($new_filter));

        $t_count                  = array();
        $list                     = array();
        $device_buiness_hall_list = array();

        if ($count) {
            //MongoDB分页类
            $pager = new MongoDBPager( 20 );
            if ( $pager->generate($count) ) {
                Response::assign( 'pager', $pager );
            }
            Response::assign( 'count', $count );

            $device_buiness_hall_list = _mongo('screen', 'screen_action_record')->find(get_mongodb_filter($new_filter), $pager->getLimit($page));
        }

        //$device_buiness_hall_list = get_data_list('screen_action_record', $new_filter, ' ORDER BY `id` DESC ', $page_no, $this->per_page);

        if ($debug == 1) {
            p($d_filter, $device_buiness_hall_list);
        }

//         $time_filter = array(
//             //'day'          => $new_filter['day'],
//             'business_id'  => (int)$new_filter['business_id'],
//             'province_id'  => (int)$new_filter['province_id'],
//             'city_id'      => (int)$new_filter['city_id'],
//             //'device_unique_id' => $new_filter['device_unique_id']

//         );

        //p($new_filter, $time_filter);
        $m_filter = get_mongodb_filter($time_filter);

        if ($debug == 1) {
            p($m_filter, $m_filter);
        }

        $e_time = _mongo('screen', 'screen_action_record')->find($m_filter, array('projection'=>['experience_time'=>1]));

        //$e_time = _mongo('screen', 'screen_action_record')->find($d_filter, array('projection'=>['experience_time'=>1]));

        $i    = 0;

        foreach ($e_time as $vv) {

            $t_count[$i] = $vv['experience_time'];
            $i++;
        }

        if ($debug == 1) {
            p($m_filter, $t_count, array($t_count));
        }
        //$time_count = array_sum(_model('screen_action_record')->getFields('experience_time', $time_filter));


        foreach ($device_buiness_hall_list as $k => $v) {
            $device_info = _uri('screen_device', array('device_unique_id'=>$v['device_unique_id']));
            if (!$device_info) {
                continue;
            }

//             $device_buiness_hall_list[$k]['phone_name'] = $device_info['phone_name'];
//             $device_buiness_hall_list[$k]['phone_name_nickname'] = $device_info['phone_name_nickname'];
//             $device_buiness_hall_list[$k]['phone_version_nickname'] = $device_info['phone_version_nickname'];
//             $device_buiness_hall_list[$k]['phone_version'] = $device_info['phone_version'];
            $list[$k]['phone_name']             = $device_info['phone_name'];
            $list[$k]['phone_name_nickname']    = $device_info['phone_name_nickname'];
            $list[$k]['phone_version_nickname'] = $device_info['phone_version_nickname'];
            $list[$k]['phone_version']          = $device_info['phone_version'];
            $list[$k]['device_unique_id']       = $v['device_unique_id'];
            $list[$k]['province_id']            = $v['province_id'];
            $list[$k]['city_id']                = $v['city_id'];
            $list[$k]['area_id']                = $v['area_id'];
            $list[$k]['business_id']            = $v['business_id'];
            $list[$k]['experience_time']        = $v['experience_time'];
            $list[$k]['day']                    = $v['day'];
            $list[$k]['add_time']               = $v['add_time'];
            $list[$k]['update_time']            = $v['update_time'];
        }

        if ($is_export == 1) {
            $this->is_export($list);
        }

        Response::assign('search_filter' , $search_filter);
        Response::assign('time_count' , array_sum($t_count));
        Response::assign('device_unique_id', $device_unique_id);
        Response::assign('region_id', $region_id);
        Response::assign('region_type', $region_type);
        Response::assign('business_id', $business_id);
        //Response::assign('device_business_hall_list', $device_buiness_hall_list);
        Response::assign('device_business_hall_list', $list);

        Response::display('admin/device_stat/experience_time_list.html');
    }

    /**
     * 导出
     */
    public function is_export($list)
    {
        if (!$list) {
            return '暂无数据';
        }

        //an_dump($list); exit;
        foreach ($list as $k=>$v) {
            $info[$k]['proinvce_id']      = business_hall_helper::get_info_name('province', $v['province_id'],  'name');
            $info[$k]['city_id']          = business_hall_helper::get_info_name('city', $v['city_id'], 'name');
            $info[$k]['area_id']          = business_hall_helper::get_info_name('area', $v['area_id'], 'name');
            $info[$k]['business_hall_id'] = business_hall_helper::get_info_name('business_hall', $v['business_id'], 'title');
            $info[$k]['phone_name']       = $v['phone_name_nickname'] ? $v['phone_name_nickname'] : $v['phone_name'];
            $info[$k]['phone_version']    = $v['phone_version_nickname']? $v['phone_version_nickname'] : $v['phone_version'];
            $info[$k]['device_unique_id'] = $v['device_unique_id'];
            //$info[$k]['imei']             = $v['imei'] ? $v['imei'] : '手机无imei';
            $info[$k]['add_time']         = substr($v['add_time'], 0, 10);
            //$experience_time              = array_sum(_model('screen_action_record')->getFields('experience_time', array('device_unique_id'=>$v['device_unique_id'])));
            $experience_time              = array_sum(_model('screen_action_record')->getFields('experience_time', array('device_unique_id'=>$v['device_unique_id'], 'business_id'=>$v['business_id'])));
            $info[$k]['experience_time']  = screen_helper::format_timestamp_text($v['experience_time']);
            $info[$k]['add_time']         = $v['add_time'];
            $info[$k]['update_time']      = $v['update_time'];

        }

        //         $info = array();
        //         $i    = 0;

        //         foreach ($list as $k => $v) {
        //             $action_list = _model('screen_action_record')->getList(array('device_unique_id'=>$v['device_unique_id'], 'type'=>2));
        // //p($action_list);
        //             foreach ($action_list as $kk => $vv) {
        //                 $info[$i]['proinvce_id']      = business_hall_helper::get_info_name('province', $v['province_id'],  'name');
            //                 $info[$i]['city_id']          = business_hall_helper::get_info_name('city', $v['city_id'], 'name');
            //                 $info[$i]['area_id']          = business_hall_helper::get_info_name('area', $v['area_id'], 'name');
            //                 $info[$i]['business_hall_id'] = business_hall_helper::get_info_name('business_hall', $v['business_id'], 'title');
            //                 $info[$i]['phone_name']       = $v['phone_name_nickname'] ? $v['phone_name_nickname'] : $v['phone_name'];
            //                 $info[$i]['phone_version']    = $v['phone_version_nickname']? $v['phone_version_nickname'] : $v['phone_version'];
            //                 $info[$i]['device_unique_id'] = $v['device_unique_id'];
            //                 $info[$i]['imei']             = $v['imei'] ? $v['imei'] : '手机无imei';

            //                 //$experience_time              = array_sum(_model('screen_action_record')->getFields('experience_time', array('device_unique_id'=>$v['device_unique_id'])));
            //                 //$experience_time              = array_sum(_model('screen_action_record')->getFields('experience_time', array('device_unique_id'=>$v['device_unique_id'], 'business_id'=>$v['business_id'])));
            //                 $experience_time  =  _uri('screen_action_record', $vv['id'], 'experience_time');
            //                 $info[$i]['experience_time']  = screen_helper::format_timestamp_text($experience_time);
            //                 $info[$i]['add_time']         = $vv['add_time'];
            //                 $info[$i]['update_time']      = $vv['update_time'];

            //                 $i++;
            //             }
            //         }
            //          p($info);exit();


            $params['filename'] = '亮屏设备';
            $params['data']     = $info;
            $params['head']     = array('所属省', '所属市', '所属区县', '营业厅名称', '手机品牌', '手机型号', '标识ID', '开始时间', '体验时长', '结束时间');

            Csv::getCvsObj($params)->export();
        }
}