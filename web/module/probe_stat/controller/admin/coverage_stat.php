<?php
/**
  * alltosun.com 营业厅覆盖统计 coverage_stat.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2018年2月28日 下午12:00:41 $
  * $Id$
  */
probe_helper::load('func');
class Action
{
    private $per_page    = 20;
    private $member_info = array();
    private $date;
    private $date_type;

    public function __construct()
    {
        $this->member_info  = member_helper::get_member_info();
        $search_filter = tools_helper::Get('search_filter', array());

        if (!empty($search_filter['date_type'])) {
            $this->date_type = $search_filter['date_type'];
        } else {
            $this->date_type = 2;
            $search_filter['date_type'] = $this->date_type;
        }

        //时间条件只用作已覆盖门店和设备状态
        if (!empty($search_filter['date'])) {
            $this->date = $search_filter['date'];
        } else {
            $this->date = date('Y-m-d');
            $search_filter['date'] = $this->date;
        }

        $str_search_filter = '';
        foreach ($search_filter as $k => $v) {
            if (!$str_search_filter) {
                $str_search_filter .= '?';
            } else {
                $str_search_filter .= '&';
            }
            $str_search_filter .= 'search_filter['.$k.']='.$v;
        }

        Response::assign('search_filter', $search_filter);
        Response::assign('str_search_filter', $str_search_filter);
    }

    public function __call($action='', $param=array())
    {
        if (!$this->member_info) {
            return '请先登录';
        }

        $res_name       = tools_helper::Get('res_name', '');
        $res_id         = tools_helper::Get('res_id', 0);

        $if_export = tools_helper::Get('if_export', 0);

        if (!$res_name && !$res_id) {
            $res_name = $this->member_info['res_name'];
            $res_id   = $this->member_info['res_id'];
        }

        //获取指定归属地下所有营业厅
        $business_hall_list_info    = $this->get_all_business_hall($res_name, $res_id);
        //处理归属地的探针覆盖量数据
        $business_hall_list = $this->handle_index_data($business_hall_list_info);

        if ($if_export && $business_hall_list) {
            $this->export_index_data($business_hall_list);
        }

        Response::assign('stat_list', $business_hall_list);
        Response::assign('res_name', $res_name);
        Response::assign('res_id', $res_id);
        Response::display('admin/coverage_stat/index.html');
    }

    /**
     * 营业厅列表
     * @return string
     */
    public function business_hall_list()
    {
        $type       = tools_helper::Get('type', 1);
        $res_name   = tools_helper::Get('res_name', '');
        $res_id     = tools_helper::Get('res_id', 0);
        $if_export  = tools_helper::Get('if_export', 0);


        //已覆盖营业厅
        $covered_business_hall_ids = $this->get_covered_business_hall($res_name, $res_id);

        if ($type == 1) {
            $business_hall_ids = $covered_business_hall_ids;
            //未覆盖营业厅
        } else if ($type == 2) {
            $business_hall_ids = $this->get_not_cover_business_hall($res_name, $res_id, $covered_business_hall_ids);
        }

        //查询营业厅详情
        if (!$business_hall_ids) {
            $stat_list = array();
        } else {
            $business_hall_list = _model('business_hall')->getList(array('id' => $business_hall_ids));
            $stat_list = $this->handle_detail_data($res_name, $res_id, $business_hall_list);
        }

        if ($if_export && $stat_list) {
            $this->export_business_hall_detail_data($stat_list, $type);
            exit();
        }

        Response::assign('stat_list', $stat_list);
        Response::assign('res_name', $res_name);
        Response::assign('res_id', $res_id);
        Response::assign('type', $type);
        Response::display('admin/coverage_stat/business_hall_list.html');
    }

