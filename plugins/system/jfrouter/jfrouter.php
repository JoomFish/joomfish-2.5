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
 * @subpackage jfrouter
 * @version 2.0
 *
 */

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

// In PHP5 this should be a instance_of check
// Currently Joom!Fish does not need to be active in Administrator
// This might be an extended version
if(JFactory::getApplication()->isAdmin()) {
	return;
}
jimport('joomla.filesystem.file');
// Joom!Fish router only gets activated if essential files are missing
//if ( !file_exists( JPATH_PLUGINS .DS. 'system' .DS. 'jfdatabase' .DS. 'jfdatabase.class.php' )) {
JFactory::getLanguage()->load('com_joomfish', JPATH_ADMINISTRATOR);
if ( !JFile::exists( dirname(__FILE__) .DS. 'contact.php' )) {
	JError::raiseNotice('no_jf_plugin', JText::_('JF_ROUTER_PLUGIN_NOT_INSTALLED'));
	return;
}
if(JFile::exists(JPATH_SITE .DS. 'components' .DS. 'com_joomfish' .DS. 'helpers' .DS. 'defines.php')) {
	require_once( JPATH_SITE .DS. 'components' .DS. 'com_joomfish' .DS. 'helpers' .DS. 'defines.php' );
	jimport('joomfish.manager');
	JLoader::register('JoomFishVersion', JOOMFISH_ADMINPATH .DS. 'version.php' );
	jimport('joomfish.joomfish');
} else {
	JError::raiseNotice('no_jf_extension', JText::_('JF_COMPONENT_NOT_INSTALLED'));
	return;
}
jimport('joomfish.route.jfroute');
jimport('joomla.application.component.model');

/**
 * Language Determination and basic routing for Joomfish
 */
class plgSystemJFRouter extends JPlugin{

	/**
	 * stored configuration from plugin
	 *
	 * @var object configuration information
	 */
	private $_config = null;

	function __construct(& $subject, $config)
	{
		if (JFactory::getApplication()->isAdmin()) {
			// This plugin is only relevant for use within the frontend!
			return;
		}
		
		jimport('joomla.html.parameter');
		parent::__construct($subject, $config);

		// put params in registry so I have easy access to them later
		$conf = JFactory::getConfig();
		$conf->setValue("jfrouter.params", $this->params);

		// Must do this here in case other plugins instantiate language!
		// Get the router
		$app	= JFactory::getApplication();
		$router = $app->getRouter();
		
		// attach build rules for language SEF
		$router->attachBuildRule(array($this, 'routeJFRule'));

		// This gets the language from the router before any other part of Joomla can load the language !!
		$uri = JURI::getInstance();
		$this->parseJFRule($router, $uri);
				
	}



	/**
	 * Custom handlers to deal with bad component routers e.g. for contact
	 */
	public static function procesCustomBuildRule($router, &$uri){
		$option = $uri->getVar("option","");
		if (strpos($option,"com_")!==0) return;
		$option = substr($option,4);
		$customFile = dirname(__FILE__).DS.$option.".php";
		if (file_exists($customFile)){
			include_once($customFile);
			if (function_exists("JFRouterHelper".ucfirst($option))){
				$function = "JFRouterHelper".ucfirst($option);
				$function ($router, $uri);
			}
		}
	}

