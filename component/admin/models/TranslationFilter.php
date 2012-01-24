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
 * $Id: TranslationFilter.php 239M 2011-06-22 06:28:53Z (local) $
 * @package joomfish
 * @subpackage Models
 *
 */
defined('_JEXEC') or die('Restricted access');

function getTranslationFilters($catid, $contentElement)
{
	if (!$contentElement)
		return array();
	$filterNames = $contentElement->getAllFilters();
	if (count($filterNames) > 0)
	{
		$filterNames["reset"] = "reset";
	}
	$filters = array();
	foreach ($filterNames as $key => $value)
	{
		$filterType = "translation" . ucfirst(strtolower($key)) . "Filter";
		$classFile = JPATH_SITE . "/administrator/components/com_joomfish/contentelements/$filterType.php";
		if (!class_exists($filterType))
		{
			if (file_exists($classFile))
				include_once($classFile);
			if (!class_exists($filterType))
			{
				continue;
			}
		}
		$filters[strtolower($key)] = new $filterType($contentElement);
	}
	return $filters;

}

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

class translationResetFilter extends translationFilter
{

	public function __construct($contentElement)
	{
		$this->filterNullValue = -1;
		$this->filterType = "reset";
		$this->filterField = "";
		parent::__construct($contentElement);

	}

	public function createFilter()
	{
		return "";

	}

	/**
	 * Creates javascript session memory reset action
	 *
	 */
	public function createFilterHTML()
	{
		$reset["title"] = JText::_('RESET');
		$reset["html"] = "<input type='hidden' name='filter_reset' id='filter_reset' value='0' /><input type='button' value='" . JText::_('RESET') . "' onclick='document.getElementById(\"filter_reset\").value=1;document.adminForm.submit()' />";
		return $reset;

	}

}

class translationFrontpageFilter extends translationFilter
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
		$FrontpageOptions[] = JHTML::_('select.option', 1, JText::_('YES'));
		$FrontpageOptions[] = JHTML::_('select.option', 0, JText::_('NO'));
		$frontpageList = array();
		$frontpageList["title"] = JText::_('ARCHIVE_FILTER');
		$frontpageList["html"] = JHTML::_('select.genericlist', $FrontpageOptions, 'archive_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value);

		return $frontpageList;

	}

}

class translationCategoryFilter extends translationFilter
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

class translationAuthorFilter extends translationFilter
{

	public function __construct($contentElement)
	{
		$this->filterNullValue = -1;
		$this->filterType = "author";
		$this->filterField = $contentElement->getFilter("author");
		parent::__construct($contentElement);

	}

	public function createFilterHTML()
	{
		$db = JFactory::getDBO();

		if (!$this->filterField)
			return "";
		$AuthorOptions = array();
		$AuthorOptions[] = JHTML::_('select.option', '-1', JText::_('ALL_AUTHORS'));

		//	$sql = "SELECT c.id, c.title FROM #__categories as c ORDER BY c.title";
		$sql = "SELECT DISTINCT auth.id, auth.username FROM #__users as auth, #__" . $this->tableName . " as c
			WHERE c." . $this->filterField . "=auth.id ORDER BY auth.username";
		$db->setQuery($sql);
		$cats = $db->loadObjectList();
		$catcount = 0;
		foreach ($cats as $cat)
		{
			$AuthorOptions[] = JHTML::_('select.option', $cat->id, $cat->username);
			$catcount++;
		}
		$Authorlist = array();
		$Authorlist["title"] = JText::_('AUTHOR_FILTER');
		$Authorlist["html"] = JHTML::_('select.genericlist', $AuthorOptions, 'author_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value);

		return $Authorlist;

	}

}

class translationKeywordFilter extends translationFilter
{

	public function __construct($contentElement)
	{
		$this->filterNullValue = "";
		$this->filterType = "keyword";
		$this->filterField = $contentElement->getFilter("keyword");
		parent::__construct($contentElement);

	}

