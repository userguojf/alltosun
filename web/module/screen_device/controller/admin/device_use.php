<?php

/**
 * alltosun.com  device_use.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王敬飞 (wangjf@alltosun.com) $
 * $Date: 2018年01月08日 下午2:39:04 $
 * $Id$
 */
class Action
{
    private $search_type = '';
    private $search_filter = array();
    private $start_day  = 0;
    private $end_day    = 0;
    private $per_page   = 30;
    private $member_id = 0;
    private $member_info = array();

    public function __construct()
    {
        $this->member_id   = member_helper::get_member_id();
        $this->member_info = member_helper::get_member_info($this->member_id);

        //初始化搜索条件
        $search_filter = tools_helper::Get('search_filter', array());

        if (empty($search_filter['start_date'])) {
            $search_filter['start_date'] = date('Y-m-d');
        }

        if (empty($search_filter['end_date'])) {
            $search_filter['end_date'] = date('Y-m-d');
        }

        $this -> search_filter = $search_filter;
        $this->start_day  = date('Ymd', strtotime($search_filter['start_date']));
        $this->end_day    = date('Ymd', strtotime($search_filter['end_date']));

        $search_filter_str = '?';
        foreach ($search_filter as $k => $v) {
            $search_filter_str .= "search_filter[{$k}]={$v}&";
        }

        $search_filter_str = rtrim($search_filter_str, '&');
        Response::assign('search_filter_str', $search_filter_str);
        Response::assign('search_filter', $search_filter);
    }

    /**
     * 首页
     * @param string $action
     * @param array $params
     */
    public function __call($action='', $params = array())
    {
        $search_type    = tools_helper::get('search_type', 'phone_version');
        $this -> search_type = $search_type;

        if (!$this->member_info) {
            return '请先登录';
        }

        if ($this->member_info['res_name'] != 'group') {
            return '您暂无权限查看此页面内容';
        }

        if ($search_type == 'phone_name') {
            //按品牌
            $stat_list = $this -> by_phone_name();

        } else {
            //按型号
            $stat_list = $this -> by_phone_version();
        }

        //获取营业厅总数
        $business_all_num = $this->get_business_hall_all_count();
        //获取新安装营业厅总数
        $business_new_num = $this->get_business_hall_new_count();
        //获取设备总数
        $device_all_num = $this->get_device_all_count();
        //获取新安装设备总数
        $device_new_num = $this->get_device_new_count();
        //获取活跃设备总数
        $active_device_num = $this->get_active_device_count();

        //获取累计体验时长
        $experience_time_num = $this->get_experience_time_sum();


        Response::assign('stat_list', $stat_list);
        Response::assign('business_all_num', $business_all_num);
        Response::assign('business_new_num', $business_new_num);
        Response::assign('device_all_num', $device_all_num);
        Response::assign('device_new_num', $device_new_num);
        Response::assign('active_device_num', $active_device_num);
        Response::assign('experience_time_num', $experience_time_num);
        Response::assign('stat_list', $stat_list);
        Response::assign('search_type', $search_type);
        Response::display('admin/device_use/index.html');
    }

    /**
     * 获取累计体验时长
     */
    private function get_experience_time_sum()
    {
        $filter = _widget('screen')->init_filter($this->member_info, $this->search_filter);

        if (!empty($filter['business_hall_id'])) {
            $filter['business_id'] = $filter['business_hall_id'];
            unset($filter['business_hall_id']);
        }

        $filter['day >='] = (int)$this->start_day;
        $filter['day <='] = (int)$this->end_day;

        $filter = get_mongodb_filter($filter);
        //为预防换厅的设备导致统计混乱， 故此按营业厅分组
        $stat_list       = _mongo('screen', 'screen_device_stat_day')->aggregate(
                array(
                        array('$match' => $filter),
                        array('$group' => array(
                                '_id'               => array(
                                        'device_unique_id'  => '$device_unique_id',
                                        'business_id'       => '$business_id',
                                ),
                                'experience_times'  => array('$sum' => '$experience_time'),
                                'business_id'       => array('$first' => '$business_id'),
                                'device_unique_id'  => array('$first' => '$device_unique_id'),
                        )
                        )
                )
        );

        //查询设备条件
        $device_filter = $this->init_filter();
        $device_filter['status'] = 1;

        $device_list = _model('screen_device')->getList($device_filter);

        $nwe_device_list = array();
        foreach ($device_list as $k => $v) {
            $nwe_device_list[$v['device_unique_id'].'_'.$v['business_id']] = 1;
        }

        $experience_stat = array();
        foreach ($stat_list as $k => $v) {
            $v = (array)$v;
            $key = $v['device_unique_id'].'_'.$v['business_id'];
            if (empty($nwe_device_list[$key])) {
                continue;
            }

            $experience_stat[$key]          = $v['experience_times'];
        }

        return array_sum($experience_stat);
    }

    /**
     * 获取活跃设备设备量
     */
    private function get_active_device_count()
    {

        $filter = _widget('screen')->init_filter($this->member_info, $this->search_filter);

        if (!empty($filter['business_hall_id'])) {
            $filter['business_id'] = $filter['business_hall_id'];
            unset($filter['business_hall_id']);
        }

        $filter['day >='] = $this->start_day;
        $filter['day <='] = $this->end_day;
        $filter['is_online'] = 1;

        $active_device_list = _model('screen_device_online_stat_day')->getList($filter, ' GROUP BY `business_id`,`device_unique_id` ');

        //查询设备条件
        $device_filter = $this->init_filter();
        $device_filter['status'] = 1;

        //查询所有上架的设备
        $device_list = _model('screen_device')->getList($device_filter);

        $nwe_device_list = array();
        foreach ($device_list as $k => $v) {
            $nwe_device_list[$v['device_unique_id'].'_'.$v['business_id']] = 1;
        }

        foreach ($active_device_list as $k => $v) {
            $key = $v['device_unique_id'].'_'.$v['business_id'];
            if (empty($nwe_device_list[$key])) {
                unset($active_device_list[$k]);
            }
        }

        return count($active_device_list);
    }

    /**
     * 获取设备总数
     */
    private function get_device_all_count()
    {
        //初始化搜索条件
        $filter = $this->init_filter();
        $filter['status'] = 1;

        //符合条件的设备
        return _model('screen_device')->getTotal($filter);
    }

    /**
     * 获取新设备总数
     */
    private function get_device_new_count()
    {
        //初始化搜索条件
        $filter = $this->init_filter();
        $filter['status'] = 1;
        $filter['day >='] = $this->start_day;
        $filter['day <='] = $this->end_day;

        //符合条件的设备
        return _model('screen_device')->getTotal( $filter );
    }


    /**
     * 获取营业厅总数
     */
    private function get_business_hall_all_count()
    {

        $filter = $this->init_filter();
        $filter['status'] = 1;

        $business_ids = _model('screen_device')->getFields('business_id', $filter, ' GROUP BY `business_id` ');
        return count($business_ids);
    }

    /**
     * 获取新增营业厅总数
     */
    private function get_business_hall_new_count()
    {

        $filter = $this->init_filter();
        $filter['status'] = 1;

        //查询
        $new_filter = $filter;
        $new_filter['day >='] = $this->start_day;
        $new_filter['day <='] = $this->end_day;
        $business_ids = _model('screen_device')->getFields('business_id', $new_filter, ' GROUP BY `business_id` ');
        $new_filter2 = $filter;
        $new_filter2['day <'] = $this->end_day;
        $business_ids2 = _model('screen_device')->getFields('business_id', $new_filter, ' GROUP BY `business_id` ');

        $business_id = array_diff($business_ids, $business_ids2);

        return count($business_id);
    }

