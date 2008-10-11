<?php
/*
  ==============================================================================================
  PHPAskIt 3.1 � 2005-2008 Amelie M.
  ==============================================================================================
  																								*/

################################################################################################
############################ CORE PHPASKIT FUNCTIONS. DO _NOT_ EDIT. ###########################
################################################################################################

if (!defined('PAI_IN')) exit('<p>This file cannot be loaded directly.</p>');
//error_reporting(0);

$upone = array('import.php', 'convertwaks.php', 'convertfaqtastic.php', 'convertaa.php');
if (in_array(basename($_SERVER['PHP_SELF']), $upone)) $path = '../';
else $path = '';

$required_files = array(
	'config.php',
	'classes/Question.php',
	'classes/Database.php',
	'classes/Error.php',
	'classes/PAI.php'
);
foreach($required_files as $file) {
	if (!file_exists($path . $file)) { ?>
		<h1>Error</h1>
		<p><strong><code><?php echo $path . $file; ?></code></strong> could not be found. Without this file, the script cannot operate. Please make sure it is present.</p>
		<?php
		exit;
	}
	else require_once $path . $file;
}

$display = '<p style="text-align: center;">Powered by <a href="http://not-noticeably.net/scripts/phpaskit/" title="PHPAskIt">PHPAskIt 3.1</a></p>';

function cleaninput($data) {
	global $pai_db;
	$data = trim(htmlentities(strip_tags($data)));
	if (get_magic_quotes_gpc()) $data = stripslashes($data);
	return mysql_real_escape_string($data, $pai_db->getConnection());
}
function clean_array($data) {
	return is_array($data) ? array_map('clean_array', $data) : cleaninput($data);
}
function adminheader() {
	?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>PHPAskIt 3.1: Admin</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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
	<script type="text/javascript" src="prototype.js"></script>
</head>

<body>
	<?php
}
function summary() {
	global $pai;
	$summary = array('[[total]]', '[[answered]]', '[[unanswered]]', '[[categories]]');
	$replace = array($pai->getTotal(), $pai->getAnswered(), $pai->getUnanswered(), $pai->getCats());

	echo str_replace($summary, $replace, $pai->getOption('sum_template'));
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
				$buildlinkprev .= 'category=' . cleaninput($_GET['category']) . '&amp;page=';
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
				$buildlinknext .= 'category=' . cleaninput($_GET['category']) . '&amp;page=';
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
		$page = (int)cleaninput($_GET['page']);
		if (empty($page)) $page = 1;
	}
	else $page = 1;
}
function check_pages($totalpages) {
	global $page;
	if ($page > $totalpages) $page = $totalpages;
}
function dopagination($query) {
	global $totalpages, $perpage, $startfrom, $page, $pai_db, $pai;
	$totalpages = mysql_num_rows($pai_db->query($query));
	if (defined('ADMIN_PERPAGE')) {
		$perpage = ceil($totalpages / ADMIN_PERPAGE);
		check_pages($perpage);
		$startfrom = ($page - 1) * ADMIN_PERPAGE;
	}
	else {
		$perpage = ceil($totalpages / $pai->getOption('totalpage_faq'));
		check_pages($perpage);
		$startfrom = ($page - 1) * $pai->getOption('totalpage_faq');
	}
}
function check_stuff() {
	global $pai;
	if (!$pai->getOption('username')) { ?>
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
	if ($pai->getOption('version') != '3.1') { ?>
		<h1>Error</h1>
		<p>You need to <a href="upgrade.php" title="Upgrade">upgrade PHPAskIt</a> before you can view this page.</p>
		<?php
		exit;
	}
	if (file_exists('import/import.php') || file_exists('import/convertaa.php') || file_exists('import/convertwaks.php') || file_exists('import/convertfaqtastic.php') || file_exists('upgrade.php')) { ?>
		<h1>Error</h1>
		<p>Please delete <code>upgrade.php</code> and the contents of the <code>/import</code> directory if you are not upgrading from a previous version of PHPAskIt or are not planning to import any questions into the script.</p>
		<?php
		exit;
	}
}

$pai_db = new Database(PAI_HOST, PAI_USER, PAI_PASS, PAI_DB);
$pai = new PAI();

$display = '<p style="text-align: center;">Powered by <a href="http://not-noticeably.net/scripts/phpaskit/" title="PHPAskIt">PHPAskIt 3.1</a></p>';

foreach($_SERVER as $key => $value) {
	$_SERVER[$key] = clean_array($value);
}
?>