    /**
     * 设备状态分布列表
     * @return string
     */
    public function device_status_list()
    {
        $type       = tools_helper::Get('type', 1);
        $res_name   = tools_helper::Get('res_name', '');
        $res_id     = tools_helper::Get('res_id', 0);
        $if_export  = tools_helper::Get('if_export', 0);

        if (!$res_name || !$res_id) {
            return '参数不完整';
        }

        //按月搜索 先查出月活设备
        if ($this->date_type == 2) {
            $device_list_info = $this->month_active($res_name, $res_id);
            $device_list = $device_list_info['month_active'];
        } else {
            //正常的设备
            $device_list = $this->get_normal_device($res_name, $res_id);
        }
        //正常设备 按天搜索
        if ($type == 1 && $this->date_type == 1) {

        //月活跃设备 按月搜索
        } else if ($type == 7 && $this->date_type == 2) {

        } else if ($type == 2) {
            $device_list = $this->get_abnormal_device($res_name, $res_id, $device_list);
        }
        $stat_list = array();
        //如果为营业厅或者区权限， 则直接展示设备
        if (in_array($res_name, array('business_hall', 'area'))) {
            $region_name = '';
            //归属地昵称
            if ($res_name == 'business_hall') {
                $region_name = business_hall_helper::get_info_name($res_name, $res_id, 'title');
            }

            foreach ($device_list as $k => $v) {
                $tmp = array();
                if ($res_name == 'area') {
                    $region_name = business_hall_helper::get_info_name('business_hall', $v['business_id'], 'title');
                }

                if (!$region_name) {
                    continue;
                }

                $tmp['region_name'] = $region_name;
                $tmp['value'] = $v['device'];
                $tmp['res_id'] = $res_id;
                $tmp['status'] = get_dev_status($v);
                $stat_list[] = $tmp;
            }
        } else {
            $stat_list = $this->handle_detail_data($res_name, $res_id, $device_list);
        }

        if ($if_export && $stat_list) {
            $this->export_device_status_detail_data($stat_list, $type, $res_name);
            exit();
        }

        Response::assign('stat_list', $stat_list);
        Response::assign('res_name', $res_name);
        Response::assign('res_id', $res_id);
        Response::assign('type', $type);
        Response::display('admin/coverage_stat/device_status_list.html');

    }

    /**
     * 有效活跃设备（月活跃）
     * 活跃的定议是：每天都在线，这个月才算月活， 否则都叫无效活跃
     */
    private function month_active($res_name, $res_id)
    {
        //拼接归属地相关字段
        $field = $res_name.'_id';
        if ($res_name == 'business_hall') {
            $field = 'business_id';
        }

        $filter             = array($field => $res_id, 'status' => 1);

        $filter['date >='] = date('Ym01', strtotime($this->date));
        $filter['date <'] = date('Ym01', strtotime('+1 month', strtotime($this->date)));

        //本月最后一天
        $max_date = date('d', strtotime($filter['date <'] - 3600));

        $where = to_where_sql($filter);

        $sql = " SELECT `device`, COUNT(*) as `active_count` FROM `probe_device_status_stat_day` {$where} GROUP BY `device` ";
        $device_list = _model('probe_device_status_stat_day')->getAll($sql);
        $not_active         = array();
        $month_active       = array();
        foreach ( $device_list as $k => $v ) {
            if ($v['active_count'] == $max_date) {
                //月活跃
                $month_active[] = $v['device'];
            } else {
                //无效活跃
                $not_active[] = $v['device'];
            }
        }

        if ($month_active) {
            $month_active = _model('probe_device')->getList(array('device' => $month_active));
        }

        if ($not_active) {
            $not_active = _model('probe_device')->getList(array('device' => $not_active));
        }

        return array('month_active' => $month_active, 'not_active' => $not_active);
    }

