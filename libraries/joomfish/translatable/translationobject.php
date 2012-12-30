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
 * @package joomfish
 * @subpackage Models
 *
 */
JLoader::register('jfContent', JOOMFISH_ADMINPATH . DS . 'tables' . DS . 'JFContent.php');
jimport('joomfish.translatable.translatable');

/**
 * Representation of one content with his translation.
 * The object includes information from the original object and
 * the refering translation. Based on that information it is
 * able to handle all necessary interactions with the tranlsation.
 * Each instance of this object represents only one translation in
 * on specified language, but it handles all the fields within the
 * ContentElement.
 *
 * @package joomfish
 * @subpackage administrator
 * @copyright 2003 - 2013, Think Network GmbH, Konstanz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Revision: 1543 $
 * @author Alex Kempkens
 */
class TranslationObject implements iJFTranslatable
{

	/** @var contentElement Reference to the ContentElement definition of the instance */
	protected  $contentElement;

	/** @var id ID of the based content */
	public $id;

	/** @var translation_id 	translation id value */
	public $translation_id = 0;

	/** @var reference_id 	id of item being translated  value */
	public $reference_id = 0;
	
	/** @var checked_out User who checked out this content if any */
	public $checked_out;

	/** @var title Title of the object; used from the field configured as titletext */
	public $title;

	/** @var titleTranslation the actual translation of the title */
	public $titleTranslation;

	/** @var language_id language for the translation */
	public $language_id;

	/** @var language Language name of the content */
	public $language;

	/** @var lastchanged Date when the translation was last modified */
	public $lastchanged;

	/** @var modified_date Date of the last modification of the content - if existing */
	public $modified_date;

	/** @var state State of the translation
	 * -1 := for at least one field of the content the translation is missing
	 *  0 := the translation exists but the original content was changed
	 *  1 := the translation is valid
	 */
	public $state = -1;

	/** @var int Number of changed fields */
	protected $_numChangedFields = 0;

	/** @var int Number of new fields, with an original other than NULL */
	protected $_numNewAndNotNullFields = 0;

	/** @var int Number for fields unchanged */
	protected $_numUnchangedFields = 0;
	/*
	 * Number of unstranslated fields
	 */
	protected $_untraslatedFields = 0;

	/** published Flag if the translation is published or not */
	public $published = false;

	/** Standard constructor
	 *
	 * @param	languageID		ID of the associated language
	 * @param	elementTable	Reference to the ContentElementTable object
	 */
	public function __construct($languageID, & $contentElement, $id=-1)
	{
		$db = JFactory::getDBO();

		if ($id > 0)
			$this->id = $id;
		$this->language_id = $languageID;
		$jfManager = JoomFishManager::getInstance();
		$lang = $jfManager->getLanguageByID($languageID);

		$this->language = $lang->name;
		$this->contentElement = $contentElement;

	}

	/** Loads the information based on a certain content ID
	 */
	public function loadFromContentID($id=null)
	{
		$db = JFactory::getDBO();
		if ($id != null && isset($this->contentElement) && $this->contentElement !== false)
		{
			$db->setQuery($this->contentElement->createContentSQL($this->language_id, $id));
			$row = null;
			$x = (string) $db->getQuery();
			$row = $db->loadObject("stdClass", false);
			$this->id = $id;
			$this->readFromRow($row);
		}

	}

