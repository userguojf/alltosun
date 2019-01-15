<?php
/**
  * alltosun.com 处理文件数据 handle_file_data.php
  * ============================================================================
  * 版权所有 (C)个人(王敬飞) 并保留所有权利。
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (15001114056@163.com) $
  * $Date: 2018年5月4日 下午3:09:09 $
  * $Id$
  */
class handle_file_data_widget
{
    /*
     * processed 处理状态
     *  0-未处理
     *  1-文件处理中
     *  2-已处理
     *  3-文件不存在
     *  4-数据解析失败
     *  5-设备不存在
     */

    /**
     * 计划任务 暂定10分钟跑一次
     * 处理拿起放下的动作数据
     */
    public function handle_action_data()
    {
        $res_name = 'add_device_record';

        $content_arr = $this->read_file($res_name);

        if ($content_arr === false) {
            return false;
        }

        if (!isset($content_arr['info'])) {
            //更新文件为异常状态
            _model('screen_file_data_record')->update($content_arr['file_data_record_id'], array('processed' => 4));
            return false;
        }

        $device_unique_id = $content_arr['device_unique_id'];

        $device_info = screen_device_helper::get_device_info_by_device($device_unique_id);

        if (!$device_info ||  !$business_info = business_hall_helper::get_business_hall_info($device_info['business_id'])) {
            //设备或营业厅不存在
            _model('screen_file_data_record')->update($content_arr['file_data_record_id'], array('processed' => 5));
            return false;
        }

        $days = array();

        foreach ($content_arr['info'] as $k => $v) {

            $v['device_unique_id'] = $device_info['device_unique_id'];

            //添加或更新记录
            $record_id = _widget('screen')->add_action_record4($v);
            if (!$record_id) {
                continue;
            }

            //按设备统计
            $device_stat_id = _widget('screen_stat')->add_action_stat_by_device($record_id);

            $days[date('Ymd', $v['add_time'])] = 1;
        }

        foreach ($days as $k => $v) {
            // 更新指定日期的亮屏动作统计（按营业厅）
            _widget('screen_stat')->add_action_stat_by_business_task($k);
        }

        return true;
    }

    /**
     * 计划任务 暂定10分钟跑一次
     * 处理内容点击的文件数据
     */
    public function handle_content_click_data()
    {
        $res_name = 'add_click';

        $content_arr = $this->read_file($res_name);

        if ($content_arr === false) {
            return false;
        }

        if (!isset($content_arr['info'])) {
            //更新文件为异常状态
            _model('screen_file_data_record')->update($content_arr['file_data_record_id'], array('processed' => 4));
            return false;
        }

        $device_unique_id = $content_arr['device_unique_id'];

        $device_info = screen_device_helper::get_device_info_by_device($device_unique_id);

        if (!$device_info ||  !$business_info = business_hall_helper::get_business_hall_info($device_info['business_id'])) {
            //设备或营业厅不存在
            _model('screen_file_data_record')->update($content_arr['file_data_record_id'], array('processed' => 5));
            return false;
        }

        $new_data = array();

        foreach ($content_arr['info'] as $k => $v) {
            $new_data[]  = array(
                    'province_id'       => (int)$business_info['province_id'],
                    'city_id'           => (int)$business_info['city_id'],
                    'area_id'           => (int)$business_info['area_id'],
                    'business_id'       => (int)$business_info['id'],
                    'device_unique_id'  => $device_info['device_unique_id'],
                    'res_id'            => (int)$v['content_id'],
                    'day'               => (int)date("Ymd", $v['time']),
                    'click_num'         => (int)$v['click_count'], //wangjf add 新增点击数量
                    'add_time'          => date('Y-m-d H:i:00', $v['time']),
                    'data_add_time'          => date('Y-m-d H:i:s'),
            );
        }

        if ($new_data) {
            _mongo('screen', 'screen_click_record')->insertMany($new_data);
        }

        return true;
    }

