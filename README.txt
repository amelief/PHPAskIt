~~~~~~~~~~~~~~~~~~
##
## Askably 3.1 ##
                ##
~~~~~~~~~~~~~~~~~~


PLEASE BE SURE TO READ ALL OF THIS FILE BEFORE USING Askably.

===================================================================
Askably (formerly PHPAskIt): Copyright © 2005-2009 Amelie M.

You may:
- Use and edit/modify Askably however you like, AS LONG AS THE CREDIT LINE STAYS INTACT AND UNEDITED
- Tell other people about it
- Ask for help regarding this script at any time

You may not:
- Redistribute this script in any way, shape or form without written permission from its creator, whether it has been modified or not
- Claim ownership of this script, however much you have modified it
- Earn money by installing, customising, modifying or troubleshooting this script for other people
- Hold Askably's creator responsible for anything that arises from its use
- Remove, modify, hide or use any other method to eliminate or render invisible the "Powered by Askably 3.1" line at the bottom of the index.php and admin.php files

Amelie, Not-Noticeably.net
===================================================================


CONTENTS OF THIS FILE:
    - Introduction
    - Change log
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

Askably is a question and answer script which incorporates categories and permalinks into your questions page. It is simple to customise and easy to set up, and makes managing your questions and answers so much easier.



CHANGE LOG
----------------------------------------

VERSION 3.1
- Optimised queries, code and general performance - now supports PHP5+ only
- Enabled bbCode for use as an alternative to HTML
- Changed to Prototype JavaScript framework for more advanced AJAX integration
- Renamed to Askably

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

To install Askably, you will need PHP 5.0+ (earlier versions DO NOT WORK) and a MySQL 4.1+ database. Normally you will need to have a paid hosting plan for this, as most free hosts do not offer this service.

If you don't know what version of PHP or MySQL you have, ask your host or take a look at your control panel - it will normally tell you there. If you don't have PHP5, you can download Askably v3.0 which is PHP4 compatible.



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

If you are going to be using Askably on a WordPress-driven site, please go to step 7.
If you are upgrading from a previous version of Askably (regardless of the above), please go to step 8.


--------FOR NEW INSTALLATIONS OF Askably--------

Before you upload the files to your server, take care to complete the following steps.

1. Create a MySQL database and user for this script. You can of course use an existing one, provided you do not already have a script (this one or another) with the same table name.

2. Open config.php in a plain text editor such as Notepad for Windows. Change the values in quotes to those matching your database details. The file is commented out to help you know what to edit.

You can change the $askably['table'] variable (the table name) to anything you like - it doesn't particularly matter since you do not need to remember this to use the script. However, if you already have an installation of Askably on your site, even if it is not in the same database, you will need to change it to something you have not used before as it is also the name of the login cookie. If you do not change it, you will have problems logging in.

3. Once you have finished editing config.php, and that you have checked over the details to make sure they're correct, upload all the files and folders to your server. It does not matter where you put them, though you should remember for linking in your site. ;) You should put the import folder inside the folder where you install Askably; for example if you installed it in the folder /faq, then the import folder should be placed in the /faq folder as well.

4. When the files have finished being uploaded, go to install.php in your browser. The url to this file will be wherever you uploaded it, eg. if you uploaded it to the folder /myfolder/questions/ then the url will look something like yoursite.com/myfolder/questions/install.php.

install.php will take you through the rest of the setup process.

You do not have to use the header and footer files supplied in the zip file. If you want to use your own header and footer files for your question page, enter the ABSOLUTE PATH to your files where specified in install.php. An absolute path usually looks like this: /home/username/public_html/folder/file.html, where "username" is replaced with the name you use to login to your FTP and control panel (your absolute path depends on your host - it may not look anything like this example). If you do not know your absolute path, ask your host about it. If you have header and footer files in the same directory as your Askably files, you can enter a relative path. A relative path normally looks like this: filename.html - basically just the name of the file you want to use. Please be aware that these header and footer files will only be used on the questions page, not the admin panel.
Do NOT fill in these sections if you want to use your WordPress theme with Askably. Instead, fill in the absolute path to wp-blog-header.php (this file is in your WordPress folder).

5. After you have finished entering all your settings, click submit. Did you get a success message? Yes? Good! The script has been installed successfully. Log back into your FTP and delete the install.php, import and upgrade.php files (if you don't do this, you won't be able to use the script).

Didn't get a success message? The most likely problem is that you haven't entered your database details correctly. Have another look at config.php, making absolutely sure that all the information is correct. If you aren't sure what your database details are, please ask your host.

6. That's it! Now you may start importing questions or customising the script.


--------INSTRUCTIONS FOR USE WITH WORDPRESS-DRIVEN SITES--------

7. Askably must be set up slightly differently if you are using a WordPress-driven site.

