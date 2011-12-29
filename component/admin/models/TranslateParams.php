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
 * $Id: TranslateParams.php 225M 2011-05-26 16:40:14Z (local) $
 * @package joomfish
 * @subpackage Models
 *
 */
defined('_JEXEC') or die('Restricted access');

class TranslateParams
{

	protected $origparams;
	protected $defaultparams;
	protected $transparams;
	protected $fields;
	protected $fieldname;
	protected $treatment;
	protected $tablename;

	protected $orig_model;
	protected $trans_model;
	
	protected $trans_form = null;
	protected $orig_form = null;
	
	protected $values = null;
	protected $orig_item = null;
	protected $trans_item = null;
	protected $component = null;

	public function __construct($original, $translation, $fieldname, $fields=null,$contentElement = null) //,$treatment = null,$tablename = null)
	{
		$this->origparams = $original;
		$this->transparams = $translation;
		$this->fieldname = $fieldname;
		$this->fields = $fields;
		$this->treatment = isset($contentElement) ? $contentElement->getTreatment() : null; //$treatment;
		//$this->tablename = $tablename;
		JLoader::import('models.JFForm', JOOMFISH_ADMINPATH);


	}

	public function showOriginal()
	{
		echo $this->origparams;

	}

	public function showDefault()
	{
		echo "";

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

	function setForms($translation_id = null, $contentid  = null)
	{

		$this->trans_form = $this->trans_model->getForm();
		
		$this->orig_form = new JFForm('orig_form',array('control'=>'orig_jform','childForm'=>$this->trans_form));
		
		if($contentid)
		{
			$this->orig_item = $this->orig_model->getItem($contentid);
			$this->orig_form->bind($this->orig_item);
		}
		else
		{
			if ($this->origparams != "")
			{
				$this->origparams = json_decode($this->origparams);
			}
			if (isset($this->origparams->jfrequest)){
				$this->orig_form->bind(array($this->fieldname => $this->origparams, "request" =>$this->origparams->jfrequest));
			}
			else 
			{
				$this->orig_form->bind(array($this->fieldname => $this->origparams));
			}
		}
		
		if($translation_id)
		{
			$this->trans_item = $this->trans_model->getItem($translation_id);
			$this->trans_form->bind($this->trans_item);
		}
		else
		{
			if ($this->transparams != "")
			{
				$this->transparams = json_decode($this->transparams);
			}
			if (isset($this->transparams->jfrequest)){
				$this->trans_form->bind(array($this->fieldname => $this->transparams, "request" =>$this->transparams->jfrequest));
			}
			else
			{
				$this->trans_form->bind(array($this->fieldname => $this->transparams));
			}
		}
	}

	public function getButtons($field, $buttons = true)
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
		
		$html .= '<div class="jf parambuttons">';
			$html .= '<a class="jf toolbar" onclick="'.$onclickCopy.'">';
				$html .= '<span class="icon-32-copy">';
				$html .= '</span>';
				//JText::_( 'COPY' );
			$html .= '</a>';
			$html .= '<a class="jf toolbar" onclick="'.$onclickDelete.'">';
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

	function outputField($field,$buttons = true,$attributes = false)
	{
		?>
			<div class="width-100 fltlft">
				<div class="width-40 fltlft">
					<?php if (!$field->hidden): ?>
					<?php echo $field->label; ?>
					<?php endif; ?>
					<?php echo $field->input; ?>
				</div>
				<div class="width-10 jf_parambuttons fltlft">
					<?php if (!$field->hidden): ?>
					<?php echo $this->getButtons($this->orig_form->getField($field->__get('fieldname'),$field->__get('group')),$buttons); ?>
					<?php endif; ?>
				</div>
				<div class="width-40 fltlft">
				<?php echo $this->getLabelInput($this->orig_form->getField($field->__get('fieldname'),$field->__get('group')),$attributes); ?>
				</div>
			</div>
		<?php
	}


	function loadLangComponent()
	{
		$lang = JFactory::getLanguage();
		$lang->load($this->component, JPATH_ADMINISTRATOR);
	}

	public function editTranslation()
	{
		$returnval = array("editor_" . $this->fieldname, "refField_" . $this->fieldname);
		// parameters : areaname, content, hidden field, width, height, rows, cols
		//editorArea("editor_" . $this->fieldname, $this->transparams, "refField_" . $this->fieldname, '100%;', '300', '70', '15');
		if($this->treatment && isset($this->treatment['model']) && isset($this->treatment['component']))
		{
			$model = $this->treatment['model'];
			$modelName = $this->treatment['modelName'];
			$this->component = $this->treatment['component'];
			//we must know where to add model path
			$componentpath = JPATH_ADMINISTRATOR."/components/".$this->component;
			
			include_once($componentpath.'/models/'.$model.'.php');
			
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
		
			$this->trans_model = new $modelName();
			$this->orig_model = new $modelName();
			JForm::addFormPath($componentpath.'/models/forms');
			JForm::addFieldPath($componentpath.'/models/fields');
			
			$this->loadLangComponent();

			
			JTable::addIncludePath($componentpath.DS.'tables');
			
			$this->setForms($translation_id, $contentid);
			/*<!-- TODO this must go to other place -->*/
			$this->setStyle();

			//translated Params and original Params in one Slider
			echo JHtml::_('sliders.start','params-sliders-'.$this->fieldname, array('useCookie'=>1));
				$paramsfieldSets = $this->trans_form->getFieldsets($this->fieldname);
				$this->outputFieldset($paramsfieldSets);
			echo JHtml::_('sliders.end');
		}
		
		else
		{
			echo $this->transparams;
		}
		return $returnval;

	}

}



class TranslateParams_content extends TranslateParams
{
	function __construct($original, $translation, $fieldname, $fields=null)
	{
		parent::__construct($original, $translation, $fieldname, $fields);
		$this->component = 'com_content';
	}

