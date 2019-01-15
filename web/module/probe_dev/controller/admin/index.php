<?php

/**
 * 探针设备管理
 *
 * @author  wangl
 */

// load func.php
probe_helper::load('func');

// load trait table
probe_helper::load('table', 'trait');

class Action
{
    use table;

    /**
     * 数字地图的各种环境这里改
     * develop_url test_url demo_url office_url
     *
     */
    private $dm_url_key = 'office_url';

    /**
     * 每页展示多少条
     *
     * @var Int
     */
    private $per_page = 20;

    /**
     * 当前登录用户ID
     *
     * @var Int
     */
    private $member_id  = 0;

    /**
     * 当前登录用户信息
     *
     * @var Array
     */
    private $member_info = array();

    /**
     * 构造函数
     *
     * @author  wangl
     */
    public function __construct()
    {
        // 获取当前登录用户
        $this -> member_info = member_helper::get_member_info();

        if ( $this -> member_info ) {
            // 当前登录ID
            $this -> member_id = $this -> member_info['id'];
        }

        Response::assign('member_info', $this -> member_info);
    }

    /**
     * 设备列表页
     *
     * @author  wangl
     */
    public function index()
    {
        if (!in_array($this -> member_info['res_name'],['group','business_hall'])) {
            return '您暂无权限';
        }

        // 搜索
        $search_filter  = Request::Get('search_filter', array());
        $is_export      = Request::Get('is_export', 0);
        $status         = Request::Get('status', 1);
        $dev_status     = 0;

        $filter = array();

        // 搜索省
        if ( isset($search_filter['province']) && $search_filter['province'] ) {
            $filter['province_id'] = $search_filter['province'];
        }

        // 搜索市
        if ( isset($search_filter['city']) && $search_filter['city'] ) {
            $filter['city_id'] = $search_filter['city'];
        }

        // 搜索区
        if ( isset($search_filter['area']) && $search_filter['area'] ) {
            $filter['area_id'] = $search_filter['area'];
        }

        //搜索状态
        if ( isset($search_filter['dev_status']) && $search_filter['dev_status'] && in_array($search_filter['dev_status'], array(0, 1, 2, 3, 4, 5, 6)) ) {
            $dev_status = $search_filter['dev_status'];
        }

        // 搜索营业厅
        if ( isset($search_filter['business']) && $search_filter['business'] ) {
            $b_info = business_hall_helper::get_info_name('business_hall', array('title'=>$search_filter['business']));
            $filter['business_id'] = $b_info ? $b_info['id'] : -1;
        }

        if ($this -> member_info['res_name'] == 'business_hall') {
            $filter['business_id'] = $this->member_info['res_id'];
        }

        // 按状态查询
        $filter['status'] = $status;

        $count = _model('probe_device')->getTotal($filter);

        $list  = array();

        if ($count) {
            if ($is_export == 1) {

                $list = _model('probe_device')->getList($filter);

            //如果是全部状态，则分页，
            } else if ($dev_status == 0) {

                $pager = new Pager($this->per_page);
                $list  = _model('probe_device')->getList($filter, $pager->getLimit());
                if ($pager->generate($count)) {
                    Response::assign('pager', $pager);
                }
            //如果是指定状态则不分页，不好分页
            } else {
                $list  = _model('probe_device')->getList($filter);
            }

        }

        foreach ( $list as $k => &$v ) {

            $v['dev_status'] = get_dev_status($v);

            //非正常状态
            if ($dev_status == 1 && $v['dev_status'] !== 1) {

                    unset($list[$k]);
                    continue;
            }

            //非故障状态
            if ($dev_status == 2 && $v['dev_status'] !== 2) {
                unset($list[$k]);
                continue;
            }

            //非已申请状态
            if ($dev_status == 3 && $v['dev_status'] !== 3) {
                unset($list[$k]);
                continue;
            }

            //非已发货状态
            if ($dev_status == 4 && $v['dev_status'] !== 4) {
                unset($list[$k]);
                continue;
            }

            //非待安装状态
            if ($dev_status == 5 && $v['dev_status'] !== 5) {
                unset($list[$k]);
                continue;
            }

            //非无数据状态
            if ($dev_status == 6 && $v['dev_status'] !== 6) {
                unset($list[$k]);
                continue;
            }

            //都不是则全部
        }

        unset($v);

        if ($is_export == 1) {
            $this->export($list);
            exit;
        }

        Response::assign('status', $status);
        Response::assign('list', $list);
        Response::assign('search_filter', $search_filter);
        Response::display("admin/index.html");
    }

