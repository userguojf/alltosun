<?php
/**
 * alltosun.com  news.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-4-23 下午12:26:56 $
 * $Id$
 */

class Action
{
    public function __construct()
    {
    }

    public function __call($action = '' , $param = array())
    {
        $touser   = '1101021002051_13';
        $agent_id = 27;
        $title       = '测试开发';
        $description = '测试内容';
        
        // $url = "http://mac.pzclub.cn/screen_dm/device?state=install";
        // $url = AnUrl('screen_dm','?state=install');
        $url = "http://mac.pzclub.cn/screen_dm/device?state=install";
        
                $params = '{"touser" : "' . $touser . '",
                "msgtype": "news",
                       "agentid": ' . $agent_id .',
                        "news": {
                           "articles":[
                               {
                                   "title"      : "' . $title . '",
                                "description": "' . $description . '",
                                        "url"        : "' . $url . '",
        },
                           ]
        }
        }';
        
        $info = _widget ( 'qydev.send_msg' )->send_message ($touser, $params, $agent_id );
        
    }
}