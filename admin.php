<?php
/*
  ==============================================================================================
  PHPAskIt 3.0 © 2005-2008 Amelie M.
  ==============================================================================================
  																								*/

#################################### ADMINISTRATION PANEL ######################################


##################### DIAGNOSTICS #####################
define('PAI_IN', true);

//CSRF PROTECTION
session_start();
if (!isset($_SESSION['pai_token'])) $_SESSION['pai_token'] = $token = md5(uniqid(rand(), TRUE));
else $token = $_SESSION['pai_token'];
$_SESSION['pai_time'] = time();

if (!file_exists('functions.php')) { ?>
	<h1>Error</h1>
	<strong><code>functions.php</code></strong> could not be found. Without this file, the script cannot operate. Please make sure it is present.</p>
	<?php
	exit;
}
require 'functions.php';

if (!@$pai->getoption('username')) { ?>
	<h1>Error</h1>
	<p>Please run <strong><a href="install.php" title="install.php"><code>install.php</code></a></strong> before accessing this page.</p>
	<?php
	exit;
}
elseif (file_exists('install.php')) { ?>
	<h1>Error</h1>
	<p>Please delete install.php before using the script. You will not need to run it again.</p>
	<?php
	exit;
}

if (@$pai->getoption('version') != '3.0') { ?>
	<h1>Error</h1>
	<p>You need to <a href="upgrade.php" title="Upgrade">upgrade PHPAskIt</a> before you can view this page.</p>
	<?php
	exit;
}

$pai->dologin();
$pai->isloggedin();
#######################################################

############# SUMMARIES, NAVIGATION, ETC. #############
ob_start();

define('IS_ADMIN', true);
if (isset($_POST['qsperpage']) && !empty($_POST['qsperpage'])) {
	$qsperpage = (int)$pai->cleaninput($_POST['qsperpage']);

	if (!is_numeric($qsperpage) || $qsperpage < 1 || $qsperpage > 999) $qsperpage = 10;
	setcookie($pai->table . '_QsPerPage', $qsperpage, (time() + (86400 * 365)), '/');
	header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
}
elseif (isset($_COOKIE[$pai->table . '_QsPerPage']) && !empty($_COOKIE[$pai->table . '_QsPerPage']) && is_numeric($_COOKIE[$pai->table . '_QsPerPage'])) define('ADMIN_PERPAGE', (int)$pai->cleaninput($_COOKIE[$pai->table . '_QsPerPage']));
else define('ADMIN_PERPAGE', 10);

$pai->adminheader();

echo '<div id="container">';

$total = mysql_fetch_object($pai->query('SELECT COUNT(`q_id`) AS `num` FROM `' . $pai->table . '`'));
$unanswered = mysql_fetch_object($pai->query('SELECT COUNT(`q_id`) AS `num` FROM `' . $pai->table . "` WHERE `answer` = '' OR `answer` = NULL"));
$cats = mysql_fetch_object($pai->query('SELECT COUNT(DISTINCT `category`) AS `num` FROM `' . $pai->table . '`'));
?>

	<h1 id="header"><a href="admin.php" title="Back to main admin page">PHPAskIt</a></h1>

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
		<li><a href="admin.php?sort=unanswered" title="View unanswered questions"<?php if ($active == 'unans') echo ' class="active"'; ?>>Unanswered (<?php echo $unanswered->num; ?>)</a></li>
	<?php if ($pai->getoption('enable_cats') == 'yes') { ?>
		<li><a href="admin.php?manage=categories" title="Manage categories"<?php if ($active == 'cats') echo ' class="active"'; ?>>Categories</a></li>
	<?php }
	if ($pai->getoption('ipban_enable') == 'yes') { ?>
		<li><a href="admin.php?manage=ips" title="Manage blocked IP addresses"<?php if ($active == 'ips') echo ' class="active"'; ?>>Blocked IPs</a></li>
	<?php }
	if ($pai->getoption('antispam_enable') == 'yes') { ?>
		<li><a href="admin.php?manage=antispam" title="Manage blocked words"<?php if ($active == 'spam') echo ' class="active"'; ?>>Blocked words</a></li>
	<?php } ?>
		<li><a href="admin.php?manage=options" title="Edit options"<?php if ($active == 'opt') echo ' class="active"'; ?>>Options</a></li>
		<li><a href="admin.php?manage=templates" title="Edit templates"<?php if ($active == 'temp') echo ' class="active"'; ?>>Templates</a></li>
		<li><a href="index.php?recent" title="Questions page">Recent</a></li>

	<?php if ($pai->getoption('enable_cats') == 'yes' && $pai->getoption('summary_enable') == 'yes') { ?>
		<li><a href="index.php" title="Summary page">Summary</a></li>
	<?php } ?>
	</ul>


	<div id="side">
		<h3>Summary</h3>

		<p>You are logged in as <strong><?php echo $pai->getoption('username'); ?></strong>. (<a href="admin.php?process=logout" title="Logout">Logout?</a>)</p>
		<p><strong>Quick stats</strong></p>
		<ul>
			<li>Total questions: <strong><?php echo $total->num; ?></strong></li>
			<li>Unanswered questions: <strong><?php echo $unanswered->num; ?></strong></li>
		<?php if ($pai->getoption('enable_cats') == 'yes') { ?>
			<li>Questions in <strong><?php echo $cats->num . ($cats->num == 1 ? ' category' : ' categories'); ?></strong></li>
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
	$pai->pages();

	$query = 'SELECT * FROM `' . $pai->table . "` WHERE `answer` = ''";
	$pai->dopagination($query);
	$query .= ' ORDER BY `dateasked` DESC LIMIT ' . $startfrom . ',' . ADMIN_PERPAGE;

	if ($totalpages > 0) { ?>
		<h2 class="question_header">Unanswered questions (<?php echo $unanswered->num; ?>)</h2>
		<?php
		$getqs = $pai->query($query);
		$pai->pagination($perpage, 'unanswered');
		echo '<ul id="question-list">';
		while($qs = mysql_fetch_object($getqs)) {
			$pai->adminqs($qs);
		}
		echo '</ul>';
	}
	else echo '<p>No questions to answer.</p>';
}
#######################################################

####### ALL QUESTIONS FROM A PARTICULAR CATEGORY ######
elseif (isset($_GET['category']) && !empty($_GET['category']) && is_numeric($_GET['category'])) {
	ob_end_flush();
	if ($pai->getoption('enable_cats') != 'yes') pai_error('Categories are disabled. To enable them, go to the <a href="admin.php?manage=options" title="Options page">options panel</a> and check &quot;enable categories&quot;.');
	if (!$cat = $pai->getfromdb('cat_name', 'cats', '`cat_id` = ' . (int)$pai->cleaninput($_GET['category']), 1)) pai_error('Invalid category.');

	$pai->pages();

	$query = 'SELECT * FROM `' . $pai->table . "` WHERE `category` = '" . (int)$pai->cleaninput($_GET['category']) . "'";
	$pai->dopagination($query);
	$query .= ' ORDER BY `q_id` DESC LIMIT ' . $startfrom . ',' . ADMIN_PERPAGE;

	if ($totalpages > 0) {
		?>
		<h2 class="question_header"><?php echo $totalpages . ($totalpages == 1 ? ' question' : ' questions'); ?> in the &quot;<?php echo $cat; ?>&quot; category</h2>
		<?php
		$getqs = $pai->query($query);
		$pai->pagination($perpage, 'bycat');
		echo '<ul id="question-list">';
		while($qs = mysql_fetch_object($getqs)) {
			$pai->adminqs($qs);
		}
		echo '</ul>';
	}
	else echo '<p>No questions in this category.</p>';
}
#######################################################