    /**
     * 计划任务 暂定10分钟跑一次
     * 处理内容轮播的文件数据
     */
    public function handle_content_stat_data()
    {
        $res_name = 'content_stat';

        $content_arr = $this->read_file($res_name);

        if ($content_arr === false) {
            return false;
        }

        if (!isset($content_arr['info'])) {
            //更新文件为异常状态
            _model('screen_file_data_record')->update($content_arr['file_data_record_id'], array('processed' => 4));
            return false;
        }

        $device_unique_id = $content_arr['device_unique_id'];

        $device_info = screen_device_helper::get_device_info_by_device($device_unique_id);

        if (!$device_info || !$business_info = business_hall_helper::get_business_hall_info($device_info['business_id'])) {
            //设备或营业厅不存在
            _model('screen_file_data_record')->update($content_arr['file_data_record_id'], array('processed' => 5));
            return false;
        }

        // 公共条件
        $info = array (
                'device_unique_id'  => $device_unique_id,
                'province_id' => ( int ) $business_info ['province_id'],
                'city_id'     => ( int ) $business_info ['city_id'],
                'area_id'     => ( int ) $business_info ['area_id'],
                'business_id' => ( int ) $business_info ['id'],
                // 'day'         => ( int ) date ( "Ymd" ),
        );

        //循环多条轮播数据
        foreach ( $content_arr['info'] as $k => $v ) {

            if ( empty($v ['content_id']) ) {
                continue;
            }

            // android毫秒改成php秒时间戳记录
            $time_stamp = ( int ) substr($v ['time'], 0, 10);

            $stat_day_info = $record_info = $info;

            // 按天统计
            $stat_day_info['content_id'] = ( int ) $v ['content_id'];
            $stat_day_info['day']        = ( int ) date("Ymd", $time_stamp);

            // 记录条件
            $record_info['content_id']  = ( int ) $v ['content_id'];
            $record_info['day']         = ( int ) date('Ymd', $time_stamp);
            $record_info ['click_time'] = $time_stamp;
            $record_info ['roll_sum']   = ( int ) $v['roll_sum'];
            $record_info ['add_time']   = date('Y-m-d H:i:s');

            // 轮播记录
            _mongo ('screen', 'screen_content_click_record')->insertOne ($record_info);

            // 上报时间大于今天统计略过
            if ( $stat_day_info['day'] > date('Ymd') ) {
                continue;
            }

            //添加天统计
            //$this->stat_day($v, $stat_day_info);
            _widget('screen_stat.content_stat')->stat_content_day(array(
                    'info'          => $v,
                    'stat_day_filter' => $stat_day_info
            ));

            // 添加设备的轮播统计
            //$this->device_stat($device_info, $v['content_id'], $v['roll_sum'], $time_stamp);
            _widget('screen_stat.content_stat')->stat_content_device(array(
                    'device_info' => $device_info,
                    'content_id'  => $v['content_id'],
                    'roll_sum'      => $v['roll_sum'],
                    'time_stamp'    => $time_stamp,
            ));

        }

        return true;
    }

    /**
     * 读取文件内容
     * @param unknown $res_name 资源类型
     * @return boolean
     */
    private function read_file($res_name)
    {
        $path = UPLOAD_PATH.'/act_data';

        $filter = array(
                'processed' => 0, //未处理
                'res_name'  => $res_name,
        );

        $file_info = _model('screen_file_data_record')->read($filter, ' ORDER BY `id` ASC LIMIT 1');

        if (!$file_info) {
            return false;
        }

        $file_path = $path.$file_info['link'];

        //文件不存在
        if (!file_exists($file_path)) {
            _model('screen_file_data_record')->update($file_info['id'], array('processed' => 3));
            return false;
        }

        //更新文件为处理中的状态
        _model('screen_file_data_record')->update($file_info['id'], array('processed' => 1));

        $content = file_get_contents($file_path);
        if (!$content) {
            //更新文件为处理完毕状态
            _model('screen_file_data_record')->update($file_info['id'], array('processed' => 2));
            return false;
        }

        $content_arr = json_decode($content, true);

        if (empty($content_arr)) {
            //更新文件为异常状态
            _model('screen_file_data_record')->update($file_info['id'], array('processed' => 4));

            return false;
        }

        $content_info = array(
                'info'      => $content_arr,
                'res_name'  => $file_info['res_name'],
                'device_unique_id'      => $file_info['device_unique_id'],
                'file_data_record_id'   => $file_info['id'],
        );
        return $content_info;
    }
}