	/** Reads the information from the values of the form
	 * The content element will be loaded first and then the values of the override
	 * what is actually in the element
	 *
	 * @param	array	The values which should be bound to the object
	 * @param	string	The field prefix
	 * @param	string	An optional field
	 * @param 	boolean	try to bind the values to the object
	 * @param 	boolean	store original values too
	 */
	public function bind($formArray, $prefix="", $suffix="", $tryBind=true, $storeOriginalText=false)
	{
		$user = JFactory::getUser();
		$db = JFactory::getDBO();

		if ($tryBind)
		{
			$this->_jfBindArrayToObject($formArray, $this);
		}
		if ($this->published == "")
			$this->published = 0;

		// Go thru all the fields of the element and try to copy the content values
		$elementTable = $this->contentElement->getTable();

		for ($i = 0; $i < count($elementTable->Fields); $i++)
		{
			$field = $elementTable->Fields[$i];
			$fieldName = $field->Name;
			if (isset($formArray[$prefix . "refField_" . $fieldName . $suffix]))
			{

				// Handle magic quotes compatability
				// NB this is not done by getVar
				/*
				if (get_magic_quotes_gpc() && $field->Type !== 'htmltext')
				{
					//$formArray[$prefix . "refField_" . $fieldName . $suffix] = JRequest::_stripSlashesRecursive($formArray[$prefix . "refField_" . $fieldName . $suffix]);
					//$formArray[$prefix . "origText_" . $fieldName . $suffix] = JRequest::_stripSlashesRecursive($formArray[$prefix . "origText_" . $fieldName . $suffix]);
				}
				else
				{
				*/
				$formArray[$prefix . "refField_" . $fieldName . $suffix] = JRequest::getVar($prefix . "refField_" . $fieldName . $suffix, '', 'post', 'string', JREQUEST_ALLOWRAW);
				$formArray[$prefix . "origText_" . $fieldName . $suffix] = JRequest::getVar($prefix . "origText_" . $fieldName . $suffix, '', 'post', 'string', JREQUEST_ALLOWRAW);
				//}

				$translationValue = $formArray[$prefix . "refField_" . $fieldName . $suffix];
				$fieldContent = new jfContent($db);

				// code cleaner for xhtml transitional compliance
				if ($field->Type == 'titletext' || $field->Type == 'text')
				{
					jimport('joomla.filter.output');
					//$translationValue = JFilterOutput::ampReplace( $translationValue );
				}
				if ($field->Type == 'htmltext')
				{
					$translationValue = str_replace('<br>', '<br />', $translationValue);

					// remove <br /> take being automatically added to empty fulltext
					$length = strlen($translationValue) < 9;
					$search = strstr($translationValue, '<br />');
					if ($length && $search)
					{
						$translationValue = NULL;
					}
				}
				if ($field->posthandler != "")
				{
					if (method_exists($this, $field->posthandler))
					{
						$handler = $field->posthandler;
						$this->$handler($translationValue, $elementTable->Fields, $formArray, $prefix, $suffix, $storeOriginalText);
					}
				}

				$originalValue = $formArray[$prefix . "origValue_" . $fieldName . $suffix];
				$originalText = ($storeOriginalText) ? $formArray[$prefix . "origText_" . $fieldName . $suffix] : "";

				$fieldContent->id = $formArray[$prefix . "id_" . $fieldName . $suffix];
				$fieldContent->reference_id = (intval($formArray[$prefix . "reference_id" . $suffix]) > 0) ? intval($formArray[$prefix . "reference_id" . $suffix]) : $this->id;
				$fieldContent->language_id = $this->language_id;
				$fieldContent->reference_table = $db->getEscaped($elementTable->Name);
				$fieldContent->reference_field = $db->getEscaped($fieldName);
				$fieldContent->value = $translationValue;
				// original value will be already md5 encoded - based on that any encoding isn't needed!
				$fieldContent->original_value = $originalValue;
				$fieldContent->original_text = !is_null($originalText) ? $originalText : "";

				$datenow = JFactory::getDate();
				$fieldContent->modified = $datenow->toMySQL();

				$fieldContent->modified_by = $user->id;
				$fieldContent->published = $this->published;
				$field->translationContent = $fieldContent;
			}
			else if ($field->Type == "params" && isset($formArray["jform"][$field->Name]))
			{	
				// unset all original parameters before they are saved
				// @todo implement full JForm parameters filtering
				foreach ($formArray["jform"][$field->Name] AS $checkname=>$checkfield) {
					if (strstr($checkname, '_orig')) {
						unset ($formArray["jform"][$field->Name][$checkname]);
					}
				}
				
				$translationValue = $formArray["jform"][$field->Name];

				if ($field->posthandler != "")
				{
					if (method_exists($this, $field->posthandler))
					{
						$handler = $field->posthandler;
						$this->$handler($translationValue, $elementTable->Fields, $formArray, $prefix, $suffix, $storeOriginalText);
					}
				}

				$registry = new JRegistry();
				$registry->loadArray($translationValue);
				$translationValue = $registry->toString();

				$fieldContent = new jfContent($db);
				$fieldContent->id = $formArray[$prefix . "id_" . $fieldName . $suffix];
				$fieldContent->reference_id = (intval($formArray[$prefix . "reference_id" . $suffix]) > 0) ? intval($formArray[$prefix . "reference_id" . $suffix]) : $this->id;
				$fieldContent->language_id = $this->language_id;
				$fieldContent->reference_table = $db->getEscaped($elementTable->Name);
				$fieldContent->reference_field = $db->getEscaped($fieldName);
				$fieldContent->value = $translationValue;
				$fieldContent->original_value = "";
				$fieldContent->original_text = "";

				$datenow = JFactory::getDate();
				$fieldContent->modified = $datenow->toMySQL();

				$fieldContent->modified_by = $user->id;
				$fieldContent->published = $this->published;
				$field->translationContent = $fieldContent;

				// TODO must also save 'request' object for Menu items - this should be moved to a separate 'post translation save' handler
				// Joomla STILL treats this as a special case in menu items see administrator/omponnets/com_menus/controllers/item.php line 167
				// its not even in the model or table class!
			}
		}

	}

