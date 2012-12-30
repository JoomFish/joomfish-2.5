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