<?php
use GuzzleHttp\json_decode;
/**
 * alltosun.com 亮靓每天软件行为计数 daily_behave.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-7-5 上午10:02:37 $
 * $Id$
 */
class Action
{
    private $filter        = [];

    // // 安装字段总是变化的   不然程序总是改造 下面两个数组默认下标就为各自数据的行为标识
    private $device_behave = [
        'app_leave',
        'app_entry',
        'app_heart_hour',
        'device_shutdown',
        'device_poweron',
        'report_record'
    ];

    
    private $device_behave_param = [
        'app_leave' => ['netstate', 'time'],
        'app_entry' => ['type', 'netstate', 'time'],
        'app_heart_hour'  => ['inapp', 'netstate', 'battery_status', 'battery_level', 'time'],
        'device_shutdown' => ['time'],
        'device_poweron'  => ['time'],
        //'report_record'   => ['content', 'time', 'report_state']
    ];

    public function index()
    {
//         exit('线下测试数据,暂时关闭线下接口');
        $device_unique_id = tools_helper::post('device_unique_id', '');
        $user_number      = tools_helper::post('user_number', '');
        $content          = tools_helper::post('content', '');

        // 日志先接受参数
        $api_log_id = api_helper::check_sign(array(), 0);

        if ( !$user_number ) {
            api_helper::return_api_data(1003, '请传参数：渠道码', array(), $api_log_id);
        }

        if ( !$device_unique_id ) {
            api_helper::return_api_data(1003, '请传参数：唯一ID', array(), $api_log_id);
        }

        $business_hall_info  = _model('business_hall')->read(array('user_number'=>$user_number));

        if ( !$business_hall_info ) {
            api_helper::return_api_data(1003, '未找到营业厅信息', array(), $api_log_id);
        }

        $device_info = _model('screen_device')->read(array('device_unique_id' => $device_unique_id, 'status' => 1));

        if ( !$device_info ) {
            api_helper::return_api_data(1003, '未找到设备', array(), $api_log_id);
        }

        // 营业厅信息
         $this->filter = [
            'province_id'      => $business_hall_info['province_id'],
            'city_id'          => $business_hall_info['city_id'],
            'area_id'          => $business_hall_info['area_id'],
            'business_hall_id' => $business_hall_info['id'],
//             'user_number'      => $user_number,//没有用只是记录 读取的时候按营业厅id
            'device_unique_id' => $device_unique_id
        ];

         if ( $content ) {
             $content = json_decode(htmlspecialchars_decode($content), true);
         }

         $result = $this->handle_content($content['content']);

        api_helper::return_api_data(1000, 'success', array ( 'info' => 'ok' ), $api_log_id);
    }

    /**
     * 数据处理代码块
     * @param unknown $conetnt
     * @param unknown $filter
     */
    private function handle_content($conetnt)
    {
        if ( !$conetnt ) {
            $this->create_db_behave($this->filter);

            return true;
        }

        foreach ($conetnt as $k => $v) {

            if ( !$v ) continue;

            if ( isset($v['record_time']) && $v['record_time'] ) {
                $this->filter['record_day'] = $v['record_time'];
            }

            // 记录每天的设备ID 跑计划任务使用
            $this->create_db_behave_device_record($this->filter);

            if ( !isset($v['content']) ) {
                $this->create_db_behave($this->filter);
                continue;
            }

            $this->handle_device_behave($v['content']);
        }

        return true;
    }

    /**
     * $this->device_behave行为的操作
     * @param unknown $conetnt
     * @param unknown $filter
     */
    private function handle_device_behave($content)
    {
        foreach ($this->device_behave as $k => $v) {
            // 数组为空就跳过
            if ( !isset($content[$v]) || !$content[$v] ) {
                continue;
            }

            // 除了历史记录都是一样的数
            if ( 'report_record' != $v ) {
                foreach ( $content[$v] as $key => $val ) {
                    // 验证字段
                    $new_filter = [];
                    $new_filter = $this->filter;

                    // 判断参数字段
                    foreach ( $this->device_behave_param[$v] as  $param_val) {
                        $new_filter[$param_val] = isset($val[$param_val]) ? $val[$param_val] : '';
                    }

                    $new_filter['date'] = date('Ymd');
                    $new_filter['behave_type'] = $k;
                    $result = $this->create_db_behave($new_filter);
                }

                // 是历史记录完成，不走下面流程了
                continue;
            }

            $report_record_filter = [];
            $report_record_filter = $this->filter;

            $report_record_filter['time'] = isset($content[$v]['time']) ? $content[$v]['time'] : '';
            $report_record_filter['report_state'] = isset($content[$v]['report_state']) ? $content[$v]['report_state'] : '';

            $report_record_filter['date'] = date('Ymd');

            $report_record_filter['behave_type']  = $k;

            $id = $this->create_db_behave($report_record_filter);

            if (isset($content[$v]['content']) && $content[$v]['content']) {
                foreach ($content[$v]['content'] as $value) {
                    //  添加数据
                    $this->create_db_behave_date_record(
                            array(
                                'daily_behave_id' => $id,
                                'date'            => $value
                                )
                    );
                }
            }

        }

        return true;
    }

    /**
     * 数据库操作代码块
     * @param unknown $conetnt
     * @param unknown $filter
     */
    private function create_db_behave($filter)
    {
        $id = _model('screen_daily_hebave_record')->create($filter);
        return $id;
    }
    private function create_db_behave_date_record($filter)
    {
        _model('screen_daily_hebave_report_date_record')->create($filter);
        return true;
    }
    private function create_db_behave_device_record($filter)
    {
        $filter['date'] = date('Ymd');
        $device_info = _model('screen_daily_hebave_device_record')->read($filter);

        if ( !$device_info ) {
            _model('screen_daily_hebave_device_record')->create($filter);
        }

        return true;
    }
}