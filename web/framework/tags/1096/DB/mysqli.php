<?php

/**
 * alltosun.com MySQLi操作的实现类 mysqli.php
 * ============================================================================
 * 版权所有 (C) 2007-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 高竞竞 (gaojj@alltosun.com) $
 * $Date: 2009-08-10 22:38:25 +0800 $
 * $Id: mysqli.php 1057 2015-11-19 08:41:55Z liudh $
 * @link http://wiki.alltosun.com/index.php?title=Framework:mysqli.php
*/

/**
 * MySQLi操作的实现类
 * @author anr@alltosun.com
 * @package AnDB
 * @link http://wiki.alltosun.com/index.php?title=Framework:class:mysqliWrapper
 */
class mysqliWrapper extends DBAbstract implements DBWrapper
{
    /**
     * @var mysqli
     */
    public $link;

    /**
     * lazy loading
     * @todo @uses DB::$links
     * @see DBWrapper::initialization()
     * @return mixed|mysqli
     * @throws AnException
     */
    public function initialization()
    {
        if (!($this->link instanceof mysqli)) {
            $this->link = call_user_func_array(array(new ReflectionClass('mysqli'), 'newInstance'), $this->config);

            // 错误处理，未连接数据
            if ($this->link->connect_error) {
                throw new AnException('DB Error.', 'mysqliWrapper::initialization() Error!connect DB failed.');
            }

            $character_set = Config::get('db_character_set');
            if (!$character_set) $character_set = 'utf8';

            if (defined('D_BUG') && D_BUG) {
                global $g;
                $g['sql'][] = array(
                     'sql'      => "mysqli->SET character_set_connection=$character_set, character_set_results=$character_set, character_set_client=binary",
                     'time'     => 0,'info'     => 0,'explain'  => 0,
                     'db'       => "mysqli_connect server={$this->config[0]} username={$this->config[1]} dbname={$this->config[3]}",
                );
            }

            $this->link->query("SET character_set_connection=$character_set, character_set_results=$character_set, character_set_client=binary");
            $this->link->query("SET sql_mode=''");
        }

        return $this->link;
    }

