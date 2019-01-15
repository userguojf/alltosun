<?php

/**
 * alltosun.com 活跃率 active_rate.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2018年2月6日 下午3:05:18 $
 * $Id$
 */

class Action
{
    private $per_page        = 18;
    private $member_id       = 0;
    private $res_name = '';
    private $res_id          = 0;
    private $member_info     = array();
    private $ranks           = 0;
    private $next_res_name    = '';
    private $next_res_name_id = '';
    private $nick_name        = '';
    private $p_filter         = array();
    
    public function __construct()
    {
    
        $this->member_id   = member_helper::get_member_id();
        $this->mongodb     = _mongo('screen', 'screen_click_record');
        $this->member_info = member_helper::get_member_info($this->member_id);
    
        if ($this->member_info) {
            $this->res_name = $this->member_info['res_name'];
            $this->res_id   = $this->member_info['res_id'];
            $this->ranks           = $this->member_info['ranks'];
        }
    
        if (!$this->member_info) {
            return $this->error_info;
        }
    
        switch($this->member_info['res_name'])
        {
            case 'group':
                $this->nick_name  = '全国';
                $this->next_res_name = 'province';
                $this->next_res_name_id = 'province_id';
                break;
            case 'province':
                $this->nick_name        = _uri($this->res_name,$this->res_id,'name');
                $this->p_filter         = array('province_id' => $this->res_id);
                $this->next_res_name    = 'city';
                $this->next_res_name_id = 'city_id';
                break;
            case 'city':
                $this->nick_name        = _uri($this->res_name,$this->res_id,'name');
                $this->p_filter         = array('city_id' => $this->res_id);
                $this->next_res_name    = 'area';
                $this->next_res_name_id = 'area_id';
                break;
            case 'area':
                $this->nick_name     = _uri($this->res_name,$this->res_id,'name');
                $this->p_filter      = array('area_id' => $this->res_id);
                $this->next_res_name = 'business_hall';
                break;
            case 'business_hall':
                $this->nick_name        = _uri('business_hall',$this->res_id,'title');
                $this->p_filter         = array('business_id' => $this->res_id);
                $this->res_name         = 'business';
                $this->next_res_name    = 'business';
                $this->next_res_name_id = 'business_id';
                break;
        }
    
        Response::assign('curr_member_ranks', $this->ranks);
    }
    
    public function index()
    {
        $page_no       = tools_helper::get('page_no', 1);
        $search_filter = Request::Get('search_filter', array());
        
        $list = array();
        $count = _model('screen_device')->getTotal(array('status'=>1));
        if ($count) {
            $pager = new Pager($this->per_page);

            $order = 'ORDER BY `id` DESC ';
            $limit = $pager->getLimit($page_no);

            $list = _model('screen_device')->getList(array('status'=>1), $order.' '.$limit);

            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }
        }

        foreach ($list as $k => $v) {
            $list[$k]['one']  = '100%';
            $list[$k]['two']  = '100%';
            $list[$k]['three']  = '100%';
            $list[$k]['four']  = '100%';
            $list[$k]['five']  = '100%';
            $list[$k]['week']  = '100%';
            p($v['add_time'] , $this->get_day($v['add_time']));
            
        }
        
        //Response::assign('search_filter', $search_filter);
        //Response::assign('status', $status);
        Response::assign('list', $list);