	/** Reads the information out of an existing JTable object into the translationObject.
	 *
	 * @param	object	instance of an mosDBTable object
	 */
	public function updateMLContent(&$dbObject,$language)
	{
		$db = JFactory::getDBO();
		if ($dbObject === null)
			return;

		if ($this->published == "")
			$this->published = 0;

		// retriev the original untranslated object for references
		// this MUST be copied by value and not by reference!
		$origObject = clone($dbObject);
		$key = $dbObject->get('_tbl_key');
		$db->setQuery("SELECT * FROM " . $dbObject->get('_tbl') . " WHERE " . $key . "='" . $dbObject->$key . "'");
		$origObject = $db->loadObject('stdClass', false);

		if ($this->contentElement->getTarget() == "native"){
			// We must reset the primary key and language fields for new translations
			// If we don't then joomla validity checks on aliases not being unique will fail etc.
			$isnew = false;
			if (isset($origObject->language)  && $origObject->language!=$language){
				$origObject->language = $language;
				$isnew = true;
			}
			// We must reset the primary key and language fields for new translations
			if ($isnew && isset($dbObject->$key)){
				$dbObject->$key = 0;	
				$this->translation_id=0;
				$this->id=0;
			}			
		}
		
		$this->copyContentToTranslation($dbObject, $origObject);

	}

	/**
	 * This method copies a currect database object into the translations
	 * The original object might be the same kind of object and it is not required that
	 * both objects are of the type mosDBTable!
	 *
	 * @param object $dbObject new values for the translation
	 * @param object $origObject original values based on the db for reference
	 */
	public function copyContentToTranslation(&$dbObject, $origObject)
	{
		$user = JFactory::getUser();

		// Go thru all the fields of the element and try to copy the content values
		$elementTable = $this->contentElement->getTable();

		for ($i = 0; $i < count($elementTable->Fields); $i++)
		{
			$field = $elementTable->Fields[$i];
			$fieldName = $field->Name;
			if (isset($dbObject->$fieldName) && $field->Translate)
			{
				$translationValue = $dbObject->$fieldName;
				$fieldContent = $field->translationContent;

				$fieldContent->value = $translationValue;
				$dbObject->$fieldName = $origObject->$fieldName;
				$fieldContent->original_value = md5($origObject->$fieldName);
				// ToDo: Add handling of original text!

				$datenow = & JFactory::getDate();
				$fieldContent->modified = $datenow->toMySQL();

				$fieldContent->modified_by = $user->id;
				
				// make sure reference_id is set if not already set
				if ((!isset($fieldContent->reference_id) || is_null($fieldContent->reference_id) ||  $fieldContent->reference_id==0)  && (isset($origObject->id) && $origObject->id>0)){
					$fieldContent->reference_id = $origObject->id;
				}
				
			}
		}

	}

