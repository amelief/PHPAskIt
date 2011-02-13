<?php
/*
  ==============================================================================================
  Askably 3.1 © 2005-2011 Amelie F.
  ==============================================================================================
*/

################################################################################################
############################ CORE ASKABLY FUNCTIONS. DO _NOT_ EDIT. ############################
################################################################################################

if (!defined('PAI_IN')) exit('<p>This file cannot be loaded directly.</p>');

/**
 * Importer class.
 */
class Importer {
	private $from;
	private $path;

	public function __construct($from, $path = '') {
		global $pai_db;
		$this->from = (in_array($from, array('aa', 'waks', 'faq', 'none')) ? $from : '');

		$this->path = cleaninput($path);

		if ($this->from != 'none') {
			$this->checkPath();
			if (substr($this->path, -1, 1) != '/' && !empty($this->path)) $this->path .= '/';
		}

		switch($this->from) {
			case 'aa':
				$this->importFromAA();
				break;
			case 'waks':
				$this->importFromWaks();
				break;
			case 'faq':
				$this->importFromFaqtastic();
				break;
			case 'none':
				$this->importFromManual();
				break;
			default:
				Error::showMessage('Invalid import type, please try again.');
		}
	}

	private function checkPath() {
		if (empty($this->path)) Error::showMessage('Missing absolute path, please go back and fill in this field.');
		if (strstr($this->path , 'http://')) Error::showMessage('Sorry, you can only use absolute paths for your ' . $this->getScriptName() . ' directory, URLs are not allowed. Please go back and enter an absolute path.');
	}

	private function getScriptName() {
		switch($this->from) {
			case 'aa':
				return 'Ask&amp;Answer';
				break;
			case 'waks':
				return "Wak's Ask&amp;Answer";
				break;
			case 'faq':
				return 'Faqtastic';
				break;
			case 'none':
				return 'Manual import';
				break;
			default:
				return '';
		}
	}

	private function importFromAA() {
		global $pai_db;
		if ($this->from != 'aa') return false;

		echo '<h2>Importing from Ask&amp;Answer</h2>';

		if (file_exists($this->path . 'config.php')) include $this->path . 'config.php';
		else Error::showMessage('Ask&amp;Answer\'s <strong><code>config.php</code></strong> could not be found. Please make sure this file exists in the directory you have specified and try again.');

		$aaqs = array();

		$getqs = mysql_query('SELECT * FROM `' . $table . '`');

		if (mysql_num_rows($getqs) < 1) Error::showMessage('There are no questions in your Ask&amp;Answer database. No questions were imported.');

		while($qs = mysql_fetch_object($getqs)) {
			$aaqs[] = $qs->question . ',' . $qs->answer . ',' . $qs->ip;
		}
		mysql_close();

		$pai_db = new Database(PAI_HOST, PAI_USER, PAI_PASS, PAI_DB);

		$import = array();
		$c = new Category();

		foreach($aaqs as $question) {
			$q = explode(',', $question);
			$import[] = "'" . cleaninput($q[0]) . "', '" . cleaninput($q[1]) . "', '" . date('Y-m-d H:i:s') . "', " . $c->getDefault() . ", '" . cleaninput($q[2]) . "'";
		}

		$sql = 'INSERT INTO `' . $pai_db->getTable() . '` (`question`, `answer`, `dateasked`, `category`, `ip`) VALUES ';

		foreach($import as $question) {
			$sql .= '(' . $question . '),';
		}

		if (substr($sql, -1, 1) == ',') $sql = substr_replace($sql, '', -1, 1);

		echo '<p>Found ' . count($aaqs) . ' question(s).</p>
		<h3>Importing questions...</h3>';

		if ($pai_db->query($sql)) echo '<p>' . mysql_affected_rows($pai_db->getConnection()) . ' question(s) successfully imported.</p>';
		else echo '<p>Sorry, an error occured when importing your questions. Please check your database settings and try again.</p>';
	}

