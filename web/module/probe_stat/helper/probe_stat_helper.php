<?php
/**
 * alltosun.com  probe_stat_helper.php
 * ============================================================================
 * 版权所有 (C) 2009-2015 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明: 这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王磊 (wangl@alltosun.com) $
 * $Date: 2016-12-8 上午11:01:07 $
*/
class probe_stat_helper
{
    /**
     * 加载对象
     *
     * @param   String  目录名
     * @param   String  文件名
     */
    public static function load($dir, $name)
    {
        if ( !$dir || !$name ) {
            return NULL;
        }

        require_once MODULE_PATH.'/probe_stat/core/'.$dir.'/'.$name.'.php';
    }

    /**
     * 获取对象实例
     *
     * @param   String  目录名
     * @param   String  文件名
     */
    public static function instance($dir, $name)
    {
        self::load($dir, $name);

        if ( class_exists($name) ) {
            return $name::instance();
        }

        return NULL;
    }

    public static function getmac($mac, $explode = ':')
    {
        if (!$mac) {
            return 0;
        }

        $mac     = dechex($mac);
        $mac_str = '';

        foreach (str_split($mac) as $k => $v) {
            if ($k && $k % 2 == 0) {
                $mac_str .= $explode;
            }
            $mac_str .= $v;
        }

        return $mac_str;
    }

    public static function mac_decode($mac)
    {
        if ( !$mac ) {
            return '';
        }

        $mac = str_replace(':', '', $mac);

        return (string)hexdec($mac);
    }

    /**
     * @example 得到两个时间戳之间的差值.单位：秒
     * @param   number  $start
     * @param   number  $end
     * @return  number
     */
    static public function getdiff($start, $end)
    {
        $start = (int)($start);
        $end   = (int)($end);

        $ary = array(
            'hour'  =>  0,
            'min'   =>  0,
            'sec'   =>  0,
            'diff'  =>  0
        );
        if ($start >= $end) {
            return $ary;
        }

        // 时间差
        $diff = $end - $start;
        $min  = (int)($diff / 60);

        $ary['diff']  = $diff;

        // 不到1分钟
        if (!$min) {
            $ary['sec'] = $diff;
            return $ary;
        } // else 大于1分钟

        $ary['sec'] = $diff - ($min * 60);

        $hour = (int)($min / 60);

        if (!$hour) {
            $ary['min'] = $min;
            return $ary;
        }

        $ary['min']  = $min - ($hour * 60);
        $ary['hour'] = $hour;

        return $ary;
    }

    static public function getdates($start_time, $end_time, $format)
    {
        if (!$start_time || !$end_time || $start_time > $end_time) {
            return array();
        }

        $dates = array();
        $start = strtotime($start_time);
        $end   = strtotime($end_time);

        while (true) {
            $dates[] = date($format, $start);
            $start  += 24 * 3600;

            if ($start > $end) {
                break;
            }
        }

    	return $dates;
    }

    /**
     * 获取设备列表
     *
     * @param   $filter
     * @param   string $fields
     * @param   string $order
     * @return  multitype:
     */
    public static function get_device_list($filter, $fields = '', $order = '')
    {
        if ( !$filter ) {
            return array();
        }

        if ( $fields ) {
            return _model('probe_device')->getFields($fields, $filter, $order);
        }
        return _model('probe_device')->getList($filter, $order);
    }

    public static function get_devices_by_business( $b_id )
    {
        if ( !$b_id ) {
            return array();
        }
        return _model('probe_device')->getFields('device', array('business_id'=>$b_id));
    }

    public static function get_obj( $name )
    {
        if ( !$name ) {
            return false;
        }
        if ( !class_exists($name) ) {
            require MODULE_PATH.'/probe_stat/core/'.$name.'.php';
        }
        return $name::get_obj();
    }

    public static function date_YW( $date )
    {
        if ( !$date ) {
            return 0;
        }

        $time = $date; // strtotime($date);
        $one  = 60 * 60 * 24;

        $y = date('Y', $time);
        $w = date('W', $time);
// an_dump($y, $w);
        if ( $w > 50 ) {
            if ( (int)date('m', $time) == 1 ) {
                $y = date('Y', $time - 8 * $one);
            }
        } else if ( $w < 2 ) {
            if ( (int)date('m', $time) == 12 ) {
                $y = date('Y', $time + 8 * $one);
            }
        }

        return "$y{$w}";
    }

