<?php
/*
  ==============================================================================================
  Askably 3.1 © 2005-2009 Amelie M.
  ==============================================================================================
*/

################################################################################################
############################ CORE ASKABLY FUNCTIONS. DO _NOT_ EDIT. ############################
################################################################################################

if (!defined('PAI_IN')) exit('<p>This file cannot be loaded directly.</p>');
require_once 'Model.php';

/**
 * Question class.
 */
class Question implements Model {

	/**
	* The question.
	*
	* @var string The question
	*/
	private $question = '';

	/**
	 * The answer.
	 *
	 * @var string The answer
	 */
	private $answer = '';

	/**
	 * The category ID.
	 *
	 * @var int The category ID
	 */
	private $category = 1;

	/**
	 * The question ID.
	 *
	 * @var int The question ID.
	 */
	private $id = 0;

	/**
	 * The date the question was asked.
	 *
	 * @var string The date the question was asked.
	 */
	private $dateAsked;

	/**
	 * The IP address of the question asker.
	 *
	 * @var string The IP address of the question asker.
	 */
	private $ip;

	/**
	 * Constructor.
	 *
	 * @param int $id The ID of the question to get.
	 */
	public function __construct($id = null) {
		if ($id != null) {
			if (!$this->findById((int)$id)) {
				$this->question = '';
				$this->answer = '';
				$this->dateAsked = time();
				$this->ip = null;
				$this->category = 1;
			}
		}
		else {
			$this->dateAsked = time();
		}
	}

	/**
	 * Create the question.
	 *
	 * @global Database $pai_db
	 * @return boolean Whether or not the database insertion was successful.
	 */
	public function create() {
		global $pai_db;
		if (empty($this->question) || empty($this->category) || empty($this->ip)) {
			echo '<p>Error, missing parameter</p>';
			return false;
		}
		if ($pai_db->query('INSERT INTO `' . $pai_db->getTable() . "` (`question`, `category`, `dateasked`, `ip`) VALUES ('" . $this->question . "', '" . $this->category . "', NOW(), '" . $this->ip . "')")) {
			$this->setId(mysql_insert_id($pai_db->getConnection()));
			return true;
		}
		else {
			echo '<p>Your question could not be added to the database at this time. Please try again later.</p>';
			return false;
		}
	}

