<?php

/**
 * storage
 *
 * @author wangl
 */
trait storage
{
    /**
     * 设备信息
     *
     * @var Array
     */
    private $dev_info = array();

    /**
     * 写入数据
     *
     * @param   Array   macs
     */
    public function write($macs)
    {
        // 遍历数据，挨个存储
        foreach ($macs as $k => $v) {
            try {
                // 按小时存
                $this->hour($v);
                // 按天存
                $this->day($v);
            } catch (Exception $e) {

                //取消设备不存在日志
                $msg = $e -> getMessage();

                if (strpos($msg, 'dev info is empty.') === false) {
                    probe_helper::write_log('storage', $msg);
                }

            }
        }
    }

    /**
     *
     *
     *
     */
    private function init($info, &$dev, &$mac, &$rssi, &$time)
    {
        if ( empty($info['mac']) || empty($info['dev']) ) {
            throw new Exception('storage hour empty mac or empty dev.');
        }

        $dev = $info['dev'];
        $mac = $info['mac'];
        $time= $info['time'];
        $rssi= abs($info['rssi']);

        // 格式化mac地址
        if ( !is_numeric($mac) ) {
            $mac = probe_helper::mac_decode($mac);
        }

        // 设备信息
        if ( empty($this->dev_info) || $this->dev_info['device'] != $dev ) {
            // 获取设备信息
            $this->dev_info = probe_dev_helper::get_info($dev);

            if ( empty($this->dev_info) ) {
                throw new Exception('dev info is empty. dev is '.$dev);
            }
        }
    }

