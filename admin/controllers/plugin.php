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
 * @subpackage plugin
 *
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * The JoomFish Tasker manages the general tasks within the Joom!Fish admin interface
 *
 */
class PluginController extends JController  {

	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerTask( 'show',  'display' );
	}

	/**
	 * Standard display control structure
	 * 
	 */
	public function display($cachable = false, $urlparams = false)
	{
		// test if any plugins are installed - if not divert to installation screen
		$db = JFactory::getDBO();
		$query = 'SELECT COUNT(*)'
			. ' FROM #__extensions AS e'
			. ' WHERE e.type = '.$db->Quote("plugin").' AND e.folder = '.$db->Quote("joomfish");
			;
		$db->setQuery( $query );
		$total = $db->loadResult();
		if ($total>0){
			$link = 'index.php?option=com_plugins&view=plugins&filter_folder=joomfish';
			$msg = "";
		}
		else {
			$link = 'index.php?option=com_installer';
			$msg = JText::_( 'NO_JOOMFISH_PLUGINS_INSTALLED_YET' );
		}
		$this->setRedirect($link, $msg);
	}
	
}
