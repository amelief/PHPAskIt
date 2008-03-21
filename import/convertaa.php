<?php
/*
  ==============================================================================================
  PHPAskIt 3.0 © 2005-2008 Amelie M.
  ==============================================================================================
  																								*/

########## IMPORT QUESTIONS FROM Ask&Answer (formerly from posed.org) TO PHPAskIt 3.0 ##########


/* ---------------------------------------------------------------------------------------------
										INSTRUCTIONS
------------------------------------------------------------------------------------------------

THIS SCRIPT IMPORTS QUESTIONS FROM Ask&Answer TO PHPAskIt.

--- IMPORTANT: YOU MUST RUN PHPAskIt's install.php BEFORE RUNNING THIS SCRIPT. ---

INSTRUCTIONS:

1. MAKE SURE YOU HAVE INSTALLED PHPAskIt BY RUNNING install.php BEFORE ATTEMPTING TO IMPORT QUESTIONS.
2. CHANGE THE DIRECTORY LINE BELOW TO YOUR ABSOLUTE PATH TO YOUR Ask&Answer INSTALLATION.
3. PLACE THIS FILE IN THE import DIRECTORY OF YOUR PHPAskIt INSTALLATION (_NOT_ YOUR Ask&Answer DIRECTORY).
4. THE IMPORTED QUESTIONS WILL GO INTO THE DEFAULT CATEGORY. YOU CAN CHANGE THIS FROM YOUR ADMIN PANEL. :)

IMPORTED QUESTIONS WON'T HAVE AN 'ASKED' TIME, NOR WILL THEY HAVE A CATEGORY (THEY WILL HAVE BEEN IMPORTED INTO THE DEFAULT CATEGORY). YOU CAN CHANGE THE CATEGORY IN YOUR ADMIN PANEL, BUT THE TIMES CANNOT BE CHANGED - THEY WILL SHOW UP AS THE DATE WHEN YOU IMPORTED THEM. ALL FUTURE ASKED QUESTIONS WILL HAVE THE CORRECT 'ASKED' TIME HOWEVER.
AFTER STEP 4 ABOVE, YOU CAN DELETE YOUR Ask&Answer FILES AND DATABASE.
*/

define('QADIR', '/home/user/public_html/folder/'); // Ask&Answer installation path (WITH trailing slash) - place this in the second set of quotes, after the comma



// --- DO NOT EDIT. ------------------------------------------------------------------------- //
define('PAI_IN', true);

error_reporting(0);

if (strstr(QADIR , 'http://')) exit('<h1>Error</h1><p>Sorry, you can only use absolute paths for your Ask&amp;Answer directory, URLs are not allowed. Please change the directory to an absolute path in convertaa.php for the conversion to continue.</p>');

if (file_exists(QADIR . 'config.php')) include QADIR . 'config.php';
else exit('<h1>Error</h1><p>Ask&amp;Answer\'s <strong><code>config.php</code></strong> could not be found. Please make sure this file exists in the directory you have specified and try again.</p>');

echo '<h3>Getting Ask&amp;Answer questions...</h3>';

$aaqs = array();

$getqs = mysql_query('SELECT * FROM `' . $table . '`');

if (mysql_num_rows($getqs) < 1) exit('<p>There are no questions in your Ask&amp;Answer database. No questions imported.</p>');

while($qs = mysql_fetch_object($getqs)) {
	$aaqs[] = $qs->question . ',' . $qs->answer . ',' . $qs->ip;
}

mysql_close();

if (file_exists('../functions.php')) include '../functions.php';
else exit('<h1>Error</h1><p>Could not find PHPAskIt\'s <strong><code>functions.php</code></strong>. Without this file, the script cannot operate. Please make sure it exists.</p>');

if (!@$pai->getoption('username')) { ?>
	<h1>Error</h1>
	<p>Please run <strong><a href="../install.php" title="install.php"><code>install.php</code></a></strong> before accessing this page.</p>
	<?php
	exit;
}

$import = array();
foreach($aaqs as $question) {
	$q = explode(',', $question);
	$import[] = "'" . $pai->cleaninput($q[0]) . "', '" . $pai->cleaninput($q[1]) . "', '" . date('Y-m-d H:i:s') . "', 1, '" . $pai->cleaninput($q[2]) . "'";
}

$sql = 'INSERT INTO `' . $pai->table . '` (`question`, `answer`, `dateasked`, `category`, `ip`) VALUES ';

foreach($import as $question) {
	$sql .= '(' . $question . '),';
}

if (substr($sql, -1, 1) == ',') $sql = substr_replace($sql, '', -1, 1);

echo '<p>Found ' . count($aaqs) . ' question(s).</p>
<h3>Importing questions...</h3>';

if ($pai->query($sql)) echo '<p>' . mysql_affected_rows($pai->connect) . ' question(s) successfully imported.</p><p>This file should now be deleted.</p>';
else echo '<p>Sorry, an error occured when importing your questions. Please check your database settings and try again.</p>';
?>