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
 * $Id: default.php 226 2011-05-27 07:29:41Z alex $
 * @package joomfish
 * @subpackage Views
 *
*/

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );
/**
 * layout translationmap
 * use in layout default default_list 
 * to set the table #__jf_translationmap manual
 * if the translation not make with joomfish
*/
$joomfishManager = JoomFishManager::getInstance();
$lang = null;
$lang = $joomfishManager->getLanguageByID($this->language_id);

JHTML::_('behavior.framework');

$contentTable = $this->contentElement->getTable();
$referencefield = "id";
foreach ($contentTable->Fields as $tableField)
{
	switch($tableField->Type)
	{
		case "referenceid":
		$referencefield = $tableField->Name;
			$idField = 'c.' . $tableField->Name;
		break;
		case "titletext":
			$titleField = 'c.' . $tableField->Name;
		break;
	}
}

$orig_languageField = 'co.language';
$trans_languageField = 'c.language';


$translation_idField = 'tm.translation_id';
$reference_idField = 'tm.reference_id';
$task = 'translate.translationmap';


?>
<style>
html {
	overflow-y: hidden;
}

#action
{
	/*
	padding-left: 2px;
	padding-right: 2px;
	*/
}

.panelform ul.adminformlist li.list
{
	margin-bottom: 2px;
	margin-top: 2px;
	padding-bottom: 2px;
	padding-top: 2px;
}

.panelform ul.adminformlist li.header
{
	margin-bottom: 2px;
	margin-top: 2px;
	padding-bottom: 2px;
	padding-top: 2px;
}

.panelform ul.adminformlist
{
	float: left;
	width: 100%;
}

.panelform ul li.header
{
	font-weight: bold;
}
.panelform ul.adminformlist li.li_row0
{
	background-color: #FFFFFF;
}

.panelform ul.adminformlist li.li_row1
{
	background-color: #F0F0F0;
}


</style>
<script>
	//<![CDATA[
function save()
{
	<!-- as ajax -->
	var actiondiv = document.id('action');
	var text = actiondiv.get('html');
	var ajax_reference_id = document.id('ajax_reference_id');
	var ajax_translation_id = document.id('ajax_translation_id');
	var ajax_language_id = document.id('ajax_language_id');
	var url = 'index.php?option=com_joomfish&task=translate.translationMapSave&reference_id='+ajax_reference_id.value+'&translation_id='+ajax_translation_id.value+'&language_id='+ajax_language_id.value+'&<?php echo JUtility::getToken(); ?>=1';
	var myRequest = new Request({
		url: url,
		method: 'get',
		onRequest: function(){
			actiondiv.set('html', text + 'loading...');
		},
		onSuccess: function(responseText,responseXML){
			if(responseText == '0')
			{
				actiondiv.set('html', 'fail to save');
				buttonSave.addClass('hide');
				ajax_reference_id.value = '';
				ajax_translation_id.value = '';
				ajax_language_id.value = ''; //
			}
			else
			{
				window.parent.document.adminForm.submit();
				
			}
		},
		onFailure: function(){
			actiondiv.set('html', 'Sorry, your request failed :(');
			buttonSave.addClass('hide');
			ajax_reference_id.value = '';
			ajax_translation_id.value = '';
			ajax_language_id.value = ''; //
		}
	});
	myRequest.send();
}
	
function action(action,action_id,content_id_reference,id,lang_id) {
	var actiondiv = document.id('action');
	
	var ajax_reference_id = document.id('ajax_reference_id');
	var ajax_translation_id = document.id('ajax_translation_id');
	var ajax_language_id = document.id('ajax_language_id');
	
	var buttonSave = document.id('saveTranslationMap');
	if(action == 'original' || action == 'translation')
	{
		buttonSave.removeClass('hide');
	}
	else
	{
		buttonSave.addClass('hide');
		ajax_reference_id.value = '';
		ajax_translation_id.value = '';
		ajax_language_id.value = ''; //
	}
	
	if(action == 'translation')
	{
		actiondiv.set('html','action set as reference, reference_id:'+content_id_reference+', translation_id:'+id + ', language_id:'+lang_id);
		ajax_reference_id.value = content_id_reference;
		ajax_translation_id.value = id;
		ajax_language_id.value = lang_id; //
	}
	else if(action == 'original')
	{
		actiondiv.set('html','action set as translation, reference_id:'+id+', translation_id:'+content_id_reference + ', language_id:'+lang_id);
		ajax_reference_id.value = id; //
		ajax_translation_id.value = content_id_reference; //
		ajax_language_id.value = lang_id;
	}
}
//]]>
</script>
<form action="index.php" method="post" name="adminForm">

