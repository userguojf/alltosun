<?php

/**
 * alltosun.com  content_stat.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年7月31日 下午5:33:33 $
 * $Id$
 */

class Action
{

    public function add_content_stat()
    {
        $user_number        = tools_helper::get('user_number', '1101081002052');
        $device_unique_id   = tools_helper::get('device_unique_id', '9c2ea1b9f8a9');
        //$info               = '[{"content_id":"6","id":3,"roll_sum":59,"time":1512638425698}]';
        $info = '[{"content_id":"6","id":51,"roll_sum":4,"time":'.(time()-rand(1, 100)).'},{"content_id":"7","id":53,"roll_sum":6,"time":'.(time()-rand(1, 100)).'}]';
        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        //$post_data['user_number'] = $user_number;
        // 加密
        $post_data['sign']              = api_helper::encode_sign($post_data);

        $post_data['user_number']       = $user_number;
        $post_data['device_unique_id']  = $device_unique_id;
        $post_data['info']              = $info;

//         an_dump($post_data);

        $api_url = SITE_URL.'/screen/api/4/content/content_stat/add_content_stat';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;

//         an_dump(json_decode($res, true));
    }

    /**
     * 文件上传的方式统计
     */
    public function add_content_stat_by_file()
    {

        $device_unique_id   = tools_helper::get('device_unique_id', '9c2ea1b9f8a9');

        //$info = '[{"content_id":"6","id":51,"roll_sum":4,"time":'.(time()+rand(1, 100)).'},{"content_id":"7","id":53,"roll_sum":6,"time":'.(time()+rand(1, 100)).'}, {"content_id":"8","id":54,"roll_sum":6,"time":'.(time()+rand(1, 100)).'}]';

        $api_url = SITE_URL.'/screen/api/3/content/content_stat/add_content_stat_by_file';
        $name = '';
        echo '
                <form action="'.$api_url.'" method="post" enctype="multipart/form-data">
                    <input type="file" name="content">
                    <input type="submit" value="提交">
                </form>
        ';
    }

    public function add_stat()
    {
        $user_number    = tools_helper::get('user_number', '1101021002051');
        $device_unique_id           = tools_helper::get('device_unique_id', 'ecd09fac8c69');
        $res_id         = tools_helper::get('res_id', '19');
        $res_name       = tools_helper::get('res_name', 'link');

        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        //$post_data['user_number'] = $user_number;
        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);

        $post_data['user_number'] = $user_number;
        $post_data['device_unique_id'] = $device_unique_id;
        $post_data['res_id'] = $res_id;
        $post_data['res_name'] = $res_name;

        an_dump($post_data);

        $api_url = SITE_URL.'/screen/api/2/content/content_stat/add_stat';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;

        an_dump(json_decode($res, true));

    }
}