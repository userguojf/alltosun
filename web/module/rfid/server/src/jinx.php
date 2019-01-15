<?php
/**
  * alltosun.com 金鑫设备数据处理类 jinx.php
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
class jinx extends BaseHandle
{
    //数据字段
    public static $fields   = array(
            'ID',
            'MAC',
            'START',
            'END',
            'DURATION',
            'RSSI',
            'MOV',
            'BAT',
            'G_Data_X',
            'G_Data_Y',
            'G_Data_Z',
            'A_Data_X',
            'A_Data_Y',
            'A_Data_Z'
    );

    public $string = '';

    public function __construct()
    {
        global $redis_cache;
        self::$redis = $redis_cache;
    }

    /**
     * 效验字符串
     * @param unknown $string 字符串
     * @return number[]|string[]|number[]|number[][]|string[][]|string[][][]|unknown[][][]|number[][][]
     */
    public function check($string, swoole_server $serv=NULL, $fd=NULL)
    {
        if (!$string) {
            return array('errno' => 1001, 'msg' => '数据不能为空');
        }

        $this->string = $string;

        //分割为数组， 解决一条数据多个包
        $rfid_list = explode("\n\n", $string);

        if (!$rfid_list) {
            return array('errno' => 1001, 'msg' => '无效的数据包数据，解析失败');
        }

        //处理数据
        $new_list       = array();
        $error_logs     = array();

        foreach ($rfid_list as $k => $v) {

            //解析为数组
            $arr_info = $this->string_to_arr($v, $fd);

            if ($arr_info['errno'] != 0) {
                //写入错误

                $error['error_data']    = $v;
                $error_logs[]           = $arr_info;
                continue;
            }

            $new_list[] = $arr_info['data'];
        }

        //调用自身处理类
        return $this->handle(array('errno' => 0, 'data' => $new_list, 'error_logs' => $error_logs));

    }

    /**
     * 数据处理
     * @param unknown array() 效验后的数据
     */
    public function handle($result)
    {

        if ( $result['errno'] !=0 ) {
            return $result;
        }

        //记录错误日志
        if ( is_array( $result['error_logs'] ) ) {
            foreach ( $result['error_logs'] as $k => $v ) {
                $v['string'] = $this->string;
                AutoLoad::instance('error_log')->write_error_log($v);
            }
        }

        $data = $result['data'];

        if (!$data) {
            return array('errno' => 1002, 'msg' =>'数据解析为空');
        }

        foreach ($data as $k => $v) {

            //只有放下的动作
            $result = $this->lay_down($v);

            if ($result != 'success') {
                $result['string'] = $this->string;
                AutoLoad::instance('error_log')->write_error_log($result);
            }
        }

        return 'success';
    }

    /**
     * 处理包 放下
     */
    public function lay_down($data)
    {

        //获取secret信息
        $secret_info = $this->get_phone_secret($data['ID']);

        //有错误
        if (isset($secret_info['errno']) && $secret_info['errno'] != 0) {
            return $secret_info;
        }

        //兼容旧版本
        if (isset($secret_info['secret'])) {
            list($secret_info['phone_name'], $secret_info['phone_version'], $secret_info['phone_color']) = explode(',', $secret_info['secret']);
        }

        //计算体验时长
        $remain_time = $data['end_timestamp'] - $data['start_timestamp'];
        $status      = 1;

        if ($remain_time < QUALIFIED_INTERVAL) {
            $status = -2;
        }

        $new_data = array(
                'label_id'          => "'".$data['ID']."'",
                'date'              => date('Ymd', $data['start_timestamp']),
                'mac'               => "'".$data['MAC']."'",
                'start_timestamp'   => $data['start_timestamp'],
                'end_timestamp'     => $data['end_timestamp'],
                'remain_time'       => $remain_time,
                'rssi'              => "'".$data['RSSI']."'",
                'province_id'       => $secret_info['province_id'],
                'city_id'           => $secret_info['city_id'],
                'area_id'           => $secret_info['area_id'],
                'business_id'       => $secret_info['business_hall_id'],
                'phone_name'        => "'".$secret_info['phone_name']."'",
                'phone_version'     => "'".$secret_info['phone_version']."'",
                'phone_color'       => "'".$secret_info['phone_color']."'",
                'status'            => $status,
                'add_time'          => "'".date('Y-m-d H:i:s')."'",
        );


        //创建动作记录
        $record_id =  AutoLoad::instance('model')->create('rfid_record_detail', $new_data);

        if (!$record_id) {
            return array('errno' => 1001, 'msg' => '插入数据失败', 'error_data' => $new_data);
        }

        $record_data = array(
                'id'                => $record_id,
                'label_id'          => $data['ID'],
                'date'              => date('Ymd', $data['start_timestamp']),
                'mac'               => $data['MAC'],
                'start_timestamp'   => $data['start_timestamp'],
                'end_timestamp'     => $data['end_timestamp'],
                'remain_time'       => $remain_time,
                'rssi'              => $data['RSSI'],
                'province_id'       => $secret_info['province_id'],
                'city_id'           => $secret_info['city_id'],
                'area_id'           => $secret_info['area_id'],
                'business_id'       => $secret_info['business_hall_id'],
                'phone_name'        => $secret_info['phone_name'],
                'phone_version'     => $secret_info['phone_version'],
                'phone_color'       => $secret_info['phone_color'],
                'status'            => $status,
        );

        //体验时长不达标或其他异常则不计统计
        if ($record_data['status'] < 1){
            return 'success';
        }

        //更新统计
        $result = $this->update_stat($record_data);

        if (is_array($result)) {
            return $result;
        }

        //更新探针数据
        $this->add_probe_user($record_data);

        return 'success';

    }


    /**
     * rfid字符串数据转换为数组
     */
    public function string_to_arr($string, $fd=NULL)
    {
        $string = trim($string);

        //分字段截取
        $arr                = explode("\n", $string);
        $new_arr            = array();  //处理后的数组
        $original_arr       = array();  //原始数组

        //需保留的有效字段
        $valid_fields = array(
                'ID',
                'MAC',
                'RSSI'
        );

        foreach ($arr as $k => $v) {

            //分割键值
            list($field, $value) = explode(':', $v);

            $field = trim($field);
            $value = trim($value);

            //不存在此字段
            if (!in_array($field, self::$fields)) {
                return array('errno' => 1002, 'msg' => "数据包无效，错误字段[{$field}]");
            }

            //保留的有效数据
            if (in_array($field, $valid_fields)) {
                $new_arr[$field] = $value;
            }

            //原始数据存储
            $original_arr[$field] = $value;
        }

        //处理请求时间
        if ( isset($original_arr['START']) && $original_arr['START'] != '--' && isset($original_arr['END']) && $original_arr['END'] != '--') {
            $new_arr['start_timestamp']     = $original_arr['START'];
            $new_arr['end_timestamp']       = $original_arr['END'];
        } else {
            return array('errno' => 1001, 'msg' => "数据包时间格式错误");
        }

        return array('errno' => 0, 'data' => $new_arr);
    }

}