You are using a Wordpress-driven site if you want to use WordPress's headers and footers or themes functions on your questions page, for example.

PLEASE NOTE: You CANNOT use a WordPress "Page" to manage your questions, even if you have PHP-running plugins such as runPHP or PHPExec installed. Askably DOES _NOT_ WORK WITHIN WORDPRESS PAGES. Do not try to include the script files into WP pages using a PHP include or other such method. Likewise, do not copy the code from the script files and paste it into a WordPress Page - THIS WILL NOT WORK.
You must upload the script's pages separately and link them manually (i.e. you must link to index.php, you cannot rely on your WordPress page navigation to include these files).

7.1. In config.php, you should specify your database settings.
IMPORTANT:--- The database settings you must give are those of your WORDPRESS DATABASE. If you don't specify these, you'll find the scripts will conflict.
The table name should be different to your WordPress ones, however. Make sure you name it something that you don't already have in that particular database, for example askably1 if it is your first Askably database, askably2 if it is your second installation, etc. You will not have to remember this name, but you should not have two tables named the same thing, whether it is in the same database or not. As explained in step 2, the table name is also the name of the login cookie and you will find logging in difficult if you have two installations with the same name.

7.2. You should then run through the rest of steps 3-6 above as normal.


--------UPGRADING FROM PREVIOUS VERSIONS OF Askably--------

8. To upgrade from a previous version of Askably (it doesn't matter which version), you should delete ALL your old files first, INCLUDING config.php and the header/footer.html files. You should then upload all of the new files - EXCEPT install.php - in their place. You should also back up your database.

8.1. Fill in the database variables in config.php, as in step 2 above. This should contain the same database information (including the table name) as your old config.php.

8.2. Run upgrade.php, which will take you through the rest of the upgrade process.

Note: Do NOT attempt to run install.php on an old version of Askably, even if the script instructs you to do so. This will corrupt your data.



IMPORTING QUESTIONS
----------------------------------------

Askably comes with four files to assist you in importing questions into the script. Currently, Askably supports the following scripts:
- Ask&Answer (formerly from posed.org): use convertaa.php
- Wak's Ask&Answer (formerly from luved.org): use convertwaks.php
- FAQtastic (formerly from scripts.inexistent.org): use convertfaqtastic.php

A separate file, import.php, is included to import questions from any other script or from manual FAQ pages.

Full instructions are provided in each file. Make sure you import questions before you let people ask you questions - otherwise they will be met with an error message instructing you to delete the import folder.



CUSTOMISATION
----------------------------------------

To customise Askably, all you need to do is to edit the header.html and footer.html files to suit you and/or modify the templates in the admin panel. If you specified your own headers/footers in the setup process or options panel, you need only modify the templates.

Once you have customised/created your own header/footer files, upload them to the location you specified in the setup or options panel. If you didn't change the values or entered a relative filename (e.g. "header.html" rather than "/an/example/absolute/path/to/folder/askably/header.html"), this location will be the same as where you uploaded the .php files.


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

You may contact me personally at amelie@not-noticeably.net but please be warned that I have limited time available to answer personal requests. I am active at the CodeGrrl forums (http://codegrrl.com/forums/) and will be able to answer any queries there. This is a better option than contacting me personally since questions asked and answered on the forums will be available for future reference, which may help others having similar problems. Your query may also be answered more quickly by other members 



CREDITS
----------------------------------------

Askably would not have been possible without the help of the following wonderful people:

Amanda (http://punkified.net), Vixx (http://furious-angel.com), Jem (http://www.jemjabella.co.uk), Valerie (http://spoken-for.org), Katy, Rachael (http://www.calmbanana.co.uk/) and Melissa (http://melly.me/).

The spinner/loading graphic is provided by http://www.ajaxload.info. Visit to get your own loading graphics!

I would also like to thank CodeGrrl for hosting Askably/PHPAskIt for the past two years. It is time to part ways, however.



TERMS OF USE/DISCLAIMER
--------------------------------------------------------

Askably is a linkware script. You may use, modify and customise the script however you like as long as the link on the bottom of each page to Not-Noticeably.net is maintained.

You may not claim ownership of this script, however much you have modified it. You may not request money for anything to do with this script either. The script is to remain free at all times. This includes requesting money for customising and/or installing, modifying or distributing Askably to/for others, whether this is a modified version of the script or not. If I am not making any money from my script, then neither should you.

If you are in any doubt as to whether what you wish to do with the script complies with the terms of use, please do not hesitate to contact me.

Please be aware that I DO NOT accept any responsibility for anything that may happen to your site, server or computer as a result of you using this script. I do my best to fully test my scripts, but I cannot guarantee their safety and security. By using this script, you do so AT YOUR OWN RISK.

Askably is Copyright © 2005-2009 Amelie M. All rights reserved.