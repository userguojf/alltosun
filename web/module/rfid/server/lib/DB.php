<?php


class Db
{
    private $_c;
    private static $_self;
    private $error_sql = '';

    private function __construct()
    {
        $this->connect();
    }

    /**
     * 实例
     */
    public static function instantiation()
    {
        if (!is_object(self::$_self)) {
            self::$_self = new self();
        }

        return self::$_self;
    }


    /**
     * 连接
     */
    private function connect()
    {
        // $dsn = "mysql:host=DB_HOST;port=DB_PORT;dbname=DB_DBNAME";
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME;

        if (!is_object($this->_c)) {
            try {
                $this->_c = new PDO($dsn, DB_USER, DB_PASS, array(
                    // 添加连接数据库时的编码
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                ));

            } catch (PDOException $e) {
                //print "DB Error: ".$e->getMessage()."<br />";
            }
        }
    }

    public function __call($action, $params = array())
    {
        //是否存在
        if (!is_callable(array(__CLASS__, $action))) {
            return false;
        }

        //执行
        $result = call_user_func_array(array(__CLASS__, $action), $params);

        if ($result === false) {
            //记录log
            $this->error();

            //获取错误信息
            $error_info = $this->_c->errorInfo();

            //数据库断开
            if (isset($error_info[1]) && $error_info[1] == 2006) {

                //重新连接
                $this->reconnect();
                //重新执行
                return call_user_func_array(array(__CLASS__, $action), $params);
            }

            return false;

        }

        return $result;

    }

    /**
     * 重新连接
     */
    private function reconnect()
    {
        if (is_object($this->_c)) {
            $this->_c = null;
        }

        //连接
        $this->connect();
    }

    /**
     * 查询多条记录
     * @param string $table
     * @param string $select
     * @param string $where
     * @param string $sort
     * @param string $limit
     */
    private function find($table, $select, $where, $sort, $limit)
    {
        $sql = "select {$select} from {$table} {$where} {$sort} {$limit}";

        // var_dump($sql);
        $rs = $this->_c->query($sql);

        if ($rs === false) {
            $this->error_sql = $sql;
            return false;
        }

        return $rs->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 查询记录数
     * @param string $table
     * @param string $select
     * @param string $where
     */
    private function count($table, $where, $select)
    {
        $sql = "select {$select} from {$table} {$where}";
        $rs = $this->_c->query($sql);

        if ($rs === false) {
            $this->error_sql = $sql;
            return false;
        }

        return $rs->rowCount();
    }

    /**
     * 查询单条记录
     * @param string $table
     * @param string $select
     * @param string $where
     */
    private function one($table, $where, $select)
    {
        $sql = "select {$select} from {$table} {$where}";
        $rs = $this->_c->query($sql);
        if ($rs === false) {
            $this->error_sql = $sql;
            return false;
        }
        return $rs->fetch(PDO::FETCH_ASSOC);
    }

    /** 插入记录
     * @param string $table
     * @param string $keys
     * @param string $values
     */
    private function insert($table, $keys, $values)
    {

        $sql = "insert into {$table} ( {$keys} ) values ( {$values} )";
        $count = $this->_c->exec($sql);
        // 需要返回自增ID @TODO leijx
        if ($count) return $this->_c->lastInsertId();
        $this->error_sql = $sql;
        return $count;
    }

    /**
     * 更新记录
     * @param string $table
     * @param string $select
     * @param string $where
     */
    private function update($table, $set, $where)
    {
        $sql = "update {$table} {$set} {$where}";
        $count = $this->_c->exec($sql);

        if ($count === false) {
            $this->error_sql = $sql;
        }

        return $count;
    }

    /**
     * 删除记录
     * @param string $table
     * @param string $where
     */
    private function delete($table, $where)
    {
        $sql = "delete from {$table} {$where}";
        $rs = $this->_c->exec($sql);

        if ($rs === false) {
            $this->error_sql = $sql;
        }

        return $rs;
    }

    /**
     * 清空记录
     * @param string $table
     */
    private function truncate($table)
    {
        $sql = "truncate {$table}";
        $rs = $this->_c->exec($sql);

        if ($rs === false) {
            $this->error_sql = $sql;
        }

        return $rs;
    }

    public function error()
    {
        if ($this->_c->errorCode() != '00000') {
            $log = date('Y-m-d H:i:s') . '：' . json_encode($this->_c->errorInfo(), JSON_UNESCAPED_UNICODE) . "\n";
            $log .= date('Y-m-d H:i:s') . '：SQL:' . $this->error_sql . "\n";
            $log .= "--------------------------------------\n";
// 	        var_dump($log);exit;
            //写入日志
            file_put_contents('/data/log/swoole/mysql_exec_error.log', $log, FILE_APPEND);
        }
    }
}