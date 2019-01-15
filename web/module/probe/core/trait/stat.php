<?php

// load func.php
probe_helper::load('func');

/**
 * stat trait
 *
 * @author wangl
 */
trait stat
{
    /**
     * 初始化统计返回数据
     *
     * @param   Int 营业厅ID
     *
     * @return  Array
     */
    private function init_data($b_id)
    {
        if ( !$b_id ) {
            return array();
        }

        // 取营业厅下设备
        $devs = probe_dev_helper::get_devs($b_id);

        if ( !$devs ) {
            return array();
        }

        // 返回数据格式
        $data = array(
            'dev'       =>  array(),
            'indoor'    =>  0,
            'oudoor'    =>  0,
            'new_num'   =>  0,
            'old_num'   =>  0,
            'remain'    =>  0
        );

        // 注：由于有些地方分设备统计，为了兼容，所有地方的统计都分设备
        foreach ( $devs as $dev => $rssi ) {
            $data['dev']['indoor'][$dev] = 0;
            $data['dev']['oudoor'][$dev] = 0;
            $data['dev']['near'][$dev]   = 0;
        }

        return $data;
    }

    /**
     * 遍历列表
     *
     * @param   Array   列表
     * @param   Int     营业厅ID
     *
     * @return  Array
     */
    private function each_list($list, $b_id)
    {
        // 初始化返回数据
        $data = $this->init_data($b_id);

        if ( !$data ) {
            return array();
        }

        // 获取规则
        $rule   = probe_rule_helper::get_rules($b_id);
        // 室外人数
        $indoor = array();
        // 室内人数
        $oudoor = array();
        // 较近人数
        $near   = array();

        // 遍历室内
        foreach ($list as $k => $v) {
            $dev        = strtolower($v['dev']);
            $continued  = $v['continued'];
            $remain     = $v['remain_time'];
            $mac        = $v['mac'];
            $is_indoor  = $v['is_indoor'];

            // 如果设置了continued规则，则判断是否满足
            //$rule['continued'][1] ： 连续活跃N天以上，不计入客流量（
            //$rule['continued'][0] ： 连续驻留N小时以上， 不计入客流量)（营业厅工作人员）
            if ( !empty($rule['continued'][1]) ) {
                if ( $continued >= $rule['continued'][1] ) {
                    continue;
                }
            }

            // 如果设置了minute规则，则判断是否满足 （连续驻留N分钟以下，不计入厅内）
            if ( !empty($rule['minute']) ) {
                if ( $remain < ($rule['minute'] * 60) ) {
                    $is_indoor = false;
                }
            }

            // 较近人数，暂时定死为55
            if ( ( abs($v['up_rssi']) <= 55 ) && ( (time() - $v['up_time'] ) <= 300) ) {
                // 去重
                if ( !isset($near[$mac]) ) {
                    if (!isset($data['dev']['near'][$dev])) {
                        // 分设备统计
                        $data['dev']['near'][$dev] = 1;
                    } else {
                        // 分设备统计
                        $data['dev']['near'][$dev] ++;
                    }

                    $near[$mac] = 1;
                }
            }

            // 室内
            if ( $is_indoor ) {
                // 去重
                if ( !isset($indoor[$mac]) ) {
                    $data['indoor'] ++;
                    $indoor[$mac] = 1;

                    // 分新老顾客统计
                    if ( $v['is_oldcustomer'] ) {
                        $data['old_num'] ++;
                    } else {
                        $data['new_num'] ++;
                    }

                    // 累加室内停留时长
                    $data['remain'] += $remain;
                }

                // 分设备统计
                $data['dev']['indoor'][$dev] ++;
            // 室外
            } else {
                // 去重
                if ( !isset($oudoor[$mac]) ) {
                    $data['oudoor'] ++;
                    $oudoor[$mac] = 1;
                }
                // 分设备统计
                $data['dev']['oudoor'][$dev] ++;
            }
        }

        return $data;
    }

