<?php
/**
 * alltosun.com  screen_status.php 亮屏状态
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * szy: 宋志宇 (songzy@alltosun.com)
 * 2018年3月13日: 2016-7-26 下午3:05:10
 * Id
 */
class Action
{
    /**
     * 有效设备定义： 非下柜的都为有效设备
     * 有效厅店定义： 设备活跃15天以上的厅店为有效厅店
     * @var unknown
     */
    private $member_info;
    private $res_id;
    private $res_name;
    private $subordinate_res_name;
    private $detail_field = '';
    private $data_type;
    private $start_date;
    private $end_date;
    private $date_type;
    private $valid = 15; //有效日期
    private $search_days = false;
    private $wangjf_debug = false;

    public function __construct()
    {
        $this->member_info = member_helper::get_member_info();
        if ($this->member_info) {
            $this->res_name = $this->member_info['res_name'];
            $this->res_id   = $this->member_info['res_id'];
        }

        //营业厅权限则跳转至厅界面
        if ($this->member_info && $this->member_info['res_name'] == 'business_hall') {
            Response::redirect(AnUrl('screen_dm/device'));
            Response::flush();
            exit;
        }

        //初始化搜索
        $this->init_search();

        //获取搜索天数
        if ($this->search_days === false) {
            $this->search_days = count($this->get_search_days());
        }

        $this->wangjf_debug = Request::Get('wangjf_debug', '');

        Response::assign('member_info', $this->member_info);
        Response::assign('valid', $this->valid);
        Response::assign('search_days', $this->search_days); //有搜索天数
    }

    public function __call($action='', $param=array())
    {
        if (!$this->member_info) {
            return '请先登录';
        }

        //搜索日期超过15天才获取有效厅店、活跃率
        if ($this->search_days >= $this->valid) {
            //获取有效厅店数
            $valid_business_hall_count  = count($this->get_valid_business_hall($this->member_info['res_name'], $this->member_info['res_id']));
            //获取活跃台数
            $active_device_count        = count($this->get_active_device($this->member_info['res_name'], $this->member_info['res_id']));
            $active_device_count_by_days = 0;
        } else {
            $valid_business_hall_count      = '--';
            $active_device_count            = 0;
            //获取按天活跃的设备数
            $active_device_count_by_days            = count($this->get_device_active_by_days($this->member_info['res_name'], $this->member_info['res_id']));
        }

        //获取新增有效厅店数
        $new_cover_business_hall_count  = count($this->get_new_cover_business_hall($this->member_info['res_name'], $this->member_info['res_id']));

        //获取未覆盖厅店数
        $not_cover_business_hall_count  = count($this->get_not_cover_business_hall($this->member_info['res_name'], $this->member_info['res_id']));

        //获取权限下所有精品厅店
        $business_hall_count        = count($this->get_business_hall($this->member_info['res_name'], $this->member_info['res_id']));

        //获取有效设备数
        $valid_device_count         = count($this->get_valid_device($this->member_info['res_name'], $this->member_info['res_id']));

        //获取新增有效设备数
        $new_valid_device_count     = count($this->get_new_valid_device($this->member_info['res_name'], $this->member_info['res_id']));

        //获取已安装设备数
        $install_device_count       = count($this->get_install_device($this->member_info['res_name'], $this->member_info['res_id']));

        //获取离线设备
        $offonline_device_count     = count($this->get_offonline_device($this->member_info['res_name'], $this->member_info['res_id']));

        Response::assign('valid_business_hall_count', $valid_business_hall_count); //有效厅店数
        Response::assign('new_cover_business_hall_count', $new_cover_business_hall_count); //新增覆盖厅店数
        Response::assign('not_cover_business_hall_count', $not_cover_business_hall_count); //未覆盖厅店数
        Response::assign('business_hall_count', $business_hall_count); //所有厅店数
        Response::assign('active_device_count_by_days', $active_device_count_by_days); //指定天活跃
        Response::assign('valid_device_count', $valid_device_count); //有效设备数
        Response::assign('new_valid_device_count', $new_valid_device_count); //新增有效设备数
        Response::assign('install_device_count', $install_device_count); //已安装设备数
        Response::assign('active_device_count', $active_device_count); //活跃设备数
        Response::assign('offonline_device_count', $offonline_device_count); //离线设备数
        Response::display('status/index.html');
    }

