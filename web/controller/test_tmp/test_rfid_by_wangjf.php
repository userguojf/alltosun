<?php
/**
  * alltosun.com 测试rfid test_rfid_by_wangjf.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年12月27日 上午11:09:19 $
  * $Id$
  */
set_time_limit(0);

//config
include MODULE_PATH.'/rfid/server/config.php';
//放缓存
include MODULE_PATH.'/rfid/server/lib/RedisCache.php';

class Action
{
    /**
     * 单点登录
     */
    private function login_test()
    {

        $timestamp = time();
        $appid = 'wifi_shujdt_awzdxhyadrtggbrd';
        $appkey = 'd1cb99814ddc2d11cdd8c099b6e5c6e8';
        $token = md5($appid.'_'.$appkey.'_'.$timestamp);
        //$url = 'http://wifi.pzclub.cn/api/member/login?timestamp='.$timestamp.'&token='.$token.'&account=beijing_YYT&appid='.$appid;
        //$url = 'http://wifi.pzclub.cn/api/member/login?timestamp='.$timestamp.'&token='.$token.'&account=admin&appid='.$appid.'&action=rfid&label_id=03003C26&business_code=1101021002051';
        //$url = "http://wifi.pzclub.cn/api/member/login?timestamp={$timestamp}&token={$token}&account=1101021002051&appid={$appid}&action=screen&device_code=316b591db813c";
        //$url = "http://wifi.pzclub.cn/api/member/login?timestamp={$timestamp}&token={$token}&account=1101021002051&appid={$appid}&action=rfid&label_id=03003C48&is_mobile=1";
        $uri = urlencode('http://mac.pzclub.cn/e/admin/rfid/stat');
        //$uri = 'http://201512awifiprobe.alltosun.net/probe_dev/admin';
        //$uri = urlencode($uri);
        //$url = SITE_URL."/api/member/login?timestamp={$timestamp}&token={$token}&account=1101021002051&appid={$appid}&redirect_uri={$uri}&action=rfid";
        $uri = urlencode("http://201711awifiprobe.alltosun.net/screen_dm/stat");
        $url = SITE_URL."/api/member/login?timestamp={$timestamp}&token={$token}&account=admin&appid={$appid}&action=redirect_uri&redirect_uri=".$uri;
        //$url = "http://mac.pzclub.cn/api/member/login?timestamp={$timestamp}&token={$token}&account=1101001011340&appid={$appid}&action=screen";
        echo htmlentities($url);exit;

        Response::redirect($url);
        //echo md5('wifi_shujdt_awzdxhyadrtggbrd');
    }

    private function fsockopen_test() {
        $type = tools_helper::get('type', 1);
        $date = date('YmdHis');
        $id   = '000000C1';

        if($fp =fsockopen('tcp://rfid.pzclub.cn', 8080, $errno, $errstr,20)){
            //var_dump($fp);
            //建立成功后，向服务器写入数据

            $send = "ID:{$id}
            MAC:2F54014BAEC9
            DATE:{$date}
            RSSI:-53
            F-TYPE:02
            MOV:0{$type}
            BAT:00";

            //测试-增值营业厅  1801121D
            $send = "action_id:2
scanner_id:d8b04cd86c38
label_id:label1801121D
mac:1a91878306f5
type:{$type}
timestamp:1516780019
rssi:-74
bat:1";

            fwrite($fp,$send);
            //检索HTTP状态码
            $data = fgets($fp,128);
            //关闭连接
            fclose($fp);
            p($data);

        }else{
            //没有连接
            echo '连接失败';
        }
    }

    private function fsockopen_test2() {
        $type = tools_helper::get('type', 1);
        $date = date('YmdHis');
        $id   = '1797001A';

        if($fp =fsockopen('tcp://localhost', 19090, $errno, $errstr,20)){
            //var_dump($fp);
            //建立成功后，向服务器写入数据

            $send = "ID:{$id}
            MAC:2F54014BAEC9
            DATE:{$date}
            RSSI:-53
            F-TYPE:02
            MOV:0{$type}
            BAT:00";

            fwrite($fp,$send);
            //检索HTTP状态码
            $data = fgets($fp,128);
            //关闭连接
            fclose($fp);
            p($data);

        }else{
            //没有连接
            echo '连接失败';
        }
    }



