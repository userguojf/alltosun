<?php

/**
 * alltosun.com DB类、DBAbstract类、DBWrapper类 DB.php
 * ============================================================================
 * 版权所有 (C) 2007-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 安然 (anr@alltosun.com) $
 * $Date: 2009-03-21 18:48:03 +0800 $
 * $Id: DB.php 1039 2015-10-26 04:09:22Z liudh $
 * @link http://wiki.alltosun.com/index.php?title=Framework:DB.php
*/

/**
 * 生成单例数据库对象
 * 通过此类可以适配多种数据库驱动
 * @author anr@alltosun.com
 * @package AnDB
 * @link http://wiki.alltosun.com/index.php?title=Framework:class:DB
 */
class DB
{
    /**
     * mysqlWrapper/mysqliWrapper/PDOWrapper等引擎的实例
     * @var $connections[]
     */
    public static $connections = array();

    /**
     * 当前建立所有的数据库连接
     * @var resource[]
     */
    public static $links = array();

    /**
     * 操作的sql语句，debug状态使用
     * @var $sql[]
     */
    public static $sql = array();

    /**
     * 最后执行的操作语句结构
     * @var $lastQuerySQL[]
     */
    public static $lastQuerySQL = array();

    /**
     * 执行SQL语句数
     * @var $SQLNO
     */
    public static $SQLNO = 0;

    /**
     * 操作的sql语句，debug状态使用
     * @var $debug
     */
    public $debug = false;

    /**
     * 连接数据库（可连接多个数据库）
     * 连接多个数据库时，第一个为主库，其他均为从库（从库数量不限）
     * @example 连接单个数据库格式之一：connect('driver', 'host', 'user', 'password', 'db')
     * @example 连接单个数据库格式之二：connect(array('driver', 'host', 'user', 'password', 'db'))
     * @example 连接多个数据库格式之一：connect(array('driver', 'host', 'user', 'password', 'db_master'), array('driver', 'host', 'user', 'password', 'db_slave0'), array('driver', 'host', 'user', 'password', 'db_slave1'))
     * @example 连接多个数据库格式之二：connect(array(array('driver', 'host', 'user', 'password', 'db_master'), array('driver', 'host', 'user', 'password', 'db_slave0'), array('driver', 'host', 'user', 'password', 'db_slave1')))
     * @example 可配合Config来连接数据库，方法为只传入一个字符串参数，该参数将被作为Config获取
     *          如：connect('db')则是调用的Config::get('db')的配置
     * @throws Exception
     */
    public static function connect()
    {
        $params = func_get_args();
        $p_arr = array();
        $node_arr = array();

        if (!$params) {
            throw new AnException('DB Error!', 'DB::connect() Error!Empty DB config.');
        }

        // 连接参数接收connect('…', '…', '…') connect(array(), array()) connect(array(array(), array()))
        if (!is_array($params[0])) {
            // 直接参数
            // Config::set('db', array('mysql', 'localhost', 'uname', 'ps', 'db_name'));
            // 1、DB::connect('driver', 'host', 'user', 'ps', 'db')
            // $params = array(0=>'driver', 1=>'host', 2=>'user', 3=>'ps', 4=>'db')
            if (count($params) == 1) {
                // 2、DB::connect('db')
                // 从Config中读取
                $db_conf = Config::get($params[0]);
                if (is_array($db_conf[0])) $p_arr = $db_conf;
                else $p_arr[] = $db_conf;
            } else {
                $p_arr[] = $params;
            }
        } elseif (count($params) == 1 && is_array($params[0][0])) {
            // 多维数组
            // Config::set('db', array(array('mysql', 'localhost', 'uname', 'ps', 'db_name'), array('mysql', 'localhost', 'uname', 'ps', 'db_name')));
            // 3、DB::connect(array(array(…), array(…))
            // $params = array(0=>array(array(……), array(……)))
            $p_arr = $params[0];
        } else {
            // 多个参数
            // 4、DB::connect(array(…), array(…))
            // $params = array(0=>array(…), 1=>array(…))
            $p_arr = $params;
        }

        if (!$p_arr) {
            throw new AnException('DB Error.', 'DB::connect() Error!Empty DB param config.');
        }

        $db = NUll;
        foreach ($p_arr as $k => $v) {
            if (!$v) continue;
            $key = md5(serialize($v));
            $driver = array_shift($v);
            if (!isset(self::$connections[$key])) {
                require_once dirname(__FILE__).'/DB/'.$driver.'.php';
                $class = $driver.'Wrapper';
                self::$connections[$key] = $tmp = new $class($v);
                $tmp->db_driver = $driver;
                $db_conf = DB::configInfo($driver, $v);
                $tmp->db_host = $db_conf['db_host'];
                $tmp->db_name = $db_conf['db_name'];
                if (defined('D_BUG')) $tmp->debug = D_BUG;
            }
            if (NULL === $db) {
                //  主库设置
                $db = self::$connections[$key];
            } else {
                // 从库设置
                $db->db_slaves[] = self::$connections[$key];
            }
        }

        // 连接热备
        /*$db_hot = Config::get('db_hot');
        if (isset($db) && !empty($db_hot) && is_array($db_hot)) {
            $db->db_hot = DB::connect($db_hot);
        }*/

         return $db;
    }

