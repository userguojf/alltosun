<?php

/**
 * alltosun.com cache类，带 namespace MemcacheWrapper.php
 * ============================================================================
 * 版权所有 (C) 2007-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 安然 (anr@alltosun.com) $
 * $Date: 2009-05-04 12:21:13 +0800 $
 * $Id: MemcacheWrapper.php 1084 2015-12-01 20:07:10Z anr $
 * @link http://wiki.alltosun.com/index.php?title=Framework:MemcacheWrapper.php
*/

// ini_set('memcache.compress_threshold', 2048);
// ini_set('memcache.hash_strategy','standard');
// ini_set('memcache.hash_function','crc32');

/**
 *
 * @author anr@alltosun.com
 * @link http://wiki.alltosun.com/index.php?title=Framework:class:MemcacheWrapper
 */
class MemcacheWrapper
{
    public  $mc = null;

    private $ns = ''; // 当前的命名空间名，可以设定多个
    // private $ns_val = ''; // 值

    private $ps = ''; // 项目命名空间名，设定之后不可更改
    // private $ps_val = NULL; // 值，初始为 NULL

    public static $ns_list = array(); // NS 缓冲列表,PS 缓冲列表
    public static $count = 0; // 计数器

    public $debug = 0;
    public $noLock = 0;

    /**
     * 连接memcache
     * @return mc Obj
     */
    public static function connect()
    {
        $params = func_get_args();

        if (is_object($params[0])) {
            // 如果直接传递的是对象的话，则直接将该对象作为连接后的mc对象进行封装
            return new self($params[0]);
        } elseif (!is_array($params[0])) {
            if (count($params) == 1) {
                // connect('mc')
                // 从Config中读取
                $conf = Config::get($params[0]);
                if (is_array($conf[0])) $p_arr = $conf;
                else $p_arr[] = $conf;
            } else {
                // connect('localhost',……)
                $p_arr[] = $params;
            }
        } elseif (is_array($params[0][0])) {
            // 把配置中的多数组直接传递过来
            // connect(array(array(), array()))
            $p_arr = $params[0];
        } else {
            // 如果是多个server
            // connect(array(), array())
            // connect(array())
            // array(array('adasa:saada', 'dasda:dsaad'))
            // array(array('1','2'), array('host','port'))
            $p_arr = $params;
        }

        $mc = new Memcache();

        foreach ($p_arr as $v) {
            if (strpos($v[0], ':') !== false) {
                // 单个连接配置支持array('host1:port1', 'host2:port2')的写法
                foreach ($v as $v2) {
                    $v2_arr = explode(':', $v2);
                    call_user_func_array(array($mc, 'addServer'), $v2_arr);
                }
            } else {
                // 默认是array('host1', 'port1')
                call_user_func_array(array($mc, 'addServer'), $v);
            }
        }

        return new self($mc);
    }

    function __construct($mc)
    {
        $this->mc = $mc;
        if (defined('D_BUG')) $this->debug = D_BUG;
    }

    /**
     * 对象中未定义方法，直接使用类中的方法
     * 自动对 key 进行处理
     */
    public function __call($name, $params = NULL)
    {
        // 继承 mc 类
        $name = strtolower($name);
        if (!method_exists($this->mc, $name)) {
            throw new AnException('Mc Error!', "MemcacheWrapper::__CAll() Error!Undefined Function:{$name}.");
        }

        if (FALSE === $this->ns_val) {
            $this->NS($this->ns, 1);
        }

        if ($params) {
            // $params[0] 为 key
            $key = $params[0];
            $mc_key = $this->mcKey($key);
            $params[0] = $mc_key;
        }

        if ($this->debug) {
            AnPHP::lastRunTime();
            $r = call_user_func_array(array($this->mc, $name), $params);
            $this->addDebugInfo("__call('{$name}')", '', $key, $mc_key, $params[0]);
            return $r;
        } else {
            return call_user_func_array(array($this->mc, $name), $params);
        }
    }

    public function __get($name)
    {
        if ('ns_val' === $name) {
            $this->NS($this->ns);
            return $this->ns_val;
        } elseif ('ps_val' === $name) {
            $this->PS();
            return $this->ps_val;
        } else {
            throw new AnException('Mc Error!', "MemcacheWrapper::__get() Error!Undefined Propertie:{$name}.");
        }
    }

