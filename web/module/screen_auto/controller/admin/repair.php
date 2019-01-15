<?php
/**
 * alltosun.com  repair.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-3-2 下午2:25:01 $
 * $Id$
 */

class Action
{
    public function index()
    {
        $page = tools_helper::get('page', 1);

        $pageshow = 100;
        $pagesize = ($page - 1) * $pageshow; 

        $list = _model('screen_device')->getList(array(1 => 1), " LIMIT $pagesize , $pageshow ");

        if ( !$list ) {
            echo '更新完成';
            exit();
        }


        foreach ($list as $k => $v) {
            $yyt_info = _model('business_hall')->read(array('id' => $v['business_id']));
            if ( !$yyt_info ) {
                continue;
            }
            $this->business_stat($yyt_info);
        }

        ++ $page;
        echo "<script>window.location.href = '". AnUrl("screen_auto/admin/repair?page={$page}") ."'</script>";
        exit();
    }

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
        $filter['lt_seven_num']   = 0;
        $filter['upgrade_num']    = 0;
        $filter['normal_num']     = 0;
        $filter['abnormal_num']   = 0;

        foreach ($device_list as $k => $v) {

            // 注意：只统计 操作时间小于安装时间的
            $normal_num = _model('screen_auto_start')->getTotal(
                    array(
                            'business_hall_id' => $v['business_id'],
                            'device_unique_id' => $v['device_unique_id'],
                            'auto_start'       => 1,
                            'operate_date <='  => date('Ymd'),
                            'operate_date >='  => $v['day'],
                            'status'           => 1
                    )
            );

            if ( $normal_num == 7 ) {
                ++ $filter['normal_num'];
            }else {
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