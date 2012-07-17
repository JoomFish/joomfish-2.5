<?php

defined('JPATH_PLATFORM') or die;
require_once 'originals/tablemenuoriginal.php';

/**
 * Menu table
 *
 * @package     Joomla.Platform
 * @subpackage  Table
 * @since       11.1
 */
class JTableMenu extends JTableMenuOriginal
{
	/**
	 * Constructor
	 *
	 * @param   JDatabase  &$db  A database connector object
	 *
	 * @since   11.1
	 */
	public function __construct(&$db)
	{
		parent::__construct($db);	
	}
	/**
	 * Overloaded bind function
	 *
	 * @param   array  $array   Named array
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  mixed  Null if operation was satisfactory, otherwise returns an error
	 *
	 * @see     JTable::bind
	 * @since   11.1
	 */
	public function bind($array, $ignore = '')
	{
		// Verify that the default home menu is not unset
		if ($this->home == '1' && ($this->language == '*' || $this->language == JoomFishManager::getDefaultLanguage()) && ($array['home'] == '0'))
		{
			$this->setError(JText::_('JLIB_DATABASE_ERROR_MENU_CANNOT_UNSET_DEFAULT_DEFAULT'));
			return false;
		}
		//Verify that the default home menu set to "all" languages" is not unset
		if ($this->home == '1' && ($this->language == '*' || $this->language == JoomFishManager::getDefaultLanguage()) && ($array['language'] != '*') && ($array['language'] != JoomFishManager::getDefaultLanguage()) )
		{
			$this->setError(JText::_('JLIB_DATABASE_ERROR_MENU_CANNOT_UNSET_DEFAULT'));
			return false;
		}

		// Verify that the default home menu is not unpublished
		if ($this->home == '1' && ($this->language == '*' || $this->language == JoomFishManager::getDefaultLanguage()) && $array['published'] != '1')
		{
			$this->setError(JText::_('JLIB_DATABASE_ERROR_MENU_UNPUBLISH_DEFAULT_HOME'));
			return false;
		}

		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}

		return JTableNested::bind($array, $ignore);
	}

}