    public function details()
    {
        $download_from = tools_helper::Get('download_from', '');

        //兼容从微信过来的下载
        if (!$this->member_info && $download_from != 'weixin') {
            return '请先登录';
        }

        $res_name       = tools_helper::Get('res_name', ''); //要查看的下级
        $res_id         = tools_helper::Get('res_id', 0);  //要查看的下级id
        $if_export      = tools_helper::Get('if_export', 0);  //要查看的下级id

        if (!$res_name) {
            //首页进来则按本身权限去查
            $res_name   = $this->res_name;
            $res_id     = $this->res_id;
        }

        if (!$res_name) {
            return '非法的参数';
        }

        //数据分组
        $group_data = $this->get_detail_group_data($res_name, $res_id);
        //处理详情页数据
        $stat_list = $this->handle_detail_group_data($group_data);

        if ($if_export == 1) {
            $this->export($stat_list);
        }

        if ($if_export == 2) {
            $this->export2($group_data);
        }

        Response::assign('res_name', $res_name);
        Response::assign('res_id', $res_id);
        Response::assign('stat_list', $stat_list);
        Response::display('status/details.html');
    }

    /**
     * 导出
     */
    private function export($stat_list)
    {
        $params = array();

        if (!$stat_list) {
            return false;
        }

        $new_arr = array();
        foreach ($stat_list as $k => $v) {
            $new_arr[] = array(
                    'title'                         => $v['title'],
                    'data1'                         => $v['data1'],
                    'data2'                         => $v['data2'],
                    'data3'                         => $v['data3'],
            );
        }

        if ($this->data_type == 1) {
            $params['head']     = array('归属地', '总有效门店(家)', '新增(家)', '未覆盖厅店(家)');
            $filename           = '总有效门店';
        } else if ($this->data_type == 2) {
            $params['head']     = array('归属地', '总有效门店(家)', '新增(家)', '总有效终端');
            $filename           = '总有效终端';
        } else if ($this->data_type == 3) {
            $params['head']     = array('归属地','总有效门店(家)', '总有效终端(台)', '终端月活跃(台)');
            $filename           = '终端活跃量';
        } else if ($this->data_type == 4) {
            $params['head']     = array('归属地','总有效门店(家)', '总有效终端(台)','离线(台)');
            $filename           = '离线设备量';
        }

        //sheet1 总计
        $params['filename'] = $filename;
        $params['data']     = $new_arr;
        Csv::getCvsObj($params)->export();
    }

