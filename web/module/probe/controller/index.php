<?php 
/**
 * alltosun.com 探针 index.php
 * ============================================================================
 * 版权所有 (C) 2009-2015 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明: 这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王磊 (wangl@alltosun.com) $
 * $Date: 2016-11-9 上午10:21:31 $
 */

// load func.php
probe_helper::load('func');

class Action
{
    /**
     * 沃联设备数据提交地址
     *
     * @return  String
     */
    public function index()
    {
        // 存储
        device('wolian')->storage();

        // 注：如果是在线下的话，则需要往线上转发一份
        if ( ONDEV ) {
            device('wolian')->transmit();
        }

        echo 'ok';
    }

    /**
     * 分时数据
     *
     * @return  String
     */
    public function get_num()
    {
        $dev  = Request::getParam('dev', '');
        $t    = Request::getParam('t', 1);

        if ( !$dev ) {
            return '请选择设备';
        }

        $devs  = explode(',', $dev);

        // 今天时间
        $date  = (int)date('Ymd');

        $time  = time();
        // 开始时间
        $start = $time - ($t * 60);
        // 结束时间
        $end   = $time;

        $table = '<table border="1" cellpadding="0" cellspacing="0" style="text-align: center; width: 600px;"><tbody><tr><th>设备</th><th>室内</th><th>室外</th><th>总计</th></tr>';

        echo '开始时间：'.date('Y-m-d H:i:s', $start);
        echo '<br />';
        echo '结束时间：'.date('Y-m-d H:i:s', $end);
        echo '<br />';

        foreach ($devs as $k => $dev) {
            $dev_info = probe_dev_helper::get_info($dev);

            if ( !$dev_info ) {
                continue;
            }

            $b_id = $dev_info['business_id'];

            $db   = get_db($b_id, 'hour');

            // 查询一分钟内，这个设备探测的记录
            $sql  = "SELECT `mac`, `is_indoor` FROM `{$db -> table}` WHERE `date` = {$date} AND `dev` = '{$dev}' AND `up_time` >= {$start} AND `up_time` <= {$end}";

            $list = $db -> getAll($sql);

            $indoor  = array();
            $outdoor = array();

            foreach ($list as $k => $v) {
                $mac = $v['mac'];

                if ( $v['is_indoor'] ) {
                    if ( isset($outdoor[$mac]) ) {
                        unset($outdoor[$mac]);
                    }
                    $indoor[$mac] = 0;
                } else {
                    if ( !isset($indoor[$mac]) ) {
                        $outdoor[$mac] = 0;
                    }
                }
            }

            $indoor  = count($indoor);
            $outdoor = count($outdoor);

            $table .= '<tr><td>'.$dev.'</td><td>'.$indoor.'</td><td>'.$outdoor.'</td><td>'.($indoor + $outdoor).'</td></tr>';
        }
        $table .= '</tbody></table>';

        echo $table;
    }

    /**
     * 按天对比
     *
     * @return  String
     */
    public function day_contrast()
    {
        $dev  = Request::getParam('dev', '');
        $date = Request::getParam('date', '');

        if ( !$dev ) {
            return '请选择设备';
        }

        $devs  = explode(',', $dev);

        if ( $date ) {
            $int_date   = (int)str_replace('-', '', $date);
        } else {
            $date       = date('Y-m-d');
            $int_date   = (int)date('Ymd');
        }

        $table = '<table border="1" cellpadding="0" cellspacing="0" style="text-align: center; width: 600px;"><tbody><tr><th>设备</th><th>室内</th><th>室外</th><th>总计</th></tr>';

        echo '时间：'.$date;
        echo '<br />';

        foreach ($devs as $k => $dev) {
            $dev_info = probe_dev_helper::get_info($dev);

            if ( !$dev_info ) {
                continue;
            }

            $b_id = $dev_info['business_id'];

            $db   = get_db($b_id, 'day');

            // 查询一分钟内，这个设备探测的记录
            $sql  = "SELECT `mac`, `is_indoor` FROM `{$db -> table}` WHERE `date` = {$int_date} AND `dev` = '{$dev}'";

            $list = $db -> getAll($sql);

            $indoor  = array();
            $outdoor = array();

            foreach ($list as $k => $v) {
                $mac = $v['mac'];

                if ( $v['is_indoor'] ) {
                    if ( isset($outdoor[$mac]) ) {
                        unset($outdoor[$mac]);
                    }
                    $indoor[$mac] = 0;
                } else {
                    if ( !isset($indoor[$mac]) ) {
                        $outdoor[$mac] = 0;
                    }
                }
            }

            $indoor  = count($indoor);
            $outdoor = count($outdoor);

            $table .= '<tr><td>'.$dev.'</td><td>'.$indoor.'</td><td>'.$outdoor.'</td><td>'.($indoor + $outdoor).'</td></tr>';
        }
        $table .= '</tbody></table>';

        echo $table;
    }

