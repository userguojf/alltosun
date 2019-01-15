<?php
class RedisCache {

    public $redis = null;

    public function __construct($redis)
    {
        $this->redis = $redis;
        return $redis;
    }

    /**
     * 链接
     * @return RedisCache
     */
    public static function content()
    {
        $redis = new Redis();         //创建Redis对象
        $redis->connect(REDIS_HOST);  //连接服务
        $redis->auth(REDIS_PASS);     //验证

        return new self($redis);
    }

    /**
     * 设置缓存
     * @param str $key
     * @param str $value
     * @param int $time
     * @return true|false
     */
    public function set($key,$value,$time = '')
    {
        $value   = json_encode($value);
        $ret_res = $this->redis->set($key, $value);

        if ($time > 0) {
            $this->redis->setTimeout($key, $time);
        }

        return $ret_res;
    }

    /**
     * 获取缓存
     * @param str $key
     * @return mixed
     */
    public function get($key)
    {
        return json_decode($this->redis->get($key),true);
    }

    public function delete($key)
    {
        return $this->redis->delete($key);
    }

 /**
  * 清除命名空间的缓存
  */
    public function delete_ns($key)
    {
        $imageSet = $this->keys($key.'*');

        foreach ( $imageSet as $value) {
            $this->delete($value);
        }

        return true;
    }
     /**
      * 清空数据
      */
     public function flushAll() {
         return $this->redis->flushAll();
     }

    public function keys($key) {
        return $this->redis->keys($key);
    }

    /**
     * 检测redis服务状态
     * @todo debug 完善后编写
     */
    public function ping()
    {
        return $this->redis->ping();
    }

     /**
      * 数据入队列
      * @param string $key KEY名称
      * @param string|array $value 获取得到的数据
      * @param bool $right 是否从右边开始入
      */
    public function push($key, $value ,$right = true) {
        $value = json_encode($value);
        return $right ? $this->redis->rPush($key, $value) : $this->redis->lPush($key, $value);
    }

    /**
     * 数据出列
     * @param string $key KEY名称
     * @param string|array $value 获取得到的数据
     * @param bool $right 是否从右边开始入
     */
    public function pop($key , $left = true) {
        $val = $left ? $this->redis->lPop($key) : $this->redis->rPop($key);
        return json_decode($val);
    }

    public function lSize($key)
    {
        return $this->redis->lSize($key);
    }

    public function increment($key) {
        return $this->redis->incr($key);
    }

    public function decrement($key) {
        return $this->redis->decr($key);
    }

    public function exists($key) {
        return $this->redis->exists($key);
    }

    public function redis() {
        return $this->redis;
    }

    /**
     * 生成命名空间前缀
     * @param unknown_type $key
     * @return Ambigous <string>
     */
    public function buildKey($key)
    {
        static $keys = array();

        if (!isset($keys[$key])) {
            $keys[$key] = $this->MC_NS.'-'.(strlen($key)<41 ? $key : substr($key, 0, 40).md5($key).strlen($key));
        }

        return $keys[$key];
    }
}