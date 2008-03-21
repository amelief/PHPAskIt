<?php
/*
  ==============================================================================================
  PHPAskIt 3.0 © 2005-2008 Amelie M.
  ==============================================================================================
  																								*/

################################################################################################
############################ CORE PHPASKIT FUNCTIONS. DO _NOT_ EDIT. ###########################
################################################################################################


if (!defined('PAI_IN')) exit('<p>This file cannot be loaded directly.</p>');

error_reporting(0);

if (basename($_SERVER['PHP_SELF']) == 'import.php' || basename($_SERVER['PHP_SELF']) == 'convertwaks.php' || basename($_SERVER['PHP_SELF']) == 'convertfaqtastic.php' || basename($_SERVER['PHP_SELF']) == 'convertaa.php') $path = '../';
else $path = '';
if (!file_exists($path . 'config.php')) { ?>
	<h1>Error</h1>
	<p><strong><code>config.php</code></strong> could not be found. Without this file, the script cannot operate. Please make sure it is present.</p>
	<?php
	exit;
}
require_once $path . 'config.php';

class pai {
	var $table;
	var $connect;
	var $mask = 'ewieogth389thwkgnwlkhasdg';

	function pai($mysqlhost, $mysqluser, $mysqlpass, $mysqldb) {
		$this->connect = @mysql_connect($mysqlhost, $mysqluser, $mysqlpass);
		if (!$this->connect) exit('<p>Error connecting to MySQL - please verify your connection details in config.php.</p>');
		@mysql_select_db($mysqldb) or exit('<p>Error accessing MySQL database - please verify your connection details in config.php.</p>');
	}
	function query($query) {
		if (!$this->connect) exit('<p>Error: could not connect to MySQL. Your server may be temporarily unavailable; please try again later.</p>');
		$result = mysql_query($query, $this->connect);
		if (!$result) return false;
		else return $result;
	}
	function cleaninput($data) {
		$data = trim(htmlspecialchars(strip_tags($data)));
		if (get_magic_quotes_gpc()) $data = stripslashes($data);
		return mysql_real_escape_string($data, $this->connect);
	}
	function getoption($optname) {
		if ($option = $this->getfromdb('option_value', 'options', "`option_name` = '" . $optname . "'", 1)) return $option;
		else return false;
	}
	function getfromdb($field, $table, $where = '', $limit = '') {
		if ($table == 'main') $table1 = $this->table;
		else $table1 = $this->table . '_' . $table;
		$query = 'SELECT `' . $field . '` FROM `' . $table1 . '`';

		if (!empty($where) && !empty($limit)) $query .= ' WHERE ' . $where . ' LIMIT ' . $limit;
		elseif (!empty($where) && empty($limit)) $query .= ' WHERE ' . $where;
		elseif (empty($where) && !empty($limit)) $query .= ' LIMIT ' . $limit;

		$result = $this->query($query);

		if (@mysql_num_rows($result) > 0) {
			if ($getdbval = mysql_fetch_object($result)) return $getdbval->$field;
			else return false;
		}
		else return false;
	}
	function pagination($numofpages, $link) {
		global $page, $getsearch;
		echo '<p class="pagination center"><strong>Page ' . $page . ' of ' . $numofpages . '</strong></p>';
		if ($page > 1 && $page <= $numofpages && $page != 0) {
			$buildlinkprev = '&laquo; <a href="?';
			switch($link) {
				case 'unanswered':
					$buildlinkprev .= 'sort=unanswered&amp;page=';
					break;
				case 'bycat':
					$buildlinkprev .= 'category=' . $this->cleaninput($_GET['category']) . '&amp;page=';
					break;
				case 'search':
					$buildlinkprev .= 'search=' . stripslashes($getsearch) . '&amp;page=';
					break;
				default:
					if (defined('IS_ADMIN')) $buildlinkprev .= 'page=';
					else $buildlinkprev .= 'recent&amp;page=';
			}
			$buildlinkprev .= ($page - 1) . '" title="Previous page">Previous page</a>';
		}
		if ($page < $numofpages && $page != $numofpages && $page >= 1) {
			$buildlinknext = '<a href="?';
			switch($link) {
				case 'unanswered':
					$buildlinknext .= 'sort=unanswered&amp;page=';
					break;
				case 'bycat':
					$buildlinknext .= 'category=' . $this->cleaninput($_GET['category']) . '&amp;page=';
					break;
				case 'search':
					$buildlinknext .= 'search=' . stripslashes($getsearch) . '&amp;page=';
					break;
				default:
					if (defined('IS_ADMIN')) $buildlinknext .= 'page=';
					else $buildlinknext .= 'recent&amp;page=';
			}
			$buildlinknext .= ($page + 1) . '" title="Next page">Next page</a> &raquo;';
		}
		if (isset($buildlinkprev) || isset($buildlinknext)) {
			echo '<p class="pagination center">';
			if (isset($buildlinkprev)) echo $buildlinkprev;
			if (isset($buildlinknext) && isset($buildlinkprev)) echo ' | ';
			if (isset($buildlinknext)) echo $buildlinknext;
			echo '</p>';
		}
	}
	function pages() {
		global $page;
		if (isset($_GET['page']) && !empty($_GET['page'])) {
			$page = (int)$this->cleaninput($_GET['page']);
			if (empty($page)) $page = 1;
		}
		else $page = 1;
	}
	function check_pages($totalpages) {
		global $page;
		if ($page > $totalpages) $page = $totalpages;
	}
	function dopagination($query) {
		global $totalpages, $perpage, $startfrom, $page;
		$totalpages = mysql_num_rows($this->query($query));
		if (defined('ADMIN_PERPAGE')) {
			$perpage = ceil($totalpages / ADMIN_PERPAGE);
			$this->check_pages($perpage);
			$startfrom = ($page - 1) * ADMIN_PERPAGE;
		}
		else {
			$perpage = ceil($totalpages / $this->getoption('totalpage_faq'));
			$this->check_pages($perpage);
			$startfrom = ($page - 1) * $this->getoption('totalpage_faq');
		}
	}
	function showqs($sql) {
		if (empty($sql->answer)) $answer = '(Unanswered)';
		else $answer = $sql->answer;

		if ($this->getoption('enable_cats') == 'yes') $cat = '<a href="?category=' . $sql->category . '" title="See all questions in the ' . $this->getfromdb('cat_name', 'cats', '`cat_id` = ' . $sql->category, 1) . ' category">' . $this->getfromdb('cat_name', 'cats', '`cat_id` = ' . $sql->category, 1) . '</a>';
		else $cat = '';

		$sub = array('[[question]]', '[[answer]]', '[[category]]', '[[permalink]]', '[[date]]');
		$replace = array($sql->question, $answer, $cat, '?q=' . $sql->q_id, date($this->getoption('date_format'), strtotime($sql->dateasked)));

		echo str_replace($sub, $replace, $this->getoption('q_template'));
	}
	function admin_logout() {
		/* Oh noes, PAI did a funny */
	}
	function askform($link) {
		if ($this->getoption('enable_cats') == 'yes') {
			$getcats = $this->query('SELECT * FROM `' . $this->table . '_cats` ORDER BY `cat_name` ASC');
			if (mysql_num_rows($getcats) > 0) {
				$cat = '<select name="category" id="category">
				<option value="">CHOOSE ONE:</option>';
				while($cats = mysql_fetch_object($getcats)) {
					$cat .= '
					<option value="' . $cats->cat_id . '">' . $cats->cat_name . '</option>';
				}
				$cat .= '</select>';
			}
			else $cat = '';
		}
		else $cat = '';

		$sub = array('[[question]]', '[[category]]', '[[submit]]');
		$replace = array('<input type="text" name="question" id="question" />', $cat, '<input type="submit" name="askit" id="askit" value="Ask" />');
		?>

		<form class="pai-question-form" method="post" action="<?php echo $this->cleaninput($link); ?>">
			<?php echo str_replace($sub, $replace, $this->getoption('ask_template')); ?>
		</form>
	<?php
	}
	function adminqs($sql) {
		global $token;
		?>
		<li class="question-container">
			<h4 class="date"><?php echo date($this->getoption('date_format'), strtotime($sql->dateasked));
			if ($this->getoption('enable_cats') == 'yes') { ?>
				<span class="category">(<a href="admin.php?category=<?php echo $sql->category; ?>" title="See all questions in the <?php echo $this->getfromdb('cat_name', 'cats', '`cat_id` = ' . $sql->category, 1); ?> category"><?php echo $this->getfromdb('cat_name', 'cats', '`cat_id` = ' . $sql->category, 1); ?></a>)</span>
				<?php
			} ?> <img src="indicator.gif" alt="Saving..." title="Saving..." id="indicator<?php echo $sql->q_id; ?>" style="display: none;" /></h4>

			<p class="question" id="question<?php echo $sql->q_id; ?>" title="Click to edit question"><a href="admin.php?q=<?php echo $sql->q_id; ?>" title="Permalink to this question"><?php echo $sql->question; ?></a></p>
			<p id="answer<?php echo $sql->q_id; ?>indicator" style="display: none;"><img src="indicator.gif" alt="Loading..." title="Loading..." /></p>
			<p id="answer<?php echo $sql->q_id; ?>" class="answer<?php if (empty($sql->answer)) echo ' unanswered'; ?>" title="<?php if (!empty($sql->answer)) echo 'Click to edit answer'; else echo 'Click to add an answer'; ?>"><input type="hidden" name="id" value="<?php echo $sql->q_id; ?>" /><?php if (empty($sql->answer)) echo '(No answer)'; else echo $sql->answer; ?></p>

			<p class="ip"><?php echo $sql->ip; ?> <?php if ($this->getoption('ipban_enable') == 'yes') { ?>[<a href="admin.php?manage=ips&amp;action=add&amp;ip=<?php echo $sql->ip; ?>&amp;token=<?php echo $token; ?>" title="Ban this IP from asking more questions">Ban?</a>]<?php } ?></p>

			<p class="tools center">
				<a href="admin.php?edit=answer&amp;qu=<?php echo $sql->q_id; ?>&amp;token=<?php echo $token; ?>" title="<?php
				if (empty($sql->answer)) {
					?>Answer this question">Answer Question<?php
				}
				else {
					?>Edit your answer to this question">Edit Answer<?php
				} ?></a> |
				<a href="admin.php?edit=question&amp;qu=<?php echo $sql->q_id; ?>&amp;token=<?php echo $token; ?>" title="Edit this question">Edit Question</a>

				<?php
				if ($this->getoption('enable_cats') == 'yes') { ?>
					| <a href="admin.php?edit=category&amp;qu=<?php echo $sql->q_id; ?>&amp;token=<?php echo $token; ?>" title="Change the category of this question">Change Category</a>
					<?php
				} ?>
				| <a href="admin.php?delete=<?php echo $sql->q_id; ?>&amp;token=<?php echo $token; ?>" onclick="return confirm('Are you sure you want to delete this question?')" title="Delete this question">Delete</a>
			</p>
		</li>
		<?php
	}
	function adminheader() {
		?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>PHPAskIt 3.0: Admin</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<style type="text/css">
		body { color: #222; font: 0.7em/1.2em Verdana, Arial, Helvetica, sans-serif; text-align: center; }
		a { color: #0080ff; text-decoration: none; }
		a:hover { text-decoration: underline; }
		fieldset { border: 0 none; }
		fieldset h4 { border-bottom: 2px solid #c0c0c0; color: #777; font-size: 1.3em; margin-top: 2em; }
		h2 { color: #32cd32; font-size: 1.7em; font-variant: small-caps; line-height: normal; }
		h3 { color: #32cd32; font: bold italic 1.5em Georgia, "Times New Roman", Times, serif; }
		img { border: 0 none; }
		input, select { border: 1px solid #c0c0c0; font: 1em Verdana, Arial, Helvetica, sans-serif; padding: 2px; }
		input:focus, select:focus, textarea:focus { border: 1px solid #0080ff; }
		textarea { border: 1px solid #c0c0c0; font: 1.3em "Courier New", Courier, monospace; padding: 2px; }
		#container { margin: 0 auto; text-align: left; width: 90%; }
		#header { border-bottom: 3px solid #32cd32; font: bold 4.7em/0.7em Tahoma, Arial, Helvetica, sans-serif; letter-spacing: -0.1em; margin-bottom: 0.25em; margin-top: 0; padding-right: 0.1em; text-align: right; }
		#header a { color: #32cd32; }
		#header a:hover { color: #0080ff; text-decoration: none; }
		#main { margin-left: 40%; width: 50%; }
		#container > #main { width: 60%; }
		#navigation { list-style: none; margin: -1.2em 0 2em 0; padding: 0; }
		#navigation a { font-weight: bold; line-height: 2em; padding: 0.5em 1em; }
		#navigation a:hover { background: #0080ff; color: white; text-decoration: none; }
		#navigation li { display: inline; margin: 0; padding: 0; }
		#navigation .active { background: #32cd32; color: white; text-decoration: none; }
		#question-list { list-style: none; margin: 0; padding: 0; }
		#question-list li { width: 99%; }
		#side { float: left; width: 30%; }
		.active:hover { background: transparent; }
		.answer { margin-left: 2.3em; margin-right: 2.4em; padding: 0.5em; width: 85%; }
		.answer:hover { background: #fff; }
		.category { font-size: 0.9em; }
		.center { text-align: center; }
		.date { color: #32cd32; font-size: 1.5em; font-weight: bold; line-height: 1.6em; letter-spacing: -0.1em; margin-top: 0.5em; }
		.edit { color: #000; font-size: 0.8em; font-weight: normal; letter-spacing: normal; }
		.ip { color: green; font-size: 0.9em; text-align: right; }
		.question { font-size: 1.2em; font-weight: bold; margin-left: 1.5em; padding: 0.5em; width: 85% }
		.question:hover { background: #fff; }
		.question-container { background: #e9fbe9; border-left: 2px dotted #32cd32; margin-bottom: 3em; padding: 0.5em 0.5em 0.2em 2em; }
		.question-container:hover { border-left: 2px dotted #0080ff; }
		.template { height: 12em; width: 90%; }
		.tools { border-top: 1px dotted #32cd32; padding-top: 0.2em; }
		.unanswered { color: #c0c0c0; letter-spacing: 0.1em; }
	</style>
	<script type="text/javascript" src="ajax.js"></script>
</head>

<body>
		<?php
	}
	function summary() {
		$total = mysql_fetch_object($this->query('SELECT COUNT(`q_id`) AS `num` FROM `' . $this->table . '`'));
		$answered = mysql_fetch_object($this->query('SELECT COUNT(`q_id`) AS `num` FROM `' . $this->table . "` WHERE `answer` != ''"));
		$unanswered = mysql_fetch_object($this->query('SELECT COUNT(`q_id`) AS `num` FROM `' . $this->table . "` WHERE `answer` = ''"));
		$cats = mysql_fetch_object($this->query('SELECT COUNT(DISTINCT `category`) AS `num` FROM `' . $this->table . '`'));

		$summary = array('[[total]]', '[[answered]]', '[[unanswered]]', '[[categories]]');
		$replace = array($total->num, $answered->num, $unanswered->num, $cats->num);

		echo str_replace($summary, $replace, $this->getoption('sum_template'));
	}
	function checktoken($post = true, $get = false, $inline = false) {
		if ($post == true) {
			if (!isset($_POST['token'])) {
				if ($inline) $this->kill_token(true);
				else $this->kill_token();
			}
			else $token = $_POST['token'];
		}
		elseif ($get == true) {
			if (!isset($_GET['token'])) {
				if ($inline) $this->kill_token(true);
				else $this->kill_token();
			}
			else $token = $_GET['token'];
		}
		if (!isset($_SESSION['pai_token']) || $token != $_SESSION['pai_token']) {
			if ($inline == true) $this->kill_token(true);
			else $this->kill_token();
		}
		if (!isset($_SESSION['pai_time']) || ((time() - $_SESSION['pai_time']) > 300)) {
			if ($inline == true) $this->kill_token(true);
			else $this->kill_token();
		}
	}
	function kill_token($inline = false) {
		$_SESSION = array();
		if (isset($_COOKIE[session_name()])) setcookie(session_name(), '', time()-3600, '/');
		@session_destroy();
		if ($inline == true) {
			ob_end_clean();
			exit('<strong>Error:</strong> Your session has expired. Please refresh the page to correct this problem.');
		}
		else {
			ob_end_flush();
			pai_error('Your session has expired. Please refresh the page to correct this problem.');
		}
	}
	function dologin() {
		global $token;
		if (isset($_GET['process'])) {
			switch($_GET['process']) {
				case 'login':
					if (isset($_POST['userlogon']) && isset($_POST['userpassword']) && !empty($_POST['userlogon']) && !empty($_POST['userpassword']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
						ob_start();
						$this->adminheader();
						echo '<h1 id="header"><a href="admin.php" title="Back to main admin page">PHPAskIt</a></h1>

						<ul id="navigation" class="center">
							<li><a href="admin.php" class="active">Login</a></li>
							<li><a href="admin.php?process=reset" title="Reset password">Lost password</a></li>
						</ul>
						<div class="center">';

						if (!isset($_POST['token'])) {
							ob_end_flush();
							pai_error('Your session has expired. Please reload the page to correct this issue.');
						}
						$this->checktoken($_POST['token']);
						if ($this->cleaninput($_POST['userlogon']) == $this->getoption('username') && md5($this->cleaninput($_POST['userpassword']) . $this->mask) == $this->getoption('password')) {
							setcookie($this->table . '_user', $this->getoption('username'), time()+(86400*365), '/');
							setcookie($this->table . '_pass', 'Loggedin_' . $this->getoption('password'), time()+(86400*365), '/');
							header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/admin.php');
							ob_end_flush();
							exit;
						}
						else {
							ob_end_clean();
							$this->display_login('Incorrect username or password. Please try again.');
						}
					}
					break;

				case 'logout':
					setcookie($this->table . '_user', '', time()-3600, '/');
					setcookie($this->table . '_pass', '', time()-3600, '/');

					$_SESSION = array();
					if (isset($_COOKIE[session_name()])) setcookie(session_name(), '', time()-3600, '/');
					@session_destroy();

					header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/admin.php');
					break;

				case 'reset':
					ob_start();
					$this->adminheader();

					echo '<h1 id="header"><a href="admin.php" title="Back to main admin page">PHPAskIt</a></h1>
					<ul id="navigation" class="center">
						<li><a href="admin.php">Login</a></li>
						<li><a href="admin.php?process=reset" title="Reset password" class="active">Lost password</a></li>
					</ul>
					<div class="center">';
					if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit']) && isset($_POST['username']) && isset($_POST['email_address']) && isset($_POST['security_word'])) {
						if (!isset($_POST['token'])) {
							ob_end_flush();
							pai_error('Your session has expired. Please reload the page to correct this issue.');
						}
						$this->checktoken($_POST['token']);
						ob_end_flush();
						$username = $this->cleaninput($_POST['username']);
						$email = $this->cleaninput($_POST['email_address']);
						$word = $this->cleaninput($_POST['security_word']);
						if ($username == $this->getoption('username') && $email == $this->getoption('youraddress') && $word == $this->getoption('security_word')) {
							$newpassword = substr(md5(substr(md5(microtime()), 5, 7)), 5, 7);
							if ($this->query('UPDATE `' . $this->table . "_options` SET `option_value` = '" . md5($newpassword . $this->mask) . "' WHERE `option_name` = 'password' LIMIT 1")) {
								$msg = 'Your password to PHPAskIt has been reset. Your new password is: ' . $newpassword . "\n\nPlease login and change it as soon as possible.";
								@mail($this->getoption('youraddress'), 'PHPAskIt: Password reset', $msg, 'From: PHPAskIt <' . $this->getoption('youraddress') . '>');
								echo '<p>Your password has been reset and sent to you by email. Please <a href="admin.php" title="Log in">log in</a> and change it as soon as possible.</p>';
							}
							else echo '<p>Your password could not be reset.</p>';
						}
						else echo '<p>The username, e-mail address or security word entered does not match the ones in PHPAskIt\'s options. Please go back and try again.</p>';
					}
					else { ?>
						<p>Please enter the username, e-mail address and the security word you provided in the options panel.</p>
						<form method="post" action="admin.php?process=reset">
							<p><input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
							<label for="username">Username:</label><br />
							<input type="text" name="username" id="username" /></p>

							<p><label for="email_address">E-mail address</label><br />
							<input type="text" name="email_address" id="email_address" /></p>

							<p><label for="security_word">Security word</label><br />
							<input type="text" name="security_word" id="security_word" /></p>

							<p><input type="submit" name="submit" id="submit" value="Submit" /></p>
						</form>
						<?php
					}
					echo '</div></body></html>';
					exit;
					break;

				default:
					pai_error('Invalid process.');
			}
		}
	}
	function isloggedin($nodata = false) {
		if (isset($_COOKIE[$this->table . '_user']) && !empty($_COOKIE[$this->table . '_user'])) {
			if ($this->getoption('username') == $this->cleaninput($_COOKIE[$this->table . '_user'])) {
				if (isset($_COOKIE[$this->table . '_pass']) && $_COOKIE[$this->table . '_pass'] == 'Loggedin_' . $this->getoption('password')) return true;
				elseif ($nodata == false) $this->display_login('Your session has expired. Please login again.');
			}
			elseif ($nodata == false) $this->display_login('Your session has expired. Please login again.');
		}
		elseif ($nodata == false) $this->display_login();
	}
	function display_login($problem = '') {
		global $token;
		$this->adminheader();
		?>
		<h1 id="header"><a href="admin.php" title="Back to main admin page">PHPAskIt</a></h1>

		<ul id="navigation" class="center">
			<li><a href="admin.php" class="active">Login</a></li>
			<li><a href="admin.php?process=reset" title="Reset password">Lost password</a></li>
		</ul>

		<?php if (!empty($problem)) echo '<p>' . $problem . '</p>'; ?>

		<form method="post" action="admin.php?process=login">
			<p><input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
			<label for="userlogon">Username:</label><br />
			<input type="text" name="userlogon" id="userlogon" /></p>

			<p><label for="userpassword">Password:</label><br />
			<input type="password" name="userpassword" id="userpassword" /></p>

			<p><input name="submitlogin" id="submitlogin" type="submit" value="Login" /></p>
		</form>
		<p class="center">Powered by <a href="http://not-noticeably.net/scripts/phpaskit/" title="PHPAskIt">PHPAskIt 3.0</a></p>
		<?php
		echo '</body></html>';
		mysql_close($this->connect);
		unset($this->connect);
		exit;
	}
}

$display = '<p style="text-align: center;">Powered by <a href="http://not-noticeably.net/scripts/phpaskit/" title="PHPAskIt">PHPAskIt 3.0</a></p>';

function clean_array($data) {
	if (get_magic_quotes_gpc()) return is_array($data) ? array_map('clean_array', $data) : trim(htmlspecialchars(strip_tags($data)));
	else return is_array($data) ? array_map('clean_array', $data) : addslashes(trim(htmlspecialchars(strip_tags($data))));
}
function pai_error($message, $die = true, $header = '') {
	global $pai;
	echo '<h3>';
	if (!empty($header)) echo $header; else echo 'Error';
	echo '</h3>
	<p>' . $message . '</p>';

	if ($die == true) {
		echo '<p style="text-align: center;">Powered by <a href="http://not-noticeably.net/scripts/phpaskit/" title="PHPAskIt">PHPAskIt 3.0</a></p>';
		if (defined('IS_ADMIN')) {
			echo '</div></div></body></html>';
		}
		elseif ($pai->getoption('is_wordpress') == 'yes') {
			if (function_exists('get_sidebar')) get_sidebar();
			if (function_exists('get_footer')) get_footer();
		}
		else include $pai->getoption('footerfile');
		exit;
	}
}

$pai = new pai(PAI_HOST, PAI_USER, PAI_PASS, PAI_DB);
$pai->table = $pai->cleaninput(PAI_TABLE);

$display = '<p style="text-align: center;">Powered by <a href="http://not-noticeably.net/scripts/phpaskit/" title="PHPAskIt">PHPAskIt 3.0</a></p>';

foreach($_SERVER as $key => $value) {
	$_SERVER[$key] = clean_array($value);
}
?>