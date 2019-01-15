<?php
/**
 * alltosun.com  index.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年9月24日 下午1:20:28 $
 * $Id$
 */

class Action
{
    public function index()
    {
        $user_number  = tools_helper::get('user_number', '1101142001192');
        $device_unique_id         = tools_helper::get('device_unique_id', 'ecd09fac8c67');
        $time               = tools_helper::get('date', date('Y-m-d H:i:s'));
        $content            = tools_helper::get('content', '	java.lang.RuntimeException: Unable to destroy activity {com.alltosun.swingarmsmanager/com.alltosun.swingarmsmanager.ui.MainActivity}: java.lang.IllegalArgumentException: Receiver not registered: com.alltosun.swingarmsmanager.receiver.HomeKeyWatchReceiver@417f016');


        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        //$post_data['user_number'] = $user_number;
        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);

        $post_data['user_number']  = $user_number;
        $post_data['device_unique_id']   = $device_unique_id;
        $post_data['date']  = $time;
        $post_data['content']        = $content;

        an_dump($post_data);
        $api_url = SITE_URL."/screen/api/2/error_log";
        //$api_url = 'http://wifi.pzclub.cn/screen/api/2/online/add';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;

        an_dump(json_decode($res, true));
    }
}