##################### SEARCH ##########################
elseif (isset($_GET['search'])) {
	ob_end_flush();
	$getsearch = $pai->cleaninput($_GET['search']);

	if (empty($getsearch)) pai_error('No search term entered.');

	$pai->pages();

	$query = 'SELECT * FROM `' . $pai->table . "` WHERE `question` LIKE '%" . $getsearch . "%' OR `answer` LIKE '%" . $getsearch . "%' OR `ip` LIKE '%" . $getsearch . "%'";
	$pai->dopagination($query);
	$query .= ' ORDER BY `q_id` DESC LIMIT ' . $startfrom . ',' . ADMIN_PERPAGE;

	if ($totalpages > 0) {
		$getqs = $pai->query($query);

		echo '<h2 class="question_header">' . $totalpages . ' ' . ($totalpages == 1 ? 'question' : 'questions') . ' containing the term &quot;' . stripslashes($getsearch) . '&quot; ' . ($totalpages == 1 ? 'was' : 'were') . ' found.</h2>';

		$pai->pagination($perpage, 'search');
		echo '<ul id="question-list">';
		while ($qs = mysql_fetch_object($getqs)) {
			$pai->adminqs($qs);
		}
		echo '</ul>';
	}
	else echo '<p>No items containing the term &quot;' . stripslashes($getsearch) . '&quot; were found.</p>';
}
#######################################################

################# QUESTION PERMALINKS #################
elseif (isset($_GET['q']) && !empty($_GET['q']) && is_numeric($_GET['q'])) {
	ob_end_flush();
	if (!$pai->getfromdb('q_id', 'main', '`q_id` = ' . (int)$pai->cleaninput($_GET['q']), 1)) pai_error('Invalid question.');
	$q = mysql_fetch_object($pai->query('SELECT * FROM `' . $pai->table . '` WHERE `q_id` = ' . (int)$pai->cleaninput($_GET['q']) . ' LIMIT 1'));

	echo '<ul id="question-list">';
	$pai->adminqs($q);
	echo '</ul>';
}
#######################################################

################### DELETE FUNCTION ###################
elseif (isset($_GET['delete']) && !empty($_GET['delete']) && is_numeric($_GET['delete'])) {
	$pai->checktoken(false, true);
	ob_end_flush();

	if (!$pai->getfromdb('q_id', 'main', '`q_id` = ' . (int)$pai->cleaninput($_GET['delete']), 1)) pai_error('Invalid question.');
	if ($pai->query('DELETE FROM `' . $pai->table . '` WHERE `q_id` = ' . (int)$pai->cleaninput($_GET['delete']) . ' LIMIT 1')) echo '<p>Question successfully deleted.</p>';
}
#######################################################