	private function importFromWaks() {
		global $pai_db;
		if ($this->from != 'waks') return false;

		echo '<h2>Importing from Wak\'s Ask&amp;Answer</h2>';

		if (file_exists($this->path . 'functions.php')) @include $this->path . 'functions.php';
		else Error::showMessage('Wak\'s Ask&amp;Answer\'s <strong><code>functions.php</code></strong> could not be found. Please make sure this file exists in your Wak\'s Ask&amp;Answer directory.');

		echo '<p>Found ' . count(getTotalAnswered()) . ' answered question(s).</p>';
		echo '<h3>Importing questions...</h3>' . "\n\n<p>";

		$c = new Category();

		foreach(getTotalAnswered() as $waksquestions) {
			list($id, $question, $answer, $ip, $dateasked, $dateanswered) = get($waksquestions);

			$id = cleaninput($id);
			$question = cleaninput($question);
			$answer = cleaninput($answer);
			$ip = cleaninput($ip);

			if (empty($id) && empty($question) && empty($answer) && empty($ip)) continue; // Stops blank lines going in as Qs

			$answer = str_replace('(br)', '<br>', $answer);

			$dateasked = date('Y-m-d H:i:s', $dateasked);

			$pai_db->query('INSERT INTO `' . $pai_db->getTable() . "` VALUES ('', '" . $question . "', '" . $answer . "', " . $c->getDefault() . ", '" . $dateasked . "', '" . $ip . "');") or exit('Couldn\'t add question #' . $id . ' to the database. Stopping import process.');
			echo 'Added question #' . $id . '...<br>';
		}
		echo '<br>All answered questions found were imported.</p>';
	}

	private function importFromFaqtastic() {
		global $pai_db;
		if ($this->from != 'faq') return false;

		echo '<h2>Importing from Faqtastic</h2>';

		if (file_exists($this->path . 'config.php')) @include $this->path . 'config.php';
		else Error::showMessage('FAQtastic\'s <strong><code>config.php</code></strong> could not be found. Please make sure this file exists in the directory you have specified and try again.');

		$faqtasticqs = array();

		$getqs = mysql_query('SELECT * FROM `' . $tablefaqs . '`', $db);

		if (mysql_num_rows($getqs) < 1) Error::showMessage('<p>There are no questions in your FAQtastic database. No questions were imported.</p>');

		while($qs = mysql_fetch_object($getqs)) {
			$faqtasticqs[] = $qs->question . ',' . $qs->answer . ',' . $qs->ip;
		}

		mysql_close($db);
		unset($db);

		$pai_db = new Database(PAI_HOST, PAI_USER, PAI_PASS, PAI_DB);

		$import = array();
		$c = new Category;

		foreach($faqtasticqs as $question) {
			$q = explode(',', $question);
			$import[] = "'" . cleaninput($q[0]) . "', '" . cleaninput($q[1]) . "', '" . date('Y-m-d H:i:s') . "', " . $c->getDefault() . ", '" . cleaninput($q[2]) . "'";
		}

		$sql = 'INSERT INTO `' . $pai_db->getTable() . '` (`question`, `answer`, `dateasked`, `category`, `ip`) VALUES ';

		foreach($import as $question) {
			$sql .= '(' . $question . '),';
		}

		if (substr($sql, -1, 1) == ',') $sql = substr_replace($sql, '', -1, 1);

		echo '<p>Found ' . count($faqtasticqs) . ' question(s).</p>
		<h3>Importing questions...</h3>';

		if ($pai_db->query($sql)) echo '<p>' . mysql_affected_rows($pai_db->getConnection()) . ' question(s) successfully imported.</p>';
		else echo '<p>Sorry, an error occured when importing your questions. Please check your database settings and try again.</p>';
	}

	private function importFromManual() {
		global $pai_db;
		if ($this->from != 'none') return false;

		echo '<h2>Manual import</h2>';

		if (!array_key_exists('importme', $_POST) || empty($_POST['importme'])) Error::showMessage('No questions entered, please enter some and try again.');

		$c = new Category;
		$sql = 'INSERT INTO `' . $pai_db->getTable() . '` VALUES ';

		$questions = explode("\n", $_POST['importme']);
		$i = 1;
		foreach($questions as $q) {
			$q = trim($q, "\r");
			if (empty($q) || !preg_match('/^(.*)\|\|(.*)?$/', $q)) Error::showMessage('Invalid question format on line ' . $i . ': questions could not be imported. Please make sure each question is on a new line, separated from its answer with \'||\' and that there are no blank lines.');

			$qa = explode('||', $q);
			$sql .= "('', '" . cleaninput($qa[0]) . "', '" . cleaninput($qa[1]) . "', " . $c->getDefault() . ", NOW(), '" . cleaninput($_SERVER['REMOTE_ADDR']) . "'),";
			$i++;
		}
		if (substr($sql, -1, 1) == ',') $sql = substr_replace($sql, '', -1, 1);

		if ($pai_db->query($sql)) echo '<p>' . mysql_affected_rows($pai_db->getConnection()) . ' question(s) were successfully imported into the database.</p>';
		else Error::showMessage('An error occurred while importing your questions. Please check your question syntax and database settings, then try again.');
	}
} ?>