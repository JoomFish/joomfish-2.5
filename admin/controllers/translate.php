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
 * @subpackage translate
 *
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

JLoader::import('helpers.controllerHelper', JOOMFISH_ADMINPATH);

/**
 * The JoomFish Tasker manages the general tasks within the Joom!Fish admin interface
 *
 */
class TranslateController extends JController
{

	/** @var string		action within the task */
	var $act = null;
	/** @var array		int or array with the choosen list id */
	var $cid = null;
	/** @var string		file code */
	var $fileCode = null;
	/**
	 * @var object	reference to the Joom!Fish manager
	 * @access private
	 */
	var $_joomfishManager = null;

	/**
	 * PHP 4 constructor for the tasker
	 *
	 * @return joomfishTasker
	 */
	function __construct()
	{
		parent::__construct();
		$this->registerDefaultTask('showTranslate');

		$this->act = JRequest::getVar('act', '');
		$this->cid = JRequest::getVar('cid', array(0));
		if (!is_array($this->cid))
		{
			$this->cid = array(0);
		}
		$this->fileCode = JRequest::getVar('fileCode', '');
		$this->_joomfishManager = JoomFishManager::getInstance();

		$this->registerTask('overview', 'showTranslate');
		$this->registerTask('edit', 'editTranslation');
		$this->registerTask('apply', 'saveTranslation');
		$this->registerTask('save', 'saveTranslation');
		$this->registerTask('publish', 'publishTranslation');
		// NB the method will check on task
		$this->registerTask('unpublish', 'publishTranslation');
		$this->registerTask('remove', 'removeTranslation');
		$this->registerTask('preview', 'previewTranslation');

		$this->registerTask('orphans', 'showOrphanOverview');
		$this->registerTask('orphandetail', 'showOrphanDetail');
		$this->registerTask('removeorphan', 'removeOrphan');

		// Populate data used by controller
		$this->_catid = JFactory::getApplication()->getUserStateFromRequest('selected_catid', 'catid', '');
		$this->_select_language_id = JFactory::getApplication()->getUserStateFromRequest('selected_lang', 'select_language_id', '-1');
		$this->_language_id = JRequest::getVar('language_id', $this->_select_language_id);
		$this->_select_language_id = ($this->_select_language_id == -1 && $this->_language_id != -1) ? $this->_language_id : $this->_select_language_id;

		// Populate common data used by view
		// get the view
		$this->view = $this->getView("translate");
		$model = $this->getModel('translate');
		$this->view->setModel($model, true);

		// Assign data for view
		$this->view->assignRef('catid', $this->_catid);
		$this->view->assignRef('select_language_id', $this->_select_language_id);
		$this->view->assignRef('task', $this->task);
		$this->view->assignRef('act', $this->act);

	}

	/**
	 * presenting the translation dialog
	 *
	 */
	function showTranslate()
	{

		// If direct translation then close the modal window
		if ($direct = intval(JRequest::getVar("direct", 0)))
		{
			$this->modalClose($direct);
			return;
		}

		JoomfishControllerHelper::setupContentElementCache();
		if (!JoomfishControllerHelper::testSystemBotState()) {
			echo "<div style='font-size:16px;fontF-weight:bold;color:red'>" . JText::_('JF_DATABASE_PLUGIN_NOT_PUBLISHED') . "</div>";
		}


		$this->showTranslationOverview($this->_select_language_id, $this->_catid);

	}

