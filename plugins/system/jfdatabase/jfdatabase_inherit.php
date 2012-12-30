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
 * @subpackage jfdatabase
 * @version 2.0
 *
*/

// Don't allow direct linking
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

/**
 * Multi lingual Database connector class
 *
 * This extention of the standard database class converts the output of the query automatically
 * with the actual selected language in the web site.
 *
 * @package joomfish
 * @subpackage database
 * @copyright 2003 - 2013, Think Network GmbH, Konstanz
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @version 1.0, 2009-01-07 $Revision: 1474 $
 * @author Geraint Edwards
*/

include_once(dirname(__FILE__)."/intercept.".strtolower(get_class(JFactory::getDBO())).".php");
class JFDatabase extends interceptDB {

	/** @var array list of multi lingual tables */
	public $mlTableList=null;

	/** @var Internal variable to hold flag about whether setRefTables is needed - JF queries don't need it */
	public $skipSetRefTables = false;

	public $orig_limit	= 0;
	public $orig_offset	= 0;

	public $skipjf = 0;
	public $translate = true;
	
	private $tableFields = null;


	/** Constructor
	*/
	public function JFDatabase( $options) {
		//if (JDEBUG) { $_PROFILER = JProfiler::getInstance('Application');$_PROFILER->mark('start jfdatabase');	}

		parent::__construct($options );

		$pfunc = $this->profile();

		$query = "select distinct reference_table from #__jf_content UNION select distinct reference_table from #__jf_translationmap";
		$this->setQuery( $query );
		$this->skipSetRefTables = true;
		$this->mlTableList = $this->loadResultArray(0,false);
		$this->skipSetRefTables = false;
		if( !$this->mlTableList ){
			if ($this->getErrorNum()>0){
				JError::raiseWarning( 200, JTEXT::_('No valid table list:') .$this->getErrorMsg());
			}
		}
		
		$pfunc = $this->profile($pfunc);
	}

	public $profileData = array();

	public function profile($func = "", $forcestart=false){
		if ($this->skipjf) return "";
		//if (!$this->debug) return "";
		// start of function
		if ($func==="" || $forcestart){
			if (!$forcestart){
				$backtrace = debug_backtrace();
				if (count($backtrace)>1){
					if (array_key_exists("class",$backtrace[1])){
						$func = $backtrace[1]["class"]."::".$backtrace[1]["function"];
					}
					else {
						$func = $backtrace[1]["function"];
					}
				}
			}
			if (!array_key_exists($func,$this->profileData)){
				$this->profileData[$func]=array("total"=>0, "count"=>0);
			}
			if (!array_key_exists("start",$this->profileData[$func])) {
				$this->profileData[$func]["start"]=array();
			}
			list ($usec,$sec) = explode(" ", microtime());
			$this->profileData[$func]["start"][] = floatval($usec)+floatval($sec);
			$this->profileData[$func]["count"]++;
			return $func;
		}
		else {
			if (!array_key_exists($func,$this->profileData)){
				exit("JFProfile start not found for function $func");
			}
			list ($usec,$sec) = explode(" ", microtime());
			$laststart = array_pop($this->profileData[$func]["start"]);
			$this->profileData[$func]["total"] += (floatval($usec)+floatval($sec)) - $laststart;
		}
	}

	/**
	 * Public function to test if table has translated content available
	 *
	 * @param string $table : tablename to test
	 */
	public function translatedContentAvailable($table){
		// mltable is a union of joomfish and native translations!
		return in_array( $table, $this->mlTableList) ;
	}

	/**
	 *Public function to test if table and field names are translatable - not point trying to translate ids etc.
	 * @param type $table
	 * @param type $fieldnames 
	 */
	public function testTranslateableFields($tableName,$fieldnames)
	{	
		
		foreach ($fieldnames as $fieldname) {
			$translatablefields = $this->getTranslateableFields($tableName);
			if ($translatablefields == array()) {
				return false;
			}
			
			if (in_array($fieldname, $translatablefields)) {
				return true;
			}
		}
		
		return false;
	}
	
	
	/**
	 *Public function to get translatable field names for a table.
	 * @param type $table
	 * @return an array of field names
	 */
	public function getTranslateableFields($tableName)
	{
		static $_tranfields =  array();

		if (!isset($_tranfields[$tableName])) {
				
			$_tranfields[$tableName] = array();

			$jfManager = JoomFishManager::getInstance();
			if (!$jfManager->getContentElement( $tableName )) return false;

			$elementTable = $jfManager->getContentElement( $tableName )->getTable();

			foreach ($elementTable->Fields as $field){
				if ($field->Translate){
					$_tranfields[$tableName][] = $field->Name;
				}
			}
		}

		return $_tranfields[$tableName];
	}
	
