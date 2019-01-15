<?php

/**
 * alltosun.com
 * ============================================================================
 * 版权所有 (C) 2007-2009 北京共创阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 高竞竞 (gaojj@alltosun.com) $
 * $Date: 2009-04-13 23:37:57 +0800 $
 * $Id: reCAPTCHA_Mailhide.php 203 2012-04-08 14:45:23Z gaojj $
*/

// get the key http://mailhide.recaptcha.net/apikey

class reCAPTCHA_Mailhide
{
    const URL = 'http://mailhide.recaptcha.net/d?k=%s&c=%s';
    public static $publickey = '010c0p4zX8-Hd906C1YKEnTg==';
    public static $privatekey = 'CFBF75B2D639E0D8AE791CC699346C18';

    public static function generate($email, $title = 'Reveal this e-mail address')
    {
        $arr = explode('@', $email);

        $len = 4;
        if (strlen($arr[0]) <= 4)
        {
            $len = 1;
        }
        elseif (strlen($arr[0]) <= 6)
        {
            $len = 3;
        }

        $arr[0] = substr($arr[0], 0, $len);

        $url = sprintf(self::URL, self::$publickey, self::encrypt($email));

        return "$arr[0]<a href=\"$url\" onclick=\"window.open('$url', '', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=500,height=300'); return false;\" title=\"$title\">...</a>@$arr[1]";
    }

    private static function encrypt($email)
    {
        $numpad = 16 - (strlen ($email) % 16);
        $email = str_pad($email, strlen($email) + $numpad, chr($numpad));

        $email = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, pack('H*', self::$privatekey), $email, MCRYPT_MODE_CBC, "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0");

        return strtr(base64_encode ($email), '+/', '-_');
    }
}

//reCAPTCHA_Mailhide
?>