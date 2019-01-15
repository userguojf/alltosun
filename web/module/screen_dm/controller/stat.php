<?php
/**
  * alltosun.com 亮屏统计 stat.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2018年3月12日 下午4:00:25 $
  * $Id$
  */
class Action
{
    private $member_info;
    private $echarts_num = 6;  // 图表所需数量
    private $date_type;
    private $start_date;
    private $end_date;
    private $device_nickname_id;
    private $subordinate_res_name;
    private $detail_field = '';
    private $detail_data_type;
    private $pie_color = array(
            0 => 'rgb(82, 131, 247)',
            1 => 'rgb(94, 201, 155)',
            2 => 'rgb(247, 198, 68)',
            3 => 'rgb(91, 98, 116)',
            4 => 'rgb(148, 210, 251)',
            5 => 'rgb(155, 212, 69)',
            6 => 'rgb(107, 118, 231)',
            7 => 'rgb(253, 161, 86)',
    );


    public function __construct()
    {
        $this->member_info = member_helper::get_member_info();
        Response::assign('member_info', $this->member_info);

        //营业厅权限则跳转至厅界面
        if ($this->member_info && $this->member_info['res_name'] == 'business_hall') {
            Response::redirect(AnUrl('screen_dm'));
            Response::flush();
            exit;
        }
        $this->init_search();

    }

    public function __call($action='', $param=array())
    {
        if (!$this->member_info) {
            return '请先登录';
        }

        //获取覆盖厅店数
        $cover_business_hall_count  = count($this->get_cover_business_hall($this->member_info['res_name'], $this->member_info['res_id']));

        //获取安装设备数
        $install_device_count       = count($this->get_install_device($this->member_info['res_name'], $this->member_info['res_id']));

        //获取活跃台数
        $active_device_count        = count($this->get_active_device($this->member_info['res_name'], $this->member_info['res_id']));

        //获取设备体验时长
        $device_experience_time     = $this->get_device_experience_by_field($this->member_info['res_name'], $this->member_info['res_id'], 'experience_time');

        //按设备将体验时长分组
        $experience_time_brand      = $this->group_experience_by_brand($device_experience_time, 'experience_time');

        //处理echart数据
        $experience_time_echarts    = $this->handle_experience_echarts($experience_time_brand, 'experience_time');

        //获取设备体验次数
        $device_action_num          = $this->get_device_experience_by_field($this->member_info['res_name'], $this->member_info['res_id'], 'action_num');

        //按设备将体验次数分组
        $action_num_brand           = $this->group_experience_by_brand($device_action_num, 'action_num');

        //处理echart数据
        $action_num_echarts         = $this->handle_experience_echarts($action_num_brand, 'action_num');

        //处理上柜占比图表数据
        $boutique_rate_echarts      = $this->get_boutique_rate_echarts($this->member_info['res_name'], $this->member_info['res_id']);

        //覆盖营业厅数
        Response::assign('cover_business_hall_count', $cover_business_hall_count);

        //已安装设备数
        Response::assign('install_device_count', $install_device_count);

        //活跃设备数
        Response::assign('active_device_count', $active_device_count);

        //平均体验时长echarts数据
        Response::assign('experience_time_echarts', json_encode($experience_time_echarts));

        //平均体验次数echarts数据
        Response::assign('action_num_echarts', json_encode($action_num_echarts));

        //上柜占比图表数据
        Response::assign('json_boutique_rate_echarts', json_encode($boutique_rate_echarts));

        //颜色
        Response::assign('json_pie_color', json_encode($this->pie_color));

        //上柜占比图表数据
        Response::assign('boutique_rate_echarts', $boutique_rate_echarts);

        //颜色
        Response::assign('pie_color', $this->pie_color);

        Response::display('stat/index.html');
    }