    /**
     * 营业厅天统计
     *
     * @param   Int 营业厅ID
     * @param   Int 日期
     *
     * @return  Array
     */
    public function day_stat($b_id, $date)
    {
        if ( !$b_id || !$date ) {
            return array();
        }

        // 格式化时间，将y-m-d类的时间变成ymd类
        if ( !is_numeric($date) ) {
            $date = (int)date('Ymd', strtotime($date));
        }

        // 获取操作数据库对象
        $db   = get_db($b_id);
        // 查询语句
        $sql  = " SELECT `dev`, `mac`, `remain_time`, `continued`, `is_indoor`, `is_oldcustomer`, `up_rssi`, `up_time` FROM `{$db -> table}` WHERE `date` = {$date} AND `b_id` = {$b_id} ";
        // 查询今天的数据
        $data = $db -> getAll($sql);

        // 遍历列表，并得到统计信息
        $data = $this -> each_list($data, $b_id);

        return $data;
    }

    /**
     * 小时统计
     *
     * @param   Int 营业厅ID
     * @param   Int 日期
     *
     * @return  Array
     */
    public function hour_stat($b_id, $date)
    {
        if ( !$b_id || !$date ) {
            return array();
        }

        // 格式化时间，将y-m-d类的时间变成ymd类
        if ( !is_numeric($date) ) {
            $date = (int)date('Ymd', strtotime($date));
        }

        // 获取数据库操作对象
        $db   = get_db($b_id, 'hour');
        // 查询sql
        $sql  = "SELECT `dev`, `mac`, `remain_time`, `continued`, `is_indoor`, `is_oldcustomer`, `frist_time`, `up_rssi` FROM `{$db -> table}` WHERE `date` = {$date} AND `b_id` = {$b_id}";
        // 查询营业厅下某天的数据
        $list = $db->getAll($sql);

        $data = array();

        // 注：将列表按小时分组
        foreach ($list as $k => $v) {
            // 当前数据在哪个小时段
            $h = date('H', $v['frist_time']);

            if ( isset($data[$h]) ) {
                array_push($data[$h], $v);
            } else {
                $data[$h] = array($v);
            }
        }

        $list = array();

        // 遍历24小时，按小时统计数量
        for ( $i = 0; $i < 24; $i ++ ) {
            $key = $i < 10 ? "0{$i}" : "{$i}";

            $list[$key] = array();

            if ( isset($data[$key]) ) {
                $list[$key] = $this -> each_list($data[$key], $b_id);
            } else {
                $list[$key] = $this -> each_list(array(), $b_id);
            }
        }

        return $list;
    }

    /**
     * 探针品牌统计
     *
     * @param   Int 营业厅ID
     * @param   Int 时间
     *
     * @return  Array   返回统计信息
     */
    public function brand_stat($b_id, $date, $type = 'in')
    {
        if ( !$b_id || !$date ) {
            return array();
        }

        if ( $type == 'in' ) {
            $is_indoor = 1;
        } else {
            $is_indoor = 0;
        }

        // 获取操作数据库对象
        $db   = get_db($b_id);
        // 查询语句
        $sql  = "SELECT * FROM `{$db -> table}` WHERE `date`={$date} AND `b_id`={$b_id} AND `is_indoor`={$is_indoor} GROUP BY mac";
        // 查询指定营业厅指定天的室内mac列表
        $list = $db -> getAll($sql);
        // 品牌
        $ary  = array();
        /*
        // 当前页
        $page = Request::Get('page_no', 1);
        // 每页展示
        $per_page = 20;
        // 开始
        $start = ($page - 1) * $per_page;
        */

        foreach ($list as $k => $v) {
            // 查询mac地址的品牌
            $name = probe_helper::get_brand($v['mac']);

            if ( isset($ary[$name]) ) {
                $ary[$name] ++;
            } else {
                $ary[$name] = 1;
            }
        }

        return array('brand' => $ary, 'list' => $list);
    }

