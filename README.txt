~~~~~~~~~~~~~~~~~~
##
## PHPAskIt 3.1 ##
                ##
~~~~~~~~~~~~~~~~~~


=== PLEASE NOTE: ===

This is a *DEVELOPMENT VERSION* of PHPAskIt. It's not alpha, beta or anything like that - it's not even reached testing yet. That means there WILL be bugs and possibly security issues, and you should really not use it on a production website. I take absolutely no responsibility for anything that does or does not happen as a result of you using development builds. For best results, please use the stable version of PHPAskIt available from http://amelierosalyn.com/scripts.

====================


PLEASE BE SURE TO READ ALL OF THIS FILE BEFORE USING PHPAskIt.

===================================================================
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
===================================================================


CONTENTS OF THIS FILE:
    - Introduction
    - Changelog
    - Requirements
    - Files
    - Installation
        + For WordPress users
        + Upgrading
    - Importing questions
    - Customisation
        + Templates and styles
    - Troubleshooting
    - Credits
    - Terms of use and disclaimer



INTRODUCTION
----------------------------------------

PHPAskIt is a question and answer script which incorporates categories and permalinks into your questions page. It is simple to customise and easy to set up, and makes managing your questions and answers so much easier.



CHANGE LOG
----------------------------------------

VERSION 3.1
- Another complete backend rewrite; optimised queries, code and general performance - now supports PHP5+ only
- Enabled limited bbCode for better formatting of answers
- Changed to Prototype and jQuery JavaScript frameworks for more advanced AJAX integration
- Removed import scripts - now built-in
- Layout tweaks - now HTML5 output with CSS3 effects (HTML4/CSS2 fallback)
- Re-released under the GPL and ported development to GitHub

VERSION 3.0
- Complete backend rewrite, now uses object-oriented programming
- Improved database structure
- Introduction of templates for easy customisation
- Automatic WordPress integration
- create.php renamed to install.php
- New default layout
- User-end question searching
- faq.php removed
- "Sort by category" option removed
- Cleaner URLs
- Summary now optional
- Better login system
- Admin panel enhanced by AJAX
- CSRF protection added
- Import scripts completely rewritten
- FAQtastic import script added

VERSION 2.1
- Several bug fixes, including not allowing duplicate categories, blocked words or blocked IP addresses
- General code optimisation for increased speed and stability
- Increased security overall, including two possibly critical flaws found in functions.php and config.php
- HTML disabled completely

VERSION 2.0.1
- A couple of security fixes in functions.php and config.php

VERSION 2.0
- More secure log in system
- Several bug fixes
- Categories now integrated into database for easier sorting
- Search feature in admin panel
- Protection from SQL injections
- Options and category management panel added
- Ability to turn off category feature
- Hide unanswered questions
- Only allows certain HTML code when HTML is enabled in questions
- Removed customisation from admin panel in order to avoid layout bugs
- Installation now takes place from create.php rather than definition in config.php
- "Friendly" error messages shown and full path disclosure avoided
- Better Windows server compliance
- Increased security - script will not run with create or import files in existence
- Improved input validation - avoids deleting inexistent entries
- Compatibility with magic_quotes_gpc off
- IP blocking
- Spam word blocking

VERSION 1.1
- Better, more secure, login system for admin panel
- Now includes index.php - an index of your categories and how many questions exist within them (previously an add-on)
- A few major bug fixes
- 3 extra files supplied for importing questions (either from a script or manually)



REQUIREMENTS
----------------------------------------

To install PHPAskIt, you will need PHP 5.0+ (earlier versions DO NOT WORK) and a MySQL 4.1+ database. Normally you will need to have a paid hosting plan for this, as most free hosts do not offer this service.

If you don't know what version of PHP or MySQL you have, ask your host or take a look at your control panel - it will normally tell you there. If you don't have PHP5, you can download PHPAskIt v3.0 which is PHP4 compatible.



FILES
----------------------------------------

Inside this zip file, you should have the following files:

- admin.php
- config.php
- functions.php
- index.php
- install.php
- upgrade.php
- ajax.js
- header.html
- footer.html
- readme.txt
- indicator.gif
- /import (folder)
--- convertaa.php
--- convertfaqtastic.php
--- convertwaks.php
--- import.php

If any of those files are missing, try redownloading the file.



INSTALLATION
----------------------------------------

NOTES:

If you are going to be using PHPAskIt on a WordPress-driven site, please go to step 7.
If you are upgrading from a previous version of PHPAskIt (regardless of the above), please go to step 8.