	function editTranslation()
	{

		$this->loadLangComponent();
		
		$cid = JRequest::getVar('cid', array(0));
		//$oldcid = $cid;
		$translation_id = 0;
		if (strpos($cid[0], '|') !== false)
		{
			list($translation_id, $contentid, $language_id) = explode('|', $cid[0]);
		}
		
		// if we have an existing translation then load this directly??
		$translation_id = $translation_id?$translation_id : $contentid;
		
		/*
		JRequest::setVar("cid", array($contentid));
		JRequest::setVar("edit", true);

		// model's populate state method assumes the id is in the request object!
		$oldid = JRequest::getInt("article_id", 0);
		// Take care of the name of the id for the item
		JRequest::setVar("article_id", $contentid);
		*/
		JLoader::import('models.JFContentModelItem', JOOMFISH_ADMINPATH);
		
		// NOW GET THE TRANSLATION - IF AVAILABLE
		$this->trans_model = new JFContentModelItem();
		$this->orig_model = new JFContentModelItem();
		//$this->trans_model->setState('article.id', $contentid);
		
		$this->setForms(); //$translation_id, $contentid
	
		$this->values = array();
		foreach($this->fields as $field)
		{
			switch($field->Name)
			{
				case 'metadata':
					if ($field->originalValue != "")
					{
						$this->orig_form->bind(array($field->Name => json_decode($field->originalValue)));
					}
					
					if ($field->translationContent->value != "")
					{
						$this->trans_form->bind(array($field->Name => json_decode($field->translationContent->value)));
					}

					$this->values['orginal'][$field->Name] = $field->originalValue;
					$this->values['translated'][$field->Name] = $field->translationContent->value;
				
				break;
				
				case 'metakey':
				case 'metadesc':
				case 'publish_up':
				case 'publish_down':
				case 'created':
				case 'created_by':
				case 'created_by_alias':
				case 'modified':
				case 'modified_by':
				case 'checked_out':
				case 'checked_out_time':
				case 'version':
				case 'hits':
					$this->values['orginal'][$field->Name] = $field->originalValue;
					$this->values['translated'][$field->Name] = $field->translationContent->value;
					if ($field->originalValue != "")
					{
						$this->orig_form->bind(array($field->Name => $field->originalValue));
					}
					
					//we cant set this here, why?
					//$this->orig_form->setFieldAttribute($field->Name, 'disabled', 'true');
					
					if ($field->translationContent->value != "")
					{
						$this->trans_form->bind(array($field->Name => $field->translationContent->value));
					}
				break;
			}
		}
		// reset old values in REQUEST array
		/*
		$cid = $oldcid;
		
		JRequest::setVar('cid', $cid);
		JRequest::setVar("article_id", $oldid);
		
		JRequest::setVar("id", $oldid);
		*/
		/*<!-- TODO this must go to other place -->*/
		$this->setStyle();
		echo JHtml::_('sliders.start','params-sliders-'.$this->fieldname, array('useCookie'=>1));
			// publishing-details
			echo JHtml::_('sliders.panel',JText::_('COM_CONTENT_FIELDSET_PUBLISHING'), 'publishing-details'); ?>
				<fieldset class="panelform joomfish_panelform">
					<ul class="adminformlist">
						<?php $this->outputDescription(); ?>
						<li>
							<?php 
							/*
							we cant set this here, why?
							$this->orig_form->setFieldAttribute('created_by', 'filter', 'unset'); ?>
							<?php $this->orig_form->setFieldAttribute('created_by', 'readonly', 'true'); ?>
							<?php $this->orig_form->setFieldAttribute('created_by', 'class','readonly'); 
							*/?>
							
							
							<?php $buttons = array('copy'=>"document.adminForm.".$this->trans_form->getFormControl()."_created_by_name.value = document.adminForm.".$this->orig_form->getFormControl()."_created_by_name.value; document.adminForm.".$this->trans_form->getFormControl()."_created_by_id.value = document.adminForm.".$this->orig_form->getFormControl()."_created_by_id.value;", 'delete'=>"document.adminForm.".$this->trans_form->getFormControl()."_created_by_name.value = '';document.adminForm.".$this->trans_form->getFormControl()."_created_by_id.value = '';"); ?>
							
							<?php $this->outputField($this->trans_form->getField('created_by'),$buttons,array(array('filter', 'unset'),array('readonly', 'true'),array('class','readonly'))) ?>
						</li>

						<li>
						
							
						
							<?php 
							/*
							we cant set this here, why?
							$this->orig_form->setFieldAttribute('created_by_alias', 'type', 'text'); ?>
							<?php $this->orig_form->setFieldAttribute('created_by_alias', 'readonly', 'true'); ?>
							<?php $this->orig_form->setFieldAttribute('created_by_alias', 'class','readonly'); */?>
							
							
							
							<?php $this->outputField($this->trans_form->getField('created_by_alias'),true,array(array('type', 'text'),array('readonly', 'true'),array('class','readonly'))); ?>
						</li>

						<li>
							<?php $this->outputField($this->trans_form->getField('created')) ?>
						</li>

						<li>
							<?php $this->outputField($this->trans_form->getField('publish_up')); ?>
						</li>

						<li>
							<?php $this->outputField($this->trans_form->getField('publish_down')); ?>
						</li>
	
						<?php if ($this->values['translated']['modified_by']) : ?>
						<li>
							<?php $this->outputField($this->trans_form->getField('modified_by')); ?>
						</li>

						<li>
							<?php $this->outputField($this->trans_form->getField('modified')); ?>
						</li>
					<?php endif; ?>

					<?php if ($this->values['translated']['version']) : ?>
						<li>
							<?php $this->outputField($this->trans_form->getField('version')); ?>
						</li>
					<?php endif; ?>

					<?php if ($this->values['translated']['hits']) : ?>
						<li>
							<?php $this->outputField($this->trans_form->getField('hits')); ?>
						</li>
					<?php endif; ?>
				</ul>
			</fieldset>
		<?php
		
		// params attribs
		$paramsfieldSets = $this->trans_form->getFieldsets($this->fieldname);
			$this->outputFieldset($paramsfieldSets);
		
		//meta-options
		echo JHtml::_('sliders.panel',JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'), 'meta-options'.$this->fieldname);
		?>
			<fieldset class="panelform joomfish_panelform">
				<ul class="adminformlist">
					<li>
						<?php $this->outputField($this->trans_form->getField('metadesc')); ?>
					</li>
					<li>
						<?php $this->outputField($this->trans_form->getField('metakey')); ?>
					</li>
					<?php foreach($this->trans_form->getGroup('metadata') as $field): ?>
					<li>
						<?php $this->outputField($this->trans_form->getField($field->__get('fieldname'),$field->__get('group'))); ?>
					</li>
					<?php endforeach; ?>
				</ul>
			</fieldset>
			<?php
		echo JHtml::_('sliders.end');
		return;
	}
}




class TranslateParams_menu extends TranslateParams
{
	function __construct($original, $translation, $fieldname, $fields=null)
	{
		parent::__construct($original, $translation, $fieldname, $fields);
		$this->component = 'com_menus';
	}

	function editTranslation()
	{

		$this->loadLangComponent();

		$cid = JRequest::getVar('cid', array(0));
		$oldcid = $cid;
		$translation_id = 0;
		if (strpos($cid[0], '|') !== false)
		{
			list($translation_id, $contentid, $language_id) = explode('|', $cid[0]);
		}
		
		// if we have an existing translation then load this directly??
		$translation_id = $translation_id?$translation_id : $contentid;

		JRequest::setVar("cid", array($translation_id));
		JRequest::setVar("edit", true);

		JLoader::import('models.JFMenusModelItem', JOOMFISH_ADMINPATH);
		$this->orig_model = new JFMenusModelItem();


		// Get The Original State Data
		// model's populate state method assumes the id is in the request object!
		$oldid = JRequest::getInt("id", 0);
		JRequest::setVar("id", $translation_id);
		// JRequest does this for us!
		$this->orig_model->setState('item.id',$contentid);
		
		//JRequest::setVar("id", $translation_id);
		// NOW GET THE TRANSLATION - IF AVAILABLE
		$this->trans_model = new JFMenusModelItem();
		$this->trans_model->setState('item.id', $translation_id);
		//$this->trans_model->setState('item.id', $translation_id);


		//setForms load also orig item and bind to orig_form if $contentid is set
		//we can do this also for trans
		
		$this->setForms(null, $contentid); //$translation_id, $contentid
		
		//$orig_item = $this->orig_model->getItem($contentid);
		//$this->orig_form->bind($orig_item);
		
		$cid = $oldcid;
		JRequest::setVar('cid', $cid);
		JRequest::setVar("id", $oldid);

		/*<!-- TODO this must go to other place -->*/
		$this->setStyle();

		
		echo JHtml::_('sliders.start','params-sliders-'.$this->fieldname, array('useCookie'=>1));
			$fieldSets = $this->trans_form->getFieldsets('request');
			$this->outputFieldset($fieldSets);

			$paramsfieldSets = $this->trans_form->getFieldsets('params');
			$this->outputFieldset($paramsfieldSets);
		echo JHtml::_('sliders.end');
		return;
	}
}

class TranslateParams_modules extends TranslateParams
{
	function __construct($original, $translation, $fieldname, $fields=null)
	{
		parent::__construct($original, $translation, $fieldname, $fields);
		$this->component = 'com_modules';
	}
	
