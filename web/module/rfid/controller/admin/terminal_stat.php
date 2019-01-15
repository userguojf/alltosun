<?php
/**
  * alltosun.com 终端体验统计 terminal_stat.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年5月26日 下午12:03:20 $
  * $Id$
  */
class Action
{
    public $member_res_name;
    public $member_res_id;
    public $member_info;
    public $ranks;
    public $per_page = 20;
    public $filter   = array();

    //一个设备的定位字段
    public $dev_fields = ' phone_version, phone_name,  phone_color, label_id, business_id ';

    public function __construct()
    {

        $this->member_id   = member_helper::get_member_id();
        $this->member_info = member_helper::get_member_info($this->member_id);
        if ( $this->member_info ) {
            $this->member_res_name = $this->member_info['res_name'];
            $this->member_res_id   = $this->member_info['res_id'];
            $this->ranks           = $this->member_info['ranks'];
        } else {
            return '您无权访问此页面';
        }

        if ( $this->member_res_name == 'business_hall' ) {
            $this->filter['business_id'] = $this->member_res_id;
        } else if($this->member_res_name != 'group' ){
            $this->filter[$this->member_res_name.'_id'] = $this->member_res_id;
        }

        Response::assign('member_res_name', $this->member_res_name);
        Response::assign('member_res_id', $this->member_res_id);
        Response::assign('member_info', $this->member_info);

    }

    public function __call($action="", $params=array())
    {
        $type               = tools_helper::get('type', 2);
        $search_filter      = tools_helper::get('search_filter', array());
        $order_dir          = tools_helper::get('order_dir', 'desc');
        $order_field        = tools_helper::get('order_field', $type == 4 ? 'average_time' : 'total_time'); //营业厅排行默认根据平均时长否则根据体验总时长
        $page               = tools_helper::get('page_no', 1);
        $is_export          = tools_helper::get('is_export', 0);
        $filter             = array();
        $handle_filter      = array();
        $fields             = '';
        $search_filter_str  = '';


        //品牌排行
        if ( $type == 1 ) {
            $fields        = 'phone_name';

        //型号排行
        } else if ( $type == 2 ) {
            $fields = ' phone_version, phone_name ';

        //设备排行
        } else if ( $type == 3 ) {
            $fields = $this->dev_fields;

        } else if ( $type == 4 ) {
           $fields = 'business_id';

        } else {
            return '不合法的排行类型';
        }

        $filter = _widget('rfid')->init_filter($this->member_info, $search_filter);

        if (isset($filter['business_hall_id'])) {
            $filter['business_id'] = $filter['business_hall_id'];
            unset($filter['business_hall_id']);
        }

        //开始时间
        if (!isset($search_filter['start_date']) || !$search_filter['start_date']) {
             $search_filter['start_date'] = date('Y-m-d', strtotime('-7 days'));
        }

        $filter['date >='] = (int)str_replace( '-', '',$search_filter['start_date'] );

        //结束时间
        if ( !isset($search_filter['end_date']) || !$search_filter['end_date'] ) {
            $search_filter['end_date'] = date('Y-m-d');
        }

        $filter['date <=']  = (int)str_replace('-', '', $search_filter['end_date']);

        if ($this->filter) {
            $filter = array_merge( $filter, $this->filter );
        }

        $where = $this->where($filter);

        $handle_filter = " GROUP BY {$fields} ";

        //排序方式
        if (in_array($order_dir, array('desc', 'asc'))) {
            $order = strtoupper($order_dir);
        } else {
            $order = 'DESC';
        }
        //指定排序
        if ($type == 4 && $order_field == 'average_time') {
            $handle_filter .= " ORDER BY average_time {$order}";
        } else {
            $handle_filter .= " ORDER BY experience_time_sum {$order}";
        }

        if ($type == 4) {
            $sql = "SELECT COUNT(distinct {$this->dev_fields}) as terminal_count, SUM(experience_time) AS experience_time_sum, CEIL(SUM(experience_time)/COUNT(distinct {$this->dev_fields})) AS average_time, {$fields} FROM rfid_record {$where} {$handle_filter}";
        } else {
            $sql = "SELECT COUNT(distinct {$this->dev_fields}) as terminal_count, SUM(experience_time) AS experience_time_sum, {$fields} FROM rfid_record {$where} {$handle_filter}";
        }

        //计算数量
        $count_sql = "SELECT COUNT(*) AS count_total FROM (SELECT COUNT(*) FROM rfid_record {$where}  GROUP BY {$fields}) as total ";
        $count_info = _model('rfid_record')->getAll($count_sql);
        $count = $count_info[0]['count_total'];

        $record_list = array();

        //导出
        if ($is_export == 1) {
            $record_list = _model('rfid_record')->getAll($sql);
            $this->export_call($record_list, $type, $filter);
        }

        if ($count) {
            $pager = new Pager($this->per_page);
            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }

            Response::assign('count', $count);

            $limit_start = ($page-1)*$this->per_page;

            $record_list = _model('rfid_record')->getAll($sql." LIMIT {$limit_start}, {$this->per_page}");
        }

