<?php
/**
  * alltosun.com 探针数据比较 data_diff.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2018年4月12日 下午3:08:19 $
  * $Id$
  */
set_time_limit(0);
probe_helper::load('func');

class Action
{

    /**
     * 获取室内室外用户数
     * @return string
     */
    public function get_user_count()
    {

        //西单厅 46120
        $probe_dev = tools_helper::Get('probe_dev', '20:28:18:a2:dc:1a');
        $day = tools_helper::Get('day', '2018-04-05');
        $day = date('Ymd', strtotime($day));

        //查询探测到的室内人数
        $indoor_mac = $this->get_user_mac($probe_dev, $day, 1);

        //查询探测到的室外人数
        $outdoor_mac = $this->get_user_mac($probe_dev, $day, 0);

        echo $probe_dev.': 室内人数：'.count($indoor_mac);
        echo '<br>';
        echo $probe_dev.': 室外人数：'.count($outdoor_mac);
    }

    /**
     * 对比室内
     */
    public function diff_indoor_user()
    {
        //设备1
        $probe_dev1 = tools_helper::Get('probe_dev1', '16120801');
        //设备1
        $probe_dev2 = tools_helper::Get('probe_dev2', '16120803');

        //查询2018-04-05 号的室内室外数据
        $day = tools_helper::Get('day', '2017-06-05');
        $day = date('Ymd', strtotime($day));


        //查询探测到的室内人数
        $indoor_mac1 = $this->get_user_mac($probe_dev1, $day, 1);

//         $day = tools_helper::Get('day', '2017-06-02');
//         $day = date('Ymd', strtotime($day));

        //查询探测到的室内人数
        $indoor_mac2 = $this->get_user_mac($probe_dev2, $day, 1);

        //比较差异
        $diff1 = array_diff($indoor_mac1, $indoor_mac2);

        $diff2 = array_diff($indoor_mac2, $indoor_mac1);

        //比较交集
        $intersect = array_intersect($indoor_mac1, $indoor_mac2);

        //查询探测到的室内人数
        $indoor_mac1 = $this->get_user_mac($probe_dev1, $day, 1);
        $indoor_mac2 = $this->get_user_mac($probe_dev2, $day, 1);

        //查询探测到的室外人数
        $outdoor_mac1 = $this->get_user_mac($probe_dev1, $day, 0);
        $outdoor_mac2 = $this->get_user_mac($probe_dev2, $day, 0);

        echo '<h3>'.$probe_dev1.': 室内室外人数：'.count($indoor_mac1).'，&nbsp;'.count($outdoor_mac1).'</h3>';
        echo '<h3>'.$probe_dev2.': 室内室外人数：'.count($indoor_mac2).'，&nbsp;'.count($outdoor_mac2).'</h3>';
        echo '<hr>';
        //用户数量对比
        echo "<h3>{$probe_dev1}设备探测到且{$probe_dev2}未探测到的室内用户有：<b>".count($diff1)."</b> 个</h3>";
        echo "<h3>{$probe_dev2}设备探测到且{$probe_dev1}未探测到的室内用户有：<b>".count($diff2)."</b> 个</h3>";
        echo "<h3>{$probe_dev2}和{$probe_dev1}同时探测到的室内用户有：<b>".count($intersect)."</b> 个</h3>";
        echo '<hr>';

        //详情
        echo "<h3>{$probe_dev1}设备探测到且{$probe_dev2}未探测到的用户有：</h3>";
        foreach ($diff1 as $k => $v) {
            echo probe_helper::mac_encode($v).'<br>';
        }
        echo '<hr>';
        echo "<h3>{$probe_dev2}设备探测到且{$probe_dev1}未探测到的用户有：</h3>";
        foreach ($diff2 as $k => $v) {
            echo probe_helper::mac_encode($v).'<br>';
        }
        echo '<hr>';
        echo "<h3>{$probe_dev2}和{$probe_dev1}同时探测到的用户有：</h3>";
        foreach ($intersect as $k => $v) {
            echo probe_helper::mac_encode($v).'<br>';
        }
    }

//     /**
//      * 比较上报时间
//      */
//     public function diff_report_time()
//     {
//         //设备1
//         $probe_dev = tools_helper::Get('probe_dev1', '16120801');

//         //查询2018-04-05 号的室内室外数据
//         $day = tools_helper::Get('day', '2017-06-05');
//         $day = date('Ymd', strtotime($day));

//         $probe_info = _model('probe_device')->read(array('device' => $probe_dev));

//         if (!$probe_info) {
//             return '不存在的设备';
//         }

//         //查询2018-04-05 号的室内室外数据
//         $db = get_db($probe_info['business_id']);

//         $filter = array(
//                 'date'      => $day,
//                 'dev'       => $probe_dev,
//         );
//         $add_times = $db->getFields('add_time', $filter, ' GROUP BY `add_time` ORDER BY `id` ASC');

//         $new_arr = array();
//         foreach ( $add_times as $v ) {
//             $k = substr($v, 0, 16);
//             if (empty($new_arr[$k])) {
//                 $new_arr[$k] = 1;
//             }
//         }

//         if (!$add_times) {
//             echo '暂无探针数据';
//             exit();
//         }

//         $hours  = array();
//         $start  = date('Y-m-d H:i', strtotime($add_times[0]));
//         $end    = date('Y-m-d H:i',strtotime($add_times[count($add_times)-1]));
//         do{
//             $hours[] = $start;
//             $start = date('Y-m-d H:i', strtotime($start) + 60);
//         }while($start <= $end);

//         foreach ($hours as $v) {
//             if (!isset($new_arr[$v])) {
//                 echo '<b><font color="red">'.$v.'</font></b><br>';
//             } else {
//                 echo '<font color="green">'.$v.'</font><br>';
//             }
//         }

//     }

