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
 * @subpackage Views
 *
*/
// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

JLoader::import( 'views.default.view',JOOMFISH_ADMINPATH);

/**
 * View class for translation overview
 *
 * @static
 * @package		Joom!Fish
 * @subpackage	translation
 * @since 2.0
 */
class TranslateViewTranslate extends JoomfishViewDefault
{
	/**
	 * Setting up special general attributes within this view
	 * These attributes are independed of the specifc view
	 */
	private function _initialize($layout="overview") {
		// get list of active languages
		$langOptions[] = JHTML::_('select.option',  '-1', JText::_( 'SELECT_LANGUAGE' ) );
		// Get data from the model
		$langActive = $this->get('Languages');		// all languages even non active once
		$defaultLang = $this->get('DefaultLanguage');
		$params = JComponentHelper::getParams('com_joomfish');
		$showDefaultLanguageAdmin = $params->get("showDefaultLanguageAdmin", false);

		if ( count($langActive)>0 ) {
			foreach( $langActive as $language )
			{
				if($language->code != $defaultLang || $showDefaultLanguageAdmin) {
					$langOptions[] = JHTML::_('select.option',  $language->lang_id, $language->title );
				}
			}
		}
		if ($layout == "overview" || $layout == "default"){
			$langlist = JHTML::_('select.genericlist', $langOptions, 'select_language_id', 'class="inputbox" size="1" onchange="if(document.getElementById(\'catid\').value.length>0) document.adminForm.submit();"', 'value', 'text', $this->select_language_id );
		}
		else {
			$confirm="";

			$langlist = JHTML::_('select.genericlist', $langOptions, 'language_id', 'class="inputbox" size="1" '.$confirm, 'value', 'text', $this->select_language_id );
		}
		$this->assignRef('langlist'   , $langlist);
		
		$googleApikey =  $params->get("google_translate_key", "");
		
		$this->assignRef('googleApikey'   , $googleApikey);
		
		
		$table = JRequest::getWord('table', null);
		$this->assignRef('table'   , $table);
	}
	/**
	 * Control Panel display function
	 *
	 * @param template $tpl
	 */
	public function display($tpl = null)
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('JOOMFISH_TITLE') . ' :: ' .JText::_('TITLE_TRANSLATION'));

		// Set  page title
		JToolBarHelper::title( JText::_( 'TITLE_TRANSLATION' ), 'jftranslations' );

		$layout = $this->getLayout();

		$this->_initialize($layout);
		if (method_exists($this,$layout)){
			$this->$layout($tpl);
		} else {
			$this->overview($tpl);
		}

		JHTML::_('behavior.tooltip');
		parent::display($tpl);
	}


	protected function overview($tpl = null)
	{
		// browser title
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('JOOMFISH_TITLE') . ' :: ' .JText::_( 'TRANSLATE' ));

		// set page title
		JToolBarHelper::title( JText::_( 'TRANSLATE' ), 'translation' );

		// Set toolbar items for the page
		JToolBarHelper::publish("translate.publish");
		JToolBarHelper::unpublish("translate.unpublish");
		JToolBarHelper::editList("translate.edit");
		JToolBarHelper::deleteList(JText::_( 'ARE_YOU_SURE_YOU_WANT_TO_DELETE_THIS_TRANSLATION'), "translate.remove");
		JToolBarHelper::custom( 'cpanel.show', 'joomfish', 'joomfish', 'COM_JOOMFISH_CONTROL_PANEL', false );
		JToolBarHelper::divider();
		JToolBarHelper::help( 'screen.translate.overview', true);

		JSubMenuHelper::addEntry(JText::_( 'COM_JOOMFISH_CONTROL_PANEL' ), 'index.php?option=com_joomfish');
		JSubMenuHelper::addEntry(JText::_( 'TRANSLATION' ), 'index.php?option=com_joomfish&amp;task=translate.overview', true);
		if (JOOMFISH_DEVMODE == true) {
			JSubMenuHelper::addEntry(JText::_( 'ORPHANS' ), 'index.php?option=com_joomfish&amp;task=translate.orphans');
		}
		if (JOOMFISH_DEVMODE == true) {
			JSubMenuHelper::addEntry(JText::_( 'MANAGE_TRANSLATIONS' ), 'index.php?option=com_joomfish&amp;task=manage.overview');
		}
		if (JOOMFISH_DEVMODE == true) {
			JSubMenuHelper::addEntry(JText::_( 'STATISTICS' ), 'index.php?option=com_joomfish&amp;task=statistics.overview');
		}
		JSubMenuHelper::addEntry(JText::_( 'LANGUAGE_CONFIGURATION' ), 'index.php?option=com_joomfish&amp;task=languages.show');
		JSubMenuHelper::addEntry(JText::_( 'CONTENT_ELEMENTS' ), 'index.php?option=com_joomfish&amp;task=elements.show');
		JSubMenuHelper::addEntry(JText::_( 'HELP_AND_HOWTO' ), 'index.php?option=com_joomfish&amp;task=help.show');
	}

	protected function edit($tpl = null)
	{
		// browser title
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('JOOMFISH_TITLE') . ' :: ' .JText::_( 'TRANSLATE' ));

		// set page title
		JToolBarHelper::title( JText::_( 'TRANSLATE' ), 'translation' );

		// Set toolbar items for the page
		if (JRequest::getVar("catid","")=="content"){
			//JToolBarHelper::preview('index.php?option=com_joomfish',true);
			$bar =  JToolBar::getInstance('toolbar');
			// Add a special preview button by hand
			$live_site = JURI::base();
			$bar->appendButton( 'Popup', 'preview', 'Preview', JRoute::_("index.php?option=com_joomfish&task=translate.preview&tmpl=component"), "800","550");
		}
		JToolBarHelper::save("translate.save");
		JToolBarHelper::apply("translate.apply");
		JToolBarHelper::cancel("translate.overview");
		JToolBarHelper::help( 'screen.translate.edit', true);

		JRequest::setVar('hidemainmenu',1);
	}

	protected function orphans($tpl = null)
	{
		// browser title
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('JOOMFISH_TITLE') . ' :: ' .JText::_( 'CLEANUP_ORPHANS' ));

		// set page title
		JToolBarHelper::title( JText::_( 'CLEANUP_ORPHANS' ), 'orphan' );

		// Set toolbar items for the page
		JToolBarHelper::deleteList(JText::_( 'ARE_YOU_SURE_YOU_WANT_TO_DELETE_THIS_TRANSLATION' ), "translate.removeorphan");
		JToolBarHelper::custom( 'cpanel.show', 'joomfish', 'joomfish', 'COM_JOOMFISH_CONTROL_PANEL', false );
		JToolBarHelper::help( 'screen.translate.orphans', true);

		JSubMenuHelper::addEntry(JText::_( 'COM_JOOMFISH_CONTROL_PANEL' ), 'index.php?option=com_joomfish', false);
		JSubMenuHelper::addEntry(JText::_( 'TRANSLATION' ), 'index.php?option=com_joomfish&amp;task=translate.overview', false);
		JSubMenuHelper::addEntry(JText::_( 'ORPHANS' ), 'index.php?option=com_joomfish&amp;task=translate.orphans', true);
		JSubMenuHelper::addEntry(JText::_( 'MANAGE_TRANSLATIONS' ), 'index.php?option=com_joomfish&amp;task=manage.overview', false);
		JSubMenuHelper::addEntry(JText::_( 'STATISTICS' ), 'index.php?option=com_joomfish&amp;task=statistics.overview', false);
		JSubMenuHelper::addEntry(JText::_( 'LANGUAGE_CONFIGURATION' ), 'index.php?option=com_joomfish&amp;task=languages.show', false);
		JSubMenuHelper::addEntry(JText::_( 'CONTENT_ELEMENTS' ), 'index.php?option=com_joomfish&amp;task=elements.show', false);
		JSubMenuHelper::addEntry(JText::_( 'HELP_AND_HOWTO' ), 'index.php?option=com_joomfish&amp;task=help.show', false);
	}

	protected function orphandetail($tpl = null)
	{
		// browser title
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('JOOMFISH_TITLE') . ' :: ' .JText::_( 'CLEANUP_ORPHANS' ));

		// set page title
		JToolBarHelper::title( JText::_( 'CLEANUP_ORPHANS' ), 'orphan' );

		// Set toolbar items for the page
		//JToolBarHelper::deleteList(JText::_( 'ARE_YOU_SURE_YOU_WANT_TO_DELETE_THIS_TRANSLATION' ), "translate.removeorphan");
		JToolBarHelper::back();
		JToolBarHelper::custom( 'cpanel.show', 'joomfish', 'joomfish', 'COM_JOOMFISH_CONTROL_PANEL', false );
		JToolBarHelper::help( 'screen.translate.orphans', true);

		// hide the sub menu
		// This won't work
		$submenu =  JModuleHelper::getModule("submenu");
		$submenu->content = "\n";

		JRequest::setVar('hidemainmenu',1);
	}

	protected function preview($tpl = null)
	{
		// hide the sub menu
		$this->_hideSubmenu();
		parent::display($tpl);

	}
	
	protected function modal($tpl = null)
	{
		// hide the sub menu
		$this->_hideSubmenu();
		
		$table 		= JRequest::getWord('table');	
		$limit 		= JFactory::getApplication()->getUserStateFromRequest('global.list.limit', 'limit', JFactory::getApplication()->getCfg('list_limit'), 'int');
		$limitstart = JFactory::getApplication()->getUserStateFromRequest("view{com_joomfish}limitstart", 'limitstart', 0);
		$data		= $this->getModel('translate')->getSimpleOriginalItemList($table, $limitstart, $limit);
		
		// Create the pagination object
		jimport('joomla.html.pagination');
		$pageNav = new JPagination($data->total, $limitstart, $limit);
		

		// Assign data for view - should really do this as I go along
		$this->assignRef('rows', $data->rows);
		$this->assignRef('pageNav', $pageNav);
			
	
	
	}
	
}