    /**
     * 设置需要修复的rfid数据到mc
     */
    private function set_rfid_repair()
    {

        global $mc_wr;
        $mc_wr->delete('rfid_repair_data');

        $file_name = tools_helper::Get('file_name', '');

        $file_content = file_get_contents(ROOT_PATH.'/images/data/rfid/'.$file_name);
        //$file_content = file_get_contents(ROOT_PATH.'/images/data/rfid/common2_20180118.log');

        $look = tools_helper::Get('look', 0);

        $content = explode("-------------\n",$file_content);

        $arr = array();
        $err_arr = array();
        $h_arr   = array();
        $not = array();
        foreach ($content as $k => $v) {

            if (strpos($v, 'ID') === false) {
                continue;
            }

            //不存在DATE
            if (strpos($v, 'SN') !== false) {
                $not[] = $v;
                continue;
            }

            $new_v = explode("\n", trim($v));
            $tmp = array();

            foreach ($new_v as $k1 => $v1) {

                if ($k1 == 7) {
                    break;
                }

                $new_v1 = explode(":", $v1);
                if (empty($new_v1[1])) {
                    p($new_v);
                    break;
                }

                list($key, $value) = explode(":", $v1);
                $tmp[trim($key)] = trim($value);
            }

            if (count($tmp) != 7) {
                $err_arr[] = $v;
                continue;
            }

            //心跳包
            if ($tmp['F-TYPE'] == '01') {
                $h_arr[] = $v;
                continue;
            }

            $arr[] = $tmp;
        }


        if ($look) {
//             p($arr);
//             p($err_arr);
//             p($h_arr);exit;
        }

        $page = tools_helper::Get('page', 1);
        $max_page = 10000;

        //分页
        $limit_start = ($page - 1)*$max_page;

        $new_data = array_slice($arr, $limit_start, $max_page);
        //p($new_data);exit;
        if (!$new_data) {
            echo '已设置完毕';
            exit();
        }

        //$mc_wr->set('rfid_repair_error', $err_arr, 3600*24);
        $mc_wr->set('rfid_repair_data', $new_data, 3600*24);
        if ($look) {
            p($mc_wr->get('rfid_repair_data'));
            $mc_wr->set('wangjf_test', '123etest', 3600*24);
        }


    }

    /**
     * 修复处理
     */
    private function rfid_repair_handle()
    {
        global $mc_wr;
        //$data = $mc_wr ->delete('rfid_repair_data');
        $data = $mc_wr->get('rfid_repair_data');

        if (!$data) {
            echo 'data为空';exit;
        }

        $page = tools_helper::Get('page', 1);
        $max_page = 100;

        //分页
        $limit_start = ($page - 1)*$max_page;

        $new_data = array_slice($data, $limit_start, $max_page);

//p($new_data);exit;
        if (!$new_data) {
            echo '已修复完毕';
            exit();
        }

        foreach ($new_data as $k => $v) {
            //p($v);
            //F-TYPE 01 为心跳包
            if ($v['F-TYPE'] == '02') {
                //拿起
                if ($v['MOV'] === '01') {
                    $result = $this->repair_up($v);
                    //放下
                } else if ($v['MOV'] === '00') {
                    $result = $this->repair_down($v);
                } else {
                    continue;
                }

                if ($result != 'success') {
                    p($v);
                    p($result);
                    echo '<hr />';
                }
            }
        }

        ++$page;
        $url = SITE_URL.'/test_rfid_by_wangjf/rfid_repair_handle?page='.$page;
        echo '<script>window.location.href="'.$url.'"</script>';

    }

    /**
     * 修复拿起数据
     * @param unknown $arr
     */
    private function repair_up($arr)
    {

        $label_id = $arr['ID'];
        //查询标签详情
        $label_info = _model('rfid_label')->read(array('label_id' => $label_id));

        if (!$label_info) {
            return 'success';
        }

        $start_timestamp = strtotime($arr['DATE']);

        $record_info = _model('rfid_record_detail')->read(array('label_id' => $label_id, 'start_timestamp' => $start_timestamp), ' ORDER BY `id` DESC LIMIT 1 ');

        //此条动作已存在
        if ($record_info) {
            return 'success';
        }

        //查询数据库未完成的
        $record_info = _model('rfid_record_detail')->read(array('label_id' => $label_id, 'end_timestamp' => 0, 'status' => 1), ' ORDER BY `id` DESC LIMIT 1 ');

        if ($record_info) {
            //如果时间差小于2,则认为不是新动作
            if (($start_timestamp - $record_info['start_timestamp']) < 60) {
                return 'success';
            }
        }

        //创建动作
        $new_data = array(
                'label_id'        => $label_id,
                'date'            => date('Ymd', $start_timestamp),
                'mac'             => $arr['MAC'],
                'start_timestamp' => $start_timestamp,
                'rssi'            => $arr['RSSI'],
                'province_id'     => $label_info['province_id'],
                'city_id'         => $label_info['city_id'],
                'area_id'         => $label_info['area_id'],
                'business_id'     => $label_info['business_hall_id'],
                'phone_name'      => $label_info['name'],
                'phone_version'   => $label_info['version'],
                'phone_color'     => $label_info['color'],
                'add_time'        => date('Y-m-d H:i:s')
        );

        $id = _model('rfid_record_detail')->create($new_data);
        return 'success';
    }

