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
defined('_JEXEC') or die('Restricted access');

JLoader::register('TableJFLanguage', JOOMFISH_ADMINPATH .DS. 'tables' .DS. 'JFLanguage.php' );
jimport('joomla.application.categories');

/**
 * This class provides routing mappings for languages
 *
 * @package		Joom!Fish
 * @subpackage	JFModel
 */

class JFRoute {

	private $_conf;
	private static $_instance = null;
	
	public function __construct() 
	{	
		$this->_conf = JFactory::getConfig();
	}
	
	
	/**
	 * Returns the global JFRoute object, only creating it if it
	 * doesn't already exist.
	 *
	 * @return  JFRoute A JFRoute object.
	 */
	public static function getInstance()
	{
		if (empty(self::$_instance))
		{
			self::$_instance = new JFRoute();
		}
	
		return self::$_instance;
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
	
	/** 
	 * Reroute current url to the translated one
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
		
		// get translated menu and categories
		$menu		= JFactory::getApplication()->getMenu();
		$menu->__construct(); // force re-loading of the menu
		
		// @todo implement better check here, id migh be something unrelated
		$catid 		= null;
		if (isset($vars['catid']))
		{
			$catid 		= $vars['catid'];
		} elseif (isset($vars['id'])) {
			$catid 		= $vars['id'];
		}
		
		if (isset($catid)) {
			$extension	= 'Content';
			if (isset($vars['option'])) {
				$extension	= ucfirst(str_ireplace('com_', '', $vars['option']));
			} 
			$categories = JCategories::getInstance($extension);
			if ($categories) {
				$categories->get((int)$catid, true); // force re-loading of the category
			}
		}
		
		// fix item routes for this item as routing is based on active menu $item->route
		// and translated query doesn't change menu routes
		// -------- not needed as it is done in menu override for all items
		/*$itemid = $vars['Itemid'] ? $vars['Itemid'] : JRequest::getInt('Itemid');
		$menuitems	= & $menu->getMenu();
		$this->fixMenuItemRoutes($menuitems, $itemid);*/
		
		/////// 4. route vars //////////////////////////////////////////////////////////////////
		$varstring 		= 'index.php?'.$uri->buildQuery($vars);
		$absolute 		= $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
		$rvarstring 	= JRoute::_($varstring, true, null);
		// Make sure our URL path begins with a slash.
		if (!preg_match('#^/#', $rvarstring))
		{
			$rvarstring = '/' . $rvarstring;
		}		
		$routedurl 		= $absolute . $rvarstring;
		
		/////// 5. reset everything to the previous state so we don't affect anything //////////
		$vars['lang'] 	= $currentlang;
		$uri->setVar('lang', $currentlang);
		$router->setVars($vars, false);
		$this->switchJFLanguageShortcode($currentlang, false);
		$this->switchJoomlaLanguageLongcode($currentJoomlaLang);
		JFactory::$language = null;
		$menu->__construct();
		if (isset($catid) && $categories) {
			$categories->get((int)$catid, true);
		}
		// fix url if we are using sef domains
		$this->prefixToHost($routedurl);
		
		return $routedurl;
	
	}

	
	/**
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

	/**
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
	
	
	/** 
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
					if (isset($arrayValue[$akey])) {
						$arrayVars [$akey] = $filter->clean($arrayValue[$akey]);
					}
				}
				$cleanvars[$k]	= $arrayVars;
			} else if (isset($v)) {
				$cleanvars[$k]	= ($filter->clean($v));
			}	
		}
		
		return $cleanvars;
	}
	
	/**
	 * Switch Joomfish language by short code
	 * @param short language code
	 * @param also switch joomla language
	 */
	public function switchJFLanguageShortcode($code, $switchjoomlatoo=true) {
		
		$language	= JoomFishManager::getInstance()->getLanguageByShortcode($code);
		$this->_conf->set('joomfish_language', $language);
		$targetcode = is_object($language) ? $language->code : null;
		$this->_conf->set('jflang', $targetcode);
		JRequest::setVar('lang', $code);
		
		if ($switchjoomlatoo===true) {
			$this->switchJoomlaLanguageLongcode($language->code);
		}
	}
	
	/**
	 * Switch Joomfish language by long code
	 * @param long language code
	 * @param also switch joomla language
	 */
	public function switchJFLanguageLongcode($code, $switchjoomlatoo=true) {
		// @todo merge with short code function
		$language	= JoomFishManager::getInstance()->getLanguageByCode($code);
		$this->_conf->set('joomfish_language', $language);
		$targetcode = is_object($language) ? $language->shortcode : null;
		$this->_conf->set('jflang', $code);
		JRequest::setVar('lang', $targetcode);
		
		if ($switchjoomlatoo===true) {
			$this->switchJoomlaLanguageLongcode($code);
		}
	}
	
