<?php
/*
  ==============================================================================================
  Askably 3.1 © 2005-2009 Amelie M.
  ==============================================================================================
*/

############################# QUESTIONS PAGE. END-USER INTERFACE :) ############################


##################### DIAGNOSTICS #####################
define('PAI_IN', true);

if (!file_exists('functions.php')) { ?>
	<h1>Error</h1>
	<strong><code>functions.php</code></strong> could not be found. Without this file, the script cannot operate. Please make sure it is present.</p>
	<?php
	exit;
}
require 'functions.php';

check_stuff();

if (($pai->getOption('enable_cats') != 'yes' || $pai->getOption('summary_enable') != 'yes') && $_SERVER['REQUEST_METHOD'] != 'POST' && empty($_SERVER['QUERY_STRING'])) {
	header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '?recent');
	exit;
}

if ($pai->getOption('is_wordpress') == 'yes') {
	include $pai->getOption('is_wp_blog_header');
	if (function_exists('get_header')) get_header();
}
else include $pai->getOption('headerfile');
#######################################################

################### PROCESS QUESTION ##################
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['question'])) {
	if (!isset($_POST['category'])) $_POST['category'] = 1;
	$question = new Question();
	$question->setQuestion($_POST['question']);
	$question->setCategory($_POST['category']);
	$question->setIp($_SERVER['REMOTE_ADDR']);

	if ($pai->getOption('ipban_enable') == 'yes' && strlen($pai->getOption('ipban_enable')) > 0) {
		$bannedips = explode(';', $pai->getOption('banned_ips'));

		foreach($bannedips as $ip) {
			if ($question->getIp() == $ip) {
				$error = new Error('Sorry, this IP address has been banned from asking questions.');
				$error->display();
			}
		}
	}
	if ($pai->getOption('antispam_enable') == 'yes' && strlen($pai->getOption('banned_words')) > 0) {
		$bannedwords = $pai->getOption('banned_words');
		if (substr($bannedwords, -1, 1) == '|') $bannedwords = substr_replace($bannedwords, '', -1, 1);

		if (preg_match('/(' . $bannedwords . ')/i', strtolower($question->getQuestion()))) {
			$error = new Error('One of the words in your question has been disallowed by the site owner. Please go back and change your question, then try again.');
			$error->display();
		}
	}

	$question->create();
	echo $pai->getOption('success_msg_template');

	if ($pai->getOption('notifybymail') == 'yes') {
		if (basename($_SERVER['SCRIPT_FILENAME']) == 'index.php' && file_exists('admin.php')) $adminurl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/admin.php';

		$subject = 'New question has been asked';

		$bodyemail = 'A new question has been asked.

The question is:
' . stripslashes(html_entity_decode($question->getQuestion())) . '

This question was asked on ' . date($pai->getOption('date_format')) . ', by IP address ' . $_SERVER['REMOTE_ADDR'] . '

Login to your admin panel ';
		if (isset($adminurl)) $bodyemail .= 'at ' . $adminurl . ' ';
		$bodyemail .= 'to answer it.


Powered by Askably 3.1.';

		if (strstr($_SERVER['SERVER_SOFTWARE'], 'Win32')) $extra = 'From: ' . $pai->getOption('youraddress') . "\r\nX-Mailer: Askably 3.1 PHP/" . phpversion();
		else $extra = 'From: Askably <' . $pai->getOption('youraddress') . ">\r\nX-Mailer: Askably 3.1 PHP/" . phpversion();

		mail($pai->getOption('youraddress'), $subject, $bodyemail, $extra);
	}
}
#######################################################

