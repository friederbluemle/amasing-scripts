<?php
class DB {


	const MAIN_DB = 'amasingDB';
	private static $dbs = array();
	protected $host = 'localhost';
	protected $user = 'prazdbusr';
	protected $pass = 'iam2good';
	protected $db = 'amasingDB';
	
	protected $connection;
	protected $isConnected = false;

	private $errors = array();
	private $queryLog = array();
	private $logQueries = true;
        
        private $memcache;
	
	public static function getConnection($db_name = self::MAIN_DB, $devMode = true) {
                
                global $host,$user,$pass;
		if (!isset(self::$dbs[$db_name]) || self::$dbs[$db_name] === null) {
			self::$dbs[$db_name] = new DB($host, $user, $pass, $db_name);
			self::$dbs[$db_name]->query('SET NAMES utf8');
		}
		return self::$dbs[$db_name];
	}
	
	public function __construct() {
	
                $this->memcache = new Memcache;
                $this->memcache->connect('localhost', 11211) or die ("Could not connect to memcache");
	}
	
	private function isConnected() { return $this->isConnected; }
	
	protected function connect($new_link=false) {
		if ($this->isConnected())
			return $this->connection;  
		if (($this->connection = mysql_connect($this->host, $this->user, $this->pass, $this->db))
			&& (mysql_select_db($this->db)))
			$this->isConnected = true;
		return $this->connection;
	}

	public function logQueries($state) { $this->logQueries = (bool)$state; }
	public function getQueryLog() { return $this->queryLog; }
	
	public function getErrors() { return $this->errors; }
	private function logError($errorString) {
		$this->errors[] = $errorString;
	}
	
	/*** public interface for querying ***/
	
	/**
	 * Function: query
	 * Generic function to run any query directly 
	 */
	public function query($sql) {
		$error = null;
		$startTime = microtime(true);
                
                $result = $this->memcache->get(md5($sql));
                if(!$result)
                {
                    if (!$this->connect()) 
                    {
                            
                            return false;
                    }
                    if (!($result = mysql_query($sql, $this->connection))) {
                            $error = mysql_error();
                            $this->logError($error);
                    }
                    $this->memcache->set(md5($sql),$result,MEMCACHE_COMPRESSED,43200);
                }
		$endTime = microtime(true);

		if ($this->logQueries) {
			$this->queryLog[] = array(
				'sql'=>$sql,
				'time'=>round(($endTime-$startTime)*1000, 3),
				'error'=>$error,
				);
		}
		
		return $result;
	}

	/**
	 * Escapes a string for use in sql query.
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	public function escapeString($string) {
		if (!$this->connect()) return false;
		return mysql_real_escape_string($string);
	}
}
?>