    /**
     * 获取用户mac
     */
    private function get_user_mac($probe_dev, $day, $is_indoor=1)
    {
        $probe_info = _model('probe_device')->read(array('device' => $probe_dev));

        if (!$probe_info) {
            return '不存在的设备';
        }

        //查询2018-04-05 号的室内室外数据
        $db = get_db($probe_info['business_id']);

        $filter = array(
                'date'      => $day,
                'dev'       => $probe_dev,
                'is_indoor' => $is_indoor,
        );
        //查询探测到的室内人数
        return $db->getFields('mac', $filter, ' GROUP BY mac ');
    }

    /**
     * 比较探测到的用户数据
     */
    public function diff_user()
    {

        //设备1
        $probe_dev1 = tools_helper::Get('probe_dev1', '20:28:18:a2:db:a6');
        //设备1
        $probe_dev2 = tools_helper::Get('probe_dev2', 'la59a63ff4026fa');
        //mac
        $mac = tools_helper::Get('mac', '');

        $mac = probe_helper::mac_decode($mac);

        //查询2018-06-01 号的室内室外数据
        $day    = tools_helper::Get('day', '2018-06-01');
        $date   = date('Ymd', strtotime($day));


        $hours  = array();
        $start  = date('Y-m-d 07:00:00', strtotime($day));
        $end    = date('Y-m-d 23:00:00',strtotime($day));
        do{
            $hours[] = $start;
            $start = date('Y-m-d H:00:00', strtotime($start) + 3600);
        }while($start <= $end);

        $probe_info = _model('probe_device')->read(array('device' => $probe_dev1));

        if (!$probe_info) {
            echo '不存在的设备'.' '.$probe_dev1;exit;
        }

        //查询2018-04-05 号的室内室外数据
        $db1 = get_db($probe_info['business_id'], 'hour');

        $probe_info = _model('probe_device')->read(array('device' => $probe_dev2));

        if (!$probe_info) {
            echo '不存在的设备'.' '.$probe_dev2;exit;
        }

        //查询2018-04-05 号的室内室外数据
        $db2 = get_db($probe_info['business_id'], 'hour');

        $new_data = array();

        foreach ($hours as $hour) {
            //查询指定小时的数据
            $start_time = strtotime($hour);
            $end_time = $start_time + 3600-1;

            $filter = array(
                    'mac'           => $mac,
                    'frist_time >='    => $start_time,
                    'up_time <='       => $end_time,
                    'dev'           => $probe_dev1,
            );
            $list = $db1->getList($filter);
            $remain_time1 = 0;
            $is_indoor1 = '无';
            foreach ($list as $k => $v) {
                $remain_time1 += $v['remain_time'];
                $is_indoor1 = $v['is_indoor'] == 1 ? '室内' : '室外';
            }

            $filter['dev'] = $probe_dev2;
            $list = $db2->getList($filter);

            $remain_time2 = 0;
            $is_indoor2 = '无';
            foreach ($list as $k => $v) {
                $remain_time2 += $v['remain_time'];
                $is_indoor2 = $v['is_indoor'] ? '室内' : '室外';
            }

            $new_data[] = array(
                'hour' => $hour,
                'data' => array(
                    'dev1' => $probe_dev1, //设备
                    'remain_time1' => round($remain_time1/60, 2).'分钟',  //停留时长
                    'is_indoor1'  => $is_indoor1,   //是否为室内

                    'dev2' => $probe_dev2, //设备
                    'remain_time2' => round($remain_time2/60, 2).'分钟',  //停留时长
                    'is_indoor2'  => $is_indoor2,   //是否为室内
                )
            );
        }
        $this->display($probe_dev1, $probe_dev2, $new_data);
    }