    /**
     * 添加调试信息，会自动记录执行时间
     * @param string $fun 执行的函数
     * @param string $op 执行的操作
     * @param string $key 操作的key
     * @param string $mc_key 对应的缓存中的key
     * @param mixed $date 操作的数据
     * @return boolean
     */
    public function addDebugInfo($fun, $op, $key, $mc_key, $date = '')
    {
        AnDebug::$op[] = array('type' => 'mc', 'info' =>
            array(
                  'fun' => $fun, //
                  'op'  => $op, // create,update,read,delete
                  'ps' => $this->ps,
                  'ps_val' => empty($this->ps_val) ? '' : $this->ps_val,
                  'ns' => $this->ns,
                  'ns_val' => $this->ns_val,
                  'key' => $key, //
                  'mc_key'=> $mc_key,
                  'data' => $date, //
                  'time' => AnPHP::lastRunTime() * 1000, //
                 )
             );

        return true;
    }

    /**
     * 设置类的命名空间
     * @param $key
     */
    public function PS($key = NULL)
    {
        if ('' === $key) {
            $this->ps = $this->ps_val = '';
            return $this;
        }

        if (NULL === $key) {
            if (defined('PROJECT_NS')) {
                // 项目的命名空间
                // 不提倡使用 2012-08-07
                $key = 'AnPHP_' . PROJECT_NS;
            } elseif (Config::get('PROJECT_NS')) {
                // 项目的命名空间
                $key = 'AnPHP_' . Config::get('PROJECT_NS');
            } elseif (Config::get('db')) {
                $key = 'AnPHP_' . md5(serialize(Config::get('db')));
            } else {
                $key = 'AnPHP_PROJECT_NS';
            }
        }

        return $this->NS($key, 2);
    }

    /**
     * 设置当前操作的命名空间
     * @param $key
     * @param $build 是否创建，默认为0不创建，为1创建（当只是判断是否存在的时候没必要创建）,为2是PS必须要创建
     */
    public function NS($key, $build = 0)
    {
        if ('' === $key) {
            $this->ns = $this->ns_val = '';
            return $this;
        }

        $mc_key = $key;
        if (!isset(self::$ns_list[$mc_key])) {
            // 读取NS
            if ($this->debug) {
                AnPHP::lastRunTime();
                self::$ns_list[$mc_key] = $this->mc->get($mc_key);
                $this->addDebugInfo(__METHOD__, 'read -> ' . (self::$ns_list[$mc_key] ? '成功' : '失败'), $key, '', self::$ns_list[$mc_key]);
            } else {
                self::$ns_list[$mc_key] = $this->mc->get($mc_key);
            }
        }

        if ($build && (!isset(self::$ns_list[$mc_key]) || false === self::$ns_list[$mc_key])) {
            // 生成NS
            self::$ns_list[$mc_key] = microtime(true) . self::$count;
            self::$count++;
            // 过期时间无限
            if ($this->debug) {
                AnPHP::lastRunTime();
                $r = $this->mc->add($mc_key, self::$ns_list[$mc_key], 0, 0);
                $this->addDebugInfo(__METHOD__, 'create', $key, '', self::$ns_list[$mc_key]);
            } else {
                $r = $this->mc->add($mc_key, self::$ns_list[$mc_key], 0, 0);
            }

            if (!$r) {
                // 高并发时，如果其它进程刚写完key情况下，重新取，李维建议
                $r = $this->mc->get($mc_key);
                if ($r) {
                    self::$ns_list[$mc_key] = $r;
                } else {
                    $this->mc->set($mc_key, self::$ns_list[$mc_key], 0, 0);
                }
            }
        }

        if (2 === $build) {
            // 如果是ps处理
            $this->ps = $key;
            $this->ps_val = self::$ns_list[$mc_key];
        } else {
            $this->ns = $key;
            $this->ns_val = self::$ns_list[$mc_key];
        }

        return $this;
    }

    /**
     * 删除命名空间
     * 可删除projectNamespace
     * @param $key 为空删除自身的ns
     * @example deleteNamespace('user_list_ns')
     */
    public function deleteNS($key = NULL)
    {
        if ($key) {
            $mc_key = $key;
        } else {
            $mc_key = $this->ns;
        }

        if ($this->debug) {
            AnPHP::lastRunTime();
            $r = $this->mc->delete($mc_key);
            $this->addDebugInfo(__METHOD__, 'delete -> ' . ($r ? '成功':'失败'), $key, '');
        } else {
            $this->mc->delete($mc_key);
        }

        unset(self::$ns_list[$mc_key]);
        if (!$key || $key === $this->ns) {
            // 删除自身的NS
            // $this->ns = '';
            // unset($this->ns_val);
            // 2015-01-04 anr,删除时不再清空key，同时将 $this->ns_val = FALSE; 清空key使用$this->NS('')
            $this->ns_val = FALSE;
        }

        return true;
    }