    /**
     * 导出 2 导出详情页
     */
    private function export2($group_data)
    {
//         p($this->data_type);
//         p($group_data);exit;

        $params = array();
        $new_arr = array();
        //sheet2 详情
        $params['head']     = array('省', '市', '区', '营业厅', '渠道视图编码', '是否有效门店');
        if ($this->data_type == 1) {
            $params['head'][]   = '是否安装亮屏';
            $filename           = '总有效门店详情';
            foreach ($group_data as $k => $v) {
                foreach ($v['valid_business_hall'] as $v1) {
                    //查询营业厅信息
                    $business_hall_info = business_hall_helper::get_info_name('business_hall', $v1);
                    if (!$business_hall_info) {
                        continue;
                    }

                    //查询省市区信息
                    $province_name  = business_hall_helper::get_info_name('province', $business_hall_info['province_id'], 'name');
                    $city_name      = business_hall_helper::get_info_name('city', $business_hall_info['city_id'], 'name');
                    $area_name      = business_hall_helper::get_info_name('area', $business_hall_info['area_id'], 'name');

                    //是否安装亮屏
                    $device_info = _uri('screen_device', array('business_id' => $business_hall_info['id'], 'status' => 1));

                    $new_arr[] = array(
                            'province_name'     => $province_name,
                            'city_name'         => $city_name,
                            'area_name'         => $area_name,
                            'busienss_hall'     => $business_hall_info['title'],
                            'user_number'       => $business_hall_info['user_number'],
                            'is_boutique'       => $business_hall_info['is_boutique'] ? '精品门店' : '非精品门店',
                            'is_install'         => $device_info ? '已安装' : '未安装'
                    );
                }
            }
        } else if ($this->data_type == 3) {
            $params['head'][]   = '终端活跃率';
            $filename           = '终端活跃率详情';

            $tmp = array();
            foreach ($group_data as $k => $v) {
                foreach ($v['active_device'] as $k1 => $v1) {
                    $info = explode('_', $k1);
                    if (empty($info[1])) {
                        continue;
                    }
                    $tmp[$info['0']][] = $info[1];
                }
            }
            foreach ( $tmp as $k => $v ) {
                //查询营业厅信息
                $business_hall_info = business_hall_helper::get_info_name('business_hall', $k);
                if (!$business_hall_info) {
                    continue;
                }
                //查询省市区信息
                $province_name  = business_hall_helper::get_info_name('province', $business_hall_info['province_id'], 'name');
                $city_name      = business_hall_helper::get_info_name('city', $business_hall_info['city_id'], 'name');
                $area_name      = business_hall_helper::get_info_name('area', $business_hall_info['area_id'], 'name');

                //查询本厅下所有设备
                $device_unique_ids = _model('screen_device')->getFields('device_unique_id', array('business_id' => $business_hall_info['id']), 'GROUP BY `device_unique_id`');
                $active_rate = round(count($v) / count($device_unique_ids) * 100, 2);

                if ($active_rate > 100) {
                    $active_rate = 100;
                }

                $new_arr[] = array(
                        'province_name'     => $province_name,
                        'city_name'         => $city_name,
                        'area_name'         => $area_name,
                        'busienss_hall'     => $business_hall_info['title'],
                        'user_number'       => $business_hall_info['user_number'],
                        'is_boutique'       => $business_hall_info['is_boutique'],
                        'active_rate'       => $active_rate.'%'
                );
            }

        } else {
            $params['head'][]   = '终端离线率';
            $filename           = '终端离线详情';
            $tmp = array();

            foreach ($group_data as $k => $v) {
                foreach ($v['offonline_device'] as $k1 => $v1) {
                    $tmp[$v1][] = $k1;
                }
            }

            foreach ( $tmp as $k => $v ) {
                //查询营业厅信息
                $business_hall_info = business_hall_helper::get_info_name('business_hall', $k);
                if (!$business_hall_info) {
                    continue;
                }
                //查询省市区信息
                $province_name  = business_hall_helper::get_info_name('province', $business_hall_info['province_id'], 'name');
                $city_name      = business_hall_helper::get_info_name('city', $business_hall_info['city_id'], 'name');
                $area_name      = business_hall_helper::get_info_name('area', $business_hall_info['area_id'], 'name');

                //查询本厅下所有设备
                $device_unique_ids  = _model('screen_device')->getFields('device_unique_id', array('business_id' => $business_hall_info['id']), 'GROUP BY `device_unique_id`');
                $offonine_rate      = round(count($v) / count($device_unique_ids) * 100, 2);

                if ($offonine_rate > 100) {
                    $offonine_rate = 100;
                }

                $new_arr[] = array(
                        'province_name'     => $province_name,
                        'city_name'         => $city_name,
                        'area_name'         => $area_name,
                        'busienss_hall'     => $business_hall_info['title'],
                        'user_number'       => $business_hall_info['user_number'],
                        'is_boutique'       => $business_hall_info['is_boutique'],
                        'offonine_rate'     => $offonine_rate.'%'
                );
            }
        }

        //sheet2 详情
        $params['filename'] = $filename;
        $params['data']     = $new_arr;
        //p($params);exit;
        Csv::getCvsObj($params)->export();

    }