--------FOR NEW INSTALLATIONS OF PHPAskIt--------

Before you upload the files to your server, take care to complete the following steps.

1. Create a MySQL database and user for this script. You can of course use an existing one, provided you do not already have a script (this one or another) with the same table name.

2. Open config.php in a plain text editor such as Notepad for Windows. Change the values in quotes to those matching your database details. The file is commented out to help you know what to edit.

You can change the $phpaskit['table'] variable (the table name) to anything you like - it doesn't particularly matter since you do not need to remember this to use the script. However, if you already have an installation of PHPAskIt on your site, even if it is not in the same database, you will need to change it to something you have not used before as it is also the name of the login cookie. If you do not change it, you will have problems logging in.

3. Once you have finished editing config.php, and that you have checked over the details to make sure they're correct, upload all the files and folders to your server. It does not matter where you put them, though you should remember for linking in your site. ;) You should put the import folder inside the folder where you install PHPAskIt; for example if you installed it in the folder /faq, then the import folder should be placed in the /faq folder as well.

4. When the files have finished being uploaded, go to install.php in your browser. The url to this file will be wherever you uploaded it, eg. if you uploaded it to the folder /myfolder/questions/ then the url will look something like yoursite.com/myfolder/questions/install.php.

install.php will take you through the rest of the setup process.

You do not have to use the header and footer files supplied in the zip file. If you want to use your own header and footer files for your question page, enter the ABSOLUTE PATH to your files where specified in install.php. An absolute path usually looks like this: /home/username/public_html/folder/file.html, where "username" is replaced with the name you use to login to your FTP and control panel (your absolute path depends on your host - it may not look anything like this example). If you do not know your absolute path, ask your host about it. If you have header and footer files in the same directory as your PHPAskIt files, you can enter a relative path. A relative path normally looks like this: filename.html - basically just the name of the file you want to use. Please be aware that these header and footer files will only be used on the questions page, not the admin panel.
Do NOT fill in these sections if you want to use your WordPress theme with PHPAskIt. Instead, fill in the absolute path to wp-blog-header.php (this file is in your WordPress folder).

5. After you have finished entering all your settings, click submit. Did you get a success message? Yes? Good! The script has been installed successfully. Log back into your FTP and delete the install.php and upgrade.php files (if you don't do this, you won't be able to use the script).

Didn't get a success message? The most likely problem is that you haven't entered your database details correctly. Have another look at config.php, making absolutely sure that all the information is correct. If you aren't sure what your database details are, please ask your host.

6. That's it! Now you may start importing questions or customising the script.


--------INSTRUCTIONS FOR USE WITH WORDPRESS-DRIVEN SITES--------

7. PHPAskIt must be set up slightly differently if you are using a WordPress-driven site.

You are using a Wordpress-driven site if you want to use WordPress's headers and footers or themes functions on your questions page, for example.

PLEASE NOTE: You CANNOT use a WordPress "Page" to manage your questions, even if you have PHP-running plugins such as runPHP or PHPExec installed. PHPAskIt DOES _NOT_ WORK WITHIN WORDPRESS PAGES. Do not try to include the script files into WP pages using a PHP include or other such method. Likewise, do not copy the code from the script files and paste it into a WordPress Page - THIS WILL NOT WORK.
You must upload the script's pages separately and link them manually (i.e. you must link to index.php, you cannot rely on your WordPress page navigation to include these files).

7.1. In config.php, you should specify your database settings.
IMPORTANT:--- The database settings you must give are those of your WORDPRESS DATABASE. If you don't specify these, you'll find the scripts will conflict.
The table name should be different to your WordPress ones, however. Make sure you name it something that you don't already have in that particular database, for example phpaskit1 if it is your first PHPAskIt database, phpaskit2 if it is your second installation, etc. You will not have to remember this name, but you should not have two tables named the same thing, whether it is in the same database or not. As explained in step 2, the table name is also the name of the login cookie and you will find logging in difficult if you have two installations with the same name.

7.2. You should then run through the rest of steps 3-6 above as normal.


--------UPGRADING FROM PREVIOUS VERSIONS OF PHPAskIt--------