    /**
     * 修复拿起数据
     * @param unknown $arr
     */
    private function repair_down($arr)
    {
        $label_id = $arr['ID'];

        $label_info = _model('rfid_label')->read(array('label_id' => $label_id));

        if (!$label_info) {
            return 'success';
        }

        $end_timestamp = strtotime($arr['DATE']);


        $start_record_info = _model('rfid_record_detail')->read(array('label_id' => $label_id, 'end_timestamp' => 0, 'status' => 1), ' ORDER BY `id` DESC LIMIT 1 ');
//p(array('label_id' => $label_id, 'end_timestamp' => 0, 'status' => 1));
        //拿起动作不存在
        if (!$start_record_info) {
            return 'success';
        }

        $status = 1;

        $remain_time = $end_timestamp - $start_record_info['start_timestamp'];

        //体验不达标
        if ($remain_time < 3) {
            $status = -2;
        }

        //超时
        if ($remain_time > 60) {
            $status = -1;
        }

        $update_data = array(
                'end_timestamp' => $end_timestamp,
                'rssi'          => $arr['RSSI'],
                'remain_time'   => $remain_time,
                'status'        => $status
        );

        $res = _model('rfid_record_detail')->update($start_record_info['id'], $update_data);

        if ($status != 1) {
            return 'success';
        }

        //统计
        $record_detail = array_merge($start_record_info, $update_data);

        //更新统计
        $result = $this->update_stat($record_detail);

        return $result;
    }

    /**
     * 更新统计
     * @param unknown $record_detail
     * @return unknown[]|number[][]|string[][]|string
     */
    private function update_stat($record_detail)
    {
        //更新record
        $update_stat = $this->update_record($record_detail);

        if ($update_stat != 'success') {
            return $update_stat;
        }

        //更新天数据
        $update_stat = $this->update_day_stat($record_detail);

        if ($update_stat != 'success') {
            return $update_stat;
        }

        //更新小时数据
        $update_stat = $this->update_stat_hour($record_detail);

        if ($update_stat != 'success') {
            return $update_stat;
        }

        return 'success';

    }

    /**
     * 更新记录表
     * @param unknown $rfid_record_detail 记录详情
     */
    private function update_record($rfid_record_detail)
    {

        if (!$rfid_record_detail) {
            return  '更新记录时详情不存在[1]';
        }


        $filter = array(
                'business_id'   => $rfid_record_detail['business_id'],
                'area_id'       => $rfid_record_detail['area_id'],
                'city_id'       => $rfid_record_detail['city_id'],
                'province_id'   => $rfid_record_detail['province_id'],
                'phone_name'    => $rfid_record_detail['phone_name'],
                'phone_version' => $rfid_record_detail['phone_version'],
                'phone_color'   => $rfid_record_detail['phone_color'],
                'label_id'      => $rfid_record_detail['label_id'],
                'date'          => date('Ymd', $rfid_record_detail['start_timestamp'])
        );

        $rfid_record_info = _model('rfid_record')->read($filter);

        //为提高容错率，取消叠加方式, 直接按天统计体验时长
        $detail_filter = $filter;
        $detail_filter['status'] = 1;
        $detail_filter['end_timestamp >'] = 0;


        $remain_times = _model('rfid_record_detail')->getFields('remain_time', $detail_filter);
        $remain_times = array_sum($remain_times);

        //存在当日的记录
        if ($rfid_record_info) {
            $update_data = array(
                    'experience_time' => $remain_times
            );

            //查询设备数
           _model('rfid_record')->update($rfid_record_info['id'], $update_data);
           return 'success';
            //不存在当日的记录
        } else {
            $new_data = $filter;
            $new_data['experience_time'] = $remain_times;
            $new_data['add_time'] = date('Y-m-d H:i:s');
            $id = _model('rfid_record')->create($new_data);
            return 'success';
        }
    }

