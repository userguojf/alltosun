<?php
/**
 * alltosun.com  自定义创建菜单 menu.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-10-17 下午12:24:51 $
 * $Id$
 */

class Action
{
    //靓亮应用ID
    private $agent_id = 27; 

    public function create()
    {

        $data = [];
        //组装数组
        $data['agent_id'] = $this->agent_id;
        $data['data']     = '{
   "button":[
       {
           "name":"菜单",
           "sub_button":[
               {
                   "type":"view",
                   "name":"测试",
                   "url" :"http://201711awifiprobe.alltosun.net/qydev/test"
               }
           ]
      }
   ]
}';

        //调用方法创建菜单
        $info = _widget('qydev.menu')->menu_create($data);

        //查看是否成功  {"errcode":0,"errmsg":"ok"}->成功
        p($info);
    }

    /**
     * 创建帮助与反馈的应用菜单
     */
    public function create_help()
    {
        if (ONDEV) {
            $feedback_url = 'http://201512awifi.alltosun.net/e/admin/help_feedback';
        } else {
            $feedback_url = 'http://wifi.pzclub.cn/e/admin/help_feedback';
        }
        $data['agent_id'] = 28;
        $data['data'] = '{
                   "button":[
                       {
                           "name":"常见问题",
                           "sub_button":[
                               {
                                   "type" : "view",
                                   "name" : "一体化排队",
                                   "url"  : "http://wifi.pzclub.cn/share/pd"
                               },
                               {
                                   "type" : "view",
                                   "name" : "门店爱WiFi",
                                   "url"  : "http://mac.pzclub.cn/faq"
                               },
                               {
                                   "type" : "view",
                                   "name" : "数字地图",
                                   "url"  : "http://wifi.pzclub.cn/share/dm"
                               },
                               {
                                   "type" : "view",
                                   "name" : "摇得",
                                   "url"  : "http://wifi.pzclub.cn/share/ibeacon"
                               },
                               {
                                   "type" : "view",
                                   "name" : "亮屏",
                                   "url"  : "http://mac.pzclub.cn/faq"
                               }
                           ]
                      },
                        {
                           "type" : "click",
                           "name" : "新手视频",
                           "key"  : "novice_video"
                       },
                        {
                           "type" : "view",
                           "name" : "我要反馈",
                           "url"  : "http://201512awifi.alltosun.net/e/admin/help_feedback"
                       }
                   ]
                }';
        //调用方法创建菜单
        $info = _widget('qydev.menu')->menu_create($data);

        //查看是否成功  {"errcode":0,"errmsg":"ok"}->成功
        echo $info;
    }
}