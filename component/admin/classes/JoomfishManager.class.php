<?php
/**
 * Joom!Fish - Multi Lingual extention and translation manager for Joomla!
 * Copyright (C) 2003 - 2011, Think Network GmbH, Munich
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
 * $Id: JoomfishManager.class.php 238 2011-06-13 08:33:38Z alex $
 * @package joomfish
 * @subpackage classes
 *
*/

/*
TODO MS:

includePaths:
	search JOOMFISH_ADMINPATH if need add includePaths
*/



/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * The JoomFishManager controls all important configuration and information
 * of the content elements. These information might be cached in the session
 * settings if necessary in furture.
 *
 * @package joomfish
 * @subpackage administrator
 * @copyright 2003 - 2011, Think Network GmbH, Munich
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version 1.0, 2009-01-07 $Revision: 1496 $
 * @author Alex Kempkens
*/
class JoomFishManager {
	/** @var array of all known content elements and the reference to the XML file */
	private $_contentElements;

	/** @static string Content type which can use default values */
	public static $DEFAULT_CONTENTTYPE='content';

	/** @var config Configuration of the map */
	private $_config=null;

	/** @var Component config */
	private $componentConfig= null;

	/**	PrimaryKey Data */
	private $_primaryKeys = null;

	/** @var array for all system known languages */
	private $allLanguagesCache=array();

	/** @var array for all languages listed by shortcode */
	private $allLanguagesCacheByShortcode=array();

	/** @var array for all languages listed by ID */
	private $allLanguagesCacheByID=array();

	/** @var array for all active languages */
	private $activeLanguagesCache=array();

	/** @var array for all active languages listed by shortcode */
	private $activeLanguagesCacheByShortcode=array();

	/** @var array for all active languages listed by ID */
	private $activeLanguagesCacheByID=array();
	
	private $treatment = null;
	/**
	MS:
	 * An array to hold included paths
	 *
	 * @var    array
	 */
	public static $includePaths = array();


	public function getIncludePath($type = 'contentelements')
	{
		$return = isset(self::$includePaths[$type]) ? self::$includePaths[$type] : null;
		return $return;
	}

	/**
	 * Add a directory where JoomfishManager should search for contentelements or.... 
	 * //You may either pass a string or an array of directories.
	 *
	 * @param   string  A path to search.
	 *
	 * @return  array   An array with directory elements
	 */

	public static function addIncludePath($path = '', $type = 'contentelements')
	{
		//static $paths;

		if (!isset(self::$includePaths)) {
			self::$includePaths = array();
		}

		if (!isset(self::$includePaths[$type])) {
			self::$includePaths[$type] = array();
		}
		/*
		if (!isset($paths[''])) {
			$paths[''] = array();
		}
		*/
		if (!empty(self::$includePaths)) {
			jimport('joomla.filesystem.path');

			if (!in_array($path, self::$includePaths[$type])) {
				array_unshift(self::$includePaths[$type], JPath::clean($path));
			}
			/*
			if (!in_array($path, $paths[''])) {
				array_unshift($paths[''], JPath::clean($path));
			}
			*/
		}

		return self::$includePaths[$type];
	}


	
	/** Standard constructor */
	public function __construct() {
		
		require_once( JOOMFISH_ADMINPATH .DS. 'helpers' .DS. 'extensionHelper.php' );
		include_once(JoomfishExtensionHelper::getExtraPath('element').DS."ContentElement.php");
		//include_once(JOOMFISH_ADMINPATH .DS. "models".DS."ContentElement.php");

		// now redundant
		$this->_loadPrimaryKeyData();

		$this->activeLanguagesCache = array();
		$this->activeLanguagesCacheByShortcode = array();
		$this->activeLanguagesCacheByID = array();
		// get all languages and split out active below
		$langlist = $this->getLanguages(false);
		$this->_cacheLanguages($langlist);

		// Must get the config here since if I do so dynamically it could be within a translation and really mess things up.
		$this->componentConfig = JComponentHelper::getParams( 'com_joomfish' );
	}

	/**
	 * Method to create a single instance of the JoomFishManager
	 * @param string $adminPath	
	 */
	public static function & getInstance($adminPath=null){
		static $instance;
		if (!isset($instance)){
			$instance = new JoomFishManager($adminPath);
		}
		return $instance;
	}

