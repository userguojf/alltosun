<?php
/**
 * alltosun.com 设备拿出设备连续5小时离线的数据和发离线的消息 offline_send_msg.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-9-22 上午11:46:03 $
 * $Id$
 */

class offline_send_msg_widget
{
    public function send_series_offline_msg()
    {
        $time = time();//- 4 * 24 *3600;

        $today = date('Ymd', $time);
        $month = date('Ym', $time);

        $yyt_ids = _model('screen_qydev_msg_record')->getFields( 'business_hall_id' ,
                array(
                    'res_name' => 'offline',
                    'month'    => $month,
                    'type'     => 0
                )
        );

        $yyt_ids = array_unique($yyt_ids);

        $list = _model('screen_offline_series_stat')->getList(
                array(
//                     'type'          => 1,
                    'offline_num >' => 1,      //连续离线的天数
                    'date'          => $today
                )
        );

        if (!$list) exit('没有要发提示消息的设备');

        $arr = array();

        foreach ($list as $k => $v) {
            // 跳过这个月发过的营业厅
            if ( !empty($yyt_ids) && in_array($v['business_hall_id'], $yyt_ids) ) {
                continue;
            }

            if ( !isset($arr[$v['business_hall_id']]) ) {
                $arr[$v['business_hall_id']] = 1;
            } else {
                ++$arr[$v['business_hall_id']];
            }
        }

        if ( !$arr ) exit('本月暂无发消息提示营业厅');

        foreach ($arr as $key => $val) {
            // 渠道码
            $user_number = screen_helper::by_id_get_field($key, 'business_hall', 'user_number');
            //
            $this->by_user_number_qydev_user_id($user_number, $val, $key);
        }
    }

    /**
     * 获取厅长循环发消息
     * @param string $user_number
     * @return unknown|boolean
     */
    public function by_user_number_qydev_user_id($user_number, $device_num, $business_hall_id)
    {
        $user_info = _model('public_contact_user')->getList(array('user_number' => $user_number));

        if(!$user_info) return false;

        $touser = '';
        foreach ($user_info as $k => $v) {
//             $this->send_msg($v['unique_id'], $device_offline_num);
            $touser = $touser.'|'.$v['unique_id'];
        }

        // 发消息  最多支持1000个
        $this->send_msg(trim($touser, '|'), $device_num, $business_hall_id);

        return true;
    }

    public function send_msg($touser, $device_num, $business_hall_id)
    {
//         p($touser);
//         p($device_num);
//         p($business_hall_id);

//         $title        = '【亮靓来报】设备状态提示';
//         $description  = '厅长大人！\n\r您好，您的厅店有'.$device_num.'台演示机已经连续2天没有网络 ，';
//         $description .= '影响最新内容的更新，和终端喜好度的数据收集，请您尽快核实，保证演示机能正常连网。';
//         //$url         = 'http://mac.pzclub.cn/screen_dm/device?state=offline';
//         $url = AnUrl('screen_dm/device','?state=offline');
        
//         $params =  '{
//                        "touser" : "'.$touser.'",
//                        "msgtype": "news",
//                        "agentid": 0,
//                        "news": {
//                            "articles":[
//                                {
//                                    "title"      : "'.$title.'",
//                                    "description": "'.$description.'",
//                                    "url"        : "'.$url.'",
//                                },
//                            ]
//                        }
//                     }';

//         $info = _widget('qydev.send_msg')->send_message($touser, $params);

//         if ( isset($info['errmsg']) && $info['errmsg'] == 'ok' ) {
//             _widget('screen.install_send_msg')->record('offline', $business_hall_id, $touser);
//         }

//         return true;
    }
}