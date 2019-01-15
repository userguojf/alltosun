<?php
/**
 * alltosun.com  first_instatll_check_send_msg.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-12-19 下午3:25:58 $
 * $Id$
 */
class first_instatll_check_send_msg_widget
{
    public function send_check_device_msg()
    {
        $time = time();

        $today = date('Ymd', $time);
        $three_days_before = date('Ymd', $time - 3 * 24 * 60 * 60);

        $filter = array();

        $list = _model('screen_offline_series_stat')->getList( array(
                'type'            => 1,
                'install_date <=' => $today,
                'install_date >'  => $three_days_before,
                'date'            => $today
        ));

        if (!$list) exit('没有要发提示消息的设备');

        $arr = array();

        foreach ($list as $k => $v) {
            if ( !isset($arr[$v['business_hall_id']]) ) {
                $arr[$v['business_hall_id']] = 1;
            } else {
                ++$arr[$v['business_hall_id']];
            }
        }

        foreach ($arr as $key => $val) {
            //渠道码
            $user_number = screen_helper::by_id_get_field($key, 'business_hall', 'user_number');
            //渠道码
            $this->by_user_number_qydev_user_id($user_number, $val, $key);
        }
    }

    /**
     * 获取厅长循环发消息
     * @param string $user_number
     * @return unknown|boolean
     */
    public function by_user_number_qydev_user_id($user_number, $device_offline_num, $business_hall_id)
    {
        $user_info = _model('public_contact_user')->getList( array('user_number' => $user_number) );

        if(!$user_info) {
            return false;
        }

        $touser = '1101021002051_13'; // guojf 每次都接受消息 随时查看
        $tophone = '15701651914';

        foreach ($user_info as $k => $v) {
            // 发短信的方法
            $tophone = $tophone.'|'.$v['user_phone'];
            // 组装企业号消息useID
            $touser = $touser.'|'.$v['unique_id'];
        }

        // 发消息  最多支持1000个
        $this->send_msg($touser, $device_offline_num, $business_hall_id);

        // 发短信
        screen_msg_helper::send_check_msg($tophone, $device_offline_num, $user_number);

        return true;
    }

    /**
     * 发消息
     * @param unknown $user_id
     * @param unknown $device_offline_num
     * @return boolean
     */
    public function send_msg($touser, $device_offline_num, $business_hall_id)
    {
//         p($user_id);
//         p($device_offline_num);
//         p($business_hall_id);
//         exit();
        $title       = '【您的亮靓已离线】修复秘籍双手奉上';
        $description  = '厅长大人！\n\r系统检测到您厅内'.$device_offline_num.'台亮靓已经离线，';
        $description .= '将无法展示您要求的内容。我们已帮您统计到终端型号，点击立刻查看（内附修复秘籍，一分钟即可召回！）';
        $url         = 'http://mac.pzclub.cn/screen_dm/device?state=check';
//         $url = AnUrl('screen_dm/device','?state=check');

        $params =  '{"touser" : "'.$touser.'",
                       "msgtype": "news",
                       "agentid": 0,
                       "news": {
                           "articles":[
                               {
                                   "title"      : "'.$title.'",
                                   "description": "'.$description.'",
                                   "url"        : "'.$url.'",
                               },]}}';

        $info = _widget('qydev.send_msg')->send_message($touser, $params);

        if ( isset($info['errmsg']) && $info['errmsg'] == 'ok' ) {
            _widget('screen.install_send_msg')->record('check', $business_hall_id, $touser);
        }

        return true;
    }
}