<?php
/*
  ==============================================================================================
  Askably 3.1 © 2005-2009 Amelie M.
  ==============================================================================================
*/

######################## INSTALLATION FILE - BUILDS TABLES FOR SCRIPT USAGE ####################

define('PAI_IN', true);

if (!file_exists('functions.php')) { ?>
	<h1>Error</h1>
	<strong><code>functions.php</code></strong> could not be found. Without this file, the script cannot operate. Please make sure it is present.</p>
	<?php
	exit;
}
require 'functions.php';

if ($pai->getoption('username')) { ?>
	<h1>Error</h1>
	<p>You have already run install.php. Please delete this file, you do not need to run it again. If you do not delete it, you leave yourself open to potential security risks.</p>
	<?php
	exit;
}

$header = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>Askably 3.1: Install Askably</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf8" />
	<style type="text/css">
		body { color: #222; font: 0.7em/1.2em Verdana, Arial, Helvetica, sans-serif; text-align: center; }
		a { color: #0080ff; text-decoration: none; }
		a:hover { text-decoration: underline; }
		code { font-size: 1.1em; }
		h2 { color: #32cd32; font-size: 1.7em; font-variant: small-caps; line-height: normal; }
		h3 { color: #32cd32; font: bold italic 1.5em Georgia, "Times New Roman", Times, serif; }
		input, select { border: 1px solid #c0c0c0; font: 1em Verdana, Arial, Helvetica, sans-serif; padding: 2px; }
		input:focus, select:focus, textarea:focus { border: 1px solid #0080ff; }
		textarea { border: 1px solid #c0c0c0; font: 1.3em "Courier New", Courier, monospace; padding: 2px; }
		#container { margin: 0 auto; text-align: left; width: 50%; }
		#header { border-bottom: 3px solid #32cd32; font: bold 4.7em/0.7em Tahoma, Arial, Helvetica, sans-serif; letter-spacing: -0.1em; margin-bottom: 0.25em; margin-top: 0; padding-right: 0.1em; text-align: right; }
		#header a { color: #32cd32; }
		#header a:hover { color: #0080ff; text-decoration: none; }
		#navigation { list-style: none; margin: -1.2em 0 2em 0; padding: 0; }
		#navigation a { font-weight: bold; line-height: 2em; padding: 0.5em 1em; }
		#navigation a:hover { background: #0080ff; color: white; text-decoration: none; }
		#navigation li { display: inline; margin: 0; padding: 0; }
		#navigation .active { background: #32cd32; color: white; text-decoration: none; }
		.center { text-align: center; }
	</style>
</head>

<body>

	<h1 id="header"><a href="install.php" title="Askably">Askably</a></h1>
	<ul id="navigation" class="center">
		<li><a href="install.php" title="Setup" class="active">Setup</a></li>
	</ul>

	<div id="container">
		<h2>Setup</h2>
HTML;

if (isset($_POST['submit']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
	foreach($_POST as $key => $value) {
		$$key = cleaninput($value);
	}
	if (empty($username)) exit('ERROR: Please enter a username.');
	if (!empty($password)) {
		if ($confirm_pass != $password) exit('ERROR: Passwords did not match, try again.');
		// Do this on raw password as the chars might have been escaped otherwise
		if (!preg_match('/^([_a-z0-9@\.-]+)$/i', $_POST['password'])) exit('ERROR: Password contains invalid characters.');
	}
	else exit('ERROR: Please enter a password.');
	if (empty($youraddress) || !preg_match('/^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i', $youraddress)) exit('ERROR: Invalid email address. You must enter a valid address in order to install Askably.');

	$tooeasy = array('phpaskit', 'pai', 'abc123', '123abc', 'q&amp;a', 'question', 'questions', 'questionsandanswers', 'questionandanswer', 'q &amp; a', 'questionsandanswer', 'questionandanswers', 'questions and answer', 'question and answer', 'question and answers', 'questions and answers', 'qanda', 'q and a', 'q & a', 'security word', 'security', 'blah', 'yeah', 'password', 'word', 'test', 'askably');

	if (!isset($word) || (isset($word) && empty($word))) exit('ERROR: Enter a security word.');
	elseif (strlen($word) <= 3 || strtolower($word) == $username || strtolower($word) == $youraddress || in_array(strtolower($word), $tooeasy)) exit('ERROR: Your security word is too obvious or too short. Try a different word.');
	elseif (strtolower($word) == strtolower($password)) exit('ERROR: Your security word cannot be the same as your password.');

	if (strlen($password) <= 6 || strtolower($password) == $username || strtolower($password) == $youraddress || in_array(strtolower($password), $tooeasy)) exit('ERROR: Your password is too obvious or too short. Please choose another.');

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

	if ($is_wordpress == 'no' && strstr($headerfile, 'http://')) exit('ERROR: Please do not use a URL for your header file. Only absolute paths may be used.');
	if ($is_wordpress == 'no' && strstr($footerfile, 'http://')) exit('ERROR: Please do not use a URL for your footer file. Only absolute paths may be used.');

	if ($is_wordpress == 'yes') {
		if (empty($is_wp_blog_header)) exit('ERROR: Please enter your absolute path to wp-blog-header.php if you wish to use WordPress Themes. If not, please uncheck the appropriate option.');
		elseif (strstr($is_wp_blog_header, 'http://')) exit('ERROR: Please enter an absolute path to wp-blog-header.php, NOT a URL.');
		if (substr($is_wp_blog_header, -1, 18) != 'wp-blog-header.php') $is_wp_blog_header += 'wp-blog-header.php';
		elseif (!file_exists($is_wp_blog_header) && !file_exists($is_wp_blog_header . 'wp_blog_header.php')) exit('ERROR: Your path to wp-blog-header.php appears to be incorrect, as Askably cannot find it. Please go back and try again.');
	}

	$makeTable = <<<SQL
CREATE TABLE IF NOT EXISTS `{$pai_db->getTable()}` (
	`q_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`question` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`answer` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`category` INT UNSIGNED NOT NULL DEFAULT 1,
	`dateasked` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`ip` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
	PRIMARY KEY (`q_id`),
	KEY (`answer`(10)),
	KEY (`category`),
	KEY (`dateasked`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
SQL;

	$makeCats = <<<SQL
CREATE TABLE IF NOT EXISTS `{$pai_db->getTable()}_cats` (
	`cat_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`cat_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`default` TINYINT(1) NOT NULL DEFAULT 0,
	PRIMARY KEY (`cat_id`),
	KEY (`default`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
SQL;

	$makeOpts = <<<SQL
CREATE TABLE IF NOT EXISTS `{$pai_db->getTable()}_options` (
	`opt_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`option_name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
	`option_value` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	PRIMARY KEY (`opt_id`),
	KEY (`option_name`),
	KEY (`option_value`(10)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
SQL;

	$pai_db->query($makeTable) or exit('<p>Sorry, an error occurred when creating the main questions table. Please check your database settings and try again.</p>');
	$pai_db->query($makeCats) or exit('<p>Sorry, an error occurred when creating the category table. Please check your database settings and try again.</p>');
	$pai_db->query($makeOpts) or exit('<p>Sorry, an error occurred when creating the settings table. Please check your database settings and try again.</p>');

	if (mysql_num_rows($pai_db->query('SELECT `cat_id` FROM `' . $pai_db->getTable() . '_cats` LIMIT 1')) == 0) {
		$pai_db->query('INSERT INTO `' . $pai_db->getTable() . "_cats` (`cat_name`, `default`) VALUES
		('Random', 1),
		('About me', 0),
		('About the site', 0);") or exit('<p>Sorry, an error occurred when inserting data into the category table. Please check your database settings and try again.</p>');
	}

	$ask_template = '<p>[[question]] ';
	$q_template = '<div class="question-container">
<p class="date">[[date]] ';
	$sum_template = '<h2>Latest questions</h2>
<h4>[[total]] total, of which [[unanswered]] unanswered';

	if ($enable_cats == 'yes') {
		$ask_template .= '&nbsp;[[category]] ';
		$q_template .= '<span class="category">([[category]])</span>';
		$sum_template .= ' in [[categories]] categories';
		$summary_enable = 'yes';
	}
	else $summary_enable = 'no';

	$ask_template .= '&nbsp; [[submit]]</p>';
	$q_template .= '
</p>
<p class="question"><a href="[[permalink]]" title="Permalink to this question"><strong>[[question]]</strong></a></p>
<p class="answer">[[answer]]</p>
</div>';
	$sum_template .= '</h4>';

	$success_msg = '<p>Thank you, your question has been successfully added to the database. Look out for an answer soon!</p>';

	$pai_db->query('INSERT INTO `' . $pai_db->getTable() . "_options` (`option_name`, `option_value`) VALUES
	('username', '" . $username . "'),
	('password', '" . md5($password . $word) . "'),
	('security_word', '" . $word . "'),
	('headerfile', '" . $headerfile . "'),
	('footerfile', '" . $footerfile . "'),
	('date_format', '" . $date_format . "'),
	('enable_cats', '" . $enable_cats . "'),
	('ipban_enable', '" . $ipban_enable . "'),
	('banned_ips', ''),
	('antispam_enable', '" . $antispam_enable . "'),
	('banned_words', ''),
	('show_unanswered', '" . $show_unanswered . "'),
	('summary_enable', '" . $summary_enable . "'),
	('titleofpage', '" . $titleofpage . "'),
	('is_wordpress', '" . $is_wordpress . "'),
	('is_wp_blog_header', '" . $is_wp_blog_header . "'),
	('notifybymail', '" . $notifybymail . "'),
	('youraddress', '" . $youraddress . "'),
	('totalpage_faq', '" . $totalpage_faq . "'),
	('ask_template', '" . $ask_template . "'),
	('q_template', '" . $q_template . "'),
	('sum_template', '" . $sum_template . "'),
	('success_msg_template', '" . $success_msg . "'),
	('version', '3.1');") or exit('<p>Sorry, an error occurred when inserting data into the settings table. Please check your database settings and try again.</p>');

	echo $header . '<p>Installation successful - please now delete this file.</p>';
}
else {
	echo $header;
	?>
	<h3 class="center">Fill in the form below to install Askably 3.1.</h3>
	<p class="center">The * character denotes a required field. Any other fields you do not fill in will use the default settings. You will be able to change all of these options in your admin panel once you have installed the script.</p>

	<form method="post" action="install.php">
		<p><strong><label for="username">The username you will use to log in to the admin panel:</label></strong><br />
		CASE SENSITIVE<br />
		<input type="text" name="username" id="username" /> *</p>

		<p><strong><label for="password">The password you will use to log in to the admin panel:</label></strong><br />
		CASE SENSITIVE. <strong>Do not use these characters: &quot;, &amp;, ', &lt;, &gt; otherwise you will be unable to login.</strong><br />
		<input type="password" name="password" id="password" /> *</p>

		<p><strong><label for="confirm_pass">Re-enter password:</label></strong><br />
		<input type="password" name="confirm_pass" id="confirm_pass"/> *</p>

		<p><strong><label for="word">Security word:</label></strong><br />
		In case you forget your password, you will need this to reset it. <strong>This cannot be left blank and should not contain any of the aforementioned symbols.</strong><br />
		<input type="text" name="word" id="word" value="" /> *</p>

		<p><strong><label for="headerfile">Header file you wish to use on your questions page:</label></strong><br />
		Absolute or relative path - leave blank to use default. <strong>DO NOT</strong> enter a <acronym title="Uniform Resource Locator - usually in this form: http://www.domainname.tld">URL</acronym> here, it will not work!<br />
		NOTE: DO NOT FILL IN THIS PART IF YOU ARE USING WORDPRESS THEMES!<br />
		<input type="text" name="headerfile" id="headerfile" value="" /></p>

		<p><strong><label for="footerfile">Footer file you wish to use on your questions page:</label></strong><br />
		As above. Again, do NOT fill in this part if you are using WordPress Themes.<br />
		<input type="text" name="footerfile" id="footerfile" value="" /></p>

		<p><strong><label for="is_wordpress">Are you using WordPress Themes with Askably?</label></strong> <input type="checkbox" name="is_wordpress" id="is_wordpress" value="yes" /><br />If you have themed your site using WordPress (i.e. using get_header() and get_footer()) and would like to apply the same themes to your questions page, please check this box.</p>

		<p><strong><label for="is_wp_blog_header">Absolute path to wp-blog-header.php:</label></strong><br />
		If you checked the above option, please enter your FULL ABSOLUTE PATH to wp-blog-header.php here.<br />
		<input type="text" name="is_wp_blog_header" id="is_wp_blog_header" value="" /></p>

		<p><strong><label for="date_format">Date/time format to use for questions:</label></strong><br />
		(See <a href="http://www.php.net/date" title="PHP Manual for Date options">http://www.php.net/date</a> for more information)<br />
		<input type="text" name="date_format" id="date_format" value="jS F Y - H:i" /></p>

		<p><strong><label for="enable_cats">Enable categories?</label></strong> <input type="checkbox" name="enable_cats" id="enable_cats" value="yes" checked="checked" /></p>

		<p><strong><label for="ipban_enable">Enable <acronym title="Internet Protocol">IP</acronym> address blocking?</label></strong> <input type="checkbox" name="ipban_enable" id="ipban_enable" value="yes" checked="checked" /></p>

		<p><strong><label for="antispam_enable">Enable anti-spam (word blocking)?</label></strong> <input type="checkbox" name="antispam_enable" id="antispam_enable" value="yes" checked="checked" /></p>

		<p><strong><label for="show_unanswered">Show unanswered questions on the front page?</label></strong> <input type="checkbox" name="show_unanswered" id="show_unanswered" value="yes" /></p>

		<p><strong><label for="summary_enable">Enable summary?</label></strong> <input type="checkbox" name="summary_enable" id="summary_enable" value="yes" checked="checked" /><br />
		Do you want to show a summary of questions by category on the front page?</p>

		<p><strong><label for="titleofpage">Front page title:</label></strong><br />
		This is the title users see at the top of the questions page.<br />
		<input type="text" name="titleofpage" id="titleofpage" value="Q&amp;A" /></p>

		<p><strong><label for="notifybymail">Notify by email when a new question is asked?</label></strong>  <input type="checkbox" name="notifybymail" id="notifybymail" value="yes" checked="checked" /><br />Requires a valid email address to be entered below.</p>

		<p><strong><label for="youraddress">Your email address:</label></strong><br />
		You must set this regardless of whether you want to be notified of new questions as it is used to reset your password in case you forget it.<br />
		<input type="text" name="youraddress" id="youraddress" value="" /> *</p>

		<p><strong><label for="totalpage_faq">Questions per page:</label></strong><br />
		The amount of questions to display per page (for end users - the admin panel has a separate option for this)<br />
		<input type="text" name="totalpage_faq" id="totalpage_faq" value="10" maxlength="3" /></p>

		<p><input type="submit" name="submit" id="submit" value="Submit" /></p>
	</form>
	<?php
}
?>
	</div>
</body>
</html>