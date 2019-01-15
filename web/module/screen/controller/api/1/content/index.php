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
 * $Date: 2017年7月3日 下午5:38:31 $
 * $Id$
 */

class Action
{
    public function get_content()
    {
        $user_number     = tools_helper::post('user_number', '');
        $check_params = array(
            //'user_number'  => $user_number
        );

        $api_log_id = api_helper::check_sign($check_params, 0);

        if (!$user_number) {
            api_helper::return_api_data(1003, '请输入营业厅的视图编码');
        }

        $filter['user_number'] = $user_number;

        $business_info = _uri('business_hall', $filter);

        if (!$business_info) {
            api_helper::return_api_data(1003, '营业厅不存在');
        }

        $result_info = array(
            'id'          => $business_info['id'],
            'title'       => $business_info['title'],
            'user_number' => $business_info['user_number'],
            'address'     => $business_info['address']
        );

        $content = '';
        foreach (screen_config::$content_put_type as $k => $v) {

            $content_filter = array(
                'start_time  <= '   => date('Y-m-d H:i:s'),
                'end_time >= '      => date('Y-m-d H:i:s'),
                'status'            => 1
            );

            $ids = _model('screen_content')->getFields('id', $content_filter);

            if (!$ids) {
                continue;
            }

            $content_res_filter = array(
                    'content_id' => $ids,
                    'res_name'   => $k
            );

            if ($k != 'group') {
                if ($k == 'business_hall') {
                    $content_res_filter['res_id'] = $business_info['id'];
                } else {
                    $content_res_filter['res_id'] = $business_info["{$k}_id"];
                }

            }

            //p($content_res_filter);
            $content_res = _model('screen_content_res')->read($content_res_filter, ' ORDER BY `content_id` DESC');

            if (!$content_res) {
                continue;
            }

            $content = _uri('screen_content', $content_res['content_id']);

            if ($content) {
                $content['link'] = _image($content['link']);
                api_helper::return_api_data(1000, 'success', $content, $api_log_id);
            }

        }


        api_helper::return_api_data(1000, 'success', $content, $api_log_id);
    }
}