    /**
     * @param mixed $params
     * @param int $fixlimit
     * @return mixed | mysqli_stmt
     * @throws AnException
     */
    public function query($params, $fixlimit = 0)
    {
        return parent::query($params, $fixlimit);
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
        if (!$this->link) $this->initialization();

        if (defined('D_BUG') && D_BUG) {
            AnPHP::lastRunTime();
        }

        if (is_array($params)) {
            $sql = array_shift($params);
            if (isset($params[0]) && is_array($params[0])) {
                $params = $params[0];
            }
        } else {
            $sql = $params;
            unset($params);
        }

        if ($fixlimit && stripos($sql, 'limit') === false) {
            $sql .= ' LIMIT 1';
        }

        $params_arr = array();
        if (isset($params) && is_array($params) && strpos($sql, '?')) {
            // @link http://mantis.alltosun.com/view.php?id=6586
            //$params_arr = $params;
            foreach ($params as $k => $v){
                $params_arr[$k] = &$params[$k];
            }
            $s = str_repeat('s', count($params_arr));
            array_unshift($params_arr, $s);
        }

        // add DEBUG
        if (defined('D_BUG') && D_BUG) {
            global $g;
            if (!$g['sql']) $g['sql'] = array();
            $explain = array();
            $info = '';

            $sql_info = $params ? "\n <br />" . var_export($params, true) : NULL;
            //$sql_real = $params ? $this->bindParam($sql, $params) : $sql;
            $sql_real = $params ? $this->bindParam($sql, $params) : $sql;


            if (strncasecmp($sql, 'SELECT ', 7) == 0 && strpos($sql, '?')) {
                $stmt = $this->link->prepare("EXPLAIN $sql");
                if ($params_arr && strpos($sql, '?')) {
                    call_user_func_array(array($stmt, 'bind_param'), $params_arr);
                }
                $stmt->execute();

                $bindResult = $this->_getResultFields($stmt, $explain);

                $stmt->store_result();
                call_user_func_array(array($stmt, 'bind_result'), $bindResult);
                $stmt->fetch();
                $stmt->free_result();
            }

            $sql_debug_info = array(
                'sql'      => $sql,
                'sql_info' => $sql_info,
                'sql_real' => $sql_real,
                'time'     => '',
                'info'     => $info,
                'explain'  => $explain,
                'db'       => "{$this->db_driver} : {$this->db_host} > {$this->db_name}",
            );
            $mtime = explode(' ', microtime());
            $sqlstarttime = $mtime[1] + $mtime[0];
        }

        // 预处理出错
        $stmt = $this->link->prepare($sql);
        if (!$stmt) {
            throw new AnException('DB Error.', "mysqliWrapper::query_exe() Error!\nmysqli_errno:" . $this->link->errno . "\nmysqli_error:" . $this->link->error . "\nError sql:$sql\n");
        }

        if ($params_arr && strpos($sql, '?')) {
            call_user_func_array(array($stmt, 'bind_param'), $params_arr);
        }

        $stmt->execute();
        if ($stmt->errno) {
            throw new AnException('DB Error.', "mysqliWrapper::query_exe() Error!\nmysqli_errno:" . $stmt->errno . "\nmysqli_error:" . $stmt->error . "\nError sql:$sql\n");
        }

        if (defined('D_BUG') && D_BUG) {
            $mtime = explode(' ', microtime());
            $sqltime = number_format(($mtime[1] + $mtime[0] - $sqlstarttime), 6)*1000;
            $sql_debug_info['time'] = $sqltime;
            $g['sql'][] = $sql_debug_info;

            $dg = array(
                'sql'  => $sql,
                'sql_info' => $sql_info,
                'sql_real' => $sql_real,
                'info'     => $info,
                'explain'  => $explain,
                'db'   => "{$this->db_driver} : {$this->db_host} > {$this->db_name}",
            );

            $this->addDebugInfo(__METHOD__, 'query', $dg);
        }

        return $stmt;
    }

    /**
     * 执行sql语句，直接返回sql语句影响的记录数
     * @return int
     * @see DBWrapper::exec()
     */
    public function exec()
    {
        $stmt = $this->query(func_get_args());
        return $stmt->affected_rows;
    }

    /**
     * 获取第一个字段的值
     * @return string 第1个字段的值
     * @see DBWrapper::getOne()
     */
    public function getOne()
    {
        $stmt = $this->query(func_get_args(), 1);

        $result = $stmt->get_result();
        if (!$result){
            return '';
        }
        $row = $result->fetch_array(MYSQLI_NUM);
        return is_string($row[0]) ? $row[0] : (string)$row[0];
        //return $row[0];

        /*
        $result = '';
        $stmt->store_result();
        $r = $stmt->store_result();
        $stmt->bind_result($result);
        $stmt->fetch();
        $stmt->free_result();
        return $result;
        */
    }

    /**
     * 获取一列记录
     * @return array 1维数组，指定字段组成
     * @see DBWrapper::getCol()
     */
    public function getCol()
    {
        $stmt = $this->query(func_get_args());

        $result = $stmt->get_result();
        if (!$result){
            return array();
        }

        $rs = array();
        while ($rt = $result->fetch_array(MYSQLI_NUM)) {
            //$rs[] = $rt[0];
            $rs[] = is_string($rt[0]) ? $rt[0] : (string)$rt[0];;
        }
        $stmt->free_result();
        return $rs;

        /*
        $result = array();
        $r = $stmt->store_result();
        $stmt->bind_result($result);
        $out = array();
        while ($stmt->fetch()) {
            $out[] = $result;
        }
        $stmt->free_result();
        return $out;
        */
    }

