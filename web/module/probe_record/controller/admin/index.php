<?php

/**
 * 探针统计(记录表统计)
 *
 * @author  wangl
 */

// load trait stat
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
     * 每页显示多少条
     *
     * @var Int
     */
    private $per_page    = 20;

    /**
     * 构造函数
     *
     * @return  Action
     */
    public function __construct()
    {
        // 获取用户信息
        $this->member_info = member_helper::get_member_info();

        Response::assign('member_info', $this -> member_info);
    }

    /**
     * 按天统计
     *
     * @author  wangl
     */
    public function day()
    {
        // b_id指定哪个营业厅
        $b_id = Request::Get('b_id', 0);
        // date指定查看哪天数据
        $date = Request::Get('date', '');

        // 登录验证
        if ( !$this->member_info ) {
            return '请先登录';
        }

        // 没有指定时间则默认为今天
        if ( !$date ) {
            $date = date('Y-m-d');
        }

        // 营业厅验证
        if (! $b_id ) {
            // 注意：记录统计以厅为单位，当没有指定营业厅时，如果当前管理员是厅管理员时，取自身所在的厅，否则无法查看
            if ( $this->member_info['res_name'] != 'business_hall' ) {
                return '请选择营业厅';
            }

            $b_id = $this->member_info['res_id'];
        }

        // 拿营业厅信息
        $b_info = business_hall_helper::get_business_hall_info($b_id);

        if ( !$b_info ) {
            return '营业厅不存在';
        }

        // 权限验证
        if ( !visit_auth($this -> member_info, 'business_hall', $b_id) ) {
            return '您无权查看';
        }

        // 获取营业厅下的探针设备
        $devs = probe_dev_helper::get_devs($b_id);

        if ( !$devs ) {
            return '营业厅下没有探针设备';
        }

        $time  = strtotime($date);
        $dates = [];

        // 生成3天的时间
        for ( $i = 0; $i < 3; $i ++ ) {
            $dates[] = date('Ymd', strtotime("-{$i} day", $time));
        }

        $data  = array();

        $indoor = 0;
        $oudoor = 0;

        foreach ( $dates as $k => $v ) {
            // 统计的具体逻辑在probe/core/trait/stat.php
            $data[$v] = $this->day_stat($b_id, $v);

            $indoor += $data[$v]['indoor'];
            $oudoor += $data[$v]['oudoor'];
        }

        Response::assign('data', $data);
        Response::assign('date', $date);
        Response::assign('devs', $devs);
        Response::assign('indoor', $indoor);
        Response::assign('oudoor', $oudoor);
        Response::assign('b_info', $b_info);
        Response::assign('date_type', 'day');

        Response::display('admin/day.html');
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
        $data   = $this->hour_stat($b_id, $date);
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
     * 按周统计
     *
     * @return String
     */
    public function week()
    {
        // 参数
        $date = Request::Get('date', '');
        $b_id = Request::Get('b_id', 0);

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

        // 访问权限验证
        if ( !visit_auth($this -> member_info, 'business_hall', $b_id) ) {
            return '您没权限访问';
        }

        // 获取营业厅下设备
        $devs = probe_dev_helper::get_devs($b_id);

        if ( !$devs ) {
            return '营业厅下没有设备';
        }

        // 时间
        if ( $date ) {
            // 判断date格式，如果是6位则表示从全国那边点过来
            if ( strlen($date) == 6 ) {

            } else {
                $date = probe_helper::revise_week(strtotime($date));
            }
        } else {
            $date = probe_helper::revise_week(time());
        }

        // 周日期
        $week  = array('周一', '周二', '周三', '周四', '周五', '周六', '周日');
        // 获取一周每天的时间
        $dates = probe_helper::get_week_days($date);
        //
        $data  = array();

        $indoor = 0;
        $oudoor = 0;

        foreach ( $dates as $k => $v ) {
            $data[$v] = $this -> day_stat($b_id, $v);

            $indoor += $data[$v]['indoor'];
            $oudoor += $data[$v]['oudoor'];
        }

        Response::assign('week', $week);
        Response::assign('devs', $devs);
        Response::assign('date', $date);
        Response::assign('data', $data);
        Response::assign('b_info', $b_info);
        Response::assign('indoor', $indoor);
        Response::assign('oudoor', $oudoor);
        Response::assign('date_type', 'week');

        Response::display('admin/week.html');
    }

    /**
     * 详细列表
     *
     * @return  String
     */
    public function detail()
    {
        // 参数
        $date        = Request::Get('date', '');
        $hour        = Request::Get('hour', '');
        $dev         = Request::Get('dev', '');
        $type        = Request::Get('type', '');
        $b_id        = Request::Get('b_id', 0);
        $mac         = Request::Get('mac', '');
        $page        = Request::Get('page_no', 1);
        $is_export   = Request::Get('is_export', 0);
        $remain_time = Request::Get('remain_time', 0);

        if ( !$this -> member_info ) {
            return '请先登录';
        }

        if ( !$b_id ) {
            return '请选择营业厅';
        }

        // 拿营业厅信息
        $b_info = business_hall_helper::get_business_hall_info($b_id);

        if ( !$b_info ) {
            return '营业厅不存在';
        }

        if ( !$date ) {
            return '请选择时间';
        }

        // 拿营业厅下的设备
        $devs = probe_dev_helper::get_devs($b_id);

        if ( !$devs ) {
            return '营业厅下没有设备';
        }

        // 权限验证
        if ( !visit_auth($this -> member_info, 'business_hall', $b_id) ) {
            return '你无权限访问';
        }

        $remain = 0;

        // 按停留时长搜索
        if ( $remain_time ) {
            // 5分钟以上
            if ( $remain_time == 1 ) {
                $remain = 5 * 60;
                // 10分钟以上
            } else if ( $remain_time == 2 ) {
                $remain = 10 * 60;
                // 20分钟以上
            } else if ( $remain_time == 3 ) {
                $remain = 20 * 60;
                // 30分钟以上
            } else if ( $remain_time == 4 ) {
                $remain = 30 * 60;
                // 1小时以上
            } else if ( $remain_time == 5 ) {
                $remain = 60 * 60;
                // 5小时以上
            } else if ( $remain_time == 6 ) {
                $remain = 5 * 60 * 60;
                /// 8小时以上
            } else if ( $remain_time == 7 ) {
                $remain = 8 * 60 * 60;
            } else if ( $remain_time == 8 ) {
                $remain = 3 * 60;
            } else {
                $remain = 0;
            }
        }

        $param = array();

        if ( $mac ) {
            $param['mac'] = probe_helper::mac_decode($mac);
        }

        if ( $dev ) {
            $param['dev'] = $dev;
        }

        if ( $remain ) {
            $param['remain'] = $remain;
        }

        if ( $type == 'in' ) {
            $param['is_indoor'] = 1;
        } else {
            $param['is_indoor'] = 0;
        }

        if ( $is_export ) {
            $param['is_export'] = 1;
        }

        // 按小时搜索
        if ( $hour !== '' ) {
            if ( !is_numeric($hour) || $hour > 23 ) {
                return '小时不正确';
            }
            $param['hour'] = $hour;
        }

        $ary = $this -> record_detail_list($b_id, str_replace('-', '', $date), $param);

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

        // 导出
        if ( $is_export ) {
            if ( !$list ) {
                return '没有要导出的内容';
            }
            $this->export($list);
            exit(0);
        }

        if ( $count ) {
            $pager = new Pager($this->per_page);

            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }
        }

        Response::assign('stat_type', $hour == '' ? 'day' : 'hour');
        Response::assign('b_info', $b_info);
        Response::assign('date', $date);
        Response::assign('hour', $hour);
        Response::assign('dev', $dev);
        Response::assign('type', $type);
        Response::assign('count', $count);
        Response::assign('list', $list);
        Response::assign('remain_time', $remain_time);
        Response::assign('mac', $mac);

        Response::display('admin/detail.html');
    }

    /**
     * 获取某个mac详细的轨迹
     *
     * @author  wangl
     */
    public function mac_detail()
    {
        // 参数
        $b_id  = Request::Get('b_id', 0);
        $date  = Request::Get('date', '');
        $start = Request::Get('start', 0);
        $end   = Request::Get('end', 0);
        $mac   = Request::Get('mac', '');
        $dev   = Request::Get('dev', '');

        if ( !$this -> member_info ) {
            return '请先登录';
        }

        if ( !$b_id ) {
            return '请选择营业厅';
        }

        // 获取营业厅信息
        $b_info = business_hall_helper::get_business_hall_info($b_id);

        if ( !$b_info ) {
            return '营业厅不存在';
        }

        // 权限验证
        if ( !visit_auth($this -> member_info, 'business_hall', $b_id) ) {
            return '您没权限访问';
        }

        // 获取营业厅下的设备
        $devs = probe_dev_helper::get_devs($b_id);

        if ( !$devs ) {
            return '营业厅下没有设备';
        }

        if ( !$date ) {
            return '请选择时间';
        }

        if ( !is_numeric($date) ) {
            $date = str_replace('-', '', $date);
        }

        if ( !$mac ) {
            return '没有mac地址';
        }

        if ( !is_numeric($mac) ) {
            $mac = probe_helper::mac_decode($mac);
        }

        $list = $this -> record_mac_timeline($b_id, $date, $mac, $dev);

        $time_line = '';
        foreach ($list as $k => $v) {
            $time_line .= ','.$v['time_line'];
        }
        $time_line = trim($time_line, ',');

        $ary = explode(',', $time_line);
        $ary = array_reverse($ary);
        $time_line = implode(',', $ary);
        // an_dump($time_line);
        // $time_line = strrev($time_line);

        if ( $end ) {
            $start = $end - 60;
        }

        Response::assign('b_info', $b_info);
        Response::assign('date', $date);
        Response::assign('start', $start);
        Response::assign('end', $end);
        Response::assign('mac', $mac);
        Response::assign('time_line', $time_line);

        Response::display('admin/mac_detail.html');
    }

    /**
     * 导出
     *
     * @param   Array   列表
     */
    private function export( $list )
    {
        $params = array();
        $head   = array('设备编号', '设备所属营业厅', 'mac地址', '首次探测时间', '最后探测时间', '停留时长');
        $data   = array();

        foreach ($list as $k => $v) {
            $data[$k] = array(
                'dev'           =>  $v['dev'],
                'business'      =>  get_resource_info('business_hall', $v['b_id'], 'title'),
                'mac'           =>  probe_helper::mac_encode($v['mac'])
            );

            $info = probe_helper::get_mac_remain($v['mac'], $v['b_id'], $v['date'], $v['dev']);

            if ( !$info ) {
                continue;
            }

            $data[$k]['frist_time'] = date('Y-m-d H:i:s', $info['frist_time']);
            $data[$k]['up_time']    = date('Y-m-d H:i:s', $info['up_time']);
            $data[$k]['remain_time']= $info['remain'];

            $ary = probe_helper::get_remain($info['remain']);

            if ( $ary['hour'] ) {
                $data[$k]['remain_time'] = $ary['hour'].'小时'.$ary['min'].'分'.$ary['sec'].'秒';
            } else if ( $ary['min'] ) {
                $data[$k]['remain_time'] = $ary['min'].'分'.$ary['sec'].'秒';
            } else {
                $data[$k]['remain_time'] = $ary['sec'].'秒';
            }
        }

        $params['data'] = $data;
        $params['head'] = $head;
        Csv::getCvsObj($params)->export();
    }
}