<?php
/**
  * alltosun.com 亮屏app体验统计 experience_stat.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年5月9日 下午2:14:59 $
  * $Id$
  */
class Action
{
    /**
     * screen统计首页
     * @param string $action
     * @param array $params
     */

    private $per_page = 20;
    public $member_id = 0;
    public $member_res_id = 0;
    public $member_res_name = '';
    public $filter          = array();
    public $interval   = 60;
    public $secret     = '';
    public $member_info = array();

    public function __construct()
    {

        $this->member_id   = member_helper::get_member_id();
        $this->member_info = member_helper::get_member_info($this->member_id);

        if (!$this->member_info) {
            return '您无权访问此页面';
        }

        Response::assign('member_info', $this->member_info);
    }


    /**
     * 设备天数据
     */
    public function stat_day()
    {
        $search_filter  = tools_helper::get('search_filter', array());
        $search_type    = tools_helper::get('search_type', 'business');
        $page           = tools_helper::get('page_no', 1);
        $order_dir      = tools_helper::get('order_dir', 'asc');
        $order_field    = tools_helper::get('order_field', 'id');
        $page           = tools_helper::get('page_no', 1);


        $filter = _widget('screen')->default_search_filter($this->member_info);

        $table_type = in_array($search_type, array('business', 'device')) ? $search_type : 'business';
        $table = "screen_{$table_type}_stat_day";

        if (!empty($search_filter['start_date'])) {
            $filter['day >=']  = str_replace('-', '', $search_filter['start_date']);
            if ($table == 'device') {
                $filter['day >='] =  (int)$filter['day >='];
            }
        }

        if (!empty($search_filter['end_date'])) {
            $filter['day <=']  = str_replace('-', '', $search_filter['end_date']);
            if ($table == 'device') {
                $filter['day <='] =  (int)$filter['day <='];
            }
        }

        if (empty($search_filter['start_date']) && empty($search_filter['end_date'])) {
            $search_filter['start_date'] = date('Y-m-d');
            $search_filter['end_date'] = $search_filter['start_date'];
            $filter['day'] = str_replace('-', '', $search_filter['start_date']);
            if ($table == 'device') {
                $filter['day'] =  (int)$filter['day'];
            }
        }

        if (empty($filter)) {
            $filter[1] = 1;
        }

        if ($table_type == 'business') {
            $stat_list = $this->get_buseinss_data($filter);
        } else {
            $stat_list = $this->get_device_data($filter);

        }

        $keys = array();

        $new_list = array();
        foreach ($stat_list as $k => $v) {
            if ($table_type == 'device') {
                $v = array_merge((array)$v, (array)($v->_id));
                unset($v['_id']);
                $tmp = $v;

                $phone_info = screen_device_helper::get_device_info_by_device($tmp['device_unique_id']);

                if (!$phone_info) {
                   continue;
                }

                $phone_name     = !empty($phone_info['phone_name_nickname']) ? $phone_info['phone_name_nickname'] : $phone_info['phone_name'];
                $phone_version  = !empty($phone_info['phone_version_nickname']) ? $phone_info['phone_version_nickname'] : $phone_info['phone_version'];
                $tmp['imei']    = $phone_info['imei'];

                $tmp['phone_name']        = $phone_name;
                $tmp['phone_version']     = $phone_version;

                $new_list[$k] = $tmp;
            }
            $keys[$k] = $v['experience_times'];
        }

        if ($table_type == 'device') {
            $stat_list = $new_list;
        }

        $experience_times_count = array_sum($keys);

        if ($keys && $stat_list && $order_field == 'experience_times') {

            if ($order_dir == 'desc') {
                array_multisort ($keys, SORT_DESC, $stat_list);
            } else {
                array_multisort ($keys, SORT_ASC, $stat_list);
            }

        }

        $search_url_str = "?search_type={$search_type}";
        foreach ($search_filter as $k => $v) {
            $search_url_str.= "&search_filter[{$k}]={$v}";
        }

        Response::assign('count', count($stat_list));
        Response::assign('experience_times_count', $experience_times_count);
        Response::assign('search_filter', $search_filter);
        Response::assign('order_dir', $order_dir);
        Response::assign('order_field', $order_field);
        Response::assign('search_type', $search_type);
        Response::assign('search_url_str', $search_url_str);
        Response::assign('stat_list', $stat_list);
        Response::display('admin/experience_stat/day.html');
    }

