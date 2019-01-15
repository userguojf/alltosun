<?php

/**
 * alltosun.com ModelRes，资源基础类 ModelRes.php
 * ============================================================================
 * 版权所有 (C) 2007-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 安然 (anr@alltosun.com) $
 * $Date: 2010-01-25 14:33:57 +0800 $
 * $Id: ModelRes.php 1096 2015-12-03 10:11:58Z liudh $
 * @link http://wiki.alltosun.com/index.php?title=Framework:ModelRes.php
 * @see http://wiki.alltosun.com/index.php?title=Model%26ModelRes
*/

/**
 * ModelRes 资源操作基础类
 * 由Model类扩展，2011年7月4日
 * 合并主表+从表操作，将多条操作分拆成单条SQL操作
 * 将表的操作转换成类的操作、数组的操作
 *
 * 使用要求：
 * 表中必须有一个且只能有一个PRIMARY KEY字段
 *
 * 满足优先使用本类，不满足使用 Model类。
 *
 * 注意：
 * 1、attribute_relation 模型的关系必须要主库
 * 2、表的前缀只支持对本表操作
 * @author anr@alltosun.com
 * @package AnModel
 * @link http://wiki.alltosun.com/index.php?title=Framework:ModelRes.php
 * @example 使用示例: http://mantis.alltosun.com/view.php?id=1785#bugnotes
 * ModelRes派生类product_mode，使用var_dump打印出来的数据
 * http://mantis.alltosun.com/view.php?id=1677
 */

class ModelRes extends Model
{
    public $ModelRes = 1; // 在ModelRes类中设置为1
    public $ModelHookPaus = 1; // 暂停 model 中的勾子执行以免多次执行

//////////////////////////////////////////////////////
// 重载 Model 类中的方法
//////////////////////////////////////////////////////

