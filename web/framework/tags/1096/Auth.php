<?php

/**
 * alltosun.com HTTP 401验证类 Auth.php
 * ============================================================================
 * 版权所有 (C) 2007-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 高竞竞 (gaojj@alltosun.com) $
 * $Date: 2009-08-23 20:31:12 +0800 $
 * $Id: Auth.php 375 2012-08-06 13:04:11Z gaojj $
*/

/**
 * HTTP 401验证类
 * @author gaojj@alltosun.com
 * @package AnRequest
 */
class Auth
{
    /**
     * 授权信息数组
     * @var array
     */
    private static $auths = array();

    /**
     * 提示信息
     * @var string
     */
    public static $desc = 'input your information';

    /**
     * 添加授权用户和密码
     * @param string $user
     * @param string $password
     */
    public static function add($user, $password)
    {
        self::$auths[$user] = crypt($password);
    }

    /**
     * 加载授权文件
     * @param string $filename
     */
    public static function loadFile($filename)
    {
        $array = file($filename);
        foreach ($array as $key => $val) {
            $val = trim($val);
            if (!$val || $val{0} == '#' || strpos($val, ':') == false) {
                continue;
            }
            list($k, $v) = explode(':', $val, 2);
            self::$auths[$k] = $v;
        }
    }

    /**
     * 强制验证
     * @param string $desc 提示信息
     * @param string $cancel_info 取消时提示信息
     */
    public static function forceCheck($desc = '', $cancel_info = '')
    {
        if (!self::isVaild()) {
            self::doAuth($desc, $cancel_info);
        }
    }

    /**
     * 输出验证失败信息
     * @param string $desc 提示信息
     * @param string $cancel_info 取消时提示信息
     */
    public static function doAuth($desc = '', $cancel_info = '')
    {
        header('WWW-Authenticate: Basic realm="' . $desc . '"');
        header('HTTP/1.0 401 Unauthorized');
        echo $cancel_info;
        exit;
    }

    /**
     * 检测用户提交的信息是否通过验证
     * @return bool
     */
    public static function isVaild()
    {
        if (empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])) {
            return false;
        }

        if (!isset(self::$auths[$_SERVER['PHP_AUTH_USER']])) {
            return false;
        }
        $password = self::$auths[$_SERVER['PHP_AUTH_USER']];

        return crypt($_SERVER['PHP_AUTH_PW'], $password) == $password;
    }

    /**
     * 获取验证用户名
     * @return string
     */
    public static function getName()
    {
        if (self::isVaild()) {
            return $_SERVER['PHP_AUTH_USER'];
        }

        return '';
    }
}
?>