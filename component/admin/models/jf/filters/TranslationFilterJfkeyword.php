<?php
/**
 * Joom!Fish - Multi Lingual extention and translation manager for Joomla!
 * Copyright (C) 2003 - 2012, Think Network GmbH, Munich
 *
 * All rights reserved. The Joom!Fish project is a set of extentions for
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307,USA.
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -----------------------------------------------------------------------------
 * $Id: translationJfkeywordFilter.php 226 2011-05-27 07:29:41Z alex $
 * @package joomfish
 * @subpackage TranslationFilters
 *
*/

// Don't allow direct linking
defined( '_JEXEC' ) or die( 'Restricted access' );

class TranslationFilterJfkeyword extends TranslationFilter
{
	public function __construct ($contentElement){
		$this->filterNullValue="";
		$this->filterType="jfkeyword";
		$params = $contentElement->getFilter("jfkeyword");		
		list($this->filterField,$this->label) = explode("|",$params);
		parent::__construct($contentElement);
	}

	public function createFilter(){
		if (!$this->filterField) return "";
		$filter="";
		if ($this->filter_value!=""){
			$db = JFactory::getDBO();
			$filter = "LOWER(c.".$this->filterField." ) LIKE '%".$db->getEscaped( $this->filter_value, true )."%'";
		}
		return $filter;
	}

	/**
 * Creates Keyword filter
 *
 * @param unknown_type $filtertype
 * @param unknown_type $contentElement
 * @return unknown
 */
	public function createFilterHTML(){
		$db = JFactory::getDBO();

		if (!$this->filterField) return "";
		$Keywordlist=array();
		$Keywordlist["title"]= JText::_($this->label);
		$Keywordlist["html"] = 	'<input type="text" name="jfkeyword_filter_value" value="'.$this->filter_value.'" class="text_area" onChange="document.adminForm.submit();" />';

		return $Keywordlist;

	}
	

}
?>
