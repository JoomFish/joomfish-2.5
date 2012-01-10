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

class TranslateForms
{

	
	protected $fields;
	protected $fieldname;
	protected $treatment;

	public $orig_model;
	public $trans_model;
	
	public $trans_form = null;
	public $orig_form = null;
	
	protected $values = null;
	public $orig_item = null;
	public $trans_item = null;
	protected $component = null;

	protected $contentElement = null;

	public function __construct( $fields=null,$contentElement = null)
	{
		//$this->trans_form = $form;
		$this->fields = $fields;
		$this->treatment = isset($contentElement) ? $contentElement->getTreatment() : null; //$treatment;
		$this->contentElement = $contentElement;
		//$this->tablename = $tablename;
		//JLoader::import('models.JFForm', JOOMFISH_ADMINPATH);
		JLoader::import('forms.JFTranslateForm', JoomfishExtensionHelper::getExtraPath('base'));


	}

	//can remove later
	function setStyle()
	{
		?>
		<style>
			div.panel fieldset.joomfish_panelform legend{font-size: small;font-weight: bold;margin-top: 5px; padding-left: 0;}
			table.adminform a.jf.toolbar {float:left;font-size: small;width: 16px;padding:1px;}
			table.adminform a.jf.toolbar span{height: 16px;width: 16px;}
		
			table.adminform a.jf.toolbar span.icon-32-copy {background-repeat: no-repeat;background-size: 16px;}
			table.adminform a.jf.toolbar span.icon-32-delete {background-repeat: no-repeat;background-size: 16px;}
			div.jf.parambuttons {float:right;padding-left: 10px;padding-right: 10px;}
			div.width-10.jf_parambuttons {width: 10%;min-width:54px;}
		</style>
		<?php
	}

	function setForms($translation_id = null, $contentid = null, $where = false)
	{

		
		$this->trans_form = $this->trans_model->getForm();
		// here we get the Form for orig with own formControl ('control'=>'orig_jform')
		// and prepare to get the xml from tran ('childForm'=>$this->trans_form)
		$this->orig_form = new JFTranslateForm('orig_form',array('control'=>'orig_jform','childForm'=>$this->trans_form));

		$jfFields = array();
		foreach($this->fields as $field)
		{
			if($field->Jformname)
			{
				$jfFields[$field->Jformname] = $field;
			}
			else
			{
				$jfFields[$field->Name] = $field;
			}
		}
		$formFields = $this->trans_form->getFieldset();
		$jfFormFields = array();
		if($formFields)
		foreach($formFields as $field)
		{
			if(array_key_exists($field->__get('fieldname'),$jfFields))
			{
				//here we get the fields without group
				$this->orig_form->setFieldAttribute($field->__get('fieldname'),'disabled','true');

				$jfField = $jfFields[$field->__get('fieldname')];

				$this->orig_form->setFieldAttribute($field->__get('fieldname'),'jfOriginal','1');
				$this->trans_form->setFieldAttribute($field->__get('fieldname'),'jfOriginal','0');
				
				
				if(isset($jfField->Extension) && $jfField->Extension)
				{
				//add here 
				/*
				
				*/
				//	$this->orig_form->setFieldAttribute($field->__get('fieldname'),'extension',$jfField->Extension);
				//	$this->trans_form->setFieldAttribute($field->__get('fieldname'),'extension',$jfField->Extension);
				}
				
				// only change the type where need
				// if we want in the edit translation work with JForm 
				// such as in content the field articletext 
				if(isset($jfField->Jformname) && $jfField->Jformname && $jfField->Name <> $jfField->Jformname)
				{
					$this->orig_form->setFieldAttribute($field->__get('fieldname'),'type',$jfField->Type);
					$this->trans_form->setFieldAttribute($field->__get('fieldname'),'type',$jfField->Type);
				}
				
				foreach($jfField as $key => $value)
				{
					if(is_string($value) || $value == '')
					{
						$this->orig_form->setFieldAttribute($field->__get('fieldname'),'jfField_'.$key,$value);
						$this->trans_form->setFieldAttribute($field->__get('fieldname'),'jfField_'.$key,$value);
					}
					elseif($key == 'translationContent')
					{
						foreach($value as $akey => $avalue)
						{
							if(is_string($avalue) || $avalue == '')
							{
								$this->orig_form->setFieldAttribute($field->__get('fieldname'),'jfFieldTranslationContent_'.$akey,$avalue);
								$this->trans_form->setFieldAttribute($field->__get('fieldname'),'jfFieldTranslationContent_'.$akey,$avalue);
							}
			
						}
					}
					elseif($key == 'ebuttons' && $value)
					{
						$this->trans_form->setFieldAttribute($field->__get('fieldname'),'jfFieldTranslationContent_'.$key,json_encode($value));
					}
					
				}
			}
			elseif(array_key_exists($field->__get('group'),$jfFields))
			{
				// here we get the fields with group like attribs
				// need we here to set something?
				//$this->orig_form->setFieldAttribute($field->__get('fieldname'),'disabled','true');
			}
		}
		if($contentid)
		{
			if($where)
			{
				$this->orig_item = $this->orig_model->getItem($contentid,false);
			}
			else
			{
				$this->orig_item = $this->orig_model->getItem($contentid);
			}
			$this->orig_form->bind($this->orig_item);
		}
		
		
		//if($translation_id)
		//{
			if($where)
			{
				$this->trans_item = $this->trans_model->getItem($translation_id,true);
			}
			else
			{
				$this->trans_item = $this->trans_model->getItem($translation_id);
			}
			$this->trans_form->bind($this->trans_item);
		//}
	}