	function parseJFRule($router, &$uri){
		//echo "got here too lang = ".$uri->getVar("lang","")."<br/>";
		$route = $uri->getPath();
		$conf = JFactory::getConfig();
		$params = $conf->getValue("jfrouter.params");
		$sefordomain = $params->get("sefordomain","sefprefix");
		$jfm =  JoomFishManager::getInstance();
		$langs = $jfm->getLanguagesIndexedById();


		if ($sefordomain == "domain"){
			$host = $uri->getHost();
			// TODO cache the indexed array
			$rawsubdomains = $params->getValue("sefsubdomain",array());
			$subdomains = array();

			foreach ($rawsubdomains as $domain) {
				list($langid,$domain) = explode("::",$domain,2);
				// if you have inactive languages and are not logged in then skip inactive language
				if (!array_key_exists($langid, $langs)) continue;
				$domain = strtolower(str_replace("http://","",$domain));
				$domain = str_replace("https://","",$domain);
				$domain = preg_replace("#/$#","",$domain);
				//$domain = str_replace("/","",$domain);
				$subdomains[$domain]=$langs[$langid]->shortcode;
			}
			if (array_key_exists($host, $subdomains)){
				$lang = $subdomains[$host];
				// This get over written later - really stupid !!!
				$uri->setVar("lang",$lang);
				JRequest::setVar('lang', $lang );
				// I need to discover language here since menu is loaded in router
				JFRoute::getInstance()->discoverJFLanguage();
				// TODO fix this for HTTPS
				$conf->setValue('config.live_site',"http://".$host);
				$conf->setValue("joomfish.current_host",$host);
				return array("lang"=>$lang);
			}
		}

		else {
			// Consider stripping base path from URI
			/*
			 $live_site = JURI::base();
			$livesite_uri = new JURI($live_site);
			$livesite_path = $livesite_uri->getPath();
			$route = str_replace($livesite_path,"",$route);
			*/

			$sefprefixes = $params->getValue("sefprefixes", array());

			// Workaround if some language prefixes are missing
			if (!is_array($sefprefixes)){
				$sefprefixes = array();
			}
			if (count($sefprefixes)<count($langs)){
				foreach ($sefprefixes as $prefix) {
					list($langid,$prefix) = explode("::",$prefix,2);
					if (array_key_exists($langid,$langs)){
						$langs[$langid]->hasprefix = true;
					}
				}
				foreach ($langs as $lang) {
					if (!isset($lang->hasprefix)){
						$sefprefixes[] = $lang->lang_id."::".$lang->sef;
					}
				}
			}

			$segments = explode('/', $route);
			$seg=0;


			
			while ($seg<count($segments)){
				if (strlen($segments[$seg])==0) {
					$seg++;
					continue;
				}
				foreach ($sefprefixes as $prefix) {
					list($langid,$prefix) = explode("::",$prefix,2);
					// explode off any suffix
					if (strpos($segments[$seg],".")>0 && $segments[$seg] != 'index.php'){
						$segcompare = substr($segments[$seg],0, strpos($segments[$seg],"."));
						// Trap for pdf, feed of html info in the extension
						if (strpos($segments[$seg],$prefix.".")===0){
							$format = str_replace($prefix.".","",$segments[$seg]);
							//$uri->setVar("format",$format);
							//JRequest::setVar('format', $format);
						}
					}
					else {
						$segcompare = $segments[$seg];
					}
					// including fix for suffix based url's and feeds
					if ($conf->getValue('sef_suffix')==1 && $conf->getValue('sef_rewrite')==1 && $conf->getValue('sef') ) {
						for ($l=0; $l<count($segments);$l++) {
							if (!empty ($segments[$l]) ) {
								$format = explode (".",$segments[$l]);
								if (!empty($format[1]) && trim($format[1])!=="" && trim($format[1])!=="php") {
									$uri->setVar("format",$format[1]);
									JRequest::setVar('format', $format[1]);
									break;
								}
							}
						}
					}
					

					// does the segment match the prefix
					if ($segcompare==$prefix){

						// This section forces the current url static to include the language string which means the base tag is correct - but ONLY on the home page
						// restricting this to the homepage means no risk for image paths etc.
						$homepage = true;
						for ($seg2=$seg+1;$seg2<count($segments);$seg2++) {
							$segment = $segments[$seg2];
							if (strlen($segment)>0) $homepage = false;
						}
						if ($homepage){
							$current = JURI::current();
							$uri	 =  JURI::getInstance();
							$current = $uri->toString( array('scheme', 'host', 'port', 'path'));
						}

						unset($segments[$seg]);//array_shift($segments);

						$uri->setPath(implode("/",$segments));

						$lang = $langs[$langid]->shortcode;
						// This get over written later - really stupid !!!
						$uri->setVar("lang",$lang);

						JRequest::setVar('lang', $lang);
						// I need to discover language here since menu is loaded in router
						JFRoute::getInstance()->discoverJFLanguage();
						return array("lang"=>$lang);
					}
				}

				$seg++;
			}
			
			
			
			
			
		}
		JFRoute::getInstance()->discoverJFLanguage();
		return array();
	}



