<?php
/*
  ==============================================================================================
  Askably 3.1 © 2005-2009 Amelie M.
  ==============================================================================================
*/

################################################################################################
############################ CORE ASKABLY FUNCTIONS. DO _NOT_ EDIT. ############################
################################################################################################


if (!defined('PAI_IN')) exit('<p>This file cannot be loaded directly.</p>');

/**
 * PAI class.
 */
class PAI {

	/**
	 * Salt for md5.
	 *
	 * @var string Salt for md5.
	 */
	private $mask;

	/**
	 * The number of unanswered questions.
	 *
	 * @var int Number of unanswered questions.
	 */
	private $unanswered = 0;

	/**
	 * The number of answered questions.
	 *
	 * @var int Number of answered questions.
	 */
	private $answered = 0;

	/**
	 * The total number of answered questions.
	 *
	 * @var int Total number of questions.
	 */
	private $total = 0;

	/**
	 * The number of categories that questions have been asked in.
	 *
	 * @var int Number of categories.
	 */
	private $cats = 0;

	/**
	 * Options cache.
	 *
	 * @var array The options cache.
	 */
	private $options = array();

	/**
	 * Constructor.
	 *
	 * @global Database $pai_db The database object.
	 */
	public function __construct() {
		global $pai_db;
		// TODO: combine these
		$total = mysql_fetch_object($pai_db->query('SELECT COUNT(`q_id`) AS `num` FROM `' . $pai_db->getTable() . '`'));
		$this->total = $total->num;

		$unanswered = mysql_fetch_object($pai_db->query('SELECT COUNT(`q_id`) AS `num` FROM `' . $pai_db->getTable() . "` WHERE `answer` = '' OR `answer` IS NULL"));
		$this->unanswered = $unanswered->num;

		$this->answered = $this->total - $this->unanswered;

		$cats = mysql_fetch_object($pai_db->query('SELECT COUNT(DISTINCT `category`) AS `num` FROM `' . $pai_db->getTable() . '`'));
		$this->cats = $cats->num;

		$options = $pai_db->query('SELECT `option_name`, `option_value` FROM `' . $pai_db->getTable() . '_options`');
		while($get_options = mysql_fetch_object($options)) {
			$this->options[$get_options->option_name] = $get_options->option_value;
		}
		$this->mask = md5($this->options['security_word'] . '6743892djkdgjh');
	}

	/**
	 * Rebuild options cache.
	 *
	 * @global Database $pai_db Database object.
	 */
	public function resetOptions() {
		global $pai_db;
		$options = $pai_db->query('SELECT `option_name`, `option_value` FROM `' . $pai_db->getTable() . '_options`');
		while($get_options = mysql_fetch_object($options)) {
			$this->options[$get_options->option_name] = $get_options->option_value;
		}
		$this->mask = md5($this->options['security_word'] . '6743892djkdgjh');
	}

	/**
	 * Getter for mask.
	 *
	 * @return string The md5 salt.
	 */
	public function getMask() {
		return $this->mask;
	}

	/**
	 * Setter for mask.
	 *
	 * @param string $word The user's security word.
	 */
	public function setMask($word) {
		$this->mask = md5($word . '6743892djkdgjh');
	}

	/**
	 * Get an option from the cache.
	 *
	 * @param string $option The option to get.
	 * @return mixed The option value or false if it is null.
	 */
	public function getOption($option) {
		return (array_key_exists($option, $this->options) ? $this->options[$option] : false);
	}

	/**
	 * Getter for unanswered questions.
	 *
	 * @return int Unanswered questions.
	 */
	public function getUnanswered() {
		return $this->unanswered;
	}

	/**
	 * Setter for unanswered questions.
	 *
	 * @param int $unanswered The unanswered question amount to set.
	 */
	public function setUnanswered($unanswered) {
		$this->unanswered = $unanswered;
	}

	/**
	 * Getter for answered questions.
	 *
	 * @return int Answered questions.
	 */
	public function getAnswered() {
		return $this->answered;
	}

