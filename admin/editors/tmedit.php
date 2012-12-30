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
<script language="javascript" type="text/javascript">
	function copyToClipboard(value, action) {
		try {
			if (document.getElementById) {
				innerHTML="";
				if (action=="copy") {
					srcEl = document.getElementById("original_value_"+value);
					innerHTML = srcEl.innerHTML;
				}
				/*
				textArea = document.getElementById("refField_"+value);
				textArea.value=innerHTML;
				alert(textArea.value);
				div = textArea.previousSibling;
				iframe= div.getElementsByTagName('iframe')[0];
				doc = iframe.contentWindow.document;
				alert(doc.body.innerHTML);
				*/
				editorobj = eval("editoreditor_"+value);
				if ( typeof(editorobj)=="object") {
					editorobj.setHTML( innerHTML);
					//editorobj.insertHTML( innerHTML);
					//alert(doc.body.innerHTML);
					//alert(editorobj.getHTML());
				}
				else {
					if (window.clipboardData){
						window.clipboardData.setData("Text",innerHTML);
						alert("<?php echo preg_replace( '#<br\s*/>#', '\n', JText::_('CLIPBOARD_COPIED') );?>");
					}
					else {
						srcEl = document.getElementById("text_origText_"+value);
    	       			srcEl.value = innerHTML;
        	   			srcEl.select();
						alert("<?php echo preg_replace( '#<br\s*/>#', '\n', JText::_('CLIPBOARD_COPY'));?>");
					}
				}
			}
		}
		catch(e){
			alert("<?php echo preg_replace( '#<br\s*/>#', '\n', JText::_('CLIPBOARD_NOSUPPORT'));?>");
		}
	}
</script>
