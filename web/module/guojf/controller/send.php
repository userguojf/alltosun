<?php
/**
 * alltosun.com  send.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-12-21 上午11:14:19 $
 * $Id$
 */
class Action
{
    public function index()
    {
        $res = _widget('screen.first_instatll_check_send_msg')->send_msg('1101021002051_13', 1, 'admin');
        p($res);
    }

    public function check()
    {
        $touser = '1101021002051_13|1101021002051_03';
        $device_offline_num = 1;

        $title       = '【您的亮靓已离线】修复秘籍双手奉上';
        $description  = '马姐，接受消息';
        //         $url = AnUrl('screen_dm/device','?state=check');
        
        $params =  '{"touser": "1101021002051_13|1101021002051_03",
                   "msgtype": "text",
                   "agentid": 31,
                   "text": {
                       "content": "'.$description.'"
                   },
                }';
        
        $info = _widget('qydev.send_msg')->send_message($touser, $params);
//         $res = _widget('screen.first_instatll_check_send_msg')->send_check_device_msg();
        p($info);
    }

//     public function offline()
//     {
//         $res = _widget('screen.offline_send_msg')->send_series_offline_msg();
//         p($res);
//     }

    public function mc()
    {
        p($mc_wr->get('qydev_msg'));
    }

//     public function delete()
//     {
//         _model('screen_business_device_num_stat')->delete(array(1 => 1));
//         _model('screen_offline_series_stat')->delete(array(1 => 1));
//         _model('screen_id_record')->delete(array('data_table' => 'screen_device','date' => 20171218));
//     }
}