    /**
     * 信号对比
     *
     * @return  String
     */
    public function rssi_contrast()
    {
        $mac  = Request::Get('mac', '');
        $dev  = Request::getParam('dev', '');
        $date = Request::getParam('date', '');

        if ( !$dev ) {
            return '请选择设备';
        }

        $devs  = explode(',', $dev);

        if ( $date ) {
            $int_date   = (int)str_replace('-', '', $date);
        } else {
            $date       = date('Y-m-d');
            $int_date   = (int)date('Ymd');
        }

        if ( !$mac ) {
            echo '没有mac地址';
            exit(-1);
        }        

        if ( !is_numeric($mac) ) {
            echo 'mac：'.$mac.'<br />';

            $mac = probe_helper::mac_decode($mac);
        } else {
            echo 'mac：'.probe_helper::mac_decode($mac).'<br />';
        }

        $lines = array();

        echo '时间：'.$date;
        echo '<br />';

        $table = '<table border="1" cellpadding="0" cellspacing="0" style="text-align: center; width: 600px;"><tbody><tr>';

        foreach ($devs as $k => $dev) {
            $dev_info = probe_dev_helper::get_info($dev);

            if ( !$dev_info ) {
                unset($devs[$k]);
                continue;
            }

            $table .= '<th>设备：'.$dev.'</th>';

            $b_id = $dev_info['business_id'];

            $db   = get_db($b_id, 'hour');

            // 查询一分钟内，这个设备探测的记录
            $sql  = "SELECT `time_line` FROM `{$db -> table}` WHERE `mac` = '{$mac}' AND `date` = {$int_date} AND `dev` = '{$dev}' ORDER BY `id` ASC ";

            $list = $db -> getAll($sql);

            $time_line = '';

            foreach ($list as $k => $v) {
                $time_line .= ','.$v['time_line'];
            }

            $time_line      = trim($time_line, ',');

            $ary            = explode(',', $time_line);

            $lines[$dev]    = array_reverse($ary);
        }

        $table .= '</tr>';

        $while = true;

        $n     = 0;

        while ( $while ) {
            $table .= '<tr>';

            $while  = false;

            foreach ($devs as $k => $dev) {
                $ary = $lines[$dev];

                if ( isset($ary[$n]) ) {
                    $arr    = explode(':', $ary[$n]);

                    if ( !empty($arr[0]) && !empty($arr[1]) ) {
                        $table .= '<td>时间：'.date('Y-m-d H:i:s', $arr[0]).'<br />信号：'.$arr[1].'</td>';
                    } else {
                        $table .= '<td></td>';
                    }

                    $while  = true;
                } else {
                    $table .= '<td></td>';
                }
            }

            $n ++;

            $table .= '</tr>';
        }

        $table .= '</tbody></table>';

        echo $table;
    }