	/** Reads some of the information from the overview row
	 */
	public function readFromRow($row)
	{
		$this->id = $row->id;
		$this->translation_id = $row->jfc_id;
		$this->title = $row->title;
		$this->titleTranslation = $row->titleTranslation;
		if (!isset($this->language_id) || $this->language_id == -1)
		{
			$this->language_id = $row->language_id;
			$this->language = $row->language;
		}
		$this->lastchanged = $row->lastchanged;
		$this->published = $row->published;
		if (isset($row->modified_date))
			$this->modified_date = $row->modified_date;
		if (isset($row->checked_out))
			$this->checked_out = $row->checked_out;

		// Go thru all the fields of the element and try to copy the content values
		$elementTable = $this->contentElement->getTable();
		$db = JFactory::getDBO();
		$fieldContent = new jfContent($db);
		for ($i = 0; $i < count($elementTable->Fields); $i++)
		{
			$field = $elementTable->Fields[$i];
			$fieldName = $field->Name;
			if (isset($row->$fieldName))
			{
				$field->originalValue = $row->$fieldName;

				if ($field->prehandleroriginal != "")
				{
					if (method_exists($this, $field->prehandleroriginal))
					{
						$handler = $field->prehandleroriginal;
						$field->originalValue = $this->$handler($row);
					}
				}
			}
		}


		$this->_loadContent($row);

	}

	/** Reads all translation information from the database
	 *
	 */
	private function _loadContent($row = false)
	{
		$db = JFactory::getDBO();

		$elementTable = $this->getTable();

		if ($this->contentElement->getTarget() == "joomfish")
		{
			$sql = "select * "
					. "\n  from #__jf_content"
					. "\n where reference_id='" . $this->id . "'"
					. "\n   and reference_table='" . $elementTable->Name . "'";
			if (isset($this->language_id) && $this->language_id != "")
			{
				$sql .= "\n   and language_id=" . $this->language_id;
			}

			//echo "load sql=>$sql<<br />";
			$db->setQuery($sql);
			$rows = $db->loadObjectList('', 'stdClass', false);
			if ($db->getErrorNum() != 0)
			{
				JError::raiseWarning(400, JTEXT::_('No valid table information: ') . $db->getErrorMsg());
			}

			$translationFields = null;
			if (count($rows) > 0)
			{
				foreach ($rows as $trow)
				{
					$fieldContent = new jfContent($db);
					if (!$fieldContent->bind($trow))
					{
						JError::raiseWarning(200, JText::_('Problems binding object to fields: ' . $fieldContent->getError()));
					}
					$translationFields[$fieldContent->reference_field] = $fieldContent;
				}
			}

			// Check fields and their state
			for ($i = 0; $i < count($elementTable->Fields); $i++)
			{
				$field = $elementTable->Fields[$i];

				if ($field->prehandlertranslation != "")
				{
					if (method_exists($this, $field->prehandlertranslation))
					{
						$handler = $field->prehandlertranslation;
						$this->$handler($field, $translationFields);
					}
				}

				if (isset($translationFields[$field->Name]))
				{
					$fieldContent = $translationFields[$field->Name];
				}
				else
				{
					$fieldContent = null;
				}

				if ($field->Translate)
				{
					if (isset($fieldContent))
					{
						$field->changed = (md5($field->originalValue) != $fieldContent->original_value);
						if ($field->changed)
						{
							$this->_numChangedFields++;
						}
						else
							$this->_numUnchangedFields++;
					}
					else
					{
						$fieldContent = new jfContent($db);
						$fieldContent->reference_id = $this->id;
						$fieldContent->reference_table = $elementTable->Name;
						$fieldContent->reference_field = $field->Name;
						$fieldContent->language_id = $this->language_id;
						$fieldContent->original_value = $field->originalValue;
						$field->changed = false;
						if ($field->originalValue != '')
						{
							$this->_numNewAndNotNullFields++;
						}
					}
				}
				$field->translationContent = $fieldContent;
			}

			// Checking the record state based on the fields. If one field is changed the record is modifed
			if ($this->_numChangedFields == 0 && $this->_numNewAndNotNullFields == 0)
			{
				$this->state = 1;
			}
			elseif ($this->_numChangedFields == 0 && $this->_numNewAndNotNullFields > 0 && $this->_numUnchangedFields == 0)
			{
				$this->state = -1;
			}
			else
			{
				$this->state = 0;
			}
		}
		else
		{
			if (isset($row))
			{	
				$noprehandlerrow = clone $row;
				// Check fields and their state
				for ($i = 0; $i < count($elementTable->Fields); $i++)
				{
					$field = $elementTable->Fields[$i];
					
					if ($field->prehandlertranslation != "")
					{
						if (method_exists($this, $field->prehandlertranslation))
						{
					  		$handler = $field->prehandlertranslation;
					  		$this->$handler($field, $row);
						}
					}
					$fieldname = $field->Name;
					$transfieldname = "jfc_" . $field->Name;
					$fieldContent = null;
					if (isset($row->$fieldname))
					{
						$fieldContent = new jfContent($db);
						// id for translation
						$fieldContent->id = intval($row->jfc_id);
						$fieldContent->language_id = $this->language_id;
						$fieldContent->reference_id = $row->id;
						$fieldContent->reference_table = $elementTable->Name;
						$fieldContent->reference_field = $fieldname;
						if (isset($row->$transfieldname))
						{
							$fieldContent->value = $row->$transfieldname;
							if (!empty($row->$fieldname) && empty($row->$transfieldname) && empty($noprehandlerrow->$transfieldname)) {
								$this->_untraslatedFields ++;
							}
							
						} else {
							$this->_untraslatedFields ++;
						}
						$fieldContent->original_value = $row->$fieldname;
						$fieldContent->original_text = $row->$fieldname;
						// TODO check published is a valid field !
						$fieldContent->published = $row->published;
					}
					else
					{
						$fieldContent = null;
					}

					$field->translationContent = $fieldContent;
				}
				
			$modifiedcompare = (isset($row->modified) && isset($row->jfc_modified) && ($row->modified > $row->jfc_modified)) ? 1 : 0;

			/*
			 * Checking the record state based on existance of translated entry in database,
			 * the fields translations (are all translatable fields not empty?) 
			 * and modified dates. If original modified date is later than transaltion modified date, original was modified after translation.
			 */ 
			if (!empty($row->jfc_id) && $this->_untraslatedFields == 0 && $modifiedcompare == 0)
			{
				$this->state = 1;
			}

			elseif (empty($row->jfc_id))
			{
				$this->state = -1;
			}
			else
			{
				$this->state = 0;
			}
			}
		}

	}