	/**
	 * Save the question.
	 *
	 * @global Database $pai_db The database object.
	 * @return boolean Whether or not the database update was successful.
	 */
	public function save() {
		global $pai_db;
		if (!$this->checkId()) return false;
		if (empty($this->question) || empty($this->category) || empty($this->ip) || empty($this->dateAsked)) return false;
		return $pai_db->query('UPDATE `' . $pai_db->getTable() . "` SET `question` = '" . $this->sqlEscape($this->question) . "', `answer` = '" . $this->sqlEscape($this->answer) . "', `category` = " . $this->sqlEscape($this->category) . ' WHERE `q_id` = ' . $this->sqlEscape($this->id) . ' LIMIT 1');
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
	 * Delete the entire question.
	 *
	 * @global Database $pai_db The database object.
	 * @return boolean Whether or not the deletion succeeded.
	 */
	public function delete() {
		global $pai_db;
		if (!$this->checkId()) return false;
		return $pai_db->query('DELETE FROM `' . $pai_db->getTable() . '` WHERE `q_id` = ' . $this->id . ' LIMIT 1');
	}

	/**
	 * Check the ID of the question - if null, not a valid question.
	 *
	 * @return int The ID OR return false if null.
	 */
	public function checkId() {
		if ($this->id == null || $this->id == 0) return false;
		else return $this->id;
	}

	/**
	 * Getter for ID.
	 *
	 * @return int The ID.
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Setter for ID.
	 *
	 * @param int $id The ID to set.
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * Get the question details.
	 *
	 * @return Array The question.
	 */
	public function getTheQuestion() {
		return array('id' => ($this->checkId() ? $this->checkId() : null), 'question' => $this->question, 'answer' => $this->answer, 'category' => $this->category);
	}

	/**
	 * Getter for question.
	 *
	 * @return string The question.
	 */
	public function getQuestion() {
		return $this->question;
	}

	/**
	 * Setter for question.
	 *
	 * @param string $question The question to set.
	 * @param boolean $validation Whether to validate the question.
	 */
	public function setQuestion($question, $validation = true) {
		if ($validation) {
			$this->question = cleaninput($question);
			if (empty($this->question)) $error = new Error('Question not submitted');
			if (isset($error)) $error->display();
		}
		else $this->question = $question;
	}

	/**
	 * Getter for answer.
	 *
	 * @return string The answer.
	 */
	public function getAnswer() {
		return $this->answer;
	}

	/**
	 * Setter for answer.
	 *
	 * @param string $answer The answer to set.
	 * @param boolean $validation Whether to validate the answer.
	 */
	public function setAnswer($answer, $validation = true) {
		if ($validation) {
			$answer = cleaninput($answer);
			if (strstr($answer, '\\n')) $answer = str_replace('\\n', "\n", $answer);
			if (strstr($answer, '\\r')) $answer = str_replace('\\r', "\r", $answer);
			$this->answer = stripslashes(nl2br($answer));
		}
		else $this->answer = $answer;
	}

	/**
	 * Getter for category.
	 *
	 * @param boolean $byname Whether to get the category by name.
	 * @return mixed The category ID or the category name.
	 */
	public function getCategory($byname = false) {
		$cat = new Category($this->category);
		if (!$cat->checkId()) return false;
		if ($byname) return $cat->getName();
		else return $cat->getId();
	}

	/**
	 * Setter for category.
	 *
	 * @global Database $pai_db The database object.
	 * @param int $category The category to set.
	 */

	public function setCategory($category) {
		$cat = new Category($category);
		$this->category = ((empty($category) || !is_numeric($category) || !$cat->checkId()) ? 1 : (int)$category);
	}

	/**
	 * Getter for date asked.
	 *
	 * @return string The date.
	 */
	public function getDateAsked() {
		return $this->dateAsked;
	}

	/**
	 * Get formatted date.
	 *
	 * @global PAI $pai PAI object.
	 * @return string The formatted date.
	 */
	public function getDateAskedFormatted() {
		global $pai;
		return date($pai->getOption('date_format'), $this->dateAsked);
	}

	/**
	 * Setter for date asked.
	 *
	 * @param string $timestamp The timestamp to set.
	 */
	public function setDateAsked($timestamp) {
		if (strstr($timestamp, '-')) $this->dateAsked = strtotime($timestamp);
		else $this->dateAsked = (is_numeric($timestamp) ? $timestamp : time());
	}

	/**
	 * Getter for IP.
	 *
	 * @return string The IP.
	 */
	public function getIp() {
		return $this->ip;
	}

	/**
	 * Setter for IP.
	 *
	 * @param string $ip The IP to set.
	 */
	public function setIp($ip = null) {
		if ($ip != null && preg_match('/[0-9\.]+/', $ip)) $this->ip = $ip;
		else $this->ip = preg_replace('/[^0-9\.]+/', '', $_SERVER['REMOTE_ADDR']);
	}

	/**
	 * Get a question from the db by its ID.
	 *
	 * @global Database $pai_db The database object.
	 * @param int $id The ID of the question to get.
	 * @param string $fields The fields to get. Default *.
	 * @return boolean Whether the operation was successful or not.
	 */
	public function findById($id, $fields = '*') {
		global $pai_db;
		if ($id == null || !is_numeric($id)) return false;
		$query = $pai_db->query('SELECT ' . $fields . ' FROM `' . $pai_db->getTable() . '` WHERE `q_id` = ' . (int)$id . ' LIMIT 1');
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
	 * Set info from db to object model.
	 *
	 * @param object $info The info to set.
	 */
	public function setAll($info) {
		if ((function_exists('property_exists') && property_exists($info, 'q_id')) || isset($info->q_id)) $this->setId($info->q_id);
		if ((function_exists('property_exists') && property_exists($info, 'question')) || isset($info->question)) $this->setQuestion($info->question, false);
		if ((function_exists('property_exists') && property_exists($info, 'answer')) || isset($info->answer)) $this->setAnswer($info->answer, false);
		if ((function_exists('property_exists') && property_exists($info, 'category')) || isset($info->category)) $this->setCategory($info->category);
		if ((function_exists('property_exists') && property_exists($info, 'dateasked')) || isset($info->dateasked)) $this->setDateAsked($info->dateasked);
		if ((function_exists('property_exists') && property_exists($info, 'ip')) || isset($info->ip)) $this->setIp($info->ip);
	}

	/**
	 * Edit the question.
	 *
	 * @global PAI $pai The PAI object.
	 * @global string $token The session token.
	 */
	public function editQuestion() {
		global $pai, $token;
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && (array_key_exists('id', $_POST) && !empty($_POST['id']))) {
			$pai->checkToken();
			foreach($_POST as $key => $value) {
				$$key = cleaninput($value);
				if (empty($value)) {
					if (array_key_exists('inline', $_GET)) {
						ob_end_clean();
						exit('<strong>Error saving question:</strong><br />Missing parameter: ' . $key);
					}
					else {
						ob_end_flush();
						$error = new Error('Missing parameter: ' . $key);
						$error->display();
					}
				}
			}

			if (!$this->checkId()) {
				if (array_key_exists('inline', $_GET)) {
					ob_end_clean();
					exit('<strong>Error saving question:</strong><br />Invalid question ID.');
				}
				else {
					ob_end_flush();
					$error = new Error('Invalid question ID.');
					$error->display();
				}
			}

			$this->setQuestion($question, false);

			if ($this->save()) {
				if (array_key_exists('inline', $_GET)) {
					ob_end_clean();
					echo '<a href="admin.php?q=' . (int)$id . '" title="Permalink to this question">' . stripslashes(strip_tags($question)) . '</a>';
				}
				else {
					ob_end_flush();
					echo '<p>Question modified.</p>';
				}
			}
			else {
				if (array_key_exists('inline', $_GET)) {
					ob_end_clean();
					exit('<strong>Error modifying question:</strong><br />Could not contact database.');
				}
				else {
					ob_end_flush();
					$error = new Error('Could not modify question.');
					$error->display();
				}
			}
		}
		else {
			if (!array_key_exists('qu', $_GET) || (empty($_GET['qu']) || !is_numeric($_GET['qu']))) {
				ob_end_flush();
				$error = new Error('Invalid question.');
				$error->display();
			}

			if (!$this->checkId()) {
				ob_end_flush();
				$error = new Error('Invalid question.');
				$error->display();
			}

			$pai->checkToken();
			ob_end_flush(); ?>

			<h2>Editing question #<?php echo $this->getId(); ?></h2>

			<p>Original question: <strong>&quot;<?php echo $this->getQuestion(); ?>&quot;</strong></p>
			<p>Asked by <?php echo $this->getIp(); ?> on <?php echo $this->getDateAskedFormatted(); ?></p>

			<form method="post" action="admin.php?edit=question">
				<p><input type="hidden" name="id" id="id" value="<?php echo $this->getId(); ?>" />
				<input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
				<label for="question">Question:</label><br />
				<textarea rows="5" cols="45" name="question" id="question"><?php echo $this->getQuestion(); ?></textarea><br />
				<input type="submit" name="submit_question" id="submit_question" value="Edit question" /></p>
			</form>

			<?php
		}
	}

	/**
	 * Edit the answer.
	 *
	 * @global PAI $pai The PAI object.
	 * @global string $token The session token.
	 */
	public function editAnswer() {
		global $pai, $token;
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && (array_key_exists('id', $_POST) && !empty($_POST['id']))) {
			$pai->checkToken();

			foreach($_POST as $key => $value) {
				$$key = cleaninput($value);
				if ($key != 'answer' && empty($value)) {
					if (array_key_exists('inline', $_GET)) {
						ob_end_clean();
						exit('<strong>Error saving answer:</strong><br />Missing parameter: ' . $key);
					}
					else {
						ob_end_flush();
						$error = new Error('Missing parameter: ' . $key);
						$error->display();
					}
				}
			}

			if (!$this->checkId()) {
				if (array_key_exists('inline', $_GET)) {
					ob_end_clean();
					exit('<strong>Error saving answer:</strong><br />Invalid question ID.');
				}
				else {
					ob_end_flush();
					$error = new Error('Invalid question ID.');
					$error->display();
				}
			}

			$answer = str_replace("\\r", "\r", $answer);
			$answer = str_replace("\\n", "\n", $answer);

			$this->setAnswer(nl2br($answer), false);

			if ($this->save()) {
				if (array_key_exists('inline', $_GET)) {
					ob_end_clean();
					if (!empty($answer)) echo $pai->convertBB(stripslashes(nl2br($answer)));
					else echo '(No answer)';
				}
				else {
					ob_end_flush();
					echo '<p>Your answer has been saved.</p>';
				}
			}
			else {
				if (array_key_exists('inline', $_GET)) {
					ob_end_clean();
					exit('<strong>Error saving answer:</strong><br />Could not contact database.');
				}
				else {
					ob_end_flush();
					$error = new Error('Could not save answer.');
					$error->display();
				}
			}
		}
		else {
			if (!array_key_exists('qu', $_GET) || (empty($_GET['qu']) || !is_numeric($_GET['qu']))) {
				ob_end_flush();
				$error = new Error('Invalid question.');
				$error->display();
			}

			if (!$this->checkId()) {
				ob_end_flush();
				$error = new Error('Invalid question');
				$error->display();
			}

			$pai->checkToken();
			ob_end_flush(); ?>

			<h2>Answering question #<?php echo $this->getId(); ?>: &quot;<?php echo $this->getQuestion(); ?>&quot;</h2>

			<p>Asked by <?php echo $this->getIp(); ?> on <?php echo $this->getDateAskedFormatted(); ?> </p>

			<form method="post" action="admin.php?edit=answer">
				<p><input type="hidden" name="id" id="id" value="<?php echo $this->getId(); ?>" />
				<input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
				Answer this question:<br />
				<textarea rows="5" cols="45" name="answer" id="answer"><?php echo strip_tags($this->getAnswer()); ?></textarea><br />
				<input type="submit" name="submit_answer" id="submit_answer" value="Answer" /></p>
			</form>
			<?php
		}
	}