	public function createFilter()
	{
		if (!$this->filterField)
			return "";
		$filter = "";
		if ($this->filter_value != "")
		{
			$db = JFactory::getDBO();
			$filter = "LOWER(c." . $this->filterField . " ) LIKE '%" . $db->getEscaped($this->filter_value, true) . "%'";
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
	public function createFilterHTML()
	{
		$db = JFactory::getDBO();

		if (!$this->filterField)
			return "";
		$Keywordlist = array();
		$Keywordlist["title"] = JText::_('KEYWORD_FILTER');
		$Keywordlist["html"] = '<input type="text" name="keyword_filter_value" value="' . $this->filter_value . '" class="text_area" onChange="document.adminForm.submit();" />';

		return $Keywordlist;

	}

}

class translationModuleFilter extends translationFilter
{

	public function __construct($contentElement)
	{
		$this->filterNullValue = -1;
		$this->filterType = "module";
		$this->filterField = $contentElement->getFilter("module");
		parent::__construct($contentElement);

	}

	public function createFilter()
	{
		$filter = "c." . $this->filterField . "<99";
		return $filter;

	}

	public function createFilterHTML()
	{
		return "";

	}

}

class translationMenutypeFilter extends translationFilter
{

	public function __construct($contentElement)
	{
		$this->filterNullValue = "-+-+";
		$this->filterType = "menutype";
		$this->filterField = $contentElement->getFilter("menutype");
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
		$MenutypeOptions = array();
		$MenutypeOptions[] = JHTML::_('select.option', $this->filterNullValue, JText::_('ALL_MENUS'));

		$sql = "SELECT DISTINCT mt.menutype FROM #__menu as mt";
		$db->setQuery($sql);
		$cats = $db->loadObjectList();
		$catcount = 0;
		foreach ($cats as $cat)
		{
			$MenutypeOptions[] = JHTML::_('select.option', $cat->menutype, $cat->menutype);
			$catcount++;
		}
		$Menutypelist = array();
		$Menutypelist["title"] = JText::_('MENU_FILTER');
		$Menutypelist["html"] = JHTML::_('select.genericlist', $MenutypeOptions, 'menutype_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value);

		return $Menutypelist;

	}

}

/**
 * filters translations based on creation/modification date of original
 *
 */
class translationChangedFilter extends translationFilter
{

	public function __construct($contentElement)
	{
		$this->filterNullValue = -1;
		$this->filterType = "lastchanged";
		$this->filterField = $contentElement->getFilter("changed");
		list($this->_createdField, $this->_modifiedField) = explode("|", $this->filterField);
		parent::__construct($contentElement);

	}

	public function createFilter()
	{
		if (!$this->filterField)
			return "";
		$filter = "";
		if ($this->filter_value != $this->filterNullValue && $this->filter_value == 1)
		{
			// translations must be created after creation date so no need to check this!
			$filter = "( c.$this->_modifiedField>0 AND jfc.modified < c.$this->_modifiedField)";
		}
		else if ($this->filter_value != $this->filterNullValue)
		{
			$filter = "( ";
			$filter .= "( c.$this->_modifiedField>0 AND jfc.modified >= c.$this->_modifiedField)";
			$filter .= " OR ( c.$this->_modifiedField=0 AND jfc.modified >= c.$this->_createdField)";
			$filter .= " )";
		}

		return $filter;

	}

	public function createFilterHTML()
	{
		$db = JFactory::getDBO();

		if (!$this->filterField)
			return "";
		$ChangedOptions = array();
		$ChangedOptions[] = JHTML::_('select.option', -1, JText::_('FILTER_BOTH'));
		$ChangedOptions[] = JHTML::_('select.option', 1, JText::_('ORIGINAL_NEWER'));
		$ChangedOptions[] = JHTML::_('select.option', 0, JText::_('TRANSLATION_NEWER'));

		$ChangedList = array();
		$ChangedList["title"] = JText::_('TRANSLATION_AGE');
		$ChangedList["html"] = JHTML::_('select.genericlist', $ChangedOptions, $this->filterType . '_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value);

		return $ChangedList;

	}

}

/**
 * Look for unpublished translations - i.e. no translation or translation is unpublished
 * Really only makes sense with a specific language selected
 *
 */
class translationTrashFilter extends translationFilter
{

	public function translationTrashFilter($contentElement)
	{
		$this->filterNullValue = -1;
		$this->filterType = "trash";
		$this->filterField = $contentElement->getFilter("trash");
		parent::__construct($contentElement);

	}

	public function createFilter()
	{
		// -1 = archive, -2 = trash
		$filter = "c." . $this->filterField . ">=-1";
		return $filter;

	}

	public function createFilterHTML()
	{
		return "";

	}

}

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

?>
