<?php
/**
 * alltosun.com
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * szy: 宋志宇 (songzy@alltosun.com)
 * 2017年12月15日: 2016-7-26 下午3:05:10
 * Id
 */

class screen_device_helper
{



    /**
     *获取 状态
     * @param int $id
     * @param str $table
     */
    public static function get_status($phone_name,$phone_version)
    {
        if (!$phone_name || !$phone_version) {
            return false;
        }
        $nick_info = _model('screen_device_nickname')->read(array('phone_name' => $phone_name , 'phone_version' => $phone_version));

        return $nick_info;
    }

    /**
     * 设备品牌型号昵称是否审核通过
     * @param unknown $phone_name
     * @param unknown $phone_version
     */
    public static function nickname_is_verify( $phone_name, $phone_version )
    {
        if (!$phone_name || !$phone_version) {
            return false;
        }

        $nick_info = _model('screen_device_nickname')->read(array('phone_name' => $phone_name , 'phone_version' => $phone_version));

        if (!$nick_info) {
            return false;
        }

        if ($nick_info['status'] == 1) {
            return true;
        }

        return false;

    }

    /**
     * 获取设备昵称, 有的地方需要昵称id, 所以返回device_nickname_id
     * @param unknown $phone_name
     * @param unknown $phone_version
     * @return string[]|string[]|unknown[]|mixed[]
     */

    public static function get_device_nickname($phone_name, $phone_version)
    {
        $return_data = array(
                'name_nickname'         => '',
                'version_nickname'      => '',
                'device_nickname_id'    => 0
        );

        if (!$phone_name || !$phone_version) {
            return $return_data;
        }

        $filter         = array('phone_name' => $phone_name, 'phone_version' => $phone_version);
        $nick_info      = _model('screen_device_nickname')->read($filter);

        //没有则创建
        if (!$nick_info) {
            $device_nickname_id                 = _model('screen_device_nickname')->create($filter);
            $return_data['device_nickname_id']  = $device_nickname_id;
            return $return_data;
        }
        //昵称审核通过后才生效
        if ($nick_info['status'] == 1) {
            $return_data['name_nickname']       = $nick_info['name_nickname'];
            $return_data['version_nickname']    = $nick_info['version_nickname'];
        }

        $return_data['device_nickname_id']  = $nick_info['id'];

        return $return_data;

    }

    /**
     * 获取最后更新的版本
     * @param unknown $device_unique_id
     */
    public static function get_last_update_version($device_unique_id, $fields='')
    {
        //获取设备唯一id
        $info = _model('screen_update_version_info')->read(array('device_unique_id' => $device_unique_id), 'ORDER BY `id` DESC');

        $return_data = array();

        if (is_array($fields)) {
            foreach ($fields as $v) {
                $return_data[$v] = '';
            }
        } else {
            $return_data = '';
        }

        if (!$info) {
            return $return_data;
        }

        if (is_array($fields)) {
            foreach ($fields as $v) {
                if (isset($info[$v])) {
                    $return_data[$v] = $info[$v];
                }
            }
        } else {
            if (isset($info[$fields])) {
                $return_data = $info[$fields];
            }
        }

        return $return_data;
    }

    /**
     * 根据设备标识获取设备信息
     * @param unknown $device_uniqu_id
     */
    public static function get_device_info_by_device($device_unique_id, $field='', $status = 1)
    {
        if (!$device_unique_id) {
            return array();
        }

        $filter     = array('device_unique_id' => $device_unique_id);

        if ($status !== false) {
            $filter['status']   = $status;
        }

        $device_info = _model('screen_device')->read($filter,' ORDER BY `id` DESC ');

        if (!$field) {
            return $device_info;
        }

        if (isset($device_info[$field])) {
            return $device_info[$field];
        } else {
            return '';
        }
    }

    /**
     * 根据设备标识获取设备信息
     * @param unknown $filter 条件
     * @param string $field 要获取的字段
     * @param number $status 状态 1正常 0-下架 2-自动下架 false-不包含状态字段
     * @return mixed|string
     */
    public static function get_device_info($filter, $field='', $status = 1)
    {
        if (!$filter) {
            return array();
        }

        if ( $status !== false) {
            $filter['status'] = $status;
        }

        $device_info = _model('screen_device')->read($filter,' ORDER BY `id` DESC ');

        if (!$field) {
            return $device_info;
        }

        if (isset($device_info[$field])) {
            return $device_info[$field];
        } else {
            return '';
        }
    }

