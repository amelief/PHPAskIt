<?php
/*
  ==============================================================================================
  This file is part of PHPAskIt 3.1 by Amelie F.

  PHPAskIt is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  PHPAskIt is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
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

if ($pai->getOption('is_wordpress')) {
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

	if ($pai->getOption('ipban_enable') && strlen($pai->getOption('ipban_enable')) > 0) {
		$bannedips = explode(';', $pai->getOption('banned_ips'));

		foreach($bannedips as $ip) {
			if ($question->getIp() == $ip) PAIError::showMessage('Sorry, this IP address has been banned from asking questions.');
		}
	}

	if ($pai->getOption('antispam_enable') && strlen($pai->getOption('banned_words')) > 0) {
		$bannedwords = $pai->getOption('banned_words');
		if (substr($bannedwords, -1, 1) == '|') $bannedwords = substr_replace($bannedwords, '', -1, 1);

		if (preg_match('/(' . $bannedwords . ')/i', strtolower($question->getQuestion()))) PAIError::showMessage('One of the words in your question has been disallowed by the site owner. Please go back and change your question, then try again.');
	}

	$question->create();
	echo $pai->getOption('success_msg_template');

	if ($pai->getOption('notifybymail')) {
		if (basename($_SERVER['SCRIPT_FILENAME']) == 'index.php' && file_exists('admin.php')) $adminurl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/admin.php';

		$subject = 'New question has been asked';

		$bodyemail = 'A new question has been asked.

The question is:
' . stripslashes(html_entity_decode($question->getQuestion())) . '

This question was asked on ' . date($pai->getOption('date_format')) . ', by IP address ' . $_SERVER['REMOTE_ADDR'] . '

Login to your admin panel ';
		if (isset($adminurl)) $bodyemail .= 'at ' . $adminurl . ' ';
		$bodyemail .= 'to answer it.


Powered by PHPAskIt 3.1.';

		if (strstr($_SERVER['SERVER_SOFTWARE'], 'Win32')) $extra = 'From: ' . $pai->getOption('youraddress') . "\r\nX-Mailer: PHPAskIt 3.1 PHP/" . phpversion();
		else $extra = 'From: PHPAskIt <' . $pai->getOption('youraddress') . ">\r\nX-Mailer: PHPAskIt 3.1 PHP/" . phpversion();

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
		$query = "SELECT `q_id` FROM `{$pai_db->getTable()}`";

		if (!$pai->getOption('show_unanswered')) $query .= " WHERE `answer` IS NOT NULL AND `answer` != ''";

		dopagination($query);
		$query .= ' ORDER BY `dateasked` DESC LIMIT ' . $startfrom . ', ' . $pai->getOption('totalpage_faq');

		summary();

		if ($totalpages > 0) {
			$getqs = $pai_db->query($query);

			pagination($perpage, 'date');

			while($qs = mysqli_fetch_object($getqs)) {
				$q = new Question($qs->q_id);
				$q->show();
			}
		}
		else echo '<p>No questions found.</p>';
	}
#######################################################

####### ALL QUESTIONS FROM A PARTICULAR CATEGORY ######
	elseif (isset($_GET['category']) && is_numeric($_GET['category'])) {
		if (!$pai->getOption('enable_cats')) PAIError::showMessage('Category sorting is disabled.');

		$cat = new Category((int)$_GET['category']);
		if (!isset($cat)) PAIError::showMessage('Invalid category.');

		$pai->askForm($_SERVER['PHP_SELF']);
		pages();

		$query = 'SELECT `q_id`, `category` FROM `' . $pai_db->getTable() . '` WHERE `category` = ' . (int)$_GET['category'];

		if (!$pai->getOption('show_unanswered')) $query .= " AND `answer` != '' AND `answer` IS NOT NULL";

		dopagination($query);
		$query .= ' ORDER BY `dateasked` DESC LIMIT ' . $startfrom . ',' . $pai->getOption('totalpage_faq');

		if ($totalpages > 0) {
			echo '<h3 class="pai-category-title">' . $totalpages . ($totalpages == 1 ? ' question' : ' questions') . ' in the &quot;' . $cat->getName() . '&quot; category</h3>';

			$getqs = $pai_db->query($query);
			pagination($perpage, 'bycat');

			while($qs = mysqli_fetch_object($getqs)) {
				$q = new Question($qs->q_id);
				$q->show();
			}
		}
		else echo '<h3 class="pai-category-title">No questions in this category</h3>';
	}
#######################################################

###################### SEARCH #########################
	elseif (isset($_GET['search'])) {
		$getsearch = cleaninput($_GET['search']);
		if (empty($getsearch)) PAIError::showMessage('Please enter a valid search term.');
		if (strlen($getsearch) < 4) PAIError::showMessage('Please enter more than four characters.');

		$pai->askForm($_SERVER['PHP_SELF']);
		pages();

		$query = "SELECT `q_id` FROM `{$pai_db->getTable()}`";

		if (!$pai->getOption('show_unanswered')) $query .= " WHERE (`question` LIKE '%" . $getsearch . "%' AND `answer` != '' AND `answer` IS NOT NULL) OR `answer` LIKE '%" . $getsearch . "%'";
		else $query .= " WHERE `question` LIKE '%" . $getsearch . "%' OR `answer` LIKE '%" . $getsearch . "%'";
		dopagination($query);

		$query .= 'ORDER BY `dateasked` DESC LIMIT ' . $startfrom . ',' . $pai->getOption('totalpage_faq');

		if ($totalpages > 0) {
			echo '<h3 class="pai-search-title">' . $totalpages . ($totalpages == 1 ? ' result' : ' results') . ' found matching &quot;' . stripslashes($getsearch) . '&quot;</h3>';

			$getqs = $pai_db->query($query);
			pagination($perpage, 'search');

			while($qs = mysqli_fetch_object($getqs)) {
				$q = new Question($qs->q_id);
				$q->show();
			}
		}
		else echo '<p>No results found.</p>';
	}
#######################################################

################ QUESTION PERMALINKS ##################
	elseif (isset($_GET['q']) && !empty($_GET['q']) && is_numeric($_GET['q'])) {

		$q = new Question((int)$_GET['q']);

		if (!isset($q) || (($q->getAnswer() == '' || $q->getAnswer() == null) && !$pai->getOption('show_unanswered'))) PAIError::showMessage('Invalid question.');
		else $q->show();

	}
#######################################################

################## CATEGORY SUMMARY ###################
	elseif (empty($_GET) || empty($_SERVER['QUERY_STRING']) && $pai->getOption('summary_enable')){
		$pai->askForm($_SERVER['PHP_SELF']);
		$pai->showSummary();
	}
	else PAIError::showMessage('Invalid query string.');
}

#######################################################

#################### SEARCH BOX ####################### ?>

	<form class="pai-search" method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>">
		<h4 class="pai-search-text">Search questions:</h4>
		<p><input type="text" name="search" id="search" maxlength="100"> <input type="submit" value="Search"></p>
	</form>

<?php #################################################

################### MISC FUNCTIONS ####################

//CREDIT LINK. DO NOT REMOVE
$display = '<p style="text-align: center;">Powered by <a href="http://amelierosalyn.com/scripts/" title="PHPAskIt">PHPAskIt 3.1</a></p>';
echo $display;

//IS USER LOGGED IN? TERMINATE SESSION
//$pai->adminLogout();

//eval(base64_decode('aWYgKGlzc2V0KCRkaXNwbGF5KSAmJiBzdHJzdHIoJGRpc3BsYXksICdQSFBBc2tJdCcpKSB7IGVjaG8gJGRpc3BsYXk7IH0gZWxzZSB7IGVjaG8gJzxwIHN0eWxlPSJ0ZXh0LWFsaWduOiBjZW50ZXI7Ij5Qb3dlcmVkIGJ5IDxhIGhyZWY9Imh0dHA6Ly9hbWVsaWVyb3NhbHluLmNvbS9zY3JpcHRzLyIgdGl0bGU9IlBIUEFza0l0Ij5QSFBBc2tJdCAzLjA8L2E+PC9wPic7IH0='));

//FOOTER INCLUDE
if ($pai->getOption('is_wordpress')) {
	if (function_exists('get_sidebar')) get_sidebar();
	if (function_exists('get_footer')) get_footer();
}
else include $pai->getOption('footerfile');
####################################################
?>