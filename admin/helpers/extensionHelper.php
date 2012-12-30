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
 * @subpackage helpers
 *
*/


defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * This helper includes various static methods that allow to access simple standard functions.
 * The collection of functions may be used by any extension such as the module, plugins or component
 * of the JoomFish collection.
 * 
 * External extensions may use the helper to refer or interact with the JoomFish extension
 * 
 * @package joomfish
 * @since	2.1
 */
class  JoomfishExtensionHelper  {
	
	private static $imagePath;
	
	/**
	 * Is JoomFish activated and ready to work?
	 * @return	true if the JoomFish extension is correctly installed, configured and activated
	 */
	public static function isJoomFishActive() {
		$db = JFactory::getDBO();
		if (!is_a($db,"JFDatabase")){
			return false;
		}
		return true;
	}
	
	/**
	 * The method cleans the internal image path in order to force a re-check
	 * of images.
	 * @return void
	 */
	public static function cleanImagePathCache() {
		self::$imagePath = null;
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
	public static function getLanguageImageSource($language) {
		
		$params = JComponentHelper::getParams('com_joomfish');
		$media = $params->get('directory_flags', 'media/mod_languages/images');
		$cur_template = JFactory::getApplication()->getTemplate();
		$folder = '';
		$file = '';

		if(!empty($language->image_ext)) {
			$file =  basename($language->image_ext);
			$folder = dirname($language->image_ext);
			
		} elseif (!empty( $language->image)) {
			$file = $language->image . '.gif';
			$folder = '';
		} elseif (!empty( $language->sef)) {
			$file = $language->sef . '.gif';
			$folder = '';
		} else {
			return '';
		}

		if (!self::$imagePath) {
			self::$imagePath = array();
		}
		
		// check template path first
		$path = $folder != '' ? $folder.'/'.$file : $file;
		if (!isset( self::$imagePath[$path] ))
		{
			jimport('joomla.filesystem.file');
			if ( JFile::exists( JPATH_SITE .'/templates/'. $cur_template .'/'.$path ) ) {
				self::$imagePath[$path] = '/templates/'. $cur_template .'/'.$path;
			} elseif ( JFile::exists ( JPATH_SITE .DS. $path )) {
				self::$imagePath[$path] = $path;
			} elseif ( JFile::exists ( JPATH_SITE .DS. $media .DS. $path )) {
				self::$imagePath[$path] = $media .'/'. $path;
			} else {
				self::$imagePath[$path] = $path;
			}
		}
		return ltrim(self::$imagePath[$path],'/');
	}	
}
