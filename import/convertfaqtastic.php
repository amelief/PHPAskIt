<?php
//TODO: Fix!
/*
  ==============================================================================================
  Askably 3.1 © 2005-2009 Amelie M.
  ==============================================================================================
																																*/

###### IMPORT QUESTIONS FROM FAQtastic v2.1 (from scripts.inexistent.org) TO Askably 3.1 ######


/* ---------------------------------------------------------------------------------------------
										INSTRUCTIONS
------------------------------------------------------------------------------------------------

THIS SCRIPT IMPORTS YOUR QUESTIONS FROM FAQtastic v2.1 ONLY - DOES NOT WORK WITH Faqtastic 3.0! NOTE: THIS SCRIPT WILL _NOT_ IMPORT YOUR SETTINGS!

--- IMPORTANT: YOU MUST RUN Askably's install.php BEFORE RUNNING THIS SCRIPT. ---

INSTRUCTIONS:

1. MAKE SURE YOU HAVE INSTALLED Askably BY RUNNING install.php BEFORE ATTEMPTING TO IMPORT QUESTIONS.
2. CHANGE THE DIRECTORY LINE BELOW TO YOUR ABSOLUTE PATH TO YOUR FAQtastic INSTALLATION.
3. PLACE THIS FILE IN THE import DIRECTORY OF YOUR Askably INSTALLATION (_NOT_ YOUR FAQtastic DIRECTORY).
4. THE IMPORTED QUESTIONS WILL GO INTO THE DEFAULT CATEGORY. YOU CAN CHANGE THIS FROM YOUR ADMIN PANEL. :)

IMPORTED QUESTIONS WON'T HAVE AN 'ASKED' TIME, NOR WILL THEY HAVE A CATEGORY (THEY WILL HAVE BEEN IMPORTED INTO THE DEFAULT CATEGORY) OR NAMES/EMAIL ADDRESSES OF VISITORS. YOU CAN CHANGE THE CATEGORY IN YOUR ADMIN PANEL, BUT THE TIMES CANNOT BE CHANGED - THEY WILL SHOW UP AS THE DATE WHEN YOU IMPORTED THEM. ALL FUTURE ASKED QUESTIONS WILL HAVE THE CORRECT 'ASKED' TIME HOWEVER.
SIMILARLY, NAMES/EMAILS CANNOT BE ADDED AS THIS IS NOT A FEATURE SUPPORTED BY Askably.
AFTER STEP 4 ABOVE, YOU CAN DELETE YOUR FAQtastic FILES AND DATABASE.
*/

define('FAQDIR', '/home/user/public_html/folder/'); // FAQtastic installation path (WITH trailing slash) - place this in the second set of quotes, after the comma



// --- DO NOT EDIT. ------------------------------------------------------------------------- //
define('PAI_IN', true);

//error_reporting(0);

if (strstr(FAQDIR , 'http://')) exit('<h1>Error</h1><p>Sorry, you can only use absolute paths for your FAQtastic directory, URLs are not allowed. Please change the directory to an absolute path in convertfaqtastic.php for the conversion to continue.</p>');

if (file_exists(FAQDIR . 'config.php')) include FAQDIR . 'config.php';
else exit('<h1>Error</h1><p>FAQtastic\'s <strong><code>config.php</code></strong> could not be found. Please make sure this file exists in the directory you have specified and try again.</p>');

echo '<h3>Getting FAQtastic questions...</h3>';

$faqtasticqs = array();

$getqs = mysql_query('SELECT * FROM `' . $tablefaqs . '`', $db);

if (mysql_num_rows($getqs) < 1) exit('<p>There are no questions in your FAQtastic database. No questions imported.</p>');

while($qs = mysql_fetch_object($getqs)) {
	$faqtasticqs[] = $qs->question . ',' . $qs->answer . ',' . $qs->ip;
}

mysql_close($db);
unset($db);

if (file_exists('../functions.php')) include '../functions.php';
else exit('<h1>Error</h1><p>Could not find Askably\'s <strong><code>functions.php</code></strong>. Without this file, the script cannot operate. Please makes sure it exists.</p>');

if (!$pai->getOption('username')) { ?>
	<h1>Error</h1>
	<p>Please run <strong><a href="../install.php" title="install.php"><code>install.php</code></a></strong> before accessing this page.</p>
	<?php
	exit;
}

$import = array();
foreach($faqtasticqs as $question) {
	$q = explode(',', $question);
	$import[] = "'" . cleaninput($q[0]) . "', '" . cleaninput($q[1]) . "', '" . date('Y-m-d H:i:s') . "', 1, '" . cleaninput($q[2]) . "'";
}

$sql = 'INSERT INTO `' . $pai_db->getTable() . '` (`question`, `answer`, `dateasked`, `category`, `ip`) VALUES ';

foreach($import as $question) {
	$sql .= '(' . $question . '),';
}

if (substr($sql, -1, 1) == ',') $sql = substr_replace($sql, '', -1, 1);

echo '<p>Found ' . count($faqtasticqs) . ' question(s).</p>
<h3>Importing questions...</h3>';

if ($pai_db->query($sql)) echo '<p>' . mysql_affected_rows($pai_db->getConnection()) . ' question(s) successfully imported.</p><p>This file should now be deleted.</p>';
else echo '<p>Sorry, an error occured when importing your questions. Please check your database settings and try again.</p>';
?>