################### EDIT FUNCTIONS ####################
elseif (isset($_GET['edit'])) {
	switch($_GET['edit']) {

		##### EDIT QUESTIONS
		case 'question':
			if ($_SERVER['REQUEST_METHOD'] == 'POST' && (isset($_POST['id']) && !empty($_POST['id']))) {
				if (isset($_GET['inline'])) $pai->checktoken(true, false, true);
				else $pai->checktoken();
				foreach($_POST as $key => $value) {
					$$key = $pai->cleaninput($value);
					if (empty($value)) {
						if (isset($_GET['inline'])) {
							ob_end_clean();
							exit('<strong>Error saving question:</strong><br />Missing parameter: ' . $key);
						}
						else {
							ob_end_flush();
							pai_error('Missing parameter: ' . $key);
						}
					}
				}
				if (!$pai->getfromdb('q_id', 'main', '`q_id` = ' . (int)$id, 1)) {
					if (isset($_GET['inline'])) {
						ob_end_clean();
						exit('<strong>Error saving question:</strong><br />Invalid question ID.');
					}
					else {
						ob_end_flush();
						pai_error('Invalid question ID.');
					}
				}
				if ($pai->query('UPDATE `' . $pai->table . "` SET `question` = '" . $question . "' WHERE `q_id` = " . (int)$id . ' LIMIT 1')) {
					if (isset($_GET['inline'])) {
						ob_end_clean();
						echo '<a href="admin.php?q=' . (int)$id . '" title="Permalink to this question">' . stripslashes(strip_tags($question)) . '</a>';
					}
					else {
						ob_end_flush();
						echo '<p>Question modified.</p>';
					}
				}
				else {
					if (isset($_GET['inline'])) {
						ob_end_clean();
						exit('<strong>Error modifying question:</strong><br />Could not contact database.');
					}
					else {
						ob_end_flush();
						pai_error('Could not modify question.');
					}
				}
			}
			else {
				if (!isset($_GET['qu']) || (empty($_GET['qu']) || !is_numeric($_GET['qu']))) {
					if (isset($_GET['inline'])) {
						ob_end_clean();
						exit('<strong>Error getting question:</strong><br />Invalid question.');
					}
					else {
						ob_end_flush();
						pai_error('Invalid question.');
					}
				}
				if (!$pai->getfromdb('q_id', 'main', '`q_id` = ' . (int)$pai->cleaninput($_GET['qu']), 1)) {
					if (isset($_GET['inline'])) {
						ob_end_clean();
						exit('<strong>Error getting question:</strong><br />Invalid question.');
					}
					else {
						ob_end_flush();
						pai_error('Invalid question.');
					}
				}
				if (!isset($_GET['inline'])) {
					$pai->checktoken(false, true);
					ob_end_flush();
					$getq = $pai->query('SELECT `q_id`, `question`, UNIX_TIMESTAMP(`dateasked`) AS `date`, `ip` FROM `' . $pai->table . '` WHERE `q_id` = ' . (int)$pai->cleaninput($_GET['qu']) . ' LIMIT 1');
					if (mysql_num_rows($getq) < 1) pai_error('Invalid question.');
					$q = mysql_fetch_object($getq); ?>

					<h2>Editing question #<?php echo $q->q_id; ?></h2>

					<p>Original question: <strong>&quot;<?php echo $q->question; ?>&quot;</strong></p>
					<p>Asked by <?php echo $q->ip; ?> on <?php echo date($pai->getoption('date_format'), $q->date); ?></p>

					<form method="post" action="admin.php?edit=question">
						<p><input type="hidden" name="id" id="id" value="<?php echo $q->q_id; ?>" />
						<input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
						<label for="question">Question:</label><br />
						<textarea rows="5" cols="45" name="question" id="question"><?php echo $q->question; ?></textarea><br />
						<input type="submit" name="submit_question" id="submit_question" value="Edit question" /></p>
					</form>

					<?php
				}
				else {
					ob_end_clean();
					$question = $pai->getfromdb('question', 'main', '`q_id` = ' . (int)$pai->cleaninput($_GET['qu']), 1);
					?>
					<form onsubmit="return submitQuestion(this);" action="admin.php?edit=question&amp;inline=true" onblur="return submitQuestion(this)" onchange="return submitQuestion(this)">
						<input type="hidden" name="id" id="id" value="<?php echo (int)$pai->cleaninput($_GET['qu']); ?>" />
						<input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
						<input type="text" name="question" id="question" style="width: 99%;" value="<?php echo strip_tags($question); ?>" /><br />
						<input type="submit" value="Save question" name="submit_question" id="submit_question" />
					</form>
					<?php
				}
			}
			break;

		##### EDIT ANSWERS
		case 'answer':
			if ($_SERVER['REQUEST_METHOD'] == 'POST' && (isset($_POST['id']) && !empty($_POST['id']))) {
				if (isset($_GET['inline'])) $pai->checktoken(true, false, true);
				else $pai->checktoken();

				foreach($_POST as $key => $value) {
					$$key = $pai->cleaninput($value);
					if ($key != 'answer' && empty($value)) {
						if (isset($_GET['inline'])) {
							ob_end_clean();
							exit('<strong>Error saving answer:</strong><br />Missing parameter: ' . $key);
						}
						else {
							ob_end_flush();
							pai_error('Missing parameter: ' . $key);
						}
					}
				}
				if (!$pai->getfromdb('q_id', 'main', '`q_id` = ' . (int)$id, 1)) {
					if (isset($_GET['inline'])) {
						ob_end_clean();
						exit('<strong>Error saving answer:</strong><br />Invalid question ID.');
					}
					else {
						ob_end_flush();
						pai_error('Invalid question ID.');
					}
				}

				$answer = str_replace("\\r", "\r", $answer);
				$answer = str_replace("\\n", "\n", $answer);

				if ($pai->query('UPDATE `' . $pai->table . "` SET `answer` = '" . nl2br($answer) . "' WHERE `q_id` = " . (int)$id . ' LIMIT 1')) {
					if (isset($_GET['inline'])) {
						ob_end_clean();
						if (!empty($answer)) echo nl2br(stripslashes($answer));
						else echo '(No answer)';
					}
					else {
						ob_end_flush();
						echo '<p>Your answer has been saved.</p>';
					}
				}
				else {
					if (isset($_GET['inline'])) {
						ob_end_clean();
						exit('<strong>Error saving answer:</strong><br />Could not contact database.');
					}
					else {
						ob_end_flush();
						pai_error('Could not save answer.');
					}
				}
			}
			else {
				if (!isset($_GET['qu']) || (empty($_GET['qu']) || !is_numeric($_GET['qu']))) {
					if (isset($_GET['inline'])) {
						ob_end_clean();
						exit('<strong>Error getting answer:</strong><br />Invalid question.');
					}
					else {
						ob_end_flush();
						pai_error('Invalid question.');
					}
				}
				if (!$pai->getfromdb('q_id', 'main', '`q_id` = ' . (int)$pai->cleaninput($_GET['qu']), 1)) {
					if (isset($_GET['inline'])) {
						ob_end_clean();
						exit('<strong>Error getting answer:</strong><br />Invalid question.');
					}
					else {
						ob_end_flush();
						pai_error('Invalid question.');
					}
				}
				if (!isset($_GET['inline'])) {
					$pai->checktoken(false, true);
					ob_end_flush();

					$getanswer = $pai->query('SELECT * FROM `' . $pai->table . '` WHERE `q_id` = ' . (int)$pai->cleaninput($_GET['qu']) . ' LIMIT 1');
					$answer = mysql_fetch_object($getanswer); ?>

					<h2>Answering question #<?php echo $answer->q_id; ?>: &quot;<?php echo $answer->question; ?>&quot;</h2>

					<p>Asked by <?php echo $answer->ip; ?> on <?php echo date($pai->getoption('date_format'), strtotime($answer->dateasked)); ?> </p>

					<form method="post" action="admin.php?edit=answer">
						<p><input type="hidden" name="id" id="id" value="<?php echo $answer->q_id; ?>" />
						<input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
						Answer this question:<br />
						<textarea rows="5" cols="45" name="answer" id="answer"><?php echo strip_tags($answer->answer); ?></textarea><br />
						<input type="submit" name="submit_answer" id="submit_answer" value="Answer" /></p>
					</form>

					<?php
				}
				else {
					ob_end_clean();
					$answer = $pai->getfromdb('answer', 'main', '`q_id` = ' . (int)$pai->cleaninput($_GET['qu']), 1);
					?>
					<form onsubmit="return submitAnswer(this);" action="admin.php?edit=answer&amp;inline=true" onblur="return submitAnswer(this)" onchange="return submitAnswer(this)">
						<input type="hidden" name="id" id="id" value="<?php echo (int)$pai->cleaninput($_GET['qu']); ?>" />
						<input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
						<textarea name="answer" id="answer" style="width: 99%;" rows="10" cols="70"><?php echo strip_tags($answer); ?></textarea><br />
						<input type="submit" value="Save answer" name="submit_answer" />
					</form>
					<?php
				}
			}
			break;

		##### EDIT CATEGORIES
		case 'category':
			if ($pai->getoption('enable_cats') != 'yes') pai_error('Categories are disabled.');
			if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_category'])) {
				$pai->checktoken();
				ob_end_flush();
				foreach($_POST as $key => $value) {
					$$key = $pai->cleaninput($value);
					if (empty($$key)) pai_error('Missing parameter: ' . $key);
				}
				if (!$pai->getfromdb('q_id', 'main', '`q_id` = ' . (int)$pai->cleaninput($_POST['id']), 1)) pai_error('Invalid question.');
				if (!$pai->getfromdb('cat_id', 'cats', '`cat_id` = ' . (int)$pai->cleaninput($_POST['category']), 1)) pai_error('Invalid category.');

				if ($pai->query('UPDATE `' . $pai->table . '` SET `category` = ' . (int)$pai->cleaninput($_POST['category']) . ' WHERE `q_id` = ' . (int)$pai->cleaninput($_POST['id']) . ' LIMIT 1')) echo '<p>The category for this question has been successfully modified.</p>';
			}
			else {
				if (!isset($_GET['qu']) || (empty($_GET['qu']) || !is_numeric($_GET['qu']))) pai_error('Invalid question.');
				if (!$pai->getfromdb('q_id', 'main', '`q_id` = ' . (int)$pai->cleaninput($_GET['qu']), 1)) pai_error('Invalid question.');
				$pai->checktoken(false, true);
				ob_end_flush();
				$getcat = $pai->query('SELECT `q_id`, `question`, `category`, UNIX_TIMESTAMP(`dateasked`) AS `date`, `ip` FROM `' . $pai->table . '` WHERE `q_id` = ' . (int)$pai->cleaninput($_GET['qu']) . ' LIMIT 1');
				$cat = mysql_fetch_object($getcat); ?>

				<h2>Editing category of question #<?php echo $cat->q_id; ?></h2>

				<p><strong>&quot;<?php echo $cat->question; ?>&quot;</strong></p>
				<p>Asked by <?php echo $cat->ip; ?> on <?php echo date($pai->getoption('date_format'), $cat->date); ?></p>

				<p>Current category: <?php echo $pai->getfromdb('cat_name', 'cats', '`cat_id` = ' . (int)$cat->category, 1); ?></p>

				<form method="post" action="admin.php?edit=category">
					<p><input type="hidden" name="id" id="id" value="<?php echo $cat->q_id; ?>" />
					<input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
					<select name="category" id="category">
					<?php
					$getcats = $pai->query('SELECT * FROM `' . $pai->table . '_cats` ORDER BY `cat_name` ASC');
					while ($cats = mysql_fetch_object($getcats)) {
						echo '
						<option value="' . $cats->cat_id . '">' . $cats->cat_name . '</option>';
					}
					?>
					</select><br />
					<input type="submit" name="submit_category" id="submit_category" value="Edit question" /></p>
				</form>
				<?php
			}
			break;

		default:
			ob_end_flush();
			echo '<p>Invalid action.</p>';
	}
}
#######################################################

