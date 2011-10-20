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
 * $Id: postinstall.php 226 2011-05-27 07:29:41Z alex $
 * @package joomfish
 * @subpackage Views
 *
*/
defined('_JEXEC') or die('Restricted access'); ?>
<div id="joomfish">
	<table width="90%" border="0" cellpadding="2" cellspacing="2" class="adminform" >	<tr align="center" valign="middle">
     <td align="left" valign="top">
       <h2>Welcome to the Joom!Fish</h2>
       <p>
       The Joom!Fish component supports you with design and creation of multilingual websites.<br />
       This topic itself isn't that easy and so the handling of this component isn't easy.</p>
       Please make sure you read the following instructions carefully and try out a first example.
       After that you will find additional information, tutorials and support on the <a href="http://www.joomfish.net" target="_blank">Joom!Fish website</a>.

       <p>This version of the Joom!Fish is only working together with the Joomla Version 1.5.6 or above. If
	   you have a different version please refer to the project site to get a suitable version.</p>

	   <h3>1. Step: Language installation</h3>
	   <p>
	   The first step is the installation of standard Joomla! language files.
	   Those files can be found on the <a href="http://extensions.joomla.org" target="_blank">Joomla! extension website</a>.<br />
	   Please install all languages (there is no limit) you like to use within your site - using
	   the <a href="<?php echo JURI::root(); ?>administrator/index.php?option=com_installer">Extensions -> Install/Unininstall</a> function.</p>

	   <h3>2. Step: Language configuration</h3>
	   <p>After you have done the language installation it is needed to activate the languages and
	   give them a "natural" name and some other configuration values. This is done with the language configuration of the Joom!Fish
	   (<a href="<?php echo JURI::root(); ?>administrator/index.php?option=com_joomfish&task=languages.show">Components -> Joom!Fish -> Languanges</a>).<br />
	   &nbsp;<br />
	   <b>First check:</b> When you change the languages within the frontend then
	   all static text should change.</p>

	   <h2>How to translate content?</h2>
	   <p>After you are sure that the basic configuration of your site works for the static text translations let's try to
	   translate dynamic content that is included in the database.</p>

	   <h3>3. Step: Translation of information in the database</h3>
	   <p>For the first simple test we try to translate the menu names in your website.<br />
	   For this please go to the <a href="<?php echo JURI::root(); ?>administrator/index.php?option=com_joomfish">control panel</a> and select the function Translation.<br />
	   In this translation overview first select the language into which you like to translate. Then select the "content element"
	   Menus. After you did that you get a list of all menus existing in the database.<br />
	   With selection the menu type "mainmenu" you reduce the list to all menu items used within this menu definition. Please translate
	   all the menu items by clicking on the title name. It is important that the translation is published (last column).<br />
	   &nbsp;<br />
	   <b>Second check:</b> When you change the languages within the frontend then the menu should show your translated menu names.</p>

   	   <h3>4. Step: Translation of additional information</h3>
   	   <p>For all other dynamic content within your database the translation process is exactly as described in step 3.
   	   For 3rd party Joomla! extensions you might need to <a href="<?php echo JURI::root(); ?>administrator/index.php?option=com_joomfish&task=elements.show">install additional content element files</a>
   	   with the configuration.</p>

	   <h2>Joom!Fish development status</h2>
	   <p>This release is a stable release and is the first version for the Joomla! 1.5.x code base.<br />
	   The development of Joom!Fish will continue with the next version 2.1, which is expected to add more functionality and to facilitate the translation process.
	   Check out our website to get the latest news.
	   </p>

	   <p>If you have any questions please use our website forum and have fun using the Joom!Fish<br>
		 &nbsp;<br />
		 Check out also the <a href="http://www.joomfish.net/en/the-club" target="_blank">Joom!Fish extensions club</a>, where you could find various of additional extensions that give additional power to your web site.
		 <br>
		 &nbsp;<br />
		Your Joom!Fish development team</p>
	   </td>
		<td align="left" valign="top" nowrap>
			<?php $this->_sideMenu();?>
			<?php $this->_creditsCopyright(); ?>
		</td>
	</tr>
  </table>
</div>