	function editTranslation()
	{
		$this->loadLangComponent();
		
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
		
		

		JLoader::import('models.JFModuleModelItem', JOOMFISH_ADMINPATH);
		
		
		/*
		//$values = array();

		JRequest::setVar("cid", array($translation_id));
		JRequest::setVar("edit", true);
		// Get The Original State Data
		// model's populate state method assumes the id is in the request object!
		$oldid = JRequest::getInt("id", 0);
		JRequest::setVar("id", $translation_id);
		
		
		// NOW GET THE TRANSLATION - IF AVAILABLE
		$this->trans_model = new JFModuleModelItem();
		$this->trans_model->setState('module.id', $translation_id);
		
		$this->orig_model = new JFModuleModelItem();
		
		$this->setForms(null, $contentid); // //$translation_id, $contentid
		
		$this->trans_item = $this->trans_model->getItem();
		
		$cid = $oldcid;
		JRequest::setVar('cid', $cid);
		JRequest::setVar("id", $oldid);
*/


		// NOW GET THE TRANSLATION - IF AVAILABLE
		$this->trans_model = new JFModuleModelItem();
		$this->orig_model = new JFModuleModelItem();

		//JRequest::setVar("cid", array($translation_id));
		JRequest::setVar("edit", true);
		$oldid = JRequest::getInt("id", 0);
		JRequest::setVar("id", $translation_id); //without setVar('id we have an redirect to com_modules why?
		//$this->setForms(null, $contentid);
		$this->setForms($translation_id, $contentid); // //$translation_id, $contentid
		//$cid = $oldcid;
		//JRequest::setVar('cid', $cid);
		JRequest::setVar("id", $oldid);

		/*<!-- TODO this must go to other place -->*/
		$this->setStyle();

		echo JHtml::_('sliders.start','params-sliders-'.$this->fieldname, array('useCookie'=>1));
			$fieldSets = $this->trans_form->getFieldsets('params');
			$this->outputFieldset($fieldSets);
		
		echo JHtml::_('sliders.end');
		// menu assignments
		// Initiasile related data.
		require_once JPATH_ADMINISTRATOR.'/components/com_menus/helpers/menus.php';
		require_once JPATH_ADMINISTRATOR.'/components/com_modules/helpers/modules.php';
		$menuTypes = MenusHelper::getMenuLinks();
		?>
		<script type="text/javascript">
			window.addEvent('domready', function(){
				validate();
				document.getElements('select').addEvent('change', function(e){validate();});
			});
			function validate(){
				var value	= document.id('jform_assignment').value;
				var list	= document.id('menu-assignment');
				if(value == '-' || value == '0'){
					$$('.jform-assignments-button').each(function(el) {el.setProperty('disabled', true); });
					list.getElements('input').each(function(el){
						el.setProperty('disabled', true);
						if (value == '-'){
							el.setProperty('checked', false);
						} else {
							el.setProperty('checked', true);
						}
					});
				} else {
					$$('.jform-assignments-button').each(function(el) {el.setProperty('disabled', false); });
					list.getElements('input').each(function(el){
						el.setProperty('disabled', false);
					});
				}
			}
		</script>

		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_MODULES_MENU_ASSIGNMENT'); ?></legend>
			<label id="jform_menus-lbl" for="jform_menus"><?php echo JText::_('COM_MODULES_MODULE_ASSIGN'); ?></label>

			<fieldset id="jform_menus" class="radio">
				<select name="jform[assignment]" id="jform_assignment">
					<?php echo JHtml::_('select.options', ModulesHelper::getAssignmentOptions($this->trans_item->client_id), 'value', 'text', $this->trans_item->assignment, true);?>
				</select>

			</fieldset>

			<label id="jform_menuselect-lbl" for="jform_menuselect"><?php echo JText::_('JGLOBAL_MENU_SELECTION'); ?></label>

			<button type="button" class="jform-assignments-button jform-rightbtn" onclick="$$('.chk-menulink').each(function(el) { el.checked = !el.checked; });">
				<?php echo JText::_('JGLOBAL_SELECTION_INVERT'); ?>
			</button>

			<button type="button" class="jform-assignments-button jform-rightbtn" onclick="$$('.chk-menulink').each(function(el) { el.checked = false; });">
				<?php echo JText::_('JGLOBAL_SELECTION_NONE'); ?>
			</button>

			<button type="button" class="jform-assignments-button jform-rightbtn" onclick="$$('.chk-menulink').each(function(el) { el.checked = true; });">
				<?php echo JText::_('JGLOBAL_SELECTION_ALL'); ?>
			</button>

			<div class="clr"></div>

			<div id="menu-assignment">

			<?php echo JHtml::_('tabs.start','module-menu-assignment-tabs', array('useCookie'=>1));?>
			<?php foreach ($menuTypes as &$type) :
				echo JHtml::_('tabs.panel', $type->title ? $type->title : $type->menutype, $type->menutype.'-details');
				$count 	= count($type->links);
				$i		= 0;
				if ($count) :
				?>
				<ul class="menu-links">
					<?php
					foreach ($type->links as $link) :
						if (trim($this->trans_item->assignment) == '-'):
							$checked = '';
						elseif ($this->trans_item->assignment == 0):
							$checked = ' checked="checked"';
						elseif ($this->trans_item->assignment < 0):
							$checked = in_array(-$link->value, $this->trans_item->assigned) ? ' checked="checked"' : '';
						elseif ($this->trans_item->assignment > 0) :
							$checked = in_array($link->value, $this->trans_item->assigned) ? ' checked="checked"' : '';
						endif;
					?>
					<li class="menu-link">
						<input type="checkbox" class="chk-menulink" name="jform[assigned][]" value="<?php echo (int) $link->value;?>" id="link<?php echo (int) $link->value;?>"<?php echo $checked;?>/>
						<label for="link<?php echo (int) $link->value;?>">
							<?php echo $link->text; ?>
						</label>
					</li>
				<?php if ($count > 20 && ++$i == ceil($count/2)) :?>
				</ul>
				<ul class="menu-links">
					<?php endif; ?>
					<?php endforeach; ?>
				</ul>
				<div class="clr"></div>
				<?php endif; ?>
			<?php endforeach; ?>

			<?php echo JHtml::_('tabs.end');?>
			
			</div>
		</fieldset>
		<?php
		return;

	}
}









/*
**************************************************************************************************************************
THE old



*/



class TranslateParams_xml extends TranslateParams
{
	var $orig_model;
	var $trans_model;

	function showOriginal()
	{
		$output = "";
		$fieldname = 'orig_' . $this->fieldname;
		$output .= $this->origparams->render($fieldname);
		$output .= <<<SCRIPT
		<script language='javascript'>
		function copyParams(srctype, srcfield){
			var orig = document.getElementsByTagName('select');		
			for (var i=0;i<orig.length;i++){
				if (orig[i].name.indexOf(srctype)>=0 && orig[i].name.indexOf("[")>=0){
					// TODO double check the str replacement only replaces one instance!!!
					targetName = orig[i].name.replace(srctype,"refField");					
					target = document.getElementsByName(targetName);
					if (target.length!=1){
						alert(targetName+" problem "+target.length);
					}
					else {
						target[0].selectedIndex = orig[i].selectedIndex;
					}
				}
			}
			var orig = document.getElementsByTagName('input');		
			for (var i=0;i<orig.length;i++){
				if (orig[i].name.indexOf(srctype)>=0 && orig[i].name.indexOf("[")>=0){				
					// treat radio buttons differently 
					if (orig[i].type.toLowerCase()=="radio"){
						//alert( orig[i].id+" "+orig[i].checked);
						targetId = orig[i].id;
						if (targetId){
							targetId = targetId.replace(srctype,"refField");
							target = document.getElementById(targetId);
							if (!target){
								alert("missing target for radio button "+orig[i].name);
							}
							else {
								target.checked = orig[i].checked;
							}
						}
						else {
							alert("missing id for radio button "+orig[i].name);
						}
					}
					else {
						// TODO double check the str replacement only replaces one instance!!!
						targetName = orig[i].name.replace(srctype,"refField");
						target = document.getElementsByName(targetName);
						if (target.length!=1){
							alert(targetName+" problem "+target.length);
						}
						else {
							target[0].value = orig[i].value;
						}
					}
				}
			}		   
			var orig = document.getElementsByTagName('textarea');		
			for (var i=0;i<orig.length;i++){
				if (orig[i].name.indexOf(srctype)>=0 && orig[i].name.indexOf("[")>=0){				
					// TODO double check the str replacement only replaces one instance!!!
					targetName = orig[i].name.replace(srctype,"refField");
					target = document.getElementsByName(targetName);
					if (target.length!=1){
						alert(targetName+" problem "+target.length);
					}
					else {
						target[0].value = orig[i].value;
					}
				}
			}		   
		}
		
		var orig = document.getElementsByTagName('select');		
		for (var i=0;i<orig.length;i++){
			if (orig[i].name.indexOf("$fieldname")>=0){
				orig[i].disabled = true;
			}
		}
		var orig = document.getElementsByTagName('input');		
		for (var i=0;i<orig.length;i++){
			if (orig[i].name.indexOf("$fieldname")>=0){
				orig[i].disabled = true;
			}
		}
		</script>
SCRIPT;
		echo $output;

	}

	function showDefault()
	{
		$output = "<span style='display:none'>";
		$output .= $this->defaultparams->render("defaultvalue_" . $this->fieldname);
		$output .= "</span>\n";
		echo $output;

	}

