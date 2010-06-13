<?php
/*
  ==============================================================================================
  Askably 3.1 © 2005-2010 Amelie M.
  ==============================================================================================
*/

################################################################################################
############################ CORE ASKABLY FUNCTIONS. DO _NOT_ EDIT. ############################
################################################################################################

if (!defined('PAI_IN')) exit('<p>This file cannot be loaded directly.</p>');
require_once 'Model.php';

/**
 * Category class.
 */
class Category implements Model {
	/**
	 * The name of the category.
	 * 
	 * @var string The name of the category.
	 */
	private $name;
	 
	/**
	 * The ID of the category.
	 * 
	 * @var int The ID of the category.
	 */
	private $id = 0;

	/**
	 * Whether the category is the default or not.
	 *
	 * @var boolean Whether the category is the default or not.
	 */
	private $default = false;

	/**
	 * Constructor.
	 * 
	 * @param int $id The ID of the category to get.
	 */
	public function __construct($id = null) {
		if ($id != null) {
			if (!$this->findById((int)$id)) $this->name = '';
		}
		else $this->name = '';
	 }
	/**
	 * Is this category the default or not?
	 * 
	 * @return boolean Get the status of the default variable.
	 */
	public function isDefault() {
		return ($this->default == 1 ? true : false);
	}

	/**
	 * Set the default flag.
	 *
	 * @param boolean $default The default value to set.
	 */
	public function setDefault($default, $validation = true) {
	 	$this->default = (bool)$default;
		if ($validation) {
			if ($this->getDefault()) {
	 			$d = new Category($this->getDefault());
				if ($d->getId() != $this->getId()) {
					 $d->setDefault(false, false);
					 $d->save();
				}
	 		}
		}
	}

	/**
	 * Get the name of the category.
	 * 
	 * @return string The name of the category.
	 */
	public function getName() {
		return $this->name;
	}
	 
	/**
	 * Get the ID of the category.
	 * 
	 * @return int The ID of the category.
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * Set the name of the category.
	 * 
	 * @param int $name The name to set.
	 */
	public function setName($name, $validation = false) {
		if ($validation) {
			$this->name = cleaninput($name);
			if (empty($this->name)) $error = new Error('Category name not submitted');
			if (isset($error)) $error->display();
		}
		else $this->name = $name;
	}
	 
	/**
	 * Set the ID of the category.
	 * 
	 * @param int $id The ID to set.
	 */
	public function setId($id) {
		$this->id = $id;
	}
	 
	/**
	 * Create the category.
	 *
	 * @global Database $pai_db
	 * @return boolean Whether or not the database insertion was successful.
	 */
	public function create() {
		global $pai_db;
		if (empty($this->name)) {
			echo '<p>Error: Please enter a name.</p>';
			return false;
		}
		if ($pai_db->query('INSERT INTO `' . $pai_db->getTable() . "_cats` (`cat_name`, `default`) VALUES ('" . $this->name . "', '" . $this->isDefault() . "')")) {
			$this->setId(mysql_insert_id($pai_db->getConnection()));
			return true;
		}
		else {
			echo '<p>The category could not be added to the database at this time. Please try again later.</p>';
			return false;
		}
	}

	/**
	 * Save the category.
	 *
	 * @global Database $pai_db The database object.
	 * @return boolean Whether or not the database update was successful.
	 */
	public function save() {
		global $pai_db;
		if (!$this->checkId()) return false;
		if (empty($this->name)) return false;
		return $pai_db->query('UPDATE `' . $pai_db->getTable() . "_cats` SET `cat_name` = '" . $this->sqlEscape($this->name) . "', `default` = '" . $this->sqlEscape($this->default) . "' WHERE `cat_id` = " . $this->sqlEscape($this->id) . ' LIMIT 1');
	}

	/**
	 * Escape SQL data for DB insertion.
	 *
	 * @global Database $pai_db The database object.
	 * @param string $data The data to escape.
	 * @return string The escaped data.
	 */
	public function sqlEscape($data) {
		global $pai_db;
		return mysql_real_escape_string(stripslashes($data), $pai_db->getConnection());
	}

	/**
	 * Delete the entire category. Move questions to default category.
	 *
	 * @global Database $pai_db The database object.
	 * @return boolean Whether or not the deletion succeeded.
	 */
	public function delete() {
		global $pai_db;
		if (!$this->checkId()) return false;
		if ($this->isDefault()) $error = new Error('You cannot delete the default category.');

		if ($pai_db->query('DELETE FROM `' . $pai_db->getTable() . '_cats` WHERE `cat_id` = ' . $this->id . ' LIMIT 1')) {
			// Move deleted questions to default category
			if ($pai_db->get('q_id', 'main', '`category` = ' . $this->id)) $pai_db->query('UPDATE `' . $pai_db->getTable() . '` SET `category` = ' . $this->getDefault() . ' WHERE `category` = ' . $this->id);
			echo '<p>The category was successfully deleted.</p>';
		}
	}

	/**
	 * Check the ID of the question - if null, not a valid category.
	 *
	 * @return mixed The ID OR return false if null.
	 */
	public function checkId() {
		if ($this->id == null || $this->id == 0) return false;
		else return $this->id;
	}

