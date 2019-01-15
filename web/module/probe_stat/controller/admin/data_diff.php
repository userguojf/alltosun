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

        echo '<h3>日期：'.date('Y-m-d', strtotime($day)).'</h3><br><br>';


        echo '<h3>'.$probe_dev1.': 室内人数：'.count($indoor_mac1).'</h3>';
        echo '<br>';
        echo '<h3>'.$probe_dev2.': 室内人数：'.count($indoor_mac2).'</h3>';

        echo '<hr>';

        echo "<h3>{$probe_dev1}设备探测到且{$probe_dev2}未探测到的用户有：</h3><br><br>";
        foreach ($diff1 as $k => $v) {
            echo probe_helper::mac_encode($v).'<br>';
        }
        echo '<hr>';
        echo "<h3>{$probe_dev2}设备探测到且{$probe_dev1}未探测到的用户有：</h3><br><br>";
        foreach ($diff2 as $k => $v) {
            echo probe_helper::mac_encode($v).'<br>';
        }
        echo '<hr>';
        echo "<h3>{$probe_dev2}和{$probe_dev1}同时探测到的用户有：</h3><br><br>";
        foreach ($intersect as $k => $v) {
            echo probe_helper::mac_encode($v).'<br>';
        }
    }

    /**
     * 比较上报时间
     */
    public function diff_report_time()
    {
        //设备1
        $probe_dev = tools_helper::Get('probe_dev1', '16120801');

        //查询2018-04-05 号的室内室外数据
        $day = tools_helper::Get('day', '2017-06-05');
        $day = date('Ymd', strtotime($day));

        $probe_info = _model('probe_device')->read(array('device' => $probe_dev));

        if (!$probe_info) {
            return '不存在的设备';
        }

        //查询2018-04-05 号的室内室外数据
        $db = get_db($probe_info['business_id']);

        $filter = array(
                'date'      => $day,
                'dev'       => $probe_dev,
        );
        $add_times = $db->getFields('add_time', $filter, ' GROUP BY `add_time` ORDER BY `id` ASC');

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

        foreach ($hours as $v) {
            if (!isset($new_arr[$v])) {
                echo '<b><font color="red">'.$v.'</font></b><br>';
            } else {
                echo '<font color="green">'.$v.'</font><br>';
            }
        }

    }

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


}