<input type="hidden" name="language_code" value="" />

<input type="hidden" name="item_id" value="<?php echo $this->reference_id;?>" />

<input type="hidden" id="ajax_language_id" name="ajax_language_id" value="" />
<input type="hidden" id="ajax_reference_id" name="ajax_reference_id" value="" />
<input type="hidden" id="ajax_translation_id" name="ajax_translation_id" value="" />

<input type="hidden" id="language_id" name="language_id" value="<?php echo $this->language_id;?>" />
<input type="hidden" id="reference_id" name="reference_id" value="<?php echo $this->reference_id;?>" />

<input type="hidden" name="catid" value="<?php echo $this->catid; ?>" />


<fieldset>
	<div style="float: right;">
		<button onclick="save();" class="hide" id="saveTranslationMap" type="button"><?php echo JText::_( 'JSAVE' );?></button>
		<button onclick="window.parent.parent.SqueezeBox.close();" type="button"><?php echo JText::_( 'JCANCEL' );?></button>
		</div>
	<div class="configuration"><?php echo JText::_( 'TRANSLATE_MAP' );?></div>
</fieldset>
<div style="height:450px;">
	
	
	<fieldset class="panelform">
		<ul class="adminformlist">
			<li>
				<div class="width-20 fltlft">
					<strong>Title</strong>
				</div>
				
				<div class="width-10 fltlft" style="width:10%;">
					<strong>ID</strong>
				</div>
				<div class="width-50 fltlft">
					<strong>Action</strong>
				</div>
				<div class="width-20 fltlft">
					<strong>Info</strong>
				</div>
				<div class="clear" style="clear:both;"></div>
			</li>
			<li>
				<div class="width-20 fltlft">
					<?php echo $this->translationObject->title; ?>
				</div>
				<div class="width-10 fltlft" style="width:10%;">
					<?php echo $this->translationObject->id; ?>
				</div>
				<div id="action" class="width-50 fltlft">
					nothing
				</div>
				<div class="width-20 fltlft">
					<div>
						tablename: <?php echo $this->catid; ?>
					</div>
					<div>
						language_id: <?php echo $this->language_id; ?>
					</div>
					<div>
						language: <?php echo $lang->lang_code; ?>
					</div>
				</div>
			</li>
		</ul>
	</fieldset>
	<div>
	<fieldset class="panelform" style="height:320px;">
		<div class="filter-select fltrt">
			<select name="filter_language" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_LANGUAGE');?></option>
				<?php 
				$translate = true;
				$db		= JFactory::getDBO();
				$query	= $db->getQuery(true);

				// Build the query.
				$query->select('a.lang_code AS value, a.title AS text, a.title_native');
				$query->from('#__languages AS a');
				$query->where('a.lang_code <> '.$db->quote($lang->lang_code));
	
				$query->order('a.title');
				$db->setQuery($query);
				$langs = $db->loadObjectList();
				array_unshift($langs, new JObject(array('value' => '*', 'text' => '*'))); //$translate ? JText::alt('JALL', 'language') : 'JALL_LANGUAGE')));
				?>
				<?php echo JHtml::_('select.options', $langs, 'value', 'text', $this->filter_language);?>
			</select>
		</div>
		<div class="clear" style="clear:both;"></div>
		<ul class="adminformlist" style="height:20px;">
			<li class="header">
				<div class="width-30 fltlft">
					<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', $titleField, $this->orderDir, $this->order,$task); ?>
				</div>
				
				<div class="width-5 fltlft" style="width:5%;">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', $idField, $this->orderDir, $this->order,$task); ?>
				</div>
				
				<div class="width-15 fltlft" style="width:15%;">
					<?php echo JHtml::_('grid.sort', 'reference_id', $reference_idField, $this->orderDir, $this->order,$task); ?>
				</div>
				
				<div class="width-15 fltlft" style="width:15%;">
					<?php echo JHtml::_('grid.sort', 'translation_id', $translation_idField, $this->orderDir, $this->order,$task); ?>
				</div>
				
				<div class="width-15 fltlft" style="width:15%;">
					<?php echo JHtml::_('grid.sort', 'trans_language', $trans_languageField, $this->orderDir, $this->order,$task); ?>
				</div>
				
				<div class="width-15 fltlft" style="width:15%;">
					<?php echo JHtml::_('grid.sort', 'orig_language', $orig_languageField, $this->orderDir, $this->order,$task); ?>
				</div>
				<div class="clear" style="clear:both;"></div>
			</li>
		</ul>
		<ul class="adminformlist" style="overflow-y:scroll;height:275px;">
			<?php if(count($this->contentmaprows) >0):?>
			<?php $k = 0;?>
			<?php foreach($this->contentmaprows as $row):?>
			
			<?php if(!$row->orig_language || ($lang->code <> $row->orig_language && $row->transmap_language <> $lang->lang_code )) : ?>
			<?php $k = 1 - $k; ?>
			<li class="<?php echo "li_row$k list"; ?>">
				<div class="width-30 fltlft">
					<?php echo $row->title; ?>
				</div>
				
				<div class="width-5 fltlft" style="width:5%;">
					<?php echo ($row->id ? $row->id : '&nbsp;'); ?>
				</div>
				
				<div class="width-15 fltlft" style="width:15%;">
					<?php echo ($row->reference_id ? $row->reference_id : '&nbsp;'.($row->orig_language != '*' ? '<a class="hasTip original" title="As Original::ID:'.$this->reference_id.', Language: '.($row->orig_language ? $row->orig_language : $row->language).'" onclick="action(\'original\',\''.$row->reference_id.'\',\''.$row->id.'\',\''.$this->reference_id.'\',\''.$row->orig_language_id.'\');">click me!</a>' : '' ) ); ?>
				</div>
				
				<div class="width-15 fltlft" style="width:15%;">
					<?php echo ($row->translation_id ? ($row->translation_id.($row->trans_language ? '&nbsp;' : ' <a class="hasTip translation" title="As Translation::ID:'.$this->reference_id.', Language: '.$lang->lang_code.'" onclick="action(\'reference\',\''.$row->translation_id.'\',\''.$row->id.'\',\''.$this->reference_id.'\',\''.$this->language_id.'\');">click me!</a>')) : ($row->trans_language ? '&nbsp;' : '<a class="hasTip translation"  title="As Translation::ID:'.$this->reference_id.', Language: '.$lang->lang_code.'" onclick="action(\'translation\',\''.$row->translation_id.'\',\''.$row->id.'\',\''.$this->reference_id.'\',\''.$this->language_id.'\');">click me!</a>')); ?>
				</div>
				
				<div class="width-15 fltlft" style="width:15%;">
				
					<?php echo ($row->trans_language ? $row->trans_language : '&nbsp;'); ?>
				
				</div>
				
				<div class="width-15 fltlft" style="width:15%;">
					<?php echo ($row->orig_language ? $row->orig_language : $row->language.'&nbsp;'); ?>
				</div>
				<div class="clear" style="clear:both;"></div>
			</li>
			<?php endif; ?>
			<?php endforeach; ?>
			<?php endif; ?>
		</ul>
	</fieldset>
	</div>
</div>
<input type="hidden" name="option" value="com_joomfish" />
<input type="hidden" name="task" value="translate.translationmap" />
<input type="hidden" name="tmpl" value="component" />
<input type="hidden" name="layout" value="translationmap" />

<input type="hidden" name="filter_order" value="<?php echo $this->order;?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->orderDir;?>" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>