	public function routeJFRule($router, &$uri){

		jimport('joomla.html.parameter');
		$registry = JFactory::getConfig();
		$multilingual_support= $registry->getValue("config.multilingual_support",false);
		$jfLang = $registry->getValue("joomfish_language", false);
		$jfm =  JoomFishManager::getInstance();
		$langs = $jfm->getLanguagesIndexedById();

		if ($multilingual_support && $jfLang){
			if ($uri->getVar("lang","")==""){
				$uri->setVar("lang",($jfLang->shortcode != '') ? $jfLang->shortcode : $jfLang->iso);
			}
			// this is dependent on Joomfish router being first!!
			$lang	= $uri->getVar("lang","");

			// This may not ready at this stage
			$params = $registry->getValue("jfrouter.params");

			// so load plugin parameters directly
			if (is_null($params)){
				$params = JPluginHelper::getPlugin("system", "jfrouter");
				$params = new JRegistry($params->params);
			}

			$sefordomain = $params->get("sefordomain","sefprefix");

			if ($sefordomain == "domain"){
				// If I set config_live_site I actually don't need this function at all let alone this logic ?  Apart from language switcher.
				// TODO cache the indexed array
				$rawsubdomains = $params->getValue("sefsubdomain",array());
				$subdomains = array();

					
				foreach ($rawsubdomains as $domain) {
					list($langid,$domain) = explode("::",$domain,2);
					$domain = strtolower(str_replace("http://","",$domain));
					$domain = str_replace("https://","",$domain);
					$domain = preg_replace("#/$#","",$domain);
					//$domain = str_replace("/","",$domain);
					$subdomains[$langs[$langid]->shortcode]=$domain;
				}

				if (array_key_exists($lang,$subdomains)) {
					$uri->setHost($subdomains[$lang]);
					$uri->delVar("lang");
					$registry->setValue("joomfish.sef_host",$subdomains[$lang]);

					plgSystemJFRouter::procesCustomBuildRule($router, $uri);
					return;
				}
					
			} else {
				// Get the path data
				$route = $uri->getPath();

				//Add the suffix to the uri
				if($router->getMode() == JROUTER_MODE_SEF && $route && !$lang!==""){

					$jfLang = $jfm->getLanguageByShortcode($lang);
					if (!$jfLang) return;

					$sefprefixes = $params->getValue("sefprefixes", array());

					// Workaround if some language prefixes are missing
					if (!is_array($sefprefixes)){
						$sefprefixes = array();
					}
					if (count($sefprefixes)<count($langs)){
						foreach ($sefprefixes as $prefix) {
							list($langid,$prefix) = explode("::",$prefix,2);
							if (array_key_exists($langid,$langs)){
								$langs[$langid]->hasprefix = true;
							}
						}
						foreach ($langs as $lang) {
							if (!isset($lang->hasprefix)){
								$sefprefixes[] = $lang->lang_id."::".$lang->sef;
							}
						}
					}

					foreach ($sefprefixes as $prefix) {
						list($langid,$prefix) = explode("::",$prefix,2);
						if ($jfLang->lang_id == $langid){
							$uri->setPath($uri->getPath()."/".$prefix);
							$uri->delVar("lang");
							plgSystemJFRouter::procesCustomBuildRule($router, $uri);
							return;
						}
					}
				}
			}

		}
		return;
	}

}
