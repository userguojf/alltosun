<?php

/**
 * alltosun.com 数据基础处理类 BaseHandle.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王敬飞 (wangjf@alltosun.com) $
 * $Date: 2017年7月30日 下午3:44:42 $
 * $Id$
 */
abstract class BaseHandle
{
    public static $redis;
    public static $serv;


    /**
     * 效验
     */
    abstract public function check($string, swoole_server $serv = NULL, $fd = NULL);


    /**
     * 处理数据
     * @param Array $result
     */
    abstract public function handle($result);


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
            return $update_stat;
        }

        if (!$update_stat) {
            //return array('errno' => 1001, 'msg' => 'record记录失败');
        }

        //更新天数据
        $update_stat = AutoLoad::instance('stat')->update_stat($record_detail);

        if (is_array($update_stat)) {
            return $update_stat;
        }

        //更新小时数据
        $update_stat = AutoLoad::instance('stat')->update_stat_hour($record_detail);

        if (is_array($update_stat)) {
            return $update_stat;
        }

        return 'success';
    }

    /**
     * 获取标签和手机绑定的唯一标示 secret
     * @param unknown $label_id
     */
    public function get_phone_secret($label_id)
    {
        if (!$label_id) {
            return array('errno' => 1001, 'msg' => '获取secret时标签id为空');
        }

        $key = PHONE_SECRET . $label_id;

//////////////////////////怀疑此处取不到缓存内容/////////////////////////
        $secret_info = self::$redis->get($key);
        if ($secret_info) {
            if (isset($secret_info['business_hall_id']) && $secret_info['business_hall_id'] && isset($secret_info['phone_version']) && $secret_info['phone_version']) {

                //设置标签在线
                $res = $this->set_label_online($label_id, $secret_info['business_hall_id']);

                return $secret_info;
            }
            echo 'Redis.secret_info.error:数据非法';
            var_dump($secret_info);
        }
///////////////////////////////////////////////////////////////////

        //重新获取
        $filter = array(
            'label_id' => "'" . $label_id . "'"
        );

        $label_info = AutoLoad::instance('model')->get_info('rfid_label', $filter, ' ORDER BY `update_time` DESC LIMIT 1');

        if (!$label_info) {
            //echo '1<br>';
            return array('errno' => 1001, 'msg' => '标签未绑定设备');
        }

        $business_hall = AutoLoad::instance('model')->get_info('business_hall', $label_info['business_hall_id']);

        if (!$business_hall) {
            return array('errno' => 1001, 'msg' => '营业厅不存在');
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
            return array('errno' => 1001, 'msg' => '更新探针用户时详情不存在[1]');
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
        $secret_info = $this->get_phone_secret($record_detail['label_id']);

        if (isset($secret_info['errno']) && $secret_info['errno'] == 1001) {
            AutoLoad::instance('error_log')->write_error_log($secret_info, '', $label_id);
            return false;
        }

        $data = array('start_time' => $date_minute_timestamp, 'end_time' => $date_minute_timestamp, 'b_id' => $record_detail['business_id'], 'dev' => $secret_info['device']);
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
     * 根据标签id生成key
     * @param unknown $label_id
     */
    public function generate_key($label_id)
    {
        return KEY_SECRET . $label_id;
    }

    /**
     * 设置标签状态
     *
     */
    public function set_label_online($label_id, $business_id = 0)
    {

        if (!$business_id) {

            //获取secret信息
            $label_info = $this->get_phone_secret($label_id);

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

        //查询是否已在线
        $online_info = AutoLoad::instance('model')->get_info('rfid_online_stat_day', array('label_id' => "'" . $label_id . "'", 'day' => "'" . $date . "'"));

        //今日已在线
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