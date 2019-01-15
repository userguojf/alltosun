<?php

/**
 * alltosun.com 多语言支持
 * ============================================================================
 * 版权所有 (C) 2007-2009 北京共创阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 高竞竞 (gaojj@alltosun.com) $
 * $Date: 2009-03-21 18:48:03 +0800 $
 * $Id: Locale.php 203 2012-04-08 14:45:23Z gaojj $
*/


class Locale
{
    private static $domain = './locale';

    public static function bindDomain($domain)
    {
        self::$domain = $domain;
    }

    public static function set($locale = 'zh_CN', $package = 'default')
    {
        putenv('LANG=$locale');
        setlocale(LC_ALL, $locale);
        bindtextdomain($package, self::$domain);
        textdomain($package);
        //bind_textdomain_codeset($package,'UTF-8');
    }
}

?>