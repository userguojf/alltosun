<?php

/**
 * alltosun.com Router路由类 Router.php
 * ============================================================================
 * 版权所有 (C) 2009-2011 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 高竞竞 (gaojj@alltosun.com) $
 * $Date: 2010-12-31 下午03:26:18 $
*/

class AnRouter
{
    public $rewriteMode = 1;
    public $urlSuffix = 'html';
    public static $defaultController = 'index';
    private static $instance;
    private $url;

    private function __construct(){}
    private function __clone(){}

    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
            // @TODO 不用$_GET['url']来rewrite
            self::$instance->url = isset($_GET['anu']) ? trim($_GET['anu'], ' /') : '';
        }
        return self::$instance;
    }

    public function dispatch(AnRule $rule)
    {
        $rule->parse($this->url);
    }
}
?>