    /**
     * 实时更新统计
     * @param unknown $rfid_record_detail 记录详情
     * @return number[]|string[]|unknown|boolean
     */
    private function update_day_stat($rfid_record_detail)
    {

        if (!$rfid_record_detail) {
            return '动作记录不能为空';
        }

        $filter = array(
                'date_for_day' => date('Ymd', $rfid_record_detail['start_timestamp']),
                'business_id'  => $rfid_record_detail['business_id']
        );

        //stat表
        $stat_info = _model('rfid_stat')->read($filter);

        //为提高容错率，取消叠加方式, 直接按天统计体验时长
        $record_filter = array(
                'date'        => $filter['date_for_day'],
                'business_id' => $rfid_record_detail['business_id'],
        );

        $experience_times       = _model('rfid_record')->getFields('experience_time', $record_filter);
        $new_experience_time    = array_sum($experience_times);
        $device_num             = count($experience_times);

        if (!$stat_info) {
            //创建
            $new_data = $filter;
            $new_data['province_id'] = $rfid_record_detail['province_id'];
            $new_data['city_id'] = $rfid_record_detail['city_id'];
            $new_data['area_id'] = $rfid_record_detail['area_id'];
            $new_data['date_for_week'] = (int)(date('Y', $rfid_record_detail['start_timestamp']) . date('W', $rfid_record_detail['start_timestamp']));
            $new_data['date_for_month'] = (int)(date('Ym', $rfid_record_detail['start_timestamp']));
            $new_data['device_num'] = $device_num;
            $new_data['experience_time'] = $new_experience_time;
            _model('rfid_stat')->create($new_data);

            return 'success';
        } else {
            //更新
            $update_data['device_num']      = $device_num;
            $update_data['experience_time'] = $new_experience_time;
            _model('rfid_stat')->update($stat_info['id'], $update_data);
            return 'success';
        }
    }

    /**
     * 更新按小时的统计
     * @param unknown $rfid_record_detail
     */
    private function update_stat_hour($rfid_record_detail)
    {

        if (!$rfid_record_detail) {
            return 'rfid记录不能为空';
        }

        $filter = array(
                'date_for_day'  => (int)date('Ymd', $rfid_record_detail['start_timestamp']),
                'date_for_hour' => (int)date('H', $rfid_record_detail['start_timestamp']),
                'business_id'   => (int)$rfid_record_detail['business_id']
        );

        //为提高容错率，取消叠加方式, 直接按天统计体验时长
        $detail_filter = array(
                'date'               => $filter['date_for_day'],
                'business_id'        => $rfid_record_detail['business_id'],
                'start_timestamp >=' => strtotime(date('Y-m-d H:00:00', $rfid_record_detail['start_timestamp'])),
                'start_timestamp <=' => strtotime(date('Y-m-d H:59:59', $rfid_record_detail['start_timestamp'])),
                'status'             => 1,
                'end_timestamp >'    => 0
        );

        $remain_times = _model('rfid_record_detail')->getFields( 'remain_time', $detail_filter);
        $remain_times = array_sum($remain_times);

        //hour表
        $hour_stat_info = _model('rfid_stat_hour')->read($filter);

        //创建或更新hour统计表
        if (!$hour_stat_info) {
            //创建
            $new_data = $filter;
            $new_data['province_id'] = $rfid_record_detail['province_id'];
            $new_data['city_id'] = $rfid_record_detail['city_id'];
            $new_data['area_id'] = $rfid_record_detail['area_id'];
            $new_data['date_for_week'] = date('Y', $rfid_record_detail['start_timestamp']) . date('W', $rfid_record_detail['start_timestamp']);
            $new_data['date_for_month'] = date('Ym', $rfid_record_detail['start_timestamp']);
            $new_data['device_num'] = 1;
            $new_data['experience_time'] = $remain_times;
            _model('rfid_stat_hour')->create($new_data);
            return 'success';
        } else {

            $label_ids = _model('rfid_record_detail')->getFields('label_id', $detail_filter, ' GROUP BY label_id, phone_name, phone_version, phone_color ');
            $count = count($label_ids);
            //更新
            $update_data = array();
            $update_data['device_num'] = $count;
            $update_data['experience_time'] = $remain_times;
            _model('rfid_stat_hour')->update($hour_stat_info['id'], $update_data);
            return 'success';
        }
    }

    private function delete_detail()
    {
//         _model('rfid_record_detail')->delete(array(1=>1));
//         _model('rfid_record')->delete(array(1=>1));
//         _model('rfid_stat')->delete(array(1=>1));
//         _model('rfid_stat_hour')->delete(array(1=>1));

    }



    /**
     * 查看已更新的设备昵称
     */
    private function get_updated_rfid_label()
    {
        //查询所有RFID设备
        $sql = ' SELECT `name`,`version` FROM `rfid_label` WHERE `device_nickname_id` > 0 GROUP BY `name`, `version` ';
        $rfid_device = _model('rfid_label')->getAll($sql);
        p($rfid_device);
    }