    /**
     * 处理详情页的分组数据
     * @param unknown $group_data
     */
    private function handle_detail_group_data($group_data)
    {
        $new_data   = array();
        $sorts      = array();
        $res_name   = $this->subordinate_res_name;

        if ($this->search_days === false) {
            $this->search_days = count($this->get_search_days());
        }

        foreach ( $group_data as $k => $v ) {
            //获取每行数据的归属地或机型昵称
            $title                      = $this->get_detail_title($k);

            if (!$title) {
                continue;
            }

            $res_id     = $k;
            $tmp        = array(
                    'title'                         => $title,
            );

            if ($this->data_type == 1) {
                if ($this->search_days >= $this->valid) {
                    $tmp['data1'] = count($this->get_valid_business_hall($this->subordinate_res_name, $k)); //有效厅店
                } else {
                    $tmp['data1'] = 0; //有效厅店
                }

                $tmp['data2'] = count($this->get_new_cover_business_hall($this->subordinate_res_name, $k)); //新增覆盖厅店
                $tmp['data3'] = count($this->get_not_cover_business_hall($this->subordinate_res_name, $k));  //未覆盖厅店
                $tmp['data4'] = count($v['cover_business_hall']); //总覆盖厅店
                if ($tmp['data3'] < 0) $tmp['data3'] = 0;
                //排序所需
                $sorts[] = $tmp['data1'];
            } else if ($this->data_type == 2) {
                if ($this->search_days >= $this->valid) {
                    $tmp['data1'] = count($this->get_valid_business_hall($this->subordinate_res_name, $k)); //有效厅店
                } else {
                    $tmp['data1'] = 0; //有效厅店
                }
                $tmp['data2'] = count($this->get_new_valid_device($this->subordinate_res_name, $k)); //新增有效设备
                $tmp['data3'] = count($v['valid_device']); //有效终端
                $tmp['data4'] = count($v['cover_business_hall']);
                //排序所需
                $sorts[] = $tmp['data3'];
            } else if ($this->data_type == 3) {
                if ($this->search_days >= $this->valid) {
                    $tmp['data1'] = count($this->get_valid_business_hall($this->subordinate_res_name, $k)); //有效厅店
                } else {
                    $tmp['data1'] = 0; //有效厅店
                }
                $tmp['data2'] = count($v['valid_device']); //有效终端
                $tmp['data3'] = count($this->get_active_device($this->subordinate_res_name, $k)); //活跃设备
                //排序所需
                $sorts[] = $tmp['data3'];
            } else if ($this->data_type == 4) {
                if ($this->search_days >= $this->valid) {
                    $tmp['data1'] = count($this->get_valid_business_hall($this->subordinate_res_name, $k)); //有效厅店
                } else {
                    $tmp['data1'] = 0; //有效厅店
                }
                $tmp['data2'] = count($v['valid_device']); //有效终端
                $tmp['data3'] = count($this->get_offonline_device($this->subordinate_res_name, $k));
                //排序所需
                $sorts[] = $tmp['data3'];
            }

            $tmp['res_id']              = $k;
            //未覆盖厅店
            $new_data[] = $tmp;
        }

        //排序
        if ($new_data) {
            array_multisort($sorts, SORT_DESC, $new_data);
        }
        return $new_data;
    }

