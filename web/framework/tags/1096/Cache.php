<?php
/**
 * alltosun.com Cache类
 * ============================================================================
 * 版权所有 (C) 2007-2009 北京共创阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 安然 (anr@alltosun.com) $
 * $Date: 2010-03-11 23:37:03 +0800 $
 * $Id: Cache.php 143 2012-02-02 17:25:16Z gaojj $
 * @link http://wiki.alltosun.com/index.php?title=Framework:Cache.php
*/

/**
 * Cache连接类
 */
class Cache
{
    /**
     * 连接指定类似的cache驱动
     * @example $C = Cache::connect('ea'); // 连接eaccelerator
     * @example $C = Cache::connect(array('ea')); // 连接eaccelerator
     * @example $C = Cache::connect('memcache', 'localhost'); // 连接memcache
     * @example $C = Cache::connect(array('memcache', 'localhost')); // 连接memcache
     * @example $C = Cache::connect('memcache', array('192.168.1.1')); // 连接memcache
     * @example $C = Cache::connect('memcache', array('localhost'), array('192.168.1.1')); // 连接多个memcache
     */
    public static function connect()
    {
        $params = func_get_args();
        if (count($params) == 1) {
            $params = $params[0];
        }

        if (is_array($params)) {
            $driver = array_shift($params);
        } else {
            $driver = $params;
            $params = null;
        }

        require_once dirname(__FILE__).'/Cache/'.$driver.'.php';
        $class = 'Cache' . $driver;
        $mc = new $class($params);

        if (defined('PROJECT_NS')) {
            // 项目的命名空间
            $mc->PS('AnPHP_' . PROJECT_NS);
        } else {
            $mc->PS('AnPHP_');
        }

        return $mc;
    }
}

/**
 * 缓存基类
 * @author anr
 * @link http://wiki.alltosun.com/index.php?title=Framework:class:CacheAbstract
 *
 */
abstract class CacheAbstract
{
    public $ps = 0; // 项目的命名空间，数据库
    public $ps_val = 0; // 值
    public  static  $ps_list;

    public $ns = 0; // 当前key的命名空间，表
    public $ns_val = 0; // 值
    public  static  $ns_list;

    public $key_list = array(); // 生成key的缓存
    public $PROJECT_NS = 'AnPHP_'; // 项目的命名空间

    public $D_BUG = 0; // 是否打开调试信息

    /**
     * 初始化
     */
    function initialization()
    {
        if (defined('PROJECT_NS')) {
            // 项目的命名空间
            $this->PROJECT_NS .= PROJECT_NS;
        }

        if (defined('D_BUG') && D_BUG) {
            $this->D_BUG = 1;
            global $g;
            $g['cache']['PROJECT_NS'][] = $g['cache_sqs'][] = array('method'=>__METHOD__, 'act'=>'set', );
        }
    }

    /**
     * 项目的命名空间
     * 如果不存在自动创建
     * @param unknown_type $key
     * @return object
     */
    public function PS($key)
    {
        if (empty($key)) {
            $this->ps = $this->ps_val = self::$ps_list[0] = 0;
            return $this;
        }
        $this->ps = $key;
        $mc_key = $this->PROJECT_NS . '_PS=' . $key;

        if (isset(self::$ps_list[$mc_key])) {
            $this->ps_val = &self::$ps_list[$mc_key];
            return $this;
        }
        self::$ps_list[$mc_key] = $this->get_key($mc_key);
        $this->ps_val = &self::$ps_list[$mc_key];

        if (false === $this->ps_val) {
            self::$ps_list[$mc_key] = microtime(true);
            $this->ps_val = &self::$ps_list[$mc_key];
            // 过期时间无限
            $this->set_key($mc_key, $this->ps_val, 0);
            if ($this->D_BUG) {
                global $g;
                $g['cache'][$this->ps][] = $g['cache_sqs'][][$this->ps] = 'set:pojectSpace='.$mc_key.'='.$this->ps_val;
            }
        }

        return $this;
    }

