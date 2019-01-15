<?php

/**
 * alltosun.com Model 基类 Model.php
 * ============================================================================
 * 版权所有 (C) 2007-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 安然 (anr@alltosun.com) $
 * $Date: 2009-10-23 14:33:57 +0800 $
 * $Id: Model.php 1029 2015-10-19 06:20:09Z liudh $
 * @link http://wiki.alltosun.com/index.php?title=Framework:Model.php
 * @see http://wiki.alltosun.com/index.php?title=Model%26ModelRes
*/

/**
 * Model基础类
 * Model不提倡直接使用，通过工厂函数_model()生成model对象来使用
 * 将表的操作转换成类的操作、数组的操作
 * 注意新添加方法先执行 initialization() 操作
 * 关于属性的值的设定的优先级
 * 1、首先：传过来优先
 * 2、其次：类中自定义
 * 3、最后：默认
 * @author anr@alltosun.com
 * @package AnModel
 * @link http://wiki.alltosun.com/index.php?title=Framework:class:Model
 * @example 使用示例: http://mantis.alltosun.com/view.php?id=1785#bugnotes
 */
class Model
{
    // 数据库相关
    // public $db_master = NULL; // 主库原始对象（可以自定义）。__construct(),setDB(),selectDB()
    // public $db = NULL; // 数据库操作对象，多库时的主库或热备主库。__get()。通过setDB()来设定
    // public $db_slave  = NULL; // 多库的从库。__get()，当多库时自动选择
    public $db_select = 'a'; // 为 m 时选择主库，为 a 选择从库如果从库不存在选择主库，为 s 选择从库如果从库不存在出错
    public $db_op = 0; // *数据库配置参数，在Config类中保存，通过Config::get('db')获取
    // 预设的分库分表、路由模式，改变Model行为的全局静态变量
    public static $preSplitKey = array();

    // 表相关
    public $table = NULL; // *主表名 1、通过__construct()传过来；2、类中定义；3、取类名 setTable() 会重新设定 table,table_org。通过setTable()来设定，也可以预设，如果预设将不自动获取。
    public $table_org = NULL; // 表原始名 setTable() 中获取
    // public $table_pre = NULL; // 表前缀。__get()
    // public $tableExists = NULL; // 表是不否存在。__get()
    // public $tb = array(); // 表结构。__get() ,如果人工设定为空，注意会影响到pk,an_ext,addtime,tableExists 4个属性都为空，可手工设置。
    // public $add_time = ture; // 表中是否有 add_time 字段，如果设置false强制不执行。__get()
    // public $an_ext = true; // 表中是否有 an_ext 字段，ModelRes 中。如果设置false强制不执行。__get()
    public $pk = NULL; // 主键
    public $field = NULL; // 要取的字段，影响每次取的行为: 1、'id,name'； 2、array('id', 'name')

    // 缓存相关
    public $mc_wr = NULL; // 缓存操作对象
    public $mcs = array(); // 使用mc对象列表
    public $cache = NULL; // 缓存开关，（0为关闭）。为null，取 CACHE 常量
    public $no_cache_fun = array(); // 不使用缓存的函数
    public $lifetime = 1800; // 缓存生命期
    public $db_ns = ''; // 数据库命名，缓存（前缀）
    public $table_ns = ''; // ModelRes 表中单记录的命名空间
    public $table_list_ns = ''; // Model/ModelRes 表记录操作结果集的命名空间
    public $table_alter_ns = ''; // 表结构命名空间
    // public $mc_table_ns; // 表中单记录缓存操作类，通过__get()获取设定。对应 $table_ns
    // public $mc_table_list_ns; // 表记录缓存操作类，通过__get()获取设定。对应 $table_list_ns
    // public $mc_table_alter_ns; // 表结构缓存操作类，通过__get()获取设定。对应 $table_alter_ns
    // public $mc_table_ns**; // 表中单记录缓存子空间操作类，通过__get()获取设定。自行设定使用
    // public $mc_table_list_ns**; // 表记录缓存子空间操作类，通过__get()获取设定。自行设定使用

