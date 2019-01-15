<?php
/**
  * alltosun.com rfid数据操作helper helper.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年5月12日 上午10:22:38 $
  * $Id$
  */

class rfid_helper
{

    /**
     * 实时更新统计
     */
    public static function update_stat($id)
    {

        $rfid_record_info = rfid_helper::get_res_info('rfid_record', $id);

        if (!$rfid_record_info) {
            return array('errno' => 1001, 'msg' => '更新统计时记录不存在');
        }

        //获取secret信息
        $secret_info = rfid_helper::get_phone_secret($rfid_record_info['label_id']);

        if (isset($secret_info['errno']) && $secret_info['errno'] == 1001) {
            $this->write_error_log($secret_info, '', $rfid_record_info['label_id']);
            return $secret_info;
        }

        $filter = array(
                'label_id'      => "'".$rfid_record_info['label_id']."'",
                'date_for_day'  => date('Ymd', $rfid_record_info['start_timestamp'])
        );

        //stat表
        $stat_info = rfid_helper::get_res_info('rfid_stat', $filter);
        if (!$stat_info) {
            //创建
            $new_data = $filter;
            $new_data['province_id']    = $secret_info['province_id'];
            $new_data['city_id']        = $secret_info['city_id'];
            $new_data['area_id']        = $secret_info['area_id'];
            $new_data['business_id']    = $secret_info['business_hall_id'];
            $new_data['date_for_week']  = (int)(date('Y', $rfid_record_info['start_timestamp']).date('W', $rfid_record_info['start_timestamp']));
            $new_data['date_for_month'] = (int)(date('Ym', $rfid_record_info['start_timestamp']));
            $new_data['action_num']     = 1;
            $new_data['experience_time'] = $rfid_record_info['remain_time'];
            $new_data['add_time']       = "'".date('Y-m-d H:i:s')."'";
            $result =  rfid_helper::create('rfid_stat', $new_data);
        } else {
            //更新
            $update_data['action_num']         = $stat_info['action_num'] + 1;
            $update_data['experience_time']    = $stat_info['experience_time'] + $rfid_record_info['remain_time'];
            $result = rfid_helper::update('rfid_stat', $filter, $update_data);
        }

        if (!$result){
            return array('errno' => 1001, 'msg' => 'stat统计失败');
        }

        if (is_array($result)) {
            return $result;
        }


        $filter = array(
                'label_id'      => "'".$rfid_record_info['label_id']."'",
                'date_for_day'  => date('Ymd', $rfid_record_info['start_timestamp']),
                'date_for_hour' => date('H', $rfid_record_info['start_timestamp'])
        );

        //hour表
        $hour_stat_info = rfid_helper::get_res_info('rfid_stat_hour', $filter);
        //创建或更新hour统计表
        if (!$hour_stat_info) {
            //创建
            $new_data = $filter;
            $new_data['province_id']        = $secret_info['province_id'];
            $new_data['city_id']            = $secret_info['city_id'];
            $new_data['area_id']            = $secret_info['area_id'];
            $new_data['business_id']        = $secret_info['business_hall_id'];
            $new_data['date_for_week']      = date('Y', $rfid_record_info['start_timestamp']).date('W', $rfid_record_info['start_timestamp']);
            $new_data['date_for_month']     = date('Ym', $rfid_record_info['start_timestamp']);
            $new_data['action_num']         = 1;
            $new_data['experience_time']    = $rfid_record_info['remain_time'];
            $new_data['add_time']           = "'".date('Y-m-d H:i:s')."'";
            $result = rfid_helper::create('rfid_stat_hour', $new_data);
        } else {
            //更新
            $update_data                       = array();
            $update_data['action_num']         = $hour_stat_info['action_num'] + 1;
            $update_data['experience_time']    = $hour_stat_info['experience_time'] + $rfid_record_info['remain_time'];
            $result = rfid_helper::update('rfid_stat_hour', $filter, $update_data);
        }

        if (!$result){
            return array('errno' => 1001, 'msg' => 'hour统计失败');
        }

        if (is_array($result)) {
            return $result;
        }

        return true;

    }