    /**
     * 设置当前操作的命名空间
     * 主动设置创建
     * @param $key
     * @param $build 是否创建，默认为0不创建，为1创建（当只是判断是否存在的时候没必要创建）
     * @return object
     */
    public function NS($key, $build = 0)
    {
        if (empty($key)) {
            $this->ns = $this->ns_val = self::$ns_list[0] = 0;
            return $this;
        }
        $this->ns = $key;
        // $mc_key = $this->PROJECT_NS . '_NS=' . $key;
        $mc_key = "{$this->PROJECT_NS}_NS_{$this->ps_val}-{$this->ns}";

        if (0 === $build && isset(self::$ns_list[$mc_key]) && self::$ns_list[$mc_key]) {
            // 只在取时
            $this->ns_val = &self::$ns_list[$mc_key];
            return $this;
        }
        self::$ns_list[$mc_key] = $this->get_key($mc_key);
        $this->ns_val = &self::$ns_list[$mc_key];

        if (1 === $build && false === $this->ns_val) {
            // 创建过期时间无限
            $this->ns_val = microtime(true);
            $this->set_key($mc_key, $this->ns_val, 0);
            if ($this->D_BUG) {
                global $g;
                $g['cache'][$this->ns][] = $g['cache_sqs'][] = 'set->nameSpace='.$mc_key.'='.$this->ns_val;
            }
        } elseif ($this->ns_val) {
            // 从缓存中读取
            if ($this->D_BUG) {
                global $g;
                $g['cache'][$this->ns][] = $g['cache_sqs'][] = 'get:HIT:nameSpace='.$mc_key.'='.$this->ns_val;
            }
        } else {
            // 示命中
            if ($this->D_BUG) {
                global $g;
                $g['cache'][$this->ns][] = $g['cache_sqs'][] = 'get:MISS:nameSpace='.$mc_key.'='.$this->ns_val;
            }
        }

        return $this;
    }

    /**
     * 删除命名空间
     * @param $key 为NULL取当前，为空直接返回
     * @example $this->deleteNS('user_list_ns')
     */
    public function deleteNS($key = NULL)
    {
        if (NULL === $key) $key = $this->ns;
        if (empty($key)) {
            $this->ns = $this->ns_val = self::$ns_list[0] = 0;
            return true;
        }

        // $mc_key = $this->PROJECT_NS . '_NS=' . $key;
        $mc_key = "{$this->PROJECT_NS}_NS_{$this->ps_val}-{$this->ns}";
        $this->ns = $this->ns_val = 0;
        $this->key_list = array();

        if ($this->D_BUG) {
            global $g;
            $g['cache'][$key][] = $g['cache_sqs'][] = 'deleteNS='.$mc_key;
        }

        return $this->delete_key($mc_key);
    }


    /**
     * 删除命名空间
     * 可删除projectNamespace
     * @param $key 为NULL取当前，为空直接返回
     * @example $this->deletePS('localhost:alltosun_db');
     */
    public function deletePS($key = NULL)
    {
        if (NULL === $key) $key = $this->ps;
        if (empty($key)) {
            $this->ps = $this->ps_val = self::$ps_list[0] = 0;
            return true;
        }

        $mc_key = $this->PROJECT_NS . '_PS=' . $key;
        $this->ps = $this->ps_val = 0;
        $this->key_list = array();

        if ($this->D_BUG) {
            global $g;
            $g['cache'][$key][] = $g['cache_sqs'][] = 'deletePS='.$mc_key;
        }

        return $this->delete_key($mc_key);
    }

