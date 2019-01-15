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

        $start_time = Request::Get('start_time', date('Ymd',time() - 30*24*3600));
        $end_time   = Request::Get('end_time', date('Ymd'));

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

        $filter['status'] = 1;
        $device_list = _model('screen_device')->getList($filter , " ORDER BY `province_id` ASC ");

        $list = $data = [];

        unset($filter['status']);

        $j = 0;
        foreach ( $device_list as $k => $v) {
            $filter['device_unique_id'] = $v['device_unique_id'];

            $online_list = _model('screen_device_online_stat_day')->getList($filter);

            $num = count($online_list);
            if ( $num < 5 ) {
                continue;
            }

            $data[$j]['user_number'] = screen_helper::by_id_get_field($v['business_id'], 'business_hall', 'user_number');
            $data[$j]['title']       = screen_helper::by_id_get_field($v['business_id'], 'business_hall', 'title');
            $data[$j]['province']    = screen_helper::by_id_get_field($v['province_id'], 'province', 'name');
            $data[$j]['city']        = screen_helper::by_id_get_field($v['city_id'], 'city', 'name');
            $data[$j]['area']        = screen_helper::by_id_get_field($v['area_id'], 'area', 'name');
            $data[$j]['device_unique_id'] = $v['device_unique_id'];

            ++ $j;
        }

        $head = array( '渠道视图编码', '门店', '省', '市', '区', '设备ID');// '设备在线数量' );

        $params['filename'] = $start_day. '-'. $end_day .'设备';
        $params['data']     = $data;
        $params['head']     = $head;

        Csv::getCvsObj($params)->export();
    }
}