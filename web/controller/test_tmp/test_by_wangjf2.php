<?php
/**
  * alltosun.com  test_by_wangjf2.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年11月10日 下午10:49:05 $
  * $Id$
  */

require ROOT_PATH.'/helper/MyRdKafka.php';
class Action
{
    /*
     * 判断图片是否为动态图片(动画)
     */
    private function isAnimatedGif($filename) {
        $fp=fopen($filename,'rb');
        $filecontent=fread($fp,filesize($filename));
        fclose($fp);

        p(chr(0x21).chr(0xff).chr(0x0b).'NETSCAPE2.0');
        p(strpos($filecontent,chr(0x21).chr(0xff).chr(0x0b).'NETSCAPE2.0')===FALSE?0:1);

    }

    private function isAnimatedGif2()
    {
        $res = $this->isAnimatedGif(ROOT_PATH.'/images/data/testGif8.gif');
        p($res);
    }

    private function createIndex()
    {
        $db         = tools_helper::get('db', '');
        $collection = tools_helper::get('collection', '');
        $sort = tools_helper::get('sort', 1); //1 升序 -1 倒序
        $field = tools_helper::get('field', ''); //1 升序 -1 倒序

        if ( !$db || !$collection || !in_array($sort, array(1, -1)) || !$field){
             echo '参数不全';
             exit();
        }

        $res = _mongo($db, $collection)->createIndex(array($field => $sort));
        p($res);
    }



    private function test()
    {

        $a =$this->shorten('http://201512awifiprobe.alltosun.net/screen_stat/admin/device_stat/device_list/device?search_filter[date_type]=0&search_filter[date_type]=2&search_filter[region_id]=1&search_filter[region_type]=province&type=1&is_group=0');
        p($a);
    }

    /**
     * 更新版本号
     */
    private function update_version_no()
    {
        $filter = array(
                'api_path' => '/screen/api/3/phone/add_device_info'
        );

        $requests = _model('api_log')->getFields('request_params', $filter, ' ORDER BY `id` DESC');

        //已经更新的设备
        $update_version = array();

        foreach ($requests as $k => $v) {
            $arr = json_decode($v, true);

            $arr = $arr['post'];

            $device_unique_id = screen_helper::get_device_unique_id($arr['phone_mac'], $arr['phone_imei']);
//p($arr);exit;
            //因为是倒序取出来，已经更新，跳过
            if (isset($update_version[$device_unique_id])) {
                continue;
            }

            if (!$device_unique_id) {
                echo 'device_unique_id不能为空';
                p($arr);
                continue;
            }

            $device = screen_device_helper::get_device_info_by_device($device_unique_id);

            $device_info = _model('screen_device')->read(array('device_unique_id' => $device_unique_id), ' ORDER BY id DESC LIMIT 1 ');

            if (!$device_info) {
                echo '设备不存在';
                p($arr);
                continue;
            }

            $res = _model('screen_device')->update($device_info['id'], array('version_no' => $arr['version']));

            $update_version[$device_unique_id] = '';

            p($res);
        }

    }