    /**
     * 读取key的值，支持multiget
     * 若key不存在直接返回false
     * @param  string|array $key 缓存的key，支持multiget
     * @return mixed 直接返回执行结果，多取时，会返回key=>value型数组（以输入排序）
     * @example $this->PS('localhost:alltosun_db')->NS('user')->get(1); // 直接返回执行结果，
     * @example $this->PS('localhost:alltosun_db')->NS('user')->get(array(1,2,3)); // 直接返回执行结果，array(1=>array(……), 2=>array(……), 3=>array(……),)
     */
    public function get($key)
    {
        if (empty($key)) throw new Exception("Empty key");

        // 判断，如果命名空间值不存在直接返回
        if ($this->ns && empty($this->ns_val)) {
            return false;
        }

        if (is_array($key)) {
            // multiget
            $mc_key = $key_map = array();
            foreach ($key as $v) {
                $build_key = "{$this->PROJECT_NS}_VALUE={$this->ps_val}-{$this->ns_val}".$this->buildKey($v);
                $key_map[$build_key] = $v;
                $mc_key[] = $build_key;
            }
        } else {
            $mc_key = "{$this->PROJECT_NS}_VALUE={$this->ps_val}-{$this->ns_val}".$this->buildKey($key);
        }

        $mc_value = $this->get_key($mc_key);
        if (is_array($key)) {
            // multiget
            $new_mc_value = array();
            foreach ($mc_value as $k=>$v) {
                $new_mc_value[$key_map[$k]] = $v;
            }
            $mc_value = $new_mc_value;
        }

        if ($this->D_BUG) {
            global $g;
            if (is_array($key)) {
                // multiget
                $key_str = implode(',', $key);
                $mc_key_str = implode(',', $mc_key);
                $len = 0;
                $data = '';
                foreach ($mc_value as $v) {
                    $len += strlen(serialize($v));
                    $data .= ' '.substr(serialize($v), 0, 120);
                }
                $g['cache'][$this->ns.':'.$key_str][] = $g['cache_sqs'][] = "MultiGet:ns:{$this->ns}={$mc_key_str} Len:".$len." MCKey:{$mc_key_str} DATA:".$data;
            } else {
                $g['cache'][$this->ns.':'.$key][] = $g['cache_sqs'][] = "Get:ns:{$this->ns}={$mc_key} Len:".strlen(serialize($mc_value))." MCKey:{$mc_key} DATA:".substr(serialize($mc_value), 0, 120);
            }
        }

        return $mc_value;
    }

    /**
     * 缓存值
     * @param $key    只能为数字、字符
     * @param $value  任意
     * @param $expire 过期时间，默认600秒，如果大于600将会添加随机时间
     * @param $fixtime 是否为固定时间，默认为0添加随机时间
     */
    public function set($key, $value, $expire=600, $fixtime=0)
    {
        if (empty($key)) throw new Exception("Empty key");

        // 判断，如果命名空间值不存在，创建之
        if ($this->ns && empty($this->ns_val)) {
            $this->NS($this->ns, 1);
        }

        $mc_key = "{$this->PROJECT_NS}_VALUE={$this->ps_val}-{$this->ns_val}".$this->buildKey($key);
        if (empty($fixtime) && $expire >= 600) $expire += rand(0, 60);

        if ($this->D_BUG) {
            global $g;
            $g['cache'][$this->ns.':'.$key][] = $g['cache_sqs'][] = 'Set:MCKey:'.$mc_key.' LifeTime:'.$expire.' Data:'.substr(serialize($value), 0, 80);
        }

        if (!$this->set_key($mc_key, $value, $expire)) {
            echo "<pre>";
            throw new Exception("Error MEMCACHE set:" . $mc_key);
            die;
        }
        return true;
    }

    /**
     * 删除缓存
     * @param $key
     * @param $timeout
     */
    public function delete($key = null, $timeout = 0)
    {
        if (empty($key)) throw new Exception("Empty key");

        // 判断，如果命名空间值不存在，创建之
        if ($this->ns && empty($this->ns_val)) {
            $this->NS($this->ns, 1);
        }
        $mc_key = "{$this->PROJECT_NS}_VALUE={$this->ps_val}-{$this->ns_val}".$this->buildKey($key);

        if ($this->D_BUG) {
            global $g;
            $g['cache'][$this->ns.':'.$key][] = $g['cache_sqs'][] = 'Delete:MCKey:'.$mc_key.' LifeTime:'.$timeout;
        }

        return $this->delete_key($mc_key, $timeout);
    }

    /**
     * 构造缓存的key
     * 如果生成的key超长(50),会自动打散、截取
     * @param string $key key的名称
     * @param int $build  如命名空间不存在，是否生成。默认为0不生成
     */
    public function buildKey($key = null, $build = 0)
    {
        global $g;

        if (is_array($key) || is_object($key)) {
             /*&& $this->D_BUG*/
            echo '<pre>';
            throw new Exception("Error Key:" . var_dump($key));
        }

        // if (NULL === $this->ps || NULL === $this->ps_val) $this->PS($this->ps);
        // if (NULL === $this->ns || NULL === $this->ns_val) $this->NS($this->ns);

        if (isset($this->key_list[$key])) {
            // 缓存key是否存在
            return $this->key_list[$key];
        }

        // $mc_key = "{$this->ps}={$this->ps_val}_{$this->ns}={$this->ns_val}_{$key}";
        $mc_key = $key;
        if (strlen($mc_key) > 50) {
            // 处理过长的key
            $mc_key = substr($mc_key, 0, 50) . md5($mc_key) . strlen($mc_key);
        }
        $this->key_list[$key] = $mc_key;

        return $mc_key;
    }

