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

jimport('joomfish.manager');

class translationFilter
{

	public $filterNullValue;
	public $filterType;
	public $filter_value;
	public $filterField = false;
	public $tableName = "";
	public $filterHTML = "";
	// Should we use session data to remember previous selections?
	public $rememberValues = true;
	public $contentElement = false;

	public function __construct($contentElement=null)
	{
		$this->contentElement = $contentElement;

		if (intval(JRequest::getVar('filter_reset', 0)))
		{
			$this->filter_value = $this->filterNullValue;
		}
		else if ($this->rememberValues)
		{
			// TODO consider making the filter variable name content type specific
			$this->filter_value = JFactory::getApplication()->getUserStateFromRequest($this->filterType . '_filter_value', $this->filterType . '_filter_value', $this->filterNullValue);
		}
		else
		{
			$this->filter_value = JRequest::getVar($this->filterType . '_filter_value', $this->filterNullValue);
		}
		//echo $this->filterType.'_filter_value = '.$this->filter_value."<br/>";
		$this->tableName = isset($contentElement) ? $contentElement->getTableName() : "";

	}
	
	/**
	 * Load and instantiate all necessary translation fitler classes 
	 */
	
	public static function getTranslationFilters($catid, $contentElement)
	{	
		JoomFishManager::addIncludePath(JOOMFISH_LIBPATH .DS. 'translationfilter','translationfilter' );
		JoomFishManager::addIncludePath(JOOMFISH_LIBPATH .DS. 'contentelement' .DS. 'contentelements', 'translationfilter');
		
		if (!$contentElement) {
			return array();
		}
		
		$filterNames = $contentElement->getAllFilters();
		if (count($filterNames) > 0)
		{
			$filterNames["reset"] = "reset";
		}
		$filters = array();
		foreach ($filterNames as $key => $value)
		{
			$filterType = "translation" . ucfirst(strtolower($key)) . "Filter";
			//$classFile =  JOOMFISH_LIBPATH .DS. 'contentelement' .DS. 'contentelements'.DS. "$filterType.php";
			if (!class_exists($filterType))
			{
				/*if (file_exists($classFile))
					include_once($classFile);*/

				// Search for the class file in the include paths.
				jimport('joomla.filesystem.path');

				if ($path = JPath::find(JoomFishManager::addIncludePath('','translationfilter'), strtolower($key) . '.php'))
				{
					include_once $path;
				}
				else if ($path = JPath::find(JoomFishManager::addIncludePath('','translationfilter'), $filterType . '.php'))
				{
					include_once $path;
				}

				if (!class_exists($filterType))
				{
					continue;
				}
			}
			
			$filters[strtolower($key)] = new $filterType($contentElement);
		}
		
		return $filters;
	
	}

	public function createFilter()
	{
		if (!$this->filterField)
			return "";
		$filter = "";
		if ($this->filter_value != $this->filterNullValue)
		{
			$filter = "c." . $this->filterField . "=$this->filter_value";
		}
		return $filter;

	}

	public function createFilterHTML()
	{
		return "";

	}
	

}