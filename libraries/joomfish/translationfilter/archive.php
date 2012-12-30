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
 * @subpackage Models
 *
 */


defined('_JEXEC') or die('Restricted access');

class translationArchiveFilter extends translationFilter
{

	public function __construct($contentElement)
	{
		$this->filterNullValue = -1;
		$this->filterType = "archive";
		$this->filterField = $contentElement->getFilter("archive");
		parent::__construct($contentElement);

	}

	public function createFilter()
	{
		if (!$this->filterField)
		return "";
		$filter = "";

		if ($this->contentElement->getTarget()  == "joomfish")
		{
			if ($this->filter_value != $this->filterNullValue)
			{
				if ($this->filter_value == 0)
				{
					$filter = " c." . $this->filterField . " >=0 ";
				}
				else
				{
					$filter = " c." . $this->filterField . " =-1 ";
				}
			}

		} else {
			if ($this->filter_value != $this->filterNullValue)
			{
				if ($this->filter_value == 0)
				{
					$filter = " ct." . $this->filterField . " !=2 OR  ct." . $this->filterField ." IS NULL";
				}
				else
				{
					$filter = " ct." . $this->filterField . " =2 ";
				}
			}
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
		$FrontpageOptions[] = JHTML::_('select.option', 1, JText::_('JYES'));
		$FrontpageOptions[] = JHTML::_('select.option', 0, JText::_('JNO'));
		$frontpageList = array();
		$frontpageList["title"] = JText::_('ARCHIVE_FILTER');
		$frontpageList["html"] = JHTML::_('select.genericlist', $FrontpageOptions, 'archive_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value);

		return $frontpageList;

	}

}
