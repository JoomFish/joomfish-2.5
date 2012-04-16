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

	private static $_instance = null;

	private $_conf;

	public static function getInstance() {
		if (!isset($_instance) ) {
			$_instance = new JFModelRoute();
		}
		return $_instance;
	}

	public function __construct() {
		$this->_conf = JFactory::getConfig();
	}

	/*
	 * Function to route given url
	*
	*/


	public function rerouteCurrentUrlCached($code=null, $cachable=false)
	{
		// Treat change of language specially because of problems if menu alias is translated

		$language	= $this->_conf->getValue("joomfish_language", null);
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
	
	public function rerouteCurrentUrl($code)
	{	

		$vars		= $this->getSafeVariablesFromRoutedRequest();
		$router		= JFactory::getApplication()->getRouter();
		$uri = JURI::getInstance();
		
		// 1. save current lang code for later
		$currentlang = $vars['lang'];
		
		// 2. set lang code in router and JURI vars to $code, push it to the router
		$vars['lang'] = $code;
		$uri->setVar('lang', $code);
		$router->setVars($vars, false);
		
		// TODO 3. swap menu values with translated ones
		
		
		// 4. route vars 
		$varstring = 'index.php?'.$uri->buildQuery($vars);
		$routedurl = JRoute::_($varstring, true, $uri->isSSL());
		//$currenturl= $uri->toString(array('path', 'query'));
		
		
		// 5. reset everything to the previous state so we don't affect anything
		$vars['lang'] = $currentlang;
		$uri->setVar('lang', $currentlang);
		$router->setVars($vars, false);
		
		$this->prefixToHost($routedurl);
		
		return $routedurl;
		
		
		
		
		
		/*$language 	= $this->_conf->getValue("joomfish_language", null);
	
		if ($language != null)
		{
			$jfLang = $language->getLanguageCode();
		}
		else
		{
			$jfLang = null;
			$lang = JFactory::getLanguage();
			$language = new stdClass();
			$language->code = $lang->getDefault();
		}
	
		if (!is_null($code) && $code != $jfLang)
		{
			$sefLang = TableJFLanguage::createByShortcode($code, false);
			$this->_conf->setValue("joomfish.sef_lang", $sefLang->code);
	
			$menu = JFactory::getApplication('site')->getMenu();
			$items = unserialize(serialize($menu->getMenu()));
			//$items = $menu->getMenu();
	
			// route url with translated menu items, first swap the whole menu with translated version
			$menu->set('_items', $this->_getJFMenuItems($sefLang->code, false, $items));
			// now run this item trough the routing
			$url = $this->_cachedGetRoute($href, $sefLang->code);
	
			// reset items back to untraslated value
			$menu->set('_items', $items);
			$this->_conf->setValue("joomfish.sef_lang", false);
	
		}
		else
		{
			$url = $this->_cachedGetRoute($href, $language->code);
		}
	
		return $url; */
	
	}

	
	/*
	 * if joomfish sef hosts are active replace prefix with sef host
	 * 
	 * @param absolute url including host 
	 * @return fixed url
	 */

	public function prefixToHost(&$url)
	{
		// I may need to use absolute URL here is using subdomains for language switching
		// this forces a full absolute URL
		// Make secure to force router to add schema and host
		// TODO watch that Joomla if introduces a new https host in the config that it is handled correctly


		//$this->_conf->setValue("joomfish.sef_host", false);

		// Annoying thing is that this 'caches' the prefix as a static so we can't change the domain easily
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

	private function _getJFMenuItems($lang, $getOriginals=true, $currentLangMenuItems=false)
	{
		static $instance;
		
		if (!isset($instance))
		{
			$instance = array();

			if (!$currentLangMenuItems)
			{
				JError::raiseWarning('SOME_ERROR_CODE', "Error translating Menus - missing currentLangMenuItems");
				return false;
			}


			// This is really annoying in PHP5 - an array of stdclass objects is copied as an array of references
			// I tried doing this as a stdclass and cloning but it didn't seek to work.
			$instance["raw"] = serialize($currentLangMenuItems);

			$defLang = $this->_conf->getValue("config.jflang");
			$instance[$defLang]["originals"] = unserialize($instance["raw"]);
		}

		if (!isset($instance[$lang]))
		{

			$db = JFactory::getDBO();

			$query	= $db->getQuery(true);

			$query->select('m.id, m.menutype, m.title, m.alias, m.path AS route, m.link, m.type, m.level');
			$query->select('m.browserNav, m.access, m.params, m.home, m.img, m.template_style_id, m.component_id, m.parent_id');
			$query->select('m.language');
			$query->select('e.element as component');
			$query->from('#__menu AS m');
			$query->leftJoin('#__extensions AS e ON m.component_id = e.extension_id');
			$query->where('m.published = 1');
			$query->where('m.parent_id > 0');
			$query->where('m.client_id = 0');
			$query->order('m.lft');

			$user = JFactory::getUser();
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('m.access IN (' . $groups . ')');

			// Set the query
			$db->setQuery($query);
			$testquery = (string)$query;
			if (!($menu = $db->loadObjectList('id', 'stdClass', true, $lang)))
			{
				JError::raiseWarning('SOME_ERROR_CODE', "Error loading Menus: " . $db->getErrorMsg());
				return false;
			}

			$activemenu = JFactory::getApplication('site')->getMenu()->getActive();
			
			
			// translacija zamenja komplet menu item, torej se sfuka id, parent id..
			if ($activemenu && isset($activemenu->id) && $activemenu->id > 0 && array_key_exists($activemenu->id, $menu))
			{
				$newmenu = array();
				$newmenu[$activemenu->id] = $menu[$activemenu->id];
				while ($activemenu->parent_id != 0 && array_key_exists($activemenu->parent_id, $menu))
				{
					$activemenu = $menu[$activemenu->parent_id];
					$newmenu[$activemenu->id] = $activemenu;
				}
				$menu = $newmenu;
			}

			$this->_setupMenuRoutes($menu);
			//$instance["raw"] = $menu;
			$instance["raw"] = array("rows"=>$menu, "originals"=>$currentLangMenuItems);
			// This is really annoying in PHP5 - an array of stdclass objects is copied as an array of references
			// I tried doing this as a stdclass and cloning but it didn't seek to work.
			$instance["raw"] = serialize($instance["raw"]);
			$instance[$lang] = unserialize($instance["raw"]);


		}
		if ($getOriginals)
		{
			return $instance[$lang]["originals"];
		}
		else
		{
			return $instance[$lang]["rows"];
		}

	}
	
	/*
	 * Re-route menus - find if any menus we are using in the route path are translated
	 */
	
	private function _setupMenuRoutes(&$menus)
	{
		if ($menus)
		{
			uasort($menus, array("self", "_menusort"));
			// first pass translate the route
			foreach ($menus as $key => $menu)
			{
				$menus[$key]->route = $menus[$key]->alias;
			}
			foreach ($menus as $key => $menu)
			{
				//Get parent information
				$parent_route = '';
				$parent_tree = array();
				if (($parent = $menus[$key]->parent_id) && (isset($menus[$parent])) &&
						(is_object($menus[$parent])) && (isset($menus[$parent]->route)) && isset($menus[$parent]->tree))
				{
					$parent_route = $menus[$parent]->route . '/';
					$parent_tree = $menus[$parent]->tree;
				}

				//Create tree
				array_push($parent_tree, $menus[$key]->id);
				$menus[$key]->tree = $parent_tree;

				//Create route
				$route = $parent_route . $menus[$key]->alias;
				$menus[$key]->route = $route;

				//Create the query array
				$url = str_replace('index.php?', '', $menus[$key]->link);
				if (strpos($url, '&amp;') !== false)
				{
					$url = str_replace('&amp;', '&', $url);
				}

				parse_str($url, $menus[$key]->query);
			}
		}

	}
	
	
	/* 
	 * get cleaned variables from routed request
	 * @return array of vars
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



	private static function _menusort(&$a, $b)
	{
		if ($a->level == $b->level)
			return 0;
		return ($a->level > $b->level) ? +1 : -1;

	}



}