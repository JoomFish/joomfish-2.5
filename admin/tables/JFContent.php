<?php
/**
 * Joom!Fish - Multi Lingual extention and translation manager for Joomla!
 * Copyright (C) 2003 - 2013, Think Network GmbH, Konstanz
 *
 * All rights reserved.  The Joom!Fish project is a set of extentions for
 * the content management system Joomla!. It enables Joomla!
 * to manage multi lingual sites especially in all dynamic information
 * which are stored in the database.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307,USA.
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -----------------------------------------------------------------------------
 * @package joomfish
 * @subpackage Tables
 *
*/

// Don't allow direct linking
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Database class for handling the joomfish contents
 *
 * @package joomfish
 * @subpackage administrator
 * @copyright 2003 - 2013, Think Network GmbH, Konstanz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Revision: 1502 $
 * @author Alex Kempkens
 */
class jfContent extends JTable  {
	/** @var int Primary ke */
	public $id=null;
	/** @var int Reference id for the language */
	public $language_id=null;
	/** @var int Reference id for the original content */
	public $reference_id=null;
	/** @var int Reference table of the original content */
	public $reference_table=null;
	/** @var int Reference field of the original content */
	public $reference_field=null;
	/** @var string translated value*/
	public $value=null;
	/** @var string original value for equals check*/
	public $original_value=null;
	/** @var string original value for equals check*/
	public $original_text=null;
	/** @var int user that checked out the jfContent*/
	//	public $checked_out=null;					// not yet supported
	/** @var datetime time when the checkout was done*/
	//	public $checked_out_time=null;			// not yet supported
	/** @var date Date of last modification*/
	public $modified=null;
	/** @var string Last translator*/
	public $modified_by=null;
	/** @var boolean Flag of the translation publishing status*/
	public $published=false;

	/** Standard constructur
	*/
	public function __construct( &$db ) {
		parent::__construct( '#__jf_content', 'id', $db );
	}

	/**
	 * Bind the content of the newValues to the object. Overwrite to make it possible
	 * to use also objects here
	 */
	public function bind( $newValues, $ignore = array() ) {
		if (is_array( $newValues )) {
			return parent::bind( $newValues, $ignore );
		} else {
			foreach (get_object_vars($this) as $k => $v) {
				if ( isset($newValues->$k) ) {
					$this->$k = $newValues->$k;
				}
			}
		}
		return true;
	}


	/**
	 * Validate language information
	 * Name and Code name are mandatory
	 * activated will automatically set to false if not set
	 */
	public function check() {
		if (trim( $this->language_id ) == '') {
			$this->_error = JText::_('NO_LANGUAGE_DBERROR');
			return false;
		}

		return true;
	}

	public function toString() {
		$retString = "<p>content field:<br />";
		$retString .= "id=$this->id; language_id=$this->language_id<br>";
		$retString .= "reference_id=$this->reference_id, reference_table=$this->reference_table, reference_field=$this->reference_field<br>";
		$retString .= "value=>" .htmlspecialchars($this->value). "<<br />";
		$retString .= "original_value=>" .htmlspecialchars($this->original_value). "<<br />";
		$retString .="modified=$this->modified, modified_by=$this->modified_by, published=$this->published</p>";

		return $retString;
	}
}

?>
