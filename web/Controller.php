<?php
/**
 * alltosun.com 主控制器 Controller.php
 * ============================================================================
 * 版权所有 (C) 2009-2011 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 高竞竞 (gaojj@alltosun.com) $
 * $Date: 2010-12-28 下午03:29:31 $
 * $Id: Controller.php 8873 2012-09-18 03:30:05Z liw $
*/

abstract class Controller
{
    /**
     * 控制器实例
     */
    private static $actionInstance = null;

    /**
     * 控制器分派
     */
    public static function dispatch()
    {
        // 定义路由规则
        $rule = AnRule::getInstance();
        //文件夹为admin
        $rule->set('{$site=liangliang}/*');
        $rule->set('{$module|AnModule::isInstalled}/{$site=admin}/*');
        $rule->set('{$module|AnModule::isInstalled}/{$proxy=proxy}/{$type=widget}/{$name}/{$func}/*');
        $rule->set('{$module|AnModule::isInstalled}/*');
        $rule->set('{$proxy=proxy}/{$type=widget}/{$name}/{$func}/*');

        // 路由解析规则
        AnRouter::getInstance()->dispatch($rule);
        // 获取解析后的Url信息
        $url = AnUrl::getInstance();

        global $redirect_url;

        $redirect_url = $url['module'];

        if(!empty($url['controller'])){
            $redirect_url = $redirect_url.'/'.$url['controller'];
        }

        if(!empty($url['action'])){
            $redirect_url = $redirect_url.'/'.$url['action'];
        }

        // 加载site控制器
        if (!empty($url['site'])) {
            //亮屏兼容
            if ($url['site'] == 'liangliang') {
                $site_controller = "adminController";
            } else {
                $site_controller = "{$url['site']}Controller";
            }

            if (class_exists($site_controller)) {

                try {
                    $c = new $site_controller();
                    call_user_func(array($c, 'init'));
                } catch (Exception $e) {
                    // @TODO 改为定义不同的异常
                    AnMessage::show($e->getMessage());
                    return;
                }
            }
        }

        // 调用模块
        if (AnModule::isInstalled($url['module'])) {
            AnModule::invoke($url['module']);
        }

        // 代理解析
        if (!empty($url['proxy'])) {
            $widget_name = !empty($url['module']) ? $url['module'].'.'.$url['name'] : $url['name'];
            $params = array_merge(array('model'=>$widget_name, 'func'=>$url['func']), $_GET);
            $smarty = Response::getView();
            $message = smarty_function_widget($params, $smarty);
            if (!empty($message)) AnMessage::show($message);
            return;
        }
        // 先加载控制器文件
        require self::getControllerFile();

// note by guojf
//         $curr_module = $url['module'].'/'.$url['site'];
//         $specal_model = $url['module'].'/'.$url['site'].'/'.$url['controller'];
//         Response::assign('specal_model',   $specal_model);
//         Response::assign('curr_module',   $curr_module);
//         Response::assign('curr_action',$curr_action);
        Response::assign('site',       $url['site']);
        Response::assign('module',     $url['module']);
        Response::assign('controller', $url['controller']);
        Response::assign('action',     $url['action']);
        Response::assign('jumpurl',    '');

        //////////////获取AnUrl第一个参数 start  add by guojf//////////////
        // 目录
        $sidebar_default_dirs = $url['module'];

        if ( $url['dirs'] ) {
            foreach ($url['dirs'] as $key => $val) {
                if ( $val == 'index' ) continue;
                $sidebar_default_dirs = $sidebar_default_dirs.'/'.$val;
            }
        } else {
            $sidebar_default_dirs = $sidebar_default_dirs.'/'.$url['site'];
        }
        // 控制器文件
        $sidebar_default_controller_file = $url['controllerFile'] && $url['controllerFile'] == 'index' ? $sidebar_default_dirs : $sidebar_default_dirs.'/'.$url['controllerFile'];
        // 控制器   'controller' => null 有这种情况
        $sidebar_default_controller = $url['controllerFile'] == $url['controller'] ? $sidebar_default_controller_file : $sidebar_default_controller_file.'/'.$url['controller'];
        // 去掉多余的 /
        $sidebar_default_controller = trim($sidebar_default_controller, '/');
        // 方法
        $sidebar_default_action = $url['action'] && $url['action'] == 'index' ? $sidebar_default_controller : $sidebar_default_controller.'/'.$url['action'];
        // 分配
        Response::assign('sidebar_default_selected', trim($sidebar_default_action, '/'));

        //////////////获取AnUrl第一个参数 end//////////////

        // 可以不传参，直接在方法里获取$url['controller']，传参是为兼容之前的写法
        self::$actionInstance = new Action($url['controller'], $url['action'], $url['params']);

        // 如果action不存在，且__call也不存在，则报404错误
        if (!method_exists(self::$actionInstance, $url['action']) && !method_exists(self::$actionInstance, '__call')) {
            $msg = 'Not Found';
            if (ONDEV) {
                $msg = 'Controller:'.$url['controller'].'; Action:'.$url['action'].'; Not Found';
            }
            Response::set404($msg);
            return;
        }

        // 处理__construct中的错误信息提示
        // @TODO 改成在__construct中抛出异常
        if (!empty(self::$actionInstance->error)) {
            // 错误代码对应提示信息
            // @TODO 改为定义不同的异常
            $errors = Config::get('error');
            $code = self::$actionInstance->error;
            AnMessage::show(array($errors[$code]['notice'], $errors[$code]['level'], $errors[$code]['link']));
            return;
        }

        $message = call_user_func_array(array(self::$actionInstance, $url['action']), array($url['params']));

        if (!empty($message)) AnMessage::show($message);
    }

