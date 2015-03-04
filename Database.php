<?php
/**
 * Database
 * ============
 * A simple class to connect to your MySQL database.
 * The class uses prepared statements, so using it should be save.
 *
 * @version    1.1
 * @author     Sibren Talens   <sibrentalens@gmail.com>
 * @copyright  2014-2015       Sibren Talens
 * @license    Apache license  http://license.sibrentalens.com
 * @link       GitHub          https://github.com/SibrenTalens/database
 * @see        PDO Manual      http://php.net/manual/en/class.pdo.php
 */
class Database{
	/**
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
	 * @since 1.0
	 *
	 * Construct the database
	 * @param string    $dbname     The name of the database
	 * @param string    $username   The username
	 * @param string    $password   The password
	 * @param string    $host       The host, default is localhost (127.0.0.1)
	 * @param array     $options    An array with options, default is empty
	 */
	public function __construct($dbname, $username, $password, $host = '127.0.0.1', $options = []){
		try{
			// Attempt to connect to the database
			$this->_pdo = new PDO('mysql:host='.$host.';dbname='.$dbname, $username, $password, $options);
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
		static::$_error = false;

		// Add a limit of one to $sql if the argument is true
		$sql .= ($limit) ? ' LIMIT 1' : '';

		// Prepare the query
		static::$_query = static::$_pdo->prepare($sql);

		// If the query is successfull
		if(static::$_query->execute($params)){
			// Fetch the results of the query and put them in $_results
			static::$_results = static::$_query->fetchAll(PDO::FETCH_OBJ);

			// Set the rowcount of the query to the variable
			static::$_count = static::$_query->rowCount();
		}else{
			// Else set the error to true
			static::$_error = true;
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
	public function get($table, $where = '1 = 1', $what = '*', $limit = false){
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
		$where = implode(' ', $where);

		// Assemble the query from the variables
		$sql = "SELECT {$what} FROM {$table} WHERE {$where}";

		// Return the result of the query function
		return static::$_query($sql, $params, $limit);
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
		$set = implode(' = ?, ', array_keys($values)).' = ?';

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
		return static::$_query($sql, $params);
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
		return static::$_query($sql, $params);
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
		if(is_numeric($where)) $where = 'id = {$where}';

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
		return static::$_query($sql, $params);
	}

	/**
	 * Create, delete, or alter a table
	 * @param  string   $action  The action (create, delete, alter)
	 * @param  string   $name    The name of the table
	 * @param  array    $columns The columns
	 * @param  bool     $exists  If not exists
	 * @return Database          The object
	 */
	public function table($action, $name, $columns, $exists){
		//
	}

	/**
	 * @since  1.0
	 *
	 * Give the results of the query
	 * @return array      The result of the previous query
	 */
	public function result(){
		// Return the private results array
		return static::$_results;
	}

	/**
	 * @since  1.0
	 *
	 * The rowcount of the previous query
	 * @return int        Rowcount
	 */
	public function count(){
		// Return the rowcount
		return static::$_count;
	}

	/**
	 * @since  1.0
	 *
	 * Returns true if any errors
	 * @return bool      Error state
	 */
	public function error(){
		// Return the value of the private $_error
		return static::$_error;
	}

	/**
	 * @since  1.0
	 *
	 * Returns the first result
	 * @return object    The first result
	 */
	public function first(){
		// Grab the first from the result array
		return static::$_results[0];
	}

	/**
	 * @since  1.1
	 *
	 * Assign null to $_pdo
	 */
	public function __destruct(){
		// Assign null to $_pdo
		static::$_pdo = null;
		return;
	}
}
