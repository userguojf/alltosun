<?php
/**
 * alltosun.com  probe_helper.php
 * ============================================================================
 * 版权所有 (C) 2009-2015 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明: 这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王磊 (wangl@alltosun.com) $
 * $Date: 2016-12-8 上午11:01:07 $
*/
class probe_helper
{
    /**
     * 编码mac地址
     *
     * 注：数据库中的mac地址是10进制的，需要该函数编码成16进制的
     *
     * @param   String  Mac地址
     * @param   String  Mac地址分隔符
     *
     * @return  String
     */
    static public function mac_encode($mac)
    {
        //并转换为二进制
        $ary = base_convert($mac, 10, 2);

        $ary_len = strlen($ary);

        //将二进制补全48位
        if ($ary_len < 48) {
            $j = 48 - $ary_len;
            for ($i=0; $i < $j; $i++) {
                $ary = '0'.$ary;
            }
        }

        //每八位转换为16进制
        $mac = '';
        for ($i=0; $i < 6; $i++) {
            $str = substr($ary, $i*8, 8);

            //转换16进制
            $hex = base_convert($str, 2, 16);

            //不够2位并用0补全
            $hex = strlen($hex) < 2 ? '0'.$hex : $hex;
            $mac .=  $hex.":";
        }

        return rtrim($mac, ':');
    }

    /**
     * 解码Mac地址
     *
     * 注：数据库中的mac地址是10进制的，需要该函数将16进制的mac地址解码成10进制
     *
     * @param   String  mac地址
     *
     * @return  String
     */
    static public function mac_decode($mac)
    {
        if ( !$mac ) {
            return '';
        }

        $mac = str_replace(':', '', $mac);

        return (string)hexdec($mac);
    }

    /**
     * 获取校正后的周时间
     *
     * @param   Int 时间戳
     *
     * @return  String  返回格式date('YW');
     */
    static public function revise_week($time)
    {
        if ( !$time ) {
            return '';
        }

        $date = date('Y-m-W', $time);

        if ( sscanf($date, "%d-%d-%d", $year, $mon, $week) != 3 ) {
            return '';
        }

        // 如果是1月份，并且周大于50的话，那么为上一年的50几周
        if ( $mon == 1 && $week > 50 ) {
            $year --;
        // 如果是12月份，并且周小于2，那么为下一年的第1周
        } else if ( $mon == 12 && $week < 2 ) {
            $year ++;
        }

        if ( $week < 10 ) $week = '0'.$week;

        return "{$year}{$week}";
    }

    /**
     * 根据时间戳，获取一周的时间
     *
     * @param   Int 时间戳
     *
     * @return  Array
     */
    static public function get_week_days($time)
    {
        if ( !$time ) {
            return array();
        }

        // 如果是6位，为年周格式时间
        if ( strlen($time) == 6 ) {
            $year = substr($time, 0, 4);
            // 周
            $week = substr($time, 4);
            // 开始时间
            $time = strtotime($year.'W'.$week);
        } else {
            $w = date('w', $time);

            if ( $w == 0 ) {
                $w = 7;
            }

            $w --;

            $time = strtotime("-{$w} day", $time);
        }

        $dates = array();

        for ( $i = 0; $i < 7; $i ++ ) {
            $dates[$i] = date('Ymd', $time + ($i * 86400));
        }

        return $dates;
    }

    /**
     * 通过mac获取手机型号
     *
     * @param   string  mac地址
     *
     * @return  String  品牌型号
     */
    static public function get_brand($mac)
    {
        if ( !$mac ) {
            return '其他';
        }

        // 将10进制转成16进制mac
        if ( is_numeric($mac) ) {
            $mac = self::mac_encode($mac);
        }

        // 截取前8位
        $str  = str_replace(':', '', substr($mac, 0, 8));

        // 查询品牌
        $info = _model('t_oui')->read(array('id' => $str));

        if ( $info ) {
            // 品牌配置信息
            $ary = probe_config::$brands;

            foreach ( $ary as $k => $v ) {
                // 品牌匹配
                if ( stripos($info['company'], $v) !== false ) {
                    return $k;
                }
            }
        }

        return '其他';
    }

    /**
     * 获取某个mac地址进入某个营业厅次数
     *
     * @param   String  mac地址
     * @param   Int     营业厅ID
     *
     * @return  Int     返回进店次数
     */
    static public function into_num($mac, $b_id)
    {
        if ( !$b_id || !$mac ) {
            return 0;
        }

        if ( !is_numeric($mac) ) {
            $mac = self::mac_decode($mac);
        }

        // 加载get_db函数所在文件
        self::load('func');
        // 数据操作对象
        $db    = get_db($b_id);
        // 查询语句
        $sql   = " SELECT COUNT(*) FROM `{$db -> table}` WHERE `mac` = '{$mac}' GROUP BY `date` ";
        // 执行查询
        $count = $db -> getAll($sql);

        return count($count);
    }