################### SORT BY DATE ######################
else {
	?>
	<h1 class="pai-page-title"><?php echo $pai->getOption('titleofpage'); ?></h1>

	<?php
	if (substr($_SERVER['QUERY_STRING'], 0, 6) == 'recent') {

		$pai->askForm($_SERVER['PHP_SELF']);
		pages();
		$table = $pai_db->getTable();
		$query = <<<SQL
SELECT `{$table}`.*, `{$table}_cats`.`cat_name`
FROM `{$table}`
JOIN `{$table}_cats` ON `{$table}`.`category` = `{$table}_cats`.`cat_id`
SQL;

		if ($pai->getOption('show_unanswered') == 'no') $query .= ' WHERE `' . $pai_db->getTable() . '`.`answer` IS NOT NULL AND `' . $pai_db->getTable() . "`.`answer` != ''";

		dopagination($query);
		$query .= ' ORDER BY `' . $table . '`.`dateasked` DESC LIMIT ' . $startfrom . ', ' . $pai->getOption('totalpage_faq');

		summary();

		if ($totalpages > 0) {
			$getqs = $pai_db->query($query);

			pagination($perpage, 'date');

			while($qs = mysql_fetch_object($getqs)) {
				$pai->showQs($qs);
			}
		}
		else echo '<p>No questions found.</p>';
	}
#######################################################

####### ALL QUESTIONS FROM A PARTICULAR CATEGORY ######
	elseif (isset($_GET['category']) && is_numeric($_GET['category'])) {
		if ($pai->getOption('enable_cats') != 'yes') {
			$error = new Error('Category sorting is disabled.');
			$error->display();
		}

		$category = (int)cleaninput($_GET['category']);

		if (empty($category)) $error = new Error('No category specified.');
		if (!$cat = $pai_db->get('cat_name', 'cats', '`cat_id` = ' . $category)) $error = new Error('Invalid category.');

		if (isset($error)) $error->display();

		$pai->askForm($_SERVER['PHP_SELF']);
		pages();

		$query = <<<SQL
SELECT `{$pai_db->getTable()}`.*, `{$pai_db->getTable()}_cats`.`cat_name`
FROM `{$pai_db->getTable()}`
JOIN `{$pai_db->getTable()}_cats` ON `{$pai_db->getTable()}`.`category` = `{$pai_db->getTable()}_cats`.`cat_id`
WHERE `{$pai_db->getTable()}`.`category` = 
SQL;

		$query .= (int)cleaninput($_GET['category']);

		if ($pai->getOption('show_unanswered') == 'no') $query .= " AND `answer` != ''";

		dopagination($query);
		$query .= ' ORDER BY `dateasked` DESC LIMIT ' . $startfrom . ',' . $pai->getOption('totalpage_faq');

		if ($totalpages > 0) {
			echo '<h3 class="pai-category-title">' . $totalpages . ($totalpages == 1 ? ' question' : ' questions') . ' in the &quot;' . $cat . '&quot; category</h3>';

			$getqs = $pai_db->query($query);
			pagination($perpage, 'bycat');

			while($qs = mysql_fetch_object($getqs)) {
				$pai->showQs($qs);
			}
		}
		else echo '<h3 class="pai-category-title">No questions in this category</h3>';
	}
#######################################################

###################### SEARCH #########################
	elseif (isset($_GET['search'])) {
		$getsearch = cleaninput($_GET['search']);
		if (empty($getsearch)) $error = new Error('Please enter a valid search term.');
		if (strlen($getsearch) < 4) $error = new Error('Please enter more than four characters.');

		if (isset($error)) $error->display();

		$pai->askForm($_SERVER['PHP_SELF']);
		pages();

		$query = <<<SQL
SELECT `{$pai_db->getTable()}`.*, `{$pai_db->getTable()}_cats`.`cat_name`
FROM `{$pai_db->getTable()}`
JOIN `{$pai_db->getTable()}_cats` ON `{$pai_db->getTable()}`.`category` = `{$pai_db->getTable()}_cats`.`cat_id`
SQL;

		if ($pai->getOption('show_unanswered') == 'no') $query .= " WHERE (`question` LIKE '%" . $getsearch . "%' AND `answer` != '') OR `answer` LIKE '%" . $getsearch . "%'";
		else $query .= " WHERE `question` LIKE '%" . $getsearch . "%' OR `answer` LIKE '%" . $getsearch . "%'";
		dopagination($query);

		$query .= 'ORDER BY `dateasked` DESC LIMIT ' . $startfrom . ',' . $pai->getOption('totalpage_faq');

		if ($totalpages > 0) {
			echo '<h3 class="pai-search-title">' . $totalpages . ($totalpages == 1 ? ' result' : ' results') . ' found matching &quot;' . stripslashes($getsearch) . '&quot;</h3>';

			$getqs = $pai_db->query($query);
			pagination($perpage, 'search');

			while($qs = mysql_fetch_object($getqs)) {
				$pai->showQs($qs);
			}
		}
		else echo '<p>No results found.</p>';
	}
#######################################################

################ QUESTION PERMALINKS ##################
	elseif (isset($_GET['q']) && !empty($_GET['q']) && is_numeric($_GET['q'])) {

		$query = <<<SQL
SELECT `{$pai_db->getTable()}`.*, `{$pai_db->getTable()}_cats`.`cat_name`
FROM `{$pai_db->getTable()}`
JOIN `{$pai_db->getTable()}_cats` ON `{$pai_db->getTable()}`.`category` = `{$pai_db->getTable()}_cats`.`cat_id`
WHERE `{$pai_db->getTable()}`.`q_id` = 
SQL;

		$getq = $pai_db->query($query . (int)cleaninput($_GET['q']) . ' LIMIT 1');
		if (mysql_num_rows($getq) < 1) {
			$error = new Error('No such question.');
			$error->display();
		}

		$q = mysql_fetch_object($getq);

		if (empty($q->answer) && $pai->getOption('show_unanswered') == 'no') {
			$error = new Error('You cannot view this question until it has been answered.');
			$error->display();
		}
		else $pai->showQs($q);

	}
#######################################################

################## CATEGORY SUMMARY ###################
	elseif (empty($_GET) || empty($_SERVER['QUERY_STRING']) && $pai->getOption('summary_enable') == 'yes'){
		$pai->askForm($_SERVER['PHP_SELF']); ?>
		<ul class="pai-summary">
			<li>View questions:
				<ul>
					<li><a href="?recent" title="Most recent">Most recent</a></li>
				</ul>
			</li>
			<li>By category:
				<ul>
		<?php
		$getcats = $pai_db->query('SELECT `' . $pai_db->getTable() . '_cats`.*, COUNT(`' . $pai_db->getTable() . '`.`q_id`) AS `num` FROM `' . $pai_db->getTable() . '_cats` LEFT JOIN `' . $pai_db->getTable() . '` ON `' . $pai_db->getTable() . '_cats`.`cat_id` = `' . $pai_db->getTable() . '`.`category` GROUP BY `' . $pai_db->getTable() . '_cats`.`cat_id` ORDER BY `cat_name` ASC');

		while ($cat = mysql_fetch_object($getcats)) {
			$num = mysql_fetch_object($pai_db->query('SELECT COUNT(`q_id`) AS `num` FROM `' . $pai_db->getTable() . "` WHERE `category` = '" . $cat->cat_id . "'"));
			?>
					<li><a href="?category=<?php echo $cat->cat_id; ?>" title="View questions in this category"><?php echo $cat->cat_name; ?></a> (<?php echo $cat->num; ?>)</li>
			<?php
		}
		?>
				</ul>
			</li>
		</ul>

		<p>Total questions: <strong><?php echo $pai->getTotal(); ?></strong> (<?php echo $pai->getUnanswered(); ?> unanswered)</p>
		<?php
	}
	else {
		$error = new Error('Invalid query string.');
		$error->display();
	}
}