    /**
     * 查看未更新的设备昵称
     */
    private function get_not_updated_rfid_label()
    {
        //查询所有RFID设备
        $sql = ' SELECT `name`,`version` FROM `rfid_label` WHERE `device_nickname_id` = 0 GROUP BY `name`, `version` ';
        $rfid_device = _model('rfid_label')->getAll($sql);
        p($rfid_device);
    }

    /**
     * 查询更新IMEI末六位一样的机型
     */
    private function update_device_nickname_id()
    {
        $look = tools_helper::Get('look', 1);

        //查询所有RFID设备
        $rfid_device = _model('rfid_label')->getList(array('device_nickname_id' => 0));

        foreach ($rfid_device as $k => $v) {
            //查询imei
            $filter = array(
                    'business_id' => $v['business_hall_id'],
                    'imei LIKE'   => '%'.$v['imei'],
                    'status'      => 1
            );

            $device_info = _model('screen_device')->read($filter);

            if (!$device_info) {
                continue;
            }

            if ($look) {
                p($v);
                p($device_info);
                echo '<hr />';
                continue;
            }

            _model('rfid_label')->update($v['id'], array('device_nickname_id' => $device_info['device_nickname_id']));

        }

    }

    /**
     * 更新设备昵称
     */
    private function update_device_nickname_id2()
    {
        $look = tools_helper::Get('look', 1);

        //查询所有RFID设备
        $rfid_device = _model('rfid_label')->getList(array('device_nickname_id' => 0));

        foreach ($rfid_device as $k => $v) {
            $v['name']      = trim($v['name']);
            $v['version']   = trim($v['version']);
            //机型信息中存在品牌， 处理
            $version = trim(str_replace($v['name'], '', $v['version']));
            $nickname = _model('screen_device_nickname')->read(array('name_nickname' => $v['name'], 'version_nickname' => $version));

            if ($nickname) {
                p($v);
                p($nickname);
                echo '<hr />';
                _model('rfid_label')->update($v['id'], array('device_nickname_id' => $nickname['id']));
                continue;
            }

            //型号为 畅享6S
            if ($version == '畅享6S'){
                $version = trim(str_replace('畅享', '畅享 ', $version));
                $nickname = _model('screen_device_nickname')->read(array('name_nickname' => $v['name'], 'version_nickname' => $version));
                if ($nickname) {
                    p($v);
                    p($nickname);
                    echo '<hr />';
                    _model('rfid_label')->update($v['id'], array('device_nickname_id' => $nickname['id']));
                    continue;
                }
            }
            //型号为 nova 青春版
            if ($version == 'nova 青春版'){
                $version = 'nova青春版';
                $nickname = _model('screen_device_nickname')->read(array('name_nickname' => $v['name'], 'version_nickname' => $version));
                if ($nickname) {
                    p($v);
                    p($nickname);
                    echo '<hr />';
                    _model('rfid_label')->update($v['id'], array('device_nickname_id' => $nickname['id']));
                    continue;
                }
            }

            //型号为 三星系列 C501X 等
            if (in_array($version, array('C501X', 'C710X', 'C900X', 'N950XC'))) {

                $nickname = _model('screen_device_nickname')->read(array('phone_name' => 'samsung', 'phone_version' => 'sm-'.strtolower($v['version'])));
                //p(array('phone_name' => 'samsung', 'phone_version' => strtolower($v['version'])));
                //p($nickname);
                if ($nickname) {
                    p($v);
                    p($nickname);
                    echo '<hr />';
                    _model('rfid_label')->update($v['id'], array('device_nickname_id' => $nickname['id']));
                    continue;
                }
            }

            //型号为 三星系列 s8 等
            if (in_array($version, array('S8', 'S8+', 'C7Pro'))) {

                $nickname = _model('screen_device_nickname')->read(array('name_nickname' => $v['name'], 'version_nickname' => 'Galaxy '.$version));
                //p(array('phone_name' => 'samsung', 'phone_version' => strtolower($v['version'])));
                //p($nickname);
                if ($nickname) {
                    p($v);
                    p($nickname);
                    echo '<hr />';
                    _model('rfid_label')->update($v['id'], array('device_nickname_id' => $nickname['id']));
                    continue;
                }
            }

            //if ($version == 'A37') {
            //p($v);
            //}
            //型号为  R11plus 格式
            if (strpos($version, 'plus') !== false && strpos($version, ' plus') == false) {
                $version = trim(str_replace('plus', ' plus', $version));
                $nickname = _model('screen_device_nickname')->read(array('name_nickname' => $v['name'], 'version_nickname' => $version));
                if ($nickname) {
                    p($v);
                    p($nickname);
                    echo '<hr />';
                    _model('rfid_label')->update($v['id'], array('device_nickname_id' => $nickname['id']));
                    continue;
                }

            }

            //型号为  mete10
            if (strpos($version, 'mete') !== false) {
                $version = trim(str_replace('mete', 'mate ', $version));
                $nickname = _model('screen_device_nickname')->read(array('name_nickname' => $v['name'], 'version_nickname' => $version));
                if ($nickname) {
                    p($v);
                    p($nickname);
                    echo '<hr />';
                    _model('rfid_label')->update($v['id'], array('device_nickname_id' => $nickname['id']));
                    continue;
                }
            }

            //型号为 华为mate10
            if (strpos($version, 'mate') !== false && strpos($version, 'mate ') === false){
                $version = trim(str_replace('mate', 'mate ', $version));
                $nickname = _model('screen_device_nickname')->read(array('name_nickname' => $v['name'], 'version_nickname' => $version));
                if ($nickname) {
                    p($v);
                    p($nickname);
                    echo '<hr />';
                    _model('rfid_label')->update($v['id'], array('device_nickname_id' => $nickname['id']));
                    continue;
                }
            }

            //型号为 华为mate10
            if (strpos($version, 'Mate') !== false && strpos($version, 'Mate ') === false){
                $version = trim(str_replace('Mate', 'mate ', $version));
                $nickname = _model('screen_device_nickname')->read(array('name_nickname' => $v['name'], 'version_nickname' => $version));
                if ($nickname) {
                    p($v);
                    p($nickname);
                    echo '<hr />';
                    _model('rfid_label')->update($v['id'], array('device_nickname_id' => $nickname['id']));
                    continue;
                }
            }

            //型号为 华为mate10pro
            if (strpos($version, 'pro') !== false && strpos($version, ' pro') == false){
                $version = trim(str_replace('pro', ' pro', $version));
                $nickname = _model('screen_device_nickname')->read(array('name_nickname' => $v['name'], 'version_nickname' => $version));
                if ($nickname) {
                    p($v);
                    p($nickname);
                    echo '<hr />';
                    _model('rfid_label')->update($v['id'], array('device_nickname_id' => $nickname['id']));
                    continue;
                }
            }

            //型号为 畅想6S
            if (strpos($version, '畅想') !== false){
                $version = trim(str_replace('畅想', '畅享', $version));

                $nickname = _model('screen_device_nickname')->read(array('name_nickname' => $v['name'], 'version_nickname' => $version));
                if ($nickname) {
                    p($v);
                    p($nickname);
                    echo '<hr />';
                    _model('rfid_label')->update($v['id'], array('device_nickname_id' => $nickname['id']));
                    continue;
                }
            }

            //型号为 畅享6S
            if (strpos($version, '畅享 ') !== false){
                $version = trim(str_replace('畅享 ', '畅享', $version));

                $nickname = _model('screen_device_nickname')->read(array('name_nickname' => $v['name'], 'version_nickname' => $version));
                if ($nickname) {
                    p($v);
                    p($nickname);
                    echo '<hr />';
                    _model('rfid_label')->update($v['id'], array('device_nickname_id' => $nickname['id']));
                    continue;
                }
            }

            //型号为 红米Note 4X
            if (strpos($version, 'Note') !== false){
                $pos = strpos($version, 'Note');
                if ($version{$pos+1} && strpos($version, 'Note ') === false) {
                    $version = trim(str_replace('Note', 'Note ', $version));
                }

                if ($version{$pos-1} && strpos($version, ' Note') === false) {
                    $version = trim(str_replace('Note', ' Note', $version));
                }
                $nickname = _model('screen_device_nickname')->read(array('name_nickname' => $v['name'], 'version_nickname' => $version));
                if ($nickname) {
                    p($v);
                    p($nickname);
                    echo '<hr />';
                    _model('rfid_label')->update($v['id'], array('device_nickname_id' => $nickname['id']));
                    continue;
                }

            }

            //型号为 荣耀8青春版
            if (strpos($version, '青春版') !== false && strpos($version, ' 青春版') === false){
                $version = trim(str_replace('青春版', ' 青春版', $version));
                $nickname = _model('screen_device_nickname')->read(array('name_nickname' => $v['name'], 'version_nickname' => $version));
                if ($nickname) {
                    p($v);
                    p($nickname);
                    echo '<hr />';
                    _model('rfid_label')->update($v['id'], array('device_nickname_id' => $nickname['id']));
                    continue;
                }

            }

            //型号为 荣耀v9
            if (strpos($version, '荣耀V') !== false){
                $version = trim(str_replace('荣耀V', '荣耀 v', $version));
                $nickname = _model('screen_device_nickname')->read(array('name_nickname' => $v['name'], 'version_nickname' => $version));
                if ($nickname) {
                    p($v);
                    p($nickname);
                    echo '<hr />';
                    _model('rfid_label')->update($v['id'], array('device_nickname_id' => $nickname['id']));
                    continue;
                }
            }

        }

    }

