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
 * $Id: intercept.jdatabasemysqli.oldMethods.php 241 2011-06-22 15:42:55Z geraint $
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

		// finalize initialization
		parent::__construct($options);

		// select the database
		if ( $select ) {
			$this->select($database);
		}

	}

	function getFieldCount(){
		if (is_object($this->cursor) && get_class($this->cursor)=="mysqli_result"){
			$fields = mysqli_num_fields($this->cursor);
			return $fields;
		}
		// This is either a broken db connection or a bad query
		return 0;
	}

	function getFieldMetaData($i){
		$meta = mysqli_fetch_field($this->cursor);
		return $meta;
	}

	function setRefTables(){

		$pfunc = $this->profile();

		if($this->cursor===true || $this->cursor===false) {
			$pfunc = $this->profile($pfunc);
			return;
		}

		// Before joomfish manager is created since we can't translate so skip this anaylsis
		$jfManager = JoomFishManager::getInstance();
		if (!$jfManager) return;

		// only needed for selects at present - possibly add for inserts/updates later
		$tempsql = $this->sql;
		if (strpos(strtoupper(trim($tempsql)),"SELECT")!==0) {
			$pfunc = $this->profile($pfunc);
			return;
		}

		$config = JFactory::getConfig();

		// get column metadata
		$fields = $this->getFieldCount();

		if ($fields<=0) {
			$pfunc = $this->profile($pfunc);
			return;
		}

		$this->refTables=array();
		$this->refTables["fieldTablePairs"]=array();
		$this->refTables["tableAliases"]=array();
		$this->refTables["reverseTableAliases"]=array();
		$this->refTables["fieldAliases"]=array();
		$this->refTables["fieldTableAliasData"]=array();
		$this->refTables["fieldCount"]=$fields;
		// Do not store sql in _reftables it will disable the cache a lot of the time

		$tableAliases = array();
		for ($i = 0; $i < $fields; ++$i) {
			$meta = $this->getFieldMetaData($i);
			if (!$meta) {
				echo JText::_("No information available<br />\n");
			}
			else {
				$tempTable =  $meta->table;
				// if I have already found the table alias no need to do it again!
				if (array_key_exists($tempTable,$tableAliases)){
					$value = $tableAliases[$tempTable];
				}
				// mysqli only
				else if (isset($meta->orgtable)){
					$value = $meta->orgtable;
					if (isset($this->tablePrefix) && strlen($this->tablePrefix)>0 && strpos($meta->orgtable,$this->tablePrefix)===0) {
						$value = substr($meta->orgtable, strlen( $this->tablePrefix));
					}
					$tableAliases[$tempTable] = $value;
				}
				else {
					continue;
				}

				if ((!($value=="session" || strpos($value,"jf_")===0)) && $this->translatedContentAvailable($value)){
					/// ARGH !!! I must also look for aliases for fieldname !!
					if (isset($meta->orgname)){
						$nameValue = $meta->orgname;
					}
					else {
						 $nameValue = $meta->name;
					}

					if (!array_key_exists($value,$this->refTables["tableAliases"])) {
						$this->refTables["tableAliases"][$value]=$meta->table;
					}
					if (!array_key_exists($meta->table,$this->refTables["reverseTableAliases"])) {
						$this->refTables["reverseTableAliases"][$meta->table]=$value;
					}
					// I can't use the field name as the key since it may not be unique!
					if (!in_array($value,$this->refTables["fieldTablePairs"])) {
						$this->refTables["fieldTablePairs"][]=$value;
					}
					if (!array_key_exists($nameValue,$this->refTables["fieldAliases"])) {
						$this->refTables["fieldAliases"][$meta->name]=$nameValue;
					}

					// Put all the mapping data together so that everything is in sync and I can check fields vs aliases vs tables in one place
					$this->refTables["fieldTableAliasData"][$i]=array("fieldNameAlias"=>$meta->name, "fieldName"=>$nameValue,"tableNameAlias"=>$meta->table,"tableName"=>$value);

				}

			}
		}
		$pfunc = $this->profile($pfunc);
	}


}
?>