	/**
	 * Setter for answered questions.
	 *
	 * @param int $answered The answered question amount to set.
	 */
	public function setAnswered($answered) {
		$this->answered = $answered;
	}

	/**
	 * Setter for total questions.
	 *
	 * @param int $total The total amount of questions to set.
	 */
	public function setTotal($total) {
		$this->total = $total;
	}

	/**
	 * Getter for total questions.
	 *
	 * @return int The total number of questions.
	 */
	public function getTotal() {
		return $this->total;
	}

	/**
	 * Getter for number of categories.
	 *
	 * @return int The number of categories.
	 */
	public function getCats() {
		return $this->cats;
	}

	/**
	 * Setter for number of categories.
	 *
	 * @param int $cats The number of categories to set.
	 */
	public function setCats($cats) {
		$this->cats = $cats;
	}

	/**
	 * Show questions.
	 *
	 * @param object $sql The query object.
	 */
	public function showQs($sql) {
		if (empty($sql->answer)) $answer = '(Unanswered)';
		else $answer = $this->convertBB($sql->answer);

		if ($this->getOption('enable_cats') == 'yes') $cat = '<a href="?category=' . $sql->category . '" title="See all questions in the ' . $sql->cat_name . ' category">' . $sql->cat_name . '</a>';
		else $cat = '';

		$sub = array('[[question]]', '[[answer]]', '[[category]]', '[[permalink]]', '[[date]]');
		$replace = array($sql->question, $answer, $cat, '?q=' . $sql->q_id, date($this->getOption('date_format'), strtotime($sql->dateasked)));

		echo str_replace($sub, $replace, $this->getOption('q_template'));
	}

	/**
	 * Magic happens here.
	 */
	public function adminLogout() {
		/* Oh noes, PAI did a funny */
	}

	/**
	 *
	 * @global Database $pai_db The database object.
	 * @param string $link Where to link back to, default index.php.
	 */
	public function askForm($link) {
		global $pai_db;
		if ($this->getOption('enable_cats') == 'yes') {
			$getcats = $pai_db->query('SELECT * FROM `' . $pai_db->getTable() . '_cats` ORDER BY `cat_name` ASC');
			if (mysql_num_rows($getcats) > 0) {
				$cat = '<select name="category" id="category">' . "\n" . '<option value="">CHOOSE ONE:</option>';
				while($cats = mysql_fetch_object($getcats)) {
					$cat .= "\n<option value=\"" . $cats->cat_id . '">' . $cats->cat_name . '</option>';
				}
				$cat .= '</select>';
			}
			else $cat = '';
		}
		else $cat = '';

		$sub = array('[[question]]', '[[category]]', '[[submit]]');
		$replace = array('<input type="text" name="question" id="question" />', $cat, '<input type="submit" name="askit" id="askit" value="Ask" />');
		?>

		<form class="pai-question-form" method="post" action="<?php echo cleaninput($link); ?>">
			<?php echo str_replace($sub, $replace, $this->getOption('ask_template')); ?>
		</form>
	<?php
	}

	/**
	 * Get a selectable list of categories.
	 *
	 * @global Database $pai_db The database object.
	 * @param int $current_cat The currently selected category.
	 */
	public function getCategories($current_cat = '') {
		global $pai_db;
		$cats = $pai_db->query('SELECT * FROM `' . $pai_db->getTable() . '_cats`');
		while($cat = mysql_fetch_object($cats)) { ?>
			<option value="<?php echo $cat->cat_id; ?>"<?php if ($cat->cat_id == $current_cat) echo ' selected="selected"'; ?>><?php echo $cat->cat_name; ?></option>
			<?php
		}
	}