    /**
     * 设备稳定性对比
     *
     * @return  String
     */
    public function stable_contrast()
    {
        $dev  = Request::getParam('dev', '');
        $t    = Request::getParam('t', 0);

        if ( !$dev ) {
            return '请选择设备';
        }

        $devs  = explode(',', $dev);

        $time  = time();
        
        $date  = (int)date('Ymd', $time);

        $start = strtotime(date('Y-m-d H:00:00', $time));

        if ( $t ) {
            $start -= ($t * 60 * 60);
        }

        $end   = strtotime(date('Y-m-d H:59:59', $start));

        $table = '<table border="1" cellpadding="0" cellspacing="0" style="text-align: center; width: 600px;"><tbody><tr><th>时间</th>';

        $data  = array(
            'indoor'    =>  array(),
            'outdoor'   =>  array(),
        );
        foreach ($devs as $k => $dev) {
            $data['indoor'][$dev]  = array();
            $data['outdoor'][$dev] = array();

            for ($i = 0; $i < 60; $i ++) {
                $key = $i < 10 ? '0'.$i : (string)$i;

                $data['indoor'][$dev]["$key"]  = array();
                $data['outdoor'][$dev]["$key"] = array();
            }

            $table .= '<th>设备：'.$dev.'</th>';
        }

        $table .= '</tr>';

        foreach ($devs as $k => $dev) {
            $dev_info = probe_dev_helper::get_info($dev);

            if ( !$dev_info ) {
                continue;
            }

            $b_id = $dev_info['business_id'];

            $db   = get_db($b_id, 'day');

            $sql  = "SELECT `mac`, `is_indoor`, `up_time` FROM `{$db -> table}` WHERE `date` = {$date} AND `dev` = '{$dev}' AND `up_time` >= {$start} AND `up_time` <= '{$end}' ORDER BY `up_time` ASC ";

            $list = $db -> getAll($sql);

            foreach ($list as $k => $v) {
                $mac = $v['mac'];
                $min = date('i', $v['up_time']);

                if ( $v['is_indoor'] ) {
                    if ( isset($data['outdoor'][$dev][$min][$mac]) ) {
                        unset($data['outdoor'][$dev][$min][$mac]);
                    }
                    $data['indoor'][$dev][$min][$mac] = 0;
                } else {
                    if ( !isset($data['indoor'][$dev][$min][$mac]) ) {
                        $data['outdoor'][$dev][$min][$mac] = 0;
                    }
                }
            }
        }

        for ($i = 0; $i < 60; $i ++) {
            $key = $i < 10 ? '0'.$i : (string)$i;

            $table .= '<tr><td>'.date('Y-m-d H:'.$key, $time).'</td>';

            foreach ($devs as $k => $dev) {
                $data['indoor'][$dev]["$key"]  = count($data['indoor'][$dev]["$key"]);
                $data['outdoor'][$dev]["$key"] = count($data['outdoor'][$dev]["$key"]);

                $indoor  = $data['indoor'][$dev]["$key"];
                $outdoor = $data['outdoor'][$dev]["$key"];

                $table .= '<td>室内：'.$indoor.'<br>室外：'.$outdoor.'<br>共：'.($indoor + $outdoor).'</td>';
            }
            $table .= '</tr>';
        }

        $table .= '</tbody></table>';

        echo $table;
    }

    /**
     * 多设备都探测到的mac地址对比
     *
     * @return  String
     */
    public function mac_contrast()
    {
        $dev  = Request::getParam('dev', '');
        $t    = Request::Get('t', 0);

        if ( !$dev ) {
            return '请选择设备';
        }

        if ( !$t ) {
            $t = 10;
        }

        // 设备
        $devs  = explode(',', $dev);

        // 当前时间
        $time  = time();
        // 今天时间
        $date  = (int)date('Ymd', $time);
        // 开始时间
        $start = $time - ($t * 60);
        // 结束时间
        $end   = $time;
        // data
        $data  = array(
        );

        echo '开始：'.date('Y-m-d H:i:s', $start).'<br />';
        echo '结束：'.date('Y-m-d H:i:s', $end).'<br />';

        // 最终表格
        $table = '<table border="1" cellpadding="0" cellspacing="0" style="text-align: center; width: 600px;"><tbody><tr><th>mac</th>';        

        foreach ($devs as $k => $dev) {
            $dev_info = probe_dev_helper::get_info($dev);

            if ( !$dev_info ) {
                continue;
            }

            $table .= '<th>'.$dev.'</th>';

            $data[$dev] = array();

            $b_id = $dev_info['business_id'];

            $db   = get_db($b_id, 'day');

            $sql  = "SELECT `mac`, `is_indoor`, `up_time`, `up_rssi` FROM `{$db -> table}` WHERE `date` = {$date} AND `dev` = '{$dev}' AND `up_time` >= {$start} AND `up_time` <= '{$end}' ORDER BY `up_time` ASC ";

            $list = $db -> getAll($sql);

            foreach ($list as $k => $v) {
                $mac = $v['mac'];

                $data[$dev][$mac] = array(
                    'up_time'   =>  $v['up_time'],
                    'up_rssi'   =>  $v['up_rssi'],
                    'is_indoor' =>  $v['is_indoor']
                );
            }
        }
        $table .= '</tr>';

        $key = $devs[0];
        foreach ($data[$key] as $mac => $v) {
            // 至少两个设备包含某个mac地址才算
            $has = false;

            $str = '<tr><td>'.probe_helper::mac_encode($mac).'</td>';
            $str .= '<td>时间：'.date('Y-m-d H:i:s', $v['up_time']).'<br>信号：'.$v['up_rssi'].'</td>';

            foreach ($devs as $k => $dev) {
                if ( $dev == $key ) {
                    continue;
                }

                if ( isset($data[$dev][$mac]) ) {
                    $has = true;
                    $row = $data[$dev][$mac];

                    $str .= '<td>时间：'.date('Y-m-d H:i:s', $row['up_time']).'<br>信号：'.$row['up_rssi'].'</td>';
                } else {
                    $str .= '<td>时间：<br>信号：</td>';
                }
            }

            if ( $has ) {
                $table .= $str;
            }
        }

        $table .= '</tbody></table>';

        echo $table;
    }

