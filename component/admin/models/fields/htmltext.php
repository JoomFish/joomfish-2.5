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
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.form.formfield');
JFormHelper::loadFieldClass('editor');

/**
 * Form Field class for the Joomla Platform.
 * Supports an HTML select list of categories
 *
 * @package	 Joomla.Platform
 * @subpackage Form
 * @since		11.1
 */
class JFormFieldHtmltext extends JFormFieldEditor //JFormField
{
	/**
	 * The form field type.
	 *
	 * @var	string
	 * @since 11.1
	 */
	public $type = 'Htmltext';

	protected function getInput()
	{
		$original = $this->form->getFieldAttribute($this->fieldname,'jfOriginal',null,$this->group);
		if($original)
		{
			return $this->value;
		}
		else
		{
			/*
			if we want have the name from the joomfish xml
			we must set here or in TranslateForms:
			
			$Name = $this->form->getFieldAttribute($this->fieldname,'jfField_Name',null,$this->group);
			$this->form->setFieldAttribute($this->fieldname,'name',$Name,$this->group)
			$this->name = $this->formControl.'['.$Name.']'.;//orig is like jform[articletext]
			$this->fieldname = $Name;//orig is like articletext
			$this->id = $this->formControl.'_'.$Name;//orig is like jform_articletext
			
			and also set the JForm data to ??
			
			*/
			
			$Name = $this->form->getFieldAttribute($this->fieldname,'jfField_Name',null,$this->group);
			//$editorFields[] = array( "editor_".$Name, "refField_".$Name );
			$this->element['buttons'] = implode(',',json_decode($this->form->getFieldAttribute($this->fieldname,'jfFieldTranslationContent_ebuttons',null,$this->group),true));
			$this->element['width'] = '100%';
			$this->element['height'] = '300';
			$this->element['cols'] = '70';
			$this->element['rows'] = '15';
			/*
			if we want have the translation value
			we must set this
			*/
			$this->value = $this->form->getFieldAttribute($this->fieldname,'jfFieldTranslationContent_value',null,$this->group);
			return parent::getInput();
		}
	}
}