	/**
	 * Edit the category.
	 *
	 * @global PAI $pai The PAI object.
	 * @global string $token The session token.
	 * @global Database $pai_db The database object.
	 */
	public function editCategory() {
		global $pai, $token, $pai_db;
		if ($pai->getOption('enable_cats') != 'yes') {
			if (array_key_exists('inline', $_GET)) {
				ob_end_clean();
				exit('<strong>Error:</strong> Categories are disabled.');
			}
			else {
				ob_end_flush();
				$error = new Error('Categories are disabled.');
				$error->display();
			}
		}
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && array_key_exists('submit_category', $_POST)) {
			$pai->checkToken();

			foreach($_POST as $key => $value) {
				$$key = cleaninput($value);
				if (empty($value)) {
					if (array_key_exists('inline', $_GET)) {
						ob_end_clean();
						exit('<strong>Error saving category:</strong> Missing parameter: ' . $key);
					}
					else {
						ob_end_flush();
						$error = new Error('Missing parameter: ' . $key);
						$error->display();
					}
				}
			}

			if (!$this->checkId()) {
				if (array_key_exists('inline', $_GET)) {
					ob_end_clean();
					exit('<strong>Error:</strong> Invalid question');
				}
				else {
					ob_end_flush();
					$error = new Error('Invalid question.');
					$error->display();
				}
			}
			$cat = new Category((int)$category);
			if (!$cat->checkId()) {
				if (array_key_exists('inline', $_GET)) {
					ob_end_clean();
					exit('<strong>Error:</strong> Invalid category.');
				}
				else {
					ob_end_flush();
					$error = new Error('Invalid category.');
					$error->display();
				}
			}

			$this->setCategory($cat->getId());
			if ($this->save()) {
				if (array_key_exists('inline', $_GET)) {
					ob_end_clean();
					echo '(<a href="admin.php?category=' . $cat->getId() . '" title="See all questions in the ' . $cat->getName() . ' category">' . $cat->getName() . '</a>)';
				}
				else {
					ob_end_flush();
					echo '<p>The category for this question has been successfully modified.</p>';
				}
			}
			else {
				if (array_key_exists('inline', $_GET)) {
					ob_end_clean();
					exit('<strong>Error:</strong> Could not save category.');
				}
				else {
					ob_end_flush();
					$error = new Error('Could not save category.');
					$error->display();
				}
			}
		}
		else {
			if (!array_key_exists('qu', $_GET) || (empty($_GET['qu']) || !is_numeric($_GET['qu']))) $error = new Error('Invalid question.');

			if (!$this->getId()) {
				$error = new Error('Invalid question.');
				$error->display();
			}
			$pai->checkToken(); ?>

			<h2>Editing category of question #<?php echo $this->getId(); ?></h2>

			<p><strong>&quot;<?php echo $this->getQuestion(); ?>&quot;</strong></p>
			<p>Asked by <?php echo $this->getIp(); ?> on <?php echo $this->getDateAskedFormatted(); ?></p>

			<p>Current category: <?php echo $this->getCategory(true); ?></p>

			<form method="post" action="admin.php?edit=category">
				<p><input type="hidden" name="id" id="id" value="<?php echo $this->getId(); ?>" />
				<input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
				<select name="category" id="category">
					<?php $pai->getCategories($this->getCategory()); ?>
				</select><br />
				<input type="submit" name="submit_category" id="submit_category" value="Edit question" /></p>
			</form>
			<?php
		}
	}
}
?>