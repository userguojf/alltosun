<?php

/**
 * alltosun.com Module模块类 Module.php
 * ============================================================================
 * 版权所有 (C) 2009-2011 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 高竞竞 (gaojj@alltosun.com) $
 * $Date: 2011-1-1 下午01:13:35 $
 * $Id: Module.php 656 2013-04-15 17:43:04Z anr $
*/

class AnModule
{
    /**
     * 判断模块是否安装
     * @param string $moduleName
     */
    public static function isInstalled($moduleName)
    {
        return !empty($moduleName) && is_dir(MODULE_PATH . '/' . $moduleName . '/');
    }

    /**
     * 调用模块控制器
     * @param string $moduleName
     */
    public static function invoke($moduleName)
    {
        self::resetTemplateDir($moduleName);
        self::loadController($moduleName);
    }

    /**
     * 加载所有模块
     */
    public static function loadAll()
    {
        foreach (new DirectoryIterator(MODULE_PATH) as $fileInfo) {
            if ($fileInfo->isDot() || !$fileInfo->isDir()) continue;
            $moduleName = $fileInfo->getFilename();
            // .svn
            if (strpos($moduleName, '.') === false) {
                self::load($moduleName);
            }
        }
    }

    /**
     * 加载模块配置、助手
     * @param string $moduleName
     */
    public static function load($moduleName)
    {
        self::loadConfig($moduleName);
        self::loadHelper($moduleName);
    }

    /**
     * 重置Smarty模板目录
     * @param string $moduleName
     */
    private static function resetTemplateDir($moduleName)
    {
        Response::getView()->setTemplateDir(Config::get('template_dir').'/'.$moduleName);
        Response::getView()->addTemplateDir(MODULE_PATH."/$moduleName/template");
    }

    /**
     * 加载模块控制器
     * @param string $moduleName
     */
    private static function loadController($moduleName)
    {
        $controllerFile = MODULE_PATH . '/' . $moduleName . '/controller.php';
        if (!file_exists($controllerFile)) {
            return false;
        }
        require_once $controllerFile;

        $moduleController = $moduleName . 'Controller';
        if (!class_exists($moduleController)) {
            return false;
        }

        $cI = new $moduleController();
        call_user_func(array($cI, 'init'));
        return true;
    }

    /**
     * 加载模块配置
     * @param string $moduleName
     */
    private static function loadConfig($moduleName)
    {
        $configFile = MODULE_PATH . '/' . $moduleName . '/config/config.php';
        if (!file_exists($configFile)) {
            return false;
        }
        require_once $configFile;
        return true;
    }

    /**
     * 加载模块助手
     * @param string $moduleName
     */
    private static function loadHelper($moduleName)
    {
        $helperFile = MODULE_PATH . '/' . $moduleName . '/helper/common.php';
        if (!file_exists($helperFile)) {
            return false;
        }
        require_once $helperFile;
        return true;
    }
}
?>