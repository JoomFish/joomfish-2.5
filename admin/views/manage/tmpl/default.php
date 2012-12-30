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
<script language="javascript" type="text/javascript">
  function updateResultDiv( resultInfo, type ) {
	resultDiv = document.getElementById("manage_results");
	if( type == 'div' ) {
		resultDiv.innerHTML = resultInfo.innerHTML;
	} else {
		resultDiv.innerHTML = resultInfo;
	}
  }

  function executeCopyOriginal( toLanguage, confirmCheck, copyCat  ) {
  		if( toLanguage == null || toLanguage.value == -1 ) return;

		var link = 'index.php?option=com_joomfish&task=manage.copy&type=original_language&phase=2&tmpl=component';
  		if( confirmCheck.checked == true ) {
			if( !window.confirm( '<?php echo JText::_('CONFIRM_COPY_TO', true);?>' ) ) return;
			link += '&overwrite=1';
  		} else {
  			link += '&overwrite=0';
  		}
		link += '&language_id=' +toLanguage.value;

		// adding all requested catids to one string
		var catString = '';
		for(var i=0; i<copyCat.length; i++) {
			if( copyCat[i].value != null && copyCat[i].checked == true ) {
				if(catString != '') {
					catString += ',';
				}
				catString += copyCat[i].value;
			}
		}
		link += '&state_catid=' + catString;

		ajaxFrame = document.getElementById("ajaxFrame");
		ajaxFrame.contentDocument.location.href = link;
  }
</script>
<form action="index.php" method="post" name="adminForm">
<table class="adminform">
	<tr>
		<td width="45%" valign="top">
			<div id="cpanel">
				<?php
				$link = 'index.php?option=com_joomfish&task=manage.copy&type=original_language&tmpl=component';
				$this->_quickiconButton( $link, 'icon-48-manage-translations.png', JText::_( 'COPY_ORIGINAL_TO_LANGUAGE' ), '/administrator/components/com_joomfish/assets/images/', 'ajaxFrame', "updateResultDiv('" .JText::_('Processing', 'text'). "');" );
/*
				echo '<div style="clear: both;" />';
				$link = 'index.php?option=com_joomfish&amp;task=copy&amp;act=manage&amp;type=translation_language&tmpl=component';
				HTML_joomfish::_quickiconButton( $link, 'dbrestore.png', JText::_( 'COPY_TRANSLATION_TO_LANGAGE' ), '/administrator/images/', 'ajaxFrame', "updateResultDiv('" .JText::_('Processing', 'text'). "');" );
				$link = 'index.php?option=com_joomfish&amp;task=update&amp;act=manage&amp;type=original_value&tmpl=component';
				HTML_joomfish::_quickiconButton( $link, 'query.png', JText::_( 'UPDATE_ORIGINAL_VALUES' ), '/administrator/images/', 'ajaxFrame', "updateResultDiv('" .JText::_( 'UPDATE_ORIGINAL_VALUES' ). "', 'text');" );
*/
				?>
			</div>
		</td>
		<td width="55%" valign="top">
			<div style="width: 98%; height: 100%;">
				<h3><?php echo JText::_( 'MANAGEMENT_INFO' );?></h3>
				<div id="manage_results"><?php echo JText::_('MANAGEMENT_INTRO');?></div>
			</div>
			<iframe style="display: none;" id="ajaxFrame" name="ajaxFrame" ></iframe>
		</td>
	</tr>
</table>

<input type="hidden" name="option" value="com_joomfish" />
<input type="hidden" name="task" value="cpanel.show" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>