    /**
     * 烽火探测设备和中科对比
     *
     * @return  String
     */
    public function fenghuo_contrast()
    {
        set_time_limit(0);

        $n   = Request::Get('n', 500);
        $dev = Request::Get('dev', '');
        $type= Request::Get('type', '');

        if ( !$dev ) {
            return '没有设备';
        }
        $devs = explode(',', $dev);

        if ( !$type ) {
            return '请选择类型';
        }

        $path = __DIR__.'/'.$type.'.txt';

        if ( !file_exists($path) ) {
            return '文件不存在';
        }

        $str = file_get_contents($path);
        $ary = explode("\n", $str);

        // 最终表格
        $table = '<table border="1" cellpadding="0" cellspacing="0" style="text-align: center; width: 600px;"><tbody>';
        $th    = '<tr><th>序号</th><th>烽火</th>';
        $td    = '';
        $num   = 1;
        $flag  = true;

        foreach ($ary as $k => $v) {
            if ( $n <= 0 ) {
                break;
            }
            $n --;

            preg_match_all('/[\w\*]+/', $v, $ary);

            if ( empty($ary[0]) || empty($ary[0][1]) ) {
                continue;
            }

            $str = $ary[0][1];
            $mac = explode('*', $str);
            $mac = array_filter($mac);
            $mac = implode('%', $mac);

            $row = '<tr><td>'.$num.'</td><td>'.$str.'</td>';
            $has = false;

            foreach ($devs as $k => $dev) {
                $dev_info = probe_dev_helper::get_info($dev);
            
                if ( !$dev_info ) {
                    continue;
                }

                $db = get_db($dev_info['business_id']);

                if ( !$db ) {
                    continue;
                }

                if ( $flag ) {
                    $th .= '<th>'.$dev.'</th>';
                }

                $sql  = " SELECT `mac` FROM {$db -> table} WHERE `date` = 20170615 AND conv(`mac`, 10, 16) LIKE '{$mac}' AND  `dev` = '{$dev_info['device']}' ORDER BY `id` DESC LIMIT 1 ";

                $r = $db -> getAll($sql);

                if ( $r ) {
                    $info = $r[0];
                } else {
                    $info = array();
                }

                if ( $info ) {
                    $has = true;

                    $row .= '<td>mac：'.probe_helper::mac_encode($info['mac']).'</td>';
                } else {
                    $row .= '<td>mac：</td>';
                }
            }
            if ( $flag ) {
                $th .= '</tr>';
                $flag = false;
            }

            if ( $has ) {
                $td .= $row.'</tr>';

                $num ++;
            }            
        }

        $table .= $th;
        $table .= $td;
        $table .= '</tbody></table>';

        echo $table;
    }

