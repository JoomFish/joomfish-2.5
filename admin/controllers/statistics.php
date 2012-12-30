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
 * @subpackage statistics
 *
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * The JoomFish Tasker manages the general tasks within the Joom!Fish admin interface
 *
 */
class StatisticsController extends JController  {

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
	}
	/**
	 * Standard display control structure
	 * 
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$this->view =  $this->getView("statistics");
		parent::display();
	}

	/**
	 * 
	 */
	public function checkstatus() {
		$type = JRequest::getString('type', '' );
		$phase = JRequest::getInt('phase', 1 );
		$statecheck_i = JRequest::getInt('statecheck_i', -1);
		$htmlResult = JText::_('MANAGEMENT_INTRO');
		$link = '';
		// get the view
		$this->_view =  $this->getView("statistics");
		$this->_model =  $this->getModel('statistics');

		switch ($type) {
			case 'translation_status':
				$message = '';
				$session = JFactory::getSession();
				$translationStatus = $session->get('translationState',array());
				$translationStatus = $this->_model->testTranslationStatus($translationStatus, $phase, $statecheck_i, $message);
				$session->set('translationState', $translationStatus );

				$htmlResult = $this->_view->renderTranslationStatusTable($translationStatus, $message);
				if( $phase<=3 ) {
					$link = 'index.php?option=com_joomfish&task=statistics.check&type=translation_status&phase=' .$phase .'&tmpl=component';

					if( $statecheck_i > -1) {
						$link .= '&statecheck_i='.$statecheck_i;
					}
				} else {
					$session->set('translationState', null );
				}
				break;

			case 'original_status':
				$message = '';
				$session = JFactory::getSession();
				$originalStatus = $session->get('originalStatus', array());
				$langCodes = array();
				$jfManager = JoomFishManager::getInstance();
				$languages = $jfManager->getLanguages(false);
				foreach ($languages as $lang) {
					$langCodes[] = $lang->getLanguageCode();
				}

				$originalStatus = $this->_model->testOriginalStatus($originalStatus, $phase, $statecheck_i, $message, $languages);
				$session->set('originalStatus', $originalStatus );
				$htmlResult = $this->_view->renderOriginalStatusTable($originalStatus, $message, $langCodes);

				if( $phase<=2 ) {
					$link = 'index.php?option=com_joomfish&task=statistics.check&type=original_status&phase=' .$phase .'&tmpl=component';

					if( $statecheck_i > -1) {
						$link .= '&statecheck_i='.$statecheck_i;
					}
				} else {
					$session->set('originalStatus', null );
				}
				break;
		}
		// Set the layout
		$this->_view->setLayout('result');
		$this->_view->assignRef('htmlResult', $htmlResult);
		$this->_view->assignRef('reload', $link);
		$this->_view->display();
	}	
}
