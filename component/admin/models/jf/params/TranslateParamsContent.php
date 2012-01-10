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

class TranslateParamsContent extends TranslateParams
{
	function __construct($original, $translation, $fieldname, $fields=null,$contentElement = null,$forms = null)
	{
		parent::__construct($original, $translation, $fieldname, $fields,$contentElement,$forms);
		$this->component = 'com_content';
	}


	function editTranslation()
	{
		$this->loadForms();
	
		$cid = JRequest::getVar('cid', array(0));
		//$oldcid = $cid;
		$translation_id = 0;
		$contentid = 0;
		$language_id = 0;
		if (strpos($cid[0], '|') !== false)
		{
			list($translation_id, $contentid, $language_id) = explode('|', $cid[0]);
		}
		
		$joomFishManager = JoomFishManager::getInstance();
		foreach($this->fields as $field)
		{
			switch($field->Name)
			{
				case 'attribs':
					//is only attribs mean with copyparams?
					// not so move this under case 'metadata'
					if (!$joomFishManager->getCfg('copyparams',1) && $field->translationContent->value == "" ) //&& $field->Name == 'attribs')
					{
						//we get the defaults from xml
						$defaults = array();
						$fieldSets = $this->forms->trans_form->getFieldsets('attribs');
						foreach ($fieldSets as $name => $fieldSet) :
							if ($name != 'editorConfig' && $name != 'basic-limited') :
								foreach ($this->forms->trans_form->getFieldset($name) as $transField) :
									$transFieldXml = $this->forms->trans_form->getField($transField->__get('fieldname'),$transField->__get('group'));
									$defaults[$transField->__get('fieldname')] = $this->forms->trans_form->getFieldAttribute($transField->__get('fieldname'),'default','',$transField->__get('group'));
								endforeach;
							endif;
						endforeach;
						if(count($defaults) > 0)
						{
							$field->translationContent->value = json_encode($defaults);
						}
					}
				case 'images':
				case 'urls':
				case 'metadata':

					if ($field->translationContent->value != "")
					{
						$this->forms->trans_form->bind(array($field->Name => json_decode($field->translationContent->value)));
					}
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
					// this fields are empty on new so we get blank and not from the loaded item
					// or this one also use copyparams ?
					/*
					if use copyparams to 
						
					if (!$joomFishManager->getCfg('copyparams',1) && $field->translationContent->value == "" )
					{
						//we get the defaults from xml
						$defaults = $this->forms->trans_form->getFieldAttribute($field->Name,'default','');
						$field->translationContent->value = json_encode($defaults);
					}
					*/
					$this->forms->trans_form->bind(array($field->Name => $field->translationContent->value));
				break;
			}
		}


		
		if(version_compare(JVERSION, '2.5', 'ge'))
		{

			// Create shortcut to parameters.
			$params = $this->forms->trans_model->getState()->get('params');
			$params = $params->toArray();
			
			// This checks if the config options have ever been saved. If they haven't they will fall back to the original settings.
			$editoroptions = isset($params['show_publishing_options']);

			if (!$editoroptions):
				$params['show_publishing_options'] = '1';
				$params['show_article_options'] = '1';
				$params['show_urls_images_backend'] = '0';
				$params['show_urls_images_frontend'] = '0';
			endif;

			// Check if the article uses configuration settings besides global. If so, use them.
			if (!empty($this->forms->trans_item->attribs['show_publishing_options'])):
					$params['show_publishing_options'] = $this->forms->trans_item->attribs['show_publishing_options'];
			endif;
			if (!empty($this->forms->trans_item->attribs['show_article_options'])):
				$params['show_article_options'] = $this->forms->trans_item->attribs['show_article_options'];
			endif;
			if (!empty($this->forms->trans_item->attribs['show_urls_images_backend'])):
				$params['show_urls_images_backend'] = $this->forms->trans_item->attribs['show_urls_images_backend'];
			endif;

			?>
			<?php echo JHtml::_('sliders.start','content-sliders-'.$this->forms->trans_item->id, array('useCookie'=>1)); ?>
			<?php // Do not show the publishing options if the edit form is configured not to. ?>
			<?php if ($params['show_publishing_options'] || ( $params['show_publishing_options'] = '' && !empty($editoroptions)) ): ?>
				<?php echo JHtml::_('sliders.panel',JText::_('COM_CONTENT_FIELDSET_PUBLISHING'), 'publishing-details'); ?>
				<fieldset class="panelform joomfish_panelform">
					<ul class="adminformlist">
						<li>
							<?php $buttons = array('copy'=>"document.adminForm.".$this->forms->trans_form->getFormControl()."_created_by_name.value = document.adminForm.".$this->forms->orig_form->getFormControl()."_created_by_name.value; document.adminForm.".$this->forms->trans_form->getFormControl()."_created_by_id.value = document.adminForm.".$this->forms->orig_form->getFormControl()."_created_by_id.value;", 'delete'=>"document.adminForm.".$this->forms->trans_form->getFormControl()."_created_by_name.value = '';document.adminForm.".$this->forms->trans_form->getFormControl()."_created_by_id.value = '';"); ?>
							
							<?php $this->forms->outputFieldParams($this->forms->trans_form->getField('created_by'),$buttons,array(array('filter', 'unset'),array('readonly', 'true'),array('class','readonly'))) ?>
						</li>
						<li>
							<?php $this->forms->outputFieldParams($this->forms->trans_form->getField('created_by_alias'),true,array(array('type', 'text'),array('readonly', 'true'),array('class','readonly'))); ?>
						</li>
						<li>
							<?php $this->forms->outputFieldParams($this->forms->trans_form->getField('created')) ?>
						</li>
						<li>
							<?php $this->forms->outputFieldParams($this->forms->trans_form->getField('publish_up')); ?>
						</li>
						<li>
							<?php $this->forms->outputFieldParams($this->forms->trans_form->getField('publish_down')); ?>
						</li>
						<?php if ($this->forms->trans_item->modified_by || $this->forms->orig_item->modified_by) : ?>
						<li>
							<?php $this->forms->outputFieldParams($this->forms->trans_form->getField('modified_by'),false,false,true); //no buttons here and no content on trans?>
						</li>
						<li>
							<?php $this->forms->outputFieldParams($this->forms->trans_form->getField('modified'),false); //no buttons here ?>
						</li>
					<?php endif; ?>
					<?php if ($this->forms->trans_item->version || $this->forms->orig_item->version) : ?>
						<li>
							<?php $this->forms->outputFieldParams($this->forms->trans_form->getField('version'),false); //no buttons here ?>
						</li>
					<?php endif; ?>
					<?php if ($this->forms->trans_item->hits || $this->forms->orig_item->hits) : ?>
						<li>
							<?php $this->forms->outputFieldParams($this->forms->trans_form->getField('hits'),false); //no buttons here ?>
						</li>
					<?php endif; ?>
					</ul>
				</fieldset>
			<?php endif; ?>
		
			<?php $fieldSets = $this->forms->trans_form->getFieldsets('attribs'); ?>
			<?php foreach ($fieldSets as $name => $fieldSet) : ?>
				<?php // If the parameter says to show the article options or if the parameters have never been set, we will
					// show the article options. ?>
				<?php if ($params['show_article_options'] || (( $params['show_article_options'] == '' && !empty($editoroptions) ))): ?>
					<?php // Go through all the fieldsets except the configuration and basic-limited, which are
						// handled separately below. ?>
					<?php if ($name != 'editorConfig' && $name != 'basic-limited') : ?>
						<?php echo JHtml::_('sliders.panel',JText::_($fieldSet->label), $name.'-options'); ?>
						<?php if (isset($fieldSet->description) && trim($fieldSet->description)) : ?>
							<p class="tip"><?php echo //$this->forms->escape(
								JText::_($fieldSet->description);
								//);?></p>
						<?php endif; ?>
						<fieldset class="panelform joomfish_panelform">
							<ul class="adminformlist">
							<?php foreach ($this->forms->trans_form->getFieldset($name) as $field) : ?>
								<li>
									<?php $this->forms->outputFieldParams($this->forms->trans_form->getField($field->__get('fieldname'),$field->__get('group'))); ?>
								</li>
							<?php endforeach; ?>
							</ul>
						</fieldset>
					<?php endif ?>
					<?php // If we are not showing the options we need to use the hidden fields so the values are not lost. ?>
				<?php elseif ($name == 'basic-limited'): ?>
						<?php foreach ($this->forms->trans_form->getFieldset('basic-limited') as $field) : ?>
							<?php $this->forms->outputFieldParams($this->forms->trans_form->getField($field->__get('fieldname'),$field->__get('group'))); ?>
						<?php endforeach; ?>

				<?php endif; ?>
			<?php endforeach; ?>

			<?php // We need to make a separate space for the configuration
					// so that those fields always show to those wih permissions 
				require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'content.php');
				$canDo = ContentHelper::getActions($this->forms->trans_model->getState()->get('filter.category_id')); //???
			?>
			<?php if ( $canDo->get('core.admin') ): ?>
				<?php echo JHtml::_('sliders.panel',JText::_('COM_CONTENT_SLIDER_EDITOR_CONFIG'), 'configure-sliders'); ?>
					<fieldset class="panelform joomfish_panelform" >
						<ul class="adminformlist">
						<?php foreach ($this->forms->trans_form->getFieldset('editorConfig') as $field) : ?>
							<li>
								<?php $this->forms->outputFieldParams($this->forms->trans_form->getField($field->__get('fieldname'),$field->__get('group')));?>
							</li>
						<?php endforeach; ?>
						</ul>
					</fieldset>
			<?php endif ?>

			<?php // The url and images fields only show if the configuration is set to allow them. ?>
			<?php // This is for legacy reasons. ?>
			<?php if ($params['show_urls_images_backend']): ?>
				<?php echo JHtml::_('sliders.panel',JText::_('COM_CONTENT_FIELDSET_URLS_AND_IMAGES'), 'urls_and_images-options'); ?>
					<fieldset class="panelform joomfish_panelform">
					<ul class="adminformlist">
						<?php foreach($this->forms->trans_form->getGroup('images') as $field): ?>
							<li>
								<?php $this->forms->outputFieldParams($this->forms->trans_form->getField($field->__get('fieldname'),$field->__get('group'))); ?>
							</li>
						<?php endforeach; ?>
							<?php foreach($this->forms->trans_form->getGroup('urls') as $field): ?>
							<li>
								<?php $this->forms->outputFieldParams($this->forms->trans_form->getField($field->__get('fieldname'),$field->__get('group'))); ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</fieldset>
			<?php endif; ?>
			<?php echo JHtml::_('sliders.panel',JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'), 'meta-options'); ?>
				<fieldset class="panelform joomfish_panelform">
					<ul class="adminformlist">
						<li>
							<?php $this->forms->outputFieldParams($this->forms->trans_form->getField('metadesc')); ?>
						</li>
						<li>
							<?php $this->forms->outputFieldParams($this->forms->trans_form->getField('metakey')); ?>
						</li>
						<?php foreach($this->forms->trans_form->getGroup('metadata') as $field): ?>
						<li>
							<?php $this->forms->outputFieldParams($this->forms->trans_form->getField($field->__get('fieldname'),$field->__get('group'))); ?>
						</li>
						<?php endforeach; ?>
					</ul>
				</fieldset>
			<?php echo JHtml::_('sliders.end'); ?>
		<?php
		}
		else
		{	

		// reset old values in REQUEST array
		/*
		$cid = $oldcid;
		
		JRequest::setVar('cid', $cid);
		JRequest::setVar("article_id", $oldid);
		
		JRequest::setVar("id", $oldid);
		*/
		
		
		echo JHtml::_('sliders.start','params-sliders-'.$this->fieldname, array('useCookie'=>1));
			// publishing-details
			echo JHtml::_('sliders.panel',JText::_('COM_CONTENT_FIELDSET_PUBLISHING'), 'publishing-details'); ?>
				<fieldset class="panelform joomfish_panelform">
					<ul class="adminformlist">
						<?php $this->forms->outputDescription(); ?>
						<li>
							<?php $buttons = array('copy'=>"document.adminForm.".$this->forms->trans_form->getFormControl()."_created_by_name.value = document.adminForm.".$this->forms->orig_form->getFormControl()."_created_by_name.value; document.adminForm.".$this->forms->trans_form->getFormControl()."_created_by_id.value = document.adminForm.".$this->forms->orig_form->getFormControl()."_created_by_id.value;", 'delete'=>"document.adminForm.".$this->forms->trans_form->getFormControl()."_created_by_name.value = '';document.adminForm.".$this->forms->trans_form->getFormControl()."_created_by_id.value = '';"); ?>
							
							<?php $this->forms->outputFieldParams($this->forms->trans_form->getField('created_by'),$buttons,array(array('filter', 'unset'),array('readonly', 'true'),array('class','readonly'))) ?>
						</li>

						<li>
							<?php $this->forms->outputFieldParams($this->forms->trans_form->getField('created_by_alias'),true,array(array('type', 'text'),array('readonly', 'true'),array('class','readonly'))); ?>
						</li>

						<li>
							<?php $this->forms->outputFieldParams($this->forms->trans_form->getField('created')) ?>
						</li>

						<li>
							<?php $this->forms->outputFieldParams($this->forms->trans_form->getField('publish_up')); ?>
						</li>

						<li>
							<?php $this->forms->outputFieldParams($this->forms->trans_form->getField('publish_down')); ?>
						</li>
	
						<?php if ($this->forms->trans_item->translated->modified_by || $this->forms->orig_item->translated->modified_by) : ?>
						<li>
							<?php $this->forms->outputFieldParams($this->forms->trans_form->getField('modified_by'),false); //no buttons here ?>
						</li>

						<li>
							<?php $this->forms->outputFieldParams($this->forms->trans_form->getField('modified'),false); //no buttons here ?>
						</li>
					<?php endif; ?>

					<?php if ($this->forms->trans_item->translated->version || $this->forms->orig_item->translated->version) : ?>
						<li>
							<?php $this->forms->outputFieldParams($this->forms->trans_form->getField('version'),false); //no buttons here ?>
						</li>
					<?php endif; ?>

					<?php if ($this->forms->trans_item->translated->hits || $this->forms->orig_item->translated->hits) : ?>
						<li>
							<?php $this->forms->outputFieldParams($this->forms->trans_form->getField('hits'),false); //no buttons here ?>
						</li>
					<?php endif; ?>
				</ul>
			</fieldset>
		<?php
		
		// params attribs
		$paramsfieldSets = $this->forms->trans_form->getFieldsets($this->fieldname);
			$this->forms->outputFieldset($paramsfieldSets);
		
		//meta-options
		echo JHtml::_('sliders.panel',JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'), 'meta-options'.$this->fieldname);
		?>
			<fieldset class="panelform joomfish_panelform">
				<ul class="adminformlist">
					<li>
						<?php $this->forms->outputFieldParams($this->forms->trans_form->getField('metadesc')); ?>
					</li>
					<li>
						<?php $this->forms->outputFieldParams($this->forms->trans_form->getField('metakey')); ?>
					</li>
					<?php foreach($this->forms->trans_form->getGroup('metadata') as $field): ?>
					<li>
						<?php $this->forms->outputFieldParams($this->forms->trans_form->getField($field->__get('fieldname'),$field->__get('group'))); ?>
					</li>
					<?php endforeach; ?>
				</ul>
			</fieldset>
			<?php
		echo JHtml::_('sliders.end');
		}
		return;
	}
}

?>
