<?php

// No direct access
defined('_JEXEC') or die;

// Dummy file  used to load menus helper from com_modules
if (!class_exists('ModulesHelper')) {
	JLoader::register('ModulesHelper', JPATH_ADMINISTRATOR.'/components/com_modules/helpers/modules.php', true);
}