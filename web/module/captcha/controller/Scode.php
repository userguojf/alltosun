<?php
/**
 * alltosun.com 验证码调用 index.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: Shenxn 申小宁 (shenxn@alltosun.com) $
 * $Date: Jul 2, 2014 2:42:21 PM $
 * $Id$
 */

include 'CaptchaText.php';

class Action
{
    public function __call($action = '', $params = array())
    {
        $captcha = new CaptchaText();
        $captcha->start();
    }
}
?>