<?php
/**
 * Joom!Fish - Multi Lingual extention and translation manager for Joomla!
 * Copyright (C) 2003 - 2011, Think Network GmbH, Munich
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
 * $Id: help.php 226 2011-05-27 07:29:41Z alex $
 * @package joomfish
 * @subpackage help
 *
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * The JoomFish Tasker manages the general tasks within the Joom!Fish admin interface
 *
 */
class HelpController extends JController  {
	/**
	 * Joom!Fish Controler for the Control Panel
	 * @param array		configuration
	 * @return joomfishTasker
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerTask( 'show',  'display' );
		$this->registerTask('postInstall', 'postInstall');
		$this->registerTask('information', 'information');
	}

	/**
	 * Standard display control structure
	 * 
	 */
	public function display( )
	{
		$this->view =  $this->getView("help");
		parent::display();
	}
	
	public function cancel()
	{
		$this->setRedirect( 'index.php?option=com_joomfish' );
	}
	
	public function postinstall() {
		// get the view
		$this->view =  $this->getView("help");

		// Set the layout
		$this->view->setLayout('postinstall');
		$this->view->display();
	}
	
	public function information() {
		// get the view
		$this->view =  $this->getView("help");

		// Set the layout
		$this->view->setLayout('information');
		$this->view->display();
	}
}
?>