	function editTranslation()
	{
		//
		echo $this->transparams->render("refField_" . $this->fieldname,'jform');
		//echo $this->origparams->render('orig_' . $this->fieldname,'orig_jform');
		
		return false;

	}

}

class JFParams extends JObject
{
	protected $trans_form = null;
	//var $translateForm=null;
	protected $orig_form = null;
	protected $fieldname = null;
	protected $values = null;
	protected $trans_item = null;
	
	function __construct($trans_form=null, $trans_item=null,$orig_form=null, $originalItem=null,$fieldname,$values)
	{
		$this->trans_form = $trans_form;
		$this->trans_item = $trans_item;
		$this->orig_form = $orig_form;
		$this->fieldname = $fieldname;
		$this->values = $values;
	}
	
	//moved to JFForm
	function makeParambuttons($field) //name)
	{
		$html = '';
		$html .= '<div class="jf parambuttons">';
			$html .= '<a class="jf toolbar" onclick="document.adminForm.'.$this->trans_form->getFormControl().'_'.($field->group ? $field->group.'_' : '').$field->fieldname.'.value = document.adminForm.'.$this->orig_form->getFormControl().'_'.($field->group ? $field->group.'_' : '').$field->fieldname.'.value;">';
				$html .= '<span class="icon-32-copy">';
				$html .= '</span>';
					//JText::_( 'COPY' );
			$html .= '</a>';
			$html .= '<a class="jf toolbar" onclick="document.adminForm.'.$this->trans_form->getFormControl().'_'.($field->group ? $field->group.'_' : '').$field->fieldname.'.value = \'\';">';
				$html .= '<span class="icon-32-delete">';
				$html .= '</span>';
					//JText::_( 'DELETE' ); 
			$html .= '</a>';
		$html .= '</div>';
		return $html;
	}
}


class JFMenuParams extends JFParams //JObject
{
/*
	var $orig_form = null;

	//function __construct($translateForm=null, $translateItem=null,$orig_form=null, $originalItem=null)
	function __construct($form=null, $item=null,$orig_form=null, $originalItem=null,$fieldname)
	{
		$this->trans_form = $form;
		//$this->translateForm = $translateForm;
		$this->orig_form = $orig_form;
		$this->fieldname = $fieldname;
	}
*/

	//TODO rewrite like class TranslateParams function editTranslation
	function render($type,$formControl = 'jform')
	{
		$this->menuform = $this->trans_form;
		
		
		
		//$sliders = & JPane::getInstance('sliders');
		//echo $sliders->startPane('params');
		echo JHtml::_('sliders.start','params-sliders-'.$formControl.$this->fieldname, array('useCookie'=>1)); //,'formControl'=>$formControl));
		$fieldSets = $this->trans_form->getFieldsets('request');
		if ($fieldSets)
		{
			foreach ($fieldSets as $name => $fieldSet)
			{
				$hidden_fields = '';
				$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_MENUS_' . $name . '_FIELDSET_LABEL';
				//echo $sliders->startPanel(JText::_($label), $name . '-options');
				echo JHtml::_('sliders.panel',JText::_($label), $name . '-options'.$formControl.$this->fieldname);
				if (isset($fieldSet->description) && trim($fieldSet->description)) :
					echo '<p class="tip">' . $this->escape(JText::_($fieldSet->description)) . '</p>';
				endif;
				?>
				<style>
					div.panel fieldset.joomfish_panelform {border: 1px solid #CCCCCC;}
				</style>
				<div class="clr"></div>
				
				<div class="width-40 fltlft">
				<fieldset class="panelform joomfish_panelform">
				<legend><?php echo JText::_('TRANSLATION'); ?></legend>
					<ul class="adminformlist">
						<?php foreach ($this->trans_form->getFieldset($name) as $field)
						{ ?>
							<?php if (!$field->hidden)
							{
								echo $field->value;
								?>
								<li><?php echo $field->label; ?>
									<?php echo $field->input; ?></li>
								<?php
							}
							else
							{
								$hidden_fields.= $field->input;
								?>
							<?php } ?>

						<?php } ?>
					</ul>
					<?php echo $hidden_fields; ?>
				</fieldset>
				</div>


				<div class="width-40 fltrt">
				<fieldset class="panelform joomfish_panelform">
				<legend><?php echo JText::_('ORIGINAL'); ?></legend>
					<ul class="adminformlist">
						<?php foreach ($this->orig_form->getFieldset($name) as $field)
						{ ?>
							<?php if (!$field->hidden)
							{
								echo $field->value;
								?>
								<li><?php echo $field->label; ?>
									<?php echo $field->input; ?></li>
								<?php
							}
							else
							{
								$hidden_fields.= $field->input;
								?>
							<?php } ?>

						<?php } ?>
					</ul>
					<?php echo $hidden_fields; ?>
				</fieldset>
				</div>
				<?php
				//echo $sliders->endPanel();
			}
		}

		$paramsfieldSets = $this->trans_form->getFieldsets('params');
		if ($paramsfieldSets)
		{
			foreach ($paramsfieldSets as $name => $fieldSet)
			{
				$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_MENUS_' . $name . '_FIELDSET_LABEL';
				//echo $sliders->startPanel(JText::_($label), $name . '-options');
				echo JHtml::_('sliders.panel',JText::_($label), $name . '-options'.$formControl.$this->fieldname);
				if (isset($fieldSet->description) && trim($fieldSet->description)) :
					echo '<p class="tip">' . $this->escape(JText::_($fieldSet->description)) . '</p>';
				endif;
				?>
				<div class="clr"></div>
				<div class="width-40 fltlft">
				<fieldset class="panelform joomfish_panelform">
				<legend><?php echo JText::_('TRANSLATION'); ?></legend>
					<ul class="adminformlist">
						<?php foreach ($this->trans_form->getFieldset($name) as $field) : ?>
							<li><?php echo $field->label; ?>
								<?php echo $field->input; ?></li>
						<?php endforeach; ?>
					</ul>
				</fieldset>
				</div>
				<div class="width-40 fltrt">
				<fieldset class="panelform joomfish_panelform">
				<legend><?php echo JText::_('ORIGINAL'); ?></legend>
					<ul class="adminformlist">
						<?php foreach ($this->orig_form->getFieldset($name) as $field) : ?>
							<li><?php echo $field->label; ?>
								<?php echo $field->input; ?></li>
						<?php endforeach; ?>
					</ul>
				</fieldset>
				</div>
				<?php
				//echo $sliders->endPanel();
			}
		}
		//echo $sliders->endPane();
		echo JHtml::_('sliders.end');
		return;

	}

}

class OLD_TranslateParams_menu extends TranslateParams_xml
{

	var $_menutype;
	var $_menuViewItem;
	//var $orig_model;
	//var $trans_model;

	//TODO rewrite like class TranslateParams function editTranslation
	function __construct($original, $translation, $fieldname, $fields=null)
	{
		parent::__construct($original, $translation, $fieldname, $fields);
		$lang = JFactory::getLanguage();
		$lang->load("com_menus", JPATH_ADMINISTRATOR);

		$cid = JRequest::getVar('cid', array(0));
		$oldcid = $cid;
		$translation_id = 0;
		if (strpos($cid[0], '|') !== false)
		{
			list($translation_id, $contentid, $language_id) = explode('|', $cid[0]);
		}

		JRequest::setVar("cid", array($contentid));
		JRequest::setVar("edit", true);

		JLoader::import('models.JFMenusModelItem', JOOMFISH_ADMINPATH);
		$this->orig_model = new JFMenusModelItem();


		// Get The Original State Data
		// model's populate state method assumes the id is in the request object!
		$oldid = JRequest::getInt("id", 0);
		JRequest::setVar("id", $contentid);
		// JRequest does this for us!
		//$this->orig_model->setState('item.id',$contentid);
		
		$this->orig_model->setFormControl('orig_jform');
		$jfMenuModelForm = $this->orig_model->getForm();

		// NOW GET THE TRANSLATION - IF AVAILABLE
		$this->trans_model = new JFMenusModelItem();
		$this->trans_model->setState('item.id', $contentid);
		if ($translation != "")
		{
			$translation = json_decode($translation);
		}
		$translationMenuModelForm = $this->trans_model->getForm();
		if (isset($translation->jfrequest)){
			$translationMenuModelForm->bind(array("params" => $translation, "request" =>$translation->jfrequest));
		}
		else {
			$translationMenuModelForm->bind(array("params" => $translation));
		}

		$cid = $oldcid;
		JRequest::setVar('cid', $cid);
		JRequest::setVar("id", $oldid);

		//$this->origparams = new JFMenuParams( $jfMenuModelForm);
		$this->transparams = new JFMenuParams($translationMenuModelForm,null,$jfMenuModelForm,null,$fieldname);

	}