    /**
     * 体验时长详情页
     */
    public function detail()
    {

        if (!$this->member_info) {
            return '请先登录';
        }

        $if_export = tools_helper::Get('if_export', 0);

        $group_data = array();

        //由首页进来不携带device_nickname_id
        if (!$this->device_nickname_id) {
            //首页进来则按本身权限去查
            $res_name   = $this->member_info['res_name'];
            $res_id     = $this->member_info['res_id'];

        } else {

            //详情页查看下级详情列表
            $res_name       = tools_helper::Get('res_name', ''); //要查看的下级
            $res_id         = tools_helper::Get('res_id', 0);  //要查看的下级id
        }

        if ((!$res_name && !$res_id ) || ( !in_array($res_name, array('province', 'city', 'area', 'business_hall', 'group')))){
            return '非法或不完整的参数';
        }

        //获取分组数据
        $group_data = $this->get_detail_group_data($res_name, $res_id);

        //处理详情页数据
        $stat_list = $this->handle_detail_group_data($res_name, $res_id, $group_data);

        //导出
        if ($if_export == 1) {
            $this->export_detail($stat_list);
        }
        Response::assign('device_nickname_id', $this->device_nickname_id);
        Response::assign('res_name', $res_name);
        Response::assign('res_id', $res_id);
        Response::assign('stat_list', $stat_list);
        Response::display('stat/detail.html');
    }

    /**
     * 体验时长详情页
     */
    public function detail_by_business_hall()
    {

        if (!$this->member_info) {
            return '请先登录';
        }

        $sort_field = Request::Get('sort_field', '');
        $sort_dir = Request::Get('sort_dir', 'desc');

        //默认排序字段
        if (!$sort_field) {
            if ($this->detail_data_type == 3) {
                $sort_field = 'conver_business_hall_count';
            } else {
                $sort_field = 'data';
            }

        }

        $if_export = tools_helper::Get('if_export', 0);

        $group_data = array();

        //由首页进来不携带device_nickname_id
        if (!$this->device_nickname_id) {
            //首页进来则按本身权限去查
            $res_name   = $this->member_info['res_name'];
            $res_id     = $this->member_info['res_id'];

        } else {
            //详情页查看下级详情列表
            $res_name       = tools_helper::Get('res_name', ''); //要查看的下级
            $res_id         = tools_helper::Get('res_id', 0);  //要查看的下级id
        }

        if ((!$res_name && !$res_id ) || ( !in_array($res_name, array('province', 'city', 'area', 'business_hall', 'group')))){
            return '非法或不完整的参数';
        }

        Response::assign('res_name', $res_name);
        Response::assign('res_id', $res_id);

        //获取分组数据
        $group_data = $this->get_detail_group_data($res_name, $res_id);
        //处理详情页数据
        $new_data = array();
        foreach ( $group_data as $k => $v ) {

            //获取每行数据的归属地或机型昵称
            $title                      = $this->get_detail_title($k);
            if (!$title) {
                continue;
            }

            //从统计首页进来
            if (!$this->device_nickname_id) {
                $res_name = $this->member_info['res_name'];
                $res_id   = $this->member_info['res_id'];
                //设备昵称id
                $device_nickname_id = $k;

            //上级页面进来
            } else {
                $res_name = $this->subordinate_res_name;
                $res_id   = $k;

                //设备昵称id
                $device_nickname_id = $this->device_nickname_id;
            }

            //获取活跃设备
            $active_device          = $this->get_active_device($res_name, $res_id, $device_nickname_id);

            //获取已安装设备
            $install_device         = $this->get_install_device($res_name, $res_id, $device_nickname_id);

            if ($this->detail_data_type == 3) {
                //获取已覆盖营业厅
                $conver_business_hall   = array_keys($v);
            } else {
                //获取已覆盖营业厅
                $conver_business_hall   = $this->get_cover_business_hall($res_name, $res_id, $device_nickname_id);
            }

            $active_device_count = count($active_device);

            if ($this->detail_data_type == 3) {
                //获取新增
                $data    = count($this->get_new_device($res_name, $res_id, $device_nickname_id));
            } else {
                if ($this->detail_data_type == 1) {
                    //平均体验时长
                    $data = round(array_sum($v) / count($v) / 60, 1); //分钟
                } else {
                    //平均体验次数
                    $data = round(array_sum($v) / count($v), 1); //分钟
                }
            }

            $tmp = array(
                    'title'                         => $title,
                    'conver_business_hall_count'    => count($conver_business_hall),
                    'install_device_count'          => count($install_device),
                    'active_device_count'           => count($active_device),
                    'data'       => $data,
            );

            if (!isset($tmp[$sort_field])) {
                if ($this->detail_data_type == 3) {
                    $sort_field = 'conver_business_hall_count';
                } else {
                    $sort_field = 'data';
                }

            }

            //排序所需
            $sorts[] = $tmp[$sort_field];
            //获取设备
            if (!$this->device_nickname_id) {
                $tmp['device_nickname_id']  = $k;
            } else {
                $tmp['device_nickname_id']  = $this->device_nickname_id;
            }
            $tmp['res_id']              = $k;

            $new_data[] = $tmp;
        }
        //排序
        if ($new_data) {
            if ($sort_dir == 'asc') {
                $sort = SORT_ASC;
            } else {
                $sort_dir = 'desc';
                $sort = SORT_DESC;
            }

            array_multisort($sorts, $sort, $new_data);
        }
        Response::assign('sort_dir', $sort_dir);
        Response::assign('sort_field', $sort_field);

        if ($if_export == 1) {
            $this->export_detail_by_business_hall($new_data);
        }

        Response::assign('stat_list', $new_data);
        Response::display('stat/detail_by_business_hall.html');
    }

