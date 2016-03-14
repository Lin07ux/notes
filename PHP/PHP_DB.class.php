<?php
/**
 * 封装一个简单的 DB 类库，以便操作数据库
 */
class DB
{
	private $host;
	private $port;
	private $user;
	private $pass;
	private $dbname;
	private $charset;
	private $prefix;	// 表前缀
	private $link;		// 数据库连接句柄资源

	public function __construct($arr = array())
	{
		$this->host = isset($arr['host']) ? $arr['host'] : 'localhost';
		$this->port = isset($arr['port']) ? $arr['port'] : 3306;
		$this->user = isset($arr['user']) ? $arr['user'] : 'root';
		$this->pass = isset($arr['pass']) ? $arr['pass'] : '';
		$this->dbname  = isset($arr['dbname'])  ? $arr['dbname']  : 'mydatabase';
		$this->charset = isset($arr['charset']) ? $arr['charset'] : 'utf8';
		$this->prefix  = isset($arr['prefix'])  ? $arr['prefix']  : '';

		$this->connect();		// 连接数据库
		$this->setCharset();	// 设置字符集
		$this->setDBname();		// 选择数据库
	}

    private function connect()
    {
    	$this->link = mysql_connect($this->host . ':' . $this_port, $this->user, $this->pass);

    	if (!this->link) {
    		// 这里应该写入日志，然后返回错误信息
    		die("连接出错。错误编号：{mysql_errno()}；错误内容：{mysql_error()}");
    	}
    }

    private function setCharset()
    {
    	$this->db_query("set names {$this->charset}");
    }

    private function setDBname() {
    	$this->db_query("use {$this->dbname}");
    }

    /**
     * 插入数据
     * @param  string $sql  需要执行的 sql 语句
     * @return mixed        成功则返回自增 ID，失败则返回 false
     */
    public function db_insert($sql)
    {
    	$this->db_query($sql);

    	return mysql_affected_rows() ? mysql_insert_id() : false;
    }

    /**
     * 删除记录
     * @param   string $sql 需要执行的 sql 语句
     * @return  mixed       成功则返回删除的行数，否则返回 false
     */
    public function db_delete($sql)
    {
    	$this->db_query($sql);

    	return mysql_affected_rows() ? mysql_affected_rows() : false;
    }

    /**
     * 更新数据
     * @param  string $sql 要执行的 sql 语句
     * @return mixed       成功时返回受影响的行数，失败返回 false
     */
    public function db_update($sql)
    {
    	$this->db_query($sql);

    	return mysql_affected_rows() ? mysql_affected_rows() : false;
    }

    /**
     * 返回查询到的一条记录
     * @param  string $sql 需要执行的 sql 语句
     * @return  mixed      成功时返回一个数组, 失败时返回 false
     */
    public function db_getRow($sql)
    {
    	$res = $this->query($sql);

    	return mysql_num_rows($res) ? mysql_fetch_assoc($res) : false;
    }

    /**
     * 查询多条记录
     * @param  string $sql 要查询的 sql 语句
     * @return mixed       成功时返回一个二维数组,失败时返回 false
     */
    public function db_getAll($sql)
    {
    	$res = $this->db_query($sql);

    	if (mysql_num_rows($res)) {
    		$list = array();

    		while ($row = mysql_fetch_assoc($res)) {
    			$list[] = $row;
    		}

    		return $list;
    	}

    	return false;
    }

    /**
     * 查询数据库
     * @param  string $sql 要执行的 sql 语句
     * @return mixed       查询出错则结束,否则返回查询结果的句柄
     */
    private function db_query($sql)
    {
    	$res = mysql_query($sql);

    	if ($res) {
    		// 这里应该写入日志中
    		die("查询出错。错误编号: {mysql_errno()}; 错误内容: {mysql_error()}。");
    	}

    	return $res;
    }

    /**
     * 休眠时执行
     * @return array 返回需要保存的属性数组
     */
    public function __sleep()
    {
    	return array(
            'host'=>$this->host,
            'port'=>$this->port,
            'user'=>$this->user,
            'pass'=>$this->pass,
            'dbname'=>$this->dbname,
            'charset'=>$this->charset,
            'prefix'=>$this->prefix
    		);
    }

    public function __wakeup()
    {
    	$this->link = $this->connect();
    	$this->setCharset();
    	$this->setDBname();
    }
}
