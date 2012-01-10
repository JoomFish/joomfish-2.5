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

class TranslationFilterLanguage extends TranslationFilter
{

	public function __construct($contentElement)
	{
		$this->filterNullValue = '-1';
		$this->filterType = "language";
		$this->filterField = $contentElement->getFilter("language");
		parent::__construct($contentElement);
	}



	public function createFilter()
	{
		if (!$this->filterField)
			return "";
		$filter = "";
/*
		// TODO set source language?
					$where[] = "c.language='*'";
					
					$more1 = "\nSELECT tm4.reference_id from #__jf_translationmap as tm4 WHERE tm4.reference_table=".$db->quote($contentTable->Name);
					$more2 = "\nSELECT tm5.translation_id from #__jf_translationmap as tm5 WHERE tm5.reference_table=".$db->quote($contentTable->Name);
					$moreFilter = '';
					if ($contentTable->Filter != '')
					{
						$whereFilter[] = $contentTable->Filter;
					}
					
					$moreFilter .= (count($whereFilter) ? implode(' AND ', $whereFilter).' AND ' : '');
					if(JoomfishManager::getDefaultLanguage() == $lang->code )
					{
						$more = " OR (".$moreFilter." c." . $referencefield. " NOT IN (".$more2." ) AND c." . $referencefield. " NOT IN (".$more1." ) ) ";
					}
					else
					{
						$more = " OR (".$moreFilter." c." . $referencefield. " NOT IN (".$more2." ) AND c." . $referencefield. " NOT IN (".$more1." )) ";
					}

					$wheretransmap = " OR (tm.reference_id=c." . $referencefield. " AND tm.reference_table=".$db->quote($contentTable->Name);
					$wheretransmap .= " AND tm.language=" . $db->quote($lang->code).") ";


			$filter = "( ";
			$filter .= "( c.$this->_modifiedField>0 AND jfc.modified >= c.$this->_modifiedField)";
			$filter .= " OR ( c.$this->_modifiedField=0 AND jfc.modified >= c.$this->_createdField)";
			$filter .= " )";


*/
		
		if ($this->filter_value != $this->filterNullValue)
		{
			if($this->filter_value == -2)
			{
				$jfManager = JoomFishManager::getInstance();
				$filter = "c." . $this->filterField . "<>'".$jfManager->getLanguageByID(JRequest::getVar('select_language_id'))->lang_code."'";
			}
			/*
			elseif($this->filter_value == '*')
			{
			
			}
			*/
			else
			{
				//$filter = "( (c." . $this->filterField . "='$this->filter_value' OR c." . $this->filterField . "='*' )) ";
				$filter = "c." . $this->filterField . "='$this->filter_value'";
			}
		}
		else
		{
			//$filter = "c." . $this->filterField . "='$this->filterNullValue'";
		}
		
		return $filter;

	}


	/**
	 * Creates language filter
	 *
	 * @param unknown_type $filtertype
	 * @param unknown_type $contentElement
	 * @return unknown
	 */
	public function createFilterHTML()
	{
		if (!$this->filterField)
			return "";
		// get list of active languages
		$langOptions[] = JHTML::_('select.option', '-1', JText::_( 'SELECT_LANGUAGE' ) ); //all lang_codes and *
		$langOptions[] = JHTML::_('select.option', '*', JText::_( '* ALL' ) ); //only *
		$langOptions[] = JHTML::_('select.option', '-2', JText::_( 'NOT_SELECTD_LANGUAGE' ) );
		// Get data from the model
		$jfManager = JoomFishManager::getInstance();
		
		$langActive = $jfManager->getLanguages(false);// all languages even non active once
		$defaultLang = JoomfishManager::getDefaultLanguage();
		
		$params = JComponentHelper::getParams('com_joomfish');
		$showDefaultLanguageAdmin = $params->get("showDefaultLanguageAdmin", false);

		if ( count($langActive)>0 ) 
		{
			foreach( $langActive as $language )
			{
				if($language->code != $defaultLang || $showDefaultLanguageAdmin) {
					$langOptions[] = JHTML::_('select.option', $language->lang_code, $language->title );
				}
			}
		}
		$langlist = array();
		$langlist["title"] = JText::_('ORG_LANGUAGE');
		$langlist["html"] = JHTML::_('select.genericlist', $langOptions, 'language_filter_value', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->filter_value);
		
		return $langlist;

	}
}
?>
