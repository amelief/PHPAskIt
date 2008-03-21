<?php
/*
  ==============================================================================================
  PHPAskIt 3.0 © 2005-2008 Amelie M.
  ==============================================================================================
  																								*/

####### IMPORT QUESTIONS FROM Wak's Ask&Answer (formerly from luved.org) TO PHPAskIt 3.0 #######


/* ---------------------------------------------------------------------------------------------
										INSTRUCTIONS
------------------------------------------------------------------------------------------------

1. MAKE SURE YOU HAVE INSTALLED PHPAskIt BY RUNNING install.php BEFORE ATTEMPTING TO IMPORT QUESTIONS.
2. CHANGE THE DIRECTORY LINE BELOW TO YOUR ABSOLUTE PATH TO YOUR Wak's Ask&Answer INSTALLATION.
3. PLACE THIS FILE IN THE import DIRECTORY OF YOUR PHPAskIt INSTALLATION (_NOT_ YOUR Wak's Ask&Answer DIRECTORY).
4. THE IMPORTED QUESTIONS WILL GO INTO THE DEFAULT CATEGORY. YOU CAN CHANGE THIS FROM YOUR ADMIN PANEL. :)

********** WARNING: THIS FILE WILL ONLY IMPORT ANSWERED QUESTIONS. MAKE SURE YOU HAVE ANSWERED ALL THE QUESTIONS YOU WANT TO IMPORT USING Wak's Ask&Answer OR THEY WILL NOT BE IMPORTED ****************
*/

define('DIR', '/home/user/public_html/folder/'); // Wak's Ask&Answer directory (WITH slash at the end) - place this in the second set of quotes, after the comma




// --- DO NOT EDIT. ------------------------------------------------------------------------- //
$dir = DIR;
define('PAI_IN', true);

error_reporting(0);

if (strstr(DIR, 'http://') || strstr($dir, 'http://')) exit('<h1>Error</h1><p>Sorry, you can only use absolute paths for your Wak\'s Ask&amp;Answer directory, URLs are not allowed. Please change the directory to an absolute path in convertwaks.php for the conversion to continue.</p>');

if (file_exists(DIR . 'functions.php')) include DIR . 'functions.php';
else exit('<h1>Error</h1><p>Wak\'s Ask&amp;Answer\'s <strong><code>functions.php</code></strong> could not be found. Please make sure this file exists in your Wak\'s Ask&amp;Answer directory.</p>');

if (file_exists('../functions.php')) include '../functions.php';
else exit('<h1>Error</h1><p>Could not find PHPAskIt\'s <strong><code>functions.php</code></strong>. Without this file, the script cannot operate. Please make sure it exists.</p>');

if (!@$pai->getoption('username')) { ?>
	<h1>Error</h1>
	<p>Please run <strong><a href="../install.php" title="install.php"><code>install.php</code></a></strong> before accessing this page.</p>
	<?php
	exit;
}

echo '<h3>Importing questions from Wak\'s Ask&amp;Answer to PHPAskIt...</h3>' . "\n\n" . '<p><strong>IMPORTANT: Only answered questions will be imported.</strong></p>' . "\n<p>" . count(getTotalAnswered()) . ' answered questions found.</p>' . "\n\n<p>";

foreach (getTotalAnswered() as $waksquestions) {
	list($id, $question, $answer, $ip, $dateasked, $dateanswered) = get($waksquestions);

	$question = $pai->cleaninput($question);
	$answer = $pai->cleaninput($answer);
	$ip = $pai->cleaninput($ip);

	$answer = str_replace('(br)', '<br />', $answer);

	$dateasked = date('Y-m-d H:i:s', $dateasked);

	$pai->query('INSERT INTO `' . $pai->table . "` VALUES ('', '" . $question . "', '" . $answer . "', 1, '" . $dateasked . "', '" . $ip . "');") or exit('Couldn\'t add question #' . $id . 'to the database... Stopping import process.');
	echo 'Added question #' . $id . '...<br />';
}
echo '<br />All questions were imported. This file should now be deleted.</p>';
?>