    public function _setTable($res_type = NULL, $initialization = 0)
    {
        parent::setTable($res_type);
        // $this->setDB();
        $this->link = 1;
        if (!$this->pk) {
            // 从配置中取 pk // Config::Set('pk', array('table_name' => 'pk_name'))
            /*($conf_pk = Config::get('pk') && !empty($conf_pk[$this->table_org]) && $this->pk = $conf_pk[$this->table_org]);
            if (!$this->pk) $this->pk = $this->db_master->checkPK($this->tb);*/
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

        return true;
    }

//////////////////////////////////////////////////////
// 重载 DB 类中的方法
//////////////////////////////////////////////////////

    /**
     * 写入1条记录
     * @param array $info 更新的内容
     * @param string $app  REPLACE ，或 ON DUPLICATE UPDATE……
     * @return intval 新插入的id
     * @link http://wiki.alltosun.com/index.php?title=Model%26ModelRes#creat.28.29
     * @example _model('product')->create(array('id'=>'1000', 'name'=>'test', 'user_id'=>100), 'ON DUPLICATE KEY UPDATE user_id=user_id+1');
     * @example _model('product')->create(array('id'=>'1000', 'name'=>'test', 'user_id'=>100), 'UPDATE user_id=user_id+1');
     * @example _model('product')->create(array('id'=>'1000', 'name'=>'test', 'user_id'=>100), 'REPLACE');
     */
    public function create($info, $app = NULL)
    {
        if (!$this->link) $this->initialization();

        if (!$info) {
            throw new AnException('Model Error!', 'ModelRes::create() Error!$info is empty.');
        }

        // 清除缓存
        if (isset($info[$this->pk]) && is_object($this->mc_wr)) {
            // 将变化的记录缓存清空
            $this->mc_table_ns->delete($this->table . '-' . $info[$this->pk]);
        }

        // 观察者
        ModelResObserver::notify($this->res_name, 'createBEF', array($this->res_name, 0, &$info));

        // 2015-01-29 anr,执行前的勾子，可以改变、监控任何……
        if ($this->hookPreCall) {
            $r = $this->hook_pre_call('create', array(&$info, &$app));
        }

        // 分拆主表、扩展属性表更新内容
        $main = array();
        $main_value = array();

        // 根据主结构进行主表与扩展属性表数据分离
        // 2012-09-14 添加如果res_model和tb不存在的时候
        if (!$this->res_type || !$this->attribute || !$this->res_model || !$this->tb) {
            $main = $info;
        } else {
            if (isset($info['res_type'])) $res_type = $info['res_type'];
            else $res_type = $this->res_type;
            if (!$res_type) $res_model = array();
            elseif ($res_type === $this->res_type) $res_model = $this->res_model;
            else $res_model = _model('attribute_relation')->get_list($res_type);
            foreach ($info as $k => $v) {
                if (array_key_exists($k, $this->tb)) {
                    // 属于主表
                    $main[$k] = $v;
                } elseif (false !== in_array($k, $res_model)) {
                    // 属于从表
                    $main_value[$k] = $v;
                } else {
                    throw new AnException('Model Error!', "ModelRes::creat() Error!Unknown column '{$k}' in {$this->table} field list.");
                }
            }
        }

        // an_ext 处理
        $this->treatAn_ext($main);

        // 补充 res_type
        if (!isset($main['res_type']) && $this->res_type && $this->attribute && $this->res_typeExists()) {
            $main['res_type'] = $this->res_type;
        }

        // 向主表中写入
        $id = $this->__call('create', array($main, $app)); // 调用 DBAbstract::create()
        // 向扩展属性表里插入
        if ($id && is_array($main_value) && $main_value) {
            // 补充 res_type
            if (!isset($info['res_type'])) $info['res_type'] = $this->res_type;
            _model('attribute_value', $this->db_op)->replace_array(array('res_id' => $id, 'res_type' => $info['res_type']), $main_value);
        }

        if ($id) {
            // 如果是UPDATE或REPLACE操作，要清除缓存
            if ($app) $this->updateID($id);
            ModelResObserver::notify($this->res_name, 'create', array($this->res_name, $id, &$info));
        }

        // 2015-01-29 anr,执行后的勾子，可以改变、监控任何……
        if ($this->hookBackCall && !$this->ModelResHookPaus) {
            //$r = $this->hook_back_call('create', array(&$info, &$app), &$id);
            $r = $this->hook_back_call('create', array(&$info, &$app), $id);
        }

        return $id;
    }

    /**
     * 更新表记录
     * @param mixed $filter WHERE条件，可以是数组，也可以是数值
     * @param array $info 更新的内容,可以是数组，也可以是字串。如果是字串，将作为SQL语句来执行
     * @return int 更新的记录数
     * @link http://wiki.alltosun.com/index.php?title=Model%26ModelRes#update.28.29
     * @example update(123123, $info);
     * @example update(123123, $info);
     * @example update(array('id'=>123123), $info);
     * @example update(array('id'=>300, 'res_type'=>8), $info);
     * @example update(123123, "SET num=num+1");
     */
    public function update($filter, $info)
    {
        if (!$this->link) $this->initialization();

        if (!$info) {
            throw new AnException('Model Error!', 'ModelRes::update() Error!$info is empty.');
        }

        // 2015-01-29 anr,执行后的勾子，可以改变、监控任何……
        if ($this->hookPreCall && !$this->ModelResHookPaus) {
            $r = $this->hook_pre_call('update', array(&$filter, &$info));
        }

        // 清除缓存
        if ($this->cacheCan('update')) {
        // if ($this->cache && is_object($this->mc_wr)) {
            AnPHP::$uri = array();
            $this->mc_table_list_ns->deleteNS();
            // 清除总表的命名空间
            if ($this->mc_table_list_ns !== $this->mcs['mc_table_list_ns']) {
                $this->mcs['mc_table_list_ns']->deleteNS();
            }
        }

        // 对 an_ext 字段处理
        $this->treatAn_ext($info);

        $id = $this->returnId($filter);
        if ($id) {
            // if (is_array($filter) && count($filter) == 2) return $this->updateID($filter, $info);
            //else return $this->updateID($id, $info);
            return $this->updateID($filter, $info);
        }

        // 如没 $this->res_type $this->attribute 观察者 可以直接操作
        if (!$this->cache && (!$this->res_type || !$this->res_model) && !ModelResObserver::isAttach($this->res_name, 'update') && !ModelResObserver::isAttach($this->res_name, 'updateBEF')) {
            return $this->db->update($this->table, $filter, $info);
        }

        // 2、找到更新的记录，更新
        // 查找相关id
        $tem_arr = array();
        // 查找改变记录对应的 res_type
        if ($this->res_type && $this->attribute && $this->res_typeExists()) {
            // 结果可能有多种 res_type
            $tem_arr = $this->db->getAll("SELECT `{$this->pk}`,`res_type` FROM `{$this->table}`" . $this->db_master->arrayToWhere($filter), $filter);
        } else {
            // 就1种 res_type
            $tem_arr = $this->db->getAll("SELECT `{$this->pk}` FROM `{$this->table}`" . $this->db_master->arrayToWhere($filter), $filter);
        }

        foreach ($tem_arr as $v) {
            if (!empty($v['res_type'])) {
                $this->updateID(array($this->pk => $v[$this->pk], 'res_type' => $v['res_type']), $info);
            } else {
                $this->updateID($v[$this->pk], $info);
            }
        }

        $r = count($tem_arr);

        // 2015-01-29 anr,执行后的勾子，可以改变、监控任何……
        if ($this->hookBackCall) {
            //$r = $this->hook_back_call('update', array(&$filter, &$info), &$r);
            $r = $this->hook_back_call('update', array(&$filter, &$info), $r);
        }

        return $r;
    }

    /**
     * 读取1条记录，支持条件
     * 从主表、扩展属性表取记录，合并成1维数组返回
     * @param mixed $filter WHERE条件
     * @param string $half_sql 排序条件 ORDER BY `add_time` DESC
     * @return array 1维数组，如果不存在返回 array()
     * @link http://wiki.alltosun.com/index.php?title=Model%26ModelRes#read.28.29
     */
    public function read($filter, $half_sql = '')
    {
        if (!$this->link) $this->initialization();

        if (!$filter && !$this->option) {
            throw new AnException('Model Error!', 'ModelRes::read() Error!$filter is empty.');
        }

        // 2015-01-29 anr,执行后的勾子，可以改变、监控任何……
        if ($this->hookPreCall) {
            $r = $this->hook_pre_call('read', array(&$filter, &$half_sql));
        }

        if (!$half_sql && !$this->option) {
            // ID操作，条件：没有附加sql、关联操作时
            $id = $this->returnId($filter);
            if ($id) return $this->readID($id);
        }

        // 以下处理带条件的情况 //
        // 查询缓存,使用命名空间
        $sql = 'SELECT ' . $this->db_master->treatField() . " FROM `{$this->table}`" . $this->db_master->arrayToWhere($filter) . $this->db_master->treatOrder() . $this->db_master->treatHalfSql($half_sql);
        $mc_key = '';
        if ($this->cacheCan('read')) {
            $mc_key = $sql . serialize($filter);
            $info = $this->mc_table_list_ns->get($mc_key);
            if (FALSE !== $info) {
                if (!empty($info[$this->pk]) && ModelResObserver::isAttach($this->res_name, 'read')) return $this->readID($info, 0); // 只处理观察者
                else return $info;
            }
        }

        // 取主表内容
        $info = $this->__call('getRow', array($sql, $filter));

        // 观察者、扩展属性
        if ($info) {
            // 处理an_ext字段
            $this->treatAn_ext($info, 2);

            if (!$this->res_type || !$this->attribute || !$this->res_model) {
                // 不用处理扩展属性
                if (!empty($info[$this->pk]) && ModelResObserver::isAttach($this->res_name, 'read')) {
                    $info = $this->readID($info, 0); // 只处理观察者
                }
            } else {
                $info = $this->readID($info);
            }
        }

        if ($mc_key) {
            $this->mc_table_list_ns->set($mc_key, $info, $this->lifetime);
        }

        // 2015-01-29 anr,执行后的勾子，可以改变、监控任何……
        if ($this->hookBackCall) {
            //$r = $this->hook_back_call('read', array(&$filter, &$half_sql), &$info);
            $r = $this->hook_back_call('read', array(&$filter, &$half_sql), $info);
        }

        return $info;
    }

    /**
     * 删除表记录
     * @param mixed $filter WHERE条件
     * @return int 删除的记录数
     * @link http://wiki.alltosun.com/index.php?title=Model%26ModelRes#delete.28.29
     */
    public function delete($filter)
    {
        if (!$this->link) $this->initialization();

        // 2015-01-29 anr,执行后的勾子，可以改变、监控任何……
        if ($this->hookPreCall) {
            $r = $this->hook_pre_call('delete', array(&$filter));
        }

        // 清除缓存
        if ($this->cacheCan('delete')) {
            AnPHP::$uri = array();
            $this->mc_table_list_ns->deleteNS();
            // 清除总表的命名空间
            if ($this->mc_table_list_ns !== $this->mcs['mc_table_list_ns']) {
                $this->mcs['mc_table_list_ns']->deleteNS();
            }
        }

        if ($this->returnId($filter)) {
            return $this->deleteId($filter);
        }

        // 可以直接操作
        if (!$this->cache && !$this->res_model && !ModelResObserver::isAttach($this->res_name, 'delete')) {
            return $this->db->delete($this->table, $filter);
        }

        // 找到变化的记录，清除之
        if ($this->res_type && $this->attribute && $this->res_typeExists()) {
            // 结果可能有多种 res_type
            $info_list = $this->db->getAll("SELECT `{$this->pk}`,`res_type` FROM `{$this->table}`" . $this->db_master->arrayToWhere($filter), $filter);
        } else {
            // 表就1种 res_type
            $info_list = $this->db->getAll("SELECT `{$this->pk}` FROM `{$this->table}`" . $this->db_master->arrayToWhere($filter), $filter);
        }

        foreach ($info_list as $v) {
            if (isset($v['res_type'])) {
                $this->deleteId(array($this->pk => $v[$this->pk], 'res_type' => $v['res_type']));
            } else {
                $this->deleteId($v[$this->pk]);
            }
        }

        $r = count($info_list);
        // 2015-01-29 anr,执行后的勾子，可以改变、监控任何……
        if ($this->hookBackCall) {
            //$r = $this->hook_back_call('delete', array(&$filter), &$r);
            $r = $this->hook_back_call('delete', array(&$filter), $r);
        }
        return $r;
    }

    /**
     * 读取指定资源列表（条件只能用在主表上）
     * @param string $filter 条件数组或字符串，可为空
     * @param string $half_sql SQL中的limit语句，可添加order by
     * @return array
     * @link http://wiki.alltosun.com/index.php?title=Model%26ModelRes#getList.28.29
     */
    public function getList($filter = array(), $half_sql = '')
    {
        if (!$this->link) $this->initialization();

        // 2015-01-29 anr,执行后的勾子，可以改变、监控任何……
        if ($this->hookPreCall) {
            $r = $this->hook_pre_call('getList', array(&$filter, &$half_sql));
        }

        if (!$half_sql && empty($this->option) && $this->cacheCan('getList')) {
            $ids_arr = $this->IDS($filter);
            if ($ids_arr) return $this->readIDSS($ids_arr); // 返回 key => val
        }

        if (!$filter && !$this->option && (!$half_sql || stripos($half_sql, 'LIMIT') === false)) {
            throw new AnException('Model Error!', 'ModelRes::getList() Error!$filter is empty.');
        }
        // @TODO 支持扩展属性条件
        // $sql = "SELECT * FROM `{$this->table}` {$where} {$half_sql}";
        $sql = 'SELECT' . $this->db_master->treatField() . " FROM `{$this->table}`" . $this->db_master->arrayToWhere($filter) . $this->db_master->treatOrder() . $this->db_master->treatLimit() . $this->db_master->treatHalfSql($half_sql);

        $r = $this->getAll($sql, $filter);
        // 2015-01-29 anr,执行后的勾子，可以改变、监控任何……
        if ($this->hookBackCall) {
            //$r = $this->hook_back_call('getList', array(&$filter, &$half_sql), &$r);
            $r = $this->hook_back_call('getList', array(&$filter, &$half_sql), $r);
        }
        return $r;
    }

    /**
     * SQL执行结果对应的第1条记录，条件只作用于主表
     * 使用方法同 db->getRow() 的执行结果
     * @return array() 1维数组，返回sql结果的记录
     * @example $this->getRow($sql);
     * @example $this->getRow($sql, $param);
     * @example $this->getRow($sql, array_values($params));
     */
    public function getRow()
    {
        if (!$this->link) $this->initialization();

        $params = func_get_args();
        $info = $this->__call('getRow', $params); // 调用 Model::getRow()

        if ($info) {
            if (isset($info[$this->pk])) return $this->readID($info);
            else return $info;
        }
        else return array();
    }

    /**
     * SQL 执行结果所有记录集。不论SQL设定字段，返回值包含都是所有字段
     * 使用方法同 db->getAll()
     * 支持 multiget
     * @return array() 2维数组，所有记录集，其中key为记录的id
     * @example $this->getAll($half_sql);
     * @example $this->getAll($half_sql, $param);
     * @example $this->getAll($half_sql, array_values($params));
     */
    public function getAll()
    {
        if (!$this->link) $this->initialization();

        $params = func_get_args();
        $info_list = $this->__call('getAll', $params); // 调用 Model::getAll()

        if (!$info_list) return array();

        // an_ext 处理
        if ($this->an_ext && isset($info_list[0]['an_ext'])) {
            foreach ($info_list as &$v) {
                if ($v['an_ext']) $v['an_ext'] = unserialize($v['an_ext']);
            }
        }

        if ((!$this->res_type || !$this->attribute || !$this->res_model) && !ModelResObserver::isAttach($this->res_name, 'read')) {
            return $info_list;
        }

        // 只有使用 SELECT * FROM…… 语句才返回扩展属性， * 位置不能在 10个字符 以后
        $tem_find = strpos($params[0], '*');
        if ((!$tem_find || $tem_find > 10) && !ModelResObserver::isAttach($this->res_name, 'read')) return $info_list;
        else return $this->readIDSS($info_list);
    }

//////////////////////////////////////////
// ModelRes 使用的函数
//////////////////////////////////////////
    /**
     * 读取系列 IDS 数据
     * @param  mixed $filter 条件数组或字符串，为空返回空数组array()
     * @return array 成功返回2维数组，否则空数组
     * @example _model('article')->readIDSS(20);
     * @example _model('article')->readIDSS("2,3,4,10,20");
     * @example _model('article')->readIDSS(array(2,3,4,10,20));
     * @example _model('article')->readIDSS(array('id'=>array(2,3,4,10,20)));
     */
    private function readIDSS($filter)
    {
        if (!$this->link) $this->initialization();

        $tem = null;

        $ids_arr = array();
        if ($filter && is_string($filter) && is_numeric($filter[0])) {
            // 2、'1,2,3,4,5'
            $ids_arr = explode(',', $filter);
        } elseif (is_numeric($filter)) {
            // 3、如果只是1个id
            $tem = $this->readID($filter);
            if ($tem) {
                // 转成二维数组
                return array($tem);
            } else {
                return array();
            }
        } elseif (is_array($filter)) {
            // 4、如果是1维数组，array(1,2,3,4);
            // 5、如果是2维数组
            $ids_arr = $filter;
        } else {
            // 参数异常
            throw new AnException('Model Error!', 'ModelRes::readIDSS() Error!$filter error.');
        }

        // 观察者是否存在
        $ModelResObserver = ModelResObserver::isAttach($this->res_name, 'read');

        // 从缓存中取数据
        if ($this->cacheCan('getList')) {
            $mc_keys = array(); // 缓存 keys
            $tem_arr = array(); // 从缓存中取出的数据
            // 生成缓存对应的key
            foreach ($ids_arr as $k => $v) {
                if (is_array($v)) {
                    $mc_keys[$v[$this->pk]] = $this->table . '-' . $v[$this->pk];
                } else {
                    $mc_keys[$v] = $this->table . '-' . $v;
                }
            }
            // 从缓存中取数据
            $tem_arr = $this->mc_table_ns->get($mc_keys);
            // 转换顺序，补取（扩展属性），转化
            $tem = array();

            foreach ($mc_keys as $k => $v) {
                if (isset($tem_arr[$v])) {
                    $tem_info = $tem_arr[$v];
                    if ($ModelResObserver && $tem_info) $tem_info = $this->readID($tem_info, 0); // 0 只处理观察者
                } else {
                    $tem_info = $this->readID($k);
                }
                // 空数组过滤 readID() 自动缓存的空数组，无数据
                if ($tem_info) $tem[] = $tem_info;
            }

            return $tem;
        }

        // 对传递过来的2维数据进行处理
        if ($ModelResObserver || $this->res_model) {
            foreach ($ids_arr as $k => $v) {
                if ($this->res_model) $ids_arr[$k] = $this->readID($v);
                else $ids_arr[$k] = $this->readID($v, 0); // 只处理观察者
            }
            return $ids_arr;
        }

        return $tem;
    }

    /**
     * 更新指定id的记录
     * 直接使用比较少
     * @param $filter 条件，可以是数组，也可以是数值
     *  array('id'=>300, 'res_type'=>8)或array('id'=>300)或300
     * @param $info 更新内容。可以是数组，也可以是sql语句。如果为空，只清除指定条件的缓存
     * @return bool 执行的结果
     * @example updateID(123123, $info);
     * @example updateID(123123, 'SET num=num+1');
     * @example updateID(array('id'=>123123), $info);
     * @example updateID(array('id'=>300, 'res_type'=>8), $info);
     */
    private function updateID($filter, $info = array())
    {
        if (!$this->link) $this->initialization();

        $id = $this->returnId($filter);
        if (!$id) {
            throw new AnException('Model Error!', 'ModelRes::updateID() Error!id is empty');
        }

        // 激活观察者：更新操作前的观察者
        ModelResObserver::notify($this->res_name, 'updateBEF', array($this->res_name, $id, &$info));

        if ($this->cacheCan('update')) {
        // if (is_object($this->mc_wr)) {
            // 将变化的记录缓存清空
            $this->mc_table_ns->delete($this->table . '-' . $id);
        }

        // 更新内容为空，清除缓存
        if (!$info) return true;

        // 更新的语句如SET num=num+1，则直接调用DB里的update处理
        if (!is_array($info)) {
            return $this->db->update($this->table, array($this->pk=>$id), $info); // 调用 DBAbstract::update()
        }

        $main = array(); // 主表内容
        $main_value = array(); // 扩展属性内容
        // 分拆主表、扩展属性表更新内容
        // 2012-09-14 添加如果res_model和tb不存在的时候
        if (!$this->res_type || !$this->attribute || !$this->res_model || !$this->tb) {
            $main = $info;
        } else {
            if ($this->res_typeExists()) {
                if (is_array($filter) && isset($filter['res_type'])) $res_type = $filter['res_type'];
                else $res_type = $this->db->getOne("SELECT `res_type` FROM `{$this->table}` WHERE `{$this->pk}`=?", $id);
            } else {
                $res_type = $this->res_type;
            }
            if (!$res_type) $res_model = array();
            elseif ($res_type === $this->res_type) $res_model = $this->res_model;
            else $res_model = _model('attribute_relation')->get_list($res_type);

            foreach ($info as $k => $v) {
                if (array_key_exists($k, $this->tb)) {
                    $main[$k] = $v;
                } elseif (false !== in_array($k, $res_model)) {
                    // 属于从表
                    $main_value[$k] = $v;
                } else {
                    throw new AnException('Model Error!', "ModelRes::updateID() Error!Unknown column '{$k}' in {$this->table} field list.");
                }
            }
        }

        // 更新扩展属性
        if ($main_value) {
            _model('attribute_value', $this->db_op)->replace_array(array('res_id' => $id, 'res_type' => $res_type), $main_value);
        }

        $r = false;

        // 2、更新主表
        if ($main) {
            // an_ext 处理，之前有可能处理过
            $this->treatAn_ext($main);

            $r = $this->db->update($this->table, array($this->pk=>$id), $main); // 调用 DBAbstract::update()
        }

        ModelResObserver::notify($this->res_name, 'update', array($this->res_name, $id, &$info));

        return $r;
    }

    /**
     * 读取1条记录，只支持 id
     * 直接使用较少
     * 从主表、扩展属性表取记录，合并成1维数组返回
     * readID(2)
     * @param mixed    $filter 1、int或string 整型或字符串，是 pk 字段； 2、array() 传递过来的主表数据或带res_type参数
     * @param int      $info_stat=1 , 1 取缓存、扩展信息，处理观察者，0 只处理观察者
     * @param string   $mc_table_list_key 需要将数据继续缓存
     * @return array 1维数组，扩展属性的key为数字
     */
    public function readID(&$filter, $info_stat = 1, $mc_table_list_key = '')
    {
        if (!$this->link) $this->initialization();
        $info = NULL; // 传递过来的主表记录值

        // 变量接收处理
        if (is_array($filter)) {
            if (count($filter) > 2 || !isset($filter['res_type'])) {
                // 主表数据
                $info = $filter;
            }
            $id = $filter[$this->pk];
        } else {
            $id = $filter;
        }

        if (!$id) {
            throw new AnException('Model Error!', 'ModelRes::readID() Error!id is empty.');
        }

        // 执行查询操作
        if ($info_stat && (NULL === $info || $this->res_model)) {
            // 查询缓存,使用命名空间
            $info_cache = NULL; // 一个 mc 不能返回的数据，mc 有可能返回 array()
            $save_cache = FALSE; // 是否要写缓存
            $mc_key = '';

            // 取主表信息
            if (NULL === $info) {
                if ($this->cacheCan('read')) {
                    $mc_key = $this->table . '-' . $id;
                    $info_cache = $this->mc_table_ns->get($mc_key);
                    if (FALSE !== $info_cache) {
                        $info = $info_cache;
                    } else {
                        $save_cache = TRUE; // 要写缓存
                    }
                }

                // 从数据库中主表中读取,如主表数据全，只需补充扩展属性
                if (NULL === $info) {
                    // lock set
                    if ($save_cache && !$this->mc_table_ns->Lock($mc_key)) {
                        $i = 1;
                        while (FALSE === ($info = $this->mc_table_ns->get($mc_key)) && ++$i < 5) {
                            // 0.1 second
                            usleep(100000);
                        }
                        if ($info === FALSE) {
                            return array();
                        }
                    } else {
                        // $info = $this->mc_table_ns->call_user_func_array(array($this->db_slave, 'read'), array($this->pk => $id), "{$this->table}-{$id}", $this->lifetime);
                        $info = $this->db_slave->read($this->table, array($this->pk => $id));
                        // @todo 将 ext 的处理放在这里
                        $this->treatAn_ext($info);
                    }
                }
            }

            // 扩展属性处理
            if ($info && $this->res_model) {
                // an_ext 处理，之前有可能处理过
                // $this->treatAn_ext($info);
                if ($info[$this->pk] && $this->res_type && $this->attribute) {
                    // 扩展属性值表,只针对于id的条件进行操作
                    // $res_type 的校正
                    if (isset($info['res_type'])) $res_type = $info['res_type'];
                    else $res_type = $this->res_type;
                    // 此处 $res_model 是当前这条记录的
                    if ($res_type === $this->res_type) $res_model = $this->res_model;
                    else $res_model = _model('attribute_relation')->get_list($res_type);

                    // 取扩展属性值
                    if ($res_model && is_array($res_model)) {
                        $tem_info_arr = _model('attribute_value', $this->db_op)->get_list(array('res_id' => $id, 'res_type' => $res_type));
                        $info = $info + $tem_info_arr;
                    }
                }
            }

            // 将结果缓存
            if ($save_cache) {
                $this->mc_table_ns->set($mc_key, $info, $this->lifetime);
                // lock unlock
                $this->mc_table_ns->unlock($mc_key);
            }
        }

        // 处理 NULL 统一返回数组
        if (NULL === $info) {
            $info = $array();
        }

        // 将结果缓存
        if ($mc_table_list_key && $this->cache && is_object($this->mc_wr)) {
            $this->mc_table_list_ns->set($mc_table_list_key, $info, $this->lifetime);
        }

        if ($info) {
            ModelResObserver::notify($this->res_name, 'read', array($this->res_name, $info[$this->pk], &$info));
        }
        return $info;
    }

    /**
     * 删除1条记录，必须有id
     * 直接使用比较少
     * @param $info 删除的条件array('id'=>300, 'res_type'=>8)或array('id'=>300) 不必再输入其它参数
     * @return bool 主表执行结果
     * @example deleteID(123123);
     * @example deleteID(array('id'=>123123));
     * @example deleteID('id'=>300, 'res_type'=>8);
     */
    private function deleteID($filter)
    {
        if (!$this->link) $this->initialization();

        $id = $this->returnId($filter);
        if (!$id) {
            throw new AnException('Model Error!', 'ModelRes::deleteID() Error!id is empty.');
        }

        if (is_object($this->mc_wr)) {
            // 将变化的记录缓存清空
            $this->mc_table_ns->delete($this->table . '-' . $id);
        }

        ModelResObserver::notify($this->res_name, 'deleteBEF', array($this->res_name, $id));

        if ($this->res_type && $this->attribute && $this->res_model) {
            // 补充 res_type
            if ($this->res_typeExists()) {
                if (is_array($filter) && isset($filter['res_type'])) $res_type = $filter['res_type'];
                else $res_type = $this->db->getOne("SELECT `res_type` FROM `{$this->table}` WHERE `{$this->pk}`=?", $id);
            } else {
                $res_type = $this->res_type;
            }
            _model('attribute_value', $this->db_op)->delete(array('res_id' => $id, 'res_type' => $res_type));
        }

        $r = $this->db->exec("DELETE FROM `{$this->table}` WHERE `{$this->pk}`=?", $id); // 调用 DBAbstract::exec()

        if ($r) {
            ModelResObserver::notify($this->res_name, 'delete', array($this->res_name, $id));
        }

        return 1;
    }

    /**
     * 得到输入的id
     * 只有数值型的id返回
     * @param $filter
     * @return int id
     */
    private function returnId($filter)
    {
        if (is_numeric($filter)) {
            return $filter;
        } elseif (is_array($filter)) {
            // @TODO 根据需求可以将非整型返回
            if (count($filter) == 1) {
                // array(123), array(0=>123);
                if (isset($filter[0]) && is_numeric($filter[0])) return $filter[0];
                // 当传入array('id'=>10)
                elseif (isset($filter[$this->pk]) && is_numeric($filter[$this->pk])) return $filter[$this->pk];
            } elseif (count($filter) == 2 && isset($filter[$this->pk]) && is_numeric($filter[$this->pk]) && isset($filter['res_type'])) {
                // 当传入array('id'=>10, 'type'=>101)
                return $filter[$this->pk];
            } else {
                return 0;
            }
        }
        return 0;
    }

    /**
     * 返回符合多读条件的数组
     * @param mixed $filter
     * @return array 正常返回1维数组，没有返回空数组
     */
    private function IDS($filter)
    {
        if (!$filter) return false;

        if (is_numeric($filter)) {
            // 2、如果只是1个id
            return array($filter);
        } elseif (is_string($filter) && is_numeric($filter[0]) && strpos($filter, ',')) {
            // 1、'1,2,3,4,5'
            return explode(',', $filter);
        } elseif (is_array($filter)) {
            if (isset($filter[0])) {
                // 3、如果是1维数组，array(1,2,3,4);
                if (isset($filter[1])) {
                    // 过滤重复的值 2015-03,如果是key是数字，去除重复值
                    return array_unique($filter);
                } else {
                    return $filter;
                }
            } elseif (isset($filter[$this->pk]) && 1==count($filter)) {
                // 4、如果是2维数组，array('id'=>array(2,3,4,10,20))
                return $filter[$this->pk];
            }
        }

        return array();
    }

    /**
     * 判断res_type字段是否在表中存在
     * 同时将特殊的表过滤，这些表虽然包含res_type但不是作为扩展属性使用
     * 注意：表中res_type的含义，即res_type为框架的表保留命名
     * @return boolean
     */
    private function res_typeExists()
    {
        static $tem = array();
        if (isset($tem[$this->db_op][$this->table])) return $tem[$this->db_op][$this->table];
        if (in_array($this->table, array('category', 'catalog', 'comment'))) {
            return $tem[$this->db_op][$this->table] = false;
        }
        $tem[$this->db_op][$this->table] = array_key_exists('res_type', $this->tb);
        return $tem[$this->db_op][$this->table];
    }
}
?>