    /**
     * 初始化搜索条件
     */
    private function init_filter()
    {
        $filter = _widget('screen')->init_filter($this->member_info, $this->search_filter);

        if (!empty($filter['business_hall_id'])) {
            $filter['business_id'] = $filter['business_hall_id'];
            unset($filter['business_hall_id']);
        }

        if (!empty($this->search_filter['phone_name'])) {
            $filter['phone_name'] = $this->search_filter['phone_name'];
        }

        if (!empty($this->search_filter['phone_version'])) {
            $filter['phone_version'] = $this->search_filter['phone_version'];
        }

        return $filter;
    }



    /**
     * 根据品牌型号统计
     */
    private function by_phone_version()
    {

        $filter = array();

        if (!empty($this->search_filter['phone_name'])) {
            $filter['phone_name'] = $this->search_filter['phone_name'];
        }

        if (!empty($this->search_filter['phone_version'])) {
            $filter['phone_version'] = $this->search_filter['phone_version'];
        }

        if (!$filter) {
            //查询所有的型号
            $filter = array(1=>1);
        }

        //分页获取设备列表（包含分页处理）
        $device_list = $this->get_device_list_by_page($filter);

        $stat_list  = array();
        foreach ($device_list as $k => $v) {
            //处理统计数据
            $stat_info = $this->handle_stat($v);

            //拼接机型信息
            $stat_info['phone_name']     = $v['phone_name'];
            $stat_info['name_nickname']  = $v['name_nickname'] ? $v['name_nickname'] : $v['phone_name'];
            $stat_info['phone_version']  = $v['phone_version'];
            $stat_info['version_nickname']  = $v['version_nickname'] ? $v['version_nickname'] : $v['phone_version'];
            $stat_list[] = $stat_info;
        }

        if (!$stat_list) {
            return $stat_list;
        }

        return $stat_list;
    }


    /**
     * 根据品牌统计
     */
    private function by_phone_name()
    {
        $filter = $this->init_filter();
        //查询所有非下架的品牌
        $filter['status'] = 1;

        //分页获取设备列表（包含分页处理）
        $device_list = $this->get_device_list_by_page($filter);

        $stat_list  = array();
        //排序数据
        $orders     = array();
        foreach ($device_list as $v) {
            //处理统计数据
            $stat_info = $this->handle_stat($v);

            //拼接机型信息
            $nickname = screen_device_helper::get_device_nickname_info(array('phone_name' => $v, 'name_nickname !=' => ''), 'name_nickname');
            $stat_info['phone_name']     = $v;
            $stat_info['name_nickname']  = $nickname;
            $stat_list[] = $stat_info;
        }
        return $stat_list;
    }

