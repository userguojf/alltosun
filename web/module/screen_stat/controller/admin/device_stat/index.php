<?php

/**
 * alltosun.com 设备统计 index.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年9月16日 下午9:10:55 $
 * $Id$
 */


load_file('screen_stat','trait', 'stat');

class Action
{
    use stat;

    private $next_res_name = '';
    private $next_res_name_id = '';
    private $nick_name   = '';


    public function __call($action='', $params=array())
    {
        if (!$this->member_info) {
            return $this->error_info;
        }

        $filter = array();

        $wangjf_debug = tools_helper::Get('wangjf_test', 0);

        switch($this->member_info['res_name'])
        {
            case 'group':
                $this->nick_name  = '全国';
                $this->next_res_name = 'province';
                $this->next_res_name_id = 'province_id';
                break;
            case 'province':
                $this->nick_name  = _uri($this->res_name,$this->res_id,'name');
                $filter           = array('province_id' => $this->res_id);
                $this->next_res_name = 'city';
                $this->next_res_name_id = 'city_id';
                break;
            case 'city':
                $this->nick_name  = _uri($this->res_name,$this->res_id,'name');
                $filter           = array('city_id' => $this->res_id);
                $this->next_res_name = 'area';
                $this->next_res_name_id = 'area_id';
                break;
            case 'area':
                $this->nick_name  = _uri($this->res_name,$this->res_id,'name');
                $filter           = array('area_id' => $this->res_id);
                $this->next_res_name = 'business_hall';
                break;
            case 'business_hall':
                $this->nick_name  = _uri('business_hall',$this->res_id,'title');
                $filter           = array('business_id' => $this->res_id);
                $this->res_name = 'business';
                $this->next_res_name = 'business';
                $this->next_res_name_id = 'business_id';
                break;
        }


        $this->nick_name = $this->nick_name.'(合计)';
        //搜索条件
        $search_filter = $this->search_filter();


        if (!empty($search_filter['hour'])) {
            unset($search_filter['hour']);
        }

        $filter = array_merge($filter, $search_filter);

        //查询营业厅列表
        $stat_list = _model('screen_business_stat_day')->getList($filter);

        //今日或时间段为一天查询小时数据
        if ($this->date_type == 2 || $this->date_type == 5) {
            $echart_data = $this->handle_hour_echart_data($filter);
            //本周、本月、时间段超过1天的查询天数据
        } else {
            //图表数据, 处理图表数据
            $echart_data = $this->handle_echart_data($stat_list);

        }

//         if ($this->res_name == 'group') {
//             $sql = "SELECT COUNT(*) as device_num,{$this->next_res_name_id} FROM `screen_device` where status=1 GROUP BY {$this->next_res_name_id}";
//         } else {
//             $sql = "SELECT COUNT(*) as device_num,{$this->next_res_name_id} FROM `screen_device` WHERE {$this->res_name}_id={$this->res_id} AND status=1 GROUP BY {$this->next_res_name_id}";
//         }

        // 修改sql语句 by guojf
        if ($this->res_name == 'group') {
            $sql = "SELECT COUNT(*) as device_num,{$this->next_res_name_id} FROM `screen_device` where status=1 ";
        } else {
            $sql = "SELECT COUNT(*) as device_num,{$this->next_res_name_id} FROM `screen_device` WHERE {$this->res_name}_id={$this->res_id} AND status=1 ";
        }

        foreach ( $filter as $k => $v ) {
            if ( strpos($k, "<=") ) {
                $sql .= " AND {$k}{$v} ";
            }
        }

        $sql .= " GROUP BY {$this->next_res_name_id} ";

        $list = _model('screen_device')->getAll($sql);

        //p($list, $this->next_res_name_id);
        //分组
        $table_list         = $this->table_list($list, $search_filter);

        if ($table_list === false) {
            return $this->error_info;
        }
        Response::assign('region_type', $this->next_res_name);
        Response::assign('region_id', $this->next_res_name_id);
        Response::assign('echart_data', $echart_data);
        Response::assign('table_list', $table_list);

        Response::display('admin/device_stat/index.html');
    }

