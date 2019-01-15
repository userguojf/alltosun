<?php

/**
 * alltosun.com 数据处理trait handle.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王敬飞 (wangjf@alltosun.com) $
 * $Date: 2017年10月26日 下午2:57:09 $
 * $Id$
 */
trait handle
{

    public $serv;
    public $fd;
    public static $redis;

    /**
     * 初始化数据
     * @param unknown $string
     * @param swoole_server $serv
     * @param unknown $fd
     */
    public function init($string, swoole_server $serv = NULL, $fd = NULL)
    {
        $this->serv = $serv;
        $this->fd = $fd;
        $this->string = $string;

        global $redis_cache;
        self::$redis = $redis_cache;

        //解析为数组
        return $this->to_array();
    }

    /**
     * 解析为数组数据
     */
    public function to_array()
    {
        $return_arr = array();

        //多个标签数据分割
        $arr = explode("\n\n", trim($this->string));

        foreach ($arr as $k => $v) {

            //单个标签处理
            $arr2 = explode("\n", trim($v));
            $arr3 = array();

            foreach ($arr2 as $k2 => $v2) {

                //字段值处理
                list($key, $value) = explode(':', $v2);
                if (!in_array($key, $this->fields)) {
                    continue;
                }

                $arr3[$key] = $value;
            }

            //校验
            $res = $this->check($arr2);

            if ($res === false) {
                continue;
            }

            if ((empty($arr3['label_id']) || $arr3['label_id'] == '--') && !empty($arr3['mac'])) {
                $arr3['label_id'] = $arr3['mac'];
            }

            // label1799006d 这样的label_id需要处理掉label
            if (!empty($arr3['label_id'])) {
                $arr3['label_id'] = trim(str_replace('label', '', $arr3['label_id']));
            }

            if ((empty($arr3['mac']) || $arr3['mac'] == '--') && !empty($arr3['label_id'])) {
                $arr3['mac'] = $arr3['label_id'];
            }

            $return_arr[] = $arr3;
        }

        return $return_arr;
    }

    /**
     * 拿起
     */
    public function up($arr)
    {
        //生成key
        $label_id = $arr['label_id'];

        $key = $this->generate_action_key($label_id);

        //存在未完成的
        if (self::$redis->get($key)) {
            //刷新请求时间
            return $this->refresh_up_request_time($arr, $key);
        }

        //获取secret信息
        $label_info = $this->get_label_info($label_id);

        //有错误
        if ($label_info === false) {
            return false;
        }

        $new_data = array(
            'label_id'        => "'" . $arr['label_id'] . "'",
            'date'            => date('Ymd', $arr['timestamp']),
            'mac'             => "'" . $arr['mac'] . "'",
            'start_timestamp' => $arr['timestamp'],
            'rssi'            => "'" . $arr['rssi'] . "'",
            'province_id'     => $label_info['province_id'],
            'city_id'         => $label_info['city_id'],
            'area_id'         => $label_info['area_id'],
            'business_id'     => $label_info['business_hall_id'],
            'phone_name'      => "'" . $label_info['phone_name'] . "'",
            'phone_version'   => "'" . $label_info['phone_version'] . "'",
            'phone_color'     => "'" . $label_info['phone_color'] . "'",
            'add_time'        => "'" . date('Y-m-d H:i:s') . "'",
        );

        //创建动作记录
        $record_id = AutoLoad::instance('model')->create('rfid_record_detail', $new_data);

        if (!$record_id) {
            $this->error(array('errno' => 1001, 'msg' => 'take_up error: Data creation failed.'));
            return false;
        }

        //动作包关联record_id
        $arr['record_id'] = $record_id;
        $arr['last_timestamp'] = $arr['timestamp'];

        $redis_result = self::$redis->set($key, $arr);

        return true;
    }

    /**
     * 放下
     */
    public function down($arr)
    {

        //生成key
        $label_id = $arr['label_id'];
        $key = $this->generate_action_key($label_id);

        //设置标签在线
        $res = $this->set_label_online($label_id);

        //开始时的包
        $start_info = self::$redis->get($key);

        if (!$start_info) {
            $this->error(array('errno' => 1002, 'msg' => 'down error: cache data is not processed'));
            return false;
        }

        $status = 1;

        //体验时长
        $dur = $arr['timestamp'] - $start_info['timestamp'];

        //最后一次拿起和第一次放下超过60s，则超时
        if ($arr['timestamp'] - $start_info['last_timestamp'] > CLEAR_INTERVAL) {
            $status = -1;
            //体验不达标，小于3s
        } else if ($dur < QUALIFIED_INTERVAL) {
            $status = -2;
        }

        $update_data = array(
            'end_timestamp' => $arr['timestamp'],
            'rssi'          => "'" . $arr['rssi'] . "'",
            'remain_time'   => $dur,
            'status'        => $status
        );

        $update = AutoLoad::instance('model')->update('rfid_record_detail', $start_info['record_id'], $update_data);

        //结束失败
        if (is_array($update)) {
            $this->error($update);
            return false;
        }

        //清除缓存
        self::$redis->delete($key);

        //体验不达标或异常则不计入统计
        if ($status != 1) {
            return true;
        }

        $record_detail = AutoLoad::instance('model')->get_info('rfid_record_detail', $start_info['record_id']);

        $result = $this->update_stat($record_detail);
        if ($result === false) {
            return false;
        }

        //更新探针数据
        $this->add_probe_user($record_detail);

        return true;
    }


