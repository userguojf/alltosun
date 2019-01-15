<?php

/**
 * alltosun.com  click.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年7月19日 下午12:04:48 $
 * $Id$
 */

class Action
{
    public function add_click()
    {
        $user_number            = tools_helper::get('user_number', '1101081002058');
        $device_unique_id       = tools_helper::get('device_unique_id', '102ab3eda203');
        $res_id                 = tools_helper::get('res_id', 442);

        $time = time();
        $time1 = $time + 10*60;
        $time2 = $time1 + 10*60;
        $info                   = '[{"content_id":"442","time":1525516775,"click_count":13}]';

        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        //$post_data['user_number']  = $user_number;

        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);

        $post_data['user_number']       = $user_number;
        $post_data['device_unique_id']  = $device_unique_id;
        $post_data['content_id']        = $res_id;
        $post_data['info']            = $info;

        an_dump($post_data);

        $api_url = SITE_URL.'/screen/api/4/phone/click/add_click';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;

        an_dump(json_decode($res, true));
    }

    /**
     * 文件上传的方式统计
     */
    public function add_click_by_file()
    {
        $api_url = SITE_URL.'/screen/api/3/phone/click/add_click_by_file';
        $name = '';
        echo '
                <form action="'.$api_url.'" method="post" enctype="multipart/form-data">
                    <input type="file" name="content">
                    <input type="submit" value="提交">
                </form>
        ';
    }
}