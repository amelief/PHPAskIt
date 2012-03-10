<?php
// TODO ADD TIMEOUT

/*
  ==============================================================================================
  This file is part of PHPAskIt 3.1, Copyright © 2005-2012 Amelie F.

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

################################ UPGRADE AND CONVERSION SCRIPT #################################

define('PAI_IN', true);

if (!file_exists('functions.php')) { ?>
	<h1>Error</h1>
	<strong><code>functions.php</code></strong> could not be found. Without this file, the script cannot operate. Please make sure it is present.</p>
	<?php
	exit;
}
require 'functions.php';

if (!$pai_db->get('question', 'main')) { ?>
	<h1>Error</h1>
	<p>Please run <strong><a href="install.php" title="install.php"><code>install.php</code></a></strong> before accessing this page.</p>
	<?php
	exit;
}
if ($pai->getOption('version') == '3.1') { ?>
	<h1>Error</h1>
	<p>Your PHPAskIt installation is already up to date. Please delete this file, you do not need to run it again.</p>
	<?php
	exit;
}

$header = '<!DOCTYPE html>
<html>
<head>
	<title>PHPAskIt 3.1: Upgrade to v3.1</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
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
		<li><a href="upgrade1.php" title="Upgrade" class="active">Upgrade</a></li>
	</ul>';

function upgradeFrom3() {
	$pai_db->query('UPDATE `' . $pai_db->getTable() . "_options` SET `option_value` = '3.1' WHERE `option_name` = 'version' LIMIT 1");
	$pai_db->query('ALTER TABLE `' . $pai_db->getTable() . "_cats` ADD `default` TINYINT NOT NULL DEFAULT 0, ADD INDEX (`default`)");

	// Clean up data types, set all to UTF8
	$pai_db->query('ALTER TABLE `' . $pai_db->getTable() . '` CHANGE `q_id` `q_id` INT UNSIGNED NOT NULL AUTO_INCREMENT');
	$pai_db->query('ALTER TABLE `' . $pai_db->getTable() . '` CHANGE `category` `category` INT UNSIGNED NOT NULL');
	$pai_db->query('ALTER TABLE `' . $pai_db->getTable() . '` CHANGE `question` `question` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL');
	$pai_db->query('ALTER TABLE `' . $pai_db->getTable() . '` CHANGE `answer` `answer` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL');
	$pai_db->query('ALTER TABLE `' . $pai_db->getTable() . '` CHANGE `ip` `ip` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL');
	$pai_db->query('ALTER TABLE `' . $pai_db->getTable() . '` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci');
	$pai_db->query('ALTER TABLE `' . $pai_db->getTable() . '_cats` CHANGE `cat_id` `cat_id` INT UNSIGNED NOT NULL AUTO_INCREMENT');
	$pai_db->query('ALTER TABLE `' . $pai_db->getTable() . '_cats` CHANGE `cat_name` `cat_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL');
	$pai_db->query('ALTER TABLE `' . $pai_db->getTable() . '_cats` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci');
	$pai_db->query('ALTER TABLE `' . $pai_db->getTable() . '_options` CHANGE `opt_id` `opt_id` INT UNSIGNED NOT NULL AUTO_INCREMENT');
	$pai_db->query('ALTER TABLE `' . $pai_db->getTable() . '_options` CHANGE `option_name` `option_name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL');
	$pai_db->query('ALTER TABLE `' . $pai_db->getTable() . '_options` CHANGE `option_value` `option_value` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL');
	$pai_db->query('ALTER TABLE `' . $pai_db->getTable() . '_options` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upgrade'])) {

	// Determine which version we are on
	if ($pai->getOption('version') && ($pai->getOption('version') == '3.0' || $pai->getOption('version') == '3.1')) $version = 3;
	elseif ($pai->getOption('is_wordpress')) $version = 2;
	else $version = 1;

	// Upgrade v1.x and v2.x to v3.0 - we can go to 3.1 from there
	if ($version < 3) {
		if ($version == 1) {
			$dates = $pai_db->query('SELECT `id`, `dateasked` FROM `' . $pai_db->getTable() . '`');

			while($convertdates = mysql_fetch_object($dates)) {
				$pai_db->query('UPDATE `' . $pai_db->getTable() . "` SET `dateasked` = '" . @date('Y-m-d H:i:s', $convertdates->dateasked) . "' WHERE `id` = " . $convertdates->id . ' LIMIT 1') or exit(mysql_error($pai_db->getConnection()));
			}

			$pai_db->query('ALTER TABLE `' . $pai_db->getTable() . '` CHANGE `dateasked` `dateasked` DATETIME NOT NULL') or exit(mysql_error($pai_db->getConnection()));
			$pai_db->query('UPDATE `' . $pai_db->getTable() . '` SET `categoryofq` = 1') or exit(mysql_error($pai_db->getConnection()));

			$pai_db->query('CREATE TABLE IF NOT EXISTS `' . $pai_db->getTable() . "_cats`
				(`cat_id` int(6) UNSIGNED NOT NULL auto_increment,
				`cat_name` tinytext NOT NULL DEFAULT '',
				PRIMARY KEY (`cat_id`))") or exit(mysql_error($pai_db->getConnection()));

			$pai_db->query('INSERT INTO `' . $pai_db->getTable() . "_cats` VALUES
				('', 'Random'),
				('', 'About me'),
				('', 'About the site');") or exit(mysql_error($pai_db->getConnection()));

			$pai_db->query('CREATE TABLE `' . $pai_db->getTable() . "_options`
				(`opt_id` int(6) UNSIGNED NOT NULL auto_increment,
				`option_name` varchar(100) NOT NULL DEFAULT '',
				`option_value` text NOT NULL DEFAULT '',
				PRIMARY KEY (`opt_id`),
				KEY (`option_name`),
				KEY (`option_value`(10)))") or exit(mysql_error($pai_db->getConnection()));

			$word = preg_replace('/([0-9])+)/', '', substr(md5(substr(md5(microtime()), 1, 3)), 3, 6));
			$pai_db->query('INSERT INTO `' . $pai_db->getTable() . "_options` (`option_name`, `option_value`) VALUES
				('username', 'admin'),
				('password', ''),
				('security_word', '" . $word . "'),
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
				('totalpage_faq', '10')") or exit(mysql_error($pai_db->getConnection()));
		}
		$pai_db->query('ALTER TABLE `' . $pai_db->getTable() . '` CHANGE `answer` `answer` TEXT NOT NULL') or exit(mysql_error($pai_db->getConnection()));
		$pai_db->query('ALTER TABLE `' . $pai_db->getTable() . '` CHANGE `categoryofq` `category` TINYINT(4) UNSIGNED NOT NULL') or exit(mysql_error($pai_db->getConnection()));
		$pai_db->query('ALTER TABLE `' . $pai_db->getTable() . '` CHANGE `id` `q_id` INT(6) UNSIGNED NOT NULL AUTO_INCREMENT') or exit(mysql_error($pai_db->getConnection()));
		if ($version == 2) $pai_db->query('ALTER TABLE `' . $pai_db->getTable() . '` DROP PRIMARY KEY, ADD PRIMARY KEY(`q_id`)') or exit(mysql_error($pai_db->getConnection()));
		else $pai_db->query('ALTER TABLE `' . $pai_db->getTable() . '` DROP KEY `id`, ADD PRIMARY KEY(`q_id`)') or exit(mysql_error($pai_db->getConnection()));
		$pai_db->query('ALTER TABLE `' . $pai_db->getTable() . '` ADD KEY(`answer`(10)), ADD KEY(`category`), ADD KEY(`dateasked`)') or exit(mysql_error($pai_db->getConnection()));

		if ($version == 2) $pai_db->query('ALTER TABLE `' . $pai_db->getTable() . '_options` ADD KEY(`option_name`), ADD KEY(`option_value`(10))') or exit(mysql_error($pai_db->getConnection()));

		$ask_template = '<p>[[question]] ';
		$q_template = '<div class="question-container">
<p class="date">[[date]] ';
		$sum_template = '<h2>Latest questions</h2>
<h4>[[total]] total, of which [[unanswered]] unanswered';

		if ($pai->getOption('enable_cats') == 'yes') {
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
			('summary_enable', '" . $summary_enable . "'),
			('is_wp_blog_header', ''),
			('ask_template', '" . $ask_template . "'),
			('q_template', '" . $q_template . "'),
			('sum_template', '" . $sum_template . "'),
			('success_msg_template', '" . $success_msg . "'),
			('version', '3.1');") or exit(mysql_error($pai_db->getConnection()));

		$pai_db->query('OPTIMIZE TABLE `' . $pai_db->getTable() . '`, `' . $pai_db->getTable() . '_cats`, `' . $pai_db->getTable() . '_options`');
	}

	// Now go from 3.0 to 3.1
	upgradeFrom3();

	$newpass = substr(md5(substr(md5(microtime()), 5, 7)), 5, 7);
	$pai_db->query('UPDATE `' . $pai_db->getTable() . "_options` SET `option_value` = '" . md5($newpass . $pai->getMask()) . "' WHERE `option_name` = 'password' LIMIT 1");

	echo $header . '<h2>Success</h2>
	<p>PHPAskIt has been successfully upgraded to version 3.1.</p>';
	
	if ($version == 1) echo '<p><strong>PLEASE NOTE:</strong> Due to the incompatibilities between PHPAskIt versions 1.0 or 1.1 and PHPAskIt 3.1, PHPAskIt was unable to import your old categories or settings. You should add new categories and change your settings from within your admin panel.</p>
	<p>Because PHPAskIt could not import your settings, your old username and password for admin.php will no longer work. You should use the following details to gain access:</p>';
	else echo '<p><strong>PLEASE NOTE:</strong> Your password for admin.php has changed. Please use the details below to log in:</p>';

	echo '<p>Username: <code>' . $pai->getOption('username') . '</code><br>
	Password: <code>' . $newpass . '</code></p>
	<p>Log in to your admin panel as soon as possible and change these values.</p>';
}
else {
	echo $header; ?>

	<h2>PHPAskIt Upgrade</h2>

	<p>Please make sure to do a full backup of your files and database before upgrading. Please note that if you are upgrading from versions 1.x or 2.x of PHPAskIt, <strong>your layout customisations will not be retained.</strong></p>

	<form action="upgrade1.php" method="post">
		<p><input type="submit" name="upgrade" id="upgrade" value="Upgrade PHPAskIt &raquo;"></p>
	</form>
	<?php
}
?>
</body>
</html>