    /**
     * 分页获取设备列表
     */
    private function get_device_list_by_page($filter)
    {

        $page           = tools_helper::get('page_no', 1);
        $order_dir      = tools_helper::Get('order_dir', 'desc');
        $order_field    = tools_helper::Get('order_field', 'device_num');
        $is_export      = tools_helper::get('is_export', 0);

        Response::assign('order_dir', $order_dir);
        Response::assign('order_field', $order_field);

        //先查出所有
        if ($this->search_type == 'phone_name') {
            $device_list = _model('screen_device')->getFields('phone_name', $filter, ' GROUP BY `phone_name` ORDER BY `device_nickname_id` DESC ');
        } else {
            $device_list = _model('screen_device_nickname') -> getList($filter);
        }

        $orders = array();

        foreach ( $device_list as $k => $v ) {
            $device_unique_ids = array();
            $business_ids      = array();

            if ($this->search_type == 'phone_name') {
                $device_list[$k] = array('phone_name'=> $v);
            }
            //获取指定范围的设备
            $device_list2 = $this->get_range_device($v);
            foreach ($device_list2 as $kk => $vv) {
                //处理为指定格式的设备列表
                if ($order_field == 'business_hall_num') {
                    //营业厅总数数据
                    $business_ids[$vv['business_id']] = 0;
                } else {
                    $device_unique_ids[$vv['device_unique_id']] = $vv['business_id'];
                }

            }

            //按活跃设备排序
            if ($order_field == 'active_device_num') {

                //活跃设备
                $active_device = $this -> get_active_device($device_unique_ids);
                //活跃设备量
                $orders[] = $order_value = count($active_device);

                //按营业厅总数排序
            } else if ($order_field == 'business_hall_num') {
                $orders[] = $order_value = count($business_ids);
                //按设备总数排序
            } else if ($order_field == 'device_num') {
                $orders[] = $order_value = count($device_unique_ids);
                //按体验时长排序
            } else {
                //体验设备
                $experience_device = $this->get_device_experience_time($device_unique_ids);
                $orders[] = $order_value = array_sum($experience_device);
            }

            $device_list[$k][$order_field] = $order_value;

        }

        //排序
        if ($order_dir == 'asc') {
            $order = SORT_ASC;
        } else {
            $order = SORT_DESC;
        }

        array_multisort($orders,$order, $device_list);

        if ($is_export == 1) {
            $this->is_export($device_list, $this->search_type);
        }

        //分页
        $count = count($device_list);
        if ( $count ) {
            $pager = new Pager($this->per_page);
            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }

            $pager->getLimit($page);
            Response::assign('count', $count);

            $limit = ($page -1)*$this->per_page;
            $device_list =  array_slice($device_list, $limit, $this->per_page);

            if ($this->search_type == 'phone_name') {
                $new_list = array();
                foreach ($device_list as $k => $v) {
                    $new_list[] = $v['phone_name'];
                }
                return $new_list;
            }
        }
        return $device_list;
    }

    /**
     * 处理统计数据
     * @param unknown $device_list
     */
    private function handle_stat($device_info)
    {
        //查询获取指定范围的设备
        $device_list = $this->get_range_device($device_info);

        //营业厅总数数据
        $business_ids = array();
        //新增门店
        $new_device_business_hall = array();
        //新增设备
        $new_device = array();
        //设备列表
        $device_unique_ids = array();
        foreach ($device_list as $k => $v) {

            //营业厅总数数据
            $business_ids[$v['business_id']] = 0;

            if ($v['day'] >= $this->start_day && $v['day'] <= $this->end_day) {
                //新增设备的门店
                $new_device_business_hall[$v['business_id']] = 1;
                //新增设备
                $new_device[$v['device_unique_id']] = 1;
            }

            //所有的设备, key 为设备， value为所在厅，便于换厅设备处理
            $device_unique_ids[$v['device_unique_id']] = $v['business_id'];
        }

        //营业厅总数
        $business_hall_num = count($business_ids);
        //设备总数
        $device_num = count($device_list);

        //新增门店量
        //$new_business_hall_num = count($new_business_hall);
        $new_business_hall_num = count($this->get_new_business_num($new_device_business_hall));

        //新增设备量
        $new_device_num = count($new_device);

        //活跃设备
        $active_device = $this -> get_active_device($device_unique_ids);
        //活跃设备量
        $active_device_num = count($active_device);

        //所有设备活跃总天数
        $active_days_count = array_sum($active_device);
        //平均活跃天数
        if ($active_days_count < 1 || $active_device_num < 1) {
            $active_days_average = 0;
        } else {
            $active_days_average = round($active_days_count/$active_device_num, 1);
        }

        //体验设备
        $experience_device = $this->get_device_experience_time($device_unique_ids);
        //体验设备数
        $experience_device_num = count($experience_device);
        //所有设备体验总时长
        $experience_times = array_sum($experience_device);
        //平均体验时长
        if ($experience_device_num < 1 || $experience_times < 1) {
            $experience_time_average = '0秒';
        } else {
            $experience_time_average = round($experience_times/$experience_device_num);
            $experience_time_average = screen_helper::format_timestamp_text($experience_time_average);
        }

        $tmp_list = array(
                'business_hall_num'     => $business_hall_num, //营业厅总数
                'device_num'            => $device_num, //设备总量
                'new_business_hall_num' => $new_business_hall_num,  //新增厅店量
                'new_device_num'        => $new_device_num,    //新增设备量
                'active_device_num'     => $active_device_num, //活跃设备量
                'active_days_count'     => $active_days_count, //活跃总天数
                'active_days_average'   => $active_days_average, //平均活跃天数
                'experience_device_num' => $experience_device_num, //体验设备数
                'experience_times'      => $experience_times, //体验总时长
                'experience_time_average' => $experience_time_average, //平均体验时长
        );

        return $tmp_list;
    }

    /**
     * 获取新增厅店量
     */
    private function get_new_business_num($new_device_business_hall)
    {
        //获取搜索日期以前的厅店
        $filter = array(
                'status' => 1,
                'day <'  => $this->start_day
        );

        $business_ids = _model('screen_device')->getFields('business_id', $filter);
        $business_ids2 = array_keys($new_device_business_hall);

        return array_diff($business_ids2, $business_ids);

    }

    /**
     * 获取指定范围的设备
     */
    private function get_range_device($range_info)
    {

        $filter = $this->init_filter();
        $filter['status'] = 1;
        //按品牌
        if ($this -> search_type == 'phone_name') {
            $filter['phone_name'] = $range_info;
        //按型号
        } else if ($this -> search_type == 'phone_version'){
            $filter['device_nickname_id'] = $range_info['id'];
        } else {
            return array();
        }

        $device_list= _model('screen_device')->getList($filter, ' GROUP BY `device_unique_id` ');

        return $device_list;
    }

    /**
     * 获取设备活跃量
     */
    private function get_active_device($device_unique_ids)
    {
        if (!$device_unique_ids) {
            return array();
        }

        $device_unique_ids2 = array_keys($device_unique_ids);

        $filter = array(
                'day >=' => $this->start_day,
                'day <=' => $this->end_day,
                'is_online' => 1,
                'device_unique_id' => $device_unique_ids2
        );

        $where = $this->to_where_sql($filter);

        //为预防换厅的设备导致统计混乱， 故此按营业厅分组
        $sql            = " SELECT COUNT(*) AS `online_num`, `device_unique_id`, `business_id` FROM `screen_device_online_stat_day` {$where} GROUP BY  `device_unique_id`, `business_id` ";
        $online_list    = _model('screen_device_online_stat_day')->getAll($sql);

        $online_device = array();
        //去除换厅或重新安装的设备
        foreach ($online_list as $k => $v) {

            if (empty($device_unique_ids[$v['device_unique_id']])) {
                continue;
            }

            if ($device_unique_ids[$v['device_unique_id']] != $v['business_id']) {
                continue;
            }

            $online_device[$v['device_unique_id']]          = $v['online_num'];
        }

        return $online_device;
    }

    /**
     * 获取设备的最后活跃信息
     * @param unknown $device_unique_id
     * @param unknown $business_id
     */
    private function get_last_active_info($device_unique_id, $business_id)
    {
        return _model('screen_device_online_stat_day')->read(array('device_unique_id' => $device_unique_id, 'business_id' => $business_id), ' ORDER BY `id` DESC LIMIT 1 ');

    }


    /**
     * 获取设备体验时长
     */
    private function get_device_experience_time($device_unique_ids)
    {

        if (!$device_unique_ids) {
            return array();
        }

        $device_unique_ids2 = array_keys($device_unique_ids);

        $filter = array(
                'day >=' => (int)$this->start_day,
                'day <=' => (int)$this->end_day,
                'device_unique_id' => $device_unique_ids2
        );

        $filter = get_mongodb_filter($filter);
        //为预防换厅的设备导致统计混乱， 故此按营业厅分组
        $stat_list       = _mongo('screen', 'screen_device_stat_day')->aggregate(
            array(
                array('$match' => $filter),
                array('$group' => array(
                        '_id'               => array(
                                'device_unique_id'  => '$device_unique_id',
                                'business_id'       => '$business_id',
                        ),
                        'experience_times'  => array('$sum' => '$experience_time'),
                        'business_id'       => array('$first' => '$business_id'),
                        'device_unique_id'  => array('$first' => '$device_unique_id'),
                )
                )
            )
        );

        $experience_stat = array();
        foreach ($stat_list as $k => $v) {
            $v = (array)$v;

            if (empty($device_unique_ids[$v['device_unique_id']])) {
                continue;
            }
            if ($device_unique_ids[$v['device_unique_id']] != $v['business_id']) {
                continue;
            }

            $experience_stat[$v['device_unique_id']]          = $v['experience_times'];
        }

        return $experience_stat;
    }

    /**
     * 获取日期天数
     */
    private function get_date_days()
    {
        $days = 0;
        $date = $this->search_filter['start_date'];
        do{
            ++$days;
            $date = date('Y-m-d', strtotime($date) + 3600*24);
        }while($date <= $this->search_filter['end_date']);

        return $days;
    }

    /**
     * 营业厅数量
     */
    public function business_num()
    {
        $phone_name = tools_helper::Get('phone_name', '');
        $phone_version = tools_helper::Get('phone_version', '');
        $page = tools_helper::Get('page_no', 1);
        //1-总数 2-新增
        $type = tools_helper::Get('type', 1);

        $device_list = array();

        if (!in_array($type, array(1, 2))) {
            $type = 1;
        }

        $filter = $this->init_filter();

        if ($phone_name) {
            $filter['phone_name'] = $phone_name;
        }

        if ($phone_version) {
            $filter['phone_version'] = $phone_version;
        }


        $filter['status'] = 1;

        if ($type == 2) {
            //查询
            $new_filter = $filter;
            $new_filter['day >='] = $this->start_day;
            $new_filter['day <='] = $this->end_day;
            $business_ids = _model('screen_device')->getFields('business_id', $new_filter, ' GROUP BY `business_id` ');
            $new_filter2 = $filter;
            $new_filter2['day <'] = $this->end_day;
            $business_ids2 = _model('screen_device')->getFields('business_id', $new_filter, ' GROUP BY `business_id` ');

            $business_id = array_diff($business_ids, $business_ids2);
            if ($business_id) {
                $filter['business_id'] = $business_id;
            }
            $count = count($business_id);
        } else {
            $business_ids = _model('screen_device')->getFields('business_id', $filter, ' GROUP BY `business_id` ');
            $count = count($business_ids);
        }

        $look = tools_helper::Get('look', 0);
        if ($look) {
            p($filter);
            p($business_ids);
        }

        if ($count) {
            $pager = new Pager($this->per_page);
            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }

            Response::assign('count', $count);

            $where = $this->to_where_sql($filter);
            $sql = " SELECT COUNT(*) AS `device_num`, `business_id` , `province_id`, `city_id` FROM `screen_device` {$where} GROUP BY `business_id` {$pager->getLimit($page)}";
            $device_list = _model('screen_device')->getAll($sql);
        }

        foreach ($device_list as $k => $v) {
            $filter['business_id'] = $v['business_id'];
            $device_unique_ids = _model('screen_device')->getFields('device_unique_id', $filter);
            $new_device_unique_ids = array();
            foreach ($device_unique_ids as $vv) {
                $new_device_unique_ids[$vv] = $v['business_id'];
            }

            $active_device = $this->get_active_device($new_device_unique_ids);
            $device_list[$k]['active_num'] = count($active_device);
        }

        Response::assign('device_list', $device_list);
        Response::assign('type', $type);
        Response::display('admin/device_use/business_num.html');
    }

    /**
     * 设备数量详情页
     */
    public function device_num()
    {
        $phone_name = tools_helper::Get('phone_name', '');
        $phone_version = tools_helper::Get('phone_version', '');
        $page = tools_helper::Get('page_no', 1);
        //1-总数 2-新增
        $type = tools_helper::Get('type', 1);

        $device_list = array();

        if (!in_array($type, array(1, 2))) {
            $type = 1;
        }

        //初始化搜索条件
        $filter = $this->init_filter();

        if ($phone_name) {
            $filter['phone_name'] = $phone_name;
        }

        if ($phone_version) {
            $filter['phone_version'] = $phone_version;
        }


        $filter['status'] = 1;

        //新增设备
        if ($type == 2) {
            $filter['day >='] = $this->start_day;
            $filter['day <='] = $this->end_day;
        }

        //符合条件的设备
        $device_list = get_data_list('screen_device', $filter, ' ORDER BY `id` DESC ', $page, $this->per_page);

        //当期那搜索条件的天数
        $days_count  =$this -> get_date_days();
        //体验总时长
        $count_experience_time = 0;

        foreach ($device_list as $k => $v) {

            //活跃天数
            $active_info = $this -> get_active_device(array($v['device_unique_id'] => $v['business_id']));
            $online_days= empty($active_info[$v['device_unique_id']]) ? 0 : $active_info[$v['device_unique_id']];

            //离线天数 搜索时间范围 - 活跃天数 = 离线天数
            $offonline_days = $days_count - $online_days;

            //最后活跃时间
            $last_active = $this -> get_last_active_info($v['device_unique_id'], $v['business_id']);
            if (!$last_active) {
                $last_active_time = '';
                $active_status = 0;
            } else {
                $last_active_time = $last_active['update_time'];
                if ($last_active['day'] == date('Ymd')) {
                    $active_status = 1;
                } else {
                    $active_status = 0;
                }
            }

            $online_status = screen_helper::get_online_status($v['device_unique_id']);

            //查询体验时长
            $experience_time_info = $this->get_device_experience_time(array($v['device_unique_id'] => $v['business_id']));
            $experience_time = empty($experience_time_info[$v['device_unique_id']]) ? 0 : $experience_time_info[$v['device_unique_id']];

            //活跃天数
            $device_list[$k]['online_days'] = $online_days;
            //离线天数
            $device_list[$k]['offonline_days'] = $offonline_days;
            //最后活跃天数
            $device_list[$k]['last_active_time'] = $last_active_time;
            //是否活跃
            $device_list[$k]['active_status'] = $active_status;
            //是否在线
            $device_list[$k]['is_online'] = $online_status;
            //体验时长
            $device_list[$k]['experience_time'] = $experience_time;
            $count_experience_time += $experience_time;
        }

        Response::assign('device_list', $device_list);
        Response::assign('type', $type);
        Response::assign('count_experience_time', $count_experience_time);
        Response::display('admin/device_use/device_num.html');
    }

