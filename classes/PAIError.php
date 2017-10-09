<?php
/*
  ==============================================================================================
  This file is part of PHPAskIt 3.1 by Amelie F.

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

################################################################################################
############################ CORE PHPASKIT FUNCTIONS. DO _NOT_ EDIT. ############################
################################################################################################

if (!defined('PAI_IN')) exit('<p>This file cannot be loaded directly.</p>');

/**
 * Error class.
 */
class PAIError extends Exception {

	/**
	 * The message to show.
	 *
	 * @var string The message to show.
	 */
	protected $message;

	/**
	 * Whether to exit on this error.
	 *
	 * @var boolean Whether to exit on this error.
	 */
	private $kill;

	/**
	 * The header to display.
	 *
	 * @var string Header to display.
	 */
	private $header;

	/**
	 * Whether to use a heading.
	 *
	 * @var boolean Whether to use a heading.
	 */
	private $heading;

	/**
	 * Constructor.
	 *
	 * @param string $message The message to show.
	 * @param boolean $die Whether to exit on this error.
	 * @param string $header The header message.
	 * @param boolean $heading Whether a header is needed.
	 */
	public function __construct($message, $die = true, $header = '', $heading = true) {
		$this->message = $message;
		$this->kill = $die;
		$this->header = $header;
		$this->heading = $heading;
	}

	/**
	 * Setter for message.
	 *
	 * @param string $message The message to set.
	 */
	public function setMessage($message) {
		$this->message = $message;
	}

	/**
	 * Setter for kill.
	 *
	 * @param boolean $die Whether to exit on this error.
	 */
	public function setDie($die) {
		$this->kill = $die;
	}

	/**
	 * Setter for heading.
	 *
	 * @param string $heading The heading to set.
	 */
	public function setHeading($heading) {
		$this->heading = $heading;
	}

	/**
	 * Display the error.
	 *
	 * @global PAI $pai The PAI object.
	 */
	public function display() {
		global $pai;
		if (isset($_GET['inline'])) {
			ob_end_clean();
			echo '<strong>Error:</strong> ' . parent::getMessage();
			if ($this->kill == true) exit;
		}
		else {
			ob_end_flush();
			if ($this->heading) {
				echo '<h3>';
				if (!empty($this->header)) echo $this->header; else echo 'Error';
				echo '</h3>
				<ul>';
			}
			echo '<li><p>' . parent::getMessage() . '</p></li>';

			if ($this->kill == true) {
				echo '</ul><p style="text-align: center;">Powered by <a href="http://amelierosalyn.com/scripts/" title="PHPAskIt">PHPAskIt 3.1</a></p>';
				if (defined('IS_ADMIN')) echo '</div></div></body></html>';
				elseif ($pai->getOption('is_wordpress') == 'yes') {
					if (function_exists('get_sidebar')) get_sidebar();
					if (function_exists('get_footer')) get_footer();
				}
				else include $pai->getOption('footerfile');
				exit;
			}
		}
	}
	
	public static function showMessage($message, $header = '', $heading = true) {
		$error = new self($message, true, $header, $heading);
		$error->display();
	}
}
?>