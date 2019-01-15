<?php

/**
 * alltosun.com 美斯特设备数据处理类 meist.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王敬飞 (wangjf@alltosun.com) $
 * $Date: 2017年6月16日 下午3:26:05 $
 * $Id$
 */
class meist extends BaseHandle
{
    //数据字段
    public static $fields = array(
        'ID',
        'MAC',
        'RSSI',
        'F-TYPE',
        'MOV',
        'BAT',
        'SN',
        'DATE',
        'X_Data',
        'Y_Data',
        'Z_Data'
    );

    public $string = '';
    public static $serv = NULL;
    public static $fd = NULL;

    /**
     * redis必须设置值
     */
    public function __construct()
    {
        global $redis_cache;
        self::$redis = $redis_cache;
    }

    public function check($string, swoole_server $serv = NULL, $fd = NULL)
    {

        if (!$string) {
            return array('errno' => 1002, 'msg' => 'Data notice: The data is empty');
        }

        $this->string = $string;

        //采集开始或结束 不做处理
        if ($string == 'SCAN OVER' || $string == 'SCAN...') {
            return array('errno' => 1002, 'msg' => 'Data info: Data is not processed');
        }

        $rfid_list = explode("\n\n", $string);

        if (!$rfid_list) {
            return array('errno' => 1002, 'msg' => 'Data notice: Invalid data');
        }

        if (is_object($serv)) {
            //发送应答
            $serv->send($fd, "user_server_get_record\r\n");
        }


        //处理数据
        $new_list = array();
        foreach ($rfid_list as $k => $v) {

            $arr_info = $this->string_to_arr($v);

            if ($arr_info['errno'] != 0) {
                //写入错误
                $arr_info['error_data'] = $v;
                $error_logs[] = $arr_info;
                continue;
            }

            $new_list[] = $arr_info['data'];
        }

        //调用自身处理类
        return $this->handle(array('errno' => 0, 'data' => $new_list, 'error_logs' => $error_logs));

    }

    /**
     * rfid字符串数据转换为数组
     */
    public function string_to_arr($string)
    {
        $string = trim($string);
        //截取
        $arr = explode("\n", $string);

        $new_arr = array();
        foreach ($arr as $k => $v) {

            $v = trim($v);

            list($field, $value) = explode(':', $v);

            $field = trim($field);
            $value = trim($value);

            //不存在此字段
            if (!in_array($field, self::$fields)) {
                return array('errno' => 1002, 'msg' => 'Data notice: Invalid data[1]');
            }

            $new_arr[$field] = $value;
        }

        if (!empty($new_arr['DATE'])) {
            $new_arr['add_timestamp'] = strtotime($new_arr['DATE']);
        } else {
            $new_arr['add_timestamp'] = time();
        }

        $new_arr['request_timestamp'] = $new_arr['add_timestamp'];

        return array('errno' => 0, 'data' => $new_arr);
    }

    /**
     * 数据处理
     * @param unknown array() 效验后的数据
     */
    public function handle($result)
    {

        if ($result['errno'] != 0) {
            return $result;
        }

        //记录错误日志
        if (is_array($result['error_logs'])) {
            foreach ($result['error_logs'] as $k => $v) {
                $v['string'] = $this->string;
                AutoLoad::instance('error_log')->write_error_log($v);
            }
        }

        $data = $result['data'];

        if (!$data) {
            return array('errno' => 1002, 'msg' => 'Data notice: Invalid data[2]');
        }

        foreach ($data as $k => $v) {

            //F-TYPE 01 为心跳包
            if ($v['F-TYPE'] == '02') {
                //拿起
                if ($v['MOV'] === '01') {
                    $result = $this->take_up($v);
                    //放下
                } else if ($v['MOV'] === '00') {
                    $result = $this->lay_down($v);
                } else {
                    continue;
                }

                if ($result != 'success') {
                    $result['string'] = $this->string;
                    AutoLoad::instance('error_log')->write_error_log($result);
                }



            }
        }

        return 'success';

    }

    /**
     * 处理包 拿起
     * @param unknown $data
     */
    public function take_up($data)
    {
        //生成key
        $label_id = $data['ID'];

        $key = $this->generate_key($label_id);

        //存在未完成的
        if (self::$redis->get($key)) {
            //刷新请求时间
            $error_info = $this->refresh_up_request_time($data, $key);

            return $error_info;
        }

        //获取secret信息
        $secret_info = $this->get_phone_secret($label_id);

        //有错误
        if (isset($secret_info['errno']) && $secret_info['errno'] != 0) {
            return $secret_info;
        }

        //兼容旧版本
        if (isset($secret_info['secret'])) {
            list($secret_info['phone_name'], $secret_info['phone_version'], $secret_info['phone_color']) = explode(',', $secret_info['secret']);
        }

        $new_data = array(
            'label_id'        => "'" . $data['ID'] . "'",
            'date'            => date('Ymd', $data['add_timestamp']),
            'mac'             => "'" . $data['MAC'] . "'",
            'start_timestamp' => $data['add_timestamp'],
            'rssi'            => "'" . $data['RSSI'] . "'",
            'province_id'     => $secret_info['province_id'],
            'city_id'         => $secret_info['city_id'],
            'area_id'         => $secret_info['area_id'],
            'business_id'     => $secret_info['business_hall_id'],
            'phone_name'      => "'" . $secret_info['phone_name'] . "'",
            'phone_version'   => "'" . $secret_info['phone_version'] . "'",
            'phone_color'     => "'" . $secret_info['phone_color'] . "'",
            'add_time'        => "'" . date('Y-m-d H:i:s') . "'",
        );

        //创建动作记录
        $record_id = AutoLoad::instance('model')->create('rfid_record_detail', $new_data);

        if (!$record_id) {
            return array('errno' => 1001, 'msg' => 'DB Error: Data creation failed. from[take_up]', 'error_data' => $new_data);
        }

        //动作包关联record_id
        $data['record_id'] = $record_id;

        $redis_result = self::$redis->set($key, array('start' => $data));

        return 'success';
    }

