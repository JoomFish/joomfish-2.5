<?php
/**
 * Joom!Fish - Multi Lingual extention and translation manager for Joomla!
 * Copyright (C) 2003 - 2012, Think Network GmbH, Munich
 *
 * All rights reserved. The Joom!Fish project is a set of extentions for
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307,USA.
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -----------------------------------------------------------------------------
 * $Id: TranslateParams.php 225M 2011-05-26 16:40:14Z (local) $
 * @package joomfish
 * @subpackage Models
 *
 */
defined('_JEXEC') or die('Restricted access');

class TranslateFormsMenu extends TranslateForms
{
	function __construct($fields=null,$contentElement = null)
	{
		parent::__construct($fields,$contentElement);
		$this->component = 'com_menus';
	}

	protected function _initForms()
	{
		$this->loadLangComponent();
		
		// for J2.5 beta2 and J2.5 RC1 and J2.5  we need to set the table
		// The Model load this and without nothing found
		JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_menus'.DS.'tables');
		
		$cid = JRequest::getVar('cid', array(0));
		$oldcid = $cid;
		$translation_id = 0;
		$contentid = 0;
		$language_id = 0;
		if (strpos($cid[0], '|') !== false)
		{
			list($translation_id, $contentid, $language_id) = explode('|', $cid[0]);
		}
		
		// if we have an existing translation then load this directly??
		$translation_id = $translation_id?$translation_id : $contentid;
		
		JRequest::setVar("cid", array($translation_id));
		JRequest::setVar("edit", true);


		//JLoader::import('models.TranslateModelMenu', JoomfishExtensionHelper::getExtraPath('base'));
		JLoader::import('TranslateModelMenu', JoomfishExtensionHelper::getExtraPath('models'));
		$this->orig_model = new TranslateModelMenu();
		
		// Get The Original State Data
		// model's populate state method assumes the id is in the request object!
		$oldid = JRequest::getInt("id", 0);
		JRequest::setVar("id", $translation_id);
		// JRequest does this for us!
		$this->orig_model->setState('item.id',$contentid);
		
		//JRequest::setVar("id", $translation_id);
		// NOW GET THE TRANSLATION - IF AVAILABLE
		//$this->trans_model = new JFMenusModelItem();
		$this->trans_model = new TranslateModelMenu();
		$this->trans_model->setState('item.id', $translation_id);
		//$this->trans_model->setState('item.id', $translation_id);


		//setForms load also orig item and bind to orig_form if $contentid is set
		//we can do this also for trans
		
		$this->setForms($translation_id, $contentid); //$translation_id, $contentid
		
		//$orig_item = $this->orig_model->getItem($contentid);
		//$this->orig_form->bind($orig_item);
		
		$cid = $oldcid;
		JRequest::setVar('cid', $cid);
		JRequest::setVar("id", $oldid);

		/*<!-- TODO this must go to other place -->*/
		$this->setStyle();

	}
}

?>
