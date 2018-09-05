<?php
/**
 * cron后台数据库连接操作类
 * 补充了中途丢失link状态重连机制
 * @package		DBProxy
 * @author		zhaoshunyao <zhaoshunyao@baidu.com>
 * @date		2011/11/09
 * 封装常用db操作函数,查询/更新/替换/批量查询/批量更新等
 * @author  shangjie@baidu.com
 * @date    2014/01/03
 * 修改checkup重连机制;查询失败后进行一次重试;使DBProxy对长时间脚本有更好的支持
 * @author    gaomochi@baidu.com
 * @date      2014/11/5
 */
class DBProxy
{
	protected $mysqli = null;
	protected $config;
	private $queryTime = 0;	//最近一条MYSQL查询语句的运行时间
    private $lastSql = '';  //最近一条MYSQL查询语句

	/**
	 * DBProxy Constructor
	 *
	 * @param array $config		Config of the dbproxy instance, as the following format:
	 * array('host' => ip,			// dbproxy server ip
	 *		 'port' => xx,			// dbproxy server port
	 *		 'username' => '',		// username to access dbproxy server
	 * 		 'password' => '',		// password to access dbproxy server
	 * 		 'database' => '',		// database to access dbproxy server
	 *		 'retry_times' => xx,	// retry times when failed to connect dbproxy cluster
	 *		)
	 */
	public function __construct(array $config)
	{
		$this->config = $config;
		$this->mysqli = $this->connect();
		$this->lastSql = '';
		$this->queryTime = 0;
	}

	/**
	 * DBProxy destructor.
	 */
	public function __destruct()
	{
		if($this->mysqli)
		{
			mysqli_close($this->mysqli);
			$this->mysqli = null;
		}
	}

	protected function connect()
	{
		$arrHosts = $this->config['hosts'];
		$intHosts = count($arrHosts);
		if($intHosts <= 0)
		{
			return false;
		}

        $intTimeoutSec = intval($this->config['timeout']) > 2 ? 2 : intval($this->config['timeout']);

		shuffle($arrHosts);
		for($i = 0; $i < $intHosts; $i++)
		{
			$mysqli = mysqli_init();
			if($intTimeoutSec == 0) $intTimeoutSec = 1;
			mysqli_options($mysqli, MYSQLI_OPT_CONNECT_TIMEOUT, $intTimeoutSec);
			mysqli_options($mysqli, 11 /*MYSQL_OPT_READ_TIMEOUT*/, 120); //读超时，mysql 120*3s=6分钟未返回数据则断开连接
			$bolRet = @mysqli_real_connect($mysqli, $arrHosts[$i], $this->config['username'], $this->config['password'], $this->config['database'], $this->config['port']);
			if(!$bolRet)
			{
				$mysqli = null;
				$logArr = array(
					'host' => $arrHosts[$i],
					'port' => $this->config['port'],
					'retrytime' => $i,
				);
				// Log::warning("connect_fail", 'dbproxy', $logArr);
				continue;
			}
			else
			{
				mysqli_set_charset($mysqli, 'utf8');
				return $mysqli;
			}
		}
		return false;
	}
	
	/**
	 * Whether the DBlink is workable.
	 * @return bool
	 */
	protected function checkup()
	{
		
			$errno = mysqli_errno($this->mysqli);
			if($errno == 2003 || $errno == 2006 || $errno == 2013 || !mysqli_ping($this->mysqli))
			{
				//2003：Can't connect to MySQL server on 'hostname' (4,110) （此情况一般是网络超时、数据库压力过大等导致）
				//2006：MySQL server has gone away （dbproxy在重启时可能会出现此问题，sleep 状态的链接）
				//2013：Lost connection to MySQL server during query （dbproxy在重启时可能会出现此问题，正在执行query）
				
				mysqli_close($this->mysqli);
				sleep(2);
				$this->mysqli = $this->connect();
				if (empty($this->mysqli)) {
					$logArr = array(
						'lastsql' => $this->lastSql,
						'queryTime' => $this->queryTime,
					);
					// Log::warning("checkup_connect_fail", 'dbproxy', $logArr);
					return false;
				}
			}
			return true;
		}
	
	
	/**
	 * close current connections.
	 */
	public function close()
	{
		if($this->mysqli)
		{
			mysqli_close($this->mysqli);
			$this->mysqli = null;
		}
	}
	
