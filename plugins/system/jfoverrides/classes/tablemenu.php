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
	
	public function store($updateNulls = false)
	{
		$db = JFactory::getDBO();
		// Verify that the alias is unique
		$table = JTable::getInstance('Menu', 'JTable');
		if ($table->load(array('alias' => $this->alias, 'parent_id' => $this->parent_id, 'client_id' => $this->client_id, 'language' => $this->language))
				&& ($table->id != $this->id || $this->id == 0))
		{
			if ($this->menutype == $table->menutype)
			{
				$this->setError(JText::_('JLIB_DATABASE_ERROR_MENU_UNIQUE_ALIAS'));
			}
			else
			{
				$this->setError(JText::_('JLIB_DATABASE_ERROR_MENU_UNIQUE_ALIAS_ROOT'));
			}
			return false;
		}
		// Verify that the home page for this language is unique
		if ($this->home == '1')
		{
			$table = JTable::getInstance('Menu', 'JTable');
			if ($table->load(array('home' => '1', 'language' => $this->language)))
			{
				if ($table->checked_out && $table->checked_out != $this->checked_out)
				{
					$this->setError(JText::_('JLIB_DATABASE_ERROR_MENU_DEFAULT_CHECKIN_USER_MISMATCH'));
					return false;
				}
				$table->home = 0;
				$table->checked_out = 0;
				$table->checked_out_time = $db->getNullDate();
				$table->store();
			}
			// Verify that the home page for this menu is unique.
			if ($table->load(array('home' => '1', 'menutype' => $this->menutype, 'language' => $this->language)) && ($table->id != $this->id || $this->id == 0))
			{
				$this->setError(JText::_('JLIB_DATABASE_ERROR_MENU_HOME_NOT_UNIQUE_IN_MENU'));
				return false;
			}
		}
		if (!JTableNested::store($updateNulls))
		{
			return false;
		}
		// Get the new path in case the node was moved
		$pathNodes = $this->getPath();
		$segments = array();
		foreach ($pathNodes as $node)
		{
			// Don't include root in path
			if ($node->alias != 'root')
			{
				$segments[] = $node->alias;
			}
		}
		$newPath = trim(implode('/', $segments), ' /\\');
		// Use new path for partial rebuild of table
		// Rebuild will return positive integer on success, false on failure
		return ($this->rebuild($this->{$this->_tbl_key}, $this->lft, $this->level, $newPath) > 0);
	}

}
