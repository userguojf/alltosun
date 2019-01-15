<?php

/**
 * alltosun.com cookie操作封装 Cookie.php
 * ============================================================================
 * 版权所有 (C) 2007-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 高竞竞 (gaojj@alltosun.com) $
 * $Date: 2009-08-23 20:31:12 +0800 $
 * $Id: Cookie.php 742 2013-11-21 15:14:08Z gaojj $
 * @link http://wiki.alltosun.com/index.php?title=Framework:Cookie.php
*/

/**
 * cookie操作封装
 * @author gaojj@alltosun.com
 * @link http://wiki.alltosun.com/index.php?title=Framework:Cookie
 */
class Cookie
{
    /**
     * cookie失效时间
     * @var int
     */
    private static $expire = 1036800;

    /**
     * cookie起作用的路径，默认为'/'，在整个域名下都有效
     * @var string
     */
    private static $path = '/';

    /**
     * cookie起作用的域名
     * @var string
     */
    private static $domain = '';

    /**
     * 是否启用只在安全的HTTPS连接下传输cookie；默认不启用
     * @var bool
     */
    private static $secure = false;

    /**
     * 是否只能在http协议中才能获取到该cookie
     * @var null
     */
    private static $httponly = null;

    /**
     * 随机密钥，用于cookie加密。可自由设定
     * @var string
     */
    public static $publickey = '01CVhZIgSogB85WNhwYVk79A==';

    /**
     * 设置cookie起作用的域名
     * 设置为.example.com则在所有子域名下都有效
     * @param string $domain
     */
    public static function set_domain($domain)
    {
        self::$domain = $domain;
    }

    /**
     * 设置是否启用只在安全的HTTPS连接下传输cookie
     * @param bool $secure
     */
    public static function set_secure($secure)
    {
        self::$secure = $secure ? true : false;
    }

    /**
     * 设置cookie起作用的路径
     * @param string $path
     */
    public static function set_path($path)
    {
        self::$path = $path;
    }

    /**
     * 设置cookie（cookie的值经过加密设置）
     * @param string $key
     * @param string $value
     * @param int $expire
     * @param string $path（V1.1.2新增）
     * @param string $domain（V1.1.2新增）
     * @param bool $secure（V1.1.2新增）
     * @param bool $httponly（V1.1.2新增）
     * @return bool
     */
    public static function set($key, $value, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        if ($httponly === null) $httponly = self::$httponly;
        if ($secure === null) $secure = self::$secure;
        if ($domain === null) $domain = self::$domain;
        if ($path === null) $path = self::$path;

        $expire = $expire === null ? time() + self::$expire : $expire;

        $value_encrypted = self::encrypt($value);

        // fix#2907 Cookie::get必须经过刷新才能取到正确的值
        $_COOKIE[$key] = $value_encrypted;

        return setcookie($key, $value_encrypted, $expire, $path, $domain, $secure, $httponly);
    }

    /**
     * 获取指定的cookie值（经过解密后的cookie值）
     * @param string $key
     * @return string
     */
    public static function get($key)
    {
        if (!isset($_COOKIE[$key])) {
            return '';
        }

        if (!self::validate($_COOKIE[$key])) {
            return '';
        }

        return self::decrypt($_COOKIE[$key]);
    }

    /**
     * 删除指定的cookie
     * @param string $key
     * @return boolean
     */
    public static function del($key)
    {
        return self::set($key, '', -1);
    }

    /**
     * 删除所有cookie
     */
    public static function clean()
    {
        foreach ($_COOKIE as $key => $val) {
            self::del($key);
        }
    }

    /**
     * cookie值加密
     * @param string $value
     * @return string
     */
    private static function encrypt($value)
    {
        // 空值不加密
        if (!$value) return '';

        $cookie_key = defined("SITE_COOKIE_KEY") ? SITE_COOKIE_KEY : '';
        return $value.substr(md5($value.$cookie_key.self::$publickey), 0, 16);
    }

    /**
     * cookie值解密
     * @param string $value_encrypted
     * @return string
     */
    private static function decrypt($value_encrypted)
    {
        // 空值不解密
        if (!$value_encrypted) return '';

        return substr($value_encrypted, 0, -16);
    }

    /**
     * 验证cookie值是否被窜改
     * @param string $value_encrypted
     * @return boolean
     */
    private static function validate($value_encrypted)
    {
        return $value_encrypted == self::encrypt(self::decrypt($value_encrypted));
    }
}
?>