    // 资源相关
    public $res_name = ''; // res_name ，在_model()中调用的名称
    public $res_type = NULL; // res_type,可以通过类名自动获取
    public $attribute = NULL; // 扩展属性表是否存在
    public $res_model = NULL; // 分类信息模型

    public $error = '';
    public $link = NULL;
    public $debug = NULL;

    // ModelRes 相关
    public $ModelRes = 0; // 在ModelRes类中设置为1

    // 连贯操作
    public $option = array(); // array('field', 'order', 'limit')

    // 内部勾子相关
    public $hookPreCall = FALSE;  // 是否执行前勾，需要在扩展类中定义，$this->hook_pre_call()
    public $hookBackCall = FALSE; // 是否执行后勾，需要在扩展类中定义，$this->hook_back_call()
    public $ModelHookPaus = 0; // 是否暂定执行 Model 类中的勾子，以免多次执行
    public $hookPassFun = array('fieldexists'=>1, 'pk'=>1, 'tableexists'=>1, 'describe'=>1); // 预定义，系统函数，或者可以添加不执行hook的函数
    public $hookFun = array('read'=>1, 'gettotal'=>1, 'getlist'=>1, 'getfields'=>1, 'create'=>1, 'update'=>1, 'delete'=>1); // 预定义，可以执行hook的函数

    /**
     * 构造函数
     * @param mixed $db      1、字符串：在config类中定义的；2、数据库操作对象；
     * @param object $mc_wr  缓存操作对象
     * @param stirng $table  操作的表名
     * @link
     */
    final public function __construct($db = NULL, $mc_wr = NULL, $table = NULL, $res_type = NULL)
    {
        // 内部勾子处理
        if (method_exists($this, 'hook_pre_call')) $this->hookPreCall = TRUE;
        if (method_exists($this, 'hook_back_call')) $this->hookBackCall = TRUE;

        if ($db) {
            if (is_string($db)) $this->db_op = $db;
            //else $this->db_master = $db;
        }
        if ($mc_wr) $this->mc_wr = $mc_wr;
        if ($table) $this->table = $table; // setTable() 后将变量转换到 $this->table_org 中
        if ($res_type) $this->res_type = $res_type;
        if (!isset($this->debug) || NULL === $this->debug) {
            if (defined('D_BUG')) $this->debug = D_BUG;
            else $this->debug = FALSE;
        }
        if (!isset($this->cache) || NULL === $this->cache) {
            if (defined('CACHE')) $this->cache = CACHE;
            else $this->cache = TRUE;
        }
    }

    /**
     * 初始化
     * 所有函数执行前必须调用此函数进行相关信息初始化工作
     * 尤其是扩展类的使用中要注意！
     * 在初始化之前、之后会调用扩展类中自身的函数初始化方法
     * 注意：扩展类中的方法，应先调用 此函数进行初始化
     * @return int 1
     */
    public function initialization()
    {
        if (!$this->link) {
            // 初始化之前执行
            if (method_exists($this, 'init')) $this->init($this);
            $this->setDB();
            $this->setTable();
            // 初始化之后执行
            if (method_exists($this, 'init_after')) $this->init_after($this);
            $this->link = 1;
        }

        return $this->link;
    }