	/**
	 * Cache languages in instance
	 * This method splits the system relevant languages in various caches for faster access
	 * @param array of languages to be stored
	 */
	private function _cacheLanguages($langlist) {
		$this->activeLanguagesCache = array();
		$this->activeLanguagesCacheByShortcode = array();
		$this->activeLanguagesCacheByID = array();

		if (count($langlist)>0){
			foreach ($langlist as $alang){
				if ($alang->published){
					$this->activeLanguagesCache[$alang->lang_code] = $alang;
					$this->activeLanguagesCacheByID[$alang->lang_id] = $alang;
					$this->activeLanguagesCacheByShortcode[$alang->sef] = $alang;
				}
				$this->allLanguagesCache[$alang->lang_code] = $alang;
				$this->allLanguagesCacheByID[$alang->lang_id] = $alang;
				$this->allLanguagesCacheByShortcode[$alang->sef] = $alang;
			}
		}
	}

	/**
	 * Load Primary key data from database
	 *
	 */
	private function _loadPrimaryKeyData() {
		if ($this->_primaryKeys==null){
			$db = JFactory::getDBO();
			$db->setQuery( "SELECT joomlatablename,tablepkID FROM `#__jf_tableinfo`");
			$rows = $db->loadObjectList('', 'stdClass', false);
			$this->_primaryKeys = array();
			if( $rows ) {
				foreach ($rows as $row) {
					$this->_primaryKeys[$row->joomlatablename]=$row->tablepkID;
				}
			}

		}
	}

	/**
	 * Get primary key given table name
	 *
	 * @param string $tablename
	 * @return string primarykey
	 */
	public function getPrimaryKey($tablename){
		if ($this->_primaryKeys==null) $this->_loadPrimaryKeyData();
		if (!is_string($tablename)){
			return false;
		}
		if (array_key_exists($tablename,$this->_primaryKeys)) return $this->_primaryKeys[$tablename];
		else return "id";
	}

	/**
	 * Loading of related XML files
	 *
	 * TODO This is very wasteful of processing time so investigate caching some how
	 * built in Joomla cache will not work because of the class structere of the results
	 * we get lots of incomplete classes from the unserialisation
	*/
	private function _loadContentElements() {
		// XML library

		// Try to find the XML file
		//MS: add includesPaths
		$filesindir = array();
		if (isset(self::$includePaths['contentelements']) && count(self::$includePaths['contentelements']))
		{
			foreach(self::$includePaths['contentelements'] as $includePath)
			{
				if(count($filesindir))
				{
					array_merge($filesindir, JFolder::files($includePath ,".xml"));
				}
				else
				{
					$filesindir = JFolder::files($includePath ,".xml");
				}
			}
		}
		else
		{
			//$filesindir = JFolder::files(JOOMFISH_ADMINPATH ."/contentelements" ,".xml");
			$filesindir = JFolder::files(JoomfishExtensionHelper::getExtraPath('xmls') ,".xml");
		}
		
		//TODO only contentelement use includePath
		//all other path in the contentElementXML
		/*
		if (isset(self::$includePaths['modelcontentelement']) && count(self::$includePaths['modelcontentelement']))
		{
			foreach(self::$includePaths['modelcontentelement'] as $includePath)
			{
				$modelfilesindir = JFolder::files($includePath ,".php",false,true);
			}
		}
		*/
		if(count($filesindir) > 0)
		{
			$this->_contentElements = array();
			foreach($filesindir as $file)
			{
				unset($xmlDoc);
				$xmlDoc = new DOMDocument();
				
				//if ($xmlDoc->load(JOOMFISH_ADMINPATH . "/contentelements/" . $file)) {
				if ($xmlDoc->load(JoomfishExtensionHelper::getExtraPath('xmls').DS. $file)) {
					$element = $xmlDoc->documentElement;
					if ($element->nodeName == 'joomfish') {
						if ( $element->getAttribute('type')=='contentelement' ) {
							$nameElements = $element->getElementsByTagName('name');
							$nameElement = $nameElements->item(0);
							$name = strtolower( trim($nameElement->textContent) );
							$contentElement = null;
							/*
							if(isset($modelfilesindir) && count($modelfilesindir) > 0)
							{
								foreach($modelfilesindir as $modelfile)
								{
									if(strtolower(JFile::stripExt(JFile::getName($modelfile))) == strtolower($name))
									{
										include_once($modelfile);
										$lassName = 'ContentElement'.$name;
										$contentElement = new $lassName( $xmlDoc );
									}
								}
							}
							*/
							//if(!$contentElement)
							//{
								$treatment = self::getTreatment($xmlDoc);
								if(count($treatment) > 0)
								{
									$includePath = JoomfishExtensionHelper::getTreatmentIncludePath($treatment);
									if(isset($treatment['contentElement']))
									{
										$className = 'ContentElement'.$treatment['contentElement'];
									}
									
									if(isset($treatment['contentElementPath']) && isset($className))
									{
										if($file = JPath::find(JPATH_ROOT.DS.$treatment['contentElementPath'], $className.'.php'))
										{
											include_once($file);
										}
									}
									elseif(isset($includePath) && isset($className))
									{
										if($file = JPath::find($includePath.DS.'element', $className.'.php'))
										{
											include_once($file);
										}
									}
									elseif(isset($className))
									{
										//if($file = JPath::find(JOOMFISH_ADMINPATH.DS.'models', $className.'.php'))
										if($file = JPath::find(JoomfishExtensionHelper::getExtraPath('element'), $className.'.php'))
										{
											include_once($file);
										}
									}
									if(isset($className) && class_exists($className))
									{
										$contentElement = new $className( $xmlDoc );
									}
								}
							//}
							
							if(!$contentElement)
							$contentElement = new ContentElement( $xmlDoc );
							
							$this->_contentElements[$contentElement->getTableName()] = $contentElement;
						}
					}
				}
			}
		}
	}

