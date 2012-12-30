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
		
		/**
		 * function to generate a new href link
		 * @param	JFLanguage	the language
		 * @param 	Module parameters
		 * @return	string	new href string
		 */
		public static function createHRef( $language, $modparams) {

			$code 		= $language->getLanguageCode();
			$jfrouter 	= JFRoute::getInstance();

			if ($modparams->get("cache_href", 1))
			{	
				$url = $jfrouter->rerouteCurrentUrlCached($code, false);
					
			}
			else
			{
				$url = $jfrouter->rerouteCurrentUrl($code);
			}
				
			return $url;
				
				

		}

	}

}