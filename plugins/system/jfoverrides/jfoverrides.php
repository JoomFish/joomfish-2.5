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
 * $Id: jfrouter.php 248 2012-02-10 14:35:53Z geraint $
 * @package joomfish
 * @subpackage jfoverrides
 * @version 2.5
 *
*/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );


if(!JFactory::getApplication()->isAdmin()) {
	return;
}

class plgSystemJFOverrides extends JPlugin
{

	public function __construct(& $subject, $config = array())
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * System Event: onAfterInitialise
	 *
	 * Load JF Core Overrides 
	 *
	 * @return	string
	 */
	public function onAfterInitialise()
	{	
		if(!defined('JFOVERRIDES_PLUGIN_LOCATION')) define('JFOVERRIDES_PLUGIN_LOCATION', dirname(__FILE__));
		if(JFactory::getApplication()->isAdmin()) {			
			$this->_requireClassFile (JFOVERRIDES_PLUGIN_LOCATION.'/classes/language.php', 'JFormFieldLanguage');
			$this->_requireClassFile (JFOVERRIDES_PLUGIN_LOCATION.'/classes/contentlanguage.php', 'JFormFieldContentLanguage');
		} else {
			//JFactory::getApplication()->setLanguageFilter(false);
			jimport('joomla.application.menu');
			$this->_requireClassFile (JFOVERRIDES_PLUGIN_LOCATION.'/classes/menu.php', 'JMenuSite', true);
			JMenuSite::getInstance('site');
		}

	}
	
	/**
	* requireClassFile
	*
	* @param string $file
	* @param string $class
	*
	* @return Boolean
	*/
	private function _requireClassFile ($file, $class, $load = false)
    {
        if (!class_exists($class)) {
            if (file_exists($file)) {
                JLoader::register($class, $file, true);
                
                if ($load == true) {
                	JLoader::load($class);
                }
                
            } else {
                JError::raiseNotice(500, JText::_('PLG_SYSTEM_CREATE_MISSING_CLASS_FILE'.' '.$class.' '.$file), 'error');
                return false;
            }
        }
    }
}