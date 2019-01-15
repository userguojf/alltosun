<?php

/**
 * eaccelerator 驱动类
 * ============================================================================
 * 版权所有 (C) 2007-2009 北京共创阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 安然 (anr@alltosun.com) $
 * $Date: 2009-05-04 12:21:13 +0800 $
 * $Id: ea.php 143 2012-02-02 17:25:16Z gaojj $
*/

class CacheEA extends CacheAbstract implements CacheWrapper
{
    public function __construct()
    {
        $this->init();
    }

    /**
     * 存储
     * @param $key
     * @param $value
     * @param $expire
     */
    public function set_key($key, $value, $expire)
    {
        return eaccelerator_put($key, $value, $expire);
    }

    /**
     * 读取
     * @param $key  缓存的key
     * @param $mem  是否缓存在内存中
     */
    public function get_key($key)
    {
        return eaccelerator_get($key);
    }

    /**
     * 删除
     * @param $key
     * @param $timeout
     */
    public function delete_key($key, $timeout)
    {
        return eaccelerator_rm($key);
    }

    /**
     * 清空所有缓存
     */
    function flush()
    {
        eaccelerator_clear();
        eaccelerator_clean();
    }
}
?>