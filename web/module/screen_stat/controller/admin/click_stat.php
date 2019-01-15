<?php

/**
 * alltosun.com 点击统计 click_stat.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年7月11日 下午2:42:42 $
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

    public $mongodb;

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

    public function __call($action = '', $params = array())
    {
        $search_filter    = Request::Get('search_filter', array());
        $page             = tools_helper::get('page_no', 1);
        $hall_title       = Request::Get('hall_title', '');
        $default_filter   = _widget('screen')->default_search_filter($this->member_info);
        $business_id      = tools_helper::get('business_id', 0);

        $res_id            = tools_helper::get('res_id', 0);
        $date             = tools_helper::get('date', 0);

        $filter = $default_filter;

        //详情页Bug
        if ($date) {
            $search_filter['start_time'] = date('Y-m-d', strtotime($date));
            $search_filter['end_time']   = date('Y-m-d', strtotime($date));
        } else {
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
        }


        if (isset($search_filter['start_time']) && $search_filter['start_time']) {
            $filter['day >='] = date('Ymd', strtotime($search_filter['start_time']));
        }

        if (isset($search_filter['end_time']) && $search_filter['end_time']) {
            $filter['day <='] = date('Ymd', strtotime($search_filter['end_time']));
        }

        if (isset($search_filter['start_time']) && strtotime($search_filter['start_time']) + 30 * 24 * 3600 == strtotime($search_filter['end_time'])) {
            $search_filter['date_type'] = 3;
        }

        if (isset($search_filter['end_time']) && $search_filter['start_time'] == $search_filter['end_time']) {
            $search_filter['date_type'] = 1;
        }

        //营业厅权限跳过标题搜索
        if ($this->res_name != 'business_hall' && $hall_title) {
            $business_hall_list = _model('business_hall')->getList(array('title' => $hall_title));
            $business_hall_ids = array();
            foreach ($business_hall_list as $k => $v) {
                //非集团管理员并且搜索的营业厅不在本身权限之内则跳过
                if ($this->res_name != 'group' && $v["{$this->res_name}_id"] != $this->res_id) {
                    continue;
                }
                $business_hall_ids[] = $v['id'];
            }

            if (!$business_hall_ids) {
                $business_hall_ids = 0;
            }
            $filter['business_id'] = $business_hall_ids;
        }

        //搜索判断
        //省
        if (!empty($search_filter['province_id']) ) {
            $filter['province_id'] = $search_filter['province_id'];
            $province                = array('province_id' => $search_filter['province_id']);
            Response::assign('where1' , $province);
        }
        //市
        if (!empty($search_filter['city_id'])) {
            $filter['city_id'] = $search_filter['city_id'];
            $city                = array('city_id' => $search_filter['city_id']);
            Response::assign('where2' , $city);
        }
        //区
        if (!empty($search_filter['area_id'])) {
            $filter['area_id'] = $search_filter['area_id'];
        }

        if (!empty($search_filter['imei'])) {
            //根据市imei查询screen_click_record 表res_id
            $imei   = _uri('screen_device')->getFields('device_unique_id',array('imei' => $search_filter['imei']));

            if($imei){
                $filter['device_unique_id']= $imei[0];
            }else{
                $filter['device_unique_id']=0;
            }

            if (empty($filter['device_unique_id'])) {
                $filter['res_id'] = 0;
            }
        }

        if ($res_id) {
            $filter['res_id'] = $res_id;
        }
        if ($business_id) {
            $filter['business_id'] = $business_id;
        }

//         if (!$filter) {
//             $filter = array(1=>1);
//         }

        $filter     = array_merge($filter, $this->p_filter);

        $filter     = get_mongodb_filter($filter);

        //查询count
        $count = $this->mongodb->count( $filter );

        $click_list = array();
        $list       = array();

        if ($count) {
            //MongoDB分页类
            $pager = new MongoDBPager( $this->per_page );
            if ( $pager->generate($count) ) {
                Response::assign( 'pager', $pager );
            }
            Response::assign( 'count', $count );
            $order = array('sort' => array('_id' => -1));
            $click_list = _mongo('screen', 'screen_click_record')->find($filter, array_merge($pager->getLimit($page), $order));
        }

        $list = array();
        $i =0;

        //$click_list = get_data_list('screen_click_record', $filter, ' ORDER BY `id` DESC ', $page, $this->per_page);
        foreach ($click_list as $k => $v) {
            $phone_info = screen_device_helper::get_device_info_by_device($v['device_unique_id']);
            //$phone_info = _uri('screen_device', array('device_unique_id' => $v['device_unique_id']));
            if (!$phone_info) {
                continue;
            }

            //获取设备昵称
            $nickname_info = screen_device_helper::get_device_nickname($phone_info['phone_name'], $phone_info['phone_version']);
            $phone_name    = $nickname_info['name_nickname'];
            $phone_version = $nickname_info['version_nickname'];

            if ($phone_name) {
                $nike_name = $phone_name;
            } else {
                $nike_name = $phone_info['phone_name'];
            }

            if ($phone_version) {
                $version = $phone_version;
            } else {
                $version = $phone_info['phone_version'];
            }

//             $click_list[$k]['phone_name'] = $nike_name;
//             $click_list[$k]['phone_version'] = $version;
//             $click_list[$k]['phone_imei'] = $phone_info['imei'];
            $list[$i]['province_id'] = $v['province_id'];
            $list[$i]['city_id'] = $v['city_id'];
            $list[$i]['area_id'] = $v['area_id'];
            $list[$i]['business_id'] = $v['business_id'];
            $list[$i]['device_unique_id'] = $v['device_unique_id'];
            $list[$i]['res_id'] = $v['res_id'];
            //$list[$i]['res_title'] = $v['res_title'];
            $list[$i]['res_title'] = _uri('screen_content', $v['res_id'], 'title');
            $list[$i]['day'] = $v['day'];
            $list[$i]['phone_name'] = $nike_name;
            $list[$i]['phone_version'] = $version;
            $list[$i]['phone_imei'] = $phone_info['imei'];
            $list[$i]['add_time'] = $v['add_time'];
            $list[$i]['click_num'] = isset($v['click_num']) ? $v['click_num'] : 0;
            $i++;
        }
// an_dump($filter);
        Response::assign('search_filter', $search_filter);
        Response::assign('click_list', $list);
        Response::display('admin/click_stat/click_list.html');

    }

    public function click_stat()
    {
        //表名
        $table = 'screen_click_record';

        $page          = Request::get('page_no' , 1) ;
        $content_id    = tools_helper::get('res_id', 0);
        $search_filter = tools_helper::get('search_filter', array());

        $filter = $list = array();

        if ($content_id) {
            $filter['content_id'] = $content_id;
        } else {
            return '请选择内容ID';
        }

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
            $filter['date >='] = date('Ymd', strtotime($search_filter['start_time']));
        } else {
            $filter['date >='] = date('Ymd', time() - 30 * 24 * 3600);
            $search_filter['start_time'] = date('Y-m-d', time() - 30 * 24 * 3600);
        }

        if (isset($search_filter['end_time']) && $search_filter['end_time']) {
            $filter['date <='] = date('Ymd', strtotime($search_filter['end_time']));
        } else {
            $filter['date <='] = $search_filter['end_time'] = date('Ymd', time());
            $search_filter['end_time'] = date('Y-m-d', time());
        }

        if (strtotime($search_filter['start_time']) + 30 * 24 * 3600 == strtotime($search_filter['end_time'])) {
            $search_filter['date_type'] = 3;
        }

        if ($search_filter['start_time'] == $search_filter['end_time']) {
            $search_filter['date_type'] = 1;
        }

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

//         if (!$filter) {
//             $filter = array(1 => 1);
//         }



        $count = _model($table)->getTotal($filter);
        if ($count) {
            $pager  = new Pager($this->per_page);
            $list   = _model($table)->getList($filter , " ORDER BY `id` DESC " . $pager->getLimit($page));

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
        }

        Response::assign('count' , $count);
        Response::assign('page' , $page);

        Response::assign('content_id', $content_id);
        Response::assign('search_filter', $search_filter);
        Response::assign('list', $list);
        Response::display('admin/click_stat/stat_count.html');
    }

    public function stat_count()
    {
        $search_filter  = Request::Get('search_filter', array());
        $page           = tools_helper::get('page_no', 1);
        $default_filter = _widget('screen')->default_search_filter($this->member_info);

        $res_id         = tools_helper::get('res_id', 0);
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
        } else {
            $filter['day >='] = date('Ymd', time() - 30 * 24 * 3600);
            $search_filter['start_time'] = date('Y-m-d', time() - 30 * 24 * 3600);
        }

        if (isset($search_filter['end_time']) && $search_filter['end_time']) {
            $filter['day <='] = date('Ymd', strtotime($search_filter['end_time']));
        } else {
            $filter['day <='] = $search_filter['end_time'] = date('Ymd', time());
            $search_filter['end_time'] = date('Y-m-d', time());
        }

        if (strtotime($search_filter['start_time']) + 30 * 24 * 3600 == strtotime($search_filter['end_time'])) {
            $search_filter['date_type'] = 3;
        }

        if ($search_filter['start_time'] == $search_filter['end_time']) {
            $search_filter['date_type'] = 1;
        }

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

        if ($res_id) {
            $filter['res_id'] = $res_id;
        }

//         if (!$filter) {
//             $filter = array(1 => 1);
//         }

        $list    = array();
        $filter  = get_mongodb_filter($filter);

        try{
            $business_result       = _mongo('screen', 'screen_click_record')->aggregate(array(
                array('$match' => $filter),
                array('$group' => array(
                    '_id'               => array(
//                         'business_id'       => '$business_id',
                        'day'               => '$day',
                        'device_unique_id'  => '$device_unique_id',

                    ),

                    'count'  => array('$sum' => '$click_num'),
                    'day'    => array('$first' => '$day'),
                    'business_id'      => array('$first' => '$business_id'),
                    'device_unique_id' => array('$first' => '$device_unique_id'),

                )),
                array('$sort' => array('day'=>-1))
            ));

        } catch (Exception $e) {
            p($e->getMessage());
            exit;
        }

        $b_num = array();
        foreach ($business_result as $k=> $v) {
            //$b_num[$v['day']] = array('count'=>array($v['count']));
            if (isset($b_num[$v['day']]['click_count'])) {
                $b_num[$v['day']]['click_count']            += $v['count'];
            } else {
                $b_num[$v['day']]['click_count']            = $v['count'];
            }

            $b_num[$v['day']]['business'][$v['business_id']]         = 1;
            $b_num[$v['day']]['device_unique_id'][$v['device_unique_id']] = 1;
        }

        $new_list = array();
        foreach ($b_num as $kk => $vv) {
            $new_list[$kk]['click_count']             = $b_num[$kk]['click_count'];
            $new_list[$kk]['business']          = count($b_num[$kk]['business']);
            $new_list[$kk]['device_unique_id']  = count($b_num[$kk]['device_unique_id']);
        }

        Response::assign('res_id', $res_id);
        Response::assign('new_list', $new_list);
        Response::assign('search_filter', $search_filter);

        Response::display('admin/click_stat/stat_count.html');
    }

    public function business()
    {

        $search_filter  = Request::Get('search_filter', array());
        $page           = tools_helper::get('page_no', 1);
        $date           = tools_helper::get('date', 0);

        $default_filter = _widget('screen')->default_search_filter($this->member_info);

        $res_id         = tools_helper::get('res_id', 0);

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
            //$filter['day >='] = date('Ymd', strtotime($search_filter['start_time']));
        } else {
            //$filter['day >='] = date('Ymd', time() - 30 * 24 * 3600);
            $search_filter['start_time'] = date('Y-m-d', time() - 30 * 24 * 3600);
        }

        if (isset($search_filter['end_time']) && $search_filter['end_time']) {
            //$filter['day <='] = date('Ymd', strtotime($search_filter['end_time']));
        } else {
            //$filter['day <='] = $search_filter['end_time'] = date('Ymd', time());
            $search_filter['end_time'] = date('Y-m-d', time());
        }

        if (strtotime($search_filter['start_time']) + 30 * 24 * 3600 == strtotime($search_filter['end_time'])) {
            $search_filter['date_type'] = 3;
        }

        if ($search_filter['start_time'] == $search_filter['end_time']) {
            $search_filter['date_type'] = 1;
        }

        if ($date) {
            $filter['day'] = $date;
        }
        $filter['res_id'] = $res_id;
        //$list  = _model('screen_click_record')->getList($filter, ' GROUP BY `business_id` ');


        $filter     = get_mongodb_filter($filter);

        //p($filter);
        try{
            $business_result       = _mongo('screen', 'screen_click_record')->aggregate(array(
                array('$match' => $filter),
                array('$group' => array(
                    '_id'               => array(
                        'business_id'       => '$business_id',
                        //'device_unique_id'  => '$device_unique_id',
                    ),
                    'day'    => array('$first' => '$day'),
                    'count'  => array('$sum' => '$click_num'),
//                     'business_id'      => array('$first' => '$business_id'),
//                     'device_unique_id' => array('$first' => '$device_unique_id'),
                ))));

        } catch (Exception $e) {
            p($e->getMessage());
            exit;
        }

        $b_num = array();
//         foreach ($business_result as $k=> $v) {
//             $b_num[$k] = (array)$v['_id'];
//         }

        $list = array();
        foreach ($business_result as $k => $v) {
            foreach ($v['_id'] as $vv) {
                $filter['business_id']  = $vv;
                $list[$k]['business_id'] = $vv;
            }

            //$device = _model('screen_click_record')->getList($filter, ' GROUP BY `business_id` , `device_unique_id`');
            //p($filter, $business_result);
            $device       = _mongo('screen', 'screen_click_record')->aggregate(array(
                array('$match' => $filter),
                array('$group' => array(
                    '_id'               => array(
                        'business_id'       => '$business_id',
                        'device_unique_id'  => '$device_unique_id'
                    ),
                ))));

            $d_num = array();
            foreach ($device as $kk=> $dvv) {
                //p((array)$v);
                $d_num[$kk] = (array)$dvv['_id'];
            }

            $list[$k]['device_num'] = count($d_num);
            //$list[$k]['click_num']  = _model('screen_click_record')->getTotal($filter);
            $list[$k]['click_num']  = $v['count'];
            $list[$k]['day']        = $v['day'];
        }

//        p($filter, $list);
        Response::assign('list', $list);
        Response::assign('search_filter', $search_filter);
        Response::assign('res_id', $res_id);

        Response::display('admin/click_stat/stat_business.html');
    }

    public function stat_device()
    {

        $search_filter  = Request::Get('search_filter', array());
        $page           = tools_helper::get('page_no', 1);
        $date           = tools_helper::get('date', 0);

        $default_filter = _widget('screen')->default_search_filter($this->member_info);

        $res_id         = tools_helper::get('res_id', 0);

        $filter = $default_filter;

//         if (isset($search_filter['date_type']) && $search_filter['date_type']) {
//             if (1 == $search_filter['date_type']) {
//                 $search_filter['start_time'] = $search_filter['end_time'] = date('Y-m-d');

//             } else if (2 == $search_filter['date_type']) {
//                 $search_filter['start_time'] = date('Y-m-d',time() - 7 * 24 * 3600);
//                 $search_filter['end_time']   = date('Y-m-d');

//             } else if (3 == $search_filter['date_type']) {
//                 $search_filter['start_time'] = date('Y-m-d',time() - 30 * 24 * 3600);
//                 $search_filter['end_time']   = date('Y-m-d');

//             }
//         }

//         if (isset($search_filter['start_time']) && $search_filter['start_time']) {
//             //$filter['day >='] = date('Ymd', strtotime($search_filter['start_time']));
//         } else {
//             //$filter['day >='] = date('Ymd', time() - 30 * 24 * 3600);
//             $search_filter['start_time'] = date('Y-m-d', time() - 30 * 24 * 3600);
//         }

//         if (isset($search_filter['end_time']) && $search_filter['end_time']) {
//             //$filter['day <='] = date('Ymd', strtotime($search_filter['end_time']));
//         } else {
//             //$filter['day <='] = $search_filter['end_time'] = date('Ymd', time());
//             $search_filter['end_time'] = date('Y-m-d', time());
//         }

//         if (strtotime($search_filter['start_time']) + 30 * 24 * 3600 == strtotime($search_filter['end_time'])) {
//             $search_filter['date_type'] = 3;
//         }

//         if ($search_filter['start_time'] == $search_filter['end_time']) {
//             $search_filter['date_type'] = 1;
//         }

        if ($date) {
            $filter['day'] = $date;
        }
        $filter['res_id'] = $res_id;

        $filter     = get_mongodb_filter($filter);

        try{
            $business_result       = _mongo('screen', 'screen_click_record')->aggregate(array(
                array('$match' => $filter),
                array('$group' => array(
                    '_id'               => array(
                        'business_id'       => '$business_id',
                    ),

                    'day'          => array('$first' => '$day'),
                    'business_id'  => array('$first' => '$business_id'),
                ))));

        } catch (Exception $e) {
            p($e->getMessage());
            exit;
        }

        $business_num = array();
        foreach ($business_result as $v) {
            $business_num[$v['business_id']]  = 1;
        }

        $click_list = array();
        $list       = array();

        if (count($business_num)) {
            //MongoDB分页类
            $pager = new MongoDBPager( $this->per_page );
            if ( $pager->generate($count) ) {
                Response::assign( 'pager', $pager );
            }
            Response::assign( 'count', $count );
            $order = array('$sort' => array('_id' => -1));

            $filter['business_id'] = array_keys($business_num);

            $skip  = $pager->getLimit($page)['skip'];
            $limit = $pager->getLimit($page)['limit'];

            $click_list = _mongo('screen', 'screen_click_record')->aggregate(array(
                array('$match' => get_mongodb_filter($filter)),
                array('$group' => array(
                    '_id'               => array(
                        //'business_id'       => '$business_id',
                        'device_unique_id'  => '$device_unique_id',
                    ),

                    'day'          => array('$first' => '$day'),
                    'business_id'  => array('$first' => '$business_id'),
                    'device_unique_id' => array('$first' => '$device_unique_id'),
                    'count'         => array('$sum' => '$click_num'),
                )),
                $order,
                array('$skip' => $skip),
                array('$limit'=> $limit)

            ));
        }

        $b_num = array();
        $list = array();

        foreach ($click_list as $k => $v) {
            $v = (array)$v;
            $filter['business_id']  = $v['business_id'];
            $list[$k]['business_id'] = $v['business_id'];
            //$list[$k]['device_num'] = count($d_num);
            $list[$k]['click_num']  = $v['count'];
            $list[$k]['day']        = $v['day'];
            $list[$k]['device_unique_id'] = $v['device_unique_id'];
        }

        Response::assign('list', $list);
        Response::assign('search_filter', $search_filter);
        Response::assign('res_id', $res_id);

        Response::display('admin/click_stat/stat_device.html');
    }

    private function get_mongodb_filter($filter)
    {
        $new_filter = array();

        if (isset($filter[1])) {
            $filter = array();
        }

        foreach ($filter as $k => $v) {

            if (is_array($v)) {
                $new_filter[trim($k)] = array('$in' => $v);
            } else if (is_numeric($v)) {
                $new_filter[trim($k)] = (int)$v;
            } else {

                if ( strpos($k, '<=') ) {
                    $new_filter[trim(str_replace('<=', '', $k))]['$lte'] = $v;
                } else if ( strpos($k, '>=') ) {
                    $new_filter[trim(str_replace('>=', '', $k))]['$gte'] = $v;
                } else if (strpos($k, '<')) {
                    $new_filter[trim(str_replace('<', '', $k))]['$lt'] = $v;
                } else if (strpos($k, '>')) {
                    $new_filter[trim(str_replace('<', '', $k))]['$gt'] = $v;
                } else {
                    $new_filter[trim($k)] = $v;
                }
            }

        }

        return $new_filter;
    }
}