	/** Returns the content element fields which are text and can be translated
	 *
	 * @param	boolean	onle translateable fields?
	 * @return	array	of fieldnames
	 */
	public function getTextFields($translation = true)
	{
		$elementTable = $this->contentElement->getTable();
		$textFields = null;

		for ($i = 0; $i < count($elementTable->Fields); $i++)
		{
			$field = $elementTable->Fields[$i];
			$fieldType = $field->Type;
			if ($field->Translate == $translation && ($fieldType == "htmltext" || $fieldType == "text"))
			{
				$textFields[] = $field->Name;
			}
		}

		return $textFields;

	}

	/**
	 * Returns the field type of a field
	 *
	 * @param string $fieldname
	 */
	public function getFieldType($fieldname)
	{
		$elementTable = $this->contentElement->getTable();
		$textFields = null;

		for ($i = 0; $i < count($elementTable->Fields); $i++)
		{
			if ($elementTable->Fields[$i]->Name == $fieldname)
				return $elementTable->Fields[$i]->Type;
		}
		return "text";

	}

	/** Sets all fields of this content object to a certain published state
	 */
	public function setPublished($published)
	{
		if ($this->contentElement->getTarget() == "native")
		{
			$this->published = $published;
		}
		else
		{
			$elementTable = $this->contentElement->getTable();
			for ($i = 0; $i < count($elementTable->Fields); $i++)
			{
				$field = $elementTable->Fields[$i];
				$fieldContent = $field->translationContent;
				$fieldContent->published = $published;
			}
		}

	}