    /**
     * 获取RFID 和 亮屏体验时长排行数据
     */
    private function get_rfid_screen_top_data()
    {
        $type = tools_helper::Get('type', 1);
        $limit = tools_helper::Get('limit', 10);

        //查询rfid
        $start_date = 20171201;
        $end_date   = 20171231;

        $start_date = tools_helper::Get('start_date', 20180101);
        $end_date   = tools_helper::Get('end_date', 20180131);;

        if ($type == 1) {

            //rfid数据
            $field = 'experience_time';
            $rfid_filter = array('date >' => $start_date, 'date <=' => $end_date);
            $where = rfid_helper::to_where_sql($rfid_filter);
            $sql =  'SELECT `phone_name`, `phone_version`, SUM(`experience_time`) as experience_times FROM rfid_record '.$where.' GROUP BY `phone_name`, `phone_version` ORDER BY `experience_times` DESC LIMIT '.$limit;

        } else if ($type == 2) {

            $field = 'action_num';
            //rfid数据
            $rfid_filter = array('date >' => $start_date, 'date <=' => $end_date, 'status' => 1, 'end_timestamp >' => 1);
            $where = rfid_helper::to_where_sql($rfid_filter);
            $sql =  'SELECT `phone_name`, `phone_version`, COUNT(*) as action_nums FROM rfid_record_detail '.$where.' GROUP BY `phone_name`, `phone_version` ORDER BY `action_nums` DESC LIMIT '.$limit;

        } else {
            echo '暂不支持的类型';
            exit();
        }

        //rfid数据
        $rfid_data = _model('rfid_record')->getAll($sql);

        //查询设备数
        foreach ($rfid_data as $k => $v) {
            $rfid_filter2 = array(
                    'date >' => $start_date,
                    'date <=' => $end_date,
                    'phone_name' => $v['phone_name'],
                    'phone_version' => $v['phone_version']
            );

            $ids = _model('rfid_record')->getFields('id', $rfid_filter2, ' GROUP BY `phone_name`, `phone_version`, `business_id`, `label_id` ');

            $rfid_data[$k]['device_num'] = count($ids);
        }

        //亮屏数据
        $screen_filter = array('day >' => $start_date, 'day <=' => $end_date);
        $result       = _mongo('screen', 'screen_device_stat_day')->aggregate(array(
                array('$match' => get_mongodb_filter($screen_filter)),
                array('$group' => array(
                        '_id'       => array('device_unique_id'  => '$device_unique_id'),
                        $field.'s'     => array('$sum' => '$'.$field),
                        'unique_id' => array('$first' => '$device_unique_id'),
                )),
        ));

        $tmp = array();
        $new_field = $field.'s';
        $orders     = array();

        foreach ($result as $k => $v) {

            $v = (array)$v;

            //查询品牌
            $device_info = screen_device_helper::get_device_info_by_device($v['unique_id']);

            if ( !$device_info ){
                continue;
            }

            $key = $device_info['phone_name'].'|@|'.$device_info['phone_version'];


            if (isset($tmp[$key])) {
                $tmp[$key][$new_field] += $v[$new_field];
                $orders[$key] = $tmp[$key][$new_field];
                $tmp[$key]['device_nums']++;

            } else {
                $orders[$key] = $tmp[$key][$new_field]      = $v[$new_field];
                $tmp[$key]['device_nums']   = 1;
            }
        }

        if ($tmp) {
             array_multisort($orders, SORT_DESC, $tmp );
        }

        //取前十条
        $tmp = array_slice($tmp, 0, $limit);
        $screen_data = array();
        foreach ($tmp as $k => $v) {
            list($phone_name, $phone_version) = explode('|@|', $k);
            $screen_data[]  = array(
                    'phone_name'        => $phone_name,
                    'phone_version'     => $phone_version,
                    $new_field          => $v[$new_field],
                    'device_nums'       => $v['device_nums'],
                    'from'              => 'screen'
            );
        }

        $data = array_merge_recursive($rfid_data, $screen_data);
//p($data);exit;
        //排序
        $new_data = array();
        $orders   = array();
        foreach ($data as $k => $v) {
            $orders[] = $v[$new_field];
        }
        if ($data) {
            array_multisort($orders, SORT_DESC, $data );
            $data = array_slice($data, 0, $limit);
        }

        //导出数据拼装
        $export_data = array();
        foreach ($data as $k => $v) {
            if (!empty($v['from']) && $v['from'] == 'screen') {
                $v['from'] = '亮靓';
            } else {
                $v['from'] = 'RFID';
            }

            if ($type == 1) {
                $v['experience_times'] = rfid_helper::format_timestamp_text($v['experience_times']);
            }

            $export_data[] = $v;
        }

        if ($type == 1) {
            $params['filename'] = $start_date.'至'.$end_date.'亮靓/RFID体验时长排行表';
            $params['head']     = array('品牌', '型号', '体验总时长', '设备数', '来源');
        } else {
            $params['filename'] = $start_date.'至'.$end_date.'亮靓/RFID体验次数排行表';
            $params['head']     = array('品牌', '型号', '体验总次数', '设备数', '来源');
        }

        $params['data']     = $export_data;

        Csv::getCvsObj($params)->export();
    }


