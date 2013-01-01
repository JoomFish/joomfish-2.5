<?php
/**
 * Joom!Fish - Multi Lingual extention and translation manager for Joomla!
 * Copyright (C) 2003 - 2013, Think Network GmbH, Konstanz
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
 *
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomfish.form.jfform');
jimport('joomfish.form.jfformfield');

class TranslateParams_xml extends TranslateParams
{ 
	public $orig_ModelItem;
	public $trans_ModelItem;
	
	public function setup($original, $translation, $fieldname, $fields=null, $options = array())
	{
	
		parent::__construct($original, $translation, $fieldname, $fields);
	
		$lang = JFactory::getLanguage();
		$lang->load($options['option'], JPATH_ADMINISTRATOR);
	
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
		$oldid = JRequest::getInt($options['ident'], 0);
		// Take care of the name of the id for the item
		JRequest::setVar($options['ident'], $contentid);
	
		JLoader::import('models.'.$options['model_item'], JOOMFISH_ADMINPATH);
		//$this->orig_ModelItem = new $options['model_item']();
	
		// Get The Original form
		// JRequest does NOT this for us in articles!!
		//$this->orig_ModelItem->setState($options['state_ident'],$contentid);
		//$jfcontentModelForm = $this->orig_ModelItem->getForm();
	
		if ($original != "" && is_string($original))
		{
			$original = json_decode($original);
		}
	
	
	
		// NOW GET THE TRANSLATION - IF AVAILABLE
		$this->trans_ModelItem = new $options['model_item']();
		$this->trans_ModelItem->setState($options['state_ident'], $translation_id);
		if ($translation != "" && is_string($translation))
		{
			$translation = json_decode($translation);
		}
	
	
		$translationcontentModelForm = $this->trans_ModelItem->getForm();
		$transfreeform = JFForm::getInstance($translationcontentModelForm);
		
		// Create second instance of the same form to avoid foreach loop problems
		$xml 	= $transfreeform->xml->asXML();
		$name 	= $transfreeform->getName();
		$originalModelForm = JForm::getInstance($name.'.original', $xml, $transfreeform->options);
		
		// Loop trough original form, change original form names and add each field from parameters fieldsets to the translated form with _orig suffix
		// another option here would be to keep 2 forms separated and do double rendering later
		foreach ($originalModelForm->getFieldsets($options['params_fieldset']) as $name=>$ffff) {
	
			if ($name == 'basic-limited') {
				continue;
			}

			$fieldset		= $originalModelForm->getFieldset($name);
			$result= $transfreeform->xml->xpath('//*/fieldset[@name="'.$name.'"]');
			
			foreach ($fieldset as $field) {
				$freefield = JFFormField::getInstance($field);
				//if($freefield->type == 'Spacer') continue;

				$freefieldname 				= $freefield->get('fieldname');
				
				$freefield->set('fieldname',$freefieldname.'_orig' );
				$freefield->set('name', $freefield->getName($freefieldname.'_orig'));
				$freefield->set('label', 'Original '.$freefield->get('label'));
				
				// copy field as XML to break reference, then change its properties
				// @todo check whether this is still necessary as we are now breaking reference when we copy form
				$freeelement 				= $freefield->get('element')->asXML();
				$freeelement				= new JXMLElement($freeelement);
				$newname					= $freeelement['name'].'_orig';
				
				// also change $original objects property names to match changed names in the form
				if (isset($original->$freeelement['name'])) {
					$original->$newname = $original->$freeelement['name'];
					unset($original->$freeelement['name']);
				}
	
				$freeelement['name']		= $newname;
				//$freeelement['label']		= JText::_('ORIGINAL').' '. JText::_($freeelement['label']);
				$freeelement['readonly'] = 'true';
				$freeelement['disabled'] = 'true';
				$freeelement['filter']	 = 'UNSET';
	
				if ($result) {
					$transfreeform->addNode($result[0], $freeelement);
				}
				
				$transfreeform->setFieldAttribute($freeelement['name'], 'filter', 'unset');
				$transfreeform->setFieldAttribute($freeelement['name'], 'readonly', 'true');
				$transfreeform->setFieldAttribute($freeelement['name'], 'disabled', 'true');
				$transfreeform->setValue($freeelement['name'], $name, $freefield->get('value'));
			}
	
			// sorts fields to keep togehter originals and transaltions, not necessary in 2 column seting
			/*$fieldarray = array ();
	
			foreach ($result[0]->field AS $fld) {
				$fxml = $fld->asXML();
				$fieldarray[] = new JXMLElement($fxml);
					
			}
	
			// we need to unset in reverse as indexes keep changing otherwise
			for ($c = count($result[0]->field); $c >=0; $c--) {
				unset($result[0]->field[$c]);
			}
	
	
			$this->_nodesort($fieldarray, SORT_ASC);
			
			$parent = dom_import_simplexml($result[0]);
			foreach ($fieldarray AS $far) {
				$child = dom_import_simplexml($far);
				$child  = $parent->ownerDocument->importNode($child, TRUE);
				$parent->appendChild($child);
			}*/
	
	
		}

	
		if (isset($translation->jfrequest)){
			$transfreeform->bind(array($options['params_fieldset'] => $translation, "request" =>$translation->jfrequest));
			$transfreeform->bind(array($options['params_fieldset'] => $original));
		}
		else {
			$transfreeform->bind(array($options['params_fieldset'] => $translation));
			$transfreeform->bind(array($options['params_fieldset'] => $original));
		}
	
		// reset old values in REQUEST array
		$cid = $oldcid;
		JRequest::setVar('cid', $cid);
		JRequest::setVar($options['ident'], $oldid);
	
		//	$this->origparams = new JFContentParams( $jfcontentModelForm);
		$this->transparams = new $options['trans_params']($transfreeform);
	
	
	}
	

	public function editTranslation()
	{
		echo $this->transparams->render("refField_" . $this->fieldname);

		return false;

	}
	
	public static function renderDoublecolumnParams ($form, $paramname, $option, $sliders) {
		$paramsfieldSets = $form->getFieldsets($paramname);
		if ($paramsfieldSets)
		{
			foreach ($paramsfieldSets as $name => $fieldSet)
			{
				$label = !empty($fieldSet->label) ? $fieldSet->label : strtoupper($option).'_' . $name . '_FIELDSET_LABEL';
				if ($name == 'basic-limited') {
					continue;
				}
				if ($name == 'editorConfig' && $option == 'com_content' ) {
					$label = 'COM_CONTENT_SLIDER_EDITOR_CONFIG';
				}
				echo $sliders->startPanel(JText::_($label), $name . '-options');
		
				if (isset($fieldSet->description) && trim($fieldSet->description)) :
				echo '<p class="tip">' . htmlspecialchars(JText::_($fieldSet->description), ENT_QUOTES, 'UTF-8') . '</p>';
				endif;
				?>
						<div class="clr"></div>
						
							<table style="width: 100%; font-size: 11px;" >
								<?php 
									$lc = array();
									$rc = array();
									$n = 0;
									foreach ($form->getFieldset($name) as $field) {
										if (strstr($field->name, '_orig')) {
											$lc[] = '<li>'.$field->label.$field->input.'</li>';
										} else {
											$rc[] = '<li>'.$field->label.$field->input.'</li>';
										}
									}
									?>
									<tr>
										<td style="width: 50%;">
											<fieldset class="panelform">
												<ul class="adminformlist">								
												<?php echo implode('', $lc); ?>
												</ul>
											</fieldset>
										</td>
										<td style="width: 50%;">
											<fieldset class="panelform">
												<ul class="adminformlist">
												<?php echo implode('', $rc); ?>
												</ul>
											</fieldset>
										</td>
									
									</tr>
							</table>
						
						
		
						<?php
						echo $sliders->endPanel();
					}
				}
	}
	
	}