    /**
     * probe_widget按天跑计划任务
     *
     * @param   Int 时间
     *
     * @return  Bool
     */
    public function widget_day_corn($date)
    {
        if ( !$date ) {
            return false;
        }

        // 时间戳

        $time = strtotime($date);
        //$date = date('Ymd', $time);

        $page     = 1;
        $per_page = 20;

        // 查询有探针的营业厅，挨个跑这些厅
        $list = probe_dev_helper::get_list(array('status' => 1), '', 'GROUP BY `business_id` '.$this -> get_limit($page, $per_page));

        do {
            // 遍历列表
            foreach ($list as $k => $v) {
                // 统计当前营业厅当前天的数据
                $data = $this -> day_stat($v['business_id'], $date);

                // 要添加的信息
                $create = array(
                    'province_id'   =>  $v['province_id'],
                    'city_id'       =>  $v['city_id'],
                    'area_id'       =>  $v['area_id'],
                    'business_id'   =>  $v['business_id'],
                    'date_for_day'  =>  $date,
                    'date_for_week' =>  probe_helper::revise_week($time),
                    'date_for_month'=>  (int)date('Ym', $time),
                    'outdoor'       =>  $data['oudoor'],
                    'indoor'        =>  $data['indoor']
                );

                $filter = array(
                    'business_id'   =>  $v['business_id'],
                    'date_for_day'  =>  $date
                );

                // 查询讯息
                $info = _model('probe_stat_day')->read($filter);

                // 有则更新没有则添加
                if ( $info ) {
                    _model('probe_stat_day')->update(array('id'=>$info['id']), $create);
                } else {
                    _model('probe_stat_day')->create($create);
                }
            }

            // 注：由于使用dowhile，并不知道上面foreach有没有执行，所以这里判断一次
            if ( $list ) {
                sleep(1);

                $page ++;

                // 查询有探针的营业厅，挨个跑这些厅
                $list = probe_dev_helper::get_list(array('status' => 1), '', 'GROUP BY `business_id` '.$this -> get_limit($page, $per_page));
            }
        } while ($list);

        return true;
    }

    /**
     * probe_widget按小时跑计划任务
     *
     * @param   Int 时间
     *
     * @return  Bool
     */
    public function widget_hour_corn($time)
    {
        if ( !$time ) {
            return false;
        }

        // 获取当前小时
        $hour  = date('H', $time);
        // 当前天
        $date  = date('Ymd', $time);
        // 当前是第几页
        $page  = 1;
        // 每页显示多少条
        $per_page = 100;

        // 查询有探针的营业厅，挨个跑这些厅
        $list = probe_dev_helper::get_list(array('status' => 1), '', 'GROUP BY `business_id` '.$this -> get_limit($page, $per_page));

        do {
            // 遍历营业厅列表
            foreach ($list as $k => $v) {
                // 按小时统计当前营业厅当前天的数据
                $data = $this -> hour_stat($v['business_id'], $date);
                $res  = array();

                // 统计数据，找到当前小时的数据
                foreach ( $data as $h => $d ) {
                    if ( $h == $hour && $res = $d ) break;
                }

                if ( !$res ) {
                    continue;
                }

                $create = array(
                    'province_id'   =>  $v['province_id'],
                    'city_id'       =>  $v['city_id'],
                    'area_id'       =>  $v['area_id'],
                    'business_id'   =>  $v['business_id'],
                    'date_for_hour' =>  $hour,
                    'date_for_day'  =>  $date,
                    'date_for_week' =>  probe_helper::revise_week($time),
                    'date_for_month'=>  date('Ym', $time),
                    'indoor'        =>  $res['indoor'],
                    'outdoor'       =>  $res['oudoor'],
                    'new_num'       =>  $res['new_num'],
                    'old_num'       =>  $res['old_num'],
                    'remain_time'   =>  $res['remain']
                );

                $filter = array(
                    'business_id'   =>  $v['business_id'],
                    'date_for_day'  =>  $date,
                    'date_for_hour' =>  $hour
                );

                // 查询信息
                $info = _model('probe_stat_hour')->read($filter);

                // 有则更新，没则添加
                if ( $info ) {
                    _model('probe_stat_hour')->update(array('id'=>$info['id']), $create);
                } else {
                    _model('probe_stat_hour')->create($create);
                }
            }

            // 注：由于使用dowhile，并不知道list是否不为空，这里判断下，如果不为空取下次要跑的数据，为空则跳出循环
            if ( $list ) {
                // 休眠2秒
                sleep(2);

                $page ++;

                // 查询有探针的营业厅，挨个跑这些厅
                $list = probe_dev_helper::get_list(array('status' => 1), '', 'GROUP BY `business_id` '.$this -> get_limit($page, $per_page));
            }
        } while ($list);

        return true;
    }