    /**
     * 校验
     */
    public function check($data)
    {
        if (empty($data[0])) {
            return false;
        }

        if (strpos($data[0], 'action_id') === false) {
            return false;
        }

        //线下环境没有serv对象
        if (is_object($this->serv)) {
            echo "response:" . $data[0] . "\r\n";
            //发送应答
            $this->serv->send($this->fd, $data[0] . "\r\n");
            return true;
        }

        return true;
    }

    /**
     * 刷新请求时间
     * @param unknown $data
     * @param unknown $key
     */
    public function refresh_up_request_time($arr, $key)
    {

        //是否达到刷新请求时间的时间
        //$mod = get_modulo_by_interval($arr['timestamp'], REFRESH_INTERVAL);

        //if ($mod == 0) {
        $cache_data = self::$redis->get($key);
        if (!$cache_data) {
            $this->error(array('errno' => 1001, 'errmsg' => 'Redis Error: Cache data is empty'));
            return false;
        }

        //如果超时，不做处理
        if (($arr['timestamp'] - $cache_data['last_timestamp']) >= CLEAR_INTERVAL) {
            return true;
        }

        //刷新时间
        $cache_data['last_timestamp'] = $arr['timestamp'];

        self::$redis->set($key, $cache_data);

        return true;

        //}

        //return true;

    }

    /**
     * 更新统计
     * @param unknown $record_detail
     * @return unknown[]|number[][]|string[][]|string
     */
    public function update_stat($record_detail)
    {
        //更新record
        $update_stat = AutoLoad::instance('stat')->update_record($record_detail);

        if (is_array($update_stat)) {
            $this->error($update_stat);
            return false;
        }

        //更新天数据
        $update_stat = AutoLoad::instance('stat')->update_stat($record_detail);

        if (is_array($update_stat)) {
            $this->error($update_stat);
            return false;
        }

        //更新小时数据
        $update_stat = AutoLoad::instance('stat')->update_stat_hour($record_detail);

        if (is_array($update_stat)) {
            $this->error($update_stat);
            return false;
        }

        return true;
    }

    /**
     * 获取标签和手机绑定的唯一标示 secret
     * @param unknown $label_id
     */
    public function get_label_info($label_id)
    {
        if (!$label_id) {
            $this->error(array('errno' => 1001, 'errmsg' => 'get_label_info error:Label_id is empty.'));
            return false;
        }

        $key = $this->generate_label_info_key($label_id);

        $label_info = self::$redis->get($key);
        if (!empty($label_info)) {

            //设置标签在线
            $res = $this->set_label_online($label_id, $label_info['business_hall_id']);

            return $label_info;
        }

        //重新获取
        $filter = array(
            'label_id' => "'" . $label_id . "'"
        );

        $label_info = AutoLoad::instance('model')->get_info('rfid_label', $filter, ' ORDER BY `update_time` DESC LIMIT 1');

        if (!$label_info) {
            $this->error(array('errno' => 1001, 'msg' => 'get_label_info error:Label unbound device'));
            return false;
        }

        $business_hall = AutoLoad::instance('model')->get_info('business_hall', $label_info['business_hall_id']);

        if (!$business_hall) {
            $this->error(array('errno' => 1001, 'msg' => 'get_label_info error:Business hall does not exist'));
            return false;
            return;
        }

        //更新secret
        $value = array(
            'phone_name'       => $label_info['name'],
            'phone_version'    => $label_info['version'],
            'phone_color'      => $label_info['color'],
            'business_hall_id' => $label_info['business_hall_id'],
            'province_id'      => $business_hall['province_id'],
            'city_id'          => $business_hall['city_id'],
            'area_id'          => $business_hall['area_id'],
            'device'           => $label_info['device']
        );

        self::$redis->set($key, $value, 3600 * 24);

        //设置标签在线
        $res = $this->set_label_online($label_id, $label_info['business_hall_id']);

        return $value;
    }