    public function table_list($list, $filter)
    {
        $table_list = array();
        $first      = array();

        //表格首行数据
        $first_data = array(
            'device_num' => 0,
            'active_num' => 0,
            'experience_time' => 0,
            'online_num'      => 0,
            'business_hall_num' => 0,
            'new_device_num'    => 0,
            'new_business_num'  => 0,
            'drop_off_num'      => 0
        );

        if (isset($filter['day'])) {
            $e_filter['day']   = $filter['day'];
            $new_num_filter['add_time >=']  = date('Y-m-d', strtotime($filter['day']))." 00:00:00";
            $new_num_filter['add_time <=']  = date('Y-m-d', strtotime($filter['day']))." 23:59:59";
        }
        if (isset($filter['day >=']) && isset($filter['day <'])) {
            $e_filter['day >='] = $filter['day >='];
            $new_num_filter['add_time >=']  = date('Y-m-d', strtotime($filter['day >=']))." 00:00:00";
            $new_num_filter['add_time <=']  = date('Y-m-d', strtotime($filter['day <']))." 23:59:59";
        }
        if (isset($filter['day >=']) && isset($filter['day <='])) {
            $e_filter['day <'] = $filter['day <='];
            $new_num_filter['add_time >=']  = date('Y-m-d', strtotime($filter['day >=']))." 00:00:00";
            $new_num_filter['add_time <=']  = date('Y-m-d', strtotime($filter['day <=']))." 23:59:59";
        }
        if (isset($filter['day <'])) {
            $e_filter['day <'] = $filter['day <'];
        }
        if (isset($date_time['day <='])) {
            $e_filter['day <='] = $filter['day <='];
            $new_num_filter['add_time >=']  = date('Y-m-d', strtotime($filter['day >=']))." 00:00:00";
            $new_num_filter['add_time <=']  = date('Y-m-d', strtotime($filter['day <=']))." 23:59:59";
        }

        $last_business_filter['add_time <']      = $new_num_filter['add_time >='];

        $last_business_filter['status'] = $new_num_filter['status']  = 1;


        $e_filter = $active_filter = $filter;

        foreach ($list as $k => $v) {

            $active_filter[$this->next_res_name_id]  = $v[$this->next_res_name_id];
            $new_num_filter[$this->next_res_name_id] = $v[$this->next_res_name_id];
            $last_business_filter[$this->next_res_name_id] = $v[$this->next_res_name_id];

            $unique_ids = _model('screen_device')->getFields('device_unique_id',array($this->next_res_name_id => $v[$this->next_res_name_id], 'status'=>1));
            $b_count    = count(_model('screen_device')->getFields('device_unique_id',array($this->next_res_name_id => $v[$this->next_res_name_id], 'status'=>1), ' GROUP BY business_id '));

            $active_filter['device_unique_id'] = $unique_ids;

            $active_num       = count(_model('screen_device_online_stat_day')->getFields('device_unique_id',$active_filter, ' GROUP BY device_unique_id '));

            $new_device_num   = count(_model('screen_device')->getFields('device_unique_id', $new_num_filter, ' GROUP BY device_unique_id '));

            $business_ids      = _model('screen_device')->getFields('business_id', $new_num_filter, ' GROUP BY business_id ');
            $last_business_ids = _model('screen_device')->getFields('business_id', $last_business_filter, ' GROUP BY business_id ');

            $new_business_num  = count(array_diff($business_ids, $last_business_ids));
            //p($business_ids, $last_business_ids, array_diff($business_ids, $last_business_ids));

            if (Request::Get('test',0)) {
                p($active_filter);
            }

            if ($this->date_type == 2) {
                $online_filter=array(
                    $this->next_res_name_id => $v[$this->next_res_name_id],
                    'day'         => (int)date('Ymd'),
                    'update_time >='  => date('Y-m-d H:i:s', time()-1800)
                );
                $online_num         = count(_model('screen_device_online_stat_day')->getFields('device_unique_id', $online_filter, ' GROUP BY `device_unique_id` '));

            }

            $e_filter[$this->next_res_name_id] = $v[$this->next_res_name_id];

            $e_time  = 0;
            $e_count = _mongo('screen', 'screen_action_record')->aggregate(array(
                array('$match' => get_mongodb_filter($e_filter)),
                array('$group' => array(
                    '_id'               => array(
                         'province_id'       => '$province_id',
                    ),
                    'count'    => array('$sum'=>'$experience_time'),
                ),
                )));

            foreach ($e_count as $ev) {
                $e_time = $ev['count'];
            }

            //查询地区名称
            if ($this->res_name == 'business') {
                $region_table = 'business_hall';
            } else {
                $region_table = $this->next_res_name;
            }

            if ($this->member_info['res_name'] != 'business') {
                $field = 'name';
            } else {
                $field = 'title';
            }

            $table_list[$k]['region_name']       = _uri($region_table, $v[$this->next_res_name_id], $field);

            $drop_off_num   = count(_model('screen_device')->getFields(
                        'device_unique_id',
                        array(
                                $this->next_res_name_id => $v[$this->next_res_name_id],
                                'status' => 0
                        ) )
            );

            $table_list[$k]['business_hall_num'] = $b_count;
            $table_list[$k]['device_num']        = $v['device_num'];
            $table_list[$k]['experience_time']   = $e_time;
            $table_list[$k]['active_num']        = $active_num;
            $table_list[$k]['new_business_num']  = $new_business_num;
            $table_list[$k]['new_device_num']    = $new_device_num;
            $table_list[$k][$this->next_res_name_id]       = $v[$this->next_res_name_id];
            $table_list[$k]['drop_off_num']      = $drop_off_num;

            $first_data['business_hall_num']  += $b_count;
            $first_data['device_num']         += $v['device_num'];
            $first_data['experience_time']    += $e_time;
            $first_data['active_num']         += $active_num;
            $first_data['new_business_num']   += $new_business_num;
            $first_data['new_device_num']     += $new_device_num;
            $first_data['drop_off_num']       += $drop_off_num;

            if ($this->date_type == 2) {
                $table_list[$k]['online_num'] = $online_num;
                $first_data['online_num']          += $online_num;
            }

        }

        $first_data[$this->res_name]  = $this->res_id;
        $first_data['region_name']    = $this->nick_name;

        $return_data[] = $first_data;

        if ($this->res_name != 'business') {
            foreach ($table_list as $k => $v) {
                $return_data[] = $v;
            }
        }

        return $return_data;
    }

