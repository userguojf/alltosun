<?php

/**
 * alltosun.com  call_back.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2018年1月8日 下午5:00:28 $
 * $Id$
 */

class Action
{
    public function index()
    {
        $request_time = tools_helper::post('request_time', '1515463611788');
        $request_path = tools_helper::post('request_path', '/screen/api/3/content/content_stat/add_content_stat');
        
        $post_data['time']  = $request_time;
        $post_data['request_path']  = $request_path;
        
        an_dump($post_data);
        
        $api_url = SITE_URL.'/screen/api/3/call_back/call_back';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;
        
        an_dump(json_decode($res, true));
    }
}