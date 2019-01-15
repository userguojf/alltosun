<?php
/**
 * alltosun.com 探针与亮屏连接功能 put.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 申小宁 (shenxn@alltosun.com) $
 * $Date: 2017年1月29日 下午4:55:55 $
 * $Id$
 */
class screen_device_widget
{
    /**
     * 获取指定时间段内 指定mac地址是否存在
     * @param unknown $params
     */
    public function check_device_mac($params)
    {
        $start_date = $end_date = date('Ymd');

        isset($params['start_date']) && $params['start_date'] ? $start_date = $params['start_date'] : '';
        isset($params['end_date']) && $params['end_date'] ? $end_date = $params['end_date'] : '';

        if (empty($params['mac'])) {
            return false;
        }

        if (empty($params['business_id'])) {
            return false;
        }

        $filter = [];

        $filter['mac']  = $params['mac'];
        $filter['b_id'] = $params['business_id'];

        if ($start_date == $end_date) {
            $filter['date'] =$start_date;
        } else {
            $filter['date >='] = $start_date;
            $filter['date <'] = $end_date;
        }

        //查询mac信息
        return _widget('probe')->check_mac_exist($filter);
    }

    /**
     * 设备自动下架
     * 1天跑1次
     */
    public function device_auto_dropoff()
    {
        $start_day  = date('Ymd', time() - 3600 * 24 * 30);

        //查询近30天活跃的设备
        $active_devices = _model('screen_device_online_stat_day')->getFields('device_unique_id', array('day >' => $start_day), ' GROUP BY `device_unique_id` ');

        //查询所有上架的设备
        $all_devices    = _model('screen_device')->getFields('device_unique_id', array('status' => 1), ' GROUP BY `device_unique_id` ');

        //近三十天离线的设备
        $offonline_devices = array_diff($all_devices, $active_devices);

        //需要过滤掉近三十天安装的设备
        $recent_30_install_devices = _model('screen_device')->getFields('device_unique_id', array('status' => 1, 'day >' => $start_day), ' GROUP BY `device_unique_id` ');
        $offonline_devices = array_diff($offonline_devices, $recent_30_install_devices);

        foreach ($offonline_devices as $v) {
            $device_info = screen_device_helper::get_device_info_by_device($v);
            if (!$device_info) {
                continue;
            }

            _model('screen_device')->update(array('status' => 1, 'device_unique_id' => $v), array('status' => 0));

            screen_device_helper::drop_off($device_info, 2); //自动下架

        }
    }
}