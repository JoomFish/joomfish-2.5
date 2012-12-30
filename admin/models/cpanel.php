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
 * @subpackage Models
 *
 */
// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

/**
 * @package		Joom!Fish
 * @subpackage	CPanel
 */
class CPanelModelCPanel extends JModel
{
	protected $_modelName = 'cpanel';

	/**
	 * return the model name
	 */
	public function getName() {
		return $this->_modelName;
	}

	public function getUsersplash() {
		$jfm = JoomFishManager::getInstance();
		return $jfm->getCfg('usersplash', 1);
	}

	/**
	 * Get a list of panel state information
	 */
	public function getPanelStates() {
		$panelStates = array();
		$systemState = $this->_checkSystemState();
		$panelStates['directory_state'] = array_key_exists('directory_state', $systemState) ? $systemState['directory_state'] : null;
		$panelStates['directory'] = array_key_exists('directory', $systemState) ? $systemState['directory'] : null;
		$panelStates['extension_state'] = array_key_exists('extension_state', $systemState) ? $systemState['extension_state']: null;
		$panelStates['extension'] = array_key_exists('extension', $systemState) ? $systemState['extension']: null;
		$panelStates['performance_state'] = array_key_exists('performance_state', $systemState) ? $systemState['performance_state']: null;
		$panelStates['performance'] = array_key_exists('performance', $systemState) ? $systemState['performance']: null;
		//$panelStates['system'] = $this->_getSystemInfo();

		return $panelStates;
	}

	/**
	 * Get a list of content informations
	 */
	public function getContentInfo() {
		$contentInfo = array();
		$contentInfo['unpublished'] = $this->_testUnpublisedTranslations();
		return $contentInfo;
	}

	/**
	 * Get the list of published tabs, based on the ID
	 */
	public function getPublishedTabs() {
		$tabs = array();

		$pane = new stdClass();
		$pane->title = 'Information';
		$pane->name = 'Information';
		$pane->alert = false;
		$tabs[] = $pane;

		// Validating other tabs based on extension configuration
		// JFTODO Move all panels to their own administrator module
		$params = JComponentHelper::getParams('com_joomfish');
		if( $params->get('showPanelNews', 1) ) {
			$pane = new stdClass();
			$pane->title = 'News';
			$pane->name = 'JFNews';
			$pane->alert = false;
			$tabs[] = $pane;
		}
		if( $params->get('showPanelUnpublished', 1) ) {
			$pane = new stdClass();
			$pane->title = 'TITLE_UNPUBLISHED';
			$pane->name = 'ContentState';
			$pane->alert = false;
			$tabs[] = $pane;
		}
		if( $params->get('showPanelState', 1) ) {
			$pane = new stdClass();
			$pane->title = 'System State';
			$pane->name = 'SystemState';
			$pane->alert = false;
			$tabs[] = $pane;
		}

		return $tabs;
	}