    /**
     * 获取有探针设备的营业厅id
     *
     * @param   获取人权限名
     * @param   获取人权限id
     * @return  营业厅id
     */
    public static function get_b_ids( $res_name, $res_id )
    {
        // an_dump($res_name, $res_id);
        if ( $res_name == 'group' ) {
             $filter = array('status'=>1);
        } else if ( $res_name == 'province' ) {
            $filter = array('province_id'=>$res_id, 'status'=>1);
        } else if ( $res_name == 'city' ) {
            $filter = array('city_id'=>$res_id, 'status'=>1);
        } else if ( $res_name == 'area' ) {
            $filter = array('area_id'=>$res_id, 'status'=>1);
        } else if ( $res_name == 'business_hall' ) {
            $filter = array('business_id'=>$res_id, 'status'=>1);
        } else {
            return array();
        }

        $b_ids = _model('probe_device')->getFields('business_id', $filter);

        return $b_ids ? array_unique($b_ids) : array();
    }

    /**
     * 获取某周的时间
     *
     * @param   时间
     * @return  array
     */
    public static function weekday( $date )
    {
        if ( !$date ) {
            return array();
        }

        $year = substr($date, 0, 4);
        $week = substr($date, 4);

        $start = mktime(0, 0, 0, 1, 1, $year);
        // 取某年1月1号是周几
        $n     = date('w', $start);

        if ( $n == 1 ) {
            $n = 0;
        } else {
            if ( $n == 0 ) {
                $n = 7;
            }
            $n = 8 - $n;
        }
        $start = strtotime("+{$n} day", $start);
        $n     = ($week - 1) * 7;
        $start = strtotime("+{$n} day", $start);

        $dates = array();
        for ( $i = 0; $i < 7; $i ++ ) {
            $dates[$i] = date('Ymd', $start + ($i * 60 * 60 * 24));
        }

        return $dates;
    }

    /**
     * 通过mac获取手机型号
     *
     * @param   string  mac地址
     */
    public static function get_brand($mac)
    {
        if ( is_numeric($mac) ) {
            $mac = self::getmac($mac);
        }

        // $mac = strtoupper($mac);

        $str  = str_replace(':', '', substr($mac, 0, 8));

        $info = _model('t_oui')->read(array('id' => $str));

        if ( !$info ) {
            return '其他';
        }

        $ary = probe_config::$brands;

        foreach ( $ary as $k => $v ) {
            if ( stripos($info['company'], $v) !== false ) {
                return $k;
            }
        }

        return '其他';
    }

    /**
     * 判断是否为老顾客
     *
     * @param   String  mac地址
     * @param   Int     营业厅ID
     * @param   Int     日期
     * @return  Bool
     */
    public static function is_oldcustomer($mac, $b_id, $date = 0)
    {
        if ( !$mac || !$b_id ) {
            return false;
        }

        $probe = self::instance('base', 'probe');

        if ( !$probe ) {
            return false;
        }

        $db = $probe -> get_db(array('type' => 'day', 'b_id' => $b_id));

        if ( !$db ) {
            return false;
        }

        if ( empty($date) ) {
            $date = (int)date('Ymd');
        }

        $filter = array(
            'mac'       =>  $mac,
            'date <'    =>  $date
        );

        $info = $db -> read($filter);

        if ( $info ) {
            return true;
        }

        return false;
    }

