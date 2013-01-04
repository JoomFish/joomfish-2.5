<?php

/**
 * Joom!Fish - Multi Lingual extention and translation manager for Joomla!
 * Copyright (C) 2003 - 2012, Think Network GmbH, Munich
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
 * @subpackage jfdatabase
 * @version 2.0
 *
 */
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

/**
 * Exchange of the database abstraction layer for multi lingual translations.
 */
class plgSystemJFInlinemapping extends JPlugin
{

	function onAfterRoute()
	{

		// NEW SYSTEM
		// amend editing page but only for native elements
		if (!in_array(JRequest::getCmd('option'), array("com_content","com_menus","com_modules", "com_categories"))) return;
		JFactory::getLanguage()->load('com_joomfish');
	
		$reference_id = JRequest::getInt("id", 0);
		$default_lang = JoomFishManager::getInstance()->getDefaultLanguage();
	
		if (JFactory::getApplication()->isAdmin() && JRequest::getCmd("layout") == "edit")
		{
			$db = JFactory::getDbo();
			$table = "content";
			if (JRequest::getCmd('option') == "com_menus")
			{
				$table = "menu";
			}
			else if (JRequest::getCmd('option') == "com_modules")
			{
				$table = "modules";
			}
			else if (JRequest::getCmd('option') == "com_categories")
			{
				$table = "categories";
			}
			$item_id = JRequest::getInt("id");
			$db->setQuery('select * from #__jf_translationmap where reference_table="' . $table . '" AND translation_id=' . $item_id);
			$translations = $db->loadObjectList();
			$original = 0;
			if (count($translations) > 0)
			{
				$original = $translations[0]->reference_id;
			}
	
				
			// Load the modal behavior script.
			JHtml::_('behavior.modal', 'a.modal');
	
			// Build the script.
			$script = array();
			$script[] = '	function jfSelectArticle_'.$original.'(id, title) {';
			$script[] = '		document.id("jfreference_id").value = id;';
			$script[] = '		document.id("'.$original.'_name").value = title;';
			$script[] = '		SqueezeBox.close();';
			$script[] = '	}';
	
			// Add the script to the document head.
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
	
	
			// Setup variables for display.
			$html	= array();
			$link	= 'index.php?option=com_joomfish&task=translate.originallist&view=translate&layout=modal&tmpl=component&table='.$table.'&function=jfSelectArticle_'.$original;
	
			$db	= JFactory::getDBO();
			$db->setQuery(
					'SELECT title' .
					' FROM #__'.$table .
					' WHERE id = '.(int) $original
			);
			$title = $db->loadResult();
	
			if ($error = $db->getErrorMsg()) {
				JError::raiseWarning(500, $error);
			}
	
			$jfreference_id = $original;
			if (empty($title) && $reference_id == 0) {
				$title = JText::_('COM_JOOMFISH_SELECT_AN_ITEM');
			} else if (empty($title)) {
				$db	= JFactory::getDBO();
				$db->setQuery(
						'SELECT title' .
						' FROM #__'.$table .
						' WHERE id = '.(int) $item_id
				);
				$title = $db->loadResult();
				$jfreference_id = 0;
					
			}
			$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
	
			// The current user display field.
			$html[] = "<div class=\'fltlft\'>";
			$html[] = "  <input type=\'text\' id=\'".$original."_name\' value=\'".$title."\' disabled=\'disabled\' size=\'35\' />";
			$html[] = "</div>";
	
	
			// The user select button.
			$html[] = "<div class=\'button2-left\' id=\'item-select-button\' >";
			$html[] = "  <div class=\'blank\'>";
			$html[] = '	<a class=\"modal\" title=\''.JText::_('COM_JOOMFISH_CHANGE_ITEM').'\'  href=\''.$link.'&amp;' .JSession::getFormToken().'=1\' rel=\"{handler: \'iframe\', size: {x: 800, y: 450}}\">'.JText::_('COM_JOOMFISH_CHANGE_ITEM_BUTTON')."</a>";
			$html[] = "  </div>";
			$html[] = "</div>";
	
	
			// class='required' for client side validation
			$class = '';
			//if ($this->required) {
			$class = " class=\'required modal-value\'";
			//}
	
			$html[] = "<input type=\'hidden\' id=\'jfreference_id\'".$class." name=\'jfreference_id\' value=\'".$jfreference_id."\' />";
			$html = implode("", $html);
	
				
			$orname 		= $original.'_name';
			$is_translation_txt = JText::_('COM_JOOMFISH_SELECTOR_IS_TRANSLATION');
			$translation_of_txt = JText::_('COM_JOOMFISH_SELECTOR_TRANSLATION_OF');
			$original_id_txt	= JText::_('COM_JOOMFISH_SELECTOR_ORIGINAL_ID');
			$yes			= JText::_('JYES');
			$no				= JText::_('JNO');
				
			$doc = JFactory::getDocument();
			$script = <<<SCRIPT
window.addEvent('domready', function() {
	var langselect = $('jform_language');
	var languagechanged = 0;
	
	if (langselect){
		var isTranslation = $original>0;
		var langselectli = langselect.getParent()
		var jftranslation = new Element("select",{ name:'jftranslation', id:'jftranslation'});
		var opt = new Element("option",{value:1, 'text':'$yes'});
		jftranslation.appendChild(opt);
		if (!isTranslation){
			opt = new Element("option",{value:0, 'text':'$no'});
			opt.selected=true;
			jftranslation.appendChild(opt);
		}
		jftranslation.value= isTranslation?1:0;
		jftranslation.addEvent('change',function(){
			if(this.value==1){
				$('jform_id').readonly = false;
				$('jform_id').removeClass('readonly');
				if(languagechanged == 1){
					$('jform_id').value = 0;
				}
	
				$('jfreference_id_lbl').style.display="block";
				$('$orname').getParent().style.display="block";
				// če je nov item ali pri obstoječem spremenimo translate v 1
				if ($('item-select-button') && (languagechanged == 0 || $reference_id == 0 )) {
					$('item-select-button').style.display="block";
				}
				if($('jfid') && languagechanged == 1) {
					$('jfid').value = 0;
					}
				if ($('jform_alias')) {
					$('jform_alias').value = "";
				}
				
			}
			else {
				$('jform_id').value = refid;
				$('jform_id').readonly = true;
				$('jform_id').addClass('readonly');
				$('jfreference_id_lbl').style.display="none";
				$('$orname').getParent().style.display="none";
	
				if ($('item-select-button')) {
					$('item-select-button').style.display="none";
				}
				if($('jfid')) {
						$('jfid').value = refid;
					}
			}
		});
	
		var jflanglabel   = new Element("label",{id:'jftranslation-lbl', for:'jftranslation'});
		jflanglabel.appendText("$is_translation_txt");
		
		var refid = $('jform_id').value;
		//var jflanginput = new Element("input",{ type:'text', name:'jfreference_id', id:'jfreference_id', value:$original, readonly:'readonly'});
		//var els =
		var jflanginput = Elements.from("$html");
		var jftranslabel  = new Element("label", {for:"jfreference_id", id:"jfreference_id_lbl"});
		jftranslabel.appendText("$translation_of_txt");
	
		var jforigalinput = new Element("input",{ type:'text', name:'jforiginal_id', id:'jforiginal_id', value:refid, readonly:'readonly'});
		var jforiglabel  = new Element("label", {for:"jforiginal_id", id:"jforiginal_id_lbl"});
		jforiglabel.appendText("$original_id_txt");
	
	
	
		var newid = false;
		if (!$('id')){
			// must also have a new pseudo  id to make sure replaces anything in the URL!
			// editing existing elements don't have this
			var newid = new Element("input",{ type:'text', name:'id', id:'jfid', value:refid, readonly:'readonly'});
			newid.style.display="none";
			var jfnewidlabel  = new Element("label", {for:"jfid"});
			jfnewidlabel.appendText("new id : ");
			jfnewidlabel.style.display="none";
		}
	
	
		// new li row
		var li = new Element('li');
		li.appendChild(jflanglabel);
		li.appendChild(jftranslation);
		// translation id
		li.appendChild(jftranslabel);
		//li.appendChild(jflanginput);
		jflanginput.inject(li);
	
		// original id
		li.appendChild(jforiglabel);
		li.appendChild(jforigalinput);
	
		if (newid){
			li.appendChild(jfnewidlabel);
			li.appendChild(newid);
		}
	
		// insert it after the lang selector
		li.inject( langselectli,'after');
	
		if(langselect.value=="*" || langselect.value=="$default_lang"){
			jftranslation.getParent().style.display="none";
		}
	
		langselect.addEventListener("change", function(){
			if(langselect.value=="*" || langselect.value=="$default_lang"){
				jftranslation.set('value', 0);
				jftranslation.getParent().style.display="none";
				languagechanged = 1;
				jftranslation.fireEvent("change");
			}
			else {
				jftranslation.set('value', 1);
				jftranslation.getParent().style.display="block";
				languagechanged = 1;
				jftranslation.fireEvent("change");
			}
	
		});
	
		if ($('item-select-button') && (languagechanged == 0 || $jfreference_id==0 )) {
					$('item-select-button').style.display="none";
		}
	
		if (!isTranslation){
				$('jfreference_id_lbl').style.display="none";
				$('$orname').getParent().style.display="none";
				$('item-select-button').style.display="none";
		}
	
	}
	// as html is inserted by js we need to manually fire modal
	SqueezeBox.initialize({});
SqueezeBox.assign($$('a.modal'), {
parse: 'rel'
});
});
		
SCRIPT;
			$doc->addScriptDeclaration($script);
		}
		}



}