    /**
     * 处理详情页的数据
     * @param unknown $data_list
     */
    private function handle_detail_data($res_name, $res_id, $data_list)
    {
        $page = tools_helper::Get('page_no', 1);
        //是否导出数据
        $if_export = tools_helper::Get('if_export', 0);

        if (!$data_list || !$res_name || !$res_id) {
            return array();
        }

        //取当前res_name的下级的相关字段
        if ($res_name == 'province') {
            $group_field = 'city_id';
            $region_table = 'city';
            //市
        } else if ($res_name == 'city') {
            $group_field = 'area_id';
            $region_table = 'area';
            //区
        } else if ($res_name == 'area') {
            //取最后一个指针位置的元素
            $end = end($data_list);
            $group_field = !empty($end['business_id']) ? 'business_id' : 'id';
            $region_table = 'business_hall';
            //厅
        } else if ($res_name == 'business_hall') {
            //取最后一个指针位置的元素
            $end = end($data_list);
            $group_field = !empty($end['business_id']) ? 'business_id' : 'id';
            $region_table = 'business_hall';
        } else {
            return array();
        }

        //按照下级归属地将数据分组
        $grouped_data = array();
        foreach ( $data_list as $k => $v) {
            if (!empty($v[$group_field])) {
                $grouped_data[$v[$group_field]][] = $v;
                $sum += 1;
            }
        }
        //分页
        $count = count($grouped_data);

        //导出数据时，则不分页
        if (!$if_export) {
            if ($count) {
                $pager = new Pager($this->per_page);
                if ($pager->generate($count)) {
                    Response::assign('pager', $pager);
                }
                $grouped_data = array_slice($grouped_data, ($page-1)*$this->per_page, $this->per_page, true);
            }
        }

        Response::assign('count', $sum);

        //循环处理数据
        $new_list = array();
        foreach ($grouped_data as $k => $v) {

            $tmp = array();
            //归属地昵称
            if ($region_table == 'business_hall') {
                $tmp['region_name'] = business_hall_helper::get_info_name($region_table, $k, 'title');
            } else {
                $tmp['region_name'] = business_hall_helper::get_info_name($region_table, $k, 'name');
            }
            if (!$tmp['region_name']) {
                continue;
            }

            $tmp['value'] = count($v);
            //归属地id
            $tmp['res_id']   = $k;

            $new_list[] = $tmp;
        }

        Response::assign('subordinate', $region_table);

        return $new_list;
    }


    /**
     * 处理首页数据
     * @param unknown $business_hall_list_info
     * @return unknown|number|boolean
     */
    private function handle_index_data($business_hall_list_info)
    {

        $order_dir      = tools_helper::Get('order_dir', '');
        $order_field    = tools_helper::Get('order_field', '');

        $orders = array();

        $business_hall_list         = $business_hall_list_info['business_hall_list'];
        //按归属地分组的字段
        $group_field                = $business_hall_list_info['group_field'];

        //归属地详情表名称
        $region_table               = $business_hall_list_info['region_table'];

        foreach ($business_hall_list as $k => $v) {

            //查询本归属地内已覆盖门店量
            $v['coverage_business_hall_count'] = count($this->get_covered_business_hall($region_table, $v[$group_field]));

            //未覆盖门店
            $v['not_covered_business_hall_count'] = $v['business_hall_count'] - $v['coverage_business_hall_count'];

            if ($this->date_type == 2) {
                $month_active = $this->month_active($region_table, $v[$group_field]);
                //有效活跃设备
                $normal_device = $month_active['month_active'];
            } else {
                //正常设备
                $normal_device = $this->get_normal_device($region_table, $v[$group_field]);
            }

            //正常的设备
            $v['normal_device_count'] = count($normal_device);

            //异常设备
            $v['abnormal_device_count']= count($this->get_abnormal_device($region_table, $v[$group_field], $normal_device));

            //待安装暂时为0
            $v['to_be_installed_device_count']   = count($this->get_to_be_installed_device($region_table, $v[$group_field]));
            //申请中暂时为0
            $v['Application_device_count']   = 0;

            //归属地昵称
            if ($region_table == 'business_hall') {
                $v['region_name'] = business_hall_helper::get_info_name($region_table, $v[$group_field], 'title');
            } else {
                $v['region_name'] = business_hall_helper::get_info_name($region_table, $v[$group_field], 'name');
            }

            if (!$v['region_name']) {
                unset($business_hall_list[$k]);
                continue;
            }

            if ($order_dir && $order_field) {
                $orders[] = $v[$order_field];
            }

            $business_hall_list[$k] = $v;
        }

        if ($orders) {
            if ($order_dir == 'asc') {
                array_multisort($orders, SORT_ASC, $business_hall_list);
            } else {
                array_multisort($orders, SORT_DESC, $business_hall_list);
            }
        }

        Response::assign('order_dir', $order_dir);
        Response::assign('order_field', $order_field);
        return $business_hall_list;

    }

    /**
     * 获取待安装设备
     * @param unknown $res_name
     * @param unknown $res_id
     */

    private function get_to_be_installed_device($res_name, $res_id)
    {
        return array();
    }