	/**
	 * Get the default category.
	 *
	 * @global Database $pai_db The Database object.
	 * @return int The default category ID.
	 */
	public function getDefault() {
		global $pai_db;
		$default = $pai_db->query('SELECT `cat_id` FROM `' . $pai_db->getTable() . '_cats` WHERE `default` = 1 LIMIT 1');
		if ($default == null || $default == false) return false;
		elseif (mysql_num_rows($default) == 1) {
			$d = mysql_fetch_object($default);
			return $d->cat_id;
		}
		else return false;
	}

	/**
	 * Get a category from the database by ID.
	 *
	 * @global Database $pai_db The database object.
	 * @param int $id The ID of the record to get.
	 * @param string $fields The fields to get.
	 * @return boolean Whether the operation succeeded or not.
	 */
	public function findById($id, $fields = '*') {
		global $pai_db;
		if ($id == null || !is_numeric($id)) return false;
		$query = $pai_db->query('SELECT ' . $fields . ' FROM `' . $pai_db->getTable() . '_cats` WHERE `cat_id` = ' . (int)$id . ' LIMIT 1');
		if ($query == true) {
			if (mysql_num_rows($query) > 0) {
				$info = mysql_fetch_object($query);
				$this->setAll($info);
				return true;
			}
			else return false;
		}
		else return false;
	}

	/**
	 * Set all properties.
	 *
	 * @param object $info The info to set.
	 */
	public function setAll($info) {
		if ((function_exists('property_exists') && property_exists($info, 'cat_id')) || isset($info->cat_id)) $this->setId($info->cat_id);
		if ((function_exists('property_exists') && property_exists($info, 'cat_name')) || isset($info->cat_name)) $this->setName($info->cat_name, false);
		if ((function_exists('property_exists') && property_exists($info, 'default')) || isset($info->default)) $this->setDefault($info->default, false);
	}

	/**
	 * Create a new category.
	 * @global PAI $pai The PAI object.
	 * @global string $token The session token.
	 * @global Database $pai_db The Database object.
	 */
	public function add() {
		global $pai, $token, $pai_db;
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['newcat'])) {
			$pai->checkToken();
			ob_end_flush();

			$this->setName(cleaninput($_POST['newcat']));

			if ($pai_db->get('cat_name', 'cats', "`cat_name` = '" . $_POST['newcat'] . "'")) {
				$error = new Error('You already have a category with that name.');
				$error->display();
			}
			if ($this->create()) echo '<p>The category has been added successfully.</p>';
		}
		else { ?>
			<h2>Add a new category</h2>

			<form method="post" action="admin.php?manage=categories&amp;action=add">
				<p><input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
				<label for="newcat">Category name:</label>
				<input type="text" name="newcat" id="newcat" maxlength="100" />
				<input type="submit" name="addcat" id="addcat" value="Add" /></p>
			</form>

			<?php
		}
	}

	/**
	 * Edit the category.
	 *
	 * @global PAI $pai The PAI object.
	 * @global Database $pai_db The Database object.
	 * @global string $token The session token.
	 */
	public function edit() {
		global $pai, $pai_db, $token;
		if (array_key_exists('catname', $_POST) && $_SERVER['REQUEST_METHOD'] == 'POST') {
			$pai->checkToken();
			ob_end_flush();

			foreach($_POST as $key => $value) {
				$$key = cleaninput($value);
				if (empty($$key)) {
					$error = new Error('Missing parameter: ' . $key);
					$error->display();
				}
			}
			if (!$this->checkId()) $error = new Error('Invalid category.');
			$this->setName($catname);

			if (isset($default) && is_numeric($default)) $this->setDefault((bool)$default);

			if ($pai_db->get('cat_name', 'cats', "`cat_name` = '" . strtolower($catname) . "' AND cat_id != " . $this->getId())) $error = new Error('You already have a category with that name.');
			if (isset($error)) $error->display();

			if ($this->save()) echo '<p>Category updated successfully.</p>';
		}
		else {
			if (!$this->checkId()) {
				$error = new Error('Invalid category.');
				$error->display();
			}
			$pai->checkToken();
			ob_end_flush();

			$_GET['id'] = (int)cleaninput($_GET['id']);
			if (empty($_GET['id'])) $error = new Error('Invalid category.'); ?>

			<h2>Edit your categories</h2>
			<p>Type the new category name below:</p>

			<?php
			$getcats = $pai_db->query('SELECT * FROM `' . $pai_db->getTable() . '_cats` WHERE `cat_id` = ' . $_GET['id'] . ' LIMIT 1');
			$theCat = mysql_fetch_object($getcats);
			$cat = new Category($theCat->cat_id); ?>

			<form id="categoryedit" method="post" action="admin.php?manage=categories&amp;action=edit">
				<p><input type="hidden" name="id" id="id" value="<?php echo $cat->getId(); ?>" />
				<input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
				<input type="text" name="catname" id="catname" value="<?php echo $cat->getName(); ?>" /></p>
				<p><input type="checkbox" name="default" id="default" value="1"<?php if ($cat->isDefault()) echo ' checked="checked"'; ?> /> Make default?</p>
				<p><input type="submit" name="submitedit" id="submitedit" value="Edit" /></p>
			</form>
			<?php
		}
	}
}
?>