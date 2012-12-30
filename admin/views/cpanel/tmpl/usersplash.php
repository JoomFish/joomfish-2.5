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

<div style="width: 782px; height: 565px; overflow: hidden;">
<div id="jfsplashcontainer" style="width:782px; height: 540px; margin: 0; padding: 0;">
	<div id="jfsplashcontent">
		<div id="jfsplashwelcome">
			<h1><?php echo JText::_('JF_SPLASH_WELCOME_TO')?></h1>
			<img id="jfsplash-logo" src="<?php echo JURI::root();?>administrator/components/com_joomfish/assets/images/joomfish_slogan.png" border="0" alt="<?php echo JText::_( 'JOOMFISH' ) .'-'. JText::_('JOOMFISH_HEADER');?>" />
			<img id="jfsplash-screens" src="<?php echo JURI::root();?>administrator/components/com_joomfish/assets/images/splash_screens.png" border="0" alt="<?php echo JText::_('JF_SPLASH_SCREENS');?>" />
			<div id="jfsplash-welcometext">
			<h2>Experience Joom!Fish in action!</h2>
			<p>
			If you don‘t know Joom!Fish this video can give you a first introduction how simple it is to localize your site 
			and what you can expect from our extension.
			</p>
			</div>
			<img id="btn-startvideo" src="<?php echo JURI::root();?>administrator/components/com_joomfish/assets/images/splash_btn_startvideo.png" border="0" alt="<?php echo JText::_( 'CLICK_TO_START_THE_VIDEO' );?>" />
		</div>
		<div id="jfsplashvideo" style="display: none">
			<embed id="jfsplashvideo-player" src="http://blip.tv/play/hoUejYl7p50i" type="application/x-shockwave-flash" width="550" height="500" autostart="true" allowscriptaccess="always" allowfullscreen="true"></embed>
		</div>
	</div>
	<div id="splashinfo">
		<div id="video-documentation">
			<img id="videopreview" src="<?php echo JURI::root();?>administrator/components/com_joomfish/assets/images/splash_videopreview.png" border="0" alt="<?php echo JText::_('JF_SPLASH_VIDEOPREVIEW');?>" />
			<h3><?php echo JText::_('JF_SPLASH_VIDEO_DOCUMENTATION');?></h3>
			<p><?php echo JText::_('JF_SPLASH_VIDEO_DOCUMENTATION_DESC');?><br />
			<a href="http://www.joomfish.net/en/documentation?utm_source=jf&utm_medium=splash&utm_campaign=help" target="_blank"><?php echo JText::_('JF_FIND_OUT_MORE')?></a></p>
		</div>
		<div id="support">
			<img id="videopreview" src="<?php echo JURI::root();?>administrator/components/com_joomfish/assets/images/splash_support.png" border="0" alt="<?php echo JText::_('JF_SPLASH_SUPPORT');?>" />
			<h3><?php echo JText::_('JF_SPLASH_SUPPORT_FORUM');?></h3>
			<p><?php echo JText::_('JF_SPLASH_SUPPORT_FORUM_DESC');?><br />
			<a href="http://www.joomfish.net/forum?utm_source=jf&utm_medium=splash&utm_campaign=help" target="_blank"><?php echo JText::_('JF_FIND_OUT_MORE')?></a></p>
		</div>
	</div>
</div>
<div style="clear:both;" ></div>
<div id="splashfooter" style="width: 780px; background-color: #c9c9c9; border-top: 1px solid #aaaaaa; margin-bottom: 2px;">
<form id="jfusersplashform" action="index.php" style="width: 100%; padding: 4px; background-color: #fff; border: none;" name="adminform" method="post">
	<input id="splash-usersplashstate" type="checkbox" value="1" <?php echo ($this->usersplash == 1 ? 'checked="checked"' : '');?> /><?php echo JText::_('SHOW_SPLASH_SCREEN')?>
	<input id="splash-btn-close" class="button" type="button" value="<?php echo JText::_( 'JFCLOSE' );?>" style="float: right; margin-right: 20px; cursor: pointer;"/>
	<input type="hidden" id="usersplashstate" name="params[usersplash]" value="<?php echo $this->usersplash;?>" />
	<input type="hidden" name="option" value="com_joomfish" />
	<input type="hidden" name="task" value="cpanel.saveconfig" />
	<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>
</div>
</div>