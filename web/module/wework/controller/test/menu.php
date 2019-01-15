<?php
/**
 * alltosun.com 测试方法 test.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-1-20 下午6:09:01 $
 * $Id$
 */

class Action
{
    public function index()
    {
//         global $mc_wr;
//         $mc_wr->get('wework_access_token_1000002');
//         p($res);exit();
        $param['agent_id'] = 1000002;
        $param['data'] = '{
                   "button":[
                       {    
                           "type":"click",
                           "name":"精彩回顾",
                           "key":"alltosun_jc"
                       },
                       {
                           "name":"业务",
                           "sub_button":[
                               {
                                   "type":"view",
                                   "name":"手机亮屏",
                                   "url":"http://mac.pzclub.cn/screen_dm/device"
                               },
                               {
                                   "type":"view",
                                   "name":"添加终端",
                                   "url":"http://mac.pzclub.cn/e/admin/rfid/add"
                               }
                           ]
                      }
                   ]
                }';

        _widget('wework.menu')->create($param);
    }
}