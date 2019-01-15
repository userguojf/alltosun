<?php
/**
  * alltosun.com 覆盖量统计首页 index.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年12月14日 下午12:36:40 $
  * $Id$
  */
class Action
{
    private $member_id;
    private $member_info;
    private $subordinate_res_name; //当前归属的下级
    private $subordinate_res_id_field;  //当前归属的下级id字段
    private $search_region;             //搜索的归属地信息
    private $per_page = 20;
    private $start_date;
    private $end_date;
    private $is_today;
    private $if_export;
    private $search_filter;
    public function __construct()
    {

        $this->member_id   = member_helper::get_member_id();
        $this->member_info = member_helper::get_member_info($this->member_id);

        $this->init_search();

        //今日
        if ($this->start_date == $this->end_date && $this->start_date == date('Y-m-d')) {
            $this->is_today = true;
        } else {
            $this->is_today = false;
        }

        $this->if_export = tools_helper::Get('if_export', 0);

        Response::assign('is_today', $this->is_today);
        Response::assign('member_info', $this->member_info);
    }

    /**
     * screen覆盖量统计首页
     * @param string $action
     * @param array $params
     */
    public function __call($action='', $params=array())
    {
        if ($this->member_info['res_name'] == 'area') {
            return '此功能暂不支持区';
        }
        //初始化设置下级
        $this->set_subordinate($this->member_info['res_name'], $this->member_info['res_id']);
        //按照下级分组
        $region = $this->group_by_subordinate();

        $s_cover_business_hall_count = 0;
        $s_install_device_count = 0;
        $s_new_cover_business_hall_count = 0;
        $s_new_device_count = 0;
        $s_active_device_count = 0;
        $s_online_device_count = 0;
        $s_drop_off_device_count = 0;
        $s_experience_time_count = 0;
        $s_active_business_hall_count = 0;
        $data_list = array();
        $first_list = array();

        $this->get_echart();
//exit;
        foreach ($region as $k => $v)
        {
            if ($this->subordinate_res_name == 'business_hall') {
                $name_field = 'title';
            } else {
                $name_field = 'name';
            }

            $region_name = business_hall_helper::get_info_name($this->subordinate_res_name, $v, $name_field);
            if (!$region_name) {
                continue;
            }

            //获取新增营业厅量(新增门店量)
            $new_cover_business_hall = $this->get_new_cover_business_hall($this->subordinate_res_name, $v);
            $new_cover_business_hall_count = count($new_cover_business_hall);

            //获取新增设备量
            $new_devices = $this->get_new_device($this->subordinate_res_name, $v);
            $new_device_count = count($new_devices);

            //获取活跃门店量
            $active_business_halls = $this->get_active_business_hall($this->subordinate_res_name, $v);
            $active_business_halls_count = count($active_business_halls);

            //获取活跃设备量
            $active_devices = $this->get_active_device($this->subordinate_res_name, $v);
            $active_device_count = count($active_devices);

            //获取营业厅总覆盖量(门店总安装量)
            $business_ids = $this->get_cover_business_hall($this->subordinate_res_name, $v);
            $cover_business_hall_count = count($business_ids);

            //获取设备总安装量(设备总安装量)
            $install_devices = $this->get_install_device($this->subordinate_res_name, $v);
            $install_device_count = count($install_devices);

            //获取下柜数量
            $drop_off_devices       = $this->get_drop_off_device($this->subordinate_res_name, $v);
            $drop_off_device_count  = count($drop_off_devices);

            $data_list[] = array(
                    'region_name'               => $region_name,
                    'cover_business_hall_count' => $cover_business_hall_count,
                    'install_device_count'      => $install_device_count,
                    'new_cover_business_hall_count' => $new_cover_business_hall_count,
                    'new_device_count'          => $new_device_count,
                    'active_device_count'       => $active_device_count,
                    'drop_off_device_count'     => $drop_off_device_count,
                    'active_business_hall_count' => $active_business_halls_count,
                    $this->subordinate_res_id_field => $v,
            );

            $s_cover_business_hall_count    += $cover_business_hall_count;
            $s_install_device_count         += $install_device_count;
            $s_new_cover_business_hall_count += $new_cover_business_hall_count;
            $s_new_device_count             += $new_device_count;
            $s_active_device_count          += $active_device_count;
            $s_drop_off_device_count        += $drop_off_device_count;
            $s_active_business_hall_count   += $active_business_halls_count;
        }

        if ($this->member_info['res_name'] == 'group') {
            $region_name ='全国';
        } else if ($this->member_info['res_name'] == 'province') {
            $region_name ='全省';
        } else if ($this->member_info['res_name'] == 'city') {
            $region_name ='全市';
        } else if ($this->member_info['res_name'] == 'area') {
            $region_name ='全区';
        } else {
            $region_name ='全厅';
        }

        $first_list = array(
                'region_name'               => $region_name,
                'cover_business_hall_count' => $s_cover_business_hall_count,
                'install_device_count'      => $s_install_device_count,
                'new_cover_business_hall_count' => $s_new_cover_business_hall_count,
                'new_device_count'          => $s_new_device_count,
                'active_device_count'       => $s_active_device_count,
                'active_business_hall_count' => $s_active_business_hall_count,
                'drop_off_device_count'     => $s_drop_off_device_count,
        );
        array_unshift($data_list, $first_list);
        //p($data_list);exit;

        Response::assign('data_list', $data_list);
        Response::display('admin/bestrow_stat/index.html');
    }

    public function get_echart()
    {

        $region = $this->group_by_subordinate();

        foreach ($region as $k => $v)
        {
            if ($this->subordinate_res_name == 'business_hall') {
                $name_field = 'title';
            } else {
                $name_field = 'name';
            }



            $region_name = business_hall_helper::get_info_name($this->subordinate_res_name, $v, $name_field);
            if (!$region_name) {
                continue;
            }

            //获取新增设备量)
            $new_device =   count($this->get_new_device($this->subordinate_res_name, $v));
            //获取设备总安装量
            $devices_num =  count($this->get_install_device($this->subordinate_res_name, $v));
            //获取1-3天和4-7天活跃的设备量
            $this->get_active_device_by_range($this->subordinate_res_name, $v, array(1=>3, 4=>7));

            //$new_cover_business_hall_count = count($new_cover_business_hall);
        }

//         Response::assign('echart_data', json_encode($echart_data));
//         Response::display('admin/bestrow_stat/echart.html');
    }