//     /**
//      * 设备体验详情
//      */
//     public function device_experience_time()
//     {
//         $device_unique_id = tools_helper::Get('device_unique_id', '');
//         $page = tools_helper::Get('page_no', 1);

//         //获取设备详情
//         $device_info = screen_device_helper::get_device_info_by_device($device_unique_id);


//         $filter = array(
//                 'day >=' => (int)$this->start_day,
//                 'day <=' => (int)$this->end_day,
//                 'device_unique_id' => $device_unique_id,
//                 'business_id'      => empty($device_info['business_id']) ? '' : $device_info['business_id'],
//                 'type'   => 2
//         );

//         $filter = get_mongodb_filter($filter);
//         //为预防换厅的设备导致统计混乱， 故此按营业厅分组
//         _mongo('')
//         $stat_list       = _mongo('screen', 'screen_action_record')->find($filter, );


//     }


    /**
     * 数组条件转换where语句
     * @param unknown $filter
     * @return string
     */
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

    /**
     * 导出
     */
    public function is_export($list, $search_type='')
    {
        if (!$list) {
            return '暂无数据';
        }

        $info = array();
        $i    = 0;

        $stat_list  = array();
        foreach ($list as $k => $v) {
            //处理统计数据
            $stat_info = $this->handle_stat($v);

            //拼接机型信息
            $stat_info['phone_name']     = $v['phone_name'];
            $stat_info['name_nickname']  = $v['name_nickname'] ? $v['name_nickname'] : $v['phone_name'];
            $stat_info['phone_version']  = $v['phone_version'];
            $stat_info['version_nickname']  = $v['version_nickname'] ? $v['version_nickname'] : $v['phone_version'];
            $stat_list[] = $stat_info;
        }

         foreach ($stat_list as $kk => $vv) {
             $info[$kk]['name_nickname']         = $vv['name_nickname'];
             $info[$kk]['version_nickname']      = $vv['version_nickname'];
             $info[$kk]['business_hall_num']     = $vv['business_hall_num'];
             $info[$kk]['device_num']            = $vv['device_num'];
             $info[$kk]['new_business_hall_num'] = $vv['new_business_hall_num'];
             $info[$kk]['new_device_num']        = $vv['new_device_num'];
             $info[$kk]['active_device_num']     = $vv['active_device_num'];
             $info[$kk]['active_days_average']   = $vv['active_days_average'];
             $info[$kk]['experience_times']      = $vv['experience_times'];
             $info[$kk]['experience_time_average'] = $vv['experience_time_average'];

         }

        if ($search_type == 'phone_version') {
            $head = array('品牌', '型号', '营业厅总数', '设备量', '新增营业厅', '新增设备数', '活跃数', '平均活跃数', '体验时长', '平均体验时长');
        } else {
            $head = array('所属省', '所属市', '所属区县', '营业厅名称', '手机品牌', '手机型号', '标识ID', 'IMEI', '体验时长', '开始时间', '结束时间');
        }

        $params['filename'] = '亮屏设备';
        $params['data']     = $info;

        //$params['head']     = array('所属省', '所属市', '所属区县', '营业厅名称', '手机品牌', '手机型号', '标识ID', 'IMEI', '体验时长', '开始时间', '结束时间');
        $params['head']     = $head;

        Csv::getCvsObj($params)->export();
    }
}
// class Action
// {
//     private $per_page = 30;
//     private $member_id  = 0;
//     private $member_res_name = '';
//     private $member_res_id   = 0;
//     private $member_info     = array();
//     private $ranks           = 0;
//     public $start_time  = '';
//     public $end_time    = '';

//     public function __construct()
//     {

//         $this->member_id   = member_helper::get_member_id();

//         $this->member_info = member_helper::get_member_info($this->member_id);

//         if ($this->member_info) {
//             $this->member_res_name = $this->member_info['res_name'];
//             $this->member_res_id   = $this->member_info['res_id'];
//             $this->ranks           = $this->member_info['ranks'];
//         }

