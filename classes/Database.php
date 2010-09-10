<?php
/*
  ==============================================================================================
  Askably 3.1 © 2005-2010 Amelie F.
  ==============================================================================================
*/

################################################################################################
############################ CORE ASKABLY FUNCTIONS. DO _NOT_ EDIT. ############################
################################################################################################

if (!defined('PAI_IN')) exit('<p>This file cannot be loaded directly.</p>');

/**
 * Database class.
 */
class Database {

	/**
	 * The table.
	 *
	 * @var string The table.
	 */
	private $table;

	/**
	 * The connection.
	 *
	 * @var resource The connection.
	 */
	private $connect;

	/**
	 * Constructor.
	 *
	 * @param string $mysqlhost MySQL host.
	 * @param string $mysqluser MySQL user.
	 * @param string $mysqlpass Password for MySQL user.
	 * @param string $mysqldb Database to use.
	 */
	public function __construct($mysqlhost, $mysqluser, $mysqlpass, $mysqldb) {
		$this->connect = mysql_connect($mysqlhost, $mysqluser, $mysqlpass);
		if ($this->connect == false) exit('Error connecting to MySQL - please verify your connection details in config.php.');
		if (mysql_select_db($mysqldb) == false) exit('Error accessing MySQL database - please verify your connection details in config.php.');
		mysql_query("SET names 'utf8'", $this->connect) or exit('Could not execute query ' . mysql_error()); // TODO: DEBUG ONLY REMOVE
		$this->table = PAI_TABLE;
	}

	/**
	  * Getter for connection.
	  *
	  * @return resource The connection.
	  */
	public function getConnection() {
		return $this->connect;
	}

	/**
	 * Query wrapper.
	 *
	 * @param string $query The query to execute.
	 * @return resource The result.
	 */
	public function query($query) {
		if ($this->connect == false) {
			$error = new Error('Error: could not connect to MySQL. Your server may be temporarily unavailable; please try again later.');
			$error->display();
		}
		$result = mysql_query($query, $this->connect);
		if ($result == false) {
			echo mysql_error();
			return false;
		}
		else return $result;
	}

	/**
	 * Get a value from the db.
	 *
	 * @param string $field The field to get.
	 * @param string $table The table to get the value from.
	 * @param string $where Any where conditions for the query.
	 * @return string The value.
	 */
	public function get($field, $table, $where = '') {
		if ($table == 'main') $table = $this->getTable();
		else $table = $this->getTable() . '_' . $table;
		$query = 'SELECT `' . $field . '` FROM `' . $table . '`';

		if (!empty($where)) $query .= ' WHERE ' . $where;
		$query .= ' LIMIT 1';
		$result = $this->query($query);

		if (mysql_num_rows($result) > 0) {
			$value = mysql_fetch_object($result);
			if ($value == true) return $value->$field;
			else return false;
		}
		else return false;
	}

	/**
	 * Get the DB table.
	 *
	 * @return string The table.
	 */
	public function getTable() {
		return $this->table;
	}
}
?>