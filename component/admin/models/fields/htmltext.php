<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.form.formfield');
JFormHelper::loadFieldClass('editor');

/**
 * Form Field class for the Joomla Platform.
 * Supports an HTML select list of categories
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldHtmltext extends JFormFieldEditor //JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
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
