<?php
/**
 * alltosun.com 测试自动开启接口 auto_start.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-1-11 下午12:04:43 $
 * $Id$
 */
class Action
{
    public function __call($action = '', $param = array())
    {
        // 杏石口营业厅	huawei	vtr-al00	5425eac0a58f
        $user_number      = tools_helper::get('user_number', '1101081002058'); // 1101081002052
        $device_unique_id = tools_helper::get ('device_unique_id', '102ab3eda203'); // 5425eac0a58f
        $reset_report     = tools_helper::post('reset_report', 0);

        $info             = '[{"auto_start_time":1525233908,"auto_start":0},{"auto_start_time":1525309663,"auto_start":0}]';

/**

{"post":
{
    "sign":"dce76a42f5c7081ef42887b4df753682",
    "source":"1002",
    "time":"1517827579352",
    "rid":"868341030619992",
    "device_unique_id":"2054fa923f5d",
    "reset_report":"1",
    "version":"2.2.0",
    "key":"alltosun2016",
    "user_number":"1401001614271",
    "info":"[
        {\"auto_start_time\":1517624837,\"auto_start\":1},
        {\"auto_start_time\":1517674037,\"auto_start\":1},
        {\"auto_start_time\":1517791458,\"auto_start\":1}
        ]"
},
"get":{"anu":"\/screen\/api\/3\/phone\/auto_start"}}

 */
        $post_data = array();
        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);

        $post_data['user_number']      = $user_number;
        $post_data['device_unique_id'] = $device_unique_id;
        $post_data['reset_report']     = $reset_report;

        $post_data['info']  = $info;

        an_dump($post_data);

        $api_url = SITE_URL.'/screen/api/3/phone/auto_start';

        $res = an_curl($api_url, $post_data, 0, 0);
// exit();
        echo $res;

    }
}