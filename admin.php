<?php
/*
  ==============================================================================================
  Askably 3.1 © 2005-2009 Amelie M.
  ==============================================================================================
*/

#################################### ADMINISTRATION PANEL ######################################


##################### DIAGNOSTICS #####################
define('PAI_IN', true);

//CSRF PROTECTION
session_start();
if (!isset($_SESSION['pai_token'])) $_SESSION['pai_token'] = $token = md5(uniqid(rand(), true));
else $token = $_SESSION['pai_token'];
$_SESSION['pai_time'] = time();

if (!file_exists('functions.php')) { ?>
	<h1>Error</h1>
	<strong><code>functions.php</code></strong> could not be found. Without this file, the script cannot operate. Please make sure it is present.</p>
	<?php
	exit;
}
require 'functions.php';

check_stuff();

$pai->doLogin();
$pai->isLoggedIn();
#######################################################

############# SUMMARIES, NAVIGATION, ETC. #############
ob_start();

define('IS_ADMIN', true);
if (isset($_POST['qsperpage']) && !empty($_POST['qsperpage'])) {
	$qsperpage = (int)cleaninput($_POST['qsperpage']);

	if (!is_numeric($qsperpage) || $qsperpage < 1 || $qsperpage > 999) $qsperpage = 10;
	setcookie($pai_db->getTable() . '_QsPerPage', $qsperpage, (time() + (86400 * 365)), '/');
	header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
}
elseif (isset($_COOKIE[$pai_db->getTable() . '_QsPerPage']) && !empty($_COOKIE[$pai_db->getTable() . '_QsPerPage']) && is_numeric($_COOKIE[$pai_db->getTable() . '_QsPerPage'])) define('ADMIN_PERPAGE', (int)cleaninput($_COOKIE[$pai_db->getTable() . '_QsPerPage']));
else define('ADMIN_PERPAGE', 10);

adminheader(); ?>

<div id="container">
	<h1 id="header"><a href="admin.php" title="Back to main admin page">Askably</a></h1>

	<?php if (isset($_SERVER['QUERY_STRING'])) {
		if (strstr($_SERVER['QUERY_STRING'], '=unanswered')) $active = 'unans';
		elseif (strstr($_SERVER['QUERY_STRING'], '=categories')) $active = 'cats';
		elseif (strstr($_SERVER['QUERY_STRING'], '=ips')) $active = 'ips';
		elseif (strstr($_SERVER['QUERY_STRING'], '=antispam')) $active = 'spam';
		elseif (strstr($_SERVER['QUERY_STRING'], '=options')) $active = 'opt';
		elseif (strstr($_SERVER['QUERY_STRING'], '=templates')) $active = 'temp';
		else $active = 'home';
	}
	else $active = 'home'; ?>
	<ul id="navigation" class="center">
		<li><a href="admin.php" title="Main admin page"<?php if ($active == 'home') echo ' class="active"'; ?>>Admin home</a></li>
		<li><a href="admin.php?sort=unanswered" title="View unanswered questions"<?php if ($active == 'unans') echo ' class="active"'; ?>>Unanswered (<?php echo $pai->getUnanswered(); ?>)</a></li>
	<?php if ($pai->getOption('enable_cats') == 'yes') { ?>
		<li><a href="admin.php?manage=categories" title="Manage categories"<?php if ($active == 'cats') echo ' class="active"'; ?>>Categories</a></li>
	<?php }
	if ($pai->getOption('ipban_enable') == 'yes') { ?>
		<li><a href="admin.php?manage=ips" title="Manage blocked IP addresses"<?php if ($active == 'ips') echo ' class="active"'; ?>>Blocked IPs</a></li>
	<?php }
	if ($pai->getOption('antispam_enable') == 'yes') { ?>
		<li><a href="admin.php?manage=antispam" title="Manage blocked words"<?php if ($active == 'spam') echo ' class="active"'; ?>>Blocked words</a></li>
	<?php } ?>
		<li><a href="admin.php?manage=options" title="Edit options"<?php if ($active == 'opt') echo ' class="active"'; ?>>Options</a></li>
		<li><a href="admin.php?manage=templates" title="Edit templates"<?php if ($active == 'temp') echo ' class="active"'; ?>>Templates</a></li>
		<li><a href="index.php?recent" title="Questions page">Recent</a></li>

	<?php if ($pai->getOption('enable_cats') == 'yes' && $pai->getOption('summary_enable') == 'yes') { ?>
		<li><a href="index.php" title="Summary page">Summary</a></li>
	<?php } ?>
	</ul>

	<div id="side">
		<h3>Summary</h3>

		<p>You are logged in as <strong><?php echo $pai->getOption('username'); ?></strong>. (<a href="admin.php?process=logout" title="Logout">Logout?</a>)</p>
		<p><strong>Quick stats</strong></p>
		<ul>
			<li>Total questions: <strong><?php echo $pai->getTotal(); ?></strong></li>
			<li>Unanswered questions: <strong><?php echo $pai->getUnanswered(); ?></strong></li>
		<?php if ($pai->getOption('enable_cats') == 'yes') { ?>
			<li>Questions in <strong><?php echo $pai->getCats() . ($pai->getCats() == 1 ? ' category' : ' categories'); ?></strong></li>
		<?php } ?>
		</ul>

		<h3>Options</h3>
		<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
			<p><strong>View <input type="text" maxlength="3" size="3" value="<?php if (defined('ADMIN_PERPAGE')) echo ADMIN_PERPAGE; ?>" name="qsperpage" id="qsperpage" /> <?php echo (defined('ADMIN_PERPAGE') && ADMIN_PERPAGE == 1) ? 'question' : 'questions'; ?> per page. <input name="go" type="submit" value="Go" /></strong></p>
		</form>
		<form method="get" action="admin.php">
			<p><label for="search">Search questions:</label><br />
			<input type="text" name="search" id="search" /> <input type="submit" value="Search" /></p>
		</form>
	</div>
	<div id="main">
<?php
#######################################################

############### UNANSWERED QUESTIONS ONLY #############
if (isset($_GET['sort']) && $_GET['sort'] == 'unanswered') {
	ob_end_flush();
	pages();

	$query = <<<SQL
SELECT * FROM `{$pai_db->getTable()}` WHERE (`answer` = '' OR `answer` IS NULL)
SQL;
	dopagination($query);
	$query .= ' ORDER BY `dateasked` DESC LIMIT ' . $startfrom . ', ' . ADMIN_PERPAGE;

	if ($totalpages > 0) { ?>
		<h2 class="question_header">Unanswered questions (<?php echo $pai->getUnanswered(); ?>)</h2>
		<?php
		$getqs = $pai_db->query($query);
		pagination($perpage, 'unanswered');
		echo '<ul id="question-list">';
		while($qs = mysql_fetch_object($getqs)) {
			$pai->adminQs($qs);
		}
		echo '</ul>';
	}
	else echo '<p>No questions to answer.</p>';
}
#######################################################

