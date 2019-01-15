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
    use stat;

    public function active_list()
    {
        if (!$this->member_info) {
            return '您无权访问此页面';
        }

        //区域条件
        $filter = $this->region_filter();

        //搜索条件
        $new_filter = $this->search_filter();

        if ($new_filter === false) {
            return $this->error_info;
        }

        $filter = array_merge($filter, $new_filter);

        //搜索条件
        $search_filter  = Request::Get('search_filter', array());
        $page_no        = Request::Get('page_no', 1);
        $is_active      = Request::Get('is_active', 0);

        unset($filter['hour']);

        if (isset($search_filter['business_id']) && !empty($search_filter['business_id']) && $business_info = _uri('business_hall', $search_filter['business_id'])) {
            $hall_title            = $business_info['title'];
            $filter['business_id'] = $business_info['id'];

            //补全条件
            $search_filter['province_id']   = $business_info['province_id'];
            $search_filter['city_id']   = $business_info['city_id'];
            $search_filter['area_id']   = $business_info['area_id'];

        } else {

            if (isset($search_filter['region_id']) && $search_filter['region_id']) {
                $region_id = $search_filter['region_id'];
            }

            if (isset($search_filter['region_type']) && in_array($search_filter['region_type'], array('province', 'city', 'area', 'business_hall'))) {
                $region_type = $search_filter['region_type'];
            }

            if (!$region_type || !$region_id) {
                return '不合法的地区信息';
            }

            $region_info = _uri($region_type, $region_id);

            if (!$region_info) {
                return '地区不存在';
            }

            $device_filter = array(
                'status' => 1
            );

            if ($region_id) {
                if ($region_type == 'business_hall') {
                    $filter['business_id'] = $region_id;
                } else {
                    $filter["{$region_type}_id"] = $region_id;
                }
            }
        }

        if (!empty($search_filter['province_id'])) {
            $filter['province_id'] = $search_filter['province_id'];
            $province = array('province_id' => $search_filter['province_id']);
            Response::assign('where1' , $province);
        }

        if (!empty($search_filter['city_id'])) {
            $filter['city_id'] = $search_filter['city_id'];
            $city = array('city_id' => $search_filter['city_id']);
            Response::assign('where2' , $city);
        }

        if (isset($search_filter['area']) && !empty($search_filter['area'])) {

            $filter['area_id'] = $device_filter['area_id'] = $search_filter['area'];
        }

        //$device_online_list = get_data_list('screen_device_online', $filter, ' GROUP BY imei, business_id ', $page_no, 15);
        $order = ' GROUP BY device_unique_id, business_id ';
        $device_online_list = array();

        //有group by 所以total取的数量还是所有的 分页不准确。
        $count = _model('screen_device_online')->getTotal($filter, $order);

        $list = get_data_list('screen_device', array('status'=>1), ' ORDER BY `id` DESC ' , $page_no, 2);
        p($list);
//         if ($count) {
//             $pager = new Pager($this->per_page);

//             $device_online_list = _model('screen_device_online')->getList($filter, ' '.$order.' '.$pager->getLimit($page_no));
//             $count = count($device_online_list);
//             if ($pager->generate($count)) {
//                 Response::assign('pager', $pager);
//             }

//             Response::assign('count', $count);
//         }

        foreach ($device_online_list as $k => $v) {
            $device_info = _uri('screen_device', array('device_unique_id'=>$v['device_unique_id']));

            if (!$device_info) {
                continue;
            }

            $device_online_list[$k]['imei'] = $device_info['imei'];
            $device_online_list[$k]['phone_name'] = $device_info['phone_name'];
            $device_online_list[$k]['phone_name_nickname'] = $device_info['phone_name_nickname'];
            $device_online_list[$k]['phone_version_nickname'] = $device_info['phone_version_nickname'];
            $device_online_list[$k]['phone_version'] = $device_info['phone_version'];
            $device_online_list[$k]['online_status'] = screen_helper::get_online_status($v['device_unique_id']);
            $device_online_list[$k]['active_status'] = _uri('screen_device_online', array('device_unique_id'=>$v['device_unique_id'], 'day'=>date("Ymd")));

            $filter['device_unique_id']  = $v['device_unique_id'];
            $filter['city_id'] = $device_info['city_id'];
            $filter['area_id'] = $device_info['area_id'];
            $filter['business_id'] = $device_info['business_id'];

            $device_online_list[$k]['active_time']   = _uri('screen_device_stat_day', $filter, 'experience_time');
        }

        Response::assign('search_filter' , $search_filter);
        //Response::assign('hall_title', $hall_title);
        Response::assign('device_online_list', $device_online_list);
        Response::display('admin/device_stat/online_list.html');
    }

    public function active()
    {
        $type = tools_helper::get('type', 0);
        $page = tools_helper::get('page_no', 1);
        $order_dir     = tools_helper::get('order_dir', 'desc');
        $order_field   = tools_helper::get('order_field', 'experience_times');

        if (!$this->member_info) {
            return '您无权访问此页面';
        }

        $is_export  = tools_helper::get('is_export', 0);

        $device_list    = _model('screen_device')->getList(array('status'=>1));

        $online_count = array();
        $list         = array(
            'first' => array(
                'device_num'   => 0,
                'business_num' => 0
            ),
            'second' => array(
                'device_num'   => 0,
                'business_num' => 0
            ),
            'third' => array(
                'device_num'   => 0,
                'business_num' => 0
            ),
            'fifth' => array(
                'device_num'   => 0,
                'business_num' => 0
            ),
            'seventh' => array(
                'device_num'   => 0,
                'business_num' => 0
            ),
            'eighth' => array(
                'device_num'   => 0,
                'business_num' => 0
            ),
        );

        $count        = 0;


        foreach ($device_list as $k => $v) {
            $online_count = count(_model('screen_device_online_stat_day')->getFields('device_unique_id', array('device_unique_id'=>$v['device_unique_id']), ' GROUP BY `business_id`, `device_unique_id`, `day` '));

            if ($online_count == 1) {
                //$list['first']['device_num'] += $online_count;
                $list['first']['business'][$v['business_id']] = 1;
                $list['first']['business_num'] = count($list['first']['business']);
                $list['first']['device_unique_id'][$v['device_unique_id']] = 1;
                $list['first']['device_num'] =  count($list['first']['device_unique_id']);

            } elseif ($online_count == 2) {
                //$list['second']['device_num'] += $online_count;
                //p(_model('screen_device_online_stat_day')->getFields('device_unique_id', array('device_unique_id'=>$v['device_unique_id']), ' GROUP BY `business_id`, `device_unique_id`, `day` '));
                $list['second']['business'][$v['business_id']] = 1;
                $list['second']['business_num'] = count($list['second']['business']);
                $list['second']['device_unique_id'][$v['device_unique_id']] = 1;
                $list['second']['device_num'] =  count($list['second']['device_unique_id']);

            } elseif ($online_count == 3) {
                //$list['third']['device_num'] += $online_count;
                $list['third']['business'][$v['business_id']] = 1;
                $list['third']['business_num'] = count($list['third']['business']);
                $list['third']['device_unique_id'][$v['device_unique_id']] = 1;
                $list['third']['device_num']  =  count($list['third']['device_unique_id']);

            } elseif ($online_count == 5) {
                //$list['fifth']['device_num'] += $online_count;
                $list['fifth']['business'][$v['business_id']] = 1;
                $list['fifth']['business_num'] = count($list['fifth']['business']);
                $list['fifth']['device_unique_id'][$v['device_unique_id']] = 1;
                $list['fifth']['device_num']  =  count($list['fifth']['device_unique_id']);

            } elseif ($online_count == 7) {
                //$list['seventh']['device_num'] += $online_count;
                $list['seventh']['business'][$v['business_id']]  = 1;
                $list['seventh']['business_num'] = count($list['seventh']['business']);
                $list['seventh']['device_unique_id'][$v['device_unique_id']] = 1;
                $list['seventh']['device_num']  =  count($list['seventh']['device_unique_id']);


            } elseif ($online_count > 7) {
                //$list['eighth']['device_num'] += $online_count;
                $list['eighth']['business'][$v['business_id']]  = 1;
                $list['eighth']['business_num'] = count($list['eighth']['business']);
                $list['eighth']['device_unique_id'][$v['device_unique_id']] = 1;
                $list['eighth']['device_num']  =  count($list['eighth']['device_unique_id']);
            }
        }


        if ($type) {
            $new_list = $this->detail($type, $list, $page, $order_dir, $order_field);
        }

        if ($is_export == 1) {
            $this->export($list);
        }

        if ($type) {
            Response::assign('list', $new_list['list']);
            Response::assign('time_count', $new_list['time_count']);
            Response::assign("order_dir", $order_dir);
            Response::assign("order_field", $order_field);

            Response::display('admin/device_stat/active_detail.html');
        } else {
            Response::assign('list', $list);
            Response::display('admin/device_stat/active_list.html');
        }
    }

    public function detail($type, $list, $page, $order_dir, $order_field)
    {
        if (!$type || !in_array($type, array(1, 2, 3, 4, 5, 6))) {
            return '请求有误';
        }

        if ($type == 1) {
            $bite = 'first';
        } elseif ($type == 2) {
            $bite = 'second';
        } elseif ($type == 3) {
            $bite = 'third';
        } elseif ($type == 4) {
            $bite = 'fifth';
        } elseif ($type == 5) {
            $bite = 'seventh';
        } elseif ($type == 6) {
            $bite = 'eighth';
        }

        if (!isset($list[$bite]['device_unique_id'])) {
            return array('list'=>array(), 'time_count'=>0);
        }

        foreach ($list[$bite]['device_unique_id'] as $k => $v) {
            $unique_id[] = $k;
        }

        $filter['device_unique_id'] = $unique_id;
        $order = ' ORDER BY `id` DESC ';

        $count = _model('screen_device_online_stat_day')->getTotal($filter, ' GROUP BY `business_id`, `device_unique_id`, `day` '.$order);

        if ($count) {
            $pager = new Pager(5);

            $online_list = _model('screen_device_online_stat_day')->getList($filter, ' GROUP BY `device_unique_id` '.$order.' '.$pager->getLimit($page));

            $count = count($online_list);

            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }

            Response::assign('count', $count);
        }

        $new_list   = array();
        $keys       = array();
        $time_count = 0;

        foreach ($online_list as $kk => $vv) {
            $info = _model('screen_device')->read(array('device_unique_id'=>$vv['device_unique_id'], 'status'=>1));

            if (!$info) {
                continue;
            }

            $new_list[$kk]['proinvce']      = business_hall_helper::get_info_name('province', $vv['province_id'],  'name');
            $new_list[$kk]['city']          = business_hall_helper::get_info_name('city', $vv['city_id'], 'name');
            $new_list[$kk]['area']          = business_hall_helper::get_info_name('area', $vv['area_id'], 'name');
            $new_list[$kk]['business']      = business_hall_helper::get_info_name('business_hall', $vv['business_id'], 'title');
            $new_list[$kk]['phone_name']    = $info['phone_name_nickname'] ? $info['phone_name_nickname'] : $info['phone_name'];
            $new_list[$kk]['phone_version'] = $info['phone_version_nickname']? $info['phone_version_nickname'] : $info['phone_version'];
            $new_list[$kk]['imei']          = $info['imei'] ? $info['imei'] : 0;
            $new_list[$kk]['install']       = $info['add_time'];

            $last_info = _model('screen_device_online_stat_day')->read(array('device_unique_id'=>$vv), ' ORDER BY `id` DESC ');

            $new_list[$kk]['last_time']     = $last_info['update_time'];
            $new_list[$kk]['time_total']    = screen_helper::format_timestamp_text($last_info['online_time']);

            $time_count  += $last_info['online_time'];

            if ($order_field == 'experience_times') {
                $keys[$kk] = $last_info['online_time'];
            }
        }

        if ($keys) {

            if ($order_dir == 'desc') {
                array_multisort ($keys, SORT_DESC, $new_list);
            } else {
                array_multisort ($keys, SORT_ASC, $new_list);
            }
        }

        return array('list'=>$new_list, 'time_count'=>$time_count);

        //         Response::assign('list', $new_list);

        //         Response::display('admin/device_stat/active_detail.html');
    }

    public function export($list)
    {
        if (!$list) {
            return  array();
        }

        foreach ($list as $k=>$v) {
            if ($k == 'first') {
                $day = '仅活跃1天的设备量';
            } elseif ($k == 'second') {
                $day = '仅活跃2天的设备量';
            } elseif ($k == 'third') {
                $day = '仅活跃3天的设备量';
            } elseif ($k == 'fifth') {
                $day = '活跃5天的设备量';
            } elseif ($k == 'seventh') {
                $day = '活跃7天的设备量';
            } elseif ($k == 'eighth') {
                $day = '7天以上';
            }

            $info[$k]['day']          = $day;
            $info[$k]['device_num']   = $v['device_num'];
            $info[$k]['business_num'] = $v['business_num'];
        }

        $params['filename'] = '亮屏设备';
        $params['data']     = $info;
        $params['head']     = array('天数', '设备量', '厅店量');

        Csv::getCvsObj($params)->export();
    }

    public function is_export($list)
    {
        if (!$list) {
            return  array();
        }

        foreach ($list as $k => $v) {
            //$info[$k]['']
        }

        $params['filename'] = '亮屏设备';
        $params['data']     = $info;
        $params['head']     = array('省', '市', '厅', '品牌', '型号', 'IMEI', '安装时长', '最后一次上报时间', '累计在线多长时间');

        Csv::getCvsObj($params)->export();
    }
}