    /**
     * 获取亮屏上柜占有率 （首页图表数据）
     * 上柜占比计算方式：已覆盖厅店/总覆盖厅店
     * @param unknown $res_name
     * @param unknown $res_id
     * @param number $limit
     */
    private function get_boutique_rate_echarts($res_name, $res_id, $limit=8)
    {
        //获取所有覆盖营业厅 (用来计算上柜占比)
        $cover_business_hall          = $this->get_cover_business_hall($res_name, $res_id);
        $cover_business_hall_count    = count($cover_business_hall);

        //获取所有已覆盖设备（按设备分组）
        $filter = $this->get_default_device_filter($res_name, $res_id);

        $device_list = _model('screen_device')->getList($filter, ' GROUP BY business_id, device_nickname_id ');

        $group_data = array();
        foreach ($device_list as $k => $v) {
            $group_data[$v['device_nickname_id']][] = $v['business_id'];
        }

        $sorts      = array();
        $new_data   = array();
        foreach ($group_data as $k1 => $v1) {
            $name = $this->get_detail_title($k1);
            if (!$name) {
                continue;
            }

            if (count($v1) == 0 || $cover_business_hall_count == 0) {
                $rate = 0;
            } else {
                $rate = round(count($v1) / $cover_business_hall_count * 100, 1);
            }

            $new_data[] = array(
                    'name' => $name,
                    'value' => $rate,
            );

            $sorts[] = $rate;
        }

        if ($sorts) {
            array_multisort($sorts, SORT_DESC, $new_data);
        }

        return array_slice($new_data, 0, $limit);

    }