    /**
     * 获取已覆盖门店
     * @param unknown $res_name_field
     * @param unknown $res_id
     */
    private function get_covered_business_hall($res_name, $res_id, $page = 0)
    {
        $field = $res_name.'_id';

        if ($res_name == 'business_hall') {
            $field = 'business_id';
        }

        //查询本归属地内已覆盖门店量
        $filter = array($field => $res_id);

        //搜索日期
        if ($this->date_type == 1) {
            $filter['date'] = date('Ymd', strtotime($this->date));
        } else {
            $filter['date >='] = date('Ym01', strtotime($this->date));
            $filter['date <'] = date('Ym01', strtotime('+1 month', strtotime($this->date)));
        }
        $list = _model('probe_device_status_stat_day')->getFields('business_id', $filter, ' GROUP BY `business_id` ');
        return $list;
    }

    /**
     * 获取搜索条件
     */
    private function get_search_filter()
    {

    }

    /**
     * 获取并处理未覆盖门店
     * @param unknown $covered_business_hall 已覆盖门店
     */
    private function get_not_cover_business_hall($res_name, $res_id, $covered_business_hall)
    {

        if (in_array($res_name, array('province', 'city', 'area'))) {
            $filter = array($res_name.'_id' => $res_id, 'type >' => 3);
        } else if ($res_name == 'business_hall') {
            $filter = array('id' => $res_id);
        } else {
            $filter = array('type >' => 3);
        }
        //获取所有门店
        $business_hall_list = _model('business_hall')->getList($filter);
        $not_cover_business_hall = array();

        //筛选未覆盖的门店
        foreach ($business_hall_list as $k => $v) {
            if (array_search($v['id'], $covered_business_hall) == false) {
                $not_cover_business_hall[] = $v['id'];
            }
        }

        return $not_cover_business_hall;
    }

    /**
     * 获取指定归属地下所有营业厅
     * @param unknown $res_name 归属地类型
     * @param unknown $res_id   归属地id
     */
    private function get_all_business_hall($res_name, $res_id)
    {

        //获取指定归属地的下级归属字段
        $filter = array();
        //集团
        if ($res_name == 'group') {
            $group_field = 'province_id';
            $region_table = 'province';
        //省
        } else if ($res_name == 'province') {
            $group_field = 'city_id';
            $region_table = 'city';
            $filter[$res_name.'_id'] = $res_id;
        //市
        } else if ($res_name == 'city') {
            $group_field = 'area_id';
            $region_table = 'area';
            $filter[$res_name.'_id'] = $res_id;
        //区
        } else if ($res_name == 'area') {
            $group_field = 'id';
            $region_table = 'business_hall';
            $filter[$res_name.'_id'] = $res_id;
        //厅
        } else {
            $group_field = 'id';
            $region_table = 'business_hall';
            $filter['id'] = $res_id;
        }

        $filter[$group_field.'>'] = 0;
        //大于3的为厅店
        $filter['type >'] = 3;

        //转换为sql
        $where = to_where_sql($filter);

        //查询总门店量
        $sql = " SELECT COUNT(*) as business_hall_count, {$group_field} FROM `business_hall` {$where} GROUP BY `{$group_field}` ";

        $business_hall_list = _model('business_hall')->getAll($sql);

        //下级字段，用于查看详情
        Response::assign('subordinate', $region_table);
        return array('business_hall_list' => $business_hall_list, 'group_field' => $group_field, 'region_table' => $region_table);
    }

    /**
     * 按归属地获取正常(活跃)的设备
     * @param unknown $res_name_field
     * @param unknown $res_id
     */
    private function get_normal_device($res_name, $res_id)
    {

        $normal_device      = array();
        $abnormal_device    = array();

        //拼接归属地相关字段
        $field = $res_name.'_id';
        if ($res_name == 'business_hall') {
            $field = 'business_id';
        }

        $filter             = array($field => $res_id, 'status' => 1);

        //搜索日期
        if ($this->date_type == 1) {
            $filter['date'] = date('Ymd', strtotime($this->date));
        } else {
            $filter['date >='] = date('Ym01', strtotime($this->date));
            $filter['date <'] = date('Ym01', strtotime('+1 month', strtotime($this->date)));
        }

        //查询正常设备
        $devices = _model('probe_device_status_stat_day')->getFields('device', $filter, ' GROUP BY `device`');

        $device_list = array();
        if ($devices) {
            foreach ( $devices as $k => $v ) {
                $device_list[] =  _model('probe_device')->read(array('device' => $v, 'status' => 1));
            }
        }
        return $device_list;
    }