    /**
     * 获取RFID 和 亮屏体验时长排行数据
     */
    private function get_rfid_screen_top_data2()
    {
        $type = tools_helper::Get('type', 1);
        $limit = tools_helper::Get('limit', 10);

        //查询rfid
        $start_date = 20171201;
        $end_date   = 20171231;

        $start_date = tools_helper::Get('start_date', 20180101);
        $end_date   = tools_helper::Get('end_date', 20180131);;

        if ($type == 1) {

            //rfid数据
            $field = 'experience_time';
            $rfid_filter = array('date >' => $start_date, 'date <=' => $end_date);
            $where = rfid_helper::to_where_sql($rfid_filter);
            $sql =  'SELECT `phone_name`, `phone_version`, `label_id`, `business_id`, SUM(`experience_time`) as experience_times, COUNT(*) as day_nums FROM rfid_record '.$where.' GROUP BY `phone_name`, `phone_version`, `label_id`, `business_id` ORDER BY `experience_times` DESC LIMIT '.$limit;

        } else if ($type == 2) {

            $field = 'action_num';
            //rfid数据
            $rfid_filter = array('date >' => $start_date, 'date <=' => $end_date, 'status' => 1, 'end_timestamp >' => 1);
            $where = rfid_helper::to_where_sql($rfid_filter);
            $sql =  ' SELECT `phone_name`, `phone_version`, `label_id`, `business_id`, SUM(`action_num`) as `action_nums`, COUNT(*) as day_nums FROM ( SELECT `phone_name`, `phone_version`, `label_id`, `business_id`, COUNT(*) as action_num FROM rfid_record_detail '.$where.' GROUP BY `phone_name`, `phone_version`, `label_id`, `business_id`, `date` ) as from_sql GROUP BY `phone_name`, `phone_version`, `label_id`, `business_id` ORDER BY `action_nums` DESC LIMIT '.$limit;

        } else {
            echo '暂不支持的类型';
            exit();
        }

        //rfid数据
        $rfid_data = _model('rfid_record')->getAll($sql);
//p($rfid_data);
        //亮屏数据
        $screen_filter = array('day >' => $start_date, 'day <=' => $end_date);
        $result       = _mongo('screen', 'screen_device_stat_day')->aggregate(array(
                array('$match' => get_mongodb_filter($screen_filter)),
                array('$group' => array(
                        '_id'           => array('device_unique_id'  => '$device_unique_id', 'business_id' => '$business_id'),
                        $field.'s'      => array('$sum' => '$'.$field),
                        'unique_id'     => array('$first' => '$device_unique_id'),
                        'business_id'   => array('$last' => '$business_id'),
                        'day_nums'      => array('$sum' => 1),
                )),
        ));

        $new_field = $field.'s';
        $orders     = array();

        $tmp = array();
        foreach ($result as $k => $v) {
            $tmp[] = $v;
            $orders[] = $v[$new_field];
        }

        if ($tmp) {
            array_multisort($orders, SORT_DESC, $tmp );
        }

        //取前十条
        $tmp = array_slice($tmp, 0, $limit);
        $screen_data = array();
        foreach ($tmp as $k => $v) {
            $v = (array)$v;
            $screen_data[]  = array(
                    'device_unique_id'  => $v['unique_id'],
                    'business_id'       => $v['business_id'],
                    $new_field          => $v[$new_field],
                    'day_nums'          => $v['day_nums'],
                    'from'              => 'screen'
            );
        }

        $data = array_merge_recursive($rfid_data, $screen_data);

        //排序
        $new_data = array();
        $orders   = array();
        foreach ($data as $k => $v) {
            $orders[] = $v[$new_field];
        }
        if ($data) {
            array_multisort($orders, SORT_DESC, $data );
            $data = array_slice($data, 0, $limit);
        }

        //导出数据拼装
        $export_data = array();
        foreach ($data as $k => $v) {
            $tmp = array();
            //查询厅
            $business_info = _model('business_hall')->read($v['business_id']);
            if (!$business_info) {
                continue;
            }

            $average  = round($v[$new_field]/$v['day_nums']);

            //查询省市区
            $tmp['province'] = business_hall_helper::get_info_name('province', $business_info['province_id'], 'name');
            $tmp['city']     = business_hall_helper::get_info_name('city', $business_info['city_id'], 'name');
            $tmp['area ']    = business_hall_helper::get_info_name('area', $business_info['area_id'], 'name');
            $tmp['business_hall'] = $business_info['title'];

            if (!empty($v['from']) && $v['from'] == 'screen') {
                $device_info = screen_device_helper::get_device_info_by_device($v['device_unique_id']);
                if (!$device_info){
                    continue;
                }

                $tmp['phone_name']     = $device_info['phone_name'];
                $tmp['phone_version']  = $device_info['phone_version'];
                $tmp['device_unique_id'] = $v['device_unique_id'];

                $tmp['from'] = '亮靓';
            } else {

                $tmp['phone_name']     = $v['phone_name'];
                $tmp['phone_version']  = $v['phone_version'];
                $tmp['label_id']       = $v['label_id'];

                $tmp['from'] = 'RFID';
            }

            if ($type == 1) {
                $tmp[$new_field]    = rfid_helper::format_timestamp_text($v[$new_field]);
                $tmp['average']     = rfid_helper::format_timestamp_text($average);
            } else {
                $tmp[$new_field]    = $v[$new_field];
                $tmp['average']     = $average;
            }

            //活跃天数
            $tmp['day_nums'] = $v['day_nums'].'天';


            $export_data[] = $tmp;
        }

        if ($type == 1) {
            $params['filename'] = $start_date.'至'.$end_date.'亮靓/RFID体验时长排行表-设备';
            $params['head']     = array('省', '市', '区', '厅', '品牌', '型号', '设备/标签', '来源', '体验总时长', '平均体验时长', '活跃天数' );
        } else {
            $params['filename'] = $start_date.'至'.$end_date.'亮屏/RFID体验次数排行表';
            $params['head']     = array('省', '市', '区', '厅', '品牌', '型号', '设备/标签', '来源', '体验总次数', '平均体验次数', '活跃天数' );
        }

        $params['data']     = $export_data;

        Csv::getCvsObj($params)->export();
    }