    /**
     * lazy load 变量
     */
    public function __get($name)
    {
        // if (!$this->link) $this->initialization();

        if ('db_master' === $name) {
            $this->setDB();
            return $this->db_master;
        } elseif ('db' === $name) {
            return $this->db = $this->selectDB('m');
        } elseif ('db_slave' === $name) {
            return $this->db_slave = $this->selectDB('a');
        } elseif ('mc_table_alter_ns' === $name) {
            $this->mcs[$name] = clone $this->mc_wr;
            $this->mcs[$name]->NS($this->db_ns . $this->table_alter_ns, 1);
            return $this->mc_table_alter_ns = &$this->mcs[$name];
        } elseif ('mc_table_list_ns' === $name) {
            $this->mcs[$name]  = clone $this->mc_wr;
            $this->mcs[$name]->NS($this->db_ns . $this->table_list_ns);
            return $this->mc_table_list_ns = &$this->mcs[$name];
        } elseif ('mc_table_ns' === $name) {
            $this->mcs[$name] = clone $this->mc_wr;
            $this->mcs[$name]->NS($this->db_ns . $this->table_ns);
            return $this->mc_table_ns = &$this->mcs[$name];
        } elseif (0 === strpos($name, 'mc_table_list_ns')) {
            $this->mcs[$name] = clone $this->mc_wr;
            $this->mcs[$name]->NS($this->db_ns . $this->table_list_ns . substr($name, 16));
            return $this->$name = &$this->mcs[$name];
        }  elseif (0 === strpos($name, 'mc_table_ns')) {
            $this->mcs[$name] = clone $this->mc_wr;
            $this->mcs[$name]->NS($this->db_ns . $this->table_ns . substr($name, 11));
            return $this->$name = &$this->mcs[$name];
        } elseif ('tb' === $name) {
            // 本表结构
            $this->tb = $this->__call('describe');  // 调用 DBAbstract::describe()
            $this->tableExists = ($this->tb) ? 1 : 0;
            return $this->tb;
        } elseif ('table_pre' === $name) {
            $this->table_pre = Config::get('table_pre') . '';
            return $this->table_pre;
        } elseif ('tableExists' === $name) {
            // 本表结构
            if (isset($this->tb)) { $this->tableExists = ($this->tb) ? 1 : 0; }
            else $this->tableExists = $this->__call('tableExists');  // 调用 DBAbstract::describe()
            return $this->tableExists;
        } elseif ('add_time' === $name) {
            // add_time 字段是否有
            return $this->add_time = array_key_exists('add_time', $this->tb);
        } elseif ('an_ext' === $name) {
            // an_ext 字段是否有
            return $this->an_ext = array_key_exists('an_ext', $this->tb);
        }

        throw new AnException('Model Error!', "Model::__get() Error!'{$name}' is not a exist var.");
    }

