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
?>
<form action="index.php" method="post" name="adminForm">
	<table>
		<tr>
			<td width="100%">
			</td>
			<td nowrap="nowrap">
				<?php echo JText::_( 'LANGUAGES' ). ':<br/>' .$this->langlist;?>
			</td>
			<td nowrap="nowrap">
				<?php echo JText::_( 'CONTENT_ELEMENTS' ). ':<br/>' .$this->clist; ?>
			</td>
		</tr>
	</table>
  <table class="adminlist" cellspacing="1">
  <thead>
    <tr>
      <th width="20"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->rows); ?>);" /></th>
      <th class="title" width="20%" align="left"  nowrap="nowrap"><?php echo JText::_( 'TITLE' );?></th>
      <th width="10%" align="left" nowrap="nowrap"><?php echo JText::_( 'LANGUAGE' );?></th>
      <th width="20%" align="left" nowrap="nowrap"><?php echo JText::_('TITLE_TRANSLATION');?></th>
      <th width="15%" align="left" nowrap="nowrap"><?php echo JText::_('TITLE_DATECHANGED');?></th>
      <th width="15%" nowrap="nowrap" align="center"><?php echo JText::_('TITLE_STATE');?></th>
      <th align="center" nowrap="nowrap"><?php echo JText::_('TITLE_PUBLISHED');?></th>
    </tr>
    </thead>
    <tfoot>
        <tr>
    	  <td align="center" colspan="7">
			<?php echo $this->pageNav->getListFooter(); ?>
		  </td>
		</tr>
    </tfoot>
    <tbody>
		<tr><td colspan="8"><p><?php echo JText::_('NOELEMENT_SELECTED');?></p></td></tr>
	</tbody>
	</table>

	<input type="hidden" name="option" value="com_joomfish" />
	<input type="hidden" name="task" value="translate.show" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
