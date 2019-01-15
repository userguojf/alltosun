<?php
/**
 * alltosun.com  captcha.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王德康 (wangdk@alltosun.com) $
 * $Date: 2013-9-5 上午11:35:51 $
 * $Id$
 */

class Action
{
    public function __call($action = '', $params = array())
    {
        $captcha_config = Config::get('captcha');
// p($captcha_config);exit();
        $captcha_config['image_width'] = Request::Get('w', $captcha_config['image_width']);
        $captcha_config['image_height'] = Request::Get('h', $captcha_config['image_height']);

        Captcha::outputImage($captcha_config);
    }

    public function check()
    {
        $captcha = Request::Get('captcha');

        if (empty($captcha)) return '请输入验证码';

        if (empty($_SESSION['securimage_code_value'])) return '验证码已过期，请点击图片刷新';

        $captcha = strtolower($captcha);
        $code    = strtolower($_SESSION['securimage_code_value']);

        if ($captcha != $code) {
            return '请按照图片中的字符正确填写验证码，不区分大小写';
        }

        return 'ok';
    }
}
?>