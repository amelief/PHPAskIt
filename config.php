<?php if (!defined('PAI_IN')) exit('<p>This file cannot be loaded directly.</p>'); $askably = array(); // DO NOT EDIT

/*
===============================================================================================
Askably 3.1 © 2005-2010 Amelie M.

You may:
- Use and edit/modify Askably however you like
- Tell other people about it
- Ask for help regarding this script at any time

You may not:
- Redistribute this script in any way, shape or form without written permission from its creator, whether it has been modified or not
- Claim ownership of this script, however much you have modified it
- Earn money by installing, customising, modifying or troubleshooting this script for other people
- Hold Askably's creator responsible for anything that arises from its use
- Remove, modify, hide or use any other method to eliminate or render invisible the "Powered by Askably 3.1" line at the bottom of the index.php and admin.php files

Amelie, Not-Noticeably.net
===============================================================================================
*/

// EDIT THESE VARIABLES TO MATCH YOUR DATABASE SETTINGS.
//  Put your settings after the = (between the ''s), don't put them in the [] brackets.

// EXAMPLE: This is WRONG:
//  $askably['localhost'] = ''
//  $askably['mydatabasename'] = ''
//  (etc.)

// This is CORRECT:
//  $askably['dbhost'] = 'localhost';
//  $askably['dbname'] = 'mydatabasename';
//  (etc.)

//---------------------------

$askably['dbhost'] = 'localhost'; 	// This is usually localhost, but check with your host if you are not sure
$askably['dbname'] = ''; 			// Your database name
$askably['dbuser'] = ''; 			// The username that has access to the above database
$askably['dbpass'] = '';			// Password for above user

$askably['table'] = 'askably'; 		// Name of the table used in the database. You need only change this if you are using more than one installation, as it is also the name of the login cookie. (If you do not change it with more than one installation, you will find logging in difficult.) */

//---------------------------



//DO NOT EDIT BELOW THIS LINE
//---------------------------

// TODO: DEBUG - REMOVE
require '../../htpasswds/settings.php';

define('PAI_HOST', $askably['dbhost']);
define('PAI_DB', $askably['dbname']);
define('PAI_USER', $askably['dbuser']);
define('PAI_PASS', $askably['dbpass']);
define('PAI_TABLE', $askably['table']);

$askably = array(); unset($askably);
?>