    /**
     * 拼limit条件
     *
     * @param   Int 页码
     * @param   Int 每页显示多少条
     *
     * @return  String
     */
    private function get_limit( $page, $per_page )
    {
        return 'LIMIT '.($page - 1) * $per_page.','.$per_page;
    }

    /**
     * 获取设备列表
     *
     * @param   Int     营业厅ID
     * @param   String  mac地址
     *
     * @return  Array
     */
    public function get_mac_list($b_id, $mac)
    {
        if ( !$b_id || !$mac ) {
            return array();
        }

        $db   = get_db($b_id);
        // 查询语句
        $sql  = "SELECT * FROM `{$db -> table}` WHERE `mac` = {$mac}";
        // 查询mac地址的所有记录
        return $db -> getAll($sql);
    }

    /**
     * probe_record模块详细列表
     *
     * @param   Int     营业厅
     * @param   Int     时间
     * @param   Array   其他参数
     *
     * @return  Array
     */
    public function record_detail_list($b_id, $date, $param = array())
    {
        if ( !$b_id || !$date ) {
            return array();
        }

        // 获取操作数据库对象
        if ( isset($param['hour']) ) {
            $db = get_db($b_id, 'hour');
        } else {
            $db = get_db($b_id);
        }

        // 取营业厅规则
        $rules = probe_rule_helper::get_rules($b_id);

        // where条件
        $where = array();

        // 按mac查
        if ( !empty($param['mac']) ) {
            $where[] = " `mac` = '{$param['mac']}' ";
        }

        // 按时间查
        if ( $date ) {
            $where[] = " `date` = {$date} ";

            // 规则
            if ( !empty($rules['continued'][1]) ) {
                $where[] = " `continued` < {$rules['continued'][1]} ";
            }
        }

        // 按营业厅查
        if ( $b_id ) {
            $where[] = " `b_id` = {$b_id} ";
        }

        // 按设备查
        if ( !empty($param['dev']) ) {
            $where[] = " `dev` = '{$param['dev']}' ";
        }

        if ( isset($param['hour']) ) {
            $start   = strtotime($date.$param['hour'].'0000');
            $end     = strtotime($date.$param['hour'].'5959');
            $where[] =" `frist_time` >= {$start} AND `frist_time` <= {$end} ";
        }

        // 按室内室外查
        if ( empty($param['is_indoor']) ) {
            // 规则：停留时长小于n分钟不算室内人数
            if ( !empty($rules['minute']) ) {
                $sec     = $rules['minute'] * 60;
                // 注：如果有规则存在，室外包含停留时长小于规则所定的时长的室内内人数
                $where[] = " (`is_indoor` = 0 OR (`is_indoor` = 1 AND `remain_time` < {$sec})) ";
            } else {
                $where[] = " `is_indoor` = 0 ";
            }
        } else {
            $where[] = " `is_indoor` = 1 ";

            // 规则：停留时长小于n分钟不算室内人数
            if ( !empty($rules['minute']) ) {
                $sec     = $rules['minute'] * 60;
                $where[] = " `remain_time` >= {$sec} ";
            }
        }

        if ( !empty($param['remain']) ) {
            $where[] = " `remain_time` >= {$param['remain']} ";
        }

        $where = ' WHERE '.implode('AND', $where)." GROUP BY `mac` ";
        $count = "SELECT COUNT(id) FROM `{$db->table}` {$where}";
        $order = " ORDER BY `frist_time` ASC  ";
        $sql   = "SELECT * FROM `{$db->table}` {$where} {$order}";

        if ( empty($param['is_export']) ) {
            $page     = Request::Get('page_no', 1);
            $per_page = 20;
            $limit    = 'LIMIT '.($page - 1) * $per_page.','.$per_page;
            $sql     .= $limit;
        }

        if ( Request::Get('debug', 0) == 1 ) {
            an_dump($sql, $count);
        }

        $list   = array();
        $count  = $db->getAll($count);
        $count  = count($count);

        if ( $count ) {
            $list = $db->getAll($sql);
        }

        return array('count' => $count, 'list' => $list);
    }