	function showOriginal()
	{
		if ($this->_menutype == "wrapper")
		{
			?>
			<table width="100%" class="paramlist">
				<tr>
					<td width="40%" align="right" valign="top"><span class="editlinktip"><!-- Tooltip -->
							<span onmouseover="return overlib('Link for Wrapper', CAPTION, 'Wrapper Link', BELOW, RIGHT);" onmouseout="return nd();" >Wrapper Link</span></span></td>

					<td align="left" valign="top"><input type="text" name="orig_params[url]" value="<?php echo $this->origparams->get('url', '') ?>" class="text_area" size="30" /></td>
				</tr>
			</table>
			<?php
		}
		parent::showOriginal();

	}

	function editTranslation()
	{
		if ($this->_menutype == "wrapper")
		{
			?>
			<table width="100%" class="paramlist">
				<tr>
					<td width="40%" align="right" valign="top"><span class="editlinktip"><!-- Tooltip -->
							<span onmouseover="return overlib('Link for Wrapper', CAPTION, 'Wrapper Link', BELOW, RIGHT);" onmouseout="return nd();" >Wrapper Link</span></span></td>
					<td align="left" valign="top"><input type="text" name="refField_params[url]" value="<?php echo $this->transparams->get('url', '') ?>" class="text_area" size="30" /></td>
				</tr>
			</table>
			<?php
		}
		parent::editTranslation();

	}

}


class XXJFModuleParams extends JFParams //JObject
{
/*

	protected $form = null;
	protected $item = null;

	function __construct($form=null, $item=null)
	{
		$this->trans_form = $form;
		$this->item = $item;

	}
*/
	function render($type,$formControl = 'jform')
	{
		$sliders = & JPane::getInstance('sliders');
		echo $sliders->startPane('params');
		
		$paramsfieldSets = $this->trans_form->getFieldsets('params');
		if ($paramsfieldSets)
		{
			foreach ($paramsfieldSets as $name => $fieldSet)
			{
				$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_MODULES_' . $name . '_FIELDSET_LABEL';
				echo $sliders->startPanel(JText::_($label), $name . '-options');

				if (isset($fieldSet->description) && trim($fieldSet->description)) :
					echo '<p class="tip">' . $this->escape(JText::_($fieldSet->description)) . '</p>';
				endif;
				?>
				<div class="clr"></div>
				<fieldset class="panelform">
					<ul class="adminformlist">
						<?php foreach ($this->trans_form->getFieldset($name) as $field) : ?>
							<li><?php echo $field->label; ?>
								<?php echo $field->input; ?></li>
						<?php endforeach; ?>
					</ul>
				</fieldset>

				<?php
				echo $sliders->endPanel();
			}
		}
		echo $sliders->endPane();
		
		
		// menu assignments
		// Initiasile related data.
		require_once JPATH_ADMINISTRATOR.'/components/com_menus/helpers/menus.php';
		require_once JPATH_ADMINISTRATOR.'/components/com_modules/helpers/modules.php';
		$menuTypes = MenusHelper::getMenuLinks();
		?>
		<script type="text/javascript">
			window.addEvent('domready', function(){
				validate();
				document.getElements('select').addEvent('change', function(e){validate();});
			});
			function validate(){
				var value	= document.id('jform_assignment').value;
				var list	= document.id('menu-assignment');
				if(value == '-' || value == '0'){
					$$('.jform-assignments-button').each(function(el) {el.setProperty('disabled', true); });
					list.getElements('input').each(function(el){
						el.setProperty('disabled', true);
						if (value == '-'){
							el.setProperty('checked', false);
						} else {
							el.setProperty('checked', true);
						}
					});
				} else {
					$$('.jform-assignments-button').each(function(el) {el.setProperty('disabled', false); });
					list.getElements('input').each(function(el){
						el.setProperty('disabled', false);
					});
				}
			}
		</script>

		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_MODULES_MENU_ASSIGNMENT'); ?></legend>
			<label id="jform_menus-lbl" for="jform_menus"><?php echo JText::_('COM_MODULES_MODULE_ASSIGN'); ?></label>

			<fieldset id="jform_menus" class="radio">
				<select name="jform[assignment]" id="jform_assignment">
					<?php echo JHtml::_('select.options', ModulesHelper::getAssignmentOptions($this->trans_item->client_id), 'value', 'text', $this->trans_item->assignment, true);?>
				</select>

			</fieldset>

			<label id="jform_menuselect-lbl" for="jform_menuselect"><?php echo JText::_('JGLOBAL_MENU_SELECTION'); ?></label>

			<button type="button" class="jform-assignments-button jform-rightbtn" onclick="$$('.chk-menulink').each(function(el) { el.checked = !el.checked; });">
				<?php echo JText::_('JGLOBAL_SELECTION_INVERT'); ?>
			</button>

			<button type="button" class="jform-assignments-button jform-rightbtn" onclick="$$('.chk-menulink').each(function(el) { el.checked = false; });">
				<?php echo JText::_('JGLOBAL_SELECTION_NONE'); ?>
			</button>

			<button type="button" class="jform-assignments-button jform-rightbtn" onclick="$$('.chk-menulink').each(function(el) { el.checked = true; });">
				<?php echo JText::_('JGLOBAL_SELECTION_ALL'); ?>
			</button>

			<div class="clr"></div>

			<div id="menu-assignment">

			<?php echo JHtml::_('tabs.start','module-menu-assignment-tabs', array('useCookie'=>1));?>

			<?php foreach ($menuTypes as &$type) :
				echo JHtml::_('tabs.panel', $type->title ? $type->title : $type->menutype, $type->menutype.'-details');

				$count 	= count($type->links);
				$i		= 0;
				if ($count) :
				?>
				<ul class="menu-links">
					<?php
					foreach ($type->links as $link) :
						if (trim($this->trans_item->assignment) == '-'):
							$checked = '';
						elseif ($this->trans_item->assignment == 0):
							$checked = ' checked="checked"';
						elseif ($this->trans_item->assignment < 0):
							$checked = in_array(-$link->value, $this->trans_item->assigned) ? ' checked="checked"' : '';
						elseif ($this->trans_item->assignment > 0) :
							$checked = in_array($link->value, $this->trans_item->assigned) ? ' checked="checked"' : '';
						endif;
					?>
					<li class="menu-link">
						<input type="checkbox" class="chk-menulink" name="jform[assigned][]" value="<?php echo (int) $link->value;?>" id="link<?php echo (int) $link->value;?>"<?php echo $checked;?>/>
						<label for="link<?php echo (int) $link->value;?>">
							<?php echo $link->text; ?>
						</label>
					</li>
					<?php if ($count > 20 && ++$i == ceil($count/2)) :?>
					</ul><ul class="menu-links">
					<?php endif; ?>
					<?php endforeach; ?>
				</ul>
				<div class="clr"></div>
				<?php endif; ?>
			<?php endforeach; ?>

			<?php echo JHtml::_('tabs.end');?>

			</div>
		</fieldset>
		<?php		
		return;

	}

}

class OLD_TranslateParams_modules extends TranslateParams_xml
{

