<?php
/**
 * alltosun.com  s.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-5-9 下午6:55:22 $
 * $Id$
 */

class Action
{
    public function __call($action = '', $param = array())
    {
        // 点击链接添加类型字段
        $type = array(4, 5);

        $cache_info = _model('screen_redirect_url_cache')->read(
                array(
                        'cache'       => $action,
                        'type'        => $type,
                    )
        );

        if ( !$cache_info ) return '系统升级中，请稍后';

        _model('screen_push_click_url_record')->create(array(
            'user_number' => $cache_info['user_number'],
        ));

        Response::redirect($cache_info['url']);
        Response::flush();
        exit;
    }

    public function loop_dir($dir)
    {

        $handle = opendir($dir);

        while (false !== ($file = readdir($handle))) {
            if ( $file === '.'  && $file === '..') {
                continue;
            }
            echo $file;
            if ( filetype($dir .'/'.$file) !== 'dir' ) {
                loop_dir($dir .'/'.$file);
            }
        }

        
    }
}