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

 *
 */
/**
 * @package joomfish
 * @subpackage frontend.includes
 * @copyright 2003 - 2013, Think Network GmbH, Konstanz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Revision: 1501 $
 * @author Alex Kempkens <Alex@JoomFish.net>
 */
// ensure this file is being included by a parent file
defined('_JEXEC') or die('Restricted access');

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
class JoomFish
{

	/**
	 * Translates a list based on cached values
	 * @param array $rows
	 * @param JFLanguage $language
	 * @param array $tableArray
	 */
	public static function translateListArrayCached(&$rows, $language, $tableArray, $onlytransFields = true)
	{
		JoomFish::translateListArray($rows, $language, $tableArray, $onlytransFields);
		return $rows;

	}

	/**
	 * Translates a list of items
	 * @param array $rows
	 * @param JFLanguage $language
	 * @param array $tableArray
	 */
	public static function translateListArray(&$rows, $language, $fields, $onlytransFields = true)
	{
		if (!isset($rows) || !is_array($rows) || count($rows)==0)
			return $rows;

		$jfManager = JoomFishManager::getInstance();

		$registry = JFactory::getConfig();
		$defaultLang = $registry->getValue("config.defaultlang");

		$db = JFactory::getDBO();
		$querySQL = (string) $db->getQuery();

		// do not try to translate if I have no fields!!!
		if (!isset($fields) || count($fields) == 0)
		{
			return;
		}

		$fielddata = JoomFish::getTablesIdsAndFields($fields);
		
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('joomfish');
		$dispatcher->trigger('onBeforeTranslationProcess', array(&$rows, $language, &$fielddata, $querySQL, $onlytransFields));
		
		// If I write content in non-default language then this skips the translation!
		//if($language == $defaultLang) return $rows;
		$rowsLanguage = $language;
		if (count($rows) > 0)
		{
			foreach ($fielddata as $reftable => $value)
			{
				if ($reftable=="allfields") continue;
				$table = $value["orgtable"];
				if (!$table || count($value['fields'])==0) {
					continue;
				}

				// If there is no translated content for this table then skip it!
				if (!$db->translatedContentAvailable($table))
					continue;

				// get the fieldnames to see if we have any translateable fields
				$fieldnames = array();
				foreach ($value['fields'] as $fieldinfo){
					if (isset($fieldinfo->orgname) && $fieldinfo->orgname!=""){
						$fieldnames[] = $fieldinfo->orgname;
					}
				}
				if (!$db->testTranslateableFields($table,$fieldnames))
					continue;
				
				// get primary key for tablename
				$idkey = $jfManager->getPrimaryKey(trim($reftable));

				// find the primary id column number
				if (!isset($value["idindex"]))
				{
					continue;
				}
				$keycol = $value["idindex"];

				$idlist = array(); // temp variable to make sure all ids in idstring are unique (for neatness more than performance)
				foreach ($rows as $row)
				{
					if (!empty($row[$keycol]))
					{
						$idlist[] = $row[$keycol];
					}
				}
				if (count($idlist) == 0)
					continue;
				$idstring = implode(",", array_unique($idlist));

				if (!$jfManager->getContentElement($table) || $jfManager->getContentElement($table)->getTarget() == "joomfish")
				{
					JoomFish::translateListArrayWithIDs($rows, $idstring, $table, $reftable, $language, $keycol, $idkey, $fielddata, $querySQL);
				}
				else
				{
					JoomFish::nativeTranslateListArrayWithIDs($rows, $idstring, $table, $reftable, $language, $keycol, $idkey, $fielddata, $querySQL, true, $onlytransFields);
				}
			}
		}

	}

	public static function getTablesIdsAndFields($fields)
	{
		$data = array();
		$jfManager = JoomFishManager::getInstance();
		$db = JFactory::getDBO();

		// find the primary id column number
		$fieldcount = 0;
		foreach ($fields as $field)
		{
			// use table instead of orgtable since orgtable could appear multiple times
			if (isset($field->orgtable) && isset($field->orgname))
			{
				if (isset($field->table) && $field->table!="")
				{
					$table = "alias_" . $field->table;
					$orgtable = substr($field->orgtable, strlen($db->getPrefix()));
				}
				else
				{
					$table = substr($field->orgtable, strlen($db->getPrefix()));
					$orgtable = $table;
				}
				if (!isset($data[$table]))
				{
					$data[$table] = array();
					$idkey = $jfManager->getPrimaryKey($orgtable);
					$data[$table]['idkey'] = $idkey;
					$data[$table]['fields'] = array();
				}
				else {
					$idkey = $data[$table]['idkey'];
				}
				// must match the alias too!
				if ($field->orgname == $idkey)
				{
					$data[$table]['idindex'] = $fieldcount;
				}
				$data[$table]['orgtable'] = $orgtable;
				// I always want the field position otherwise the translations could be shifted to the wrong columns
				$data[$table]['fields'][$fieldcount] = $field;
			}
			$data["allfields"][] = $field;
			$fieldcount++;
		}
		return $data;

	}