    /**
     * probe_record模块时间线
     *
     * @param   Int     营业厅
     * @param   Int     时间
     * @param   String  mac
     * @param   String  探测设备
     *
     * @return  Array
     */
    public function record_mac_timeline($b_id, $date, $mac, $dev)
    {
        if ( !$b_id || !$date || !$mac ) {
            return array();
        }

        // 获取操作数据库对象
        $db = get_db($b_id, 'hour');

        $filter = array(
            'mac'   =>  $mac,
            'date'  =>  $date,
            'dev'   =>  $dev,
            'b_id'  =>  $b_id
        );

        return $db -> getList($filter, 'ORDER BY `id`');
    }

    /**
     * 数字地图接口
     *
     * @param   String  appi_id
     * @param   String  设备编号
     * @param   Int     日期
     *
     * @return  Array
     */
    public function api_szdt($app_id, $dev, $date)
    {

        if ( !$app_id || !$dev || !$date ) {
            throw new Exception('Parameter incomplete');
        }

        // 查询设备信息
        $dev_info = probe_dev_helper::get_info((String)$dev);

        if ( !$dev_info ) {
            throw new Exception('设备'.$dev.'不存在');
        }

        if ( !is_numeric($date) ) {
            $date = date('Ymd', strtotime($date));
        }

        // 设备所属营业厅
        $b_id = $dev_info['business_id'];
        // 时间戳
        $time = strtotime($date);
        // 昨天的时间
        $yesterdate = (int)date('Ymd', $time - 86400);

        // 取今天的数据
        $now_data  = $this -> day_stat($b_id, $date);
        // 取昨天的数据
        $prev_data = $this -> day_stat($b_id, $yesterdate);
        // 最终返回的数据
        $return    = array(
            'today'     =>  array(
                'indoor'    =>  $now_data['indoor'],    // 今天总室内
                'outdoor'   =>  $now_data['oudoor'],    // 今天总室外
                'dev'       =>  array(
                    'indoor'    =>  $now_data['dev']['indoor'][$dev],   // 设备室内
                    'outdoor'   =>  $now_data['dev']['oudoor'][$dev],   // 设备室外
                    'nearer'    =>  $now_data['dev']['near'][$dev]      // 设备较近
                )
            ),
            'yesterday' =>  array(
                'indoor'    =>  $prev_data['indoor'],   // 昨天总室内
                'outdoor'   =>  $prev_data['oudoor']    // 昨天总室外
            )
        );

        return $return;
    }

    /**
     * rfid接口
     *
     * @param   Int     营业厅ID
     * @param   String  设备编号
     * @param   Int     开始探测时间
     * @param   Int     结束探测时间
     *
     * @return  Array
     */
    public function api_rfid($b_id, $dev, $start, $end)
    {
        if ( !$b_id || !$end ) {
            throw new Exception('Parameter incomplete');
        }

        // 取营业厅信息
        $b_info = business_hall_helper::get_business_hall_info($b_id);

        if ( !$b_info ) {
            throw new Exception('营业厅不存在');
        }

        if ( $dev ) {
            // 获取设备信息
            $dev_info = probe_dev_helper::get_info((String)$dev);

            if ( !$dev_info ) {
                throw new Exception('设备'.$dev.'不存在');
            }

            if ( $dev_info['business_id'] != $b_id ) {
                echo 'bc';
                throw new Exception('设备'.$dev.'不在'.$b_info['title'].'内');
            }
        }

        // 获取数据操作对象
        $db   = get_db($b_id);

        // 获取营业厅规则
        $rule = probe_rule_helper::get_rules($b_id);

        // 日期
        $date  = (Int)date('Ymd', $end);
        $start = $end - (1 * 60);
        $end   = $end + (1 * 60);

        // 查询条件
        $where = " WHERE `date` = {$date} AND `b_id` = {$b_id} AND `up_time` >= {$start} AND `up_time` <= {$end} ";

        if ( $dev ) {
            $where .= " AND `dev` = '{$dev}' ";
        } else {
            $where .= " AND `is_indoor` = 1 ";
        }

        if ( !empty($rule['continued'][1]) ) {
            $where .= " AND `continued` < {$rule['continued'][1]} ";
        }

        $where .= " GROUP BY `mac` ";
        $sql    = "SELECT * FROM `{$db -> table}` {$where}";

        $list = $db -> getAll($sql);

        foreach ($list as $k => $v) {
            $list[$k]['mac'] = probe_helper::mac_encode($v['mac']);

            unset($list[$k]['continued']);
            unset($list[$k]['is_indoor']);
        }

        return $list;
    }

