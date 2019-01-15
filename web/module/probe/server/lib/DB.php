<?php


class Db
{
	private $_c;
	private static $_self;

	public function __construct()
	{
		// $dsn = "mysql:host=DB_HOST;port=DB_PORT;dbname=DB_DBNAME";
		$dsn = 'mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME;

		if (!is_object($this->_c)) {
		    $this->_c = new PDO($dsn, DB_USER, DB_PASS, array(
		        // 添加连接数据库时的编码
		        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
		    ));
		}
	}

	public static function instantiation()
	{
	    if (!is_object(self::$_self)) {
	        self::$_self = new self();
	    }

	    return self::$_self;
	}

	/**
	 * 查询多条记录
	 * @param string $table
	 * @param string $select
	 * @param string $where
	 * @param string $sort
	 * @param string $limit
	 */
	public function find($table, $select, $where, $sort, $limit)
	{
		$sql = "select {$select} from {$table} {$where} {$sort} {$limit}";

		// var_dump($sql);
		$rs = $this->_c->query($sql);

		return $rs->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * 查询记录数
	 * @param string $table
	 * @param string $select
	 * @param string $where
	 */
	public function count($table, $where, $select)
	{
		$sql = "select {$select} from {$table} {$where}";
		$rs = $this->_c->query($sql);
		return $rs->rowCount();
	}

	/**
	 * 查询单条记录
	 * @param string $table
	 * @param string $select
	 * @param string $where
	 */
	public function one($table, $where, $select)
	{
		$sql = "select {$select} from {$table} {$where}";
		$rs = $this->_c->query($sql);
		if (!$rs) return false;
		return $rs->fetch(PDO::FETCH_ASSOC);
	}

    /** 插入记录
     * @param string $table
     * @param string $keys
     * @param string $values
     */
    public function insert($table, $keys, $values)
    {
        $sql = "insert into {$table} ( {$keys} ) values ( {$values} )";
        $count = $this->_c->exec($sql);
        // 需要返回自增ID @TODO leijx
        if ($count) return $this->_c->lastInsertId();
        return $count;
    }

	/**
	 * 更新记录
	 * @param string $table
	 * @param string $select
	 * @param string $where
	 */
	public function update($table, $set, $where)
	{
		$sql = "update {$table} {$set} {$where}";
		$count = $this->_c->exec($sql);
		return $count;
	}

	/**
	 * 删除记录
	 * @param string $table
	 * @param string $where
	 */
	public function delete($table, $where)
	{
		$sql = "delete from {$table} {$where}";
		$rs = $this->_c->exec($sql);
		return $rs;
	}

	/**
	 * 清空记录
	 * @param string $table
	 */
	public function truncate($table)
	{
		$sql = "truncate {$table}";
		$rs = $this->_c->exec($sql);
		return $rs;
	}
}