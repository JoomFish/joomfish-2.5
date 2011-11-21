<?php

/**
 * Joom!Fish - Multi Lingual extention and translation manager for Joomla!
 * Copyright (C) 2003 - 2010, Think Network GmbH, Munich
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
 * $Id: jfdatabase.php 241 2011-06-22 15:42:55Z geraint $
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

if (JFile::exists(JPATH_SITE . DS . 'components' . DS . 'com_joomfish' . DS . 'helpers' . DS . 'defines.php'))
{
	require_once( JPATH_SITE . DS . 'components' . DS . 'com_joomfish' . DS . 'helpers' . DS . 'defines.php' );
	JLoader::register('JoomfishManager', JOOMFISH_ADMINPATH . DS . 'classes' . DS . 'JoomfishManager.class.php');
	JLoader::register('JoomFishVersion', JOOMFISH_ADMINPATH . DS . 'version.php');
	JLoader::register('JoomFish', JOOMFISH_PATH . DS . 'helpers' . DS . 'joomfish.class.php');
}
else
{
	JError::raiseNotice('no_jf_extension', JText::_('Joom!Fish extension not installed correctly. Plugin not executed'));
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

	function __construct(& $subject, $config)
	{

		jimport('joomla.html.parameter');
		parent::__construct($subject, $config);

		// put params in registry so I have easy access to them later
		$conf = JFactory::getConfig();
		$conf->setValue("jfdatabase.params", $this->params);

		if (JFactory::getApplication()->isAdmin())
		{
			return;
		}

		$this->config = array(
			'adapter' => "inheritor"
		);

		if (defined('JOOMFISH_PATH'))
		{
			$this->jfInitialize();
		}
		else
		{
			JError::raiseNotice('no_jf_component', JText::_('Joom!Fish component not installed correctly. Plugin not executed'));
		}

	}

	/**
	 * During this event we setup the database and link it to the Joomla! ressources for future use
	 * @return void
	 */
	function onAfterInitialise()
	{
		if (JFactory::getApplication()->isAdmin())
		{
			// This plugin is only relevant for use within the frontend!
			return;
		}
		$this->setupJFDatabase();

	}

	function onAfterRoute()
	{
		// amend editing page!
		$reference_id = JRequest::getInt("id");
		if (JFactory::getApplication()->isAdmin() && JRequest::getCmd("layout") == "edit" && $reference_id > 0)
		{
			$db = JFactory::getDbo();
			$table = "content";
			if (JRequest::getCmd('option') == "com_menus")
			{
				$table = "menu";
			}
			$db->setQuery('select * from #__jf_translationmap where reference_table="' . $table . '" AND translation_id=' . JRequest::getInt("id"));
			$translations = $db->loadObjectList();
			$original = 0;
			if (count($translations) > 0)
			{
				$original = $translations[0]->reference_id;
			}

			$doc = JFactory::getDocument();
			$script = <<<SCRIPT
window.addEvent('domready', function() {
	var langselect = $('jform_language');
	if (langselect){
		var isTranslation = $original>0;
		var langselectli = langselect.getParent()
		var jflangselect = new Element("select",{ name:'jftranslation', id:'jftranslation'});
		var opt = new Element("option",{value:1, 'text':'Yes'});
		jflangselect.appendChild(opt);
		if (!isTranslation){
			opt = new Element("option",{value:0, 'text':'No'});
			jflangselect.appendChild(opt);
		}
		jflangselect.value= isTranslation?1:0;
		jflangselect.addEvent('change',function(){
			if(this.value==1){
				$('jform_id').value = 0;
				$('jfid').value = 0;
				$('jform_id').readonly = false;
				$('jform_id').removeClass('readonly');
			}
			else {
				$('jform_id').value = refid;
				$('jfid').value = refid;
				$('jform_id').readonly = true;
				$('jform_id').addClass('readonly');
			}
		});
			
		var jflanglabel   = new Element("label",{id:'jftranslation-lbl', for:'jftranslation'});		
		jflanglabel.appendText("Is Translation?");
		
		var refid = $('jform_id').value;
		var jflanginput = new Element("input",{ type:'text', name:'jftranslation_id', id:'jftranslation_id', value:$original});
		var jforigalinput = new Element("input",{ type:'text', name:'jforiginal_id', id:'jforiginal_id', value:refid});

		var jftranslabel  = new Element("label", {for:"jftranslation_id"});
		jftranslabel.appendText("translation id : ");

		var jforiglabel  = new Element("label", {for:"jforiginal_id"});
		jforiglabel.appendText("origional id : ");

		var newid = false;
		if (!$('id')){
			// must also have a new pseudo  id to make sure replaces anything in the URL!
			// editing existing elements don't have this 
			var newid = new Element("input",{ type:'text', name:'id', id:'jfid', value:refid});		
			var jfnewidlabel  = new Element("label", {for:"jfid"});
			jfnewidlabel.appendText("new id : ");
		}


		// new li row
		var li = new Element('li');
		li.appendChild(jflanglabel);
		li.appendChild(jflangselect);
		// translation id
		li.appendChild(jftranslabel);
		li.appendChild(jflanginput);
		// original id
		li.appendChild(jforiglabel);
		li.appendChild(jforigalinput);
		
		if (newid){
			li.appendChild(jfnewidlabel);
			li.appendChild(newid);
		}
		
		// insert it after the lang selector
		li.inject( langselectli,'after');				
	}
});
SCRIPT;
			$doc->addScriptDeclaration($script);
		}

	}

	public function onContentBeforeSave($context, &$article, $isNew)
	{
		//$this->doAfterSave($context, $article, $isNew, "content");

	}

	public function onContentAfterSave($context, &$article, $isNew)
	{
		$this->doAfterSave($context, $article, $isNew, "content");

	}

	public function onMenuAfterSave($context, &$article, $isNew)
	{
		$this->doAfterSave($context, $article, $isNew, "menu");

	}

	private function doAfterSave($context, &$article, $isNew, $table)
	{
		$translationid = JRequest::getInt("jftranslation_id");
		$originalid = JRequest::getInt("jforiginal_id");
		$jftranslation = JRequest::getInt("jftranslation");
		if ($jftranslation > 0)
		{
			$jform = JRequest::getVar("jform");
			if ($jform && isset($jform['language']))
			{
				$language = $jform['language'];
			}
			else
			{
				return;
			}
			if ($translationid > 0)
			{
				// existing translation
				$db = JFactory::getDbo();
				$sql = "replace into #__jf_translationmap (reference_id, translation_id, reference_table, language ) values ($translationid, $article->id," . $db->quote($table) . "," . $db->quote($language) . ")";
				$db->setQuery($sql);
				$success = $db->query();
				return;
			}
			else
			{
				// new translation
				$db = JFactory::getDbo();
				$sql = "replace into #__jf_translationmap (reference_id, translation_id, reference_table, language ) values ($originalid,$article->id ," . $db->quote($table) . "," . $db->quote($language) . ")";
				$db->setQuery($sql);
				$success = $db->query();
				return;
			}
		}
		else
		{
			
		}

	}

	function onAfterRender()
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

	function sortprofile($a, $b)
	{
		return $a["total"] >= $b["total"] ? -1 : 1;

	}

	/**
	 * Setup for the Joom!Fish database connectors, overwriting the original instances of Joomla!
	 * Which connector is used and which technique is based on the extension configuration
	 * @return void
	 */
	function setupJFDatabase()
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

			$test = JFactory::getDBO();

			$conf->setValue('config.mbf_content', 1);
			$conf->setValue('config.multilingual_support', 1);
		}

	}

	/** This function initialize the Joom!Fish manager in order to have
	 * easy access and prepare certain information.
	 * @access private
	 */
	function jfInitialize()
	{
		
	}

}