    public function handle_hour_echart_data($filter)
    {

        $date_data       = array();
        $experience_time = array();
        $action_num      = array();
        $i               = 0;
        $t_count      = array();
        $region       = $this->region_filter();

        $a_filter = $region;
        $filter   = array_merge($filter, $region);

        $unique_ids = _model('screen_device')->getFields('device_unique_id', array('status' => 1));

        foreach (self::$hour_range as $v) {
            $v = strlen($v) == 1 ? '0'.$v : $v;
            $start = date('Y-m-d', strtotime($filter['day'])).' ' .$v.':00:00';
            $end   = date('Y-m-d', strtotime($filter['day'])).' ' .$v.':59:59';

//             if ($start > date("Y-m-d H:i:s")) {
//                 continue;
//             }
            //日期
            $date_data[] = $v;

            $experience_time_filter = $filter;
            $experience_time_filter['add_time >='] = $start;
            $experience_time_filter['add_time <='] = $end;
            $experience_time_filter['type']             = 2;
            $experience_time_filter['device_unique_id'] = $unique_ids;

            $e_filter = get_mongodb_filter($experience_time_filter);

            $exper_time = _mongo('screen', 'screen_action_record')->find($e_filter, array('projection'=>['experience_time'=>1]));


            foreach ($exper_time as $vv) {

                $t_count[$i] = $vv['experience_time'];
                $i++;
            }

            //体验时长
            //$time_count = array_sum(_model('screen_action_record')->getFields('experience_time', $experience_time_filter));

            $e_time = round(array_sum($t_count)/60, 1);

            $experience_time[] = $e_time;

            //活跃数
            $a_filter['add_time >='] = $start;
            $a_filter['add_time <='] = $end;

            //p($action_filter);
            $result       = _mongo('screen', 'screen_device_online')->aggregate(array(
                        array('$match' => get_mongodb_filter($a_filter)),
                        array('$group' => array(
                                '_id'               => array(
                                        'device_unique_id'  => '$device_unique_id',
                                )
                        )
                        )

            ));

            $result = $result->toArray();
            $active_num[] = count($result);

            $t_count = array();

        }

        return json_encode(array(
                'date_data'     => $date_data,
                'active_num'    => $active_num,
                'experience_time' => $experience_time
        ));

    }

