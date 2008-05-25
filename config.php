<?php if (!defined('PAI_IN')) exit('<p>This file cannot be loaded directly.</p>'); $phpaskit = array(); // DO NOT EDIT

/*
===============================================================================================
PHPAskIt 3.0 � 2005-2008 Amelie M.

You may:
- Use and edit/modify PHPAskIt however you like
- Tell other people about it
- Ask for help regarding this script at any time

You may not:
- Redistribute this script in any way, shape or form without written permission from its creator, whether it has been modified or not
- Claim ownership of this script, however much you have modified it
- Earn money by installing, customising, modifying or troubleshooting this script for other people
- Hold PHPAskIt's creator responsible for anything that arises from its use
- Remove, modify, hide or use any other method to eliminate or render invisible the "Powered by PHPAskIt 3.0" line at the bottom of the index.php and admin.php files

Amelie, Not-Noticeably.net
===============================================================================================
*/

// EDIT THESE VARIABLES TO MATCH YOUR DATABASE SETTINGS.
//  Put your settings after the = (between the ''s), don't put them in the [] brackets.

// EXAMPLE: This is WRONG:
//  $phpaskit['localhost'] = ''
//  $phpaskit['mydatabasename'] = ''
//  (etc.)

// This is CORRECT:
//  $phpaskit['dbhost'] = 'localhost';
//  $phpaskit['dbname'] = 'mydatabasename';
//  (etc.)

//---------------------------

$phpaskit['dbhost'] = 'localhost'; 		// This is usually localhost, but check with your host if you are not sure
$phpaskit['dbname'] = ''; 				// Your database name
$phpaskit['dbuser'] = ''; 				// The username that has access to the above database
$phpaskit['dbpass'] = '';				// Password for above user

$phpaskit['table'] = 'phpaskit'; 		// Name of the table used in the database. You need only change this if you are using more than one installation, as it is also the name of the login cookie. (If you do not change it with more than one installation, you will find logging in difficult.) */

//---------------------------



//DO NOT EDIT BELOW THIS LINE
//---------------------------

define('PAI_HOST', $phpaskit['dbhost']);
define('PAI_DB', $phpaskit['dbname']);
define('PAI_USER', $phpaskit['dbuser']);
define('PAI_PASS', $phpaskit['dbpass']);
define('PAI_TABLE', $phpaskit['table']);

$phpaskit = array(); unset($phpaskit);
?>