	function __construct($original, $translation, $fieldname, $fields=null)
	{

		parent::__construct($original, $translation, $fieldname, $fields);
		$lang = JFactory::getLanguage();
		$lang->load("com_modules", JPATH_ADMINISTRATOR);

		$cid = JRequest::getVar('cid', array(0));
		$oldcid = $cid;
		$translation_id = 0;
		if (strpos($cid[0], '|') !== false)
		{
			list($translation_id, $contentid, $language_id) = explode('|', $cid[0]);
		}

		// if we have an existing translation then load this directly!
		// This is important for modules to populate the assignement fields 
		$contentid = $translation_id?$translation_id : $contentid;
		
		JRequest::setVar("cid", array($contentid));
		JRequest::setVar("edit", true);

		JLoader::import('models.JFModuleModelItem', JOOMFISH_ADMINPATH);
		
		$values = array();

		// Get The Original State Data
		// model's populate state method assumes the id is in the request object!
		$oldid = JRequest::getInt("id", 0);
		JRequest::setVar("id", $contentid);
		
		
		$this->orig_model = new JFModuleModelItem();
		$this->orig_model->setFormControl('orig_jform');
		$orig_form = $this->orig_model->getForm();
		
		// NOW GET THE TRANSLATION - IF AVAILABLE
		$this->trans_model = new JFModuleModelItem();
		$this->trans_model->setState('module.id', $contentid);
		if ($translation != "")
		{
			$translation = json_decode($translation);
		}
		$trans_form = $this->trans_model->getForm();
		if (isset($translation->jfrequest)){
			$trans_form->bind(array("params" => $translation, "request" =>$translation->jfrequest));
		}
		else {
			$trans_form->bind(array("params" => $translation));
		}

		$cid = $oldcid;
		JRequest::setVar('cid', $cid);
		JRequest::setVar("id", $oldid);

		$this->transparams = new JFModuleParams($trans_form, $this->trans_model->getItem(),$orig_form,null,$fieldname,$values);
		//$this->transparams = new JFModuleParams($orig_form);
	}

	function showOriginal()
	{
		parent::showOriginal();

		$output = "";
		if ($this->origparams->getNumParams('advanced'))
		{
			$fieldname = 'orig_' . $this->fieldname;
			$output .= $this->origparams->render($fieldname, 'advanced');
		}
		if ($this->origparams->getNumParams('other'))
		{
			$fieldname = 'orig_' . $this->fieldname;
			$output .= $this->origparams->render($fieldname, 'other');
		}
		if ($this->origparams->getNumParams('legacy'))
		{
			$fieldname = 'orig_' . $this->fieldname;
			$output .= $this->origparams->render($fieldname, 'legacy');
		}
		echo $output;

	}


	function editTranslation()
	{
		parent::editTranslation();

	}

}

class JFContentParams extends JFParams //JObject
{
/*	var $form = null;
	//var $translateForm=null;
	var $orig_form = null;
	var $fieldname = null;
	var $values = null;
	
	//JFContentParams($trans_form,null,$orig_form,null,$fieldname,$values);
	function __construct($form=null, $item=null,$orig_form=null, $originalItem=null,$fieldname,$values)
	{
		$this->trans_form = $form;
		$this->orig_form = $orig_form;
		$this->fieldname = $fieldname;
		$this->values = $values;
	}
	
	function makeParambuttons($fieldname)
	{
		$html = '';
		$html .= '<div class="jf parambuttons">';
			$html .= '<a class="jf toolbar" onclick="document.adminForm.'.$this->trans_form->getFormControl().'_'.$fieldname.'.value = document.adminForm.'.$this->orig_form->getFormControl().'_'.$fieldname.'.value;">';
				$html .= '<span class="icon-32-copy">';
				$html .= '</span>';
					//JText::_( 'COPY' );
			$html .= '</a>';
			$html .= '<a class="jf toolbar" onclick="document.adminForm.'.$this->trans_form->getFormControl().'_'.$fieldname.'.value = \'\';">';
				$html .= '<span class="icon-32-delete">';
				$html .= '</span>';
					//JText::_( 'DELETE' ); 
			$html .= '</a>';
		$html .= '</div>';
		return $html;
	}
*/

