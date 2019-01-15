<?php

/**
 * alltosun.com tinyurl获取类
 * ============================================================================
 * 版权所有 (C) 2007-2009 北京共创阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 安然 (anr@alltosun.com) $
 * $Date: 2009-04-13 23:37:57 +0800 $
 * $Id: TinyURL.php 143 2012-02-02 17:25:16Z gaojj $
*/

class TinyURL
{
    const MAKE_URL = 'http://tinyurl.com/create.php?url=%s&alias=%s';
    const PREVIEW_URL = 'http://preview.tinyurl.com/%s';

    public static function Make($url, $alias = '')
    {
        $url = sprintf(self::MAKE_URL, urlencode($url), $alias);
        $content = @file_get_contents($url);
        $pattern = '|<b>(http://tinyurl\.com/([^<]+))</b>|i';
        if (preg_match($pattern, $content, $matches))
        {
            return $matches[1];
        }
        return '';
    }

    public static function Preview($url)
    {
        $url = end(explode('.com/', $url));
        $url = sprintf(self::PREVIEW_URL, $url);
        $preview = @file_get_contents($url);
        $pattern = '/id="redirecturl" href="(.*?)">/i';
        if (preg_match($pattern, $preview, $matches))
        {
            return $matches[1];
        }
        return '';
    }
}

?>