    /**
     * 更新营业厅
     */
    private function update_business_hall()
    {
        $user_number1 = tools_helper::Get('user_number1', '1101001902792');
        $user_number2 = tools_helper::Get('user_number2', '1101051002033');

        $business_info1 = _model('business_hall')->read(array('user_number' => $user_number1));
        $business_info2 = _model('business_hall')->read(array('user_number' => $user_number2));

        if (!$business_info1 || !$business_info2) {
            echo '数据不存在';
            exit();
        }
        //删除厅
        _model('business_hall')->delete($business_info1['id']);

        unset($business_info1['id']);
        unset($business_info1['add_time']);
        unset($business_info1['update_time']);

        _model('business_hall')->update($business_info2['id'], $business_info1);

        //先删除冗余的 member
        _model('member')->delete(array('member_user' => $user_number1));

        //更新
        _model('member')->update(array('member_user' => $business_info2['user_number']), array('member_user' => $user_number1));
    }

    public function video_test()
    {
//         $file = _widget('screen_content.video')->compress_video(UPLOAD_PATH.'/video/2018/02/27/20180227153143000000_1_43.mp4', UPLOAD_PATH.'/video/2018/02/27/20180227153143000000_1_43_compress.mp4');
//         echo '<video src="'.$file.'" controls = "true"></video>';


        $res = array();
        $no = exec('cat '.UPLOAD_PATH.'/txt_upload/content_stat/2018/04/27/20180427151759000000_1_2.txt 2>&1', $res);
        //p($no);
        p($res);
    }

    /**
     * 下载文件
     */
    public function upload_file()
    {
        $file_name = UPLOAD_PATH.'/txt_upload/content_stat/2018/04/27/20180427151759000000_1_2.txt';     //下载文件名

        //检查文件是否存在
        if (! file_exists ($file_name)) {
            echo "文件找不到";
            exit ();
        } else {
            //打开文件
            $file = fopen ($file_name, "r");
            //输入文件标签
            Header ( "Content-type: application/octet-stream" );
            Header ( "Accept-Ranges: bytes" );
            Header ( "Accept-Length: " . filesize ($file_name) );
            Header ( "Content-Disposition: attachment; filename=" . $file_name );
            //输出文件内容
            //读取文件内容并直接输出到浏览器
            echo fread ( $file, filesize ( $file_name ) );
            fclose ( $file );
            exit ();
        }
    }

    /**
     * 播放视频
     */
    private function video_play()
    {
        echo '<video controls="true" >
                <source src="/upload/video/2018/03/01/20180301152000000000_1_87_compress.mp4" type="video/mp4">
            </video>';
        //echo '<video src="" type="video/mp4"></video>';
    }

    // 取
    public function kafka_test()
    {
        $kf = new MyRdkafka();

        $consumer = $kf->get_consumer(array('screen-api-3-content-content_stat-add_content_stat'));

        $key = 1;
        while ($key < 5) {
            //消费消息并触发回调  参数：超时时间
            $message = $consumer->consume(60);
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                   $param = explode('<br>', rtrim($message->payload, '<br>'));
                    foreach ($param as $k => $v) {
                        $arr = json_decode($v, true);
                        p(_widget('screen_content.kafka')->add_content_stat($arr));
                    }
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    echo "没有更多消息<br>";
                    break;
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    echo "超时<br>";
                    break;
                default:
                    MyLogger::kafkaLog()->error($message->errstr().var_export($message->err, true), array('path' => 'consume_$message'));
                    throw new \Exception($message->errstr(), $message->err);
                    break;
            }

            //延迟1秒执行
            sleep(1);

            $key++;
            //break;
        }

    }

    public function elk_test()
    {
         MyLogger::kafkaLog()->info("kafka日志测试1", array('path' => 'test'));
    }

}