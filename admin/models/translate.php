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
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

JLoader::register('JFModel', JOOMFISH_ADMINPATH . DS . 'models' . DS . 'JFModel.php');

/**
 * This is the corresponding module for translation management
 * @package		Joom!Fish
 * @subpackage	Translate
 */
class TranslateModelTranslate extends JFModel
{

	protected $_modelName = 'translate';

	/**
	 * return the model name
	 */
	public function getName()
	{
		return $this->_modelName;

	}

	/**
	 * Method to prepare the language list for the translation backend
	 * The method defines that all languages are being presented except the default language
	 * if defined in the config.
	 * @return array of languages
	 */
	public function getLanguages()
	{
		$jfManager = JoomFishManager::getInstance();
		return $jfManager->getLanguages(false);

	}

	/**
	 * Deletes the selected translations (only the translations of course)
	 * @return string	message
	 */
	public function removeTranslation($catid, $cid)
	{
		$message = '';
		$db = JFactory::getDBO();
		foreach ($cid as $cid_row)
		{
			list($translationid, $contentid, $language_id) = explode('|', $cid_row);

			$jfManager = JoomFishManager::getInstance();
			$contentElement = $jfManager->getContentElement($catid);
			if ($contentElement->getTarget() == "joomfish")
			{
				$contentTable = $contentElement->getTableName();
				$contentid = intval($contentid);
				$translationid = intval($translationid);

				// safety check -- complete overkill but better to be safe than sorry
				// get the translation details
				JLoader::import('tables.JFContent', JOOMFISH_ADMINPATH);
				$translation = new jfContent($db);
				$translation->load($translationid);

				if (!isset($translation) || $translation->id == 0)
				{
					$this->setState('message', JText::sprintf('NO_SUCH_TRANSLATION', $translationid));
					continue;
				}

				// make sure translation matches the one we wanted
				if ($contentid != $translation->reference_id)
				{
					$this->setState('message', JText::_('SOMETHING_DODGY_GOING_ON_HERE'));
					continue;
				}

				$sql = "DELETE from #__jf_content WHERE reference_table='$catid' and language_id=$language_id and reference_id=$contentid";
				$db->setQuery($sql);
				$db->query();
				if ($db->getErrorNum() != 0)
				{
					$this->setError(JText::_('SOMETHING_DODGY_GOING_ON_HERE'));
					JError::raiseWarning(400, JTEXT::_('No valid table information: ') . $db->getErrorMsg());
					continue;
				}
				else
				{
					$this->setState('message', JText::_('TRANSLATION_SUCCESSFULLY_DELETED'));
				}
			}
			else
			{
				$db = JFactory::getDbo();
				$contentElement = $jfManager->getContentElement($catid);
				$tableclass = $contentElement->getTableClass();
				if ($tableclass && intval($translationid) > 0)
				{
					// load the translation and amend
					$table = JTable::getInstance($tableclass);
					$table->load(intval($translationid));
					if (!$table->delete())
					{
						$this->setError(JText::_('SOMETHING_DODGY_GOING_ON_HERE'));
						JError::raiseWarning(400, JTEXT::_('No valid table information: ') . $db->getErrorMsg());
						continue;
					}
					else
					{
						$this->setState('message', JText::_('TRANSLATION_SUCCESSFULLY_DELETED'));
					}
				}
			}
		}
		return $message;

	}
	
	/*
	 * Get a list of originals ids and titles for modal selector
	 */
	public function getSimpleOriginalItemList($table, $limitstart, $limit) {
		
		$db				= JFactory::getDbo();
		$jfManager 		= JoomFishManager::getInstance();
		$contentElement = $jfManager->getContentElement($table);
		$tableclass 	= $contentElement->getTableClass();
		$primarykey		= $contentElement->getReferenceId();
		
		$table			= $contentElement->getTable();
		
		foreach ($table->Fields as $tableField)
			{
				if ($tableField->Type == "titletext") {
					
					if (strtolower($tableField->Name) != "title")
					{
						
						$titlecolumn = $db->quoteName($tableField->Name) . ' as title';					
					} else {
						
						$titlecolumn	= $db->quoteName($tableField->Name);
					}
				}
			}
		
		
		// Build up the rows for the table
		$rows = null;
		$total = 0;
		$result = new stdClass();
		
		if (!$contentElement)
		{
			$table = "content";
			$contentElement = $jfManager->getContentElement->getContentElement($table);
		}
		

		$query	= $db->getQuery(true);
		
		$query->select($db->quoteName($primarykey).' AS id,'. $titlecolumn);
		$query->from($db->quoteName('#__'.$tableclass).' AS c');

		if ($table->Filter != '')
		{
			$query->where ($table->Filter);
		}
		
		$query->where ('(c.language="*" OR c.language='. $db->quote($jfManager->getDefaultLanguage()).')');
		
		$db->setQuery($query, $limitstart, $limit);	
		$rows = $db->loadObjectList();
		
		if ($db->getErrorNum())
		{
			JError::raiseWarning(200, JTEXT::_('No valid database connection: ') . $db->stderr());
			// should not stop the page here otherwise there is no way for the user to recover
			$rows = array();
		}
		
		$count = count($rows);
		
		$result->total 	= $total;
		$result->rows	= $rows;
		
		return $result;
		
	}

}

?>
