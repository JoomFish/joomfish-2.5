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

class TranslationFilterExtension extends TranslationFilter
{

	public function __construct($contentElement)
	{
		$this->filterNullValue = "-1";
		$this->filterType = "extension";
		$this->filterField = $contentElement->getFilter("extension");
		parent::__construct($contentElement);

	}

	public function createFilter()
	{
		if (!$this->filterField)
			return "";
		$filter = "";
		if ($this->filter_value != $this->filterNullValue)
		{
			$filter = "c." . $this->filterField . "='" . $this->filter_value . "'";
		}
		return $filter;

	}

	public function createFilterHTML()
	{
		$db = JFactory::getDBO();

		if (!$this->filterField)
			return "";
		$ExtensionOptions = array();
		$ExtensionOptions[] = JHTML::_('select.option', $this->filterNullValue, JText::_('ALL_EXTENSIONS'));
		/*
		$sql = "SELECT DISTINCT cat.extension FROM #__categories as cat";
		$db->setQuery($sql);
		*/
		$query = $db->getQuery(true);
		$query->select('DISTINCT category.extension');
		$query->from('#__categories as category');
		$query->where('id > \'1\'');
		$query->where('extension <> \'\'');
		$db->setQuery($query);
		
		
		
		$cats = $db->loadObjectList();
		$catcount = 0;
		foreach ($cats as $cat)
		{
			$ExtensionOptions[] = JHTML::_('select.option', $cat->extension, $cat->extension);
			$catcount++;
		}
		$Extensionlist = array();
		$Extensionlist["title"] = JText::_('EXTENSION_FILTER');
		$Extensionlist["html"] = JHTML::_('select.genericlist', $ExtensionOptions, 'extension_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value);

		return $Extensionlist;

	}

}

?>
