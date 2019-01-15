<?php
/**
 * alltosun.com  index.php
 * ============================================================================
 * 版权所有 (C) 2009-2015 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明: 这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王磊 (wangl@alltosun.com) $
 * $Date: 2017-4-26 下午5:41:56 $
*/

probe_helper::load('func');


probe_helper::load('stat', 'trait');

class Action
{
    use stat;

    /**
     * 管理员信息
     *
     * @var Array
     */
    private $member_info = array();

    /**
     * 表名
     *
     * @var String
     */
    private $table = 'probe_stat_hour';

    /**
     * 构造函数
     *
     * @return  Obj
     */
    public function __construct()
    {

        $this->member_info = member_helper::get_member_info();

        if (!$this->member_id) {
            qydev_helper::check_qydev_auth(AnUrl('e/admin/probe'));
        }

        Response::assign('member_info', $this->member_info);
        Response::assign('curr_page', 'probe');
    }

    /**
     * 移动版探针首页
     *
     * @return  String
     */
    public function index()
    {
        $date     = Request::Get('date', date('Y-m-d', time() - 3600*24));
        $res_name = Request::Get('res_name', '');
        $res_id   = Request::Get('res_id', 0);

        if ( !$this->member_info ) {
            return '请先登录';
        }

        // 访问资源权限验证
        if ( $res_name ) {
            if ( !visit_auth($this -> member_info, $res_name, $res_id) ) {
                return '您没权限访问';
            }
        } else {
            $res_name = $this -> member_info['res_name'];
            $res_id   = $this -> member_info['res_id'];
        }

        try {
            $return = $this->m_index($res_name, $res_id, $date);
        } catch (Exception $e) {
            return $e -> getMessage();
        }
        // an_dump($return);
        Response::assign('data', $return['stat']);
        Response::assign('indoor', $return['indoor']);
        Response::assign('outdoor', $return['outdoor']);
        Response::assign('new_num', $return['new_num']);
        Response::assign('old_num', $return['old_num']);
        Response::assign('remain_time', $return['remain']);
        Response::assign('curr_num', $return['curr_num']);
        Response::assign('brands', $return['brands']);
        Response::assign('now_week_data', $return['now_week_data']);
        Response::assign('prev_week_data', $return['prev_week_data']);
        Response::assign('hours', $return['hours']);
        Response::assign('res_name', $res_name);
        Response::assign('res_id', $res_id);
        Response::assign('date', $date);
        Response::display('admin/probe/index.html');
    }

    /**
     * 地区列表
     *
     * @return  String
     */
    public function region_list()
    {
        $res_name = Request::Get('res_name', '');
        $res_id   = Request::Get('res_id', 0);
        $date     = Request::Get('date', '');
        $hour     = Request::Get('hour', '');
        // 1为人流，2为客流
        $type     = Request::Get('type', 1);

        if ( !$this -> member_info ) {
            return '请先登录';
        }

        if ( !visit_auth($this -> member_info, $res_name, $res_id) ) {
            return '请先登录';
        }

        if ( !$date ) {
            return '请选择时间';
        }

        try {
            $list = $this -> m_day_stat($res_name, $res_id, $date, $hour, $type);
        } catch (Exception $e) {
            return $e -> getMessage();
        }

        Response::assign('res_name', $res_name);
        Response::assign('res_id', $res_id);
        Response::assign('date', $date);
        Response::assign('hour', $hour);
        Response::assign('list', $list);
        Response::assign('type', $type);
        Response::display('admin/probe/region_list.html');
    }

    /**
     * 营业厅mac地址列表
     *
     * @return  String
     */
    public function mac_list()
    {
        $b_id = Request::getParam('business_id', 0);
        $date = Request::getParam('date', '');
        $hour = Request::getParam('hour', '');
        $type = Request::getParam('type', 1);
        $page = Request::getParam('page', 1);
        $per_page = 20;
        $hours= array(
            '00', '01', '02', '03', '04', '05',
            '06', '07', '08', '09', '10', '11',
            '12', '13', '14', '15', '16', '17',
            '17', '19', '20', '21', '22', '23'
        );

        if ( !$this -> member_info ) {
            return '请先登录';
        }

        if ( !$b_id ) {
            return '请选择营业厅';
        }

        // 取营业厅信息
        $b_info = business_hall_helper::get_business_hall_info($b_id);

        if ( !$b_info ) {
            return '营业厅不存在';
        }

        if ( !visit_auth($this -> member_info, 'business_hall', $b_id) ) {
            return '您没权限访问';
        }

        if ( !$date ) {
            return '请选择时间';
        }

        $devs = probe_dev_helper::get_devs($b_id);

        if ( !$devs ) {
            return '该营业厅下没有探针设备';
        }

        if ( $hour !== '' ) {
            if ( !$hour || !is_numeric($hour) || (int)$hour > 23 ) {
                return '小时不正确';
            }
        }

        try {
            // 查询列表
            $ary   = $this -> m_mac_list($b_id, $date, $hour, $type);

            if ( empty($ary['count']) ) {
                $count = 0;
            } else {
                $count = $ary['count'];
            }

            if ( empty($ary['list']) ) {
                $list = array();
            } else {
                $list = $ary['list'];
            }
        } catch (Exception $e) {
            return $e -> getMessage();
        }

        if ( Request::isAjax() ) {
            foreach ($list as $k => $v) {
                $list[$k]['brand'] = probe_helper::get_brand($v['mac']);
                $list[$k]['mac']   = probe_helper::mac_encode($v['mac']);
                $list[$k]['hi']    = date('H:i', $v['frist_time']);
            }
            return array('info' => 'ok', 'list' => $list);
        }

        if ( is_numeric($date) ) {
            // 转换时间格式 Y-m-d
            $date = date('Y-m-d', strtotime($date));
        }

        Response::assign('hours', $hours);
        Response::assign('count', $count);
        Response::assign('per_page', $per_page);
        Response::assign('page', $page);
        Response::assign('b_info', $b_info);
        Response::assign('date', $date);
        Response::assign('hour', $hour);
        Response::assign('type', $type);
        Response::assign('list', $list);
        Response::display('admin/probe/mac_list.html');
    }

