<?php

/**
 * alltosun.com Response类 Response.php
 * ============================================================================
 * 版权所有 (C) 2007-2009 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 高竞竞 (gaojj@alltosun.com) $
 * $Date: 2009-07-23 17:36:09 +0800 $
 * $Id: Response.php 659 2013-04-19 01:31:40Z anr $
*/

class Response
{
    private static $html = '';
    //状态相关
    private static $status_code = '200';
    private static $status_msg = 'ok';
    private static $status_url = '';

    //模板相关
    private static $tpl= null;


    public static function setView($obj)
    {
        self::$tpl = $obj;
    }

    public static function getView()
    {
        return self::$tpl;
    }

    public static function fetch($file)
    {
        return self::$tpl->fetch($file);
    }

    public static function display($file)
    {
        self::setContent(self::fetch($file));
    }

    public static function assign($tpl_var, $value = null)
    {
        self::$tpl->assign($tpl_var, $value);
    }

    public static function setStatus($code, $msg = '')
    {
        self::$status_code = $code;
        self::$status_msg = $msg;
    }

    public static function set404($msg = 'Not Found')
    {
        self::setStatus(404, $msg);
    }

    /**
     * url重定向
     * @param mixed $url 重定向的目标url
     * @param int $status_code 重定向的状态码（默认为302，临时跳转；可设置301，永久跳转）
     */
    public static function redirect($url = null, $status_code = 302)
    {
        $site = 'http://' . $_SERVER['HTTP_HOST'];
        if (!$url) {
            $url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $site;
        }

        if (substr($url, 0, 4) != 'http') {
            if ($url{0} != '/') {
                $url = '/'.$url;
            }
            $url = $site.$url;
        }

        self::setStatus($status_code, 'See Other');
        self::$status_url = $url;
    }

    public static function setContent($html)
    {
        self::$html = $html;
    }

    public static function getContent()
    {
        return self::$html;
    }

    public static function flush()
    {
        header('Poweredy-By: AnPHP' . (!empty(AnPHP::$version) ? '/' . AnPHP::$version :'') . '; Alltosun.com');
        switch (self::$status_code) {
            case 301:
                header('HTTP/1.1 301 Moved Permanently');
                header('Location: '.self::$status_url);
                break;
            case 302:
                header('HTTP/1.1 302 See Other');
                header('Location: '.self::$status_url);
                break;
            case 404:
                header('HTTP/1.1 404 Not Found');
                echo self::$status_msg;
                break;
            default:
                echo self::$html;
                break;
        }
    }
}
?>