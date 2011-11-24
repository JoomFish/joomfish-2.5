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
 * $Id: joomfish.class.php 226 2011-05-27 07:29:41Z alex $
 *
*/

/**
* @package joomfish
 * @subpackage frontend.includes
 * @copyright 2003 - 2011, Think Network GmbH, Munich
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Revision: 1501 $
 * @author Alex Kempkens <Alex@JoomFish.net>
*/

// ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * The joom fish change the text information in the supported
 * objects after they have been loaded. The idea is to create a
 * flexible environment which can add the multi language support at
 * any time.</p>
 *
 * The basic concept behind the joom fish is to map an existing content
 * with all his general information to a different translation of it's
 * text content. There is no additional copy of the information like the
 * author or publishing flags, only a copy of the text fields.
 *
 * @author	A. Kempkens
 */
class JoomFish {

	/**
	 * Translates a list based on cached values
	 * @param array $rows
	 * @param JFLanguage $language
	 * @param array $tableArray
	 */
	public function translateListCached ($rows,  $language , $tableArray) {
		JoomFish::translateList($rows,  $language , $tableArray);
		return $rows;
	}

	/**
	 * Translates a list of items
	 * @param array $rows
	 * @param JFLanguage $language
	 * @param array $tableArray
	 */
	public function translateList( &$rows, $language , $tableArray) {
		if (!isset($rows) || !is_array($rows)) return $rows;

		$jfManager = JoomFishManager::getInstance();

		$registry = JFactory::getConfig();
		$defaultLang = $registry->getValue("config.defaultlang");

		$db = JFactory::getDBO();
		$querySQL = (string) $db->getQuery();

		// do not try to translate if I have no fields!!!
		if (!isset($tableArray) || count($tableArray)==0) {
			//echo "$tableArray $querySQL<br>";
			return;
		}
		// If I write content in non-default language then this skips the translation!
		//if($language == $defaultLang) return $rows;
		$rowsLanguage = $language;
		if (count($rows)>0){
			foreach ($tableArray["fieldTablePairs"] as $key=>$value){
				$reftable = $tableArray["fieldTablePairs"][$key];
				$alias = $tableArray["tableAliases"][$reftable];

				// If there is not translated content for this table then skip it!
				if (!$db->translatedContentAvailable($reftable)) continue;

				// get primary key for tablename
				$idkey = $jfManager->getPrimaryKey( trim($reftable) );

				// I actually need to check the primary key against the alias list!

				for ($i=0;$i<$tableArray["fieldCount"];$i++){
					if (!array_key_exists($i,$tableArray["fieldTableAliasData"])) continue;
					// look for fields from the correct table with the correct name
					if (($tableArray["fieldTableAliasData"][$i]["tableName"]==$reftable) &&
					($tableArray["fieldTableAliasData"][$i]["fieldName"]==$idkey)
					&&  ($tableArray["fieldTableAliasData"][$i]["tableNameAlias"]==$alias)){
						$idkey=$tableArray["fieldTableAliasData"][$i]["fieldNameAlias"];
						break;
					}
				}


				// NASTY KLUDGE TO DEAL WITH SQL CONSTRUCTION IN contact.php, weblinks.php where multiple tables to be translated all use "id" which gets dropped! etc.
				if ($reftable=='categories' && isset($content->catid) && $content->catid>0) {
					$idkey = "catid";
				}
				if ($reftable=='sections' && count($rows)>0 && isset($content->sectionid) && $content->sectionid>0) {
					$idkey = "sectionid";
				}
				$idstring = "";
				$idlist = array(); // temp variable to make sure all ids in idstring are unique (for neatness more than performance)
				foreach( array_keys( $rows) as $key ) {
					$content = $rows[$key];


					if (isset($content->$idkey) && !in_array($content->$idkey,$idlist)) {
						//print ($idkey ." ".$content->$idkey." list<br>");
						$idstring .= (strlen( $idstring)>0?",":""). $content->$idkey;
						$idlist[] = $content->$idkey;
					}
				}
				if (strlen( $idstring)==0) continue;

				JoomFish::translateListWithIDs( $rows, $idstring, $reftable, $language ,$idkey, $tableArray, $querySQL);
			}
		}
	}