    /**
     * 获取所有记录
     * @return array 2维数组
     * @see DBWrapper::getAll()
     */
    public function getAll()
    {
        $stmt = $this->query(func_get_args());

        $rs = array();
        $result = $stmt->get_result();
        if (!$result){
            return $rs;
        }
        while($rt = $result->fetch_array(MYSQLI_ASSOC)){
            $rowValue = array();
            foreach ($rt as $k => $v) {
                if (is_numeric($v)){
                    $rowValue[$k] = (string)$v; // 统一成字符串格式
                }else{
                    $rowValue[$k] = $v;
                }
            }

            $rs[] = $rowValue;
        }
        $stmt->free_result();
        return $rs;


        /*
        $result = $rowData = array();

        $bindResult = $this->_getResultFields($stmt, $rowData);

        $stmt->store_result();
        call_user_func_array(array($stmt, 'bind_result'), $bindResult);

        while ($stmt->fetch()) {
            // rowData is references
            $rowValue = array();
            foreach ($rowData as $k=>$v) {
                if (is_numeric($v)){
                    $rowValue[$k] = $v . ''; // 统一成字符串格式
                }else{
                    $rowValue[$k] = $v;
                }
            }
            $result[] = $rowValue;
        }
        $stmt->free_result();

        return $result;
        */
    }

    /**
     * 获取一行记录
     * @return array 1维数组
     * @see DBWrapper::getRow()
     */
    public function getRow()
    {
        $stmt = $this->query(func_get_args(), 1);

        $result = $stmt->get_result();
        if (!$result){
            return array();
        }
        $row = $result->fetch_array(MYSQLI_ASSOC);
        if (empty($row)) return array();

        $rowValue = array();
        foreach ($row as $k => $v) {
            if (is_numeric($v)){
                $rowValue[$k] = (string)$v; // 统一成字符串格式
            }else{
                $rowValue[$k] = $v;
            }
        }

        return $rowValue;

        /*
        $rowData = array();

        $bindResult = $this->_getResultFields($stmt, $rowData);

        $stmt->store_result();
        call_user_func_array(array($stmt, 'bind_result'), $bindResult);

        if ($stmt->fetch()) {
            $stmt->free_result();

            if (!empty($rowData)){
                $rowValue = array();
                foreach ($rowData as $k=>$v) {
                    if (is_numeric($v)){
                        $rowValue[$k] = $v . ''; // 统一成字符串格式
                    }else{
                        $rowValue[$k] = $v;
                    }
                }
                return $rowValue;
            }
            return $bindResult;
        }

        return array();
        */
    }

    /**
     * 返回上次插入的id
     * @return int
     * @see DBWrapper::lastInsertId()
     */
    public function lastInsertId()
    {
        return $this->initialization()->insert_id;
    }


    /**
     * 获取查询结果的Fields信息
     * @param $stmt mysqli_stmt
     * @param $rowData array 引用的每行数据信息
     * @return array 返回供$stmt::bind_result绑定结果集的参数数组
     * @author gaojj@alltosun.com
     */

    private function _getResultFields($stmt, &$rowData)
    {
        $result = $stmt->result_metadata();

        // 错误处理
        if (!$result) {
            throw new AnException('DB Error.', "mysqliWrapper::_getResultFields() Error!\nmysqli_errno:" . $stmt->errno . "\nmysqli_error:" . $stmt->error);
        }

        $fields = $result->fetch_fields();
        $bindResult = array();
        foreach ($fields as $v) {
            $bindResult[] = &$rowData[$v->name];
        }
        return $bindResult;
    }

    /**
     * 事务开始
     */
    public function beginTransaction()
    {
        $this->initialization()->autocommit(false);
    }

    /**
     * 事务提交
     */
    public function commit()
    {
        $this->initialization()->commit();
        $this->initialization()->autocommit(true);
    }

    /**
     * 事务回滚
     */
    public function rollBack()
    {
        $this->initialization()->rollback();
        $this->initialization()->autocommit(true);
    }

    /**
     * 关闭
     */
    public function close()
    {
        if (isset($this->link)) {
            $this->link->close();
            unset($this->link);
        }
    }

    function refValues($arr)
    {
        if (strnatcmp(phpversion(),'5.3') >= 0) //Reference is required for PHP 5.3+
        {
            $refs = array();
            foreach($arr as $key => $value){
                $refs[$key] = &$arr[$key];
            }
            return $refs;
        }
        return $arr;
    }
}

