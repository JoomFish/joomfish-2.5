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
if ( count($langActive)>0 ) {
	//$outString = '<div class="flaggedlist">'."\n";
	//$outString .= '<div class="jflabel">'."\n".'<label for="jflanguageselection" class="jflanguageselection">' .JText::_("JText::_("JFMSELECT")"). '</label>'."\n".'</div>'."\n".'<div class="jfselect">'."\n";
	$langOptions=array();
	$noscriptString='';
	$activeLangImg  = null;
	foreach( $langActive as $language )
	{
		$languageCode = $language->getLanguageCode();
		if( $language->code == $curLanguage->getTag() && !$show_active ) {
			continue;		// Not showing the active language
		}
		$href = JFModuleHTML::createHRef ($language, $params);
		$langImg = JFModuleHTML::getLanguageImageSource($language);
		
		if ($language->code == $curLanguage->getTag() ){
			$activehref=$href;
			$activeLangImg = array( 'img' => $langImg, 'code' => $languageCode, 'name' => $language->title_native );
		}
		if (isset($language->disabled) && $language->disabled){
			$disabled=" disabled='disabled'";
			$noscriptString .= '<span lang="' .$languageCode. '" xml:lang="' .$languageCode. '" style="opacity:0.5" class="opaque">' .$language->title_native. '</span>&nbsp;';
			$langOption=JFModuleHTML::makeOption($href , $language->title_native, $disabled." style='padding-left:22px;background-image: url(\"".JURI::base(true) . '/' . $langImg."\");background-repeat: no-repeat;background-position:center left;opacity:0.5;' class='opaque'" );
		}
		else {
			$disabled="";
			$noscriptString .= '<a href="' .$href. '"><span lang="' .$languageCode. '" xml:lang="' .$languageCode. '">' .$language->title_native. '</span></a>&nbsp;';
			$langOption=JFModuleHTML::makeOption($href , $language->title_native, $disabled." style='padding-left:22px;background-image: url(\"".JURI::base(true) . '/' . $langImg."\");background-repeat: no-repeat;background-position:center left;'" );
		}

		$langOption->iso = $language->iso;
		$langOptions[] = $langOption;

	}

	if( count( $langOptions ) >= 1 ) {

		$outString = '<div id="jflanguageselection">';
		$outString .= '<label for="jflanguageselection" class="jflanguageselection">' .JText::_('MOD_JFLANGUAGESELECTION_JFMSELECT'). '</label>';
		if( $activeLangImg != null ) {
			$outString .='<img src="' .JURI::base(true) . '/' . $activeLangImg['img']. '" alt="' .$activeLangImg['name']. '" title="' .$activeLangImg['name']. '" border="0" class="langImg"/>';
		}
		$langlist = JFModuleHTML::selectList( $langOptions, 'lang', ' class="jflanguageselection" onfocus="jfselectlang=this.selectedIndex;" onchange="if(this.options[this.selectedIndex].disabled){this.selectedIndex=jfselectlang;} else {document.location.replace(this.value);}"', 'value', 'text', $activehref);
		$outString .= ''.$langlist.'';
		$outString .= '</div>'."\n";

		if( $noscriptString != '' ) {
			$outString .= '<noscript>' .$noscriptString. '</noscript>';
		}
	} elseif (count( $langOptions ) == 0) {
		$outString = '<div id="jflanguageselection">';
		$outString .= '</div>';
	}

	echo $outString;
}

if( $inc_jf_css && JFile::exists(JPATH_ROOT.DS.'modules'.DS.'mod_jflanguageselection'.DS.'tmpl'.DS.'mod_jflanguageselection.css') ) {
	$document = JFactory::getDocument();
	$document->addStyleSheet(JURI::base(true).'/modules/mod_jflanguageselection/tmpl/mod_jflanguageselection.css');
}
