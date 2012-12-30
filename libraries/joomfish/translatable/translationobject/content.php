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
jimport('joomfish.translatable.translationobject');
jimport('joomfish.translatable.translatable');


class  TranslationObjectContent  extends TranslationObject 
{

	/** Reads the information out of an existing JTable object into the translationObject.
	 *
	 * @param	object	instance of an mosDBTable object
	 */
	public function updateMLContent(&$dbObject, $language)
	{
		$db = JFactory::getDBO();
		if ($dbObject === null)
			return;

		if ($this->published == "")
			$this->published = 0;

		// retrieve the original untranslated object for references
		// this MUST be copied by value and not by reference!
		$origObject = clone($dbObject);
		$key = $dbObject->get('_tbl_key');
		$db->setQuery("SELECT * FROM " . $dbObject->get('_tbl') . " WHERE " . $key . "='" . $dbObject->$key . "'");
		$origObject = $db->loadObject('stdClass', false);

		// We must reset the primary key and language fields for new translations
		// If we don't then joomla validity checks on aliases not being unique will fail etc.
		$isnew = false;
		if ($this->contentElement->getTarget() == "native"){
			if (isset($origObject->language)  && $origObject->language!=$language){
				$origObject->language = $language;
				$isnew = true;
			}

			// We must reset the primary key and language fields for new translations
			if ($isnew && isset($dbObject->$key)){
				$dbObject->$key = 0;	
				$this->translation_id=0;
				$this->id=0;

				// If we don't then joomla validity checks on aliases not being unique will fail etc.
				$alias = $dbObject->title;
				$this->filterTitle($alias);			
				$dbObject->alias = $alias;
			}
		}
		$this->copyContentToTranslation($dbObject, $origObject);

	}
	
	// Post handlers
	public function filterTitle(&$alias)
	{
		if ($alias == "")
		{
			$alias = JRequest::getString("refField_title");
		}
		$alias = JFilterOutput::stringURLSafe($alias);

	}

	public function filterName(&$alias)
	{
		if ($alias == "")
		{
			$alias = JRequest::getString("refField_name");
		}
		$alias = JFilterOutput::stringURLSafe($alias);

	}

	/**
	 * Special pre translation handler for content text to combine intro and full text
	 *
	 * @param unknown_type $row
	 */
	public function fetchArticleText($row)
	{

		/*
		 * We need to unify the introtext and fulltext fields and have the
		 * fields separated by the {readmore} tag, so lets do that now.
		 */
		if (JString::strlen($row->fulltext) > 1)
		{
			return $row->introtext . "<hr id=\"system-readmore\" />" . $row->fulltext;
		}
		else
		{
			return $row->introtext;
		}

	}

	/**
	 * Special pre translation handler for content text to combine intro and full text
	 *
	 * @param unknown_type $row
	 */
	public function fetchArticleTranslation($field, &$row)
	{	
		
		
		

		if ($field->Name != 'introtext' || is_null($row)) {
			return;
		}	
		
		/*
		 * We need to unify the introtext and fulltext fields and have the
		 * fields separated by the {readmore} tag, so lets do that now.
		*/
		if (trim($row->jfc_fulltext) != '')
		{
			/*if (trim($row->jfc_introtext) != '')
			{*/
				$fulltext	= $row->jfc_fulltext;
				$introtext	= $row->jfc_introtext;
			/*}
			else
			{
				$row->jfc_introtext = clone $row->jfc_fulltext;
				$row->jfc_fulltext 	= "";
				$fulltext 		= "";
			}
			if (JString::strlen($fulltext) > 1)
			{*/
				$row->jfc_introtext = $introtext . "<hr id=\"system-readmore\" />" . $fulltext;
				$row->jfc_fulltext 	= "";
			//}
		} 

	}

	/**
	 * Special post translation handler for content text to split intro and full text
	 *
	 * @param unknown_type $row
	 */
	public function saveArticleText(&$introtext, &$fields, &$formArray, $prefix, $suffix, $storeOriginalText)
	{

		// Search for the {readmore} tag and split the text up accordingly.
		$pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
		$tagPos = preg_match($pattern, $introtext);

		if ($tagPos > 0)
		{
			list($introtext, $fulltext) = preg_split($pattern, $introtext, 2);
			JRequest::setVar($prefix . "refField_fulltext" . $suffix, $fulltext, "post");
			$formArray[$prefix . "refField_fulltext" . $suffix] = $fulltext;
		}
		else
		{
			JRequest::setVar($prefix . "refField_fulltext" . $suffix, "", "post");
			$formArray[$prefix . "refField_fulltext" . $suffix] = "";
		}

	}


}