    /**
     * 按小时存
     *
     * @author  wangl
     */
    private function hour($info)
    {
        //赋值源数据，预防后续逻辑更改
        $orig_info = $info;
        $this->init($info, $dev, $mac, $rssi, $time);
        $b_id  = $this->dev_info['business_id'];
        // 获取操作数据库对象
        $db    = get_db($b_id, 'hour');
        // 年月日时间
        $date  = date('Ymd', $time);
        // 小时开始时间戳
        $start = strtotime(date('Y-m-d H:00:00', $time));
        // 小时结束时间戳
        $end   = strtotime(date('Y-m-d H:59:59', $time));
        // 阀值，值暂时为添加设备时所填的值
        $threshold = abs($this->dev_info['rssi']);

        //连续N次为室内的值
        $n         = 4;
        //N分钟之内叠加停留时长的值
        $l_time      = 10;

        //当在测试营业厅下   机制变更为 1次为室内  20分钟以上不计算时长
        if (110687 == $b_id) {
            $n = 1;
            $l_time = 20;
        }

        // 查询条件
        $filter = array(
            'mac'   =>  $mac,
            'date'  =>  $date,
            'dev'   =>  $dev,
            'frist_time >=' =>  $start,
            'frist_time <=' =>  $end
        );

        // 查询当前mac地址，某小时是否存在
        $info = $db -> read($filter, ' ORDER BY `id` DESC ');

        // 已存在当前mac
        if ( $info ) {
            // 修改内容
            $update = array(
                'up_time'       =>  $time,
                'up_rssi'       =>  $rssi
            );

            // 计算本次探测时间和上次探测时间相差多少秒
            $diff = $time - $info['up_time'];

            // 注：用户可能进厅之后离开，然后再次进厅，如果用户离开的时间小于10分钟的话，则加停留时长，否则不加停留时长
            if ($diff > 0 &&  $diff < $l_time * 60 ) {
                //$diff > 0  可能网络原因导致上报延迟，上报的探测时间比当前时间还小 wangjf
                $update['remain_time'] = (int)($info['remain_time'] + $diff);
            } else {

            }

            // 注：当探测到用户时，需要判断该用户是否在室内。当前判断规则是：当一个用户连续n次的信号小于某值时，认为是室内。n暂定为4

            // 注：indoor_num记录了该mac已经连续几次为室内
            $indoor_num= $info['indoor_num'];
            // 当前是否已经为室内
            $is_indoor = $info['is_indoor'];

            // 注：该用户不是室内的话才判断
            if ( $is_indoor == 0 ) {
                // 注：如果当前的信号值大于阀值，则打断了连续为室内，将indoor_num重置为0否则indoor_num加1
                if ( $rssi > $threshold ) {
                    $indoor_num = 0;
                } else {
                    $indoor_num ++;
                }

                if ( $indoor_num >= $n ) {
                    // 修改当前mac为室内
                    $update['is_indoor']  = 1;
                }
                // 修改连续探测为室内的次数
                $update['indoor_num'] = $indoor_num;
            }

            // 注：设备每次探测到某个mac地址时都会上报探测信息。每次上报的信息保存到time_line字段中。如果用户在探针下停留时间太久，将导致该字段太大，后续考虑优化
            $update['time_line'] = $info['time_line'].','.$time.':'.$rssi;

            //修改当前为室内用户
            if (110687 == $b_id) {
                $update['is_indoor']  = 1;
            }

            // 更新
            $db->update(array('id'=>$info['id']), $update);

            // 注：在实际应用中发现会有这种情况：营业厅下有两个设备，其中一个设备最后探测该mac的时间10:30，并且为室外，另一个设备第一次探测到该mac的时间为10:40，并且为室内，这样的话一个设备探测是室外，另一个设备探测是室内，会对统计那里造成影响，导致数据不一致。解决办法是当该用户在当前这个设备为室内时，更新该用户在另外设备也为室内
            if ( !empty($update['is_indoor']) ) {
                $db -> update(array(
                    'mac'   =>  $mac,
                    'date'  =>  $date,
                    'b_id'  =>  $b_id,
                    'frist_time >=' =>  $start,
                    'frist_time <=' =>  $end,
                    'is_indoor' =>  0
                ), array('is_indoor' => 1));
            }
        // 不存在当前mac
        } else {

            //当前小时的停留时长
            $remain_time = 0;

            //wangjf add N分钟前是否是上一小时
            $is_last_hour       = false;
            $last_hour_info     = array();
            $last_hour_start    = date('Y-m-d H:00:00', $time - $l_time * 60);
            $last_hour_remain_time = 0;

            if ($last_hour_start < $start) {
                $is_last_hour   = true;
            }

            //是上一小时
            if ($is_last_hour) {

                $last_hour_start    = strtotime($last_hour_start);
                $last_hour_end      = strtotime(date('Y-m-d H:59:59', $time - $l_time * 60));

                //查询上一小时mac地址是否存在
                $filter['frist_time >='] = $last_hour_start;
                $filter['frist_time <='] = $last_hour_end;
                $last_hour_info = $db -> read($filter, ' ORDER BY `id` DESC ');

                //上一小时的最后一次数据上报与本次上报是否符合停留时长的叠加规则
                if ($time - $last_hour_info['up_time'] < $l_time * 60) {
                    //符合规则 则重新组装为上一小时的数据, 进行存储
                    $orig_info['time']       = $last_hour_end;
                    //回调
                    try {
                        // 按小时存
                        $this->hour($orig_info);
                        // 按天存
                        $this->day($orig_info);

                    } catch (Exception $e) {

                        //取消设备不存在日志
                        $msg = $e -> getMessage();

                        if (strpos($msg, 'dev info is empty.') === false) {
                            probe_helper::write_log('storage', $msg);
                        }

                        return false;
                    }

                    //当前小时的数据
                    $remain_time = $time - $start;
                    if ($remain_time < 0) {
                        $remain_time = 0;
                    } else {
                        $remain_time + 1; //因刚才从59秒算起，所以要把上1小时缺少的那1秒叠加至当前时间
                    }
                }
            }

            // 注：在添加该mac时，需要判断该mac是否为老顾客。判断的方式为：只要该mac在之前出现过就认为是老顾客
            $is_oldcustomer = $this -> is_oldcustomer($mac, $b_id, $date);

            // 注：只有该mac是老顾客时才获取该mac的活跃天数，不是老顾客时活跃天数为0
            if ( $is_oldcustomer  ) {
                // 注：由于营业厅中有营业员，在统计室内人数或者其他统计的时候希望把营业员过滤掉，所以加了活跃天数来标识营业员。活跃天数计算方法：当一个用户在某天停留时长大于m小时时认为活跃1天。（当连续活跃n天时认为该mac是营业厅员，但是也有可能是其他情况）
                $continued = $this -> get_continuity($mac, $b_id, $time);
            } else {
                $continued = 0;
            }

            $create = array(
                'dev'           =>  $dev,  // 设备
                'b_id'          =>  $b_id, // 营业厅
                'date'          =>  $date, // 时间
                'mac'           =>  $mac,  // mac地址
                'frist_time'    =>  $time, // 首次探测时间
                'up_time'       =>  $time, // 最后探测时间
                'frist_rssi'    =>  $rssi, // 首次探测信号
                'up_rssi'       =>  $rssi, // 最后探测信号
                'remain_time'   =>  $remain_time,     // 停留时长
                'continued'     =>  $continued,
                'is_oldcustomer'=>  $is_oldcustomer,
                'time_line'     =>  $time.':'.$rssi
            );

            if (110687 == $b_id) {
                $create['is_indoor'] = 1;
            }

            // 注：indoor_num记录了该mac已经连续几次为室内
            if ( $rssi <= $threshold ) {
                $create['indoor_num'] = 1;
            }

            // 注：当一个设备最后探测时间是10:30，并且为室内时，另一个设备第一次探测时间为10:40，并且一直都是室外时，这就和更新时一样产生一个是室内一个是室外的情况，这里的解决方法是在添加是查询下该mac是否在其他设备下被标识为室内，如果有在当前设备下也标识为室内
            $is_indoor = $this -> is_indoor($mac, $b_id, $time);

            if ( $is_indoor ) {
                $create['is_indoor'] = 1;
            }

            // 添加
            $id = $db->create($create);
        }
    }