    /**
     * 处理小时的图表数据
     */
    private function handle_hour_echart_data($res_name, $res_id)
    {
        //获取默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);
        unset($filter['status']);

        $start = strtotime(date("Y-m-d 00:00:00", strtotime($this->start_date)));
        $end = strtotime(date("Y-m-d 23:59:59", strtotime($this->end_date))) + 1;

        $hour_arr = array();
        do{
            $hour_arr[date('H:00', $start)] = array('start' => date('Y-m-d H:i:s', $start), 'end' => date('Y-m-d H:i:s', $start + 3600));
            $start += 3600;
        }while($start < $end);

        $new_data = array();

        $dates      = array();
        $active_nums = array();
        $exper_times = array();

        foreach ($hour_arr as $k => $v) {
            //查询活跃设备
            $active_filter = $filter;
            $active_filter['add_time >=']   = $v['start'];
            $active_filter['add_time <']    = $v['end'];
            //$active_filter['device_unique_id'] = $unique_device;

            $result       = _mongo('screen', 'screen_device_online')->aggregate(array(
                    array('$match' => get_mongodb_filter($active_filter)),
                    array('$group' => array(
                            '_id'               => array(
                                    'device_unique_id'  => '$device_unique_id',
                            ),
                        ),
                    ),
                )
            );

            $result = $result->toArray();
            $active_num = count($result);
            //查询体验时长
            $experience_filter = $active_filter;
            $experience_filter['type'] = 2;
            $exper_time = _mongo('screen', 'screen_action_record')->find(get_mongodb_filter($experience_filter), array('projection'=>array('experience_time'=>1)));
            $time = 0;
            foreach ($exper_time as $vv) {
                $time += $vv['experience_time'];
            }

            $dates[]          = $k;
            $active_nums[]     = $active_num;

            if ($time) {
                $time = round($time/60, 2);
            }

            $exper_times[]     = $time;
        }

        return array('dates' => $dates, 'active_nums' => $active_nums, 'exper_times' => $exper_times);
    }

    /**
     * 处理图表数据
     */
    private function handle_echart_data($res_name, $res_id)
    {
        //获取默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);
        unset($filter['status']);

        $start = strtotime(date("Y-m-d 00:00:00", strtotime($this->start_date)));
        $end = strtotime(date("Y-m-d 23:59:59", strtotime($this->end_date))) + 1;

        $hour_arr = array();
        do{
            $date_arr[date('Ymd', $start)] = array('start' => date('Ymd', $start), 'end' => date('Ymd', $start + 3600*24));
            $start += 3600*24;
        }while($start < $end);

        $dates      = array();
        $active_nums = array();
        $exper_times = array();
        foreach ($date_arr as $k => $v) {
            //查询活跃设备
            $active_filter = $filter;
            $active_filter['day >=']   = $v['start'];
            $active_filter['day <']    = $v['end'];

            $devices = _model('screen_device_online_stat_day')->getFields('device_unique_id', $active_filter, ' GROUP BY `device_unique_id`');

            $active_num = count($devices);
            //查询体验时长
            $experience_filter  = $active_filter;
            $experience_times   = _model('screen_business_stat_day')->getFields('experience_time', $experience_filter);
            $time               = array_sum($experience_times);

            $dates[]          = $k;
            $active_nums[]     = $active_num;
            if ($time > 0) {
                $time = round($time / 60, 2);
            }

            $exper_times[]     = $time;
        }
        return array('dates' => $dates, 'active_nums' => $active_nums, 'exper_times' => $exper_times);
    }

    /**
     * 导出可安装厅店列表
     */
    public function export_can_install_business_hall()
    {
        //查询本归属下精品门店
    }

    /**
     * 营业厅总覆盖量
     */
    public function business_hall_cover()
    {
        $res_name   = tools_helper::Get('res_name', '');
        $res_id     = tools_helper::Get('res_id', 0);
        $page       = tools_helper::Get('page_no', 1);
        $order_field    = tools_helper::Get('order_field', 'install_device_count');
        $order_dir      = tools_helper::Get('order_dir', 'desc');

        //查询营业厅覆盖量
        $business_ids = $this->get_cover_business_hall($res_name, $res_id);

        $data_list = array();

        $tmp_data_list = array();
        $sorts         = array();
        //排序
        foreach ( $business_ids as $business_id ) {
            if ($order_field == 'online_device_count') {
                $tmp = array(
                    $order_field => count($this->get_online_device('business_hall', $business_id)),
                    'business_id'         => $business_id,
                );
            } else if ($order_field == 'active_device_count'){
                $tmp = array(
                        $order_field => count($this->get_active_device('business_hall', $business_id)),
                        'business_id'         => $business_id,
                );
            } else if ($order_field == 'experience_time_count') {
                $tmp = array(
                        $order_field => array_sum($this->get_device_experience_by_field('business_hall', $business_id, 'experience_time')),
                        'business_id'         => $business_id,
                );
            //默认
            } else {
                $order_field = 'install_device_count';
                $tmp = array(
                        $order_field => count($this->get_install_device('business_hall', $business_id)),
                        'business_id'         => $business_id,
                );
            }

            $tmp_data_list[] = $tmp;

            $sorts[] = $tmp[$order_field];
        }

        //排序
        if ($tmp_data_list) {
            if ($order_dir == 'desc') {
                array_multisort($sorts, SORT_DESC, $tmp_data_list);
            } else {
                array_multisort($sorts, SORT_ASC, $tmp_data_list);
            }
        }

        //分页
        $count = count($tmp_data_list);
        $new_data = array();
        if ($count && !$this->if_export) {
            $pager = new Pager($this->per_page);

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }

            $limit_start = ($page - 1) * $this->per_page;
            $new_data = array_slice($tmp_data_list, $limit_start, $this->per_page);
        } else {
            $new_data = $tmp_data_list;
        }

        $online_device_count    = 0;
        $install_device_count   = 0;
        $active_device_count    = 0;
        $experience_time_count  = 0;

        foreach ( $new_data as $k => $v ) {
            $business_id    = $v['business_id'];
            $business_info  = business_hall_helper::get_business_hall_info($business_id);
            //查询厅
            $v['province'] = business_hall_helper::get_info_name('province', $business_info['province_id'], 'name');
            //查询市
            $v['city'] = business_hall_helper::get_info_name('city', $business_info['city_id'], 'name');
            //查询区
            $v['area'] = business_hall_helper::get_info_name('area', $business_info['area_id'], 'name');
            //查询厅
            $v['business_name'] = business_hall_helper::get_info_name('business_hall', $business_info['id'], 'title');
            //用户渠道编码
            $v['user_number'] = $business_info['user_number'];

            //获取在线设备
            if ($this->member_info['res_name'] == 'group' && $this->is_today) {
                if (!isset($v['online_device_count'])) {
                    $v['online_device_count'] = count($this->get_online_device('business_hall', $business_id));
                }
            } else {
                $v['online_device_count'] = 0;
            }
            $online_device_count += $v['online_device_count'];

            //获取设备量
            if (!isset($v['install_device_count'])) {
                $v['install_device_count'] = count($this->get_install_device('business_hall', $business_id));
            }
            $install_device_count += $v['install_device_count'];

            //获取活跃数量
            if (!isset($v['active_device_count'])) {
                $v['active_device_count'] = count($this->get_active_device('business_hall', $business_id));
            }
            $active_device_count += $v['active_device_count'];

            //获取体验时长
            if ($this->member_info['res_name'] == 'group') {
                if (!isset($v['experience_time_count'])) {
                    $v['experience_time_count'] = array_sum($this->get_device_experience_by_field('business_hall', $business_id, 'experience_time'));
                }
            } else {
                $v['experience_time_count'] = 0;
            }

            $experience_time_count += $v['experience_time_count'];
            $data_list[] = $v;
        }

