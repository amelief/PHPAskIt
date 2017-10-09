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

################################################################################################
############################ CORE PHPASKIT FUNCTIONS. DO _NOT_ EDIT. ############################
################################################################################################

if (!defined('PAI_IN')) exit('<p>This file cannot be loaded directly.</p>');
// TODO: DEBUG ONLY
//error_reporting(0);

$required_files = glob('classes/*.php');
$required_files[] = 'config.php';

foreach($required_files as $file) {
	if (!file_exists($file)) { ?>
		<h1>Error</h1>
		<p><strong><code><?php echo $file; ?></code></strong> could not be found. Without this file, the PHPAskIt cannot operate. Please make sure it is present.</p>
		<?php
		exit;
	}
	else require_once $file;
}

$display = '<p style="text-align: center;">Powered by <a href="http://amelierosalyn.com/scripts/" title="PHPAskIt">PHPAskIt 3.1</a></p>';

function cleaninput($data) {
	global $pai_db;
	$data = trim(htmlentities(strip_tags($data), ENT_QUOTES, 'UTF-8'));
	if (get_magic_quotes_gpc()) $data = stripslashes($data);
	return @mysqli_real_escape_string($pai_db->getConnection(), $data);
}
function clean_array($data) {
	return is_array($data) ? array_map('clean_array', $data) : cleaninput($data);
}
function adminheader() {
	header('Content-Type: text/html; charset=utf-8');
	?><!DOCTYPE html>
<html>
<head>
	<title>PHPAskIt 3.1: Admin</title>
	<meta charset="utf-8">
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
		#navigation a:hover { background: #0080ff; color: white; text-decoration: none; -moz-border-radius-bottomleft: 6px; -moz-border-radius-bottomright: 6px; -webkit-border-bottom-left-radius: 6px; -webkit-border-bottom-right-radius: 6px; border-bottom-left-radius: 6px; border-bottom-right-radius: 6px; }
		#navigation li { display: inline; margin: 0; padding: 0; }
		#navigation .active { background: #32cd32; color: white; text-decoration: none; -moz-border-radius-bottomleft: 6px; -moz-border-radius-bottomright: 6px; -webkit-border-bottom-left-radius: 6px; -webkit-border-bottom-right-radius: 6px; border-bottom-left-radius: 6px; border-bottom-right-radius: 6px; }
		#question-list { list-style: none; margin: 0; padding: 0; }
		#question-list li { width: 99%; }
		#side { float: left; width: 30%; }
		.active:hover { background: transparent; }
		.answer p { margin-left: 2.3em; margin-right: 2.4em; padding: 0.5em; width: 85%; }
		.answer p:hover { background: #fff; cursor: text; -moz-border-radius: 4px; -webkit-border-radius: 4px; border-radius: 4px; }
		.category { font-size: 0.9em; }
		.center { text-align: center; }
		.date { color: #32cd32; font-size: 1.5em; font-weight: bold; line-height: 1.6em; letter-spacing: -0.1em; margin-top: 0.5em; }
		.edit { color: #000; font-size: 0.8em; font-weight: normal; letter-spacing: normal; }
		.ip { color: green; font-size: 0.9em; text-align: right; }
		.nolist { list-style: none; }
		.question, .answer { padding: 0; margin: 0; }
		.question p { font-size: 1.2em; font-weight: bold; margin-left: 1.5em; padding: 0.5em; width: 85% }
		.question p:hover { background: #fff; cursor: text; -moz-border-radius: 4px; -webkit-border-radius: 4px; border-radius: 4px; }
		.question-container { background: #e9fbe9; margin-bottom: 3em; padding: 0.5em 0.5em 0.2em 2em; -moz-border-radius: 20px; -webkit-border-radius: 20px; border-radius: 20px; -moz-box-shadow: 1px 1px 3px #ddd; -webkit-box-shadow: 1px 1px 3px #ddd; box-shadow: 1px 1px 3px #ddd; }
		.question-container:hover { -moz-box-shadow: 2px 2px 2px #ccc; -webkit-box-shadow: 2px 2px 2px #ccc; box-shadow: 2px 2px 2px #ccc; }
		.template { height: 12em; width: 90%; }
		.tools { border-top: 1px dotted #32cd32; padding-top: 0.2em; }
		.unanswered { color: #c0c0c0; letter-spacing: 0.1em; }
	</style>
	<script type="text/javascript" src="js/prototype.js"></script>
	<script type="text/javascript" src="js/scriptaculous.js"></script>
	<script type="text/javascript" src="js/effects.js"></script>
	<script type="text/javascript" src="js/jquery.js"></script>
	<script type="text/javascript">
		//<![CDATA[
		var $j = jQuery.noConflict();

		$j(function() {
			$j("ul#importlist input[type=radio]").click(function() {
				$j(".option_content:visible").slideUp();
				$j("#showabspath" + $j(this).attr("value")).slideDown();
			});
		});

		function updateStats() {
			new Ajax.Request('admin.php?reset=stats', {
				asynchronous: true,
				onComplete: function(request) {
					$('stats-info').update(request.responseText);
				}
			});
			new Ajax.Request('admin.php?reset=unans', {
				asynchronous: true,
				onComplete: function(request) {
					$('unanswered-qs').update(request.responseText);
					if ($('unanswered-qs-header')) $('unanswered-qs-header').update(request.responseText);
				}
			});
		}
		//]]>
	</script>
	<!--[if lt IE 9]>
		<script src="js/html5.js"></script>
	<![endif]-->
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
	$totalpages = mysqli_num_rows($pai_db->query($query));
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
	if (version_compare(PHP_VERSION, '5.0.0', '<')) { ?>
		<h1>Error</h1>
		<p>PHPAskIt requires PHP 5+ to run - your version is <?php echo PHP_VERSION; ?>. If you cannot upgrade your version, you may wish to use PHPAskIt v3.0 which supports PHP 4.</p>
		<?php
		exit;
	}
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
	if (basename($_SERVER['PHP_SELF'] != 'admin.php')) {
		if (file_exists('upgrade.php')) { ?>
	 		<h1>Error</h1>
	 		<p>Please delete <code>upgrade.php</code> if you are not upgrading from a previous version of PHPAskIt.</p>
	 		<?php
	 		exit;
		}
	}
}

function checkTime(&$pai) {
	if (array_key_exists('pai_time', $_SESSION)) {
		// TODO user defined time
		if ((time() - $_SESSION['pai_time']) > 3600) $pai->killToken(true);
		//if ($pai->getOption('timeout') && !empty($pai->getOption('timeout')) {
			//if ((time() - $_SESSION['pai_time']) > (int)$pai->getOption('timeout')) $pai->killToken(true);
		//}
		else $_SESSION['pai_time'] = time();
	}
	else $_SESSION['pai_time'] = time();
}

// Because nl2br outputs <br /> which is XHTML. We're using HTML, so we want <br>
function nl2br_brfix($data) {
	if (version_compare(PHP_VERSION, '5.3.0', '>=')) return nl2br($data, false); // PHP 5.3 does this properly

	return str_replace('<br />', '<br>', nl2br($data));
}

$pai_db = new Database(PAI_HOST, PAI_USER, PAI_PASS, PAI_DB);
$pai = new PAI();

$display = '<p style="text-align: center;">Powered by <a href="http://amelierosalyn.com/scripts/" title="PHPAskIt">PHPAskIt 3.1</a></p>';

foreach($_SERVER as $key => $value) {
	$_SERVER[$key] = clean_array($value);
}
?>