    /*
     * 通过代理DB类来实现的方法
     * public function create();
     * public function update();
     * public function read();
     * public function delete();
     * public function getList();
     * public function getTotal();
     * public function getFields();
     * public function getRow();
     * public function getCol();
     * public function getOne();
     * public function query();
     * public function exec();
     * public function tableExists();
     * public function describe();
     * public function fieldExists();
     * public function pk();
     */
    final public function __call($name, $params = NULL)
    {
        if (!$this->link) $this->initialization();

        // 继承 db 类
        $name = strtolower($name);
        if (!method_exists($this->db_master, $name)) {
            throw new AnException('Model Error!', "Model::__CAll() Error!'{$name}' is Undefined Function.");
        }

        if ($params && !is_array($params)) {
            $params = array($params);
        }

        // 2014-12-30 anr,执行前的勾子，可以改变、监控任何……
        if ($this->hookPreCall && !$this->ModelHookPaus) {
//            $r = $this->hook_pre_call(&$name, &$params);
            $r = $this->hook_pre_call($name, $params);
            /*if (NULL !== $r) {
                return $r;
            }*/
        }

        // 处理要取的字段，read,getlist 补充参数
        if ($this->field && in_array($name, array('read', 'getlist'))) {
            $this->option['field'] = $this->field;
        }

        // 表名、表前缀处理
        if (in_array($name, array('read', 'gettotal', 'getlist', 'getfields', 'create', 'update', 'delete', 'fieldexists'))) {
            // 函数需要 table 参数
            array_unshift($params, $this->table);
        } elseif (in_array($name, array('pk', 'tableexists', 'describe'))) {
            if (!$params) $params = array($this->table);
            elseif ($this->table_pre && 0 !== strpos($params[0], $this->table_pre)) $params[0] = $this->table_pre . $params[0];
        } elseif ($this->table_pre) {
            // 对直接执行sql的表名添加前缀兼容处理
            // @note 不支持关联查询的表名替换，SQL不要出现与表名相同的字符，对于使用?传递参数，可以满足绝大多数表名替换
            if (false === strpos($params[0], $this->table)) {
                // 如果已经替换不再处理
                $params[0] = str_replace($this->table_org, $this->table, $params[0]);
            }
        }

        // 缓存与读写分离
        if ( in_array($name, array('read', 'gettotal', 'getlist', 'getfields'))
          || in_array($name, array('getrow', 'getall', 'getone', 'getcol'))
          ||(in_array($name, array('query', 'exec')) && strncasecmp($params[0], 'SELECT ', 7) === 0)) {
            if ($this->cache && is_object($this->mc_wr) && (!$this->no_cache_fun || !in_array($name, $this->no_cache_fun))) {
                if (('read' === $name || 'getlist' === $name) && $this->option) {
                    $mc_key = $this->table . $name . serialize($this->option);
                    $r = $this->mc_table_list_ns->call_user_func_array(array($this->db_slave, $name), $params, $mc_key, $this->lifetime);
                } else {
                    $r = $this->mc_table_list_ns->call_user_func_array(array($this->db_slave, $name), $params, NULL, $this->lifetime);
                }
                // 增加is_numeric限制，因mc_wr::call_user_func_array()锁定超时会返回AnEmptyVariable对象，需要转换为int(0)
                if ('gettotal' === $name && !is_numeric($r)) $r = 0;
            } else {
                $r = call_user_func_array(array($this->db_slave, $name), $params);
            }
        } elseif (in_array($name, array('pk', 'tableexists', 'describe', 'fieldexists'))) {
            if ($this->cache && is_object($this->mc_wr) && (!$this->no_cache_fun || !in_array($name, $this->no_cache_fun))) $r = $this->mc_table_alter_ns->call_user_func_array(array($this->db_slave, $name), $params, NULL, $this->lifetime);
            else $r = call_user_func_array(array($this->db_slave, $name), $params);
        } elseif (in_array($name, array('create', 'update', 'delete', 'query', 'exec'))) {
            // 不能缓存的操作
            if (('create' === $name && (empty($params[2]) || (false === stripos($params[2], 'UPDATE ')))) && empty($params[1]['add_time']) && $this->add_time) {
                // 补充 add_time 字段，只在执行单纯的创建一个记录的时候补充，UPDATE时不更新,REPLACE&INSERT时更新
                $params[1]['add_time'] = date('Y-m-d H:i:s');
            }
            $r = call_user_func_array(array($this->db, $name), $params);

            if ($this->cache && is_object($this->mc_wr)) {
                // 清空_uri缓存
                AnPHP::$uri = array();
                // 清除表的命名空间
                $this->mc_table_list_ns->deleteNS();
                // 清除总表的命名空间
                if ($this->mc_table_list_ns !== $this->mcs['mc_table_list_ns']) {
                    $this->mcs['mc_table_list_ns']->deleteNS();
                }
                if (in_array($name, array('query', 'exec')) && 0 === strncasecmp($params[0], 'ALTER ', 6)) {
                    $this->mc_table_alter_ns->deleteNS();
                }
                // 2014-12-30 anr,add 如果有更新操作，强制在主库执行后续操作，以避免因同步延迟导致的脏数据
                $this->db_select = 'm';
                $this->db_slave = $this->db;
            }
        } elseif ('begintransaction' === $name) {
            // 对事务主持，事务：强行指定在主库上执行，不支持多表
            $this->db_select = 'm';
            // 2014-12-30 anr,add 强制在主库执行后续操作
            $this->db_slave = $this->db;
            $this->cache = 0;
            $r = call_user_func_array(array($this->db, $name), $params);
        } elseif ('commit' === $name) {
            // 对事务支持，事务：强行指定在主库上执行，不支持多表
            if (is_object($this->mc_wr)) {
                AnPHP::$uri = array();
                $this->mc_table_list_ns->deleteNS();
            }
            $r = call_user_func_array(array($this->db, $name), $params);
        } else {
            $r = call_user_func_array(array($this->db, $name), $params);
        }

        $this->option = array();
        // 2014-12-30 anr,执行后的勾子，可以改变、监控任何……
        if ($this->hookBackCall && !$this->ModelHookPaus) {
            //$this->hook_back_call(&$name, &$params, &$r);
            $this->hook_back_call($name, $params, $r);
            // AnModelHook::check(&$name, &$params, &$r);
            // AnModelHook::backCall(&$name, &$params, &$r);
        }
        return $r;
    }

