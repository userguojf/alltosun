<?php
/**
 * alltosun.com 自启动接口  auto_start.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-1-11 上午11:10:00 $
 * $Id$
 */
// 需求：接口增加自启动是否开启的数据上报能力，由杨柯传给后端
class Action
{
    // 自动上报的状态记录
    public $auto_start = array(
            0 => '未启动',
            1 => '自启动',
            2 => '关机'
    );
    // status字段记录
    public $status = array(
            0 => '下架',
            1 => '正常',
            2 => '设备重新上报之前数据（升级或者重新安装）',
            3 => '统计7天完成'
    );

    public function __call($action = '', $param = array())
    {

        $user_number      = tools_helper::post('user_number', '');
        $device_unique_id = tools_helper::post('device_unique_id', '');
        $info             = tools_helper::post('info', '');
        $reset_report     = tools_helper::post('reset_report', 0);

        $filter = [];
        // 日志先接受参数
        $api_log_id = api_helper::check_sign(array(), 0);

//         if ( $auto_start == 10009 || !in_array($auto_start, array(0, 1)) ) {
//             api_helper::return_api_data(1003, '请传参数：自动开启', array(), $api_log_id);
//         }

        if ( !$user_number ) {
            api_helper::return_api_data(1003, '请传参数：渠道码', array(), $api_log_id);
        }

        if ( !$device_unique_id ) {
            api_helper::return_api_data(1003, '请传参数：唯一ID', array(), $api_log_id);
        }

        $business_hall_info  = _model('business_hall')->read(array('user_number'=>$user_number));

        if ( !$business_hall_info ) {
            api_helper::return_api_data(1003, '未找到营业厅信息', array(), $api_log_id);
        }

        // 营业厅信息
        $filter = [
            'province_id'      => $business_hall_info['province_id'],
            'city_id'          => $business_hall_info['city_id'],
            'area_id'          => $business_hall_info['area_id'],
            'business_hall_id' => $business_hall_info['id']
        ];

        // 拿取设备信息处理
        $device_info = _model('screen_device')->read(
                    array(
                            'business_id'      => $business_hall_info['id'],
                            'device_unique_id' => $device_unique_id,
                            'status'           => 1 // 必须输上架状态
        ));

        if ( !$device_info ) {
            api_helper::return_api_data(1003, '未找到设备信息', array(), $api_log_id);
        }
        // 设备信息
        $filter['device_unique_id'] = $device_unique_id;
        // 开启的信息
        $start_info = json_decode(htmlspecialchars_decode($info), true);

        if ( !is_array($start_info) ) {
            api_helper::return_api_data(1003, '自动开启的对象参数格式错误', array(), $api_log_id);
        }

        if ( $reset_report ) {
            $old_info_ids = _model('screen_auto_start')->getFields( 'id',
                    array(
                        'business_hall_id' => $business_hall_info['id'],
                        'device_unique_id' => $device_unique_id,
                    )
            );

            if ( $old_info_ids ) {
                _model('screen_auto_start')->update(array( 'id' => $old_info_ids),  array( 'status' => 2 ) );
            }
        }

        // 存储成功标识
//         $create_id = 0;

        // 用户操作信息
        foreach ($start_info as $k => $v) {
            ///////// 1、过滤单日多条记录
            if ( !isset($v['auto_start']) || !isset($v['auto_start_time']) ) {
                continue;
            }

            // 处理重新安装或者系统升级
            $filter['reset_report'] = $reset_report;

            // 操作行为
            $filter['auto_start']   = $v['auto_start'];
            // 操作的时间
            $filter['operate_time'] = date('Y-m-d H:i:s', $v['auto_start_time']);
            // 操作当日
            $filter['operate_date'] = date('Ymd', $v['auto_start_time']);
            // 设备正常
            $filter['status']       = 1;
            // 安装时间
            $filter['install_date'] = $device_info['day'];

            // 记录数据
            $create_id = _model('screen_auto_start')->create($filter);
        }
        // 存储有一次就行
//         if ( !$create_id ) {
//             api_helper::return_api_data(1003, '自动开启的对象参数', array(), $api_log_id);
//         }

        // 按营业厅统计调用代码块
        $this->business_stat($business_hall_info);

        api_helper::return_api_data(1000, 'success', array ( 'info' => 'ok' ), $api_log_id);
    }

    // 自定义统计
    public function business_stat($yyt_info)
    {
        $table  = 'screen_auto_start_business_stat';
        $filter = [];

        $device_list = _model('screen_device')->getList(
                array(
                        'business_id' => $yyt_info['id'],
                        'status'      => 1
                )
        );

        if ( !$device_list ) return false;

        $filter['device_all_num'] = count($device_list);
        $filter['normal_num']     = 0;
        $filter['abnormal_num']   = 0;

        foreach ($device_list as $k => $v) {
            $normal_num = _model('screen_auto_start')->getTotal(
                                                    array(
                                                            'device_unique_id' => $v['device_unique_id'],
                                                            'auto_start'       => 1,
                                                            'operate_date <='  => date('Ymd'),
                                                            'operate_date >='  => $v['day'],
                                                            'status'           => 1
                                                        )
                                            );

            if ( $normal_num == 7 ) {
                ++ $filter['normal_num'];
            } else {
                ++ $filter['abnormal_num'];
            }
        }

        $info = _model($table)->read(array('business_hall_id' => $yyt_info['id']));

        if ( !$info ) {
             $filter['province_id'] = $yyt_info['province_id'];
             $filter['city_id']     = $yyt_info['city_id'];
             $filter['area_id']     = $yyt_info['area_id'];
             $filter['business_hall_id'] = $yyt_info['id'];

             _model($table)->create($filter);
        } else {
            _model($table)->update(array('business_hall_id' => $yyt_info['id']), $filter);
        }

        return true;
    }

}