	/**
	 * This method checks the different system states based on the definition in the component XML file.
	 * @return array with rows of the different component states
	 *
	 */
	private function _checkSystemState() {
		$db = JFactory::getDBO();

		$checkResult = array();

		// Read the file to see if it's a valid template XML file
		$xmlDoc = new DOMDocument();

		$xmlfile = JOOMFISH_ADMINPATH .DS. 'manifest.xml';
		if (!$xmlDoc->load( $xmlfile)) {
			return $checkResult;
		}

		$element = $xmlDoc->documentElement;


		if ($element->nodeName != 'extension') {
			return $checkResult;
		}
		if ($element->getAttribute( "type" ) != "component") {
			return $checkResult;
		}
		$checkElements = $xmlDoc->getElementsByTagName('check')->item(0);
		if (!isset($checkElements) || !$checkElements->hasChildNodes()){
			return $checkResult;
		}

		// Default values of different master states
		$checkResult['directory_state'] = true;
		$checkResult['module_state'] = true;
		$checkResult['extension_state'] = true;
		$checkResult['performance_state'] = true;

		foreach ($checkElements->childNodes as $child){
			$type = $child->nodeName;
			$check = new stdClass();
			switch ($type) {
				case 'directory':
					$check->description = $child->textContent;
					$check->result = is_writable(JPATH_SITE .DS. $check->description) ? true : false;
					$check->resultText = $check->result ? JText::_( 'WRITABLE' ) : JText::_( 'WRITABLE' );
					$check->link = '';
					$checkResult[$type][] = $check;
					$checkResult[$type. '_state'] = $checkResult[$type. '_state'] & $check->result;
					break;

				case 'module':
					$check->description = JText::_($child->getAttribute('name'));
					$mtype = $child->getAttribute('type');
					$field = $child->getAttribute('field');
					$value = $child->getAttribute('value');
					$name = $child->getAttribute('name');
					$condition = $child->textContent;
						
					$sql = "SELECT $field, id FROM #__modules WHERE $condition";
					$db->setQuery($sql);
					$resultValue = $db->loadRow();
						
					if( $resultValue != null ) {
						$check->result = ($value == $resultValue[0]) ? true : false;
						$check->resultText = $check->result ? JText::_($field) : JText::_('un'.$field);
							
						$check->link = JURI::root().'administrator/index.php?option=com_modules&task=module.edit&hidemainmenu=1&id='.$resultValue[1];
					} else {
						$check->result = false;
						$check->resultText = JText::_( 'NOT_INSTALLED' );
							
						$check->link = '';
					}
						
					$checkResult['extension'][] = $check;
					$checkResult['extension_state'] = $checkResult['extension_state'] & $check->result;
					break;

				case 'extension':
					$check->description = JText::_($child->getAttribute('name'));
					$etype = $child->getAttribute('type');
					$field = $child->getAttribute('field');
					$value = $child->getAttribute('value');
					$name = $child->getAttribute('name');
					$condition = $child->textContent;

					if ($field=='ordering'){
						$sql = "SELECT extension_id, element, ordering FROM #__extensions  WHERE $condition ORDER BY ordering";
						$db->setQuery($sql);
						$resultValues = $db->loadObjectList();
						echo $db->getErrorMsg();
						if (array_key_exists($value,$resultValues) && $resultValues[$value]->element==$name){
							$check->result = true ;
							$check->resultText = JText::_($field);
							$check->link = JURI::root().'administrator/index.php?option=com_'.$etype.'s&task='.$etype.'.edit&hidemainmenu=1&extension_id='.$resultValues[$value]->extension_id;
						}
						else {
							$sql = "SELECT $field, extension_id FROM #__extensions WHERE $condition";
							$db->setQuery($sql);
							$resultValue = $db->loadRow();
							$check->result = false;
							$check->resultText = JText::_('un'.$field);
							$check->link = JURI::root().'administrator/index.php?option=com_'.$etype.'s&task='.$etype.'.edit&hidemainmenu=1&extension_id='.$resultValue[1];
						}
					}
					else {
						$sql = "SELECT $field, extension_id FROM #__extensions WHERE $condition";
						$db->setQuery($sql);
						$resultValue = $db->loadRow();

						if( $resultValue != null ) {
							$check->result = ($value == $resultValue[0]) ? true : false;
							$check->resultText = $check->result ? JText::_($field) : JText::_('un'.$field);

							$check->link = JURI::root().'administrator/index.php?option=com_'.$etype.'s&task='.$etype.'.edit&hidemainmenu=1&extension_id='.$resultValue[1];
						} else {
							$check->result = false;
							$check->resultText = JText::_( 'NOT_INSTALLED' );

							$check->link = '';
						}
					}

					$checkResult[$type][] = $check;
					$checkResult[$type. '_state'] = $checkResult[$type. '_state'] & $check->result;
					break;
						
				case 'performance':
					$check->description = JText::_($child->getAttribute('name'));
					$check->name = $child->getAttribute('name');
					$check->type = $child->getAttribute('type');
					$check->link = $child->getAttribute('link');
					$check->link = ($check->link != '' && preg_match('/http:/i', $check->link)) ? JURI::root() .$check->link : $check->link;
						
					if($check->type=='database') {
						$checkfunction = $child->getAttribute('check_function');
						$check_true = $child->getAttribute('check_true');
						$check_false = $child->getAttribute('check_false');
						$optimal_value = $child->getAttribute('optimal_value');
						$check->current = $db->name;
						$check->available = function_exists($checkfunction) ? $check_true : $check_false;
						$check->optimal = $optimal_value;
						if($check->available==$optimal_value && $check->available != $check->current) {
							$check->result = false;
							$check->resultText = JText::sprintf('JF_PERFORMANCE_NOT_OPTIMAL', $check->current, $check->optimal);
						} else {
							$check->result = true;
							$check->resultText = JText::sprintf('JF_PERFORMANCE_OPTIMAL', $check->current, $check->optimal);
						}
					} elseif ($check->type=='php') {
						$check->required = $child->getAttribute('required');
						$check->optimal = $child->getAttribute('optimal_value');
						$check->current = phpversion();
						if (version_compare($check->current,$check->required,"<")){
							$check->result = false;
							$check->resultText = JText::sprintf('JF_PERFORMANCE_LESS_REQUIRED', $check->current, $check->required);
						} elseif(version_compare($check->current,$check->required,">=") && version_compare($check->current,$check->optimal,"<")) {
							$check->result = true;
							$check->resultText = JText::sprintf('JF_PERFORMANCE_NOT_OPTIMAL', $check->current, $check->optimal);
						} else {
							$check->result = true;
							$check->resultText = JText::sprintf('JF_PERFORMANCE_OPTIMAL', $check->current, $check->optimal);
						}
					} elseif ($check->type=='config') {
						$check->value = $child->getAttribute('value');
						$check->optimal = $child->getAttribute('optimal_value');
						$jfm = JoomFishManager::getInstance();
						$check->current = $jfm->getCfg($check->value);
						if($check->current == $check->optimal) {
							$check->result = true;
							$check->resultText = JText::sprintf('JF_PERFORMANCE_CONFIG_OPTIMAL', JText::_($check->value), $check->current);
						} else {
							$check->result = false;
							$check->resultText = JText::sprintf('JF_PERFORMANCE_CONFIG_NOT_OPTIMAL', JText::_($check->value), $check->current, $check->optimal);
						}
					}
						
						
					$checkResult[$type][] = $check;
					$checkResult[$type. '_state'] = $checkResult[$type. '_state'] & $check->result;
					break;
			}
		}
		return $checkResult;
	}