	/**
	 * Description
	 *
	 * @access public
	 * @return int The number of rows returned from the most recent query.
	 */
	public function getNumRows( $cur=null, $translate=true, $language=null )
	{
		if ($this->skipjf) return parent::getNumRows($cur);
		$count = parent::getNumRows($cur);
		
		if (!$translate) {
			//$this->translate = false;
			return $count;
		}				

		// setup Joomfish plugins
		$dispatcher	   = JDispatcher::getInstance();
		JPluginHelper::importPlugin('joomfish');

		// must allow fall back for contnent table localisation to work
		$allowfallback 		= true;
		$onlytransFields 	= true;
		$keycol 			= "";
		$idkey				= "";
		$reference_table 	= "";
		$tablealias 		= "";
		$ids				= "";
		$fielddata			= "";
		$jfm = JoomFishManager::getInstance();
		$this->setLanguage($language);
		$registry = JFactory::getConfig();
		$defaultLang = $registry->getValue("config.defaultlang");
		
		$rows = array($count);
		
		// @todo check whether this triggers are still necessary
		if ($defaultLang == $language){
			$dispatcher->trigger('onBeforeTranslation', array(&$rows, &$ids, $reference_table, $tablealias, $language, $keycol, $idkey, &$fielddata, $this->sql, $allowfallback, $onlytransFields));
		} else {
			$dispatcher->trigger('onBeforeTranslation', array(&$rows, &$ids, $reference_table, $tablealias, $language, $keycol, $idkey, &$fielddata, $this->sql, $allowfallback, $onlytransFields));
			$dispatcher->trigger('onAfterTranslation', array(&$rows, &$ids, $reference_table, $tablealias, $language, $keycol, $idkey, &$fielddata, $this->sql, $allowfallback, $onlytransFields));
		}
		
		$count = $rows[0];
		return $count;
	}

	/**
	* Overwritten method to loads the first field of the first row returned by the query.
	*
	* @return The value returned in the query or null if the query failed.
	*/
	public function loadResult( $translate=true, $language=null ) {
		if ($this->skipjf) return parent::loadResult();
		$this->translate = $translate;
		
		if (!$translate){
			$this->skipSetRefTables=true;
			$result = parent::loadResult();
			$this->skipSetRefTables=false;					
			return $result;
		}
		
		$result=null;
		$ret=null;

		$result = $this->_loadObject( $translate, $language );

		$pfunc = $this->profile();

		if( $result != null ) {
			$fields = get_object_vars( $result );
			$field = each($fields);
			$ret = $field[1];
		}

		$pfunc = $this->profile($pfunc);

		return $ret;
	}

	/**
	 * Overwritten Load an array of single field results into an array
	 *
	 * @access	public
	 */
	public function loadResultArray($numinarray = 0,  $translate=true, $language=null){
		
		if ($this->skipjf)  return parent::loadResultArray($numinarray);
		$this->translate = $translate;
		if (!$translate){
			$reslt 				= parent::loadResultArray($numinarray);
			return $reslt;
		}
		$results=array();
		$ret=array();
		//$results = $this->loadObjectList( '','stdClass',  $translate, $language );
		//$pfunc = $this->profile();

		$results = $this->loadObjectList( '', 'stdClass',$translate, $language , false);
		$pfunc = $this->profile();

		if( $results != null && count($results)>0) {
			foreach ($results as $row) {
				if (is_object($row)){
					// untranslated results always objects
					$fields = get_object_vars( $row );
				}
				else {
					$fields =  $row ;
				}
				$keycount = 0;
				foreach ($fields as $k=>$v) {
					if ($keycount==$numinarray){
						$key = $k;
						break;
					}
				}
				$ret[] = $fields[$key];
			}
		}

		$pfunc = $this->profile($pfunc);

		return $ret;
	}