############### MANAGEMENT AND OPTIONS ################
elseif (isset($_GET['manage']) && !empty($_GET['manage'])) {
	switch($_GET['manage']) {

		##### OPTIONS
		case 'options':
			if (isset($_POST['submit']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
				$pai->checktoken();

				if (isset($_POST['currentpass']) && !empty($_POST['currentpass']) && md5($_POST['currentpass'] . $pai->mask) == $pai->getoption('password')) {
					foreach($_POST as $key => $value) {
						$$key = $pai->cleaninput($value);
					}
					if (!empty($password)) {
						if ($confirm_pass != $password) pai_error('Passwords did not match, try again.');
						$replace = array('&', '<', '>', '\\', '[', ']', '/', '"', '*', '\$', '(', ')', '%', '^', '{', '}', '|');
						foreach ($replace as $invalid) {
							if (strstr($_POST['password'], $invalid)) {
								ob_end_flush();
								pai_error('Password contains invalid characters.');
							}
						}
					}
					if (empty($youraddress) || !eregi('^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$', $youraddress)) {
						ob_end_flush();
						pai_error('Please enter a valid email address.');
					}

					$tooeasy = array('phpaskit', 'pai', 'abc123', '123abc', 'q&amp;a', 'question', 'questions', 'questionsandanswers', 'questionandanswer', 'q &amp; a', 'questionsandanswer', 'questionandanswers', 'questions and answer', 'question and answer', 'question and answers', 'questions and answers', 'qanda', 'q and a', 'q & a', 'security word', 'security', 'blah', 'yeah', 'password', 'word', 'test');

					if (!isset($word) || (isset($word) && empty($word))) {
						ob_end_flush();
						pai_error('Enter a security word.');
					}
					elseif (strlen($word) <= 3 || strtolower($word) == strtolower($pai->getoption('username')) || strtolower($word) == $username || strtolower($word) == $pai->getoption('youraddress') || in_array(strtolower($word), $tooeasy)) {
						ob_end_flush();
						pai_error('Your security word is too obvious or too short. Try a different word.');
					}
					elseif (md5(strtolower($word) . $pai->mask) == $pai->getoption('password')) {
						ob_end_flush();
						pai_error('Your security word cannot be the same as your password.');
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
						pai_error('Please do not use a URL for your header file. Only absolute paths may be used.');
					}
					if ($is_wordpress == 'no' && strstr($footerfile, 'http://')) {
						ob_end_flush();
						pai_error('Please do not use a URL for your footer file. Only absolute paths may be used.');
					}

					if ($is_wordpress == 'yes') {
						if (empty($is_wp_blog_header)) {
							ob_end_flush();
							pai_error('Please enter your absolute path to wp-blog-header.php if you wish to use WordPress Themes. If not, please uncheck the appropriate option.');
						}
						elseif (strstr($is_wp_blog_header, 'http://')) {
							ob_end_flush();
							pai_error('Please enter an absolute path to wp-blog-header.php, NOT a URL.');
						}
						elseif (!file_exists($is_wp_blog_header)) {
							ob_end_flush();
							pai_error('Your path to wp-blog-header.php appears to be incorrect, as PHPAskIt cannot find it. Please go back and try again.');
						}
					}

					$update = array();
					$update[] = 'UPDATE `' . $pai->table . "_options` SET `option_value` = '" . $word . "' WHERE `option_name` = 'security_word' LIMIT 1";
					$update[] = 'UPDATE `' . $pai->table . "_options` SET `option_value` = '" . $headerfile . "' WHERE `option_name` = 'headerfile' LIMIT 1";
					$update[] = 'UPDATE `' . $pai->table . "_options` SET `option_value` = '" . $footerfile . "' WHERE `option_name` = 'footerfile' LIMIT 1";
					$update[] = 'UPDATE `' . $pai->table . "_options` SET `option_value` = '" . $date_format . "' WHERE `option_name` = 'date_format' LIMIT 1";
					$update[] = 'UPDATE `' . $pai->table . "_options` SET `option_value` = '" . $enable_cats . "' WHERE `option_name` = 'enable_cats' LIMIT 1";
					$update[] = 'UPDATE `' . $pai->table . "_options` SET `option_value` = '" . $ipban_enable . "' WHERE `option_name` = 'ipban_enable' LIMIT 1";
					$update[] = 'UPDATE `' . $pai->table . "_options` SET `option_value` = '" . $antispam_enable . "' WHERE `option_name` = 'antispam_enable' LIMIT 1";
					$update[] = 'UPDATE `' . $pai->table . "_options` SET `option_value` = '" . $show_unanswered . "' WHERE `option_name` = 'show_unanswered' LIMIT 1";
					$update[] = 'UPDATE `' . $pai->table . "_options` SET `option_value` = '" . $summary_enable . "' WHERE `option_name` = 'summary_enable' LIMIT 1";
					$update[] = 'UPDATE `' . $pai->table . "_options` SET `option_value` = '" . $titleofpage . "' WHERE `option_name` = 'titleofpage' LIMIT 1";
					$update[] = 'UPDATE `' . $pai->table . "_options` SET `option_value` = '" . $is_wordpress . "' WHERE `option_name` = 'is_wordpress' LIMIT 1";
					$update[] = 'UPDATE `' . $pai->table . "_options` SET `option_value` = '" . $is_wp_blog_header . "' WHERE `option_name` = 'is_wp_blog_header' LIMIT 1";
					$update[] = 'UPDATE `' . $pai->table . "_options` SET `option_value` = '" . $notifybymail . "' WHERE `option_name` = 'notifybymail' LIMIT 1";
					$update[] = 'UPDATE `' . $pai->table . "_options` SET `option_value` = '" . $youraddress . "' WHERE `option_name` = 'youraddress' LIMIT 1";
					$update[] = 'UPDATE `' . $pai->table . "_options` SET `option_value` = '" . (int)$totalpage_faq . "' WHERE `option_name` = 'totalpage_faq' LIMIT 1";
					if (!empty($username) && $username != $pai->getoption('username')) $update[] = 'UPDATE `' . $pai->table . "_options` SET `option_value` = '" . $username . "' WHERE `option_name` = 'username'";
					if (!empty($password) && md5($password . $pai->mask) != $pai->getoption('password')) $update[] = 'UPDATE `' . $pai->table . "_options` SET `option_value` = '" . md5($password . $pai->mask) . "' WHERE `option_name` = 'password'";
					foreach($update as $query) {
						$pai->query($query);
					}
					setcookie($pai->table . '_user', $pai->getoption('username'), time()+(86400*365), '/');
					setcookie($pai->table . '_pass', 'Loggedin_' . $pai->getoption('password'), time()+(86400*365), '/');
					ob_end_flush();
					echo '<p>Options updated.</p>';
				}
				elseif (empty($_POST['currentpass'])) {
					ob_end_flush();
					pai_error('You did not enter your current password. You cannot change the options if you do not enter this. Please go back and try again.');
				}
				else {
					ob_end_flush();
					pai_error('Incorrect current password supplied. Please press the back button on your browser to try again.');
				}
			}
			else {
				ob_end_flush();
				?>

				<h2>Options</h2>
				<p>Edit PHPAskIt's options here. Please note that if you change your password you may need to clear out your browser's cookies in order to be able to login again.</p>

				<form method="post" action="admin.php?manage=options">
					<p><input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
					<strong><label for="username">Your username:</label></strong><br />
					CASE SENSITIVE<br />
					<input type="text" name="username" id="username" value="<?php echo $pai->getoption('username'); ?>" /></p>

					<p><strong><label for="currentpass">Current password:</label></strong><br />
					CASE SENSITIVE - <strong style="color: red;">You must enter this in order to change any of the settings on this page.</strong><br />
					<input type="password" name="currentpass" id="currentpass" /></p>

					<p><strong><label for="password">New password:</label></strong><br />
					CASE SENSITIVE - only enter this if you want to change your password. <strong>Do not use these characters: &quot;, &amp;, ', &lt;, &gt; otherwise you will be unable to login.</strong><br />
					<input type="password" name="password" id="password" /></p>

					<p><strong><label for="confirm_pass">Re-enter new password:</label></strong><br />CASE SENSITIVE - only enter if you are changing your password.<br />
					<input type="password" name="confirm_pass" id="confirm_pass"/></p>

					<p><strong><label for="word">Security word:</label></strong><br />
					In case you forget your password, you will need this to reset it. <strong>This cannot be left blank and should not contain any of the aforementioned symbols.</strong><br />
					<input type="text" name="word" id="word" value="<?php echo $pai->getoption('security_word'); ?>" /></p>

					<p><strong><label for="headerfile">Header file you wish to use:</label></strong><br />
					Absolute or relative path - leave blank to use default. <strong>DO NOT</strong> enter a <acronym title="Uniform Resource Locator - usually in this form: http://www.domainname.tld">URL</acronym> here, it will not work!<br />
					NOTE: DO NOT FILL IN THIS PART IF YOU ARE USING WORDPRESS THEMES!<br />
					<input type="text" name="headerfile" id="headerfile" value="<?php echo $pai->getoption('headerfile'); ?>" /></p>

					<p><strong><label for="footerfile">Footer file you wish to use:</label></strong><br />
					As above. Again, do NOT fill in this part if you are using WordPress Themes.<br />
					<input type="text" name="footerfile" id="footerfile" value="<?php echo $pai->getoption('footerfile'); ?>" /></p>

					<p><strong><label for="is_wordpress">Are you using WordPress Themes with PHPAskIt?</label></strong> <input type="checkbox" name="is_wordpress" id="is_wordpress" value="yes" <?php if ($pai->getoption('is_wordpress') == 'yes') echo 'checked="checked" '; ?>/><br />If you have themed your site using WordPress (i.e. using get_header() and get_footer()) please check this box.</p>

					<p><strong><label for="is_wp_blog_header">Absolute path to wp-blog-header.php:</label></strong><br />
					If you checked the above option, please enter your FULL ABSOLUTE PATH to wp-blog-header.php here.<br />
					<input type="text" name="is_wp_blog_header" id="is_wp_blog_header" value="<?php echo $pai->getoption('is_wp_blog_header'); ?>" /></p>

					<p><strong><label for="date_format">Date/time format to use for questions:</label></strong><br />
					Currently displays as <strong><?php echo date($pai->getoption('date_format')); ?></strong> - return to this page after changing the value to see how it comes out.<br />
					(See <a href="http://www.php.net/date" title="PHP Manual for Date options">http://www.php.net/date</a> for more information)<br />
					<input type="text" name="date_format" id="date_format" value="<?php echo $pai->getoption('date_format'); ?>" /></p>

					<p><strong><label for="enable_cats">Enable categories?</label></strong> <input type="checkbox" name="enable_cats" id="enable_cats" value="yes" <?php if ($pai->getoption('enable_cats') == 'yes') echo 'checked="checked" '; ?>/></p>

					<p><strong><label for="ipban_enable">Enable <acronym title="Internet Protocol">IP</acronym> address blocking?</label></strong> <input type="checkbox" name="ipban_enable" id="ipban_enable" value="yes" <?php if ($pai->getoption('ipban_enable') == 'yes') echo 'checked="checked" '; ?>/></p>

					<p><strong><label for="antispam_enable">Enable anti-spam (word blocking)?</label></strong> <input type="checkbox" name="antispam_enable" id="antispam_enable" value="yes" <?php if ($pai->getoption('antispam_enable') == 'yes') echo 'checked="checked" '; ?>/></p>

					<p><strong><label for="show_unanswered">Show unanswered questions on the front page?</label></strong> <input type="checkbox" name="show_unanswered" id="show_unanswered" value="yes" <?php if ($pai->getoption('show_unanswered') == 'yes') echo 'checked="checked" '; ?>/></p>

					<p><strong><label for="summary_enable">Enable summary?</label></strong> <input type="checkbox" name="summary_enable" id="summary_enable" value="yes" <?php if ($pai->getoption('summary_enable') == 'yes') echo 'checked="checked" '; ?>/><br />
					Do you want to show a summary of questions by category on the front page?</p>

					<p><strong><label for="titleofpage">Front page title:</label></strong><br />
					This is the title users see at the top of the questions page.<br />
					<input type="text" name="titleofpage" id="titleofpage" value="<?php echo $pai->getoption('titleofpage'); ?>" /></p>

					<p><strong><label for="notifybymail">Notify by e-mail when a new question is asked?</label></strong>  <input type="checkbox" name="notifybymail" id="notifybymail" value="yes" <?php if ($pai->getoption('notifybymail') == 'yes') echo 'checked="checked" '; ?>/><br />Requires a valid e-mail address to be entered below.</p>

					<p><strong><label for="youraddress">Your e-mail address:</label></strong><br />
					You should set this (regardless of whether you want to be notified of new questions) as it is used to reset your password in case you forget it.<br />
					<input type="text" name="youraddress" id="youraddress" value="<?php echo $pai->getoption('youraddress'); ?>"/></p>

					<p><strong><label for="totalpage_faq">Questions per page on the FAQ page:</label></strong><br />
					The FAQ page is the page that visitors to your site see.<br />
					<input type="text" name="totalpage_faq" id="totalpage_faq" value="<?php echo (int)$pai->getoption('totalpage_faq'); ?>" maxlength="3" /></p>

					<p><input type="submit" name="submit" id="submit" value="Submit" /></p>
				</form>

				<?php
			}
			break;

		##### TEMPLATES
		case 'templates':
			if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_templates'])) {
				$pai->checktoken();
				ob_end_flush();

				$form = strip_tags($_POST['question_form'], '<div> <p> <img> <span> <b> <i> <u> <em> <strong> <table> <tr> <td> <th> <br> <br /> <acronym> <abbr> <hr> <hr /> <big> <small> <blockquote> <center> <cite> <fieldset> <ul> <li> <ol> <font> <h1> <h2> <h3> <h4> <h5> <h6> <h7> <q> <thead> <tfoot> <sub> <tt> <tbody> <sup> <kbd> <del> <ins> <label> <legend>');
				$q = strip_tags($_POST['questions'], '<a> <div> <p> <img> <span> <b> <i> <u> <em> <strong> <table> <tr> <td> <th> <br> <br /> <acronym> <abbr> <hr> <hr /> <big> <small> <blockquote> <center> <cite> <fieldset> <ul> <li> <ol> <font> <h1> <h2> <h3> <h4> <h5> <h6> <h7> <q> <thead> <tfoot> <sub> <tt> <tbody> <sup> <kbd> <del> <ins>');
				$summary = strip_tags($_POST['summary'], '<a> <div> <p> <img> <span> <b> <i> <u> <em> <strong> <table> <tr> <td> <th> <br> <br /> <acronym> <abbr> <hr> <hr /> <big> <small> <blockquote> <center> <cite> <fieldset> <ul> <li> <ol> <font> <h1> <h2> <h3> <h4> <h5> <h6> <h7> <q> <thead> <tfoot> <sub> <tt> <tbody> <sup> <kbd> <del> <ins>');
				$success_msg = strip_tags($_POST['success_msg'], '<a> <div> <p> <img> <span> <b> <i> <u> <em> <strong> <table> <tr> <td> <th> <br> <br /> <acronym> <abbr> <hr> <hr /> <big> <small> <blockquote> <center> <cite> <fieldset> <ul> <li> <ol> <font> <h1> <h2> <h3> <h4> <h5> <h6> <h7> <q> <thead> <tfoot> <sub> <tt> <tbody> <sup> <kbd> <del> <ins>');

				$no = '/(onclick|ondblclick|onload|onfocus|onblur|onmouse|onkey|javascript|alert)/i';
				if (preg_match($no, $form) || preg_match($no, $q) || preg_match($no, $summary) || preg_match($no, $success_msg)) pai_error('Please don\'t use JavaScript in your templates.');

				if (empty($form)) {
					$form = '<p>[[question]] ';
					if ($pai->getoption('enable_cats') == 'yes') $form .= '&nbsp;[[category]] ';
					$form .= '&nbsp; [[submit]]</p>';
				}
				if (empty($q)) {
					$q = '<div class="question-container">
<p class="date">[[date]] ';
					if ($pai->getoption('enable_cats') == 'yes') $q .= '<span class="category">([[category]])</span>';
					$q .= '
</p>
<p class="question"><a href="[[permalink]]" title="Permalink to this question"><strong>[[question]]</strong></a></p>
<p class="answer">[[answer]]</p>
</div>';
				}
				if (empty($summary)) {
					$summary = '<h2>Latest questions</h2>
<h4>[[total]] total, of which [[unanswered]] unanswered';
					if ($pai->getoption('enable_cats') == 'yes') $summary .= ' in [[categories]] categories';
					$summary .= '</h4>';
				}
				if (empty($success_msg)) $success_msg = '<p>Thank you, your question has been successfully added to the database. Look out for an answer soon!</p>';

				if (!strstr(strtolower($form), '[[question]]')) pai_error('You must have the [[question]] variable in your question form template. Please go back and add it.');
				elseif (!strstr(strtolower($form), '[[submit]]')) pai_error('You must have [[submit]] variable in your question form template. Please go back and add it.');
				elseif (!strstr(strtolower($form), '[[category]]') && $pai->getoption('enable_cats') == 'yes') pai_error('You must have the [[category]] variable in your question form template. Please go back and add it. If you do not wish to use categories, please disable them on the options page.');

				if (!strstr(strtolower($q), '[[question]]')) pai_error('You must have the [[question]] variable in your question/answer template. Please go back and add it.');
				elseif (!strstr(strtolower($q), '[[answer]]')) pai_error('You must have the [[answer]] variable in your question/answer template. Please go back and add it.');
				elseif (!strstr(strtolower($q), '[[category]]') && $pai->getoption('enable_cats') == 'yes') pai_error('You must have the [[category]] variable in your question/answer template. Please go back and add it. If you do not wish to use categories, please disable them on the options page.');

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

				if ($pai->query('UPDATE `' . $pai->table . "_options` SET `option_value` = '" . $form . "' WHERE `option_name` = 'ask_template' LIMIT 1") && $pai->query('UPDATE `' . $pai->table . "_options` SET `option_value` = '" . $q . "' WHERE `option_name` = 'q_template' LIMIT 1") && $pai->query('UPDATE`' . $pai->table . "_options` SET `option_value` = '" . $summary . "' WHERE `option_name` = 'sum_template' LIMIT 1") && $pai->query('UPDATE`' . $pai->table . "_options` SET `option_value` = '" . $success_msg . "' WHERE `option_name` = 'success_msg_template' LIMIT 1")) echo '<p>Templates edited.</p>';
			}
			else {
				ob_end_flush();
				?>
				<h2>Templates</h2>

				<p>Modify how your questions appear here.</p>

				<p><strong>Jump to: <a href="#qf_template" title="Question form template">question form</a> | <a href="#q_template" title="Question template">question/answer layout</a> | <a href="#s_template" title="Summary template">summary layout</a> | <a href="#sm_template" title="Success message template">success message</a></strong></p>

				<form method="post" action="admin.php?manage=templates">
					<fieldset>
						<input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
						<h4><a name="qf_template" id="qf_template"></a><label for="question_form">Question form</label></h4>
						<p>This is the form used to ask questions.</p>
						<p>Key:</p>
						<ul><li><strong>[[question]]</strong> - inserts the question text box</li>
						<li><strong>[[category]]</strong> - inserts the category dropdown menu (if categories are enabled)</li>
						<li><strong>[[submit]]</strong> - displays the submit button.</li></ul>
						<p><textarea name="question_form" id="question_form" cols="30" rows="5" class="template"><?php echo htmlentities($pai->getoption('ask_template')); ?></textarea></p>
					</fieldset>

					<fieldset>
						<h4><a name="q_template" id="q_template"></a><label for="questions">Questions and answers</label></h4>
						<p>How your questions and answers will appear on your site.</p>
						<p>Key:</p>
						<ul><li><strong>[[question]]</strong> - displays the question.</li>
						<li><strong>[[permalink]]</strong> - this question's permanent link (Note: this tag does not create the actual link. Use with a normal &lt;a&gt; tag, e.g. &lt;a href="[[permalink]]"&gt;).</li>
						<li><strong>[[answer]]</strong> - displays the answer.</li>
						<li><strong>[[category]]</strong> - displays the category (if enabled).</li>
						<li><strong>[[date]]</strong> - displays the date and time (depending on format) the question was asked.</li></ul>
						<p><textarea name="questions" id="questions" cols="30" rows="5" class="template"><?php echo htmlentities($pai->getoption('q_template')); ?></textarea></p>
					</fieldset>

					<fieldset>
						<h4><a name="s_template" id="s_template"></a><label for="summary">Question summary</label></h4>
						<p>This is the list of answered/unanswered questions at the top of your recent questions page.</p>
						<p>Key:</p>
						<ul><li><strong>[[total]]</strong> - displays total questions in the database.</li>
						<li><strong>[[answered]]</strong> - displays number of answered questions in the database.</li>
						<li><strong>[[unanswered]]</strong> - displays number of unanswered questions in the database.</li>
						<li><strong>[[categories]]</strong> - displays the number of categories that questions have been asked in (not the total number of categories, just those that contain questions).</li></ul>
						<p><textarea name="summary" id="summary" cols="30" rows="5" class="template"><?php echo htmlentities($pai->getoption('sum_template')); ?></textarea></p>
					</fieldset>

					<fieldset>
						<h4><a name="sm_template" id="sm_template"></a><label for="success_msg">Success message</label></h4>
						<p>This is the message that will appear to users when their question has been successfully added to the database.</p>
						<p><textarea name="success_msg" id="success_msg" cols="30" rows="5" class="template"><?php echo htmlentities($pai->getoption('success_msg_template')); ?></textarea></p>
					</fieldset>

					<p class="center"><input type="submit" name="submit_templates" id="submit_templates" value="Submit" style="padding-left: 2em; padding-right: 2em;" /></p>
				</form>
				<?php
			}
			break;

		##### BLOCKED IPS
		case 'ips':
			if ($pai->getoption('ipban_enable') != 'yes') pai_error('IP banning is currently disabled.');
			if (isset($_GET['action'])) {
				switch($_GET['action']) {

					case 'add':
						if (($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['newip'])) || isset($_GET['ip'])) {
							if (isset($_POST['newip'])) {
								$pai->checktoken();
								$newip = $pai->cleaninput($_POST['newip']);
							}
							elseif (isset($_GET['ip'])) {
								$pai->checktoken(false, true);
								$newip = $pai->cleaninput($_GET['ip']);
							}
							else $pai->kill_token();
							ob_end_flush();

							if (!isset($newip) || empty($newip)) pai_error('Please enter an IP address.');
							if (!preg_match("^((\d|[1-9]\d|2[0-4]\d|25[0-5]|1\d\d)(?:\.(\d|[1-9]\d|2[0-4]\d|25[0-5]|1\d\d)){3})$^", $newip)) pai_error('Invalid IP address.');

							$existingips = explode(';', $pai->getoption('banned_ips'));

							if (in_array($newip, $existingips)) pai_error('You have already blocked that IP address.');

							if (strlen($pai->getoption('banned_ips')) > 0) $iplist = $pai->getoption('banned_ips') . $newip . ';';
							else $iplist = $newip . ';';

							if ($pai->query('UPDATE `' . $pai->table . "_options` SET `option_value` = '" . $iplist . "' WHERE `option_name` = 'banned_ips' LIMIT 1")) echo '<p>The IP address ' . $newip . ' will now be unable to ask you any questions.</p>';
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
						if (!isset($_GET['ip']) || !is_numeric($_GET['ip'])) pai_error('Invalid IP.');
						$ip = (int)$pai->cleaninput($_GET['ip']);

						if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editip'])) {
							$pai->checktoken();
							ob_end_flush();

							$editip = $pai->cleaninput($_POST['editip']);
							if (!preg_match("^((\d|[1-9]\d|2[0-4]\d|25[0-5]|1\d\d)(?:\.(\d|[1-9]\d|2[0-4]\d|25[0-5]|1\d\d)){3})$^", $editip)) pai_error('Invalid IP.');
							$iplist = explode(';', $pai->getoption('banned_ips'));

							if (in_array($editip, $iplist)) pai_error('You have already blocked that IP.');

							if ($ip < count($iplist) && !empty($iplist[$ip])) {
								$iplist[$ip] = $editip;
								$newips = '';
								for($ipcount = 0; $ipcount < count($iplist); $ipcount++) {
									if (!empty($iplist[$ipcount])) $newips .= $iplist[$ipcount] . ';';
								}

								if (strstr($newips, ';;')) $newips = str_replace(';;', ';', $newips);
								if (substr($newips, 0, 1) == ';') $newips = substr_replace($newips, '', 0, 1);

								if ($pai->query('UPDATE `' . $pai->table . "_options` SET `option_value` = '" . $newips . "' WHERE `option_name` = 'banned_ips'")) echo '<p>IP address edited successfully.</p>';
							}
							else pai_error('There is no blocked IP address with that ID.');
						}
						else {
							$pai->checktoken(false, true);
							ob_end_flush();

							$iplist = explode(';', $pai->getoption('banned_ips'));
							if ($ip < count($iplist) && !empty($iplist[$ip])) { ?>

								<h2>Edit IP address</h2>

								<form method="post" action="admin.php?manage=ips&amp;action=edit&amp;ip=<?php echo $ip; ?>">
									<p><input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
									<input type="text" maxlength="15" name="editip" id="editip" value="<?php echo $iplist[$ip]; ?>" /> <input type="submit" name="submit" id="submit" value="Submit" /></p>
								</form>
								<?php
							}
							else pai_error('There is no blocked IP address with that ID.');
						}
						break;

					case 'delete':
						if (!isset($_GET['ip']) || !is_numeric($_GET['ip'])) pai_error('Invalid IP.');
						$pai->checktoken(false, true);
						ob_end_flush();

						$ip = (int)$pai->cleaninput($_GET['ip']);

						$iplist = explode(';', $pai->getoption('banned_ips'));
						if ($ip < count($iplist) && !empty($iplist[$ip])) {
							$iplist[$ip] = '';
							$newips = '';
							for($ipcount = 0; $ipcount < count($iplist); $ipcount++) {
								if (!empty($iplist[$ipcount])) $newips .= $iplist[$ipcount] . ';';
							}
							if (strstr($newips, ';;')) $newips = str_replace(';;', ';', $newips);
							if (substr($newips, 0, 1) == ';') $newips = substr_replace($newips, '', 0, 1);

							if ($pai->query('UPDATE `' . $pai->table . "_options` SET `option_value` = '" . $newips . "' WHERE `option_name` = 'banned_ips'")) echo '<p>The IP address has successfully been unblocked and will now be able to ask you questions.</p>';
						}
						else pai_error('There is no blocked IP with that ID.');
						break;

					default:
						ob_end_flush();
						pai_error('Invalid action.');
				}
			}
			else {
				ob_end_flush();
				echo '<h2>Blocked IP Addresses</h2>';
				if (strlen($pai->getoption('banned_ips')) > 0) {
					$bannedips = explode(';', $pai->getoption('banned_ips'));
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
			if ($pai->getoption('antispam_enable') != 'yes') pai_error('Word blocking is not enabled.');
			if (isset($_GET['action'])) {
				switch($_GET['action']) {

					case 'add':
						if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['newword'])) {
							$pai->checktoken();
							ob_end_flush();

							$replace = array('&', '<', '>', '\\', '[', ']', '/', '"', '*', '\$', '(', ')', '%', '^', '{', '}', '|');
							$newword = $pai->cleaninput(str_replace($replace, '', strtolower($_POST['newword'])));

							if (empty($newword)) pai_error('No word submitted.');

							$wordlist = explode('|', $pai->getoption('banned_words'));
							if (in_array($newword, $wordlist)) pai_error('You have already blocked that word.');

							if (strlen($pai->getoption('banned_words')) > 0) $wordlist = $pai->getoption('banned_words') . $newword . '|';
							else $wordlist = $newword . '|';

							if ($pai->query('UPDATE `' . $pai->table . "_options` SET `option_value` = '" . $wordlist . "' WHERE `option_name` = 'banned_words' LIMIT 1")) echo '<p>The word ' . $newword . ' will now be blocked from questions.</p>';
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
						if (!isset($_GET['word']) || !is_numeric($_GET['word'])) pai_error('Invalid word.');
						$word = (int)$pai->cleaninput($_GET['word']);

						if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editword'])) {
							$pai->checktoken();
							ob_end_flush();

							$replace = array('&', '<', '>', '\\', '[', ']', '/', '"', '*', '\$', '(', ')', '%', '^', '{', '}', '|', '#');
							$editword = $pai->cleaninput(str_replace($replace, '', strtolower($_POST['editword'])));

							if (empty($editword)) pai_error('No word submitted.');

							$wordlist = explode('|', $pai->getoption('banned_words'));

							if (in_array($editword, $wordlist)) pai_error('You have already blocked that word.');

							if ($word < count($wordlist) && !empty($wordlist[$word])) {
								$wordlist[$word] = $editword;
								$newwords = '';
								for($wordcount = 0; $wordcount < count($wordlist); $wordcount++) {
									if (!empty($wordlist[$wordcount])) $newwords .= $wordlist[$wordcount] . '|';
								}
								if (strstr($newwords, '||')) $newwords = str_replace('||', '|', $newwords);
								if (substr($newwords, 0, 1) == '|') $newwords = substr_replace($newwords, '', 0, 1);

								if ($pai->query('UPDATE `' . $pai->table . "_options` SET `option_value` = '" . $newwords . "' WHERE `option_name` = 'banned_words'")) echo '<p>Word edited successfully.</p>';
							}
							else pai_error('There is no blocked word with that ID');
						}
						else {
							$pai->checktoken(false, true);
							ob_end_flush();

							$wordlist = explode('|', $pai->getoption('banned_words'));
							if ($word < count($wordlist) && !empty($wordlist[$word])) { ?>

								<h2>Edit blocked word</h2>
								<p><strong>Do not use symbols (such as &amp; \ / ( ) [ ] $ * ^ % &gt; &lt; ) in the word you want to block or the system will not work.</strong></p>

								<form method="post" action="admin.php?manage=antispam&amp;action=edit&amp;word=<?php echo $word; ?>">
									<p><input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
									<input type="text" maxlength="20" name="editword" id="editword" value="<?php echo $wordlist[$word]; ?>" /> <input type="submit" name="submit" id="submit" value="Submit" /></p>
								</form>

								<?php
							}
							else pai_error('There is no blocked word with that ID.');
						}
						break;

					case 'delete':
						if (!isset($_GET['word']) || !is_numeric($_GET['word'])) pai_error('No word submitted.');
						$pai->checktoken(false, true);
						ob_end_flush();

						$word = (int)$pai->cleaninput($_GET['word']);

						$wordlist = explode('|', $pai->getoption('banned_words'));

						if ($word < count($wordlist) && !empty($wordlist[$word])) {
							$wordlist[$word] = '';
							$newwords = '';
							for($wordcount = 0; $wordcount < count($wordlist); $wordcount++) {
								if (!empty($wordlist[$wordcount])) $newwords .= $wordlist[$wordcount] . '|';
							}
							if (strstr($newwords, '||')) $newwords = str_replace('||', '|', $newwords);
							if (substr($newwords, 0, 1) == '|') $newwords = substr_replace($newwords, '', 0, 1);

							if ($pai->query('UPDATE `' . $pai->table . "_options` SET `option_value` = '" . $newwords . "' WHERE `option_name` = 'banned_words'")) echo '<p>The word has successfully been unblocked and will now be allowed in questions.</p>';
						}
						else pai_error('There is no blocked word with that ID.');
						break;

					default:
						ob_end_flush();
						pai_error('Invalid action.');
				}
			}
			else {
				ob_end_flush();
				echo '<h2>Banned words</h2>';
				if (strlen($pai->getoption('banned_words')) > 0) {
					$bannedwords = explode('|', $pai->getoption('banned_words'));
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
			if ($pai->getoption('enable_cats') != 'yes') pai_error('Categories are disabled.');
			if (isset($_GET['action'])) {
				switch($_GET['action']) {

					case 'add':
						if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['newcat'])) {
							$pai->checktoken();
							ob_end_flush();

							$_POST['newcat'] = $pai->cleaninput($_POST['newcat']);
							if (empty($_POST['newcat'])) pai_error('No category submitted.');
							if ($pai->getfromdb('cat_name', 'cats', "`cat_name` = '" . $_POST['newcat'] . "'", 1)) pai_error('You already have a category of that name.');
							if ($pai->query('INSERT INTO `' . $pai->table . "_cats` (`cat_name`) VALUES ('" . $_POST['newcat'] . "')")) echo '<p>The category has been added successfully.</p>';
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
						break;

					case 'edit':
						if (isset($_POST['catname']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
							$pai->checktoken();
							ob_end_flush();

							foreach($_POST as $key => $value) {
								$$key = $pai->cleaninput($value);
								if (empty($$key)) pai_error('Missing parameter: ' . $key);
							}
							if (!$pai->getfromdb('cat_id', 'cats', '`cat_id` = ' . (int)$id, 1)) pai_error('Invalid category.');
							if ($pai->getfromdb('cat_name', 'cats', "`cat_name` = '" . strtolower($catname) . "'", 1)) pai_error('You already have a category with that name.');
							if ($pai->query('UPDATE `' . $pai->table . "_cats` SET `cat_name` = '" . $catname . "' WHERE `cat_id` = " . (int)$id . ' LIMIT 1')) echo '<p>Category updated successfully.</p>';
						}
						else {
							if (!isset($_GET['id']) || !is_numeric($_GET['id'])) pai_error('Invalid category.');
							$pai->checktoken(false, true);
							ob_end_flush();

							$_GET['id'] = (int)$pai->cleaninput($_GET['id']);
							if (empty($_GET['id'])) pai_error('Invalid category.');

							if (!$pai->getfromdb('cat_id', 'cats', '`cat_id` = ' . $_GET['id'], 1)) pai_error('Invalid category.'); ?>

							<h2>Edit your categories</h2>
							<p>Type the new category name below:</p>

							<?php
							$getcats = $pai->query('SELECT * FROM `' . $pai->table . '_cats` WHERE `cat_id` = ' . $_GET['id'] . ' LIMIT 1');
							$cat = mysql_fetch_object($getcats); ?>

							<form id="categoryedit" method="post" action="admin.php?manage=categories&amp;action=edit">
								<p><input type="hidden" name="id" id="id" value="<?php echo $cat->cat_id; ?>" />
								<input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
								<input type="text" name="catname" id="catname" value="<?php echo $cat->cat_name; ?>" />
								<input type="submit" name="submitedit" id="submitedit" value="Edit" /></p>
							</form>
							<?php
						}
						break;

					case 'delete':
						if (!isset($_GET['id']) || !is_numeric($_GET['id'])) pai_error('Invalid category.');
						$pai->checktoken(false, true);
						ob_end_flush();

						$id = (int)$pai->cleaninput($_GET['id']);
						if (empty($id)) pai_error('Invalid category.');

						if ($id == 1) pai_error('You cannot delete the default category.');
						if (!$pai->getfromdb('cat_id', 'cats', '`cat_id` = ' . $id, 1)) pai_error('Invalid category.');

						if ($pai->query('DELETE FROM `' . $pai->table . '_cats` WHERE `cat_id` = ' . $id . ' LIMIT 1')) {
							if ($pai->getfromdb('q_id', 'main', '`category` = ' . $id, 1)) $pai->query('UPDATE `' . $pai->table . '` SET `category` = 1 WHERE `category` = ' . $id);
							echo '<p>The category was successfully deleted.</p>';
						}
						break;

					default:
						ob_end_flush();
						pai_error('Invalid action.');
				}
			}
			else {
				ob_end_flush();
				?>
				<h2>Manage your categories</h2>
				<p>Below are the categories of questions you would like to be asked. Here you can edit, add to or delete them. Deleting a category will not delete the questions in it, but those questions will then be reset to the default category. (You cannot delete the default category)</p>

				<?php
				$getcats = $pai->query('SELECT * FROM `' . $pai->table . '_cats` ORDER BY `cat_name` ASC');
				if (mysql_num_rows($getcats) > 0) {
					echo '<ul>';
					while ($cat = mysql_fetch_object($getcats)) {
						echo '<li><strong>' . $cat->cat_name;
						if ($cat->cat_id == 1) echo ' (default)';
						echo '</strong> (';
						$qs = mysql_fetch_object($pai->query('SELECT COUNT(`q_id`) AS `num` FROM `' . $pai->table . '` WHERE `category` = ' . $cat->cat_id));
						echo $qs->num . ') &nbsp; [<a href="admin.php?manage=categories&amp;action=edit&amp;id=' . $cat->cat_id . '&amp;token=' . $token . '" title="Edit the name of this category">Edit</a>]';
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
			pai_error('Invalid action.');
	}
}
#######################################################

#################### SORT BY DATE #####################
else {
	ob_end_flush();

	$pai->pages();

	$query = 'SELECT * FROM `' . $pai->table . '`';
	$pai->dopagination($query);
	$query .= ' ORDER BY `dateasked` DESC LIMIT ' . $startfrom . ',' . ADMIN_PERPAGE; ?>

	<h2 class="question_header">Latest questions</h2>

	<?php
	if ($totalpages > 0) {
		$getqs = $pai->query($query);
		$pai->pagination($perpage, 'date');
		echo '<ul id="question-list">';
		while($qs = mysql_fetch_object($getqs)) {
			$pai->adminqs($qs);
		}
		echo '</ul>';
	}
	else echo '<p>No questions found.</p>';
}
#######################################################

#################### MISC FUNCTIONS ###################
//CREDIT LINK. DO NOT REMOVE
$display = '<p style="text-align: center;">Powered by <a href="http://not-noticeably.net/scripts/phpaskit/" title="PHPAskIt">PHPAskIt 3.0</a></p>';

//TERMINATE SESSION (but not if answering a question!)
if (!isset($_GET['inline'])) {
	$pai->admin_logout();

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