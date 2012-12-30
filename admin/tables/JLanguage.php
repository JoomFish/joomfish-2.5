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
 * This class is a downgrade implemented to allow Joomla 1.5 using the 1.6 data structure with JoomFish.
 * It is partically re-used from the Joomla 1.6 JTableLanguage version
 * @todo test that this class is only used in a Joomla 1.5 environment 
 *
 * @package joomfish
 * @subpackage administrator
 * @copyright 2003 - 2013, Think Network GmbH, Konstanz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Revision: 1474 $
 */
class TableJLanguage extends JTable  {
	/**
	 * Primary key 
	 * @var int 
	 * @access public
	 */
	public $lang_id=-1;
	
	/**
	 * The ISO code of the language files. Can be entered manually
	 * @var string
	 * @access public
	 */
	public $lang_code=null;
	
	/** 
	 * The full title/name of the language
	 * @var varchar
	 * @since 2.1
	 * @access public
	 */
	public $title='';

	/** 
	 * A native version of the language title
	 * @var varchar
	 * @since 2.1
	 * @access public
	 */ 
	public $title_native='';
	

	/**
	 * The language description
	 *
	 * @var	varchar
	 * @since 2.1
	 * @access public
	 */
	public $description='';

	/**
	 * The site Meta Description
	 *
	 * @var	varchar
	 * @since 2.1
	 * @access public
	 */
	public $metadesc='';

	/**
	 * The site Meta keywords
	 *
	 * @var	varchar
	 * @since 2.1
	 * @access public
	 */
	public $metakey='';

	/**
	 * @var string	sef short code for the URL or language switching
	 * @since 2.1
	 * @access public
	 */
	public $sef=null;
	
	/** 
	 * @var string Image reference if there is any - this is the "official" image reference of the flag name (two letter iso code) as of defined by Joomla 1.6
	 * @since 2.1
	 * @access public
	 */
	public $image='';
	
	/** @var int Flag status of the language (active = published = 1)
	 * @since 2.1
	 * @access public
	 */
	public $published=false;
	
	/** Standard constructur
	*/
	public function __construct( &$db ) {
		parent::__construct( '#__languages', 'lang_id', $db );
	}
	
	/**
	 * Validate language information
	 * Name and Code name are mandatory
	 * activated will automatically set to false if not set
	 */
	public function check() {
		if (trim( $this->title ) == '') {
			$this->_error = "You must enter a name.";
			return false;
		}

		if (trim( $this->sef ) == '') {
			$this->_error = "You must enter a corresponding language code.";
			return false;
		}

		// check for existing language code
		$this->_db->setQuery( "SELECT id FROM #__languages "
		. "\nWHERE code='$this->sef' AND id!='$this->id'"
		);

		$xid = intval( $this->_db->loadResult() );
		if ($xid && $xid != intval( $this->id )) {
			$this->_error = "There is already a language with the code you provided, please try again.";
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
		if (is_array( $newValues )) {
			return parent::bind( $newValues );
		} elseif (is_a($newValues, 'JLanguage')) {
			$this->published = false;
			$this->title = $newValues->name;
			$this->lang_code = $newValues->tag;
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
	 * Bind the content of the newValues to the object. Overwrite to make it possible
	 * to use also objects here
	 */
	public function bindFromJLanguage( $jLanguage ) {
		$retval = false;
		if (is_array( $jLanguage )) {
			$this->active = false;
			$this->title = $jLanguage['name'];
			$this->lang_code = $jLanguage['tag'];
			$this->sef= strpos($jLanguage['tag'], '-') > 0 ? substr($jLanguage['tag'], 0, strpos($jLanguage['tag'], '-')) : $jLanguage['tag'];
			$retval = true;
		}
		return $retval;
	}
}

