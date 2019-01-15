<?php

/**
 * alltosun.com 提示信息类 Message.php
 * ============================================================================
 * 版权所有 (C) 2009-2011 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 高竞竞 (gaojj@alltosun.com) $
 * $Date: 2010-12-31 下午03:13:08 $
*/

class AnMessage
{
    /**
     * 是否使用当前module模板目录中的提示模板
     * @var bool
     */
    public static $use_module_msg_tpl = false;
    /**
     * 是否使用当前site模板目录中的提示模板
     * @var bool
     */
    public static $use_site_msg_tpl = false;
    /**
     * 提示信息模板名称
     * @var string
     */
    private static $msg_tpl_name = 'msg.html';

    /**
     * 输出提示信息
     * @param mixed $message 提示信息数据 string或者array('msg', 'type', 'url');
     * @tutorial type可以取值success, error, notice, info（默认为info，提示信息）
     * @tutorial 没有设置url或者url为空时返回上一页；
     * @return
     */
    public static function show($message)
    {
        if (Request::isAjax()) {
            self::show_ajax($message);
        } else {
            self::show_template($message);
        }
    }

    /**
     * Ajax调用显示信息，输出json格式数据
     * @param $message
     */
    public static function show_ajax($message)
    {
        if (is_array($message)) {
            // 如果是数组，则直接json_encode
            echo json_encode($message);
        } else {
            // 如果是字符串，则加上info
            echo json_encode(array('info'=>$message));
        }
    }

    /**
     * 正常页面显示信息，输出提示页面
     * @param $message
     */
    public static function show_template($message)
    {
        $message      = (array)$message;
        $msg          = $message[0];
        $type         = isset($message[1]) ? $message[1] : 'info';
        $redirect_url = isset($message[2]) ? htmlspecialchars($message[2]) : '';
        if ($redirect_url) {
            if (strncasecmp($redirect_url, 'http', 4) != 0) {
                if ($redirect_url{0} != '/') {
                    $redirect_url = '/'.$redirect_url;
                }
                $redirect_url = SITE_URL.$redirect_url;
            }

            $meta_redirect = "<meta http-equiv='refresh' content='5; url=$redirect_url'>";

            Response::assign('redirect_url', $redirect_url);
            Response::assign('meta_redirect', $meta_redirect);
        }

        Response::assign('msg', $msg);
//        Response::assign('msg', _($msg));
        Response::assign('type', $type);

        Response::display(self::get_msg_tpl());
    }

    /**
     * 查找提示信息模板的路径
     * 所有后台提示信息，默认先在www的admin下查找，www中没有的话去core的admin中查找
     * 所有手机提示信息，默认先在www的mobile下查找，www中没有的话去mobile module的template中查找
     * 所有前台提示信息，分以下几种情况：
     * 1、如果定义了self::$use_module_msg_tpl属性的话，则去当前的module目录下找
     * 2、如果定义了self::$use_site_msg_tpl属性的话，则去当前的site目录下找
     * 3、默认先在www下查找，www中没有则去core中查找
     * @todo 抽象admin和mobile的判断
     */
    private static function get_msg_tpl()
    {
        $url    = AnUrl::getInstance();
        $dirs   = $url['dirs'];
        $msg_tpl_name = self::$msg_tpl_name;

        // 后台提示信息
        if (!empty($url['dirs'][0]) && $url['dirs'][0] == 'admin') {
            // 默认先在www的admin下查找
            $tpl = Config::get('template_dir').'/admin/'.$msg_tpl_name;
            if (file_exists($tpl)) {
                return $tpl;
            }
            // www中没有的话去core的admin中查找
            return MODULE_CORE.'/template/admin/'.$msg_tpl_name;
        }

        // mobile提示信息
        if (!empty($url['dirs'][0]) && $url['dirs'][0] == 'mobile') {
            // 默认先在www的mobile下查找
            $tpl = Config::get('template_dir').'/mobile/'.$msg_tpl_name;
            if (file_exists($tpl)) {
                return $tpl;
            }

            // www中没有的话去mobile module的template中查找
            return MODULE_PATH.'/mobile/template/'.$msg_tpl_name;
        }
        // 前台提示信息
        if (self::$use_module_msg_tpl) {
            $module = $url['module'];
            return MODULE_PATH.'/'.$module.'/template/'.$msg_tpl_name;
        }

        if (self::$use_site_msg_tpl) {
            $site = $url['site'];
            return Config::get('template_dir').'/'.$site.'/'.$msg_tpl_name;
        }
        // 默认先在www下查找
        $tpl = Config::get('template_dir').'/'.$msg_tpl_name;
        if (file_exists($tpl)) {
            return $tpl;
        }

        // www中没有的话去core中查找
        return MODULE_CORE.'/template/'.$msg_tpl_name;
    }
}
?>