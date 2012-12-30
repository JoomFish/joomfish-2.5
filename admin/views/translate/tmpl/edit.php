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
 * @subpackage Views
 *
*/

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );
/**
	* @return void
	* @param object $this->translationObject
	* @param array $this->langlist
	* @param string $this->catid
	* @desc Shows the dialog for the content translation
	*/

if ($this->showMessage) {
	echo $this->loadTemplate('message');
}

$select_language_id = $this->select_language_id;
$elementTable = $this->translationObject->getTable();
$option = JRequest::getCmd("option");

/*ini_set('xdebug.var_display_max_children', 3000 );
ini_set('xdebug.var_display_max_depth', 3000 );*/

// Should use CSS for image waps - in the meantime to this.
//$jsfile = '<script language="javascript" type="text/javascript" src="'.JURI::root().'/includes/js/mambojavascript.js" ></script>';
//JFactory::getApplication()->addCustomHeadTag( $jsfile );

//$this->_JoomlaHeader( JText::_('TITLE_TRANSLATION'), 'joomfish', '', false );

jimport( 'joomla.html.editor' );
$wysiwygeditor = JFactory::getEditor();

$editorFields=null;
foreach ($this->tranFilters as $filter) {
	echo "<input type='hidden' name='".$filter->filterType."_filter_value' value='".$filter->filter_value."'/>";
}

// check system and user editor and load appropriate copying script
$user = JFactory::getUser();
$conf = JFactory::getConfig();
$editor = $conf->getValue('config.editor');

// Place a reference to the element Table in the config so that it can be used in translation of urlparams !!!
$conf->setValue('joomfish.elementTable',$elementTable);

echo "\n<!-- editor is $editor //-->\n";
$editorFile = JOOMFISH_ADMINPATH."/editors/".strtolower($editor).".php";
if (file_exists($editorFile)){
	require_once($editorFile);
}
else {
	?>

	<script language="javascript" type="text/javascript">
	function copyToClipboard(value,action) {
		try {
			if (document.getElementById) {
				innerHTML="";
				if (action=="copy") {
					srcEl = document.getElementById("original_value_"+value);
					innerHTML = srcEl.innerHTML;
				}
				if (window.clipboardData){
					window.clipboardData.setData("Text",innerHTML);
					alert("<?php echo JText::_('CLIPBOARD_COPIED'); ?>");
				}
				else {
					srcEl = document.getElementById("text_origText_"+value);
					srcEl.value = innerHTML;
					srcEl.select();
					alert("<?php echo JText::_('CLIPBOARD_COPY');?>");
				}
			}
		}
		catch(e){
			alert("<?php echo JText::_('CLIPBOARD_NOSUPPORT');?>");
		}
	}
	function translationWriteValue(field, value){
		try {
			var srcEl = document.getElementById("text_origText_"+field);
			srcEl.value = value;
			srcEl.select();
			if (window.clipboardData){
				window.clipboardData.setData("Text",innerHTML);
				alert("<?php echo JText::_('CLIPBOARD_COPIED'); ?>");
			}
			else {
				srcEl = document.getElementById("text_origText_"+field);
				srcEl.value = value;
				srcEl.select();
				alert("<?php echo JText::_('CLIPBOARD_COPY');?>");
			}
		}

		catch(e){
			alert("<?php echo JText::_('CLIPBOARD_NOSUPPORT');?>");
		}
	}

	</script>
<?php } ?>

	<script language="javascript" type="text/javascript">

	function translateText(result) {
	       if (!result.error) {
				translationWriteValue(this.value, result.data.translations[0].translatedText);
				}
				else {
					alert(result.error.message)
				}
	      }
	    
	function googleTranslate(value) {
		<?php
		$jfm = JoomFishManager::getInstance();
		$languages = $jfm->getLanguagesIndexedById();
		$targetlang = $languages[$select_language_id];
		$code = substr($targetlang->code,0,2);
		$defaultLang = substr($this->get('DefaultLanguage'),0,2);
		?>
		
		var APIKey = '<?php echo $this->googleApikey;?>';
		if (!APIKey) {
			alert('<?php echo JText::_('GOOGLE_TRANSLATE_API_KEY');?>');
			return;
		}
		
		this.value = value;
		var newScript = document.createElement('script');
		newScript.type = 'text/javascript';
	    var sourceText = escape(document.getElementById('original_value_'+value).innerHTML);
	    // WARNING: be aware that YOUR-API-KEY inside html is viewable by all your users.
	    // Restrict your key to designated domains or use a proxy to hide your key
	    // to avoid misuage by other party.
	    var source = 'https://www.googleapis.com/language/translate/v2?key=<?php echo $this->googleApikey;?>&source=<?php echo $defaultLang;?>&target=<?php echo $code;?>&callback=translateText&q=' + sourceText;
	    newScript.src = source;

	    document.getElementsByTagName('head')[0].appendChild(newScript);
	}
	
	function confirmChangeLanguage(fromLang, fromIndex){
		selections = document.getElementsByName("language_id")[0].options;
		selection = document.getElementsByName("language_id")[0].selectedIndex;
		//alert(selection+" from "+ fromIndex+" which is "+fromLang+" xx "+document.getElementsByName("language_id")[0].value);
		var toLang = selections[selection].text;
		var toValue = selection = document.getElementsByName("language_id")[0].value;
		if (fromIndex!=toValue){
			answer = confirm("<?php echo preg_replace( '#<br\s*/>#', '\n', JText::_('JS_CHANGE_TRANSLATION_LANGUAGE')); ?>");
			if (!answer) {
				document.getElementsByName("language_id")[0].selectedIndex=fromIndex;
			}
		}
		else {
			alert("<?php echo preg_replace( '#<br\s*/>#', '\n', JText::_('JS_REINSTATE_TRANSLATION_LANGUAGE',true)); ?>");
		}
	}
    </script>
