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


class TranslationFilterFrontpage extends TranslationFilter
{

	public function __construct($contentElement)
	{
		$this->filterNullValue = -1;
		$this->filterType = "frontpage";
		$this->filterField = $contentElement->getFilter("frontpage");
		parent::__construct($contentElement);

	}

	public function createFilter()
	{
		if (!$this->filterField)
			return "";
		$filter = "";
		if ($this->filter_value != $this->filterNullValue)
		{
			$db = JFactory::getDBO();
			$sql = "SELECT content_id FROM #__content_frontpage order by ordering";
			$db->setQuery($sql);
			$ids = $db->loadResultArray();
			if (is_null($ids))
			{
				$ids = array();
			}
			$ids[] = -1;
			$idstring = implode(",", $ids);
			$not = "";
			if ($this->filter_value == 0)
			{
				$not = " NOT ";
			}
			$filter = " c." . $this->filterField . $not . " IN (" . $idstring . ") ";
		}
		return $filter;

	}

	/**
	 * Creates frontpage filter
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

		$FrontpageOptions = array();
		$FrontpageOptions[] = JHTML::_('select.option', -1, JText::_('FILTER_ANY'));
		$FrontpageOptions[] = JHTML::_('select.option', 1, JText::_('JF_YES'));
		$FrontpageOptions[] = JHTML::_('select.option', 0, JText::_('JF_NO'));
		$frontpageList = array();
		$frontpageList["title"] = JText::_('FRONTPAGE_FILTER');
		$frontpageList["html"] = JHTML::_('select.genericlist', $FrontpageOptions, 'frontpage_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value);

		return $frontpageList;

	}
}

?>
