<?php 
/**
 * Joom!Fish - Multi Lingual extention and translation manager for Joomla!
 * Copyright (C) 2003 - 2012, Think Network GmbH, Munich
 *
 * All rights reserved. The Joom!Fish project is a set of extentions for
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307,USA.
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -----------------------------------------------------------------------------
 * $Id: information.php 226 2011-05-27 07:29:41Z alex $
 * @package joomfish
 * @subpackage Views
 *
*/
defined('_JEXEC') or die('Restricted access'); ?>
<div id="joomfish">
	<form action="index.php" method="post" name="adminForm">
	<table width="90%" border="0" cellpadding="2" cellspacing="2" class="adminform" >	<tr align="center" valign="middle">
		<tr align="center" valign="middle">
			<td align="left" valign="top" width="70%">
				<h2>PREAMBLE</h2>
		The JoomFish is an extention for the open source CMS Joomla!.<br />
		Joomla! is Copyright (C) 2005 Open Source Matters. All rights reserved.<br />
		license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php<br />
		Joomla! is free software. This version may have been modified pursuant<br />
		to the GNU General Public License, and as distributed it includes or<br />
		is derivative of works licensed under the GNU General Public License or<br />
		other free or open source software licenses.<br />
		See /COPYRIGHT.php for copyright notices and details.<br />
		&nbsp;<br />
		Within this license the "product" refers to the name "Joom!Fish" or "Mambel Fish".<br />
		Also the term "Joom!Fish - Joomla! Babel Fish" must not be used by any derived software.

					<?php
					switch ( $this->get('fileCode') ) {
						case "changelog":
					?>
					<h2>Changelog</h2>
					<?php
					echo nl2br(file_get_contents(JOOMFISH_ADMINPATH ."/documentation/CHANGELOG.php"));
					break;

						case "license":
					?>
					<h2>Think Network Open Source License</h2>
					<?php
					echo nl2br(file_get_contents(JOOMFISH_ADMINPATH ."/documentation/LICENSE.php"));
					break;

						case "readme":
						default:
					?>
					<h2>Read ME</h2>
					<?php
					echo nl2br(file_get_contents(JOOMFISH_ADMINPATH ."/documentation/ReadMe.php"));
					break;
					}
			?>
			</td>
			<td align="left" valign="top" nowrap>
				<?php $this->_sideMenu();?>
				<?php $this->_creditsCopyright(); ?>
			</td>
		</tr>
	</table>
<input type="hidden" name="option" value="com_joomfish" />
<input type="hidden" name="task" value="help.show" />
<input type="hidden" name="boxchecked" value="0" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>