    /**
     * 移动版探针统计首页
     *
     * @param   Array   当前管理员
     * @param   String  访问资源名
     * @param   String  访问资源ID
     * @pram    Int     时间
     *
     * @return  Array
     */
    public function m_index($res_name, $res_id, $date)
    {
        $return = array(
            'stat'  =>  array(
                'indoor'    =>  array(),
                'outdoor'   =>  array(),
                'new_num'   =>  array(),
                'old_num'   =>  array(),
                'remain_time'   =>  array()
            ),
            'indoor'    =>  0,
            'outdoor'   =>  0,
            'new_num'   =>  0,
            'old_num'   =>  0,
            'remain'    =>  0,
            'curr_num'  =>  0,
            'now_week_data'     =>  array(),
            'prev_week_data'    =>  array(),
            'hours'     =>  array(),
            'brands'    =>  array()
        );

        if ( !$res_name || !$date ) {
            return $return;
        }

        $time = strtotime($date);

        if ( !is_numeric($date) ) {
            $date = (Int)date('Ymd', $time);
        }

        $filter = get_filter($res_name, $res_id);

        if ( !$filter ) {
            $filter = array(
                'id >'  =>  0
            );
        }

        $filter['date_for_day'] = $date;

        $return['hours'] = array(
            '00', '01', '02', '03', '04', '05', '06',
            '07', '08', '09', '10', '11', '12', '13',
            '14', '15', '16', '17', '18', '19', '20',
            '21', '22', '23'
        );

        // 在小时统计表中查询数据
        $hour_stat_list  = _model('probe_stat_hour')->getList($filter, ' ORDER BY `id` ASC ');

        foreach ($return['hours'] as $k => $v) {
            $return['stat']['indoor'][$v]     = 0;
            $return['stat']['outdoor'][$v]    = 0;
            $return['stat']['new_num'][$v]    = 0;
            $return['stat']['old_num'][$v]    = 0;
            $return['stat']['remain_time'][$v]= 0;
        }

        foreach ($hour_stat_list as $k => $v) {
            $h = $v['date_for_hour'];

            $return['stat']['indoor'][$h]  += $v['indoor'];
            $return['stat']['outdoor'][$h] += $v['outdoor'];
            $return['stat']['new_num'][$h] += $v['new_num'];
            $return['stat']['old_num'][$h] += $v['old_num'];
            $return['stat']['remain_time'][$h] += $v['remain_time'];
        }

        // 室内人数
        $return['indoor']  = 0;
        // 室外人数
        $return['outdoor'] = 0;
        // 新顾客
        $return['new_num'] = 0;
        // 老顾客
        $return['old_num'] = 0;
        // 停留时长
        $return['remain']  = 0;

        // 本周时间
        $now_week  = probe_helper::get_week_days($time);
        // 上周时间
        $prev_week = probe_helper::get_week_days($time - 7 * 86400);

        // 本周的数据
        $return['now_week_data']  = $this -> m_week_for_hour($res_name, $res_id, $now_week);

        // 上周的数据
        $return['prev_week_data'] = $this -> m_week_for_hour($res_name, $res_id, $prev_week);

        // 注：营业厅时需要有品牌统计
        if ( $res_name == 'business_hall' ) {
            // 获取厅下设备
            $devs = probe_dev_helper::get_devs($res_id);

            if ( !$devs ) {
                throw new Exception('营业厅下没有设备');
            }

            // 品牌统计
            $res    = $this -> brand_stat($res_id, $date);
            $return['brands'] = $res['brand'];

            // 统计今天数据
            $r = $this -> day_stat($res_id, $date);
            // an_dump($r);
            $return['indoor']  = $r['indoor'];
            $return['outdoor'] = $r['oudoor'];
            $return['new_num'] = $r['new_num'];
            $return['old_num'] = $r['old_num'];
            $return['remain']  = $r['remain'];

            // 当前在线人数
            $return['curr_num'] = probe_helper::get_curr_num($res_id);
        } else {

               $return['brands'] = array();
               //此处统计不能sum, 各个时间段包含重复
//             $return['indoor']  = array_sum($return['stat']['indoor']);
//             $return['outdoor'] = array_sum($return['stat']['outdoor']);
//             $return['new_num'] = array_sum($return['stat']['new_num']);
//             $return['old_num'] = array_sum($return['stat']['old_num']);
//             $return['remain']  = array_sum($return['stat']['remain_time']);
            // 在天统计表中查询数据
            $day_stat_list  = _model('probe_stat_day')->getList($filter, ' ORDER BY `id` ASC ');

            foreach ($day_stat_list as $k => $v) {
                $return['indoor']  += $v['indoor'];
                $return['outdoor'] += $v['outdoor'];

//                 $return['new_num'] += $v['new_num'];
//                 $return['old_num'] += $v['old_num'];
                $return['remain']  += $v['remain_time'];
            }
            //暂时置为0  wangjf add
            $return['new_num'] = 0;
            $return['old_num'] = 0;
            // 当前在线人数
            $return['curr_num'] = 0;
        }

        return $return;
    }