    /**
     * 更新rfid设备昵称id
     */
    private function update_rfid_device_nickname()
    {
        _widget('rfid.phone')->update_all_device_nickname_id();
    }

    /**
     * 所有的nickname_id更新为0
     */
    private function update_0_device_nickname_id()
    {
        _model('rfid_label')->update(array(1=>1), array('device_nickname_id' => 0));
    }

    /**
     * 更新大郊亭营业厅
     */
    private function update_rfid_business()
    {
        //异常：110375
        $err_business_id = 110375;
        //正常：46435
        $business_id = 46435;
        //查询营业厅
        $business_hall = _model('business_hall')->read($business_id);

        if (!$business_hall) {
            return false;
        }

        //更新label
        echo '<br>'._model('rfid_label')->update(array('business_hall_id' => $err_business_id), array('business_hall_id' => $business_id, 'area_id' => $business_hall['area_id']));

        //更新rfid_online_stat_day
        echo '<br>'._model('rfid_online_stat_day')->update(array('business_id' => $err_business_id), array('business_id' => $business_id, 'area_id' => $business_hall['area_id']));

        //更新rfid_probe_user_record
        echo '<br>'._model('rfid_probe_user_record')->update(array('business_id' => $err_business_id), array('business_id' => $business_id));

        //更新rfid_record
        echo '<br>'._model('rfid_record')->update(array('business_id' => $err_business_id), array('business_id' => $business_id, 'area_id' => $business_hall['area_id']));

        //更新rfid_record
        echo '<br>'._model('rfid_rwtool')->update(array('business_id' => $err_business_id), array('business_id' => $business_id, 'area_id' => $business_hall['area_id']));

        //更新rfid_record_detail
        echo '<br>'._model('rfid_record_detail')->update(array('business_id' => $err_business_id), array('business_id' => $business_id, 'area_id' => $business_hall['area_id']));

        //更新rfid_shoppe
        echo '<br>'._model('rfid_shoppe')->update(array('business_id' => $err_business_id), array('business_id' => $business_id, 'area_id' => $business_hall['area_id']));

        //更新rfid_shoppe
        echo '<br>'._model('rfid_stat')->update(array('business_id' => $err_business_id), array('business_id' => $business_id, 'area_id' => $business_hall['area_id']));

        //更新rfid_stat_hour
        echo '<br>'._model('rfid_stat_hour')->update(array('business_id' => $err_business_id), array('business_id' => $business_id, 'area_id' => $business_hall['area_id']));

        //删除缓存

    }