	/** Presentation of the content's that must be translated
	 */
	function showTranslationOverview($language_id, $catid)
	{
		$db = JFactory::getDBO();

		$limit = JFactory::getApplication()->getUserStateFromRequest('global.list.limit', 'limit', JFactory::getApplication()->getCfg('list_limit'), 'int');
		$limitstart = JFactory::getApplication()->getUserStateFromRequest("view{com_joomfish}limitstart", 'limitstart', 0);
		$search = JFactory::getApplication()->getUserStateFromRequest("search{com_joomfish}", 'search', '');
		$search = $db->getEscaped(trim(strtolower($search)));

		// Build up the rows for the table
		$rows = null;
		$total = 0;
		$filterHTML = array();
		if ($language_id != -1 && isset($catid) && $catid != "")
		{
			$contentElement = $this->_joomfishManager->getContentElement($catid);
			if (!$contentElement)
			{
				$catid = "content";
				$contentElement = $this->_joomfishManager->getContentElement($catid);
			}
			jimport('joomfish.translationfilter.translationfilter');
			$tranFilters = translationFilter::getTranslationFilters($catid, $contentElement);

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
					$filterHTML[$tranFilter->filterType] = $afilterHTML;
			}
		}

		// Create the pagination object
		jimport('joomla.html.pagination');
		$pageNav = new JPagination($total, $limitstart, $limit);

		// get list of element names
		$elementNames[] = JHTML::_('select.option', '', JText::_('PLEASE_SELECT'));
		//$elementNames[] = JHTML::_('select.option',  '-1', '- All Content elements' );
		// force reload to make sure we get them all
		$elements = $this->_joomfishManager->getContentElements(true);
		foreach ($elements as $key => $element)
		{
			$elementNames[] = JHTML::_('select.option', $key, $element->Name);
		}
		$clist = JHTML::_('select.genericlist', $elementNames, 'catid', 'class="inputbox" size="1" onchange="if(document.getElementById(\'select_language_id\').value>=0) document.adminForm.submit();"', 'value', 'text', $catid);

		// get the view
		$this->view = $this->getView("translate", "html");

		// Set the layout
		$this->view->setLayout('default');

		// Assign data for view - should really do this as I go along
		$this->view->assignRef('rows', $rows);
		$this->view->assignRef('search', $search);
		$this->view->assignRef('pageNav', $pageNav);
		$this->view->assignRef('clist', $clist);
		$this->view->assignRef('language_id', $language_id);
		$this->view->assignRef('filterlist', $filterHTML);
		$this->view->assignRef('language_id', $language_id);