####### ALL QUESTIONS FROM A PARTICULAR CATEGORY ######
elseif (isset($_GET['category']) && !empty($_GET['category']) && is_numeric($_GET['category'])) {
	ob_end_flush();
	if ($pai->getOption('enable_cats') != 'yes') $error = new Error('Categories are disabled. To enable them, go to the <a href="admin.php?manage=options" title="Options page">options panel</a> and check &quot;enable categories&quot;.');
	if (!$cat = $pai_db->get('cat_name', 'cats', '`cat_id` = ' . (int)cleaninput($_GET['category']))) $error = new Error('Invalid category.');
	if (isset($error)) $error->display();

	pages();

	$query = 'SELECT * FROM `' . $pai_db->getTable() . '` WHERE `category` = ' . (int)cleaninput($_GET['category']);
	dopagination($query);
	$query .= ' ORDER BY `q_id` DESC LIMIT ' . $startfrom . ', ' . ADMIN_PERPAGE;

	if ($totalpages > 0) {
		?>
		<h2 class="question_header"><?php echo $totalpages . ($totalpages == 1 ? ' question' : ' questions'); ?> in the &quot;<?php echo $cat; ?>&quot; category</h2>
		<?php
		$getqs = $pai_db->query($query);
		pagination($perpage, 'bycat');
		echo '<ul id="question-list">';
		while($qs = mysql_fetch_object($getqs)) {
			$pai->adminQs($qs);
		}
		echo '</ul>';
	}
	else echo '<p>No questions in this category.</p>';
}
#######################################################

##################### SEARCH ##########################
elseif (isset($_GET['search'])) {
	ob_end_flush();
	$getsearch = cleaninput($_GET['search']);

	if (empty($getsearch)) {
		$error = new Error('No search term entered.');
		$error->display();
	}

	pages();

	$query = 'SELECT * FROM `' . $pai_db->getTable() . "` WHERE `question` LIKE '%" . $getsearch . "%' OR `answer` LIKE '%" . $getsearch . "%' OR `ip` LIKE '%" . $getsearch . "%'";
	dopagination($query);
	$query .= ' ORDER BY `q_id` DESC LIMIT ' . $startfrom . ', ' . ADMIN_PERPAGE;

	if ($totalpages > 0) {
		$getqs = $pai_db->query($query);

		echo '<h2 class="question_header">' . $totalpages . ' ' . ($totalpages == 1 ? 'question' : 'questions') . ' containing the term &quot;' . stripslashes($getsearch) . '&quot; ' . ($totalpages == 1 ? 'was' : 'were') . ' found.</h2>';

		pagination($perpage, 'search');
		echo '<ul id="question-list">';
		while($qs = mysql_fetch_object($getqs)) {
			$pai->adminQs($qs);
		}
		echo '</ul>';
	}
	else echo '<p>No items containing the term &quot;' . stripslashes($getsearch) . '&quot; were found.</p>';
}
#######################################################

################# QUESTION PERMALINKS #################
elseif (isset($_GET['q']) && !empty($_GET['q']) && is_numeric($_GET['q'])) {
	ob_end_flush();

	if (!$pai_db->get('q_id', 'main', '`q_id` = ' . (int)cleaninput($_GET['q']))) {
		$error = new Error('Invalid question.');
		$error->display();
	}

	$q = mysql_fetch_object($pai_db->query('SELECT * FROM `' . $pai_db->getTable() . '` WHERE `q_id` = ' . (int)cleaninput($_GET['q']) . ' LIMIT 1'));

	echo '<ul id="question-list">';
	$pai->adminQs($q);
	echo '</ul>';
}
#######################################################

################### DELETE FUNCTION ###################
elseif (isset($_GET['delete']) && !empty($_GET['delete']) && is_numeric($_GET['delete'])) {
	$pai->checkToken();
	if (!isset($_GET['inline'])) ob_end_flush();

	$question = new Question((int)$_GET['delete']);

	if (!$question->checkId()) {
		if (isset($_GET['inline'])) {
			ob_end_clean();
			echo '<strong>Error:</strong> Invalid question.';
		}
		else {
			$error = new Error('Invalid question.');
			$error->display();
		}
	}
	if ($question->delete()) echo (array_key_exists('inline', $_GET) ? '' /* TODO: Do some AJAX here */ : '<p>Question successfully deleted.</p>');
}
#######################################################

################### EDIT FUNCTIONS ####################
elseif (array_key_exists('edit', $_GET)) {
	switch($_GET['edit']) {

		##### EDIT QUESTIONS
		case 'question':
			if (array_key_exists('id', $_POST)) $question = new Question((int)$_POST['id']);
			elseif (array_key_exists('qu', $_GET)) $question = new Question((int)$_GET['qu']);
			if (isset($question)) $question->editQuestion();
			else {
				$error = new Error('Invalid question');
				$error->display();
			}
			break;

		##### EDIT ANSWERS
		case 'answer':
			if (array_key_exists('id', $_POST)) $question = new Question((int)$_POST['id']);
			elseif (array_key_exists('qu', $_GET)) $question = new Question((int)$_GET['qu']);
			if (isset($question)) $question->editAnswer();
			else {
				$error = new Error('Invalid question');
				$error->display();
			}
			break;

		##### EDIT CATEGORIES
		case 'category':
			if (array_key_exists('id', $_POST)) $question = new Question((int)$_POST['id']);
			elseif (array_key_exists('qu', $_GET)) $question = new Question((int)$_GET['qu']);
			if (isset($question)) $question->editCategory();
			else {
				$error = new Error('Invalid question');
				$error->display();
			}
			break;

		default:
			ob_end_flush();
			echo '<p>Invalid action.</p>';
	}
}
#######################################################

