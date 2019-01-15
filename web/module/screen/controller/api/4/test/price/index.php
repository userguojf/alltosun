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
 * $Date: 2017年9月11日 下午9:34:23 $
 * $Id$
 */

class Action
{
    public function edit_price()
    {
//         $id = _model ( 'screen_show_pic_cache' )->delete ( array (1 => 1) );
//         p($id);
// //         exit();
        $device_unique_id  = Request::get('device_unique_id', '4c189a4632d9');
        $price             = Request::get('price', 1114);

        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();

        //$post_data['user_number']  = $user_number;

        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);

        $post_data['device_unique_id'] = $device_unique_id;
        $post_data['price']  = $price;

        an_dump($post_data);

        $api_url = SITE_URL.'/screen/api/3/price/edit_price';
        //$api_url = 'http://mac.pzclub.cn/screen/api/3/price/edit_price';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;

//         an_dump(json_decode($res, true));
    }
}