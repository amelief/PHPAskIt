<?php
/*
  ==============================================================================================
  PHPAskIt 3.0 © 2005-2008 Amelie M.
  ==============================================================================================
  																								*/

############################## IMPORT QUESTIONS INTO THE DATABASE ##############################


/* ---------------------------------------------------------------------------------------------
											INSTRUCTIONS
------------------------------------------------------------------------------------------------

1.	Run install.php before running this script.

2.	Copy this line as many times as you have questions:

		$question[] = "QUESTION || ANSWER";

	Add your question after the first " (where it currently says QUESTION), and add your answer before the second " (where it currently says ANSWER, after the ||). Leave the rest of the line as it is, do NOT edit anything other than the part where it currently says QUESTION or ANSWER. If anything other than these parts are changed, the script will not work. Questions should be separated from answers with "||" (without quotes).

	IMPORTANT: YOU MUST 'ESCAPE' ALL QUOTES ( " ) FROM QUESTIONS AND ANSWERS. TO ESCAPE, PLACE A BACKSLASH ( \ ) IN FRONT OF A QUOTE, E.G. \".

	> Example:
		Question to import: What's your favourite book?
		Answer to import: "The Da Vinci Code" by Dan Brown

	The "s in the answer need to be escaped. They should look like this:

		Q: What's your favourite book?
		A: \"The Da Vinci Code\" by Dan Brown

	When added to the import script, the question above should look like:

		$question[] = "What's your favourite book? || \"The Da Vinci Code\" by Dan Brown";

	IF YOU DO NOT DO THIS, THE SCRIPT WILL NOT WORK! Single quotes, or apostrophes ( ' ) do not need to be escaped.


Please note: imported questions and answers will go into the default category, but you can change this from your admin panel.
Questions will also have today's date and your IP address, however this is not editable.
*/

//------- DO _NOT_ EDIT THIS PART ------------------------------------------------------------//
define('PAI_IN', true);

error_reporting(0);

if (file_exists('../functions.php')) include '../functions.php';
else exit('<p><strong>Error: <code>functions.php</code></strong> could not be found. Without this file, the script cannot operate. Please make sure it is present and try again.</p>');

if (!@$pai->getoption('username')) { ?>
	<h1>Error</h1>
	<p>Please run <strong><a href="../install.php" title="install.php"><code>install.php</code></a></strong> before accessing this page.</p>
	<?php
}

$question = array();

echo '<h1>Importing questions...</h1>';
//--------------------------------------------------------------------------------------------//


//------- BEGIN EDITING HERE! ----------------------------------------------------------------//




$question[] = "QUESTION || ANSWER";
$question[] = "QUESTION || ANSWER";





//------- STOP EDITING NOW -------------------------------------------------------------------//

$sql = 'INSERT INTO `' . $pai->table . '` VALUES ';
foreach($question as $q) {
	$qa = explode('||', $q);
	$sql .= "('', '" . $pai->cleaninput($qa[0]) . "', '" . $pai->cleaninput($qa[1]) . "', 1, NOW(), '" . $pai->cleaninput($_SERVER['REMOTE_ADDR']) . "'),";
}
if (substr($sql, -1, 1) == ',') $sql = substr_replace($sql, '', -1, 1);

if ($pai->query($sql)) echo '<p>Your questions were successfully imported into the database. You should now delete this file.</p>';
else echo '<p>An error occurred while importing your questions. Please check your database settings and try again.</p>';
?>