	/**
	 * Loading of specific XML files
	*/
	private function _loadContentElement($tablename) {

		
		if (!is_array($this->_contentElements)){
			$this->_contentElements = array();
		}
		if (array_key_exists($tablename,$this->_contentElements)){
			return;
		}
		$file = null;
		if (isset(self::$includePaths['contentelements']) && count(self::$includePaths['contentelements']))
		{
			foreach(self::$includePaths['contentelements'] as $includePath)
			{
				if ($file = JPath::find($includePath, strtolower($tablename).'.xml'))
				{
					break;
				}
			}
		}
		if(!$file)
		{
			//$file = JOOMFISH_ADMINPATH .'/contentelements/'.$tablename.".xml";
			$file = JoomfishExtensionHelper::getExtraPath('xmls').DS.$tablename.'.xml';
		}
		//TODO only contentelement use includePath
		//all other path in the contentElementXML
		/*
		if (isset(self::$includePaths['modelcontentelement']) && count(self::$includePaths['modelcontentelement']))
		{
			foreach(self::$includePaths['modelcontentelement'] as $includePath)
			{
				if ($modelfile = JPath::find($includePath, strtolower($tablename).'.php'))
				{
					break;
				}
			}
		}
		*/
		if (file_exists($file)){
			unset($xmlDoc);
			$xmlDoc = new DOMDocument();
			if ($xmlDoc->load( $file)) {
				$element = $xmlDoc->documentElement;
				if ($element->nodeName == 'joomfish') {
					if ( $element->getAttribute('type')=='contentelement' ) {
						$nameElements = $element->getElementsByTagName('name');
						$nameElement = $nameElements->item(0);
						$name = strtolower( trim($nameElement->textContent) );
						$contentElement = null;
						/*
						if(isset($modelfile))
						{
							include_once($modelfile);
							$lassName = 'ContentElement'.$tablename;
							$contentElement = new $lassName( $xmlDoc );
						}
						else
						{
						*/
							$treatment = self::getTreatment($xmlDoc);
							if(count($treatment) > 0)
							{
								$includePath = JoomfishExtensionHelper::getTreatmentIncludePath($treatment);
								if(isset($treatment['contentElement']))
								{
									$className = 'ContentElement'.$treatment['contentElement'];
								}
								/*
								if(isset($treatment['contentElementPath']) && isset($className))
								{
									if($file = JPath::find(JPATH_ROOT.DS.$treatment['contentElementPath'], $className.'.php'))
									{
										include_once($file);
									}
								}
								else
								*/
								if(isset($includePath) && isset($className))
								{
									if($file = JPath::find($includePath.DS.'element', $className.'.php'))
									{
										include_once($file);
									}
								}
								elseif(isset($className))
								{
									
									//if($file = JPath::find(JOOMFISH_ADMINPATH.DS.'models', $className.'.php'))
									if($file = JPath::find(JoomfishExtensionHelper::getExtraPath('element'), $className.'.php'))
									{
										include_once($file);
									}
								}
								
								if(isset($className) && class_exists($className))
								{
									$contentElement = new $className( $xmlDoc );
								}
							}
						//}
						if(!$contentElement)
						{
							$contentElement = new ContentElement( $xmlDoc );
						}
						$this->_contentElements[$contentElement->getTableName()] = $contentElement;
						return $contentElement;
					}
				}
			}
		}
		return null;
	}


