<?php 

/**
 * 探针统计列表
 *
 * @author  wangl
 */

probe_helper::load('func');

/**
 * Action
 *
 * @author  wangl
 */
class Action
{
    /**
     * 每页显示多少条
     * @var int
     */
    private $per_page = 20;

    /**
     * 当前登录用户信息
     * @var array
     */
    private $member_info = array();

    /**
     * 构造函数
     *
     * @return  Action
     */
    public function __construct()
    {
        $this->member_info = member_helper::get_member_info();

        Response::assign('member_info', $this->member_info);
    }

    /**
     * list首页
     *
     * @author  wangl
     */
    public function index()
    {
        // 时间类型
        $date_type  = Request::Get('date_type', '');
        // 时间
        $date       = Request::Get('date', 0);
        // 资源名
        $res_name   = Request::Get('res_name', 'all');
        // 资源ID
        $res_id     = Request::Get('res_id', 0);

        if ( !$this -> member_info ) {
            return '请先登录';
        }

        if ( !$date ) {
            return '请选择时间';
        }

        if ( $res_name == 'all' ) {
            $res_name = $this -> member_info['res_name'];
            $res_id   = $this -> member_info['res_id'];
        }

        if ( !visit_auth($this -> member_info, $res_name, $res_id) ) {
            return '您无权限访问';
        }

        $data = $this->get_data($date_type, $date, $res_name, $res_id);

        Response::assign('date_type', $date_type);
        Response::assign('date', $date);
        Response::assign('data', $data);
        Response::assign('res_name', $res_name);
        Response::assign('res_id', $res_id);
        Response::display('admin/list.html');
    }

    /**
     * 获取数据封装
     *
     * @author  wangl
     */
    private function get_data( $date_type, $date, $res_name, $res_id)
    {
        if ( !$date_type || !$date ) {
            return array();
        }

        $data = array(
            'name'        => array(),
            'outdoor'     => array(),
            'indoor'      => array(),
            'subordinate' => ''
        );

        // 查询时的date字段
        $date_field  = 'date_for_'.$date_type;
        // 当前地区的下级地区
        $subordinate = '';
        // filter查询时的字段名
        $field_name  = '';
        // filter查询时的字段值
        $field_val   = '';
/*
        if ( $date_type == 'month' ) {
            $date_field = 'date_for_month';
        } else if ( $date_type == 'week' ) {
            $date_field = 'date_for_week';
        } else if ( $date_type == 'day' ) {
            $date_field = 'date_for_day';
        }
*/
        // 全国管理员
        if ( $res_name == 'group' ) {
            $subordinate = 'province';

        // 省级管理员
        } else if ( $res_name == 'province' ) {
            $subordinate = 'city';
            $field_name  = 'province_id';
            $field_val   = $res_id;

        // 市级管理员
        } else if ( $res_name == 'city' ) {
            $subordinate = 'area';
            $field_name  = 'city_id';
            $field_val   = $res_id;

        // 区级管理员
        } else if ( $res_name == 'area' ) {
            $subordinate = 'business';
            $field_name  = 'area_id';
            $field_val   = $res_id;

        // 营业厅管理员
        } else {
            $subordinate = 'business';
            $field_name  = 'business_id';
            $field_val   = $res_id;
        }

        $filter = array(
            $date_field =>  $date
        );

        if ( $field_name ) {
            $filter[$field_name] = $field_val;
        }

        $list = _model('probe_stat_day')->getList($filter);
        foreach ($list as $k => $v) {
            // 查询下级ID，例如，当前身份是省，则查询city_id
            $key = $v[$subordinate.'_id'];

            if ( empty($data['name'][$key]) ) {
                // 下级是营业厅
                if ( $subordinate == 'business' ) {
                    $name = _uri('business_hall', $key, 'title');
                } else {
                    $name = _uri($subordinate, $key, 'name');
                }

                $data['name'][$key]    = $name;
                $data['outdoor'][$key] = $v['outdoor'];
                $data['indoor'][$key]  = $v['indoor'];
            } else {
                $data['outdoor'][$key] += $v['outdoor'];
                $data['indoor'][$key]  += $v['indoor'];
            }
        }

        $data['subordinate'] = $subordinate;

        return $data;
    }
}
?>
