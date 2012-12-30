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

jimport( 'joomla.application.component.view');
jimport('joomla.html.pane');
JLoader::import( 'views.default.view',JOOMFISH_ADMINPATH);

/**
 * HTML View class for the WebLinks component
 *
 * @static
 * @package		Joomla
 * @subpackage	Weblinks
 * @since 1.0
 */
class HelpViewHelp extends JoomfishViewDefault
{
	/**
	 * Control Panel display function
	 *
	 * @param template $tpl
	 */
	public function display($tpl = null)
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('JOOMFISH_TITLE') . ' :: ' .JText::_( 'HELP_AND_HOWTO' ));
		JHTML::stylesheet( 'jfhelp.css', 'administrator/components/com_joomfish/assets/css/' );
				
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_( 'HELP_AND_HOWTO' ), 'help' );
		JToolBarHelper::custom( 'cpanel.show', 'joomfish', 'joomfish', 'COM_JOOMFISH_CONTROL_PANEL' , false );
		
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
		JSubMenuHelper::addEntry(JText::_( 'CONTENT_ELEMENTS' ), 'index.php?option=com_joomfish&amp;task=elements.show');
		JSubMenuHelper::addEntry(JText::_( 'HELP_AND_HOWTO' ), 'index.php?option=com_joomfish&amp;task=help.show', true);
		
		$layout = $this->getLayout();
		if (method_exists($this,$layout)){
			$this->$layout($tpl);
		}
		
		$helpov = $this->getHelpPathL('help.overview');
		$this->assignRef('helppath', $helpov);
		
		parent::display($tpl);
	}
	
	/**
	 * Method to show the information related to the project
	 * @access public
	 * @return void
	 */
	protected function information($tpl=null) {
		$document = JRequest::getVar('fileCode','');
		$this->assignRef('fileCode', $document);		
	}
	
	/**
	 * Load a template file -- This is a special implementation that tries to find the files within the distribution help
	 * dir first. There localized versions of these files can be stored!
	 *
	 * @access	public
	 * @param string $tpl The name of the template source file ...
	 * automatically searches the template paths and compiles as needed.
	 * @return string The output of the the template script.
	 */
	public function loadTemplate( $tpl = null)
	{
		global  $option;

		// clear prior output
		$this->_output = null;

		$file = isset($tpl) ? $this->_layout.'_'.$tpl : $this->_layout;
		// Add specific prefix to the file for usage within the help directry
		$file = 'help.' . $file;
		
		// clean the file name
		$file = preg_replace('/[^A-Z0-9_\.-]/i', '', $file);

		// Get Help URL
		jimport('joomla.language.help');
		$filetofind = JHelp::createURL($file, true);		
		
		// TODO: reactivate - this construct can not deal with symbolic links!
		//$this->_template = JPath::find(JPATH_ADMINISTRATOR, $filetofind);
		$fullname = JPATH_ADMINISTRATOR.DS.$filetofind;
		$this->_template = JFile::exists($fullname) ? $fullname : false;

		if ($this->_template != false)
		{
			// unset so as not to introduce into template scope
			unset($tpl);
			unset($file);

			// never allow a 'this' property
			if (isset($this->this)) {
				unset($this->this);
			}

			// start capturing output into a buffer
			ob_start();
			// include the requested template filename in the local scope
			// (this will execute the view logic).
			include $this->_template;

			// done with the requested template; get the buffer and
			// clear it.
			$this->_output = ob_get_contents();
			ob_end_clean();

			return $this->_output;
		}
		else {
			return parent::loadTemplate($tpl);
		}
	}
}