    private function delete_redis()
    {



        $redis_cache = RedisCache::content();
        $redis_cache->set('phone_abc', 122333);

        $result = $redis_cache->redis->keys('phone_*');

        $look  =tools_helper::Get('look', 1);
        foreach ($result as $v) {
            echo $v.'<br />';
            if (!$look) {
                $redis_cache->delete($v);
            }

            p($redis_cache->get($v));
        }
    }

    private function update_version()
    {
//         $res=  _model('rfid_record')->update(array('label_id' => '1801121D'), array('phone_name' => '三星', 'phone_version' => 'S8', 'phone_color' => '黑色'));
// p($res);
        $res=  _model('rfid_record_detail')->update(array('label_id' => '1801121D'), array('phone_name' => '三星', 'phone_version' => 'S8', 'phone_color' => '黑色'));

        p($res);
    }

    /**
     * 删除设备
     * 2018-04-19 因在测试-增值厅将设备做了测试，明天要发往省公司，所以需删除数据
     */
    public function delete_device()
    {
        $province_id = tools_helper::Get('province_id', 0);

        if (!$province_id) {
            echo '请传递省份';
            exit;
        }

        //查询设备
        $devices = _model('rfid_label')->getFields('label_id', array('province_id' => $province_id));

        p($devices);

        //删除所有设备和相关统计
        foreach ($devices as $k => $v) {
            $this->delete_device_stat($v);
        }
    }