    /**
     * 身份认证，判断是否能访问某个统计页面
     *
     *
     * @return  Bool
     */
    public static function auth($param = array())
    {
        if ( !isset($param['member']) || !$param['member'] ) {
            return false;
        }

        if ( !isset($param['res_name']) || !$param['res_name'] ) {
            return false;
        }

        $member   = $param['member'];
        $res_name = $param['res_name'];
        $res_id   = $param['res_id'];

        // 全国管理员可以访问所有
        if ( $member['res_name'] == 'group' ) {
            return true;
        }

        // 判断访问资源是否存在
        if ( !in_array($res_name, array('group', 'province', 'city', 'area', 'business_hall')) ) {
            return false;
        }

        // 除了全国管理员，没人可以访问全国资源
        if ( $res_name == 'group' ) {
            return false;
        }

        $res_info = get_resource_info($res_name, $res_id);

        if ( !$res_info ) {
            return false;
        }

        // 访问某省
        if ( $res_name == 'province' ) {
            // 身份为省级管理员时
            if ( $member['res_name'] == 'province' && $member['res_id'] == $res_info['id'] ) {
                return true;
            }
            return false;

            // 访问某个市
        } else if ( $res_name == 'city' ) {
            // 身份为省级管理员时
            if ( $member['res_name'] == 'province' && $member['res_id'] == $res_info['province_id'] ) {
                return true;
                // 身份为市级管理员时
            } else if ( $member['res_name'] == 'city' && $member['res_id'] == $res_info['id'] ) {
                return true;
            }
            return false;

            // 访问某个区
        } else if ( $res_name == 'area' ) {
            // 身份为省级管理员时
            if ( $member['res_name'] == 'province' && $member['res_id'] == $res_info['province_id'] ) {
                return true;
                // 身份为市级管理员时
            } else if ( $member['res_name'] == 'city' && $member['res_id'] == $res_info['city_id'] ) {
                return true;
                // 身份为区级管理员时
            } else if ( $member['res_name'] == 'area' && $member['res_id'] == $res_id['id'] ) {
                return true;
            }
            return false;

        } else if ( $res_name == 'business_hall' ) {
            // 身份为省级管理员时
            if ( $member['res_name'] == 'province' && $member['res_id'] == $res_info['province_id'] ) {
                return true;
                // 身份为市级管理员时
            } else if ( $member['res_name'] == 'city' && $member['res_id'] == $res_info['city_id'] ) {
                return true;
                // 身份为区级管理员时
            } else if ( $member['res_name'] == 'area' && $member['res_id'] == $res_info['area_id'] ) {
                return true;
                // 身份为营业厅管理员时
            } else if ( $member['res_name'] == 'business_hall' && $member['res_id'] == $res_info['id'] ) {
                return true;
            }
            return false;
        }

        return false;
    }

    /**
     * 获取某个mac地址进入某个营业厅次数
     *
     * @param   String  mac地址
     * @param   Int     营业厅ID
     * @return  Int     返回进店次数
     */
    public static function into_num($mac, $b_id)
    {
        if ( !$b_id || !$mac ) {
            return 0;
        }

        if ( !is_numeric($mac) ) {
            $mac = self::mac_decode($mac);
        }

        $db = self::instance('base', 'probe') -> get_db(array('type' => 'day', 'b_id' => $b_id));

        if ( !$db ) {
            return 0;
        }

        $sql   = " SELECT COUNT(*) FROM `{$db -> table}` WHERE `mac` = '{$mac}' GROUP BY `date` ";

        $count = $db -> getAll($sql);

        return count($count);
    }

    /**
     * 获取某个mac进店列表
     *
     * @param   String  mac地址
     * @param   Int     营业厅ID
     * @return  Array
     */
    public static function into_list($mac, $b_id)
    {
        if ( !$mac || !$b_id ) {
            return array();
        }

        if ( !is_numeric($mac) ) {
            $mac = self::mac_decode($mac);
        }

        $db = self::instance('base', 'probe') -> get_db(array('type' => 'day', 'b_id' => $b_id));

        if ( !$db ) {
            return 0;
        }

        $sql   = " SELECT * FROM `{$db -> table}` WHERE `mac` = '{$mac}' GROUP BY `date` ";

        return $db -> getAll($sql);
    }

    /**
     * 换算停留时长
     *
     * @param   Int     停留时长
     * @return  Array
     */
    public static function get_remain($time)
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
     * 查询设备信息
     *
     * @param   Array   查询条件
     * @return  Array   返回查询到的信息
     */
    public static function get_dev_info($filter)
    {
        if ( !$filter ) {
            return array();
        }

        return _model('probe_device')->read($filter);
    }

    /**
     * 查询营业厅设备表
     *
     * @param   Array   查询条件
     * @param   String  order, group, limit
     * @return  Array
     */
    public static function get_dev_list($filter, $order = '')
    {
        if ( !$filter ) {
            return array();
        }

        return _model('probe_device')->getList($filter, $order);
    }

    /**
     * 获取营业厅下设备
     *
     * @param   Int     营业厅ID
     * @return  Array
     */
    public static function get_devs($b_id)
    {
        if ( !$b_id ) {
            return array();
        }

        $list = self::get_dev_list(array('business_id' => $b_id, 'status' => 1));

        $data = array();

        foreach ($list as $k => $v) {
            $dev = $v['device'];

            $data[$dev] = $v['rssi'];
        }

        return $data;
    }

