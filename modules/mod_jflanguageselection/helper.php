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
 * $Id: helper.php 245 2012-02-10 19:06:54Z alex $
 * @package joomfish
 * @subpackage mod_jflanguageselection
 *
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.file');


// Prevent redifinition of class, when module is used in two separate locations
if (!defined('JFMODULE_CLASS'))
{
	define('JFMODULE_CLASS', true);
/*
	class JFMenuSite extends JMenuSite
	{

		public function setItems($items)
		{
			$this->_items = $items;

		}

	}
	
	function changeClass(&$obj,$class_type)
	{
		if(class_exists($class_type,true))
		{
			$obj = unserialize(preg_replace("/^O:[0-9]+:\"[^\"]+\":/i",
					"O:".strlen($class_type).":\"".$class_type."\":", serialize($obj)));
		}
	}
*/
	class JFModuleHTML
	{

		/**
		 * 
		 * @param $value
		 * @param $text
		 * @param $style
		 */
		public static function makeOption($value, $text='', $style='')
		{
			$obj = new stdClass;
			$obj->value = $value;
			$obj->text = $text;
			$obj->style = $style;
			return $obj;

		}

		/**
		 * Generates an HTML select list
		 * @param array An array of objects
		 * @param string The value of the HTML name attribute
		 * @param string Additional HTML attributes for the <select> tag
		 * @param string The name of the object variable for the option value
		 * @param string The name of the object variable for the option text
		 * @param mixed The key that is selected
		 * @returns string HTML for the select list
		 */
		public static function selectList(&$arr, $tag_name, $tag_attribs, $key, $text, $selected=NULL)
		{
			// check if array
			if (is_array($arr))
			{
				reset($arr);
			}

			$html = "\n<select name=\"$tag_name\" $tag_attribs>";
			$count = count($arr);

			for ($i = 0, $n = $count; $i < $n; $i++)
			{
				$k = $arr[$i]->$key;
				$t = $arr[$i]->$text;
				$id = ( isset($arr[$i]->id) ? @$arr[$i]->id : null);

				$extra = ' ' . $arr[$i]->style . " ";
				$extra .= $id ? " id=\"" . $arr[$i]->id . "\"" : '';
				if (is_array($selected))
				{
					foreach ($selected as $obj)
					{
						$k2 = $obj->$key;
						if ($k == $k2)
						{
							$extra .= " selected=\"selected\"";
							break;
						}
					}
				}
				else
				{
					$extra .= ( $k == $selected ? " selected=\"selected\"" : '');
				}
				$html .= "\n\t<option value=\"" . $k . "\"$extra >" . $t . "</option>";
			}
			$html .= "\n</select>\n";

			return $html;

		}

		/**
		 * internal function to generate a new href link
		 * @param	TableJFLanguage	the language
		 * @return	string	new href string
		 */
		public static function _createHRef($language, $modparams)
		{
			// NB I pass the language in order to ensure I use the standard language cache files
			$db = JFactory::getDBO();
			$pfunc = $db->_profile();

			$uri = JURI::getInstance();
			$currenturl = $uri->toString();

			$code = $language->getLanguageCode();
			$app = JFactory::getApplication();
			$router = $app->getRouter();

			$vars = $router->getVars();
			$href = "index.php";
			$hrefVars = '';

			// set lang to correct value
			$vars['lang'] = $code;
			$filter = & JFilterInput::getInstance();

			foreach ($vars as $k => $v)
			{
				if ($hrefVars != "")
				{
					$hrefVars .= "&";
				}
				if (is_array($v))
				{
					$arrayValue = $filter->clean($v, 'array');
					$arrayVars = '';
					foreach (array_keys($arrayValue) as $akey)
					{
						if ($arrayVars != '')
						{
							$arrayVars .= "&";
						}
						$arrayVars .= $k . '[' . $akey . ']=' . $filter->clean($arrayValue[$akey]);
					}
					$hrefVars .= $arrayVars;
				}
				else
				{
					$hrefVars .= $k . '=' . $filter->clean($v);
				}
			}

			// Add the existing variables
			if ($hrefVars != "")
			{
				$href .= '?' . $hrefVars;
			}

			$params = JComponentHelper::getParams("com_joomfish");
			if ($modparams->get("cache_href", 1))
			{
				// special Joomfish database cache
				//$jfm = JoomFishManager::getInstance();
				//$cache = $jfm->getCache($language->code);
				//$url = $cache->get(array("JFModuleHTML", '_createHRef2'), array($currenturl, $href, $code));
				$cache 	= JFactory::getCache('com_joomfish', 'callback');
				$url = $cache->get("JFModuleHTML::_createHRef2", array($currenturl, $href, $code));
				
			}
			else
			{
				$url = JFModuleHTML::_createHRef2($currenturl, $href, $code);
			}
			$db->_profile($pfunc);
			return $url;

		}

		public static function _createHRef2($currenturl, $href, $code)
		{
			// Treat change of language specially because of problems if menu alias is translated
			$registry = JFactory::getConfig();
			$language = $registry->getValue("joomfish_language", null);
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
				$registry = JFactory::getConfig();
				$sefLang = TableJFLanguage::createByShortcode($code, false);
				$registry->setValue("joomfish.sef_lang", $sefLang->code);

				$menu = JFactory::getApplication('site')->getMenu();
				$items = $menu->getMenu();

				// Should really do this with classes and clones - this is a proof of concept
				//changeClass($menu, "JFMenuSite");
				//echo "_items is protected - this is not efficient !!<br/>";
				
				$menu->set('_items', JFModuleHTML::getJFMenu($sefLang->code, false, $menu->getMenu()));
				$url = JFModuleHTML::_route($href, $sefLang);
				// restore the items
				//$menu->set('_items', JFModuleHTML::getJFMenu($language->code, true));
				$menu->set('_items', $items);
				$registry->setValue("joomfish.sef_lang", false);

				/*
				  $menu  = JSite::getMenu(true);
				  if (version_compare(phpversion(), '5.0') >= 0) {
				  $keepmenu = clone($menu);
				  }
				  else {
				  $keepmenu = $menu;
				  }
				  $menu = new JMenuSite();
				  $url = JRoute::_( $href );
				  $registry->setValue("joomfish.sef_lang", false);
				  $menu = $keepmenu;
				  return $url;
				 */
			}
			else
			{
				$url = JFModuleHTML::_route($href, $language);
			}

			return $url;

		}

		public static function _route($href, $sefLang)
		{
			$jfm = JoomFishManager::getInstance();
			$conf = JFactory::getConfig();
			$code = $sefLang->code;
			if ($jfm->getCfg("transcaching", 1) && $code !== $conf->getValue('config.defaultlang'))
			{
				$cache = $jfm->getCache($code);
				// add ssl flag into cache determination
				$uri = JURI::getInstance();
				$url = $cache->get(array("JFModuleHTML", 'getRoute'), array($href, $code, $uri->isSSL()));
			}
			else
			{
				$url = JFModuleHTML::getRoute($href, $code);
			}
			return $url;

		}

		public static function getRoute($href, $code="")
		{
			// I may need to use absolute URL here is using subdomains for language switching
			// this forces a full absolute URL
			// Make secure to force router to add schema and host
			// TODO watch that Joomla if introduces a new https host in the config that it is handled correctly
			$ssl = 1;
			$registry = JFactory::getConfig();
			$registry->setValue("joomfish.sef_host", false);
			// Annoying thing is that this 'caches' the prefix as a static so we can't change the domain easily
			$url = JRoute::_($href, true, $ssl);
			$currenthost = $registry->getValue("joomfish.current_host", false);
			$sefhost = $registry->getValue("joomfish.sef_host", false);
			if ($sefhost && $currenthost)
			{
				$url = str_replace($currenthost, $sefhost, $url);
			}
			// if not secure then return url to unsecure state
			$uri = JURI::getInstance();
			if (!$uri->isSSL())
			{
				$url = str_replace("https://", "http://", $url);
			}
			$registry->setValue("joomfish.sef_host", false);
			return $url;

		}

			public static function getJFMenu($lang, $getOriginals=true, $currentLangMenuItems=false)
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
				
				$registry = JFactory::getConfig();				
				
				// This is really annoying in PHP5 - an array of stdclass objects is copied as an array of references
				// I tried doing this as a stdclass and cloning but it didn't seek to work.
				$instance["raw"] = serialize($currentLangMenuItems);

				$defLang = $registry->getValue("config.jflang");
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

				if (!($menu = $db->loadObjectList('id', 'stdClass', true, $lang)))
				{
					JError::raiseWarning('SOME_ERROR_CODE', "Error loading Menus: " . $db->getErrorMsg());
					return false;
				}
				
				$tempmenu = JFactory::getApplication('site')->getMenu();
				$activemenu = $tempmenu->getActive();
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
				
				JFModuleHTML::_setupMenuRoutes($menu);
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

		public static function _setupMenuRoutes(&$menus)
		{
			if ($menus)
			{
				uasort($menus, array("JFModuleHTML", "_menusort"));
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

		public static function _menusort(&$a, $b)
		{
			if ($a->level == $b->level)
				return 0;
			return ($a->level > $b->level) ? +1 : -1;

		}

		/**
		 * Returns the language image based on the standard media folder (as configured in the component) or template information
		 * The component parameters will be used as folder path within the template or starting with the root directory of your site
		 * If the image is found in the current template + folder this reference is returned. Otherwise the reference from
		 * JPATH_SITE + folder. The reference is not verified if the image exists!
		 *  
		 * @param	$language	JFLnaguage language object including the detailed information
		 * @return	string		Path to the image found
		 */
		public static function getLanguageImageSource($language)
		{
			return JoomfishExtensionHelper::getLanguageImageSource($language);

		}

	}

}