	public function getButtons($field, $buttons = true, $size = 'small')
	{
		$html = '';
		if(strtolower($field->__get('type')) != 'spacer')
		{
		$onclickCopy = 'document.adminForm.'.$this->trans_form->getFormControl().'_'.($field->group ? $field->group.'_' : '').$field->fieldname.'.value = document.adminForm.'.$this->orig_form->getFormControl().'_'.($field->group ? $field->group.'_' : '').$field->fieldname.'.value;';
		$onclickDelete = 'document.adminForm.'.$this->trans_form->getFormControl().'_'.($field->group ? $field->group.'_' : '').$field->fieldname.'.value = \'\';';
		if($buttons && is_array($buttons))
		{
			if(isset($buttons['copy']))
			{
				$onclickCopy = $buttons['copy'];
			}
			if(isset($buttons['delete']))
			{
				$onclickDelete = $buttons['delete'];
			}
		}
		
		$class = ($size == 'small' ? 'jf ' : '');
		
		$html .= '<div class="'.$class.'parambuttons">';
			$html .= '<a class="'.$class.'toolbar" onclick="'.$onclickCopy.'">';
				$html .= '<span class="icon-32-copy">';
				$html .= '</span>';
				//JText::_( 'COPY' );
			$html .= '</a>';
			$html .= '<a class="'.$class.'toolbar" onclick="'.$onclickDelete.'">';
				$html .= '<span class="icon-32-delete">';
				$html .= '</span>';
					//JText::_( 'DELETE' ); 
			$html .= '</a>';
		$html .= '</div>';
		}
		else
		{
		$html .= '&nbsp;';
		}
		return $html;
	}
	