	public function query($sql)
	{
		if(!$this->checkup())
		{
			return false;
		}

		$startTime = microtime(true);
		$this->lastSql = $sql;
		$objRes = mysqli_query($this->mysqli, $sql);
		if(!$objRes)
		{
			mysqli_close($this->mysqli);
			sleep(2);
			$this->mysqli = $this->connect();
			$objRes = mysqli_query($this->mysqli, $sql);
			if(!$objRes) {
				return false;
			}
		}

		$endTime = microtime(true);
		$this->queryTime = $endTime - $startTime;
		return $objRes;
	}
	
	public function query_all_row($sql)
	{
		if(!$this->checkup())
		{
			return false;
		}

		$startTime = microtime(true);
		$this->lastSql = $sql;
		$objRes = mysqli_query($this->mysqli, $sql);
		
		if(!$objRes)
		{
			mysqli_close($this->mysqli);
			sleep(2);
			$this->mysqli = $this->connect();
			$objRes = mysqli_query($this->mysqli, $sql);
			if(!$objRes) {
				return false;
			}
		}
		$arrResult = array();
		while($arrTmp = mysqli_fetch_assoc($objRes))
		{
			$arrResult[] = $arrTmp;
		}
		$endTime = microtime(true);
		$this->queryTime = $endTime - $startTime;
		return $arrResult;
	}
	
	public function queryAllRows($sql)
	{
		if(!$this->checkup())
		{
			return false;
		}
	
		$startTime = microtime(true);
		$this->lastSql = $sql;
		$objRes = mysqli_query($this->mysqli, $sql);
		if(!$objRes)
		{
			mysqli_close($this->mysqli);
			sleep(2);
			$this->mysqli = $this->connect();
			$objRes = mysqli_query($this->mysqli, $sql);
			if(!$objRes) {
				return false;
			}
		}
		$arrResult = array();
		while($arrTmp = mysqli_fetch_assoc($objRes))
		{
			$arrResult[] = $arrTmp;
		}
		$endTime = microtime(true);
		$this->queryTime = $endTime - $startTime;
		return $arrResult;
	}
	
	public function queryFirstRow($sql)
	{
		if(!$this->checkup())
		{
			return false;
		}

		$startTime = microtime(true);
		$this->lastSql = $sql;
		$objRes = mysqli_query($this->mysqli, $sql);
		
		if(!$objRes)
		{
			mysqli_close($this->mysqli);
			sleep(2);
			$this->mysqli = $this->connect();
			$objRes = mysqli_query($this->mysqli, $sql);
			if(!$objRes) {
				return false;
			}
		}
        $ret = mysqli_fetch_assoc($objRes);
		
        $endTime = microtime(true);
		$this->queryTime = $endTime - $startTime;
		
		return $ret;
	}
	
	public function fetch_assoc($query)
	{
		if(!$this->checkup())
		{
			return false;
		}
		
        $startTime = microtime(true);
		$this->lastSql = $sql;
		
		$ret = mysqli_fetch_assoc($query);
        
        $endTime = microtime(true);
		$this->queryTime = $endTime - $startTime;
		
		return $ret;
	}