    /**
     * 获取控制器文件路径
     * @TODO 移动到AnRule中
     */
    private static function getControllerFile()
    {
        $defaultController = AnRouter::$defaultController;
        $url  = AnUrl::getInstance();
        $dirs = '';

        if (!empty($url['module'])) {
            // 加载模块自己的controller
            $controller_dir = MODULE_PATH."/{$url['module']}/controller";
        } else {
            $controller_dir = Config::get('controller_dir');
        }

        if (empty($url['controllerFile'])) {
            $url['controllerFile'] = $defaultController;
        }
        if (empty($url['action'])) {
            $url['action'] = 'index';
        }
        if (!empty($url['dirs'])) {
            $dirs = implode('/', $url['dirs']).'/';
        }

        $file = $controller_dir.'/'.$dirs.$url['controllerFile'].'.php';

        if (file_exists($file)) {
            return $file;
        }

        // 如果控制器不存在的话，统一走默认控制器，此时控制器和控制器文件都为默认控制器
        $params = $url['params'];
        array_unshift($params, $url['action']);
        $url['params'] = $params;
        $url['action'] = $url['controllerFile'];
        $url['controller'] = $url['controllerFile'] = $defaultController;
        $file = $controller_dir.'/'.$dirs.$url['controllerFile'].'.php';
        return $file;
    }
}

class adminController implements controllerInterface
{
    public function init()
    {
        //获取解析后的Url信息
        $url = AnUrl::getInstance();

        $member_id = member_helper::get_member_id();

        if (!$member_id) {
            //除了微信绑定的页面
            if (!empty($url['module']) && $url['module'] == 'e') {
                if (!is_weixin() && $url['controllerFile'] != 'wechat') {
                    Response::redirect(AnUrl('liangliang/e_login'));
                    Response::flush();
                }

            } else if (!empty($url['module'])) {
                Response::redirect(AnUrl('liangliang/login'));
                Response::flush();
            }
            return false;
        }

        if ($url['site'] == 'admin' || $url['site'] == 'liangliang') {
            if ($url['controller'] != 'login_auth' && $url['controller'] !='logout') {

                $dir = '';
                // 存在目录情况下
                if (isset($url['dirs'])) {
                    $dir = join($url['dirs'], '/');
                }

                $action_url = trim($url['module'].'/'.$dir.'/'.$url['controller'].'/'.$url['action'], '/');
                action_helper::action_controller($action_url);
            }

        }

        $member_info = member_helper::get_member_info($member_id);

        Response::assign('member_id', $member_id);
        Response::assign('member_info', $member_info);
    }
}

interface controllerInterface
{
    public function init();
}