    public static function get_device_nickname_info($filter = '', $params = '')
    {
        if (!$filter) {
            return false;
        }

        if ($params) {
            return _uri('screen_device_nickname', $filter, $params);
        } else {
            return _uri('screen_device_nickname', $filter);
        }
    }

    /**
     * 根据条件获取设备
     */
    public static function get_device_list_by_filter($filter=array(), $status=1)
    {

        // status == false 无stauts条件， status == 1 正常   status == 0 下架
        if ($status !== false) {
            $filter['status'] = $status;
        }

        return _model('screen_device')->getList(
                $filter,
                ' ORDER BY `id` DESC '
        );
    }

    /**
     * 查询mac 近期出现是否出现
     * @param string $mac
     * @param number $business_id
     * @param number $start_date
     * @param number $end_date
     */
    public static  function check_device_mac($mac = '', $business_id = 0, $start_date = 0, $end_date = 0)
    {
        $default_end_date = date('Ymd');
        $default_start_date = date('Ymd',time() - 7 * 3600 * 24);

        if ( !$mac ) return false;
        if ( !$business_id ) return false;
        if ( !$start_date  )  $start_date =  $default_start_date ;
        if ( !$end_date ) $end_date =  $default_end_date ;

        $filter = array(
            'mac'         => $mac,
            'b_id'        => $business_id,
            'start_date'  => $start_date,
            'end_date'    => $end_date
        );

        return _widget('probe')->check_mac_exist($filter);
    }

    /**
     * 校验设备是否可上报
     * 规则：暂时定为 近5天每天夜间是否连续上报5条数据
     * @param unknown $device_unique_id
     */
    public static function check_device_is_report($device_unique_id)
    {
        //获取前五天的时间
        $start_date = date('Ymd', time() - 5*24*3600);

        do{
            //查询夜间的动作 0-7点的
            $count1 = _mongo('screen', 'screen_action_record_night')->count(get_mongodb_filter(array(
                    'device_unique_id' => $device_unique_id,
                    'day'              => $start_date,
                    'add_time <'         => date('Y-m-d 07:00:00', strtotime($start_date)),
            )));

            //查询夜间的动作 11点的
            $count2 = _mongo('screen', 'screen_action_record_night')->count(get_mongodb_filter(array(
                    'device_unique_id' => $device_unique_id,
                    'day'              => $start_date,
                    'add_time >'         => date('Y-m-d 23:00:00', strtotime($start_date)),
            )));

            //五天内有小于5条的, 则认为可上报
            if (($count1 + $count2) < 50) {
                return true;
            }

            $start_date = date('Ymd', strtotime($start_date) + 24*3600);

        } while ($start_date <= date('Ymd'));

        return false;
        //查询当前时间是否发生过动作

    }

    /**
     * 设备是否是自动下架
     * @param unknown $device_unique_id
     */
    public static function is_auto_dropoff($device_unique_id)
    {
        $record_info = _model('screen_device_offline_record')->read(array('device_unique_id' => $device_unique_id), ' ORDER BY `id` DESC ');

        if ($record_info && $record_info['type'] == 2) {
            return true;
        }

        return false;
    }

    /**
     * 设备下架统一方法
     * @param unknown $device_info
     * @param unknown $type
     * @return boolean
     *
     */
    public static function drop_off($device_info, $type)
    {
        // $type 是screen_device_config::$offline_type
        if ( empty( $device_info ) || !isset( screen_device_config::$offline_type[$type] ) ){
            return false;
        }

        _model('screen_device_offline_record')->create(
            array(
                'province_id' => $device_info['province_id'],
                'city_id'     => $device_info['city_id'],
                'area_id'     => $device_info['area_id'],
                'device_id'   => $device_info['id'],
                'business_id' => $device_info['business_id'],
                'device_unique_id' => $device_info['device_unique_id'],
                'type'             => $type,
                'date'             => date('Ymd')
            )
        );

        return true;
    }
}
?>