	function render($type,$formControl = 'jform')
	{
		//translated Params and original Params in one Slider
		echo JHtml::_('sliders.start','params-sliders-'.$formControl.$this->fieldname, array('useCookie'=>1));
		?>
		<!-- TODO this must go to other place -->
		<style>
			div.panel fieldset.joomfish_panelform legend{font-size: small;font-weight: bold;margin-top: 5px; padding-left: 0;}
			table.adminform a.jf.toolbar {float:left;font-size: small;width: 16px;padding:1px;}
			table.adminform a.jf.toolbar span{height: 16px;width: 16px;}
		
			table.adminform a.jf.toolbar span.icon-32-copy {background-repeat: no-repeat;background-size: 16px;}
			table.adminform a.jf.toolbar span.icon-32-delete {background-repeat: no-repeat;background-size: 16px;}
			div.jf.parambuttons {float:right;padding-left: 10px;}
		
		</style>
		<?php echo JHtml::_('sliders.panel',JText::_('COM_CONTENT_FIELDSET_PUBLISHING'), 'publishing-details'); ?>
			<div class="width-40 fltlft">
				<fieldset class="panelform joomfish_panelform">
				<legend><?php echo JText::_('TRANSLATION'); ?></legend>
				<ul class="adminformlist">
					<li>
						<?php echo $this->trans_form->getLabel('created_by'); ?>
						<?php echo $this->trans_form->getInput('created_by'); ?>
					</li>

					<li>
						<?php echo $this->trans_form->getLabel('created_by_alias'); ?>
						<?php echo $this->trans_form->getInput('created_by_alias'); ?>
					</li>

					<li>
						<?php echo $this->trans_form->getLabel('created'); ?>
						<?php echo $this->trans_form->getInput('created'); ?>
					</li>

					<li>
						<?php echo $this->trans_form->getLabel('publish_up'); ?>
						<?php echo $this->trans_form->getInput('publish_up'); ?>
					</li>

					<li>
						<?php echo $this->trans_form->getLabel('publish_down'); ?>
						<?php echo $this->trans_form->getInput('publish_down'); ?>
					</li>

					<?php if ($this->values['translated']['modified_by']) : ?>
						<li>
							<?php echo $this->trans_form->getLabel('modified_by'); ?>
							<?php echo $this->trans_form->getInput('modified_by'); ?>
						</li>

						<li>
							<?php echo $this->trans_form->getLabel('modified'); ?>
							<?php echo $this->trans_form->getInput('modified'); ?>
						</li>
					<?php endif; ?>

					<?php if ($this->values['translated']['version']) : ?>
						<li>
							<?php echo $this->trans_form->getLabel('version'); ?>
							<?php echo $this->trans_form->getInput('version'); ?>
						</li>
					<?php endif; ?>

					<?php if ($this->values['translated']['hits']) : ?>
						<li>
							<?php echo $this->trans_form->getLabel('hits'); ?>
							<?php echo $this->trans_form->getInput('hits'); ?>
						</li>
					<?php endif; ?>
				</ul>
			</fieldset>
			</div>
				<!--<div class="width-40 fltrt">-->
				<div class="width-40 fltlft">
				<fieldset class="panelform joomfish_panelform">
				<legend><?php echo JText::_('ORIGINAL'); ?></legend>
				<ul class="adminformlist">
					<?php //$this->orig_form->setFieldAttribute('created_by', 'type', 'text'); ?>
					<?php $this->orig_form->setFieldAttribute('created_by', 'filter', 'unset'); ?>
					<?php $this->orig_form->setFieldAttribute('created_by', 'readonly', 'true'); ?>
					<?php $this->orig_form->setFieldAttribute('created_by', 'class','readonly'); ?>

					<li>
						<div class="width-100 fltlft">
						<?php echo $this->orig_form->getLabel('created_by'); ?>
						<?php echo $this->orig_form->getInput('created_by'); ?>
						<!-- TODO ms: 
						add copy and delete button for each field only if needed
						only here for example
						we can add for each panel an global where copy delete all fields in the panel?
						 -->
						<?php
						/*
						*/
						?>
						<div class="jf parambuttons">
						<a class="jf toolbar" onclick="document.adminForm.<?php echo $this->trans_form->getFormControl();?>_created_by_name.value = document.adminForm.<?php echo $this->orig_form->getFormControl();?>_created_by_name.value;
						document.adminForm.<?php echo $this->trans_form->getFormControl();?>_created_by_id.value = document.adminForm.<?php echo $this->orig_form->getFormControl();?>_created_by_id.value;
						">
							<span class="icon-32-copy">
							</span>
							<?php //echo JText::_( 'COPY' ); ?>
						</a>
						<a class="jf toolbar" onclick="document.adminForm.<?php echo $this->trans_form->getFormControl();?>_created_by_name.value = '';document.adminForm.<?php echo $this->trans_form->getFormControl();?>_created_by_id.value = '';">
							<span class="icon-32-delete">
							</span>
							<?php //echo JText::_( 'DELETE' ); ?>
						</a>
						</div>
						</div>
						<?php
						/*
						*/
						?>
						
					</li>
					
					
					
					<?php $this->orig_form->setFieldAttribute('created_by_alias', 'type', 'text'); ?>
					<?php $this->orig_form->setFieldAttribute('created_by_alias', 'readonly', 'true'); ?>
					<?php $this->orig_form->setFieldAttribute('created_by_alias', 'class','readonly'); ?>
					<li>
					<div class="width-100 fltlft">
						<?php echo $this->orig_form->getLabel('created_by_alias'); ?>
						<?php echo $this->orig_form->getInput('created_by_alias'); ?>
						<?php echo $this->makeParambuttons( $this->orig_form->getField('created_by_alias')); ?>
					</div>
					</li>

					
					
					<li>
					<div class="width-100 fltlft">
						<?php echo $this->orig_form->getLabel('created'); ?>
						<?php echo $this->orig_form->getInput('created'); ?>
						<?php echo $this->makeParambuttons($this->orig_form->getField('created')); ?>
					</div>
					</li>

					<li>
					<div class="width-100 fltlft">
						<?php echo $this->orig_form->getLabel('publish_up'); ?>
						<?php echo $this->orig_form->getInput('publish_up'); ?>
						<?php echo $this->makeParambuttons($this->orig_form->getField('publish_up')); ?>
					</div>
					</li>

					<li>
					<div class="width-100 fltlft">
						<?php echo $this->orig_form->getLabel('publish_down'); ?>
						<?php echo $this->orig_form->getInput('publish_down'); ?>
						<?php echo $this->makeParambuttons($this->orig_form->getField('publish_down')); ?>
						</div>
					</li>

					<?php if ($this->values['orginal']['modified_by']) ://if ($this->item->modified_by) : ?>
						<li>
						<div class="width-100 fltlft">
							<?php echo $this->orig_form->getLabel('modified_by'); ?>
							<?php echo $this->orig_form->getInput('modified_by'); ?>
							
							<?php
								if ($this->values['translated']['modified_by']) :
									echo $this->makeParambuttons($this->orig_form->getField('modified_by'));
								endif;
							?>
							</div>
						</li>

						<li>
						<div class="width-100 fltlft">
							<?php echo $this->orig_form->getLabel('modified'); ?>
							<?php echo $this->orig_form->getInput('modified'); ?>
							<?php
								if ($this->values['translated']['modified_by']) :
									echo $this->makeParambuttons($this->orig_form->getField('modified'));
								endif;
							?>
							</div>
						</li>
					<?php endif; ?>

					<?php if ($this->values['orginal']['version']) ://if ($this->item->version) : ?>
						<li>
						<div class="width-100 fltlft">
							<?php echo $this->orig_form->getLabel('version'); ?>
							<?php echo $this->orig_form->getInput('version'); ?>
							<?php
								if ($this->values['translated']['version']) :
									echo $this->makeParambuttons($this->orig_form->getField('version'));
								endif;
							?>
							</div>
						</li>
					<?php endif; ?>

					<?php if ($this->values['orginal']['hits']) ://if ($this->item->hits) : ?>
						<li>
						<div class="width-100 fltlft">
							<?php echo $this->orig_form->getLabel('hits'); ?>
							<?php echo $this->orig_form->getInput('hits'); ?>
							<?php
								if ($this->values['translated']['hits']) :
									echo $this->makeParambuttons($this->orig_form->getField('hits'));
								endif;
							?>
							</div>
						</li>
					<?php endif; ?>
				</ul>
			</fieldset>
			</div>
		<?php
		$paramsfieldSets = $this->trans_form->getFieldsets($this->fieldname);
		if ($paramsfieldSets)
		{
			foreach ($paramsfieldSets as $name => $fieldSet)
			{
				$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_CONTENT_' . $name . '_FIELDSET_LABEL';
				
				echo JHtml::_('sliders.panel',JText::_($label), $name . '-options'.$formControl.$this->fieldname);
				if (isset($fieldSet->description) && trim($fieldSet->description)) :
					echo '<p class="tip">' . $this->escape(JText::_($fieldSet->description)) . '</p>';
				endif;
				?>
				<div class="clr"></div>
				<div class="width-40 fltlft">
				<fieldset class="panelform joomfish_panelform">
				<legend><?php echo JText::_('TRANSLATION'); ?></legend>
					<ul class="adminformlist">
						<?php foreach ($this->trans_form->getFieldset($name) as $field) : ?>
							<li><?php echo $field->label; ?>
								<?php echo $field->input; ?>
								
							</li>
						<?php endforeach; ?>
					</ul>
				</fieldset>
				</div>
				<!--<div class="width-40 fltrt">-->
				<div class="width-40 fltlft">
				<fieldset class="panelform joomfish_panelform">
				<legend><?php echo JText::_('ORIGINAL'); ?></legend>
					<ul class="adminformlist">
						<?php foreach ($this->orig_form->getFieldset($name) as $field) : ?>
							<li>
							<div class="width-100 fltlft">
							<?php echo $field->label; ?>
							<?php 
								echo $field->input;
								if(strtolower($field->__get('type')) == 'componentlayout')
								{
									?>
									<script>
										window.addEvent('domready', function() {
											document.id('<?php echo $field->__get('id'); ?>').set('disabled','disabled');
										});
									</script>
									<?php
								}
								/*
								
								*/
								if (!$field->hidden && strtolower($field->__get('type')) != 'spacer')
								echo 
								//$this->makeParambuttons($field->fieldname);
								$this->makeParambuttons($field);//->fieldname);
							?>
							</div>
							</li>
						<?php endforeach; ?>
					</ul>
				</fieldset>
				</div>
				
				<?php
				
				echo JHtml::_('sliders.panel',JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'), 'meta-options'.$formControl.$this->fieldname);
			?>
			<div class="width-40 fltlft">
				<fieldset class="panelform joomfish_panelform">
				<legend><?php echo JText::_('TRANSLATION'); ?></legend>
					<ul class="adminformlist">
						<li>
							<?php echo $this->trans_form->getLabel('metadesc'); ?>
							<?php echo $this->trans_form->getInput('metadesc'); ?>
						</li>
						<li>
							<?php echo $this->trans_form->getLabel('metakey'); ?>
							<?php echo $this->trans_form->getInput('metakey'); ?>
						</li>
						<?php foreach($this->trans_form->getGroup('metadata') as $field): ?>
						<li>
							<?php if (!$field->hidden): ?>
								<?php echo $field->label; ?>
							<?php endif; ?>
							<?php echo $field->input; ?>
						</li>
						<?php endforeach; ?>
					</ul>
				</fieldset>
			</div>
			
			<!--<div class="width-40 fltrt">-->
				<div class="width-40 fltlft">
				<fieldset class="panelform joomfish_panelform">
				<legend><?php echo JText::_('ORIGINAL'); ?></legend>
					<ul class="adminformlist">
						<li>
						<div class="width-100 fltlft">
							<?php echo $this->orig_form->getLabel('metadesc'); ?>
							<?php echo $this->orig_form->getInput('metadesc'); ?>
							<?php echo $this->makeParambuttons($this->orig_form->getField('metadesc')); ?>
						</div>
						</li>
						<li>
						<div class="width-100 fltlft">
							<?php echo $this->orig_form->getLabel('metakey'); ?>
							<?php echo $this->orig_form->getInput('metakey'); ?>
							<?php echo $this->makeParambuttons($this->orig_form->getField('metakey')); ?>
						</div>
						</li>
						<?php foreach($this->orig_form->getGroup('metadata') as $field): ?>
						<li>
						<div class="width-100 fltlft">
							<?php if (!$field->hidden): ?>
							<?php echo $field->label; ?>
							<?php endif; ?>
							<?php echo $field->input; ?>
							<?php if (!$field->hidden && strtolower($field->__get('type')) != 'spacer'): ?>
								<?php echo $this->makeParambuttons($field);//->fieldname); ?>
							<?php endif; ?>
						</div>
						</li>
						<?php endforeach; ?>
					</ul>
				</fieldset>
			</div>
			<?php
			}
		}
		echo JHtml::_('sliders.end');
		return;
	}

}

class Old_TranslateParams_content extends TranslateParams_xml
{

