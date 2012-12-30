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
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

class interceptDB extends JDatabaseMySQLi
{	

	/**
	 * This special constructor reuses the existing resource from the existing db connecton
	 *
	 * @param unknown_type $options
	 */
	function __construct($options)
	{
		$db = JFactory::getDBO();

		// support for recovery of existing connections (Martin N. Brampton)
		if (isset($this->options))
			$this->options = $options;

		$select = array_key_exists('select', $options) ? $options['select'] : true;
		$database = array_key_exists('database', $options) ? $options['database'] : '';

		// perform a number of fatality checks, then return gracefully
		if (!function_exists('mysqli_connect'))
		{
			$this->errorNum = 1;
			$this->errorMsg = 'The MySQL adapter "mysqli" is not available.';
			return;
		}

		// connect to the server
		$this->connection = $db->get("connection");

		// finalize initialization
		parent::__construct($options);

		// select the database
		if ($select)
		{
			$this->select($database);
		}

	}

	public function loadObjectList($key='', $class="stdClass", $translate=true, $language=null, $asObject = true, $onlytransFields = true)
	{	
		$this->translate = $translate;
			
		if ($this->skipjf || $translate === false) return parent::loadObjectList($key, $class);
		$pfunc = $this->profile();
		
		 // we can't call the query twice!
		
		if (!($cur = $this->query()))
		{
			return null;
		}

		 $fields = array();
		if (!$this->doTranslate( $fields))
		{
			$array = array();
			while ($row = mysqli_fetch_object($cur, $class)) {
				if ($key) {
					$array[$row->$key] = $row;
				} else {
					$array[] = $row;
				}
			}
			mysqli_free_result($cur);
			return $array;
		}
		
		$jfdata = array();
		if ($key != "")
		{
			while ($row = mysqli_fetch_array($cur, MYSQLI_BOTH))
			{
				$jfdata[$row[$key]] = $row;
			}
		}
		else
		{	
			while ($row = mysqli_fetch_array($cur, MYSQLI_BOTH))
			{
				$jfdata[] = $row;
			}
		}

		if (count($jfdata)==0){
			return $jfdata;
		}
		
		// Before joomfish manager is created since we can't translate so skip this anaylsis
		$jfManager = JoomFishManager::getInstance();
		if (!$jfManager){
			return $jfdata;
		}
		
		if (isset($jfManager))
		{
			$this->setLanguage($language);
		}

		if ($jfManager->getCfg("transcaching", 1))
		{
			$this->orig_limit = $this->get("limit");
			$this->orig_offset = $this->get("offset");

			// cache the results
			// special Joomfish database cache
			// $cache = $jfManager->getCache($language);			
			// $jfdata = $cache->get(array("JoomFish", 'translateListArrayCached'), array($jfdata, $language, $fields));
			$cache 	= JFactory::getCache('com_joomfish', 'callback');		
			$jfdata = $cache->get("JoomFish::translateListArrayCached", array(&$jfdata, $language, $fields, $onlytransFields));
			$this->orig_limit = 0;
			$this->orig_offset = 0;
		}
		else
		{
			$this->orig_limit =  $this->get("limit");
			$this->orig_offset = $this->get("offset");
			JoomFish::translateListArray($jfdata, $language, $fields, $onlytransFields);
			$this->orig_limit = 0;
			$this->orig_offset = 0;
		}

		mysqli_free_result($cur);

		if ($asObject)
		{
			$array = array();
			foreach ($jfdata as $row)
			{
				$obj = new stdClass();
				$fieldcount = 0;
				foreach ($fields as $field)
				{
					$fieldname = $field->name;
					$obj->$fieldname = $row[$fieldcount];
					$fieldcount++;
				}
				if ($key)
				{
					$array[$obj->$key] = $obj;
				}
				else
				{
					$array[] = $obj;
				}
			}
			$pfunc = $this->profile($pfunc);

			return $array;
		}
		$pfunc = $this->profile($pfunc);
		return $jfdata;

	}

	private function doTranslate( &$fields)
	{
		if ($this->skipjf) return false;
		// This is 
		if (isset($this->sql->jfprocessed) && $this->sql->jfprocessed){
			return false;
		}
		$cur = $this->cursor;
		$doTranslate = false;
		$jfManager = JoomFishManager::getInstance();
		if (isset($jfManager))
		{
			$fields = mysqli_fetch_fields($cur);
			foreach ($fields as $field)
			{
				if (isset($field->orgtable) && $field->orgtable!="")
				{
					$table = substr($field->orgtable, strlen($this->tablePrefix));
					if (!$this->translatedContentAvailable($table))
					{
						continue;
					}
					// is this field translateable 
					if (isset($field->orgname) && $field->orgname!=""&& $this->testTranslateableFields($table,array($field->orgname)))
					{
						$doTranslate = true;
						break;
					}
				}
			}
		}
		return $doTranslate;

	}