    /**
     * 解析数据连接参数
     * @param string $db_driver 如果$db_conf不存在，从config中取配置
     * @param string $db_conf
     * @return array
     */
    public static function configInfo($db_driver, $db_conf = NULL)
    {
        $status = array();
        if (NULL === $db_conf) {
            $status['db_op'] = $db_driver;
            $db_conf = Config::get($db_driver);
            $db_driver = array_shift($db_conf);
        }
        if (is_array($db_conf[0])) {
            $status['db_slave'] = 1;
            $db_conf = $db_conf[0];
        } else {
            $status['db_slave'] = 0;
        }

        if ('mysql' == $db_driver || 'mysqli' == $db_driver) {
            $status['db_host'] = $db_conf[0];
            $status['db_name'] = $db_conf[3];
        } elseif ('sqlite' == $db_driver) {
            $status['db_host'] = 'localhost';
            $status['db_name'] = $db_conf[0];
        } elseif ('PDO' == $db_driver) {
            // parse_str("mysql:dbname=shop;host=localhost", $var);
            parse_str(str_replace(array(':', ';'), '&', $db_driver), $var);
            $status['db_host'] = $var['host'];
            $status['db_name'] = $var['dbname'];
        }

        return $status;
    }

    /**
     * 关闭数据库连接
     * 注册在 register_shutdown_function() 中
     */
    public static function close()
    {
        foreach (self::$connections as $v) {
            if (method_exists($v, 'close')) {
                $v->close();
            }
        }
    }

    /**
     * 得到数据库的生存状态
     * @param string $db_host
     * @todo
     */
    public static function getState($db_host)
    {
        return true;
    }

    /**
     * 设置数据库的生存状态
     * @param string $db_host
     * @param bool $state
     * @todo
     */
    public static function setState($db_host, $state)
    {
        return true;
    }

