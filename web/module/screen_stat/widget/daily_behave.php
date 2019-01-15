<?php
/**
 * alltosun.com  daily_behave.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-7-26 下午6:55:32 $
 * $Id$
 */
class daily_behave_widget
{
    private $time = 0;
    private $id   = 0;
    private $filter = [];

    public function stat()
    {
        set_time_limit ( 0 );

        $this->time = time();

        $limit = 50; // 每次处理多少个设备
        $yerstaday = date('Ymd', $this->time - 2 * 24 * 60 * 60);

        $list = _model('screen_daily_hebave_device_record')->getList(
                array('record_day'  => $yerstaday, 'status' => 0), " LIMIT {$limit} " );

        if (!$list) exit('暂无设备信息');

        foreach ($list as $k => $v) {
            $this->filter = array(
                        'province_id'      => $v['province_id'],
                        'city_id'          => $v['city_id'],
                        'area_id'          => $v['area_id'],
                        'business_hall_id' => $v['business_hall_id'],
                        'device_unique_id' => $v['device_unique_id'],
                        'record_day'       => $v['record_day'],
                    );

            $this->id = $v['id'];

            $device_info_list = $this->get_record_list();

            // 处理数据
            if ( !$device_info_list ) {
                $this->update_status();
                continue;
            }

            $this->handle_device_info($device_info_list);

        }
    }

    private function get_record_list()
    {
        // 处理的数据类型  心跳 关机 开机
        $handle_type   = [1, 2, 3, 4];
        $device_filter = $this->filter;
        $device_filter['behave_type'] = $handle_type;

        $device_info_list = _model('screen_daily_hebave_record')->getList(
                $device_filter,
                " ORDER BY `id` DESC " // 计算首次进入主页面是否是自启动
        );

        return $device_info_list;
    }

    /**
     * 单个设备信息的循环操作
     * @param unknown $device_info_list
     */
    private function handle_device_info($device_info_list)
    {
        // 多次开关机 算最后一次 但是首次就算第一次开关机的  一样  因为今天第一次都成功 就看第二天的吧
        // 初始化
        $param = [];
        $param = $this->filter;
        $param['heart_hour_num'] = 0;
        $param['boot_time'] = strtotime(date('Y-m-d 08:00:00', $this->time - 2*24*3600));
        $param['down_time'] = strtotime(date('Y-m-d 20:00:00', $this->time - 2*24*3600));

        foreach ($device_info_list as $k => $v) {

            // 开机
            if ( 4 == $v['behave_type'] ) {
                $param['boot_time'] = strlen($v['time']) == 10 ? $v['time'] : substr($v['time'], 0, 10);

            // 关机
            } elseif ( 3 == $v['behave_type'] ) {
                $param['down_time'] = strlen($v['time']) == 10 ? $v['time'] : substr($v['time'], 0, 10);

            // 进入主界面
            } elseif ( 1 == $v['behave_type'] ) {
                if (!isset($param['auot_start'])) {
                    $param['auto_start'] = $v['type'];
                }
            // 心跳次数
            } elseif ( 2 == $v['behave_type'] ) {
                ++ $param['heart_hour_num'];
            }
        }

        $result = $this->create_happpenind_record($param);

        if ( $result ) $this->update_status();
    }

    private function update_status()
    {
        _model('screen_daily_hebave_device_record')->update(array('id' => $this->id), array('status' => 1));
        return true;
    }

    private function create_happpenind_record($filter)
    {
        // 最开始读出的表就是每天一个设备  所以存储也就不用判断是否存在设备，直接创建
        if (  $filter['boot_time'] > $filter['down_time'] ) {
            $filter['down_time'] = strtotime(date('Y-m-d 20:00:00', $this->time - 2 * 3600*24));
        }

        $filter['date'] = date('Ymd');

        // 就是比如是8次心跳 但是就可能计算了6次或者7次  都可以
        $filter['time_num'] = floor(($filter['down_time'] - $filter['boot_time']) / 3600);

        // 防止程序终端  读的状态没有更新再一次读取到
        $happening_info = _model('screen_daily_behave_happening_record')->read($this->filter);

        if ( $happening_info ) {
            _model('screen_daily_behave_happening_record')->update(array('id' => $happening_info['id']), $filter);
            return true;
        }

        _model('screen_daily_behave_happening_record')->create($filter);
        return true;
    }
}