	/**
	 * Display questions in formatted for admin.
	 *
	 * @global string $token The session token.
	 * @param object $sql The query object.
	 */
	public function adminQs($sql) {
		global $token;
		$question = new Question();
		$question->setAll($sql);
		?>
		<li class="question-container">
			<form action="admin.php?edit=category&amp;inline=true" method="post" onsubmit="
				new Ajax.Request('admin.php?edit=category&amp;inline=true', {
					asynchronous:true,
					onComplete:function(request) {
						Element.show('category_read_<?php echo $question->getId(); ?>');
						Element.hide('indicator<?php echo $question->getId(); ?>');
						Element.hide('category_edit_<?php echo $question->getId(); ?>');
						$('category_read_<?php echo $question->getId(); ?>').update(request.responseText);
					},
					onLoading:function(request) {
						Element.show('indicator<?php echo $question->getId(); ?>');
					},
					parameters:Form.serialize(this)
				}); return false;">
				<h4 class="date"><?php echo $question->getDateAskedFormatted();
				if ($this->getOption('enable_cats') == 'yes') { ?>
					<span class="category" id="category_read_<?php echo $question->getId(); ?>">(<a href="admin.php?category=<?php echo $question->getCategory(); ?>" title="See all questions in the <?php echo $question->getCategory(true); ?> category"><?php echo $question->getCategory(true); ?></a>)</span>
					<span id="category_edit_<?php echo $question->getId(); ?>" class="category" style="display: none;">
						<input type="hidden" name="id" id="category_id_<?php echo $question->getId(); ?>" value="<?php echo $question->getId(); ?>" />
						<input type="hidden" name="token" id="category_token<?php echo $question->getId(); ?>" value="<?php echo $token; ?>" />
						<select name="category" id="category_edit_<?php echo $question->getId(); ?>_menu">
							<?php $this->getCategories($question->getCategory()); ?>
						</select>
						<input type="submit" value="Save category" name="submit_category" id="submit_category_<?php echo $question->getId(); ?>" style="font-size: 0.8em;" /> <input type="reset" onclick="Element.hide('category_edit_<?php echo $question->getId(); ?>'); Element.show('category_read_<?php echo $question->getId(); ?>'); return false;" name="cancel" id="cancel_category_<?php echo $question->getId(); ?>" value="Cancel" style="font-size: 0.8em" />
					</span>
					<a href="admin.php?edit=category" onclick="Element.hide('category_read_<?php echo $question->getId(); ?>'); Element.show('category_edit_<?php echo $question->getId(); ?>'); if ($('category_edit_<?php echo $question->getId(); ?>_menu')) $('category_edit_<?php echo $question->getId(); ?>_menu').focus(); return false;" style="font-size: 0.6em;">e</a>
				<?php
			} ?> <img src="indicator.gif" alt="Saving..." title="Saving..." id="indicator<?php echo $question->getId(); ?>" style="display: none;" /></h4>
			</form>
			<form action="admin.php?edit=question&amp;inline=true" method="post" onsubmit="
				new Ajax.Request('admin.php?edit=question&amp;inline=true', {
					asynchronous:true,
					onComplete:function(request) {
						$('question_read_<?php echo $question->getId(); ?>').style.display = 'block';
						Element.hide('indicator<?php echo $question->getId(); ?>');
						Element.hide('question_edit_<?php echo $question->getId(); ?>');
						$('question_read_<?php echo $question->getId(); ?>').update(request.responseText);
					},
					onLoading:function(request) {
						Element.show('indicator<?php echo $question->getId(); ?>');
					},
					parameters:Form.serialize(this)
				}); return false;">
				<p class="question" id="question<?php echo $question->getId(); ?>" title="Click to edit question">
					<span id="question_read_<?php echo $question->getId(); ?>" style="display: block;" onclick="Element.hide('question_read_<?php echo $question->getId(); ?>'); Element.show('question_edit_<?php echo $question->getId(); ?>'); if ($('question_edit_<?php echo $question->getId(); ?>_box')) $('question_edit_<?php echo $question->getId(); ?>_box').focus(); return false;">
						<a href="admin.php?q=<?php echo $question->getId(); ?>" title="Permalink to this question"><?php echo $question->getQuestion(); ?></a>
					</span>
					<span id="question_edit_<?php echo $question->getId(); ?>" style="display: none;">
						<input type="hidden" name="id" id="question_id_<?php echo $question->getId(); ?>" value="<?php echo $question->getId(); ?>" />
						<input type="hidden" name="token" id="question_token<?php echo $question->getId(); ?>" value="<?php echo $token; ?>" />
						<input type="text" name="question" id="question_edit_<?php echo $question->getId(); ?>_box" style="width: 99%;" value="<?php echo $question->getQuestion(); ?>" /><br />
						<input type="submit" value="Save question" name="submit_question" id="submit_question_<?php echo $question->getId(); ?>" /> <input type="reset" onclick="Element.hide('question_edit_<?php echo $question->getId(); ?>'); $('question_read_<?php echo $question->getId(); ?>').style.display = 'block'; return false;" name="cancel" id="cancel_question_<?php echo $question->getId(); ?>" value="Cancel" />
					</span>
				</p>
			</form>
			<form action="admin.php?edit=answer&amp;inline=true" method="post" onsubmit="
				new Ajax.Request('admin.php?edit=answer&amp;inline=true', {
					asynchronous:true,
					onComplete:function(request) {
						if (request.responseText == '(No answer)') $('answer<?php echo $question->getId(); ?>').className = 'answer unanswered';
						else $('answer<?php echo $question->getId(); ?>').className = 'answer';
						$('answer_read_<?php echo $question->getId(); ?>').style.display = 'block';
						Element.hide('indicator<?php echo $question->getId(); ?>');
						Element.hide('answer_edit_<?php echo $question->getId(); ?>');
						$('answer_read_<?php echo $question->getId(); ?>').update(request.responseText);
					},
					onLoading:function(request) {
						Element.show('indicator<?php echo $question->getId(); ?>');
					},
					parameters:Form.serialize(this)
				}); return false;">
				<p id="answer<?php echo $question->getId(); ?>" class="answer<?php if ($question->getAnswer() == '') echo ' unanswered'; ?>" title="<?php if ($question->getAnswer() == '') echo 'Click to add an answer'; else echo 'Click to edit answer'; ?>">
				<span id="answer_read_<?php echo $question->getId(); ?>" style="display: block;" onclick="Element.hide('answer_read_<?php echo $question->getId(); ?>'); Element.show('answer_edit_<?php echo $question->getId(); ?>'); if ($('answer_edit_<?php echo $question->getId(); ?>_area')) $('answer_edit_<?php echo $question->getId(); ?>_area').focus(); return false;">
					<?php if ($question->getAnswer() == '') echo '(No answer)'; else echo $this->convertBB($question->getAnswer()); ?>
				</span>
				<span id="answer_edit_<?php echo $question->getId(); ?>" style="display: none;">
					<input type="hidden" name="id" id="answer_id_<?php echo $question->getId(); ?>" value="<?php echo $question->getId(); ?>" />
					<input type="hidden" name="token" id="answer_token<?php echo $question->getId(); ?>" value="<?php echo $token; ?>" />
					<textarea id="answer_edit_<?php echo $question->getId(); ?>_area" name="answer" style="width: 99%;" rows="10" cols="70"><?php echo ($question->getAnswer() == '' ? '' : strip_tags($question->getAnswer())); ?></textarea><br />
					<input type="submit" value="Save answer" name="save" id="save_button_<?php echo $question->getId(); ?>" /> <input type="reset" onclick="Element.hide('answer_edit_<?php echo $question->getId(); ?>'); $('answer_read_<?php echo $question->getId(); ?>').style.display = 'block'; return false;" name="cancel" id="cancel_answer_<?php echo $question->getId(); ?>" value="Cancel" />
				</span>
			</p>
			</form>
			<p class="ip"><?php echo $question->getIp(); ?> <?php if ($this->getOption('ipban_enable') == 'yes') { ?>[<a href="admin.php?manage=ips&amp;action=add&amp;ip=<?php echo $question->getIp(); ?>&amp;token=<?php echo $token; ?>" title="Ban this IP from asking more questions">Ban?</a>]<?php } ?></p>

			<p class="tools center">
				<a href="admin.php?edit=answer&amp;qu=<?php echo $question->getId(); ?>&amp;token=<?php echo $token; ?>" title="<?php
				if ($question->getAnswer() == '') {
					?>Answer this question">Answer Question<?php
				}
				else {
					?>Edit your answer to this question">Edit Answer<?php
				} ?></a> |
				<a href="admin.php?edit=question&amp;qu=<?php echo $question->getId(); ?>&amp;token=<?php echo $token; ?>" title="Edit this question">Edit Question</a>

				<?php
				if ($this->getOption('enable_cats') == 'yes') { ?>
					| <a href="admin.php?edit=category&amp;qu=<?php echo $question->getId(); ?>&amp;token=<?php echo $token; ?>" title="Change the category of this question">
					Change Category</a>
					<?php
				} ?>
				| <a href="admin.php?delete=<?php echo $question->getId(); ?>&amp;token=<?php echo $token; ?>" onclick="return confirm('Are you sure you want to delete this question?')" title="Delete this question">Delete</a>
			</p>
		</li>
		<?php
	}