    /**
     * 按天存
     *
     * @param   Array
     */
    private function day($info)
    {
        $this->init($info, $dev, $mac, $rssi, $time);

        $b_id  = $this->dev_info['business_id'];
        // 获取操作数据库对象
        $db    = get_db($b_id, 'hour');
        // 年月日时间
        $date  = date('Ymd', $time);
        // 小时开始时间戳
        $start = strtotime(date('Y-m-d H:00:00', $time));
        // 小时结束时间戳
        $end   = strtotime(date('Y-m-d H:59:59', $time));

        // 查询今天探测到该mac的所有记录
        $list = $db -> getList(array(
            'mac'   =>  $mac,
            'date'  =>  $date,
            'dev'   =>  $dev,
            'b_id'  =>  $b_id
        ), ' ORDER BY `id` ASC ');

        // 注：是否为室内。只要小时记录表中该mac的任何一条数据为室内则为室内
        $is_indoor      = 0;
        // 注：是否为老顾客。只要小时记录表中该mac的任何一条数据为老顾客，则为老顾客
        $is_oldcustomer = 0;
        // 注：停留时长。小时记录表中每条数据停留时长之和
        $remain_time    = 0;
        // 注：首次探测时间。小时记录表中最小的frist_time
        $frist_time     = 0;
        // 注：最后探测时间。小时记录表中最大的up_time
        $up_time        = 0;
        // 注：首次探测信号。小时记录表中最小frist_time对应的信号
        $frist_rssi     = 0;
        // 注：最后探测信号。小时记录表中最大up_rsii对应的信号
        $up_rssi        = 0;
        // 注：活跃天数。小时记录表中最大活跃天数
        $continued      = 0;

        foreach ($list as $key => $val) {
            // 累加每条记录的停留时长
            $remain_time += $val['remain_time'];

            // 只要其中某条记录为室内，就为室内
            if ( $val['is_indoor'] ) {
                $is_indoor = 1;
            }

            if ( $val['is_oldcustomer'] ) {
                // 老顾客
                $is_oldcustomer = 1;
            }

            // 首次探测时间和首次探测信号
            if ( $frist_time == 0 ) {
                $frist_time = $val['frist_time'];
                $frist_rssi = $val['frist_rssi'];
            }

            // 最后探测时间和最后探测信号
            if ( $val['up_time'] > $up_time ) {
                $up_time = $val['up_time'];
                $up_rssi = $val['up_rssi'];
            }

            // 活跃时长
            if ( $val['continued'] > $continued ) {
                $continued = $val['continued'];
            }
        }

        // 获取数据库操作对象
        $db = get_db($b_id);

        // 查询条件
        $filter = array(
            'mac'   =>  $mac,
            'date'  =>  $date,
            'dev'   =>  $dev,
            'b_id'  =>  $b_id
        );

        // 查询按天统计表中是否出现过该mac
        $info = $db -> read($filter);

        // 如果出现过则更新
        if ( $info ) {
            // 要更新信息
            $update = array(
                'up_time'       =>  $up_time,
                'up_rssi'       =>  $up_rssi,
                'is_indoor'     =>  $is_indoor,
                'remain_time'   =>  $remain_time,
                'is_oldcustomer'=>  $is_oldcustomer
            );

            // 更新
            $db -> update($info['id'], $update);
        } else {
            // 要添加的信息
            $create = array(
                'dev'           =>  $dev,     // 设备
                'b_id'          =>  $b_id,    // 营业厅
                'date'          =>  $date,    // 时间
                'mac'           =>  $mac,     // mac地址
                'frist_time'    =>  $frist_time,
                'up_time'       =>  $up_time,
                'frist_rssi'    =>  $frist_rssi,
                'up_rssi'       =>  $up_rssi,
                'remain_time'   =>  $remain_time,
                'continued'     =>  $continued,
                'is_indoor'     =>  $is_indoor,
                'is_oldcustomer'=>  $is_oldcustomer
            );

            // 添加
            $db -> create($create);
        }

        // 注：在时间应用中，如果一个设备最后探测时间为10:30，并且为室内，另一个设备第一次探测时间为10:40并且为室外，这样两条数据不一致会导致统计数量对不上。解决方法是当探测为室内时，更新其他设备探测到该mac的信息
        if ( $is_indoor ) {
            $db -> update(array(
                'mac'       =>  $mac,
                'date'      =>  $date,
                'b_id'      =>  $b_id,
                'is_indoor' =>  0
            ), array('is_indoor' => 1));
        }
    }