    /**
     * 获取精品门店营业厅
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_boutique_business_hall($res_name, $res_id)
    {

        $filter = array(
                'is_boutique' => 1
        );

        if ($res_name == 'business_hall') {
            $filter['id'] = $res_id;
        } else if (in_array($res_name, array('province' ,'city', 'area'))) {
            $filter[$res_name.'_id'] = $res_id;
        }

        return _model('business_hall')->getFields('id', $filter);
    }

    /**
     * 处理详情页的分组数据
     * @param unknown $group_data
     */
    private function handle_detail_group_data($res_name, $res_id, $group_data)
    {
        //获取排序规则
        $sort_field = Request::Get('sort_field', '');
        $sort_dir = Request::Get('sort_dir', 'desc');

        $new_data = array();
        $sorts = array();

        //默认排序字段
        if (!$sort_field) {
            if ($this->detail_data_type == 3) {
                $sort_field = 'conver_business_hall_count';
            } else {
                $sort_field = 'data';
            }

        }

        foreach ( $group_data as $k => $v ) {
            //获取每行数据的归属地或机型昵称
            $title                      = $this->get_detail_title($k);
            if (!$title) {
                continue;
            }

            //从统计首页进来
            if (!$this->device_nickname_id) {
                $res_name   = $this->member_info['res_name'];
                $res_id     = $this->member_info['res_id'];
                //昵称id
                $device_nickname_id = $k;
            //上级页面进来
            } else {
                $res_name   = $this->subordinate_res_name;
                $res_id     = $k;

                //昵称id
                $device_nickname_id = $this->device_nickname_id;
            }

            //获取活跃设备
            $active_device          = $this->get_active_device($res_name, $res_id, $device_nickname_id);

            //获取已安装设备
            $install_device         = $this->get_install_device($res_name, $res_id, $device_nickname_id);

            if ($this->detail_data_type == 3) {
                //获取已覆盖营业厅
                $conver_business_hall   = array_keys($v);
            } else {
                //获取已覆盖营业厅
                $conver_business_hall   = $this->get_cover_business_hall($res_name, $res_id, $device_nickname_id);
            }


            $active_device_count = count($active_device);

            if ($this->detail_data_type == 3) {
                //获取新增
                $data    = count($this->get_new_device($res_name, $res_id, $device_nickname_id));
            } else {
                if ($this->detail_data_type == 1) {
                    //平均体验时长
                    $data = round(array_sum($v) / count($v) / 60, 1); //分钟
                } else {
                    //平均体验次数
                    $data = round(array_sum($v) / count($v), 1); //分钟
                }

            }
//p($conver_business_hall);
            $tmp = array(
                    'title'                         => $title,
                    'conver_business_hall_count'    => count($conver_business_hall),
                    'install_device_count'          => count($install_device),
                    'active_device_count'           => $active_device_count,
                    'data'       => $data,
            );

            if (!isset($tmp[$sort_field])) {
                if ($this->detail_data_type == 3) {
                    $sort_field = 'conver_business_hall_count';
                } else {
                    $sort_field = 'data';
                }

            }

            //排序所需
            $sorts[] = $tmp[$sort_field];
            //获取设备
            if (!$this->device_nickname_id) {
                $tmp['device_nickname_id']  = $k;
            } else {
                $tmp['device_nickname_id']  = $this->device_nickname_id;
            }
            $tmp['res_id']              = $k;

            $new_data[] = $tmp;
        }
        //排序
        if ($new_data) {
            if ($sort_dir == 'asc') {
                $sort = SORT_ASC;
            } else {
                $sort_dir = 'desc';
                $sort = SORT_DESC;
            }

            array_multisort($sorts, $sort, $new_data);
        }

        Response::assign('sort_dir', $sort_dir);
        Response::assign('sort_field', $sort_field);

        return $new_data;
    }

    /**
     * 获取详情页体验数据
     * @param unknown $res_name
     * @param unknown $res_id
     * @param unknown $devices
     * @return number
     */
    private function get_detail_average_experience($res_name, $res_id, $devices)
    {
        if ($this->detail_data_type == 1) {
            $field = 'experience_time';
        } else {
            $field = 'action_num';
        }

        //根据下级
        $filter                 = $this->get_default_device_filter($res_name, $res_id);
        $filter['day >=']       = date('Ymd', strtotime($this->start_date));
        $filter['day <=']       = date('Ymd', strtotime($this->end_date));
        $filter['device_unique_id'] = $devices;

        unset($filter['status']);
        //获取体验时长
        $result       = _mongo('screen', 'screen_device_stat_day')->aggregate(array(
                array('$match' => get_mongodb_filter($filter)),
                array('$group' => array(
                        '_id'               => array('device_unique_id'  => '$device_unique_id'),
                        $field              => array('$sum' =>'$'.$field),
                        'device_unique_id'  => array('$first' => '$device_unique_id'),
                ))
        ));

        $device_num = 0;
        $sum = 0;
        foreach ($result as $k1 => $v1) {
            $v1 = (array)$v1;
            $sum                += $v1[$field];
            $device_num         += 1;
        }

        if (!$sum || !$device_num) {
            $data = 0;
        } else {
            if ($this->detail_data_type == 1) {
                //平均体验时长
                $data = round($sum / $device_num / 60, 1); //分钟
            } else {
                //平均体验次数
                $data = round($sum / $device_num, 1); //分钟
            }

        }
        return $data;
    }

