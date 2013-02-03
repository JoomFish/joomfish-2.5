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

JLoader::import( 'views.default.view',JOOMFISH_ADMINPATH);

/**
 * HTML View class for the WebLinks component
 *
 * @static
 * @package		Joomla
 * @subpackage	Weblinks
 * @since 1.0
 */
class StatisticsViewStatistics extends JoomfishViewDefault
{
	public function display($tpl = null)
	{
		JHTML::stylesheet( 'joomfish.css', 'administrator/components/com_joomfish/assets/css/' );

		$document = JFactory::getDocument();
		$document->setTitle(JText::_('JOOMFISH_TITLE') . ' :: ' .JText::_('TITLE_Statistics'));

		// Set toolbar items for the page
		JToolBarHelper::title(JText::_( 'TITLE_Statistics' ), 'statistics' );
		JToolBarHelper::custom( 'cpanel.show', 'joomfish', 'joomfish', 'COM_JOOMFISH_CONTROL_PANEL' , false );
		JToolBarHelper::divider();
		JToolBarHelper::help( 'screen.statistics', true);

		JSubMenuHelper::addEntry(JText::_( 'COM_JOOMFISH_CONTROL_PANEL' ), 'index.php?option=com_joomfish');
		JSubMenuHelper::addEntry(JText::_( 'TRANSLATION' ), 'index.php?option=com_joomfish&amp;task=translate.overview');
		if (JOOMFISH_DEVMODE == true) {
			JSubMenuHelper::addEntry(JText::_( 'ORPHANS' ), 'index.php?option=com_joomfish&amp;task=translate.orphans');
		}
		if (JOOMFISH_DEVMODE == true) {
			JSubMenuHelper::addEntry(JText::_( 'MANAGE_TRANSLATIONS' ), 'index.php?option=com_joomfish&amp;task=manage.overview', false);
		}
		if (JOOMFISH_DEVMODE == true) {
			JSubMenuHelper::addEntry(JText::_( 'STATISTICS' ), 'index.php?option=com_joomfish&amp;task=statistics.overview', true);
		}
		JSubMenuHelper::addEntry(JText::_( 'LANGUAGE_CONFIGURATION' ), 'index.php?option=com_joomfish&amp;task=languages.show');
		JSubMenuHelper::addEntry(JText::_( 'CONTENT_ELEMENTS' ), 'index.php?option=com_joomfish&amp;task=elements.show');
		JSubMenuHelper::addEntry(JText::_( 'HELP_AND_HOWTO' ), 'index.php?option=com_joomfish&amp;task=help.show');

		$this->panelStates	= $this->get('PanelStates');
		$this->contentInfo	= $this->get('ContentInfo');
		$this->publishedTabs	= $this->get('PublishedTabs');

		$this->assignRef('panelStates', $this->panelStates);
		$this->assignRef('contentInfo', $this->contentInfo);
		$this->assignRef('publishedTabs', $this->publishedTabs);

		JHTML::_('behavior.tooltip');
		parent::display($tpl);
	}

	/**
	 * This method renders a nice status overview table from the content element files
	 *
	 * @param unknown_type $contentelements
	 */
	public function renderOriginalStatusTable($originalStatus, $message='', $langCodes=null) {
		$htmlOutput = '';

		$htmlOutput = '<table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminlist">';
		$htmlOutput .= '<tr><th>' .JText::_('CONTENT'). '</th><th>' .JText::_('TABLE_EXIST'). '</th><th>' .JText::_('ORIGINAL_TOTAL'). '</th><th>' .JText::_('ORPHANS'). '</th>';
		if(is_array($langCodes)) {
			foreach ($langCodes as $code) {
				$htmlOutput .= '<th>' .$code. '</th>';
			}
		}
		$htmlOutput .= '</tr>';

		$ceName = '';
		foreach ($originalStatus as $statusRow ) {
			$href = 'index.php?option=com_joomfish&amp;task=translate.overview&amp;catid='.$statusRow['catid'];
			$htmlOutput .= '<tr>';
			$htmlOutput .= '<td><a href="' .$href. '" target="_blank">' .$statusRow['name']. '</a></td>';
			$htmlOutput .= '<td style="text-align: center;">' .($statusRow['missing_table'] ? JText::_( 'MISSING' ) : JText::_( 'VALID' )). '</td>';
			$htmlOutput .= '<td style="text-align: center;">' .$statusRow['total']. '</td>';
			$htmlOutput .= '<td style="text-align: center;">' .$statusRow['orphans']. '</td>';
			if(is_array($langCodes)) {
				foreach ($langCodes as $code) {
					if( array_key_exists('langentry_' .$code, $statusRow)) {
						$persentage = intval( ($statusRow['langentry_' .$code]*100) / $statusRow['total'] );
						$htmlOutput .= '<td>' .$persentage. '%</td>';
					} else {
						$htmlOutput .= '<td>&nbsp;</td>';
					}
				}
			}
			$htmlOutput .= '</tr>';
		}

		if($message!='') {
			$span = 4 + count($langCodes);
			$htmlOutput .= '<tr><td colspan="'.$span.'" class="message">' .$message. '</td></tr>';
		}
		$htmlOutput .= '</table>';

		return $htmlOutput;
	}

	/**
	 * Status table for translation checks
	 *
	 * @param array $translationStatus
	 * @return unknown
	 */
	public function renderTranslationStatusTable($translationStatus, $message='') {
		$htmlOutput = '';

		$htmlOutput .= '<table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminlist">';
		$htmlOutput .= '<tr><th>' .JText::_('CONTENT'). '</th><th>' .JText::_('LANGUAGE'). '</th><th>' .JText::_('TRANSLATION_TOTAL'). '</th><th>' .JText::_('TITLE_PUBLISHED'). '</th><th>' .JText::_('VALID'). '</th><th>' .JText::_('UNVALID'). '</th></tr>';
		
		foreach ($translationStatus as $statusRow ) {
			$href = 'index.php?option=com_joomfish&amp;task=translate.overview&amp;catid='.$statusRow['catid'].'&amp;language_id='.$statusRow['language_id'];
			$htmlOutput .= '<tr>';
			$htmlOutput .= '<td><a href="'.$href.'">' .$statusRow['content']. '</a></td>';
			$htmlOutput .= '<td>' .$statusRow['language']. '</td>';
			$htmlOutput .= '<td style="text-align: center;">' .$statusRow['total']. '</td>';
			$htmlOutput .= '<td style="text-align: center;">' .$statusRow['published']. '</td>';
			$htmlOutput .= '<td style="text-align: center;">' .$statusRow['state_valid']. '</td>';
			$htmlOutput .= '<td style="text-align: center;">' .$statusRow['state_unvalid']. '</td>';
			$htmlOutput .= '</tr>';
		}

		if($message!='') {
			$htmlOutput .= '<tr><td colspan="7" class="message">' .$message. '</td></tr>';
		}
		$htmlOutput .= '</table>';

		return $htmlOutput;
	}
}
