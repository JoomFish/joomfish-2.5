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
 * @subpackage jfdatabase
 * @version 2.0
 *
 */
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

// Joom!Fish bot get's only activated if essential files are missing
if (!file_exists(dirname(__FILE__) . DS . 'jfdatabase_inherit.php'))
{
	JError::raiseNotice('no_jf_plugin', JText::_('Joom!Fish plugin not installed correctly. Plugin not executed'));
	return;
}

jimport('joomla.filesystem.file');
JFactory::getLanguage()->load('com_joomfish', JPATH_ADMINISTRATOR);

if (JFile::exists(JPATH_SITE . DS . 'components' . DS . 'com_joomfish' . DS . 'helpers' . DS . 'defines.php'))
{
	require_once( JPATH_SITE . DS . 'components' . DS . 'com_joomfish' . DS . 'helpers' . DS . 'defines.php' );
	jimport('joomfish.manager');
	JLoader::register('JoomFishVersion', JOOMFISH_ADMINPATH . DS . 'version.php');
	jimport('joomfish.joomfish');
}
else
{
	JError::raiseNotice('no_jf_extension', JText::_('JF_COMPONENT_NOT_INSTALLED'));
	return;
}

/**
 * Exchange of the database abstraction layer for multi lingual translations.
 */
class plgSystemJFDatabase extends JPlugin
{

	/**
	 * stored configuration from plugin
	 *
	 * @var object configuration information
	 */
	var $_config = null;

	public function __construct(& $subject, $config)
	{
		
		jimport('joomla.html.parameter');
		parent::__construct($subject, $config);

		// put params in registry so I have easy access to them later
		$conf = JFactory::getConfig();
		$conf->setValue("jfdatabase.params", $this->params);

		/*if (JFactory::getApplication()->isAdmin())
		{
			return;
		}*/

		$this->config = array(
			'adapter' => "inheritor"
		);

		if (defined('JOOMFISH_PATH'))
		{
			$this->jfInitialize();
		}
		else
		{
			JError::raiseNotice('no_jf_component', JText::_('JF_COMPONENT_NOT_INSTALLED'));
		}
		
		// workaround for lame Joomla not triggering after save events WONT WORK, WE NEED TO DO IT AS OVERRIDE >>>> add AFTER SAVE
		/*$option 		= JRequest::getCmd('option');
		
		if ((in_array($option, array("com_menus","com_modules", "com_categories"))) && JFactory::getApplication()->isAdmin() 
				&& JRequest::getCmd("layout") == "edit" && in_array(JRequest::getCmd("task"), array('item.apply', 'item.save', 'item.save2new', 'item.save2copy' )) )
		{	
			
			$tablename 		= rtrim(str_replace('com_', '', $option), 's');
			$reference_id 	= JRequest::getInt("jfreference_id", 0);
			$translation_id	= JRequest::getInt("jftranslation_id", 0);
			$tableclass = JoomFishManager::getInstance()->getContentElement($tablename)->getTableClass();
			$table = JTable::getInstance($tableclass);
			
			if (intval($translation_id) > 0)
			{
				$table->load(intval($translation_id));
				$isNew = false;
			}
			else
			{
				// load the original and amend
				$table->load(intval($reference_id));
				$key = $table->getKeyName();
				$table->$key = 0;
				if (is_callable(array($table, "setLocation")))
				{
					//$table->setLocation($table->parent_id, 'last-child');
					$table->setLocation(intval($reference_id), 'after');
				}
				$isNew = true;
			}
			
			$this->onContentAfterSave($option.'.'.$tablename, $table, $isNew);
		}*/
	}

	/**
	 * During this event we setup the database and link it to the Joomla! ressources for future use
	 * @return void
	 */
	public function onAfterInitialise()
	{
		if (JFactory::getApplication()->isAdmin())
		{
			// This plugin is only relevant for use within the frontend!
			return;
		}
		$dbtype = JFactory::getConfig()->getValue('dbtype','mysqli');
		if ($dbtype != 'mysqli') {
			JError::raiseNotice('no_jf_extension', JText::_('JF_DATABASE_DRIVER_NOT_SUPPORTED'));
			return;
		}
		$this->setupJFDatabase();

	}
	
	/*public function onContentPrepareForm($form, $data)
    {
       
                if (!($form instanceof JForm)) {
                        $this->_subject->setError('JERROR_NOT_A_FORM');
                        return false;
                }
       
                // Add the registration fields to the form.
                JForm::addFormPath(JOOMFISH_ADMINPATH . DS . 'models' .DS. 'fields');
                $form->loadFile('translationitem', false);
 
                return true;
     }*/       

	/*public function onContentBeforeSave($context, &$article, $isNew)
	{ // see http://joomlacode.org/gf/project/joomla/tracker/?action=TrackerItemEdit&tracker_id=8103&tracker_item_id=28654 before activating
	}*/

