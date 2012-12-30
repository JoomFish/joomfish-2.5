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


jimport('joomfish.translateparams.xml');

class TranslateParams_content extends TranslateParams_xml
{
	
	function __construct($original, $translation, $fieldname, $fields=null)
	{	
		$options 			= array();
		$options['option']	= 'com_content';
		$options['ident']	= 'article_id';
		$options['state_ident']	= 'article.id';
		$options['model_item']	= 'JFContentModelItem';
		$options['trans_params']	= 'JFContentParams';
		$options['params_fieldset']	= 'attribs';
		
		return $this->setup($original, $translation, $fieldname, $fields, $options);
		
		
		
		/////////////////////////////////////////////////////////////////////////
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
		$this->orig_contentModelItem = new JFContentModelItem();

		// Get The Original form 
		// JRequest does NOT this for us in articles!!
		$this->orig_contentModelItem->setState('article.id',$contentid);
		$jfcontentModelForm = $this->orig_contentModelItem->getForm(array(),true, false);
		
		if ($original != "")
		{
			$original = json_decode($original);
		}
		
		

		// NOW GET THE TRANSLATION - IF AVAILABLE
		$this->trans_contentModelItem = new JFContentModelItem();
		$this->trans_contentModelItem->setState('article.id', $translation_id);
		if ($translation != "")
		{
			$translation = json_decode($translation);
		}
		
		
		$translationcontentModelForm = $this->trans_contentModelItem->getForm(array(),true, true);
		$transfreeform = JFForm::getInstance($translationcontentModelForm);
		//$xml = $freeform->get('xml');
		
		foreach ($jfcontentModelForm->getFieldsets('attribs') as $name=>$ffff) {
			
			if ($name == 'basic-limited') {
				continue;
			}
			//$attrfieldset = $jfcontentModelForm->getFieldset($name);
			$fieldset		= $jfcontentModelForm->getFieldset($name);
			$result= $transfreeform->xml->xpath('//*/fieldset[@name="'.$name.'"]');
			foreach ($fieldset as $field) {
				$freefield = JFFormField::getInstance($field);
				if($freefield->type == 'Spacer') continue;
				//$jfcontentModelForm->setFieldAttribute($freefield->name, 'readonly', 'true');

				//foreach ($xml->fields as $xmlfield) {
				//$result= & $xmlfield->xpath('//*[@name="attribs"]');
				$freefieldname 				= $freefield->get('fieldname');
				$freefield->set('fieldname',$freefieldname.'_orig' );
				$freefield->set('name', $freefield->getName($freefieldname.'_orig'));
				$freefield->set('label', 'Original '.$freefield->get('label'));
				
				$freeelement 				= $freefield->get('element')->asXML();
				$freeelement				= new JXMLElement($freeelement);
				//$freeelement['label']		= 'Original'.$freeelement['label'];
				//$freeelement['fieldname'] 	= 'orig_'.$freeelement['fieldname'];
				$newname					= $freeelement['name'].'_orig';
				if (isset($original->$freeelement['name'])) {
					$original->$newname = $original->$freeelement['name'];
					unset($original->$freeelement['name']);
				}
				
				$freeelement['name']		= $newname;
				$freeelement['label']		= JText::_('ORIGINAL').' '. JText::_($freeelement['label']);
				$freeelement['readonly'] = 'true';
				$freeelement['disabled'] = 'true';
				$freeelement['filter']	 = 'UNSET';
				//$result = $freeform->findField($name);
				
				if ($result) {
					$transfreeform->addNode($result[0], $freeelement);
				}
				$transfreeform->setFieldAttribute($freeelement['name'], 'filter', 'unset');
				$transfreeform->setFieldAttribute($freeelement['name'], 'readonly', 'true');
				$transfreeform->setFieldAttribute($freeelement['name'], 'disabled', 'true');
				$transfreeform->setValue($freeelement['name'], $name, $freefield->get('value'));
			}
			
			$fieldarray = array ();
			
			foreach ($result[0]->field AS $fld) {
					$fxml = $fld->asXML();
					$fieldarray[] = new JXMLElement($fxml);
					
			}

			// we need to unset in reverse as indexes keep changing otherwise
			for ($c = count($result[0]->field); $c >=0; $c--) {
				/*$dom = dom_import_simplexml($fldr);
				$dom->parentNode->removeChild($dom);*/
				unset($result[0]->field[$c]);
			}
			
			
			$this->_nodesort($fieldarray, SORT_ASC);
			$parent = dom_import_simplexml($result[0]);
			foreach ($fieldarray AS $far) {
				//$result[0]->addChild('field',$far);
				$child = dom_import_simplexml($far);
				// Import the <cat> into the dictionary document
				$child  = $parent->ownerDocument->importNode($child, TRUE);			
				$parent->appendChild($child);
			}

			
		}
		//$freeform->set('xml', $xml);
		
		if (isset($translation->jfrequest)){
			$transfreeform->bind(array("attribs" => $translation, "request" =>$translation->jfrequest));
			$transfreeform->bind(array("attribs" => $original));
		}
		else {
			$transfreeform->bind(array("attribs" => $translation));
			$transfreeform->bind(array("attribs" => $original));
		}

		// reset old values in REQUEST array
		$cid = $oldcid;
		JRequest::setVar('cid', $cid);
		JRequest::setVar("article_id", $oldid);

		//	$this->origparams = new JFContentParams( $jfcontentModelForm);
		$this->transparams = new JFContentParams($transfreeform);


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

class JFContentParams extends JObject
{

	var $form = null;

	function __construct($form=null, $item=null)
	{
		$this->form = $form;

	}

	function render($type)
	{
		$sliders = & JPane::getInstance('sliders');
		echo $sliders->startPane('params');
	
		TranslateParams_xml::renderDoublecolumnParams ($this->form, 'attribs', 'com_content', $sliders);

		echo $sliders->endPane();
		return;

	}

}

