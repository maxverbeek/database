<?php
/**
 * @version    1.1
 * @author     Sibren Talens  <sibrentalens@gmail.com>
 * @copyright  2014-2015      Sibren Talens
 * @license    Apache license http://sibrentalens.com/license
 * @link       GitHub         https://github.com/SibrenTalens/database
 */
class Database{
	/**
	 * @since  1.0
	 *
	 * Private static variables
	 * @var Database  $_instance   Contains the instance of this class
	 * @var string      $_dbname   The name of the database
	 * @var string      $_username The username
	 * @var string      $_password The password
	 * @var string      $_host     The host, usually localhost
	 * @var array       $_options  The options to use while connecting
	 */
	private static $_instance = null,
	               $_dbname,
	               $_username,
	               $_password,
	               $_host,
	               $_options;

	/**
	 * @since  1.0
	 *
	 * Private variables
	 * @var PDO         $_pdo      The PDO object, or DBH (DataBase Handler)
	 * @var string      $_query    The prepared query
	 * @var array       $_results  The results of the query
	 * @var bool        $_error    Any errors occurred while connecting will go here
	 * @var int         $_count    The number of rows fetched
	 */
	private $_pdo,
	        $_query,
	        $_results,
	        $_error = false,
	        $_count = 0;

	/**
	 * @since  1.0
	 *
	 * Set the config variables
	 * @param  string   $dbname     The name of the database
	 * @param  string   $username   The username
	 * @param  string   $password   The password
	 * @param  string   $host       The host, usually localhost
	 * @param  array    $options    Any options used while connecting
	 */
	public static function init($dbname, $username, $password, $host = '127.0.0.1', array $options = array()){
		// Set all the static variables to the ones provided as parameter
		self::$_dbname = $dbname;
		self::$_username = $username;
		self::$_password = $password;
		self::$_host = $host;
		self::$_options = $options;
		return;
	}

	/**
	 * @since  1.0
	 *
	 * If available, returns an existing instance, else creates and returns one
	 * @return Database           The instance of the class
	 */
	public static function inst(){
		// If an instance isn't already created, make one
		if(!isset(self::$_instance)) self::$_instance = new Database();

		// Return the instance
		return self::$_instance;
	}

	/**
	 * @since  1.0
	 *
	 * Construct the database with data given in the init() function
	 */
	private function __construct(){
		try{
			// Attempt to connect to the database
			$this->_pdo = new PDO(
				'mysql:host='.self::$_host.';dbname='.self::$_dbname,
				self::$_username,
				self::$_password,
				self::$_options
			);
		}catch(PDOException $e){
			// Kill the script and produce an error
			die($e->getMessage());
		}
		return;
	}

	/**
	 * @since  1.0
	 *
	 * Queries a string to the database
	 * @param  string   $sql     The string to be executed
	 * @param  array    $params  Optional parameters
	 * @param  bool     $limit   An optional limit to the query
	 * @return Database          The result
	 */
	private function _query($sql, array $params = array(), $limit = false){
		// Set the error to false
		$this->_error = false;

		// Add a limit of one to $sql if the argument is true
		$sql .= ($limit) ? ' LIMIT 1' : '';

		// Prepare the query
		$this->_query = $this->_pdo->prepare($sql);

		// If the query is successfull
		if($this->_query->execute($params)){
			// Fetch the results of the query and put them in $_results
			$this->_results = $this->_query->fetchAll(PDO::FETCH_OBJ);

			// Set the rowcount of the query to the variable
			$this->_count = $this->_query->rowCount();
		}else{
			// Else set the error to true
			$this->_error = true;
		}
		// Return the statement
		return $this;
	}

