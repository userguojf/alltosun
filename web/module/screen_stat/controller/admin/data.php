<?php
/**
 * alltosun.com  data.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-7-30 下午7:47:52 $
 * $Id$
 */

class  Action
{
    // 跑每天的设备记录
    public function get_old_data()
    {
        $id = tools_helper::get('id', 0);

        $list = _model('screen_daily_hebave_record')->getList(
            array('id >' => $id), " LIMIT 200 "
        );

        if (!$list) {
            echo '完成';
            exit();
        }

        foreach ($list as $k => $v) {
            $filter = [];
            $filter = [
                    'province_id'      => $v['province_id'],
                    'city_id'          => $v['city_id'],
                    'area_id'          => $v['area_id'],
                    'business_hall_id' => $v['business_hall_id'],
                    'device_unique_id' => $v['device_unique_id'],
                    'record_day'       => $v['record_day']
                    ];
            $this->create_device($filter);
            $id = $v['id'];
        }

//         sleep(1);
//         header('location:'.AnUrl("screen_stat/admin/data/get_old_data?id={$id}"));
        echo "<script>window.location.href = '". AnUrl("screen_stat/admin/data/get_old_data?id={$id}")."'</script>";
//         exit();
    }

    public function create_device($filter)
    {
        $device_info = _model('screen_daily_hebave_device_record')->read($filter);

        if ( !$device_info ) {
           $filter['date'] = date('Ymd');
           $filter['target'] = 1;

           _model('screen_daily_hebave_device_record')->create($filter);
        }
        return true;
    }

}