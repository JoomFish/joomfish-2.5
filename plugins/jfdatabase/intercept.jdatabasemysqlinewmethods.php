<?php
/**
 * Joom!Fish - Multi Lingual extention and translation manager for Joomla!
 * Copyright (C) 2003 - 2010, Think Network GmbH, Munich
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
 * $Id: intercept.jdatabasemysqlinewmethods.php 241 2011-06-22 15:42:55Z geraint $
 * @package joomfish
 * @subpackage jfdatabase
 * @version 2.0
 *
*/


// Don't allow direct linking
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

class interceptDB extends JDatabaseMySQLi   {
	
	/**
	 * This special constructor reuses the existing resource from the existing db connecton
	 *
	 * @param unknown_type $options
	 */
	function __construct($options){
		$db =  JFactory::getDBO();
		
		// support for recovery of existing connections (Martin N. Brampton)
		if (isset($this->options)) $this->options = $options;
				
		$select		= array_key_exists('select', $options)	? $options['select']	: true;
		$database	= array_key_exists('database',$options)	? $options['database']	: '';

		// perform a number of fatality checks, then return gracefully
		if (!function_exists( 'mysqli_connect' )) {
			$this->errorNum = 1;
			$this->errorMsg = 'The MySQL adapter "mysqli" is not available.';
			return;
		}

		// connect to the server
		$this->resource =  $db->_resource;

		// finalize initialization
		parent::__construct($options);

		// select the database
		if ( $select ) {
			$this->select($database);
		}
		
	}

	function setRefTables(){ 
		}

	function loadObjectList( $key='' ,$class="stdClass",  $translate=true, $language=null , $asObject = true)
	{
		if (!$translate){
			return  parent::loadObjectList( $key , $class);
	}

		if (!($cur = $this->query())) {
			return null;
	}

		$fields = array();
		if (!$this->doTranslate($cur, $fields)){
			return  parent::loadObjectList( $key , $class);
		}

		$pfunc = $this->profile();
		/*
		 $results = parent::loadObjectList( $key , $class);
			$pfunc = $this->profile($pfunc);
		return $results;
		*/

		$jfdata = array();
		if ($key != ""){
			while ($row = mysqli_fetch_array($cur, MYSQLI_BOTH)) {
				$jfdata[$row[$key]] = $row;
			}
		}
		else {
			while ($row = mysqli_fetch_row($cur)) {
				$jfdata[] = $row;
		}
		}
		
		// Before joomfish manager is created since we can't translate so skip this anaylsis
		$jfManager = JoomFishManager::getInstance();
		if (!$jfManager) return;
		if( isset($jfManager)) {
			$this->setLanguage($language);
		}

		if ($jfManager->getCfg("transcaching",1)){
			// cache the results
			$cache = $jfManager->getCache($language);
			$this->orig_limit	= $this->limit;
			$this->orig_offset	= $this->offset;
			$jfdata = $cache->get( array("JoomFish", 'translateListArrayCached'), array($jfdata, $language, $fields));
			$this->orig_limit	= 0;
			$this->orig_offset	= 0;
			}
			else {
			$this->orig_limit	= $this->limit;
			$this->orig_offset	= $this->offset;
			JoomFish::translateListArray( $jfdata, $language, $fields );
			$this->orig_limit	= 0;
			$this->orig_offset	= 0;
				}

		mysqli_free_result( $cur );

		if ($asObject){
			$array = array();
			foreach ($jfdata as $row){
				$obj = new stdClass();
				$fieldcount = 0;
				foreach ($fields as $field){
					$fieldname = $field->name;
					$obj->$fieldname = $row[$fieldcount];
					$fieldcount++;
					}
				if ($key) {
					$array[$obj->$key] = $obj;
				} else {
					$array[] = $obj;
				}
				}
			$pfunc = $this->profile($pfunc);

			return $array;
					}
		$pfunc = $this->profile($pfunc);
		return $jfdata;
					}
					
	private function doTranslate($cur , &$fields)
	{
		$doTranslate=false;
		$jfManager = JoomFishManager::getInstance();
		if( isset($jfManager)) {
			$fields = mysqli_fetch_fields($cur);
			foreach ($fields as $field){
				if (isset($field->orgtable)){
					$table = substr($field->orgtable, strlen( $this->tablePrefix));
					if ($this->translatedContentAvailable($table)) {
						$doTranslate=true;
						break;
					}
					}
					}
					}
		return $doTranslate;
				}
/*
	public function query(){
		if (is_a($this->sql, "JDatabaseQuery")){
			$sql = $this->replacePrefix((string) $this->sql);
			// search for
			// AND a.language in \(.*,\*\) using regexp !

			// Annoying that _from is protected in databasequery object and no get method!
			$x = 1;
			}
		return parent::query();		
		}
*/
}