	/** Updates the reference id of all included fields. This
	 * Happens e.g when the reference object was created new
	 *
	 * @param	referenceID		new reference id
	 */
	public function updateReferenceID($referenceID)
	{
		if (intval($referenceID) <= 0)
			return;

		$elementTable = $this->contentElement->getTable();
		for ($i = 0; $i < count($elementTable->Fields); $i++)
		{
			$field = $elementTable->Fields[$i];
			$fieldContent = $field->translationContent;
			$fieldContent->reference_id = $referenceID;
		}

	}

	/** 
	 * Stores all fields of the content element
	 */
	public function store()
	{
		$elementTable = $this->contentElement->getTable();

		// different route based on target for saving data
		if ($this->contentElement->getTarget() == "native")
		{
			$db = JFactory::getDbo();
			$tableclass = $this->contentElement->getTableClass();
			if ($tableclass)
			{
				// find the reference id and translation id
				$reference_id = false;
				$translation_id = false;
				$language_id = false;
				for ($i = 0; $i < count($elementTable->Fields); $i++)
				{
					$field = $elementTable->Fields[$i];
					$fieldContent = $field->translationContent;

					if ($field->Translate)
					{
						// must have the original id
						if (isset($fieldContent->reference_id) && intval($fieldContent->reference_id) > 0)
						{
							//// NEW SYSTEM - Split the store method into 2  methods store and store Translation or override the bind method
							$reference_id = intval($fieldContent->reference_id);
							$translation_id = $this->translation_id;
							
							$this->reference_id = $reference_id;
							
							$language_id = intval($fieldContent->language_id);
							$jfm = JoomFishManager::getInstance();
							$languages = $jfm->getLanguagesIndexedById();
							$language_id = $languages[$language_id]->code;

							break;
						}
					}
				}
				if (!$reference_id)
				{
					return false;
				}

				// Now do the translation
				if (intval($translation_id) > 0)
				{
					// load the translation and amend
					$table = JTable::getInstance($tableclass);
					$table->load(intval($translation_id));
					$isNew = false;
				}
				else
				{
					// load the original and amend
					$table = JTable::getInstance($tableclass);
					$table->load(intval($reference_id));
					$key = $table->getKeyName();
					$table->$key = 0;
					if (is_callable(array($table, "setLocation")))
					{
						//$table->setLocation($table->parent_id, 'last-child');
						$table->setLocation(intval($reference_id), 'after');
					}
					$isNew = true;
				}
				$table->language = $language_id;
				for ($i = 0; $i < count($elementTable->Fields); $i++)
				{
					$field = $elementTable->Fields[$i];

					if ($field->Translate && isset($field->translationContent))
					{
						$fieldContent = $field->translationContent;
						$fieldname = $field->Name;
						$table->$fieldname = $fieldContent->value;
					}
				}

				// Is the translation published
				$publishfield = $this->contentElement->getPublishedField();
				if (isset($table->$publishfield))
				{
					$table->$publishfield = $this->published;
				}

							$app = JFactory::getApplication();
				$language= JFactory::getLanguage(); //get the current language
				$language->load( 'com_content');
				
				// Check the data.
				if (!$table->check())
				{
					$app->enqueueMessage($table->getError(), 'error');
					return false;
				}

				// Store the data.
				if (!$table->store())
				{
					$app->enqueueMessage($table->getError(), 'error');
					return false;
				}
				
				$this->id = $table->id;
				
				// Save the translation map 
				$this->generateTranslationMap($table, $isNew, $this->contentElement->getTableName(), $elementTable);
	
				if ($tableclass == "Menu")
				{
					$dispatcher = JDispatcher::getInstance();
					$dispatcher->trigger("onMenuAfterSave", array("com_menu,menu", &$table, $isNew, $elementTable));
				}
				else if ($tableclass == "Module")
				{
					$dispatcher = JDispatcher::getInstance();
					$dispatcher->trigger("onModuleAfterSave", array("com_modules,module", &$table, $isNew, $elementTable));
				}			
				
				/*
				// contient has its own plugin!
				else if ($tableclass == "Content")
				{
					$dispatcher = JDispatcher::getInstance();
					$dispatcher->trigger("onContentAfterJFSave", array("com_content.content", &$table, $isNew, $elementTable));
				}
				 */
				
				return true;
			}
			else
				return false;
		}
		else
		{
			$success = true;
			for ($i = 0; $i < count($elementTable->Fields); $i++)
			{
				$field = $elementTable->Fields[$i];
				$fieldContent = $field->translationContent;

				if ($field->Translate)
				{
					if (isset($fieldContent->reference_id))
					{
						if (isset($fieldContent->value) && $fieldContent->value != '')
						{
							$success = $success && $fieldContent->store(true);
						}
						// special case to handle readmore in original when there is none in the translation
						else if (isset($fieldContent->value) && $fieldContent->reference_table == "content" && $fieldContent->reference_field == "fulltext")
						{
							$success = $success && $fieldContent->store(true);
						}
						else
						{
							$success = $success && $fieldContent->delete();
						}
					}
				}
			}
			return $success;
		}

	}