    /**
     * 换算停留时长
     *
     * @param   Int     停留时长
     *
     * @return  Array
     */
    static public function get_remain($time)
    {
        $ary = array(
            'hour'  =>  0,      // 停留多少小时
            'min'   =>  0,      // 多少分钟
            'sec'   =>  $time   // 多少秒
        );

        // 小于60秒自己返回
        if ( $ary['sec'] < 60 ) {
            return $ary;
        }

        // 计算停留多少分钟
        $ary['min'] = (int)($ary['sec'] / 60);
        // 重新计算停留秒数
        $ary['sec'] = $time - $ary['min'] * 60;

        if ( $ary['min'] < 60 ) {
            return $ary;
        }

        // 计算停留多少小时
        $ary['hour'] = (int)($ary['min'] / 60);
        // 重新计算停留分钟数
        $ary['min']  = $ary['min'] - ($ary['hour'] * 60);

        return $ary;
    }

    /**
     * 获取某个mac地址，某天在某个营业厅的停留时长和首次探测时间和最后探测时间
     *
     * @param   String  mac地址
     * @param   Int     营业厅ID
     * @param   Int     时间
     * @param   String  设备编号
     *
     * @return  Array
     */
    static public function get_mac_remain($mac, $b_id, $date, $dev = '')
    {
        if ( !$mac || !$date || !$b_id ) {
            return array();
        }

        // 加载get_db函数所在的文件
        self::load('func');

        // 获取数据库操作对象
        $db = get_db($b_id);

        if ( !$db ) {
            return array();
        }

        $filter = array(
            'mac'   =>  $mac,
            'date'  =>  $date,
            'b_id'  =>  $b_id
        );

        if ( $dev ) {
            $filter['dev'] = $dev;
        }

        $info = $db -> read($filter, ' GROUP BY `mac` ');

        return array(
            'frist_time'    =>  $info['frist_time'],
            'up_time'       =>  $info['up_time'],
            'remain'        =>  $info['remain_time']
        );
    }

    /**
     * rfid获取某个营业厅下的探针设备
     *
     * @param   Int 营业厅ID
     *
     * @return  Array
     */
    static public function rfid_get_devs($b_id)
    {
        if ( !$b_id ) {
            return array();
        }

        // 最终返回的数据
        $data = array();
        // 拿营业厅下设备列表
        $list = probe_dev_helper::get_devs($b_id);

        foreach ($list as $k => $v) {
            $data[] = $v['device'];
        }

        return $data;
    }

    /**
     * 获取当前在店人数
     *
     * @param   Int 营业厅ID
     *
     * @return  Int
     */
    static public function get_curr_num($b_id)
    {
        if ( !$b_id ) {
            return 0;
        }

        // 获取操作数据库对象
        $db = get_db($b_id);

        $time     = time();
        $int_date = date('Ymd', $time);
        $start    = $time - 300;  //当前在店人数改为5分钟内

        // 获取规则
        $rule   = probe_rule_helper::get_rules($b_id);

        $rule_where = '';

        //按照规则
        if ( !empty($rule['continued'][1]) ) {
            $rule_where = " AND `continued` < {$rule['continued'][1]} ";
        }

        if ( !empty($rule['minute']) ) {
            $rule['minute'] = $rule['minute'] * 60;
            $rule_where .= " AND `remain_time` >= {$rule['minute']} ";
        }

        // 查询sql
        $sql   = " SELECT `mac` FROM `{$db -> table}` WHERE `date` = {$int_date} AND `b_id` = {$b_id} AND `up_time` >= {$start} AND `is_indoor` = 1 {$rule_where} GROUP BY `mac` ";

        // 执行查询
        $macs = $db -> getAll($sql);

        return count($macs);
    }

    /**
     * 探针记录log
     *
     * @param   String  资源名
     * @param   String  内容
     *
     * @return  Bool
     */
    static public function write_log($res_name, $content)
    {
        if ( !$content ) {
            return false;
        }

        // 暂时忽略debug信息
        if ( $res_name == 'debug' ) {
            return true;
        }

        $create = array(
            'res_name'  =>  $res_name,
            'date'      =>  (int)date('Ymd'),
            'content'   =>  $content
        );

        _model('probe_log')->create($create);

        return true;
    }

    /**
     * 探针记录版本号log
     *
     * @param   String  资源名
     * @param   String  内容
     *
     * @return  Bool
     */
    static public function write_update_version_log($request, $response, $version, $cfg_version)
    {
        if ( !$request) {
            return false;
        }

        $mac = '';
        if (isset($request['DevMAC'])) {
            $mac = $request['DevMAC'];
        }

        $create = array(
                'request'           =>  json_encode($request),
                'response'          =>  json_encode($response),
                'update_version'    =>  $version,
                'update_cfg_version' => $cfg_version,
                'mac'               => $mac
        );

        _model('probe_update_log')->create($create);

        return true;
    }

    /**
     * 加载文件
     *
     * @param   String  文件名
     * @param   String  目录名
     *
     * @return  Void
     */
    static public function load($name, $res_name = 'func')
    {
        if ( $res_name == 'trait' ) {
            if ( !trait_exists($name) ) {
                require MODULE_PATH.'/probe/core/'.$res_name.'/'.$name.'.php';
            }
        } else if ( $res_name == 'interface' ) {
            if ( !interface_exists($name) ) {
                require MODULE_PATH.'/probe/core/'.$res_name.'/'.$name.'.php';
            }
        } else if ( $res_name == 'func' ) {
            if ( !function_exists('get_db') ) {
                require MODULE_PATH.'/probe/core/'.$res_name.'/'.$name.'.php';
            }
        } else {
            require MODULE_PATH.'/probe/core/'.$res_name.'/'.$name.'.php';
        }
    }
}