	/**
	 * Testing if old installation is found and upgraded?
	 * This method is rebuild and checks now for old JoomFish installations not MambelFish anymore!
	 * @return int		0 := component not installed, 1 := installed but not upgraded, 2 := installed and upgraded
	 */
	private function _testOldInstall()
	{
		$db = JFactory::getDBO();
		$oldInstall = 0;

		$db->setQuery( "SHOW TABLES LIKE '%jf_%'" );
		$db->query();
		$rows = $db->loadResultArray();
		foreach ($rows as $row) {
			if( preg_match( '/mbf_content/i', $row ) ) {
				$oldInstall = 1;
				break;
			}
		}

		$jfManager = JoomFishManager::getInstance();
		if( $oldInstall == 1 && $jfManager->getCfg( 'mbfupgradeDone' ) ) {
			$oldInstall = 2;
		}

		return $oldInstall;
	}

	/**
	 * This method gethers certain information of the system which can be used for presenting
	 * @return array with inforation about the system
	 */
	private function _getSystemInfo() {
		$db = JFactory::getDBO();

		$db->setQuery( 'SELECT count(DISTINCT reference_id, reference_table) FROM #__jf_content');
		$db->query();
		$translations = $db->loadResult();

		$res = array( 'translations' => $translations );
		return $res;
	}

	/**
	 * Start of a function to obtain overview summary of orphan translations
	 *
	 * @return array of orphan tables or nothing if no orphans found
	 */
	private function _testOrphans( ) {

		$config	= JFactory::getConfig();
		$dbprefix = $config->get("dbprefix");
		$db = JFactory::getDBO();

		$orphans = array();
		$tranFilters=array();
		$filterHTML=array();

		$query = "select distinct CONCAT('".$dbprefix."',reference_table) from #__jf_content";
		$db->setQuery( $query );
		$tablesWithTranslations = $db->loadResultArray();

		$query = "SHOW TABLES";
		$db->setQuery( $query );
		$tables = $db->loadResultArray();

		$allContentElements = $this->_joomfishManager->getContentElements();
		foreach ($allContentElements as $catid=>$ce){
			$tablename = $dbprefix.$ce->referenceInformation["tablename"];
			if (in_array($tablename,$tables) &&
					in_array($tablename,$tablesWithTranslations)){
				$db->setQuery( $ce->createOrphanSQL( -1, null, -1, -1,$tranFilters ) );
				$rows = $db->loadObjectList();
				if ($db->getErrorNum()) {
					$this->_message = $db->stderr();
					return false;
				}

				$total = count($rows);
				if ($total>0) {
					$orphans[] = array( 'catid' => $catid, 'name' => $ce->Name, 'total' => $total);
				}
			}
		}

		foreach ($tablesWithTranslations as $twv) {
			if (!in_array($twv,$tables)) {
				$this->_message = "Translations exists for table <b>$twv</b> which is no longer in the database<br/>";
			}
		}
		return $orphans;
	}

