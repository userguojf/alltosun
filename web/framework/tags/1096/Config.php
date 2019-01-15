<?php

/**
 * alltosun.com 配置变量存储 Config.php
 * ============================================================================
 * 版权所有 (C) 2007-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 安然 (anr@alltosun.com) $
 * $Date: 2009-07-23 15:00:14 +0800 $
 * $Id: Config.php 375 2012-08-06 13:04:11Z gaojj $
 * @link http://wiki.alltosun.com/index.php?title=Framework:Config.php
*/

/**
 * 配置变量存储
 * @author anr@alltosun.com
 * @package AnConfig
 * @link http://wiki.alltosun.com/index.php?title=Framework:class:Config
 */
class Config extends ConfigAbstract
{
    /**
     * 加载配置文件
     * @param string $filename 文件名
     * @throws Exception
     */
    public static function loadFile($filename)
    {
        $ext = strtolower(end(explode('.', $filename)));
        $config = array();

        if ($ext == 'php') {
            $config = include $filename;
        } elseif ($ext == 'ini') {
            $config = parse_ini_file($filename, true);
        } elseif ($ext == 'xml') {
            $config = simplexml_load_file($filename);
            if ($config) {
                $config = (array)$config;
            }
        } elseif ($ext == 'yaml') {
            // can load yaml if load the module syck <http://pecl.php.net/package/syck> or have the function
            if (function_exists('syck_load')) {
                $config = syck_load($filename);
            }
        }

        if (!is_array($config) || empty($config)) {
            throw new Exception("Can't load the file: $filename");
        }

        return self::set($config);
    }

    /**
     * 设置配置的key, value
     */
    public static function set()
    {
        parent::$ns = __CLASS__;
        $params = func_get_args();
        return call_user_func_array(array('parent','_set'), $params);
    }

    /**
     * 获取指定key的配置
     */
    public static function get()
    {
        parent::$ns = __CLASS__;
        $params = func_get_args();
        return call_user_func_array(array('parent','_get'), $params);
    }
}

/**
 * 配置变量存储抽象类
 * @author anr@alltosun.com
 * @package AnConfig
 * @link http://wiki.alltosun.com/index.php?title=Framework:class:ConfigAbstract
 */
abstract class ConfigAbstract
{
    /**
     * 信息存储区
     * @var array
     */
    private static $bag = array();

    /**
     * 当前配置的namespace
     * @var string
     */
    protected static $ns = '';

    /**
     * 设置配置的key, value
     * @param string|array $key
     * @param mixed $value
     * @return true
     */
    protected static function _set($key, $value = '')
    {
        if (!is_array($key)) {
            $key = array($key=>$value);
        }

        foreach ($key as $k=>$v) {
            self::$bag[self::$ns.$k] = $v;
        }

        return true;
    }

    /**
     * 获取指定key的配置
     * @param string|array $key
     * @return mixed
     */
    protected static function _get($key)
    {
        if (func_num_args() > 1) {
            $key = func_get_args();
        }

        if (is_array($key)) {
            $out = array();
            foreach ($key as $v) {
                $out[] = self::_get($v);
            }

            return $out;
        }

        if (!isset(self::$bag[self::$ns.$key])) {
            return null;
        }

        return self::$bag[self::$ns.$key];
    }

    /**
     * 删除指定key的配置
     * @param string $key
     * @return true
     */
    protected static function _remove($key)
    {
        if (isset(self::$bag[self::$ns.$key])) {
            unset(self::$bag[self::$ns.$key]);
        }

        return true;
    }
}
?>