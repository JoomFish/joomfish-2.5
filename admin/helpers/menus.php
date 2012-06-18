<?php

// No direct access
defined('_JEXEC') or die;

// Dummy file  used to load menus helper from com_menu
if (!class_exists('MenusHelper')) {
		JLoader::register('MenusHelper', JPATH_ADMINISTRATOR.'/components/com_menus/helpers/menus.php', true);	
}