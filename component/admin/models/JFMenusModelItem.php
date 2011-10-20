<?php
/**
 * Joom!Fish - Multi Lingual extention and translation manager for Joomla!
 * Copyright (C) 2003 - 2011, Think Network GmbH, Munich
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
 * $Id: JFMenusModelItem.php 226 2011-05-27 07:29:41Z alex $
 * @package joomfish
 * @subpackage Models
 *
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

include_once(JPATH_ADMINISTRATOR."/components/com_menus/models/item.php");
class JFMenusModelItem extends MenusModelItem {
	function &getItem($translation=null)
	{
		$params = new JParameter( $translation );
		static $item;
		if (isset($item)) {
			return $item;
		}

		$table = clone(parent::getItem());

		// replace values
		$table->params = $translation;

		// I could pick up the URL here or treat as a special content element field type?
		if ($table->type == 'component'){

			// Note that to populate the initial value of the urlparams
			$conf = JFactory::getConfig();
			$elementTable = $conf->getValue('joomfish.elementTable',false);
			foreach ($elementTable->Fields as $efield) {
				if ($efield->Name=="link" && isset($efield->translationContent->value) && $efield->translationContent->value!==""){
					$uri = new JURI($efield->translationContent->value);
					if ($uri->getVar("option",false)){
						$table->link = $efield->translationContent->value;
					}
				}
			}

			$url = str_replace('index.php?', '', $table->link);
			$url = str_replace('&amp;', '&', $url);
			$table->linkparts = null;
			if(strpos($url, '&amp;') !== false)
			{
			   $url = str_replace('&amp;','&',$url);
			}
			
			parse_str($url, $table->linkparts);

			$db = $this->getDBO();
			if ($component = @$table->linkparts['option']) {
				$query = 'SELECT `extension_id`' .
				' FROM `#__extensions`' .
				' WHERE `link` <> \'\'' .
				' AND `parent` = 0' .
				' AND `element` = "'.$db->getEscaped($component).'"';
				$db->setQuery( $query );
				$table->componentid = $db->loadResult();
			}
		}
		//$values = $params->getProperties(false);
		//print_r($values);
		$item = $table;
		return $item;
	}

}
class JFDefaultMenusModelItem extends MenusModelItem {

	function &getItem()
	{
		static $item;
		if (isset($item)) {
			return $item;
		}

		$table =  parent::getItem();
		$clone = clone($table);
		// get an empty version for the defalut
		JRequest::setVar("edit",false);
		$table = null;
		JRequest::setVar( 'cid',array(0));
		$table =  parent::getItem();
		$item = clone($table);
		$item->component_id = $clone->component_id;
		$item->type = $clone->type;
		$item->menutype = $clone->menutype;

		//$component		= $this->getComponent();

		// restore original
		$table = $clone;

		return $item;
	}

}
?>