	/**
	* Overwritten Fetch a result row as an associative array
	*
	* @access	public
	* @return array
	*/
	public function loadAssoc( $translate=true, $language=null) {
		if ($this->skipjf) return parent::loadAssoc();
		$this->translate = $translate;
		
		if (!$translate){
			$rslt = parent::loadAssoc();
			return $rslt;
		}
		
		$result=null;
		$result = $this->_loadObject( $translate, $language );

		$pfunc = $this->profile();

		if( $result != null ) {
			$fields = get_object_vars( $result );
			$pfunc = $this->profile($pfunc);
			return $fields;
		}
		return $result;
	}

	/**
	* Overwritten Load a assoc list of database rows
	*
	* @access	public
	* @param string The field name of a primary key
	* @return array If <var>key</var> is empty as sequential list of returned records.
	*/
	public function loadAssocList( $key='', $column = null, $translate=true, $language=null )
	{
		if ($this->skipjf) return parent::loadAssocList($key);
		$this->translate = $translate;
		
		if (!$translate){	
			$reslt 				= parent::loadAssocList($key);
			return $reslt;
		}
		
		$result=null;
		$rows = $this->loadObjectList($key, 'stdClass', $translate, $language );

		$pfunc = $this->profile();
		$results = array();
			if( $rows != null ) {
			
			foreach ($rows as $row) {
				$value = ($column) ? (isset($row->$column) ? $row->$column : get_object_vars( $row )) : get_object_vars( $row );
				if ($key!=""){
					$results[$row->$key] = $value;
				}
				else {
					$results[] = $value;
				}
			}
			$pfunc = $this->profile($pfunc);
		}
		unset($rows);
		return $results;
	}

	/**
	* This global function loads the first row of a query into an object
	*/
	public function loadObject($class="stdClass", $translate=true, $language=null ) {
		if ($this->skipjf) return parent::loadObject($class);
		
		$objects = $this->loadObjectList("",$class,$translate,$language);
		if (!is_null($objects) && count($objects)>0){
			return $objects[0];
		}
		return null;
	}

	/**
	 * private function to handle the requirement to call different loadObject version based on class
	 *
	 * @param boolran $translate
	 * @param string $language
	 */
	private function _loadObject( $translate=true, $language=null ) {
		return $this->loadObject('stdClass', $translate, $language);
	}

	/**
	 * Overwritten
	 *
	 * @access	public
	 * @return The first row of the query.
	 */
	public function loadRow( $translate=true, $language=null)
	{
		if ($this->skipjf) return parent::loadRow();
		$this->translate = $translate;
		
		if (!$translate){
			$reslt = parent::loadRow();
			return $reslt;
		}
		
		$result=null;
		$result = $this->loadObject( "stdClass", $translate, $language );

		$pfunc = $this->profile();

		$row = array();
		if( $result != null ) {
			$fields = get_object_vars( $result );
			foreach ($fields as $val) {
				$row[] = $val;
			}
			return $row;
		}
		return $row;
	}

	/**
	* Overwritten Load a list of database rows (numeric column indexing)
	*
	* @access public
	* @param string The field name of a primary key
	* @return array If <var>key</var> is empty as sequential list of returned records.
	* If <var>key</var> is not empty then the returned array is indexed by the value
	* the database key.  Returns <var>null</var> if the query fails.
	*/
	public function loadRowList( $key=null , $translate=true, $language=null)
	{
		if ($this->skipjf) return parent::loadRowList($key);	
		$this->translate = $translate;
		
		if (!$translate){
			$reslt 				= parent::loadRowList($key);
			return $reslt;
		}
		
		$results=array();
		
		if (is_null($key)) $key="";
		$rows = $this->loadObjectList($key, 'stdClass', $translate, $language );

		$pfunc = $this->profile();

		$row = array();
		if( $rows != null ) {
			foreach ($rows as $row) {
				$fields = get_object_vars( $row );
				$result = array();
				foreach ($fields as $val) {
					$result[] = $val;
				}
				if ($key!="") {
					$results[$row->$key] = $result;
				}
				else {
					$results[] = $result;
				}
			}
		}
		$pfunc = $this->profile($pfunc);
		return $results;
	}