		$this->view->display();
		//TranslateViewTranslate::showTranslationOverview( $rows, $search, $pageNav, $langlist, $clist, $catid ,$language_id,$filterHTML );

	}

	/** Details of one content for translation
	 */
	// DONE
	function editTranslation()
	{
		$cid = JRequest::getVar('cid', array(0));
		$translation_id = 0;
		if (strpos($cid[0], '|') >= 0)
		{
			list($translation_id, $contentid, $language_id) = explode('|', $cid[0]);
			$select_language_id = ($this->_select_language_id == -1 && $language_id != -1) ? $language_id : $this->_select_language_id;
		}
		else
		{
			$select_language_id = -1;
		}
		$catid = $this->_catid;

		$user = JFactory::getUser();
		$db = JFactory::getDBO();

		$translationObject = null;


		if (isset($catid) && $catid != "")
		{
			$contentElement = $this->_joomfishManager->getContentElement($catid);
			$translationClass = $contentElement->getTranslationObjectClass();
			$translationObject = new $translationClass( $language_id, $contentElement );
			$translationObject->loadFromContentID($contentid);
		}

		// fail if checked out not by 'me'
		if ($translationObject->checked_out && $translationObject->checked_out <> $user->id)
		{
			JFactory::getApplication()->redirect("index.php?option=option=com_joomfish&task=translate",
					"The content item $translationObject->title is currently being edited by another administrator");
		}

		// get existing filters so I can remember them!
		jimport('joomfish.translationfilter.translationfilter');
		$tranFilters = translationFilter::getTranslationFilters($catid, $contentElement);

		// get the view
		$this->view = $this->getView("translate");

		// Set the layout
		$this->view->setLayout('edit');

		// Need to load com_config language strings!
		$lang = JFactory::getLanguage();
		$lang->load('com_config');

		// Assign data for view - should really do this as I go along
		$this->view->assignRef('translationObject', $translationObject);
		$this->view->assignRef('tranFilters', $tranFilters);
		$this->view->assignRef('select_language_id', $select_language_id);
		$filterlist = array();
		$this->view->assignRef('filterlist', $filterlist);

		$this->view->display();
	}

	/** Saves the information of one translation
	 */
	// DONE
	function saveTranslation()
	{
		$catid = $this->_catid;
		$select_language_id = $this->_select_language_id;
		$language_id = $this->_language_id;

		$id = JRequest::getVar('reference_id', null);
		$jfc_id = JRequest::getVar('jfc_id ', null);

		$translationObject = null;
		if (isset($catid) && $catid != "")
		{
			$contentElement = $this->_joomfishManager->getContentElement($catid);
			$translationClass = $contentElement->getTranslationObjectClass();
			$translationObject = new $translationClass( $language_id, $contentElement );

			// get's the config settings on how to store original files
			$storeOriginalText = ($this->_joomfishManager->getCfg('storageOfOriginal') == 'md5') ? false : true;
			$translationObject->bind($_POST, '', '', true, $storeOriginalText);
			$success = $translationObject->store();
			if ( $success)
			{
				JPluginHelper::importPlugin('joomfish');
				$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger('onAfterTranslationSave', array($_POST));
				$this->view->message = JText::_('TRANSLATION_SAVED');
			}
			else
			{
				$this->view->message = JText::_('ERROR_SAVING_TRANSLATION');
			}

			// Clear Translation Cache
			$db = JFactory::getDBO();
			$lang = new TableJFLanguage($db);
			$lang->load($language_id);
			$cache = $this->_joomfishManager->getCache($lang->code);
			$cache->clean();
		}
		else
		{
			$this->view->message = JText::_('Cannot save - invalid catid');
		}

		if ($this->task == "apply")
		{
			$cid = $translationObject->id . "|" . $id . "|" . $language_id;
			JRequest::setVar('cid', array($cid));
			//$this->editTranslation();
			$this->setRedirect( "index.php?option=com_joomfish&task=translate.edit&cid[]=$cid",$this->view->message);
		}
		else
		{
			// redirect to overview
			$this->setRedirect( "index.php?option=com_joomfish&task=translate.overview",$this->view->message);
		}

	}

	/**
	 * method to remove a translation
	 */
	function removeTranslation()
	{
		$this->cid = JRequest::getVar('cid', array(0));
		if (!is_array($this->cid))
		{
			$this->cid = array(0);
		}


		$model = $this->view->getModel();
		$model->removeTranslation($this->_catid, $this->cid);
		// redirect to overview
		$this->showTranslate();

	}

	/**
	 * Reload all translations and publish/unpublish them
	 */
	// DONE
	function publishTranslation()
	{
		$catid = $this->_catid;
		$publish = $this->task == "publish" ? 1 : 0;
		$cid = JRequest::getVar('cid', array(0));
		$model = $this->view->getModel();
		if (strpos($cid[0], '|') >= 0)
		{
			list($translation_id, $contentid, $language_id) = explode('|', $cid[0]);
		}
		foreach ($cid as $cid_row)
		{
			list($translation_id, $contentid, $language_id) = explode('|', $cid_row);

			$contentElement = $this->_joomfishManager->getContentElement($catid);
			$translationClass = $contentElement->getTranslationObjectClass();
			$translationObject = new $translationClass( $language_id, $contentElement );
			$translationObject->loadFromContentID($contentid);
			if ($translationObject->state >= 0)
			{
				$translationObject->setPublished($publish);
				// This is not saving an updated translation so pass a false here
				$translationObject->store(false);
				$model->setState('message', $publish ? JText::_('TRANSLATION_PUBLISHED') : JText::_('TRANSLATION_PUBLISHED'));
			}
		}

		// redirect to overview
		$this->showTranslate();

	}

	/**
	 * Previews content translation
	 *
	 */
	function previewTranslation()
	{
		// get the view
		$this->view = $this->getView("translate");

		// Set the layout
		$this->view->setLayout('preview');

		// Assign data for view - should really do this as I go along
		//$this->view->assignRef('rows'   , $rows);
		$this->view->display();

	}

	/**
	 * show original value in an IFrame - for form safety
	 *
	 */
	function originalValue()
	{
		$cid = trim(JRequest::getVar('cid', ""));
		$language_id = JRequest::getInt('lang', 0);
		if ($cid == "")
		{
			JError::raiseWarning(200, JText::_('INVALID_PARAMATERS'));
			return;
		}
		$translation_id = 0;
		$contentid = intval($cid);
		$catid = $this->_catid;

		$user = JFactory::getUser();
		$db = JFactory::getDBO();

		$translationObject = null;

		if (isset($catid) && $catid != "")
		{
			$contentElement = $this->_joomfishManager->getContentElement($catid);
			$translationClass = $contentElement->getTranslationObjectClass();
			$translationObject = new $translationClass( $language_id, $contentElement );
			$translationObject->loadFromContentID($contentid);
		}

		$fieldname = JRequest::getString('field', '');

		// get the view
		$this->view = $this->getView('translate');

		// Set the layout
		$this->view->setLayout('originalvalue');

		// Assign data for view - should really do this as I go along
		$this->view->assignRef('translationObject', $translationObject);
		$this->view->assignRef('field', $fieldname);
		$this->view->display();

	}

	/** Presentation of translations that have been orphaned
	 */
	function showOrphanOverview()
	{
		$language_id = $this->_language_id;
		$catid = $this->_catid;

		$db = JFactory::getDBO();


		$limit = JFactory::getApplication()->getUserStateFromRequest('global.list.limit', 'limit', JFactory::getApplication()->getCfg('list_limit'), 'int');
		$limitstart = JFactory::getApplication()->getUserStateFromRequest("view{com_joomfish}limitstart", 'limitstart', 0);
		$search = JFactory::getApplication()->getUserStateFromRequest("search{com_joomfish}", 'search', '');
		$search = $db->getEscaped(trim(strtolower($search)));

		$tranFilters = array();
		$filterHTML = array();

		// Build up the rows for the table
		$rows = null;
		$total = 0;
		if (isset($catid) && $catid != "")
		{
			$contentElement = $this->_joomfishManager->getContentElement($catid);

			$db->setQuery($contentElement->createOrphanSQL($language_id, null, $limitstart, $limit, $tranFilters));
			$rows = $db->loadObjectList();
			if ($db->getErrorNum())
			{
				JError::raiseError(200, JTEXT::_('No valid database connection: ') . $db->stderr());
				return false;
			}

			$total = count($rows);

			for ($i = 0; $i < count($rows); $i++)
			{
				$rows[$i]->state = null;
				$rows[$i]->title = $rows[$i]->original_text;
				if (is_null($rows[$i]->title))
				{
					$rows[$i]->title = JText::_('ORIGINAL_MISSING');
				}
				$rows[$i]->checked_out = false;
			}
		}

		jimport('joomla.html.pagination');
		$pageNav = new JPagination($total, $limitstart, $limit);

		// get list of active languages
		$langlist = "";

		$langOptions[] = JHTML::_('select.option', '-1', JText::_('SELECT_LANGUAGE'));
		//$langOptions[] = JHTML::_('select.option',  '-2', JText::_('SELECT_NOTRANSLATION') );

		$langActive = $this->_joomfishManager->getLanguages(false);  // all languages even non active once

		if (count($langActive) > 0)
		{
			foreach ($langActive as $language)
			{
				$langOptions[] = JHTML::_('select.option', $language->id, $language->title);
			}
		}
		$langlist = JHTML::_('select.genericlist', $langOptions, 'select_language_id', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $language_id);

		// get list of element names
		$elementNames[] = JHTML::_('select.option', '', JText::_('PLEASE_SELECT'));
		//$elementNames[] = JHTML::_('select.option',  '-1', '- All Content elements' );
		$elements = $this->_joomfishManager->getContentElements(true);
		foreach ($elements as $key => $element)
		{
			$elementNames[] = JHTML::_('select.option', $key, $element->Name);
		}
		$clist = JHTML::_('select.genericlist', $elementNames, 'catid', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $catid);

		// get the view
		$this->view = $this->getView("translate");

		// Set the layout
		$this->view->setLayout('orphans');

		// Assign data for view - should really do this as I go along
		$this->view->assignRef('rows', $rows);
		$this->view->assignRef('search', $search);
		$this->view->assignRef('pageNav', $pageNav);
		$this->view->assignRef('langlist', $langlist);
		$this->view->assignRef('clist', $clist);
		$this->view->assignRef('language_id', $language_id);
		$this->view->assignRef('filterlist', $filterHTML);
		$this->view->display();
		//HTML_joomfish::showOrphanOverview( $rows, $search, $pageNav, $langlist, $clist, $catid ,$language_id,$filterHTML );

	}

	/**
	 * method to show orphan translation details
	 *
	 * @param unknown_type $jfc_id
	 * @param unknown_type $contentid
	 * @param unknown_type $tablename
	 * @param unknown_type $lang
	 */
	function showOrphanDetail()
	{
		$jfc_id = JRequest::getVar('jfc_id ', null);
		$cid = JRequest::getVar('cid', array(0));
		if (strpos($cid[0], '|') >= 0)
		{
			list($translation_id, $contentid, $language_id) = explode('|', $cid[0]);
		}
		$contentElement = $this->_joomfishManager->getContentElement($this->_catid);
		$tablename = $contentElement->getTableName();

		$db = JFactory::getDBO();

		// read details of orphan translation
		//$sql = "SELECT * FROM #__jf_content WHERE id=$mbfc_id AND reference_id=$contentid AND reference_table='".$tablename."'";
		$sql = "SELECT * FROM #__jf_content WHERE reference_id=$contentid AND language_id='" . $language_id . "' AND reference_table='" . $tablename . "'";
		$db->setQuery($sql);
		$rows = null;
		$rows = $db->loadObjectList();

		// get the view
		$this->view = $this->getView("translate");

		// Set the layout
		$this->view->setLayout('orphandetail');
		// Assign data for view - should really do this as I go along
		$this->view->assignRef('rows', $rows);
		$this->view->assignRef('tablename', $tablename);
		$this->view->display();
		//HTML_joomfish::showOrphan($rows, $tablename);

	}

	/**
	 * method to remove orphan translation
	 */
	public function removeOrphan()
	{
		$this->cid = JRequest::getVar('cid', array(0));
		if (!is_array($this->cid))
		{
			$this->cid = array(0);
		}

		$model = $this->view->getModel();
		$model->removeTranslation($this->_catid, $this->cid);

		$this->view->message = JText::_('Orphan Translation(s) deleted');
		// redirect to overview
		$this->showOrphanOverview();

	}

	function modalClose($linktype)
	{

		@ob_end_clean();
		switch ($linktype) {
			case 1:
			default:
?>
				<script language="javascript" type="text/javascript">
					window.parent.SqueezeBox.close();
<?php
				if ($this->task == "save")
				{
					echo "alert('" . JText::_('TRANSLATION_SAVED') . "');";
				}
?>
				</script>
<?php
				break;
			case 2:
?>
				<script language="javascript" type="text/javascript">
					window.close();
<?php
				if ($this->task == "save")
				{
					echo "alert('" . JText::_('TRANSLATION_SAVED') . "');";
				}
?>
				</script>
<?php
				break;
		}
		exit();

	}
	
	/** Presentation of the content's that must be translated
	 */
	function originallist()
	{

			// get the view
			$this->view = $this->getView("translate", "html");
	
			// Set the layout
			$this->view->setLayout('modal');
	
			parent::display();
	}

}