	/**
	 * Function to translate a section object
	 * @param array $rows
	 * @param string $ids
	 * @param string $reference_table
	 * @param JFLanguage $language
	 * @param string $refTablePrimaryKey
	 * @param array $tableArray
	 * @param string $querySQL
	 * @param boolean $allowfallback
	 * @param boolean only translate translatable fields
	 */
	public static function translateListArrayWithIDs(&$rows, $ids, $reference_table, $tablealias, $language, $keycol, $idkey, &$fielddata, $querySQL, $allowfallback=true, $onlytransFields = true)
	{

		$registry = JFactory::getConfig();
		$defaultLang = $registry->getValue("config.defaultlang");
		$language = (isset($language) && $language != '') ? $language : $defaultLang;

		$db = JFactory::getDBO();

		// setup Joomfish pluginds
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('joomfish');

		if ($reference_table == "jf_content")
		{
			return;  // I can't translate myself ;-)
		}

		$results = $dispatcher->trigger('onBeforeTranslation', array(&$rows, $ids, $reference_table, $tablealias, $language, $keycol, $idkey, &$fielddata, $querySQL, $allowfallback, $onlytransFields));

		// if onBeforeTranslation has cleaned out the list then just return at this point
		if (strlen($ids) == 0)
			return;

		static $languages;
		if (!isset($languages))
		{
			$jfm = JoomFishManager::getInstance();
			$languages = $jfm->getLanguagesIndexedByCode();
		}

		// process fallback language
		$fallbacklanguage = false;

		$fallbackrows = array();
		$idarray = explode(",", $ids);
		$fallbackids = array();
		if (isset($languages[$language]) && $languages[$language]->fallback_code != "")
		{
			$fallbacklanguage = $languages[$language]->fallback_code;
			if (!array_key_exists($fallbacklanguage, $languages))
			{
				$allowfallback = false;
			}
		}
		if (!$fallbacklanguage)
		{
			$allowfallback = false;
		}

		if (isset($ids) && $reference_table != '')
		{
			$user = JFactory::getUser();
			$published = $user->authorise('core.publish', 'com_joomfish') ? "\n	AND jf_content.published=1" : "";
			//$published = "\n	AND jf_content.published=1";
			$sql = "SELECT jf_content.reference_field, jf_content.value, jf_content.reference_id, jf_content.original_value "
					. "\nFROM #__jf_content AS jf_content"
					. "\nWHERE jf_content.language_id=" . $languages[$language]->id
					. $published
					. "\n   AND jf_content.reference_id IN($ids)"
					. "\n   AND jf_content.reference_table='$reference_table'"
			;
			$db->setQuery($sql);
			$translations = $db->loadObjectList('', 'stdClass', false);
			if (count($translations) > 0)
			{
				$fieldmap = null;
				foreach (array_keys($rows) as $key)
				{
					// assign by reference since not an object
					$row_to_translate = & $rows[$key];
					$rowTranslationExists = false;

					if (isset($row_to_translate[$keycol]))
					{
						foreach ($translations as $row)
						{
							//  go on only it this is the matching row
							if ($row->reference_id == $row_to_translate[$keycol])
							{
								$refField = $row->reference_field;

								foreach ($fielddata[$tablealias]["fields"] as $fieldcount => $field)
								{
									if ($field->orgname == $refField)
									{
										$row_to_translate[$fieldcount] = $row->value;
										$rowTranslationExists = true;
									}
								}
							}
						}

						if (!$rowTranslationExists)
						{
							if ($allowfallback && isset($row_to_translate[$keycol]))
							{
								$fallbackrows[$key] = & $row_to_translate;
								$fallbackids[$key] = $row_to_translate[$keycol];
							}
							else
							{
								//$results = $dispatcher->trigger('onMissingTranslation', array (&$row_to_translate, $language,$reference_table, $fielddata, $querySQL));
							}
						}
					}
				}
			}
			else
			{
				foreach (array_keys($rows) as $key)
				{
					// assign by reference since not an object
					$row_to_translate = & $rows[$key];
					if ($allowfallback && isset($row_to_translate[$keycol]))
					{
						$fallbackrows[$key] = & $row_to_translate;
						$fallbackids[$key] = $row_to_translate[$keycol];
					}
					else
					{
						//$results = $dispatcher->trigger('onMissingTranslation', array (&$row_to_translate, $language,$reference_table, $fielddata, $querySQL));
					}
				}
			}


			if ($allowfallback && count($fallbackrows) > 0)
			{
				$fallbackids = implode($fallbackids, ",");
				JoomFish::translateListArrayWithIDs($fallbackrows, $fallbackids, $reference_table, $tablealias, $fallbacklanguage, $keycol, $idkey, $fielddata, $querySQL, false, $onlytransFields);
			}

			$dispatcher->trigger('onAfterTranslation', array(&$rows, &$ids, $reference_table, $tablealias, $language, $keycol, $idkey, &$fielddata, $querySQL, $allowfallback, $onlytransFields));
		}
		

	}