    /**
     * 获取详情页数据的标题
     * @param unknown $res_id
     */
    private function get_detail_title($res_id)
    {
        $title = '';
        //按nickname_id分组(由首页进来)
        if (!$this->device_nickname_id) {
            //获取设备昵称
            $nickname_info = screen_device_helper::get_device_nickname_info($res_id);
            if ( !$nickname_info ) {
                return false;
            }

            $title = empty($nickname_info['name_nickname']) ? $nickname_info['phone_name'] : $nickname_info['name_nickname'];
            $title .= ' ';
            $title .= empty($nickname_info['version_nickname']) ? $nickname_info['phone_version'] : $nickname_info['version_nickname'];
            //按归属地分组(由详情页点击查看下级详情列表)
        } else {
            //获取归属地名称
            if (in_array($this->subordinate_res_name, array('province', 'city', 'area'))) {
                $title = business_hall_helper::get_info_name($this->subordinate_res_name, $res_id, 'name');
            } else if ($this->subordinate_res_name == 'business_hall') {
                $title = business_hall_helper::get_info_name($this->subordinate_res_name, $res_id, 'title');
            } else {
                return false;
            }
        }

        return $title;
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

        if (!$this->device_nickname_id) {
            $this->detail_field                          = 'device_nickname_id';
        } else {
            $filter['device_nickname_id']   = $this->device_nickname_id;
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
        }

        $group_data = array();

        //体验时长或体验次数
        if ($this->detail_data_type == 1 || $this->detail_data_type == 2) {
            $field = $this->detail_data_type == 1 ? 'experience_time' : 'action_num';
            //由二级页面进来
            if ($this->device_nickname_id) {
                //查询此品牌下所有设备
                $devices = $this->get_install_device($res_name, $res_id, $this->device_nickname_id);

                //查询指定设备的体验数据
                if (!$devices) {
                    $result = array();
                } else {
                    $result = $this->get_device_experience_by_field($res_name, $res_id, $field, $devices);
                }
            //由首页进来
            } else {
                //查询所有体验数据
                $result = $this->get_device_experience_by_field($res_name, $res_id, $field);
            }

            foreach ($result as $k => $v) {
                $v = (array)$v;
                //获取设备详情
                $screen_info = screen_device_helper::get_device_info_by_device($v['device_unique_id']);

                if (!$screen_info) {
                    continue;
                }
                $group_data[$screen_info[$this->detail_field]][$v['device_unique_id']] = $v[$field];
            }
        //终端上柜 查询所有设备
        } else if ($this->detail_data_type == 3) {
            //默认条件
            $filter = $this->get_default_device_filter($res_name, $res_id);

            //查询已覆盖厅店
            $device_business_hall = _model('screen_device')->getList($filter, ' GROUP BY business_id, device_nickname_id');
            foreach ($device_business_hall as $k => $v) {
                $group_data[$v[$this->detail_field]][$v['business_id']] = $v['business_id'];
            }

        }

        Response::assign('subordinate_res_name', $this->subordinate_res_name);

        return $group_data;
    }

