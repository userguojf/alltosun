<?php
/**
 * alltosun.com  record.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年7月19日 上午9:59:35 $
 * $Id$
 */

class Action
{
    public function add_device_record()
    {

//         $time = time() - 24*3600*5 + 12*3600;
        $time = time() - 24*3600*5 + 12*3600;
        $ex1 = rand(1, 10);
        $ex2 = rand(1, 10);
        $ex3 = rand(1, 10);
        $device_unique_id = tools_helper::Get( "device_unique_id", "102ab3eda203");

        $info           =   '[{"experience_time":'.$ex1.',"add_time":"'.($time+100).'"},{"experience_time":'.$ex2.',"add_time":"'.($time+200).'"}]';
        $info = '[{"add_time":1528206112,"experience_time":7}]';
        $post_data = api_helper::make_test_base_data();

        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);
        $post_data['device_unique_id']  = $device_unique_id;
        $post_data['info']  = $info;

        $api_url = SITE_URL.'/screen/api/4/phone/record/add_device_record';
        $res = an_curl($api_url, $post_data, 0, 0);
        p($post_data);
        echo $res;

        an_dump(json_decode($res, true));
    }

    /**
     * 文件上传的方式统计
     */
    public function add_device_record_by_file()
    {

        $api_url = SITE_URL.'/screen/api/3/phone/record/add_device_record_by_file';
        $name = '';
        echo '
                <form action="'.$api_url.'" method="post" enctype="multipart/form-data">
                    <input type="file" name="content">
                    <input type="submit" value="提交">
                </form>
        ';
    }
}