<?php
/**
  * alltosun.com 探针设备widget probe_dev.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2018年3月10日 下午3:59:04 $
  * $Id$
  */
probe_helper::load('func');
class probe_dev_widget
{

    public function device_status_stat_day($date='')
    {
        if (!$date) {
            $date = date('Ymd');
        }

        //查询所有的设备
        $device_list = _model('probe_device')->getList(array('status' => 1));
        foreach ( $device_list as $k => $v ) {
            // 初始化数据库操作对象
            $db  = get_db($v['business_id']);

            if (!is_object($db)) {
                continue;
            }

            //wangjf add
            $last_info = $db->read(array('dev' => $v['device'], 'date' => $date), ' ORDER BY `id` DESC ');

            //离线
            if ( !$last_info ) {
                $status = 2;
            //在线（活跃）
            } else {
                $status = 1;
            }
            //注：暂时只有这两种状态，后续还有待发货、已发货等

            //查询统计
            $filter = array(
                    'device' => $v['device'],
                    'date'   => $date,
                    'business_id' => $v['business_id']
            );

            $stat_day = _model('probe_device_status_stat_day')->read($filter);

            if ($stat_day) {
                continue;
            }
            //插入统计
            $new_data = array(
                    'province_id'   => $v['province_id'],
                    'city_id'       => $v['city_id'],
                    'area_id'       => $v['area_id'],
                    'business_id'   => $v['business_id'],
                    'device'        => $v['device'],
                    'status'        => $status,
                    'date'          => $date
            );
            _model('probe_device_status_stat_day')->create($new_data);
        }
    }
}