    public function update_stat(){
        $date = tools_helper::get('date', date('Y-m-d 00:00:00'));
        //$date = tools_helper::get('date', date('Y-m-d 00:00:00'));
        $filter = array(
                'add_time >=' => date('Y-m-d 00:00:00', strtotime($date)),
                'add_time <=' => date('Y-m-d 23:59:59', strtotime($date)),
                'type'         => 2
        );


        $device_list = _model('screen_action_record')->getList($filter, ' GROUP BY `device_unique_id`, `business_id` ');

        $stat_ids  = array();

        foreach ($device_list as $k => $v) {
            $stat_filter = array(
                    'day'                   => $v['day'],
                    'device_unique_id'      => $v['device_unique_id'],
                    'business_id'           => $v['business_id'],
            );

            //查询统计
            $stat_info = _model('screen_device_stat_day')->read($stat_filter);

            $new_filter = array_merge($filter, $stat_filter);

            //获取动作数
            $action_num = _model('screen_action_record')->getTotal($new_filter);
            $experience_time = array_sum(_model('screen_action_record')->getFields('experience_time', $new_filter));

            if (!$stat_info) {

                $new_stat = array(
                        'province_id' => $v['province_id'],
                        'city_id'     => $v['city_id'],
                        'area_id'     => $v['area_id'],
                        'business_id' => $v['business_id'],
                        'device_unique_id'        => $v['device_unique_id'],
                        'day'         => $v['day'],
                        'action_num'  => $action_num,
                        'experience_time' => $experience_time,
                );


                $id = _model('screen_device_stat_day')->create($new_stat);
                if ($id) {
                    $stat_ids[] = $id;
                }
            } else {
                _model('screen_device_stat_day')->update($stat_info['id'], array('action_num'  => $action_num,'experience_time' => $experience_time));
                $stat_ids[] = $stat_info['id'];
            }
        }

        $stat_ids = array_unique($stat_ids);

        //按厅统计
        foreach ($stat_ids as $v) {
            //按厅统计
            _widget('screen_stat')->add_action_stat_by_business($v);
        }
    }


    public function drop_off()
    {
        $page = tools_helper::get('page_no' , 1) ;
        $search_filter = tools_helper::get('search_filter', array());

        if ( !isset($search_filter['res_id']) || !isset($search_filter['res_name']) ) {
            return '请传正确的参数';
        }

        $filter = $list = [];
// p($this->member_info);
        if ( $search_filter['res_id'] && $search_filter['res_name'] ) {
            $filter[$search_filter['res_name']."_id"] = $search_filter['res_id'];
        } else {
            if ( $this->res_name == 'business_hall' ) {
                $filter["business_id"] = $this->member_info['res_id'];
            } else if ( $this->res_name == 'group' ){

            } else {
                $filter[$this->member_info['res_name']."_id"] = $this->member_info['res_id'];

            }
        }
// p($filter);
        $filter['status'] = 0;

        $count = _model('screen_device')->getTotal($filter);
        if ($count) {
            $pager  = new Pager($this->per_page);
            $list   = _model('screen_device')->getList($filter , " ORDER BY `id` DESC " . $pager->getLimit($page));

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
        }

        Response::assign('count' , $count);
        Response::assign('page' , $page);
        Response::assign('list', $list);
        Response::display('admin/device_stat/drop_off.html');
    }
}