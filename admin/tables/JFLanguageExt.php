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
 * This class is the special implementation of JoomFish extended language fields.
 * As the relationship between the Joomla language table as well as these additional
 * fields is always a 1:1 mapping the management of this class is aggregaed in the general class
 * JFLanguage
 *
 * @package joomfish
 * @subpackage administrator
 * @copyright 2003 - 2013, Think Network GmbH, Konstanz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Revision: 1474 $
 * @author Alex Kempkens
 */
class TableJFLanguageExt extends JTable  {
	/** @var int Primary key */
	public $lang_id=-1;
	
	/** @var string Image reference if there is any*/
	public $image_ext='';
	
	/** @var string optional code of language to fall back on if translation is missing */
	public $fallback_code='';
	
	/** @var string parameter set base on key=value pairs */
	public $params='';

	/** @var string Order of the languages within the lists*/
	public $ordering=0;
	
	/** Standard constructur
	*/
	public function __construct( &$db ) {
		parent::__construct( '#__jf_languages_ext', 'lang_id', $db );
	}

	/**
	 * Validate language information
	 * Related lang_id must exist in the language table
	 */
	public function check() {
		// check for existing language id
		$this->_db->setQuery( "SELECT lang_id FROM #__language "
		. "\nWHERE lang_id='$this->lang_id'"
		);

		$xid = intval( $this->_db->loadResult() );
		if ($xid == null) {
			$this->_error = "The related language id does not exist!";
			return false;
		}

		return true;
	}

	/**
	 * Bind the content of the newValues to the object. Overwrite to make it possible
	 * to use also objects here
	 */
	public function bind($src, $ignore = array()) {
	//public function bind( $newValues ) {
		if (is_array( $src )) {
			return parent::bind( $src, $ignore );
		} elseif (is_a($src, 'JLanguage')) {
			$this->shortcode= $src->tag;
		} else {
			foreach (get_object_vars($this) as $k => $v) {
				if ( isset($src->$k) ) {
					$this->$k = $src->$k;
				}
			}
		}
		return true;
	}
	
	/**
	 * Bind the content of the newValues to the object. Overwrite to make it possible
	 * to use also objects here
	 */
	public function bindFromJLanguage( $jLanguage ) {
		$retval = false;
		if (is_array( $jLanguage )) {
			$this->shortcode= strpos($jLanguage['tag'], '-') > 0 ? substr($jLanguage['tag'], 0, strpos($jLanguage['tag'], '-')) : $jLanguage['tag'];
			$this->fallback_code = '';
			$retval = true;
		}
		return $retval;
	}
	
	/**
	 * Special method to synchronize the language id between the core table and the extended table
	 * @param	int	new lang_id
	 * @return	boolean	success or failure
	 * @since	2.1
	 * @access	public 
	 */
	public function updateLanguageID($lang_id) {
		$lang_id = (int) $lang_id;
		$this->_db->setQuery('UPDATE #__jf_languages_ext set lang_id='.$lang_id.' WHERE lang_id='.$this->lang_id);
		return $this->_db->query();
	}
}