	public function getTreatment($xmlDoc)
	{
		if(isset($this->treatment))
		{
			return $this->treatment;
		}
		
		$treatment = array();
		//DOMDOcument way
		$xpath = new DOMXPath($xmlDoc);
		$treatments = $xpath->query('//reference/treatment')->item(0);
		if($treatments && $treatments->hasChildNodes())
		{
			foreach ($treatments->childNodes as $node)
			{
				if($node->nodeType == XML_ELEMENT_NODE)
				{
					$add = array();
					if($node->hasAttributes())
					{
						$attributes = $node->attributes;
						if(!is_null($attributes))
						{
							foreach ($attributes as $index => $attribute)
							{
								$add[$attribute->name] = $attribute->value;
							}
						}
					}
					if(count($add) > 0)
					{
						$treatment[$node->nodeName] = array('value'=>$node->nodeValue,'attributes'=>$add);
					}
					else
					{
						$treatment[$node->nodeName] = $node->nodeValue;
					}
				}
			}
		}
		$this->treatment = $treatment;
		return $this->treatment;
	}


	/**
	 * Method to return the content element files
	 *
	 * @param boolean $reload	forces to reload the element files
	 * @return unknown
	 */
	public function getContentElements( $reload=false ) {
		if( !isset( $this->_contentElements ) || $reload ) {
			$this->_loadContentElements();
		}
		return $this->_contentElements;
	}

	/** gives you one content element
	 * @param	key 	of the element
	*/
	public function getContentElement( $key ) {
		$element = null;
		if( isset($this->_contentElements) &&  array_key_exists( strtolower($key), $this->_contentElements ) ) {
			$element = $this->_contentElements[ strtolower($key) ];
		}
		else {
			$element = $this->_loadContentElement($key);
		}
		return $element;
	}

	/**
	 * Returns the system default language based on the Joomla configuration
	 * @since	2.1
	 * @return	string	Language_code of the system default language;	
	 */
	public static function getDefaultLanguage() {
		static $defaultLanguage;
		
		if(!isset($defaultLanguage)) {
			$params = JComponentHelper::getParams('com_languages');
			$defaultLanguage = $params->get("site", 'en-GB');
		}
		return $defaultLanguage;
	}
	
	/**
	* @param string The name of the variable (from configuration.php)
	* @return mixed The value of the configuration variable or null if not found
	*/
	public function getCfg( $varname , $default=null) {
		// Must not get the config here since if I do so dynamically it could be within a translation and really mess things up.
 		return $this->componentConfig->getValue($varname,$default);
	}

	/**
	* @param string The name of the variable (from configuration.php)
	* @param mixed The value of the configuration variable
	*/
	public function setCfg( $varname, $newValue) {
		$config = JComponentHelper::getParams( 'com_joomfish' );
		$config->setValue($varname, $newValue);
	}