    /**
     * 获取详情
     * @param unknown $table
     * @param unknown $id
     * @return boolean|mixed
     */
    public static function get_res_info($table, $filter, $order='')
    {
        if (!$filter) {
            return array();
        }

        if (is_array($filter)) {
            $where = " WHERE";
            foreach($filter as $k => $v) {
                $where .= " {$k}={$v} AND";
            }

            $where = rtrim($where, 'AND');

        } else {
            $where = " WHERE id={$filter}";
        }

        if ($order) {
            $where .= $order;
        }

        return HandleData::$db->one($table, $where, '*');
    }

    /**
     * 数据添加创建
     * @param unknown $table
     * @param unknown $new_data
     */
    public static function create($table, $new_data)
    {
        if (!$table) {
            return array('errno' => 1001, 'msg' => '数据表不存在');
        }
        if (!is_array($new_data)) {
            return array('errno' => 1001, 'msg' => '数据格式错误[1]');
        }

        $keys       = implode(',', array_keys($new_data));
        $values     = implode(',', array_values($new_data));

        return HandleData::$db->insert($table, $keys, $values);
    }

    /**
     * 更新数据
     * @param unknown $filter 条件
     * @param unknown $update_info
     */
    public static function update($table, $filter, $update_data)
    {

        if (!$filter) {
            return array('errno' => 1001, 'msg' => '更新条件不合法');
        }

        if (!is_array($update_data)) {
            return array('errno' => 1001, 'msg' => '更新时数据格式错误');
        }

        $set = ' SET ';

        //set语句处理
        foreach ($update_data as $k => $v) {
            $set .= " {$k}={$v},";
        }
        $set = rtrim($set, ',');

        //where 条件
        $where = " WHERE ";
        if (is_array($filter)) {
            foreach ($filter as $k => $v) {
                $where .= " {$k}={$v} AND";
            }

            $where = rtrim($where, 'AND');
        } else {
            $where .= "id={$filter} ";
        }

        return HandleData::$db->update($table, $set, $where);
    }

    /**
     * 获取标签和手机绑定的唯一标示 secret
     * @param unknown $label_id
     */
    public static function get_phone_secret($label_id)
    {
        if (!$label_id) {
            return array('errno' => 1001, 'msg' => '获取secret时标签id为空');
        }

        $key = PHONE_SECRET.$label_id;
        $secret = array();
        if (HandleData::$redis->redis->exists($key)) {
             $secret = HandleData::$redis->get($key);
        }

        if (isset($secret['secret']) && $secret['secret']) {
            return $secret;
        }

        //重新获取
        $filter = array(
                'label_id' => "'".$label_id."'"
        );

        $label_info = self::get_res_info('rfid_label', $filter, ' ORDER BY `update_time` DESC LIMIT 1');

//////////////// 上线需更改 /////////////////
        if (!$label_info) {
            $label_info['name'] = '苹果';
            $label_info['version'] = '10s';
            $label_info['color'] = '炫酷黑';
            $label_info['business_hall_id'] = '88584';
        }


//         if (!$label_info) {
//             return array('errno' => 1001, 'msg' => '标签未绑定设备');
//         }
//////////////// 上线需更改 /////////////////
        $business_hall = self::get_res_info('business_hall', $label_info['business_hall_id']);

        if (!$business_hall) {
            return array('errno' => 1001, 'msg' => '营业厅不存在');
        }

        //更新secret
        $value  = array(
                'secret'            => $label_info['name'].','.$label_info['version'].','.$label_info['color'],
                'business_hall_id'  => $label_info['business_hall_id'],
                'province_id'       => $business_hall['province_id'],
                'city_id'           => $business_hall['city_id'],
                'area_id'           => $business_hall['area_id'],
        );

        HandleData::$redis->set($key, $value, 3600*24);

        return $value;

    }



}