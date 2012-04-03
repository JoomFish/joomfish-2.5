<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Platform.
 * Provides a list of content languages
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @see         JFormFieldLanguage for a select list of application languages.
 * @since       11.1
 */
class JFormFieldContentLanguage extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'ContentLanguage';

	/**
	 * Method to get the field options for content languages.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{	
		$basicoptions = JHtml::_('contentlanguage.existing');
		
		// set default
		if (!isset($this->value) || empty ($this->value) || $this->value == '*') {
			$jfManager = JoomFishManager::getInstance();
			$this->value = $jfManager->getDefaultLanguage();
		}
		
		return $basicoptions;
	}
}