	/** Creates an array with all the active languages for the JoomFish
	 *
	 * @return	Array of languages
	 */
	public function getActiveLanguages($cacheReload=false) {
		if( isset($this) && $cacheReload) {
			$langList = $this->getLanguages();
			$this->_cacheLanguages($langList);
		}
		/* if signed in as Manager or above include inactive languages too */
		$user = JFactory::getUser();
		if ( isset($this) && $this->getCfg("frontEndPreview") && isset($user) && (strtolower($user->usertype)=="manager" || strtolower($user->usertype)=="administrator" || strtolower($user->usertype)=="super administrator")) {
			if (isset($this) && isset($this->allLanguagesCache)) return $this->allLanguagesCache;
		}
		else {
			if (isset($this) && isset($this->activeLanguagesCache)) return $this->activeLanguagesCache;
		}
		return JoomFishManager::getLanguages( true );
	}

	/** Creates an array with all languages for the JoomFish
	 *
	 * @param boolean	indicates if those languages must be active or not
	 * @return	Array of languages
	 */
	public function getLanguages( $active=true ) {
		$db = JFactory::getDBO();
		$langActive=null;

		//$sql = 'SELECT * FROM #__jf_languages';
		$sql = 'select `l`.`lang_id` AS `lang_id`,`l`.`lang_code` AS `lang_code`,`l`.`title` AS `title`,`l`.`title_native` AS `title_native`,`l`.`sef` AS `sef`,`l`.`description` AS `description`,`l`.`metakey` AS `metakey`,`l`.`metadesc` AS `metadesc`,`l`.`published` AS `published`,`l`.`image` AS `image`,`lext`.`image_ext` AS `image_ext`,`lext`.`fallback_code` AS `fallback_code`,`lext`.`params` AS `params`,`lext`.`ordering` AS `ordering` from (`#__languages` `l` left join `#__jf_languages_ext` `lext` on((`l`.`lang_id` = `lext`.`lang_id`)))';

		if( $active ) {
			$sql  .= ' WHERE published=1';
		}
		$sql .= ' order by `lext`.`ordering`';

		$db->setQuery(  $sql );
		$rows = $db->loadObjectList('lang_id', 'stdClass', false);
		// We will need this class defined to popuplate the table
		include_once(JOOMFISH_ADMINPATH .DS. 'tables'.DS.'JFLanguage.php');
		if( $rows ) {
			foreach ($rows as $row) {
				$lang = JTable::getInstance('JFLanguage', 'Table');
				$lang->bind($row);

				$langActive[] = $lang;
			}
		}

		return $langActive;
	}

	/**
	 * Fetches full langauge data for given shortcode from language cache
	 *
	 * @param array()
	 * @deprecated		replace by getLanguageBySEF
	 */
	public function getLanguageByShortcode($shortcode, $active=false){
		if ($active){
			if (isset($this) && isset($this->activeLanguagesCacheByShortcode) && array_key_exists($shortcode,$this->activeLanguagesCacheByShortcode))
			return $this->activeLanguagesCacheByShortcode[$shortcode];
		}
		else {
			if (isset($this) && isset($this->allLanguagesCacheByShortcode) && array_key_exists($shortcode,$this->allLanguagesCacheByShortcode))
			return $this->allLanguagesCacheByShortcode[$shortcode];
		}
		return false;
	}

	/**
	 * Fetches full langauge data for given shortcode from language cache
	 *
	 * @param array()
	 * @since	2.1
	 */
	public function getLanguageBySEF($sef, $active=false){
		if ($active){
			if (isset($this) && isset($this->activeLanguagesCacheByShortcode) && array_key_exists($sef,$this->activeLanguagesCacheByShortcode))
			return $this->activeLanguagesCacheByShortcode[$sef];
		}
		else {
			if (isset($this) && isset($this->allLanguagesCacheByShortcode) && array_key_exists($sef,$this->allLanguagesCacheByShortcode))
			return $this->allLanguagesCacheByShortcode[$sef];
		}
		return false;
	}

	/**
	 * Fetches full langauge data for given code from language cache
	 *
	 * @param array()
	 */
	public function getLanguageByCode($code, $active=false){
		if ($active){
			if (isset($this) && isset($this->activeLanguagesCache) && array_key_exists($code,$this->activeLanguagesCache))
			return $this->activeLanguagesCache[$code];
		}
		else {
			if (isset($this) && isset($this->allLanguagesCache) && array_key_exists($code,$this->allLanguagesCache))
			return $this->allLanguagesCache[$code];
		}
		return false;
	}
	