	/**
	 * Check the session token.
	 *
	 * @param boolean $post Is this a post request?
	 * @param boolean $get Is this a get request?
	 * @param boolean $inline Is this an AJAX request?
	 */
	public function checkToken($post = true, $get = false, $inline = false) {
		 //TODO: Use request method instead of static
		if ($post == true) {
			if (!array_key_exists('token', $_POST)) {
				if ($inline) $this->killToken(true);
				else $this->killToken();
			}
			else $token = $_POST['token'];
		}
		elseif ($get == true) {
			if (!array_key_exists('token', $_GET)) {
				if ($inline) $this->killToken(true);
				else $this->killToken();
			}
			else $token = $_GET['token'];
		}
		if (!array_key_exists('pai_token', $_SESSION) || $token != $_SESSION['pai_token']) {
			if ($inline == true) $this->killToken(true);
			else $this->killToken();
		}
		// TODO: Redo this - doesn't work
		if (!array_key_exists('pai_time', $_SESSION) || ((time() - $_SESSION['pai_time']) > 300)) {
			if ($inline == true) $this->killToken(true);
			else $this->killToken();
		}
	}

	/**
	 * Clear session token.
	 *
	 * @param boolean $inline Is this an AJAX request?
	 */
	protected function killToken($inline = false) {
		$_SESSION = array();
		if (array_key_exists(session_name(), $_COOKIE)) setcookie(session_name(), '', time()-3600, '/');
		@session_destroy();
		if ($inline == true) {
			ob_end_clean();
			exit('<strong>Error:</strong> Your session has expired. Please refresh the page to correct this problem.');
		}
		else {
			ob_end_flush();
			$error = new Error('Your session has expired. Please refresh the page to correct this problem.');
			$error->display();
		}
	}