<form action="index.php" method="post" name="adminForm">
   	<table width="100%">
	  <tr>
	    <td>
		<table width="90%" border="0" cellpadding="2" cellspacing="2" class="adminform">
			<?php
			$k=1;
			for( $i=0; $i<count($elementTable->Fields); $i++ ) {
				$field = $elementTable->Fields[$i];

				$field->preHandle($elementTable);
				$originalValue = $field->originalValue;

				// if we supress blank originals
				if ($field->ignoreifblank && $field->originalValue==="") continue;

				if( $field->Translate ) {
					$translationContent = $field->translationContent;

					// This causes problems in Japanese/Russian and params fields
					//jimport('joomla.filter.output');
					//JFilterOutput::objectHTMLSafe( $translationContent );


					if( strtolower($field->Type)=='hiddentext') {
							?>
							<tr><td colspan="3" style="display:none"><td>
							<input type="hidden" name="id_<?php echo $field->Name;?>" value="<?php echo $translationContent->id;?>" />
							<input type="hidden" name="origValue_<?php echo $field->Name;?>" value='<?php echo md5( $field->originalValue );?>' />
							<textarea  name="origText_<?php echo $field->Name;?>" style="display:none"><?php echo $field->originalValue;?></textarea>
							<textarea name="refField_<?php echo $field->Name;?>"  style="display:none"><?php echo $translationContent->value; ?></textarea>
							</td></tr>
							<?php
					}
					else {
				?>
		    <tr class="<?php echo "row$k"; ?>">
		      <th colspan="3"><?php echo JText::_( 'DBFIELDLABEL' ) .': '. $field->Label;?></th>
		    </tr>
	      	<?php
	      	if (strtolower($field->Type)!='params'){
	      	?>
		    <tr class="<?php echo "row$k"; ?>">
		      <td align="left" valign="top"><?php echo JText::_( 'ORIGINAL' );?></td>
		      <td align="left" valign="top" id="original_value_<?php echo $field->Name?>">
		      <?php
		      if (preg_match("/<form/i",$field->originalValue)){
		      	$ovhref = JRoute::_("index.php?option=com_joomfish&task=translate.originalvalue&field=".$field->Name."&cid=".$this->translationObject->id."&lang=".$select_language_id.'&tmpl=component');
		      	echo '<a class="modal" rel="{handler: \'iframe\', size: {x: 700, y: 500}}" href="'.$ovhref.'" >'.JText::_("Content contains form - click here to view in popup window").'</a>';
		      }
		      else {
		      	echo $field->originalValue;
		      }
		      ?>
		      </td>
			  <td valign="top" class="button">
				<input type="hidden" name="origValue_<?php echo $field->Name;?>" value='<?php echo md5( $field->originalValue );?>' />
				<textarea  name="origText_<?php echo $field->Name;?>" style="display:none"><?php echo $field->originalValue;?></textarea>
				<?php 
				 if( strtolower($field->Type)=='readonlytext'){
					 
				 }
				else if( strtolower($field->Type)!='htmltext' ) {?>
					<a class="toolbar" onclick="document.adminForm.refField_<?php echo $field->Name;?>.value = document.adminForm.origText_<?php echo $field->Name;?>.value;"><span class="icon-32-copy"></span><?php echo JText::_( 'COPY' ); ?></a>
				<?php }	else { ?>
					<div id='googlebranding'>
						<a class="toolbar" onclick="googleTranslate('<?php echo $field->Name;?>');" onmouseout="MM_swapImgRestore();"><span class="icon-32-copy"></span><?php echo JText::_( 'TRANSLATE' ); ?></a>
					</div>
					<a class="toolbar" onclick="copyToClipboard('<?php echo $field->Name;?>','copy');" onmouseout="MM_swapImgRestore();"><span class="icon-32-copy"></span><?php echo JText::_( 'COPY' ); ?></a>
				<?php  }?>
			  </td>
		    </tr>
		    <tr class="<?php echo "row$k"; ?>">
		      <td align="left" valign="top"><?php echo JText::_( 'TRANSLATION' );?></td>
		      <td align="left" valign="top">
					  <input type="hidden" name="id_<?php echo $field->Name;?>" value="<?php echo $translationContent->id;?>" />
						<?php
						if( strtolower($field->Type)=='text' || strtolower($field->Type)=='titletext' ) {
							$length = ($field->Length>0)?$field->Length:60;
							$maxLength = ($field->MaxLength>0) ? "maxlength=".$field->MaxLength:"";
							?>
							<input class="inputbox" type="text" name="refField_<?php echo $field->Name;?>" size="<?php echo $length;?>" value="<?php echo $translationContent->value; ?>" "<?php echo $maxLength;?>"/>

							<?php
						} else if( strtolower($field->Type)=='textarea' ) {
							$ta_rows = ($field->Rows>0)?$field->Rows:15;
							$ta_cols = ($field->Columns>0)?$field->Columns:30;
							?>
							<textarea name="refField_<?php echo $field->Name;?>" rows="<?php echo $ta_rows;?>" cols="<?php echo $ta_cols;?>" ><?php echo $translationContent->value; ?></textarea>
							<?php
						} else if( strtolower($field->Type)=='htmltext' ) {
							?>
							<?php
							$editorFields[] = array( "editor_".$field->Name, "refField_".$field->Name );
							// parameters : areaname, content, hidden field, width, height, rows, cols
							echo $wysiwygeditor->display( "refField_".$field->Name, $translationContent->value, '100%', '300', '70', '15',$field->ebuttons ) ;
						}
						else if( strtolower($field->Type)=='readonlytext') {
							$length = ($field->Length>0)?$field->Length:60;
							$maxLength = ($field->MaxLength>0)?$field->MaxLength:60;
							$value =  strlen($translationContent->value)>0? $translationContent->value:$field->originalValue;
							?>
							<input class="inputbox" type="text" readonly="readonly" name="refField_<?php echo $field->Name;?>" size="<?php echo $length;?>" value="<?php echo $value; ?>" maxlength="<?php echo $maxLength;?>"/>
							<?php
						}
						?>
				</td>
				<td valign="top" class="button">
					<?php
					if ( strtolower($field->Type)=='readonlytext'){
					}
					else if( strtolower($field->Type)!='htmltext' ) {?>
					<a class="toolbar" onclick="document.adminForm.refField_<?php echo $field->Name;?>.value = '';"><span class="icon-32-delete"></span><?php echo JText::_( 'DELETE' ); ?></a>
					<?php } else {?>
					<a class="toolbar" onclick="copyToClipboard('<?php echo $field->Name;?>','clear');"><span class="icon-32-delete"></span><?php echo JText::_( 'DELETE' ); ?></a>

					<?php }?>
					</td>
		    </tr>
	      	<?php
	      	}
	      	// else if params
	      	else {
	      		// Special Params handling
	      		// if translated value is blank then we always copy across the original value
	      		$joomFishManager =  JoomFishManager::getInstance();
	      		if ($joomFishManager->getCfg('copyparams',1) &&  $translationContent->value==""){
	      			$translationContent->value = $field->originalValue;
	      		}
	      	?>
		    <tr class="<?php echo "row$k"; ?>">
		      <td colspan="3">
				<input type="hidden" name="origValue_<?php echo $field->Name;?>" value='<?php echo md5( $field->originalValue );?>' />
					    <textarea  name="origText_<?php echo $field->Name;?>" style="display:none"><?php echo $field->originalValue;?></textarea>
				<input type="hidden" name="id_<?php echo $field->Name;?>" value="<?php echo $translationContent->id;?>" />

			      <?php
			     	jimport('joomfish.translateparams.translateparams');
				 	$transparams = TranslateParams::getTranslateParams($elementTable->Name, $field->originalValue, $translationContent->value, $field->Name,$elementTable->Fields);
				  
					// TODO sort out default value for author in params when editing new translation
					$retval = $transparams->editTranslation();
					if ($retval){
						$editorFields[] = $retval;
					}
				?>
		      </td>
		    </tr>
	      	<?php
	      	}
					}
	      	?>
				<?php
				}
				$k=1-$k;
			}
				?>
		</table>
	  </td>
	  <td valign="top" width="30%">
		<?php
		jimport('joomla.html.pane');
		$tabs =  JPane::getInstance('tabs');
		echo $tabs->startPane("translation");
		echo $tabs->startPanel(JText::_( 'PUBLISHING' ),"ItemInfo-page");
	  ?>
  	<table width="100%" border="0" cellpadding="4" cellspacing="2" class="adminForm">
      <tr>
        <td width="34%"><strong><?php echo JText::_('TITLE_STATE');?>:</strong></td>
        <td width="50%"><?php echo $this->translationObject->state > 0 ? JText::_('STATE_OK') : ($this->translationObject->state < 0 ? JText::_('STATE_NOTEXISTING') : JText::_('STATE_CHANGED'));?></td>
      </tr>
      <tr>
        <td><strong><?php echo JText::_( 'LANGUAGE' );?>:</strong></td>
        <td><?php echo $this->langlist;?></td>
      </tr>
      <tr>
        <td><strong><?php echo JText::_('TITLE_PUBLISHED')?>:</strong></td>
        <td><input type="checkbox" name="published" value="1" <?php echo $this->translationObject->published&0x0001 ? 'checked="checked"' : ''; ?> /></td>
      </tr>
      <tr>
        <td><strong><?php echo JText::_('TITLE_DATECHANGED');?>:</strong></td>
	    <td><?php echo  $this->translationObject->lastchanged ? JHTML::_('date',  $this->translationObject->lastchanged, JText::_('DATE_FORMAT_LC2')):JText::_( 'NEW' );?></td>
      </tr>
	  </table>
	  <?php
	  echo $tabs->endPanel();
	  echo $tabs->endPane();
		?>
	  <input type="hidden" name="select_language_id" value="<?php echo $select_language_id;?>" />
	  <input type="hidden" name="reference_id" value="<?php echo $this->translationObject->id;?>" />
	  <input type="hidden" name="translation_id" value="<?php echo $this->translationObject->translation_id;?>" />
	  <input type="hidden" name="reference_table" value="<?php echo (isset($elementTable->name) ? $elementTable->name : '');?>" />
	  <input type="hidden" name="catid" value="<?php echo $this->catid;?>" />
	</td></tr>
	</table>
	<input type="hidden" name="option" value="com_joomfish" />
	<input type="hidden" name="task" value="translate.edit" />
	<input type="hidden" name="direct" value="<?php echo intval(JRequest::getVar("direct",0));?>" />

	<?php echo JHTML::_( 'form.token' ); ?>
</form>
<script type="text/javascript">
	Joomla.submitbutton = function(pressbutton) {
	var form = document.getElementsByName ('adminForm');
	<?php
	if( isset($editorFields) && is_array($editorFields) ) {
		foreach ($editorFields as $editor) {
			// Where editor[0] = your areaname and editor[1] = the field name
			echo $wysiwygeditor->save( $editor[1]);
		}
	}
	?>
	if (pressbutton == 'cancel') {
		Joomla.submitform( pressbutton );
		return;
	} else {
		Joomla.submitform( pressbutton );
	}
}
</script>
