<?php
/**
 * alltosun.com  index.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 申小宁 (shenxn@alltosun.com) $
 * $Date: 2017-4-6 下午6:28:28 $
 * $Id$
 */

class Action
{
    public function __call($action = '', $params = array())
    {
//         p(_widget('qydev.department')->get_department_list(array('id' => 2)));

        qydev_helper::check_qydev_auth(AnUrl('qydev'));

        echo "我登录了".qydev_helper::get_qydev_user_id();
    }

    public function get_cy()
    {
        qydev_helper::check_qydev_auth(AnUrl('qydev'));

        echo "我登录了".qydev_helper::get_qydev_user_id();

        $access_token = _widget('qydev.token')->get_access_token();
        $url = "https://qyapi.weixin.qq.com/cgi-bin/user/get?access_token={$access_token}&userid=JT_0000_01";

        p(json_decode(curl_get($url,true),true));
    }
}