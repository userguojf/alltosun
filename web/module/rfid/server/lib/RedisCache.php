<?php

class RedisCache
{

    private $redis = null;

    /**
     * RedisCache constructor.
     * @param $redis Redis
     * @return Redis
     */
    public function __construct($redis)
    {
        $this->redis = $redis;
        return $redis;
    }

    /**
     * 代理方法，写入失败解决方式
     * @param unknown $action
     * @param array $params
     */
    public function __call($action, $params = array())
    {
        //是否存在
        if (!is_callable(array(__CLASS__, $action))) {
            return false;
        }

        $result = false;

        try {
            //执行
            $result = call_user_func_array(array(__CLASS__, $action), $params);
        } catch (Exception $e) {
            AutoLoad::instance('error_log')->write_error_log(array('errno' => 1001, 'msg' => $e->getMessage(), 'error_data' => 'Redis error'));
            exit;
        }

        if ($result === false && $this->ping() != '+PONG') {
echo '__call 重连..';
            //重新连接
            $this->reconnect();
            //重新执行
            return call_user_func_array(array(__CLASS__, $action), $params);
        }

        return $result;
    }

    /**
     * 代理方法， 未定义的属性
     * @param unknown $property
     */
    public function __get($property)
    {
        //是否存在属性
        if (!property_exists('RedisCache', $property)) {
            return false;
        }

        if ($property == 'redis') {
            //对象已经不存在或者已失效
            if ($this->redis->ping() != '+PONG') {
echo '__get 重连..';
                //清空原有的
                $this->redis = null;
                $redis =  self::connect_redis();
                $this->redis = $redis;
            }
        }

        return $this->$property;
    }

    /**
     * 函数名写错了。
     * @return RedisCache
     */
    public static function content()
    {
        $redis =  self::connect_redis();
        return new self($redis);
    }

    /**
     * @return Redis
     */
    public static function connect_redis()
    {

        try {
            $redis = new Redis();         //创建Redis对象
            if (defined('REDIS_PORT')) {
                $redis->connect(REDIS_HOST, REDIS_PORT);  //连接服务
            } else {
                $redis->connect(REDIS_HOST);  //连接服务
            }
        } catch (Exception $e) {
            AutoLoad::instance('error_log')->write_error_log(array('errno' => 1001, 'msg' => $e->getMessage(), 'error_data' => 'Redis error'));
            exit();
        }

        if (REDIS_PASS) {
            $redis->auth(REDIS_PASS);     //验证
        }

        if (defined('REDIS_DBINDEX') && REDIS_DBINDEX != 0) {
            $redis->select(REDIS_DBINDEX); //选择库
        }
        return $redis;
    }

    /**
     * 重新连接
     */
    public function reconnect()
    {
        echo "reconnect Redis..\n";

        //清空原有的
        $this->redis = null;

        $redis =  self::connect_redis();
        $this->redis = $redis;

        /*
        $redis = new Redis();         //创建Redis对象
        $redis->connect(REDIS_HOST);  //连接服务
        $redis->auth(REDIS_PASS);     //验证

        $this->redis = $redis;
        */
    }

    /**
     * 设置缓存
     * @param str $key
     * @param str $value
     * @param int $time
     * @return true|false
     */
    private function set($key, $value, $time = '')
    {
        $value = json_encode($value);
        $ret_res = $this->redis->set($key, $value);

        if (!$ret_res) {
            echo 'Redis create fail<br>';
        }

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
    private function get($key)
    {
        $res = $this->redis->get($key);

        if (!$res) {
            return $res;
        }

        return json_decode($res, true);
    }

    private function delete($key)
    {
        return $this->redis->delete($key);
    }

    /**
     * 清除命名空间的缓存
     */
    private function delete_ns($key)
    {
        $imageSet = $this->keys($key . '*');

        foreach ($imageSet as $value) {
            $this->delete($value);
        }

        return true;
    }

    /**
     * 清空数据
     */
    private function flushAll()
    {
        return $this->redis->flushAll();
    }

    private function keys($key)
    {
        return $this->redis->keys($key);
    }

    /**
     * 检测redis服务状态
     * @todo debug 完善后编写
     */
    private function ping()
    {
        return $this->redis->ping();
    }

    /**
     * 数据入队列
     * @param string $key KEY名称
     * @param string|array $value 获取得到的数据
     * @param bool $right 是否从右边开始入
     */
    private function push($key, $value, $right = true)
    {
        $value = json_encode($value);
        return $right ? $this->redis->rPush($key, $value) : $this->redis->lPush($key, $value);
    }

    /**
     * 数据出列
     * @param string $key KEY名称
     * @param string|array $value 获取得到的数据
     * @param bool $right 是否从右边开始入
     */
    private function pop($key, $left = true)
    {
        $val = $left ? $this->redis->lPop($key) : $this->redis->rPop($key);
        return json_decode($val);
    }

    private function lSize($key)
    {
        return $this->redis->lSize($key);
    }

    private function increment($key)
    {
        return $this->redis->incr($key);
    }

    private function decrement($key)
    {
        return $this->redis->decr($key);
    }

    private function exists($key)
    {
        return $this->redis->exists($key);
    }

    private function redis()
    {
        return $this->redis;
    }

    /**
     * 生成命名空间前缀
     * @param unknown_type $key
     * @return Ambigous <string>
     */
    private function buildKey($key)
    {
        static $keys = array();

        if (!isset($keys[$key])) {
            $keys[$key] = $this->MC_NS . '-' . (strlen($key) < 41 ? $key : substr($key, 0, 40) . md5($key) . strlen($key));
        }

        return $keys[$key];
    }
}