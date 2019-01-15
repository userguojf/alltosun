<?php

/**
 * alltosun.com PDO操作的实现类 PDO.php
 * ============================================================================
 * 版权所有 (C) 2007-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 安然 (anr@alltosun.com) $
 * $Date: 2009-03-21 18:48:03 +0800 $
 * $Id: PDO.php 225 2012-04-11 17:09:58Z gaojj $
 * @link http://wiki.alltosun.com/index.php?title=Framework:PDO.php
*/

/**
 * PDO操作的实现类
 * @author anr@alltosun.com
 * @package AnDB
 * @link http://wiki.alltosun.com/index.php?title=Framework:class:PDOWrapper
 */
class PDOWrapper extends DBAbstract implements DBWrapper
{
    /**
     * 初始化配置
     * @todo 更精细的配置控制
     */
    public function init()
    {
        // parse_str("mysql:dbname=shop;host=localhost", $var);
        parse_str(str_replace(array(':', ';'), '&', $this->config), $var);
        $this->db_host = $var['host'];
        $this->db_name = $var['dbname'];
        $this->db_driver = 'PDO';
        $this->db_slave = !empty($model->db_slaves);
    }

    /**
     * lazy loading
     * @todo @uses DB::$links
     * @see DBWrapper::initialization()
     */
    public function initialization()
    {
        if (!($this->link instanceof PDO)) {
            $this->link = call_user_func_array(array(new ReflectionClass('PDO'), 'newInstance'), $this->config);
            $this->link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return $this->link;
    }

    /**
     * 执行sql查询
     * @param mixed $params
     * @param int $fixlimit 是否要在sql后面自动补充limit 1
     * @return resource
     * @see DBWrapper::query_exe()
     * @FIXME 对于数组参数需要测试
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
        if (!isset($params[0])) {
            if (!$sth = $this->link->query($sql)) {
                throw new Exception("Error sql query:$sql");
            }
        } else {
            if (is_array($params[0])) {
                $params = $params[0];
            }
            // @FIXME 对于数组参数需要测试
            $params_arr = array();
            foreach ($params as $v) {
                if (!is_array($v)) {
                	$params_arr[] = $v;
                } else {
                    foreach ($v as $v1) {
                    	$params_arr[] = $v1;
                    }
                }
            }
            $sth = $this->link->prepare($sql);
            if (!$sth->execute($params_arr)) {
                throw new Exception("Error sql prepare:$sql");
            }
        }

        return $sth;
    }

    /**
     * 执行sql语句，直接返回sql语句影响的记录数
     * @return int
     * @see DBWrapper::exec()
     */
    public function exec()
    {
        $sth = $this->query(func_get_args());

        return $sth->rowCount();
    }

    /**
     * 获取第一个字段的值
     * @return string 第1个字段的值
     * @see DBWrapper::getOne()
     */
    public function getOne()
    {
        $sth = $this->query(func_get_args(), 1);

        return $sth->fetchColumn();
    }

    /**
     * 获取一列记录
     * @return array 1维数组，指定字段组成
     * @see DBWrapper::getCol()
     */
    public function getCol()
    {
        $sth = $this->query(func_get_args());

        if ($out = $sth->fetchAll(PDO::FETCH_COLUMN, 0)) {
            return $out;
        }

        return array();
    }

    /**
     * 获取所有记录
     * @return array 2维数组
     * @see DBWrapper::getAll()
     */
    public function getAll()
    {
        $sth = $this->query(func_get_args());

        if ($out = $sth->fetchAll(PDO::FETCH_ASSOC)) {
            return $out;
        }

        return array();
    }

    /**
     * 获取一行记录
     * @return array 1维数组
     * @see DBWrapper::getRow()
     */
    public function getRow()
    {
        $sth = $this->query(func_get_args(), 1);

        if ($out = $sth->fetch(PDO::FETCH_ASSOC)) {
            return $out;
        }

        return array();
    }

    /**
     * 返回上次插入的id
     * @return int
     * @see DBWrapper::lastInsertId()
     */
    public function lastInsertId()
    {
        return $this->initialization()->lastInsertId();
    }

    /**
     * 事务开始
     */
    public function beginTransaction()
    {
        $this->initialization()->beginTransaction();
    }

    /**
     * 事务提交
     */
    public function commit()
    {
        $this->initialization()->commit();
    }

    /**
     * 事务回滚
     */
    public function rollBack()
    {
        $this->initialization()->rollBack();
    }
}
?>