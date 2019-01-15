<?php
/**
  * alltosun.com rfid移动端统计 stat.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年7月27日 上午11:15:29 $
  * $Id$
  */
class Action
{
    public $member_info;
    public $member_res_id;
    public $member_res_name;
    public $member_id;
    public $interval   = 60;

    //饼图颜色
    public static $echarts_pie_colors = array(
            'rgb(72, 141, 224)',
            'rgb(247, 152, 87)',
            'rgb(204, 204, 204)',
            'rgb(244, 199, 98)',
            'rgb(125, 119, 225)'
    );

    public function __construct()
    {
        //登录信息
        $this->member_id   = member_helper::get_member_id();

        if ($this->member_id) {
            $member_info = member_helper::get_member_info($this->member_id);
            if ($member_info) {
                $this->member_info     = $member_info;
                $this->member_res_name = $member_info['res_name'];
                $this->member_res_id   = $member_info['res_id'];

                Response::assign('member_info', $this->member_info);
            }
        }

    }

    public function __call($action='', $params=array())
    {
        if (!$this->member_id) {
            return '请先登录';
        }

        //暂时不开放非营业厅管理员权限
        if ($this->member_res_name != 'business_hall') {
            return '您无此权限';
        }

        $search_filter = tools_helper::Get('search_filter', array());

        if (!isset($search_filter['data_type']) || !in_array($search_filter['data_type'], array(1, 2))) {
            $search_filter['data_type'] = $data_type =  1;
        } else {
            $data_type = $search_filter['data_type'];
        }

        $filter = $this->search_filter_date($search_filter);

        $where = rfid_helper::to_where_sql($filter);

        if ($data_type == 1) {
            $field = 'experience_time';
            $sql = "SELECT *, SUM(experience_time) AS {$field} FROM rfid_record {$where} GROUP BY `business_id`, `label_id` ORDER BY {$field} DESC";
            $device_list = _model('rfid_record')->getList($filter);
            $list = _model('rfid_record')->getAll($sql);
        } else {
            $field = 'action_num';
            $list = $this->get_action_data($filter);
        }

        //处理为图表所需数据以及格式
        $i              = 0; //取前五条
        $echarts_data   = array();
        foreach ($list as $k => $v) {
            if ($i > 4) {
                break;
            }
            $echarts_data[] = array(
                    'value' => $v[$field],
                    'name' => $v['phone_name'].'&nbsp'.$v['phone_version'],
                    'itemStyle' => array(
                            'normal' => array(
                                    'color' => self::$echarts_pie_colors[$i]
                            )
                    )
            );

            $i++;

        }
//         var_dump($search_filter);exit;
        $echarts_data_json = json_encode($echarts_data);
        Response::assign('echarts_pie_colors', self::$echarts_pie_colors);
        Response::assign('field', $field);
        Response::assign('echarts_data_json', $echarts_data_json);
        Response::assign('echarts_data', $echarts_data);
        Response::assign('search_filter', $search_filter);
        Response::assign('device_list', $list);
        Response::display('admin/rfid/stat/index2.html');
    }

    public function get_action_data($filter)
    {
        $filter['status'] = 1;
        $filter['end_timestamp >'] =  0;
        $where = rfid_helper::to_where_sql($filter);

        $sql = "SELECT *, count(*) AS action_num FROM rfid_record_detail {$where} GROUP BY `business_id`, `label_id` ORDER BY action_num DESC";

        $list = _model('rfid_record_detail')->getAll($sql);

        return $list;
    }

    /**
     * 客流量
     */
    public function flow()
    {
        if (!$this->member_id) {
            return '请先登录';
        }

        //暂时不开放非营业厅管理员权限
        if ($this->member_res_name != 'business_hall') {
            return '您无此权限';
        }

        //日期条件
        $filter         = $this->search_filter_date();

        //设备条件
        $device_info    = $this->search_filter_device();

        if (is_string($device_info)) {
            return $device_info;
        }

        $filter = array_merge($filter, $device_info);

        $end_timestamp_list = _model('rfid_record_detail')->getFields('end_timestamp', $filter, ' ORDER BY `start_timestamp` DESC');

        $mac_list = array();
        foreach ($end_timestamp_list as $k => $v) {
            $probe_filter = array(
                    'up_time <=' => $v,
                    'up_time >' => strtotime(date('Y-m-d H:i', $v)) - $this->interval
            );
            $mac_list[] = _model('rfid_probe_user_record')->getList($probe_filter, ' GROUP BY `mac`');
        }

        $new_mac_list = array();
        foreach ($mac_list as $k => $v) {
            foreach ($v as $key => $value) {
                if (isset($new_mac_list[$value['mac']])) {
                    continue;
                }

                $new_mac_list[$value['mac']] = $value;
            }

        }
        Response::assign('flow_list', $new_mac_list);
        Response::display('admin/rfid/stat/flow.html');
    }