	/**
	 * Function to translate a section object
	 * @param array $rows
	 * @param array $ids
	 * @param string $reference_table
	 * @param JFLanguage $language
	 * @param string $refTablePrimaryKey
	 * @param array $tableArray
	 * @param string $querySQL
	 * @param boolean $allowfallback
	 */
	public function translateListWithIDs( &$rows, $ids, $reference_table, $language, $refTablePrimaryKey="id", & $tableArray, $querySQL, $allowfallback=true )
	{
		//print " translateListWithIDs for ids=$ids refTablePrimaryKey=$refTablePrimaryKey<br>" ;
		$config	= JFactory::getConfig();
		$debug = $config->get("debug");

		$registry = JFactory::getConfig();
		$defaultLang = $registry->getValue("config.defaultlang");
		$language = (isset($language) && $language!='') ? $language : $defaultLang;

		$db = JFactory::getDBO();

		// setup Joomfish pluginds
		$dispatcher	   = JDispatcher::getInstance();
		JPluginHelper::importPlugin('joomfish');

		if ($reference_table == "jf_content" ) {
			return;					// I can't translate myself ;-)
		}

		$results = $dispatcher->trigger('onBeforeTranslation', array (&$rows, &$ids, $reference_table, $language, $refTablePrimaryKey, & $tableArray, $querySQL, $allowfallback));

		// if onBeforeTranslation has cleaned out the list then just return at this point
		if (strlen($ids)==0) return ;

		// find reference table alias
		$reftableAlias = $reference_table;
		for ($i=0;$i<$tableArray["fieldCount"];$i++){
			if (!array_key_exists($i,$tableArray["fieldTableAliasData"])) continue;
			if ($tableArray["fieldTableAliasData"][$i]["tableName"]==$reference_table &&
			$tableArray["fieldTableAliasData"][$i]["fieldNameAlias"]==$refTablePrimaryKey ){
				$reftableAlias = $tableArray["fieldTableAliasData"][$i]["tableNameAlias"];
				break;
			}
		}

		// NASTY KLUDGE TO DEAL WITH SQL CONSTRUCTION IN contact.php, weblinks.php where multiple tables to be translated all use "id" which gets dropped! etc.
		$currentRow = current($rows);
		// must not check on catid>0 since this would be uncategorised items
		if ($reference_table=='categories' && count($rows)>0 && isset($currentRow->catid) ) {
			$reftableAlias = $tableArray["tableAliases"]["categories"];
		}
		if ($reference_table=='sections' && count($rows)>0 && isset($currentRow->sectionid)) {
			$reftableAlias = $tableArray["tableAliases"]["sections"];
		}

		//print " translateListWithIDs( ".count($rows). ", ids=$ids, reftab=$reference_table, $language, primkey = $refTablePrimaryKey )<br>";
		if ($debug) {
			echo "<p><strong>JoomFish debug (new):</strong><br>"
			. "reference_table=$reference_table<br>"
			. "$refTablePrimaryKey  IN($ids)<br>"
			. "language=$language<br>"
			.(count($rows)>0? "class=" .get_class(current($rows)):"")
			. "</p>";
		}

		static $languages;
		if (!isset($languages)){
			$jfm = JoomFishManager::getInstance();
			$languages = $jfm->getLanguagesIndexedByCode();
		}

		// process fallback language
		$fallbacklanguage = false;
		$fallbackrows=array();
		$idarray = explode(",",$ids);
		$fallbackids=array();
		$allowfallback=false;
		if (isset($languages[$language]) && $languages[$language]->fallback_code!="") {
			$fallbacklanguage = $languages[$language]->fallback_code;
			if (!array_key_exists($fallbacklanguage, $languages)){
				$allowfallback=false;
			}
		}
		if (!$fallbacklanguage) {
			$allowfallback=false;
		}

		if (isset($ids) && $reference_table!='') {
			$user = JFactory::getUser();
			$published = $user->authorise('core.publish', 'com_joomfish') ?"\n	AND jf_content.published=1":"";
			//$published = "\n	AND jf_content.published=1";
			$sql = "SELECT jf_content.reference_field, jf_content.value, jf_content.reference_id, jf_content.original_value "
			. "\nFROM #__jf_content AS jf_content"
			. "\nWHERE jf_content.language_id=".$languages[$language]->lang_id
			. $published
			. "\n   AND jf_content.reference_id IN($ids)"
			. "\n   AND jf_content.reference_table='$reference_table'"
			;
			$db->setQuery( $sql );
			$translations = $db->loadObjectList('','stdClass', false);
			if (count($translations)>0){
				$fieldmap = null;
				foreach( array_keys( $rows) as $key ) {
					$row_to_translate = $rows[$key];
					$rowTranslationExists=false;
					//print_r ($row_to_translate); print"<br>";
					if( isset( $row_to_translate->$refTablePrimaryKey ) ) {
						foreach ($translations as $row){
							if ($row->reference_id!=$row_to_translate->$refTablePrimaryKey) continue;
							// TODO - consider building array for refFields.  Some queries may have multiple aliases e.g. SELECT a.*, a.field as fieldalias
							$refField = $row->reference_field;
							// adjust refField for aliases (make sure the field is from the same table!).
							// I could reduce the calculation by building an array of translation reference fields against their mapping number
							// but this refinement can wait!

							$fieldmatch=false; // This is used to confirm the field is from the correct table
							for ($i=0;$i<$tableArray["fieldCount"];$i++){
								if (!array_key_exists($i,$tableArray["fieldTableAliasData"])) continue;
								// look for fields from the correct table with the correct name
								if ($tableArray["fieldTableAliasData"][$i]["tableName"]==$reference_table &&
								$tableArray["fieldTableAliasData"][$i]["fieldName"]==$refField &&
								$tableArray["fieldTableAliasData"][$i]["tableNameAlias"] == $reftableAlias){
									$refField=$tableArray["fieldTableAliasData"][$i]["fieldNameAlias"];
									$fieldmatch=true;
									break;
								}
							}
							$fieldIndex = $i;
							if ($fieldmatch && isset( $row->reference_id)  && $row->reference_id==$row_to_translate->$refTablePrimaryKey && $fieldIndex<=$tableArray["fieldCount"]){
								if (is_subclass_of($row_to_translate, 'mosDBTable')) {
									$row_to_translate->set($row->reference_field, $row->value);
								} else {
									$row_to_translate->$refField = $row->value;
								}
								$rowTranslationExists=true;
								//print_r( $row_to_translate);
							}
						}
						if (!$rowTranslationExists){
							if ($allowfallback && isset($rows[$key]->$refTablePrimaryKey)){
								$fallbackrows[$key] = $rows[$key];
								$fallbackids[$key] = $rows[$key]->$refTablePrimaryKey;
							}
							else {
								$results = $dispatcher->trigger('onMissingTranslation', array (&$row_to_translate, $language,$reference_table, $tableArray, $querySQL));

								//JoomFish::processMissingTranslation($row_to_translate, $language,$reference_table);
							}
						}
					}
				}
			}
			else {
				foreach( array_keys( $rows ) as $key ) {
					if ($allowfallback && isset($rows[$key]->$refTablePrimaryKey)){
						$fallbackrows[$key] = $rows[$key];
						$fallbackids[$key] = $rows[$key]->$refTablePrimaryKey;
					}
					else {
						$results = $dispatcher->trigger('onMissingTranslation', array (&$rows[$key], $language,$reference_table, $tableArray, $querySQL));
						//JoomFish::processMissingTranslation($rows[$key], $language,$reference_table);
					}
				}
			}


			if ($allowfallback && count($fallbackrows)>0 ){
				$fallbackids = implode($fallbackids,",");
				JoomFish::translateListWithIDs( $fallbackrows, $fallbackids, $reference_table, $fallbacklanguage, $refTablePrimaryKey, $tableArray,$querySQL, false);
			}

			$dispatcher->trigger('onAfterTranslation', array (&$rows, $ids, $reference_table, $language, $refTablePrimaryKey, $tableArray, $querySQL, $allowfallback));
		}
	}

