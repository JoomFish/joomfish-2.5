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

/**
 * Look for unpublished translations - i.e. no translation or translation is unpublished
 * Really only makes sense with a specific language selected
 *
 */

class translationPublishedFilter extends translationFilter
{

	public function translationPublishedFilter($contentElement)
	{
		$this->filterNullValue = -1;
		$this->filterType = "published";
		$this->filterField = $contentElement->getFilter("published");
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
				if ($this->filter_value == 1)
				{
					$filter = "jfc." . $this->filterField . "=$this->filter_value";
				}
				else if ($this->filter_value == 0)
				{
					$filter = " ( jfc." . $this->filterField . "=$this->filter_value AND jfc.reference_field IS NOT NULL ) ";
				}
				else if ($this->filter_value == 2)
				{
					$filter = " jfc.reference_field IS NULL  ";
				}
				else if ($this->filter_value == 3)
				{
					$filter = " jfc.reference_field IS NOT NULL ";
				}
			}
		}
		else
		{
			if ($this->filter_value != $this->filterNullValue)
			{
				if ($this->filter_value == 1)
				{
					$filter = "ct." . $this->filterField . "=$this->filter_value";
				}
				else if ($this->filter_value == 0)
				{
					$filter = " ( ct." . $this->filterField . "=$this->filter_value AND tm.translation_id IS NOT NULL ) ";
				}
				else if ($this->filter_value == 2)
				{
					$filter = " tm.translation_id IS NULL  ";
				}
				else if ($this->filter_value == 3)
				{
					$filter = " tm.translation_id IS NOT NULL ";
				}
			}
		}

		return $filter;

	}

	public function createFilterHTML()
	{
		$db = JFactory::getDBO();

		if (!$this->filterField)
			return "";

		$PublishedOptions = array();
		$PublishedOptions[] = JHTML::_('select.option', -1, JText::_('FILTER_ANY'));
		$PublishedOptions[] = JHTML::_('select.option', 3, JText::_('FILTER_AVAILABLE'));
		$PublishedOptions[] = JHTML::_('select.option', 1, JText::_('TITLE_PUBLISHED'));
		$PublishedOptions[] = JHTML::_('select.option', 0, JText::_('TITLE_UNPUBLISHED'));
		$PublishedOptions[] = JHTML::_('select.option', 2, JText::_('FILTER_MISSING'));

		$publishedList = array();
		$publishedList["title"] = JText::_('TRANSLATION_AVAILABILITY');
		$publishedList["html"] = JHTML::_('select.genericlist', $PublishedOptions, 'published_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value);

		return $publishedList;

	}

}