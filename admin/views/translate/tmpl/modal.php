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
$function	= JRequest::getCmd('function', 'jfSelectArticle');


?>
<form action="<?php echo JRoute::_('index.php?option=com_joomfish&task=translate.originallist&view=translate&layout=modal&tmpl=component&table='.$this->table.'&function='.$function.'&'.JSession::getFormToken().'=1');?>" method="post" name="adminForm" id="adminForm">
  <table class="adminlist" cellspacing="1">
  <thead>
    <tr>
      <th class="title" width="20%" align="left"  nowrap="nowrap"><?php echo JText::_( 'TITLE' );?></th>
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
    <?php
    $k=0;
    $i=0;
	foreach ($this->rows as $row ) {
				?>
    <tr class="<?php echo "row$k"; ?>">
      <td>
      	<?php
      	$title = $row->title;
      	if(strlen($title) > 75) {
      		$title = '<span title="' .$title. '">';
      		$title .= substr($row->title,0, 75) .' ...';
      		$title .= '</span>';
      	}
      	?>
      	<a class="pointer" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $row->id; ?>', '<?php echo $this->escape(addslashes($row->title)); ?>');">
		<?php echo $this->escape($title); ?></a>
		</td>
 
	</tr>
		<?php
		$k = 1 - $k;
		$i++;
	}?>
	</tbody>
</table>

	<input type="hidden" name="option" value="com_joomfish" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