    /**
     * 比较探测到的用户数据详情
     */
    public function diff_user_detail()
    {

        //设备1
        $probe_dev1 = tools_helper::Get('probe_dev1', '20:28:18:a2:db:a6');
        //设备1
        $probe_dev2 = tools_helper::Get('probe_dev2', 'la59a63ff4026fa');
        //mac
        $mac = tools_helper::Get('mac', '');

        $mac = probe_helper::mac_decode($mac);

        //查询2018-06-01 号的室内室外数据
        $day    = tools_helper::Get('day', '2018-06-01');
        $date   = date('Ymd', strtotime($day));


        $hours  = array();
        $start  = date('Y-m-d 07:00:00', strtotime($day));
        $end    = date('Y-m-d 23:00:00',strtotime($day));
        do{
            $hours[] = $start;
            $start = date('Y-m-d H:00:00', strtotime($start) + 3600);
        }while($start <= $end);

        $probe_info = _model('probe_device')->read(array('device' => $probe_dev1));

        if (!$probe_info) {
            echo '不存在的设备'.' '.$probe_dev1;exit;
        }

        //查询2018-04-05 号的室内室外数据
        $db1 = get_db($probe_info['business_id'], 'hour');

        $probe_info = _model('probe_device')->read(array('device' => $probe_dev2));

        if (!$probe_info) {
            echo '不存在的设备'.' '.$probe_dev2;exit;
        }

        //查询2018-04-05 号的室内室外数据
        $db2 = get_db($probe_info['business_id'], 'hour');

        $new_data = array();

        foreach ($hours as $hour) {
            //查询指定小时的数据
            $start_time = strtotime($hour);
            $end_time = $start_time + 3600-1;

            $filter = array(
                    'mac'           => $mac,
                    'frist_time >='    => $start_time,
                    'up_time <='       => $end_time,
                    'dev'           => $probe_dev1,
            );
            $list = $db1->getList($filter);
            $time_line1 = '';
            foreach ($list as $k => $v) {
               if ($time_line1) {
                    $time_line1 .= ',';
               }
               $time_line1 .= $v['time_line'];
            }

            $filter['dev'] = $probe_dev2;
            $list = $db2->getList($filter);

            $time_line2 = '';
            foreach ($list as $k => $v) {
                if ($time_line2) {
                    $time_line2 .= ',';
                }
                $time_line2 .= $v['time_line'];
            }

            $new_data[] = array(
                    'hour' => $hour,
                    'data' => array(
                            'dev1' => $probe_dev1, //设备
                            'time_line1' => $time_line1,
                            'dev2' => $probe_dev2, //设备
                            'time_line2' => $time_line2,
                    )
            );
        }
        echo '<table border="1" width="800">';
        echo "<tr><th>时间</th><th>{$probe_dev1}</th><th>{$probe_dev2}</th></tr>";
        foreach ($new_data as $k => $v) {
            echo "<tr>";
            echo "<td>{$v['hour']}</td>";
            echo '<td>';
            $arr = explode(',', $v['data']['time_line1']);
            if (!$arr || !$v['data']['time_line1']) {
                echo '无';
            } else {
                foreach ($arr as $v2) {
                    $v2_arr = explode(':', $v2);
                    if (empty($v2_arr[1])) {
                        $v2_arr[1] = 0;
                    }
                    echo '探测时间：'.date('Y-m-d H:i:s', $v2_arr[0]).'&nbsp;&nbsp;&nbsp;信号：'.$v2_arr[1].'<br>';
                }
            }

            echo '</td>';
            echo '<td>';
            $arr = explode(',', $v['data']['time_line2']);
            if (!$arr || !$v['data']['time_line2']) {
                echo '无';
            } else {
                foreach ($arr as $v2) {
                    $v2_arr = explode(':', $v2);
                    if (empty($v2_arr[1])) {
                        $v2_arr[1] = 0;
                    }
                    echo '探测时间：'.date('Y-m-d H:i:s', $v2_arr[0]).'&nbsp;&nbsp;&nbsp;信号：'.$v2_arr[1].'<br>';
                }
            }

            echo '</td>';


            echo "</tr>";
        }
        echo '</table>';
    }


