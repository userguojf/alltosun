<?php
/**
 * alltosun.com  import_user.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-7-4 下午12:46:18 $
 * $Id$
 */

class import_user
{
    private $access_token = '';

    public function __construct()
    {
        $this->access_token = _widget('qydev.token')->get_access_token();

        if (!$this->access_token) {
            return false;
        }
    }

    
}