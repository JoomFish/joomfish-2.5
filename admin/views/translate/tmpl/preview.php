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
// JURI::base() returns admin path so go up one level
$live_site = JURI::base()."..";
$base = '<base href="'.$live_site.'/index.html" />';
JFactory::getApplication()->addCustomHeadTag( $base );

$editor		= JFactory::getEditor();

?>
	<script>

	var form = window.parent.document.adminForm	;
	var title = form.refField_title.value;
	var title_orig = form.origText_title.value;

	var alltext="";
	var alltext_orig = window.parent.document.getElementById("original_value_introtext").innerHTML;

	if (window.parent.getRefField){
		alltext = window.parent.getRefField("introtext");
		if (window.parent.getRefField("fulltext")) {
			alltext += window.parent.getRefField("fulltext");
		}
		else if (form.refField_fulltext) {
			alltext += form.refField_fulltext.value;
		}
	}
	else {
		alltext = window.top.<?php echo $editor->getContent('refField_introtext') ?>;
		alltext += window.top.<?php echo $editor->getContent('refField_fulltext') ?>;
	}
	alltext_orig += window.parent.document.getElementById("original_value_fulltext").innerHTML;

	</script>
<table align="center" width="100%" cellspacing="2" cellpadding="2" border="0">
	<tr>
		<th ><h2><?php echo JText::_( 'ORIGINAL' );?></h2></th>
		<th ><h2><?php echo JText::_( 'TRANSLATION' );?></h2></th>
	</tr>
	<tr>
		<td class="contentheading" style="width:50%!important"><script>document.write(title_orig);</script></td>
		<td class="contentheading" ><script>document.write(title);</script></td>
	</tr>
	<tr>
		<script>document.write("<td valign=\"top\" >" + alltext_orig + "</td>");</script>
		<script>document.write("<td valign=\"top\" >" + alltext + "</td>");</script>
	</tr>
	<tr>
		<td align="center" colspan="2"><a href="javascript:;" onClick="window.print(); return false"><?php echo JText::_( 'PRINT' );?></a></td>
	</tr>
</table>
