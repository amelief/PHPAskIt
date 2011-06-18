<?php
/*
  ==============================================================================================
  This file is part of PHPAskIt 3.1, Copyright © 2005-2011 Amelie F.

  PHPAskIt is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  PHPAskIt is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this PHPAskIt.  If not, see <http://www.gnu.org/licenses/>.
  ==============================================================================================
*/

################################################################################################
############################ CORE PHPASKIT FUNCTIONS. DO _NOT_ EDIT. ############################
################################################################################################


if (!defined('PAI_IN')) exit('<p>This file cannot be loaded directly.</p>');

/**
 * PAI class.
 */
class PAI {

	/**
	 * Salt for md5.
	 *
	 * @var string Salt for md5.
	 */
	private $mask;

	/**
	 * The number of unanswered questions.
	 *
	 * @var int Number of unanswered questions.
	 */
	private $unanswered = 0;

	/**
	 * The number of answered questions.
	 *
	 * @var int Number of answered questions.
	 */
	private $answered = 0;

	/**
	 * The total number of answered questions.
	 *
	 * @var int Total number of questions.
	 */
	private $total = 0;

	/**
	 * The number of categories that questions have been asked in.
	 *
	 * @var int Number of categories.
	 */
	private $cats = 0;

	/**
	 * Options cache.
	 *
	 * @var array The options cache.
	 */
	private $options = array();

	/**
	 * Constructor.
	 *
	 * @global Database $pai_db The database object.
	 */
	public function __construct() {
		global $pai_db;
		// TODO: combine these
		$total = mysql_fetch_object($pai_db->query('SELECT COUNT(`q_id`) AS `num` FROM `' . $pai_db->getTable() . '`'));
		$this->total = $total->num;

		$unanswered = mysql_fetch_object($pai_db->query('SELECT COUNT(`q_id`) AS `num` FROM `' . $pai_db->getTable() . "` WHERE `answer` = '' OR `answer` IS NULL"));
		$this->unanswered = $unanswered->num;

		$this->answered = $this->total - $this->unanswered;

		$cats = mysql_fetch_object($pai_db->query('SELECT COUNT(`cat_id`) AS `num` FROM `' . $pai_db->getTable() . '_cats`'));
		$this->cats = $cats->num;

		$options = $pai_db->query('SELECT `option_name`, `option_value` FROM `' . $pai_db->getTable() . '_options`');
		while($get_options = mysql_fetch_object($options)) {
			$this->options[$get_options->option_name] = $get_options->option_value;
		}
		$this->mask = md5($this->options['security_word'] . PAI_SALT);
	}

	/**
	 * Rebuild options cache.
	 *
	 * @global Database $pai_db Database object.
	 */
	public function resetOptions() {
		global $pai_db;
		$options = $pai_db->query('SELECT `option_name`, `option_value` FROM `' . $pai_db->getTable() . '_options`');
		while($get_options = mysql_fetch_object($options)) {
			$this->options[$get_options->option_name] = $get_options->option_value;
		}
		$this->mask = md5($this->options['security_word'] . PAI_SALT);
	}

	/**
	 * Getter for mask.
	 *
	 * @return string The md5 salt.
	 */
	public function getMask() {
		return $this->mask;
	}

	/**
	 * Setter for mask.
	 *
	 * @param string $word The user's security word.
	 */
	public function setMask($word) {
		$this->mask = md5($word . PAI_SALT);
	}

	/**
	 * Get an option from the cache.
	 *
	 * @param string $option The option to get.
	 * @return mixed The option value or false if it is null.
	 */
	public function getOption($option) {
		if (array_key_exists($option, $this->options)) {
			if ($this->options[$option] == 'yes') return true;
			elseif ($this->options[$option] == 'no') return false;
			return $this->options[$option];
		}
		return false;
	}

	/**
	 * Getter for unanswered questions.
	 *
	 * @return int Unanswered questions.
	 */
	public function getUnanswered() {
		return $this->unanswered;
	}

	/**
	 * Setter for unanswered questions.
	 *
	 * @param int $unanswered The unanswered question amount to set.
	 */
	public function setUnanswered($unanswered) {
		$this->unanswered = $unanswered;
	}

	/**
	 * Getter for answered questions.
	 *
	 * @return int Answered questions.
	 */
	public function getAnswered() {
		return $this->answered;
	}

