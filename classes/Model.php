<?php
/*
  ==============================================================================================
  This file is part of PHPAskIt 3.1, Copyright © 2005-2012 Amelie F.

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
 * Basic model for classes.
 *
 * @author Amelie F.
 */
interface Model {
	/**
	 * Save the object.
	 */
	public function save();

	/**
	 * Create the object.
	 */
	public function create();

	/**
	 * Find a record by ID.
	 *
	 * @param int $id The ID of the record to find.
	 */
	public function findById($id, $fields = '*');

	/**
	 * Escape data for entering into MySQL.
	 *
	 * @param string $data The data to escape.
	 */
	public function sqlEscape($data);

	/**
	 * Delete the object.
	 */
	public function delete();

	/**
	 * Check that a valid ID exists for the object.
	 */
	public function checkId();

	/**
	 * Set the ID of the object.
	 *
	 * @param int $id The ID to set.
	 */
	public function setId($id);

	/**
	 * Get the ID of the object.
	 */
	public function getId();

	/**
	 * Set all properties of the object.
	 *
	 * @param object $info The info to set.
	 */
	public function setAll($info);
}
?>