	/**
	 * Switch Joomla language by long code
	 * @param long language code
	 */
	
	public function switchJoomlaLanguageLongcode($code) {
		$this->_conf->set('language', $code);
		$this->_conf->set('lang_site', $code);
		JRequest::setVar('language', $code);
	}
	
	/*
	 * Discover language to be used by Joomfish and set all necessary variables
	 */
	
	public 	function discoverJFLanguage() {

		static $discovered;
		if (isset($discovered) && $discovered){
			return;
		}
		$discovered=true;

		// Find language without loading strings
		$locale	= $this->_conf->getValue('config.language');

		// Attention - we need to access the site default values
		// #12943 explains that a user might overwrite the orignial settings based on his own profile
		$langparams = JComponentHelper::getParams('com_languages');
		$defLanguage = $langparams->get("site");
		$this->_conf->setValue("config.defaultlang", (isset($defLanguage) && $defLanguage!='') ? $defLanguage : $locale);

		// get params from registry in case function called statically
		$params = $this->_conf->getValue("jfrouter.params");

		$determitLanguage 		= $params->get( 'determitLanguage', 1 );
		$newVisitorAction		= $params->get( 'newVisitorAction', 'browser' );
		$use302redirect			= $params->get( 'use302redirect', 0 );
		$enableCookie			= $params->get( 'enableCookie', 1 );

		// get instance of JoomFishManager to obtain active language list and config values
		$jfm =  JoomFishManager::getInstance();

		$client_lang = '';
		$lang_known = false;
		$jfcookie = JRequest::getVar('jfcookie', null ,"COOKIE");
		if (isset($jfcookie["lang"]) && $jfcookie["lang"] != "") {
			$client_lang = $jfcookie["lang"];
			$lang_known = true;
		}

		$uri = JURI::getInstance();
		if ($requestlang = JRequest::getVar('lang', null ,"REQUEST")){
			if( $requestlang != '' ) {
				$client_lang = $requestlang;
				$lang_known = true;
			}
		}

		// no language choosen - Test plugin e.g. IP lookup tool
		if ( !$lang_known)	{
			// setup Joomfish pluginds
			$dispatcher	   = JDispatcher::getInstance();
			$iplang="";
			JPluginHelper::importPlugin('joomfish');
			$dispatcher->trigger('onDiscoverLanguage', array (& $iplang));
			if ($iplang!=""){
				$client_lang = $iplang;
				$lang_known = true;
			}
		}

		if ( !$lang_known && $determitLanguage &&
				key_exists( 'HTTP_ACCEPT_LANGUAGE', $_SERVER ) && !empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ) {

			switch ($newVisitorAction) {
				// usesing the first defined Joom!Fish language
				case 'joomfish':
					$activeLanguages = $jfm->getActiveLanguages();
					reset($activeLanguages);
					$first = key($activeLanguages);
					$client_lang = $activeLanguages[$first]->getLanguageCode();
					break;

				case 'site':
					// We accept that this default locale might be overwritten by user settings!
					$jfLang = TableJFLanguage::createByJoomla( $locale );
					$client_lang = $jfLang->getLanguageCode();
					break;

					// no language chooses - assume from browser configuration
				case 'browser':
				default:
					// language negotiation by Kochin Chang, June 16, 2004
					// retrieve active languages from database
					$active_iso = array();
					$active_isocountry = array();
					$active_code = array();
					$activeLanguages = $jfm->getActiveLanguages();
					if( count( $activeLanguages ) == 0 ) {
						return;
					}

					foreach ($activeLanguages as $alang) {
						$active_iso[] = $alang->iso;
						if( preg_match('/[_-]/i', $alang->iso) ) {
							$isocountry = preg_split('[_-]',$alang->iso);
							$active_isocountry[] = $isocountry[0];
						}
						$active_code[] = $alang->shortcode;
					}

					// figure out which language to use - browser languages are based on ISO codes
					$browserLang = explode(',', $_SERVER["HTTP_ACCEPT_LANGUAGE"]);

					foreach( $browserLang as $blang ) {
						if( in_array($blang, $active_iso) ) {
							$client_lang = $blang;
							break;
						}
						$shortLang = substr( $blang, 0, 2 );
						if( in_array($shortLang, $active_isocountry) ) {
							$client_lang = $shortLang;
							break;
						}

						// compare with code
						if ( in_array($shortLang, $active_code) ) {
							$client_lang = $shortLang;
							break;
						}
					}
					break;
			}
		}

		// get the name of the language file for joomla
		$jfLang = TableJFLanguage::createByShortcode($client_lang, false);
		if( $jfLang === null && $client_lang!="") {
			$jfLang = TableJFLanguage::createByISO( $client_lang, false );
		}
		else if( $jfLang === null) {
			$jfLang = TableJFLanguage::createByJoomla( $locale );
		}

		if( !$lang_known && $use302redirect ) {
			// using a 302 redirect means that we do not change the language on the fly the first time, but with a clean reload of the page

			$href= "index.php";
			$hrefVars = '';
			$queryString = JRequest::getVar('QUERY_STRING', null ,"SERVER");
			if( !empty($queryString) ) {
				$vars = explode( "&", $queryString );
				if( count($vars) > 0 && $queryString) {
					foreach ($vars as $var) {
						if( preg_match('/=/i', $var ) ) {
							list($key, $value) = explode( "=", $var);
							if( $key != "lang" ) {
								if( $hrefVars != "" ) {
									$hrefVars .= "&amp;";
								}
								// ignore mosmsg to ensure it is visible in frontend
								if( $key != 'mosmsg' ) {
									$hrefVars .= "$key=$value";
								}
							}
						}
					}
				}
			}

			// Add the existing variables
			if( $hrefVars != "" ) {
				$href .= '?' .$hrefVars;
			}

			if( $jfLang->getLanguageCode() != null ) {
				$ulang = 'lang=' .$jfLang->getLanguageCode();
			} else {
				// it's important that we add at least the basic parameter - as of the sef is adding the actual otherwise!
				$ulang = 'lang=';
			}

			// if there are other vars we need to add a & otherwiese ?
			if( $hrefVars == '' ) {
				$href .= '?' . $ulang;
			} else {
				$href .= '&amp;' . $ulang;
			}

			$this->_conf->setValue("config.multilingual_support", true);

			JFactory::getApplication()->setUserState('application.lang',$jfLang->code);
			$this->switchJFLanguageLongcode($jfLang->code, true);

			$href = JRoute::_($href,false);

			header( 'HTTP/1.1 303 See Other' );
			header( "Location: ". $href );
			exit();
		}

		if( isset($jfLang) && $jfLang->code != "" && ($jfLang->active  || $jfm->getCfg("frontEndPreview") )) {
			$locale = $jfLang->code;
		} else {
			$jfLang = TableJFLanguage::createByJoomla( $locale );
			if( !$jfLang->active ) {
				?>
					<div style="background-color: #c00; color: #fff">
						<p style="font-size: 1.5em; font-weight: bold; padding: 10px 0px 10px 0px; text-align: center; font-family: Arial, Helvetica, sans-serif;">
						Joom!Fish config error: Default language is inactive!<br />&nbsp;<br />
						Please check configuration, try to use first active language
						</p>
					</div>
				<?php
		
		
				$activeLanguages = $jfm->getActiveLanguages();
				if( count($activeLanguages) > 0 ) {
					$jfLang = $activeLanguages[0];
					$locale = $jfLang->code;
				} else {
					// No active language defined - using system default is only alternative!
				}
			}
			$client_lang = ($jfLang->shortcode!='') ? $jfLang->shortcode : $jfLang->iso;
		}

		$lang = JFactory::getLanguage();

		// TODO set the cookie domain so that it works for all subdomains
		if ($enableCookie){
			setcookie( "lang", "", time() - 1800, "/" );
			setcookie( "jfcookie", "", time() - 1800, "/" );
			setcookie( "jfcookie[lang]", $client_lang, time()+24*3600, '/' );
		}


		$this->_conf->setValue("config.multilingual_support", true);
		
		JFactory::getApplication()->setUserState('application.lang',$jfLang->code);
		$this->switchJFLanguageLongcode($jfLang->code);
		
		// Force factory static instance to be updated if necessary
		if ($jfLang->code != $lang->getTag()){
			// Must not assign by reference in order to overwrite the existing reference to the static instance of the language
			JFactory::$language=false;
			// get translated menu
			/*$menu		= JFactory::getApplication()->getMenu();
			$menu->__construct();*/ // force re-loading of the menu
			$lang = JFactory::getLanguage();
		}
		// no need to set locale for this ISO code its done by JLanguage

		// overwrite with the valued from $jfLang
		$jfparams = JComponentHelper::getParams("com_joomfish");
		$overwriteGlobalConfig =  $jfparams->get( 'overwriteGlobalConfig', 0 );  // TODO check where this value is set, seems not to be working 
		if($overwriteGlobalConfig ) {
			// We should overwrite additional global variables based on the language parameter configuration
			$params = new JRegistry($jfLang->params);
			$paramarray = $params->toArray();
			foreach ($paramarray as $key=>$val) {
				$this->_conf->setValue("config.".$key,$val);
			}
		}


		JFactory::getApplication()->setLanguageFilter(true);

	}


}