    private function display($dev1, $dev2, $new_data)
    {
        $css = "
        <style>
            table {
                table-layout: fixed;
                width: 800px;
                border:0px;
                margin: 0px;
                font-family : 微软雅黑,宋体;
                font-size : 0.5em;
                border: 1px solid #ccc;
            }

            tr td {
                text-overflow: ellipsis; /* for IE */
                -moz-text-overflow: ellipsis; /* for Firefox,mozilla */
                overflow: hidden;
                white-space: nowrap;

                text-align: left
            }
        </style>";

        $html = $css.'<table border="1" cellspacing="0" cellpadding="5" width="150%">'.$this -> join_table_hander($dev1, $dev2);
        foreach ($new_data as $k => $v) {
            $html.= $this -> join_table_content($v['hour'], $v['data']);
        }
        $html .= '</table>';
        echo $html;
        exit();
    }

    /**
     * 拼接表内容
     */
    private function join_table_content($hour, $info)
    {

        $html = '<tr>';

        $html .= "
        <td title='{$hour}'><b>{$hour}</b></td>
        <td>体验时长:{$info['remain_time1']}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$info['is_indoor1']}</td>
        <td>体验时长:{$info['remain_time2']}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$info['is_indoor2']}</td>
        <tr>";
        return $html;
    }

    /**
     * 拼接表头
     */
    private function join_table_hander($dev1, $dev2)
    {
        return  "<tr>
                    <th width='10%'>小时</th>
                    <th width='20%'>{$dev1}</th>
                    <th width='20%'>{$dev2}</th>
                <tr> ";
    }


    /**
     * 上报稳定性测试
     */
    public function diff_report_time()
    {
        //设备1
        $probe_dev = tools_helper::Get('probe_dev', 'df5a4dbf59ec49a');

        //查询2018-04-05 号的室内室外数据
        $day = tools_helper::Get('day', '2018-06-04');
        $day = date('Y-m-d 00:00:00', strtotime($day));

        $filter = array(
                'report_time >='      => $day,
                'report_time <='      => date('Y-m-d 23:59:59', strtotime($day)),
                'dev'       => $probe_dev,
        );
        $add_times = _model('probe_report')->getFields('report_time', $filter, ' GROUP BY `report_time` ORDER BY `id` ASC');
        $new_arr = array();
        foreach ( $add_times as $v ) {
            $k = substr($v, 0, 16);
            if (empty($new_arr[$k])) {
                $new_arr[$k] = 1;
            }
        }

        if (!$add_times) {
            echo '暂无探针数据';
            exit();
        }

        $hours  = array();
        $start  = date('Y-m-d H:i', strtotime($add_times[0]));
        $end    = date('Y-m-d H:i',strtotime($add_times[count($add_times)-1]));
        do{
            $hours[] = $start;
            $start = date('Y-m-d H:i', strtotime($start) + 60);
        }while($start <= $end);
        echo '<head><title>数据上报稳定性测试</title></head>';
        echo '<h2>数据上报稳定性测试</h2>';
        echo '<h4 style="color:green">测试说明：5分钟内未上报数据则为异常，否则为正常。下列测试结果中，连续5次被标红则为异常。</h4>';
        echo '<h4 style="color:green">解释：红色代表指定分钟未上报数据， 绿色代表指定分钟已上报一条或多条数据</h4>';
        echo '<h6 style="color:green">时间：'.$start.'至'.$end.'</h6>';
        foreach ($hours as $v) {
            if (!isset($new_arr[$v])) {
                echo '<b><font color="red">'.$v.'</font></b><br>';
            } else {
                echo '<font color="green">'.$v.'</font><br>';
            }
        }
    }

    /**
     * 同一Mac的稳定性测试
     */
    public function diff_mac()
    {

        //设备1
        $probe_dev = tools_helper::Get('probe_dev', '20:28:18:a2:db:a6');
        //mac
        $mac = tools_helper::Get('mac', '');

        $interval =  tools_helper::Get('interval', 10);

        if ($interval) {
            $interval = $interval*60;
        } else {
            $interval = 30;
        }

        $mac = probe_helper::mac_decode($mac);

        //查询2018-06-01 号的室内室外数据
        $day    = tools_helper::Get('day', '2018-06-05');
        $date   = date('Ymd', strtotime($day));


        $hours  = array();
        $start  = date('Y-m-d 07:00:00', strtotime($day));
        $end    = date('Y-m-d 23:00:00',strtotime($day));
        do{
            $hours[] = $start;
            $start = date('Y-m-d H:00:00', strtotime($start) + 3600);
        }while($start <= $end);

        $probe_info = _model('probe_device')->read(array('device' => $probe_dev));

        if (!$probe_info) {
            echo '不存在的设备'.' '.$probe_dev;exit;
        }

        //查询2018-04-05 号的室内室外数据
        $db = get_db($probe_info['business_id'], 'hour');

        foreach ($hours as $hour) {
            //查询指定小时的数据
            $start_time = strtotime($hour);
            $end_time = $start_time + 3600-1;

            $filter = array(
                    'mac'           => $mac,
                    'frist_time >='    => $start_time,
                    'up_time <='       => $end_time,
                    'dev'           => $probe_dev,
            );
            $list = $db->getList($filter);
            $time_line1 = '';
            foreach ($list as $k => $v) {
               if ($time_line1) {
                    $time_line1 .= ',';
               }
               $time_line1 .= $v['time_line'];
            }

            $new_data[] = array(
                    'hour' => $hour,
                    'data' => array(
                            'dev1' => $probe_dev, //设备
                            'time_line1' => $time_line1,
                    )
            );
        }
        echo '<head><title>同一MAC地址的稳定性测试</title></head>';
        echo '<h2>同一MAC地址的稳定性测试</h2>';
        echo '<h4 style="color:green">测试说明：测试指定MAC的数据收集</h4>';
        echo '<h4 style="color:green">测试MAC：'.$mac.'</h4>';
        echo '<h4 style="color:green">测试结果如下：</h4>';

        echo '<table border="1" width="800">';
        echo "<tr><th>时间</th><th>{$probe_dev}</th></tr>";

        $last_probe_time = 0;

        foreach ($new_data as $k => $v) {
            echo "<tr>";
            echo "<td>{$v['hour']}</td>";
            echo '<td>';
            $arr = explode(',', $v['data']['time_line1']);
            if (!$arr || !$v['data']['time_line1']) {
                echo '无';
            } else {
                foreach ($arr as $v2) {
                    $v2_arr = explode(':', $v2);
                    if (empty($v2_arr[1])) {
                        $v2_arr[1] = 0;
                    }
                    $red = false;
                    if ($last_probe_time > 0 && ( ($v2_arr[0] - $last_probe_time ) > $interval)) {
                        $red = true;
                    }

                    if ( $red ) {
                        echo '<span style="color:red">';
                    }

                    echo '探测时间：'.date('Y-m-d H:i:s', $v2_arr[0]).'&nbsp;&nbsp;&nbsp;信号：'.$v2_arr[1].'<br>';

                    if ( $red ) {
                        echo '</span>';
                    }
                    $last_probe_time = $v2_arr[0];
                }
            }

            echo '</td>';
            echo "</tr>";
        }
        echo '</table>';
    }

    /**
     * 断网数据存储以及上报能力的测试
     */
    public function data_local_storage()
    {

        //设备
        $probe_dev = tools_helper::Get('probe_dev', '20:28:18:a2:db:a6');

        //指定日期
        $start_time    = tools_helper::Get('start_time', date('Y-m-d H:i:s'));
        $end_time      = tools_helper::Get('end_time', date('Y-m-d H:i:s'));

        //查询上报数据
        $filter = array(
                'report_time >='    => $start_time,
                'report_time <='    => $end_time,
                'dev'               => $probe_dev,
        );

        $list = _model('probe_report')->getList($filter);

        $new_data = [];
        foreach ($list as $tmp) {
            $data_arr = explode(';', $tmp['probe_data']);

            $new_data[] = array(
                    'dev' => $tmp['dev'],
                    'report_time' => $tmp['report_time'],
                    'data' => $data_arr
            );

        }
        echo '<head><title>断网数据存储以及上报能力的测试</title></head>';
        echo '<h2>断网数据存储以及上报能力的测试</h2>';
        echo '<h4 style="color:green">测试说明：由2018/06/04 19:10:00断网 ， 至2018/06/05 10:34:00恢复网络。测试断网期间数据是否正常收集，以及恢复网络后是否将断网期间收集的数据正常上报</h4>';
        echo '<h4 style="color:green">测试结果如下：</h4>';
        echo '<table border="1" width="800">';
        echo "<tr><th>上报时间</th><th>上报内容（设备：{$probe_dev}）</th></tr>";
        foreach ($new_data as $k => $v) {
            echo "<tr>";
            echo "<td>{$v['report_time']}</td>";
            echo '<td>';
            if (!$v['data']) {
                echo '无';
            } else {
                foreach ($v['data'] as $v2) {
                    $arr = explode(',', $v2);
                    if (!empty($arr[2])) {
                        list($s, $t) = explode(':', $arr[2]);
                        $t = date('Y-m-d H:i:s', $t);
                        $arr[2] = '探测时间:'.$t;

                        $str = $arr[0].'&nbsp;&nbsp;&nbsp;'.$arr[1].'&nbsp;&nbsp;&nbsp;'.$arr[2].'<br>';
                        $str = str_replace('mac', '用户MAC', $str);
                        $str = str_replace('rssi', '信号', $str);
                        echo $str;
                    }
                }

            }

            echo '</td>';
            echo "</tr>";
        }
        echo '</table>';
    }





}