	/**
	* Overwritten insert function to enable storage of material created in non-default language.
	* Note that this creates a translation which is identical to the original - when we update
	* the original in the default language we then keep the translation (although it will appread out of date!).
	*
	* @param	string	table name
	* @param	object	instance with information to store
	* @param	string	primary key name of table
	* @param	boolean	debug info printed or not
	* @param	boolean	passthru without storing information in a translation table
	*/
	function insertObject( $table, &$object, $keyName = NULL, $verbose=false , $passthru=false) {
		
		if ($this->skipjf) return parent::insertObject( $table, $object, $keyName, $verbose);
		
		$jfManager = JoomFishManager::getInstance();
		if( isset($jfManager)) {
			$this->setLanguage($language);
		}
		$conf	= JFactory::getConfig();
		$default_lang	= $conf->getValue('config.defaultlang');

		// if the currect language is the site default language the translations will not be updated!
		$passthru = $language == $default_lang;

		if( !$passthru && isset($jfManager)) {
			//Must insert parent first to get reference id !
			$parentInsertReturn = parent::insertObject( $table, $object, $keyName, $verbose);
			
			$pfunc = $this->profile();
			
			$translationObject=null;
			if( isset($table) && $table!="" ) {
				$tableName = preg_replace( '/^#__/', '', $table);
				if ($table != "#__jf_content" ){
					$contentElement = $jfManager->getContentElement( $tableName );
					if( isset( $contentElement ) ) {
						if ($contentElement->getTarget() == "native"){
							// TODO need to know if this is an update calling an insert or a raw insert!
							// if a raw insert then we may nee to do something extra here ??
						}
						else {
							$translationClass = $contentElement->getTranslationObjectClass();
							$translationObject = new $translationClass( $jfManager->getLanguageID($language), $contentElement );
							if( isset( $object->$keyName ) ) {
								$translationObject->loadFromContentID( $object->$keyName );
								$translationObject->updateMLContent( $object );
								if( isset( $object->state ) ) {
									$translationObject->published = ($object->state == 1) ? true : false;
								} else if ( isset( $object->published ) ) {
									$translationObject->published = ($object->published == 1) ? true : false;
								}
								if ($translationObject->published){
									if ( $jfManager->getCfg("frontEndPublish")){
										$user = JFactory::getUser();
										$access = new stdClass();
										$access->canPublish =  $user->authorize('com_content', 'publish', 'content', 'all');
										if ($access->canPublish) $translationObject->setPublished($translationObject->published);
									}
								}
								$translationObject->store();

								if ($jfManager->getCfg("transcaching",1)){
									// clean the cache!
									$cache = $jfManager->getCache($language);
									$cache->clean();
								}
							}
						}
					}
				}
				//}
			}

			$pfunc = $this->profile($pfunc);

			return $parentInsertReturn;
		}
		else {
			return parent::insertObject( $table, $object, $keyName, $verbose);
		}
	}

	/**
	* Overwritten update function to enable storage of translated information.
	* Based on the configuration in the content elements the automatic storage of
	* information is activated or not. It is important that this upgraded method will ensure
	* that all information will be written into the translation table. Only in the case that the
	* default language is choosen the information will be updated directly within the original tables.
	* To make sure that all other information will be written into the tables as expected the
	* statements will be manipulated as needed.
	*
	* @param	string	table name
	* @param	object	instance with information to store
	* @param	string	primary key name of table
	* @param	boolean	update fields with null or not
	* @param	boolean	passthru without storing information in a translation table
	*/
	function updateObject( $table, &$object, $keyName, $updateNulls=true, $passthru=false ) {
		if ($this->skipjf) return parent::updateObject( $table, $object, $keyName, $updateNulls );
		$pfunc = $this->profile();

		$jfManager = JoomFishManager::getInstance();

		if( isset($jfManager)) {
			$this->setLanguage($language);
		}
		$conf	= JFactory::getConfig();
		$default_lang	= $conf->getValue('config.defaultlang');

		// if the currect language is the site default language the translations will not be updated!
		$passthru = $language == $default_lang;

		if( !$passthru && isset($jfManager)) {
						
			$translationObject=null;
			if( isset($table) && $table!="") {
				$tableName = preg_replace( '/^#__/', '', $table);
				if ($table != "#__jf_content" ){
					$contentElement = $jfManager->getContentElement( $tableName );
					if( isset( $contentElement ) ) {
						$translationClass = $contentElement->getTranslationObjectClass();
						$translationObject = new $translationClass( $jfManager->getLanguageID($language), $contentElement );
						if( isset( $object->$keyName ) ) {
							// load the native language version
							$translationObject->loadFromContentID( $object->$keyName );							
							// update the translation object with the transalation and the status of any changes etc.
							$translationObject->updateMLContent( $object , $language);
											
							// TODO move this to translation object class
							if( isset( $object->state ) ) {
								$translationObject->published = ($object->state == 1) ? true : false;
							} else if ( isset( $object->published ) ) {
								$translationObject->published = ($object->published == 1) ? true : false;
							}
							if ( $jfManager->getCfg("frontEndPublish")){
								$user = JFactory::getUser();
								$access = new stdClass();
								$access->canPublish =  $user->authorize('com_content', 'publish', 'content', 'all');
								if ($access->canPublish) $translationObject->setPublished($translationObject->published);
							}

							$success = $translationObject->store();

							if ($jfManager->getCfg("transcaching",1)){
								// clean the cache!
								$cache = $jfManager->getCache($language);
								$cache->clean();
							}
						}
					}
				}
			}

			$pfunc = $this->profile($pfunc);

			return parent::updateObject( $table, $object, $keyName, $updateNulls );

		} else {
			return parent::updateObject( $table, $object, $keyName, $updateNulls );
		}
	}

