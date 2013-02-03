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
 * @subpackage Models
 *
*/
// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.filesystem.file');
JLoader::register('JFModel', JOOMFISH_ADMINPATH .DS. 'models' .DS. 'JFModel.php' );

/**
 * @package		Joom!Fish
 * @subpackage	CPanel
 */
class LanguagesModelLanguages extends JFModel
{
	/**
	 * @var string	name of the current model
	 * @access private
	 */
	private $_modelName = 'languages';

	/**
	 * @var array	set of languages found in the system
	 * @access private
	 */
	private $_languages = null;

	/**
	 * default constrcutor
	 */
	public function __construct() {
		parent::__construct();

		$this->addTablePath(JOOMFISH_ADMINPATH .DS. 'tables');

		$app	= JFactory::getApplication();
		$option = JRequest::getVar('option', '');
		// Get the pagination request variables
		$limit		= $app->getUserStateFromRequest( 'global.list.limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$limitstart	= $app->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}


	/**
	 * return the model name
	 */
	public function getName() {
		return $this->_modelName;
	}

	/**
	 * generic method to load the language related data
	 * @return array of languages
	 */
	public function getData() {
		if($this->_languages == null) {
			$this->loadLanguages();
		}
		return $this->_languages;
	}
	
	/** This method adds an empty line to the dataset for further usage
	 * @since 2.1
	 * @return void
	 */
	public function add() {
		if($this->_languages == null) {
			$this->loadLanguages();
		}
		
		$jfLanguage = $this->getTable('JFLanguage');
		$this->_languages['new'] = $jfLanguage;
	}

	/**
	 * Method to store language information
	 */
	public function store($cid, $data) {
		if( is_array($cid) && count($cid)>0 ) {
			for ($i=0; $i<count($cid); $i++) {
				$jfLang = $this->getTable('JFLanguage');
				$jfLang->set('lang_id', $cid[$i]);
				$jfLang->set('title', $data['title'][$i]);
				$jfLang->set('title_native', $data['title_native'][$i]);
				
				// The checkbox is only filled when it was active - so we have to check if
				// one box is fitting to your language
				$jfLang->set('published', false);
				if( isset($data['published']) ) {
					foreach( $data['published'] as $activeLanguageID ) {
						if( $activeLanguageID == $jfLang->lang_id ) {
							$jfLang->set('published', true);
							break;
						}
					}
				}
				$jfLang->set('lang_code', $data['lang_code'][$i]);
				$jfLang->set('sef', $data['sef'][$i]);
				$jfLang->set('image_ext', $data['image'][$i]);
				$jfLang->set('image', $this->extractImagePrefix($data['image'][$i]));
				$jfLang->set('ordering', $data['order'][$i]);
				$jfLang->set('fallback_code', $data['fallbackCode'][$i]);
				$jfLang->set('params', $data['params'][$i]);
				
				// ensure the meta key information are stored in the respective fields, therefore extract them additionally form the params
				$params = new JRegistry($data['params'][$i]);
				$jfLang->set('metadesc', $params->get('MetaDesc', ''));
				$jfLang->set('metakey', $params->get('MetaKeys', ''));
				$jfLang->set('sitename', $params->get('sitename', ''));
				
				if( !$jfLang->store() ) {
					$this->setError($jfLang->getError());
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Method to remove a language completely
	 */
	public function remove($cid, $data) {
		if( is_array($cid) && count($cid)>0 ) {
			for ($i=0; $i<count($cid); $i++) {
				$jfLang = $this->getTable('JFLanguage');
				if( !$jfLang->delete($cid[$i]) ) {
					$this->setError($jfLang->getError());
					return false;
				}
			}
		}
		return true;
	}
	
	/** 
	 * Method to set the Joomla client system wide default language
	 * 
	 * @param int $lang_id	language ID to be set as new default language
	 * @return boolean		success or not
	 * @since 2.1
	 * @access public
	 */
	public function setDefault($lang_id) {
		
		$jfLang = $this->getTable('JFLanguage');
		$jfLang->load($lang_id);
		
		// We define that the default language can only be changed for the Joomla Client - not the admin
		$client	= JApplicationHelper::getClientInfo(0);
	
		$params = JComponentHelper::getParams('com_languages');
		$params->set($client->name, $jfLang->lang_code);
	
		$table = JTable::getInstance('extension');
		$table->load($table->find(array("element"=>'com_languages')));
		
		$table->params = $params->toString();
	
		// pre-save checks
		if (!$table->check()) {
			JError::raiseWarning( 500, $table->getError() );
			return false;
		}
	
		// save the changes
		if (!$table->store()) {
			JError::raiseWarning( 500, $table->getError() );
			return false;
		}
		
		return true;
	}

	/**
	 * Loads content languages based on table information
	 */
	private function loadLanguages() {
		global  $option;
		$db = JFactory::getDBO();

		$filter_order		= JFactory::getApplication()->getUserStateFromRequest( $option.'filter_order',		'filter_order',		'l.ordering',	'cmd' );
		$filter_order_Dir	= JFactory::getApplication()->getUserStateFromRequest( $option.'filter_order_Dir',	'filter_order_Dir',	'',				'word' );

		// 1. read all known languages from the database
		//$sql = "SELECT l.*"
		//. "\nFROM #__jf_languages AS l";
			$sql = 'select l.lang_id AS lang_id,l.lang_code AS lang_code,l.title AS title,l.title_native AS title_native,
				l.sef AS sef,l.description AS description,l.published AS published,l.image AS image, l.ordering AS ordering,
				lext.image_ext AS image_ext,lext.fallback_code AS fallback_code,lext.params AS params
				from #__languages as l 
				left join #__jf_languages_ext as lext on l.lang_id = lext.lang_id'; 

		if ($filter_order != ''){
			$sql .= ' ORDER BY ' .$filter_order .' '. $filter_order_Dir;
		}
		$db->setQuery( $sql	);
		$contentlanguages = $db->loadObjectList('lang_code');
		
		// check for published Site Languages
		$query	= $db->getQuery(true);
		$query->select('a.element AS lang_code, a.name AS title, a.enabled As enabled');
		$query->from('`#__extensions` AS a');
		$query->where('a.type = '.$db->Quote('language'));
		$query->where('a.client_id = 0');
		
		$db->setQuery($query);
		$frontlanguages = $db->loadObjectList('lang_code');
		
		foreach ($frontlanguages AS &$frontlang) {
			if( array_key_exists( $frontlang->lang_code, $contentlanguages )) {
				unset($frontlanguages[$frontlang->lang_code]);
				continue;
			}
			$langarr 					= explode ('-', $frontlang->lang_code);
			$frontlang->lang_id   		= null;
			$frontlang->title_native 	= $frontlang->title;
			$frontlang->sef   			= $langarr[0];
			$frontlang->published   	= 0;  // default to unpublished as it needs to be saved before it can be used - inserted in languages table
			$frontlang->image_ext 		= 'media/com_joomfish/default/flags/' .$langarr[0]. '.gif';
		}
		
		$languages = array_merge($contentlanguages, $frontlanguages);
		
		if($languages === null) {
			JError::raiseWarning( 400, $db->getErrorMsg());
		}
		
		// We convert any language of the table into a JFLanguage object. As within the language list the key will be the language code
		$this->_languages = array();
		foreach($languages as $language) {
			$jfLanguage = $this->getTable('JFLanguage');
			$jfLanguage->bind($language);

			$this->_languages[$jfLanguage->lang_code] = $jfLanguage;
		}
	}
		
	/**
	 * Method to identify the Joomla 1.6 image prefix for a given full image path
	 * As the specification of this prefix might change here is one special method handling this
	 * @param	string	full image name
	 * @return	string	Joomla 1.6 image prefix
	 * @since	2.1
	 * @access	private 
	 */
	private function extractImagePrefix($image) {
		$fileName = JFile::getName($image);
		return JFile::stripExt($fileName);
	}
}
?>