############### MANAGEMENT AND OPTIONS ################
elseif (array_key_exists('manage', $_GET) && !empty($_GET['manage'])) {
	switch($_GET['manage']) {

		##### OPTIONS
		case 'options':
			if (isset($_POST['submit']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
				$pai->checkToken();

				if (isset($_POST['currentpass']) && !empty($_POST['currentpass']) && md5($_POST['currentpass'] . $pai->getMask()) == $pai->getOption('password')) {
					foreach($_POST as $key => $value) {
						$$key = cleaninput($value);
					}
					if (!empty($password)) {
						if ($confirm_pass != $password) {
							ob_end_flush();
							$error = new Error('Passwords did not match, try again.');
							$error->display();
						}
						if (!preg_match('/^([_a-z0-9@\.-]+)$/i', $_POST['password'])) {
							ob_end_flush();
							$error = new Error('Password contains invalid characters.');
							$error->display();
						}
					}
					if (empty($youraddress) || !preg_match('/^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i', $youraddress)) {
						ob_end_flush();
						$error = new Error('Please enter a valid email address.');
						$error->display();
					}

					$tooeasy = array('phpaskit', 'pai', 'abc123', '123abc', 'q&amp;a', 'question', 'questions', 'questionsandanswers', 'questionandanswer', 'q &amp; a', 'questionsandanswer', 'questionandanswers', 'questions and answer', 'question and answer', 'question and answers', 'questions and answers', 'qanda', 'q and a', 'q & a', 'security word', 'security', 'blah', 'yeah', 'password', 'word', 'test', 'askably');

					if (!isset($word) || (isset($word) && empty($word))) {
						ob_end_flush();
						$error[] = new Error('Enter a security word.');
					}
					elseif (strlen($word) <= 3 || strtolower($word) == strtolower($pai->getOption('username')) || strtolower($word) == $username || strtolower($word) == $pai->getOption('youraddress') || in_array(strtolower($word), $tooeasy)) {
						ob_end_flush();
						$error[] = new Error('Your security word is too obvious or too short. Try a different word.');
					}
					elseif (md5(strtolower($word) . $pai->getMask()) == $pai->getOption('password')) {
						ob_end_flush();
						$error[] = new Error('Your security word cannot be the same as your password.');
					}

					if (!empty($password) && (strlen($password) <= 3 || strtolower($password) == strtolower($pai->getOption('username')) || strtolower($password) == $username || strtolower($password) == $pai->getOption('youraddress') || in_array(strtolower($password), $tooeasy))) {
						ob_end_flush();
						$error[] = new Error('Your new password is too obvious or too short. Try a different word.');
					}

					if (isset($error)) {
						$num = count($error);
						for($i = 0; $i < $num; $i++) {
							if ($i < ($num - 1)) $error[$i]->setDie(false);
							if ($i > 0) $error[$i]->setHeading(false);
							$error[$i]->display();
						}
					}

					if (empty($totalpage_faq) || $totalpage_faq < 1 || $totalpage_faq > 999 || !is_numeric($totalpage_faq)) $totalpage_faq = 10;
					if (empty($titleofpage)) $titleofpage = 'Q&amp;A';
					if (empty($date_format)) $date_format = 'l F j, Y - g:ia';

					if (!isset($is_wordpress) || $is_wordpress != 'yes') $is_wordpress = 'no';
					if (!isset($enable_cats) || $enable_cats != 'yes') $enable_cats = 'no';
					if (!isset($ipban_enable) || $ipban_enable != 'yes') $ipban_enable = 'no';
					if (!isset($antispam_enable) || $antispam_enable != 'yes') $antispam_enable = 'no';
					if (!isset($show_unanswered) || $show_unanswered != 'yes') $show_unanswered = 'no';
					if (!isset($summary_enable) || $summary_enable != 'yes') $summary_enable = 'no';
					if (!isset($notifybymail) || $notifybymail != 'yes') $notifybymail = 'no';

					if (empty($headerfile) && $is_wordpress == 'no') $headerfile = 'header.html';
					elseif (!empty($headerfile) && $is_wordpress == 'yes') $headerfile = '';
					if (empty($footerfile) && $is_wordpress == 'no') $footerfile = 'footer.html';
					elseif (!empty($footerfile) && $is_wordpress == 'yes') $footerfile = '';
					if ($is_wordpress == 'no' && strstr($headerfile, 'http://')) {
						ob_end_flush();
						$error[] = new Error('Please do not use a URL for your header file. Only absolute paths may be used.');
					}
					if ($is_wordpress == 'no' && strstr($footerfile, 'http://')) {
						ob_end_flush();
						$error[] = new Error('Please do not use a URL for your footer file. Only absolute paths may be used.');
					}
					if (isset($error)) {
						$num = count($error);
						for($i = 0; $i < $num; $i++) {
							if ($i < ($num - 1)) $error[$i]->setDie(false);
							if ($i > 0) $error[$i]->setHeading(false);
							$error[$i]->display();
						}
					}

					if ($is_wordpress == 'yes') {
						if (empty($is_wp_blog_header)) {
							ob_end_flush();
							$error = new Error('Please enter your absolute path to wp-blog-header.php if you wish to use WordPress Themes. If not, please uncheck the appropriate option.');
						}
						elseif (strstr($is_wp_blog_header, 'http://')) {
							ob_end_flush();
							$error = new Error('Please enter an absolute path to wp-blog-header.php, NOT a URL.');
						}
						elseif (!file_exists($is_wp_blog_header)) {
							ob_end_flush();
							$error = new Error('Your path to wp-blog-header.php appears to be incorrect, as Askably cannot find it. Please go back and try again.');
						}
					}
					if (isset($error)) $error->display();

					$update = array();
					$update[] = 'UPDATE `' . $pai_db->getTable() . "_options` SET `option_value` = '" . $word . "' WHERE `option_name` = 'security_word' LIMIT 1";
					$update[] = 'UPDATE `' . $pai_db->getTable() . "_options` SET `option_value` = '" . $headerfile . "' WHERE `option_name` = 'headerfile' LIMIT 1";
					$update[] = 'UPDATE `' . $pai_db->getTable() . "_options` SET `option_value` = '" . $footerfile . "' WHERE `option_name` = 'footerfile' LIMIT 1";
					$update[] = 'UPDATE `' . $pai_db->getTable() . "_options` SET `option_value` = '" . $date_format . "' WHERE `option_name` = 'date_format' LIMIT 1";
					$update[] = 'UPDATE `' . $pai_db->getTable() . "_options` SET `option_value` = '" . $enable_cats . "' WHERE `option_name` = 'enable_cats' LIMIT 1";
					$update[] = 'UPDATE `' . $pai_db->getTable() . "_options` SET `option_value` = '" . $ipban_enable . "' WHERE `option_name` = 'ipban_enable' LIMIT 1";
					$update[] = 'UPDATE `' . $pai_db->getTable() . "_options` SET `option_value` = '" . $antispam_enable . "' WHERE `option_name` = 'antispam_enable' LIMIT 1";
					$update[] = 'UPDATE `' . $pai_db->getTable() . "_options` SET `option_value` = '" . $show_unanswered . "' WHERE `option_name` = 'show_unanswered' LIMIT 1";
					$update[] = 'UPDATE `' . $pai_db->getTable() . "_options` SET `option_value` = '" . $summary_enable . "' WHERE `option_name` = 'summary_enable' LIMIT 1";
					$update[] = 'UPDATE `' . $pai_db->getTable() . "_options` SET `option_value` = '" . $titleofpage . "' WHERE `option_name` = 'titleofpage' LIMIT 1";
					$update[] = 'UPDATE `' . $pai_db->getTable() . "_options` SET `option_value` = '" . $is_wordpress . "' WHERE `option_name` = 'is_wordpress' LIMIT 1";
					$update[] = 'UPDATE `' . $pai_db->getTable() . "_options` SET `option_value` = '" . $is_wp_blog_header . "' WHERE `option_name` = 'is_wp_blog_header' LIMIT 1";
					$update[] = 'UPDATE `' . $pai_db->getTable() . "_options` SET `option_value` = '" . $notifybymail . "' WHERE `option_name` = 'notifybymail' LIMIT 1";
					$update[] = 'UPDATE `' . $pai_db->getTable() . "_options` SET `option_value` = '" . $youraddress . "' WHERE `option_name` = 'youraddress' LIMIT 1";
					$update[] = 'UPDATE `' . $pai_db->getTable() . "_options` SET `option_value` = '" . (int)$totalpage_faq . "' WHERE `option_name` = 'totalpage_faq' LIMIT 1";

					if (!empty($username) && $username != $pai->getOption('username')) $update[] = 'UPDATE `' . $pai_db->getTable() . "_options` SET `option_value` = '" . $username . "' WHERE `option_name` = 'username'";

					$pai->setMask($word);

					if (!empty($password) && md5($password . $pai->getMask()) != $pai->getOption('password')) $newpassword = $password;
					else $newpassword = $_POST['currentpass'];

					$update[] = 'UPDATE `' . $pai_db->getTable() . "_options` SET `option_value` = '" . md5($newpassword . $pai->getMask()) . "' WHERE `option_name` = 'password'";
					foreach($update as $query) {
						$pai_db->query($query);
					}
					$pai->resetOptions();

					setcookie($pai_db->getTable() . '_user', $pai->getOption('username'), time()+(86400*365), '/');
					setcookie($pai_db->getTable() . '_pass', 'Loggedin_' . $pai->getOption('password'), time()+(86400*365), '/');
					ob_end_flush();
					echo '<p>Options updated.</p>';
				}
				elseif (empty($_POST['currentpass'])) {
					ob_end_flush();
					$error = new Error('You did not enter your current password. You cannot change the options if you do not enter this. Please go back and try again.');
				}
				else {
					ob_end_flush();
					$error = new Error('Incorrect current password supplied. Please press the back button on your browser to try again.');
				}
				if (isset($error)) $error->display();
			}
			else {
				ob_end_flush();
				?>

				<h2>Options</h2>
				<p>Edit Askably's options here. Please note that if you change your password you may need to clear out your browser's cookies in order to be able to login again.</p>

				<form method="post" action="admin.php?manage=options">
					<p><input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
					<strong><label for="username">Your username:</label></strong><br />
					CASE SENSITIVE<br />
					<input type="text" name="username" id="username" value="<?php echo $pai->getOption('username'); ?>" /></p>

					<p><strong><label for="currentpass">Current password:</label></strong><br />
					CASE SENSITIVE - <strong style="color: red;">You must enter this in order to change any of the settings on this page.</strong><br />
					<input type="password" name="currentpass" id="currentpass" /></p>

					<p><strong><label for="password">New password:</label></strong><br />
					CASE SENSITIVE - only enter this if you want to change your password. <strong>Please only use alphanumeric characters. You may also use these characters: !, @, _, -, . (dot).</strong><br />
					<input type="password" name="password" id="password" /></p>

					<p><strong><label for="confirm_pass">Re-enter new password:</label></strong><br />CASE SENSITIVE - only enter if you are changing your password.<br />
					<input type="password" name="confirm_pass" id="confirm_pass"/></p>

					<p><strong><label for="word">Security word:</label></strong><br />
					In case you forget your password, you will need this to reset it. <strong>This cannot be left blank.</strong><br />
					<input type="text" name="word" id="word" value="<?php echo $pai->getOption('security_word'); ?>" /></p>

					<p><strong><label for="headerfile">Header file you wish to use:</label></strong><br />
					Absolute or relative path - leave blank to use default. <strong>DO NOT</strong> enter a <acronym title="Uniform Resource Locator - usually in this form: http://www.domainname.tld">URL</acronym> here, it will not work!<br />
					NOTE: DO NOT FILL IN THIS PART IF YOU ARE USING WORDPRESS THEMES!<br />
					<input type="text" name="headerfile" id="headerfile" value="<?php echo $pai->getOption('headerfile'); ?>" /></p>

					<p><strong><label for="footerfile">Footer file you wish to use:</label></strong><br />
					As above. Again, do NOT fill in this part if you are using WordPress Themes.<br />
					<input type="text" name="footerfile" id="footerfile" value="<?php echo $pai->getOption('footerfile'); ?>" /></p>

					<p><strong><label for="is_wordpress">Are you using WordPress Themes with Askably?</label></strong> <input type="checkbox" name="is_wordpress" id="is_wordpress" value="yes" <?php if ($pai->getOption('is_wordpress') == 'yes') echo 'checked="checked" '; ?>/><br />If you have themed your site using WordPress (i.e. using get_header() and get_footer()) please check this box.</p>

					<p><strong><label for="is_wp_blog_header">Absolute path to wp-blog-header.php:</label></strong><br />
					If you checked the above option, please enter your FULL ABSOLUTE PATH to wp-blog-header.php here.<br />
					<input type="text" name="is_wp_blog_header" id="is_wp_blog_header" value="<?php echo $pai->getOption('is_wp_blog_header'); ?>" /></p>

					<p><strong><label for="date_format">Date/time format to use for questions:</label></strong><br />
					Currently displays as <strong><?php echo date($pai->getOption('date_format')); ?></strong> - return to this page after changing the value to see how it comes out.<br />
					(See <a href="http://www.php.net/date" title="PHP Manual for Date options">http://www.php.net/date</a> for more information)<br />
					<input type="text" name="date_format" id="date_format" value="<?php echo $pai->getOption('date_format'); ?>" /></p>

					<p><strong><label for="enable_cats">Enable categories?</label></strong> <input type="checkbox" name="enable_cats" id="enable_cats" value="yes" <?php if ($pai->getOption('enable_cats') == 'yes') echo 'checked="checked" '; ?>/></p>

					<p><strong><label for="ipban_enable">Enable <acronym title="Internet Protocol">IP</acronym> address blocking?</label></strong> <input type="checkbox" name="ipban_enable" id="ipban_enable" value="yes" <?php if ($pai->getOption('ipban_enable') == 'yes') echo 'checked="checked" '; ?>/></p>

					<p><strong><label for="antispam_enable">Enable anti-spam (word blocking)?</label></strong> <input type="checkbox" name="antispam_enable" id="antispam_enable" value="yes" <?php if ($pai->getOption('antispam_enable') == 'yes') echo 'checked="checked" '; ?>/></p>

					<p><strong><label for="show_unanswered">Show unanswered questions on the front page?</label></strong> <input type="checkbox" name="show_unanswered" id="show_unanswered" value="yes" <?php if ($pai->getOption('show_unanswered') == 'yes') echo 'checked="checked" '; ?>/></p>

					<p><strong><label for="summary_enable">Enable summary?</label></strong> <input type="checkbox" name="summary_enable" id="summary_enable" value="yes" <?php if ($pai->getOption('summary_enable') == 'yes') echo 'checked="checked" '; ?>/><br />
					Do you want to show a summary of questions by category on the front page?</p>

					<p><strong><label for="titleofpage">Front page title:</label></strong><br />
					This is the title users see at the top of the questions page.<br />
					<input type="text" name="titleofpage" id="titleofpage" value="<?php echo $pai->getOption('titleofpage'); ?>" /></p>

					<p><strong><label for="notifybymail">Notify by e-mail when a new question is asked?</label></strong>  <input type="checkbox" name="notifybymail" id="notifybymail" value="yes" <?php if ($pai->getOption('notifybymail') == 'yes') echo 'checked="checked" '; ?>/><br />Requires a valid e-mail address to be entered below.</p>

					<p><strong><label for="youraddress">Your e-mail address:</label></strong><br />
					You should set this (regardless of whether you want to be notified of new questions) as it is used to reset your password in case you forget it.<br />
					<input type="text" name="youraddress" id="youraddress" value="<?php echo $pai->getOption('youraddress'); ?>"/></p>

					<p><strong><label for="totalpage_faq">Questions per page on the FAQ page:</label></strong><br />
					The FAQ page is the page that visitors to your site see.<br />
					<input type="text" name="totalpage_faq" id="totalpage_faq" value="<?php echo (int)$pai->getOption('totalpage_faq'); ?>" maxlength="3" /></p>

					<p><input type="submit" name="submit" id="submit" value="Submit" /></p>
				</form>

				<?php
			}
			break;

		##### TEMPLATES
		case 'templates':
			if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_templates'])) {
				$pai->checkToken();
				ob_end_flush();

				$form = strip_tags($_POST['question_form'], '<div> <p> <img> <span> <b> <i> <u> <em> <strong> <table> <tr> <td> <th> <br> <br /> <acronym> <abbr> <hr> <hr /> <big> <small> <blockquote> <center> <cite> <fieldset> <ul> <li> <ol> <font> <h1> <h2> <h3> <h4> <h5> <h6> <h7> <q> <thead> <tfoot> <sub> <tt> <tbody> <sup> <kbd> <del> <ins> <label> <legend>');
				$q = strip_tags($_POST['questions'], '<a> <div> <p> <img> <span> <b> <i> <u> <em> <strong> <table> <tr> <td> <th> <br> <br /> <acronym> <abbr> <hr> <hr /> <big> <small> <blockquote> <center> <cite> <fieldset> <ul> <li> <ol> <font> <h1> <h2> <h3> <h4> <h5> <h6> <h7> <q> <thead> <tfoot> <sub> <tt> <tbody> <sup> <kbd> <del> <ins>');
				$summary = strip_tags($_POST['summary'], '<a> <div> <p> <img> <span> <b> <i> <u> <em> <strong> <table> <tr> <td> <th> <br> <br /> <acronym> <abbr> <hr> <hr /> <big> <small> <blockquote> <center> <cite> <fieldset> <ul> <li> <ol> <font> <h1> <h2> <h3> <h4> <h5> <h6> <h7> <q> <thead> <tfoot> <sub> <tt> <tbody> <sup> <kbd> <del> <ins>');
				$success_msg = strip_tags($_POST['success_msg'], '<a> <div> <p> <img> <span> <b> <i> <u> <em> <strong> <table> <tr> <td> <th> <br> <br /> <acronym> <abbr> <hr> <hr /> <big> <small> <blockquote> <center> <cite> <fieldset> <ul> <li> <ol> <font> <h1> <h2> <h3> <h4> <h5> <h6> <h7> <q> <thead> <tfoot> <sub> <tt> <tbody> <sup> <kbd> <del> <ins>');

				$no = '/(onclick|ondblclick|onload|onfocus|onblur|onmouse|onkey=|javascript|alert)/i';
				if (preg_match($no, $form) || preg_match($no, $q) || preg_match($no, $summary) || preg_match($no, $success_msg)) {
					$error = new Error('Please don\'t use JavaScript in your templates.');
					$error->display();
				}

				if (empty($form)) {
					$form = '<p>[[question]] ';
					if ($pai->getOption('enable_cats') == 'yes') $form .= '&nbsp;[[category]] ';
					$form .= '&nbsp; [[submit]]</p>';
				}
				if (empty($q)) {
					$q = '<div class="question-container">
<p class="date">[[date]] ';
					if ($pai->getOption('enable_cats') == 'yes') $q .= '<span class="category">([[category]])</span>';
					$q .= '
</p>
<p class="question"><a href="[[permalink]]" title="Permalink to this question"><strong>[[question]]</strong></a></p>
<p class="answer">[[answer]]</p>
</div>';
				}
				if (empty($summary)) {
					$summary = '<h2>Latest questions</h2>
<h4>[[total]] total, of which [[unanswered]] unanswered';
					if ($pai->getOption('enable_cats') == 'yes') $summary .= ' in [[categories]] categories';
					$summary .= '</h4>';
				}
				if (empty($success_msg)) $success_msg = '<p>Thank you, your question has been successfully added to the database. Look out for an answer soon!</p>';

				if (!strstr(strtolower($form), '[[question]]')) $error[] = new Error('You must have the [[question]] variable in your question form template. Please go back and add it.');
				if (!strstr(strtolower($form), '[[submit]]')) $error[] = new Error('You must have [[submit]] variable in your question form template. Please go back and add it.');
				if (!strstr(strtolower($form), '[[category]]') && $pai->getOption('enable_cats') == 'yes') $error[] = new Error('You must have the [[category]] variable in your question form template. Please go back and add it. If you do not wish to use categories, please disable them on the options page.');

				if (!strstr(strtolower($q), '[[question]]')) $error[] = new Error('You must have the [[question]] variable in your question/answer template. Please go back and add it.');
				if (!strstr(strtolower($q), '[[answer]]')) $error[] = new Error('You must have the [[answer]] variable in your question/answer template. Please go back and add it.');
				if (!strstr(strtolower($q), '[[category]]') && $pai->getOption('enable_cats') == 'yes') $error[] = new Error('You must have the [[category]] variable in your question/answer template. Please go back and add it. If you do not wish to use categories, please disable them on the options page.');

				if (isset($error)) {
					$num = count($error);
					for($i = 0; $i < $num; $i++) {
						if ($i < ($num - 1)) $error[$i]->setDie(false);
						if ($i > 0) $error[$i]->setHeading(false);
						$error[$i]->display();
					}
				}

				if (get_magic_quotes_gpc()) {
					$form = stripslashes($form);
					$q = stripslashes($q);
					$summary = stripslashes($summary);
					$success_msg = stripslashes($success_msg);
				}
				$form = mysql_real_escape_string($form);
				$q = mysql_real_escape_string($q);
				$summary = mysql_real_escape_string($summary);
				$success_msg = mysql_real_escape_string($success_msg);

				if ($pai_db->query('UPDATE `' . $pai_db->getTable() . "_options` SET `option_value` = '" . $form . "' WHERE `option_name` = 'ask_template' LIMIT 1") && $pai_db->query('UPDATE `' . $pai_db->getTable() . "_options` SET `option_value` = '" . $q . "' WHERE `option_name` = 'q_template' LIMIT 1") && $pai_db->query('UPDATE`' . $pai_db->getTable() . "_options` SET `option_value` = '" . $summary . "' WHERE `option_name` = 'sum_template' LIMIT 1") && $pai_db->query('UPDATE`' . $pai_db->getTable() . "_options` SET `option_value` = '" . $success_msg . "' WHERE `option_name` = 'success_msg_template' LIMIT 1")) echo '<p>Templates edited.</p>';
			}
			else {
				ob_end_flush();
				?>
				<h2>Templates</h2>

				<p>Modify how your questions appear here.</p>

				<p><strong>Jump to: <a href="#qf_template" title="Question form template">question form</a> | <a href="#q_template" title="Question template">question/answer layout</a> | <a href="#s_template" title="Summary template">summary layout</a> | <a href="#sm_template" title="Success message template">success message</a></strong></p>

				<form method="post" action="admin.php?manage=templates">
					<fieldset>
						<legend></legend>
						<input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
						<h4><a name="qf_template" id="qf_template"></a><label for="question_form">Question form</label></h4>
						<p>This is the form used to ask questions.</p>
						<p>Key:</p>
						<ul><li><strong>[[question]]</strong> - inserts the question text box</li>
						<li><strong>[[category]]</strong> - inserts the category dropdown menu (if categories are enabled)</li>
						<li><strong>[[submit]]</strong> - displays the submit button.</li></ul>
						<p><textarea name="question_form" id="question_form" cols="30" rows="5" class="template"><?php echo htmlentities($pai->getOption('ask_template')); ?></textarea></p>
					</fieldset>

					<fieldset>
						<legend></legend>
						<h4><a name="q_template" id="q_template"></a><label for="questions">Questions and answers</label></h4>
						<p>How your questions and answers will appear on your site.</p>
						<p>Key:</p>
						<ul><li><strong>[[question]]</strong> - displays the question.</li>
						<li><strong>[[permalink]]</strong> - this question's permanent link (Note: this tag does not create the actual link. Use with a normal &lt;a&gt; tag, e.g. &lt;a href="[[permalink]]"&gt;).</li>
						<li><strong>[[answer]]</strong> - displays the answer.</li>
						<li><strong>[[category]]</strong> - displays the category (if enabled).</li>
						<li><strong>[[date]]</strong> - displays the date and time (depending on format) the question was asked.</li></ul>
						<p><textarea name="questions" id="questions" cols="30" rows="5" class="template"><?php echo htmlentities($pai->getOption('q_template')); ?></textarea></p>
					</fieldset>

					<fieldset>
						<legend></legend>
						<h4><a name="s_template" id="s_template"></a><label for="summary">Question summary</label></h4>
						<p>This is the list of answered/unanswered questions at the top of your recent questions page.</p>
						<p>Key:</p>
						<ul><li><strong>[[total]]</strong> - displays total questions in the database.</li>
						<li><strong>[[answered]]</strong> - displays number of answered questions in the database.</li>
						<li><strong>[[unanswered]]</strong> - displays number of unanswered questions in the database.</li>
						<li><strong>[[categories]]</strong> - displays the number of categories that questions have been asked in (not the total number of categories, just those that contain questions).</li></ul>
						<p><textarea name="summary" id="summary" cols="30" rows="5" class="template"><?php echo htmlentities($pai->getOption('sum_template')); ?></textarea></p>
					</fieldset>

					<fieldset>
						<legend></legend>
						<h4><a name="sm_template" id="sm_template"></a><label for="success_msg">Success message</label></h4>
						<p>This is the message that will appear to users when their question has been successfully added to the database.</p>
						<p><textarea name="success_msg" id="success_msg" cols="30" rows="5" class="template"><?php echo htmlentities($pai->getOption('success_msg_template')); ?></textarea></p>
					</fieldset>

					<p class="center"><input type="submit" name="submit_templates" id="submit_templates" value="Submit" style="padding-left: 2em; padding-right: 2em;" /></p>
				</form>
				<?php
			}
			break;

		##### BLOCKED IPS
		case 'ips':
			if ($pai->getOption('ipban_enable') != 'yes') {
				$error = new Error('IP banning is currently disabled.');
				$error->display();
			}
			if (isset($_GET['action'])) {
				switch($_GET['action']) {

					case 'add':
						if (($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['newip'])) || isset($_GET['ip'])) {
							if (isset($_POST['newip'])) {
								$pai->checkToken();
								$newip = cleaninput($_POST['newip']);
							}
							elseif (isset($_GET['ip'])) {
								$pai->checkToken();
								$newip = cleaninput($_GET['ip']);
							}
							else $pai->killToken();
							ob_end_flush();

							if (!isset($newip) || empty($newip)) $error = new Error('Please enter an IP address.');
							if (!preg_match("^((\d|[1-9]\d|2[0-4]\d|25[0-5]|1\d\d)(?:\.(\d|[1-9]\d|2[0-4]\d|25[0-5]|1\d\d)){3})$^", $newip)) Error('Invalid IP address.');

							$existingips = explode(';', $pai->getOption('banned_ips'));

							if (in_array($newip, $existingips)) $error = new Error('You have already blocked that IP address.');
							if (isset($error)) $error->display();

							if (strlen($pai->getOption('banned_ips')) > 0) $iplist = $pai->getOption('banned_ips') . $newip . ';';
							else $iplist = $newip . ';';

							if ($pai_db->query('UPDATE `' . $pai_db->getTable() . "_options` SET `option_value` = '" . $iplist . "' WHERE `option_name` = 'banned_ips' LIMIT 1")) echo '<p>The IP address ' . $newip . ' will now be unable to ask you any questions.</p>';
						}
						else {
							ob_end_flush();
							?>
							<h2>Block an IP address</h2>
							<p>Please note that blocking an IP address will not completely block a user from your site, it will only stop them from submitting questions using that particular IP address.</p>
							<p><label for="newip">Type the IP to be blocked below, in the form x.x.x.x (x can be up to 3 digits long). You must include all four parts (digit groups) of the address; simply typing x.x.x. or x.x will result in an error. You cannot ban IP ranges using this script.</label></p>
							<form method="post" action="admin.php?manage=ips&amp;action=add">
								<p><input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
								<input type="text" name="newip" id="newip" maxlength="15" />
								<input type="submit" name="add_ip" id="add_ip" value="Add" /></p>
							</form>
							<?php
						}
						break;

					case 'edit':
						if (!isset($_GET['ip']) || !is_numeric($_GET['ip'])) {
							$error = new Error('Invalid IP.');
							$error->display();
						}
						$ip = (int)cleaninput($_GET['ip']);

						if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editip'])) {
							$pai->checkToken();
							ob_end_flush();

							$editip = cleaninput($_POST['editip']);
							if (!preg_match("^((\d|[1-9]\d|2[0-4]\d|25[0-5]|1\d\d)(?:\.(\d|[1-9]\d|2[0-4]\d|25[0-5]|1\d\d)){3})$^", $editip)) $error = new Error('Invalid IP.');
							$iplist = explode(';', $pai->getOption('banned_ips'));

							if (in_array($editip, $iplist)) $error = new Error('You have already blocked that IP.');
							if (isset($error)) $error->display();

							if ($ip < count($iplist) && !empty($iplist[$ip])) {
								$iplist[$ip] = $editip;
								$newips = '';
								for($ipcount = 0; $ipcount < count($iplist); $ipcount++) {
									if (!empty($iplist[$ipcount])) $newips .= $iplist[$ipcount] . ';';
								}

								if (strstr($newips, ';;')) $newips = str_replace(';;', ';', $newips);
								if (substr($newips, 0, 1) == ';') $newips = substr_replace($newips, '', 0, 1);

								if ($pai_db->query('UPDATE `' . $pai_db->getTable() . "_options` SET `option_value` = '" . $newips . "' WHERE `option_name` = 'banned_ips'")) echo '<p>IP address edited successfully.</p>';
							}
							else {
								$error = new Error('There is no blocked IP address with that ID.');
								$error->display();
							}
						}
						else {
							$pai->checkToken();
							ob_end_flush();

							$iplist = explode(';', $pai->getOption('banned_ips'));
							if ($ip < count($iplist) && !empty($iplist[$ip])) { ?>

								<h2>Edit IP address</h2>

								<form method="post" action="admin.php?manage=ips&amp;action=edit&amp;ip=<?php echo $ip; ?>">
									<p><input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
									<input type="text" maxlength="15" name="editip" id="editip" value="<?php echo $iplist[$ip]; ?>" /> <input type="submit" name="submit" id="submit" value="Submit" /></p>
								</form>
								<?php
							}
							else {
								$error = new Error('There is no blocked IP address with that ID.');
								$error->display();
							}
						}
						break;

					case 'delete':
						if (!isset($_GET['ip']) || !is_numeric($_GET['ip'])) {
							$error = new Error('Invalid IP.');
							$error->display();
						}
						$pai->checkToken();
						ob_end_flush();

						$ip = (int)cleaninput($_GET['ip']);

						$iplist = explode(';', $pai->getOption('banned_ips'));
						if ($ip < count($iplist) && !empty($iplist[$ip])) {
							$iplist[$ip] = '';
							$newips = '';
							for($ipcount = 0; $ipcount < count($iplist); $ipcount++) {
								if (!empty($iplist[$ipcount])) $newips .= $iplist[$ipcount] . ';';
							}
							if (strstr($newips, ';;')) $newips = str_replace(';;', ';', $newips);
							if (substr($newips, 0, 1) == ';') $newips = substr_replace($newips, '', 0, 1);

							if ($pai_db->query('UPDATE `' . $pai_db->getTable() . "_options` SET `option_value` = '" . $newips . "' WHERE `option_name` = 'banned_ips'")) echo '<p>The IP address has successfully been unblocked and will now be able to ask you questions.</p>';
						}
						else {
							$error = new Error('There is no blocked IP with that ID.');
							$error->display();
						}
						break;

					default:
						ob_end_flush();
						$error = new Error('Invalid action.');
						$error->display();
				}
			}
			else {
				ob_end_flush();
				echo '<h2>Blocked IP Addresses</h2>';
				if (strlen($pai->getOption('banned_ips')) > 0) {
					$bannedips = explode(';', $pai->getOption('banned_ips'));
					$numofips = (count($bannedips) - 1); ?>

					<p>Currently <?php echo $numofips . ' IP ' . ($numofips > 1 ? 'addresses are' : 'address is'); ?> banned from asking you questions.</p>
					<p>[<a href="admin.php?manage=ips&amp;action=add" title="Block an IP address">Add a new IP address to the block list</a>]</p>

					<ul>
					<?php
					for($ipcount = 0; $ipcount < $numofips; $ipcount++) {
						if(!empty($bannedips[$ipcount])) { ?>
							<li><strong><?php echo $bannedips[$ipcount]; ?></strong> &nbsp; [<a href="admin.php?manage=ips&amp;action=edit&amp;ip=<?php echo $ipcount; ?>&amp;token=<?php echo $token; ?>" title="Edit this IP address">Edit</a>] [<a href="admin.php?manage=ips&amp;action=delete&amp;ip=<?php echo $ipcount; ?>&amp;token=<?php echo $token; ?>" title="Unblock this IP address">Unblock</a>]</li>
							<?php
						}
					}
				echo '</ul>';
				}
				else echo '<p>No IP addresses are currently banned from asking you questions.</p>
				<p>[<a href="admin.php?manage=ips&amp;action=add" title="Block an IP address">Add a new IP address to the block list</a>]</p>';
			}
			break;

		##### ANTISPAM
		case 'antispam':
			if ($pai->getOption('antispam_enable') != 'yes') {
				$error = new Error('Word blocking is not enabled.');
				$error->display();
			}
			if (isset($_GET['action'])) {
				switch($_GET['action']) {

					case 'add':
						if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['newword'])) {
							$pai->checkToken();
							ob_end_flush();

							$replace = array('&', '<', '>', '\\', '[', ']', '/', '"', '*', '\$', '(', ')', '%', '^', '{', '}', '|');
							$newword = cleaninput(str_replace($replace, '', strtolower($_POST['newword'])));

							if (empty($newword)) $error = new Error('No word submitted.');

							$wordlist = explode('|', $pai->getOption('banned_words'));
							if (in_array($newword, $wordlist)) $error = new Error('You have already blocked that word.');

							if (isset($error)) $error->display();

							if (strlen($pai->getOption('banned_words')) > 0) $wordlist = $pai->getOption('banned_words') . $newword . '|';
							else $wordlist = $newword . '|';

							if ($pai_db->query('UPDATE `' . $pai_db->getTable() . "_options` SET `option_value` = '" . $wordlist . "' WHERE `option_name` = 'banned_words' LIMIT 1")) echo '<p>The word ' . $newword . ' will now be blocked from questions.</p>';
						}
						else {
							ob_end_flush();
							?>
							<h2>Block a word</h2>

							<p>Please note that by blocking a word, it will not be allowed at all in questions you are asked. Words are not starred out or censored, instead an error will appear asking the user to change their question. Some common spam content is automatically deleted, such as [url] and [link].</p>
							<p><strong>Do not use symbols (such as &amp; \ / ( ) [ ] $ * ^ % &gt; &lt; ) in the word you want to block or the system will not work.</strong> You may however use spaces to block complete phrases.</p>
							<p><strong>Note:</strong> the system is case insensitive. By blacklisting the word &quot;word&quot;, you will also be blacklisting &quot;WORD&quot;, &quot;wOrD&quot;, &quot;WorD&quot; and other case variants.</p>

							<form method="post" action="admin.php?manage=antispam&amp;action=add">
								<p><input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
								<label for="newword">Word to disallow:</label><br />
								<input type="text" name="newword" id="newword" maxlength="100" />
								<input type="submit" name="add_word" id="add_word" value="Add" /></p>
							</form>

							<?php
						}
						break;

					case 'edit':
						if (!isset($_GET['word']) || !is_numeric($_GET['word'])) {
							$error = new Error('Invalid word.');
							$error->display();
						}
						$word = (int)cleaninput($_GET['word']);

						if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editword'])) {
							$pai->checkToken();
							ob_end_flush();

							$replace = array('&', '<', '>', '\\', '[', ']', '/', '"', '*', '\$', '(', ')', '%', '^', '{', '}', '|', '#');
							$editword = cleaninput(str_replace($replace, '', strtolower($_POST['editword'])));

							if (empty($editword)) {
								$error = new Error('No word submitted.');
								$error->display();
							}

							$wordlist = explode('|', $pai->getOption('banned_words'));

							if (in_array($editword, $wordlist)) {
								$error = new Error('You have already blocked that word.');
								$error->display();
							}

							if ($word < count($wordlist) && !empty($wordlist[$word])) {
								$wordlist[$word] = $editword;
								$newwords = '';
								for($wordcount = 0; $wordcount < count($wordlist); $wordcount++) {
									if (!empty($wordlist[$wordcount])) $newwords .= $wordlist[$wordcount] . '|';
								}
								if (strstr($newwords, '||')) $newwords = str_replace('||', '|', $newwords);
								if (substr($newwords, 0, 1) == '|') $newwords = substr_replace($newwords, '', 0, 1);

								if ($pai_db->query('UPDATE `' . $pai_db->getTable() . "_options` SET `option_value` = '" . $newwords . "' WHERE `option_name` = 'banned_words'")) echo '<p>Word edited successfully.</p>';
							}
							else {
								$error = new Error('There is no blocked word with that ID');
								$error->display();
							}
						}
						else {
							$pai->checkToken();
							ob_end_flush();

							$wordlist = explode('|', $pai->getOption('banned_words'));
							if ($word < count($wordlist) && !empty($wordlist[$word])) { ?>

								<h2>Edit blocked word</h2>
								<p><strong>Do not use symbols (such as &amp; \ / ( ) [ ] $ * ^ % &gt; &lt; ) in the word you want to block or the system will not work.</strong></p>

								<form method="post" action="admin.php?manage=antispam&amp;action=edit&amp;word=<?php echo $word; ?>">
									<p><input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
									<input type="text" maxlength="20" name="editword" id="editword" value="<?php echo $wordlist[$word]; ?>" /> <input type="submit" name="submit" id="submit" value="Submit" /></p>
								</form>

								<?php
							}
							else {
								$error = new Error('There is no blocked word with that ID.');
								$error->display();
							}
						}
						break;

					case 'delete':
						if (!isset($_GET['word']) || !is_numeric($_GET['word'])) {
							$error = new Error('No word submitted.');
							$error->display();
						}
						$pai->checkToken();
						ob_end_flush();

						$word = (int)cleaninput($_GET['word']);

						$wordlist = explode('|', $pai->getOption('banned_words'));

						if ($word < count($wordlist) && !empty($wordlist[$word])) {
							$wordlist[$word] = '';
							$newwords = '';
							for($wordcount = 0; $wordcount < count($wordlist); $wordcount++) {
								if (!empty($wordlist[$wordcount])) $newwords .= $wordlist[$wordcount] . '|';
							}
							if (strstr($newwords, '||')) $newwords = str_replace('||', '|', $newwords);
							if (substr($newwords, 0, 1) == '|') $newwords = substr_replace($newwords, '', 0, 1);

							if ($pai_db->query('UPDATE `' . $pai_db->getTable() . "_options` SET `option_value` = '" . $newwords . "' WHERE `option_name` = 'banned_words'")) echo '<p>The word has successfully been unblocked and will now be allowed in questions.</p>';
						}
						else {
							$error = new Error('There is no blocked word with that ID.');
							$error->display();
						}
						break;

					default:
						ob_end_flush();
						$error = new Error('Invalid action.');
						$error->display();
				}
			}
			else {
				ob_end_flush();
				echo '<h2>Banned words</h2>';
				if (strlen($pai->getOption('banned_words')) > 0) {
					$bannedwords = explode('|', $pai->getOption('banned_words'));
					$numofwords = (count($bannedwords) - 1);
					?>
					<p>Currently <?php echo $numofwords . ' word' . ($numofwords == 1 ? ' is ' : 's are '); ?>blocked.</p>
					<p>[<a href="admin.php?manage=antispam&amp;action=add" title="Add a new word to the block list">Add a new word to the block list</a>]</p>

					<ul>
					<?php
					for($wordcount = 0; $wordcount < $numofwords; $wordcount++) {
						if(!empty($bannedwords[$wordcount])) { ?>
							<li><strong><?php echo $bannedwords[$wordcount]; ?></strong> &nbsp; [<a href="admin.php?manage=antispam&amp;action=edit&amp;word=<?php echo $wordcount; ?>&amp;token=<?php echo $token; ?>" title="Edit this word">Edit</a>] [<a href="admin.php?manage=antispam&amp;action=delete&amp;word=<?php echo $wordcount; ?>&amp;token=<?php echo $token; ?>" title="Unblock this word">Delete</a>]</li>
								<?php
							}
						}
					echo '</ul>';
				}
				else echo '<p>No words are currently blocked.</p>
				<p>[<a href="admin.php?manage=antispam&amp;action=add" title="Add a new word to the block list">Add a new word to the block list</a>]</p>';
			}
			break;

		##### CATEGORIES
		case 'categories':
			if ($pai->getOption('enable_cats') != 'yes') {
				$error = new Error('Categories are disabled.');
				$error->display();
			}
			if (isset($_GET['action'])) {
				switch($_GET['action']) {

					case 'add':
						$cat = new Category();
						$cat->add();
						break;

					case 'edit':
						if (array_key_exists('id', $_POST)) $cat = new Category((int)$_POST['id']);
						elseif (array_key_exists('id', $_GET)) $cat = new Category((int)$_GET['id']);
						if (isset($cat)) $cat->edit();
						else {
							$error = new Error('Invalid category.');
							$error->display();
						}
						break;

					case 'delete':
						if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
							$error = new Error('Invalid category.');
							$error->display();
						}
						$pai->checkToken();
						ob_end_flush();

						$id = (int)cleaninput($_GET['id']);
						if (empty($id)) {
							$error = new Error('Invalid category.');
							$error->display();
						}

						if ($id == 1) $error = new Error('You cannot delete the default category.');
						if (!$pai_db->get('cat_id', 'cats', '`cat_id` = ' . $id)) $error = new Error('Invalid category.');
						if (isset($error)) $error->display();

						if ($pai_db->query('DELETE FROM `' . $pai_db->getTable() . '_cats` WHERE `cat_id` = ' . $id . ' LIMIT 1')) {
							if ($pai_db->get('q_id', 'main', '`category` = ' . $id)) $pai_db->query('UPDATE `' . $pai_db->getTable() . '` SET `category` = 1 WHERE `category` = ' . $id);
							echo '<p>The category was successfully deleted.</p>';
						}
						break;

					default:
						ob_end_flush();
						$error = new Error('Invalid action.');
						$error->display();
				}
			}
			else {
				ob_end_flush();
				?>
				<h2>Manage your categories</h2>
				<p>Below are the categories of questions you would like to be asked. Here you can edit, add to or delete them. Deleting a category will not delete the questions in it, but those questions will then be reset to the default category. (You cannot delete the default category)</p>

				<?php
				$getcats = $pai_db->query('SELECT `' . $pai_db->getTable() . '_cats`.*, COUNT(`' . $pai_db->getTable() . '`.`q_id`) AS `num` FROM `' . $pai_db->getTable() . '_cats` LEFT JOIN `' . $pai_db->getTable() . '` ON `' . $pai_db->getTable() . '_cats`.`cat_id` = `' . $pai_db->getTable() . '`.`category` GROUP BY `' . $pai_db->getTable() . '_cats`.`cat_id` ORDER BY `cat_name` ASC');
				if (mysql_num_rows($getcats) > 0) {
					echo '<ul>';
					while($cat = mysql_fetch_object($getcats)) {
						echo '<li><strong>' . $cat->cat_name;
						if ($cat->isDefault == 1) echo ' (default)';
						echo '</strong> (' . $cat->num . ') &nbsp; [<a href="admin.php?manage=categories&amp;action=edit&amp;id=' . $cat->cat_id . '&amp;token=' . $token . '" title="Edit the name of this category">Edit</a>]';
						if ($cat->cat_id != 1) echo ' [<a href="admin.php?manage=categories&amp;action=delete&amp;id=' . $cat->cat_id . '&amp;token=' . $token . '" title="Delete this category" onclick="return confirm(\'Are you sure you want to delete this category?\')">Delete</a>]';
						echo '</li>';
					}
					echo '</ul>';
				}
				else echo '<p>There are no categories.</p>';
				echo '<p>[<a href="admin.php?manage=categories&amp;action=add" title="Add a new category">Add new category</a>]</p>';
			}
			break;

		default:
			ob_end_flush();
			$error = new Error('Invalid action.');
			$error->display();
	}
}
#######################################################

