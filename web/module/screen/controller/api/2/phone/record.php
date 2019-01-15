<?php

/**
 * alltosun.com 添加动作记录 record.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年7月18日 下午4:03:10 $
 * $Id: record.php 373322 2017-09-28 05:04:13Z huangyq $
 */

class Action
{
//////////////////////////////////////////////// 2017-11-06优化前的 START /////////////////////////////////////////////////////////
    public function add_device_record()
    {
        //暂时返回正确
        //api_helper::return_api_data(1000, 'success', array('info'=>'ok'), $api_log_id);

        $info            = tools_helper::post('info', '');
        $check_params = array(
        );

        $api_log_id = api_helper::check_sign($check_params, 0);

        if (!$info) {
            api_helper::return_api_data(1003, '请上传动作相关信息', array(), $api_log_id);
        }

        //暂时正确
        //api_helper::return_api_data(1000, 'success', array('info'=>'ok'), $api_log_id);

        //受影响的按设备统计id
        $device_stat_ids = array();

        $new_info = json_decode(htmlspecialchars_decode($info), true);

        if (count($new_info) > 20) {
            $new_info = array_slice($new_info, count($new_info) - 20, 20);
        }

        //api_helper::return_api_data(1000, 's', $info , $api_log_id);
        foreach ($new_info as $k => $v) {

            if (empty($v['device_unique_id'])) {
                continue;
            }

            if (!$v['type']) {
                continue;
            }

            //wangjf add 23点-07点之间的数据不记录
            $h = (int)(date('H', $v['experience_time']));
            if ($h < 7 || $h >= 23) {
                continue;
            }

            //不是当天的数据则不处理或存储， 因为除了当天以外所有的统计都已经统计完了
            if (date('Ymd', $v['experience_time']) != date('Ymd')) {
                continue;
            }

            //添加或更新记录
            $record_id = _widget('screen')->add_action_record($v);
            if (!$record_id) {
                continue;
            }

            //如果有记录id并且为放下的动作，则更新统计
            if ($v['type'] == 2 && $record_id) {
                //按设备统计
                $device_stat_id = _widget('screen_stat')->add_action_stat_by_device($record_id);
            }
        }
        api_helper::return_api_data(1000, 'success', array('info'=>'ok'), $api_log_id);
    }

}