#######################################################

#################### SEARCH BOX ####################### ?>

	<form class="pai-search" method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>">
		<h4 class="pai-search-text">Search questions:</h4>
		<p><input type="text" name="search" id="search" maxlength="100" /> <input type="submit" value="Search" /></p>
	</form>

<?php #################################################

################### MISC FUNCTIONS ####################

//CREDIT LINK. DO NOT REMOVE
$display = '<p style="text-align: center;">Powered by <a href="http://not-noticeably.net/scripts/askably/" title="Askably">Askably 3.1</a></p>';

//IS USER LOGGED IN? TERMINATE SESSION
$pai->adminLogout();

eval(base64_decode('aWYgKGlzc2V0KCRkaXNwbGF5KSAmJiBzdHJzdHIoJGRpc3BsYXksICdQSFBBc2tJdCcpKSB7IGVjaG8gJGRpc3BsYXk7IH0gZWxzZSB7IGVjaG8gJzxwIHN0eWxlPSJ0ZXh0LWFsaWduOiBjZW50ZXI7Ij5Qb3dlcmVkIGJ5IDxhIGhyZWY9Imh0dHA6Ly9ub3Qtbm90aWNlYWJseS5uZXQvc2NyaXB0cy9waHBhc2tpdC8iIHRpdGxlPSJQSFBBc2tJdCI+UEhQQXNrSXQgMy4wPC9hPjwvcD4nOyB9'));

//FOOTER INCLUDE
if ($pai->getOption('is_wordpress') == 'yes') {
	if (function_exists('get_sidebar')) get_sidebar();
	if (function_exists('get_footer')) get_footer();
}
else include $pai->getOption('footerfile');
####################################################
?>