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
 * $Id: translate.php 226 2012-02-10 07:29:41Z alex $
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
	protected $_jfManager;
	
	public function __construct() {
		$this->_jfManager = JoomFishManager::getInstance();
		parent::__construct();
	}

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
		return $this->_jfManager->getLanguages(false);

	}
	
	
	/** 
	 * get a list of content's that must be translated
	 * @return object result
	*/
	public function getTranslations( $language_id, $catid, $limit, $limitstart, $search )
	{

	$db = JFactory::getDBO();
	$result = new stdClass();
	
	// Build up the rows for the table
	$rows = null;
	$total = 0;
	$result->filterHTML = array();
	if ($language_id != -1 && isset($catid) && $catid != "")
	{
		$contentElement = $this->_jfManager->getContentElement($catid);
		if (!$contentElement)
		{
			$catid = "content";
			$contentElement = $this->_jfManager->getContentElement($catid);
		}
		JLoader::import('models.TranslationFilter', JOOMFISH_ADMINPATH);
		$tranFilters = getTranslationFilters($catid, $contentElement);
	
		$total = $contentElement->countReferences($language_id, $tranFilters);
	
		if ($total < $limitstart)
		{
			$limitstart = 0;
		}
	
		$db->setQuery($contentElement->createContentSQL($language_id, null, $limitstart, $limit, $tranFilters));
		$rows = $db->loadObjectList();
		if ($db->getErrorNum())
		{
			JError::raiseWarning(200, JTEXT::_('No valid database connection: ') . $db->stderr());
			// should not stop the page here otherwise there is no way for the user to recover
			$rows = array();
		}
	
		// Manipulation of result based on further information
		for ($i = 0; $i < count($rows); $i++)
		{
			$translationClass = $contentElement->getTranslationObjectClass();
			$translationObject = new $translationClass( $language_id, $contentElement );
			$translationObject->readFromRow($rows[$i]);
			$rows[$i] = $translationObject;
		}
	
		foreach ($tranFilters as $tranFilter)
		{
			$afilterHTML = $tranFilter->createFilterHTML();
			if (isset($afilterHTML))
			$result->filterHTML[$tranFilter->filterType] = $afilterHTML;
		}
		}
		
		$result->rows = &$rows;
		$result->total = $total;
		
		return $result;
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

			$contentElement = $this->_jfManager->getContentElement($catid);
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
				$contentElement = $this->_jfManager->getContentElement($catid);
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
	 * Get orphans list
	 * @return object $rows
	 */
	public function getOrphans($language_id, $limitstart, $limit, $tranFilters) {
			
			$data 		= new stdClass();
			$db 		= JFactory::getDBO();
			
			$data->total = 0;
			$contentElement = $this->_jfManager->getContentElement($catid);
			$db->setQuery($contentElement->createOrphanSQL($language_id, null, $limitstart, $limit, $tranFilters));
			$data->rows = $db->loadObjectList();
			
			if ($db->getErrorNum())
			{
				JError::raiseError(200, JTEXT::_('No valid database connection: ') . $db->stderr());
				return false;
			}

			$data->total = count($data->rows);

			for ($i = 0; $i < count($data->rows); $i++)
			{
				$data->rows[$i]->state = null;
				$data->rows[$i]->title = $data->rows[$i]->original_text;
				if (is_null($data->rows[$i]->title))
				{
					$data->rows[$i]->title = JText::_('ORIGINAL_MISSING');
				}
				$data->rows[$i]->checked_out = false;
			}
			
			return $data;
	}
	
	/*
	 * Get details for orphan
	 * @return object $rows
	 */
	public function getOrphanDetail($contentid=null, $language_id=null, $tablename) {
		
		$db = JFactory::getDBO();

		// read details of orphan translation
		//$sql = "SELECT * FROM #__jf_content WHERE id=$mbfc_id AND reference_id=$contentid AND reference_table='".$tablename."'";
		$sql = "SELECT * FROM #__jf_content WHERE reference_id=$contentid AND language_id='" . $language_id . "' AND reference_table='" . $tablename . "'";
		$db->setQuery($sql);
		$rows = null;
		$rows = $db->loadObjectList();
		
		return $rows;
	}

}

?>
