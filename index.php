<?php
/*
  ==============================================================================================
  PHPAskIt 3.1 © 2005-2008 Amelie M.
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

if (($pai->getoption('enable_cats') != 'yes' || $pai->getoption('summary_enable') != 'yes') && $_SERVER['REQUEST_METHOD'] != 'POST' && empty($_SERVER['QUERY_STRING'])) {
	header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '?recent');
	exit;
}

if ($pai->getoption('is_wordpress') == 'yes') {
	include $pai->getoption('is_wp_blog_header');
	if (function_exists('get_header')) get_header();
}
else include $pai->getoption('headerfile');
#######################################################

################### PROCESS QUESTION ##################
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['question'])) {
	$question = new question();
	$question->set_question($_POST['question']);
	$question->set_category($_POST['category']);
	$question->set_ip($_SERVER['REMOTE_ADDR']);
	$question->set_dateasked(time());
	$the_question = $question->get_question();

	if ($pai->getoption('ipban_enable') == 'yes' && strlen($pai->getoption('ipban_enable')) > 0) {
		$bannedips = explode(';', $pai->getoption('banned_ips'));

		foreach($bannedips as $ip) {
			if ($_SERVER['REMOTE_ADDR'] == $ip) {
				$error = new pai_error('Sorry, this IP address has been banned from asking questions.');
				$error->display();
			}
		}
	}
	if ($pai->getoption('antispam_enable') == 'yes' && strlen($pai->getoption('banned_words')) > 0) {
		$bannedwords = $pai->getoption('banned_words');
		if (substr($bannedwords, -1, 1) == '|') $bannedwords = substr_replace($bannedwords, '', -1, 1);

		if (preg_match('/(' . $bannedwords . ')/i', strtolower($the_question['question']))) {
			$error = new pai_error('One of the words in your question has been disallowed by the site owner. Please go back and change your question, then try again.');
			$error->display();
		}
	}

	$question->create();
	echo $pai->getoption('success_msg_template');

	if ($pai->getoption('notifybymail') == 'yes') {
		if (basename($_SERVER['SCRIPT_FILENAME']) == 'index.php' && file_exists('admin.php')) $adminurl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/admin.php';

		$subject = 'New question has been asked';

		$question = str_replace('&quot;', '"', $question);
		$question = str_replace('&amp;', '&', $question);

		$bodyemail = 'A new question has been asked.

The question is:
' . stripslashes($question) . '

This question was asked on ' . date($pai->getoption('date_format')) . ', by IP address ' . $_SERVER['REMOTE_ADDR'] . '

Login to your admin panel ';
		if (isset($adminurl)) $bodyemail .= 'at ' . $adminurl . ' ';
		$bodyemail .= 'to answer it.


Powered by PHPAskIt 3.1.';

		if (strstr($_SERVER['SERVER_SOFTWARE'], 'Win32')) $extra = 'From: ' . $pai->getoption('youraddress') . "\r\nX-Mailer: PHPAskIt 3.1 PHP/" . phpversion();
		else $extra = 'From: PHPAskIt <' . $pai->getoption('youraddress') . ">\r\nX-Mailer: PHPAskIt 3.1 PHP/" . phpversion();

		mail($pai->getoption('youraddress'), $subject, $bodyemail, $extra);
	}
}
#######################################################

################### SORT BY DATE ######################
else {
	?>
	<h1 class="pai-page-title"><?php echo $pai->getoption('titleofpage'); ?></h1>

	<?php
	if (substr($_SERVER['QUERY_STRING'], 0, 6) == 'recent') {

		$pai->askform($_SERVER['PHP_SELF']);
		pages();
		$table = $pai_db->get_table();
		$query = <<<SQL
SELECT `{$table}`.*, `{$table}_cats`.`cat_name`
FROM `{$table}`
JOIN `{$table}_cats` ON `{$table}`.`category` = `{$table}_cats`.`cat_id`
SQL;

		if ($pai->getoption('show_unanswered') == 'no') $query .= ' WHERE `' . $pai_db->get_table() . "`.`answer` != '' OR `" . $pai_db->get_table() . '`.`answer` IS NOT NULL';

		dopagination($query);
		$query .= ' ORDER BY `' . $table . '`.`dateasked` DESC LIMIT ' . $startfrom . ', 10';// . $pai->getoption('totalpage_faq');

		summary();

		if ($totalpages > 0) {
			$getqs = $pai_db->query($query);

			pagination($perpage, 'date');

			while($qs = mysql_fetch_object($getqs)) {
				$pai->showqs($qs);
			}
		}
		else echo '<p>No questions found.</p>';
	}
#######################################################

####### ALL QUESTIONS FROM A PARTICULAR CATEGORY ######
	elseif (isset($_GET['category']) && is_numeric($_GET['category'])) {
		if ($pai->getoption('enable_cats') != 'yes') pai_error('Category sorting is disabled.');

		$category = (int)cleaninput($_GET['category']);

		if (empty($category)) pai_error('No category specified.');
		if (!$cat = $pai_db->get('cat_name', 'cats', '`cat_id` = ' . $category, 1)) pai_error('Invalid category.');

		$pai->askform($_SERVER['PHP_SELF']);
		pages();

		$query = <<<SQL
SELECT `{$pai_db->get_table()}`.*, `{$pai_db->get_table()}_cats`.`cat_name`
FROM `{$pai_db->get_table()}`
JOIN `{$pai_db->get_table()}_cats` ON `{$pai_db->get_table()}`.`category` = `{$pai_db->get_table()}_cats`.`cat_id`
WHERE `{$pai_db->get_table()}`.`category` = 
SQL;

		$query .= (int)cleaninput($_GET['category']);

		if ($pai->getoption('show_unanswered') == 'no') $query .= " AND `answer` != ''";

		dopagination($query);
		$query .= ' ORDER BY `dateasked` DESC LIMIT ' . $startfrom . ',' . $pai->getoption('totalpage_faq');

		if ($totalpages > 0) {
			echo '<h3 class="pai-category-title">' . $totalpages . ($totalpages == 1 ? ' question' : ' questions') . ' in the &quot;' . $cat . '&quot; category</h3>';

			$getqs = $pai_db->query($query);
			pagination($perpage, 'bycat');

			while($qs = mysql_fetch_object($getqs)) {
				$pai->showqs($qs);
			}
		}
		else echo '<h3 class="pai-category-title">No questions in this category</h3>';
	}
#######################################################

###################### SEARCH #########################
	elseif (isset($_GET['search'])) {
		$getsearch = cleaninput($_GET['search']);
		if (empty($getsearch)) $error = new pai_error('Please enter a valid search term.');
		if (strlen($getsearch) < 4) $error = new pai_error('Please enter more than four characters.');

		if (isset($error)) $error->display();

		$pai->askform($_SERVER['PHP_SELF']);
		pages();

		$query = <<<SQL
SELECT `{$pai_db->get_table()}`.*, `{$pai_db->get_table()}_cats`.`cat_name`
FROM `{$pai_db->get_table()}`
JOIN `{$pai_db->get_table()}_cats` ON `{$pai_db->get_table()}`.`category` = `{$pai_db->get_table()}_cats`.`cat_id`
SQL;

		if ($pai->getoption('show_unanswered') == 'no') $query .= " WHERE (`question` LIKE '%" . $getsearch . "%' AND `answer` != '') OR `answer` LIKE '%" . $getsearch . "%'";
		else $query .= " WHERE `question` LIKE '%" . $getsearch . "%' OR `answer` LIKE '%" . $getsearch . "%'";
		dopagination($query);

		$query .= 'ORDER BY `dateasked` DESC LIMIT ' . $startfrom . ',' . $pai->getoption('totalpage_faq');

		if ($totalpages > 0) {
			echo '<h3 class="pai-search-title">' . $totalpages . ($totalpages == 1 ? ' result' : ' results') . ' found matching &quot;' . stripslashes($getsearch) . '&quot;</h3>';

			$getqs = $pai_db->query($query);
			pagination($perpage, 'search');

			while($qs = mysql_fetch_object($getqs)) {
				$pai->showqs($qs);
			}
		}
		else echo '<p>No results found.</p>';
	}
#######################################################

################ QUESTION PERMALINKS ##################
	elseif (isset($_GET['q']) && !empty($_GET['q']) && is_numeric($_GET['q'])) {

		$query = <<<SQL
SELECT `{$pai_db->get_table()}`.*, `{$pai_db->get_table()}_cats`.`cat_name`
FROM `{$pai_db->get_table()}`
JOIN `{$pai_db->get_table()}_cats` ON `{$pai_db->get_table()}`.`category` = `{$pai_db->get_table()}_cats`.`cat_id`
WHERE `{$pai_db->get_table()}`.`q_id` = 
SQL;

		$getq = $pai_db->query($query . (int)cleaninput($_GET['q']) . ' LIMIT 1');
		if (mysql_num_rows($getq) < 1) pai_error('No such question.');

		$q = mysql_fetch_object($getq);

		if (empty($q->answer) && $pai->getoption('show_unanswered') == 'no') pai_error('You cannot view this question until it has been answered.');
		else $pai->showqs($q);

	}
#######################################################

################## CATEGORY SUMMARY ###################
	elseif (empty($_GET) || empty($_SERVER['QUERY_STRING']) && $pai->getoption('summary_enable') == 'yes'){
		$pai->askform($_SERVER['PHP_SELF']); ?>
		<ul class="pai-summary">
			<li>View questions:
				<ul>
					<li><a href="?recent" title="Most recent">Most recent</a></li>
				</ul>
			</li>
			<li>By category:
				<ul>
		<?php
		$getcats = $pai_db->query('SELECT `' . $pai_db->get_table() . '_cats`.*, COUNT(`' . $pai_db->get_table() . '`.`q_id`) AS `num` FROM `' . $pai_db->get_table() . '_cats` LEFT JOIN `' . $pai_db->get_table() . '` ON `' . $pai_db->get_table() . '_cats`.`cat_id` = `' . $pai_db->get_table() . '`.`category` GROUP BY `' . $pai_db->get_table() . '_cats`.`cat_id` ORDER BY `cat_name` ASC');

		while ($cat = mysql_fetch_object($getcats)) {
			$num = mysql_fetch_object($pai_db->query('SELECT COUNT(`q_id`) AS `num` FROM `' . $pai_db->get_table() . "` WHERE `category` = '" . $cat->cat_id . "'"));
			?>
					<li><a href="?category=<?php echo $cat->cat_id; ?>" title="View questions in this category"><?php echo $cat->cat_name; ?></a> (<?php echo $cat->num; ?>)</li>
			<?php
		}
		?>
				</ul>
			</li>
		</ul>

		<p>Total questions: <strong><?php echo $pai->total; ?></strong> (<?php echo $pai->unanswered; ?> unanswered)</p>
		<?php
	}
	else {
		$error = new pai_error('Invalid query string.');
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
$display = '<p style="text-align: center;">Powered by <a href="http://not-noticeably.net/scripts/phpaskit/" title="PHPAskIt">PHPAskIt 3.1</a></p>';

//IS USER LOGGED IN? TERMINATE SESSION
$pai->admin_logout();

eval(base64_decode('aWYgKGlzc2V0KCRkaXNwbGF5KSAmJiBzdHJzdHIoJGRpc3BsYXksICdQSFBBc2tJdCcpKSB7IGVjaG8gJGRpc3BsYXk7IH0gZWxzZSB7IGVjaG8gJzxwIHN0eWxlPSJ0ZXh0LWFsaWduOiBjZW50ZXI7Ij5Qb3dlcmVkIGJ5IDxhIGhyZWY9Imh0dHA6Ly9ub3Qtbm90aWNlYWJseS5uZXQvc2NyaXB0cy9waHBhc2tpdC8iIHRpdGxlPSJQSFBBc2tJdCI+UEhQQXNrSXQgMy4wPC9hPjwvcD4nOyB9'));

//FOOTER INCLUDE
if ($pai->getoption('is_wordpress') == 'yes') {
	if (function_exists('get_sidebar')) get_sidebar();
	if (function_exists('get_footer')) get_footer();
}
else include $pai->getoption('footerfile');
####################################################
?>