    /**
     * 处理图表体验数据
     * @param unknown $data 数据
     * @param unknown $field 字段
     * @param unknown $is_average 是否为平均数
     */
    private function handle_experience_echarts($data, $field)
    {
        $sorts = array();
        //将device_nickname_id 放到数组值中
        $new_data = array();
        foreach ( $data as $k => $v ) {

            if ($field == 'experience_time') {
                   //平均
                   $v['data']               = round($v[$field] / $v['device_num'] / 60, 1); //分钟
            } else {
                $v['data']               = round($v[$field] / $v['device_num'], 1);
            }

            $v['device_nickname_id']        = $k;

            $sorts[]                        = $v['data'];
            $new_data[]                     = $v;
        }

        if ($sorts) {
            array_multisort($sorts, SORT_DESC, $new_data);
        }

        $new_data = array_slice($new_data, 0, $this->echarts_num);

        $echarts_data = array();
        $echarts_title = array();

        foreach ($new_data as $k => $v) {
            //获取设备昵称
            $nickname_info = screen_device_helper::get_device_nickname_info($v['device_nickname_id']);

            if ( !$nickname_info ) {
                continue;
            }

            $brand_name = empty($nickname_info['name_nickname']) ? $nickname_info['phone_name'] : $nickname_info['name_nickname'];
            $brand_name .= ' ';
            $brand_name .= empty($nickname_info['version_nickname']) ? $nickname_info['phone_version'] : $nickname_info['version_nickname'];


            $echarts_data[]     = $v['data'];
            $echarts_title[]    = msubstr($brand_name, 0, 7);
        }

        return array('title' => $echarts_title, 'data' => $echarts_data);
    }