    public function __toString()
    {
        return $this->getModelName();
    }

    public function __wakeup()
    {
        if (!isset($this->debug) || NULL === $this->debug) {
            if (defined('D_BUG')) $this->debug = D_BUG;
            else $this->debug = FALSE;
        }
        if (!isset($this->cache) || NULL === $this->cache) {
            if (defined('CACHE')) $this->cache = CACHE;
            else $this->cache = TRUE;
        }
    }

//////////////////////////////////////////////////////
// 类设置方法
//////////////////////////////////////////////////////
    /**
     * 设置数据库相关属性
     * 取值顺序：1、传递过来的，2、db_master对象
     * @param  mixed $db, 可是数据库对象 ，可以是数据连配置数组，也可以是字符串即通过Config::get('db')方式获取配置，不提倡使用数组
     * @return true
     */
    public function setDB($db = NULL)
    {
        if (!empty($this->db_master) && is_object($this->db_master) && (NULL === $db || $this->db_op === $db)) {
            return true;
        }

        if ($db) {
            // 从配置中读
            $this->db_master = clone DB::connect($db);
            $this->db_op = $db;
        } elseif ($this->db_op) {
            $this->db_master = clone DB::connect($this->db_op);
        }

        if (empty($this->db_master) || !is_object($this->db_master)) {
            throw new AnException('Model Error!', 'Model::setDB() Error!db is empty.');
        }

        // 设置连贯操作参数
        $this->db_master->option = &$this->option;

        // 将已经设置好的对象清除
        if (isset($this->db)) unset($this->db);
        if (isset($this->db_slave)) unset($this->db_slave);
        // 清除关联缓存对象
        if (isset($this->mc_table_alter_ns)) unset($this->mc_table_alter_ns);
        if (isset($this->mc_table_list_ns)) unset($this->mc_table_list_ns);
        if (isset($this->mc_table_ns)) unset($this->mc_table_ns);

        // 设置缓存
        $this->db_ns = $this->db_master->db_host . '::' . $this->db_master->db_name . '-';

        return true;
    }

    /**
     * 设置表
     * @param string $table_name 表名
     * @param int $initialization 是否是
     * @return bool
     */
    public function setTable($table_name = NULL)
    {
        // 判断是否重复设置
        if ($this->table_org && (NULL === $table_name || $this->table_org === $table_name)) {
            return true;
        }

        // 清除关联缓存对象
        if (isset($this->mc_table_alter_ns)) unset($this->mc_table_alter_ns);
        if (isset($this->mc_table_list_ns)) unset($this->mc_table_list_ns);
        if (isset($this->mc_table_ns)) unset($this->mc_table_ns);
        // 清除相关属性
        if ($this->link) {
            // 清除表相关属性
            if (isset($this->tb)) unset($this->tb);
            if (isset($this->tableExists)) unset($this->tableExists);
            if (isset($this->pk)) $this->pk = NULL;
            if (isset($this->res_model)) $this->res_model = NULL;
        }
        // $this->link = 1;

        if ($table_name) {
            $this->table_org = $table_name;
        } else {
            if ($this->table) $this->table_org = $this->table;
            else $this->table_org = $this->getModelName();
        }

        if ('table' === $this->table_org) {
            // 特殊名处理 _model('table')
            $this->table_list_ns = $this->table_ns = $this->table_alter_ns = 'table_alter_ns';
            return true;
        }

        // 合法的表名验证
        if (!DB::legalName($this->table_org)) {
            throw new AnException('Model Error!', "Model::setTable() Error!Table name '{$this->table_org}' is invalid!");
        }

        // 添加表前缀
        $this->table_pre = Config::get('table_pre') . '';
        $this->table = $this->table_pre . $this->table_org;

        // 缓存使用的命名空间
        $this->table_ns = $this->table;
        $this->table_list_ns = $this->table . '_list_ns';
        $this->table_alter_ns = 'table_alter_ns';

        if ($this->ModelRes) {
            //$this->setDB();
            if (!$this->pk) {
                $this->pk = $this->db_master->checkPK($this->tb);
            }
            if (!$this->pk) {
                throw new AnException('Model Error!', 'ModelRes::setTable() Error!Empty PK.');
            }
            $this->db_master->pk = &$this->pk;

            // 模型相关
            if ($this->res_type) {
                static $attribute = array();
                // $this->attribute 扩展属性表是否存在
                if (!isset($attribute[$this->db_ns])) $attribute[$this->db_ns] = $this->__call('tableExists', array('attribute'));
                $this->attribute = $attribute[$this->db_ns];
                if ($this->attribute) $this->res_model = _model('attribute_relation')->get_list($this->res_type);
            }
        }

        return true;
    }

