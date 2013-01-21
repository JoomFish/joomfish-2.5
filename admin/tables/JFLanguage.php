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

JLoader::register('TableJFLanguageExt', JOOMFISH_ADMINPATH .DS. 'tables' .DS. 'JFLanguageExt.php' );
JLoader::register('TableJLanguage', JOOMFISH_ADMINPATH .DS. 'tables' .DS. 'JLanguage.php' );

/**
 * This class is a special version of the language management for Joomla 1.5 & Joomfish.
 * It coveres the standard standard Joomla content language management and aggregates the
 * additional language management used within the JoomFish extension
 *
 * @package joomfish
 * @subpackage administrator
 * @copyright 2003 - 2013, Think Network GmbH, Konstanz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Revision: 1511 $
 */
class TableJFLanguage extends JTable  {
	/** @var int Primary key
	 * @access public
	 */
	public $lang_id=-1;
	
	/** @var string The full title/name of the language
	 * @since 2.1
	 * @access public
	 */
	public $title='';

	/** @var string The full name of the language
	 * @deprecated 2.1 replaced by title_native
	 * @access public
	 * */
	public $name='';
	
	/** @var string A native version of the language title
	 * @since 2.1
	 * @access public
	 */ 
	public $title_native='';
	
	/** @var int Flag if the language is activated for this site
	 * @deprecated 2.1 replaced by published
	 * @access public
	 */
	public $active=false;
	/** @var int Flag status of the language (active = published = 1)
	 * @since 2.1
	 * @access public
	 */
	public $published=false;
	
	/**
	 * @var string short code for URL or language switching
	 * @deprecated 2.1 replaced by sef
	 * @access public
	 */
	public $shortcode='';
	
	/**
	 * @var string	sef short code for the URL or language switching
	 * @since 2.1
	 * @access public
	 */
	public $sef='';
	
	/** 
	 * In Joomla! 1.5 this code is now a valid ISO code. This is why we removed the column ISO and replaced all calls to redirect to code instead
	 * Be aware that code (Joomla! iso code) inlucdes the country-language names!
	 * @var string The name Joomla is using for this language
	 * @deprecated 2.1 replaced by lang_code
	 * @access public
	*/
	public $code='';
	/**
	 * @var string The short iso code of the language files. Can be entered manually
	 * @access public
	 */
	public $lang_code='';
	
	/**
	 * The full ISO code as it is stored in the language files. This code is only available if a language file is installed
	 * @var string
	 * @access public
	 */
	public $iso=null;
	
	/**
	 * The Joomla 1.0 language code based on the information of the language file. The value is only set if a language ile is installed
	 * @var string
	 * @access public
	 */
	public $backwardlang=null;
	
	/** 
	 * @var string Image reference if there is any - this is the "official" image reference of the flag name (two letter iso code) as of defined by Joomla 1.6
	 * @since 2.1
	 * @access public
	 */
	public $image='';
	
	/**
	 * @var string Image reference as of JoomFish incl. a flexible Image path in the system
	 * @since 2.1
	 * @access public
	 */
	public $image_ext='';
	
	/** @var string optional code of language to fall back on if translation is missing
	 * @access public
	 */
	public $fallback_code='';
	
	/** @var string parameter set base on key=value pairs
	 * @access public
	 */
	public $params='';
	
	/** 
	 * @var string Order of the languages within the lists
	 * @access public
	 */
	public $ordering=0;
	
	/**
	 * @var JLanguage is the associated Joomla language file if existis
	 * @since 2.1
	 * @access private
	 */
	private $jLanguage = null;
	
	/**
	 * @var JTableLanguage	reference to the language table class for Joomla 1.6 data structure
	 * @since	2.1
	 * @access private
	 */
	private $jLanguageTable = null;
	
	/**
	 * @var JFLanguageExtended	extended language information from JoomFish
	 * @access private
	 */ 
	private $jfLanguageExt = null;
	
	/**
	 * Mapping attribute list for deprecated fields
	 * @var array
	 * @access private
	 * @since 2.1
	 */
	private static $deprecatedAttribs = array('lang_id' => 'id',  'lang_code' => 'code', 'title_native' => 'name', 'published' => 'active', 'sef' => 'shortcode');
		
	/** Standard constructur
	*/
	public function __construct( &$db ) {
		JTable::addIncludePath(JOOMFISH_ADMINPATH .DS. 'tables');
		
		// Try to initialize a reference to the Joomla 1.6 Language
		jimport('joomla.filesystem.path');
		if(JPath::find(JTable::addIncludePath(), 'language.php')) {
			$this->jLanguageTable = JTable::getInstance('Language', 'JTable');
		} else {
			$this->jLanguageTable = JTable::getInstance('JLanguage', 'Table');
		}
		
		$this->jfLanguageExt = JTable::getInstance('JFLanguageExt', 'Table');

		// This must come last since the other tables and variables are used bu the _set method
		parent::__construct( '#__languages', 'lang_id', $db );

	}