	/**
	 * Setter for answered questions.
	 *
	 * @param int $answered The answered question amount to set.
	 */
	public function setAnswered($answered) {
		$this->answered = $answered;
	}

	/**
	 * Setter for total questions.
	 *
	 * @param int $total The total amount of questions to set.
	 */
	public function setTotal($total) {
		$this->total = $total;
	}

	/**
	 * Getter for total questions.
	 *
	 * @return int The total number of questions.
	 */
	public function getTotal() {
		return $this->total;
	}

	/**
	 * Getter for number of categories.
	 *
	 * @return int The number of categories.
	 */
	public function getCats() {
		return $this->cats;
	}

	/**
	 * Setter for number of categories.
	 *
	 * @param int $cats The number of categories to set.
	 */
	public function setCats($cats) {
		$this->cats = $cats;
	}

	/**
	 * Magic happens here.
	 */
	public function adminLogout() {
		/* Oh noes, PAI did a funny */
	}

	/**
	 *
	 * @global Database $pai_db The database object.
	 * @param string $link Where to link back to, default index.php.
	 */
	public function askForm($link) {
		global $pai_db;
		if ($this->getOption('enable_cats') == 'yes') {
			$getcats = $pai_db->query('SELECT * FROM `' . $pai_db->getTable() . '_cats` ORDER BY `cat_name` ASC');
			if (mysql_num_rows($getcats) > 0) {
				$cat = '<select name="category" id="category">' . "\n" . '<option value="">CHOOSE ONE:</option>';
				while($cats = mysql_fetch_object($getcats)) {
					$cat .= "\n<option value=\"" . $cats->cat_id . '">' . $cats->cat_name . '</option>';
				}
				$cat .= '</select>';
			}
			else $cat = '';
		}
		else $cat = '';

		$sub = array('[[question]]', '[[category]]', '[[submit]]');
		$replace = array('<input type="text" name="question" id="question">', $cat, '<input type="submit" name="askit" id="askit" value="Ask">');
		?>

		<form class="pai-question-form" method="post" action="<?php echo cleaninput($link); ?>">
			<?php echo str_replace($sub, $replace, $this->getOption('ask_template')); ?>
		</form>
	<?php
	}

	/**
	 * Show the summary of questions.
	 *
	 * @global Database $pai_db The database object.
	 */
	public function showSummary() {
		global $pai_db;
		$summary = <<<HTML
<ul class="pai-summary">
	<li>View questions:
		<ul>
			<li><a href="?recent" title="Most recent">Most recent</a></li>
		</ul>
	</li>
	<li>By category:
		<ul>
HTML;
		$getcats = $pai_db->query('SELECT `' . $pai_db->getTable() . '_cats`.cat_id, COUNT(`' . $pai_db->getTable() . '`.`q_id`) AS `num` FROM `' . $pai_db->getTable() . '_cats` LEFT JOIN `' . $pai_db->getTable() . '` ON `' . $pai_db->getTable() . '_cats`.`cat_id` = `' . $pai_db->getTable() . '`.`category` GROUP BY `' . $pai_db->getTable() . '_cats`.`cat_id` ORDER BY `cat_name` ASC');

		while ($cat = mysql_fetch_object($getcats)) {
			$c = new Category($cat->cat_id);
			$summary .= <<<HTML
			<li><a href="?category={$c->getId()}" title="View questions in this category">{$c->getName()}</a> ({$cat->num})</li>
HTML;
		}
		$summary .= <<<HTML
		</ul>
	</li>
</ul>

<p>Total questions: <strong>{$this->getTotal()}</strong> ({$this->getUnanswered()} unanswered)</p>
HTML;
		echo $summary;
	}

	/**
	 * Get a selectable list of categories.
	 *
	 * @global Database $pai_db The database object.
	 * @param int $current_cat The currently selected category.
	 */
	public function getCategories($current_cat = '') {
		global $pai_db;
		$cats = $pai_db->query('SELECT * FROM `' . $pai_db->getTable() . '_cats`');
		while($cat = mysql_fetch_object($cats)) { ?>
			<option value="<?php echo $cat->cat_id; ?>"<?php if ($cat->cat_id == $current_cat) echo ' selected="selected"'; ?>><?php echo $cat->cat_name; ?></option>
			<?php
		}
	}