    /**
     * 详情
     * @return string
     */
    public function stat_detail()
    {

        $from_type      = tools_helper::Get('from_type', '');

        if (!$this->member_id) {
            return '请先登录';
        }

        //暂时不开放非营业厅管理员权限
        if ($this->member_res_name != 'business_hall') {
            return '您无此权限';
        }

        $label_info = array();

        //单点登录
        if ($from_type == 'sso_login') {

            $from       = tools_helper::Get('from', 'dm_detail');

            $filter = $this->search_filter_by_sso_login();

            if (isset($filter['id']) && $filter['id'] == 0) {
                return '管理员无权限或者参数不全';
            }

            if ($from == 'dm_detail' && !isset($filter['label_id'])) {
                return '请传递标签信息';
            }

            //近七天排行数据的详情
            if ($from == 'dm_near_seven') {
                $label_info['phone_name']           = tools_helper::Get('phone_name', '');
                $label_info['phone_version']        = tools_helper::Get('phone_version', '');

                if (!$label_info['phone_name'] || !$label_info['phone_version']) {
                    return '缺少机型信息';
                }

                $filter['phone_name'] = $label_info['phone_name'];
                $filter['phone_version'] = $label_info['phone_version'];

            //数字地图查看详情的单点登录
            } else {

                $label_info = _uri('rfid_label', array('label_id' => $filter['label_id']));

                if (!$label_info) {
                    return '本厅不存在此设备';
                }

                $label_info['phone_name'] = $label_info['name'];
                $label_info['phone_version'] = $label_info['version'];
                $label_info['phone_color'] = $label_info['color'];

            }

        } else {
            //日期条件
            $filter = $this->search_filter_date();

            //设备条件
            $label_info = $this->search_filter_device();

            if (is_string($label_info)) {
                return $label_info;
            }

            $filter = array_merge($filter, $label_info);
        }

        $filter['status'] = 1;

        $detail_list    = _model('rfid_record_detail')->getList($filter);
        $time_count     = 0;


        foreach ($detail_list as $k => $v) {
            $time_count += $v['remain_time'];
        }

        Response::assign('label_info', $label_info);
        Response::assign('detail_list', $detail_list);
        Response::assign('time_count', $time_count);
        Response::display('admin/rfid/stat/stat_detail.html');

    }

    /**
     * 客流量详情
     */
    public function flow_detail()
    {
        if (!$this->member_id) {
            return '请先登录';
        }

        //暂时不开放非营业厅管理员权限
        if ($this->member_res_name != 'business_hall') {
            return '您无此权限';
        }

        $id = tools_helper::Get('detail_id', 0);

        if (!$id) {
            return '缺少详情信息';
        }

        $detail_info = _uri('rfid_record_detail', $id);

        if (!$detail_info) {
            return '没有此RFID的详情记录';
        }

        $filter = array(
                'up_time <=' => $detail_info['end_timestamp'],
                'up_time >' => strtotime(date('Y-m-d H:i', $detail_info['end_timestamp'])) - $this->interval
        );

        $flow_list = _model('rfid_probe_user_record')->getList($filter, ' GROUP BY `mac`');

        Response::assign('flow_list', $flow_list);
        Response::display('admin/rfid/stat/flow.html');
    }

