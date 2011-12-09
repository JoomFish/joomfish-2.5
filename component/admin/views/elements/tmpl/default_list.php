<?php
/**
 * Joom!Fish - Multi Lingual extention and translation manager for Joomla!
 * Copyright (C) 2003 - 2011, Think Network GmbH, Munich
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
 * $Id: default_list.php 226 2011-05-27 07:29:41Z alex $
 * @package joomfish
 * @subpackage Views
 *
*/
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<form action="index.php" method="post" name="adminForm">
	<table class="adminlist" cellspacing="1">
		<thead>
		    <tr>
		      <th width="20" nowrap>&nbsp;</th>
		      <th class="title" width="35%" align="left"><?php echo JText::_('TITLE_NAME');?></th>
		      <th width="15%" align="left"><?php echo JText::_('TITLE_AUTHOR');?></th>
		      <th width="15%" nowrap="nowrap" align="left"><?php echo JText::_('TITLE_VERSION');?></th>
		      <th nowrap="nowrap" align="left"><?php echo JText::_('TITLE_DESCRIPTION');?></th>
		    </tr>
	    </thead>
		<tfoot>
		    <tr>
		      <td align="center" colspan="5">
				<?php echo $this->pageNav->getListFooter(); ?>
			  </td>
		    </tr>
	    </tfoot>
	    <tbody>
		    <?php
		    $elements = $this->joomfishManager->getContentElements();
		    $k=0;
		    $i=0;
		    $element_values = array_values($elements);
		    for ( $i=$this->pageNav->limitstart; $i<$this->pageNav->limitstart + $this->pageNav->limit && $i<$this->pageNav->total; $i++ ) {
		    	$element = $element_values[$i];
		    	$key = $element->referenceInformation['tablename'];
						?>
		    <tr class="<?php echo "row$k"; ?>">
		      <td width="20">
		        <?php		if ($element->checked_out && $element->checked_out != $user->id) { ?>
		        &nbsp;
		        <?php		} else { ?>
		        <input type="radio" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $key; ?>" onclick="isChecked(this.checked);" />
		        <?php		} ?>
		      </td>
		      <td>
		      	<a href="#detail" onclick="return listItemTask('cb<?php echo $i;?>','elements.detail')"><?php echo $element->Name; ?></a>
					</td>
		      <td><?php echo $element->Author ? $element->Author : '&nbsp;'; ?></td>
		      <td><?php echo $element->Version ? $element->Version : '&nbsp;'; ?></td>
		      <td><?php echo $element->Description ? $element->Description : '&nbsp;'; ?></td>
						<?php
						$k = 1 - $k;
		    }
				?>
			</tr>
		</tbody>
	</table>
	<input type="hidden" name="option" value="com_joomfish" />
	<input type="hidden" name="task" value="elements.show" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