8. To upgrade from a previous version of PHPAskIt (it doesn't matter which version), you should delete ALL your old files first, INCLUDING config.php and the header/footer.html files. You should then upload all of the new files - EXCEPT install.php - in their place. You should also back up your database.

8.1. Fill in the database variables in config.php, as in step 2 above. This should contain the same database information (including the table name) as your old config.php. Please note that the default table name has changed as a result of PHPAskIt's renaming. Make sure that you put in the correct details for the database table you were using previously.

8.2. If the script does not redirect you to the upgrade page, go to upgrade.php, which will take you through the rest of the upgrade process.

Note: Do NOT attempt to run install.php on an old version of PHPAskIt, even if the script instructs you to do so. This will corrupt your data.



IMPORTING QUESTIONS
----------------------------------------

PHPAskIt comes with the ability to import questions from 3 previously popular scripts as well as the ability to add your own questions from a manual FAQ or other script. Currently, PHPAskIt supports the following scripts:
- Ask&Answer (formerly from posed.org)
- Wak's Ask&Answer (formerly from luved.org)
- FAQtastic (from scripts.inexistent.org)

To import questions, simpy head to the Import section in your admin panel and you will be guided through the rest.



CUSTOMISATION
----------------------------------------

To customise PHPAskIt, all you need to do is to edit the header.html and footer.html files to suit you and/or modify the templates in the admin panel. If you specified your own headers/footers in the setup process or options panel, you need only modify the templates.

Once you have customised/created your own header/footer files, upload them to the location you specified in the setup or options panel. If you didn't change the values or entered a relative filename (e.g. "header.html" rather than "/an/example/absolute/path/to/folder/phpaskit/header.html"), this location will be the same as where you uploaded the .php files.


TEMPLATE VARIABLES AND STYLES

The following template variables can be used to customise your questions page (templates can be edited from your admin panel):

For the question form (the form users can use to ask questions):

[[question]] - inserts the question text box
[[category]] - inserts the category dropdown menu (if categories are enabled)
[[submit]] - displays the submit button.


For the display of questions and answers:

[[question]] - displays the question.
[[permalink]] - this question's permanent link (Note: this tag does not create the actual link. Use with a normal <a> tag, e.g. <a href="[[permalink]]">).
[[answer]] - displays the answer.
[[category]] - displays the category (if enabled).
[[date]] - displays the date and time (depending on format) the question was asked.


For the question summary header (this is the list of answered/unanswered questions at the top of your recent questions page):

[[total]] - displays total questions in the database.
[[answered]] - displays number of answered questions in the database.
[[unanswered]] - displays number of unanswered questions in the database.
[[categories]] - displays the number of categories that questions have been asked in (not the total number of categories, just those that contain questions).

The success message has no variables available.

-------

The following styles/classes can be used to customise the questions page even further:

pai-page-title - the title of the questions page (as specified in the setup process/options panel). This is in a <h1> tag.

pai-summary - this is the summary of questions. <ul> tag.

pai-category-title - this is the title at the top of each page when a category link is clicked (to show all questions in that category). <h3> tag.

pai-search-title - this is the title showing how many results have been found from a search (questions page only, not applicable to admin panel). <h3> tag.

pai-search - this is the form containing the search box. <form> tag.

pai-search-text - the text next to the search box. <h4> tag.

pai-question-form - the form containing the question box. <form> tag.

These should be defined in your CSS, e.g.

    .pai-page-title {
        color: red;
    }

(To make your question page title show up red)



TROUBLESHOOTING
----------------------------------------

Having problems? Spotted a bug? Something not working?

You may contact me personally at http://amelierosalyn.com/about/#contact but please be warned that I have limited time available to answer personal requests. I am active at the GWG forums (http://girlswhogeek.com/forums/) and will be able to answer any queries there. This is a better option than contacting me personally since questions asked and answered on the forums will be available for future reference, which may help others having similar problems. Your query may also be answered more quickly by other members.



CREDITS
----------------------------------------

PHPAskIt would not have been possible without the help of the following wonderful people:

Amanda, Vixx (http://furious-angel.com), Jem (http://www.jemjabella.co.uk), Valerie (http://spoken-for.org), Katy, Rachael and Melissa (http://kirako.net).

The spinner/loading graphic is provided by http://www.ajaxload.info. Visit to get your own loading graphics!

PHPAskIt makes use of the Prototype (prototypejs.org) and jQuery (jquery.com) Javascript libraries, and the Scriptaculous (script.aculo.us) effects bundle.



TERMS OF USE/DISCLAIMER
--------------------------------------------------------

PHPAskIt is a released under the terms of the GNU General Public License v3. 

PHPAskIt is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

PHPAskIt is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.

-----

PHPAskIt � 2014 Amelie F.