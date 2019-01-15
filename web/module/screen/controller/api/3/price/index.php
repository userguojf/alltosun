<?php

/**
 * alltosun.com 更改价格 index.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年9月11日 下午9:24:38 $
 * $Id$
 */
class Action
{
    public function edit_price()
    {
        $device_unique_id = tools_helper::post ( 'device_unique_id', '' );
        $price            = Request::Post ( 'price', 0 );

        $api_log_id = api_helper::check_sign ( array (), 0 );

        if (! $device_unique_id) {
            api_helper::return_api_data ( 1003, '请输入设备唯一标识', array (), $api_log_id );
        }

        if (! $price) {
            api_helper::return_api_data ( 1003, '请填写手机价格', array (), $api_log_id );
        }

        // 读出修改价格的设备信息
        $device_info = _model ( 'screen_device' )->read ( array (
                'device_unique_id' => $device_unique_id,
                'status' => 1
        ) );

        if ( !$device_info ) {
            api_helper::return_api_data(1003, '未知的设备信息（提示：设备可能下架）', array(), $api_log_id);
        }

        //确定有机型宣传图
        $content_info = _widget ( 'screen' )->get_type4_content_by_device ( $device_info ['business_id'], $device_info ['phone_name'], $device_info ['phone_version'], $device_info);

        if (! $content_info) {
            api_helper::return_api_data ( 1003, '由于网络原因，获取对应内容失败', array (), $api_log_id );
        }

        $phone_name      = $device_info['phone_name_nickname'] ? $device_info['phone_name_nickname'] : strtoupper($device_info['phone_name']);
        $phone_version   = $device_info['phone_version_nickname'] ? $device_info['phone_version_nickname'] : strtoupper($device_info['phone_version']);
        $font_color_type = $content_info['font_color_type'] ? $content_info['font_color_type'] : 2 ;

        //指定机型情况
        if ($content_info['is_specify']) {
            // 给原图片压价格操作
            $url = screen_helper::compose_screen_image ( $content_info ['link'], $price, $font_color_type );
        } else {
            // 既压价格也加标题
            $url = screen_content_helper::compose_phone_model_image($phone_name, $phone_version, $content_info['link'], $font_color_type, $price);
        }

        if ( $url ) {
            // 查看数据
            $show_pic_info = _model ( 'screen_show_pic' )->read ( array (
                    'device_unique_id' => $device_info ['device_unique_id']
            ) );

            if ($show_pic_info) {
                _model ( 'screen_show_pic' )->update ( array (
                        'device_unique_id' => $device_info ['device_unique_id']
                ), array (
                        'business_hall_id' => $device_info['business_id'],
                        'content_id'      => $content_info ['id'],
                        'content_link'    => $content_info['link'],
                        'font_color_type' => $font_color_type,
                        'is_specify'      => $content_info['is_specify'],
                        'link'            => $url,
                        'price'           => $price
                ) );
            } else {
                $param = array (
                        'device_unique_id' => $device_info ['device_unique_id'],
                        'business_hall_id' => $device_info ['business_id'],
                        'content_id'       => $content_info ['id'],
                        'font_color_type'  => $font_color_type,
                        'is_specify'       => $content_info['is_specify'],
                        'content_link'     => $content_info['link'],
                        'link'             => $url,
                        'price'            => $price
                );
                _model ( 'screen_show_pic' )->create ( $param );
            }
        }

        // 添加价格统计
        screen_price_helper::record($device_info, $price, $content_info['id']);

        // 最后修改价格
        _model ( 'screen_device' )->update ( $device_info['id'],
                 array (
                    'price' => $price
                ) );

        // 推送
        push_helper::push_msg ( '2' );

        api_helper::return_api_data ( 1000, 'success', array ( 'info' => 'ok' ), $api_log_id );
    }
}