	public function onContentAfterSave($context, &$article, $isNew)
	{	
		if (!is_a($article, 'JTable')) {
			return;
		}
		
		// We need this plugin to respond to the native saving of content items in the backend of Joomla
		$tableName = $article->getTableName();
		$this->doAfterSave($context, $article, $isNew, $tableName);

	}

	public function onExtensionAfterSave($context, &$table, $isNew){
		if (!is_a($table, 'JTable')) {
			return;
		}
		// We need this plugin to respond to the native saving of modules etc. in the backend of Joomla
		$tablename = $table->getTableName();
		$this->doAfterSave($context, $table, $isNew, $tablename);
	}
	
	public function onMenuAfterSave($context, &$table, $isNew, $elementTable)
	{	
		//$data = JRequest::getVar("jform", array());
		
		foreach ($elementTable->Fields AS $Field) {
			if ($Field->Name == "home" && JRequest::getVar('origText_home') == "1") {
				$db = JFactory::getDbo();

			$query = $db->getQuery(true);
			$query->update('#__menu');
			$query->set($db->quoteName('home').' = 1');
			$query->where('id = ' .(int) $table->id);
			$db->setQuery($query);
			$db->query();
			}
		}
		
		$this->doAfterSave($context, $table, $isNew, "menu");
		

	}

	public function onModuleAfterSave($context, &$table, $isNew, $elementTable)
	{
		$this->doAfterSave($context, $table, $isNew, "modules");

		// For modules must also save module/menu assignments

		$data = JRequest::getVar("jform", array());
		if (isset($data['assignment']))
		{
			//
			// Process the menu link mappings.
			//

			$assignment = isset($data['assignment']) ? $data['assignment'] : 0;

			$db = JFactory::getDbo();

			$query = $db->getQuery(true);
			$query->delete();
			$query->from('#__modules_menu');
			$query->where('moduleid = ' . (int) $table->id);
			$db->setQuery((string) $query);
			$db->query();

			// If the assignment is numeric, then something is selected (otherwise it's none).
			if (is_numeric($assignment))
			{
				// Variable is numeric, but could be a string.
				$assignment = (int) $assignment;

				// Logic check: if no module excluded then convert to display on all.
				if ($assignment == -1 && empty($data['assigned']))
				{
					$assignment = 0;
				}

				// Check needed to stop a module being assigned to `All`
				// and other menu items resulting in a module being displayed twice.
				if ($assignment === 0)
				{
					$query->clear();
					$query->insert('#__modules_menu');
					$query->set('moduleid=' . (int) $table->id);
					$query->set('menuid=0');
					$db->setQuery((string) $query);
					if (!$db->query())
					{
						$this->setError($db->getErrorMsg());
						return false;
					}
				}
				elseif (!empty($data['assigned']))
				{
					// Get the sign of the number.
					$sign = $assignment < 0 ? -1 : +1;

					// Preprocess the assigned array.
					$tuples = array();
					foreach ($data['assigned'] as &$pk)
					{
						$tuples[] = '(' . (int) $table->id . ',' . (int) $pk * $sign . ')';
					}

					$db->setQuery(
							'INSERT INTO #__modules_menu (moduleid, menuid) VALUES ' .
							implode(',', $tuples)
					);

					if (!$db->query())
					{
						$this->setError($db->getErrorMsg());
						return false;
					}
				}
			}
		}

	}

