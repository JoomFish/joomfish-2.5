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


class TranslateParamsMenu extends TranslateParams
{
	function __construct($original, $translation, $fieldname, $fields=null,$contentElement = null,$forms = null)
	{
		parent::__construct($original, $translation, $fieldname, $fields,$contentElement,$forms);
		$this->component = 'com_menus';
	}

	function editTranslation()
	{
		$this->loadForms();
		echo JHtml::_('sliders.start','params-sliders-'.$this->fieldname, array('useCookie'=>1));
			$fieldSets = $this->forms->trans_form->getFieldsets('request');
			$this->forms->outputFieldset($fieldSets);

			$paramsfieldSets = $this->forms->trans_form->getFieldsets('params');
			$this->forms->outputFieldset($paramsfieldSets);
		echo JHtml::_('sliders.end');
		return;
	}
}


?>