	/**
	 *  Internal function to determit the table name from an sql query
	 *
	 *  This is now deprecated
	 *
	 * @return	string	table name
	 */
	function getTableName() {

		$pfunc = $this->profile();

		$conf	= JFactory::getConfig();
		$dbprefix 	= $conf->getValue('config.dbprefix');

		$posFrom = strpos( strtoupper($this->sql), 'FROM ') + 5; // after 'FROM '
		$posWhere = strpos( strtoupper($this->sql), 'WHERE ');
		$table = substr( $this->sql, $posFrom, $posWhere - $posFrom);
		if( strpos( $table, ' ' ) !== false ) {
			$table = substr( $table, 0, strpos( $table, ' ' ) );
		}
		if (isset($dbprefix) && strlen($dbprefix)>0) $table = preg_replace( '/'.$dbprefix.'/', '', $table);
		$table = preg_replace( "/\r/", "", $table) ;
		$table = preg_replace( "/\n/", "", $table) ;

		$pfunc = $this->profile($pfunc);

		return $table;
	}

	/**
	 * Override query in order to extract ref tables data
	 *
	 * @return n/a
	 */
	function query() {
		if ($this->skipjf) return parent::query();
		$res = parent::query();
		return $this->cursor;
	}


	/**
	 * Private function to get the language for a specific translation
	 *
	 */
	function setLanguage( & $language){

		$pfunc = $this->profile();

		// first priority to passed in language
		if (!is_null($language) && $language !=""){
			return;
		}
		// second priority to language for build route function in other language
		// ie so that module can translate the SEF URL
		$registry = JFactory::getConfig();
		$sefLang = $registry->getValue("joomfish.sef_lang", false);
		if ($sefLang){
			//$jfLang = TableJFLanguage::createByShortcode($sefLang, false);
			$language = $sefLang;
		}
		else {
			$language = JFactory::getLanguage()->getTag();
		}

		$pfunc = $this->profile($pfunc);


	}

	/**
	 * Returns a reference to the global Database object, only creating it
	 * if it doesn't already exist. And keeps sure that there is only one
	 * instace for a specific combination of the JDatabase signature
	 *
	 * @param array  An array of otions
	 * @return database A database object
	 * @since 2.5
	*/
	
	public static function getInstance($options = array())
	{		
		// Sanitize the database connector options.
		$options['driver'] = (isset($options['driver'])) ? preg_replace('/[^A-Z0-9_\.-]/i', '', $options['driver']) : 'mysql';
		$options['database'] = (isset($options['database'])) ? $options['database'] : null;
		$options['select'] = (isset($options['select'])) ? $options['select'] : true;

		// Get the options signature for the database connector.
		$signature = md5(serialize($options));
		
		if (empty(parent::$instances[$signature])) {
			parent::$instances[$signature] = new JFDatabase($options);
		}
		
		$database = parent::$instances[$signature];
		
		return $database;
	}


	function getListCount( $query )
	{
		if ($this->skipjf) return parent::_getListCount( $query );
		if ($this->translate === false) return parent::_getListCount( $query );
		$this->skipSetRefTables=true;
		$this->db->setQuery( $query );
		$this->db->query();
		$this->skipSetRefTables=false;

		return $this->db->getNumRows();
	}

	/**
	 * The following methods are not yet implemented in Joomfish (or may not be possible)
	 */
	// function queryBatch( $abort_on_error=true, $p_transaction_safe = false)
}