        Response::display('admin/active_rate/index.html');
    }
    
    public function active()
    {
        $search_filter  = Request::Get('search_filter', array());
        $page           = tools_helper::get('page_no', 1);
        $type           = tools_helper::get('type', 1);
        $default_filter = _widget('screen')->default_search_filter($this->member_info);
        
        $res_id         = tools_helper::get('res_id', 0);
        $date           = tools_helper::get('date', '');
        
        //var_dump($res_id);
        $filter = $default_filter;
        
        if (isset($search_filter['date_type']) && $search_filter['date_type']) {
            if (1 == $search_filter['date_type']) {
                $search_filter['start_time'] = $search_filter['end_time'] = date('Y-m-d');
        
            } else if (2 == $search_filter['date_type']) {
                $search_filter['start_time'] = date('Y-m-d',time() - 7 * 24 * 3600);
                $search_filter['end_time']   = date('Y-m-d');
        
            } else if (3 == $search_filter['date_type']) {
                $search_filter['start_time'] = date('Y-m-d',time() - 30 * 24 * 3600);
                $search_filter['end_time']   = date('Y-m-d');
        
            }
        }
        
        if (isset($search_filter['start_time']) && $search_filter['start_time']) {
            $filter['day >='] = date('Ymd', strtotime($search_filter['start_time']));
            
            $filter['day <='] = date('Ymd', strtotime($search_filter['start_time']));
        } else {
            $filter['day >='] = date('Ymd', time());
//             $search_filter['start_time'] = date('Y-m-d', time() - 30 * 24 * 3600);
            
            $filter['day <='] = $search_filter['end_time'] = date('Ymd', time());
            $search_filter['start_time'] = date('Y-m-d', time());
        }
        
//         if (isset($search_filter['end_time']) && $search_filter['end_time']) {
//             $filter['day <='] = date('Ymd', strtotime($search_filter['end_time']));
//         } else {
//             $filter['day <='] = $search_filter['end_time'] = date('Ymd', time());
//             $search_filter['end_time'] = date('Y-m-d', time());
//         }
        
//         if (strtotime($search_filter['start_time']) + 30 * 24 * 3600 == strtotime($search_filter['end_time'])) {
//             $search_filter['date_type'] = 3;
//         }
        
//         if ($search_filter['start_time'] == $search_filter['end_time']) {
//             $search_filter['date_type'] = 1;
//         }
        
//         if (isset($search_filter['start_date']) && $search_filter['start_date']) {
//             $filter['day >='] = date('Ymd', strtotime($search_filter['start_date']));
//             $search_filter['start_time'] = $search_filter['start_date'];
//         } else {
//             $filter['day >='] = date('Ymd', time() - 30 * 24 * 3600);
//             $search_filter['start_time'] = date('Y-m-d', time() - 30 * 24 * 3600);
//         }
        
//         if (isset($search_filter['end_date']) && $search_filter['end_date']) {
//             $filter['day <='] = date('Ymd', strtotime($search_filter['end_date']));
//             $search_filter['end_time'] = $search_filter['end_date'];
//         } else {
//             $filter['day <='] = $search_filter['end_time'] = date('Ymd', time());
//             $search_filter['end_time'] = date('Y-m-d', time());
//         }
//         if ($date) {
//             $filter['day <='] = $date;
//             $filter['day >='] = $date;
//             //$search_filter['end_time'] = $search_filter['end_date'];
//         }
        
//         $filter['day <='] = '20180206';
//         $filter['day >='] = '20180206';
        
        //权限控制
        if ('group' == $this->res_name) {
        
        } else if ('province' == $this->res_name) {
            $filter['province_id'] = $this->res_id;
        
        } else if ('city' == $this->res_name) {
            $filter['city_id'] = $this->res_id;
        
        } else if ('area' == $this->res_name) {
            $filter['area_id'] = $this->res_id;
        
        } else if ('business_hall' == $this->res_name) {
            $filter['business_id'] = $this->res_id;
        }
         
        //搜索判断
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
        
        if (!empty($search_filter['area_id'])) {
            $filter['area_id'] = $search_filter['area_id'];
        }
        
        $filter['status']  = 1;

        $list = array();
        $count = _model('screen_device')->getTotal($filter);
        if ($count) {
            $pager = new Pager($this->per_page);
        
            $order = 'ORDER BY `id` DESC ';
            $limit = $pager->getLimit($page);
        
            $list = _model('screen_device')->getList($filter, $order.' '.$limit);
        
            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }
        }

        
        foreach ($list as $k => $v) {

            $day = $this->get_day($v['update_time']);

            foreach ($day as $kk => $vv) {
                if ($kk == 'week') {
                    $active_num = $this->get_active($v['device_unique_id'], $vv[0], $vv[1]);
                    $list[$k]['week'] = round($active_num/$count, 2).'%';
                } else {
                    $active_num = $this->get_active($v['device_unique_id'], $vv, $vv);

                    if ($kk == 'one') {
                        $list[$k]['one'] = round($active_num/$count, 2).'%';
                    } else if ($kk == 'two') {
                        $list[$k]['two'] = round($active_num/$count, 2).'%';
                    } else if ($kk == 'three') {
                        $list[$k]['three'] = round($active_num/$count, 2).'%';
                    } else if ($kk == 'four') {
                        $list[$k]['four'] = round($active_num/$count, 2).'%';
                    } else if ($kk == 'five') {
                        $list[$k]['five'] = round($active_num/$count, 2).'%';
                    }
                    
                }
            }
        }
        
        //p($list);
        
        Response::assign('list', $list);
        Response::assign('search_filter', $search_filter);
        Response::assign('start_time', date("Y-m-d", strtotime($filter['day >='])));
        
        Response::display("admin/active_rate/index.html");
    }
    
    // 天
    public function get_day($add_time)
    {
        $day        = array();
        
        $day['one']   = date("Ymd", strtotime($add_time));
        $day['two']   = date("Ymd", strtotime($add_time)+86400);
        $day['three'] = date("Ymd", strtotime($add_time)+86400*2);
        $day['four']  = date("Ymd", strtotime($add_time)+86400*3);
        $day['five']  = date("Ymd", strtotime($add_time)+86400*4);
        $day['week']  = $this->get_week($add_time);

        return $day;
    }
    
    public function get_active($unique_id, $start_date, $end_date)
    {
        $new_filter = array(
            'day >=' => $start_date,
            'day <=' => $end_date,
            //'device_unique_id' => $device_unique_id
        );
        
        $filter = array(
            'day >=' => $start_date,
            'day <=' => $end_date,
            //'is_online' => 1,
            //'device_unique_id' => $device_unique_ids2
        );
        
        $where = $this->to_where_sql($filter);
    
        //为预防换厅的设备导致统计混乱， 故此按营业厅分组
        $sql            = " SELECT COUNT(*) AS `online_num`, `device_unique_id`, `business_id` FROM `screen_device_online_stat_day` {$where} GROUP BY  `device_unique_id`, `business_id` ";
        $online_list    = _model('screen_device_online_stat_day')->getAll($sql);
        
        $online_device = array();
        //去除换厅或重新安装的设备  
        foreach ($online_list as $k => $v) {
    
            $online_device[$v['device_unique_id']]          = $v['online_num'];
        }

        return count($online_device);
    }
    
    // 周
    public function get_week($day)
    {
        $lastday=date('Y-m-d',strtotime("$day Sunday")); 
        $firstday=date('Y-m-d',strtotime("$lastday -6 days")); 
        
        return array($firstday,$lastday); 
    }
    
    private function to_where_sql($filter)
    {
        if (!$filter) {
            return '';
        }
    
        $where = '';
    
        if (is_array($filter)) {
    
            foreach ($filter as $k => $v) {
    
                if ( !$where ) {
                    $where = " WHERE ";
                }
    
                if (is_array($v)) {
                    $in = '';
                    foreach ($v as $vv) {
                        $in .= "'{$vv}',";
                    }
                    $in = rtrim($in, ',');
                    $where .= " {$k} in({$in}) AND";
                } else {
                    if (is_string($v)) {
                        $v = "'{$v}'";
                    }
                    if ( strpos($k, '<') || strpos($k, '>') ) {
                        $where .= " {$k}{$v} AND";
                    } else {
                        $where .= " {$k}={$v} AND";
                    }
                }
            }
    
            $where = rtrim($where, 'AND');
        } else {
    
            if ( !$where ) {
                $where = " WHERE ";
            }
    
            $where .= "id={$filter} ";
        }
    
        return $where;
    }
}