        //拼接地址可用的搜索条件
        $search_filter_str = "?type={$type}";
        foreach ($search_filter as $k => $v) {
            $search_filter_str .= "&search_filter[{$k}]={$v}";
        }

        Response::assign('count', $count);
        Response::assign('search_filter', $search_filter);
        Response::assign('search_filter_str', $search_filter_str);
        Response::assign('order_dir', $order_dir);
        Response::assign('order_field', $order_field);
        Response::assign('record_list', $record_list);
        Response::assign('type', $type);
        Response::display('admin/terminal_stat/index.html');
    }



    private function where($filter)
    {
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

        return $where;
    }

    /**
     * 地域排行
     */
    public function region()
    {
        $type          = tools_helper::Get( 'type', 2 );
        $search_filter = tools_helper::Get( 'search_filter', array() );
        $region_type   = tools_helper::Get( 'region_type', '');


        //默认
        if ( $this->member_res_name == 'group' ) {
            if (!$region_type) $region_type = 'province';
        } else if ( $this->member_res_name == 'province' ) {
            if (!$region_type) {
                $region_type = 'city';
            } else {
                if ($region_type == 'province') return '您无权访问此页面';
            }
        } else if ( $this->member_res_name == 'city' || $this->member_res_name == 'area') {
            if (!$region_type) {
                $region_type = 'business_hall';
            } else {
                if ($region_type == 'city' || $region_type == 'province') return '您无权访问此页面';
            }

        } else {
            return '您无权访问此页面';
        }

        $filter        = array();

        //搜索条件
        if (isset($search_filter['province_id']) && $search_filter['province_id']) {
            $filter['province_id'] = $search_filter['province_id'];
            $region_type = 'city';
        }

        if (isset($search_filter['city_id']) && $search_filter['city_id']) {
            $filter['city_id'] = $search_filter['city_id'];
            $region_type = 'area';
        }

        if (isset($search_filter['area_id']) && $search_filter['area_id']) {
            $filter['area_id'] = $search_filter['area_id'];
            $region_type = 'business_hall';
        }


        //品牌排行
        if ( $type == 1 ) {
            $fields        = 'phone_name';

        //型号排行
        } else if ( $type == 2 ) {
            $fields = ' phone_version, phone_name ';

        //设备排行
        } else if ( $type == 3 ) {
            $fields = $this->dev_fields;

        } else {
            return '不合法的排行类型';
        }

        if ($region_type == 'business_hall') {
            $fields .= ', business_id';
        } else {
            $fields .= ', '.$region_type.'_id';
        }

        if ( isset( $search_filter['start_date'] ) && $search_filter['start_date'] ) {
            $filter['date >='] = str_replace( '-', '', $search_filter['start_date'] );
        }

        if ( isset( $search_filter['end_date'] ) && $search_filter['end_date'] ) {
            $filter['date <='] = str_replace( '-', '', $search_filter['end_date'] );
        }

        if ( isset( $search_filter['phone_name'] ) && $search_filter['phone_name'] ) {
            $filter['phone_name'] = "'{$search_filter['phone_name']}'";
        }

        if ( isset( $search_filter['phone_version'] ) && $search_filter['phone_version'] ) {
            $search_filter['phone_version'] = rfid_helper::url_params_decode($search_filter['phone_version']);
            $filter['phone_version'] = "'{$search_filter['phone_version']}'";
        }

        if ( isset( $search_filter['phone_color'] ) && $search_filter['phone_color'] ) {
            $filter['phone_color'] = "'{$search_filter['phone_color']}'";
        }

        if ( isset( $search_filter['label_id'] ) && $search_filter['label_id'] ) {
            $filter['label_id'] = "'{$search_filter['label_id']}'";
        }

        if ( $this->filter ) {
            $filter = array_merge($this->filter, $filter);
        }

        $where = $this->where($filter);

        $handle_filter = " GROUP BY {$fields} ORDER BY experience_time_sum DESC";

        $sql = "SELECT COUNT(distinct {$this->dev_fields}) as terminal_count, SUM(experience_time) AS experience_time_sum, {$fields} FROM rfid_record {$where} {$handle_filter}";

        $stat_list = _model('rfid_record')->getAll($sql);

        //拼接地址可用的搜索条件
        $search_filter_str = "?type={$type}";
        foreach ($search_filter as $k => $v) {
            if ($k == 'phone_version') {
                $v = rfid_helper::url_params_encode($v);
            }
            $search_filter_str .= "&search_filter[{$k}]={$v}";
        }
        Response::assign('type', $type);
        Response::assign('search_filter_str', $search_filter_str);
        Response::assign('search_filter', $search_filter);
        Response::assign('stat_list', $stat_list);
        Response::assign('region_type', $region_type);
        Response::display('admin/terminal_stat/region.html');

    }

    /**
     * 营业厅排行的下级页：标签列表页
     */
    public function business_hall_label()
    {
        $type          = tools_helper::Get( 'type', 4 );
        $search_filter = tools_helper::Get( 'search_filter', array() );
        $page          = tools_helper::Get( 'page_no', 1 );

        $filter        = array();

        if ( isset( $search_filter['start_date'] ) && $search_filter['start_date'] ) {
            $filter['date >='] = str_replace( '-', '', $search_filter['start_date'] );
        }

        if ( isset( $search_filter['end_date'] ) && $search_filter['end_date'] ) {
            $filter['date <='] = str_replace( '-', '', $search_filter['end_date'] );
        }

        if ( isset( $search_filter['phone_name'] ) && $search_filter['phone_name'] ) {
            $filter['phone_name'] = $search_filter['phone_name'];
        }

        if ( isset( $search_filter['phone_version'] ) && $search_filter['phone_version'] ) {
            $filter['phone_version'] = rfid_helper::url_params_decode($search_filter['phone_version']);
        }

        if ( isset( $search_filter['phone_color'] ) && $search_filter['phone_color'] ) {
            $filter['phone_color'] = $search_filter['phone_color'];
        }

        if ( isset( $search_filter['label_id'] ) && $search_filter['label_id'] ) {
            $filter['label_id'] = $search_filter['label_id'];
        }

        if ( isset( $search_filter['business_id'] ) && $search_filter['business_id'] && $business_hall_title = business_hall_helper::get_business_hall_info($search_filter['business_id'], 'title')) {
            $filter['business_id'] = $search_filter['business_id'];
            Response::assign('business_hall_title', $business_hall_title);
        } else {
            return '未知的营业厅信息';
        }

        //拼接地址可用的搜索条件, 用于下级列表
        $search_filter_str = "?type={$type}";
        foreach ($search_filter as $k => $v) {
            $search_filter_str .= "&search_filter[{$k}]={$v}";
        }

        if ( $this->filter ) {
            $filter = array_merge( $filter, $this->filter );
        }
        $where = $this->where($filter);

        //获取count
        $count_sql = " SELECT count(*) as count_total FROM (SELECT count(*) FROM rfid_record {$where} GROUP BY label_id) as total";
        $count_info = _model('rfid_record')->getAll($count_sql);
        $count = $count_info[0]['count_total'];

        $label_list = array();
        if ($count) {

            $pager = new Pager($this->per_page);

            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }

            $limit = ($page-1)*$this->per_page;

            $sql = " SELECT *, SUM(experience_time) AS experience_time_sum FROM rfid_record {$where} GROUP BY label_id LIMIT {$limit}, {$this->per_page}";

            $label_list = _model('rfid_record')->getAll($sql);

        }

        Response::assign('count', $count);
        Response::assign('search_filter_str', $search_filter_str);
        Response::assign('search_filter', $search_filter);
        Response::assign('label_list', $label_list);
        Response::display('admin/terminal_stat/label_list.html');
    }

    /**
     * 详情
     */
    public function detail()
    {
        $type          = tools_helper::Get( 'type', 2 );
        $search_filter = tools_helper::Get( 'search_filter', array() );
        $page          = tools_helper::Get( 'page_no', 1 );

        $filter        = array();

        if ( isset( $search_filter['start_date'] ) && $search_filter['start_date'] ) {
            $filter['date >='] = str_replace( '-', '', $search_filter['start_date'] );
        }

        if ( isset( $search_filter['end_date'] ) && $search_filter['end_date'] ) {
            $filter['date <='] = str_replace( '-', '', $search_filter['end_date'] );
        }

        if ( isset( $search_filter['phone_name'] ) && $search_filter['phone_name'] ) {
            $filter['phone_name'] = $search_filter['phone_name'];
        }

        if ( isset( $search_filter['phone_version'] ) && $search_filter['phone_version'] ) {
            $filter['phone_version'] = rfid_helper::url_params_decode($search_filter['phone_version']);
        }

        if ( isset( $search_filter['phone_color'] ) && $search_filter['phone_color'] ) {
            $filter['phone_color'] = $search_filter['phone_color'];
        }

        if ( isset( $search_filter['label_id'] ) && $search_filter['label_id'] ) {
            $filter['label_id'] = $search_filter['label_id'];
        }

        if ( isset( $search_filter['business_id'] ) && $search_filter['business_id'] ) {
            $filter['business_id'] = $search_filter['business_id'];
        }

        $filter['end_timestamp >'] = 0;
        $filter['status >']        = 0;

        if ( $this->filter ) {
            $filter = array_merge( $filter, $this->filter );
        }

        $detail_list = get_data_list( 'rfid_record_detail', $filter, ' ORDER BY `id` DESC ', $page, $this->per_page );

        Response::assign('detail_list', $detail_list);
        Response::display('admin/terminal_stat/detail.html');
    }


    /**
     * 导出列表
     * @param unknown $list
     */
    private function export_call($list, $type, $filter)
    {
        $new_list = array();

        $new_filter = array();

        if (!empty($filter['date >='])) {
            $new_filter['date >='] = $filter['date >='];
        }

        if (!empty($filter['date <='])) {
            $new_filter['date <='] = $filter['date <='];
        }

        $new_filter['status']       = 1;

        foreach ($list as $k => $v) {

            //营业厅排行
            if ($type == 4) {

                $business_hall_info = _uri('business_hall', $v['business_id']);

                if (!$business_hall_info) {
                    echo '未知错误';
                    exit();
                }
                $new_list[$k]['province']       = _uri('province', $business_hall_info['province_id'], 'name');
                $new_list[$k]['city']           = _uri('city', $business_hall_info['city_id'], 'name');
                $new_list[$k]['area']           = _uri('area', $business_hall_info['area_id'], 'name');
                $new_list[$k]['business_hall']  = $business_hall_info['title'];
                $new_filter['business_id']      = $business_hall_info['id'];

                //型号排行
            } else if ($type == 2) {
                $new_list[$k]['phone_name']       = $v['phone_name'];
                $new_list[$k]['phone_version']    = $v['phone_version'];
                $new_filter['phone_name']         = $v['phone_name'];
                $new_filter['phone_version']         = $v['phone_version'];
                $v['average_time']                = ceil($v['experience_time_sum']/$v['terminal_count']);

                //品牌排行
            } else if ($type == 1) {
                $new_list[$k]['phone_name']       = $v['phone_name'];
                $new_filter['phone_name']         = $v['phone_name'];
                $v['average_time']                = ceil($v['experience_time_sum']/$v['terminal_count']);
            } else {
                echo '暂不支持此类型排行数据导出';
                exit;
            }

            $new_list[$k]['terminal_count']       = $v['terminal_count'];
            $new_list[$k]['experience_time_sum'] = round($v['experience_time_sum']/60, 1);
            $new_list[$k]['average_time']        = round($v['average_time']/60, 1);

            //获取动作数
            $new_list[$k]['time_count']          = _model('rfid_record_detail')->getTotal($new_filter);

        }


        $params['data']     = $new_list;
        //营业厅排行
        if ($type == 4) {
            $params['filename'] = 'rfid各营业厅体验排行';
            $params['head']     = array('省', '市', '区', '营业厅', '体验设备数', '体验总时长', '平均时长', '动作数');
            //型号排行
        } else if ($type == 2) {
            $params['filename'] = 'rfid各型号体验排行';
            $params['head']     = array('设备品牌', '设备型号', '体验设备数', '体验总时长', '平均时长', '动作数');
            //品牌排行
        } else if ($type == 1) {
            $params['filename'] = 'rfid各品牌体验排行';
            $params['head']     = array('设备品牌', '体验设备数', '体验总时长', '平均时长', '动作数');
        } else {
            echo '暂不支持此类型排行数据导出';
            exit;
        }

        Csv::getCvsObj($params)->export();
    }
}