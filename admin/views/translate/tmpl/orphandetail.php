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
	 * show the Orphan translations
	 *
	 * @param array $rows
	 * @param array $catid	category id's
	 */
$rows = $this->rows;
global  $act,  $option;
$user = JFactory::getUser();
$db = JFactory::getDBO();

$this->_JoomlaHeader("Orphan Detail");
         ?>
  <input type="hidden" name="cid[]" value="<?php echo $rows[0]->lang_id."|".$rows[0]->reference_id."|".$rows[0]->language_id; ?>"/>
   <input type="hidden" name="catid" value="<?php echo $catid;?>" />
   <table width="100%" border="1" cellpadding="4" cellspacing="2" class="adminForm">
	<tr align="center" valign="middle">
	      <th width="10%" align="left" valign="top"><?php echo JText::_( 'DBFIELDLABEL' );?></th>
	      <th width="12%" align="left" valign="top"><?php echo JText::_( 'ORIGINAL' );?></th>
	      <th width="78%" align="left" valign="top"><?php echo JText::_( 'TRANSLATION' );?></th>
        </tr>
        <?php
        $style1="style='background-color:rgb(241,243,245)'";
        $style2="style='background-color:rgb(255,228,196)'";
        $style=$style1;
        ?>
		<tr align="center" valign="middle" <?php echo $style; ?>>
	      <td align="left" valign="top"><?php echo "Debug Info";?></td>
	      <td colspan="2" align="left" valign="top"><?php echo "Original Table:<b>".$rows[0]->reference_table."</b> === Orginal Id: <b>".$rows[0]->reference_id."</b>";?></td>
        </tr>
        <?php
        foreach ($rows as $row) {
        	$style=$style==$style1?$style2:$style1;
		?>
        <tr align="center" valign="middle" <?php echo $style; ?>>
	      <td align="left" valign="top"><?php echo $row->reference_field;?></td>
	      <td align="left" valign="top"><?php echo is_null($row->original_text)?$row->original_value:$row->original_text;?></td>
	      <td align="left" valign="top"><?php echo $row->value;?></td>
	    </tr>
	    <?php
        }
	    ?>
   </table>

<?php
$this->_JoomlaFooter('translate.orphandetail', $act, $option);
