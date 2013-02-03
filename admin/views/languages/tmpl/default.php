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
defined('_JEXEC') or die('Restricted access'); 
JHtml::_('behavior.mootools');
JHtml::_('behavior.modal');
?>
<script type="text/javascript">
	//<![CDATA[
function showImageBrowser(fieldNum){
	var imgField = document.getElementById('flagValue'+fieldNum);
	SqueezeBox.initialize();
	SqueezeBox.setContent( 'iframe', '<?php echo JURI::base()?>index.php?option=com_joomfish&task=languages.fileBrowser&layout=filebrowser&type=image&tmpl=component&current='+imgField.value+'&flagField='+fieldNum
	);
}

function showConfigEditor(fieldId, lang_id) {
	var field = document.getElementById(fieldId);
	
	SqueezeBox.initialize();
	SqueezeBox.setContent( 'iframe', '<?php echo JURI::base()?>index.php?option=com_joomfish&task=languages.translateconfig&layout=translateconfig&tmpl=component&paramsField='+fieldId+'&lang_id='+lang_id+'&current='+encodeURI(field.value)
 	);
}
//]]>
</script>
<div id="jfToggleSidebarContainer">
	<a href="#" id="jfToggleSidebar"><?php echo JText::_( 'JF_TOGGLE_SIDEBAR' ); ?></a>
