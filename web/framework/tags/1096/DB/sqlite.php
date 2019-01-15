<?php

/**
 * alltosun.com SQLite操作的实现类 sqlite.php
 * ============================================================================
 * 版权所有 (C) 2007-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 安然 (anr@alltosun.com) $
 * $Date: 2009-03-21 18:48:03 +0800 $
 * $Id: sqlite.php 225 2012-04-11 17:09:58Z gaojj $
 * @link http://wiki.alltosun.com/index.php?title=Framework:sqlite.php
*/

/**
 * SQLite操作的实现类
 * @author anr@alltosun.com
 * @package AnDB
 * @link http://wiki.alltosun.com/index.php?title=Framework:class:sqliteWrapper
 */
class sqliteWrapper extends DBAbstract implements DBWrapper
{
    /**
     * 初始化配置
     */
    public function init()
    {
        $this->db_host = 'localhost';
        $this->db_name = $this->config[0];
        $this->db_driver = 'sqlite';
        $this->db_slave = !empty($model->db_slaves);
    }

    /**
     * 初始化连接
     * @todo @uses DB::$links
     * @see DBWrapper::initialization()
     */
    public function initialization()
    {
        if (!($this->link instanceof SQLiteDatabase)) {
            $this->link = call_user_func_array(array(new ReflectionClass('SQLiteDatabase'), 'newInstance'), $this->config);
        }

        return $this->link;
    }

    /**
     * 执行sql查询
     * @param mixed $params
     * @param int $fixlimit 是否要在sql后面自动补充limit 1
     * @return resource
     * @see DBWrapper::query_exe()
     */
    public function query_exe($params, $fixlimit = 0)
    {
        $params = (array)$params;

        $sql = array_shift($params);
        if ($fixlimit && stripos($sql, 'limit') === false) {
            $sql .= ' LIMIT 1';
        }

        DB::$sql[] = $sql;
        $this->initialization();
        if (isset($params[0])) {
            if (is_array($params[0])) {
                $params = $params[0];
            }
            foreach ($params as $key => $val) {
                $params[$key] = sqlite_escape_string($val);
            }
            $sql = $this->bindParam($sql, $params);
        }

        $query = $this->link->query($sql);
        if ($query === false) {
            throw new Exception("Error sql query:$sql");
        }

        return $query;
    }

    /**
     * 执行sql语句，直接返回sql语句影响的记录数
     * @return int
     * @see DBWrapper::exec()
     */
    public function exec()
    {
        $this->query(func_get_args());

        return $this->link->changes();
    }

    /**
     * 获取一列记录
     * @return array 1维数组，指定字段组成
     * @see DBWrapper::getCol()
     */
    public function getCol()
    {
        $query = $this->query(func_get_args());
        $rs = array();
        while ($rt = $query->fetch(SQLITE_NUM)) {
            $rs[] = $rt[0];
        }

        return $rs;
    }

    /**
     * 获取第一个字段的值
     * @return string 第1个字段的值
     * @see DBWrapper::getOne()
     */
    public function getOne()
    {
        $query = $this->query(func_get_args(), 1);

        return $query->fetchSingle();
    }

    /**
     * 获取一行记录
     * @return array 1维数组
     * @see DBWrapper::getRow()
     */
    public function getRow()
    {
        $query = $this->query(func_get_args(), 1);

        return $query->fetch(SQLITE_ASSOC);
    }

    /**
     * 获取所有记录
     * @return array 2维数组
     * @see DBWrapper::getAll()
     */
    public function getAll()
    {
        $query = $this->query(func_get_args());

        return $query->fetchAll(SQLITE_ASSOC);
    }

    /**
     * 返回上次插入的id
     * @return int
     * @see DBWrapper::lastInsertId()
     */
    public function lastInsertId()
    {
        return $this->initialization()->lastInsertRowid();
    }

    /**
     * 事务开始
     */
    public function beginTransaction()
    {
        $this->query('BEGIN TRANSACTION');
    }

    /**
     * 事务提交
     */
    public function commit()
    {
        $this->query('COMMIT');
    }

    /**
     * 事务回滚
     */
    public function rollBack()
    {
        $this->query('ROLLBACK');
    }
}
?>