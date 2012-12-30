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
 * @subpackage manage
 *
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * The JoomFish Tasker manages the general tasks within the Joom!Fish admin interface
 *
 */
class ManageController extends JController  {

	/**
	 * @var object reference to the currecnt view
	 * @access private
	 */
	private $_view = null;
	
	/**
	 * @var object reference to the current model
	 * @access private
	 */
	private $_model = null;
	
	/**
	 * PHP 4 constructor for the tasker
	 *
	 * @return joomfishTasker
	 */
	public function __construct( ){
		parent::__construct();
		$this->registerTask('show',  'display' );
		$this->registerTask('check',  'checkstatus' );
		$this->registerTask('copy',  'copy' );
	}
	/**
	 * Standard display control structure
	 * 
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$this->view =  $this->getView("manage");
		parent::display();
	}
	
	/**
	 * 
	 */
	public function copy() {
		$type = JRequest::getString('type', '' );
		$phase = JRequest::getInt('phase', 1 );
		$statecheck_i = JRequest::getInt('statecheck_i', -1);
		$state_catid = JRequest::getVar('state_catid', '' );
		$htmlResult = JText::_('MANAGEMENT_INTRO');
		$language_id = JRequest::getInt( 'language_id', null );
		$overwrite = JRequest::getInt( 'overwrite', 0 );
		$link = '';
		
		// get the view
		$this->_view =  $this->getView("manage");
		$this->_model =  $this->getModel('manage');

		switch ($type) {
			case 'original_language':
				$message = '';
				$session = JFactory::getSession();
				$original2languageInfo = $session->get('original2languageInfo',array());
				$original2languageInfo = $this->_model->copyOriginalToLanguage($original2languageInfo, $phase, $state_catid, $language_id, $overwrite, $message);
				$session->set('original2languageInfo', $original2languageInfo );

				if($phase == 1) {
					$langlist = JHTML::_('select.genericlist', $this->_model->getLanguageList(), 'select_language', 'id="select_language" class="inputbox" size="1"' );
					$htmlResult = $this->_view->renderCopyInformation($original2languageInfo, $message, $langlist);
				} elseif( $phase == 2 || $phase == 3 ) {
					$htmlResult = $this->_view->renderCopyProcess($original2languageInfo, $message);
					$link = 'index.php?option=com_joomfish&task=manage.copy&type=original_language&phase=' .$phase. '&language_id=' .$language_id. '&state_catid=' .$state_catid. '&overwrite=' .$overwrite .'&tmpl=component';
				} else {
					$htmlResult = $this->_view->renderCopyProcess($original2languageInfo, $message);
					$session->set('original2languageInfo', null );
				}
				break;
		}
		$this->_view->setLayout('result');
		$this->_view->assignRef('htmlResult', $htmlResult);
		$this->_view->assignRef('reload', $link);
		$this->_view->display();
	}
	
}
