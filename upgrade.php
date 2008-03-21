<?php
/*
  ==============================================================================================
  PHPAskIt 3.0 © 2005-2008 Amelie M.
  ==============================================================================================
  																								*/

################################ UPGRADE AND CONVERSION SCRIPT #################################


define('PAI_IN', true);

if (!file_exists('functions.php')) { ?>
	<h1>Error</h1>
	<strong><code>functions.php</code></strong> could not be found. Without this file, the script cannot operate. Please make sure it is present.</p>
	<?php
	exit;
}
require 'functions.php';

if (!@$pai->getfromdb('question', 'main', '', 1)) { ?>
	<h1>Error</h1>
	<p>Please run <strong><a href="install.php" title="install.php"><code>install.php</code></a></strong> before accessing this page.</p>
	<?php
	exit;
}
if (@$pai->getoption('version') == '3.0') { ?>
	<h1>Error</h1>
	<p>Your PHPAskIt installation is already up to date. Please delete this file, you do not need to run it again.</p>
	<?php
	exit;
}

$header = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>PHPAskIt 3.0: Upgrade to v3.0</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
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

	<h1 id="header"><a href="upgrade.php" title="PHPAskIt">PHPAskIt</a></h1>
	<ul id="navigation" class="center">
		<li><a href="upgrade.php" title="Upgrade" class="active">Upgrade</a></li>
	</ul>';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upgrade'])) {
	if (@$pai->getoption('is_wordpress')) $version = 2;
	else $version = 1;

	if ($version == 1) {
		$dates = $pai->query('SELECT `id`, `dateasked` FROM `' . $pai->table . '`');

		while($convertdates = mysql_fetch_object($dates)) {
			$pai->query('UPDATE `' . $pai->table . "` SET `dateasked` = '" . @date('Y-m-d H:i:s', $convertdates->dateasked) . "' WHERE `id` = " . $convertdates->id . ' LIMIT 1') or exit('<p>Sorry, an error occurred during the upgrade process. Please check your database settings and try again.</p>');
		}

		$pai->query('ALTER TABLE `' . $pai->table . '` CHANGE `dateasked` `dateasked` DATETIME NOT NULL') or exit('<p>Sorry, an error occurred during the upgrade process. Please check your database settings and try again.</p>');
		$pai->query('UPDATE `' . $pai->table . '` SET `categoryofq` = 1') or exit('<p>Sorry, an error occurred during the upgrade process. Please check your database settings and try again.</p>');

		$pai->query('CREATE TABLE IF NOT EXISTS `' . $pai->table . "_cats`
			(`cat_id` int(6) UNSIGNED NOT NULL auto_increment,
			`cat_name` tinytext NOT NULL DEFAULT '',
			PRIMARY KEY (`cat_id`))") or exit('<p>Sorry, an error occurred during the upgrade process. Please check your database settings and try again.</p>');

		$pai->query('INSERT INTO `' . $pai->table . "_cats` VALUES
			('', 'Random'),
			('', 'About me'),
			('', 'About the site');") or exit('<p>Sorry, an error occurred during the upgrade process. Please check your database settings and try again.</p>');

		$newpass = substr(md5(substr(md5(microtime()), 5, 7)), 5, 7);

		$pai->query('CREATE TABLE `' . $pai->table . "_options`
			(`opt_id` int(6) UNSIGNED NOT NULL auto_increment,
			`option_name` varchar(100) NOT NULL DEFAULT '',
			`option_value` text NOT NULL DEFAULT '',
			PRIMARY KEY (`opt_id`),
			KEY (`option_name`),
			KEY (`option_value`(10)))") or exit('<p>Sorry, an error occurred during the upgrade process. Please check your database settings and try again.</p>');

		$pai->query('INSERT INTO `' . $pai->table . "_options` (`option_name`, `option_value`) VALUES
			('username', 'admin'),
			('password', '" . md5($newpass . $pai->mask) . "'),
			('security_word', ''),
			('headerfile', 'header.html'),
			('footerfile', 'footer.html'),
			('date_format', 'l F j, Y - g:ia'),
			('enable_cats', 'yes'),
			('ipban_enable', 'yes'),
			('banned_ips', ''),
			('antispam_enable', 'yes'),
			('banned_words', ''),
			('show_unanswered', 'yes'),
			('titleofpage', 'Q&amp;A'),
			('is_wordpress', 'no'),
			('notifybymail', 'no'),
			('youraddress', ''),
			('totalpage_faq', '10')") or exit('<p>Sorry, an error occurred during the upgrade process. Please check your database settings and try again.</p>');
	}
	$pai->query('ALTER TABLE `' . $pai->table . '` CHANGE `answer` `answer` TEXT NOT NULL') or exit('<p>Sorry, an error occurred during the upgrade process. Please check your database settings and try again.</p>');
	$pai->query('ALTER TABLE `' . $pai->table . '` CHANGE `categoryofq` `category` TINYINT(4) UNSIGNED NOT NULL') or exit('<p>Sorry, an error occurred during the upgrade process. Please check your database settings and try again.</p>');
	$pai->query('ALTER TABLE `' . $pai->table . '` CHANGE `id` `q_id` INT(6) UNSIGNED NOT NULL AUTO_INCREMENT') or exit('<p>Sorry, an error occurred during the upgrade process. Please check your database settings and try again.</p>');
	if ($version == 2) $pai->query('ALTER TABLE `' . $pai->table . '` DROP PRIMARY KEY, ADD PRIMARY KEY(`q_id`)') or exit('<p>Sorry, an error occurred during the upgrade process. Please check your database settings and try again.</p>');
	else $pai->query('ALTER TABLE `' . $pai->table . '` DROP KEY `id`, ADD PRIMARY KEY(`q_id`)') or exit('<p>Sorry, an error occurred during the upgrade process. Please check your database settings and try again.</p>');
	$pai->query('ALTER TABLE `' . $pai->table . '` ADD KEY(`answer`(10)), ADD KEY(`category`), ADD KEY(`dateasked`)') or exit('<p>Sorry, an error occurred during the upgrade process. Please check your database settings and try again.</p>');

	if ($version == 2) $pai->query('ALTER TABLE `' . $pai->table . '_options` ADD KEY(`option_name`), ADD KEY(`option_value`(10))') or exit('<p>Sorry, an error occurred during the upgrade process. Please check your database settings and try again.</p>');

	$ask_template = '<p>[[question]] ';
	$q_template = '<div class="question-container">
<p class="date">[[date]] ';
	$sum_template = '<h2>Latest questions</h2>
<h4>[[total]] total, of which [[unanswered]] unanswered';

	if ($pai->getoption('enable_cats') == 'yes') {
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

	$pai->query('INSERT INTO `' . $pai->table . "_options` (`option_name`, `option_value`) VALUES
		('summary_enable', '" . $summary_enable . "'),
		('is_wp_blog_header', ''),
		('ask_template', '" . $ask_template . "'),
		('q_template', '" . $q_template . "'),
		('sum_template', '" . $sum_template . "'),
		('success_msg_template', '" . $success_msg . "'),
		('version', '3.0');") or exit('<p>Sorry, an error occurred during the upgrade process. Please check your database settings and try again.</p>');

	$pai->query('OPTIMIZE TABLE `' . $pai->table . '`, `' . $pai->table . '_cats`, `' . $pai->table . '_options`');

	echo $header . '<h2>Success</h2>
	<p>PHPAskIt has been successfully upgraded to version 3.0.</p>';
	if ($version == 1) {
		echo '<p><strong>PLEASE NOTE:</strong> Due to the incompatibilities between PHPAskIt versions 1.0 or 1.1 and PHPAskIt 3.0, PHPAskIt was unable to import your old categories or settings. You should add new categories and change your settings from within your admin panel.</p>
		<p>Because PHPAskIt could not import your settings, your old username and password for admin.php will no longer work. You should use the following details to gain access:</p>
		<p>Username: <code>admin</code><br />
		Password: <code>' . $newpass . '</p>
		<p>Log in to your admin panel as soon as possible and change these values.</p>';
	}
}
else {
	echo $header; ?>

	<h2>PHPAskIt Upgrade</h1>

	<p>Please make sure to do a full backup of your files and database before upgrading. Please note that regardless of which version you were using before (1.x or 2.x), <strong>your layout customisations will not be retained.</strong></p>

	<form action="upgrade.php" method="post">
		<p><input type="submit" name="upgrade" id="upgrade" value="Upgrade PHPAskIt &raquo;" /></p>
	</form>
	<?php
}
?>
</body>
</html>