	/** 
	 * Method to set object attributes
	 * This method automatically updates the deprecated attributes corresponding to the definition
	 * @access	public
	 * @since	2.1
	 * @param	string $property
	 * @param	any $value
	 * @return	object	previous value
	 */
	public function __set($property, $value = null) {
		$previous = parent::set($property, $value);
		
		// updating deprecated attributes based on property name
		if(array_key_exists($property, self::$deprecatedAttribs)) {
			$oldAttrib = self::$deprecatedAttribs[$property];
			$this->$oldAttrib = $value;
		}

		// updating aggregated attributes
		if(array_key_exists($property, get_object_vars($this->jLanguageTable))) {
			$this->jLanguageTable->set($property, $value);
		} 
		if(array_key_exists($property, get_object_vars($this->jfLanguageExt))) {
			$this->jfLanguageExt->set($property, $value);
		}
		
		return $previous;
	}
	
	/**
	 * Wrapping PHP4 & Joomla method calls
	 * @access	public
	 * @since	2.1
	 * @param	string $property
	 * @param	any $value
	 * @return	object	previous value
	 */
	public function set($property, $value = null) {
		return $this->__set($property, $value);
	}

	/**
	 * Method to identify if there is an existing front-end language file for Joomla
	 * @return boolean	yes if the file exists
	 * @since 2.1
	 * @access public
	 */
	public function hasFrontendTranslation() {
		if($this->jLanguage != null) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Loads the class instance and updates all related fields as well as the associated extended table
	 * @param	mixed	Optional primary key.  If not specifed, the value of current key is used
	 * @return	boolean	True if successful
	 * @access public
	 */
	public function load($keys = null, $reset = true) {
	//public function load($oid=null) {
		// loading all of the refered objects
		$this->jLanguageTable->load($keys, $reset);
		$this->jfLanguageExt->load($keys, $reset);
		
		// now copy attributes of underlying objects to this instance for more easy access
		foreach (get_object_vars($this->jLanguageTable) as $key => $value) {
			$this->set($key, $value);
		}
		
		foreach (get_object_vars($this->jfLanguageExt) as $key => $value) {
			if ($key == 'params') {
				$value = new JRegistry($value);
			}
			$this->set($key, $value);
			
		}
		
		$this->checkFrontendLanguage();
	}
	
	
	/**
	 *	Loads the language by it's code name
	 *	@todo	verify if the method makes sense this way!
	 *	@param string $code iso name of the language
	 *	@return any result from the database operation
	 */
	public function loadByJoomla( $code=null ) {
		if ($code === null) {
			return false;
		}
		$jfm = JoomFishManager::getInstance();
		$langdata = $jfm->getLanguageByCode($code,$active);
		return $langdata;
	}

	/**
	 *	Creates a new language by it's iso name
	 *	@param string $iso iso name of the language
	 *	@return object new language instance or null
	 */
	public static function createByJoomla( $code, $active=true ) {
		$db = JFactory::getDBO();

		$lang = new TableJFLanguage($db);
		$jfm = JoomFishManager::getInstance();
		$langdata = $jfm->getLanguageByCode($code,$active);

		if( !$lang->bind($langdata) ) {
			$lang = null;
		}
		return $lang;
	}

	/**
	 *	Loads the language by it's iso name
	 *	@param string $iso iso name of the language
	 * 	@deprecated 2.1
	 *	@return any result from the database operation
	 */
	public function loadByISO( $iso=null ) {
		if ($iso === null) {
			return false;
		}
		$jfm = JoomFishManager::getInstance();
		$langdata = $jfm->getLanguageByCode($code,$active);
	}

	/**
	 * Creats the language by it's short code
	 * @param string	$shortcode name of the language
	 * @return object	language class or null
	 */
	public static function createByShortcode( $shortcode, $active=true ) {
		$db = JFactory::getDBO();
		if ($shortcode === null || $shortcode=='') {
			return null;
		}
		$lang = new TableJFLanguage($db);
		$jfm = JoomFishManager::getInstance();
		$langdata = $jfm->getLanguageByShortcode($shortcode,$active);
		// if we allow Joomfish to attempt to translate this object then the language is loaded 
		// too early by JFactory::getLanguage();  This then breaks everything!!!
		if( !$lang->bind($langdata) ) {
			$lang = null;
		}
		return $lang;
	}

	/**
	 *	Loads the language by it's iso name
	 *	@param string $iso iso name of the language
	 *	@return any result from the database operation
	 */
	public function createByISO( $iso, $active=true ) {
		$db = JFactory::getDBO();

		if ($iso === null) {
			return false;
		}
		$lang = new TableJFLanguage($db);
		$jfm = JoomFishManager::getInstance();
		$langdata = $jfm->getLanguageByCode($iso,$active);

		if( !$lang->bind($langdata) ) {
			$lang = null;
		}
		return $lang;
	}


	/**
	 * Return the language code for the urls (shortcode)
	 * @return string	short code of the language
	 */
	public function getLanguageCode() {
		return ($this->jLanguageTable->sef!='') ? $this->jLanguageTable->sef : $this->jLanguageTable->lang_code;
	}

	/**
	 * Validate language information
	 * Name and Code name are mandatory
	 * activated will automatically set to false if not set
	 */
	public function check() {
		$retValue = $this->jLanguageTable->check();
		return $retValue & $this->jfLanguageExt->check();
	}

	/**
	 * Bind the content of the newValues to the object. Overwrite to make it possible
	 * to use also objects here
	 */
	public function bind($src, $ignore = array()) {
//	public function bind( $newValues ) {
		if (is_array( $src )) {
			return parent::bind( $src, $ignore );
		} elseif (is_a($src, 'JLanguage')) {
			$this->set('published', false);
			$this->set('title', $src->name);
			$this->set('lang_code', $src->tag);
		} else {
			foreach (get_object_vars($this) as $k => $v) {
				if ( isset($src->$k) ) {
					$this->set($k, $src->$k);
				}
			}
		}
		
		// allow bind of aggregated objects
		$this->jLanguageTable->bind($src);
		// If the core language object includes special meta information we ensure those are stored in our parameter objcet
		$langParameter = new JRegistry($this->params);
		$langParameter->set('MetaDesc', $this->jLanguageTable->get('metadesc'));
		$langParameter->set('MetaKeys',$this->jLanguageTable->get('metakey'));
		$langParameter->set('sitename', $this->jLanguageTable->get('sitename', ''));
		//$this->params = $langParameter->toString();
		
		$src->params = $this->params;
		$this->jfLanguageExt->bind($src, $ignore);

		// check for existing frontend language
		$this->checkFrontendLanguage();
		
		return true;
	}
	
	/**
	 * Bind the content of the newValues to the object. Overwrite to make it possible
	 * to use also objects here
	 */
	public function bindFromJLanguage( $jLanguage ) {
		$retval = false;
		if (is_array( $jLanguage )) {
			$this->set('published', false);
			$this->set('title_native', $jLanguage['name']);
			$this->set('lang_code', $jLanguage['tag']);
			$this->set('sef', strpos($jLanguage['tag'], '-') > 0 ? substr($jLanguage['tag'], 0, strpos($jLanguage['tag'], '-')) : $jLanguage['tag']);
			$this->set('fallback_code', '');
			$retval = true;
		}
		return $retval;
	}

	/**
	 * @param unknown_type $source
	 * @param unknown_type $order_filter
	 * @param unknown_type $ignore
	 * @return string|string|string|string|string
	 */
	public function save($source, $order_filter = '', $ignore = '') {
		
	}

	/**
	 * Method to store both the basic object as well as the aggregated one
	 * @access public
	 * @param boolean If false, null object variables are not updated
	 * @return null|string null if successful otherwise returns and error message
	 */
	public function store($updateNulls = false) {
		// special treatment if a new record is to be created
		if($this->lang_id == -1) {
			$this->jLanguageTable->set('lang_id', null);
			$this->jfLanguageExt->set('lang_id', null);
		} else {
			// ensure the extended table exists
			$langExt = JTable::getInstance('JFLanguageExt', 'Table');
			$langExt->load($this->jLanguageTable->get('lang_id'));
			if($langExt->get('lang_id') == -1) {
				// extended row does not exist, create one
				$this->jfLanguageExt->set('lang_id', null);
			}
		}

		$retValue = $this->jLanguageTable->store($updateNulls);
		$retValue = $retValue & $this->jfLanguageExt->store($updateNulls);
		
		// again special treatment to ensure the lang_id match each other!
		if($this->lang_id == -1 || $this->jLanguageTable->lang_id != $this->jfLanguageExt->lang_id) {
			$retValue = $retValue & $this->jfLanguageExt->updateLanguageID($this->jLanguageTable->lang_id);
		}
		$this->lang_id = $this->jLanguageTable->lang_id;
		return $retValue;
	}
	
	/**
	 * Removes the current language and ALL translations maped to the language
	 * This only succeeds if all contents could be removed!
	 * @param int	$id
	 * @return boolean	based on success or not
	 */
	public function delete($pk = null) {
	//public function delete($id) {
		$res = $this->jLanguageTable->delete($pk);
		$res = $res & $this->jfLanguageExt->delete($pk);
		return $res;
	}
	
	/**
	 * Method to verify the existance of a frontend file for the language
	 * @since	2.1
	 * @access private
	 * @return	void
	 */
	private function checkFrontendLanguage() {
		// search for the corresponding Joomla language 
		// Read the languages dir to find new installed languages
		// This method returns a list of JLanguage objects with the related inforamtion
		$systemLanguages = JLanguage::getKnownLanguages(JPATH_SITE);
		if(array_key_exists($this->lang_code, $systemLanguages)) {
			$this->jLanguage = JLanguage::getInstance($this->lang_code);
			if($this->jLanguage != null) {
				$this->iso = $this->jLanguage->get('locale');
				$this->backwardlang = $this->jLanguage->get('backward');
			}
		} else {
			$this->jLanguage = null;
		}
	}
}

