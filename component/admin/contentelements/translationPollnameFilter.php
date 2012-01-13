<?php
/**
 * Joom!Fish - Multi Lingual extention and translation manager for Joomla!
 * Copyright (C) 2003 - 2012, Think Network GmbH, Munich
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
 * $Id: translationPollnameFilter.php 226 2011-05-27 07:29:41Z alex $
 * @package joomfish
 * @subpackage TranslationFilters
 *
*/

// Don't allow direct linking
defined( '_JEXEC' ) or die( 'Restricted access' );

class translationPollnameFilter extends translationFilter
{
	public function __construct ($contentElement){
		$this->filterNullValue=-1;
		$this->filterType="pollname";
		$this->filterField = $contentElement->getFilter("pollname");
		parent::__construct($contentElement);
	}

	/**
 * Creates vm_pollname filter
 *
 * @param unknown_type $filtertype
 * @param unknown_type $contentElement
 * @return unknown
 */
	public function createFilterHTML(){
		$db = JFactory::getDBO();

		if (!$this->filterField) return "";
		$pollnameOptions=array();
		$pollnameOptions[] = JHTML::_('select.option', '-1', JText::_( 'ALL_POLLS' ) );

		//	$sql = "SELECT c.id, c.title FROM #__categories as c ORDER BY c.title";
		$sql = "SELECT DISTINCT p.id, p.title FROM #__polls as p, #__".$this->tableName." as c";
		if ($this->filterField!=$this->filterNullValue){
			$sql.= " WHERE c.".$this->filterField."=p.id ORDER BY p.title";
		}
		$db->setQuery($sql);
		$cats = $db->loadObjectList();
		
		$catcount=0;
		foreach($cats as $cat){
			$pollnameOptions[] = JHTML::_('select.option', $cat->id,$cat->title);
			$catcount++;
		}
		$pollnameList=array();
		$pollnameList["title"]= JText::_( 'WHICH_POLL' );
		$pollnameList["html"] = JHTML::_('select.genericlist',  $pollnameOptions, 'pollname_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value );

		return $pollnameList;
	}

}
?>