    /**
     * 删除设备统计
     */
    private function delete_device_stat($device)
    {
        //查询设备详情
        $lable_info = _model('rfid_label')->read(array('label_id' => $device));

        if (!$lable_info) {
            echo $device.'标签不存在';exit;
        }


        //首先删除在线统计
        _model('rfid_online_stat_day')->delete(array('label_id' => $device));

        //删除设备体验记录
        _model('rfid_record')->delete(array('label_id' => $device));

        //删除设备体验记录详情
        _model('rfid_record_detail')->delete(array('label_id' => $device));

        //删除设备体验记录详情
        _model('rfid_record_detail')->delete(array('label_id' => $device));

        //更新rfid设备读写器
        $rwtool = _model('rfid_rwtool')->read(array('business_id' => $lable_info['business_hall_id']));
        if ($rwtool) {
            _model('rfid_rwtool')->update(array('business_id' => $lable_info['business_hall_id']), array('label_num' => $rwtool['label_num'] -1 ));
        }

        //最后删除设备
        _model('rfid_label')->delete(array('label_id' => $device));

    }

    /**
     * 导出指定厅下的RFID
     */
    public function export_business_rfid()
    {
        $user_number = array(
                '4403041102464',
        );

//         $user_number = array(
//                 '1101061002037',
//                 '1101061002036',
//                 '1101011002039',
//         );

        $label_infos = array();

        //用户渠道编码
        foreach ($user_number as $u) {
            $business_hall_info = _model('business_hall')->read(array('user_number' => $u));

            if (!$business_hall_info) {
                continue;
            }

            $province   = business_hall_helper::get_info_name('province', $business_hall_info['province_id'], 'name');
            $city       = business_hall_helper::get_info_name('city', $business_hall_info['city_id'], 'name');
            $area       = business_hall_helper::get_info_name('area', $business_hall_info['area_id'], 'name');

            //查询厅下所有标签
            $label_list = _model('rfid_label')->getList(array('business_hall_id' => $business_hall_info['id']));

            foreach ($label_list as $k => $v) {
                //查询标签体验次数
                $remain_times = _model('rfid_record_detail')->getFields('remain_time', array(
                        'status' => 1,
                        'end_timestamp >' => 100,
                        'label_id'      => $v['label_id'],
                        'business_id'   => $v['business_hall_id'],
                ));

                $action_num     = count($remain_times);
                $remain_times   = array_sum($remain_times);

                //查询状态
                $online_id = _model('rfid_online_stat_day')->read(array(
                        'label_id' => $v['label_id'],
                        'day'      => date('Ymd'),
                        'business_id' => $v['business_hall_id'],
                ));

                if ($online_id) {
                    $status = '在线';
                } else {
                    $status = '离线';
                }

                //查询柜台
                $shoppe_info = shoppe_helper::get_shoppe_info($v['shoppe_id']);
                $shoppe = $shoppe_info ? $shoppe_info['shoppe_name'] : '';
                $label_info = array(
                        'province' => $province,
                        'city' => $city,
                        'area' => $area,
                        'hall' => $business_hall_info['title'],
                        'user_number' => $business_hall_info['user_number']."\t",
                        'label_id' => $v['label_id']."\t",
                        'shoppe' => $shoppe,
                        'brand'  => $v['name'],
                        'version' => $v['version'],
                        'color'     => $v['color'],
                        'imei'      => $v['imei'],
                        'action_num' => $action_num."\t",
                        'remain_times' => round($remain_times / 60, 2).'分钟',
                        'status'        => $status,
                );

                $label_infos[] = $label_info;
            }
        }
        //导出
        $hand = array(
                '省',
                '市',
                '区',
                '厅',
                '渠道编码',
                '标签',
                '柜台',
                '手机品牌',
                '手机型号',
                '手机颜色',
                '手机IMEI',
                '体验次数',
                '体验时长',
                '状态'
        );

        $params['filename'] = 'RFID统计';
        $params['data']     = $label_infos;
        $params['head']     = $hand;
        Csv::getCvsObj($params)->export();
    }


    public function update_phone_name()
    {
        $label_id = array(
                '180111d4',
        );

        foreach ($label_id as $v) {

            _model('rfid_record_detail')->update(array('label_id' => $v), array('phone_name' => '英菲尼迪', 'phone_version' => 'QX60-主驾驶', 'phone_color' => '棕色'));

            _model('rfid_record')->update(array('label_id' => $v), array('phone_name' => '英菲尼迪', 'phone_version' => 'QX60-主驾驶', 'phone_color' => '棕色'));

        }

    }





}