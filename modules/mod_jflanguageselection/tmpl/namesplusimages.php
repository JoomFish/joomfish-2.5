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
$outString = '<div id="jflanguageselection">';
$outString .= '<ul class="jflanguageselection">';
foreach( $langActive as $language )
{
	$langActive = '';
	if( $language->code == $curLanguage->getTag() ) {
		if( !$show_active ) {
			continue;		// Not showing the active language
		} else {
			$langActive = ' id="active_language"';
		}
	}

	$href = JFModuleHTML::createHRef ($language, $params);

	if (isset($language->disabled) && $language->disabled){
		$outString .= '<li' .$langActive. ' style="opacity:0.5" class="opaque">';
	}
	else {
		$outString .= '<li' .$langActive. '>';
	}
	
	$outString .= '<a href="' .$href. '" >';
	
	if($type == 'namesplusimages') {
		$langImg = JFModuleHTML::getLanguageImageSource($language);
		$outString .='<img src="' .JURI::base(true) . '/' . $langImg. '" alt="' .$language->title_native. '" title="' .$language->title_native. '" border="0" class="langImg"/>';
	}

	if (isset($language->disabled) && $language->disabled){
			$outString .= '<span lang="' .$language->getLanguageCode(). '" xml:lang="' .$language->getLanguageCode(). '" >' .$language->title_native. '</span>';
		}
		else {
			$outString .= '<span lang="' .$language->getLanguageCode(). '" xml:lang="' .$language->getLanguageCode(). '">' .$language->title_native. '</span>';
	}

	$outString .= '</a></li>';
}
$outString .= '</ul></div>';

echo $outString;

if( $inc_jf_css && JFile::exists(JPATH_ROOT.DS.'modules'.DS.'mod_jflanguageselection'.DS.'tmpl'.DS.'mod_jflanguageselection.css') ) {
	$document = JFactory::getDocument();
	$document->addStyleSheet(JURI::base(true).'/modules/mod_jflanguageselection/tmpl/mod_jflanguageselection.css');
}

