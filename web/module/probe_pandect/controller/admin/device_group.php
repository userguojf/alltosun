<?php

/**
 * alltosun.com  探针、rfid设备一览
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * szy: 宋志宇 (songzy@alltosun.com)
 * 2018年1月3日: 2016-7-26 下午3:05:10
 * Id
 */
// load func.php
probe_helper::load('func');

// load trait table
probe_helper::load('table', 'trait');

include MODULE_PATH.'/rfid/server/config.php';
//放缓存
include MODULE_PATH.'/rfid/server/lib/RedisCache.php';
//清缓存
include MODULE_PATH.'/rfid/server/src/secret_helper.php';
require_once ROOT_PATH."/helper/PHPExcel.php";
require_once ROOT_PATH."/helper/PHPExcel/Writer/Excel2007.php";

class Action
{
    use table;

    /**
     * 数字地图的各种环境这里改
     * develop_url test_url demo_url office_url
     *
     */
    private $dm_url_key = 'office_url';
    private $per_page = 9;
    private $member_id  = 0;
    private $member_info = array();


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
     * 
     */
    public function index()
    {

        if ('group' != $this -> member_info['res_name']) {
            return '您暂无权限';
        }
        // 搜索
        $search_filter  = Request::Get('search_filter', array());
        $is_export      = Request::Get('is_export', 0);
        $status         = Request::Get('status', 1);
        $dev_status     = 0;
        $search_filter['put_type'] = 1;
        //$search_filter[search_type] 0 探针  1 rfid
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
            // 按状态查询
            $filter['status'] = $status;

            if ($search_filter['search_type'] == 0) {
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
            }
            //查rfid 1
            if ($search_filter['search_type'] == 1) {
                Response::redirect(AnUrl('probe_pandect/admin/device_group/rfid_list?search_filter[search_type]=1&search_filter[put_type]=1'));
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
     *  待审批设备列表
     */
    public function approve_list()
    {
        $search_filter  = Request::Get('search_filter', array());
        $filter = array();
        $filter['error_type'] = '正确数据';
        $filter['status != '] = 1;
        //put_type 3 已审核的
        if ($search_filter['put_type'] == 3) {
            Response::redirect(AnUrl('probe_pandect/admin/device_group/approved_list?search_filter[put_type]=3'));
        }
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

        // 按状态
        if ($search_filter['status'] == 1) {
            $filter['status'] = 0;
        }

        if ($search_filter['status'] == 2) {
            $filter['status'] = 2;
        }

        // 搜索营业厅
        if ( isset($search_filter['business']) && $search_filter['business'] ) {
            $b_info = business_hall_helper::get_info_name('business_hall', array('title'=>$search_filter['business']));
            $filter['business_id'] = $b_info ? $b_info['id'] : -1;
        }

        if($search_filter['search_type'] == 1){
            $filter['device_type'] = '探针';
        }else if ($search_filter['search_type'] == 2){
            $filter['device_type'] = 'rfid';
        }
        $list = _model('device_application')->getList($filter, 'GROUP BY create_time ,`city_id`  ORDER BY `create_time` desc');

        $count = count($list);
        if($count){
            $pager = new Pager($this->per_page);
            $list = _model('device_application')->getList($filter, 'GROUP BY create_time ,`city_id`  ORDER BY `create_time` desc '.$pager->getLimit());
            if($pager->generate($count)){
                Response::assign('pager',$pager);
            }
        }

        Response::assign('list', $list);
        Response::assign('search_filter', $search_filter);
        Response::display("admin/approve_list.html");
    }

    /**
     * 已审批设备列表
     */
    public function approved_list()
    {
        $search_filter  = Request::Get('search_filter', array());
        $filter = array();
        $filter['error_type'] = '正确数据';
        $filter['status'] = 1;
        $filter['order_status'] = 1;
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
        // 搜索营业厅
        if ( isset($search_filter['business']) && $search_filter['business'] ) {
            $b_info = business_hall_helper::get_info_name('business_hall', array('title'=>$search_filter['business']));
            $filter['business_id'] = $b_info ? $b_info['id'] : -1;
        }

        if ($search_filter['order_type'] == 0 ) {
            $filter['order_status'] = 1;
        }


        if ($search_filter['order_type'] == 2 ) {
            $filter['order_status'] = 3;
            $filter['status'] = 1;
        }
        if($search_filter['search_type'] == 1){
            $filter['device_type'] = '探针';
        }else if ($search_filter['search_type'] == 2){
            $filter['device_type'] = 'rfid';
        }
        //已发货列表
        if ($search_filter['order_type'] == 1 ) {
            Response::redirect(AnUrl('probe_pandect/admin/device_group/send_list?search_filter[put_type]=3&search_filter[order_type]=1'));
        }
        
        $list = _model('device_application')->getList($filter, 'GROUP BY create_time ,`city_id`  ORDER BY `create_time` desc');
        $count = count($list);
        if($count){
            $pager = new Pager($this->per_page);
            $list = _model('device_application')->getList($filter, 'GROUP BY create_time ,`city_id`  ORDER BY `create_time` desc '.$pager->getLimit());
            if($pager->generate($count)){
                Response::assign('pager',$pager);
            }
        }

        Response::assign('list', $list);
        Response::assign('search_filter', $search_filter);
        Response::display("admin/approved_list.html");
    }
    
    /**
     *已发货列表
     */
    public function send_list()
    {
        $search_filter = Request::Get('search_filter',array());
        $search_filter[order_type] = 1;
        $filter['order_status'] = 2;
        
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
        // 搜索营业厅
        if ( isset($search_filter['business']) && $search_filter['business'] ) {
            $b_info = business_hall_helper::get_info_name('business_hall', array('title'=>$search_filter['business']));
            $filter['business_id'] = $b_info ? $b_info['id'] : -1;
        }
        
        // 搜快递单号
        if ( isset($search_filter['order_code']) && $search_filter['order_code'] ) {
            $filter['order_code'] = $search_filter['order_code'];
        }
        
        if($search_filter['search_type'] == 1){
            $filter['device_type'] = '探针';
        }else if ($search_filter['search_type'] == 2){
            $filter['device_type'] = 'rfid';
        }
        $list = _model('device_application')->getList($filter, ' ORDER BY `create_time` desc');
        $count = count($list);
        if($count){
            $pager = new Pager($this->per_page);
            $list = _model('device_application')->getList($filter, 'GROUP BY create_time ,`city_id`  ORDER BY `create_time` desc '.$pager->getLimit());
            if($pager->generate($count)){
                Response::assign('pager',$pager);
            }
        }
        Response::assign('list', $list);
        Response::assign('search_filter', $search_filter);
        Response::display("admin/send_goods_list.html");
    }

    /**
     *已发货设备详情
     */
    public function view_send_details()
    {
        
        $id = Request::Get('id',0);
        $device_type = Request::Get('device_type','');
        $is_error_export = Request::Get('is_error_export',0);
        $list =array();
        $list_tmp = _model('goods_contact_extend_relation')->getList(array('application_id' => $id));
        $count = count($list_tmp);
        if($count){
            $pager = new Pager($this->per_page);
            if($pager->generate($count)){
                $list = _model('goods_contact_extend_relation')->getList(array('application_id' => $id),$pager->getLimit());
                Response::assign('pager',$pager);
            }
        }
        foreach ($list as $k => $v){
            $phone_list = explode(",",$v['phone']);
            $email_list = explode(",",$v['email']);
            $linkman_list = explode(",",$v['linkman']);
        }
       
        //导出错误数据
        if ($is_error_export == 1) {
            $send_list =array();
            $this->send_export($list_tmp,$device_type);
            return array('导出成功','success', AnUrl("probe_pandect/admin/view_send_details"));
            exit();
        }
        
        Response::assign('list', $list);
        Response::assign('id', $id);
        Response::assign('device_type', $device_type);
        Response::assign('phone_list', $phone_list);
        Response::assign('email_list', $email_list);
        Response::assign('linkman_list', $linkman_list);
        Response::assign('search_filter', $search_filter);
        Response::display("admin/view_send_details.html");
    }
    
    
    /**
     * 导出已发货设备
     *
     * @return  String
     */
    public function send_export($list,$device_type)
    {
    
        $info = array();
        foreach ($list as $k => $v) {
    
            $info = array(
                    'device_type' => $device_type,
                    'linkman'     => $v['linkman'],
                    'phone'       => $v['phone'],
                    'email'       => $v['email'],
                    'device_mac_label_id'   => $v['device_mac_label_id'],
                    'add_time'      => $v['add_time'],
            );
            $info_list[] = $info;
        }
    
        $params['filename'] = date('Ymd').'已发货设备列表';
        $params['data']     = $info_list;
        $params['head']     = array('设备类型','联系人', '联系电话' ,'邮箱', '设备编码','发货时间');
    
        Csv::getCvsObj($params)->export();
    
    }
    
    
    /**
     *rfid 列表
     */
    public function rfid_list()
    {
        $search_filter = Request::get('search_filter', array());
        $order         = " ORDER BY `id`  DESC ";

        $page          = tools_helper::get('page_no', 1);

        $filter =  _widget('rfid')->init_filter($this->member_info, $search_filter);
        //标签
        if(!empty($search_filter['label_id'])) {
            $filter['label_id'] = trim($search_filter['label_id']);
        }

        //专柜
        if(!empty($search_filter['shoppe_id'])) {
            $filter['shoppe_id'] = trim($search_filter['shoppe_id']);
        }

        if (!$filter) {
            $filter[1] = 1;
        }

        if ( isset($filter['business_id']) ) {
            $filter['business_hall_id'] = $filter['business_id'];
            unset($filter['business_id']);
        }

        $count = _model('rfid_label')->getTotal($filter);
        $rfid_list = array();
        if ($count) {
            $pager  = new Pager($this->per_page);
            $rfid_list   = _model('rfid_label')->getList($filter ,$order.$pager->getLimit($page));

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
        }
        //获取柜台
        $shoppe_list = shoppe_helper::get_business_hall_shoppe($this->member_info['res_name'], $this->member_info['res_id']);
        Response::assign('count', $count);
        Response::assign('shoppe_list', $shoppe_list);
        Response::assign('rfid_list' , $rfid_list );
        Response::assign('search_filter',$search_filter);
        Response::assign('page' , $page);
        Response::display('admin/rfid_list.html');
    }

    /**
     * 添加探针设备
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
     * 删除信息
     * @return string
     */
    public function change_approve()
    {
        $id = Request::Post('id', 0);
        $status = Request::Post('status', 0);

        if ( empty($id) ) {
            return '请选择您要操作的信息';
        }

        $id = explode(',', trim($id, ','));
        foreach ($id as $v) {
            $info = _uri('device_application', array('id'=>$v));

            if (!$info) {
                continue;
            }
            _model('device_application')->update(array('id' => $v), array('status'=>$status));

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
        $device = Request::Post('device', '');
        $rssi   = Request::Post('rssi', 0);
        $remarks= Request::Post('remarks', '');

        if ( !$p_id ) {
            return '请选择省';
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
        if ( !$device ) {
            return '请输入设备编号';
        }
        if ( !$rssi ) {
            return '请输入探测范围';
        }
        if ( !is_numeric($rssi) ) {
            return '探测范围只能是数字';
        }

        //wangjf add 将设备转换为小写
        $device = strtolower($device);

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
                    'device_num'  => $device,
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
     * 分时统计
     *
     * @return string
     */
    public function hour()
    {
        // date指定查看哪天数据
        $date = Request::Get('date', '');
        // b_id指定查看哪个营业厅
        $b_id = Request::Get('b_id', 0);

        if ( !$this -> member_info ) {
            return '请先登录';
        }

        // 注：别的地方点来时传的参数不是b_id这里做下兼容
        if ( !$b_id ) {
            $b_id = Request::Get('business_id', 0);
        }

        if ( !$b_id ) {
            // 注意：记录统计以厅为单位，当没有指定营业厅时，如果当前管理员是厅管理员时，取自身所在的厅，否则无法查看
            if ( $this->member_info['res_name'] != 'business_hall' ) {
                return '请选择营业厅';
            }

            $b_id = $this->member_info['res_id'];
        }

        if ( !$b_id ) {
            return '请选择营业厅';
        }

        // 拿营业厅信息
        $b_info = business_hall_helper::get_business_hall_info($b_id);

        if ( !$b_info ) {
            return '营业厅不存在';
        }

        if ( !visit_auth($this -> member_info, 'business_hall', $b_id) ) {
            return '您没权限访问';
        }

        // 获取营业厅设备
        $devs = probe_dev_helper::get_devs($b_id);

        if ( !$devs ) {
            return '营业厅下没有设备';
        }
        // 时间
        if ( !$date ) {
            $date = date('Y-m-d');
        }

        // 具体逻辑在probe/core/trait/stat.php
        $data = probe_pandect_helper::hour_stat($b_id, $date);
        $indoor = 0;
        $oudoor = 0;

        foreach ( $data as $k => $v ) {
            $indoor += $v['indoor'];
            $oudoor += $v['oudoor'];
        }

        Response::assign('date', $date);
        Response::assign('data', $data);
        Response::assign('b_info', $b_info);
        Response::assign('devs', $devs);
        Response::assign('indoor', $indoor);
        Response::assign('oudoor', $oudoor);
        Response::assign('date_type', 'hour');
        Response::display('admin/hour.html');
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

    public function add_rfid()
    {
        $id = Request::get('id' , 0);

            $filter = array();
            //权限
            if ($this->member_info['res_name'] == 'group') {
                $filter = array('1' => 1);

            } else if ($this->member_info['res_name'] == 'province') {
                $filter['province_id'] = $this->member_info['res_id'];
                Response::assign('province_arr' , array('province_id' => $this->member_info['res_id']));

            } else if ($this->member_info['res_name'] == 'city') {
                $filter['city_id'] = $this->member_info['res_id'];
                Response::assign('city_arr' , array('city_id' => $this->member_info['res_id']));

            } else if ($this->member_info['res_name'] == 'area') {
                $filter['area_id'] = $this->member_info['res_id'];

            } else if ($this->member_info['res_name'] == 'business_hall') {
                $filter['business_id'] = $this->member_info['res_id'];

            }

            if ($id) {
                //rfid_label表信息
                $rfid_info = _model('rfid_label')->read(array('id' => $id));

                if (!$rfid_info) {
                    return '该数据已经不存在，返回请刷新';
                }
                //rfid_phone表 手机信息
                //             $phone_info = _model('rfid_phone')->read(array('id' => $rfid_info['phone_id']));

                //business_hall表营业厅信息
                $business_hall_info = _model('business_hall')->read(array('id' => $rfid_info['business_hall_id']));

                Response::assign('id' , $id);

                Response::assign('province_id' , $business_hall_info['province_id']);
                Response::assign('city_id' , $business_hall_info['city_id']);
                Response::assign('area_id' , $business_hall_info['area_id']);
                Response::assign('province_arr' , array('province_id' => $business_hall_info['province_id']));
                Response::assign('city_arr' , array('city_id' => $business_hall_info['city_id']));

                //             Response::assign('phone_name' , $phone_info['name']);
                //             Response::assign('phone_varsion' , $phone_info['version']);

                Response::assign('rfid_info' , $rfid_info);
            }

            Response::display('admin/rfid_add.html');
        }

        public function save_rfid()
        {
            $id         = Request::post('id' , 0);
            $rfid_info  = Request::post('rfid_info' , array());

            //传给数字地图参数
            $param = array();
            /**
             * 表单验证
             */

            if ($this->member_info['res_name'] != 'business_hall') {
                if (!isset($rfid_info['business_hall_id']) || !$rfid_info['business_hall_id']) return '请选择厅店';
            }

            if (!isset($rfid_info['label_id']) || !$rfid_info['label_id']) return '请填写标签ID';

            if (!$id) {
                //标签ID唯一判断
                $is_exist_info = _model('rfid_label')->read(array('label_id' => $rfid_info['label_id']));

                if ($is_exist_info) {
                    return '填写的标签ID已存在';
                }
            }

            if (!isset($rfid_info['name']) || !$rfid_info['name']) {
                return '请选择手机品牌';
            }

            if (!isset($rfid_info['version']) || !$rfid_info['version']) {
                return '请选择型号';
            }

            if (!isset($rfid_info['color']) || !$rfid_info['color']) {
                return '请选择颜色';
            }

            if (!isset($rfid_info['imei']) || !$rfid_info['imei']) {
                return '请填写IMEI末六位';
            }

            if (strlen($rfid_info['imei']) != 6) {
                return 'IMEI输入的应该是六位的数字';
            }

            if (!isset($rfid_info['shoppe_id']) || !$rfid_info['shoppe_id'] ) {
                return '请选择柜台';
            }

            //获取手机信息的ID
            $phone_id = _uri('rfid_phone',
                    array(
                            'name'    => $rfid_info['name'],
                            'version' => $rfid_info['version'],
                            'color'   => $rfid_info['color']
                    ),
                    'id');

                    if (!$phone_id) {
                        return '手机信息刚刚被删除，请重新选择手机信息';
                    }

                    //手机信息
                    $rfid_info['phone_id'] = $phone_id;

                    if ($this->member_info['res_name'] == 'business_hall') {
                        $business_hall_info = _model('business_hall')->read(array('id' => $this->member_info['res_id']));
                        $rfid_info['business_hall_id'] = $business_hall_info['id'];

                    } else {
                        $business_hall_info = _model('business_hall')->read(array('id' => $rfid_info['business_hall_id']));

                    }

                    if (!$business_hall_info) {
                        return '未找到营业厅信息，可能由于某些操作删除';
                    }

                    $rfid_info['province_id'] = $business_hall_info['province_id'];
                    $rfid_info['city_id']     = $business_hall_info['city_id'];
                    $rfid_info['area_id']     = $business_hall_info['area_id'];


                    if(!$id) {
                        //数字地图需要数据
                        $param = array(
                                'type'        => 'create',
                                'user_number' => $business_hall_info['user_number'],
                                'label_id'    => $rfid_info['label_id'],
                                'phone_name'    => $rfid_info['name'],
                                'phone_version' => $rfid_info['version'],
                                'shoppe_id'     => $rfid_info['shoppe_id'],
                        );

                        //传给数字地图并记录日志
                        rfid_helper::create_api_log($param);
                        // p($rfid_info);p($param);exit();
                        //创建
                        _model('rfid_label')->create($rfid_info);
                    } else {
                        //缓存
                        secret_helper::update_secret($rfid_info['label_id']);

                        //更新
                        _model('rfid_label')->update($id , $rfid_info);
                    }

                    return array('操作成功', 'success', AnUrl("probe_pandect/admin/device_group/rfid_list?search_filter[search_type]=1&search_filter[put_type]=1"));
        }

        //删除
        public function delete_rfid()
        {
            $id = Request::getParam('id' , 0);

            if (!$id) {
                return array('info'=>'请选择删除的数据');
            }

            $rfid_info = _model('rfid_label')->read(array('id' => $id));

            if (!$rfid_info) {
                return '未找到RFID信息，可能由于某些操作删除';
            }

            $business_hall_info = _model('business_hall')->read(array('id' => $rfid_info['business_hall_id']));

            if (!$business_hall_info) {
                return '未找到营业厅信息，可能由于某些操作删除';
            }

            //数字地图需要数据
            $param = array(
                    'type'          => 'delete',
                    'user_number'   => $business_hall_info['user_number'],
                    'label_id'      => $rfid_info['label_id'],
                    'phone_name'    => $rfid_info['name'],
                    'phone_version' => $rfid_info['version'],
                    'shoppe_id'     => $rfid_info['shoppe_id'],
            );

            //传给数字地图
            rfid_helper::create_api_log($param);

            //删除
            _model('rfid_label')->delete(array('id' => $id));

            return array('info' => 'ok');

        }

        /**
         * 待审批设备详情页
         */
        public function application_details()
        {
            $province_id = Request::Get('province_id','');
            $city_id     = Request::Get('city_id','');
            $create_time = Request::Get('create_time','');
            $device_type = Request::Get('device_type','');

            $filter = array(
                    'device_type' => $device_type,
                    'province_id' => $province_id,
                    'city_id'     => $city_id,
                    'create_time' => $create_time,
                    'error_type' => '正确数据',
            );

            $count = _model('device_application')->getTotal($filter);
            $list  = array();
            if ($count) {
                if ($dev_status == 0) {
                    $pager = new Pager($this->per_page);
                    $list  = _model('device_application')->getList($filter, $pager->getLimit());
                    if ($pager->generate($count)) {
                        Response::assign('pager', $pager);
                    }
                }
            }

            $per_tpl    = MODULE_PATH.'/probe_pandect/template/widget/application_details_admin_list.html';
           // $list = _model('device_application')->getList($filter);

            Response::assign('list',$list);
            Response::assign('filter',$filter);
            Response::assign('details','details');
            $html = Response::fetch($per_tpl);
            Response::display("admin/approve_list.html");
        }


        public function stat()
        {

            $search_filter = Request::Get('search_filter', array());
            $business_id   = Request::Get('business_id', 0);
            $filter      = array();
            $default_value  = array(
                    'phone_name'     => $search_filter['phone_name'],
                    'phone_version'  => $search_filter['phone_version'],
                    'phone_color'    => $search_filter['color'],
                    'business_id'    => $search_filter['business_id']
            );

            $filter  = set_search_filter_default_value($filter, $default_value);
            if($business_id){
                $filter['business_id'] = $business_id;

            }
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



            $table       = 'rfid_record';


            //时间过滤
            if ($start_date > $end_date) {
                return array('开始时间必须小于结束时间!');
            }

            if ($start_date > date('Y-m-d') || $end_date > date('Y-m-d')) {
                return array('开始时间或结束时间不能大于当前时间!');
            }

            //小时
            if ( $date_type == 'hour') {
                $filter['date']   = str_replace('-', '', $start_date);
                $end_date = '';

                //天
            } else if ( $date_type == 'day') {
                //条件
                $filter['date >='] =  str_replace('-', '', $start_date);;
                $filter['date <='] =  str_replace('-', '', $end_date);;
                //周
            } else if  ( $date_type == 'week') {
                //（条件）
                $filter['date >='] = date('Y').date('W',strtotime($start_date));
                $filter['date <='] = date('Y').date('W',strtotime($end_date));
                //月
            } else if ( $date_type == 'month') {
                //条件
                $filter['date >='] = date('Ym', strtotime($start_date));
                $filter['date <='] = date('Ym', strtotime($end_date));
            } else {
                return "无效的日期搜索类型“{$date_type}”";
            }
            //获取
            $stat_list = _model($table)->getList($filter, ' ORDER BY `id` DESC');
            //添加所需数据
            $stat_info =array();
           // $stat_info['count'] = $count;

            foreach ($stat_list as $k => $v){
                $stat_info['date_list'][]= $v['date'];
                $stat_info['time_count_list'][] = $v['experience_time'];
                $stat_info['number_count_list'][] = 1;

            }

            $date_list    = $stat_info['date_list'];
            $time_count_list   = $stat_info['time_count_list'];
            $number_count_list = $stat_info['number_count_list'];
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
            Response::assign('business_id', $business_id);
            Response::assign('search_filter', $search_filter);
            Response::assign('search_filter_str', $search_filter_str);
            Response::assign('json_number_count', json_encode($number_count_list));
            Response::assign('json_time_count', json_encode($time_count_list));
            Response::assign('json_date_list', json_encode($date_list));
            Response::display('admin/rfid_stat.html');
        }



        /**
         * rfid 统计详情
         * 详情
         */
        public function detail()
        {
            $date           = tools_helper::get('date', 0);
            $search_filter  = tools_helper::get('search_filter', array());
            $page           = tools_helper::get('page_no', 1);
            $filter = array();
            $default_value  = array(
                    'phone_name'     => $search_filter['phone_name'],
                    'phone_version'  => $search_filter['phone_version'],
                    'phone_color'    => $search_filter['phone_color'],
                    'business_id'    => $search_filter['business_id']
            );

            $filter  = set_search_filter_default_value($filter, $default_value);


            /**
             * 统计页跳转过来携带的条件
             */
            if (isset($search_filter['date_type']) && $search_filter['date_type']) {
                $date_type = $search_filter['date_type'];
            } else {
                $date_type = '';
            }

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

            if (isset($search_filter['date']) && $search_filter['date']) {
                $date = $search_filter['date'];
            } else {
                $date = '';
            }

            if ($date_type && ( !$date || !$start_date)) {
                return '参数不完整';
            }


            if ($date_type == 'hour') {

                $search_filter['start_date'] = $start_date;

                $end_date   = $search_filter['end_date']   = $start_date;

            } else if ($date_type == 'week') {

                list($start_time, $end_time) = explode('-', $date);
                $start_date = str_replace("/", '-', $start_time);
                $end_date   = str_replace("/", '-', $end_time);

                $start_date = $search_filter['start_date'] = $start_date;
                $end_date   = $search_filter['end_date'] = $end_date;

            } else if ($date_type == 'month') {

                $start_date = $search_filter['start_date'] = date('Y-m-01', strtotime($start_date));
                $end_date   =  $search_filter['end_date']  = date('Y-m-d',strtotime('-1 day', strtotime('+1 months', strtotime($start_date))));

            } else if ($date_type == 'day') {
                if (!$date) {
                    $date = date('Ymd');
                }

                $start_date = $search_filter['start_date'] = date('Y-m-d',strtotime($date));
                $end_date   = $search_filter['end_date'] = $start_date;
            }

            $filter['date >='] = str_replace('-', '', $start_date);
            $filter['date <='] = str_replace('-', '', $end_date);

            //原生Sql 分页
            $where = $this->where($filter);

            $group = 'GROUP BY business_id,label_id,phone_name,phone_version,phone_color';

            $count_sql = "SELECT count(*) as count_total, SUM(experience_time_count) as experience_time  FROM (SELECT SUM(experience_time) as experience_time_count  FROM `rfid_record` {$where} {$group}) as count_sql ";

            $count_info = _model('rfid_record')->getAll($count_sql);
        p($count_info);
            $count                  = $count_info[0]['count_total'];
            $experience_time_count  = $count_info[0]['experience_time'];

            $record_list            = array();

            if ($count) {

                $pager = new Pager($this->per_page);
                if ($pager->generate($count)) {
                    Response::assign('pager', $pager);
                }


                Response::assign('count', $count);

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


            Response::assign('search_filter_str', $search_filter_str);
            Response::assign('experience_time_count', $experience_time_count);
            Response::assign('search_filter', $search_filter);
            Response::assign('record_list', $record_list);
            Response::display('admin/rfid/detail.html');
        }


        /**
         * rfid记录列表
         */
        public function record()
        {
            $page           = tools_helper::get('page_no', 1);
            $search_filter  = tools_helper::get('search_filter', array());
            $order_field    = tools_helper::get('order_field', 'id');
            $order_dir      = tools_helper::get('order_dir', 'desc');
            $filter         = array();

            if ($this->member_res_name =='business_hall') {
                $search_filter['business_id'] = $this->member_res_id;
            }

            $business_title = member_helper::get_title_info($this->member_id);

            if (isset($search_filter['date']) && $search_filter['date']) {
                $filter['date ='] = str_replace('-', '', $search_filter['date']);
            } else {
                $filter['date = '] = str_replace('-', '',($search_filter['date'] = date('Y-m-d')));
            }

            if (isset($search_filter['business_id']) && $search_filter['business_id']) {
                $business_title = _uri('business_hall', $search_filter['business_id'], 'title');
                $filter['business_id'] = $search_filter['business_id'];
            }

            if (isset($search_filter['label_id']) && $search_filter['label_id']) {
                $filter['label_id'] = $search_filter['label_id'];
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
            Response::display('admin/rfid/record_list.html');
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

}