    /**
     * 判断是否为老顾客
     *
     * @param   String  mac地址
     * @param   Int     营业厅ID
     * @param   Int     时间
     * @return  Bool
     */
    private function is_oldcustomer($mac, $b_id, $date)
    {
        if ( !$mac || !$b_id || !$date ) {
            return false;
        }

        $db = get_db($b_id);

        $filer = array(
                'mac'       =>  $mac,
                'date <'    =>  $date
        );

        $info = $db -> read($filer);

        return $info ? true : false;
    }

    /**
     * 获取活跃天数
     *
     * @param   String  mac地址
     * @param   Int     营业厅ID
     * @param   Int     时间
     * @return  Int
     */
    private function get_continuity($mac, $b_id, $time)
    {
        if ( !$mac || !$b_id || !$time ) {
            return 0;
        }

        // 数据库操作对象
        $db = get_db($b_id);

        // 营业厅规则
        $rules = probe_rule_helper::get_rules($b_id);

        // 没有规则返回0
        if ( empty($rules['continued'][1]) ) {
            return 0;
        }

        // 昨天的时间
        $date = (Int)date('Ymd', $time - (60 * 60 * 24));

        // 查询昨天停留时长最大的一条数据
        $filter     = array(
                'mac'   =>  $mac,
                'date'  =>  $date
        );
        $info = $db -> read($filter, 'ORDER BY `remain_time` DESC LIMIT 1');

        // 昨天不存在返回0
        if ( !$info ) {
            return 0;
        }

        $num = ((int)$rules['continued'][0]) * 60 * 60;

        // 昨天的停留时长大于规则设置的停留时长
        if ( $info['remain_time'] >= $num ) {
            return $info['continued'] + 1;
        }

        if ( $info['continued'] >= $rules['continued'][1] ) {
            return $info['continued'];
        } else {
            return 0;
        }
    }

    /**
     * 是否为室内
     *
     * @param   String  mac地址
     * @param   Int     营业厅
     * @param   Int     时间
     * @return  Bool
     */
    private function is_indoor($mac, $b_id, $time)
    {
        if ( !$mac || !$b_id || !$time ) {
            return false;
        }

        // 年月日时间
        $date  = date('Ymd', $time);
        // 小时开始时间戳
        $start = strtotime(date('Y-m-d H:00:00', $time));
        // 小时结束时间戳
        $end   = strtotime(date('Y-m-d H:59:59', $time));
        // 获取数据库操作对象
        $db    = get_db($b_id, 'hour');

        // 查询当前小时该mac是否已经为室内
        $filter = array(
                'mac'       =>  $mac,
                'date'      =>  $date,
                'is_indoor' =>  1,
                'frist_time >=' =>  $start,
                'frist_time <=' =>  $end
        );
        $info = $db -> read($filter);

        return $info ? true : false;
    }
}