	/**
	 * Convert the existing bbCoded text to HTML.
	 *
	 * @param string $text The text to format.
	 * @return string The formatted text.
	 */
	public function convertBB($text) {
		$text = preg_replace('/\[code\](.*?)\[\/code\]/is', '<pre>$1</pre>', $text);
		$text = preg_replace('/\[b\](.*?)\[\/b\]/is', '<strong>$1</strong>', $text);
		$text = preg_replace('/\[u\](.*?)\[\/u\]/is', '<span style="text-decoration: underline;">$1</span>', $text);
		$text = preg_replace('/\[i\](.*?)\[\/i\]/is', '<em>$1</em>', $text);
		$text = preg_replace('/\[q=([0-9]+)\](.*?)\[\/q\]/is', '<a href="?q=$1">$2</a>', $text);

		$patterns[] = "#\[url=([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*?)\]([^?\r\t].*?)\[/url\]#is";
		$replacements[] = '<a href="$1" title="$2">$2</a>';

		$patterns[] = "#\[url=((www)\.[\w\#$%&~/.\-;:=,?@\[\]+]*?)\]([^?\n\r\t].*?)\[/url\]#is";
		$replacements[] = '<a href="http://$1" title="$3">$3</a>';

		$text = preg_replace($patterns, $replacements, $text);

		return $text;
	}

