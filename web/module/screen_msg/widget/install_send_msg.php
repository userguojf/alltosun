<?php
/**
 * alltosun.com 设备安装成功发消息 install_send_msg.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-9-21 下午8:47:37 $
 * $Id$
 */
class install_send_msg_widget 
{
    public function send_install_msg() 
    {
        set_time_limit ( 0 );

        // 关键的时间
        $time = time();// - 4 * 24 * 3600;

        $table = 'screen_business_device_num_stat';
        $date  = date('Ymd', $time);

        $filter = array(
            'date'          => $date,
            'install_num >' => 0
        );

        $business_device_info = _model ( $table )->getList ( $filter );

        if (! $business_device_info )  exit ( '暂无设备添加' );

        foreach ( $business_device_info as $k => $v ) {
            // 渠道码
            $user_number = business_hall_helper::get_info_name ( 'business_hall', array('id' => $v['business_hall_id']), 'user_number' );

            $this->by_user_number_qydev_user_id ( $user_number, $v['install_num'], $v['all_num'], $v['business_hall_id'] );
        }
    }

    /**
     * 获取厅长循环发消息
     *
     * @param string $user_number
     * @return unknown boolean
     */
    public function by_user_number_qydev_user_id($user_number, $new_num, $total_num, $business_hall_id)
    {
        $user_info = _model ( 'public_contact_user' )->getList ( array ( 'user_number' => $user_number ) );

        if (! $user_info) return false;

        $touser = '1101021002051_13'; // guojf 每次都接受消息 随时查看

        foreach ($user_info as $k => $v) {
            $touser = $touser.'|'.$v['unique_id'];
        }
//         $this->send_msg($touser, $new_num, $total_num, $business_hall_id);

        // 发消息  最多支持1000个
        $this->send_msg(trim($touser, '|'), $new_num, $total_num, $business_hall_id);

        return true;
    }

    /**
     * 
     * @param unknown $touser
     * @param unknown $new_num
     * @param unknown $total_num
     * @param unknown $business_hall_id
     * @return boolean
     */
    public function send_msg($touser, $new_num, $total_num, $business_hall_id)
    {
//         p($touser);
//         p($new_num);
//         p($total_num);
//         p($business_hall_id);
//         exit();
        $title       = '【亮靓来报】终端体验大排行';
        $description = '厅长大人！\n\r截至今日19:00，您的厅店有' . $new_num . '台手机新装了“亮靓”APP，累计安装量' . $total_num . '台，快来看看终端体验报告！';

        // $url = "http://mac.pzclub.cn/screen_dm/device?state=install";
        // $url = AnUrl('screen_dm','?state=install');
        $url = "http://mac.pzclub.cn/screen_dm/device?state=install";

        $params = '{"touser" : "' . $touser . '",
                       "msgtype": "news",
                       "agentid": 0,
                       "news": {
                           "articles":[
                               {
                                   "title"      : "' . $title . '",
                                   "description": "' . $description . '",
                                   "url"        : "' . $url . '",
                               },
                           ]
                       }
                    }';

        $info = _widget ( 'qydev.send_msg' )->send_message ($touser, $params );

        if ( isset($info['errmsg']) && $info['errmsg'] == 'ok' ) {
            $this->record('install', $business_hall_id, $touser);
        }

        return true;
    }

    /**
     * 
     * @param unknown $res_name
     * @param unknown $business_hall_id
     * @param unknown $touser
     * @return boolean
     */
    public function record( $res_name, $business_hall_id, $touser )
    {
        if ( !$res_name || !$business_hall_id || !$touser ) return false;

        _model ( 'screen_qydev_msg_record' )->create ( array (
                'res_name'         => $res_name,
                'business_hall_id' => $business_hall_id,
                'touser'           => $touser,
                'date'             => date("Ymd"),
                'month'            => date('Ym'),
                'type'             => 0
        ) );
    }

}