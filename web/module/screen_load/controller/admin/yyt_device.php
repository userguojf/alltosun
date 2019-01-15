<?php
/**
 * alltosun.com  ting_device.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-6-19 下午5:34:11 $
 * $Id$

规则：
亮屏覆盖门店数：指有终端安装亮屏的门店总数
安装手机数≥5门店数：指安装终端数量大于或等于5个的门店数
安装手机总数：指该省所有门店安装终端的总数
设备在线数量：指搜索时间段内 有过在线行为的终端的总数

 */
class Action
{
   
    public function index()
    {
        set_time_limit(0);

        $start_time = Request::Get('start_time', date('Y-m-d',time() - 30*24*3600));
        $end_time   = Request::Get('end_time', date('Y-m-d'));
        $is_export  = tools_helper::get('is_export', 0);;

        $filter = $list = array();
        //搜索
        $start_day = date('Ymd', strtotime($start_time) );
        $end_day   = date('Ymd', strtotime($end_time) );

        $province_ids = _model('province')->getFields('id', array(1 => 1));

        $filter = [
            'day >=' => $start_day,
            'day <=' => $end_day
        ];

        $online_device_arrs = _model('screen_device_online_stat_day')->getFields('device_unique_id', $filter);

        $filter['status'] = 1;
        $device_list = _model('screen_device')->getList($filter);

        $list = [];

        foreach ( $device_list as $k => $v) {

            if ( isset($list[$v['province_id']]) ) {
                $list[$v['province_id']]['device_all_num'] += 1;

                if ( !in_array($v['business_id'], $list[$v['province_id']]['yyt_list']) ) {
                    array_push($list[$v['province_id']]['yyt_list'], $v['business_id']);
                }
                if ( isset($list[$v['province_id']][$v['business_id']]) ) {
                    $list[$v['province_id']][$v['business_id']] += 1;
                    if ( $list[$v['province_id']][$v['business_id']] == 5) {
                        $list[$v['province_id']]['device_gt_5_yyt_list'] += 1;
                    }
                } else {
                    $list[$v['province_id']][$v['business_id']] = 1;
                }


            } else {

                $list[$v['province_id']]['yyt_list'] = [];
                $list[$v['province_id']]['device_gt_5_yyt_list'] = 0;
                $list[$v['province_id']]['device_all_num'] = 1;
                $list[$v['province_id']]['device_online']  = 0;

                $list[$v['province_id']][$v['business_id']] = 1;

                array_push($list[$v['province_id']]['yyt_list'], $v['business_id']);
            }


            if ( in_array($v['device_unique_id'], $online_device_arrs) ) {
                $list[$v['province_id']]['device_online']  += 1;
            }
        }

        foreach ($province_ids as $v) {
            if ( !isset($list[$v]) ) {
                $list[$v]['yyt_list'] = [];
                $list[$v]['device_gt_5_yyt_list'] = 0;
                $list[$v]['device_all_num'] = 0;
                $list[$v]['device_online']  = 0;
            }
        }

        $j = 0;
        foreach ( $list as $key => $val ) {
            $data[$j]['province'] = screen_helper::by_id_get_field($key, 'province', 'name');
            $data[$j]['yyt_list'] = count($val['yyt_list']);
            $data[$j]['device_gt_5_yyt_list'] = $val['device_gt_5_yyt_list'];
            $data[$j]['device_all_num'] = $val['device_all_num'];
            $data[$j]['device_online']  = $val['device_online'];

            ++ $j;
        }

        if ( $is_export ) {
            $head = array(  '省份', '亮屏覆盖门店数', '安装手机数≥5门店数', '安装手机总数', '设备在线数量');

            $params['filename'] = $start_day. '-'. $end_day .'亮屏详情';

            $params['data']     = $data;
            $params['head']     = $head;

            Csv::getCvsObj($params)->export();
        }

        Response::assign('list', $data);
        Response::assign('start_time', $start_time);
        Response::assign('end_time', $end_time);
        Response::display('admin/load_yyt_device_list.html');
    }

}