    /**
     * 设备品牌对比
     *
     * @return  String
     */
    public function brand_contrast()
    {
        $dev  = Request::Get('dev', '');
        $date = Request::Get('date', '');

        if ( !$dev ) {
            return '没有设备编号';
        }

        // 设备
        $devs = explode(',', $dev);

        // 时间
        if ( $date ) {
            $int_date = str_replace('-', '', $date);
        } else {
            $int_date = (int)date('Ymd');
            $date     = date('Y-m-d');
        }

        echo '时间：'.$date.'<br />';

        $table = '<table border="1" cellpadding="0" cellspacing="0" style="text-align: center; width: 600px;"><tbody><tr><th>设备</th><th>可识别设备</th><th>不可识别设备</th></tr>';

        foreach ($devs as $k => $dev) {
            $dev_info = probe_dev_helper::get_info($dev);

            if ( !$dev_info ) {
                continue;
            }

            $db   = get_db($dev_info['business_id'], 'day');

            $sql  = " SELECT `mac` FROM {$db -> table} WHERE `date` = {$int_date} AND `dev` = '{$dev}' ";

            // 查询某天某设备探测到的数据
            $list = $db -> getAll($sql);

            // 不可识别的数量
            $norecog = 0;
            // 可识别的数量
            $recog   = 0;

            foreach ( $list as $v ) {
                $mac = $v['mac'];

                // 查设备型号
                $name = probe_helper::get_brand($mac);

                if ( $name == '其他' ) {
                    $norecog ++;
                } else {
                    $recog ++;
                }
            }

            $table .= '<tr><td>'.$dev.'</td><td>'.$recog.'</td><td>'.$norecog.'</td></tr>';
        }
        $table .= '</tbody></table>';

        echo $table;
    }

    /**
     * 设备重合率对比
     *
     * @return  String
     */
    public function coincide_contrast()
    {
        set_time_limit(0);

        $dev  = Request::Get('dev', '');
        $type = Request::Get('type', 'in');
        $date = Request::Get('date', '');

        if ( !$dev ) {
            return '没有设备';
        }

        // 设备
        $devs = explode(',', $dev);

        // 时间
        if ( $date ) {
            $int_date = str_replace('-', '', $date);
        } else {
            $int_date = (int)date('Ymd');
            $date     = date('Y-m-d');
        }

        // 类型
        if ( !in_array($type, array('in', 'out')) ) {
            return '类型不正确';
        }

        $data  = array();

        $table = '<table border="1" cellpadding="0" cellspacing="0" style="text-align: center; width: 600px;">';
        $th    = '<tr><th>序号</th>';

        $td    = '';

        $num   = 1;

        foreach ($devs as $k => $dev) {
            $dev_info = probe_dev_helper::get_info($dev);

            if ( !$dev_info ) {
                unset($devs[$k]);
                continue;
            }

            $th .= '<th>'.$dev.'</th>';

            $db  = get_db($dev_info['business_id']);

            $sql = " SELECT `mac` FROM `{$db -> table}` WHERE `date` = {$int_date} AND `dev` = '{$dev}' ";

            if ( $type == 'in' ) {
                $sql .= " AND `is_indoor` = 1 ";
            } else if ( $type == 'out' ) {
                $sql .= " AND `is_indoor` = 0 ";
            }

            // 查询探测到的mac地址
            $list = $db -> getAll($sql);

            $data[$dev] = array();

            foreach ( $list as $probe ) {
                $mac = $probe['mac'];

                $data[$dev][$mac] = 0;
            }
        }
        $th .= '</tr>';

        $key = $devs[0];

        foreach ( $data[$key] as $mac => $info ) {
            $td .= '<tr><td>'.$num.'</td><td>'.probe_helper::mac_encode($mac).'</td>';

            $num ++;

            foreach ( $devs as $k => $dev ) {
                if ( $dev == $key ) {
                    continue;
                }

                if ( isset($data[$dev][$mac]) ) {
                    $td .= '<td>'.probe_helper::mac_encode($mac).'</td>';
                } else {
                    $td .= '<td>--</td>';
                }
            }

            $td .= '</tr>';
        }

        echo '时间：'.$date.'<br />';
        if ( $type == 'in' ) {
            echo '类型：室内对比';
        } else if ( $type == 'out' ) {
            echo '类型：室外对比';
        }
        echo $table.$th.$td.'</table>';
    }