	/**
	 * Do multiple sql queries as a transaction
	 *
	 * @param array $arrSql	Array of sql queries to be executed
	 * @return bool Returns true on success or false on failure
	 */
	public function doTransaction(array $arrSql)
	{
		if(!$this->checkup())
		{
			return false;
		}

		mysqli_autocommit($this->mysqli, false);
		
		foreach($arrSql as $strSql)
		{
			$ret =  mysqli_query($this->mysqli, $strSql);
			if(!$ret)
			{
				mysqli_rollback($this->mysqli);
				mysqli_autocommit($this->mysqli, true);
				return false;
			}
		}

		mysqli_commit($this->mysqli);
		mysqli_autocommit($this->mysqli, true);
		
		return true;
	}
	
	/**
	 * Get the last inserted data's autoincrement id
	 * @return int
	 */
	public function get_insert_id()
	{
		if(!$this->checkup())
		{
			return false;
		}
		
		return mysqli_insert_id($this->mysqli);
	}
	
	/**
	 * Get the last inserted data's autoincrement id
	 * @return int
	 */
	public function getLastInsertID()
	{
		if(!$this->mysqli)
		{
			return false;
		}
		return mysqli_insert_id($this->mysqli);
	}

	/**
	 * Escapes special characters in a string for use in a SQL query
	 * @param string $str	String to be escaped
	 * @return bool|string	Return escaped string on success or false on failure
	 */
	public function real_escape_string($str)
	{
		return mysqli_real_escape_string($this->mysqli, $str);
	}
	
	/**
	 * Get errno of the last sql query
	 */
	public function get_errno()
	{
		if(!$this->mysqli)
		{
			return -1;
		}
		else
		{
			return mysqli_errno($this->mysqli);
		}
	}

	/**
	 * Get errno
	 */
	public function getErrno()
	{
		if($this->mysqli)
		{
			return mysqli_errno($this->mysqli);
		}
		else
		{
			return -1;
		}
	}

	/**
	 * Get errmsg of the last sql query
	 */
	public function get_error()
	{
		if (!$this->mysqli)
		{
			return 'mysql server not available';
		}
		else
		{
			return mysqli_error($this->mysqli);
		}
	}
	
	/**
	 * Get errmsg
	 */
	public function getErrmsg()
	{
		if($this->mysqli)
		{
			return mysqli_error($this->mysqli);
		}
		else
		{
			return 'mysql server not available';
		}
	}

	/**
	 * SET AUTO Commit of the Transaction
	 * @param TRUE/FALSE
	 */
	public function setAutoCommit($bool){

		$this->mysqli->autocommit($bool);
	}
	
	/**
	 * COMMIT the Transaction
	 * 
	 */
	public function commit(){
		$this->mysqli->commit();
	}
	
	/**
	 * 
	 * rollback the trancation
	 */
	public function rollback(){
		$this->mysqli->rollback();
	}

	/**
	 * Escapes special characters in a string for use in a SQL query
	 * @param string $str	String to be escaped
	 * @return bool|string	Return escaped string on success or false on failure
	 */
	public function realEscapeString($str)
	{
		if(!$this->mysqli)
		{
			return false;
		}
		return mysqli_real_escape_string($this->mysqli, $str);

	}
	
    /**
	 * Get number of affected rows of the last SQL query
	 * @return int
	 */
	public function getAffectedRows()
	{
		if(!$this->mysqli)
		{
			return false;
		}
		return mysqli_affected_rows($this->mysqli);
	}

	/**
	 * 得到最后查询的SQL
	 */
	public function getLastSql()
	{
		return $this->lastSql;
	}
	
	/**
	 * 得到查询的SQL执行时间
	 */
	public function getQueryTime()
	{
		return $this->queryTime;
	}