	/**
	 * This method tests for the content elements and their original/translation status
	 * It will return an array listing all content element names including information about how may originals
	 *
	 * @param array 	$originalStatus	array with original state values if exist
	 * @param int		$phase	which phase of the status check
	 * @param string	$statecheck_i	running row number starting with -1!
	 * @param string	$message	system message
	 * @param array		$languages	array of availabe languages
	 * @return array	with resulting rows
	 */
	private function _testOriginalStatus($originalStatus, &$phase, &$statecheck_i, &$message, $languages) {
		$originalStatus	= JModel::getInstance('StatisticsModelStatistics')->testOriginalStatus($originalStatus, $phase, $statecheck_i, $message, $languages);
		return $originalStatus;
	}

	/**
	 * This method checks the translation status
	 * The process follows goes through out all existing translations and checks their individual status.
	 * The output is a summary information based grouped by content element files and the languages
	 *
	 * @param array 	$translationStatus	array with translation state values
	 * @param int		$phase	which phase of the status check
	 * @param string	$statecheck_i	running row number starting with -1!
	 * @param string	$message	system message
	 */
	private function _testTranslationStatus( $translationStatus, &$phase, &$statecheck_i, &$message ) {
		$translationStatus = JModel::getInstance('StatisticsModelStatistics')->testTranslationStatus( $translationStatus, $phase, $statecheck_i, $message );
		return $translationStatus;
	}

	/**
	 * This method creates an overview of unpublished translations independed of the content element
	 * @return array 	of unpublished translations or null
	 */
	private function _testUnpublisedTranslations() {
		$db = JFactory::getDBO();
		$unpublishedTranslations = null;

		$sql = "select jfc.reference_table, jfc.reference_id, jfc.language_id, jfl.title as language" .
				"\n from #__jf_content as jfc, #__languages as jfl" .
				"\n where jfc.published=0  and jfc.language_id = jfl.lang_id" .
				"\n group by jfc.reference_table, jfc.reference_id, jfc.language_id" .
				"\n limit 0, 50";
		$db->setQuery($sql);

		if( $rows = $db->loadObjectList() ) {
			foreach ($rows as $row) {
				$unpublished = array();
				$unpublished['reference_table'] = $row->reference_table;
				$unpublished['catid'] = $row->reference_table;
				$unpublished['reference_id'] = $row->reference_id;
				$unpublished['language_id'] = $row->language_id;
				$unpublished['language'] = $row->language;
				$unpublishedTranslations[] = $unpublished;
			}
		}


		$sqlnative = $db->getQuery(true);

		$sqlnative->select('jfc.reference_table,  jfc.reference_id, jfc.translation_id, jfc.language AS langcode, jfl.title AS language, jfl.lang_id AS language_id');
		$sqlnative->from('#__jf_translationmap AS jfc');
		$sqlnative->join('', '#__languages AS jfl ON jfc.language = jfl.lang_code');


		$celements = JoomFishManager::getInstance()->getContentElements();
		$where = array();

		foreach ($celements AS $contentElement) {
			if ($contentElement->getTarget() == "native") {
					
				$sqlnative->leftJoin('#__'.$contentElement->getTableName().' AS jf'.$contentElement->getTableName().
						' ON jfc.reference_table=\''.$contentElement->getTableName().'\''.
						' AND jf'.$contentElement->getTableName().'.'.$contentElement->getReferenceId().'= jfc.translation_id'.
						' AND jf'.$contentElement->getTableName().'.'.$contentElement->getPublishedField().'=0');
					
				$where [] = 'jf'.$contentElement->getTableName().'.'.$contentElement->getPublishedField().'=0';
				$sqlnative->select('jf'.$contentElement->getTableName().'.'.$contentElement->getPublishedField().' AS published');

			}
		}

		$where = implode(' OR ', $where);
		$sqlnative->where ($where);
		$db->setQuery($sqlnative);

		if( $rowsnative = $db->loadObjectList() ) {
			foreach ($rowsnative as $row) {
				$unpublished = array();
				$unpublished['reference_table'] = $row->reference_table;
				$unpublished['catid'] = $row->reference_table;
				$unpublished['reference_id'] = $row->reference_id;
				$unpublished['language_id'] = $row->language_id;
				$unpublished['language'] = $row->language;
				$unpublishedTranslations[] = $unpublished;
			}
		}

		return $unpublishedTranslations;
	}
}