    /**
     * 实时数据
     *
     * @return  String
     */
    public function min()
    {
        $dev = Request::getParam('dev', '');

        if ( !$dev ) {
            return '请选择设备';
        }

        $dev_info = probe_dev_helper::get_info($dev);

        if ( !$dev_info ) {
            return '设备不存在';
        }

        $b_id  = $dev_info['business_id'];
        // 今天时间
        $date  = (int)date('Ymd');
        // 开始时间
        $start = strtotime(date('Y-m-d H:i:00'));
        // 结束时间
        $end   = strtotime(date('Y-m-d H:i:59'));

        $db   = get_db($b_id, 'hour');

        $sql  = "SELECT `mac`, `is_indoor`, `up_time` FROM `{$db -> table}` WHERE `date` = {$date} AND `up_time` >= {$start} AND `up_time` <= {$end}";

        $list = $db -> getAll($sql);

        $data = array(
            'indoor'    =>  array(),
            'outdoor'   =>  array()
        );

        for ( $i = 0; $i < 60; $i ++ ) {
            $k = $i < 10 ? '0'.$i : $i;

            $data['indoor'][$k]  = array();
            $data['outdoor'][$k] = array();
        }

        foreach ($list as $k => $v) {
            $mac = $v['mac'];
            $sec = date('s', $v['up_time']);

            if ( $v['is_indoor'] ) {
                if ( isset($data['outdoor'][$sec][$mac]) ) {
                    unset($data['outdoor'][$sec][$mac]);
                }
                $data['indoor'][$sec][$mac] = 0;
            } else {
                if ( !isset($data['indoor'][$sec][$mac]) ) {
                    $data['indoor'][$sec][$mac] = 0;
                }
            }
        }

        for ( $i = 0; $i < 60; $i ++ ) {
            $k = $i < 10 ? '0'.$i : $i;

            $data['indoor'][$k]  = count($data['indoor'][$k]);
            $data['outdoor'][$k] = count($data['outdoor'][$k]);
        }

        if ( Request::isAjax() ) {
            return array('info' => 'ok', 'data' => $data);
        }

        Response::assign('dev', $dev);
        Response::assign('data', $data);
        Response::display('min.html');
    }

    /**
     * 手动跑计划任务
     *
     * @return  String
     */
    public function corn()
    {
        $type = Request::Get('type', 'day');
        $date = Request::Get('date', '');

        if ( !$date ) {
            return '请选择日期';
        }

        if ( $type == 'day' ) {
            _widget('probe')->corn($date);
        } else {
            $time = strtotime($date);

            _widget('probe')->hour_corn($time);
        }
    }

    /**
     * 转换mac地址
     *
     * @return  String
     */
    public function get_mac()
    {
        $mac  = Request::Get('mac', '');

        if ( !$mac ) {
            echo 'mac地址不能为空';
            exit(-1);
        }

        if ( is_numeric($mac) ) {
            an_dump(probe_helper::mac_encode($mac));
        } else {
            an_dump(probe_helper::mac_decode($mac));
        }
    }

    /**
     * 转换时间格式
     *
     * @return  String
     */
    public function get_time()
    {
        $time = Request::Get('time', '');

        if ( !$time ) {
            exit(-1);
        }

        if ( is_numeric($time) ) {
            an_dump(date('Y-m-d H:i:s', $time));
        } else {
            an_dump(strtotime($time));
        }
    }

    /**
     * 通过mac地址查型号
     *
     * @return  String
     */
    public function mac_for_model()
    {
        $mac = probe_helper::mac_encode('194006882249392', '-');
        $url = 'http://mac.51240.com/'.$mac.'__mac/';

        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);

        curl_close($ch);