#################### SORT BY DATE #####################
else {
	ob_end_flush();

	pages();

	$query = <<<SQL
SELECT `{$pai_db->getTable()}`.*
FROM `{$pai_db->getTable()}`
SQL;

	dopagination($query);
	$query .= ' ORDER BY `dateasked` DESC LIMIT ' . $startfrom . ', ' . ADMIN_PERPAGE; ?>

	<h2 class="question_header">Latest questions</h2>

	<?php
	if ($totalpages > 0) {
		$getqs = $pai_db->query($query);
		pagination($perpage, 'date');
		echo '<ul id="question-list">';
		while($qs = mysql_fetch_object($getqs)) {
			$pai->adminQs($qs);
		}
		echo '</ul>';
	}
	else echo '<p>No questions found.</p>';
}
#######################################################

#################### MISC FUNCTIONS ###################
//CREDIT LINK. DO NOT REMOVE
$display = '<p style="text-align: center;">Powered by <a href="http://not-noticeably.net/scripts/askably/" title="Askably">Askably 3.1</a></p>';

//TERMINATE SESSION (but not if answering a question!)
if (!isset($_GET['inline'])) {
	$pai->adminLogout();

	eval(base64_decode('aWYgKGlzc2V0KCRkaXNwbGF5KSAmJiBzdHJzdHIoJGRpc3BsYXksICdQSFBBc2tJdCcpKSB7IGVjaG8gJGRpc3BsYXk7IH0gZWxzZSB7IGVjaG8gJzxwIHN0eWxlPSJ0ZXh0LWFsaWduOiBjZW50ZXI7Ij5Qb3dlcmVkIGJ5IDxhIGhyZWY9Imh0dHA6Ly9ub3Qtbm90aWNlYWJseS5uZXQvc2NyaXB0cy9waHBhc2tpdC8iIHRpdGxlPSJQSFBBc2tJdCI+UEhQQXNrSXQgMy4wPC9hPjwvcD4nOyB9'));
//---------------------------

//FOOTER
	echo '</div>
	</div>
	</body>
	</html>';
}
#######################################################
?>