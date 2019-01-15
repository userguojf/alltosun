<?php

/**
 * alltosun.com Url类 Url.php
 * ============================================================================
 * 版权所有 (C) 2009-2011 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 高竞竞 (gaojj@alltosun.com) $
 * $Date: 2010-12-31 下午03:25:49 $
*/

class AnUrl implements ArrayAccess
{
    private static $instance;
    private $urls = array();

    private function __construct() {}
    private function __clone() {}

    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function set($key, $value = '')
    {
        $this->urls[$key] = $value;
        return true;
    }

    public function get($key)
    {
        return isset($this->urls[$key]) ? $this->urls[$key] : '';
    }

    public function __set($key, $value = '')
    {
       return $this->set($key, $value);
    }

    public function __get($key)
    {
       return $this->get($key);
    }

    public function offsetExists($offset)
    {
        return isset($this->urls[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        return $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        unset($this->urls[$offset]);
    }
}
?>