    /**
     * 日期信息搜索条件
     */
    public function search_filter_date($search_filter=array())
    {

        if (!$search_filter) {
            $search_filter  = tools_helper::get('search_filter', array());
        }

        $start_date     = '';
        $end_date       = '';
        $date_title     = '';

        //根据管理员获取默认筛选条件
        $filter         = _widget('rfid')->default_search_filter($this->member_info);

        if (isset($search_filter['start_date']) && $search_filter['start_date']) {
            $start_date = $search_filter['start_date'];
        }

        if (isset($search_filter['end_date']) && $search_filter['end_date']) {
            $end_date = $search_filter['end_date'];
        }

        //指定时间
        if ($start_date && $end_date) {
            $filter['date >=']  = str_replace('-', '', $start_date);
            $filter['date <=']  = str_replace('-', '', $end_date);
            $date_title         = str_replace('-', '/', $start_date).'-'.str_replace('-', '/', $end_date);

            //按指定时间段类型搜索
        } else if (isset($search_filter['date_type']) && $search_filter['date_type']) {
            if ($search_filter['date_type'] == 1) {
                //今天
                $filter['date'] = date('Ymd');
                $date_title     = date('Y年m月d日');
            } else if ($search_filter['date_type'] == 2) {
                //近七日
                $filter['date >']   = date('Ymd', strtotime('-7 days'));
                $filter['date <=']  = date('Ymd');
                $date_title         = date('Y/m/d', strtotime('-7 days')).'-'.date('Y/m/d');;
            } else if ($search_filter['date_type'] == 3) {
                //随意一段时间
                $filter['date'] = date('Ymd');
                $date_title     = date('Y年m月d日');
            } else {
                //默认
                $filter['date'] = date('Ymd');
                $search_filter['date_type'] = 1;
                $date_title     = date('Y年m月d日');
            }
        } else {
            //默认
            $filter['date'] = date('Ymd');
            $search_filter['date_type'] = 1;
            $date_title     = date('Y年m月d日');
        }

        $search_str = '';
        //子页面所需条件
        foreach ($search_filter as $k => $v) {
            if (!$search_str) {
                $search_str = '?';
            } else {
                $search_str .= '&';
            }

            $search_str .= "search_filter[{$k}]={$v}";
        }


        Response::assign('date_title', $date_title);
        Response::assign('search_filter', $search_filter);
        Response::assign('search_str', $search_str);

        return $filter;

    }

    /**
     *设备信息搜索条件
     */
    public function search_filter_device()
    {

        $phone_name         = tools_helper::Get('phone_name', '');
        $phone_version      = tools_helper::Get('phone_version', '');
        $phone_color        = tools_helper::Get('phone_color', '');
        $label_id           = tools_helper::Get('label_id', '');
        $phone_filter       = array();

        if (!$phone_name) {
            return '缺少手机品牌';
        }

        if (!$phone_version) {
            return '缺少手机型号';
        }

        if (!$phone_color) {
            return '缺少手机颜色';
        }

        if (!$label_id) {
            return '缺少标签id';
        }

        $filter = array(
                'phone_name'    => $phone_name,
                'phone_version' => $phone_version,
                'phone_color'   => $phone_color,
                'label_id'      => $label_id
        );

        return $filter;

    }

    /**
     * 根据单点登录拼接搜索条件
     */
    public function search_filter_by_sso_login()
    {
        $from       = tools_helper::Get('from', 'dm_detail');
        //根据管理员获取默认筛选条件
        $filter         = _widget('rfid')->default_search_filter($this->member_info);

        if ($from == 'dm_detail') {
            $business_id    = tools_helper::Get('business_id', 0);
            $label_id       = tools_helper::Get('label_id', '');

            if ($business_id && $this->member_info['res_name'] != 'business_hall') {
                //如果登录的非厅管理员，则以business_id 为准
                $filter['business_id'] = $business_id;
            }

            if ($label_id) {
                $filter['label_id'] = $label_id;
            }

            $filter['date'] = date('Ymd');
            $date_title = date('Y年m月d日');

        } else if ($from == 'dm_near_seven') {

            if ($this->member_info['res_name'] != 'business_hall') {
                return array('id' => 0);
            }

            $filter['date >='] = date('Ymd', strtotime('-6 days'));
            $filter['date <='] = date('Ymd');
            $date_title = date('Y年m月d日', strtotime('-6 days')).'-'.date('Y年m月d日');
        } else {
            $filter['id'] = 0;
        }

        Response::assign('date_title', $date_title);
        return $filter;
    }


}