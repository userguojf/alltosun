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


        $filter = [
            'day >=' => $start_day,
            'day <=' => $end_day
        ];

        //$online_device_arrs = _model('screen_device_online_stat_day')->getFields('device_unique_id', $filter);

//         $sql  = "SELECT province_id, city_id, area_id, business_id, COUNT(*) AS device_num";
//         $sql .= " FROM `screen_device`  ";
//         $sql .= " WHERE province_id=1 AND day >= {$start_day} AND day <= {$end_day} AND status=1 ";
//         $sql .= " GROUP BY business_id ";

//         $device_list = _model('screen_device')->getAll($sql);

        $filter['status'] = 0;
        $device_list = _model('screen_device')->getList($filter , " ORDER BY `province_id` ASC ");

        $list = [];

        foreach ( $device_list as $k => $v) {

            if ( !isset($list[$v['business_id']]) ) {
                $list[$v['business_id']] = [];
                $list[$v['business_id']]['device_num'] = [];
                $list[$v['business_id']]['online_num'] = 0;
            }

            array_push($list[$v['business_id']]['device_num'], $v['device_unique_id']);
        }

        $data = [];
        $j = 0;
        foreach ( $list as $key => $val ) {
            $yyt_ifno = _uri('business_hall', array('id' => $key));
            if ( !$yyt_ifno ) continue;

            $data[$j]['user_number'] = $yyt_ifno['user_number'];
            $data[$j]['province']    = screen_helper::by_id_get_field($yyt_ifno['province_id'], 'province', 'name');
            $data[$j]['city']        = screen_helper::by_id_get_field($yyt_ifno['city_id'], 'city', 'name');
            $data[$j]['area']        = screen_helper::by_id_get_field($yyt_ifno['area_id'], 'area', 'name');
            $data[$j]['title']       = $yyt_ifno['title'];
            $data[$j]['device_num']  = count($val['device_num']);

            ++ $j;
        }

        $head = array( '渠道视图编码', '省', '市', '区', '门店', '下柜手机数');

        $params['filename'] = $start_day. '-'. $end_day .'（厅为单位）亮屏详情';
        $params['data']     = $data;
        $params['head']     = $head;

        Csv::getCvsObj($params)->export();

    }

}