        if ($this->if_export == 1) {
            $this->export_business_hall_cover($data_list);
        }

        Response::assign('count', $count);
        Response::assign('data_list', $data_list);
        Response::assign('res_name', $res_name);
        Response::assign('res_id', $res_id);
        Response::assign('order_field', $order_field);
        Response::assign('order_dir', $order_dir);
        Response::assign('online_device_count', $online_device_count);
        Response::assign('install_device_count', $install_device_count);
        Response::assign('active_device_count', $active_device_count);
        Response::assign('experience_time_count', $experience_time_count);
        Response::display('admin/bestrow_stat/business_hall_cover.html');
    }

    /**
     * 导出覆盖营业厅列表
     * @param unknown $list
     */
    public function export_business_hall_cover ($list)
    {
        if ( !$list ) {
            return false;
        }

        $data = array();

        foreach ($list as $k => $v) {
            $tmp = array(
                    'province' => $v['province'],
                    'city'      => $v['city'],
                    'area'      => $v['area'],
                    'business_name' => $v['business_name'],
                    'user_number'   => $v['user_number'],
                    'install_device_count' => $v['install_device_count'],
                    'active_device_count'  => $v['active_device_count'],
            );

            if ( $this->member_info['res_name'] == 'group' ) {
                $tmp['experience_time_count'] = round($v['experience_time_count'] / 60, 1);
            }

            $data[] = $tmp;
        }

        $params = array();
        $params['filename'] = '营业厅覆盖统计';
        $params['data']     = $data;
        $params['head']     = array('所属省', '所属市', '所属区县', '营业厅名称', '渠道编码', '设备总数', '活跃设备');

        if ( $this->member_info['res_name'] == 'group' ) {
            $params['head'][] = '体验时长/分钟';
        }

        Csv::getCvsObj($params)->export();
    }

    /**
     * 营业厅新增覆盖量
     */
    public function new_cover_business_hall()
    {
        $res_name   = tools_helper::Get('res_name', '');
        $res_id     = tools_helper::Get('res_id', 0);
        $page       = tools_helper::Get('page_no', 1);
        $order_field    = tools_helper::Get('order_field', '');
        $order_dir      = tools_helper::Get('order_dir', 'desc');

        //查询营业厅新增覆盖量
        $business_ids = $this->get_new_cover_business_hall($res_name, $res_id);

        $data_list = array();

        $tmp_data_list = array();
        $sorts         = array();
        //排序
        foreach ( $business_ids as $business_id ) {
            $tmp = array('business_id' => $business_id);
            if ($order_field == 'experience_time_count') {
                $sorts[] = $tmp['experience_time_count'] = array_sum($this->get_device_experience_by_field('business_hall', $business_id, 'experience_time'));
            }
            $tmp_data_list[]      = $tmp;
        }

        //排序
        if ($sorts) {
            if ($order_dir == 'desc') {
                array_multisort($sorts, SORT_DESC, $tmp_data_list);
            } else {
                array_multisort($sorts, SORT_ASC, $tmp_data_list);
            }
        }

        //分页
        $count = count($tmp_data_list);
        $new_data = array();
        if ($count && !$this->if_export) {
            $pager = new Pager($this->per_page);

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }

            $limit_start = ($page - 1) * $this->per_page;
            $new_data = array_slice($tmp_data_list, $limit_start, $this->per_page);
        } else {
            $new_data = $tmp_data_list;
        }

        $online_device_count    = 0;
        $install_device_count   = 0;
        $active_device_count    = 0;
        $experience_time_count  = 0;

        foreach ( $new_data as $k => $v ) {
            $business_id    = $v['business_id'];
            $business_info  = business_hall_helper::get_business_hall_info($business_id);
            //查询省
            $v['province'] = business_hall_helper::get_info_name('province', $business_info['province_id'], 'name');
            //查询市
            $v['city'] = business_hall_helper::get_info_name('city', $business_info['city_id'], 'name');
            //营业厅名称
            $v['business_name'] = $business_info['title'];

            //获取体验时长
            if ($this->member_info['res_name'] == 'group') {
                if (!isset($v['experience_time_count'])) {
                    $v['experience_time_count'] = array_sum($this->get_device_experience_by_field('business_hall', $business_id, 'experience_time'));
                }
            } else {
                $v['experience_time_count'] = 0;
            }

            //获取活跃数量
            if (!isset($v['active_device_count'])) {
                $v['active_device_count'] = count($this->get_active_device('business_hall', $business_id));
            }

            //获取新增设备
            if (!isset($v['new_device_count'])) {
                $v['new_device_count'] = count($this->get_new_device('business_hall', $business_id));
            }

            $data_list[] = $v;
        }

        if ($this->if_export == 1) {
            $this->export_new_cover_business_hall($data_list);
        }

        Response::assign('count', $count);
        Response::assign('res_name', $res_name);
        Response::assign('res_id', $res_id);
        Response::assign('order_field', $order_field);
        Response::assign('order_dir', $order_dir);
        Response::assign('data_list', $data_list);
        Response::display('admin/bestrow_stat/new_cover_business_hall.html');
    }

    /**
     * 导出新增覆盖营业厅
     * @param unknown $list
     */
    public function export_new_cover_business_hall ($list)
    {
        if ( !$list ) {
            return false;
        }

        $data = array();

        foreach ($list as $k => $v) {
            $tmp = array(
                    'province' => $v['province'],
                    'city'      => $v['city'],
                    'business_name' => $v['business_name'],
                    'active_device_count'  => $v['active_device_count'],
                    'new_device_count'  => $v['new_device_count'],
            );

            if ( $this->member_info['res_name'] == 'group') {
                $tmp['experience_time_count'] = round($v['experience_time_count'] / 60, 1);
            }

            $data[] = $tmp;
        }

        $params = array();
        $params['filename'] = '新覆盖营业厅列表';
        $params['data']     = $data;
        $params['head']     = array('所属省', '所属市', '营业厅名称', '活跃设备', '新增设备');

        if ( $this->member_info['res_name'] == 'group') {
            $params['head'][] = '体验时长/分钟';
        }

        Csv::getCvsObj($params)->export();
    }

    /**
     * 设备列表
     */
    public function device_list()
    {
        $res_name   = tools_helper::Get('res_name', '');
        $res_id     = tools_helper::Get('res_id', 0);
        $page       = tools_helper::Get('page_no', 1);
        $order_field    = tools_helper::Get('order_field', '');
        $order_dir      = tools_helper::Get('order_dir', 'desc');


        //查询总安装设备列表
        $devices = $this->get_install_device($res_name, $res_id);

        //查询设备品牌型号分布(图表所用)
        $device_brand_distribute = $this->get_install_device_distribute($res_name, $res_id);
        Response::assign('device_brand_distribute', $device_brand_distribute);

        //获取所有品牌
        $phone_names = $this->get_phone_name($res_name, $res_id);
        Response::assign('phone_names', $phone_names);

        $data_list      = array();
        $tmp_data_list  = array();
        $sorts          = array();
        //排序
        foreach ( $devices as $device ) {
            $tmp = array('device_unique_id' => $device);
            if ($order_field == 'experience_time_count') {
                $sorts[] = $tmp['experience_time_count'] = array_sum($this->get_device_experience_by_field('business_hall', 0, 'experience_time', array($device)));
            }
            $tmp_data_list[]      = $tmp;
        }

        //排序
        if ($sorts && isset($tmp[$order_field])) {
            if ($order_dir == 'desc') {
                array_multisort($sorts, SORT_DESC, $tmp_data_list);
            } else {
                array_multisort($sorts, SORT_ASC, $tmp_data_list);
            }
        }

        //分页
        $count = count($tmp_data_list);
        $new_data = array();
        if ($count && !$this->if_export) {
            $pager = new Pager($this->per_page);

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
            $limit_start = ($page - 1) * $this->per_page;
            $new_data = array_slice($tmp_data_list, $limit_start, $this->per_page);
        } else {
            $new_data = $tmp_data_list;
        }

        //搜索的天数
        $search_days = count($this->get_search_days());

        foreach ( $new_data as $k => $v ) {
            $device_unique_id    = $v['device_unique_id'];
            $filter = $this->get_default_device_filter($res_name, $res_id);
            unset($filter['status']);
            $filter['device_unique_id'] = $device_unique_id;

            //下柜设备
            $device_info  = screen_device_helper::get_device_info($filter);
            //获取设备活跃天数
            $v['active_days'] = count($this->get_active_days($res_name, $res_id, $device_info));
            //离线天数
            $add_time2      = strtotime($device_info['add_time']);
            $start_date2    = strtotime($this->start_date);
            //设备添加时间大于搜索的的开始时间，则需要减去相差天数
            if ($add_time2 > $start_date2) {
                $v['offline_days'] = $search_days - ceil(($add_time2 - $start_date2)/(3600*24)) - $v['active_days'] + 1;
            } else {
                //获取设备离线天数
                $v['offline_days'] = $search_days - $v['active_days'] + 1;
            }

            if ($v['offline_days'] < 0) {
                $v['offline_days'] = 0;
            }
            //添加时间
            $v['add_time'] = $device_info['add_time'];
            //最后活跃时间
            $v['last_active_time'] = $this->get_device_last_active($device_info);
            //是否在线
            $v['is_online'] = $online_status = screen_helper::get_online_status($device_unique_id);
            //是否活跃
            $v['is_active'] = $this->get_device_is_active($device_unique_id);

            //品牌
            $v['phone_name'] = $device_info['phone_name_nickname'] ? $device_info['phone_name_nickname'] : $device_info['phone_name'];
            //型号
            $v['phone_version'] = $device_info['phone_version_nickname'] ? $device_info['phone_version_nickname'] : $device_info['phone_version'];
            //IMEI
            $v['imei'] = $device_info['imei'];
            //查询厅
            $v['business_name'] = business_hall_helper::get_info_name('business_hall', $device_info['business_id'], 'title');
            //用户渠道编码
            $v['user_number'] = business_hall_helper::get_info_name('business_hall', $device_info['business_id'], 'user_number');

            $data_list[] = $v;
        }

        if ($this->if_export == 1) {
            $this->export_device_list($data_list, $type);
        }

        Response::assign('count', $count);
        Response::assign('res_name', $res_name);
        Response::assign('res_id', $res_id);
        Response::assign('order_field', $order_field);
        Response::assign('order_dir', $order_dir);
        Response::assign('data_list', $data_list);
        Response::display('admin/bestrow_stat/device_list.html');
    }

    /**
     * 导出设备列表
     * @param unknown $list
     */
    public function export_device_list ($list, $type)
    {
        if ( !$list ) {
            return false;
        }

        $data = array();

        foreach ($list as $k => $v) {
            $tmp = array(
                    'province' => $v['province'],
                    'city'      => $v['city'],
                    'business_name' => $v['business_name'],
                    'user_number'   => $v['user_number'],
                    'phone_name'    => $v['phone_name'],
                    'phone_version' => $v['phone_version'],
                    'imei'          => $v['imei'],
                    'device_unique_id' => $v['device_unique_id'],
                    'is_online'     => $v['is_online'] ? '在线' : '离线',
                    'is_active'     => $v['is_active'] ? '活跃' : '不活跃',
            );

            if ( $this->member_info['res_name'] == 'group' ) {
                $tmp['experience_time_count'] = round($v['experience_time_count'] / 60, 1);
            }

            $data[] = $tmp;
        }

        $params = array();

        if ($type == 1) {
            $params['filename'] = '总安装设备列表';
        } else if ($type == 2) {
            $params['filename'] = '在线设备列表';
        } else if ($type == 3) {
            $params['filename'] = '新增设备列表';
        } else if ($type == 4) {
            $params['filename'] = '活跃设备列表';
        } else if ($type == 5) {
            $params['filename'] = '下柜设备列表';
        } else {
            return false;
        }

        $params['data']     = $data;
        $params['head']     = array('所属省', '所属市', '营业厅名称', '渠道编码', '品牌', '型号', 'IMEI', '设备标识', '今日是否在线', '是否活跃');

        if ( $this->member_info['res_name'] == 'group' ) {
            $params['head'][] = '体验时长/分钟';
        }

        Csv::getCvsObj($params)->export();
    }

    /**
     * 体验时长列表
     */
    public function experience_time_list()
    {
        $res_name   = tools_helper::Get('res_name', '');
        $res_id     = tools_helper::Get('res_id', 0);
        $page       = tools_helper::Get('page_no', 1);
        $order_field    = tools_helper::Get('order_field', '');
        $order_dir      = tools_helper::Get('order_dir', 'desc');


        //查询总安装设备列表
        $data = $this->get_device_experience_by_field($res_name, $res_id, 'experience_time');
        $data_list      = array();
        $tmp_data_list  = array();
        $sorts          = array();
        //排序
        foreach ( $data as $device => $experience_time_count) {
            $tmp = array('device_unique_id' => $device);
            if ($order_field == 'experience_time_count') {
                $sorts[] = $tmp['experience_time_count'] = $experience_time_count;
            }
            $tmp_data_list[]      = $tmp;
        }

        //排序
        if ($sorts && isset($tmp[$order_field])) {
            if ($order_dir == 'desc') {
                array_multisort($sorts, SORT_DESC, $tmp_data_list);
            } else {
                array_multisort($sorts, SORT_ASC, $tmp_data_list);
            }
        }

        //分页
        $count = count($tmp_data_list);
        $new_data = array();
        if ($count && !$this->if_export) {
            $pager = new Pager($this->per_page);

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }

            $limit_start = ($page - 1) * $this->per_page;
            $new_data = array_slice($tmp_data_list, $limit_start, $this->per_page);
        } else {
            $new_data = $tmp_data_list;
        }

        $search_days = count($this->get_search_days());
        $experience_time_counts = 0;
        foreach ( $new_data as $k => $v ) {
            $device_unique_id    = $v['device_unique_id'];
            $device_info  = screen_device_helper::get_device_info_by_device($device_unique_id);
            //查询厅
            $v['business_name'] = business_hall_helper::get_info_name('business_hall', $device_info['business_id'], 'title');
            //用户渠道编码
            $v['user_number'] = business_hall_helper::get_info_name('business_hall', $device_info['business_id'], 'user_number');
            //是否在线
            $v['is_online'] = $online_status = screen_helper::get_online_status($device_unique_id);
            //是否活跃
            $v['is_active'] = $this->get_device_is_active($device_unique_id);
            //品牌
            $v['phone_name'] = $device_info['phone_name_nickname'] ? $device_info['phone_name_nickname'] : $device_info['phone_name'];
            //型号
            $v['phone_version'] = $device_info['phone_version_nickname'] ? $device_info['phone_version_nickname'] : $device_info['phone_version'];
            //IMEI
            $v['imei'] = $device_info['imei'];
            //添加时间
            $v['add_time'] = $device_info['add_time'];
            //最后活跃时间
            $v['last_active_time'] = $this->get_device_last_active($device_info);
            //获取设备活跃天数
            $v['active_days'] = count($this->get_active_days($res_name, $res_id, $device_info));

            //离线天数 设备添加时间大于搜索的的开始时间，则需要减去相差天数
            $add_time2      = strtotime($device_info['add_time']);
            $start_date2    = strtotime($this->start_date);
            if ($add_time2 > $start_date2) {
                $v['offline_days'] = $search_days - ceil(($add_time2 - $start_date2)/(3600*24)) - $v['active_days'] + 1;
            } else {
                //获取设备离线天数
                $v['offline_days'] = $search_days - $v['active_days'] + 1;
            }

            if ($v['offline_days'] < 0) {
                $v['offline_days'] = 0;
            }

            //设备
            $v['device_unique_id'] = $device_unique_id;
            //厅
            $v['business_id'] = $device_info['business_id'];

            //获取体验时长
            if ($this->member_info['res_name'] == 'group') {
                if (!isset($v['experience_time_count'])) {
                    $v['experience_time_count'] = $this->get_device_experience_time($device_unique_id);
                } else {
                    $v['experience_time_count'] = $v['experience_time_count'];
                }
            } else {
                $v['experience_time_count'] = 0;
            }
            $experience_time_counts += $v['experience_time_count'];
            $data_list[] = $v;
        }

        if ($this->if_export == 1) {
            $this->export_experience_time_list($data_list);
        }

        Response::assign('count', $count);
        Response::assign('experience_time_counts', $experience_time_counts);
        Response::assign('res_name', $res_name);
        Response::assign('res_id', $res_id);
        Response::assign('order_field', $order_field);
        Response::assign('order_dir', $order_dir);
        Response::assign('data_list', $data_list);
        Response::display('admin/bestrow_stat/experience_time_list.html');
    }

    /**
     * 导出体验时长列表
     * @param unknown $list
     */
    public function export_experience_time_list($list)
    {
        if ( !$list ) {
            return false;
        }

        $data = array();
        foreach ($list as $k => $v) {
            $tmp = array(
                    'business_name' => $v['business_name'],
                    'user_number'   => $v['user_number'],
                    'phone_name'    => $v['phone_name'],
                    'phone_version' => $v['phone_version'],
                    'imei'          => $v['imei'],
                    'device_unique_id' => $v['device_unique_id'],
                    'active_days'   => $v['active_days'],
                    'offline_days'  => $v['offline_days'],
                    'add_time'      => $v['add_time'],
                    'last_active_time'  => $v['last_active_time'],
                    'is_online'     => $v['is_online'] ? '在线' : '离线',
                    'is_active'     => $v['is_active'] ? '活跃' : '不活跃',
                    'experience_time_count' => round($v['experience_time_count'] / 60, 1),
            );

            $data[] = $tmp;
        }

        $params = array();

        $params['filename'] = '设备体验详情列表';
        $params['data']     = $data;
        $params['head']     = array('营业厅名称', '渠道编码', '品牌', '型号', 'IMEI', '设备标识', '活跃天数', '离线天数', '添加时间', '最后活跃时间',  '今日是否在线', '是否活跃', '体验时长/分钟');
        Csv::getCvsObj($params)->export();
    }


    /**
     * 体验时长详情
     */
    public function experience_time_detail()
    {
        $res_name   = tools_helper::Get('res_name', '');
        $res_id     = tools_helper::Get('res_id', 0);
        $device_unique_id     = tools_helper::Get('device_unique_id', '');
        $page       = tools_helper::Get('page_no', 1);

        //借用设备的默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);
        //清除掉设备表特有的status字段条件
        unset($filter['status']);
        $filter ['device_unique_id'] = $device_unique_id;
        $filter['day >='] = date('Ymd', strtotime($this->start_date));
        $filter['day <='] = date('Ymd', strtotime($this->end_date));

        //分页
        $count = _mongo('screen', 'screen_action_record')->count(get_mongodb_filter($filter));
        $new_data = array();
        if ($count) {
            $pager = new MongoDBPager($this->per_page);
            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
            $new_data = _mongo('screen', 'screen_action_record')->find(get_mongodb_filter($filter), array_merge($pager->getLimit($page), array('sort' => array('add_time' => -1))));
        }

        $data_list = array();
        foreach ( $new_data as $k => $v ) {
            $v = (array)$v;
            $tmp = array();
            $device_unique_id    = $v['device_unique_id'];
            $device_info  = screen_device_helper::get_device_info_by_device($device_unique_id);
            //查询厅
            $tmp['province'] = business_hall_helper::get_info_name('province', $device_info['province_id'], 'name');
            //查询市
            $tmp['city'] = business_hall_helper::get_info_name('city', $device_info['city_id'], 'name');
            //查询厅
            $tmp['business_name'] = business_hall_helper::get_info_name('business_hall', $device_info['business_id'], 'title');
            //用户渠道编码
            $tmp['user_number'] = business_hall_helper::get_info_name('business_hall', $device_info['business_id'], 'user_number');
            //品牌
            $tmp['phone_name'] = $device_info['phone_name_nickname'] ? $device_info['phone_name_nickname'] : $device_info['phone_name'];
            //型号
            $tmp['phone_version'] = $device_info['phone_version_nickname'] ? $device_info['phone_version_nickname'] : $device_info['phone_version'];
            //IMEI
            $tmp['imei'] = $device_info['imei'];
            //体验开始时间
            $tmp['start_time'] = $v['add_time'];
            //体验结束时间
            $tmp['end_time'] = $v['update_time'];
            //体验时长
            $tmp['experience_time'] = $v['experience_time'];

            $data_list[] = $tmp;
        }

        Response::assign('count', $count);
        Response::assign('data_list', $data_list);
        Response::assign('res_name', $res_name);
        Response::assign('res_id', $res_id);
        Response::assign('device_unique_id', $device_unique_id);
        Response::display('admin/bestrow_stat/experience_time_detail.html');
    }

    /**
     * 获取搜索天数
     */
    private function get_search_days()
    {
        $start_date = $this->start_date;
        if ($this->end_date > date('Y-m-d')) {
            $end_date = date('Y-m-d');
        } else {
            $end_date = $this->end_date;
        }
        $days = array();
        do{
            $days[] = $start_date;
            $start_date = date('Y-m-d', strtotime($start_date)+24*3600);
        }while($start_date < $end_date);

        return $days;
    }

    /**
     * 计算活跃天数
     * @param diff_start_time 搜索开始时间
     * @param diff_end_time   搜索结束时间
     * @param add_time        设备添加时间
     * @param filter          搜索活跃天数条件
     */
    private function get_active_days($res_name, $res_id, $device_info)
    {
        //借用设备的默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);

        //清除掉设备表特有的status字段条件
        unset($filter['status']);

        //设备添加日期大于结束日期
        if ($device_info['add_time'] > $this->end_date) {
            return array();
        }

        $filter['day >=']       = date('Ymd', strtotime($this->start_date));
        $filter['day <=']       = date('Ymd', strtotime($this->end_date));
        $filter['device_unique_id'] = $device_info['device_unique_id'];
        $filter['business_id']  = $device_info['business_id'];

        $days = _model('screen_device_online_stat_day')->getFields('day', $filter, ' GROUP BY `day` ');
        return $days;
    }

    /**
     * 获取设备最后活跃时间
     */
    private function get_device_last_active($device_info)
    {
        $filter = array(
                'device_unique_id'  => $device_info['device_unique_id'],
                'business_id'       => $device_info['business_id']
        );
        //最后活跃时间
        $last_active = _model('screen_device_online_stat_day')->read($filter, ' ORDER BY `day` DESC ');

        if ($last_active) {
            return $last_active['update_time'];
        } else {
            return '';
        }
    }

    /**
     * 获取设备是否活跃
     * @param unknown $device_unique_id
     */
    private function get_device_is_active($device_unique_id)
    {
        $filter['device_unique_id']  = $device_unique_id;
        $filter['day >='] = date('Ymd', strtotime($this->start_date));
        $filter['day <='] = date('Ymd', strtotime($this->end_date));
        $device = _model('screen_device_online_stat_day')->getFields('device_unique_id', $filter, ' GROUP BY `device_unique_id` ');

        if ($device) return true;

        return false;
    }

    /**
     * 获取设备体验时长或体验次数
     * @param unknown $res_name 登录者res_name
     * @param unknown $res_id 登录者res_id
     * @return unknown
     */
    private function get_device_experience_time($device_unique_id)
    {
        //借用设备的默认条件

        $filter['day >='] = date('Ymd', strtotime($this->start_date));
        $filter['day <='] = date('Ymd', strtotime($this->end_date));
        $filter['device_unique_id'] = $device_unique_id;

        $result       = _mongo('screen', 'screen_device_stat_day')->aggregate(array(
                array('$match' => get_mongodb_filter($filter)),
                array('$group' => array(
                        '_id'       => array('device_unique_id'  => '$device_unique_id'),
                        'experience_time'      => array('$sum' =>'$experience_time'),
                )),
                //一定要排序，因为设备换厅后，一定是在最后面的
                array('$sort'=>array('_id'=>-1)),
        ));

        $experience_time = 0;
        foreach ($result as $k => $v) {
            $experience_time += $v['experience_time'];
        }

        return $experience_time;
    }


    /**
     * 获取设备体验时长或体验次数
     * @param unknown $res_name 登录者res_name
     * @param unknown $res_id 登录者res_id
     * @return unknown
     */
    private function get_device_experience_by_field($res_name, $res_id, $field, $devices=array())
    {
        $filter = array();
        if ($devices) {
            $filter['device_unique_id'] = $devices;
        } else {
            //借用设备的默认条件
            $filter = $this->get_default_device_filter($res_name, $res_id);

            //清除掉设备表特有的status字段条件
            unset($filter['status']);
        }

        $filter['day >='] = date('Ymd', strtotime($this->start_date));
        $filter['day <='] = date('Ymd', strtotime($this->end_date));
        $result       = _mongo('screen', 'screen_device_stat_day')->aggregate(array(
                array('$match' => get_mongodb_filter($filter)),
                array('$group' => array(
                        '_id'       => array('device_unique_id'  => '$device_unique_id'),
                        $field      => array('$sum' =>'$'.$field),
                        'device_unique_id'  => array('$first' => '$device_unique_id'),
                        'business_id'       => array('$first' => '$business_id'),
                )),
                //一定要排序，因为设备换厅后，一定是在最后面的
                array('$sort'=>array('_id'=>-1)),
        ));

        $new_result = array();
        foreach ($result as $k => $v) {
            $v = (array)$v;
            $new_result[$v['device_unique_id']] = $v[$field];
        }

        return $new_result;
    }

    /**
     * 获取下柜设备量
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_drop_off_device($res_name, $res_id)
    {
        $filter = $this->get_default_device_filter($res_name, $res_id);

        $filter['status'] = 0;

        if (!empty($this->search_filter['phone_name'])) {
            $filter['phone_name'] = $this->search_filter['phone_name'];
        }

        $devices = _model('screen_device')->getFields('device_unique_id', $filter, ' GROUP BY `device_unique_id` ');
        return $devices;
    }

    /**
     * 获取所有品牌
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_phone_name($res_name, $res_id)
    {
        $filter = $this->get_default_device_filter($res_name, $res_id);
        $device_nickname_ids = _model('screen_device')->getFields('device_nickname_id', $filter, ' GROUP BY `device_nickname_id` ');
        if ( !$device_nickname_ids ) {
            return array();
        }
        $nicknames = _model('screen_device_nickname')->getList($device_nickname_ids, ' GROUP BY `phone_name` ');
        if (!$nicknames) {
            return array();
        }

        $new_data = array();
        foreach ($nicknames as $k => $v) {
            $phone_nickname = !empty($v['name_nickname']) ? $v['name_nickname'] : $v['phone_name'];
            $new_data[] = array('phone_name' => $v['phone_name'], 'phone_nickname' => $phone_nickname);
        }

        return $new_data;
    }

    /**
     * 获取在线设备
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_online_device($res_name, $res_id)
    {
        $filter = $this->get_default_device_filter($res_name, $res_id);
        unset($filter['status']);

        $filter['day']              = (int)date('Ymd');
        $filter['update_time >=']   = date('Y-m-d H:i:s', time()-1800);

        $devices = _model('screen_device_online_stat_day')->getFields('device_unique_id', $filter, ' GROUP BY `device_unique_id` ');
        return $devices;
    }

    /**
     * 获取活跃设备 （默认今天）
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_active_device($res_name, $res_id)
    {

        //借用设备的默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);

        //在线
        $filter['day >=']       = date('Ymd', strtotime($this->start_date));
        $filter['day <=']       = date('Ymd', strtotime($this->end_date));

        //清除掉设备表特有的status字段条件
        unset($filter['status']);

        //获取设备
        $devices = _model('screen_device_online_stat_day')->getFields('device_unique_id', $filter, ' GROUP BY `device_unique_id`');
        return $devices;
    }

    private function get_active_device_by_range($res_name, $res_id, $rule)
    {
        //借用设备的默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);

        //在线
        $filter['day >=']       = date('Ymd', strtotime($this->start_date));
        $filter['day <=']       = date('Ymd', strtotime($this->end_date));

        //清除掉设备表特有的status字段条件
        unset($filter['status']);

        $where = to_where_sql($filter);

        $sql = " SELECT count(*) as `count_days`, `device_unique_id`, `business_id` FROM `screen_device_online_stat_day` {$where} GROUP BY `device_unique_id`, `business_id` ORDER BY `id` ASC ";

        $days_count = _model('screen_device_online_stat_day')->getAll($sql);

        p($days_count);

    }

    /**
     * 获取活跃营业厅 （默认今天）
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_active_business_hall($res_name, $res_id)
    {

        //借用设备的默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);

        //在线
        $filter['day >=']       = date('Ymd', strtotime($this->start_date));
        $filter['day <=']       = date('Ymd', strtotime($this->end_date));

        //清除掉设备表特有的status字段条件
        unset($filter['status']);
        //获取设备
        $business_ids = _model('screen_device_online_stat_day')->getFields('business_id', $filter, ' GROUP BY `business_id`');
        return $business_ids;
    }

    /**
     * 获取新增设备
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_new_device($res_name, $res_id)
    {
        //初始化默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);

        $filter['day >=']       = date('Ymd', strtotime($this->start_date));
        $filter['day <=']       = date('Ymd', strtotime($this->end_date));

        //获取指定日期的新增设备
        $new_devices = _model('screen_device')->getFields('device_unique_id', $filter);
        return $new_devices;
    }

    /**
     * 初始化搜索
     */
    private function init_search()
    {
        $search_filter = tools_helper::Get('search_filter', array());

        if (empty($search_filter['date_type']) || !in_array($search_filter['date_type'], array(1, 2, 3, 4))) {
            $search_filter['date_type'] = 3;  //默认日期类型为本月
        }

        //是否为首页
        $is_index = tools_helper::Get('is_index', 0);

        $this->date_type = $search_filter['date_type'];

        //今日
        if ($this->date_type == 1 && $is_index == 1) {
            $search_filter['start_date']    = $this->start_date   = date('Y-m-d');
            $search_filter['end_date']      = $this->end_date     = date('Y-m-d');
        //本周
        } else if ($this->date_type == 2 && $is_index == 1) {
            //获取本周开始日期和结束日期
            $date_info          = screen_helper::get_day_by_time(date('Y-m-d H:i:s'));
            list($start)        = explode(' ', $date_info['start']);
            list($end)          = explode(' ', $date_info['end']);
            $search_filter['start_date']    = $this->start_date   = $start;
            $search_filter['end_date']      = $this->end_date     = $end;

        //本月
        } else if ($this->date_type == 3 && $is_index == 1) {
            $search_filter['start_date']    = $this->start_date   = date('Y-m-01');
            $search_filter['end_date']      = $this->end_date     = date( "Y-m-d", strtotime( "first day of next month" ) - 24*3600 );

        //任意时间
        } else if ($this->date_type == 4 || $is_index != 1){
            if (empty($search_filter['start_date'])) {
                $search_filter['start_date'] = date('Y-m-01');
            }

            $this->start_date = $search_filter['start_date'];

            if (empty($search_filter['end_date'])) {
                $search_filter['end_date'] = date( "Y-m-d", strtotime( "first day of next month" ) - 3600*24 );
            }

            $this->end_date = $search_filter['end_date'];
        }

        //拼接搜索条件, 只在搜索时的展示用
        $res_name = tools_helper::Get('res_name', '');
        $res_id = tools_helper::Get('res_id', 0);

        if ($res_name == 'business_hall' && empty($search_filter['business_id']) &&  $res_id) {
            $business_hall_info = business_hall_helper::get_info_name('business_hall', $res_id);
            $search_filter['province_id']   = $business_hall_info['province_id'];
            $search_filter['city_id']       = $business_hall_info['city_id'];
            $search_filter['area_id']       = $business_hall_info['area_id'];
            $search_filter['business_id']       = $business_hall_info['id'];
        } else if ($res_name == 'area' && empty($search_filter['area_id']) &&  $res_id) {
            $area_info = business_hall_helper::get_info_name('area', $res_id);
            $search_filter['province_id']   = $area_info['province_id'];
            $search_filter['city_id']       = $area_info['city_id'];
            $search_filter['area_id']       = $area_info['id'];
        } else if ($res_name == 'city' && empty($search_filter['city_id']) &&  $res_id) {
            $search_filter['province_id']   = business_hall_helper::get_info_name('city', $res_id, 'province_id');
            $search_filter['city_id']       = $res_id;
        } else if ($res_name == 'province' && empty($search_filter['province_id']) && $res_id) {
            $search_filter['province_id'] = $res_id;
        }

        //搜索字符串
        $search_filter_str = '';
        foreach ( $search_filter as $k => $v ) {
            if (empty($v)) {
                continue;
            }
            if (!$search_filter_str) {
                $search_filter_str .= '?';
            } else {
                $search_filter_str .= '&';
            }

            $search_filter_str .= 'search_filter['.$k.']='.$v;
        }

        $this->search_filter = $search_filter;

        Response::assign('search_filter_str', $search_filter_str);
        Response::assign('search_filter', $search_filter);
    }

    /**
     * 获取新增厅店
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_new_cover_business_hall($res_name, $res_id)
    {
        //初始化默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);

//         $filter['status'];
        $filter['day >=']       = date('Ymd', strtotime($this->start_date));
        $filter['day <=']       = date('Ymd', strtotime($this->end_date));

        //获取新安装设备的厅店
        $business_id_new        = _model('screen_device')->getFields('business_id',$filter, ' GROUP BY `business_id` ');

        $filter['day <='] = $filter['day >='];
        unset($filter['day >=']);

        //获取之前已覆盖厅店
        $business_id_all = _model('screen_device')->getFields('business_id',$filter, ' GROUP BY `business_id` ');

        $new_business_id = array_diff($business_id_new, $business_id_all);

        //返回新增厅店
        return $new_business_id;
    }

    /**
     * 获取已安装设备
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_install_device($res_name, $res_id)
    {
        $online_devices    = false;
        $action_devices    = false;
        $search_devices       = false;

        if (!empty($this->search_filter['online_status'])) {
            //搜索在线设备
            if( $this->search_filter['online_status'] == 1 ) {
                $search_devices = $this->get_online_device($res_name, $res_id);
            //离线设备
            } else if ( $this->search_filter['online_status'] == 2 ) {
                $search_devices = $this->get_offline_device($res_name, $res_id);
            }

            //设备量为0，则直接返回
            if (empty($search_devices)) return array();
        }

        if (!empty($this->search_filter['active_status'])) {
            //搜索活跃设备
            if ( $this->search_filter['active_status'] == 1 ) {
                $action_devices = $this->get_active_device($res_name, $res_id);
            //搜索不活跃设备
            } else if ($this->search_filter['active_status'] == 2) {
                $action_devices = $this->get_not_active_device($res_name, $res_id);
            }

            //设备量为0，则直接返回
            if (empty($action_devices)) return array();

            //存在上述N个条件
            if ($search_devices) {
                $search_devices = array_intersect($search_devices, $action_devices);
            } else {
                $search_devices = $action_devices;
            }
        }

        //获取有效设备
        $filter = $this->get_default_device_filter($res_name, $res_id);

        //加上时间
        $filter['day <=']       = date('Ymd', strtotime($this->end_date));

        if (!empty($this->search_filter['phone_name'])) {
            $filter['phone_name'] = $this->search_filter['phone_name'];
        }

        if ($search_devices) {
            $filter['device_unique_id'] = $search_devices;
        }

        //为了兼容后续有详情页， 先把所有设备取出
        $devices = _model('screen_device')->getFields('device_unique_id', $filter, 'GROUP BY `device_unique_id`');
        return $devices;
    }

    /**
     * 获取离线终端
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_offline_device($res_name, $res_id)
    {

        //获取在线设备
        $online_device = $this->get_online_device($res_name, $res_id);

        //初始化默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);

        //获取已安装设备
        $all_device = _model('screen_device')->getFields('device_unique_id', $filter, 'GROUP BY device_unique_id');

        $offline = array_diff($all_device, $online_device);

        return $offline;

    }

    /**
     * 获取不活跃终端
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_not_active_device($res_name, $res_id)
    {

        //获取指定日期的在线设备
        $action_device = $this->get_active_device($res_name, $res_id);

        //初始化默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);

        //获取已安装设备
        $all_device = _model('screen_device')->getFields('device_unique_id', $filter, 'GROUP BY device_unique_id');

        $not_active = array_diff($all_device, $action_device);

        return $not_active;

    }

    /**
     * 获取已安装设备分布排行前10
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_install_device_distribute($res_name, $res_id)
    {
        //获取有效设备
        $device_unique_ids = $this->get_install_device($res_name, $res_id);

        if (!$device_unique_ids) {
            return array();
        }

        $filter = array('device_unique_id' => $device_unique_ids, 'status' => 1);

        $where = to_where_sql($filter);
        $sql = " SELECT count(*) as total, `device_nickname_id` FROM `screen_device` {$where} GROUP BY `device_nickname_id` ORDER BY `total` DESC LIMIT 10";
        //为了兼容后续有详情页， 先把所有设备取出
        $device_nicknames = _model('screen_device')->getAll($sql);

        $sorts = array();
        foreach ($device_nicknames as $k => $v) {
            //查询品牌型号
            $device_nickname_info = screen_device_helper::get_device_nickname_info($v['device_nickname_id']);
            $sorts[] = $v['total'];
            if (!$device_nickname_info) {
                $device_nicknames[$k]['phone_name'] = '未知';
            } else {
                $device_nicknames[$k]['phone_name'] = !empty($device_nickname_info['name_nickname']) ? $device_nickname_info['name_nickname'] : $device_nickname_info['phone_name'] ;
                $device_nicknames[$k]['phone_name'] .= !empty($device_nickname_info['version_nickname']) ? $device_nickname_info['version_nickname'] : $device_nickname_info['phone_version'] ;
            }
        }

        array_multisort($sorts, SORT_ASC, $device_nicknames);
        return $device_nicknames;
    }


    /**
     * 按照下级分组
     */
    private function group_by_subordinate()
    {
        $filter = $this->get_default_device_filter($this->member_info['res_name'], $this->member_info['res_id']);
        $region = _model('screen_device')->getFields($this->subordinate_res_id_field, $filter, ' GROUP BY '.$this->subordinate_res_id_field);
        return $region;
    }

    /**
     * 生成获取设备的默认条件
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_default_device_filter($res_name, $res_id)
    {
        //获取设备状态为1
        $filter = array('status' => 1);
        if (in_array($res_name, array('province', 'city', 'area'))) {
            $filter[$res_name.'_id'] = $res_id;

        } else if ($res_name == 'business_hall') {
            $filter['business_id'] = $res_id;
        }

        //初始化条件
        $search_filter = _widget('screen')->init_filter($this->member_info, $this->search_filter);

        if ($res_name == 'province' || !empty($search_filter['province_id'])) {
            //优先使用搜索条件
            if (!empty($search_filter['province_id'])) {
                $filter['province_id'] = $search_filter['province_id'];
            }
        }

        if ($res_name == 'city' || !empty($search_filter['city_id'])) {
            //优先使用搜索条件
            if (!empty($search_filter['city_id'])) {
                $filter['city_id'] = $search_filter['city_id'];
            }
        }

        if ($res_name == 'area' || !empty($search_filter['area_id'])) {
            //优先使用搜索条件
            if (!empty($search_filter['area_id'])) {
                $filter['area_id'] = $search_filter['area_id'];
            }
        }

        if ($res_name == 'business_hall' || !empty($search_filter['business_hall_id'])) {
            //优先使用搜索条件
            if (!empty($search_filter['business_hall_id'])) {
                $filter['business_id'] = $search_filter['business_hall_id'];
            }
        }
        return $filter;
    }

    /**
     * 设置下级
     */
    private function set_subordinate($res_name, $res_id)
    {
     if ($res_name == 'group') {
            $this->subordinate_res_id_field = 'province_id';
            $this->subordinate_res_name = 'province';
        } else if ($res_name == 'province') {
            $this->subordinate_res_id_field = 'city_id';
            $this->subordinate_res_name = 'city';
        } else if ($res_name == 'city') {
            $this->subordinate_res_id_field = 'area_id';
            $this->subordinate_res_name = 'area';
        } else if ($res_name == 'area') {
            $this->subordinate_res_id_field = 'business_id';
            $this->subordinate_res_name = 'business_hall';
        } else {
            $this->subordinate_res_id_field = 'business_id';
            $this->subordinate_res_name = 'business_hall';
        }

        Response::assign('subordinate_res_name', $this->subordinate_res_name);
        Response::assign('subordinate_res_id_field', $this->subordinate_res_id_field);
    }
    /**
     * 获取覆盖营业厅
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_cover_business_hall($res_name, $res_id)
    {
        //初始化默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);

        //加上时间
        $filter['day <=']       = date('Ymd', strtotime($this->end_date));

        //为了兼容后续有详情页，先把所有营业厅id取出
        $business_hall = _model('screen_device')->getFields('business_id', $filter, ' GROUP BY `business_id` ');

        return $business_hall;
    }

}