	public function query()
	{	
		$sql = (is_a($this->sql, "JDatabaseQueryMySQLi")) ? (string)$this->sql : $this->sql;
		
		if ($this->skipjf) return parent::query();
		if($this->translate === false || !stristr($sql, 'SELECT')) {
			return parent::query();
		}
		
		$jfmCount = 0;
		$jfManager = JoomFishManager::getInstance();
		$defaultlang = $jfManager->getDefaultLanguage();
		

		

		
		if (is_a($this->sql, "JDatabaseQueryMySQLi") && !isset($this->sql->jfprocessed) && ($this->sql->where !== null && is_a($this->sql->where, "JDatabaseQueryElement")) ) {
			
			$elements = $this->sql->where->getElements();
			foreach ( $elements as &$element) {
				if(strstr($element, 'language')) {
					//str_ireplace("\,\'\*\'", "\,\'\*\',\'".$defaultlang."'", $value);
					$element = str_ireplace(",'*'" , ",'*','".$defaultlang."'" , $element);
					$jfmCount++;
				}
			}
		$this->sql->clear('where');	
		$this->sql->where($elements);
		
		}
		
		$tablewithprefix = $this->getTableName();
		$table = str_ireplace('#__', '', $tablewithprefix );
		
		//$key = $jfManager->getPrimaryKey($table);   obsolete
		$ce = $jfManager->getContentElement($table);
		$key = is_object($ce) ? $ce->getReferenceId() : false;
		$isnative = (is_object($ce) && $ce->getTarget() == 'native') ? true : false;
		
		// last check - do we have content element for this and is this table native??
		if ($key === false || $isnative === false) {
			return parent::query();
		}
		
		
		if ( $this->translate === true && is_a($this->sql, "JDatabaseQueryMySQLi") && !isset($this->sql->jfprocessed) && ($this->sql->select !== null && is_a($this->sql->select, "JDatabaseQueryElement") ) ) {
				
			$elements2 = $this->sql->select->getElements();
			$keyfound = false;
			$asfound = false;
			foreach ( $elements2 as $element2) {
				// is there id column or * in the select query
				if(stristr($element2, ' '.$key.' ') || stristr($element2, ','.$key) || stristr($element2, ' '.$key.',') || strstr($element2, '*') || stristr($element2, '.'.$key.',') || stristr($element2, ' '.$key.' ,' ) || stristr($element2, '.'.$key.' ,' )) {							
					$keyfound = true;
				}
				
				// is any column named as soemthing else or has table prepended, then do join
				if(preg_match('/FROM(.*?) AS/is',$element2) || stristr($element2, '.') ) {
					$asfound = true;
				}
				
			}
			
			if ($keyfound === false && $asfound == true) {
				// OUR JOIN NEEDS TO BE FIRST FOR "USING" TO WORK PROPERLY
				if (is_array($this->sql->join) ) {
					$joinelements = array();
					$joinprevious = $this->sql->join;

					$this->sql->clear('join');
					$this->sql->innerJoin($tablewithprefix. ' AS jfself USING (' .$key. ')');
					foreach ($joinprevious AS $joinprev) {
						$name = JoomlaProtectedFixDatabaseQueryElement::getInstance($joinprev)->get('name');
						$type = str_ireplace(' JOIN','', $name);
						$elements = $joinprev->getElements();
						$this->sql->join($type, $elements);
					}
					
				} else {
					$this->sql->innerJoin($tablewithprefix. ' AS jfself USING (' .$key. ')');
				}

				$this->sql->select('jfself.'.$key.' AS '.$key);
				$jfmCount++;
			} else if ($keyfound === false && $asfound === false) {
				$this->sql->select($key);
				$jfmCount++;
			}

		} else if ($this->translate === true && is_string($this->sql)  && !isset($this->sql->jfprocessed) && stristr($this->sql, 'SELECT') && stristr($this->sql, 'FROM')) {
			$keyfound = false;
			$asfound = false;
			
			preg_match('/SELECT(.*?)FROM/is', $this->sql, $selectstring);
			$selectstring = $selectstring[0];
			if(stristr($selectstring, ' '.$key.' ') || stristr($selectstring, ','.$key) || stristr($selectstring, ' '.$key.',') || strstr($selectstring, '*') || stristr($selectstring, '.'.$key.',') || stristr($selectstring, ' '.$key.' ,' ) || stristr($selectstring, '.'.$key.' ,' )) {
				$keyfound = true;
			}
			if(preg_match('/FROM(.*?) AS/is',$selectstring) || stristr($selectstring, '.') ) {
				$asfound = true;
			}
			
			
			if ($keyfound === false  && $asfound == true) {
				//$this->sql = preg_replace('/SELECT(.*?)FROM/i', 'SELECT '. $selectstring . ', jfself.'.$key.' AS '.$key .' FROM', $this->sql  );
				$this->sql = preg_replace('/SELECT /i', 'SELECT jfself.'.$key.' AS '.$key .', ', $this->sql, 1);
				
				//make sure our join comes first
				$replacejoin = preg_match('/( left| right| inner| outer| join| where)/i',$this->sql,$matches);
				if ($replacejoin) {
					$this->sql = preg_replace('/( left| right| inner| outer| join| where)/i',' JOIN ' .$tablewithprefix. ' AS jfself USING ('.$key.') '.$matches[0],$this->sql, 1);
				} else {
					$this->sql .= ' JOIN ' .$tablewithprefix. ' AS jfself USING ('.$key.') ';
				}
				$jfmCount++;

			} else if ($keyfound === false && $asfound === false) {
				//$this->sql = preg_replace('/SELECT /i', 'SELECT '.$key. ', ', $this->sql, 1);
				$this->sql = preg_replace('/ FROM/is', ','.$key. ' FROM', $this->sql, 1);
				$jfmCount++;
			}
			

		}
		

		// NEW SYSTEM disabled for now - the query handling for joins etc. is too complex
		/*if (false && is_a($this->sql, "JDatabaseQuery") && !isset($this->sql->jfprocessed))
		{
			// Do the from first
			$sql = $this->replacePrefix((string) $this->sql);
			//$jfManager = JoomFishManager::getInstance();
			//$contentElements = $jfManager->getContentElements( );
			// search for
			// AND a.language in \(.*,\*\) using regexp !
			// Before joomfish manager is created since we can't translate so skip this anaylsis
			$jfManager = JoomFishManager::getInstance();
			if (!$jfManager)
				return;
			$language = false;
			if (isset($jfManager))
			{
				$this->setLanguage($language);
			}
			$from = $this->sql->from;
			$joins = $this->sql->join;
			if ($from || $join)
			{
				$joinElements = array();
				if ($joins){
					foreach ($joins as $join) {
						 $joinElements = array_merge($joinElements, $join->getElements() );
					}
				}
				$fromElements = $from->getElements();
				if ($fromElements)
				{
					foreach ($fromElements as $fromElement)
					{
						// remove surplus spaces
						$fromElement = preg_replace('/\s{2}/', '', $fromElement);
						$fromElement = preg_replace('/' . $this->getPrefix() . '/', '', $fromElement);
						$fromElement = preg_replace('/#__/', '', $fromElement);
						$parts = explode(" ", $fromElement);
						$table = trim($parts[0]);
						//if ($this->translatedContentAvailable($table))
						// TODO need new translatedContentAvailable method !
						// This is the mapping table method!!
						// NEW SYSTEM
						if (in_array($table,array("menu", "content", "modules",  "categories")))
						//if (in_array($table,array("content",  "categories")))
						{
							$alias = trim($parts[count($parts) - 1]);
							$jfalias = 'jftm' . $jfmCount;
							$jfmCount++;
							// TODO needs to get primary key for this table not assume it is id
							$this->sql->leftJoin("#__jf_translationmap AS $jfalias ON $jfalias.reference_table = " . $this->quote($table) . "   AND $jfalias.reference_id = $alias.id AND $jfalias.language= " . $this->quote($language));
							$this->sql->where(" $jfalias.reference_id IS NULL ");
						}
					}
				}
			}
			if ($jfmCount>0){
				$this->sql->jfprocessed = true;
			}
		} */
		
		$sqlfinal = (is_a($this->sql, "JDatabaseQueryMySQLi")) ? (string)$this->sql : $this->sql;
		
		$result = parent::query();
		
		/*if ($jfmCount>0){
				$this->sql->jfprocessed = true;
			}*/
		return $result;

	}

}

/*
 * Get and set JDatabaseQueryElement properties that are declared as protected. 
 * Pass JDatabaseQueryElement object to constructor
 */

class JoomlaProtectedFixDatabaseQueryElement extends JDatabaseQueryElement {
	
	public $obj;
	
	public static function getInstance(JDatabaseQueryElement $obj) {
		return new JoomlaProtectedFixDatabaseQueryElement($obj);
	}
	
	public function __construct(JDatabaseQueryElement $obj) {
		$this->obj = $obj;
	}
	
	public function get($name) {
		return $this->obj->$name;
	}
	
	public function set($name, $value) {
		$this->obj->$name = $value;
	}
	
}