</div>
<form action="index.php" method="post" name="adminForm">
<table cellspacing="0" cellpadding="0" border="0" class="jfAdminContainer">
	<tbody>
		<tr>
		<td>
			<div id="editcell">
				<table class="adminlist jfMaxInput jfLangList">
				<thead>
					<tr>
						<th width="20">
						</th>
						<th class="title">
							<?php echo $this->fetchTooltip('JF_LANGUAGE_TITLE', 'LANGUAGE_TITLE_HELP'); ?>
						</th>
						<th class="title">
							<?php echo $this->fetchTooltip('JF_LANGUAGE_TITLE_NATIVE', 'JF_LANGUAGE_TITLE_NATIVE_HELP'); ?>
						</th>
						<th class="title" nowrap="nowrap">
							<?php echo $this->fetchTooltip('JF_DEFAULT_LANGUAGE', JText::_('JF_DEFAULT_LANGUAGE_HELP')); ?>
						</th>
						<th class="title" nowrap="nowrap">
							<?php echo $this->fetchTooltip('TITLE_JOOMLA', JText::_('JOOMLACODE_HELP')); ?>
						</th>
						<th class="title" nowrap="nowrap">
							<?php echo $this->fetchTooltip('JF_JOOMLA_FRONTEND_TRANSLATION', JText::_('JF_JOOMLA_FRONTEND_TRANSLATION_HELP')); ?>
						</th>
						<th class="title" nowrap="nowrap">
							<?php echo $this->fetchTooltip('TITLE_STATUS', JText::_('STATUS_HELP')); ?>
						</th>
						<th class="title" nowrap="nowrap">
							<?php echo $this->fetchTooltip('TITLE_SHORTCODE', JText::_('SHORTCODE_HELP')); ?>
						</th>
						<th class="title">
							<?php echo $this->fetchTooltip('TITLE_FALLBACK', JText::_('FALLBACK_HELP')); ?>
						</th>
						<th class="title">
							<?php echo $this->fetchTooltip('TITLE_IMAGE', JText::_('IMAGES_DIR_HELP')); ?>
						</th>
						<th class="title" nowrap="nowrap" width="10">
							<?php echo JHTML::_('grid.sort',  'Order', 'l.ordering', $this->lists['order_Dir'], $this->lists['order'] ); ?>
						</th>
						<th class="title">
							<?php echo $this->fetchTooltip('TITLE_CONFIG', JText::_('CONFIG_HELP')); ?>
						</th>
					</tr>
				</thead>
				<tfoot></tfoot>
				<tbody>
					<?php
					$k=0;
					$i=0;
					reset($this->items);
					$model = $this->getModel('languages');
					foreach ($this->items as $language ) { ?>
					<tr class="<?php echo 'row' . $k; ?>">
				      	<td align="center">
			      			<input type="hidden" name="cid[]" value="<?php echo $language->lang_id; ?>" />
							<?php 
							if ( $this->defaultLanguage != $language->lang_code ) {?>
			      			<input type="checkbox" name="checkboxid[]" id="cb<?php echo $language->lang_id; ?>" value="<?php echo $language->lang_id; ?>" onclick="isChecked(this.checked);" />
				      		<?php }?>
				      	</td>
						<td><input type="text" name="title[]" value="<?php echo $language->title; ?>" maxlength="50" /></td>
						<td><input type="text" name="title_native[]" value="<?php echo $language->title_native; ?>" maxlength="50" /></td>
						<td align="center">
							<?php if ($language->lang_code == $this->defaultLanguage) :?>
								<div class="icon-16-default jfIconContainer" />
							<?php else :?>
								<div class="jfIconContainer" />
							<?php endif;?>
						</td>
						<td><input type="text" name="lang_code[]" value="<?php echo $language->lang_code; ?>" maxlength="5" /></td>
						<td align="center">
							<?php if ($language->hasFrontendTranslation()) :?>
								<?php echo JHTML::_('image','admin/tick.png', JText::_('JF_AVAILABLE'),array('title'=>JText::_('JF_AVAILABLE_FRONTEND_LANGUAGE')),true)?>
							<?php else :?>
								<a href="<?php echo JURI::base();?>/index.php?option=com_installer&view=languages&filter_search=<?php echo $language->title; ?>">
									<?php echo JHTML::_('image','admin/publish_x.png', JText::_('JF_NOT_AVAILABLE'),array('title'=>JText::_('JF_NOT_AVAILABLE_FRONTEND_LANGUAGE')),true)?>
								</a>
							<?php endif;?>
						</td>
						<td align="center">
							<?php if($language->lang_id == -1) :?>
								<?php echo JHTML::_('image','admin/publish_y.png', JText::_('JF_NOT_SAVED'),array('title'=>JText::_('JF_NOT_SAVED_LANGUAGE')),true); ?>
							<?php else: ?>
								<input type="checkbox" name="published[]"<?php echo $language->published==1 ? ' checked="checked"' : ''; ?> value="<?php echo $language->lang_id; ?>" />
							<?php endif;?>
						</td>
						<td><input type="text" name="sef[]" value="<?php echo $language->sef; ?>" maxlength="10" /></td>
						<td><input type="text" name="fallbackCode[]" value="<?php echo $language->fallback_code; ?>" maxlength="20" /></td>
						<td nowrap="nowrap">
				      		<?php
							$src = JoomfishExtensionHelper::getLanguageImageSource($language);
							?>
							<img src="<?php echo $src != '' ? JURI::root().$src : JURI::root().'images/blank.png';?>" alt="<?php echo html_entity_decode( $src );?>" title="<?php echo $language->title?>" class="flag" id="flagImage<?php echo $i;?>" />
				      		<input id="flagValue<?php echo $i;?>" type="text" name="image[]" value="<?php echo $src ?>" style="width: 100px;" readonly="readonly" />
				      		<input id="browseLanguageImage" class="button" type="button" value="<?php echo JText::_( 'JF_BROWSE' );?>" onClick="showImageBrowser('<?php echo $i;?>');"/>
						</td>
				      <td><input type="text" name="order[]" value="<?php echo $language->ordering; ?>" maxlength="5" /></td>
				      <td align="center"><input id="paramsValue<?php echo $i;?>" type="hidden" name="params[]" value="<?php echo $language->params; ?>" />
				      	<a href="#" onClick="showConfigEditor('paramsValue<?php echo $i;?>', '<?php echo $language->lang_id;?>');"><?php echo JHTML::_('image.administrator', 'menu/icon-16-config.png', '/images/', null, null, JText::_( 'JF_EDIT' ));?></a>
					  </td>
					      <?php
					      $k = 1 - $k;
					      $i++;
					}?>
					</tr>
				</tbody>
				</table>
			
			<input type="hidden" name="option" value="com_joomfish" />
			<input type="hidden" name="task" value="languages.show" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
			<?php echo JHTML::_( 'form.token' ); ?>
			</div>
		</td>
		<td id="adminJFSidebar" style="display: none;">
			
			<table class="jfSidebarInformation">
				<thead><th colspan="2"><?php echo JText::_('JF_CONFIG_INFORMATION')?></th>
			    <tr>
			      <td width="45%"><strong><?php echo $this->fetchTooltip('System default language', JText::_('SYSTEM_DEFAULT_LANGUAGE_HELP')); ?></strong></td>
			      <td nowrap="nowrap"><?php echo $this->defaultLanguage; ?></td>
			    </tr>
			    <tr>
			      <td><strong><?php echo $this->fetchTooltip('Overwrite global config values', JText::_('OVERWRITE_GLOBAL_CONFIG_HELP')); ?></strong></td>
			      <td nowrap="nowrap"><?php echo $this->overwriteGlobalConfig ? JText::_('JYES') : JText::_('JNO'); ?></td>
			    </tr>
			    <tr>
			      <td><strong><?php echo $this->fetchTooltip('Flags directory', JText::_('FLAGS_DIRECTORY_HELP')); ?></strong></td>
			      <td nowrap="nowrap"><?php echo $this->directory_flags; ?></td>
			    </tr>
			</table>
		</td>
		</tr>
	</tbody>
</table>
</form>