    /**
     * 初始化搜索
     */
    private function init_search()
    {
        $search_filter = tools_helper::Get('search_filter', array());

        if (empty($search_filter['date_type']) || !in_array($search_filter['date_type'], array(1, 3, 4))) {
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

        //主要用于指定机型品牌下的数据
        if (!empty($search_filter['device_nickname_id'])) {
            $this->device_nickname_id = $search_filter['device_nickname_id'];
        }

        //搜索类型 (主要用于详情页的体验时长或体验次数)
        if (!empty($search_filter['data_type']) && in_array($search_filter['data_type'], array(1, 2, 3))) {
            $this->detail_data_type = $search_filter['data_type'];
        } else {
            $this->detail_data_type     = 1;
            $search_filter['data_type'] = 1;
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

        Response::assign('search_filter', $search_filter);
        Response::assign('search_filter_str', $search_filter_str);
    }

    /**
     * 获取设备体验时长或体验次数
     * @param unknown $res_name 登录者res_name
     * @param unknown $res_id 登录者res_id
     * @param unknown $field  screen_device_stat_day集合中的字段
     * @return unknown
     */
    private function get_device_experience_by_field($res_name, $res_id, $field, $devices=array())
    {
        //借用设备的默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);

        if ($devices) {
            $filter['device_unique_id'] = $devices;
        }

        //清除掉设备表特有的status字段条件
        unset($filter['status']);

        $filter['day >='] = date('Ymd', strtotime($this->start_date));
        $filter['day <='] = date('Ymd', strtotime($this->end_date));

        $result       = _mongo('screen', 'screen_device_stat_day')->aggregate(array(
                array('$match' => get_mongodb_filter($filter)),
                array('$group' => array(
                        '_id'       => array('device_unique_id'  => '$device_unique_id', 'business_id' => '$business_id'),
                        $field      => array('$sum' =>'$'.$field),
                        'device_unique_id'  => array('$first' => '$device_unique_id'),
                        'business_id'       => array('$first' => '$business_id'),
                )),
                //一定要排序，因为设备换厅后，一定是在最后面的
                array('$sort'=>array('_id'=>-1)),
        ));

        //去除已下柜的设备
        $filter = $this->get_default_device_filter($res_name, $res_id);
        $filter['status'] = 0;
        $where  = to_where_sql($filter);
        $off_device = _model('screen_device')->getAll(' SELECT business_id, device_unique_id FROM `screen_device` '.$where);
        $new_off_device = array();
        foreach ($off_device as $k => $v) {
            $new_off_device[$v['business_id'].'_'.$v['device_unique_id']] = $v['device_unique_id'];
        }

        $new_result = array();
        foreach ($result as $k => $v) {
            $v = (array)$v;
            if (!isset($new_off_device[$v['business_id'].'_'.$v['device_unique_id']])) {
                $new_result[$v['device_unique_id']] = $v;
            }
        }

        return $new_result;
    }

    /**
     * 按品牌分组体验数据
     * @param unknown $data
     */
    private function group_experience_by_brand($data, $field)
    {
        //按品牌型号分组
        $arr = array();
        foreach ($data as $k => $v) {
            $v = (array)$v;
            //获取机型id
            $device_nickname_id = screen_device_helper::get_device_info_by_device($v['device_unique_id'], 'device_nickname_id');
            if (!$device_nickname_id) {
                continue;
            }

            if (empty($arr[$device_nickname_id])) {
                $arr[$device_nickname_id][$field]  = $v[$field];
                $arr[$device_nickname_id]['device_num']        = 1;
            } else {
                $arr[$device_nickname_id][$field]  += $v[$field];
                $arr[$device_nickname_id]['device_num']        += 1;
            }
        }

        return $arr;
    }


    /**
     * 获取活跃设备 （默认今天）
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_active_device($res_name, $res_id, $device_nickname_id = 0)
    {

        //借用设备的默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);

        //在线
        $filter['day >=']       = date('Ymd', strtotime($this->start_date));
        $filter['day <=']       = date('Ymd', strtotime($this->end_date));

        //清除掉设备表特有的status字段条件
        unset($filter['status']);

        if ($device_nickname_id) {
            $filter['device_nickname_id'] = $device_nickname_id;
        }
        //获取设备
        $devices = _model('screen_device_online_stat_day')->getFields('device_unique_id', $filter, ' GROUP BY `device_unique_id`');
        return $devices;
//         //活跃的设备， 因为要处理下柜的设备，所以营业厅和设备id都查出
//         $online_info = _model('screen_device_online_stat_day')->getAll(' SELECT business_id, device_unique_id FROM `screen_device_online_stat_day` '.to_where_sql($filter).' GROUP BY `device_unique_id`, `business_id` ORDER BY `id` DESC');

//         //查询所有下架的设备
//         $filter = $this->get_default_device_filter($res_name, $res_id);
//         $filter['status'] = 0;
//         if ($device_nickname_id) {
//             $filter['device_nickname_id'] = $device_nickname_id;
//         }

//         $where  = to_where_sql($filter);
//         $off_device = _model('screen_device')->getAll(' SELECT business_id, device_unique_id FROM `screen_device` '.$where);

//         //去除下架的设备
//         $new_off_device = array();
//         foreach ($off_device as $k => $v) {
//             $new_off_device[$v['business_id'].'_'.$v['device_unique_id']] = $v['device_unique_id'];
//         }

//         $new_result = array();
//         foreach ($online_info as $k => $v) {
//             if (!isset($new_off_device[$v['business_id'].'_'.$v['device_unique_id']])) {
//                 $new_result[$v['device_unique_id']] = $v['business_id'];
//             }
//         }
//         return $new_result;
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
    private function get_cover_business_hall($res_name, $res_id, $device_nickname_id=0)
    {
        //初始化默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);

        if ($device_nickname_id) {
            $filter['device_nickname_id'] = $device_nickname_id;
        }

        //为了兼容后续有详情页，先把所有营业厅id取出
        $business_hall_ids = _model('screen_device')->getFields('business_id', $filter, ' GROUP BY `business_id` ');

        return $business_hall_ids;
    }

    /**
     * 获取有效厅店
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_valid_business_hall($res_name, $res_id, $device_nickname_id = 0)
    {
        //初始化默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);

        unset($filter['status']);

        if (!$filter) {
            $filter = array(1=>1);
        }

        if ($device_nickname_id) {
            $filter['device_nickname_id'] = $device_nickname_id;
        }

        //为了兼容后续有详情页，先把所有有设备营业厅id取出
        $business_hall_ids = _model('screen_device_valid_active_stat')->getFields('business_id', $filter, ' GROUP BY `business_id` ');
        return $business_hall_ids;
    }

    /**
     * 获取已安装设备
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_install_device($res_name, $res_id, $device_nickname_id=0, $is_all=false)
    {
        //初始化默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);

        if ($device_nickname_id) {
            $filter['device_nickname_id'] = $device_nickname_id;
        }

        //是否去除所有字段
        if ($is_all) {
            //为了兼容后续有详情页， 先把所有设备取出
            $devices = _model('screen_device')->getList($filter, 'GROUP BY `device_unique_id`');
        } else {
            //为了兼容后续有详情页， 先把所有设备取出
            $devices = _model('screen_device')->getFields('device_unique_id', $filter, 'GROUP BY `device_unique_id`');
        }


        return $devices;
    }

    /**
     * 获取新增设备
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_new_device($res_name, $res_id, $device_nickname_id)
    {
        //初始化默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);

        $filter['day >=']       = date('Ymd', strtotime($this->start_date));
        $filter['day <=']       = date('Ymd', strtotime($this->end_date));

        if ($device_nickname_id) {
            $filter['device_nickname_id'] = $device_nickname_id;
        }

        //获取指定日期的新增设备
        $new_devices = _model('screen_device')->getFields('device_unique_id', $filter);

        return $new_devices;
    }

    /**
     * 获取有效设备
     * @param unknown $res_name
     * @param unknown $res_id
     */
    private function get_valid_device($res_name, $res_id, $device_nickname_id=0)
    {
        //初始化默认条件
        $filter = $this->get_default_device_filter($res_name, $res_id);

        unset($filter['status']);

        if ($device_nickname_id) {
            $filter['device_nickname_id'] = $device_nickname_id;
        }

        if (!$filter) {
            $filter = array(1=>1);
        }

        //为了兼容后续有详情页，先把所有设备取出
        $devices = _model('screen_device_valid_active_stat')->getFields('device_unique_id', $filter);
        return $devices;
    }

    /**
     * 导出营业厅详情数据
     * @param unknown $stat_list
     */
    private function export_detail_by_business_hall($stat_list)
    {
        $params = array();

        if (!$stat_list) {
            return false;
        }

        $new_arr = array();
        foreach ($stat_list as $k => $v) {
            $new_arr[] = array(
                    'title'                         => $v['title'],
                    'install_device_count'          => $v['install_device_count'],
                    'active_device_count'           => $v['active_device_count'],
                    'data'                          => $v['data']
            );
        }

        $params['head'] = array('归属地/机型','总安装(台)', '活跃(台)');
        if ($this->detail_data_type == 1) {
            $params['head'][]   = '平均体验时长';
            $filename           = '终端体验时长排行';
        } else if ($this->detail_data_type == 2) {
            $params['head'][]   = '平均体验次数';
            $filename           = '终端体验次数排行';
        } else if ($this->detail_data_type == 3) {
            $params['head'][]   = '离线(台)';
            $filename           = '终端上柜占比';
        }

        $params['filename'] = $filename;
        $params['data']     = $new_arr;

        Csv::getCvsObj($params)->export();
    }

    /**
     * 导出详情页数据
     * @param unknown $stat_list
     */
    private function export_detail($stat_list)
    {
        $params = array();

        if (!$stat_list) {
            return false;
        }

        $new_arr = array();
        foreach ($stat_list as $k => $v) {
            $new_arr[] = array(
                    'title'                         => $v['title'],
                    'conver_business_hall_count'    => $v['conver_business_hall_count'],
                    'install_device_count'          => $v['install_device_count'],
                    'active_device_count'           => $v['active_device_count'],
                    'data'                          => $v['data']
            );
        }

        $params['head'] = array('归属地/机型', '覆盖厅店','总安装(台)', '活跃(台)');
        if ($this->detail_data_type == 1) {
            $params['head'][]   = '平均体验时长';
            $filename           = '终端体验时长排行';
        } else if ($this->detail_data_type == 2) {
            $params['head'][]   = '平均体验次数';
            $filename           = '终端体验次数排行';
        } else if ($this->detail_data_type == 3) {
            $params['head'][]   = '离线(台)';
            $filename           = '终端上柜占比';
        }

        $params['filename'] = $filename;
        $params['data']     = $new_arr;

        Csv::getCvsObj($params)->export();
    }

}