	/**
	 * Translates a list based on cached values
	 * @param array $rows
	 * @param JFLanguage $language
	 * @param array $tableArray
	 */
	public function translateListArrayCached ($rows,  $language , $tableArray) {
		JoomFish::translateListArray($rows,  $language , $tableArray);
		return $rows;
	}

	/**
	 * Translates a list of items
	 * @param array $rows
	 * @param JFLanguage $language
	 * @param array $tableArray
	 */
	public function translateListArray( &$rows, $language , $fields) {
		if (!isset($rows) || !is_array($rows)) return $rows;

		$jfManager = JoomFishManager::getInstance();

		$registry = JFactory::getConfig();
		$defaultLang = $registry->getValue("config.defaultlang");

		$db = JFactory::getDBO();
		$querySQL = (string) $db->getQuery();

		// do not try to translate if I have no fields!!!
		if (!isset($fields) || count($fields)==0) {
			return;
		}

		$fielddata = 	JoomFish::getTablesIdsAndFields($fields, $this);

		// If I write content in non-default language then this skips the translation!
		//if($language == $defaultLang) return $rows;
		$rowsLanguage = $language;
		if (count($rows)>0){
			foreach ($fielddata as $reftable=>$value){

				$table =$value["orgtable"];

				// If there is no translated content for this table then skip it!
				// TODO New method for storage - mapping table
				if (!$db->translatedContentAvailable($table) || in_array($table,array("content","menu","modules"))) continue;
				//if (!$db->translatedContentAvailable($table) || in_array($table,array("content","modules"))) continue;

				// get primary key for tablename
				$idkey = $jfManager->getPrimaryKey( trim($reftable) );

				// find the primary id column number
				if (!isset($value["idindex"])) {
					continue;
				}
				$keycol = $value["idindex"];

				$idlist = array(); // temp variable to make sure all ids in idstring are unique (for neatness more than performance)
				foreach(  $rows as $row ) {
						if (!empty($row[$keycol])){
							$idlist[] = $row[$keycol];
						}
				}
				if (count( $idlist)==0) continue;
				$idstring = implode(",",$idlist);

				JoomFish::translateListArrayWithIDs( $rows, $idstring, $table, $reftable, $language ,$keycol, $fielddata, $querySQL);
			}
		}
	}

