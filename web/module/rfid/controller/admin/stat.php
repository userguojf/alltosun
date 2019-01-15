<?php
/**
  * alltosun.com 统计 stat.php
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
     * rfid统计首页
     * @param string $action
     * @param array $params
     */

    private $per_page = 20;
    public $member_id = 0;
    public $member_res_id = 0;
    public $member_res_name = '';
    public $member_info = array();
    public $filter          = array();
    public $interval   = 60;
    public $secret     = '';

    public function __construct()
    {

        $this->member_id   = member_helper::get_member_id();
        $this->member_info = member_helper::get_member_info($this->member_id);
        if ($this->member_info) {
            $this->member_res_name = $this->member_info['res_name'];
            $this->member_res_id   = $this->member_info['res_id'];
            $this->ranks           = $this->member_info['ranks'];
        } else {
            return '您无权访问此页面';
        }

        if ($this->member_res_name != 'group') {
            if ($this->member_res_name =='business_hall') {
                $this->filter['business_id'] = $this->member_res_id;
            } else {
                $this->filter[$this->member_res_name.'_id'] = $this->member_res_id;
            }

        }

        $this->secret = md5('ondev_by_alltusun');
        //条件搜索时的地址参数
        $action_res_url     = 'rfid/admin/stat';
        Response::assign('member_info', $this->member_info);
        Response::assign('action_res_url', $action_res_url);


    }

    public function __call($action='', $params=array())
    {

        $search_filter = Request::Get('search_filter', array());

        //日期类型
        if (isset($search_filter['date_type']) && $search_filter['date_type']) {
            if (in_array($search_filter['date_type'], rfid_config::$stat_date_type)) {
                $date_type   =  $search_filter['date_type'];
            } else {
                return "无效的日期搜索类型“{$search_filter['date_type']}”";
            }
        } else {
             $date_type = $search_filter['date_type'] = 'day';
        }


        //开始日期
        if (isset($search_filter['start_date']) && $search_filter['start_date']) {
            $start_date  = $search_filter['start_date'];
        } else {
            //默认七天
            $start_date = $search_filter['start_date'] = date('Y-m-d', strtotime('-6 days'));
        }

        //结束日期
        if (isset($search_filter['end_date']) && $search_filter['end_date']) {
            $end_date  = $search_filter['end_date'];
        } else {
            //默认至今
            $end_date = $search_filter['end_date'] = date('Y-m-d');
        }

        $filter      = $this->filter;

        $table       = 'rfid_stat';

        //时间过滤
        if ($start_date > $end_date) {
            return array('开始时间必须小于结束时间!');
        }

        if ($start_date > date('Y-m-d') || $end_date > date('Y-m-d')) {
            return array('开始时间或结束时间不能大于当前时间!');
        }

        //小时
        if ( $date_type == 'hour') {
            $filter['date_for_day']   = str_replace('-', '', $start_date);
            $table                    = 'rfid_stat_hour';
            $end_date = '';

        //天
        } else if ( $date_type == 'day') {
            //条件
            $filter['date_for_day >='] =  str_replace('-', '', $start_date);;
            $filter['date_for_day <='] =  str_replace('-', '', $end_date);;
        //周
        } else if  ( $date_type == 'week') {
            //（条件）
            $filter['date_for_week >='] = date('Y').date('W',strtotime($start_date));
            $filter['date_for_week <='] = date('Y').date('W',strtotime($end_date));
        //月
        } else if ( $date_type == 'month') {
            //条件
            $filter['date_for_month >='] = date('Ym', strtotime($start_date));
            $filter['date_for_month <='] = date('Ym', strtotime($end_date));
        } else {
            return "无效的日期搜索类型“{$date_type}”";
        }


        //获取
        $list = _model($table)->getList($filter, ' ORDER BY `id` DESC');

        //解析所需数据
        $stat_info = rfid_helper::parse_stat($list, $search_filter);

        $date_list    = $stat_info['date_list'];
        $number_count_list   = $stat_info['number_count_list'];
        $time_count_list   = $stat_info['time_count_list'];
        $stat_list    = $stat_info['list'];

        if ($stat_list) {
            krsort($stat_list);
        }
        //拼接搜索条件
        $search_filter_str = '';
        foreach ($search_filter as $k => $v) {

            if (!$search_filter_str) {
                $search_filter_str .= '?';
            } else {
                $search_filter_str .= '&';
            }

            $search_filter_str .= "search_filter[$k]={$v}";
        }



        Response::assign('stat_list', $stat_list);
        Response::assign('search_filter', $search_filter);
        Response::assign('search_filter_str', $search_filter_str);
        Response::assign('json_number_count', json_encode($number_count_list));
        Response::assign('json_time_count', json_encode($time_count_list));
        Response::assign('json_date_list', json_encode($date_list));
        Response::display('admin/stat/index.html');
    }
    /**
     * rfid 统计详情
     * 详情
     */
    public function detail()
    {
        $search_filter  = tools_helper::get('search_filter', array());
        $page           = tools_helper::get('page_no', 1);

        $filter = _widget('rfid')->init_filter($this->member_info, $search_filter);

        if ( !empty($filter['business_hall_id']) ) {
            $filter['business_id'] = $filter['business_hall_id'];
            unset($filter['business_hall_id']);
        }

        if (!empty($search_filter['label_id'])) {
            $filter['label_id'] = trim($search_filter['label_id']);
        }

        /**
         * 统计页跳转过来携带的条件
         */
        if (isset($search_filter['date_type']) && $search_filter['date_type']) {
            $date_type = $search_filter['date_type'];
        } else {
            $date_type = '';
        }

        //首页进来
        if (!empty($search_filter['date'])) {
            $start_date = $search_filter['start_date'] = date('Y-m-d', strtotime($search_filter['date']));
            $end_date = $search_filter['end_date'] = date('Y-m-d', strtotime($search_filter['date']));
            unset($search_filter['date']);
        } else {
            if (isset($search_filter['start_date']) && $search_filter['start_date']) {
                $start_date = $search_filter['start_date'];
            } else {
                $start_date = $search_filter['start_date'] = date('Y-m-d');
            }

            if (isset($search_filter['end_date']) && $search_filter['end_date']) {
                $end_date = $search_filter['end_date'];
            } else {
                $end_date = $search_filter['end_date'] = date('Y-m-d');
            }
        }
        unset($search_filter['date_type']);

        $filter['date >='] = date('Ymd', strtotime($start_date));
        $filter['date <='] = date('Ymd', strtotime($end_date));

        //原生Sql 分页
        $where = $this->where($filter);
        $group = 'GROUP BY business_id,label_id,phone_name,phone_version,phone_color';

        $count_sql = "SELECT count(*) as count_total, SUM(experience_time_count) as experience_time  FROM (SELECT SUM(experience_time) as experience_time_count  FROM `rfid_record` {$where} {$group}) as count_sql ";

        $count_info = _model('rfid_record')->getAll($count_sql);

        $count                  = $count_info[0]['count_total'];
        $experience_time_count  = $count_info[0]['experience_time'];

        $record_list            = array();

        if ($count) {
            $pager = new Pager($this->per_page);
            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }

            $limit_start = ($page-1)*$this->per_page;
            //分页查询
            $sql = " SELECT *,SUM(experience_time) as experience_time_count FROM `rfid_record` {$where} {$group} ORDER BY `experience_time_count` DESC LIMIT {$limit_start}, {$this->per_page}";

            $record_list = _model('rfid_record')->getAll($sql);

        }
        foreach ($record_list as $k => $v) {
            $record_list[$k]['province_name'] = _uri('province', $v['province_id'], 'name');
            $record_list[$k]['city_name'] = _uri('city', $v['city_id'], 'name');
            $record_list[$k]['area_name'] = _uri('area', $v['area_id'], 'name');
            $record_list[$k]['experience_time'] = $v['experience_time_count'];
            //查询省市厅信息
            $record_list[$k]['business_name'] = _uri('business_hall', $v['business_id'], 'title');
        }

        //拼接搜索条件
        $search_filter_str = '';
        foreach ($search_filter as $k => $v) {

            if (!$search_filter_str) {
                $search_filter_str .= '?';
            } else {
                $search_filter_str .= '&';
            }

            $search_filter_str .= "search_filter[$k]={$v}";
        }

        Response::assign('count', $count);
        Response::assign('search_filter_str', $search_filter_str);
        Response::assign('experience_time_count', $experience_time_count);
        Response::assign('search_filter', $search_filter);
        Response::assign('record_list', $record_list);
        Response::display('admin/stat/detail.html');
    }

    /**
     * 记录列表
     */
    public function record()
    {
        $label_id       = tools_helper::get('label_id', '');
        $page           = tools_helper::get('page_no', 1);
        $search_filter  = tools_helper::get('search_filter', array());
        $business_id    = tools_helper::get('business_id', -1);
        $order_field    = tools_helper::get('order_field', 'id');
        $order_dir      = tools_helper::get('order_dir', 'desc');
        $search_type    =  tools_helper::get('search_type', '');
        $search_text    =  tools_helper::get('search_text', '');

        $filter         = array();

        //单点登录接口兼容
        if ($business_id != -1) {
            $search_filter['business_id'] = $business_id;
        } else {
            $filter   = $this->filter;
        }

        if ($this->member_res_name =='business_hall') {
            $search_filter['business_id'] = $this->member_res_id;
        }

        if ($label_id) {
            $search_type = 'label_id';
            $search_text = $label_id;
            $search_filter['label_id'] = $label_id;
        }

        $business_title = member_helper::get_title_info($this->member_id);

        if (isset($search_filter['start_date']) && $search_filter['start_date']) {
            $filter['date >='] = str_replace('-', '', $search_filter['start_date']);
        } else {
            $filter['date >= '] = str_replace('-', '', ($search_filter['start_date'] = date('Y-m-d')));
        }

        if (isset($search_filter['end_date']) && $search_filter['end_date']) {
            $filter['date <='] = str_replace('-', '', $search_filter['end_date']);
        } else {
            $filter['date <= '] = str_replace('-', '',($search_filter['end_date'] = date('Y-m-d')));
        }

        if (isset($search_filter['business_id']) && $search_filter['business_id']) {
            $business_title = _uri('business_hall', $search_filter['business_id'], 'title');
            $filter['business_id'] = $search_filter['business_id'];
        }

        if (isset($search_filter['label_id']) && $search_filter['label_id']) {
            $filter['label_id'] = trim($search_filter['label_id']);
        }

        if (isset($search_filter['phone_name']) && $search_filter['phone_name']) {
            $filter['phone_name'] = $search_filter['phone_name'];
        }

        if (isset($search_filter['phone_version']) && $search_filter['phone_version']) {
            $filter['phone_version'] = $search_filter['phone_version'] = rfid_helper::url_params_decode($search_filter['phone_version']);
        }

        if (isset($search_filter['phone_color']) && $search_filter['phone_color']) {
            $filter['phone_color'] = $search_filter['phone_color'];
        }

        if ($search_type && $search_text) {
            $filter[$search_type] = $search_text;
        }

        $filter['end_timestamp >'] = 0;
        $filter['status >']        = 0;

        $remain_time_list = _model('rfid_record_detail')->getFields('remain_time', $filter);
        $remain_time_count = array_sum($remain_time_list);

        $record_list = get_data_list('rfid_record_detail', $filter, " ORDER BY {$order_field} {$order_dir} ", $page, $this->per_page);

        Response::assign('remain_time_count', $remain_time_count);
        Response::assign('search_type', $search_type);
        Response::assign('order_field', $order_field);
        Response::assign('order_dir', $order_dir);
        Response::assign('business_id', $business_id);
        Response::assign('search_text', $search_text);
        Response::assign('business_title', $business_title);
        Response::assign('record_list', $record_list);
        Response::assign('search_filter', $search_filter);
        Response::display('admin/stat/record_list.html');
    }



    /**
     * record客流列表
     */
    public function stat_detail_passenger_flow()
    {
        $search_filter      = tools_helper::get('search_filter', array());
        $page               = tools_helper::get('page_no', 1);

        //查询所有的记录id
        if (isset($search_filter['start_date']) && $search_filter['start_date']) {
            $filter['date >='] = str_replace('-', '', $search_filter['start_date']);
        }

        if (isset($search_filter['end_date']) && $search_filter['end_date']) {
            $filter['date <='] = str_replace('-', '', $search_filter['end_date']);
        }

        if (isset($search_filter['business_id']) && $search_filter['business_id']) {
            $business_title = _uri('business_hall', $search_filter['business_id'], 'title');
            $filter['business_id'] = $search_filter['business_id'];
        }

        if (isset($search_filter['label_id']) && $search_filter['label_id']) {
            $filter['label_id'] = trim($search_filter['label_id']);
        }

        if (isset($search_filter['phone_name']) && $search_filter['phone_name']) {
            $filter['phone_name'] = $search_filter['phone_name'];
        }

        if (isset($search_filter['phone_version']) && $search_filter['phone_version']) {
            $filter['phone_version'] = $search_filter['phone_version'] = rfid_helper::url_params_decode($search_filter['phone_version']);
        }

        if (isset($search_filter['phone_color']) && $search_filter['phone_color']) {
            $filter['phone_color'] = $search_filter['phone_color'];
        }

        $filter['end_timestamp >'] = 0;
        $filter['status >']        = 0;

        $end_timestamp_list = _model('rfid_record_detail')->getFields('end_timestamp', $filter);

        $mac_list = array();
        foreach ($end_timestamp_list as $k => $v) {
            $mac_list[] = _model('rfid_probe_user_record')->getList(array('up_time <=' => $v, 'up_time >=' => $v - $this->interval));
        }

        $new_mac_list = array();
        foreach ($mac_list as $k => $v) {
            foreach ($v as $key => $value) {
                if (isset($new_mac_list[$value['mac']])) {
                    continue;
                }

                $new_mac_list[$value['mac']] = $value['up_time'];
            }

        }



        Response::assign('type', 'stat_detail');
        Response::assign('passenger_list', $new_mac_list);
        Response::display('admin/stat/unique_passenger_flow_list.html');

    }

    /**
     * record客流列表
     */
    public function record_passenger_flow()
    {
        $detail_id      = tools_helper::get('detail_id', 0);
        $page           = tools_helper::get('page_no', 1);
        $passenger_list = array();

        if (!$detail_id) {
            return '没有此RFID的详情记录';
        }

        $detail_info = _uri('rfid_record_detail', $detail_id);
        if (!$detail_info) {
            return '没有此RFID的详情记录';
        }

        $filter = array(
            'up_time <=' => $detail_info['end_timestamp'],
            'up_time >' => strtotime(date('Y-m-d H:i', $detail_info['end_timestamp'])) - $this->interval
        );

        $passenger_list = get_data_list('rfid_probe_user_record', $filter, ' GROUP BY `mac` ORDER BY id ASC', $page, $this->per_page);

        Response::assign('secret', $this->secret);
        Response::assign('passenger_list', $passenger_list);
        Response::display('admin/stat/passenger_flow_list.html');

    }

    /**
     * 处理where条件
     * @param unknown $filter
     * @return string
     */
    private function where($filter)
    {
        //条件
        $where ="";
        foreach ($filter as $k => $v) {

            if ( !$where ) {
                $where = " WHERE ";
            }

            if ($k == 'label_id') {
                $v = "'".$v."'";
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

        return $where;
    }

    //导出指定厅指定时间的rfid数据
    public function export_business_hall()
    {
        $start_time = tools_helper::Get('start_time', date('Y-m-d 00:00:00'));
        $end_time   = tools_helper::Get('end_time', date('Y-m-d 59:59:59'));
        $user_number = tools_helper::Get('user_number', '');

        if (!$user_number) {
            echo '营业厅编码不能为空';
            return false;
        }

        if (!strtotime($start_time) || !strtotime($end_time)) {
            echo '开始时间不能为空';
            return false;
        }

        $business_hall_info = _uri('business_hall', array('user_number' => $user_number));

        if ( !$business_hall_info ){
            echo '不存在此营业厅';
            return false;
        }

        $filter = array(
                'start_timestamp >=' => strtotime($start_time),
                'start_timestamp <=' => strtotime($end_time),
                'status'             => 1,
                'business_id'        => $business_hall_info['id']
        );

        $detail_list = _model('rfid_record_detail')->getList($filter);

        $new_list    = array();

        foreach ($detail_list as $k => $v) {

            $filter = array(
                    'up_time <=' => $v['end_timestamp'],
                    'up_time >' => strtotime(date('Y-m-d H:i', $v['end_timestamp'])) - $this->interval
            );

            $macs = _model('rfid_probe_user_record')->getFields('mac', $filter, ' GROUP BY `mac` ');

            $new_list[$k]['label_id']       = $v['label_id'];
            $new_list[$k]['business_hall']  = $business_hall_info['title'];
            $new_list[$k]['phone_name']     = $v['phone_name'];
            $new_list[$k]['phone_version']  = $v['phone_version'];
            $new_list[$k]['start_time'] = date('Y-m-d H:i:s', $v['start_timestamp']);
            $new_list[$k]['end_time']   = date('Y-m-d H:i:s', $v['end_timestamp']);
            $new_list[$k]['remain_time']    = $v['remain_time'];
            $new_list[$k]['mac']            = implode(',', $macs);
        }

        $params['filename'] = $business_hall_info['title'].'rfid体验详情'.$start_time.'-'.$end_time;
        $params['data']     = $new_list;
        $params['head']     = array('标签ID', '所属营业厅', '手机品牌', '手机型号', '拿起时间', '放下时间', '时长(秒)', '客流mac');

        Csv::getCvsObj($params)->export();



    }




}