//         Response::assign('curr_member_ranks', $this->ranks);
//         Response::assign('member_res_name', $this->member_res_name);
//         Response::assign('member_res_id', $this->member_res_id);
//     }

//     public function get_list()
//     {
//         //搜索条件
//         $search_filter  = Request::Get('search_filter', array());
//         $page_no        = tools_helper::get('page_no', 1);
//         $search_type    = tools_helper::get('search_type', 'nickname');
//         $phone_name     = tools_helper::get('phone_name', '');
//         $phone_version  = tools_helper::get('phone_version', '');

//         $order_dir     = tools_helper::get('order_dir', 'desc');
//         $order_field   = tools_helper::get('order_field', 'device_num');

//         $is_export     = tools_helper::get('is_export', 0);

//         $filter = array();
//         //营业厅权限跳过标题搜索
// //         if ($this->member_res_name != 'business_hall' ) {
// //             $business_hall_list = _model('business_hall')->getList(array('title' => $hall_title));
// //             $business_hall_ids = array();
// //             foreach ($business_hall_list as $k => $v) {
// //                 //非集团管理员并且搜索的营业厅不在本身权限之内则跳过
// //                 if ($this->member_res_name != 'group' && $v["{$this->member_res_name}_id"] != $this->member_res_id) {
// //                     continue;
// //                 }
// //                 $business_hall_ids[] = $v['id'];
// //             }

// //             if (!$business_hall_ids) {
// //                 $business_hall_ids = 0;
// //             }
// //             $filter['business_id'] = $business_hall_ids;
// //         }

//         //搜索判断
//         if (!empty($search_filter['province_id'])) {
//             $filter['province_id'] = $search_filter['province_id'];

//             $province = array('province_id' => $search_filter['province_id']);
//             Response::assign('where1' , $province);
//         }

//         if (!empty($search_filter['city_id'])) {
//             $filter['city_id'] = $search_filter['city_id'];

//             $city = array('city_id' => $search_filter['city_id']);
//             Response::assign('where2' , $city);
//         }

//         if (!empty($search_filter['area_id'])) {
//             $filter['area_id'] = $search_filter['area_id'];
//         }

//         if ($phone_name) {
//             $filter['phone_name']        = $phone_name;
//             $search_filter['phone_name'] = $phone_name;
//         }

//         if ($phone_version) {
//             $info = _uri('screen_device', array('phone_version_nickname'=>$phone_version));
//             if ($info) {
//                 $filter['phone_version_nickname'] = $phone_version;
//             } else {
//                 $filter['phone_version'] = $phone_version;
//             }
//             $search_filter['phone_version'] = $phone_version;
//         }

//         $filter['status'] = 1;

// //         an_dump($list);
// //         exit;
// //         $use_list = get_data_list('screen_device_name_nickname', $filter, ' ORDER BY `id` DESC ', $page_no, $this->per_page);

//          $sum = 0;

// //         foreach ($use_list as $k => $v) {
// //             //count(_model('screen_device')->getFields('phone_name', $new_filter));
// //             $count = count(_model('screen_device')->getList(array('phone_name'=> $v['phone_name'])));
// //             $keys[$k] = $use_list[$k]['num'] = $count;

// //             //$sum += $count;

// //         }

//         //array_multisort ($keys, SORT_DESC, $use_list);
//         if ($search_type == 'version') {
//             $order  = ' GROUP BY `phone_name` ';
//         } else {
//             $order  = ' GROUP BY `phone_name`, `phone_version` ';
//         }

//         //$count    = _model('screen_device')->getTotal($filter, $order);


//         $keys     = array();
//         $use_list = array();

//         $start_time = '';
//         $end_time   = '';
//         $day_count  = 0;
//         $new_filter = array();


//         if (!empty($search_filter['start_date'])) {
//             $active_filter['add_time >=']       = $search_filter['start_date']." 00:00:00 ";
//             $last_filter['add_time <']          = $search_filter['start_date']." 00:00:00 ";
//             $new_business_filter['add_time >='] = $search_filter['start_date']." 00:00:00 ";
//             //$new_filter['add_time >=']          = $search_filter['start_date']." 00:00:00 ";
//             $this->start_time = $search_filter['start_date']." 00:00:00 ";
//         }

//         if (!empty($search_filter['end_date'])) {
//             $new_filter['add_time <=']          = $search_filter['end_date']. " 23:59:59 ";
//             $active_filter['add_time <=']       = $search_filter['end_date']. " 23:59:59 ";
//             $new_business_filter['add_time <='] = $search_filter['end_date']. " 23:59:59 ";
//             $this->end_time  = $search_filter['end_date']. " 23:59:59 ";
//         }

//         //$active_filter = $new_filter;
//         if (empty($search_filter['start_date']) || empty($search_filter['end_date'])) {
//             $last_filter['add_time <']          = date("Y-m-d")." 00:00:00 ";
//             $new_business_filter['add_time >='] = date("Y-m-d"). " 00:00:00 ";
//             $new_business_filter['add_time <='] = date("Y-m-d"). " 23:59:59 ";
//             $active_filter['add_time >='] = date("Y-m-d"). " 00:00:00 ";
//             $active_filter['add_time <='] = date("Y-m-d"). " 23:59:59 ";
//             $this->end_time   = '';
//             $this->start_time = '';

//         }

//         $e_filter = $active_filter;

// //         if ($start_time && $end_time) {
// //             $day_count = $this->diff_betweentwo_days(strtotime($start_time), strtotime($end_time));
// //         } else {
// //             $day_count = 1;
// //         }

//         $active_filter['status'] = $new_business_filter['status'] = $last_filter['status'] = $new_filter['status'] = 1;

//         $use_list = _model('screen_device')->getList($filter, $order);

//         $time_count = array();
//         if ($search_type == 'version') {
//             foreach ($use_list as $k => $v) {
//                 $count = _model('screen_device')->getTotal(array('phone_name'=>$v['phone_name'], 'status'=>1));

//                 $keys[$k] = $use_list[$k]['num'] = $count;
//                 $use_list[$k]['name_nickname']   = $v['phone_name_nickname'];
//                 $sum += $count;
//             }

//             array_multisort ($keys, SORT_DESC, $use_list);
//         } else {
//             foreach ($use_list as $k => $v) {
//                 $active_filter['phone_name']    = $new_business_filter['phone_name']    = $last_filter['phone_name']    = $new_filter['phone_name']    = $v['phone_name'];
//                 $active_filter['phone_version'] = $new_business_filter['phone_version'] = $last_filter['phone_version'] = $new_filter['phone_version'] = $v['phone_version'];

//                 $i = 0;

//                 // 设备数
//                 $count        = count(_model('screen_device')->getFields('device_unique_id', $new_filter, ' GROUP BY device_unique_id '));

//                 // 门店数
//                 $business_num = count(_model('screen_device')->getFields('device_unique_id', $new_filter, ' GROUP BY `business_id` '));

//                 // 活跃量
//                 $device_unique_ids = _model('screen_device')->getFields('device_unique_id', $active_filter, ' GROUP BY `device_unique_id` ');

//                 // 设备活跃总天数
//                 //p($new_filter);
//                 $average_active_count = $this->get_average_active($device_unique_ids, $active_filter, $day_count);
//                 if (!$average_active_count) {
//                     $average_active = 0;
//                     $average_time   = 0;
//                 } else {
//                     $average_active = $average_active_count['average_active_count'];
//                     $average_time   = $average_active_count['average_time_count'];
//                 }
//                 //$avergae_active_count = 0;
//                 //p($avergae_active_count);
//                 //新增设备数
//                 $new_device_num   = count(_model('screen_device')->getFields('device_unique_id', $new_business_filter, ' GROUP BY device_unique_id '));