	static  function getTablesIdsAndFields($fields, $db){
		$data = array();
		$jfManager = JoomFishManager::getInstance();
		$db = JFactory::getDBO();

		// find the primary id column number
		$fieldcount = 0;
		foreach ($fields as $field){
			// use table instead of orgtable since orgtable could appear multiple times
			if (isset($field->orgtable) && isset($field->orgname)){
				if (isset($field->table)){
					$table = "alias_".$field->table;
					$orgtable = substr($field->orgtable, strlen( $db->getPrefix()));
				}
				else {
					$table = substr($field->orgtable, strlen( $db->getPrefix()));
					$orgtable = $table;
				}
				if (!isset($data[$table])){
					$data[$table] = array();
					$idkey = $jfManager->getPrimaryKey( trim($table));
					$data[$table]['idkey'] = $idkey;
					$data[$table]['fields'] = array();
				}
				// must match the alias too!
				if ($field->orgname == $idkey ){
					$data[$table]['idindex'] = $fieldcount;
				}
				$data[$table]['fields'][]=$field;
				$data[$table]['orgtable']=$orgtable;
				$fieldcount++;
			}
		}
		return $data;
	}

	/**
	 * Function to translate a section object
	 * @param array $rows
	 * @param array $ids
	 * @param string $reference_table
	 * @param JFLanguage $language
	 * @param string $refTablePrimaryKey
	 * @param array $tableArray
	 * @param string $querySQL
	 * @param boolean $allowfallback
	 */
	public function translateListArrayWithIDs( &$rows, $ids, $reference_table, $tablealias, $language, $keycol, & $fielddata, $querySQL, $allowfallback=true )
	{

		$config	= JFactory::getConfig();
		$debug = $config->get("dbprefix");

		$registry = JFactory::getConfig();
		$defaultLang = $registry->getValue("config.defaultlang");
		$language = (isset($language) && $language!='') ? $language : $defaultLang;

		$db = JFactory::getDBO();

		// setup Joomfish pluginds
		$dispatcher	   = JDispatcher::getInstance();
		JPluginHelper::importPlugin('joomfish');

		if ($reference_table == "jf_content" ) {
			return;					// I can't translate myself ;-)
		}

		$results = $dispatcher->trigger('onBeforeTranslation', array (&$rows, &$ids, $reference_table, $language, $keycol, & $fielddata, $querySQL, $allowfallback));

		// if onBeforeTranslation has cleaned out the list then just return at this point
		if (strlen($ids)==0) return ;

		static $languages;
		if (!isset($languages)){
			$jfm = JoomFishManager::getInstance();
			$languages = $jfm->getLanguagesIndexedByCode();
		}

		// process fallback language
		$fallbacklanguage = false;
		$fallbackrows=array();
		$idarray = explode(",",$ids);
		$fallbackids=array();
		if (isset($languages[$language]) && $languages[$language]->fallback_code!="") {
			$fallbacklanguage = $languages[$language]->fallback_code;
			if (!array_key_exists($fallbacklanguage, $languages)){
				$allowfallback=false;
			}
		}
		if (!$fallbacklanguage) {
			$allowfallback=false;
		}

		if (isset($ids) && $reference_table!='') {
			$user = JFactory::getUser();
			$published = $user->authorise('core.publish', 'com_joomfish') ?"\n	AND jf_content.published=1":"";
			//$published = "\n	AND jf_content.published=1";
			$sql = "SELECT jf_content.reference_field, jf_content.value, jf_content.reference_id, jf_content.original_value "
			. "\nFROM #__jf_content AS jf_content"
			. "\nWHERE jf_content.language_id=".$languages[$language]->id
			. $published
			. "\n   AND jf_content.reference_id IN($ids)"
			. "\n   AND jf_content.reference_table='$reference_table'"
			;
			$db->setQuery( $sql );
			$translations = $db->loadObjectList('','stdClass', false);
			if (count($translations)>0){
				$fieldmap = null;
				foreach( array_keys( $rows) as $key ) {
					// assign by reference since not an object
					$row_to_translate  =& $rows[$key];
					$rowTranslationExists=false;

					if( isset( $row_to_translate[$keycol] ) ) {
						foreach ($translations as $row){
							//  go on only it this is the matching row
							if ($row->reference_id==$row_to_translate[$keycol]) {
								$refField = $row->reference_field;

								$fieldcount = 0;
								foreach ($fielddata[$tablealias]["fields"] as $field){
									if ($field->orgname == $refField){
										$row_to_translate[$fieldcount] = $row->value;
										$rowTranslationExists=true;
									}
									$fieldcount++;
								}
							}

						}

						if (!$rowTranslationExists){
							if ($allowfallback && isset($row_to_translate[$keycol])){
								$fallbackrows[$key] = & $row_to_translate;
								$fallbackids[$key] = $row_to_translate[$keycol];
							}
							else {
								//$results = $dispatcher->trigger('onMissingTranslation', array (&$row_to_translate, $language,$reference_table, $fielddata, $querySQL));
							}
						}

					}
				}
			}
			else {
				foreach( array_keys( $rows) as $key ) {
					// assign by reference since not an object
					$row_to_translate  =& $rows[$key];
					if ($allowfallback && isset($row_to_translate[$keycol])){
						$fallbackrows[$key] = & $row_to_translate;
						$fallbackids[$key] = $row_to_translate[$keycol];
					}
					else {
						//$results = $dispatcher->trigger('onMissingTranslation', array (&$row_to_translate, $language,$reference_table, $fielddata, $querySQL));
					}
				}
			}


			if ($allowfallback && count($fallbackrows)>0 ){
				$fallbackids = implode($fallbackids,",");
				JoomFish::translateListArrayWithIDs( $fallbackrows, $fallbackids, $reference_table, $fallbacklanguage,  $keycol,  $fielddata,$querySQL, false);
			}

			$dispatcher->trigger('onAfterTranslation', array (&$rows, $ids, $reference_table, $language,  $keycol,  $fielddata, $querySQL, $allowfallback));


		}
	}

