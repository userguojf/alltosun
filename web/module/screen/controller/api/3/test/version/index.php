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
 * $Date: 2017年7月21日 下午3:14:54 $
 * $Id$
 */
class Action
{
    public function index()
    {
        $version_no  = tools_helper::get('version_no', '');
        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        //$post_data['user_number'] = $user_number;
        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);

        $post_data['version_no'] = $version_no;
        an_dump($post_data);

        $api_url = SITE_URL.'/screen/api/3/version';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;

        an_dump(json_decode($res, true));
    }

    public function get_version_info()
    {
        $version_no  = tools_helper::get('version_no', '');
        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);

        $post_data['version_no'] = $version_no;

        //$api_url = SITE_URL.'/screen/api/3/version/get_version_info';
        $api_url = 'http://test.pzclub.cn/screen/api/3/version/get_version_info';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;
    }

    /**
     *
     */
    public function update_version_info()
    {
        //0-正常请求模式 1-扫码模式 2-本地布丁模式
        $mode  = tools_helper::Get('mode', 2);
        $code  = tools_helper::Get('code', 100);
        $info  = tools_helper::Get('info', 'infoinfo');
        $handlePatchVersion  = tools_helper::Get('handlePatchVersion', 'v1.233.45.67');
        $device_unique_id  = tools_helper::Get('device_unique_id', '2ac2323ddse24f');


        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        //$post_data['user_number'] = $user_number;
        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);

        $post_data['device_unique_id'] = $device_unique_id;
        $post_data['handlePatchVersion'] = $handlePatchVersion;
        $post_data['info'] = $info;
        $post_data['code'] = $code;
        $post_data['mode'] = $mode;

        an_dump($post_data);

        $api_url = SITE_URL.'/screen/api/3/version/update_version_info';

        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;

        an_dump(json_decode($res, true));
    }
}