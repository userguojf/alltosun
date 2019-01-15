<?php

/**
 * alltosun.com 自动加载类 AutoLoad.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王敬飞 (wangjf@alltosun.com) $
 * $Date: 2017年6月6日 下午6:10:08 $
 * $Id$
 */
class AutoLoad
{
    private static $objects = array();

    //实例
    public static function instance($class)
    {

        //已被实例化
        if (isset(self::$objects[$class]) && is_object(self::$objects[$class])) {
            return self::$objects[$class];
        }

        //src
        $class_path = ROOT_PATH . '/src/' . $class . '.php';

        if (!file_exists($class_path)) {

            //core
            $class_path = ROOT_PATH . '/core/' . $class . '.php';
        }

        if (!file_exists($class_path)) {
            //返回空对象
            return (object)array();
        }

        require $class_path;

        self::$objects[$class] = new $class();
        return self::$objects[$class];

    }
}