	/**
	 * Function to translate list of elements where the data is stord in Joomla native tables using language flag
	 * @param array $rows
	 * @param string $ids
	 * @param string $reference_table
	 * @param JFLanguage $language
	 * @param string $refTablePrimaryKey
	 * @param array $tableArray
	 * @param string $querySQL
	 * @param boolean $allowfallback
	 */
	public static function nativeTranslateListArrayWithIDs(&$rows, $ids, $reference_table, $tablealias, $language, $keycol, $idkey, &$fielddata, $querySQL, $allowfallback=true, $onlytransFields = true)
	{
		$registry = JFactory::getConfig();
		$defaultLang = $registry->getValue("config.defaultlang");
		$language = (isset($language) && $language != '') ? $language : $defaultLang;

		$db = JFactory::getDBO();

		// setup Joomfish pluginds
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('joomfish');

		$results = $dispatcher->trigger('onBeforeTranslation', array(&$rows, $ids, $reference_table, $tablealias, $language, $keycol, $idkey, &$fielddata, $querySQL, $allowfallback, $onlytransFields));

		// if onBeforeTranslation has cleaned out the list then just return at this point
		if (strlen($ids) == 0)
			return;

		static $languages;
		if (!isset($languages))
		{
			$jfm = JoomFishManager::getInstance();
			$languages = $jfm->getLanguagesIndexedByCode();
		}

		// process fallback language
		$fallbacklanguage = false;

		$fallbackrows = array();
		$fallbackids = array();
		if (isset($languages[$language]) && $languages[$language]->fallback_code != "")
		{
			$fallbacklanguage = $languages[$language]->fallback_code;
			if (!array_key_exists($fallbacklanguage, $languages))
			{
				$allowfallback = false;
			}
		}
		if (!$fallbacklanguage)
		{
			$allowfallback = false;
		}

		if (isset($ids) && $reference_table != '')
		{
			$user = JFactory::getUser();
			// NEW SYSTEM - check for published state of translation e.g. using mapping table!
			// Need to know the published column name!!!
			$jfm = JoomFishManager::getInstance();
			$published = $jfm->getContentElement($reference_table)->getPublishedField();
			$published = $user->authorise('core.publish', 'com_joomfish') ? "\n	AND $published=1" : "";
			
			$sql = "SELECT tab.*, tmap.reference_id, tmap.translation_id FROM #__$reference_table as tab"
					. "\n LEFT JOIN #__jf_translationmap AS tmap ON tmap.reference_table = " . $db->quote($reference_table) . "   AND tmap.translation_id = tab.$idkey AND tmap.language= " . $db->quote($languages[$language]->code)
					. "\n  WHERE tmap.reference_id IN($ids)"
					//. "\nWHERE tab.language=" . $db->quote($languages[$language]->code)
					. $published
					. "\n AND tmap.reference_id IS NOT NULL "
			;
			$db->setQuery($sql);
			$translations = $db->loadObjectList("reference_id", 'stdClass', false);
			
			if (count($translations) > 0)
			{	
				
				$fieldmap = null;
				$rowsToUnset = array();
				foreach (array_keys($rows) as $key)
				{	
					// assign by reference since not an object
					$row_to_translate = & $rows[$key];
					$rowTranslationExists = false;
					$refid = $row_to_translate[$keycol];
					if (array_key_exists($refid, $translations))
					{						
						$rowTranslationExists = true;
						$translation = $translations[$refid];
						//  go on only it this is the matching row
						if ($translation->reference_id == $refid)
						{	
							$row_to_translate['original_id'] = $refid;
							
							foreach ($fielddata[$tablealias]["fields"] as $fieldcount => $field)
							{	
								
								
								$fieldname = $field->orgname;
								
								$transTest = ($onlytransFields && !$db->testTranslateableFields($reference_table,array($field->orgname))) ? false : true;
								
								if (isset($translation->$fieldname) && $transTest) {
									$row_to_translate[$fieldcount] = $translation->$fieldname;
								}
							}
						
							$rowsToUnset[] = $translation->translation_id; // we cannot unset here as this foreach will set index again if unset element comes after current one!
						}


					} 
					
					if (!$rowTranslationExists)
						{
							if ($allowfallback && isset($row_to_translate[$keycol]))
							{
								$fallbackrows[$key] = & $row_to_translate;
								$fallbackids[$key] = $row_to_translate[$keycol];
							}
							else
							{
								//$results = $dispatcher->trigger('onMissingTranslation', array (&$row_to_translate, $language,$reference_table, $fielddata, $querySQL));
							}
					
						}
				}
				
				// loop again and remove duplicates
				// @todo try to merge above loops 
				foreach (array_keys($rows) as $key) {
					if (in_array($rows[$key][$keycol], $rowsToUnset)) {
						unset ($rows[$key]);
					}
						
				}
			}
				else
			{
				foreach (array_keys($rows) as $key)
				{
					// assign by reference since not an object
					$row_to_translate = & $rows[$key];
					if ($allowfallback && isset($row_to_translate[$keycol]))
					{
						$fallbackrows[$key] = & $row_to_translate;
						$fallbackids[$key] = $row_to_translate[$keycol];
					}
					else
					{
						//$results = $dispatcher->trigger('onMissingTranslation', array (&$row_to_translate, $language,$reference_table, $fielddata, $querySQL));
					}
				}
			}
			

			if ($allowfallback && count($fallbackrows) > 0)
			{
				$fallbackids = implode($fallbackids, ",");
				JoomFish::nativeTranslateListArrayWithIDs($fallbackrows, $fallbackids, $reference_table, $tablealias, $fallbacklanguage, $keycol, $idkey, $fielddata, $querySQL, false, $onlytransFields);
			}

			$dispatcher->trigger('onAfterTranslation', array(&$rows, $ids, $reference_table,  $language, $keycol, $idkey, &$fielddata, $querySQL, $allowfallback, $onlytransFields));
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
	public static function contentElementFields($reference_table)
	{
		static $info;
		if (!isset($info))
		{
			$info = array();
		}
		if (!isset($info[$reference_table]))
		{
			$cacheDir = JPATH_CACHE;
			$cacheFile = $cacheDir . "/" . $reference_table . "_cefields.cache";
			if (file_exists($cacheFile))
			{
				$cacheFileContent = file_get_contents($cacheFile);
				$info[$reference_table] = unserialize($cacheFileContent);
			}
			else
			{
				$jfm = JoomFishManager::getInstance();
				$contentElement = $jfm->getContentElement($reference_table);
				// The language is not relevant for this function so just use the current language
				$registry = JFactory::getConfig();
				$lang = $registry->getValue("config.jflang");

				$translationClass = $contentElement->getTranslationObjectClass();
				$translationObject = new $translationClass( $jfManager->getLanguageID($lang), $contentElement );
				$textFields = $translationObject->getTextFields();
				$info[$reference_table]["textFields"] = $textFields;
				$info[$reference_table]["fieldTypes"] = array();
				if ($textFields !== null)
				{
					$defaultSet = false;
					foreach ($textFields as $field)
					{
						$info[$reference_table]["fieldTypes"][$field] = $translationObject->getFieldType($field);
					}
				}
				$cacheFileContent = serialize($info[$reference_table]);
				$handle = @fopen($cacheFile, "w");
				if ($handle)
				{
					fwrite($handle, $cacheFileContent);
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
	public static function version()
	{
		return JoomFishManager::getVersion();

	}

}