    // 超级缓区


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
    public function call_user_func_array($fun, $arg, $key=null, $expire=300, $fixtime=0)
    {
        static $lamp = array(); // 回调变量
        $result = array();

        // 判断，如果命名空间值不存在，创建之
        if ($this->ns && empty($this->ns_val)) {
            $this->NS($this->ns, 1);
        }

        if (empty($key)) {
            if (is_array($fun) && isset($fun[0])) {
                $key = serialize(get_class($fun[0]).'->'.$fun[1]).':'.serialize($arg);
            } else {
                $key = serialize($fun).':'.serialize($arg);
            }
        }

        $key = $this->buildKey($key);
        $mc_key = "{$this->PROJECT_NS}_VALUE={$this->ps_val}-{$this->ns_val}".$key;

        // 灯亮返回，正在请求
        if (isset($lamp[$mc_key]) && $lamp[$mc_key] == 1) {return ;}  // 自身回调中，返回

        // 如果有缓存直接返回
        // $result = $this->get_key($mc_key);
        $result = $this->get($key);
        if ($result !== FALSE) {
            return $result;
        }

        // 如果锁定，等待5次，返回空
        if ($this->isLock($mc_key)) {
            $i = 1;
            // $result = $this->get_key($mc_key);
            $result = $this->get($key);
            while ($result === FALSE && ++$i < 5) {
                // 0.1 second
                usleep(100000);
            }
        } else {
            // 请求函数
            $this->lock($mc_key);

            // 加灯，进行请求
            $lamp[$mc_key] = 1;
            !is_array($arg) && $arg = array($arg); //兼容php5.3
            $result = call_user_func_array($fun, $arg);
            if ($result !== FALSE) {
                // $this->set_key($mc_key, $result, $expire);
                $this->set($key, $result, $expire, $fixtime);
            }

            $this->unlock($mc_key);
        }

        $lamp[$mc_key] = 0; // 关灯，请求结束
        return $result;
    }

    /**
     * 缓存执行的语句
     * 缓存返回结果与输出
     * @param $eval_str 执行的PHP语句
     * @param $ns       命名空间
     * @param $key      缓存的key
     * @param $expire   生存期
     */
    public function doEval($eval_str, $key = null, $expire = 600)
    {
        if (empty($key)) $key = $eval_str;
        $key = $this->buildKey($key);
        $mc_key = "{$this->PROJECT_NS}_VALUE={$this->ps_val}-{$this->ns_val}".$key;

        // 判断，如果命名空间值不存在，创建之
        if ($this->ns && empty($this->ns_val)) {
            $this->NS($this->ns, 1);
        }

        // 如果有缓存直接返回，不为false，不为null
        $result = $this->get($key);
        if (is_array($result)) {
            echo $result[1];
            return $result[0];
        }

        ob_start();
        ob_flush();
        $tem = eval($eval_str . ';');
        $tem_echo = ob_get_clean();

        $this->set($key, array($tem, $tem_echo), $expire);
        return $tem;
    }

    private function isLock($key)
    {
        $key = "lock:{$key}";

        if (!$this->get_key($key)) {
            return false;
        }

        if ($this->D_BUG) {
            global $g;
            $g['cache'][$this->ns.':'.$key][] = $g['cache_sqs'][] = 'islock:true='.$key;
        }
        return true;
    }

    private function lock($key)
    {
        $key = "lock:{$key}";

        if ($this->D_BUG) {
            global $g;
            $g['cache'][$this->ns.':'.$key][] = $g['cache_sqs'][] = 'lock='.$key;
        }

        return $this->set_key($key, '1', 0, 5);
    }

    private function unlock($key)
    {
        $key = "lock:{$key}";
        if ($this->D_BUG) {
            global $g;
            $g['cache'][$this->ns.':'.$key][] = $g['cache_sqs'][] = 'unlock='.$key;
        }

        return $this->delete_key($key, 0);
    }
}


interface CacheWrapper
{
    public function set_key($key, $value, $expire);
    public function get_key($key);
    public function delete_key($key, $timeout=0);
    public function flush();
}

?>