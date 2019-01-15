<?php
/**
 * 权限控制模块
 * 1、依赖模块 tools
 * 2、Controller.php 中增加权限控制
 * 3、action.sql 中  user 2为默认超级管理员
 * 权限控制代码
 * public function init()
    {
        // 权限控制
        $url = AnUrl::getInstance();

        if ($url['site'] == 'admin') {
            if ($url['module'] == 'user' && $url['controller'] == 'login' && $url['action'] == '') {
                // 登录接口要放开
            } else {

                $dir = '';

                // 存在目录情况下
                if (isset($url['dirs'])) {
                    $dir = join($url['dirs'], '/');
                }

                $action_url = trim($url['module'].'/'.$dir.'/'.$url['controller'].'/'.$url['action'], '/');
                action_helper::action_controller($action_url);
            }
        }
    }
 */


?>