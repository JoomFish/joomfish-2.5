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
 * $Id: translate.php 226 2012-02-10 07:29:41Z alex $
 * @package joomfish
 * @subpackage Models
 *
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

JLoader::register('JFModel', JOOMFISH_ADMINPATH . DS . 'models' . DS . 'JFModel.php');
JLoader::register('TableJFLanguage', JOOMFISH_ADMINPATH .DS. 'tables' .DS. 'JFLanguage.php' );

/**
 * This class provides routing mappings for languages
 *
 * @package		Joom!Fish
 * @subpackage	JFModel
 */

class JFModelRoute extends JFModel {

	private $_conf;

	public function __construct() {
		$this->_conf = JFactory::getConfig();
	}

	/*
	 * Function to route given url
	*
	*/


	public function rerouteCurrentUrlCached($code=null, $cachable=false)
	{
		$uri 		= JRequest::getURI();
		
		if ($cachable == true) {
			$cache 	= JFactory::getCache('com_joomfish', 'callback');
			$url 	= $cache->get(array($jfrouter, "rerouteCurrentUrl" ),  array($code), md5(serialize(array($code, $uri))));
		} else {
			$url 	= $this->rerouteCurrentUrl($code);
		}

		return $url;

	}
	
	/* Reroute current url to the translated one
	 * @param short language code
	 */
	
	public function rerouteCurrentUrl($code=null)
	{	

		$vars		= $this->getSafeVariablesFromRoutedRequest();
		
		// @Todo if this is not default language this vars might be wrong due to translated path!
		
		$router		= JFactory::getApplication()->getRouter();
		$uri 		= JURI::getInstance();
		
		/////// 1. save current lang codes for later ///////////////////////////////////////////
		$currentlang 		= isset($vars['lang']) ? $vars['lang'] : null;
		$currentJoomlaLang 	= $this->_conf->get('language');
		
		/////// 2. set lang code in router and JURI vars to $code, push it to the router
		$vars['lang'] = $code;
		$uri->setVar('lang', $code);
		$router->setVars($vars, false);		

		
		/////// 3. swap menu values with translated ones ////////////////////////////////////////
		
		// switch active translation language
		$this->switchJFLanguageShortcode($code, true);

		JFactory::$language = null; // reset language instance as JFDatabase->setLanguage uses  JFactory::getLanguage()
		
		// get translated menu
		$menu		= JFactory::getApplication()->getMenu();
		$menu->__construct(); // force re-loading of the menu
		
		// fix item routes for this item as routing is based on active menu $item->route
		// and translated query doesn't change menu routes
		// -------- not needed as it is done in menu override for all items
		/*$itemid = $vars['Itemid'] ? $vars['Itemid'] : JRequest::getInt('Itemid');
		$menuitems	= & $menu->getMenu();
		$this->fixMenuItemRoutes($menuitems, $itemid);*/
		
		/////// 4. route vars //////////////////////////////////////////////////////////////////
		$varstring = 'index.php?'.$uri->buildQuery($vars);
		//$currenturl= $uri->toString(array('path', 'query'));
		$absolute = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
		$routedurl = $absolute . JRoute::_($varstring, true, $uri->isSSL());
		
		/////// 5. reset everything to the previous state so we don't affect anything //////////
		$vars['lang'] = $currentlang;
		$uri->setVar('lang', $currentlang);
		$router->setVars($vars, false);
		$this->switchJFLanguageShortcode($currentlang, false);
		$this->switchJoomlaLanguageLongcode($currentJoomlaLang);
		JFactory::$language = null;
		
		// fix url if we are using sef domains
		$this->prefixToHost($routedurl);
		
		return $routedurl;
	
	}

	
	/*
	 * if joomfish sef hosts are active replace prefix with sef host
	 * 
	 * @param absolute url including host 
	 * @return fixed url
	 */

	public function prefixToHost(&$url)
	{
		$currenthost = $this->_conf->getValue("joomfish.current_host", false);
		
		// joomfish sef prefix
		$sefhost = $this->_conf->getValue("joomfish.sef_host", false);
		
		if ($sefhost && $currenthost)
		{
			$url = str_replace($currenthost, $sefhost, $url);
		}
		
		$this->_conf->setValue("joomfish.sef_host", false);

		return $url;

	}

	/*
	 * Fix menu items to hold translated route
	 * @param Joomla Menu items
	 * @param ids of items to be fixed, if null all items get fixed routes
	 */
	
	public function fixMenuItemRoutes(&$menuitems, $ids=null) {
		
		if ($ids === null) {
			$ids = array_keys($menuitems);
		}
		
		if (!is_array($ids)) {
			$ids = array($ids);
		}

		foreach ($ids AS $id) {
			$temproute = array();
			foreach ($menuitems[$id]->tree AS $treeID) {
				$temproute[$treeID] = $menuitems[$treeID]->alias;
			}
			$menuitems[$id]->route = implode('/', $temproute);
			$temproute = null;
		}
		
		return $menuitems;
		
	}
	
	
	/* 
	 * get cleaned variables from routed request
	 * @return array of cleaned vars
	 */
	public function getSafeVariablesFromRoutedRequest() {
		
		$vars =  JFactory::getApplication()->getRouter()->getVars();
		$filter = JFilterInput::getInstance();
		
		$cleanvars = array();
		
		foreach ($vars as $k => $v) {

			if (is_array($v))
			{
				$arrayValue = $filter->clean($v, 'array');
				$arrayVars = array();
				
				foreach (array_keys($arrayValue) as $akey)
				{
					$arrayVars [$akey] = $filter->clean($arrayValue[$akey]);
				}
				$cleanvars[$k]	= $arrayVars;
			} else {
				$cleanvars[$k]	= $filter->clean($v);
			}	
		}
		
		return $cleanvars;
	}
	
	public function switchJFLanguageShortcode($code, $switchjoomlatoo=true) {
		$language	= JoomFishManager::getInstance()->getLanguageByShortcode($code);
		$this->_conf->set('joomfish_language', $language);
		$targetcode = is_object($language) ? $language->code : null;
		$this->_conf->set('jflang', $targetcode);
		if ($switchjoomlatoo===true) {
			$this->switchJoomlaLanguageLongcode($language->code);
		}
	}
	
	public function switchJoomlaLanguageLongcode($code) {
		$this->_conf->set('language', $code);
		$this->_conf->set('lang_site', $code);
	}


}