	/**
	 * @since  1.0
	 *
	 * Get data from a table
	 * @param  string  $table   The table to get data from
	 * @param  string  $where   The condition, may only be one
	 * @param  string  $what    The rows to select
	 * @param  bool    $limit   An optional limit to the query
	 * @return Database
	 */
	public function get($table, $where = '1 = 1', $what = "*", $limit = false){
		// Create an array from the where argument
		$where = explode(' ', $where);

		// Create an empty params array
		$params = array();

		// Add the variable part in where to the params array
		$params[] = $where[2];

		// Replace the variable part in where with a question mark
		$where[2] = '?';

		// Create a string from the condition
		// with the variable replaced by a question mark
		$where = implode(" ", $where);

		// Assemble the query from the variables
		$sql = "SELECT {$what} FROM {$table} WHERE {$where}";

		// Return the result of the query function
		return $this->_query($sql, $params, $limit);
	}

	/**
	 * @since  1.0
	 *
	 * Update a row in a table
	 * @param  string      $table  The table to use
	 * @param  int|string  $where  The row to update
	 * @param  array       $values The values to insert
	 * @return Database
	 */
	public function update($table, $where, $values){
		// If a number is given, add 'id = ' to the condition
		if(is_numeric($where)) $where = 'id = '.$where;

		// Create an array from the where argument
		$where = explode(' ', $where);

		// Create $set from the array keys given in the argument $values
		$set = implode(' = ?, ', array_keys($values))." = ?";

		// Grab the values of the $params argument
		$params = array_values($values);

		// Add the variable part in where to the params array
		$params[] = $where[2];

		// Replace the variable part in where with a question mark
		$where[2] = '?';

		// Create a string from the condition
		// with the variable replaced by a question mark
		$where = implode(' ', $where);

		// Assemble the query from the variables
		$sql = "UPDATE {$table} SET {$set} WHERE {$where}";

		// Return the result of the query function
		return $this->_query($sql, $params);
	}

	/**
	 * @since  1.0
	 *
	 * Insert data into the database
	 * @param  string  $table  The table to use
	 * @param  array   $data   The data to insert
	 * @return Database
	 */
	public function insert($table, array $data){
		// Put a comma between the array keys form $data
		$keys = implode(', ', array_keys($data));

		// Create $values containing count($data) question marks
		$values = substr(str_repeat('?, ', count($data)), 0, -2);

		// Create $params form the values of $data
		$params = array_values($data);

		// Assemble the query from the variables
		$sql = "INSERT INTO {$table} ({$keys}) VALUES ({$values})";

		// Return the result of the query function
		return $this->_query($sql, $params);
	}

	/**
	 * @since  1.0
	 *
	 * Delete a row from the database
	 * @param  string      $table  The table to use
	 * @param  int|string  $where  What to delete
	 * @return Database
	 */
	public function delete($table, $where){
		// If a number is given, add 'id = ' to the condition
		if(is_numeric($where)) $where = "id = {$where}";

		// Create an array from the where argument
		$where = explode(' ', $where);

		// Create an empty array $params
		$params = array();

		// Add the variable part in where to the params array
		$params[] = $where[2];

		// Replace the variable part in where with a question mark
		$where[2] = '?';

		// Create a string from the condition
		// with the variable replaced by a question mark
		$where = implode(' ', $where);

		// Assemble the query from the variables
		$sql = "DELETE FROM {$table} WHERE {$where}";

		// Return the result of the query function
		return $this->_query($sql, $params);
	}

	/**
	 * @since  1.0
	 *
	 * Give the results of the query
	 * @return array      The result of the previous query
	 */
	public function result(){
		// Return the private results array
		return $this->_results;
	}

	/**
	 * @since  1.0
	 *
	 * The rowcount of the previous query
	 * @return int        Rowcount
	 */
	public function count(){
		// Return the rowcount
		return $this->_count;
	}

	/**
	 * @since  1.0
	 *
	 * Returns true if any errors
	 * @return bool      Error state
	 */
	public function error(){
		// Return the value of the private $_error
		return $this->_error;
	}

	/**
	 * @since  1.0
	 *
	 * Returns the first result
	 * @return object    The first result
	 */
	public function first(){
		// Grab the first from the result array
		return $this->_results[0];
	}

	/**
	 * @since  1.1
	 *
	 * Assign null to $_pdo
	 */
	public function __destruct(){
		// Assign null to $_pdo
		$this->_pdo = null;
		return;
	}
}