    /**
     * 选择数据库对象
     * @param string $select
     *                     为NULL根据对象db_select属性确定；
     *                     为'a'自动选择从库，如果没有从库返回主库；
     *                     为'm' 强制选择主库；
     *                     为's'强制选择从库，如果从库不存在报错。
     * @return object
     */
    public function selectDB($select = NULL)
    {
        if (NULL === $select) $select = $this->db_select;

        // 主数据库服务器存活检查
        if (!isset($this->db) || NULL===$this->db) {
            if ($this->db_master->db_hot_open && !DB::getState($this->db_master->db_host)) {
                // 切换到热备库
                $this->db_master = DB::connect($this->db_master->db_hot_conf);
                $this->db_op = $this->db_master->db_hot_conf;
                if($this->debug) {
                    AnDebug::$op[] = array('type' => 'db', 'info' => array(
                         'sql'      => $this->getPClass() . "->USE {$this->db_op} > db_host[{$select}]",
                         'sql_info' => '',
                         'sql_real' => '',
                         'time'     => 0,'info'     => 0,'explain'  => 0,
                         'db'       => $this->db->db_host.' > '.$this->db->db_name.' > '.$this->table,
                    ));
                }
                if (!DB::getState($this->db_master->db_host)) throw new AnException('DB Error.', 'Master::selectDB() Error! DB and Hot DB is died.');
            } else {
                if (!DB::getState($this->db_master->db_host)) throw new AnException('DB Error.', 'Master::selectDB() Error! DB is died.');
            }

            $this->db = $this->db_master;
            if($this->debug) {
                AnDebug::$op[] = array('type' => 'db', 'info' => array(
                     'sql'      => $this->getPClass() . "->USE {$this->db_op} > db_master",
                     'sql_info' => '',
                     'sql_real' => '',
                     'time'     => 0,'info'     => 0,'explain'  => 0,
                     'db'       => $this->db->db_host.' > '.$this->db->db_name.' > '.$this->table,
                ));
            }
        }

        // 选择主库
        if ('m' === $select) return $this->db;

        // 以下为从库选择
        if (!$this->db->db_slaves || !is_array($this->db->db_slaves)) {
            if ('a' === $select) {
                // 从库不存在 ，自动选择返回主库
                return $this->db;
            } else {
                // 从库不存在 ，强制返回从库，报错
                throw new AnException('DB Error.', 'Model::selectDB() Error!db_slaves is empty.');
            }
        } elseif (!isset($this->db_slave)) {
            // 从库状态检查
            $select = array_rand($this->db->db_slaves);
            $this->db_slave = $this->db->db_slaves[$select];
            $this->db_slave->option = &$this->option;
            if ($this->ModelRes) $this->db_slave->pk = &$this->pk;

            if ($this->debug) {
                $g['sql'][] = array(
                     'sql'      => "USE {$this->db_op} > db_slaves[{$select}]",
                     'time'     => 0,'info'     => 0,'explain'  => 0,
                     'db'       => $this->db_slave->db_host.' > '.$this->db_slave->db_name.' > '.$this->table,
                );
            }

            return $this->db_slave;
        }
    }

