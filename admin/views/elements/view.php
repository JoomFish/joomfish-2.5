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
 * HTML View class for the WebLinks component
 *
 * @static
 * @package		Joomla
 * @subpackage	Weblinks
 * @since 1.0
 */
class ElementsViewElements extends JoomfishViewDefault 
{
	function display($tpl = null)
	{
		$document = JFactory::getDocument();
		// browser title
		$document->setTitle(JText::_('JOOMFISH_TITLE') . ' :: ' .JText::_( 'CONTENT_ELEMENTS' ));
		// set page title
		JToolBarHelper::title( JText::_( 'CONTENT_ELEMENTS' ), 'extension' );
		
		$layout = $this->getLayout();
		if (method_exists($this,$layout)){
			$this->$layout($tpl);
		} else {
			$this->overview($tpl);
		}
		parent::display($tpl);
	}

	function overview($tpl = null) {
		// Set toolbar items for the page
		JToolBarHelper::custom("elements.installer","extension","extension", JText::_( 'INSTALL' ),false);
		JToolBarHelper::custom("elements.detail","preview","preivew", JText::_( 'DETAIL' ),true);
		JToolBarHelper::deleteList(JText::_( 'ARE_YOU_SURE_YOU_WANT_TO_DELETE_THIS_CE_FILE' ), "elements.remove");
		JToolBarHelper::custom( 'cpanel.show', 'joomfish', 'joomfish', 'COM_JOOMFISH_CONTROL_PANEL' , false );
		JToolBarHelper::divider();
		JToolBarHelper::help( 'screen.elements', true);
		
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
		JSubMenuHelper::addEntry(JText::_( 'LANGUAGE_CONFIGURATION' ), 'index.php?option=com_joomfish&amp;task=languages.show');
		JSubMenuHelper::addEntry(JText::_( 'CONTENT_ELEMENTS' ), 'index.php?option=com_joomfish&amp;task=elements.show', true);
		JSubMenuHelper::addEntry(JText::_( 'HELP_AND_HOWTO' ), 'index.php?option=com_joomfish&amp;task=help.show');
	}
	
	function edit($tpl = null)
	{
		// Set toolbar items for the page
		JToolBarHelper::back();
		JToolBarHelper::custom( 'cpanel.show', 'joomfish', 'joomfish', 'COM_JOOMFISH_CONTROL_PANEL' , false );
		JToolBarHelper::help( 'screen.elements', true);

		// hide the sub menu
		$this->_hideSubmenu();		
	}	

	function installer($tpl = null)
	{
		// browser title
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('JOOMFISH_TITLE') . ' :: ' .JText::_( 'CONTENT_ELEMENT_INSTALLER' ));
		
		// set page title
		JToolBarHelper::title( JText::_('JOOMFISH_TITLE') .' :: '. JText::_( 'CONTENT_ELEMENT_INSTALLER' ), 'fish' );

		// Set toolbar items for the page
		JToolBarHelper::custom( 'elements.show', 'back', 'back', JText::_( 'BACK' ), false );
		JToolBarHelper::deleteList(JText::_( 'ARE_YOU_SURE_YOU_WANT_TO_DELETE_THIS_CE_FILE' ), "elements.remove_install");
		JToolBarHelper::custom( 'cpanel.show', 'joomfish', 'joomfish', 'COM_JOOMFISH_CONTROL_PANEL' , false );
		JToolBarHelper::help( 'screen.elements', true);

		// hide the sub menu
		$this->_hideSubmenu();
	}	
}
