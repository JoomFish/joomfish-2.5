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
 * $Id: TranslationFilter.php 239M 2011-06-22 06:28:53Z (local) $
 * @package joomfish
 * @subpackage Models
 *
 */
defined('_JEXEC') or die('Restricted access');

class TranslationFilterCategory extends TranslationFilter
{

	private $section_filter_value;

	public function __construct($contentElement)
	{
		$this->filterNullValue = -1;
		$this->filterType = "category";
		$this->filterField = $contentElement->getFilter("category");
		parent::__construct($contentElement);

		// if currently selected category is not compatible with section then reset
		if (intval(JRequest::getVar('filter_reset', 0)))
		{
			$this->section_filter_value = -1;
		}
		else if ($this->rememberValues)
		{
			$this->section_filter_value = JFactory::getApplication()->getUserStateFromRequest('section_filter_value', 'section_filter_value', -1);
		}
		else
		{
			$this->section_filter_value = JRequest::getVar("section_filter_value", -1);
		}

		if ($this->section_filter_value != -1 and $this->filter_value >= 0)
		{
			$cat = JTable::getInstance('category');
			$cat->load($this->filter_value);
			if ($cat->section != $this->section_filter_value)
			{
				$this->filter_value = -1;
			}
		}
		if ($this->section_filter_value == 0)
		{
			$this->filter_value = 0;
		}

	}

	/**
	 * Creates category filter
	 *
	 * @param unknown_type $filtertype
	 * @param unknown_type $contentElement
	 * @return unknown
	 */
	public function createFilterHTML()
	{
		$db = JFactory::getDBO();

		if (!$this->filterField)
			return "";

		// limit choices to specific section
		$sectionfilter = "";
		if ($this->section_filter_value != -1)
		{
			$sectionfilter = " AND section=" . $db->quote($this->section_filter_value);
		}

		$categoryOptions = array();
		$categoryOptions[-1] = JHTML::_('select.option', '-1', JText::_('ALL_CATEGORIES'));
		// if content categories then add "static content" null category
		if ($this->tableName == "content" && $this->section_filter_value <= 0)
		{
			$categoryOptions[0] = JHTML::_('select.option', '0', JText::_('UNCATEGORIZED'));
		}


		//	$sql = "SELECT c.id, c.title FROM #__categories as c ORDER BY c.title";
		$sql = "SELECT DISTINCT cat.id, cat.title FROM #__categories as cat, #__" . $this->tableName . " as c
			WHERE c." . $this->filterField . "=cat.id $sectionfilter ORDER BY cat.title";
		$db->setQuery($sql);
		$cats = $db->loadObjectList();
		$catcount = 0;
		foreach ($cats as $cat)
		{
			$categoryOptions[$cat->id] = JHTML::_('select.option', $cat->id, $cat->title);
			$catcount++;
		}
		$categoryList = array();
		$categoryList["title"] = JText::_('CATEGORY_FILTER');
		$categoryList["html"] = JHTML::_('select.genericlist', $categoryOptions, 'category_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value);

		return $categoryList;

	}
}
?>