    /**
     * 移动版获取一周室内人数
     *
     * @param   String  资源名
     * @param   Int     资源id
     *
     * @return  Array
     */
    public function m_week_for_hour($res_name, $res_id, $dates)
    {
        if ( !$res_name || !$dates ) {
            return array();
        }

        $return = array();

        foreach ($dates as $day) {
            $filter = get_filter($res_name, $res_id);

            $filter['date_for_day'] = $day;

            $list = _model('probe_stat_day')->getList($filter);
            $indoor = 0;

            foreach ($list as $k => $v) {
                $indoor += $v['indoor'];
            }
            $return[$day] = $indoor;
        }

        return $return;
    }

    /**
     * 移动版按天统计
     *
     * @param   String  res name
     * @param   Int     res id
     * @param   Int     日期
     * @param   Int     小时
     * @param   Int     类型
     *
     * @return  Array
     */
    public function m_day_stat($res_name, $res_id, $date, $hour, $type)
    {
        if ( !$res_name || !$date || !$type ) {
            return array();
        }


        if ( !is_numeric($date) ) {
            $date = (Int)date('Ymd', strtotime($date));
        }

        // 取下级
        $list = region_helper::get_subordinate_list($res_name, $res_id);

        // an_dump($list);
        foreach ($list as $k => $v) {
            $filter = array();

            if ( $res_name == 'group' ) {
                $filter['province_id'] = $v['id'];
            } else if ( $res_name == 'province' ) {
                $filter['city_id']     = $v['id'];
            } else if ( $res_name == 'city' ) {
                $filter['area_id']     = $v['id'];
            } else if ( $res_name == 'area' ) {
                $filter['business_id'] = $v['id'];
            }

            $filter['date_for_day']  = $date;
            $table = 'probe_stat_day';

            if ( $hour ) {
                $filter['date_for_hour'] = $hour;
                $table = 'probe_stat_hour';
            }

            $stat_list = _model($table)->getList($filter);

            $num = 0;
            foreach ($stat_list as $val) {
                if ( $type == 1 ) {
                    $num += $val['outdoor'];
                } else if ( $type == 2 ) {
                    $num += $val['indoor'];
                } else if ( $type == 3 ) {
                    $num += $val['new_num'];
                } else if ( $type == 4 ) {
                    $num += $val['old_num'];
                } else {
                    throw new Exception('无法识别的类型');
                }
            }

            if ( $num ) {
                $list[$k]['num'] = $num;
            } else {
                unset($list[$k]);
            }
        }

        return $list;
    }