    /**
     * 获取异常的设备 按月：
     * @param unknown $res_name_field
     * @param unknown $res_id
     */
    private function get_abnormal_device($res_name, $res_id, $normal_device)
    {
        $abnormal_device    = array();

        //拼接归属地相关字段
        $field = $res_name.'_id';
        if ($res_name == 'business_hall') {
            $field = 'business_id';
        }

        $filter             = array($field => $res_id, 'status' => 1);
        //查询所有设备
        $devices = _model('probe_device')->getFields('device', $filter, ' GROUP BY `device`');
        foreach ( $normal_device as $k => $v ) {
            $key = array_search($v['device'], $devices);
            if ($key != false) {
                unset($devices[$key]);
            }
        }
        $device_list = array();
        if ($devices) {
            foreach ( $devices as $k => $v ) {
                $device_list[] =  _model('probe_device')->read(array('device' => $v, 'status' => 1));
            }
        }
        return $device_list;
    }

    /**
     * 导出主页数据
     */
    private function export_index_data($data_list)
    {

        $data = array();
        foreach ($data_list as $k => $v) {
            $tmp = array(
                    'region_name'           => $v['region_name'],
                    'business_hall_count'   => $v['business_hall_count'],
                    'coverage_business_hall_count' => $v['coverage_business_hall_count'],
                    'normal_device_count'   => $v['normal_device_count'],
                    'abnormal_device_count' => $v['abnormal_device_count'],
                    'to_be_installed_device_count' => $v['to_be_installed_device_count'],
                    'not_covered_business_hall_count' => $v['not_covered_business_hall_count']
            );

            $data[] = $tmp;
        }

        $params['filename'] = '覆盖统计表';
        $params['data']     = $data;
        $params['head']     = array('归属地', '总门店量' ,'已覆盖门店量', '设备数（状态正常）',  '设备数（状态异常）', '设备数（待安装）', '未覆盖门店');
        Csv::getCvsObj($params)->export();
    }


    /**
     * 导出厅覆盖数据
     */
    private function export_business_hall_detail_data($data_list, $type)
    {

        $data = array();
        foreach ($data_list as $k => $v) {
            $tmp = array(
                    'region_name'           => $v['region_name'],
                    'value'                 => $v['value'],
            );

            $data[] = $tmp;
        }

        $params['filename'] = $type == 1 ? '已覆盖厅店' : '未覆盖厅店';
        $params['data']     = $data;
        $params['head']     = array('归属地', $params['filename']);
        Csv::getCvsObj($params)->export();
    }

    /**
     * 导出详情页数据
     */
    private function export_device_status_detail_data($data_list, $type, $res_name)
    {
        $data = array();
        foreach ($data_list as $k => $v) {
            $tmp = array(
                    'region_name'           => $v['region_name'],
                    'value'                 => $v['value'],
            );

            if (in_array($res_name, array('business_hall', 'area'))) {
                $dev_status = probe_dev_config::$dev_status[$v['status']];
                $tmp['status'] = empty($dev_status['status']) ? '未知' : $dev_status['status'];
            }

            $data[] = $tmp;
        }

        if ($type == 1) {
            $filename = '设备覆盖分布表（设备正常）';
        } else if ($type == 2) {
            $filename = '设备覆盖分布表（设备异常）';
        } else if ($type == 3) {
            $filename = '设备覆盖分布表（设备待安装）';
        } else if ($type == 7) {
            $filename = '设备覆盖分布表（设备有效月活）';
        }
        $params['filename'] = $filename;
        $params['data']     = $data;
        $params['head']     = array('归属地');

        if (in_array($res_name, array('business_hall', 'area'))) {
            $params['head'][]     = '设备';
            $params['head'][]     = '设备状态';
        } else {
            if ($type == 1) {
                $params['head'][]     = '设备数（正常）';
            } else if ($type == 2) {
                $params['head'][]     = '设备数（异常）';
            } else if ($type == 3) {
                $params['head'][]     = '设备数（待安装）';
            }else if ($type == 7) {
                $params['head'][]     = '设备数（有效月活）';
            }
        }
        Csv::getCvsObj($params)->export();
    }
}