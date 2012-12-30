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
<form method="post" name="fileBrowserForm" class="fileBrowserForm" action="index.php">
	  <h1><?php echo $this->title;?></h1>
		<div id="fileBrowserFolderPath">
			<a id="fileBrowserFolderPathUp" href="<?php echo JRoute::_('index.php?option=com_joomfish&task=languages.fileBrowser&layout=filebrowser&folder='.$this->parent.'&type='.$this->type.'&tmpl=component&flagField='.$this->flagField);?>">
				<span><?php echo JText::_( 'JF_UP' ); ?></span>
			</a>
			<input type="text" value="<?php echo $this->path;?>" name="path" disabled="disabled" id="addressPath" maxlength="255" />
			<div class="clr"></div>
		</div>

	  <div id="fileBrowserFolders">

			<?php if(count($this->folders)): ?>
				<?php foreach ($this->folders as $folder) :	?>
					<div class="item">
						<a href="<?php echo JRoute::_('index.php?option=com_joomfish&task=languages.fileBrowser&layout=filebrowser&folder='.($this->path != '' ? $this->path .'/' : '').$folder.'&type='.$this->type.'&tmpl=component&flagField='.$this->flagField);?>">
							<img alt="<?php echo $folder;?>" src="<?php echo JURI::root()?>administrator/components/com_joomfish/assets/images/folder.png">
							<span><?php echo $folder;?></span>
						</a>
					</div>
				<?php endforeach;?>
			<?php endif;?>

			<?php if(count($this->files)): ?>
			<?php foreach($this->files as $file): ?>
				<div class="item">
					<a href="/<?php echo $this->path.'/'.$file;?>" class="flagFile" id="field-<?php echo $this->flagField;?>">
						<img alt="<?php echo $file;?>" src="<?php echo JURI::root().$this->path.'/'.$file;?>">
						<span><?php echo $file;?></span>
					</a>
				</div>
			<?php endforeach;?>
			<?php endif;?>

			<div class="clr"></div>
		</div>
</form>