    /**
     * 清空本资源所生成的缓存
     */
    public function mc_delete_ns()
    {
        if (!$this->link) $this->initialization();

        if (!is_object($this->mc_wr)) return false;

        $this->mc_table_ns->deleteNS(); // 清除所有结果,res_type为所同类型ID的命名空间
        unset($this->mc_table_ns);
        $this->mc_table_list_ns->deleteNS(); // 清除所有列表
        unset($this->mc_table_list_ns);
        $this->mc_table_alter_ns->deleteNS(); // 清除表
        unset($this->mc_table_alter_ns);

        return true;
    }

    /**
     * 返回去除_model类的名称
     * 也就是Model默认的表名
     */
    public function getModelName()
    {
        // user=getModelName(user_model)
        return substr(get_class($this), 0, strrpos(get_class($this), '_'));
    }

    /**
     * 返回当前对象的父类和当前类名
     * 主要用于 debug 信息
     * @return string
     */
    public function getPClass()
    {
        $p_class = get_parent_class($this);
        if ($p_class) {
            return $p_class.':'.get_class($this);
        } else {
            return get_class($this);
        }
    }

    /**
     * 预制分库分表的参数
     * @param string $res_name _model($res_name) 参数
     * @param string $splitKey 分库分表的 key 值，用resource表设置的分库分表规则根据 key 值进行操作
     */
    public static function preSplitKey($res_name, $splitKey)
    {
        Model::$preSplitKey[$res_name] = $splitKey;
    }

    // 连贯操作
    /**
     * 设置要读取的字段，连贯操作。针对于read(),getList有效
     * 连贯操作
     * @example _model('user')->field('name')->read(100);
     * @example _model('user')->field('name', 'gender', 'mail')->read(100);
     * @example _model('user')->field('name', 'gender', 'mail')->getList(array('id <'=>100));
     */
    public function field()
    {
        $this->option['field'] = func_get_args();
        return $this;
    }

    /**
     * 设置order排序
     * 连贯操作
     * @example _model('user')->order('id')->getList(array('id <'=>100));
     * @example _model('user')->order('id DESC')->getList(array('id <'=>100));
     */
    public function order()
    {
        $this->option['order'] = func_get_args();
        return $this;
    }

    /**
     * 设置limit分页
     * 连贯操作
     * @example _model('user')->limit(1)->getList(array('id <'=>100));
     * @example _model('user')->limit(1, 10)->getList(array('id <'=>100));
     */
    public function limit()
    {
        $this->option['limit'] = func_get_args();
        return $this;
    }

    /**
     * 对 an_ext 字段进行处理
     * @param $info 要处理的数组
     * @param $mode 进行怎么的处理，1为压缩 2为解压
     * @return bool true
     */
    public function treatAn_ext(&$info, $mode = 1)
    {
        if (!$mode || !$this->an_ext || !is_array($info) || empty($info['an_ext'])) return true;
        if (2 === $mode && is_string($info['an_ext'])) $info['an_ext'] = unserialize($info['an_ext']);
        if (1 === $mode &&  is_array($info['an_ext'])) $info['an_ext'] = serialize($info['an_ext']);
        return true;
    }

    /**
     * 是否能使用缓存
     * @param string $name 函数名
     * @return boolean
     */
    protected function cacheCan($name = '')
    {
        return ($this->cache && is_object($this->mc_wr) && (!$name || !$this->no_cache_fun || !in_array($name, $this->no_cache_fun)));
    }

    /**
     * 检查是否可以使用勾子
     * 返回 FALSE 意味着是通过性函数，正常返回 TRUE
     * @param string $name 函数名
     * @throws AnException
     * @return boolean
     */
    public function hookCheck($name)
    {
        // 1、过滤，不能执行勾子的函数
        if (array_key_exists($name, $this->hookPassFun)) {
            return FALSE;
        }

        // 2、通过，支持的操作
        if (array_key_exists($name, $this->hookFun)) {
            return TRUE;
        }

        // 不支持的操作报错
        throw new AnException('Not support function:' . $name . '()');
        return FALSE;
    }
}

?>