    /**
     * 获取详情页的分组数据（按机型或按归属地分组数据）
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_detail_group_data($res_name, $res_id)
    {
        //初始化默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);

        if ($res_name == 'group') {
            $this->detail_field= 'province_id';
            $this->subordinate_res_name = 'province';
        } else if ($res_name == 'province') {
            $this->detail_field = 'city_id';
            $this->subordinate_res_name = 'city';
        } else if ($res_name == 'city') {
            $this->detail_field = 'area_id';
            $this->subordinate_res_name = 'area';
        } else {
            $this->detail_field = 'business_id';
            $this->subordinate_res_name = 'business_hall';
        }

        unset($filter['status']);

        $new_device_list = array();

        //以安装设备为准分组数据
        $filter         = $this->get_default_device_filter($res_name, $res_id);
        $device_list    = _model('screen_device')->getList($filter, ' GROUP BY `device_unique_id` ');

        //调试
        if ($this->wangjf_debug == 'get_detail_group_data_1') {
            p($device_list);exit;
        }

        foreach ($device_list as $k => $v) {
            $new_device_list[$v[$this->detail_field]]['cover_business_hall'][$v['business_id']] = $v['business_id'];
            $new_device_list[$v[$this->detail_field]]['valid_device'][$v['device_unique_id']]   = $v['business_id'];
        }

        //调试
        if ($this->wangjf_debug == 'get_detail_group_data_2') {
            p($new_device_list);exit;
        }

        Response::assign('subordinate_res_name', $this->subordinate_res_name);

        return $new_device_list;
    }

    /**
     * 初始化搜索
     */
    private function init_search()
    {
        $search_filter = tools_helper::Get('search_filter', array());

        if (empty($search_filter['date_type']) || !in_array($search_filter['date_type'], array(1, 2, 3, 4))) {
            $search_filter['date_type'] = 4;  //默认日期类型为 本月
        }

        $this->date_type = $search_filter['date_type'];

        //今日
        if ($this->date_type == 1) {
            $this->start_date   = date('Y-m-d');
            $this->end_date     = date('Y-m-d');
        //近七天
        } else if ($this->date_type == 2) {
            $this->start_date   = date('Y-m-d', strtotime('-6 days'));
            $this->end_date     = date('Y-m-d');
        //本月
        } else if ($this->date_type == 4) {
            $this->start_date   = date('Y-m-01');
            $this->end_date     = date('Y-m-d', strtotime(date('Y-m-01', strtotime('+1 month'))) - 24*3600 );
        //任意时间
        } else {
            if (empty($search_filter['start_date'])) {
                $search_filter['start_date'] = date('Y-m-d');
            }

            $this->start_date = $search_filter['start_date'];

            if (empty($search_filter['end_date'])) {
                $search_filter['end_date'] = date('Y-m-d');
            }

            $this->end_date = $search_filter['end_date'];
        }

        if (empty($search_filter['data_type']) || !in_array($search_filter['data_type'], array(1, 2, 3, 4))) {
            $search_filter['data_type'] = $this->data_type = 1;
        } else {
            $this->data_type = $search_filter['data_type'];
        }

        //搜索字符串
        $search_filter_str = '';
        foreach ( $search_filter as $k => $v ) {
            if (!$search_filter_str) {
                $search_filter_str .= '?';
            } else {
                $search_filter_str .= '&';
            }

            $search_filter_str .= 'search_filter['.$k.']='.$v;
        }

        Response::assign('search_filter_str', $search_filter_str);
        Response::assign('search_filter', $search_filter);
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
    * 获取详情页数据的标题
    * @param unknown $res_id
    */
   private function get_detail_title($res_id)
   {
       $title = '';
       //获取归属地名称
       if (in_array($this->subordinate_res_name, array('province', 'city', 'area'))) {
           $title = business_hall_helper::get_info_name($this->subordinate_res_name, $res_id, 'name');
       } else if ($this->subordinate_res_name == 'business_hall') {
           $title = business_hall_helper::get_info_name($this->subordinate_res_name, $res_id, 'title');
       } else {
           return false;
       }
       return $title;
   }


    /**
     * 获取活跃设备 （默认本月）
     * @param string $res_name
     * @param int    $res_id
     * @param array  $param
     */
    private function get_active_device($res_name, $res_id)
    {

        //借用设备的默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);

        //清除掉设备表特有的status字段条件
        unset($filter['status']);

        $filter['month >=']       = date('Ym', strtotime($this->start_date));
        $filter['month <=']       = date('Ym', strtotime($this->end_date));
        $filter['active_days >='] = $this->valid;
        //获取设备
        $devices = _model('screen_device_active_stat_month')->getFields('device_unique_id', $filter, ' GROUP BY `device_unique_id`');
        return $devices;
    }

    /**
     * 获取活跃天数 15天以内
     * @param string $res_name
     * @param int    $res_id
     * @param array  $param
     */
    private function get_device_active_by_days($res_name, $res_id)
    {

        //借用设备的默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);

        //清除掉设备表特有的status字段条件
        unset($filter['status']);

        $filter['month >=']       = date('Ym', strtotime($this->start_date));
        $filter['month <=']       = date('Ym', strtotime($this->end_date));

        //获取搜索天数
        if ($this->search_days === false) $this->search_days = count($this->get_search_days());

        $filter['active_days >='] = $this->search_days;

        //获取设备
        $devices = _model('screen_device_active_stat_month')->getFields('device_unique_id', $filter, ' GROUP BY `device_unique_id`');
        return $devices;
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
        } else if ($res_name != 'group') {
            return array('id' => 0);
        }

        return $filter;
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

        //为了兼容后续有详情页，先把所有有设备营业厅id取出
        $business_hall_ids = _model('screen_device')->getFields('business_id', $filter, ' GROUP BY `business_id` ');
        return $business_hall_ids;
    }