	/**
	 * Verify login details.
	 *
	 * @global string $token The session token.
	 * @global Database $pai_db The database object.
	 */
	public function doLogin() {
		global $token, $pai_db;
		if (array_key_exists('process', $_GET)) {
			switch($_GET['process']) {
				case 'login':
					if (array_key_exists('userlogon', $_POST) && array_key_exists('userpassword', $_POST) && !empty($_POST['userlogon']) && !empty($_POST['userpassword']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
						ob_start();
						adminheader();
						echo '<h1 id="header"><a href="admin.php" title="Back to main admin page">Askably</a></h1>

						<ul id="navigation" class="center">
							<li><a href="admin.php" class="active">Login</a></li>
							<li><a href="admin.php?process=reset" title="Reset password">Lost password</a></li>
						</ul>
						<div class="center">';

						if (!array_key_exists('token', $_POST)) {
							ob_end_flush();
							$error = new Error('Your session has expired. Please reload the page to correct this issue.');
							$error->display();
						}
						$this->checkToken($_POST['token']);
						if (cleaninput($_POST['userlogon']) == $this->getOption('username') && md5(cleaninput($_POST['userpassword']) . $this->getMask()) == $this->getOption('password')) {
							setcookie($pai_db->getTable() . '_user', $this->getOption('username'), time()+(86400*365), '/');
							// TODO: Change this. Not secure.
							setcookie($pai_db->getTable() . '_pass', 'Loggedin_' . $this->getOption('password'), time()+(86400*365), '/');
							header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/admin.php');
							ob_end_flush();
							exit;
						}
						else {
							ob_end_clean();
							$this->displayLogin('Incorrect username or password. Please try again.');
						}
					}
					break;

				case 'logout':
					setcookie($pai_db->getTable() . '_user', '', time()-3600, '/');
					setcookie($pai_db->getTable() . '_pass', '', time()-3600, '/');

					$_SESSION = array();
					if (array_key_exists(session_name(), $_COOKIE)) setcookie(session_name(), '', time()-3600, '/');
					@session_destroy();

					header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/admin.php');
					break;

				case 'reset':
					ob_start();
					adminheader();

					echo '<h1 id="header"><a href="admin.php" title="Back to main admin page">Askably</a></h1>
					<ul id="navigation" class="center">
						<li><a href="admin.php">Login</a></li>
						<li><a href="admin.php?process=reset" title="Reset password" class="active">Lost password</a></li>
					</ul>
					<div class="center">';
					if ($_SERVER['REQUEST_METHOD'] == 'POST' && array_key_exists('submit', $_POST) && array_key_exists('username', $_POST) && array_key_exists('email_address', $_POST) && array_key_exists('security_word', $_POST)) {
						if (!array_key_exists('token', $_POST)) {
							ob_end_flush();
							$error = new Error('Your session has expired. Please reload the page to correct this issue.');
							$error->display();
						}
						$this->checkToken($_POST['token']);
						ob_end_flush();
						$username = cleaninput($_POST['username']);
						$email = cleaninput($_POST['email_address']);
						$word = cleaninput($_POST['security_word']);
						if ($username == $this->getOption('username') && $email == $this->getOption('youraddress') && $word == $this->getOption('security_word')) {
							$newpassword = substr(md5(substr(md5(microtime()), 5, 7)), 5, 7);
							if ($pai_db->query('UPDATE `' . $pai_db->getTable() . "_options` SET `option_value` = '" . md5($newpassword . $this->getMask()) . "' WHERE `option_name` = 'password' LIMIT 1")) {
								$msg = 'Your password to Askably has been reset. Your new password is: ' . $newpassword . "\n\nPlease login and change it as soon as possible.";
								@mail($this->getOption('youraddress'), 'Askably: Password reset', $msg, 'From: Askably <' . $this->getOption('youraddress') . '>');
								echo '<p>Your password has been reset and sent to you by email. Please <a href="admin.php" title="Log in">log in</a> and change it as soon as possible.</p>';
							}
							else echo '<p>Your password could not be reset.</p>';
						}
						else echo '<p>The username, e-mail address or security word entered does not match the ones in Askably\'s options. Please go back and try again.</p>';
					}
					else { ?>
						<p>Please enter the username, e-mail address and the security word you provided in the options panel.</p>
						<form method="post" action="admin.php?process=reset">
							<p><input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
							<label for="username">Username:</label><br />
							<input type="text" name="username" id="username" /></p>

							<p><label for="email_address">E-mail address</label><br />
							<input type="text" name="email_address" id="email_address" /></p>

							<p><label for="security_word">Security word</label><br />
							<input type="text" name="security_word" id="security_word" /></p>

							<p><input type="submit" name="submit" id="submit" value="Submit" /></p>
						</form>
						<?php
					}
					echo '</div></body></html>';
					exit;
					break;

				default:
					$error = new Error('Invalid process.');
					$error->display();
			}
		}
	}

	/**
	 * Verify that a user is logged in.
	 *
	 * @global Database $pai_db The database object.
	 * @param boolean $nodata Don't display anything to the user.
	 * @return mixed Whether the user is logged in, if they aren't, display the login form.
	 */
	public function isLoggedIn($nodata = false) {
		global $pai_db;
		if (array_key_exists($pai_db->getTable() . '_user', $_COOKIE) && !empty($_COOKIE[$pai_db->getTable() . '_user'])) {
			if ($this->getOption('username') == cleaninput($_COOKIE[$pai_db->getTable() . '_user'])) {
				//TODO: Change this. Not secure to have db password in cookie.
				if (array_key_exists($pai_db->getTable() . '_pass', $_COOKIE) && $_COOKIE[$pai_db->getTable() . '_pass'] == 'Loggedin_' . $this->getOption('password')) return true;
				elseif ($nodata == false) $this->displayLogin('Your session has expired. Please login again.');
			}
			elseif ($nodata == false) $this->displayLogin('Your session has expired. Please login again.');
		}
		elseif ($nodata == false) $this->displayLogin();
	}

	/**
	 * Display the login form.
	 *
	 * @global string $token The session token.
	 * @global Database $pai_db The database object.
	 * @param string $problem Any error messages.
	 */
	protected function displayLogin($problem = '') {
		// TODO: redirect to requested URL
		global $token, $pai_db;
		adminheader();
		?>
		<h1 id="header"><a href="admin.php" title="Back to main admin page">Askably</a></h1>

		<ul id="navigation" class="center">
			<li><a href="admin.php" class="active">Login</a></li>
			<li><a href="admin.php?process=reset" title="Reset password">Lost password</a></li>
		</ul>

		<?php if (!empty($problem)) echo '<p>' . $problem . '</p>'; ?>

		<form method="post" action="admin.php?process=login">
			<p><input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
			<label for="userlogon">Username:</label><br />
			<input type="text" name="userlogon" id="userlogon" /></p>

			<p><label for="userpassword">Password:</label><br />
			<input type="password" name="userpassword" id="userpassword" /></p>

			<p><input name="submitlogin" id="submitlogin" type="submit" value="Login" /></p>
		</form>
		<p class="center">Powered by <a href="http://not-noticeably.net/scripts/askably/" title="Askably">Askably 3.1</a></p>
		<?php
		echo '</body></html>';
		mysql_close($pai_db->getConnection());
		exit;
	}
}
?>