	/** Checkouts all fields of this content element
	 */
	public function checkout($who, $oid=null)
	{
		$elementTable = $this->contentElement->getTable();
		for ($i = 0; $i < count($elementTable->Fields); $i++)
		{
			$field = $elementTable->Fields[$i];
			$fieldContent = $field->translationContent;

			if ($field->Translate)
			{
				if (isset($fieldContent->reference_id))
				{
					$fieldContent->checkout($who, $oid);
					JError::raiseWarning(200, JText::_('Problems binding object to fields: ' . $fieldContent->getError()));
				}
			}
		}

	}

	/** Checkouts all fields of this content element
	 */
	public function checkin($oid=null)
	{
		$elementTable = $this->contentElement->getTable();
		for ($i = 0; $i < count($elementTable->Fields); $i++)
		{
			$field = $elementTable->Fields[$i];
			$fieldContent = $field->translationContent;

			if ($field->Translate)
			{
				if (isset($fieldContent->reference_id))
				{
					$fieldContent->checkin($oid);
					JError::raiseWarning(200, JText::_('Problems binding object to fields: ' . $fieldContent->getError()));
				}
			}
		}

	}

	/** Delets all translations (fields) of this content element
	 */
	public function delete($oid=null)
	{
		$elementTable = $this->contentElement->getTable();
		for ($i = 0; $i < count($elementTable->Fields); $i++)
		{
			$field = $elementTable->Fields[$i];
			$fieldContent = $field->translationContent;
			if ($field->Translate)
			{
				if (isset($fieldContent->reference_id))
				{
					if (!$fieldContent->delete($oid))
					{
						echo $fieldContent->getError() . "<br />";
					}
				}
			}
		}

	}

	/** Returns the content element table this content is based on
	 */
	public function getTable()
	{
		return $this->contentElement->getTable();

	}

	/**
	 * Temporary legacy function copied from Joomla
	 *
	 * @param unknown_type $array
	 * @param unknown_type $obj
	 * @param unknown_type $ignore
	 * @param unknown_type $prefix
	 * @return unknown
	 */
	private function _jfBindArrayToObject($array, &$obj, $ignore='', $prefix=NULL)
	{
		if (!is_array($array) || !is_object($obj))
		{
			return (false);
		}

		foreach (get_object_vars($obj) as $k => $v)
		{
			if (substr($k, 0, 1) != '_')
			{
				// internal attributes of an object are ignored
				if (strpos($ignore, $k) === false)
				{
					if ($prefix)
					{
						$ak = $prefix . $k;
					}
					else
					{
						$ak = $k;
					}
					if (isset($array[$ak]))
					{
						$obj->$k = $array[$ak];
					}
				}
			}
		}

		return true;

	}

	public function generateTranslationMap( &$article, $isNew, $tablename, $elementTable=false){
		$keyname = $article->getKeyName();
		$translationid = $article->$keyname;
		$originalid = $this->reference_id;
		if ($originalid > 0)
		{
			$language = $article->language;

			$db = JFactory::getDbo();
			$sql = "replace into #__jf_translationmap (reference_id, translation_id, reference_table, language ) values ($originalid, $translationid," . $db->quote($tablename) . "," . $db->quote($language) . ")";
			$db->setQuery($sql);
			$success = $db->query();
			return;
		}
		else
		{
			
		}
	}
}