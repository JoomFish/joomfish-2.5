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
 * $Id: TranslationObject.php 239M 2013-01-01 06:28:53Z (local) $
 * @package joomfish
 * @subpackage Models
 *
 */
jimport('joomfish.translatable.translationobject');
jimport('joomfish.translatable.translatable');


class  TranslationObjectMenu extends TranslationObject 
{
    
    	// Post handlers
	public function filterTitle(&$alias)
	{
		if ($alias == "")
		{
			$alias = JRequest::getString("refField_title");
		}
		$alias = JFilterOutput::stringURLSafe($alias);

	}

		// Post handlers

	public function saveUrlParams(&$link, $fields, $formarray)
	{
		// Check for the special 'request' entry.
		$data = $formarray["jform"];
		if (isset($formarray['refField_link']) && isset($data['request']) && is_array($data['request']) && !empty($data['request']))
		{
			// Parse the submitted link arguments.
			$args = array();
			parse_str(parse_url($formarray['refField_link'], PHP_URL_QUERY), $args);

			// Merge in the user supplied request arguments.
			$args = array_merge($args, $data['request']);
			$link = 'index.php?' . urldecode(http_build_query($args, '', '&'));
		}

	}

	public function saveMenuPath(&$path, $fields, $formArray, $prefix, $suffix, $storeOriginalText)
	{
		$pathfield = false;
		$alias = false;
		$ref = false;
		foreach ($fields as $field)
		{
			if ($field->Name == "path")
			{
				$pathfield = $field;
			}
			if ($field->Name == "alias")
			{
				$alias = $field;
			}
			if ($field->Name == "id")
			{
				$ref = $field;
			}
		}
		if (!$pathfield || !$ref || !$alias)
		{
			return;
		}
		//$path = $alias->translationContent->value;
		//return;

		$table = JTable::getInstance("Menu");
		// TODO get this from the translation!
		$pk = (intval($formArray[$prefix . "reference_id" . $suffix]) > 0) ? intval($formArray[$prefix . "reference_id" . $suffix]) : $this->id;

		$table->load($pk);
		$langid = $alias->translationContent->language_id;
		// Get the path from the node to the root (translated)
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$select = 'p.*, jfc.value as jfcvalue';
		$query->select($select);
		$query->from('#__menu AS n, #__menu AS p');
		$query->join('left', "#__jf_content as jfc ON jfc.reference_table='menu' AND jfc.reference_id=p.id AND jfc.language_id='$langid' and jfc.reference_field='alias' ");
		$query->where('n.lft BETWEEN p.lft AND p.rgt');
		$query->where('n.id = ' . (int) $pk);
		$query->order('p.lft');

		$db->setQuery($query);
		$sql = (string) $db->getQuery();
		$pathNodes = $db->loadObjectList('', 'stdClass', false);

		$segments = array();
		foreach ($pathNodes as $node)
		{
			// Don't include root in path
			if ($node->alias != 'root')
			{
				if (isset($node->jfcvalue))
				{
					$segments[] = $node->jfcvalue;
				}
				else
				{
					$segments[] = $node->alias;
				}
			}
		}
		$newPath = trim(implode('/', $segments), ' /\\');
		$path = $newPath;

		// Also need to rebuild children - translated or not!!!
		// 
		// Use new path for partial rebuild of table
		// rebuild will return positive integer on success, false on failure
		//$path = $table->rebuild($table->id,  $table->lft, $table->level, $newPath);;

	}
	
	/*
	 * Unset home attribute on copy, we can only have one home in single menu tree
	*/
	
	public function unsetHomeTr($field, &$row) {
		if($row->home == "1") {
			$row->jfc_home = "0";
		}
	}

}