	public function getLabelInput($field, $attributes = false)
	{
		$html = '';
		$html .= '<div class="width-100 fltlft">';
		if (!$field->hidden):
		$html .= $field->label;
		endif;

		switch(strtolower($field->__get('type')))
		{
			case 'componentlayout':
				$html .= '<script>';
				$html .= "window.addEvent('domready', function() {document.id('".$field->__get('id')."').set('disabled','disabled');});";
				$html .= '</script>';
			break;
			
			case 'radio':
				
				$html .= '<script>';
				$html .= "window.addEvent('domready', function() {document.id('".$field->__get('id')."').getElements('input').each(function(element){
					element.set('disabled','disabled');
					});});";
				$html .= '</script>';
				
			break;
			
			case 'media':
				$this->orig_form->setFieldAttribute($field->__get('fieldname'), 'type', 'text', $field->__get('group'));
			break;

			case 'modal_article':
				$html .= '<script>';
				$html .= "window.addEvent('domready', function() {
						var displayValue = document.id('".$field->__get('id')."_name').value;
						
						document.id('".$field->__get('id')."_id').getParent().getElements('.button2-left').each(function(element)
						{
							element.set('style','display:none;');
						});
					});";
				$html .= '</script>';
			break;
		}
		
		if(strtolower($field->__get('type')) != 'spacer')
		{
			$this->orig_form->setFieldAttribute($field->__get('fieldname'), 'disabled', 'true', $field->__get('group'));
			if(is_array($attributes))
			{
				foreach($attributes as $attribute)
				{
					$this->orig_form->setFieldAttribute($field->__get('fieldname'), $attribute[0], $attribute[1], $field->__get('group'));
				}
			}
		}
		
		$html .= $this->orig_form->getInput($field->__get('fieldname'), $field->__get('group'));
		//$html .= $field->input;
		
		$html .= '</div>';
		return $html;
	}

	function outputFieldset($paramsfieldSets)
	{
		if ($paramsfieldSets)
		{
			$hidden_fields = '';
			foreach ($paramsfieldSets as $name => $fieldSet)
			{
				$counter = 0;
				$label = !empty($fieldSet->label) ? $fieldSet->label : strtoupper($this->component).'_' . $name . '_FIELDSET_LABEL';
				echo JHtml::_('sliders.panel',JText::_($label), $name . '-options'.$this->fieldname);
				if (isset($fieldSet->description) && trim($fieldSet->description)) :
					echo '<p class="tip">' . $this->escape(JText::_($fieldSet->description)) . '</p>';
				endif;
				?>
				<div class="clr"></div>
				<fieldset class="panelform joomfish_panelform">
					<ul class="adminformlist">
						<?php foreach ($this->trans_form->getFieldset($name) as $field) : ?>
						<?php if(strtolower($field->__get('type')) != 'hidden') : $counter++; endif; ?>
						<?php if($counter == 1) : $this->outputDescription(); endif; ?>
						
						<?php if (!$field->hidden) : ?>
						<li>
							<div class="width-100 fltlft">
								<div class="width-40 fltlft">
									<?php echo $field->label; ?>
									<?php echo $field->input; ?>
								</div>
								<div class="width-10 jf_parambuttons fltlft">
									<?php echo $this->getButtons($this->orig_form->getField($field->__get('fieldname'),$field->__get('group'))); ?>
								</div>
								<div class="width-40 fltlft">
								<?php if(strtolower($field->__get('type')) != 'spacer') : ?>
								<?php $this->orig_form->setFieldAttribute($field->__get('fieldname'), 'disabled', 'true', $field->__get('group')); ?>
								<?php endif; ?>
									<?php echo $this->getLabelInput($this->orig_form->getField($field->__get('fieldname'),$field->__get('group'))); ?>
								</div>
							</div>
						</li>
						<?php else : $hidden_fields .= $field->input; ?>
						<?php endif; ?>
						<?php endforeach; ?>
					</ul>
					<?php echo $hidden_fields; ?>
				</fieldset>
			<?php
			}
		}
	}

	function outputDescription()
	{
		?>
		<li>
			<div class="width-100 fltlft">
				<div class="width-40 fltlft">
					<fieldset class="panelform joomfish_panelform">
						<legend><?php echo JText::_('TRANSLATION'); ?></legend>
					</fieldset>
				</div>
				<div class="width-10 jf_parambuttons fltlft">
					&nbsp;
				</div>
				<div class="width-40 fltlft">
					<fieldset class="panelform joomfish_panelform">
						<legend><?php echo JText::_('ORIGINAL'); ?></legend>
					</fieldset>
				</div>
			</div>
		</li>
		<?php
	}

	function outputFieldParams($field,$buttons = true,$attributes = false, $hideTrans = false)
	{
		?>
			<div class="width-100 fltlft">
				<div class="width-40 fltlft">
					<?php if (!$field->hidden): ?>
					<?php echo $field->label; ?>
					<?php endif; ?>
					<?php if ($hideTrans && !$field->__get('value')) : ?>
					<?php $this->trans_form->setFieldAttribute($field->__get('fieldname'),'type','hidden',$field->__get('group')); ?>
					<?php endif; ?>
					<?php echo $this->trans_form->getInput($field->__get('fieldname'), $field->__get('group')); //$field->input; ?>

				</div>
				<div class="width-10 jf_parambuttons fltlft">
					<?php if (!$field->hidden && $buttons) : ?>
					<?php echo $this->getButtons($this->orig_form->getField($field->__get('fieldname'),$field->__get('group')),$buttons); ?>
					<?php else : ?>
					<?php echo '&nbsp;'; ?>
					<?php endif; ?>
					
				</div>
				<div class="width-40 fltlft">
				<?php echo $this->getLabelInput($this->orig_form->getField($field->__get('fieldname'),$field->__get('group')),$attributes); ?>
				</div>
			</div>
		<?php
	}

	/*
	
	output not the params
	output the other fields in edit
	but at this moment not used
	*/
	function outputField(&$k,$fieldForm,$JfField,$buttons = true,$attributes = false)
	{
		/*
		$k is for the different colors for each visible field
		the table output from joomfish views/translate/tmpl/edit
		have for each field 3 rows
		in row 1 is one th colspan 3
		in row 2+3 are 3 td
			one is for the description Original or Translation
			the next is for the field value
			the last for the buttons
	
		*/
		?>
			<div class="width-100 fltlft">
			<?php //set here the label if not hidden?>
				<div class="width-100 fltlft">
					<?php if (!$fieldForm->hidden): ?>
					<?php echo $fieldForm->label; ?>
					<?php endif; ?>
					<?php echo $fieldForm->input; ?>
					
				</div>

				
				<div class="width-100 fltlft">
				<?php echo $this->getLabelInput($this->orig_form->getField($fieldForm->__get('fieldname'),$fieldForm->__get('group')),$attributes); ?>
				</div>
				<div class="width-10 jf_parambuttons fltlft">
					<?php if (!$fieldForm->hidden && $buttons) : ?>
					<?php echo $this->getButtons($this->orig_form->getField($fieldForm->__get('fieldname'),$fieldForm->__get('group')),$buttons); ?>
					<?php else : ?>
					<?php echo '&nbsp;'; ?>
					<?php endif; ?>
				</div>
			</div>
		<?php
	}


	function loadLangComponent()
	{
		$lang = JFactory::getLanguage();
		$lang->load($this->component, JPATH_ADMINISTRATOR);
	}

	public function getForm($form = true)
	{
		if(!isset($this->trans_form) || !isset($this->orig_form))
		{
			$this->initForms();
		}
		return ($form ? $this->trans_form : $this->orig_form);
		
	}

	public function initForms()
	{
		if(!isset($this->trans_form) || !isset($this->orig_form))
		{
			$this->_initForms();
		}
	}

	protected function _initForms()
	{
		$cid = JRequest::getVar('cid', array(0));
		$oldcid = $cid;
		$translation_id = 0;
		if (strpos($cid[0], '|') !== false)
		{
			list($translation_id, $contentid, $language_id) = explode('|', $cid[0]);
		}
		// if we have an existing translation then load this directly!
		// This is important for modules to populate the assignement fields 
		$translation_id = $translation_id?$translation_id : $contentid;
		
		if($this->treatment && isset($this->treatment['model']) && isset($this->treatment['extension']) && !isset($this->treatment['translateForms']))
		{
			$model = $this->treatment['model'];
			$modelName = $this->treatment['modelName'];
			$this->component = $this->treatment['extension'];
			//we must know where to add model path
			$componentpath = JPATH_ADMINISTRATOR."/components/".$this->component;
			
			include_once($componentpath.'/models/'.$model.'.php');
			$this->trans_model = new $modelName();
			$this->orig_model = new $modelName();
			JForm::addFormPath($componentpath.'/models/forms');
			JForm::addFieldPath($componentpath.'/models/fields');
			
			$this->loadLangComponent();
			JTable::addIncludePath($componentpath.DS.'tables');
			
			$this->setForms($translation_id, $contentid);
			/*<!-- TODO this must go to other place -->*/
			$this->setStyle();
		}
		else
		{
			if($this->treatment && isset($this->treatment['extension']))
			{
				//we have an component name
			}

			JLoader::import('models.TranslateModel', JoomfishExtensionHelper::getExtraPath('base'));
		
			// NOW GET THE TRANSLATION - IF AVAILABLE
			$this->trans_model = new TranslateModel(array(),$this->contentElement,$this->fields);
			$this->orig_model = new TranslateModel(array(),$this->contentElement,$this->fields);
			
			$this->component = 'com_joomfish';
			//we must know where to add model path
			$componentpath = JPATH_ADMINISTRATOR."/components/".$this->component;
			
			JForm::addFormPath($componentpath.'/models/forms');
			JForm::addFieldPath($componentpath.'/models/fields');
			
			$this->loadLangComponent();
			JTable::addIncludePath($componentpath.DS.'tables');
			
			$this->setForms($translation_id, $contentid,true);
			//<!-- TODO this must go to other place -->
			$this->setStyle();
			
			/*
			
			//TODO we must add xml
			//echo $this->transparams;
			
			
			
			
			
			
			require_once( JOOMFISH_ADMINPATH .DS. 'models' .DS. 'JFForm.php' );
			TODO foreach $this->fields we must set
			$orig_item = new JObject();
			$orig_item_field = $field->translationContent->reference_field;
			$orig_item->$orig_item_field = $field->originalValue;
								
			$trans_item = new JObject();
			$trans_item_field = $field->translationContent->reference_field;
			$trans_item->$trans_item_field = ($field->translationContent->value ? $field->translationContent->value : $field->originalValue);

			
			
			
			
				$formfield = new JXMLElement('<field></field>');
				$formfield['type'] = $field->Type;
				$formfield['name'] = $field->Name;
				$formfield['label'] = ???;
				$formfield['description']= ???;
				$formfield['class'] = ??? ;

			
			//$trans_form = $this->form;
			
			$this->trans_form->setField($formfield);
			or
			$this->trans_form->load($formfields);
			
			$this->orig_form = new JFForm('orig_form',array('control'=>'orig_jform','childForm'=>$this->trans_form));
			$this->orig_form->bind($orig_item);
			//$this->orig_form->setFieldAttribute($trans_item_field,'disabled','true');
								
			$this->trans_form->bind($trans_item);
			
			*/
		}
	}
}
?>