	//var $orig_contentModelItem;
	//var $trans_contentModelItem;
	
	function __construct($original, $translation, $fieldname, $fields=null)
	{
		parent::__construct($original, $translation, $fieldname, $fields);
		$lang = JFactory::getLanguage();
		$lang->load("com_content", JPATH_ADMINISTRATOR);

		$cid = JRequest::getVar('cid', array(0));
		$oldcid = $cid;
		$translation_id = 0;
		if (strpos($cid[0], '|') !== false)
		{
			list($translation_id, $contentid, $language_id) = explode('|', $cid[0]);
		}
		JRequest::setVar("cid", array($contentid));
		JRequest::setVar("edit", true);

		// model's populate state method assumes the id is in the request object!
		$oldid = JRequest::getInt("article_id", 0);
		// Take care of the name of the id for the item
		JRequest::setVar("article_id", $contentid);
		
		JLoader::import('models.JFContentModelItem', JOOMFISH_ADMINPATH);
		$this->orig_model = new JFContentModelItem();

		// Get The Original form 
		// JRequest does NOT this for us in articles!!
		$this->orig_model->setFormControl('orig_jform');
		$this->orig_model->setState('article.id',$contentid);

		$orig_form = $this->orig_model->getForm();
		if ($original != "")
		{
			$original = json_decode($original);
		}
		if (isset($original->jfrequest))
		{
			$orig_form->bind(array($fieldname => $original, "request" =>$original->jfrequest));
		}
		else 
		{
			$orig_form->bind(array($fieldname => $original));
		}
		
		// NOW GET THE TRANSLATION - IF AVAILABLE
		$this->trans_model = new JFContentModelItem();
		//$this->trans_model->setFormControl('jform');
		$this->trans_model->setState('article.id', $contentid);
		if ($translation != "")
		{
			$translation = json_decode($translation);
		}
		$trans_form = $this->trans_model->getForm();
		if (isset($translation->jfrequest) && $fieldname == 'attribs'){
			$trans_form->bind(array($fieldname => $translation, "request" =>$translation->jfrequest));
		}
		else {
			$trans_form->bind(array($fieldname => $translation));
		}

		$values = array();
		foreach($fields as $field)
		{
			switch($field->Name)
			{
				case 'metadata':
					if ($field->originalValue != "")
					{
						$orig_form->bind(array($field->Name => json_decode($field->originalValue)));
					}
					
					if ($field->translationContent->value != "")
					{
						$trans_form->bind(array($field->Name => json_decode($field->translationContent->value)));
					}
					$values['orginal'][$field->Name] = $field->originalValue; //also json_decode?
					$values['translated'][$field->Name] = $field->translationContent->value; //also json_decode
				break;
				
				case 'metakey':
				case 'metadesc':
				case 'publish_up':
				case 'publish_down':
				case 'created':
				case 'created_by':
				case 'created_by_alias':
				case 'modified':
				case 'modified_by':
				case 'checked_out':
				case 'checked_out_time':
				case 'version':
				case 'hits':
					$values['orginal'][$field->Name] = $field->originalValue;
					$values['translated'][$field->Name] = $field->translationContent->value;
					if ($field->originalValue != "")
					{
						$orig_form->bind(array($field->Name => $field->originalValue));
					}
					$orig_form->setFieldAttribute($field->Name, 'disabled', 'true');
					
					if ($field->translationContent->value != "")
					{
						$trans_form->bind(array($field->Name => $field->translationContent->value));
					}
				break;
			}
		}
			
		$fieldSets = $orig_form->getFieldsets($fieldname);
		foreach ($fieldSets as $name => $fieldSet)
		{
			foreach ($orig_form->getFieldset($name) as $field)
			{
				if(strtolower($field->__get('type')) != 'spacer')
				{
					$orig_form->setFieldAttribute($field->__get('fieldname'), 'disabled', 'true', $field->__get('group'));
					//$orig_form->setFieldAttribute($field->__get('fieldname'), 'readonly', 'true', $field->__get('group'));
					if(strtolower($field->__get('type')) == 'componentlayout' )
					{
					}
					//$orig_form->setFieldAttribute($field->__get('fieldname'), 'readonly', 'true', $field->__get('group'));
					//$orig_form->setFieldAttribute($field->__get('fieldname'), 'class','readonly', $field->__get('group'));
					//$orig_form->setFieldAttribute($field->__get('fieldname'), 'type','text', $field->__get('group'));
				}
			}
		}
		//$orig_form->setFieldAttribute('metadesc', 'disabled', 'true');
		//$orig_form->setFieldAttribute('metakey', 'disabled', 'true');
		
		foreach($orig_form->getGroup('metadata') as $field)
		{
			$orig_form->setFieldAttribute($field->__get('fieldname'), 'readonly', 'true', $field->__get('group'));
			$orig_form->setFieldAttribute($field->__get('fieldname'), 'disabled', 'true', $field->__get('group'));
		}
			
		//here we can hidde fields if we want
		//$orig_form->setFieldAttribute('robots', 'type', 'hidden', 'metadata');
		//$trans_form->setFieldAttribute('robots', 'type', 'hidden', 'metadata');



		// reset old values in REQUEST array
		$cid = $oldcid;
		
		JRequest::setVar('cid', $cid);
		JRequest::setVar("article_id", $oldid);

		$this->transparams = new JFContentParams($trans_form,null,$orig_form,null,$fieldname,$values);
	}

	function showOriginal()
	{
		parent::showOriginal();

		$output = "";
		if ($this->origparams->getNumParams('advanced'))
		{
			$fieldname = 'orig_' . $this->fieldname;
			$output .= $this->origparams->render($fieldname, 'advanced');
		}
		if ($this->origparams->getNumParams('legacy'))
		{
			$fieldname = 'orig_' . $this->fieldname;
			$output .= $this->origparams->render($fieldname, 'legacy');
		}
		echo $output;

	}

	function editTranslation()
	{
		parent::editTranslation();
	}

}

class TranslateParams_components extends TranslateParams_xml
{

	var $_menutype;
	var $_menuViewItem;
	var $orig_model;
	var $trans_model;

	function __construct($original, $translation, $fieldname, $fields=null)
	{
		$lang = JFactory::getLanguage();
		$lang->load("com_config", JPATH_ADMINISTRATOR);

		$this->fieldname = $fieldname;
		$content = null;
		foreach ($fields as $field)
		{
			if ($field->Name == "option")
			{
				$comp = $field->originalValue;
				break;
			}
		}
		$lang->load($comp, JPATH_ADMINISTRATOR);

		$path = DS . "components" . DS . $comp . DS . "config.xml";
		$xmlfile = JApplicationHelper::_checkPath($path);

		$this->origparams = new JParameter($original, $xmlfile, "component");
		$this->transparams = new JParameter($translation, $xmlfile, "component");
		$this->defaultparams = new JParameter("", $xmlfile, "component");
		$this->fields = $fields;

	}

	function showOriginal()
	{
		if ($this->_menutype == "wrapper")
		{
			?>
			<table width="100%" class="paramlist">
				<tr>
					<td width="40%" align="right" valign="top"><span class="editlinktip"><!-- Tooltip -->
							<span onmouseover="return overlib('Link for Wrapper', CAPTION, 'Wrapper Link', BELOW, RIGHT);" onmouseout="return nd();" >Wrapper Link</span></span></td>

					<td align="left" valign="top"><input type="text" name="orig_params[url]" value="<?php echo $this->origparams->get('url', '') ?>" class="text_area" size="30" /></td>
				</tr>
			</table>
			<?php
		}
		parent::showOriginal();

	}

	function editTranslation()
	{
		if ($this->_menutype == "wrapper")
		{
			?>
			<table width="100%" class="paramlist">
				<tr>
					<td width="40%" align="right" valign="top"><span class="editlinktip"><!-- Tooltip -->
							<span onmouseover="return overlib('Link for Wrapper', CAPTION, 'Wrapper Link', BELOW, RIGHT);" onmouseout="return nd();" >Wrapper Link</span></span></td>
					<td align="left" valign="top"><input type="text" name="refField_params[url]" value="<?php echo $this->transparams->get('url', '') ?>" class="text_area" size="30" /></td>
				</tr>
			</table>
			<?php
		}
		parent::editTranslation();

	}

}
?>