    /**
     * 营业厅探测到某个mac地址的详细信息
     *
     * @return  String
     */
    public function mac_detail()
    {
        $b_id = Request::Get('business_id', 0);
        $mac  = Request::Get('mac', '');

        if ( !$this -> member_info ) {
            return '请先登录';
        }

        if ( !$b_id ) {
            return '请选择营业厅';
        }

        // 取营业厅信息
        $b_info = business_hall_helper::get_business_hall_info($b_id);

        if ( !$b_info ) {
            return '营业厅不存在';
        }

        if ( !visit_auth($this -> member_info, 'business_hall', $b_id) ) {
            return '您没权限访问';
        }

        if ( !$mac ) {
            return '没有mac地址';
        }

        $list = $this -> m_mac_detail($b_id, $mac);

        Response::assign('b_info', $b_info);
        // Response::assign('date', $date);
        Response::assign('mac', $mac);
        Response::assign('list', $list);

        Response::display('admin/probe/mac_detail.html');
    }

    /**
     * 小时列表
     *
     * @return  String
     */
    public function hour_list()
    {
        // 参数
        $date = Request::Get('date', '');
        $type = Request::Get('type', 0);

        if ( !$this -> member_info ) {
            return '请先登录';
        }

        if ( !$date ) {
            return '请选择时间';
        }

        $time = strtotime($date);

        if ( !is_numeric($date) ) {
            $date = (int)date('Ymd', $time);
        }

        $res_name = $this -> member_info['res_name'];
        $res_id   = $this -> member_info['res_id'];
        $weeks    = array(
            '周一', '周二', '周三',
            '周四', '周五', '周六', '周日'
        );
        $hours    = array(
            '00', '01', '02', '03', '04', '05',
            '06', '07', '08', '09', '10', '11',
            '12', '13', '14', '15', '16', '17',
            '17', '19', '20', '21', '22', '23'
        );
        $data     = array();

        // 周查看
        if ( $type == 5 ) {
            // 本周时间
            $now_week  = probe_helper::get_week_days($time);
            // 上周时间
            $prev_week = probe_helper::get_week_days($time - 7 * 86400);

            // 本周数据
            $now_week_data['indoor']  = $this -> m_week_for_hour($res_name, $res_id, $now_week);

            // 上周数据
            $prev_week_data['indoor'] = $this -> m_week_for_hour($res_name, $res_id, $prev_week);

            Response::assign('now_week_data', $now_week_data);
            Response::assign('prev_week_data', $prev_week_data);
            Response::assign('weeks', $weeks);
        } else {
            foreach ($hours as $k => $v) {
                $data[$v] = 0;
            }

            if ( $res_name == 'group' ) {
                $filter = array();
            } else if ( $res_name == 'province' ) {
                $filter['province_id'] = $res_id;
            } else if ( $res_name == 'city' ) {
                $filter['city_id'] = $res_id;
            } else if ( $res_name == 'area' ) {
                $filter['area_id'] = $res_id;
            } else {
                $filter['business_id'] = $res_id;
            }

            $filter['date_for_day'] = $date;

            $stat_list  = _model('probe_stat_hour')->getList($filter, ' ORDER BY `id` ASC ');

            foreach ($stat_list as $k => $v) {
                $key = $v['date_for_hour'];

                if ( $type == 1 ) {
                    $data[$key] += $v['outdoor'];
                } else if ( $type == 2 ) {
                    $data[$key] += $v['indoor'];
                } else if ( $type == 3 ) {
                    $data[$key] += $v['new_num'];
                } else if ( $type == 4 ) {
                    $data[$key] += $v['old_num'];
                }
            }

            Response::assign('data', $data);
        }

        Response::assign('date', $date);
        Response::assign('type', $type);

        if ( $type == 5 ) {
            Response::display('admin/probe/list_week.html');
        } else {
            Response::display('admin/probe/list_hour.html');
        }
    }
}
?>