    /**
     * 添加设备
     *
     * @author  wangl
     */
    public function add()
    {
        Response::display("admin/add.html");
    }

    /**
     * 删除信息
     * @return string
     */
    public function delete()
    {
        $id = Request::Post('id', 0);

        if ( empty($id) ) {
            return '请选择您要操作的信息';
        }

        $id = explode(',', trim($id, ','));
        foreach ($id as $v) {
            $info = _uri('probe_device', array('id'=>$v));

            if (!$info) {
                continue;
            }

            if ($info['status'] != 0) {
                _model('probe_device')->update(array('id' => $v), array('status'=>0));

                $b_info = business_hall_helper::get_info_name('business_hall', $info['business_id']);

                //添加探针传递给数字地图
                $param = array(
                        'user_number' => $b_info['user_number'],
                        'device_num'  => $info['device'],
                        'type'        => 'delete',
                        'c_url'       => $this->dm_url_key //环境切换
                );

                probe_dev_helper::sync_dm_data($param);
            }
        }

        return 'ok';
    }

    /**
     * 恢复
     *
     * @return  String
     */
    public function recovery()
    {
        $id = Request::Post('id', 0);

        if ( !$id ) {
            return '请选择要回复的数据';
        }

        $info = _model('probe_device')->read($id);

        if ( !$info ) {
            return '要恢复的数据不存在';
        }

        if ( $info['status'] == 1 ) {
            return 'ok';
        }

        $dev_info = _model('probe_device')->read(array('device' => $info['device'], 'status' => 1));

        if ( $dev_info ) {
            return '已存在相同设备编号的探针';
        }

        $res = _model('probe_device')->update(array('id' => $id), array('status' => 1));

        if ( !$res ) {
            return '恢复失败';
        }

        return 'ok';
    }

    public function edit()
    {
        $id          = Request::Get('id', 0);

        if (!$id) {
            return '请选择您要操作的信息';
        }

        $info = _uri('probe_device', $id);

        if ( !$info ) {
            return '您操作的信息不存在';
        }

        Response::assign('info', $info);

        Response::display("admin/add.html");
    }