    /**
     * 删除缓存
     * @param $key
     * @param $timeout
     */
    public function delete($key = NULL, $timeout = 0)
    {
        if (FALSE === $this->ns_val) {
            return true;
        }

        $mc_key = $this->mcKey($key);
        if ($this->debug) {
            AnPHP::lastRunTime();
            $r = $this->mc->delete($mc_key, $timeout);
            $this->addDebugInfo(__METHOD__, 'delete -> ' . ($r ? '成功':'失败'), $key, $mc_key);
            return $r;
        } else {
            return $this->mc->delete($mc_key, $timeout);
        }
    }

    /**
     * 读取key的值
     * @param string|array $key 缓存的key，支持multiget
     */
    public function get($key)
    {
        if (FALSE === $this->ns_val) {
            // 如果命令空间不存在（因未命中缓存返回FALSE），直接返回 FALSE，提高读取速度
            return FALSE;
        }

        if (is_array($key)) {
            // multiget
            $mc_key = $key_map = array();
            foreach ($key as $v) {
                $build_key = $this->mcKey($v);
                $key_map[$build_key] = $v;
                $mc_key[] = $build_key;
            }
        } else {
            $mc_key = $this->mcKey($key);
        }

        if ($this->debug) {
            AnPHP::lastRunTime();
            $mc_value = $this->mc->get($mc_key);
            $this->addDebugInfo(__METHOD__, 'read -> ' . ($mc_value ? '成功' : '失败'), $key, $mc_key, $mc_value);
        } else {
            $mc_value = $this->mc->get($mc_key);
        }

        if (is_array($key)) {
            // multiget, key转换，将返回的mc_key转换成key
            $new_mc_value = array();
            foreach ($mc_value as $k => $v) {
                $new_mc_value[$key_map[$k]] = $v;
            }
            $mc_value = $new_mc_value;
        }

        return $mc_value;
    }

    /**
     * 设置
     * @param $key
     * @param $value
     * @param $expire
     * @param $fixtime 是否为固定时间，默认为0取随机时间
     */
    public function set($key, $value, $expire = 1800, $fixtime = 0)
    {
        if (FALSE === $key) {
            throw new AnException('Mc Error!', 'MemcacheWrapper::set() Error!Key can not be FALSE!');
        }
        if (FALSE === $value) {
            throw new AnException('Mc Error!', 'MemcacheWrapper::set() Error!Value can not be FALSE!');
        }

        if (FALSE === $this->ns_val) {
            $this->NS($this->ns, 1);
        }

        $mc_key = $this->mcKey($key);
        if (!$fixtime) $expire += rand(0, 60);

        if ($this->debug) {
            AnPHP::lastRunTime();
            if (!$this->mc->set($mc_key, $value, MEMCACHE_COMPRESSED, $expire)) {
                throw new AnException('Mc Error!', 'MemcacheWrapper::set() Error!');
            }
            $this->addDebugInfo(__METHOD__, 'create', $key, $mc_key, $value);
        } else {
            if (!$this->mc->set($mc_key, $value, MEMCACHE_COMPRESSED, $expire)) {
                throw new AnException('Mc Error!', 'MemcacheWrapper::set() Error!');
            }
        }

        return true;
    }

    /**
     * 缓存 call_user_func_array() 的结果
     * 支持命名空间、生命期、指定缓存键
     * @example $this->mc_wr->call_user_func_array(array($this, __METHOD__), $params); // 在类内部使用
     * @example $this->mc_wr->call_user_func_array(array($obj, get_list), $params); // 直接给类添加缓存
     * @example $this->mc_wr->call_user_func_array(__FUNCTION__, $params); // 在函数内部使用
     * @example $this->mc_wr->call_user_func_array('ip_to_location', $ip); // 直接给函数添加缓存
     * @param mixed $fun call_user_func_array()第1个参数
     * @param mixed $arg call_user_func_array()第2个参数
     * @param string $key 缓存键
     * @param int $expire 生命期
     * @param int $fixtime 是否为固定时间，默认为0取随机时间
     */
    public function call_user_func_array($fun, $arg = array(), $key = null, $expire = 1800, $fixtime = 0)
    {
        static $lamp = array(); // 回调变量
        $result = array();

        if (FALSE === $this->ns_val) {
            $this->NS($this->ns, 1);
        }

        if (!$key) {
            if (is_array($fun) && isset($fun[0])) {
                // $key = serialize(get_class($fun[0]).'->'.$fun[1]).':'.serialize($arg);
                $key = get_class($fun[0]).'->'.$fun[1].'('.str_replace(array("\n", ' '), '', var_export($arg, true)) .')';
            } else {
                // $key = serialize($fun).'('.serialize($arg);
                $key = $fun.'('.str_replace(array("\n", ' '), '', var_export($arg, true)) .')';
            }
        }

        if ($this->noLock) {
            // 如果不使用锁
            if (($result = $this->get($key)) !== FALSE ) {
                return $result;
            }
            if (($result = call_user_func_array($fun, $arg)) !== FALSE) {
                $this->set($key, $result, $expire, $fixtime);
            } else {
                throw new AnException('Mc Error!', 'MemcacheWrapper::call_user_func_array() Error!Result can not be FALSE!');
            }
            return $result;
        }

        // 灯亮返回，正在请求
        if (isset($lamp[$key]) && $lamp[$key] === 1) {return ;}  // 自身回调中，返回

        // 如果有缓存直接返回
        if (($result = $this->get($key)) !== FALSE) {
            return $result;
        }

        // 如果锁定，等待5次，返回空
        if (!$this->Lock($key)) {
            $i = 1;
            while (FALSE === ($result = $this->get($key)) && ++$i < 5) {
                // 0.1 second
                usleep(100000);
            }
            if ($result === FALSE) {
                return new AnEmptyVariable();
            }
        } else {
            // 请求函数
            // 加灯，进行请求
            $lamp[$key] = 1;
            !is_array($arg) && $arg = array($arg); //兼容php5.3
            if (($result = call_user_func_array($fun, $arg)) !== FALSE) {
                $this->set($key, $result, $expire, $fixtime);
            } else {
                throw new AnException('Mc Error!', 'MemcacheWrapper::call_user_func_array() Error!Result can not be FALSE!');
            }

            $lamp[$key] = 0; // 关灯，请求结束
            $this->unlock($key);
        }

        return $result;
    }

