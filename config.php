<?php if (!defined('PAI_IN')) exit('<p>This file cannot be loaded directly.</p>'); $phpaskit = array(); // DO NOT EDIT

/*
  ==============================================================================================
  PHPAskIt 3.1 by Amelie F.

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

$phpaskit['dbhost'] = 'localhost'; 	// This is usually localhost, but check with your host if you are not sure
$phpaskit['dbname'] = ''; 			// Your database name
$phpaskit['dbuser'] = ''; 			// The username that has access to the above database
$phpaskit['dbpass'] = '';			// Password for above user

$phpaskit['table'] = 'phpaskit'; 		// Name of the table used in the database. You need only change this if you are using more than one installation, as it is also the name of the login cookie. (If you do not change it with more than one installation, you will find logging in difficult.) */
$phpaskit['salt'] = '6743892djkdgjh'; // 'Salt' with which to encrypt your password. You don't have to remember this, so make it as long and as complicated as you like. The only character you can't use is the single quote/apostrophe (').

//---------------------------



//DO NOT EDIT BELOW THIS LINE
//---------------------------

// TODO: DEBUG - REMOVE
require '../../htpasswds/settings.php';

define('PAI_HOST', $phpaskit['dbhost']);
define('PAI_DB', $phpaskit['dbname']);
define('PAI_USER', $phpaskit['dbuser']);
define('PAI_PASS', $phpaskit['dbpass']);
define('PAI_TABLE', $phpaskit['table']);
define('PAI_SALT', $phpaskit['salt']);

$phpaskit = array(); unset($phpaskit);
?>