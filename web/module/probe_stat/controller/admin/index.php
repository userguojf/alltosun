<?php

/**
 * 探针统计
 *
 * @author  wangl
 */

class Action
{
    /**
     * 当前管理员信息
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
        // load func.php
        probe_helper::load('func');

        $this -> member_info = member_helper::get_member_info();

        Response::assign('member_info', $this -> member_info);
    }

    /**
     * 统计入口
     *
     * @author  wangl
     */
    public function index()
    {
        if ( !$this->member_info ) {
            return '请先登录';
        }

        // 地区
        $region    = Request::Get('region', 'all');
        // 地区id
        $region_id = Request::Get('region_id', 0);
        // 搜索营业厅
        $business  = Request::Get('business', '');

        // 按时间搜索
        $date_type = Request::Get('date_type', 'day');
        // 开始时间
        $start_time= Request::Get('start_time', '');
        // 结束时间
        $end_time  = Request::Get('end_time', '');

        $res_name  = $this->member_info['res_name'];
        $res_id    = $this->member_info['res_id'];

        // 点击各省统计时，验证权限
        if ( $region == 'province' && $region_id ) {
            $res_name = 'province';
            $res_id   = $region_id;

            if ( !visit_auth($this->member_info, $res_name, $res_id) ) {
                return '您没权限访问';
            }
        }

        // 按营业厅搜索时，验证权限
        if ( $business ) {
            $b_info = business_hall_helper::get_business_hall_info(array('title'=>$business));

            if ( !$b_info ) {
                return '营业厅不存在';
            }

            $res_name = 'business_hall';
            $res_id   = $b_info['id'];

            if ( !visit_auth($this->member_info, $res_name, $res_id) ) {
                return '权限不足，无法查看';
            }

            // 分省查看时，判断搜索营业厅是否在选择的省内
            if ( $region == 'province' && $region_id ) {
                if ( $b_info['province_id'] != $region_id ) {
                    return $business.'不在所选择的省内';
                }
            }
        }

        // 没搜索开始时间，默认为今天
        if ( !$start_time ) {
            $start_time = date('Y-m-d',time() - 3600*24*7);
        }

        // 没搜索结束时间，默认为今天
        if ( !$end_time ) {
            $end_time = date('Y-m-d');
        }

        // 开始时间戳
        $star = strtotime($start_time);
        // 结束时间戳
        $end  = strtotime($end_time);
        // 日期
        $dates= array();

        do {
            if ( $date_type == 'week' ) {
                $dates[] = probe_helper::revise_week($star);
            } else if ( $date_type == 'month' ) {
                $dates[] = date('Ym', $star);
            } else {
                $dates[] = date('Ymd', $star);
            }
            // 每次加1天
            $star += 86400;
        } while ($star < $end);

        // 去重
        $dates = array_unique($dates);

        $filter = array();
        $data   = array(
        	'dates'    =>  array(),
            'outdoor'  =>  array(),
            'indoor'   =>  array(),
        );
        foreach ($dates as $k => $v) {
            $data['dates'][]     = $v;
            $data['outdoor'][$v] = 0;
            $data['indoor'][$v]  = 0;
        }

        if ( $res_name == 'province' ) {
            $filter['province_id'] = $res_id;
        } else if ( $res_name == 'city' ) {
            $filter['city_id'] = $res_id;
        } else if ( $res_name == 'area' ) {
            $filter['area_id'] = $res_id;
        } else if ( $res_name == 'business_hall' ) {
            $filter['business_id'] = $res_id;
        } else if ( $res_name == 'group' ) {

        } else {
            return '地区错误';
        }

        if ( $date_type == 'month' ) {
            $key = 'date_for_month';
        } else if ( $date_type == 'week' ) {
            $key = 'date_for_week';
        } else if ( $date_type == 'day' ) {
            $key = 'date_for_day';
        } else {
            return '时间类型错误';
        }

        $filter[$key] = $dates;
// an_dump($filter);
        $list = _model('probe_stat_day')->getList($filter);

        foreach ($list as $k => $v) {
            $index = $v[$key];
            $data['outdoor'][$index] += $v['outdoor'];
            $data['indoor'][$index]  += $v['indoor'];
        }

        Response::assign('region', $region);
        Response::assign('region_id', $region_id);
        Response::assign('business', $business);
        Response::assign('date_type', $date_type);
        Response::assign('start_time', $start_time);
        Response::assign('end_time', $end_time);
        Response::assign('data', $data);
        Response::assign('res_name', $res_name);
        Response::assign('res_id', $res_id);

        Response::display('admin/index.html');
    }
}
