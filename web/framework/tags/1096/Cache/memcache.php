<?php

/**
 * memcache 封装类
 * ============================================================================
 * 版权所有 (C) 2007-2009 北京共创阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 安然 (anr@alltosun.com) $
 * $Date: 2009-05-04 12:21:13 +0800 $
 * $Id: memcache.php 143 2012-02-02 17:25:16Z gaojj $
*/

class CacheMemcache extends CacheAbstract implements CacheWrapper
{
    private $mc = null;

    public function __construct()
    {
        // array(1) { [0]=>  array(1) { [0]=>  array(2) { [0]=>  string(9) "127.0.0.1" [1]=>  int(12121) } } }
    	$params = func_get_args();
        $params = $params[0];

        $this->mc = new Memcache();
        foreach ($params as $v) {
            call_user_func_array(array($this->mc, 'addServer'), $v);
        }

        // 必须要执行
        $this->initialization();
    }

    /**
     * 删除
     * @param $key
     * @param $timeout
     */
    public function delete_key($key, $timeout=0)
    {
        return $this->mc->delete($key, $timeout);
    }

    /**
     * 读取
     * @param $key  缓存的key
     * @param $mem  是否缓存在内存中
     */
    public function get_key($key)
    {
        return $this->mc->get($key);
    }

    /**
     * 存储
     * @param $key
     * @param $value
     * @param $expire
     */
    public function set_key($key, $value, $expire)
    {
        return $this->mc->set($key, $value, MEMCACHE_COMPRESSED, $expire);
    }

    public function flush()
    {
        return $this->mc->flush();
    }
}
?>