    /*
     * select记录
     * param $tableName 表名
     * param $selectFields array 提取字段名
     * param $whereFields array 查询条件,kv形式
     * param $limit str 限制一次提取数据量,默认1000
     * return array on succ, false on fail
     */
    public function selectTable($tableName, $selectFields, $whereFields, $limit='0,1000'){
        
        $whereSql = '';
        $tmpSqlArr = array();
        foreach($whereFields as $name=>$val){
            $tmpSqlArr[] = "$name = '$val'";
        }
        $whereSql = implode(' AND ', $tmpSqlArr);
        
        $strSql = 'SELECT '. implode(',', $selectFields) .' FROM ' . $tableName 
                .' WHERE '. $whereSql . ' LIMIT ' .$limit;
		$result = $this->query_all_row( $strSql );
        
        if( false === $result ){
            $logArr = array(
                    'errno' => $this->getErrno(),
                    'errmsg' => $this->getErrmsg(),
                    'sql' => $strSql
                    );
            // Log::warning("S_DB_read_fail", 'dbproxy', $logArr);
        }

        return $result;
    }


    /*
     * 更新记录
     * param $tableName 表名
     * param $updateFields array 待更新数据,kv形式
     * param $whereFields array 查询条件,kv形式
     * param $limit str 限制一次更新数据量,默认1
     * return true on succ, false on fail
     */
    public function updateTable($tableName, $updateFields, $whereFields, $limit='1'){
        
        $updateSql = '';
        $tmpSqlArr = array();
        foreach($updateFields as $name=>$val){
            $tmpSqlArr[] = "$name = '$val'";
        }
        $updateSql = implode(' , ', $tmpSqlArr);

        $whereSql = '';
        $tmpSqlArr = array();
        foreach($whereFields as $name=>$val){
            $tmpSqlArr[] = "$name = '$val'";
        }
        $whereSql = implode(' AND ', $tmpSqlArr);
            
        $strSql = 'UPDATE ' . $tableName .' SET '. $updateSql 
                .' WHERE '. $whereSql .' LIMIT ' .$limit;
        $result = $this->query( $strSql );
        if( false === $result ){
            $logArr = array(
                    'errno' => $this->getErrno(),
                    'errmsg' => $this->getErrmsg(),
                    'sql' => $strSql
                    );
            // Log::warning("S_DB_update_fail", 'dbproxy', $logArr);
        }

        return $result;
    }
    
    /*
     * 记录的replace,仅在表中有主键或唯一索引时生效，否则默认为插入
     * param $tableName 表名
     * param $replaceFields array 待更新数据,kv形式
     * return val on succ, false on fail
     * val为受影响的行的数目，即被删除和被插入的行数之和
     * val=1:插入, val>1:删除并更新
     */
    public function replaceTable($tableName, $replaceFields){

        $keySql = '';
        $valSql = '';
        $comma = '';
        foreach($replaceFields as $name=>$val){
            $keySql .= $comma ."`$name`";
            $valSql .= $comma ."'$val'";
            $comma = ', ';
        }

        $strSql = 'REPLACE into '. $tableName .'('. $keySql .') VALUES ('
                . $valSql .')';
		$result = $this->query( $strSql );
        
        if( false === $result ){
            $logArr = array(
                    'errno' => $this->getErrno(),
                    'errmsg' => $this->getErrmsg(),
                    'sql' => $strSql
                    );
            // Log::warning("S_DB_replace_fail", 'dbproxy', $logArr);
        }

        return $result;
    }

    /*
     * 支持where查询条件为 id IN ()形式的查询
     * param $tableName 表名
     * param $selectFields array 提取字段名
     * param $whereFields array 查询条件,kv形式,如 'song_id'=>'123,234'
     * param $limit str 限制一次提取数据量,默认1000
     * return array on succ, false on fail
     */
    public function multiSelectTable($tableName, $selectFields, $whereFields, $limit='0,1000'){
        
        $whereSql = '';
        $tmpSqlArr = array();
        foreach($whereFields as $name=>$val){
            $tmpSqlArr[] = "$name IN ( $val )";
        }
        $whereSql = implode(' AND ', $tmpSqlArr);
        
        $strSql = 'SELECT '. implode(',', $selectFields) .' FROM ' . $tableName 
                .' WHERE '. $whereSql . ' LIMIT ' .$limit;
		$result = $this->query_all_row( $strSql );
        
        if( false === $result ){
            $logArr = array(
                    'errno' => $this->getErrno(),
                    'errmsg' => $this->getErrmsg(),
                    'sql' => $strSql
                    );
            // Log::warning("S_DB_read_fail", 'dbproxy', $logArr);
        }

        return $result;
    }
    
