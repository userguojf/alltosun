<?php

/**
 * 探针函数库
 *
 * @author  wangl
 */

// load use error
probe_helper::load('probe_error', 'error');

/**
 * 获取操作探针记录表的数据库对象
 *
 * @param   Int 营业厅ID
 * @param   String  类型：day为按天记录表，hour为按小时记录表
 *
 * @return  Bool|Obj
 */
function get_db($b_id, $type = 'day', $create = false)
{
    if ( !$b_id ) {
        return false;
    }

    // 获取营业厅信息
    $b_info = business_hall_helper::get_business_hall_info($b_id);

    if ( !$b_info ) {
        return false;
    }

    $p_id  = $b_info['province_id'];
    $c_id  = $b_info['city_id'];
    $a_id  = $b_info['area_id'];
    $table = 'probe_'.$p_id.'_'.$c_id.'_'.$a_id.'_'.$b_id.'_'.$type;
    if ( $create ) {
        // 注：在创建表时，无法使用当前写法，因为当前表并不存在，所以使用上面的写法
        $db = _model('init', 'awifi_probe');

        $db -> table = $table;
    } else {
        try{
            $db    = _model($table, 'awifi_probe');
        } catch(Exception $e) {
            return false;
        }
    }

    return $db;
}


/**
 * 获取 mongodb 操作对象,
 * @param string $db 操作的mongodb数据库
 * @param string $collection 操作的mongodb集合
 * @param string $b_id 营业厅id
 */
function get_mongodb( $db='', $collection='', $b_id='')
{
    //验证
    if ( !$b_id && !$db ) {
        return false;
    } else if ($db && !$collection) {
        return false;
    }

    //探针组成规则
    if ($b_id) {

        // 获取营业厅信息
        $b_info = business_hall_helper::get_business_hall_info($b_id);

        if ( !$b_info ) {
            return false;
        }

        $p_id  = $b_info['province_id'];
        $c_id  = $b_info['city_id'];
        $a_id  = $b_info['area_id'];
        $collection = 'probe_'.$p_id.'_'.$c_id.'_'.$a_id.'_'.$b_id.'_hour';

        if (empty(probe_config::$citys[$b_info['province_id']])) {
            return false;
        }

        $db = 'probe_'.probe_config::$citys[$b_info['province_id']];
    }

    $mon = (new MongoDB\Client('mongodb://192.168.2.21:27019'))->$db->$collection ;

    return $mon;
}



/**
 * 访问权限验证
 *
 * @param   Array   当前登录管理员
 * @param   String  访问资源名
 * @param   Int     访问资源ID
 * @return  Bool
 */
function visit_auth($member_info, $res_name, $res_id)
{
    if ( !$member_info || !$res_name ) {
        return false;
    }

    // 集团管理员
    if ( $member_info['res_name'] == 'group' ) {
        return true;
    }

    // 访问集团信息
    if ( $res_name == 'group' ) {
        return false;
    }

    // 获取访问资源信息
    $res_info = _model($res_name)->read($res_id);

    if ( !$res_info ) {
        return false;
    }

    // 访问同级资源
    if ( $member_info['res_name'] == $res_name ) {
        return $res_info['id'] == $member_info['res_id'] ? true : false;
    // 访问不同级资源
    } else {
        $key = $member_info['res_name'].'_id';

        if ( empty($res_info[$key]) ) {
            return false;
        } else {
            return $res_info[$key] == $member_info['res_id'] ? true : false;
        }
    }
}

/**
 * 获取查询条件
 *
 * @param   String  权限名
 * @param   Int     权限ID
 * @return  Array
 *
 * @author  wangl
 */
function get_filter($res_name, $res_id)
{
    if ( empty($res_name) ) {
        throw new Exception('res_name is empty');
    }

    $filter = array();

    // 全国
    if ( $res_name == 'group' ) {

    // 省
    } else if ( $res_name == 'province' ) {
        $filter['province_id'] = $res_id;
    // 市
    } else if ( $res_name == 'city' ) {
        $filter['city_id'] = $res_id;
    // 区
    } else if ( $res_name == 'area' ) {
        $filter['area_id'] = $res_id;
    // 营业厅
    } else if ( $res_name == 'business_hall' ) {
        $filter['business_id'] = $res_id;
    // 其他
    } else {
        throw new Exception('res_name value is not defined');
    }

    return $filter;
}

/**
 * 设备是否在线
 *
 * @param   Array   设备信息
 *
 * @return  Bool
 */
function dev_is_online($dev_info)
{
    if ( empty($dev_info['business_id']) || empty($dev_info['device']) ) {
        return false;
    }

    // 初始化数据库操作对象
    $db  = get_db($dev_info['business_id']);

    if (!is_object($db)) {
        return false;
    }

    //wangjf add
    $last_info = $db->read(array('dev' => $dev_info['device']), ' ORDER BY `id` DESC ');

    if ( !$last_info ) {
        return false;
    }

    $time = strtotime($last_info['update_time']);

    // 当前时间减去设备最后更新时间大于10分钟认为不在线
    if ( time() - $time > 600 ) {
        return false;
    }

    return true;
}

/**
 * 获取设备状态
 *
 * @param   Array   设备信息
 *
 * @return  Bool
 */
function get_dev_status($dev_info)
{
    if ( empty($dev_info['business_id']) || empty($dev_info['device']) ) {
        return false;
    }

    // 初始化数据库操作对象
    $db  = get_db($dev_info['business_id']);
    if (!is_object($db)) {
        return false;
    }

    //wangjf add
    $last_info = $db->read(array('dev' => $dev_info['device']), ' ORDER BY `id` DESC ');

    //一直无数据，状态码为 6 设备已激活无数据
    if ( !$last_info ) {
        return 6;
    }

    $time = strtotime($last_info['update_time']);

    // 当前时间减去设备最后更新时间小于10 * 3分钟认为设备正常  状态码为 1
    if ( time() - $time < 600 * 3 ) {
        return 1;
    }

    //当前时间减去设备最后更新时间大于10分钟认为设备故障 状态码为 2
    return 2;
}

/**
 * 设备工厂函数
 *
 * @param   String  设备名
 * @return  Obj
 *
 * @author  wangl
 */
function device($name)
{
    if ( !$name ) {
        throw new Exception('No device name.');
    }

    // 注：设备类应该是 类名=文件名  例如：沃联设备  类名：wolian，文件名：wolian.php

    if ( !class_exists($name) ) {
        $path = MODULE_PATH.'/probe/core/dev/'.$name.'.php';

        if ( !file_exists($path) ) {
            throw new Exception('Device does not exist.');
        }

        require $path;
    }

    // 加速多次调用，如果多次调用的话返回同一个对象
    static $devs = array();

    if ( !isset($devs[$name]) ) {
        $devs[$name] = new $name;
    }

    // load interface device
    probe_helper::load('device', 'interface');

    // 注意：设备类必须实现device接口，用接口来规范每个类必须提供的功能
    if ( !($devs[$name] instanceof device) ) {
        throw new Exception('the '.$name.' not implements device.');
    }

    return $devs[$name];
}