    /**
     * 获取营业厅统计数据
     */
    private function get_buseinss_data($filter)
    {
        $get_field = 'sum(experience_time) experience_times,sum(action_num) action_nums,id, province_id, city_id, area_id,business_id, day, sum(device_num) device_nums';

        //条件
        $where ="";
        foreach ($filter as $k => $v) {
            if ( !$where ) {
                $where = " WHERE ";
            }
            if ( strpos($k, '<') || strpos($k, '>') ) {
                $where .= " {$k}{$v} AND";
            } else {
                $where .= " {$k}={$v} AND";
            }
        }
        if ( $where ) {
            $where = rtrim($where, 'AND');
        }
        $sql = "select {$get_field} from screen_business_stat_day {$where} GROUP BY business_id ";
        return _model('screen_business_stat_day')->getAll($sql);
    }

    /**
     * 获取设备统计数据
     * @param unknown $filter
     */
    public function get_device_data($filter)
    {
        $filter     = get_mongodb_filter($filter);
        try{
            $result       = _mongo('screen', 'screen_device_stat_day')->aggregate(array(
                    array('$match' => $filter),
                    array('$group' => array(
                        '_id'               => array(
                                'device_unique_id'  => '$device_unique_id',
                                'province_id'       => '$province_id',
                                'city_id'           => '$city_id',
                                'area_id'           => '$area_id',
                                'business_id'       => '$business_id',

                        ),
                        'experience_times'  => array('$sum' => '$experience_time'),
                        'action_nums'       => array('$sum' => '$action_num'),
                    )
                )

            ), array('typeMap' => array()));

        } catch (Exception $e) {
            p($e->getMessage());
            exit;
        }

        return $result;
    }

    /**
     * screen 统计详情
     * 详情
     */
    public function detail()
    {
        $search_filter  = tools_helper::get('search_filter', array());
        $search_type    = tools_helper::get('search_type', 'business');
        $device_code      = tools_helper::get('device_code', '');
        $business_id    = tools_helper::get('business_id', 0);
        $page           = tools_helper::get('page_no', 1);
        $from_type      = tools_helper::get('from_type', 'parent');

        $filter         = array();

        if ($this->member_info['res_name'] == 'business_hall') {
            $business_id = $this->member_info['res_id'];
        }

        $search_type    = in_array($search_type, array('business', 'device')) ? $search_type : 'business';

        if (isset($search_filter['start_date']) && $search_filter['start_date']) {
            $filter['day >='] = str_replace('-', '', $search_filter['start_date']);
        } else {
            $filter['day >='] = date('Ymd');
        }

        if (isset($search_filter['end_date']) && $search_filter['end_date']) {
            $filter['day <=']  = str_replace('-', '', $search_filter['end_date']);
        } else {
            $filter['day <=']  = date('Ymd');
        }

        if ($business_id) {
            $filter['business_id'] = $business_id;
        }

        if ($from_type == 'sso_login') {
            if ($device_code) {
                 $filter['device_unique_id'] = $device_code;
            }

        } else {
            if ($search_type == 'device' && $device_code) {
                $filter['device_unique_id'] = $device_code;
            }
        }

        $default_filter         = _widget('screen')->default_search_filter($this->member_info);

        $business_title = _uri('business_hall', $business_id, 'title');

        $filter = array_merge($filter, $default_filter);

        $filter['type']     = 2;

        $filter = get_mongodb_filter($filter);

        //查询count
        $count = _mongo( 'screen', 'screen_action_record' )->count( $filter );

        $sort = array('sort' => array('_id' => -1));
        $list = array();
        if ($count) {
            //MongoDB分页类
            $pager = new MongoDBPager( $this->per_page );
            if ( $pager->generate($count) ) {
                Response::assign( 'pager', $pager );
            }
            Response::assign( 'count', $count );
            $list = _mongo('screen', 'screen_action_record')->find($filter, array_merge($pager->getLimit($page), $sort));
        }

        $action_list = array();
        foreach ($list as $k => $v) {
            $action_list[] = (array)$v;
        }

        foreach ($action_list as $k => $v) {

            $phone_info = screen_device_helper::get_device_info_by_device($v['device_unique_id']);
            //$phone_info = _uri('screen_device', array('device_unique_id' => $v['device_unique_id']));

            if (!$phone_info) {
                $phone_name     = '';
                $phone_version  = '';

            } else {
                $phone_name     = !empty($phone_info['phone_name_nickname']) ? $phone_info['phone_name_nickname'] : $phone_info['phone_name'];
                $phone_version  = !empty($phone_info['phone_version_nickname']) ? $phone_info['phone_version_nickname'] : $phone_info['phone_version'];
            }

            $action_list[$k]['phone_name']      = $phone_name;
            $action_list[$k]['phone_version']   = $phone_version;
            $action_list[$k]['imei']            = $phone_info['imei'];

        }

        Response::assign('business_title', $business_title);
        Response::assign('action_list', $action_list);
        Response::display('admin/experience_stat/detail.html');

    }
}