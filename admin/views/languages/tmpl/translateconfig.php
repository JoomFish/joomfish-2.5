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

defined('_JEXEC') or die('Restricted access'); ?>
<form action="index.php" method="post" name="adminForm">
<input type="hidden" id="paramsfield" name="paramsfield" value="<?php echo $this->paramsField;?>" />
<input type="hidden" name="lang_id" value="<?php echo $this->lang_id;?>" />
<fieldset>
	<div style="float: right;">
		<button id="saveConfigTranslation" type="button"><?php echo JText::_( 'JTOOLBAR_CLOSE' );?></button>
	</div>
	<div class="configuration"><?php echo JText::_( 'TRANSLATE_SYSTEM_CONFIGURATION' );?></div>
</fieldset>
<?php
foreach ($this->jf_siteconfig as $groupname=>$group) {
 	?>
<fieldset class="adminform">
	<legend><?php echo JText::_( $groupname ); ?></legend>
	<table class="admintable" cellspacing="1" style="width:100%">
		<tbody>
		<?php
		foreach ($group as $field=>$data) {
			?>
			<tr>
				<td valign="top" class="key" style="font-weight:bold;width:100px;" rowspan="2">
					<span class="editlinktip hasTip" title="<?php echo JText::_( $data[0] )."::".JText::_( $data[1] ); ?>">
						<?php echo JText::_(  $data[0]); ?>
					</span>
				</td>
				<td valign="top">
				<?php echo $this->jconf->$field; ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php 
					if ($data[2]=="text") { ?>
					<input class="text_area translation" type="text" name="trans-<?php echo $field;?>" value="<?php echo  $this->translations->get($field,""); ?>" style="width:100%;" />
					<?php } else {?>
					<textarea class="text_area translation" rows="2" name="trans-<?php echo $field;?>" style="width:100%;"><?php echo $this->translations->get($field,""); ?></textarea>
					<?php }?>
					
				</td>
			</tr>
			<?php
		}
		?>
		</tbody>	
	</table>
</fieldset>
 	<?php
 } 
?>

<fieldset class="adminform"   >
	<legend><?php echo JText::_( 'JOOMFISH_CONFIGURATION' ); ?></legend>
	<table class="admintable" cellspacing="1" style="width:100%;">
		<tbody>
			<tr>
				<td valign="top" class="key" style="font-weight:bold;width:100px;" rowspan="2">
					<span class="editlinktip hasTip" title="<?php echo JText::_( 'PLACEHOLDER' )."::".JText::_( "TRANS_DEFAULT_HELP"); ?>">
						<?php echo JText::_( 'PLACEHOLDER' ); ?>
					</span>
				</td>
				<td valign="top">
				<?php echo $this->defaulttext; ?>
				</td>
			</tr>
			<tr>
				<td>
					<textarea class="text_area translation" rows="2" name="trans-defaulttext" style="width:100%;"><?php echo $this->trans_defaulttext; ?></textarea>				
				</td>
			</tr>
		</tbody>	
	</table>
</fieldset>

<input type="hidden" name="option" value="com_joomfish" />
<input type="hidden" name="task" value="languages.translateConfig" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>
