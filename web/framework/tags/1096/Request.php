<?php

/**
 * alltosun.com Request请求信息处理类 Request.php
 * ============================================================================
 * 版权所有 (C) 2007-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 高竞竞 (gaojj@alltosun.com) $
 * $Date: 2009-04-13 23:37:57 +0800 $
 * $Id: Request.php 375 2012-08-06 13:04:11Z gaojj $
*/

/**
 * Request请求信息处理类
 * @author gaojj@alltosun.com
 * @package AnRequest
 */
class Request
{
    /**
     * 按照默认值的类型转换数据类型
     * @param mixed $value
     * @param mixed $default
     * @return mixed
     */
    private static function getValue($value, $default = '')
    {
        if (is_string($default)) {
            return (string)$value;
        }

        if (is_int($default)) {
            return (int)$value;
        }

        if (is_array($default)) {
            return (array)$value;
        }

        if (is_float($default)) {
            return (float)$value;
        }

        return $value;
    }

    /**
     * 判断请求是否是Ajax请求
     * @return bool
     */
    public static function isAjax()
    {
        return 'XMLHttpRequest' == @$_SERVER['HTTP_X_REQUESTED_WITH'];
    }

    /**
     * 判断请求是否是Get请求
     * @return bool
     */
    public static function isGet()
    {
        return 'GET' == self::getMethod();
    }

    /**
     * 判断请求是否是Post请求
     * @return bool
     */
    public static function isPost()
    {
        return 'POST' == self::getMethod();
    }

    /**
     * 判断请求是否是Put请求
     * @return bool
     */
    public static function isPut()
    {
        return 'PUT' == self::getMethod();
    }

    /**
     * 判断请求是否是Delete请求
     * @return bool
     */
    public static function isDelete()
    {
        return 'DELETE' == self::getMethod();
    }

    /**
     * 获取请求方式
     * @return string
     */
    public static function getMethod()
    {
        return @$_SERVER['REQUEST_METHOD'];
    }

    /**
     * 获取客户端ip
     * @return string
     */
    public static function getClientIp()
    {
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
             $onlineip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
             $onlineip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
             $onlineip = $_SERVER['REMOTE_ADDR'];
        } else {
            return '';
        }

        return filter_var($onlineip, FILTER_VALIDATE_IP) !== false ? $onlineip : '';
    }

    /**
     * 获取HTTP_REFERER
     * @return string
     */
    public static function getRf()
    {
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    }

    /**
     * 获取HTTP_USER_AGENT
     * @return string
     */
    public static function getUa()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    }

    /**
     * 先获取$_GET[$key]，不存在再获取$_POST[$key]，值会按照默认值的类型进行类型转换
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getParam($key, $default = '')
    {
        $param = null;

        if (isset($_GET[$key])) {
            $param = $_GET[$key];
        } elseif (isset($_POST[$key])) {
            $param = $_POST[$key];
        }
        // 没有获取到值，则返回默认值
        if ($param === null) {
            return $default;
        }

        return self::getValue($param, $default);
    }

    /**
     * 获取$_GET[$key]，值会按照默认值的类型进行类型转换
     * @param string $key
     * @param mixed $default
     */
    public static function Get($key, $default = '')
    {
        if (isset($_GET[$key])) {
            return self::getValue($_GET[$key], $default);
        }

        return $default;
    }

    /**
     * 获取$_POST[$key]，值会按照默认值的类型进行类型转换
     * @param string $key
     * @param mixed $default
     */
    public static function Post($key, $default = '')
    {
        if (isset($_POST[$key])) {
            return self::getValue($_POST[$key], $default);
        }

        return $default;
    }

    /**
     * 获取命令行执行的argv数组
     * @param array $argv
     * @return array
     */
    public static function getArgv(array $argv = array())
    {
        $result = array();
        $last_arg = null;
        $argv = empty($argv) ? $_SERVER['argv'] : $argv;
        foreach ($argv as $v) {
            if (strncasecmp($v, '--', 2) == 0) {
                // --prefix=/usr/local
                $parts = explode("=", substr($v, 2), 2);
                if (isset($parts[1])) {
                    $result[$parts[0]] = $parts[1];
                } else {
                    $result[$parts[0]] = true;
                }
            } elseif ($v{0} == '-') {
                // ls -a -l; ls -al; php -r last_value
                $string = substr($v, 1);
                $len = strlen($string);
                for ($i = 0; $i < $len; $i++) {
                    $key = $string[$i];
                    $result[$key] = true;
                }
                $last_arg = $key;
            } elseif ($last_arg !== null) {
                // last_arg = last_value
                $result[$last_arg] = $v;
                $last_arg = null;
            }
        }

        return $result;
    }
}
?>