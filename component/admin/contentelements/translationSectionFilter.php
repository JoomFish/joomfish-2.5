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
 * $Id: translationSectionFilter.php 226 2011-05-27 07:29:41Z alex $
 * @package joomfish
 * @subpackage TranslationFilters
 *
*/

// Don't allow direct linking
defined( '_JEXEC' ) or die( 'Restricted access' );

class translationSectionFilter extends translationFilter
{
	public function __construct ($contentElement){
		$this->filterNullValue=-1;
		$this->filterType="section";
		$this->filterField = $contentElement->getFilter("section");
		parent::__construct($contentElement);
	}

	/**
 * Creates section filter
 *
 * @param unknown_type $filtertype
 * @param unknown_type $contentElement
 * @return unknown
 */
	public function createFilterHTML(){
		$db = JFactory::getDBO();

		if (!$this->filterField) return "";
		$sectionOptions=array();
		$sectionOptions[] = JHTML::_('select.option', '-1', JText::_( 'ALL_SECTIONS' ));
		$sectionOptions[] = JHTML::_('select.option', '0', JText::_( 'UNCATEGORIZED' ));

		//	$sql = "SELECT c.id, c.title FROM #__categories as c ORDER BY c.title";
		$sql = "SELECT DISTINCT section.id, section.title FROM #__sections as section, #__".$this->tableName." as c
			WHERE c.".$this->filterField."=section.id ORDER BY section.title";
		$db->setQuery($sql);
		$sections = $db->loadObjectList();
		$sectioncount=0;
		foreach($sections as $section){
			$sectionOptions[] = JHTML::_('select.option', $section->id,$section->title);
			$sectioncount++;
		}
		$sectionList=array();
		$sectionList["title"]= JText::_( 'SECTION_FILTER' );
		$sectionList["html"] = JHTML::_('select.genericlist', $sectionOptions, 'section_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value );
		return $sectionList;

	}

}
?>
