<?php
/**
 * alltosun.com 短连接重定向中转 redirect.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-1-8 下午2:36:22 $
 * $Id$
 */
class Action
{
    public function __call($action = '', $param = array())
    {
        //         return '统计数据升级中，敬请谅解';

        $cache_info = _model('screen_redirect_url_cache')->read(array('cache' => $action));

//         if ( !$cache_info || !$cache_info['url']) return '参数错误';
        // 如果没有存进去
        if ( !$cache_info || !$cache_info['user_number']) return '统计数据升级中，请您登陆';

        $url = AnUrl('screen_dm/device',"?state=check&is_auth=1&user_number={$cache_info['user_number']}");

        Response::redirect($url);
        Response::flush();
        exit;
    }
}