    /**
     * 添加探针用户
     * @param unknown $detail_id
     */
    public function add_probe_user($record_detail)
    {
        if (!$record_detail) {
            return array('errno' => 1001, 'errmsg' => 'add_probe_user error:record_detail not null.');
        }

        //当前分钟
        $date_minute = date('Y-m-d H:i:00');
        $date_minute_timestamp = strtotime($date_minute);

        //上一分钟
        //$date_minute = date('YmdHi', strtotime($date_minute)-60);
        $date_minute = date('YmdHi', strtotime($date_minute));

        //根据营业厅id生成key
        $key = $record_detail['business_id'];

        //验证， 如果有，则执行到这， 否则，取探针用户
        $value = self::$redis->redis->hget(REQUEST_PROBE_TIME, $key);

        if ($value && $value == $date_minute) {
            return false;
        }

        //获取secret信息，获取secret中的设备信息
        $secret_info = $this->get_label_info($record_detail['label_id']);

        if (isset($secret_info['errno']) && $secret_info['errno'] == 1001) {
            AutoLoad::instance('error_log')->write_error_log($secret_info, '', $label_id);
            return false;
        }

        $data = array(
            'start_time' => $date_minute_timestamp,
            'end_time'   => $date_minute_timestamp,
            'b_id'       => $record_detail['business_id'],
            'dev'        => $secret_info['device']
        );
        //$url = 'http://wifi.pzclub.cn/probe/api/rfid?debug=1&powerby=alltosun';
        $url = RFID_SITE_URL . '/probe/api/rfid';

        $result = curl_post($url, $data);

        $result = json_decode($result, true);

        if (!isset($result['info']) || $result['info'] != 'ok') {
            return array('errno' => 1001, 'msg' => json_encode($result));
        }

        //重置缓存标示
        self::$redis->redis->hset(REQUEST_PROBE_TIME, $key, $date_minute);

        foreach ($result['data'] as $k => $v) {

            $new_data = array(
                'mac'         => "'" . $v['mac'] . "'",
                'business_id' => $record_detail['business_id'],
                'date_minute' => "'" . $date_minute . "'",
                'first_time'  => $v['frist_time'],
                'up_time'     => $v['up_time'],
                'dev'         => "'" . $v['dev'] . "'",
                'first_rssi'  => $v['frist_rssi'],
                'up_rssi'     => $v['up_rssi'],
                'add_time'    => "'" . date('Y-m-d H:i:s') . "'"
            );

            AutoLoad::instance('model')->create('rfid_probe_user_record', $new_data);
        }

    }

    /**
     * 根据标签id生成动作数据key
     * @param unknown $label_id
     */
    public function generate_action_key($label_id)
    {
        return KEY_SECRET . $label_id;
    }

    /**
     * 根据标签id生成label数据key
     * @param unknown $label_id
     */
    public function generate_label_info_key($label_id)
    {
        return PHONE_SECRET . $label_id;
    }

    public function error($arr)
    {
        $arr['string'] = $this->string;
        AutoLoad::instance('error_log')->write_error_log($arr);
    }

    /**
     * 设置标签状态
     *
     */
    public function set_label_online($label_id, $business_id = 0)
    {


        if (!$business_id) {

            //获取secret信息
            $label_info = $this->get_label_info($label_id);

            //有错误
            if (!isset($label_info['business_hall_id'])) {
                return false;
            }

            $business_id = $label_info['business_hall_id'];

        }

        //set操作
        $date = date('Ymd');
        $key = 'onlineStatus|' . $date . '|' . $business_id;

        $res = self::$redis->redis->sadd($key, $label_id);

        //写入失败，则代表今日不是第一次上报数据
        if (!$res) {
            return true;
        }

        //查询营业厅表
        $business_hall_info = AutoLoad::instance('model')->get_info('business_hall', $business_id);

        if (!$business_hall_info) {
            return false;
        }

        //查询今日是否已经设置在线
        $online_info = AutoLoad::instance('model')->get_info('rfid_online_stat_day', array('label_id' => "'" . $label_id . "'", 'day' => "'" . $date . "'"));

        //今日已存在
        if ($online_info) {
            return true;
        }

        //记录今日在线表
        $new_data = array(
            'province_id' => $business_hall_info['province_id'],
            'city_id'     => $business_hall_info['city_id'],
            'area_id'     => $business_hall_info['area_id'],
            'business_id' => $business_hall_info['id'],
            'label_id'    => "'" . $label_id . "'",
            'day'         => "'" . $date . "'",
            'add_time'    => "'" . date('Y-m-d H:i:s') . "'"
        );

        AutoLoad::instance('model')->create('rfid_online_stat_day', $new_data);

        //查询读写器统计
        $stat_info = AutoLoad::instance('model')->get_info('rfid_rwtool_stat_day', array('business_id' => $business_id, 'day' => "'" . $date . "'"));

        $online_label_num = self::$redis->redis->sSize($key);

        $status = 2;

        if ($stat_info) {
            //读写器正常(标签正常)
            if ($online_label_num >= $stat_info['label_num']) {
                $status = 1;
            }
            AutoLoad::instance('model')->update('rfid_rwtool_stat_day', $stat_info['id'], array('online_label_num' => $online_label_num, 'status' => $status));
        } else {
            $new_data['online_label_num'] = $online_label_num;
            //添加读写器统计
            unset($new_data['label_id']);

            //查询读写器
            $rwtool_info = AutoLoad::instance('model')->get_info('rfid_rwtool', array('business_id' => $business_id));

            if (!$rwtool_info) {
                return false;
            }

            //读写器正常(标签正常)
            if ($online_label_num >= $rwtool_info['label_num']) {
                $status = 1;
            }

            $new_data['label_num'] = $rwtool_info['label_num'];
            $new_data['status'] = $status;

            AutoLoad::instance('model')->create('rfid_rwtool_stat_day', $new_data);
        }
        return true;
    }
}