	/**
	 * Cached extraction of content element field information
	 * this cached version is shared between pages and hence makes a big improvement to load times
	 * for newly visited pages in a cached scenario
	 *
	 * @param string $reference_table
	 * @return value
	 */
	public function contentElementFields($reference_table){
		static $info;
		if (!isset($info)){
			$info = array();
		}
		if (!isset($info[$reference_table])){
			$cacheDir = JPATH_CACHE;
			$cacheFile = $cacheDir."/".$reference_table."_cefields.cache";
			if (file_exists($cacheFile)){
				$cacheFileContent = file_get_contents($cacheFile);
				$info[$reference_table] = unserialize($cacheFileContent);
			}
			else {
				$jfm = JoomFishManager::getInstance();
				$contentElement = $jfm->getContentElement( $reference_table );
				// The language is not relevant for this function so just use the current language
				$registry = JFactory::getConfig();
				$lang = $registry->getValue("config.jflang");

				include_once( JPATH_ADMINISTRATOR.DS."components".DS."com_joomfish".'/models/ContentObject.php');
				$contentObject = new ContentObject( $jfm->getLanguageID($lang), $contentElement );
				$textFields = $contentObject->getTextFields();
				$info[$reference_table]["textFields"] = $textFields ;
				$info[$reference_table]["fieldTypes"] = array();
				if( $textFields !== null ) {
					$defaultSet = false;
					foreach ($textFields as $field) {
						$info[$reference_table]["fieldTypes"][$field] = $contentObject->getFieldType($field);
					}
				}
				$cacheFileContent = serialize($info[$reference_table]);
				$handle = @fopen($cacheFile,"w");
				if ($handle){
					fwrite($handle,$cacheFileContent);
					fclose($handle);
				}
			}
		}

		return $info[$reference_table];
	}

	/**
	  * Version information of the component
	  *
	  */
	public function version() {
		return JoomFishManager :: getVersion();
	}
}
