<?php

/**
 * Joom!Fish - Multi Lingual extention and translation manager for Joomla!
 * Copyright (C) 2003 - 2013, Think Network GmbH, Konstanz, 2007-2009 GWE Systems Ltd
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


// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');

class JFormFieldSefsubdomain extends JFormField
{

	public function getInput()
	{
			if(JPath::find(JPATH_SITE .DS. 'components' .DS. 'com_joomfish' .DS. 'helpers','defines.php')) {
				require_once(JPATH_SITE .DS. 'components' .DS. 'com_joomfish' .DS. 'helpers'.DS.'defines.php');
				jimport('joomfish.manager');
			} else {
				JError::raiseNotice('no_jf_component', JText::_('Joom!Fish component not installed correctly. Plugin not executed'));
			}
			$jfm = JoomFishManager::getInstance();
			$activeLanguages = $jfm->getActiveLanguages();

			$value = $this->value;
			$indexedvalues = array();
			if (!is_array($value)){
				$default = $value;
				foreach ($activeLanguages as $key => $val) {
					$indexedvalues[$key] = $val->lang_id."::".$default; 
				}
			}
			else {
				foreach ($value as $val) {
					list($key,$val) = explode("::",$val,2);
					$indexedvalues[$key] = $val; 					
				}
			}
			$html = "<fieldset  style='clear:left'><table>";
			$html .= "<tr style='font-weight:bold;'><td>".JText::_( 'JFIELD_LANGUAGE_LABEL' )."</td><td>".JText::_( 'JFIELD_LANGUAGE_LABEL' )."</td></tr>";
			foreach ($activeLanguages as $key => $val) {
				$html .= "<tr>";
				$html .= '<td>'.$val->name.'</td><td>';
				$prefix = array_key_exists($val->lang_id,$indexedvalues)? $indexedvalues[$val->lang_id] : ""; 
				$idprefix = $val->lang_id."::".$prefix;
				$html .= "<input type='text' length='10' maxlength='50' id='sefprefix".$val->lang_id."' onblur='document.getElementById(\"hiddensefsubdomain".$val->lang_id."\").value=\"".$val->lang_id."::\"+this.value;' value='".$prefix."' />";
				$html .= "<input type='hidden' id='hiddensefsubdomain".$val->lang_id."' name='".$this->name."[]' value='".$idprefix."' />";
				$html .= "</td></tr>";
			}
			$html .="</table></fieldset>";

			return $html;
		
	}
}
