<?php

/**
 * alltosun.com  ajax.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 申小宁 (guojf@alltosun.com) $
 * $Date: 2017-7-9 下午4:12:29 $
 * $Id$
 */

require MODULE_PATH.'/screen/helper/screen_helper_push.php';

class Action
{
    public function __call($action = '', $params = array())
    {
        $msg = '1';

        screen_helper_push::push($msg);
    }
}