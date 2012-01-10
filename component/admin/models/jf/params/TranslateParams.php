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
	protected $contentElement = null;
	protected $forms = null;
	
	public function __construct($original, $translation, $fieldname, $fields=null,$contentElement = null,$forms = null) //,$treatment = null,$tablename = null)
	{
		$this->origparams = $original;
		/*
		$joomFishManager = JoomFishManager::getInstance();
		if ($joomFishManager->getCfg('copyparams',1) && $translation == "")
		{
			$translation = $original;
		}
		
		*/
		$this->transparams = $translation;
		$this->fieldname = $fieldname;
		$this->fields = $fields;
		$this->treatment = isset($contentElement) ? $contentElement->getTreatment() : null; //$treatment;
		$this->contentElement = $contentElement;
		$this->forms = $forms;
		//$this->tablename = $tablename;
		//JLoader::import('models.JFForm', JOOMFISH_ADMINPATH);
		JLoader::import('forms.JFTranslateForm', JoomfishExtensionHelper::getExtraPath('base'));


	}

	public function showOriginal()
	{
		echo $this->origparams;

	}

	public function showDefault()
	{
		echo "";

	}


	public function loadForms()
	{
		if(!isset($this->forms) && isset($this->contentElement))
		{
			$formClass = $this->contentElement->getTranslateFormsClass();
			$this->forms = new $formClass($this->fields,$this->contentElement);
			$this->forms->initForms();
			/*
			$this->trans_form = $forms->getForm(true);
			$this->orig_form = $forms->getForm(false);
			*/
		}
		elseif(isset($this->forms))
		{
			//we have the $forms must do nothing
			/*
			$this->trans_form = $this->forms->getForm(true);
			$this->orig_form = $this->forms->getForm(false);
			*/
		}
	
	}

	public function editTranslation()
	{
		$returnval = array("editor_" . $this->fieldname, "refField_" . $this->fieldname);
		// parameters : areaname, content, hidden field, width, height, rows, cols
		//editorArea("editor_" . $this->fieldname, $this->transparams, "refField_" . $this->fieldname, '100%;', '300', '70', '15');
		
		
		$this->loadForms();
		if($this->treatment && isset($this->treatment['model']) && isset($this->treatment['extension']))
		{
			/*
			$model = $this->treatment['model'];
			$modelName = $this->treatment['modelName'];
			$this->component = $this->treatment['extension'];
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
			
			<!-- TODO this must go to other place -->
			$this->setStyle();
			*/
			//translated Params and original Params in one Slider
			echo JHtml::_('sliders.start','params-sliders-'.$this->fieldname, array('useCookie'=>1));
				$paramsfieldSets = $this->forms->trans_form->getFieldsets($this->fieldname);
				$this->forms->outputFieldset($paramsfieldSets);
			echo JHtml::_('sliders.end');
		}
		
		else
		{
			echo $this->transparams;
		}
		return $returnval;

	}

}



?>
