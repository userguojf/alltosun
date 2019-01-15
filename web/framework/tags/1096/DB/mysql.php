<?php

/**
 * alltosun.com MySQL操作的实现类 mysql.php
 * ============================================================================
 * 版权所有 (C) 2007-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 安然 (anr@alltosun.com) $
 * $Date: 2009-03-21 18:48:03 +0800 $
 * $Id: mysql.php 573 2012-10-31 09:30:07Z anr $
 * @link http://wiki.alltosun.com/index.php?title=Framework:mysql.php
*/

/**
 * MySQL操作的实现类
 * @author anr@alltosun.com
 * @package AnDB
 * @link http://wiki.alltosun.com/index.php?title=Framework:class:mysqlWrapper
 */
class mysqlWrapper extends DBAbstract implements DBWrapper
{
    /**
     * 连接数据库
     * @uses DB::$links
     * @link http://wiki.alltosun.com/index.php?title=Framework:class:mysqlWrapper#initialization.28.29
     */
    public function initialization()
    {
        if ($this->link === null) {
            // 数据库不同，建立新链接
            $dbkey = md5(serialize($this->config));
            $dbname = array_pop($this->config);

            if (isset(DB::$links[$dbkey])) {
                // 1、如果数据库的链接存在
                return $this->link = &DB::$links[$dbkey];
            }

            if (count($this->config) < 4) {
                // 2、如果参数不足，补全
                $this->config = array_pad($this->config, 3, '');
                // 3、new_link
                array_push($this->config, true);
            }

            $this->link = call_user_func_array('mysql_connect', $this->config);

            // 错误处理，未连接数据
            if (!is_resource($this->link)) {
                throw new AnException('DB Error.', "mysqlWrapper::initialization() Error!connect DB failed.{$this->config[0]} > {$dbname} username={$this->config[1]}");
            }

            //$version = mysql_get_server_info($this->link);
            $character_set = Config::get('db_character_set');
            if(!$character_set) $character_set = 'utf8';

            $sql = "SET character_set_connection=$character_set, character_set_results=$character_set, character_set_client=binary";
            mysql_unbuffered_query($sql, $this->link);
            mysql_unbuffered_query("SET sql_mode=''", $this->link);
            mysql_select_db($dbname, $this->link);
            DB::$links[$dbkey] = &$this->link;

            if ($this->debug) {
                $dg = array(
                        'sql'      => "mysql->{$sql}",
                        'db'       => "mysql_connect server={$this->config[0]} username={$this->config[1]} dbname={$dbname}",
                        'info'     => 0,
                );
                AnPHP::lastRunTime();
                $this->addDebugInfo(__METHOD__, 'connect', $dg);
            }
        }

        return $this->link;
    }

    /**
     * 获取查询返回的关联数组
     * @param resource $query
     * @return array
     */
    public function fetch($query)
    {
        return mysql_fetch_array($query, MYSQL_ASSOC);
    }

    /**
     * 执行sql查询
     * @param mixed $params
     * @param int $fixlimit 是否要在sql后面自动补充limit 1
     * @return resource
     * @link http://wiki.alltosun.com/index.php?title=Framework:class:mysqlWrapper#query_exe.28.29
     * @see DBWrapper::query_exe()
     */
    public function query_exe($params, $fixlimit = 0)
    {
        if (!$this->link) $this->initialization();

        if ($this->debug) {
            AnPHP::lastRunTime();
        }

        if (is_array($params)) {
            $sql = array_shift($params);
            if (isset($params[0]) && is_array($params[0])) {
                $params = $params[0];
            }
        } else {
            $sql = $params;
        }

        if ($fixlimit && stripos($sql, 'limit') === false) {
            $sql .= ' LIMIT 1';
        }

        if ($params && is_array($params) && strpos($sql, '?')) {
            $params_arr = array();
            foreach ($params as $v) {
                // @link http://mantis.alltosun.com/view.php?id=6586
                if (is_numeric($v)) $params_arr[] = $v;
                else $params_arr[] = mysql_real_escape_string($v, $this->link);
            }

            $sql = $this->bindParam($sql, $params_arr);
        }

        $query = mysql_query($sql, $this->link);

        // mysql 出错处理
        if ($query === false) {
            throw new AnException('DB Error.',"mysqlWrapper::query_exe() Error!\nmysql_errno:" . mysql_errno($this->link) . "\nmysql_error:" . mysql_error($this->link) . "\nError sql:" . $sql . "\n");
        }

        // add DEBUG
        if ($this->debug) {
            $dg = array(
                    'sql'  => $sql,
                    'info' => mysql_info(),
                    'db'   => "{$this->db_driver} : {$this->db_host} > {$this->db_name}",
            );
            if (strncasecmp($sql, 'SELECT ', 7) == 0) {
                $dg['explain'] = mysql_fetch_assoc(mysql_query('EXPLAIN '.$sql, $this->link));
            }
            if ($params) {
                $dg['sql_info'] = "\n <br />" . var_export($params, true);
            }
            $this->addDebugInfo(__METHOD__, 'query', $dg);
        }

        return $query;
    }

    /**
     * 执行sql语句，直接返回sql语句影响的记录数
     * @link http://wiki.alltosun.com/index.php?title=Framework:class:mysqlWrapper#exec.28.29
     * @return int
     * @see DBWrapper::exec()
     */
    public function exec()
    {
        $this->query(func_get_args());

        // 只有在主库执行的操作才会使用exec()
        return mysql_affected_rows($this->link);
    }

    /**
     * 获取第一个字段的值
     * @return string 第1个字段的值
     * @see DBWrapper::getOne()
     */
    public function getOne()
    {
        $query = $this->query(func_get_args(), 1);
        $rs = mysql_fetch_array($query, MYSQL_NUM);
        mysql_free_result($query);

        return $rs[0];
    }

    /**
     * 获取一行记录
     * @return array 1维数组
     * @see DBWrapper::getRow()
     */
    public function getRow()
    {
        $query = $this->query(func_get_args(), 1);
        $rs = mysql_fetch_array($query, MYSQL_ASSOC);
        mysql_free_result($query);

        return $rs === false ? array() : $rs;
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
        while ($rt = mysql_fetch_array($query, MYSQL_NUM)) {
            $rs[] = $rt[0];
        }
        mysql_free_result($query);
        return $rs;
    }

    /**
     * 获取所有记录
     * @return array 2维数组
     * @see DBWrapper::getAll()
     */
    public function getAll()
    {
        $query = $this->query(func_get_args());
        $rs = array();
        while ($rt = mysql_fetch_array($query, MYSQL_ASSOC)) {
            $rs[] = $rt;
        }
        mysql_free_result($query);

        return $rs;
    }

    /**
     * 返回上次插入的id
     * @return int
     * @see DBWrapper::lastInsertId()
     */
    public function lastInsertId()
    {
        $this->initialization();

        return mysql_insert_id($this->link);
    }

    /**
     * 事务开始
     */
    public function beginTransaction()
    {
        $this->query('START TRANSACTION');
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

    /**
     * 关闭
     */
    public function close()
    {
        if (isset($this->link) && is_resource($this->link)) {
            mysql_close($this->link);
            unset($this->link);
        }
    }
}
?>