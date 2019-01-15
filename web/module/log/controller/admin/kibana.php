<?php

/**
 * alltosun.com 短信日志 message.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 沈飞 (shenf@alltosun.com) $
 * $Date: 2016-4-18 下午12:55:53 $
 * $Id: message.php 375690 2017-10-20 10:27:36Z shenxn $
 */

class Action
{
    private $per_page = 30;

    /**
     * 操作日志信息列表
     * @param unknown_type $action
     * @param array() $params
     */
    public function __call($action = '', $params = array())
    {
        Response::display('admin/kibana.html');
    }
}