    /**
     * 获取有效厅店
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_valid_business_hall($res_name, $res_id)
    {
        //初始化默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);

        unset($filter['status']);

        if (!$filter) {
            $filter = array(1=>1);
        }

        $filter['month >=']       = date('Ym', strtotime($this->start_date));
        $filter['month <=']       = date('Ym', strtotime($this->end_date));
        $filter['active_days >='] = $this->valid;

        //有效设备， 因为要处理下柜的设备，所以营业厅和设备id都查出，因为换厅的设备一定会在最后面，所以按照id正序
        $business_id = _model('screen_device_active_stat_month')->getFields('business_id',$filter, ' GROUP BY `business_id` ');

        return $business_id;
    }

    /**
     * 获取新覆盖厅店
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_new_cover_business_hall($res_name, $res_id)
    {
        //初始化默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);

        $filter['status'];

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
     * 获取未覆盖门店数
     */
    public function get_not_cover_business_hall($res_name, $res_id)
    {
        //获取所有精品门店
        $boutique_busienss_hall = $this->get_business_hall($res_name, $res_id);

        //获取所有已覆盖厅店
        $cover_business_hall = $this->get_cover_business_hall($res_name, $res_id);

//p($res_name, $res_id, $cover_business_hall, $boutique_busienss_hall);
        $not_cover = array_diff($boutique_busienss_hall, $cover_business_hall);

        return $not_cover;

    }

    /**
     * 获取有效设备
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_valid_device($res_name, $res_id)
    {
        //获取有效设备
        $filter = $this->get_default_device_filter($res_name, $res_id);

        //为了兼容后续有详情页， 先把所有设备取出
        $devices = _model('screen_device')->getFields('device_unique_id', $filter, 'GROUP BY `device_unique_id`');

        return $devices;
    }

    /**
     * 获取新增有效设备
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_new_valid_device($res_name, $res_id)
    {
        //初始化默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);

        $filter['day >=']       = date('Ymd', strtotime($this->start_date));
        $filter['day <=']       = date('Ymd', strtotime($this->end_date));

        //获取指定日期的新增有效设备
        $new_devices = _model('screen_device')->getFields('device_unique_id', $filter);

        return $new_devices;
    }

    /**
     * 获取权限下所有营业厅(包括没有设备的营业厅)
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_business_hall($res_name, $res_id)
    {
        //初始化默认条件
        $filter = array();
        if (in_array($res_name, array('province', 'city', 'area'))) {
            $filter[$res_name.'_id'] = $res_id;
        } else if ($res_name == 'business_hall') {
            $filter['id'] = $res_id;
        } else if ($res_name == 'group') {
           $filter['1'] = 1;
        }

        //精品门店
        $filter['is_boutique'] = 1;

        //为了兼容后续有详情页，先把所有有设备营业厅id取出
        $business_hall_ids = _model('business_hall')->getFields('id', $filter);
        return $business_hall_ids;
    }

    /**
     * 获取已安装设备
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_install_device($res_name, $res_id)
    {
        //初始化默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);

        //为了兼容后续有详情页， 先把所有设备取出
        $devices = _model('screen_device')->getFields('device_unique_id', $filter, 'GROUP BY `device_unique_id`');

        return $devices;
    }

    /**
     * 获取指定日期的离线终端
     * @param unknown $res_name
     * @param unknown $res_id
     */
    public function get_offonline_device($res_name, $res_id)
    {
        //初始化默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);

        $filter['day >=']       = date('Ymd', strtotime($this->start_date));
        $filter['day <=']       = date('Ymd', strtotime($this->end_date));

        unset($filter['status']);

        //获取指定日期的在线设备
        $online_device = _model('screen_device_online_stat_day')->getFields('device_unique_id', $filter, 'GROUP BY device_unique_id');

        //初始化默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);

        //获取归属地内所有设备
        $all_device = _model('screen_device')->getFields('device_unique_id', $filter, 'GROUP BY device_unique_id');

        $offonline = array_diff($all_device, $online_device);

        return $offonline;

    }

}