    /**
     * 获取一个时间戳是一年中的第几周
     *
     * @param   Int 时间戳
     * @return  Int 格式：201705，表示2017年第5周
     */
    public static function get_week($time)
    {
        if ( !$time ) {
            return 0;
        }

        // 一天的时间
        $one  = 3600 * 24;

        $y = date('Y', $time);
        $w = date('W', $time);

        if ( $w > 50 ) {
            if ( (int)date('m', $time) == 1 ) {
                $y = date('Y', $time - 8 * $one);
            }
        } else if ( $w < 2 ) {
            if ( (int)date('m', $time) == 12 ) {
                $y = date('Y', $time + 8 * $one);
            }
        }

        return "$y{$w}";
    }

    /**
     * 获取某个mac地址，某天在某个营业厅的停留时长和首次探测时间和最后探测时间
     *
     * @param   String  mac地址
     * @param   Int     营业厅ID
     * @param   Int     时间
     * @param   String  设备编号
     * @return  Array
     */
    public static function get_mac_remain($mac, $b_id, $date, $dev = '')
    {
        if ( !$mac || !$date || !$b_id ) {
            return array();
        }

        // 获取数据库操作对象
        $db = probe_helper::instance('base', 'probe') -> get_db(array('type' => 'day', 'b_id' => $b_id));

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
     * 获取我可以查看的营业id
     *
     * @param   String  权限名
     * @param   Int     权限ID
     */
    public static function allow_b_ids($res_name, $res_id)
    {
        if ( $res_name == 'group' ) {
            $filter = array(
                'status'    =>  1
            );
        } else if ( $res_name == 'province' ) {
            $filter = array(
                'province_id'   =>  $res_id,
                'status'        =>  1
            );
        } else if ( $res_name == 'city' ) {
            $filter = array(
                'city_id'   =>  $res_id,
                'status'    =>  1
            );
        } else if ( $res_name == 'area' ) {
            $filter = array(
                'area_id'   =>  $res_id,
                'status'    =>  1
            );
        } else if ( $res_name == 'business_hall' ) {
            $filter = array(
                'business_id'   =>  $res_id,
                'status'        =>  1
            );
        } else {
            return array();
        }

        return _model('probe_device')->getFields('business_id', $filter, ' GROUP BY `business_id` ');
    }

    /**
     * 获取设备型号数组
     *
     * @return  Array
     */
    public static function get_brands()
    {
        $brands = probe_config::$brands;

        foreach ($brands as $k => $v) {
            $brands[$k] = 0;
        }
        $brands['其他'] = 0;

        return $brands;
    }

    /**
     * rfid获取某个营业厅下的探针设备
     *
     * @param   Int 营业厅ID
     * @return  Array
     */
    public static function rfid_get_devs($b_id)
    {
        if ( !$b_id ) {
            return array();
        }

        // 最终返回的数据
        $data = array();
        // 拿营业厅下设备列表
        $list = self::get_dev_list(array('business_id' => $b_id, 'status' => 1));

        foreach ($list as $k => $v) {
            $data[] = $v['device'];
        }

        return $data;
    }

    /**
     * 获取当前在店人数
     *
     * @param   Int 营业厅ID
     * @return  Int
     */
    public static function get_curr_num($b_id)
    {
        if ( !$b_id ) {
            return 0;
        }

        $time     = time();
        $int_date = date('Ymd', $time);
        $start    = $time - (10 * 60);

        $probe    = self::instance('base', 'probe');
        $db       = $probe -> get_db(array('type' => 'day', 'b_id' => $b_id));

        if ( !$b_id ) {
            return 0;
        }

        $sql   = " SELECT COUNT(`id`) FROM `{$db -> table}` WHERE `date` = {$int_date} AND `b_id` = {$b_id} AND `up_time` >= {$start} AND `is_indoor` = 1 GROUP BY `mac` ";

        $count = $db -> getAll($sql);

        return count($count);
    }

    /**
     * 探针记录log
     *
     * @param   String  资源名
     * @param   String  内容
     * @return  Int
     */
    public static function write_log($res_name, $content)
    {
        if ( !$content ) {
            return 0;
        }

        $create = array(
            'res_name'  =>  $res_name,
            'date'      =>  (int)date('Ymd'),
            'content'   =>  $content
        );

        $id = _model('probe_log')->create($create);

        if ( !$id ) {
            return 0;
        }

        return $id;
    }
}
?>
