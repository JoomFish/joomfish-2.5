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
 * $Id: JFContentModelItem.php 225M 2011-05-26 16:40:14Z (local) $
 * @package joomfish
 * @subpackage Models
 *
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.form.form');


class TranslateModel extends JModel {
	
	
	protected $fields;
	protected $contentElement = null;
	protected $formName = null;
	
	
	
	public function __construct($config = array(),$contentElement = null,$fields = null)
	{
		parent::__construct($config);
		$this->fields = $fields;
		//$this->treatment = isset($contentElement) ? $contentElement->getTreatment() : null; //$treatment;
		$this->contentElement = $contentElement;
		$this->formName = ($this->contentElement ? 'tranlateForm'.$this->contentElement->getTableName() : 'tranlateForm');
		
	}
	
	
	
	protected function populateState()
	{
		parent::populateState();
	}
	

	function getForm()
	{
		if(isset($this->fields))
		{
			//first we must create the xml
			//we can create as JXMLElement or string
			$xml = '<form>';
			
			foreach($this->fields as $field)
			{
				$xml .= '<field ';
				$xml .= 'name="'.$field->Name.'" ';
				$xml .= 'type="'.$field->Type.'" ';
				$xml .= 'label="'.$field->Lable.'" ';
				
				if($field->Type == 'category')
				{
					//we need the scope or extension
					$xml .= 'extension="com_'.$field->Extension.'" ';//$field->Scope.'" '; //i think we add extension to the xml extension is the name in jfield category not scope
					$xml .= 'class="inputbox" ';
					$formfield['class'] ="inputbox" ;
				}
				$xml .= '/>';
			}
			$xml .= '</form>';
			
			$form = JForm::getInstance($this->formName, $xml);
			
			
			return $form;
		}
		return null;
		
	}


	//return JObject
	function getItem($id,$trans = true)
	{
		if(isset($this->fields))
		{
			$item = new JObject();
			
			foreach($this->fields as $field)
			{
				if($trans)
				{
					if(isset($field->translationContent))
					{
						$item_field = $field->translationContent->reference_field;
						$item->$item_field = $field->translationContent->value;
					}
					else
					{
						$item_field = $field->Name;
						$item->$item_field = $field->originalValue;
					}
				}
				else
				{
					$item_field = $field->Name;
					$item->$item_field = $field->originalValue;
				}
			}
			return $item;
		}
		return null;
	
	}

	
	
	/**
	 * Overload Method to get a form object - we MUST NOT use JPATH_COMPONENT
	 *
	 * @param	string	$name		The name of the form.
	 * @param	string	$source		The form source. Can be XML string if file flag is set to false.
	 * @param	array	$options	Optional array of options for the form creation.
	 * @param	boolean	$clear		Optional argument to force load a new form.
	 * @param	string	$xpath		An optional xpath to search for the fields.
	 *
	 * @return	mixed JForm object on success, False on error.
	 *
	 * @see		JForm
	 * @since	11.1
	 */
	protected function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = false)
	{
		// Handle the optional arguments.

		$options['control']	= JArrayHelper::getValue($options, 'control', false);
		// Create a signature hash.
		$hash = md5($source.serialize($options));

		// Check if we can use a previously loaded form.
		if (isset($this->_forms[$hash]) && !$clear) {
			return $this->_forms[$hash];
		}

		// Get the form.
		if (strpos($name, "com_")===0){
			if (strpos($name , ".")>0){
				$component = substr($name, 0, strpos($name , "."));
			}
			else {
				$component = $name;
			}
			$componentpath = JPATH_BASE."/components/".$component;
			JForm::addFormPath($componentpath.'/models/forms');
			JForm::addFieldPath($componentpath.'/models/fields');
		}
		else {
			JForm::addFormPath(JPATH_COMPONENT.'/models/forms');
			JForm::addFieldPath(JPATH_COMPONENT.'/models/fields');
		}

		try {
			/*
			THIS is not more need
			//we must change the name if we want more than one model
			//like translated and orginal
			$form = JForm::getInstance($name.(isset($this->control) && $this->control <> 'jform' ? '_orig' : ''), $source, $options, false, $xpath);
			
			*/
			$form = JForm::getInstance($name, $source, $options, false, $xpath);
			if (isset($options['load_data']) && $options['load_data']) {
				// Get the data for the form.
				$data = $this->loadFormData();
			} else {
				$data = array();
			}
			// Allow for additional modification of the form, and events to be triggered.
			// We pass the data because plugins may require it.
			$this->preprocessForm($form, $data);
			// Load the data into the form after the plugins have operated.
			$form->bind($data);

		} catch (Exception $e) {
			$this->setError($e->getMessage());
			return false;
		}

		// Store the form for later.
		$this->_forms[$hash] = $form;

		return $form;
	}
}

?>