    /**
     * 处理包 放下
     */
    public function lay_down($data)
    {
        //生成key
        $label_id = $data['ID'];

        //设置标签在线状态
        $this->set_label_online($label_id);

        $key = $this->generate_key($label_id);

        //开始时的包
        $start_info = self::$redis->get($key);

        if (!$start_info) {
            return array('errno' => 1002, 'msg' => 'Data info: Data is not processed[1]');
        }

///////////////////////怀疑此处未取到缓存内容//////////////////////////////
        if (!isset($start_info['start']['record_id']) || !$start_info['start']['record_id'] || !isset($start_info['start']['add_timestamp']) || !$start_info['start']['add_timestamp']) {
            //AutoLoad::instance('error_log')->write_error_log(array('errno' => 1001, 'string' => $this->string, 'msg' => 'Redis Error: Cache data is empty', 'error_data' => $start_info));
            return 'Redis Error: Cache data is empty';
        }
//////////////////////////////////////////////////////////////////////

        $start_info = $start_info['start'];
        $status = 1;

        //刷新请求时间
        $result = $this->refresh_down_request_time($data, $key);

        if (is_array($result)) {
            //清除缓存
            self::$redis->delete($this->generate_key($key));
            return $result;
        }

        //超时
        if ($result === false) {
            //替换为上次动作时间
            $data['add_timestamp'] = $start_info['request_timestamp'];
            $status = -1;
        }

        //未达到处理条件
        if ($result == 'success') {
            //超时
            if ($data['request_timestamp'] - $start_info['request_timestamp'] >= CLEAR_INTERVAL) {
                //替换为上次动作时间
                $data['add_timestamp'] = $start_info['request_timestamp'];
                $status = -1;
            }
        }

        //结束此条记录
        return $this->end($data, $start_info, $status);

    }

    /**
     * 结束记录
     * @param unknown $data
     * @param unknown $start_info
     * @return string[][]|number[][]|unknown[][]|unknown[]|string|unknown[][]|string[]
     */
    public function end($data, $start_info, $status = 1)
    {
        //结束此条记录
        $remain_time = $data['add_timestamp'] - $start_info['add_timestamp'];

        //如果状态正常， 则数据没任何替换操作
        if ($status == 1) {
            //体验时长不达标
            if ($remain_time < QUALIFIED_INTERVAL) {
                $status = -2;
            }
        }

        $update_data = array(
            'end_timestamp' => $data['add_timestamp'],
            'rssi'          => "'" . $data['RSSI'] . "'",
            'remain_time'   => $remain_time,
            'status'        => $status
        );

        //开始记录时生成的id
        $record_id = $start_info['record_id'];

        //结束记录
        $update = AutoLoad::instance('model')->update('rfid_record_detail', $record_id, $update_data);

        //结束失败
        if (is_array($update)) {
            $update['error_data'] = $update_data;
            return $update;
        }

        //清除缓存
        self::$redis->delete($this->generate_key($data['ID']));

        //体验时长不达标则执行到此处
        if ($remain_time < QUALIFIED_INTERVAL) {
            return 'success';
        }

        $record_detail = AutoLoad::instance('model')->get_info('rfid_record_detail', $record_id);

        //更新统计
        $result = $this->update_stat($record_detail);

        if (is_array($result)) {
            return $result;
        }

        //更新探针数据
        $this->add_probe_user($record_detail);

        return 'success';
    }


    /**
     * 刷新缓存中的上次请求时间
     * @param unknown $data
     */
    public function refresh_down_request_time($data, $key)
    {

        //是否达到刷新请求时间的时间
        $mod = get_modulo_by_interval($data['request_timestamp'], REFRESH_INTERVAL);
        if ($mod == 0) {

            $cache_data = self::$redis->get($key);
            if (!$cache_data) {
                return array('errno' => 1001, 'msg' => 'Redis Error: Cache data is empty[1]');
            }

            //超时返回false
            if (($data['request_timestamp'] - $cache_data['start']['request_timestamp']) >= CLEAR_INTERVAL) {
                return false;
            }

        }

        return 'success';

    }


    /**
     * 刷新请求时间
     * @param unknown $data
     * @param unknown $key
     */
    public function refresh_up_request_time($data, $key)
    {

        //是否达到刷新请求时间的时间
        $mod = get_modulo_by_interval($data['request_timestamp'], REFRESH_INTERVAL);

        if ($mod == 0) {
            $cache_data = self::$redis->get($key);
            if (!$cache_data) {
                return array('errno' => 1001, 'msg' => 'Redis Error: Cache data is empty');
            }

            //如果超时，不做处理
            if (($data['request_timestamp'] - $cache_data['start']['request_timestamp']) >= CLEAR_INTERVAL) {
                return 'success';
            }

            //刷新时间
            $cache_data['start']['request_timestamp'] = $data['add_timestamp'];

            self::$redis->set($key, $cache_data);

            return 'success';

        }

        return 'success';

    }

}