    /*
     * 支持记录的批量更新
     * param $tableName 表名
     * param $mainidField str 主id字段名
     * param $updateFields array 更新数据,二维数组kv形式,如 
     * array(
     *       'hot'=> array(
     *           123 => 345,    
     *           234 => 345,    
     *       ),
     *       'title'=> array(
     *           123 => 'hello',    
     *           234 => 'world',    
     *       ),
     *  )
     * return true on succ, false on fail
     */
    public function multiUpdate($tableName, $mainidField, $updateFields){
        $strSql = 'UPDATE ' .$tableName. ' SET ';
        $resIds = array();
        $comma = ',';
        foreach($updateFields as $field=>$updateData) {
            $resIds = array_unique(array_merge($resIds, array_keys($updateData))); 
            $tmpCaseSql = $field.' = CASE '. $mainidField;
            $tmpWhenSql = '';
            foreach($updateData as $id=>$val){
                 $tmpWhenSql .= sprintf(" WHEN %d THEN '%s'", $id, $val);
            }
            $tmpWhenSql .=  ' END '.$comma ;
            $strSql .= $tmpCaseSql . $tmpWhenSql;
        }
        $strSql = substr($strSql ,0, -1);
        $tmpWhereSql = 'WHERE ' .$mainidField. ' IN ('. implode(',', $resIds) .')';
        $strSql .= $tmpWhereSql;
        $result = $this->query( $strSql );
        if( false === $result ){
            $logArr = array(
                    'errno' => $this->getErrno(),
                    'errmsg' => $this->getErrmsg(),
                    'sql' => $strSql
                    );
            // Log::warning("S_DB_update_fail", 'dbproxy', $logArr);
        }
        return $result;
    }
    
    /*
     * 记录的replace,仅在表中有主键或唯一索引时生效，否则默认为插入
     * param $tableName 表名
     * param $replaceFields array 待更新数据,二维数组kv形式,如
     * array(
     *    array(
     *        'song_id'   =>  123,
     *        'test_nums' =>  333,
     *        'addtime'   =>  '2014-01-08',
     *    ),
     *    array(
     *        'song_id'   =>  123,
     *        'test_nums' =>  333,
     *        'addtime'   =>  '2014-01-08',
     *    ),
     * )
     * return true on succ, false on fail
     */
    public function multiReplace($tableName, $replaceFields){
        $oneReplaceField = current($replaceFields); 
        if(empty($oneReplaceField) || !is_array($oneReplaceField)) 
            return false;
        
        $keySql = '';
        $comma = '';
        foreach($oneReplaceField as $name=>$val){
            $keySql .= $comma ."`$name`";
            $comma = ', ';
        }

        $valSql = '';
        $comma1 = '';
        foreach($replaceFields as $oneRow){
            $tmpValSql = '(';
            $comma2 = '';
            foreach($oneRow as $key=>$val){
                $tmpValSql .= $comma2 ."'$val'";
                $comma2 = ', ';
            } 
            $tmpValSql .= ')';
            $valSql .= $comma1 . $tmpValSql;
            $comma1 = ', ';
        }
        $strSql = 'REPLACE into '. $tableName .'('. $keySql .') VALUES '
                . $valSql ;
		$result = $this->query( $strSql );
        
        if( false === $result ){
            $logArr = array(
                    'errno' => $this->getErrno(),
                    'errmsg' => $this->getErrmsg(),
                    'sql' => $strSql
                    );
            // Log::warning("S_DB_replace_fail", 'dbproxy', $logArr);
        }

        return $result;
    }
}