//                 // 新增营业厅
//                 $business_ids      = _model('screen_device')->getFields('business_id', $new_business_filter, ' GROUP BY business_id ');
//                 $last_business_ids = _model('screen_device')->getFields('business_id', $last_filter, ' GROUP BY business_id ');

//                 //p($business_ids, $last_business_ids, $new_business_filter, $last_filter);
//                 $new_business_num  = count(array_diff($business_ids, $last_business_ids));

//                 //体验时长
//                 $e_filter['device_unique_id'] = $device_unique_ids;
//                 //p($e_filter);
//                 $e_time = _mongo('screen', 'screen_action_record')->find(get_mongodb_filter($e_filter), array('projection'=>['experience_time'=>1]));
//                 foreach ($e_time as $vv) {

//                     if (!isset($vv['experience_time'])) {
//                         continue;
//                     }

//                     $time_count[$i] = $vv['experience_time'];
//                     $i++;
//                 }

//                 //p($time_count);
//                 $use_list[$k]['new_device']      = $new_device_num;
//                 $use_list[$k]['new_business']    = $new_business_num;
//                 $use_list[$k]['device_num']      = $count;
//                 $use_list[$k]['business_num']    = $business_num;
//                 //$use_list[$k]['active_num']      = $active_num;
//                 $use_list[$k]['active_num']      = count($device_unique_ids);
//                 $use_list[$k]['experience_time'] = array_sum($time_count);
//                 $use_list[$k]['average_active']  = $average_active;
//                 $use_list[$k]['average_time']    = $average_time;

//                 $use_list[$k]['num'] = $count;

//                 $sum += $count;

//                 if ($order_field == 'experience_times') {
//                     //$keys[$k] = $experience_time;
//                     $keys[$k]  = array_sum($time_count);
//                 } elseif ($order_field == 'device_num') {
//                     $keys[$k] = $count;
//                 } elseif ($order_field == 'active_num') {
//                     $keys[$k] = $active_num;
//                 } elseif ($order_field == 'business_num') {
//                     $keys[$k] = $business_num;
//                 }

//                 $time_count = array();
//             }

//         }

//         //排序
//         $experience_times_count = array_sum($keys);

//         if ($keys && $use_list) {

//             if ($order_dir == 'desc') {
//                 array_multisort ($keys, SORT_DESC, $use_list);
//             } else {
//                 array_multisort ($keys, SORT_ASC, $use_list);
//             }
//         }

//         if ($is_export == 1) {
//             $this->is_export($use_list, 'nickname');
//         }

//         Response::assign('use_list', $use_list);
//         Response::assign('search_filter', $search_filter);
//         Response::assign('use_count', $sum);
//         Response::assign('search_type', $search_type);
//         Response::assign('order_dir', $order_dir);
//         Response::assign('order_field', $order_field);
//         Response::assign('phone_name', $phone_name);
//         Response::assign('phone_version', $phone_version);

//         Response::display('admin/device_use/device_use.html');
//     }

//     public function get_average_active($unique_ids, $active_filter, $day_count)
//     {

//         if (!$unique_ids) {
//             return array('average_active_count'=>0, 'average_time_count'=>0);
//         }

//         $average_count = array();

//         $i             = 0;

//         if (!$this->start_time || !$this->end_time) {
//             foreach ($unique_ids as $v) {
//                 $add_time = _uri('screen_device', array('device_unique_id'=>$v, 'status'=>1), 'add_time');

//                 $filter = array(
//                     'add_time >=' => $add_time,
//                     'add_time <=' => date("Y-m-d H:i:s"),
//                     'device_unique_id' => $v
//                 );

//                 // 一个设备活跃总天数
//                $active = count(_model('screen_device_online_stat_day')->getFields('device_unique_id', $filter, ' GROUP BY `day`, `device_unique_id` '));

//                $e_time = _mongo('screen', 'screen_action_record')->find(get_mongodb_filter($filter), array('projection'=>['experience_time'=>1]));
//                foreach ($e_time as $vv) {

//                    if (!isset($vv['experience_time'])) {
//                        continue;
//                    }

//                    $time_count[$i] = $vv['experience_time'];
//                    $i++;
//                }

//                $day_count = $this->diff_betweentwo_days(strtotime($add_time), strtotime(date("Y-m-d H:i:s")));

//                if (!$day_count) {
//                    $day_count = 1;
//                }

//                $average_active = round($active/$day_count, 3);
//                $average_time   = round(array_sum($time_count)/$day_count, 3);

//                //if ($active) {
//                $average_active_count[] = $average_active;
//                $average_time_count[]   = $average_time;
//                //}
//             }
//         } else {
//             $day_count = $this->diff_betweentwo_days(strtotime($this->start_time), strtotime($this->end_time));

//             foreach ($unique_ids as $v) {
//                 $filter = array(
//                     'add_time >=' => $this->start_time,
//                     'add_time <=' => $this->end_time,
//                     'device_unique_id' => $v
//                 );

//                 $active = count(_model('screen_device_online_stat_day')->getFields('device_unique_id', $filter, ' GROUP BY `day`, `device_unique_id` '));


//                 $e_time = _mongo('screen', 'screen_action_record')->find(get_mongodb_filter($filter), array('projection'=>['experience_time'=>1]));
//                 foreach ($e_time as $vv) {

//                     if (!isset($vv['experience_time'])) {
//                         continue;
//                     }

//                     $time_count[$i] = $vv['experience_time'];
//                     $i++;
//                 }

//                 $average_active = round($active/$day_count, 3);
//                 $average_time   = round(array_sum($time_count)/$day_count, 3);

//                 //if ($active) {
//                 $average_active_count[] = $average_active;
//                 $average_time_count[]   = $average_time;
//                 //}
//             }
//         }

//         return array('average_active_count'=>array_sum($average_active_count), 'average_time_count'=>array_sum($average_time_count));
//     }

//     public function new_business_device()
//     {
//         $phone_name        = tools_helper::get('phone_name', '');
//         $search_filter     = tools_helper::get('search_filter', array());
//         $hall_title        = tools_helper::get('hall_title', '');
//         $device_unique_id  = tools_helper::get('device_unique_id', '');
//         $phone_version     = tools_helper::get('phone_version', '');
//         $page_no           = tools_helper::get('page_no', 1);
//         $is_export         = tools_helper::get('is_export', 0);
//         $type              = tools_helper::get('type', 1);
//         $order_dir     = tools_helper::get('order_dir', 'desc');
//         $order_field   = tools_helper::get('order_field', 'experience_times');

//         if (!empty($search_filter['province_id'])) {
//             $filter['province_id'] = $search_filter['province_id'];

//             $province = array('province_id' => $search_filter['province_id']);
//             Response::assign('where1' , $province);
//         }

//         if (!empty($search_filter['city_id'])) {
//             $filter['city_id'] = $search_filter['city_id'];

//             $city = array('city_id' => $search_filter['city_id']);
//             Response::assign('where2' , $city);
//         }

//         if (!empty($search_filter['area_id'])) {
//             $filter['area_id'] = $search_filter['area_id'];
//         }

//         if ($hall_title) {
//             $business_id = _uri('business_hall', array('title LIKE' => '%'.$hall_title.'%'), 'id');
//             if ($business_id) {
//                 $filter['business_id'] = $business_id;
//             }
//         }