	private function doAfterSave($context, &$article, $isNew, $table, $elementTable=false)
	{
		if (strpos($table,'#__')===0){
			$table = str_replace('#__', '', $table);
		}
		// if its a new translation then jfreference_id is empy		
		$referenceid = JRequest::getInt("jfreference_id",0);
		
		// originalid is the id of the item being edited  !
		$originalid = JRequest::getInt("jforiginal_id", 0);
		$jftranslation = JRequest::getInt("jftranslation",0);
		$keyname = $article->getKeyName();
		
		// don't do anything if not a translation
		if (!$jftranslation){
			return;
		}
		
		$jform = JRequest::getVar("jform");
		if ($jform && isset($jform['language']))
		{
			$language = $jform['language'];
		}
		else if (JRequest::getInt("select_language_id"))
		{
			$language = JRequest::getInt("select_language_id");
			$jfm = JoomFishManager::getInstance();
			$languages = $jfm->getLanguagesIndexedById();
			$language = $languages[$language]->code;
		}
		else
		{
			return;
		}
		
		if ($originalid > 0)
		{	
			if($referenceid <= 0 || $originalid == $referenceid) {
				//translation from existing item in default language so we need to insert new items id as transaltion id
				// new translation so the originalid field is the id of the item that has been translated i.e. the reference id
				$db = JFactory::getDbo();
				$translationid = $article->$keyname;
				$sql = "replace into #__jf_translationmap (reference_id, translation_id, reference_table, language ) values ($originalid, $translationid ," . $db->quote($table) . "," . $db->quote($language) . ")";
				$db->setQuery($sql);
				$success = $db->query();
				//check out original item we were editing
				$ortable = JTable::getInstance($table);
				$ortable->checkIn($originalid);
			
				return;
				
			} else if ($referenceid > 0)
			{
				// existing translation
				$db = JFactory::getDbo();
				$sql = "replace into #__jf_translationmap (reference_id, translation_id, reference_table, language ) values ($referenceid, $originalid," . $db->quote($table) . "," . $db->quote($language) . ")";
				$db->setQuery($sql);
				$success = $db->query();
				return;
			}
			/*else
			{
				// new translation so the originalid field is the id of the item that has been translated i.e. the reference id
				$db = JFactory::getDbo();
				$translationid = $article->$keyname;
				$sql = "replace into #__jf_translationmap (reference_id, translation_id, reference_table, language ) values ($originalid, $translationid ," . $db->quote($table) . "," . $db->quote($language) . ")";
				$db->setQuery($sql);
				$success = $db->query();
				return;
			}*/
		}
		else if ($originalid == 0 && $referenceid > 0 )
		{
			// new menu item, so we are making a new translation of an existing item
			$db = JFactory::getDbo();
			$translationid = $article->$keyname;
			$sql = "replace into #__jf_translationmap (reference_id, translation_id, reference_table, language ) values ($referenceid, $translationid ," . $db->quote($table) . "," . $db->quote($language) . ")";
			$db->setQuery($sql);
			$success = $db->query();
			return;
		}
		

	}

	public function onAfterRender()
	{
		if (JFactory::getApplication()->isAdmin())
		{
			// This plugin is only relevant for use within the frontend!
			return;
		}

		$db = JFactory::getDBO();
		if (isset($db->profileData))
		{
			$buffer = JResponse::getBody();
			$info = "";
			$info .= "<div style='font-size:11px'>";
			uasort($db->profileData, array($this, "sortprofile"));
			foreach ($db->profileData as $func => $data)
			{
				$info .= "$func = " . round($data["total"], 4) . " (" . $data["count"] . ")<br />";
			}
			$info .= "</div>";
			$buffer = str_replace("JFTimings", $info, $buffer);
			JResponse::setBody($buffer);
		}

	}

	protected function sortprofile($a, $b)
	{
		return $a["total"] >= $b["total"] ? -1 : 1;

	}

	/**
	 * Setup for the Joom!Fish database connectors, overwriting the original instances of Joomla!
	 * Which connector is used and which technique is based on the extension configuration
	 * @return void
	 */
	protected function setupJFDatabase()
	{
		if (file_exists(dirname(__FILE__) . DS . 'jfdatabase_inherit.php'))
		{

			require_once( dirname(__FILE__) . DS . 'jfdatabase_inherit.php' );

			// make sure jfManager is initialised before we switch db handler
			$jfManager = JoomFishManager::getInstance();

			$conf = JFactory::getConfig();

			$host = $conf->getValue('config.host');
			$user = $conf->getValue('config.user');
			$password = $conf->getValue('config.password');
			$db = $conf->getValue('config.db');
			$dbprefix = $conf->getValue('config.dbprefix');
			$dbtype = $conf->getValue('config.dbtype');
			$debug = $conf->getValue('config.debug');
			$driver = $conf->getValue('config.dbtype');

			$options = array("driver" => $driver, "host" => $host, "user" => $user, "password" => $password, "database" => $db, "prefix" => $dbprefix, "select" => true);

			$db = new JFDatabase($options);
			$debug = $conf->getValue('config.debug');
			$db->debug($debug);

			if ($db->getErrorNum() > 2)
			{
				JError::raiseError('joomla.library:' . $db->getErrorNum(), 'JDatabase::getInstance: Could not connect to database <br/>' . $db->getErrorMsg());
			}

			// replace the database handle in the factory
			JFactory::$database = null;
			JFactory::$database = $db;

			$conf->setValue('config.mbf_content', 1);
			$conf->setValue('config.multilingual_support', 1);
		}

	}

	/** This function initialize the Joom!Fish manager in order to have
	 * easy access and prepare certain information.
	 * @access private
	 */
	public function jfInitialize()
	{
		
	}
	
	// @Todo move this to specific joomfish plugins
	public function onBeforeTranslationProcess (&$rows, $language, &$fielddata, $querySQL, $onlytransFields) {
		if (stristr($querySQL, 'LEFT JOIN #__categories AS c on c.id = a.catid')) {

				$previouskey = null; 
				foreach ($rows[0] as $key => $value) {
					if (is_string($key) && $key == 'catid') {
						$idindex = (int)$previouskey;
						break;
					}
					$previouskey = $key;
				}
			$fielddata['alias_c']['idindex'] = $idindex;
		}
	}
	

}