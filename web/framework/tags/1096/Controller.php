<?php

/**
 * alltosun.com 控制器基类
 * ============================================================================
 * 版权所有 (C) 2007-2009 北京共创阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 安然 (anr@alltosun.com) $
 * $Date: 2009-08-23 20:31:12 +0800 $
 * $Id: Controller.php 203 2012-04-08 14:45:23Z gaojj $
*/

abstract class Controller
{
    private static $self = null;

    public static function dispatch($path = './controller')
    {
        $url = trim(@$_GET['url'], ' /');
        // trim the url extention (xxx/xxx.html or yyy/yyy.asp or any extention)
        list($url) = explode('.', $url, 2);
        $tmp = array_filter(explode('/', $url));

        $seq = 0;
        $count = count($tmp);
        for ($i = 0; $i < $count; $i++) {
            if (!is_dir($path.'/'.$tmp[$i])) {
                break;
            }
            $path .= '/'.$tmp[$i];
            $seq++;
        }

        if ($seq > 0) {
            $tmp = array_slice($tmp, $seq);
        }

        $controller = array_shift($tmp);
        !$controller && $controller = 'index';
        $action = array_shift($tmp);
        !$action && $action = 'index';
        $file = $path.'/'.$controller.'.php';

        if (!file_exists($file)) {
            $action = $controller;
            $controller = 'index';
            $file = $path.'/'.$controller.'.php';
            // throw new Exception("controller not exists: $controller");
        }

        require $file;
        self::$self = new Action($controller, $action, $tmp);

        // if (!is_callable(array(self::$self, $action))) {
        //     array_unshift($tmp, $action);
        //     $action = 'index';
        // }

        // if (method_exists(self::$self, 'initialization')) {
        //     self::$self->initialization();
        // }

        return call_user_func_array(array(self::$self, $action), $tmp);
    }
}
?>