	/**
	 * Check the session token.
	 *
	 * @param boolean $post Is this a post request?
	 * @param boolean $get Is this a get request?
	 * @param boolean $inline Is this an AJAX request?
	 */
	public function checkToken() {
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if (!array_key_exists('token', $_POST)) $this->killToken();
			else $token = $_POST['token'];
		}
		elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
			if (!array_key_exists('token', $_GET)) $this->killToken();
			else $token = $_GET['token'];
		}
		if (!array_key_exists('pai_token', $_SESSION) || $token != $_SESSION['pai_token']) $this->killToken();
	}

	/**
	 * Clear session token.
	 *
	 * @param boolean $time Is this caused by a timeout or an invalid token?
	 */
	public function killToken($time = false) {
		global $pai_db;
		$_SESSION = array();
		if (array_key_exists(session_name(), $_COOKIE)) setcookie(session_name(), '', time()-3600, '/');
		@session_destroy();
		if (array_key_exists('inline', $_GET)) {
			if ($time) {
				ob_end_clean();
				exit('<strong>Error:</strong> Your session has expired. Please <a href="admin.php?message=expired">login again</a> to correct this problem.');
			}
			else {
				ob_end_clean();
				exit('<strong>Error:</strong> Your session has expired. Please refresh the page to correct this problem.');
			}
		}
		else {
			if ($time) {
				ob_end_clean();
				setcookie($pai_db->getTable() . '_user', '', time()-3600, '/');
				setcookie($pai_db->getTable() . '_pass', '', time()-3600, '/');

				header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/admin.php?message=expired');
				exit;
			}
			else Error::showMessage('Your session has expired. Please refresh the page to correct this problem.');
		}
	}

	/**
	 * Convert the existing bbCoded text to HTML.
	 *
	 * @param string $text The text to format.
	 * @return string The formatted text.
	 */
	public function convertBB($text) {
		$text = preg_replace('/\[code\](.*?)\[\/code\]/is', '<pre>$1</pre>', $text);
		$text = preg_replace('/\[b\](.*?)\[\/b\]/is', '<strong>$1</strong>', $text);
		$text = preg_replace('/\[u\](.*?)\[\/u\]/is', '<span style="text-decoration: underline;">$1</span>', $text);
		$text = preg_replace('/\[i\](.*?)\[\/i\]/is', '<em>$1</em>', $text);
		$text = preg_replace('/\[q=([0-9]+)\](.*?)\[\/q\]/is', '<a href="?q=$1">$2</a>', $text);

		$patterns[] = "#\[url=([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*?)\]([^?\r\t].*?)\[/url\]#is";
		$replacements[] = '<a href="$1" title="$2">$2</a>';

		$patterns[] = "#\[url=((www)\.[\w\#$%&~/.\-;:=,?@\[\]+]*?)\]([^?\n\r\t].*?)\[/url\]#is";
		$replacements[] = '<a href="http://$1" title="$3">$3</a>';

		$text = preg_replace($patterns, $replacements, $text);

		return $text;
	}

	/**
	 * Verify login details.
	 *
	 * @global string $token The session token.
	 * @global Database $pai_db The database object.
	 */
	public function doLogin() {
		global $token, $pai_db;
		if (array_key_exists('process', $_GET)) {
			switch($_GET['process']) {
				case 'login':
					if (array_key_exists('userlogon', $_POST) && array_key_exists('userpassword', $_POST) && !empty($_POST['userlogon']) && !empty($_POST['userpassword']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
						ob_start();
						adminheader();
						echo '<h1 id="header"><a href="admin.php" title="Back to main admin page">PHPAskIt</a></h1>

						<ul id="navigation" class="center">
							<li><a href="admin.php" class="active">Login</a></li>
							<li><a href="admin.php?process=reset" title="Reset password">Lost password</a></li>
						</ul>
						<div class="center">';

						if (!array_key_exists('token', $_POST)) Error::showMessage('Your session has expired. Please reload the page to correct this issue.');
						$this->checkToken();
						if (cleaninput($_POST['userlogon']) == $this->getOption('username') && md5(cleaninput($_POST['userpassword']) . $this->getMask()) == $this->getOption('password')) {
							setcookie($pai_db->getTable() . '_user', $this->getOption('username'), time()+(86400*365), '/');
							// TODO: Change this. Not secure.
							setcookie($pai_db->getTable() . '_pass', 'Loggedin_' . $this->getOption('password'), time()+(86400*365), '/');
							header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/admin.php');
							ob_end_flush();
							exit;
						}
						else {
							ob_end_clean();
							$this->displayLogin('Incorrect username or password. Please try again.');
						}
					}
					break;

				case 'logout':
					setcookie($pai_db->getTable() . '_user', '', time()-3600, '/');
					setcookie($pai_db->getTable() . '_pass', '', time()-3600, '/');

					$_SESSION = array();
					if (array_key_exists(session_name(), $_COOKIE)) setcookie(session_name(), '', time()-3600, '/');
					@session_destroy();

					header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/admin.php');
					break;

				case 'reset':
					ob_start();
					adminheader();

					echo '<h1 id="header"><a href="admin.php" title="Back to main admin page">PHPAskIt</a></h1>
					<ul id="navigation" class="center">
						<li><a href="admin.php">Login</a></li>
						<li><a href="admin.php?process=reset" title="Reset password" class="active">Lost password</a></li>
					</ul>
					<div class="center">';
					if ($_SERVER['REQUEST_METHOD'] == 'POST' && array_key_exists('submit', $_POST) && array_key_exists('username', $_POST) && array_key_exists('email_address', $_POST) && array_key_exists('security_word', $_POST)) {
						if (!array_key_exists('token', $_POST)) Error::showMessage('Your session has expired. Please reload the page to correct this issue.');

						$this->checkToken();
						ob_end_flush();
						$username = cleaninput($_POST['username']);
						$email = cleaninput($_POST['email_address']);
						$word = cleaninput($_POST['security_word']);
						if ($username == $this->getOption('username') && $email == $this->getOption('youraddress') && $word == $this->getOption('security_word')) {
							$newpassword = substr(md5(substr(md5(microtime()), 5, 7)), 5, 7);
							if ($pai_db->query('UPDATE `' . $pai_db->getTable() . "_options` SET `option_value` = '" . md5($newpassword . $this->getMask()) . "' WHERE `option_name` = 'password' LIMIT 1")) {
								$msg = 'Your password to PHPAskIt has been reset. Your new password is: ' . $newpassword . "\n\nPlease login and change it as soon as possible.";
								@mail($this->getOption('youraddress'), 'PHPAskIt: Password reset', $msg, 'From: PHPAskIt <' . $this->getOption('youraddress') . '>');
								echo '<p>Your password has been reset and sent to you by email. Please <a href="admin.php" title="Log in">log in</a> and change it as soon as possible.</p>';
							}
							else echo '<p>Your password could not be reset.</p>';
						}
						else echo '<p>The username, e-mail address or security word entered does not match the ones in PHPAskIt\'s options. Please go back and try again.</p>';
					}
					else { ?>
						<p>Please enter the username, e-mail address and the security word you provided in the options panel.</p>
						<form method="post" action="admin.php?process=reset">
							<p><input type="hidden" name="token" id="token" value="<?php echo $token; ?>">
							<label for="username">Username:</label><br>
							<input type="text" name="username" id="username"></p>

							<p><label for="email_address">E-mail address</label><br>
							<input type="text" name="email_address" id="email_address"></p>

							<p><label for="security_word">Security word</label><br>
							<input type="text" name="security_word" id="security_word"></p>

							<p><input type="submit" name="submit" id="submit" value="Submit"></p>
						</form>
						<?php
					}
					echo '</div></body></html>';
					exit;
					break;

				default:
					Error::showMessage('Invalid process.');
			}
		}
	}

	/**
	 * Verify that a user is logged in.
	 *
	 * @global Database $pai_db The database object.
	 * @param boolean $nodata Don't display anything to the user.
	 * @return mixed Whether the user is logged in, if they aren't, display the login form.
	 */
	public function isLoggedIn($nodata = false) {
		global $pai_db;
		if (array_key_exists($pai_db->getTable() . '_user', $_COOKIE) && !empty($_COOKIE[$pai_db->getTable() . '_user'])) {
			if ($this->getOption('username') == cleaninput($_COOKIE[$pai_db->getTable() . '_user'])) {
				//TODO: Change this. Not secure to have db password in cookie.
				if (array_key_exists($pai_db->getTable() . '_pass', $_COOKIE) && $_COOKIE[$pai_db->getTable() . '_pass'] == 'Loggedin_' . $this->getOption('password')) return true;
				elseif ($nodata == false) $this->displayLogin('Your session has expired. Please login again.');
			}
			elseif ($nodata == false) $this->displayLogin('Your session has expired. Please login again.');
		}
		elseif ($nodata == false) {
			array_key_exists('message', $_GET) ? $this->displayLogin('Your session has expired. Please login again.') : $this->displayLogin();
		}
	}

	/**
	 * Display the login form.
	 *
	 * @global string $token The session token.
	 * @global Database $pai_db The database object.
	 * @param string $problem Any error messages.
	 */
	protected function displayLogin($problem = '') {
		// TODO: redirect to requested URL
		global $token, $pai_db;
		adminheader();
		?>
		<h1 id="header"><a href="admin.php" title="Back to main admin page">PHPAskIt</a></h1>

		<ul id="navigation" class="center">
			<li><a href="admin.php" class="active">Login</a></li>
			<li><a href="admin.php?process=reset" title="Reset password">Lost password</a></li>
		</ul>

		<?php if (!empty($problem)) echo '<p>' . $problem . '</p>'; ?>

		<form method="post" action="admin.php?process=login">
			<p><input type="hidden" name="token" id="token" value="<?php echo $token; ?>">
			<label for="userlogon">Username:</label><br>
			<input type="text" name="userlogon" id="userlogon"></p>

			<p><label for="userpassword">Password:</label><br>
			<input type="password" name="userpassword" id="userpassword"></p>

			<p><input name="submitlogin" id="submitlogin" type="submit" value="Login"></p>
		</form>
		<p class="center">Powered by <a href="http://amelie.nu/scripts/" title="PHPAskIt">PHPAskIt 3.1</a></p>
		<?php
		echo '</body></html>';
		mysql_close($pai_db->getConnection());
		exit;
	}

	public function getQs($type = array()) {
		global $pai_db, $totalpages, $startfrom, $perpage;

		if (empty($type)) Error::showMessage('Unable to load questions.');

		switch($type[0]) {
			case 'unanswered':
				$query = <<<SQL
SELECT `q_id` FROM `{$pai_db->getTable()}` WHERE (`answer` = '' OR `answer` IS NULL)
SQL;
				$title = 'Unanswered questions (<span id="unanswered-qs-header">' . $this->getUnanswered() . '</span>)';

				break;
			case 'category':
				if (empty($type[1])) Error::showMessage('Invalid category');

				$query = 'SELECT `q_id` FROM `' . $pai_db->getTable() . '` WHERE `category` = ' . (int)cleaninput($_GET['category']);
				$title = '%s %s found in the &quot;' . $type[1] . '&quot; category';

				break;
			case 'search':
				if (empty($type[1])) Error::showMessage('Invalid search term');
				$getsearch = $type[1];

				$query = 'SELECT `q_id` FROM `' . $pai_db->getTable() . "` WHERE `question` LIKE '%" . $getsearch . "%' OR `answer` LIKE '%" . $getsearch . "%' OR `ip` LIKE '%" . $getsearch . "%'";
				$title = '%s %s containing &quot;' . stripslashes($getsearch) . '&quot;.';

				break;
			case 'date':
				$query = <<<SQL
SELECT `{$pai_db->getTable()}`.* FROM `{$pai_db->getTable()}`
SQL;
				$title = 'Latest questions';

				break;
			default:
				Error::showMessage('No questions found.');
		}

		ob_end_flush();
		pages();

		dopagination($query);
		$query .= ' ORDER BY `dateasked` DESC LIMIT ' . $startfrom . ', ' . ADMIN_PERPAGE;

		echo '<h2 class="question_header">' . sprintf($title, $totalpages, ($totalpages == 1 ? 'question' : 'questions')) . '</h2>';

		if ($totalpages > 0) {
			$getqs = $pai_db->query($query);
			pagination($perpage, $type[0]);
			echo '<ul id="question-list">';
			while($qs = mysql_fetch_object($getqs)) {
				$q = new Question($qs->q_id);
				$q->show();
			}
			echo '</ul>';
		}
		else echo '<p>No questions found.</p>';
	}
}
?>