    /**
     *
     * 检查表名、字段名、Model名是否合法，合法返回true
     * @param string $name
     * @return true|false
     */
    public static function legalName($name)
    {
        // liw 修改 2014-11-13：项目设计时数据表名不要以数字开头
        // 具体表现为：当表开头的数字同时存在在resource的自增id时会出现表被指向到该resource记录。
        // @fixed http://ace.alltosun.com/alltosun.com/task/9622.html

        // if (preg_match("/^[a-zA-Z0-9_\.\-]*$/", $name)) {
        if (preg_match("/^[a-zA-Z][a-zA-Z0-9_\.\-]*$/", $name)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 返回最后执行的SQL语句数组
     * @return array
     */
    public static function lastSQL()
    {
        return DB::$lastQuerySQL;
    }
}

/**
 * 数据库操作的抽象层，封装丰富的基本操作(CRUD)与高级操作
 * Model/ModelRes类通过的“代理”的方式调用DB中的方法，以实现对数据库表操作的统一
 * 本类中的方法不涉及缓存的操作，即不读缓存不删除缓存
 * 2010.10.12 实现数据库读写分离时数据库对象的选择
 * @example $db = DB::connect(array(), array());
 * @example $db->read(1); // 操作主库
 * @example $db['m']->read(1); // 操作主库
 * @example $db[-1]->read(1);  // 操作随机从库
 * @example $db['s']->read(1); // 操作随机从库
 * @example $db[0]->read(1); // 操作指定从库
 * @example $db[1]->read(1); // 操作指定从库
 * @package AnDB
 * @link http://wiki.alltosun.com/index.php?title=Framework:class:DBAbstract
 */
abstract class DBAbstract
{
    /**
     * 数据库配置
     * @var string[]
     */
    protected $config = array();

    /**
     * 当前的数据库连接
     * @var resource|null
     */
    public $link = null;

    /**
     * 使用的引擎
     * @var string
     */
    public $db_driver = '';

    /**
     * 使用的主机名
     * @var string
     */
    public $db_host   = '';

    /**
     * 使用的db名
     * @var string
     */
    public $db_name   = '';

    /**
     * 作为主键的字段名
     * @var string
     */
    public $pk = '';

    /**
     * db从库
     * @var array
     */
    public $db_slaves = array();

    /**
     * hot热备是否打开
     * @var bool
     */
    public $db_hot_open = false;

    /**
     * hot热备库配置，默认取Config类中的配置
     * @var string
     */
    public $db_hot_conf = 'db_hot';

    /**
     * db热备库的对象
     * @var string
     */
    public $db_hot = '';

    /**
     * SQL连贯操作缓存值，引用
     * @var array ref
     */
    public $option = NULL;

    /**
     * 表名缓存
     * @var array
     */
    public static $tables = array();

    /**
     * 最后执行的操作语句结构
     * @var array
     */
    public static $lastSQLS = array();

    /**
     * 初始化配置，并调用存在的init()方法
     * @param array $config
     */
    public function __construct($config)
    {
        $this->config = $config;
        if (method_exists($this, 'init')) {
            $this->init();
        }
    }

    /**
     * 对象被clone时增加连接到DB::$connections
     * @uses DB::$connections
     */
    public function __clone()
    {
        DB::$connections[] = $this;
    }

    /**
     * 执行SQL
     * 根据SQL进行主从分离
     * @param mixed $params
     * @param int $fixlimit 是否要在sql后面自动补充limit 1
     */
    public function query($params, $fixlimit = 0)
    {
        if (!$params) {
            throw new AnException('DB Error.', 'DBAbstract::query() Error!$params is empty');
        }

        if ($this->option) $this->option = array();
        DB::$lastQuerySQL = $params;
        DB::$SQLNO += 1;

        return $this->query_exe($params, $fixlimit);
    }

    /**
     * 读取1条记录，支持各种形式的条件
     * @param string $table 表名
     * @param array $filter 1、数字 id；2、 1维条件数组（array(1,2,3)或array('id'=>2000)）；3、 SQL语句WHERE…ORDER…LIMIT…\OR…\ 等；4、逗号分隔的id "1,2,3,4"
     * @param string $sql 补充的sql，在where以后，可以是  ORDER 也可以是 LIMIT，甚至 是 WHERE 的补充
     * @return array 1维数组
     * @example read('article', 2)
     * @example read('article', array('id'=>2000))
     * @example read('article', array(1,2,3,4))
     * @example read('article', "1,2,3,4")
     * @example read('article', "ORDER BY `id`"); // 读最新1条记录
     * @example read('article', "ORDER BY `id` LIMIT 1"); // 读最新1条记录（会自动补充 LIMIT 1）
     * @example read('article', array('user_id'=>2010, 'res_type'=>1)) // 条件数组
     * @example read('article', array('user_id'=>2010, 'res_type'=>1), 'ORDER BY `add_time` DESC') // 条件数组
     */
    public function read($table, $filter, $half_sql = '')
    {
        $sql = 'SELECT' . $this->treatField() . ' FROM' .$this->treatTable($table) . $this->arrayToWhere($filter) . $this->treatOrder() . $this->treatLimit() . $this->treatHalfSql($half_sql);

        return $this->getRow($sql, $filter);
    }

    /**
     * 删除表记录，支持各种形式的条件
     * @param string $table 表名
     * @param array $filter 参数形式参考 read()
     * @return int 执行的结果
     * @example delete('article', 1001);
     * @example delete('article', array('id'=>2000));
     * @example delete('article', array('id'=>array(110000,1000,1001)));
     * @example delete('article', array(110000,1000,1001));
     */
    public function delete($table, $filter)
    {
        $sql = 'DELETE FROM' .$this->treatTable($table) . $this->arrayToWhere($filter);
        return $this->exec($sql, $filter);
    }

    /**
     * 更新表记录，支持各种形式的条件
     * @param string $table 表名
     * @param array $filter 参数形式参考 read()，支持各种形式的条件
     * @param array $info 更新的内容
     * @return int 更新的记录数
     * @example update('ad', 1001, "SET num=num+1");  // +1 操作
     * @example update('ad', 1001, array('name'=>"互动阳光");
     * @example update('ad', array('id'=>2000), array('name'=>"互动阳光");
     */
    public function update($table, $filter, $info)
    {
        $params = (is_array($info)) ? array_values($info) : array();


        $sql = 'UPDATE' . $this->treatTable($table) . $this->arrayToUpdate($info) . $this->arrayToWhere($filter);

        if ($filter && is_array($filter)) {
            foreach ($filter as $v) {
                $params[] = $v;
            }
        }

        return $this->exec($sql, $params);
    }

    /**
     * 写入1条记录
     * @param string $table 表名
     * @param array $info 插入的数组
     * @param string|null $half_sql 附加的SQL语句  REPLACE ，或 ON DUPLICATE UPDATE……，或简写的UPDATE
     * @return int 新插入的id
     * @example create('ad', array('title'=>'alltosun'));
     * @example create('ad', array('id'=>'1000', 'title'=>'test', 'hits'=>100), 'ON DUPLICATE KEY UPDATE hits=hits+1');
     * @example create('ad', array('id'=>'1000', 'title'=>'test', 'hits'=>100), 'UPDATE hits=hits+1');
     * @example create('ad', array('id'=>'1000', 'title'=>'test', 'hits'=>100), 'REPLACE');
     */
    public function create($table, $info, $half_sql = '')
    {
        if ($half_sql && (false !== stripos($half_sql, 'REPLACE'))) {
            $half_sql = '';
            $sql = 'REPLACE INTO' . $this->treatTable($table);
        } else {
            $sql = 'INSERT INTO' . $this->treatTable($table);
            if ($half_sql && (false !== stripos($half_sql, 'UPDATE ')) && (false === stripos($half_sql, 'ON '))) {
                $half_sql = 'ON DUPLICATE KEY ' . $half_sql;
            }
        }

        $sql .= '('.$this->treatField(array_keys($info)).') VALUES ('.implode(',', array_fill(0, count($info), '?')) . ') '. $half_sql;

        $result = $this->exec($sql, $info);
        $id = $this->lastInsertId();

        if ($id) return $id;
        return $result;
    }

    /**
     * 获取指定条件记录集
     * @param string $table 表名
     * @param array $filter 参数形式参考 read()和arrayToWhere()，支持各种形式的条件
     * @param string $sql SQL中的limit语句，或其它任何SQL语句
     * @return array 2维数组
     * @example getList('ad', 'LIMIT 10'); // 取10条记录
     * @example getList('ad', 'ORDER BY `id` LIMIT 10'); // 取最新10条记录
     * @example getList('ad', array('res_type' => 1), 'ORDER BY `addtime` LIMIT 10'); // 取 res_type=1 的最新10条记录
     * @example getList('ad', array('res_type' => 1, 'name' =>'%福%'), 'ORDER BY `addtime` LIMIT 10'); // 取 res_type=1，name中包含福的最新10条记录
     * @example getList('ad', "2,3,4,10,20", "LIMIT 10");
     * @example getList('ad', array(2,3,4,10,20), "LIMIT 10");
     * @example getList('ad', array('id'=>array(2,3,4,10,20)), "LIMIT 10");
     */
    public function getList($table, $filter = array(), $half_sql = '', $field = '')
    {
        // $filter 可为空
        if (!$filter && empty($this->option['limit']) && (!$half_sql || stripos($half_sql, 'LIMIT') === false)) {
            // 2011-7-18 条件为空，报错
            throw new AnException('DB Error.', 'DBAbstract::getList() Error!$filter is empty.');
        }

        $sql = 'SELECT' . $this->treatField($field) . ' FROM' . $this->treatTable($table) . $this->arrayToWhere($filter, NULL, 0) . $this->treatOrder() . $this->treatLimit() . $this->treatHalfSql($half_sql);

        return $this->getAll($sql, $filter);
    }

    /**
     * 返回指定条件的字段集，1维数组
     * @param string $table 表名
     * @param array $field 取的字段名，只支持1个字段名
     * @param array $filter 参数形式参考 read()和arrayToWhere()，支持各种形式的条件
     * @param string $sql SQL中的limit语句，或其它任何SQL语句
     * @param string $table 指定表
     * @return array 1维数组
     * @example getFields('ad', 'id'); // 取所有记录
     * @example getFields('ad', 'id', 'WHERE id<100 ORDER BY `id` LIMIT 10'); // 取最新10条记录
     * @example getFields('ad', 'id', 'ORDER BY `id` LIMIT 10'); // 取最新10条记录
     * @example getFields('ad', 'id', 'LIMIT 10'); // 取10条记录
     * @example getFields('ad', 'id', array('res_type' => 1), 'ORDER BY `id` LIMIT 10'); // 取 res_type=1 的最新10条记录
     * @example 更多参考 DBAbstract::getList()
     */
    public function getFields($table, $field = 'id', $filter = array(), $half_sql = '')
    {
        // $filter 可为空
        if (!$filter && empty($this->option['limit']) && (!$half_sql || stripos($half_sql, 'LIMIT') === false)) {
            // 2011-7-18 条件为空，报错
            throw new AnException('DB Error.', 'DBAbstract::getFields() Error!$filter is empty.');
        }

        $sql = 'SELECT' . $this->treatField($field) . ' FROM' .$this->treatTable($table) . $this->arrayToWhere($filter, NULL, 0) . $this->treatOrder() . $this->treatLimit() . $this->treatHalfSql($half_sql);

        return $this->getCol($sql, $filter);
    }

    /**
     * 统计相关条件记录数
     * @param string $table 表名
     * @param array $filter 参数形式参考 read()和arrayToWhere()，支持各种形式的条件。
     *        注意：如果为空统计全表记录数。
     * @return int
     * @example getTotal(); // 统计全表记录数
     * @example getTotal('ad', array('type' => 1)); // 统计 type 为1的记录数
     * @example getTotal('ad', array('type' => 1, 'name like' =>'%福%'));  // 统计 type 为1的记录数且name中包含“福”的记录数
     * @example 更多参考 getList()
     */
    public function getTotal($table, $filter = array(), $half_sql = '')
    {
        // $filter 可为空

        $sql = 'SELECT COUNT(*) FROM' .$this->treatTable($table) . $this->arrayToWhere($filter, NULL, 0) . $this->treatHalfSql($half_sql);

        return $this->getOne($sql, $filter);
    }

    /**
     * 创建数据连接
     */
    public function getDriver()
    {
        return $this->initialization();
    }

    /**
     * 构造WHERE条件SQL语句
     * 对 LIKE 的支持通过在key中添加LIKE的方式进行，array('title LIKE'=>'%allto%')
     * @param mixed $filter 1维条件数组、2维数组、id组成的字符串、简单的sql语句（不能有'"）
     * @param string $pk    PK字段的名，默认为id
     * 以下为$filter参数的具体说明
     * @return string 1、WHERE `res_id`=? AND `res_type`=?，2、WHERE `res_id` IN(?,?,?)，3、WHERE `name` LIKE ?
     * @example 0、条件为空，返回空字符串；如'',0,false 返回： ''
     * @example 1、条件为字符串
     *          1.1 将做为未来SQL的一部分，注意会过滤所以不支持'"，只支持简化的SQL。如'ORDER BY `id` LIMIT 10'、'LIMIT 10' 直接返回至未来的SQL中
     *          1.2 条件为含有逗号分隔的字符串，会转化成IN操作。如'1,2,3'，返回： WHERE `id` IN(?,?,?)
     * @example 3、条件为数字，如1，直接返回： WHERE `id`='1'
     * @example 4、条件为1维数组，key为自然数字，转换成IN操作。如array(1,2,3,4)，返回：  WHERE `id` IN(?,?,?)
     *          4.1 条件为1维数组，key为字段名，转换成条件 array('add_time'=>'2010-10-1')，返回：WHERE `add_time` = ?
     * @example 5、条件是2维数组，将依次转化成多个条件。如：array('id'=>array(1,2,3,4), 'group_id'=>array(1,2))，返回： WHERE `id` IN(?,?,?) AND `group_id` IN (?,?)
     * @example 6、对LIKE,AND,OR,>,<,=,!的支持是通过条件数组key的特殊写法来支持的
     *          6.1 对LIKE的支持 array('name LIKE'=>'%福%') 返回 WHERE `name` LIKE ?
     *          6.1 对LIKE的支持 array('title LIKE'=>array('allto%', '%tosun%')) 返回 WHERE (`title` LIKE? OR `title` LIKE?)
     *          6.2 对AND支持(key中带AND) array('add_time'=>'2010-10-1', 'AND `id`'=>'100')，返回：WHERE `add_time` =? AND `id` =?
     *          6.3 对AND支持(key中不带AND,即默认支持) array('add_time'=>'2010-10-1', 'id'=>'100')，返回：WHERE `add_time` =? AND `id` =?
     *          6.3 对OR支持(key中带OR) array('add_time'=>'2010-10-1', 'OR `add_time`'=>'2010-10-2')，返回：WHERE `add_time` =? OR `add_time` =?
     *          6.4 对>,<,=,! 支持(key中带相应符号) array('add_time >'=>'2010-10-1', 'OR `add_time` >='=>'2010-10-2')，返回：WHERE `add_time` >? OR `add_time` >=?
     */
    public function arrayToWhere(&$filter, $pk = NULL, $check = 1)
    {
        if (!$filter) {
            // 0、条件为空
            if (!$check) {
                // 不检查条件为空，即允许条件为空
                return '';
            }
            // 条件为空报错 2012-06-11
            throw new AnException('DB Error.', 'DBAbstract::arrayToWhere() Error!$filter is empty.');
        }

        if (NULL === $pk) $pk = $this->pk ? $this->pk : 'id';

        if (is_numeric($filter)) {
            // 3、如果只是1个数字id
            $filter = (array) $filter;
            return " WHERE `{$pk}`=?";
        } elseif (is_string($filter)) {
            if (is_numeric($filter[0]) || (strpos($filter, ',') && !strpos($filter, ' '))) {
                // 2、'1,2,3,4,5'
                // 注意：不支持 'a,b,c,d'字符串组成的keys
                // 注意：不能有空格，因空格是sql的组成部分
                $filter = $this->removeFilterBadChar(explode(',', $filter));
                return " WHERE `{$pk}` IN(" . implode(',', array_fill(0, count($filter), '?')) . ")";
            } else {
                if (' ' === $filter[0]) {
                    throw new AnException('DB Error.', 'DBAbstract::arrayToWhere() Error!$filter is invalid,the first character can not be space.');
                }
                // 1、WHERE,LIMIT,ORDER 简单的SQL语句
                // 注意：不能包含 英文单引、双引号 '"
                // 复杂的写到sql参数中
                //@todo
                return ' ' . addslashes($filter);
            }
        } elseif (isset($filter[0])) {
            // 4、如果是1维数组，array(1,2,3,4);
            return " WHERE `{$pk}` IN(" . implode(',', array_fill(0, count($filter), '?')) . ')';
        }

        $where = '';
        $sql = '';
        $parm = array();
        foreach ($filter as $k => $v) {
            // 过滤 key
            $k = $this->removeFilterBadChar($k);

            if (is_array($v)) {
                if (!$v) continue;

                if (strpos($k, '>') || strpos($k, '<') || strpos($k, '!')  || strpos($k, '=') || stripos($k, 'LIKE')) {
                    // 6.1、如果是LIKE条件的2维数组，array('title LIKE'=>array('allto%', '%tosun'))
                    $connection = '';
                    $field = $k;
                    // array('title LIKE'=>array('allto%', '%tosun'), 'OR content LIKE'=>array('all%', '%to'))
                    if (false !== stripos($k, 'OR ')) {
                        $connection = substr($k, 0, stripos($k, 'OR ') + 3);
                        $field = substr($k, stripos($k, 'OR ') + 3);
                    } elseif (false !== stripos($k, 'AND ')) {
                        $connection = substr($k, 0, stripos($k, 'AND ') + 4);
                        $field = substr($k, stripos($k, 'AND ') + 4);
                    }
                    // array('(title LIKE'=>array('allto%'), 'OR content LIKE'=>array('all%'), ') AND intro LIKE'=>array('%all'))
                    if (false !== strpos($field, '(')) {
                        $connection .= '(';
                        $field = substr($field, strpos($field, '(') + 1);
                    } elseif (false !== strpos($field, ')')) {
                        $connection = ')' . $connection;
                        $field = substr($field, strpos($field, ')') + 1);
                    }
                    $where_arr = array();
                    foreach ($v as $v1) {
                        $where_arr[] = $field . '?';
                        $parm[] = $v1;
                    }
                    $where = $connection . '(' . implode(' OR ', $where_arr).')';
                } else {
                    // 5、如果是2维数组，array('id'=>array(1,2,3,4))
                    $where = $k . ' IN(' . implode(',', array_fill(0, count($v), '?')) . ')';
                    foreach ($v as $v1) {
                        $parm[] = $v1;
                    }
                }
            } elseif (strpos($k, '>') || strpos($k, '<') || strpos($k, '!')  || strpos($k, '=') || stripos($k, 'LIKE')) {
                // 6、array('add_time >'=>'2010-10-1')，条件key中带 > < 符号
                // 6、array('add_time ='=>'2010-10-1')，条件key中带 > < 符号
                // 6、array('add_time !='=>'2010-10-1')，条件key中带 > < 符号
                // 6、array('title LIKE'=>'%福')，LIKE 的支持
                $where = $k . '?';
                $parm[] = $v;
            } else {
                // 8、array('res_type'=>1)
                $where = $k . '=?';
                $parm[] = $v;
            }

            if (!$sql) $sql = " WHERE {$where}";
            // 如 key 中有 AND OR，不处理，否则添加 AND
            else $sql = $sql . ' ' . ((false !== stripos($k, 'OR ') || false !== stripos($k, 'AND ')) ? '': 'AND ') . $where;
        }
        $filter = $parm;

        return $sql;
    }

    /**
     * 构造更新的SQL
     * @param mixed $info 0、为空，返回空；
     *                    1、如果是字串，直接返回，如：id=id+1；
     *                    2、一维数组，array('name'=>'alltosun.com')，返回：SET `name`=?
     * @return string
     */
    public function arrayToUpdate(&$info)
    {
        if (!$info) {
            throw new AnException('DB Error.', 'DBAbstract::arrayToUpdate() Error!$info is empty.');
        }
        if (is_string($info)) {
            // 1、如果是SQL语句
            return $info;
        }

        // 2、1维数组，类似于array('name'=>'alltosun.com')
        $s = '';
        foreach ($info as $k => $v) {
            // 过滤 key
            if ($s) { $s .= ','; }
            $s .= '`'.$this->removeBadChar($k).'`=?';
        }

        return ' SET ' . $s;
    }

    /**
     * 绑定变量，替换问号为变量值
     * @param string $sql sql语句
     * @param array $params 变量值数组
     * @return string 绑定变量后的sql语句
     */
    public function bindParam($sql, $params)
    {
        if (!$params || !is_array($params) || !strpos($sql, '?')) return $sql;

        // 进行 ? 的替换，变量替换
        $offset = 1;
        $i = 0;
        if (substr_count($sql, '?') != count($params)){
            die("BindParam error, SQL: " . $sql . "\n<br>" . var_export($params, true));
        }

        //变成下表形式
        if (!isset($params[0])){
            $params = array_values($params);
        }
        while ($offset = strpos($sql, '?', $offset)) {
            $p = $params[$i++];
            if ('`' === $sql[$offset-1] || "'" === $sql[$offset-1]) {
                $sql = substr_replace($sql, $p, $offset, 1);
            } else {
                $sql = substr_replace($sql, "'".$p."'", $offset, 1);
            }
            $offset = 1 + $offset + strlen($p);
        }

        return $sql;
    }

    /**
     * 过滤表名、字段名中的非法字符
     * @param string $name
     * @return string
     */
    public function removeBadChar($name)
    {
        return str_replace(array(' ', '`', "'", "'", ',', ';', '*', '#', '/', '\\', '%'), '', $name);
    }

    /**
     * 过滤 Filter 中的非法字符
     * @param mixed $array
     * @return mixed
     */
    public function removeFilterBadChar($array)
    {
        if (is_numeric($array)) {
            return $array;
        } elseif (!is_array($array)) {
            return str_replace(array('"', "'", ',', ';', '*', '#', '/', '\\', '%'), '', $array);
        }

        foreach ($array as $k => $v) {
            $array[$k] = $this->removeFilterBadChar($v);
        }

        return $array;
    }

    /**
     * 判断表是否存在
     * @param string $table
     * @return array 为空返回空array(), 正常返回 array([0]=>"ad", [1]=>"ad_model")
     * @example $this->tableExists('adsss'); // array()
     * @example $this->tableExists('ad'); // array([0]=>"ad")
     * @example $this->tableExists('ad%'); // array([0]=>"ad", [1]=>"ad_model")
     */
    public function tableExists($table)
    {
        return $this->getCol("SHOW TABLES LIKE '" . $this->removeBadChar($table) . "'");
    }

    /**
     * 读取表结构
     * @param string $table 表名
     * @return array 表的结构 2 维数组
     */
    public function describe($table)
    {
        // if (!$this->tableExists($table)) return array(); // 去除可以减少一步操作
        $tb = array();
        $table_describe = $this->getAll('Describe `' . $this->removeBadChar($table) . '`');
        foreach ($table_describe as $v) {
            $tb[$v['Field']] = $v;
        }
        return $tb;
    }

    /**
     * 判断表中字段是否存在
     * @param string $table
     * @param string $field
     * @return bool
     */
    public function fieldExists($table, $field)
    {
        $tb = $this->describe($table);
        if (!$tb) return false;
        return array_key_exists($field, $tb);
    }

    /**
     * 处理函数后补充的sql
     * @param string $half_sql，位于where以后的补充Sql
     * @return string
     */
    public function treatHalfSql($half_sql)
    {
        if ($half_sql) {
            return ' ' . $half_sql;
        } else {
            return '';
        }
    }

    /**
     * 处理表名
     * @param string $table
     * @return string
     */
    public function treatTable($table)
    {
        if (isset($this->tabbles[$table])) {
            return $this->tabbles[$table];
        }
        $this->tabbles[$table] = $this->removeBadChar($table);
        if (!$this->tabbles[$table]) {
            throw new AnException('DB Error!', 'DBAbstract::treatTable() Error!$table is empty.');
        }

        return $this->tabbles[$table] = ' `' . $this->tabbles[$table] . '`';
    }

    /**
     * 返回表中唯一的PK字段名
     * 如果有多个，返回为0
     * @param  string $table 表名
     * @return string PK字段名
     */
    public function pk($table)
    {
        return $this->checkPK($this->describe($table));
    }

    /**
     * 返回表中唯一的PK字段名
     * 如果有多个，返回为0
     * @param  string $table 表名
     * @return string PK字段名
     */
    public function checkPK($tb)
    {
        if (!$tb) return '';

        $pk = $uk = '';
        foreach ($tb as $v) {
            // array(["id"]=>array{["Field"]=>"id", ["Type"]=>"int(10) unsigned",["Null"]=>"NO",["Key"]=>"PRI",["Default"]=>NULL,["Extra"]=>"auto_increment")
            if ($v['Key'] === 'PRI' && $pk !== 0) {
                if ($pk) $pk = 0; // 有多个 pk 字段不可以使用
                else $pk = $v['Field'];
            } elseif ($v['Key'] === 'UNI') {
                if (!$uk) $uk = $v['Field'];
            }
        }

        if ($pk) return $pk;
        elseif ($uk) return $uk;
        else return '';
    }

    /**
     * 处理SQL中的field部分
     * @param string $field 字段名，支持逗号分隔的多个字段名
     * @return string
     */
    public function treatField($field = NULL)
    {
        if ((!$field && empty($this->option['field'])) || ('*' === $field)) {
            return ' *';
        }

        if ($field) {
            if (is_array($field)) ;
            elseif (strpos($field, ',')) $field = explode(',', $field);
            else return ' `' . $this->removeBadChar($field) . '`';
        } else {
            if (strpos($this->option['field'][0], ',')) $field = explode(',', $this->option['field'][0]);
            else $field = $this->option['field'];
        }

        $sql = '';
        foreach ($field as $v) {
            if ($sql) { $sql .= ','; }
            $sql .= '`' . $this->removeBadChar($v) . '`';
        }

        return ' ' . $sql;
    }

    /**
     * 处理SQL中的order部分
     * @return string
     */
    public function treatOrder()
    {
        if (empty($this->option['order'])) {
            return '';
        }

        if (strpos($this->option['order'][0], ',')) {
            $this->option['order'] = explode(',', $this->option['order'][0]);
        }

        $sql = '';
        foreach ($this->option['order'] as $v) {
            // $sql .= $this->removeBadChar($v);
            if ($sql) { $sql .= ','; }
            $sql .= $this->removeFilterBadChar($v);
        }

        return ' ORDER BY ' . $sql;
    }

    /**
     * 处理SQL中的limit部分
     * @return string
     */
    public function treatLimit()
    {
        if (empty($this->option['limit'])) {
            return '';
        }

        $sql = ' LIMIT ' . intval($this->option['limit'][0]);
        if (!empty($this->option['limit'][1])) $sql .= ',' . intval($this->option['limit'][1]);

        return $sql;
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
    public function addDebugInfo($fun, $op, $info)
    {
        $info['time'] = AnPHP::lastRunTime() * 1000;
        $info['op'] = $op;
        if (empty($info['sql_real'])) $info['sql_real'] = '';
        if (empty($info['sql_info'])) $info['sql_info'] = '';
        if (empty($info['explain'])) $info['explain'] = array();
        AnDebug::$op[] = array('type' => 'db', 'info' => $info);

        return true;
    }

    /**
     * SQL语句过滤程序，由80sec提供，这里作了适当的修改
     * @param string $db_string
     * @param string $querytype
     */
    static function MysqlCheckSql($db_string, $querytype = 'select')
    {
        $clean = '';
        $error = '';
        $old_pos = 0;
        $pos = -1;
        // $log_file = DEDEINC.'/../data/'.md5($cfg_cookie_encode).'_safe.txt';

        // 如果是普通查询语句，直接过滤一些特殊语法
        if ($querytype == 'select') {
            $notallow1 = "[^0-9a-z@\._-]{1,}(union|sleep|benchmark|load_file|outfile)[^0-9a-z@\.-]{1,}";
            //$notallow2 = "--|/\*";
            if (eregi($notallow1,$db_string)) {
                // fputs(fopen($log_file,'a+'),"$userIP||$getUrl||$db_string||SelectBreak\r\n");
                exit("<font size='5' color='red'>Safe Alert: Request Error step 1 !</font>");
            }
        }

        // 完整的SQL检查
        while (true) {
            $pos = strpos($db_string, '\'', $pos + 1);
            if ($pos === false) {
                break;
            }
            $clean .= substr($db_string, $old_pos, $pos - $old_pos);
            while (true) {
                $pos1 = strpos($db_string, '\'', $pos + 1);
                $pos2 = strpos($db_string, '\\', $pos + 1);
                if ($pos1 === false) {
                    break;
                } elseif ($pos2 == false || $pos2 > $pos1) {
                    $pos = $pos1;
                    break;
                }
                $pos = $pos2 + 1;
            }
            $clean .= '$s$';
            $old_pos = $pos + 1;
        }
        $clean .= substr($db_string, $old_pos);
        $clean = trim(strtolower(preg_replace(array('~\s+~s' ), array(' '), $clean)));

        // 老版本的Mysql并不支持union，常用的程序里也不使用union，但是一些黑客使用它，所以检查它
        if (strpos($clean, 'union') !== false && preg_match('~(^|[^a-z])union($|[^[a-z])~s', $clean) != 0) {
            $fail = true;
            $error="union detect";
        }

        // 发布版本的程序可能比较少包括--,#这样的注释，但是黑客经常使用它们
        elseif (strpos($clean, '/*') > 2 || strpos($clean, '--') !== false || strpos($clean, '#') !== false) {
            $fail = true;
            $error="comment detect";
        }

        // 这些函数不会被使用，但是黑客会用它来操作文件，down掉数据库
        elseif (strpos($clean, 'sleep') !== false && preg_match('~(^|[^a-z])sleep($|[^[a-z])~s', $clean) != 0) {
            $fail = true;
            $error="slown down detect";
        } elseif (strpos($clean, 'benchmark') !== false && preg_match('~(^|[^a-z])benchmark($|[^[a-z])~s', $clean) != 0) {
            $fail = true;
            $error="slown down detect";
        } elseif (strpos($clean, 'load_file') !== false && preg_match('~(^|[^a-z])load_file($|[^[a-z])~s', $clean) != 0) {
            $fail = true;
            $error="file fun detect";
        } elseif (strpos($clean, 'into outfile') !== false && preg_match('~(^|[^a-z])into\s+outfile($|[^[a-z])~s', $clean) != 0) {
            $fail = true;
            $error="file fun detect";
        }

        // 老版本的MYSQL不支持子查询，我们的程序里可能也用得少，但是黑客可以使用它来查询数据库敏感信息
        elseif (preg_match('~\([^)]*?select~s', $clean) != 0) {
            $fail = true;
            $error="sub select detect";
        }
        if (!empty($fail)) {
            // fputs(fopen($log_file,'a+'),"$userIP||$getUrl||$db_string||$error\r\n");
            exit("<font size='5' color='red'>Safe Alert: Request Error step 2!</font>");
        } else {
            return $db_string;
        }
    }
}

/**
 * DB实现的接口类
 * @author anr@alltosun.com
 * @package AnDB
 * @link http://wiki.alltosun.com/index.php?title=Framework:class:DBWrapper
 */
interface DBWrapper
{
    /**
     * 获取一行记录
     * @return array 1维数组
     */
    public function getRow();

    /**
     * 获取一列记录
     * @return array 1维数组，指定字段组成
     */
    public function getCol();

    /**
     * 获取第一个字段的值
     * @return string 第1个字段的值
     */
    public function getOne();

    /**
     * 获取所有记录
     * @return array 2维数组
     */
    public function getAll();

    /**
     * 返回上次插入的id
     * @return int
     */
    public function lastInsertId();

    /**
     * 创建数据连接
     */
    public function getDriver();

    /**
     * 执行sql语句，并返回结果的资源
     * @param string $sql
     * @return resource
     */
    public function query_exe($sql);

    /**
     * 执行sql语句，直接返回sql语句影响的记录数
     * @return int
     */
    public function exec();

    /**
     * 初始化，需要定义$db_host、$db_name、$db_driver
     */
    public function initialization(); //
}

/**
 * 注册关闭数据库连接
 */
register_shutdown_function('DB::close');

?>