	/** Fetch full language object by language id
	 *  @param	int language id
	 *  @param	boolean	search only in active languages
	 *  @return JFLanguage object
	 */
	public function getLanguageByID($id, $active=false) {
		if ($active){
			if (isset($this) && isset($this->activeLanguagesCacheByID) && array_key_exists($id,$this->activeLanguagesCacheByID))
			return $this->activeLanguagesCacheByID[$id];
		}
		else {
			if (isset($this) && isset($this->allLanguagesCacheByID) && array_key_exists($id,$this->allLanguagesCacheByID))
			return $this->allLanguagesCacheByID[$id];
		}
		return false;
	}
	/**
	 * 
	 * @param boolean $active
	 */
	public function getLanguagesIndexedByCode($active=false){
		if ($active){
			if (isset($this) && isset($this->activeLanguagesCache))
			return $this->activeLanguagesCache;
		}
		else {
			if (isset($this) && isset($this->allLanguagesCache))
			return $this->allLanguagesCache;
		}
		return false;
	}

	/**
	 * 
	 * @param boolean $active
	 */
	public function getLanguagesIndexedById($active=false){
		if ($active){
			if (isset($this) && isset($this->activeLanguagesCacheByID))
			return $this->activeLanguagesCacheByID;
		}
		else {
			if (isset($this) && isset($this->allLanguagesCacheByID))
			return $this->allLanguagesCacheByID;
		}
		return false;
	}

	/** Retrieves the language ID from the given language name
	 *
	 * TODO Think about moving the SQL query to the model or JTable class
	 * @param	string	Code language name (normally $mosConfig_lang
	 * @return	int 	Database id of this language
	 */
	public function getLanguageID( $codeLangName="" ) {
		$db = JFactory::getDBO();
		$langID = -1;
		if ($codeLangName != "" ) {
			// Should check all languages not just active languages
			if (isset($this) && isset($this->allLanguagesCache) && array_key_exists($codeLangName,$this->allLanguagesCache)){
				return $this->allLanguagesCache[$codeLangName]->id;
			}
			else {
				$db->setQuery( "SELECT lang_id FROM #__language WHERE published=1 and lang_code = '$codeLangName' order by ordering" );
				$langID = $db->loadResult(false);
			}
		}
		return $langID;
	}

	/** Retrieves the language code (for URL) from the given language name
	 *
	 * @param	string	Code language name (normally $mosConfig_lang
	 * @return	int 	Database id of this language
	 */
	public function getLanguageCode( $codeLangName="" ) {
		$db = JFactory::getDBO();
		$langID = -1;
		if ($codeLangName != "" ) {
			if (isset($this) && isset($this->activeLanguagesCache) && array_key_exists($codeLangName,$this->activeLanguagesCache))
			return $this->activeLanguagesCache[$codeLangName]->shortcode;
			else {
				$db->setQuery( "SELECT sef FROM #__language WHERE published=1 and lang_code = '$codeLangName' order by ordering" );
				$langID = $db->loadResult(false);
			}
		}
		return $langID;
	}

	/**
	 * 
	 * @param string $lang
	 */
	public function & getCache($lang=""){
		$conf = JFactory::getConfig();
		if ($lang===""){
			$lang=$conf->getValue('config.language');
		}
		// I need to get language specific cache for language switching module
		if (!isset($this->_cache)) {
			$this->_cache = array();
		}
		if (isset($this->_cache[$lang])){
			return $this->_cache[$lang];
		}

		jimport('joomla.cache.cache');

		if (version_compare(phpversion(),"5.0.0",">=")){
			// Use new Joomfish DB Cache Storage Handler but only for php 5
			$storage = 'jfdb';
			// Make sure we have loaded the cache stroage handler
			JLoader::import('JCacheStorageJFDB', dirname( __FILE__ ));
		}
		else {
			$storage = 'file';
		}

		$options = array(
			'defaultgroup' 	=> "joomfish-".$lang,
			'cachebase' 	=> $conf->getValue('config.cache_path'),
			'lifetime' 		=> $this->getCfg("cachelife",1440) * 60,	// minutes to seconds
			'language' 		=> $conf->getValue('config.language'),
			'storage'		=> $storage
		);

		$this->_cache[$lang] = JCache::getInstance( "callback", $options );
		return $this->_cache[$lang];
	}
}