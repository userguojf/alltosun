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
 * $Date: 2017年7月3日 下午12:41:48 $
 * $Id$
 */

class Action
{
    public function get_version_info()
    {
        // 验证接口
        $check_params = array(
        );
        $api_log_id = api_helper::check_sign($check_params, 1);
    
//         if (!$type) {
//             api_helper::return_data(1003, '请选择手机类型');
//         }
    
        //拼装返回数据
        //$version_info = _widget('version')->get_new_version($type);
        $version_info    = _model('screen_version')->read(array('status'=>1), ' ORDER BY `id` DESC ');
        if ($version_info) {
            $result = array(
                'version_no' => $version_info['version_no'],
                'link'       => $version_info['link'],
                'intro'      => $version_info['intro'],
                'add_time'   => $version_info['add_time']
            );
        } else {
            $result = array();
        }
    
    
        api_helper::return_api_data(1000, 'success', $result, $api_log_id);
    }
}