    public function increment($key, $offset = 1, $expire = 86400)
    {
        if (!$offset) return false;

        if (FALSE === $this->ns_val) {
            $this->NS($this->ns, 1);
        }

        $mc_key = $this->mcKey($key);

        if ($this->debug) {
            AnPHP::lastRunTime();
            $return = $this->mc->increment($mc_key, $offset);
            if (false === $return) {
                $this->set($key, $offset, $expire);
                $return = $offset;
            }
            $this->addDebugInfo(__METHOD__, 'update', $key, $mc_key, $offset);
        } else {
            $return = $this->mc->increment($mc_key, $offset);
            if (false === $return) {
                $this->set($key, $offset, $expire);
                $return = $offset;
            }
        }

        return $return;
    }

    public function lock($key)
    {
        $mc_key = 'lock_' . $this->mcKey($key);
        if ($this->debug) {
            AnPHP::lastRunTime();
            $result = $this->mc->add($mc_key, 1, 0, 5);
            $this->addDebugInfo(__METHOD__, 'create', $key, '', $result);
        } else {
            // 锁定 5 秒
            return $this->mc->add($mc_key, 1, 0, 5);
        }

        return $result;
    }

    public function unlock($key)
    {
        $mc_key = 'lock_' . $this->mcKey($key);
        if ($this->debug) {
            AnPHP::lastRunTime();
            $result = $this->mc->delete($mc_key);
            $this->addDebugInfo(__METHOD__, 'delete', $key, $mc_key, $result);
        } else {
            return $this->mc->delete($mc_key);
        }

        return $result;
    }

    /**
     * 生成key
     * 添加前缀
     * @param $key
     * @return string
     */
    public function buildKey($key)
    {
        static $keys = array();

        if (!empty($keys[$key])) {
            return $keys[$key];
        }
        if (!$key || is_array($key) || is_object($key)) {
            throw new AnException('Mc Error!', 'MemcacheWrapper::buildKey() Error!');
        }
        return $keys[$key] = (strlen($key)<74 ? $key : substr($key, 0, 40).md5($key).'_'.strlen($key));
    }

    /**
     * 返回带命名空间的 key
     * @param string $key
     * @return string
     */
    public function mcKey($key)
    {
        return $this->ps_val . '-' . $this->ns_val .'-' . $this->buildKey($key);
    }
}


/**
 * 缓存的类接口
 * @author anr
 *
 */
interface AnCache
{
    public function get(); // 如果没有命中必须要返回 bool型的 FALSE
    public function set(); // 值不支持 bool型的 FALSE
    public function delete();
}

/**
 * 空变量对象，可作为数组循环，也可作为字符串处理
 * @author gaojj@alltosun.com
 */
class AnEmptyVariable implements Iterator, ArrayAccess
{
    // Iterator
    public function current(){}
    public function key(){}
    public function next(){}
    public function rewind(){}
    public function valid() { return false; }

    // ArrayAccess
    public function offsetSet($offset, $value) { return false; }
    public function offsetExists($offset) { return false; }
    public function offsetUnset($offset) { return false; }
    public function offsetGet($offset) { return false; }

    public function __toString() { return ''; }
}
?>