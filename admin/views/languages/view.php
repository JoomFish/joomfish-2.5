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
jimport( 'joomla.filesystem.file');
jimport( 'joomla.application.component.view');
jimport('joomla.html.pane');

/**
 * HTML View class for the WebLinks component
 *
 * @static
 * @package		Joomla
 * @subpackage	Weblinks
 * @since 1.0
 */
class LanguagesViewLanguages extends JoomfishViewDefault
{
	/**
	 * Control Panel display function
	 *
	 * @param template $tpl
	 */
	public function display($tpl = null)
	{

		$document = JFactory::getDocument();
		$document->setTitle(JText::_('JOOMFISH_TITLE') . ' :: ' .JText::_( 'LANGUAGE_TITLE' ));

		// Set toolbar items for the page
		JToolBarHelper::apply('languages.apply');
		JToolBarHelper::save( 'languages.save');
		JToolBarHelper::title( JText::_( 'LANGUAGE_TITLE' ), 'language' );
		JToolBarHelper::makeDefault ('languages.setDefault');
		JToolBarHelper::deleteList('ARE_YOU_SURE_YOU_WANT_TO_DELETE_THE_SELCTED_ITEMS', 'languages.remove');
		JToolBarHelper::addNew( 'languages.add' );
		JToolBarHelper::cancel('languages.cancel', 'JTOOLBAR_CLOSE');
		JToolBarHelper::divider();
		JToolBarHelper::help( 'screen.languages', true);

		JSubMenuHelper::addEntry(JText::_( 'COM_JOOMFISH_CONTROL_PANEL' ), 'index.php?option=com_joomfish');
		JSubMenuHelper::addEntry(JText::_( 'TRANSLATION' ), 'index.php?option=com_joomfish&amp;task=translate.overview');
		if (JOOMFISH_DEVMODE == true) {
			JSubMenuHelper::addEntry(JText::_( 'ORPHANS' ), 'index.php?option=com_joomfish&amp;task=translate.orphans');
		}
		if (JOOMFISH_DEVMODE == true) {
			JSubMenuHelper::addEntry(JText::_( 'MANAGE_TRANSLATIONS' ), 'index.php?option=com_joomfish&amp;task=manage.overview');
		}
		if (JOOMFISH_DEVMODE == true) {
			JSubMenuHelper::addEntry(JText::_( 'STATISTICS' ), 'index.php?option=com_joomfish&amp;task=statistics.overview');
		}
		JSubMenuHelper::addEntry(JText::_( 'LANGUAGE_CONFIGURATION' ), 'index.php?option=com_joomfish&amp;task=languages.show', true);
		JSubMenuHelper::addEntry(JText::_( 'CONTENT_ELEMENTS' ), 'index.php?option=com_joomfish&amp;task=elements.show');
		JSubMenuHelper::addEntry(JText::_( 'HELP_AND_HOWTO' ), 'index.php?option=com_joomfish&amp;task=help.show');

		$option				= JRequest::getCmd('option', 'com_joomfish');
		$filter_state		= JFactory::getApplication()->getUserStateFromRequest( $option.'filter_state',		'filter_state',		'',				'word' );
		$filter_catid		= JFactory::getApplication()->getUserStateFromRequest( $option.'filter_catid',		'filter_catid',		0,				'int' );
		$filter_order		= JFactory::getApplication()->getUserStateFromRequest( $option.'filter_order',		'filter_order',		'l.ordering',	'cmd' );
		$filter_order_Dir	= JFactory::getApplication()->getUserStateFromRequest( $option.'filter_order_Dir',	'filter_order_Dir',	'',				'word' );
		$search				= JFactory::getApplication()->getUserStateFromRequest( $option.'search',			'search',			'',				'string' );
		$search				= JString::strtolower( $search );

		$languages	= $this->get('data');
		$defaultLanguage = $this->get('defaultLanguage');

		$this->assignRef('items', $languages);
		$this->assignRef('defaultLanguage', $defaultLanguage);
		
		$jfManager = JoomFishManager::getInstance();
		$overwriteGlobalConfig = $jfManager->getCfg('overwriteGlobalConfig');
		$this->assignRef('overwriteGlobalConfig', $overwriteGlobalConfig);
		$directory_flags = $jfManager->getCfg('directory_flags');
		$this->assignRef('directory_flags', $directory_flags);

		// state filter
		$lists['state']	= JHTML::_('grid.state',  $filter_state );

		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_order;

		// search filter
		$lists['search']= $search;
		
		$user 			= JFactory::getUser();
		$this->assignRef('user', 		$user);		
		$this->assignRef('lists',		$lists);

		JHTML::_('behavior.tooltip');
		parent::display($tpl);
	}

	/**
	 * Method displaying the config traslation layout
	 */
	public function translateConfig($tpl = null) {
		$document = JFactory::getDocument();
		$livesite = JURI::base();
		$document->addStyleSheet($livesite.'components/com_joomfish/assets/css/joomfish.css');
		JHtml::_('behavior.modal');
		JHTML::script('com_joomfish/joomfish.mootools.js', true, true);
				
		//$document->setTitle(JText::_('JOOMFISH_TITLE') . ' :: ' .JText::_( 'LANGUAGE_TITLE' ));
		$paramsField = JRequest::getVar('paramsField', '');
		$this->assignRef('paramsField',$paramsField);
		$lang_id = JRequest::getVar('langId', '');
		$this->assignRef('lang_id',$lang_id);

		parent::display($tpl);
	}

	/**
	 * Method to initialize the language depended image (flag) browser
	 * The browser is initialized with the default root path based on the Joomfish configuration
	 * @param $tpl
	 */
	public function filebrowser($tpl = null){
		$document = JFactory::getDocument();
		$livesite = JURI::base();
		$document->addStyleSheet($livesite.'components/com_joomfish/assets/css/joomfish.css');
		$document->addStyleSheet(JURI::root(true).'/media/media/css/popup-imagelist.css');
		JHtml::_('behavior.modal');
		JHTML::script('com_joomfish/joomfish.mootools.js', true, true);
				
        $jfManager = JoomFishManager::getInstance();
        $root = $jfManager->getCfg('directory_flags');
        
        $current = JRequest::getVar('current', '');
        if($current != '') {
        	$root = dirname($current);
        }
        
        // remove leading / in case it exists
        $root = preg_replace('/^\/(.*)/', "$1", $root);
        
        $flagField = JRequest::getVar('flagField', '');
        
		$folder = JRequest::getVar( 'folder', $root, 'default', 'path');
		$type = JRequest::getCmd('type', 'image');
		if(JString::trim($folder)=="") {
			$path=JPATH_SITE.DS.JPath::clean('/');
		} else {
			$path=JPATH_SITE.DS.JPath::clean($folder);
		}
		
		JPath::check($path);
		$title = JText::_( 'BROWSE_LANGUAGE_FLAGS' );
		$filter = '.jpg|png|gif|xcf|odg|bmp|jpeg';

		if (JFolder::exists($path)){
			$folderList=JFolder::folders($path);
			$filesList=JFolder::files($path, $filter);
		}

		if (!empty($folder)){
			$parent=substr($folder, 0,strrpos($folder,'/'));
		}
		else {
			$parent = '';
		}

		$this->assignRef('folders',$folderList);
		$this->assignRef('files',$filesList);
		$this->assignRef('parent',$parent);
		$this->assignRef('path',$folder);
		$this->assignRef('type',$type);
		$this->assignRef('title',$title);
		$this->assignRef('flagField', $flagField);

		parent::display($tpl);

	}
}
?>