//         if ($phone_version) {

//             $device_info = _uri('screen_device', array('phone_version LIKE '=>'%'.$phone_version.'%'));

//             if ($device_info) {
//                 $filter['phone_version LIKE ']  = $phone_version;
//             } else {
//                 $filter['phone_version_nickname LIKE '] = $phone_version;
//             }
//         }
//         $filter['phone_name'] = $phone_name;

//         $new_num_filter = $filter;

//         if (!empty($search_filter['start_date'])) {
//             $filter['add_time >=']              = $search_filter['start_date']." 00:00:00 ";
//             $active_filter['add_time >=']       = $search_filter['start_date']." 00:00:00 ";
//             $last_filter['add_time <']          = $search_filter['start_date']." 00:00:00 ";
//             $new_business_filter['add_time >='] = $search_filter['start_date']." 00:00:00 ";
//             // 新增设备数条件
//             $new_num_filter['add_time >=']      = $search_filter['start_date']." 00:00:00 ";
//             $e_filter['day >=']                 = $search_filter['start_date']." 00:00:00 ";

//             $start_time = $search_filter['start_date'];
//         }

//         if (!empty($search_filter['end_date'])) {
//             $filter['add_time <=']              = $search_filter['end_date']. " 23:59:59 ";
//             $active_filter['add_time <=']       = $search_filter['end_date']. " 23:59:59 ";
//             $new_business_filter['add_time <='] = $search_filter['end_date']. " 23:59:59 ";
//             $new_num_filter['add_time <=']      = $search_filter['end_date']. " 23:59:59 ";
//             $e_filter['day >=']                 = $search_filter['end_date']. " 23:59:59 ";
//             $end_time  = $search_filter['end_date'];
//         }

//         //$active_filter = $new_filter;
//         if (empty($search_filter['start_date']) || empty($search_filter['end_date'])) {
//             $last_filter['add_time <'] = date("Y-m-d")." 00:00:00 ";
//             $new_business_filter['add_time >='] = date("Y-m-d"). " 00:00:00 ";
//             $new_business_filter['add_time <='] = date("Y-m-d"). " 23:59:59 ";

//             $new_num_filter['add_time >=']      = date("Y-m-d"). " 00:00:00 ";
//             $new_num_filter['add_time <=']      = date("Y-m-d"). " 23:59:59 ";
//             $e_filter['day >=']                 = date("Y-m-d"). " 00:00:00 ";
//             $e_filter['day <=']                 = date("Y-m-d"). " 23:59:59 ";

//             $filter['add_time >='] = date("Y-m-d"). " 00:00:00 ";
//             $filter['add_time <='] = date("Y-m-d"). " 23:59:59 ";
//         }

//         if ($device_unique_id) {
//             $filter['device_unique_id'] = $device_unique_id;
//         }

//         $new_business_filter['status']        = $last_filter['status']        = $filter['status']            = 1;
//         $new_business_filter['phone_name']    = $last_filter['phone_name']    = $new_filter['phone_name']    = $phone_name;
//         $new_business_filter['phone_version'] = $last_filter['phone_version'] = $new_filter['phone_version'] = $phone_version;


//         //导出
//         if ($is_export) {
//             $export_list  = _model('screen_device')->getList($filter);
//             $this->is_export($export_list);
//         }

//         // 新增营业厅
//         $business_ids      = _model('screen_device')->getFields('business_id', $new_business_filter, ' GROUP BY business_id ');
//         $last_business_ids = _model('screen_device')->getFields('business_id', $last_filter, ' GROUP BY business_id ');

//         $new_business_ids  = array_diff($business_ids, $last_business_ids);

//         $list  = array();
//         $i     = 0;

//         if ($new_business_ids) {
//             $filter['business_id'] = $new_business_ids;
//         }

//         if ($type == 1) {
//             $list = _model('screen_device')->getList($filter, ' GROUP BY `business_id` ORDER BY `id` DESC ');
//         } else {
//             $list = _model('screen_device')->getList($filter, ' ORDER BY `id` DESC ');
//         }

//         if ($type == 1) {
//             foreach ($list as $k => $v) {
//                 $e_time = array();
//                 $active_filter['business_id'] = $new_num_filter['business_id']       = $v['business_id'];

//                 //p($new_num_filter);
//                 $new_device_ids                    = _model('screen_device')->getFields('device_unique_id', $new_num_filter, ' GROUP BY device_unique_id ');

//                 $e_filter['device_unique_id']      = $new_device_ids;
//                 $e_filter['business_id']           = $v['business_id'];
//                 if (!$active_filter) {
//                     $active_filter['1'] = 1;
//                 } else {
//                     $active_filter['device_unique_id'] = $new_device_ids;
//                 }

//                 $active_num    = count(_model('screen_device_online_stat_day')->getFields('device_unique_id',$active_filter, ' GROUP BY `device_unique_id`, `day` '));

//                 $e_count = _mongo('screen', 'screen_action_record')->aggregate(array(
//                     array('$match' => get_mongodb_filter($e_filter)),
//                     array('$group' => array(
//                         '_id'               => array(
//                             'device_unique_id'       => '$device_unique_id',
//                         ),
//                         'count'    => array('$sum'=>'$experience_time'),
//                     ),
//                     )));

//                 foreach ($e_count as $v) {
//                     $e_time[]     = $v['count'];

//                     $count_time[] = $v['count'];
//                 }

//                 $list[$k]['new_device_num']        = count($new_device_ids);
//                 $list[$k]['active_num']            = $active_num;
//                 $list[$k]['e_time']                = array_sum($e_time);

//                 if ($order_field == 'experience_times') {
//                     $keys[$k] = array_sum($e_time);
//                 }

//             }
//         } else {
//             foreach ($list as $k => $v) {

//                 $active_filter['device_unique_id'] = $v['device_unique_id'];

//                 // 体验时长
//                 $exper_time = _mongo('screen', 'screen_action_record')->find(get_mongodb_filter($active_filter), array('projection'=>['experience_time'=>1]));

//                 $t_count = array();

//                 foreach ($exper_time as $vv) {

//                     $t_count[$i] = $vv['experience_time'];
//                     $i++;
//                 }

//                 $list[$k]['experience_time'] = array_sum($t_count);

//                 // 是否在线过
//                 $online_info = _model('screen_device_online_stat_day')->read(array('day'=>date("Ymd"), 'device_unique_id'=>$v['device_unique_id']));
//                 //$online_info = screen_helper::get_online_status($v['device_unique_id']);

//                 //最后活跃时间
//                 $last_active = _model('screen_device_online_stat_day')->read(array('device_unique_id'=>$v['device_unique_id']), ' ORDER BY `id` DESC ');

//                 if ($last_active) {
//                     $update_time             = $last_active['update_time'];
//                     $list[$k]['last_active'] = $last_active['update_time'];
//                 } else {
//                     $update_time             = '';
//                     $list[$k]['last_active'] = '';
//                 }
//                 // 活跃 不活跃天数
//                 $res = $this->get_time_diff($search_filter['start_date'], $search_filter['end_date'], $v['add_time'], $active_filter);


//                 $list[$k]['unonline_day'] = $res['unactive_count'];
//                 $list[$k]['active_day']   = $res['active_count'];

//                 // 是否活跃
//                 $list[$k]['active_status']   = $online_info ? 1 : 0;

//                 if ($order_field == 'experience_times') {
//                     $keys[$k] = array_sum($t_count);
//                 }
//             }
//         }

