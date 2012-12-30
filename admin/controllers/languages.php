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
 * @subpackage languages
 *
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * The JoomFish Tasker manages the general tasks within the Joom!Fish admin interface
 *
 */
class LanguagesController extends JController  {
	/** var JInput	jinput reference */
	private $jinput = null;
		
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->jinput = JFactory::getApplication()->input;
		
		$this->registerTask('show',  'display' );
	}


	/**
	 * Standard display control structure
	 * 
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$this->view =  $this->getView("languages");
		parent::display();
	}
	
	/**
	 * Standard handler for the new dialog
	 * adding a new option of languages to fill out
	 */
	public function add() {
		$model = $this->getModel('languages');
		$model->add();
		
		$this->view =  $this->getView("languages");
		$this->view->setModel($model, true);
		$this->view->display();
	}
	
	/*
	 * Standard Handler for cancel of dialog
	 */
	public function cancel()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );
		
		$this->setRedirect( 'index.php?option=com_joomfish' );
	}

	/**
	 * Standard method to save the language information
	 *
	 */
	public function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );

		$post	= JRequest::get('post');
		$cid 	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		
		$model = $this->getModel('languages');
		
		if ($model->store($cid, $post)) {
			$msg = JText::_( 'LANGUAGES_SAVED' );
		} else {
			$msg = JText::_( 'Error Saving Languages:' .$model->getErrors());
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_joomfish';
		$this->setRedirect($link, $msg);
	}	

	/**
	 * Standard method to save the language information
	 *
	 */
	public function apply()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );

		$post	= JRequest::get('post');
		$cid 	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		
		$model = $this->getModel('languages');
		
		if ($model->store($cid, $post)) {
			$msg = JText::_( 'LANGUAGES_SAVED' );
		} else {
			$msg = JText::_( 'ERROR_SAVING_LANGUAGES' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_joomfish&task=languages.show';
		$this->setRedirect($link, $msg);
	}	

	/**
	 * Method to show the file/image browser for the flag selection
	 * @access public
	 */
	public function fileBrowser() {
		$document = JFactory::getDocument();

		$viewType	= $document->getType();
		$viewName	= JRequest::getCmd( 'view', $this->getName() );
		$viewLayout	= JRequest::getCmd( 'layout', 'fileBrowser' );

		$this->view =  $this->getView("languages");

		// Set the layout
		$this->view->setLayout($viewLayout);

		$this->view->filebrowser();
	}
	
	/**
	 * Method to call the deletion of languages
	 */
	public function remove() {
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );

		$post	= JRequest::get('post');
		$cid 	= JRequest::getVar( 'checkboxid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		
		$model = $this->getModel('languages');
		
		if ($model->remove($cid, $post)) {
			$msg = JText::_( 'LANGUAGES_REMOVED' );
		} else {
			$msg = JText::_( 'ERROR_DELETING_LANGUAGES' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_joomfish&task=languages.show';
		$this->setRedirect($link, $msg);
	}
	
	/** Method to change the website default language
	 * @since 2.1
	 * @access public
	 */
	public function setDefault() {
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );

		$post	= JRequest::get('post');
		$cid 	= JRequest::getVar( 'checkboxid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		
		$model = $this->getModel('languages');
		
		if ($model->setDefault($cid[0])) {
			$msg = JText::_( 'DEFAULT_LANGUAGE_SET' );
		} else {
			$msg = JText::_( 'ERROR_CHANGING_DEFAULT_LANGUAGE' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_joomfish&task=languages.show';
		$this->setRedirect($link, $msg);
	}

	/**
	 * Method to translate global config values
	 *
	 */
	public function translateConfig(){
		$document = JFactory::getDocument();

		$viewType	= $document->getType();
		$viewName	= JRequest::getCmd( 'view', $this->getName() );
		$viewLayout	= JRequest::getCmd( 'layout', 'translateconfig' );
		$lang_id = JRequest::getVar( 'lang_id', null, 'request', 'int' );
		
		$this->view =  $this->getView("languages");

		// Set the layout
		$this->view->setLayout($viewLayout);

		// load the current config parameters from the language
		$this->view->translations = new JRegistry();
		$current = JRequest::getVar('current', '', 'request', 'string');
		if($current != null) {
			$this->view->translations = new JRegistry($current);
		}
		

		// Default Text handled 'manually'
		$config = JComponentHelper::getParams( 'com_joomfish' );
		$this->view->defaulttext = $config->getValue("defaultText");		
		$this->view->trans_defaulttext = $this->view->translations->get("defaulttext","");
		
		// Set the config detials for translation in the view
		$elementfolder =JPath::clean(  JOOMFISH_LIBPATH .DS. 'contentelement' .DS. 'contentelements' );
		include($elementfolder.DS."language.config.php");
		$this->view->jf_siteconfig=$jf_siteconfig;

		// Need to load com_config language strings!
		$lang = JFactory::getLanguage();
		$lang->load( 'com_config' );

		$jconf = new JConfig();
		$this->view->jconf = $jconf;
		
		$this->view->translateConfig();
		
	}

	/**
	 * Method to translate global config values
	 * @deprecated 2.1 - as solved in iframe/slimbox
	 * @access private
	 */
	private function saveTranslateConfig(){
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );

		$post	= JRequest::get('post');
		$lang_id 	= JRequest::getInt( 'lang_id',0 );
		$model = $this->getModel('languages');
		$language = $model->getTable('JFLanguage');		
		$language->load($lang_id);

		if  (is_null($lang_id) || !isset($language->id) || $language->id<=0){
			die( 'Invalid Language Id' );
		}
		
		$data = array();
		foreach ($_REQUEST as $key=>$val) {
			if (strpos($key,"trans_")===0){
				$key = str_replace("trans_","",$key);
				if (ini_get('magic_quotes_gpc')) {
          		  $val = stripslashes($val);
        		} 
        		$data[$key]=$val;
			}
		}
		$registry = new JRegistry();
		$registry->loadArray($data);
		$language->params = $registry->toString();

		if ($language->store()) {
			$msg = JText::_( 'LANGUAGES_SAVED' );
		} else {
			$msg = JText::_( 'ERROR_SAVING_LANGUAGES' );
		}
		JFactory::getApplication()->redirect("index.php?option=com_joomfish&task=languages.show",$msg);
	}
	
	
}
?>