        echo $result;
    }

    /**
     * 测试提供给rfid的接口
     *
     * @return  String
     */
    public function rfid_api()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://201512awifi.alltosun.net/probe/api/rfid');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('start_time' => time(), 'end_time' => time(), 'b_id' => 23));

        $res = curl_exec($ch);

        an_dump($res);

        curl_close($ch);
    }

    /**
     * 修复或查看一个营业厅内，其中一个设备算某个mac地址为室内
     * 另一个设备不算室内的数据
     *
     * @return  String
     */
    public function storage_problem()
    {
        $repair= Request::Get('repair', 0);
        $list  = probe_dev_helper::get_list(array('status' => 1), ' GROUP BY `business_id` ');

        foreach ($list as $k => $v) {
            $db  = get_db($v['business_id']);

            $sql = "SELECT COUNT(*) as `num` FROM `{$db -> table}` as t1, `{$db -> table}` as t2 WHERE t1.date = t2.date AND t1.mac = t2.mac AND t1.is_indoor != t2.is_indoor order by t1.mac";

            $r   = $db -> getAll($sql);

            an_dump('营业厅：'.$v['business_id'], $r);

            if ( $r ) {
                $num = $r[0]['num'];
            } else {
                $num = 0;
            }

            if ( !$num ) {
                continue;
            }

            if ( $repair ) {
                $sql = "UPDATE `{$db -> table}` as t1, `{$db -> table}` as t2 SET t1.is_indoor = 1, t2.is_indoor = 1 WHERE t1.date = t2.date AND t1.mac = t2.mac AND t1.is_indoor != t2.is_indoor";
                $db -> getAll($sql);
            }
        }

        echo 'ok';
    }

    /**
     * 接口上报监控
     *
     * @return  String
     */
    public function report()
    {
        

        $date = Request::Get('date', '');
        $devs = Request::Get('devs', '');

        // 时间
        if ( $date ) {
            $int_date = str_replace('-', '', $date);
        } else {
            $int_date = (int)date('Ymd');
            $date     = date('Y-m-d');
        }

        // 查询所有设备
        $dev_list = probe_dev_helper::get_list(array('status' => 1));

        if ( !$dev_list ) {
            trigger_error('devs is empty.', E_USER_ERROR);
        }

        foreach ($dev_list as $k => $v) {
            $dev  = $v['device'];
            $rssi = abs($v['rssi']);
            $b_id = $v['business_id'];
            $db   = get_db($b_id);

            $table = '<table border="1" cellpadding="0" cellspacing="0" style="text-align: center; width: 600px;">';
            $th    = '<tr><th>时间</th><th>数据量</th></tr>';
            $td    = '';

            $sql = " SELECT COUNT(*) as `num`, FROM_UNIXTIME(frist_time,'%Y-%m-%d %H:%i') AS times FROM `{$db -> table}` WHERE `date` = {$int_date} AND `dev` = '{$dev}' GROUP BY times ORDER BY `times` DESC ";

            $list = $db -> getAll($sql);
            $last = 0;

            foreach ( $list as $info ) {
                $red = 0;

                if ( $last == 0 ) {
                    $last = $info['times'];
                } else {
                    $time1 = strtotime($info['times']);
                    $time2 = strtotime($last);

                    if ( $time2 - $time1 >= (5 * 60) ) {
                        $red = 1;
                    }
                    $last = $info['times'];
                }

                if ( $red ) {
                    $style = 'style="background-color: red;"';
                } else {
                    $style = 'style=""';
                }
                $td .= '<tr '.$style.'><td>'.$info['times'].'</td><td>'.$info['num'].'</td></tr>';
            }

            $table .= $th.$td.'</table>';
            echo '设备：'.$dev.'<br />';
            echo $table;
            echo '<br /><br />';
        }
    }

    /**
     * 停留时长
     *
     * @author  wangl   
     */
    public function remain_time()
    {
        $b_id = Request::Get('b_id', 0);

        $b_id or die('请选择营业厅');

        $b_info = business_hall_helper::get_business_hall_info($b_id);

        $b_info or die('营业厅不存在');

        $par = array(
            'is_export' => 1,
            'is_indoor' =>  1
        );
        $res = record_detail_list($b_id, date('Ymd'), $par);

        $remain   = 0;
        foreach ($res['list'] as $v) {
            $remain += $v['remain_time'];
        }

        an_dump($b_info['title']);
        an_dump('室内总停留时长：'.$remain);
        an_dump('室内总人数：'.$res['count']);

        $avg = (int)($remain / $res['count']);

        an_dump('平均：'.$avg.'秒');
        an_dump('平均：'.(int)($avg / 60).'分');

        $et_avg = 0;
        $lt_avg = 0;
        foreach ($res['list'] as $v) {
            if ( $v['remain_time'] >= $avg ) {
                $et_avg ++;
            } else {
                $lt_avg ++;
            }
        }

        an_dump('大于平均：'.$et_avg);
        an_dump('小于平均：'.$lt_avg);
    }
}