//         Response::assign('list', $list);
//         Response::assign('type', $type);
//         Response::assign('phone_name', $phone_name);
//         Response::assign('search_filter', $search_filter);
//         Response::assign('device_unique_id', $device_unique_id);
//         Response::assign('hall_title', $hall_title);
//         Response::assign('phone_version', $phone_version);

//         if ($type == 1) {
//             Response::display('admin/device_use/new_business_list.html');
//         } else {
//             Response::display('admin/device_use/new_device_list.html');
//         }
//     }

//     /**
//      时间总天数
//      */
//     private function diff_betweentwo_days ($s_time, $e_time)
//     {
//         if ($s_time < $e_time) {
//             $tmp = $e_time;
//             $e_time = $s_time;
//             $s_time = $tmp;
//         }

//         $test = $s_time - $e_time;

//         //return ($s_time - $e_time) / 86400;
//         return floor(($s_time-$e_time)/86400);
//         //return date("d", $s_time - $e_time);
//     }

//     /**
//      * 计算活跃 不活跃天数
//      * @param diff_start_time 搜索开始时间
//      * @param diff_end_time   搜索结束时间
//      * @param add_time        设备添加时间
//      * @param filter          搜索活跃天数条件
//      */
//     private function get_time_diff($diff_start_time, $diff_end_time, $add_time, $filter)
//     {
// //         if (!$diff_start_time || !$diff_end_time || !$add_time) {
// //             return array('active_count' => 0, 'unactive_count'=>0);
// //         }

//         if (!$diff_end_time) {
//             $diff_end_time = date("Ymd");
//         }

//         if (!$diff_start_time) {
//             $diff_start_time = date("Ymd", strtotime($add_time));
//         }

//         $start_time = strtotime($diff_start_time);
//         $end_time   = strtotime($diff_end_time);
//         $time       = strtotime(date("Ymd", strtotime($add_time)));
//         $day        = strtotime(date("Ymd"));
//         $time_count = 0;

//         // 添加时间在 搜索时间区间内
//         if ($time >= $start_time && $time <= $end_time) {

//             $active_filter['day >=']    = date("Ymd", strtotime($add_time));

//             if ($end_time > $day) {
//                 $time_count              = $this->diff_betweentwo_days($time, $day);

//                 $active_filter['day <='] = date("Ymd");
//             } else {
//                 $time_count              = $this->diff_betweentwo_days($time, $end_time);

//                 $active_filter['day <='] = date("Ymd", strtotime($diff_end_time));
//             }
//         }

//         // 添加时间 小于 搜索时间区间
//         if ($time < $start_time && $time < $end_time) {
//             $time_count                   = $this->diff_betweentwo_days($start_time, $end_time);

//             $active_filter['day >=']      = date("Ymd", strtotime($diff_start_time));

//             if ($end_time > $day) {
//                 $active_filter['day <=']  = date("Ymd");
//             } else {

//                 $active_filter['day <=']  = date("Ymd", strtotime($diff_end_time));
//             }
//         }

//         // 添加时间 大于 搜索时间区间
//         if ($time > $start_time && $time > $end_time) {

//             return array('active_count' => 0, 'unactive_count'=>0);
//         }

//         //$active_filter['province_id']      = $filter['province_id'];
//         $active_filter['device_unique_id'] = $filter['device_unique_id'];

//         // 活跃数
//         $active_list       = _model('screen_device_online_stat_day')->getList($active_filter, ' GROUP BY `day` ');

//         // 不活跃数
//         $unactive_count     = $time_count-count($active_list);

//         if ($unactive_count <= 0) {
//             $unactive_count = 0;
//         }

//         return array('active_count' => count($active_list), 'unactive_count'=>$unactive_count);
//     }
//     /**
//      * 导出
//      */
//     public function is_export($list, $search_type='')
//     {
//         if (!$list) {
//             return '暂无数据';
//         }

//         $info = array();
//         $i    = 0;

//         if ($search_type == 'nickname') {
//             foreach ($list as $k=>$v) {
//                 $info[$k]['phone_name']      = $v['phone_name'];
//                 $info[$k]['phone_version']   = $v['phone_version_nickname'] ? $v['phone_version_nickname'] : $v['phone_version'];
//                 $info[$k]['business_num']    = $v['business_num'];
//                 $info[$k]['device_num']      = $v['device_num'];
//                 $info[$k]['new_business']    = $v['new_business'];
//                 $info[$k]['new_device']      = $v['new_device'];
//                 $info[$k]['active_num']      = $v['active_num'];
//                 $info[$k]['average_active']  = $v['average_active'];
//                 $info[$k]['experience_time'] = screen_helper::format_timestamp_text($v['experience_time']);
//                 $info[$k]['average_time']   = screen_helper::format_timestamp_text($v['average_time']);
//             }
//         } else {
//             foreach ($list as $k => $v) {
//                 $action_list = _model('screen_action_record')->getList(array('device_unique_id'=>$v['device_unique_id'], 'type'=>2));

//                 foreach ($action_list as $kk => $vv) {
//                     $info[$i]['proinvce_id']      = business_hall_helper::get_info_name('province', $v['province_id'],  'name');
//                     $info[$i]['city_id']          = business_hall_helper::get_info_name('city', $v['city_id'], 'name');
//                     $info[$i]['area_id']          = business_hall_helper::get_info_name('area', $v['area_id'], 'name');
//                     $info[$i]['business_hall_id'] = business_hall_helper::get_info_name('business_hall', $v['business_id'], 'title');
//                     $info[$i]['phone_name']       = $v['phone_name_nickname'] ? $v['phone_name_nickname'] : $v['phone_name'];
//                     $info[$i]['phone_version']    = $v['phone_version_nickname']? $v['phone_version_nickname'] : $v['phone_version'];
//                     $info[$i]['device_unique_id'] = $v['device_unique_id'];
//                     $info[$i]['imei']             = $v['imei'] ? $v['imei'] : '手机无imei';

//                     //$experience_time              = array_sum(_model('screen_action_record')->getFields('experience_time', array('device_unique_id'=>$v['device_unique_id'])));
//                     //$experience_time              = array_sum(_model('screen_action_record')->getFields('experience_time', array('device_unique_id'=>$v['device_unique_id'], 'business_id'=>$v['business_id'])));
//                     $experience_time  =  _uri('screen_action_record', $vv['id'], 'experience_time');
//                     $info[$i]['experience_time']  = screen_helper::format_timestamp_text($experience_time);
//                     $info[$i]['add_time']         = $vv['add_time'];
//                     $info[$i]['update_time']      = $vv['update_time'];

//                     $i++;
//                 }
//             }
//         }

//          //p($info);exit();


//         if ($search_type == 'nickname') {
//             $head = array('品牌', '型号', '营业厅总数', '设备量', '新增营业厅', '新增设备数', '活跃数', '平均活跃数', '体验时长', '平均体验时长');
//         } else {
//             $head = array('所属省', '所属市', '所属区县', '营业厅名称', '手机品牌', '手机型号', '标识ID', 'IMEI', '体验时长', '开始时间', '结束时间');
//         }

//         $params['filename'] = '亮屏设备';
//         $params['data']     = $info;

//         //$params['head']     = array('所属省', '所属市', '所属区县', '营业厅名称', '手机品牌', '手机型号', '标识ID', 'IMEI', '体验时长', '开始时间', '结束时间');
//         $params['head']     = $head;

//         Csv::getCvsObj($params)->export();
//     }
// }