    public function save()
    {
        $id     = Request::Post('id', 0);
        $p_id   = Request::Post('province', 0);
        $c_id   = Request::Post('city', 0);
        $a_id   = Request::Post('area', 0);
        $b_id   = Request::Post('business', 0);
        $mac    = strtolower(Request::Post('mac', ''));
        $rssi   = Request::Post('rssi', 0);
        $remarks= Request::Post('remarks', '');

        if ( !$p_id ) {
            return '请选择省';
        }

        if ( !$mac ) {
            return '请输入MAC地址！';
        }

        $p_info = business_hall_helper::get_info_name('province', $p_id);
        if ( !$p_info ) {
            return '省不存在';
        }
        if ( !$c_id ) {
            return '请选择市';
        }
        $c_info = business_hall_helper::get_info_name('city', $c_id);
        if ( !$c_info ) {
            return '市不存在';
        }
        if ( !$a_id ) {
            return '请选择区';
        }
        $a_info = business_hall_helper::get_info_name('area', $a_id);
        if ( !$a_info ) {
            return '区不存在';
        }
        if ( !$b_id ) {
            return '请选择营业厅';
        }
        $b_info = business_hall_helper::get_info_name('business_hall', $b_id);
        if ( !$b_info ) {
            return '营业厅不存在';
        }
        if ( $b_info['province_id'] != $p_id || $b_info['city_id'] != $c_id || $b_info['area_id'] != $a_id ) {
            return '营业厅不存在';
        }

        $device = strtolower($device);

       //查询是否存在唯一编码  如果存在则替换编码
        $device = probe_dev_helper::get_device_for_mac($mac);

        if ( !$rssi ) {
            return '请输入探测范围';
        }
        if ( !is_numeric($rssi) ) {
            return '探测范围只能是数字';
        }

        $info = _model('probe_device')->read(array('device'=>$device, 'status'=>1));

        if ( $info && $info['id'] != $id ) {
            return '该设备已存在';
        }

        // 创建数据表
        if ( !$this->create_table($b_id) ) {
            return '创建数据表失败';
        }

        $info = array(
            'province_id'   =>  $p_id,
            'city_id'       =>  $c_id,
            'area_id'       =>  $a_id,
            'business_id'   =>  $b_id,
            'device'        =>  $device,
            'mac'           => $mac,
            'rssi'          =>  abs($rssi),
            'remarks'       =>  $remarks
        );

        if ( $id ) {
            _model('probe_device')->update($id, $info);
        } else {
            $id = _model('probe_device')->create($info);

            if ( !$id ) {
                return '添加失败';
            }

            //添加探针传递给数字地图
            $param = array(
                    'user_number' => $b_info['user_number'],
                    'device_num'  => $mac,
                    'type'        => 'create',
                    'c_url'       => $this->dm_url_key //环境切换
            );

            probe_dev_helper::sync_dm_data($param);
        }

        Response::redirect(AnUrl('probe_dev/admin'));
    }

    /**
     * 设备在线列表
     *
     * @return  String
     */
    public function export($list, $type=0)
    {

        $online_list = array();

        foreach ($list as $k => $v) {

            if ( empty($v['business_id']) || empty($v['device']) ) {
                continue;
            }

            $business_hall_info = _uri('business_hall', $v['business_id']);

            if (!$business_hall_info) {
                continue;
            }

            $online_info = array(
                    'business_title'    => $business_hall_info['title'],
                    'user_number'       => $business_hall_info['user_number'],
                    'device'            => $v['device'],
            );

            if (!empty(probe_dev_config::$dev_status[$type]['status'])) {
                $online_info['dev_status'] = probe_dev_config::$dev_status[$type]['status'];
            } else {
                $dev_status = get_dev_status($v);
                if (!empty(probe_dev_config::$dev_status[$dev_status]['status'])) {
                    $online_info['dev_status'] = probe_dev_config::$dev_status[$dev_status]['status'];
                } else {
                    $online_info['dev_status'] = '未知';
                }
            }

            $online_list[] = $online_info;
        }

        $params['filename'] = date('Ymd').'探针设备列表';
        $params['data']     = $online_list;
        $params['head']     = array('营业厅名称', '渠道码' ,'设备', '设备状态');

        Csv::getCvsObj($params)->export();

    }


    /**
     * 设备在线列表
     *
     * @return  String
     */
    public function device_online_list($filter)
    {

        $list  = _model('probe_device')->getList($filter);

        $online_list = array();

        foreach ($list as $k => $v) {

            // 初始化数据库操作对象
            $db  = get_db($v['business_id']);

            //wangjf add
            $last_info = $db->read(array('dev' => $v['device']), ' ORDER BY `id` DESC ');

            if ( !$last_info ) {
                continue;
            }

            if ( $last_info['update_time'] >= date('Y-m-d 00:00:00') ) {
                $device_info = array(
                        'business_title'    => $business_hall_info['title'],
                        'user_number'       => $business_hall_info['user_number'],
                        'device'            => $v['device']
                );
                $online_list[] = $device_info;
            }
        }

        if ($is_export != 1) {
            var_dump($online_list);exit;
        }

        $params['filename'] = date('Ymd').'探针设备在线列表';
        $params['data']     = $online_list;
        $params['head']     = array('营业厅名称', '渠道码' ,'设备');

        Csv::getCvsObj($params)->export();

    }
}