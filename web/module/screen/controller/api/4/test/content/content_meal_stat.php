<?php

/**
 * alltosun.com  content_meal_stat.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2018年1月17日 下午4:52:53 $
 * $Id$
 */

class Action
{
    public function index()
    {
        $user_number        = tools_helper::get('user_number', '1101051002015');
        $device_unique_id   = tools_helper::get('device_unique_id', '94fe2291bc83');
        $content_meal_id    = tools_helper::get('content_meal_id', 37);
        //$click_time         = tools_helper::get('click_time', '');
        $res_info           = '[{"content":[{"app_name":"亮靓","open_count":12,"run_time":43774},{"app_name":"系统桌面","open_count":2,"run_time":40590},{"app_name":"安全中心","open_count":5,"run_time":18580},{"app_name":"设置","open_count":5,"run_time":15441},{"app_name":"系统用户界面","open_count":1,"run_time":2647},{"app_name":"授权管理","open_count":3,"run_time":1375}],"record_time":1522926559,"type":1},{"content":[],"record_time":1522840160,"type":1},{"content":[],"record_time":1522753761,"type":1},{"content":[{"app_name":"亮靓","open_count":11,"run_time":2077032},{"app_name":"系统桌面","open_count":21,"run_time":1689313},{"app_name":"LockScreenSample","open_count":7,"run_time":1320296},{"app_name":"系统用户界面","open_count":3,"run_time":5662},{"app_name":"设置","open_count":1,"run_time":3662},{"app_name":"安全中心","open_count":1,"run_time":2974},{"app_name":"Activity 栈","open_count":1,"run_time":1071},{"app_name":"Android 系统","open_count":1,"run_time":14}],"record_time":1522667362,"type":1},{"content":[{"app_name":"亮靓","open_count":61,"run_time":17006760},{"app_name":"系统桌面","open_count":62,"run_time":3341574},{"app_name":"豆芽 API Key","open_count":5,"run_time":39335},{"app_name":"安全中心","open_count":22,"run_time":21938},{"app_name":"系统用户界面","open_count":7,"run_time":13462},{"app_name":"相机","open_count":2,"run_time":9561},{"app_name":"设置","open_count":5,"run_time":7899},{"app_name":"授权管理","open_count":3,"run_time":1075},{"app_name":"通讯录与拨号","open_count":1,"run_time":997}],"record_time":1522580963,"type":1},{"content":[{"app_name":"亮靓","open_count":60,"run_time":13316997},{"app_name":"系统桌面","open_count":142,"run_time":2447944},{"app_name":"系统用户界面","open_count":16,"run_time":388577},{"app_name":"DemoSpring","open_count":1,"run_time":338159},{"app_name":"用户反馈","open_count":4,"run_time":332705},{"app_name":"安全中心","open_count":85,"run_time":154573},{"app_name":"设置","open_count":31,"run_time":114660},{"app_name":"系统更新","open_count":3,"run_time":68542},{"app_name":"授权管理","open_count":17,"run_time":10442},{"app_name":"应用商店","open_count":1,"run_time":4735},{"app_name":"通讯录与拨号","open_count":2,"run_time":4490},{"app_name":"相册","open_count":1,"run_time":1981},{"app_name":"Circle Menu","open_count":1,"run_time":1656},{"app_name":"Activity 栈","open_count":1,"run_time":1374}],"record_time":1522494564,"type":1},{"content":[{"app_name":"亮靓","open_count":117,"run_time":23790569},{"app_name":"Circle Menu","open_count":53,"run_time":4244125},{"app_name":"系统桌面","open_count":97,"run_time":2321151},{"app_name":"BoomMenuButton","open_count":16,"run_time":1885296},{"app_name":"设置","open_count":12,"run_time":446388},{"app_name":"安全中心","open_count":52,"run_time":380870},{"app_name":"通讯录与拨号","open_count":4,"run_time":314804},{"app_name":"系统用户界面","open_count":33,"run_time":121796},{"app_name":"Assistive Touch","open_count":13,"run_time":23515},{"app_name":"相册","open_count":2,"run_time":2941},{"app_name":"搜索","open_count":1,"run_time":2428},{"app_name":"授权管理","open_count":1,"run_time":1152}],"record_time":1522408166,"type":1}],';
        $res_info = '[{"run_time":12,"time":1521448786},{"run_time":4,"time":1521448817},{"run_time":1,"time":1521448824},{"run_time":4,"time":1521448829},{"run_time":3,"time":1521448837},{"run_time":2,"time":1521448891},{"run_time":25,"time":1521448900}]';
        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        //$post_data['user_number'] = $user_number;
        // 加密
        $post_data['sign']              = api_helper::encode_sign($post_data);

        $post_data['user_number']       = $user_number;
        $post_data['content_meal_id']       = $content_meal_id;
        $post_data['device_unique_id']  = $device_unique_id;
        $post_data['res_info']        = $res_info;

        an_dump($post_data);

        $api_url = SITE_URL.'/screen/api/3/content/content_meal_stat';
        $api_url = 'http://mac.pzclub.cn/screen/api/3/content/content_meal_stat';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;

        an_dump(json_decode($res, true));
    }
}