    /**
     * 移动版mac_list
     *
     * @param   Int 营业厅ID
     * @param   Int 日期
     * @param   Int 小时
     * @param   Int 类型
     *
     * @return  Array
     */
    public function m_mac_list($b_id, $date, $hour, $type)
    {
        if ( !$b_id || !$date || !$type ) {
            return array();
        }

        if ( !is_numeric($date) ) {
            $date = (int)date('Ymd', strtotime($date));
        }

        $rules    = probe_rule_helper::get_rules($b_id);
        $page     = Request::getParam('page', 1);
        $per_page = 20;
        $limit    = $this -> get_limit($page, $per_page);
        $where    = array();
        $where[]  = ' `date` =  '.$date;
        $where[]  = ' `b_id` =  '.$b_id;

        if ( $hour !== '' ) {
            // 按小时查询
            $start   = strtotime($date.$hour.'0000');
            $end     = strtotime($date.$hour.'5959');
            $where[] = " `frist_time` >= {$start} AND `frist_time` <= {$end} ";

            $db = get_db($b_id, 'hour');
        } else {
            $db = get_db($b_id);
        }

        // 过滤掉连续n天，停留时长在m小时的人
        if ( !empty($rules['continued'][1]) ) {
            $where[] = " `continued` < {$rules['continued'][1]} ";
        }

        // 查询室外
        if ( $type == 1 ) {
            // 没有规则
            if ( empty($rules['minute']) ) {
                $where[] = " `is_indoor` = 0 ";
                // 有规则
            } else {
                $sec     = $rules['minute'] * 60;
                $where[] = " (`is_indoor` = 0 OR (`is_indoor` = 1 AND `remain_time` < {$sec})) ";
            }
            // 查询室内，或者新老顾客。注：查询新老顾客时必须查室内的人
        } else if ( $type == 2 || $type == 3 || $type == 4 ) {
            // 没规则
            if ( empty($rules['minute']) ) {
                $where[] = ' `is_indoor` = 1 ';
                // 有规则
            } else {
                $sec     = $rules['minute'] * 60;
                $where[] = " `is_indoor` = 1 AND `remain_time` >= {$sec} ";
            }

            // 查询室内新顾客
            if ( $type == 3 ) {
                $where[] = ' `is_oldcustomer` = 0 ';
                // 查询老顾客
            } else if ( $type == 4 ) {
                $where[] = ' `is_oldcustomer` = 1 ';
            }
        } else {
            throw new Exception('type不正确');
        }

        $where  = ' WHERE '.implode(' AND ', $where)." GROUP BY `mac` ";
        $count  = "SELECT COUNT(id) FROM `{$db -> table}` {$where}";
        $order  = " ORDER BY `id` ASC  ";
        $sql    = "SELECT * FROM `{$db -> table}` {$where} {$order} {$limit} ";

        if ( Request::Get('debug', 0) == 1 ) {
            an_dump($sql, $count);
        }

        $list   = array();
        $count  = count($db->getAll($count));

        if ( $count ) {
            $list = $db->getAll($sql);
        }

        return array('count' => $count, 'list' => $list);
    }

    /**
     * 移动版mac_detail
     *
     * @param   Int     营业厅id
     * @param   String  mac地址
     *
     * @return  Array
     */
    function m_mac_detail($b_id, $mac)
    {

        if ( !$b_id || !$mac ) {
            return '';
        }

        if ( !is_numeric($mac) ) {
            $mac = probe_helper::mac_decode($mac);
        }

        $db  = get_db($b_id);
        $sql = " SELECT * FROM `{$db -> table}` WHERE `mac` = '{$mac}' GROUP BY `date` ";

        return $db -> getAll($sql);
    }
}