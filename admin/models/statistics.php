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
 * @subpackage	Model.statistics
 */
class StatisticsModelStatistics extends JModel
{
	protected $_modelName = 'statistics';

	/**
	 * return the model name
	 */
	public function getName() {
		return $this->_modelName;
	}
	
	/**
	 * This method checks the translation status
	 * The process follows goes through out all existing translations and checks their individual status.
	 * The output is a summary information based grouped by content element files and the languages
	 *
	 * @access protected
	 * @param array 	$translationStatus	array with translation state values
	 * @param int		$phase	which phase of the status check
	 * @param string	$statecheck_i	running row number starting with -1!
	 * @param string	$message	system message
	 */
	public function testTranslationStatus( $translationStatus, &$phase, &$statecheck_i, &$message ) {
		$db = JFactory::getDBO();
		$jfManager = JoomFishManager::getInstance();

		$sql = '';

		switch ($phase) {
			case 1:
				$sqljf = "SELECT jfc.reference_table, jfc.language_id, jfl.lang_code AS langcode, jfl.title AS language" .
						"\n FROM #__jf_content AS jfc" .
						"\n JOIN #__languages AS jfl ON jfc.language_id = jfl.lang_id" .
						"\n GROUP BY jfc.reference_table, jfc.language_id";
				
				$sqlnative = "SELECT jfc.reference_table, jfc.language AS langcode, jfl.title AS language, jfl.lang_id AS language_id" .
						"\n FROM #__jf_translationmap AS jfc" .
						"\n JOIN #__languages AS jfl ON jfc.language = jfl.lang_code" .
						"\n GROUP BY jfc.reference_table, jfc.language";
				
				
				$db->setQuery($sqljf);
				$rowsjf = $db->loadObjectList();
				$db->setQuery($sqlnative);
				$rowsnative = $db->loadObjectList();
				
				$rows = array_merge($rowsjf,$rowsnative);

				$translationStatus = array();
				if( is_array($rows) && count($rows)>0 ) {
					foreach ($rows as $row) {
						$status = array();
						$contentElement = $jfManager->getContentElement( $row->reference_table );
						$status['content'] = $contentElement->Name;
						$status['catid'] = $row->reference_table;
						$status['language_id'] = $row->language_id;
						$status['language'] = $row->language;
						$status['langcode'] = $row->langcode;

						$status['total'] = '';
						$status['state_valid'] = '';
						$status['state_unvalid'] = '';
						$status['state_missing'] = '';
						$status['state'] = '';
						$status['published'] = '';
						
						if ($contentElement->getTarget() == "native") {
							$sql = "SELECT * FROM #__jf_translationmap" .
									"\n WHERE reference_table='" .$row->reference_table. "'" .
									"\n   AND language ='" .$row->langcode . "'" .
									"\n GROUP BY reference_id";
						} else {
								$sql = "SELECT * FROM #__jf_content" .
									"\n WHERE reference_table='" .$row->reference_table. "'" .
									"\n   AND language_id=" .$row->language_id .
									"\n GROUP BY reference_id";
						}
						$db->setQuery($sql);
						if( $totalrows = $db->loadRowList() ) {
							$status['total'] = count($totalrows);
						}

						$translationStatus[] = $status;
					}

					$message = JText::_('TRANSLATION_PHASE1_GENERALCHECK');
					$phase ++;
				} else {
					$message = JText::_( 'NO_TRANSLATION_AVAILABLE' );
					$phase = 4;		// exit
				}
				break;

			case 2:
				if( is_array($translationStatus) && count ($translationStatus)>0 ) {

					for ($i=0; $i<count($translationStatus); $i++) {
						$stateRow =& $translationStatus[$i];
						$contentElement = $jfManager->getContentElement( $stateRow['catid'] );
						if ($contentElement->getTarget() == "native") {
							$sql = "SELECT * FROM #__jf_translationmap AS jftm" .
									"\n JOIN #__".$stateRow['catid']." AS jtab ON jtab.id = jftm.translation_id" .
									"\n WHERE jftm.reference_table='"  .$stateRow['catid'].  "'" .
									"\n   AND jftm.language ='" .$stateRow['langcode']. "'" .
									"\n AND jtab.".$contentElement->getPublishedField()." > 0" .
									"\n GROUP BY jftm.reference_id";
						} else {
							$sql = "select *" .
									"\n from #__jf_content as jfc" .
									"\n where published=1" .
									"\n and reference_table='" .$stateRow['catid']. "'".
									"\n and language_id=" .$stateRow['language_id'].
									"\n group by reference_ID";
						}
						


						$db->setQuery($sql);
						if( $rows = $db->loadRowList() ) {
							$stateRow['published'] = count($rows);
						} else {
							$stateRow['published'] = 0;
						}
					}
				}

				$message = JText::sprintf('TRANSLATION_PHASE2_PUBLISHEDCHECK', '');
				$phase ++;
				break;

			case 3:
				if( is_array($translationStatus) && count ($translationStatus)>0 ) {
					if( $statecheck_i>=0 && $statecheck_i<count($translationStatus)) {
						$stateRow =& $translationStatus[$statecheck_i];

						$contentElement = $jfManager->getContentElement( $stateRow['catid'] );
						$filters = array();

						// trap missing content element files
						if (is_null($contentElement)){
							$message = JText::_('TRANSLATION_PHASE3_STATECHECK');
							$stateRow['state_valid'] = 0;
							$stateRow['state_unvalid'] = 0;
							$stateRow['state_missing'] = 0;
							$statecheck_i ++;
							break;
						}

						// we need to find an end, thats why the filter is at 10.000!
						$db->setQuery( $contentElement->createContentSQL( $stateRow['language_id'], null, 0, 10000,$filters ) );
						if( $rows = $db->loadObjectList() ) {
							$stateRow['state_valid'] = 0;
							$stateRow['state_unvalid'] = 0;
							$stateRow['state_missing'] = 0;

							for( $i=0; $i<count($rows); $i++ ) {
								$translationClass = $contentElement->getTranslationObjectClass();
								$translationObject = new $translationClass( $stateRow['language_id'], $contentElement);
								$translationObject->readFromRow($rows[$i]);
								$rows[$i] = $translationObject;

								switch( $translationObject->state ) {
									case 1:
										$stateRow['state_valid'] ++;
										break;
									case 0:
										$stateRow['state_unvalid'] ++;
										break;
									case -1:
									default:
										$stateRow['state_missing'] ++;
										break;
								}
							}
						}

					}

					if ($statecheck_i<count($translationStatus)-1) {
						$statecheck_i ++;
						$message = JText::sprintf('TRANSLATION_PHASE2_PUBLISHEDCHECK', ' ('. $translationStatus[$statecheck_i]['content'] .'/' .$translationStatus[$statecheck_i]['language'].')');
					} else {
						$message = JText::_('TRANSLATION_PHASE3_STATECHECK');
						$phase = 4;	// exit
					}

				} else {
					$message = JText::_('TRANSLATION_PHASE3_STATECHECK');
					$phase = 4; // exit
				}

				break;
		}


		return $translationStatus;
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
	public function testOriginalStatus($originalStatus, &$phase, &$statecheck_i, &$message, $languages) {
		$db = JFactory::getDBO();
		$jfManager = JoomFishManager::getInstance();
		$tranFilters=array();
		$filterHTML=array();
		$sql = '';

		switch ($phase) {
			case 1:
				$originalStatus = array();
			
				$sqljf = "select distinct CONCAT('".$db->getPrefix()."',reference_table) from #__jf_content";
				
				$sqlnative = "select distinct CONCAT('".$db->getPrefix()."',reference_table) from #__jf_translationmap";
				
				
				$db->setQuery($sqljf);
				$rowsjf = $db->loadResultArray();
				$db->setQuery($sqlnative);
				$rowsnative = $db->loadResultArray();
				
				$tablesWithTranslations = array_merge($rowsjf,$rowsnative);

				$sql = "SHOW TABLES";
				$db->setQuery( $sql );
				$tables = $db->loadResultArray();

				$allContentElements = $jfManager->getContentElements();
				
				$jfManager = JoomFishManager::getInstance();
				$defaultlang = $jfManager->getDefaultLanguage();

				foreach ($allContentElements as $catid=>$ce){
					$ceInfo = array();
					$ceInfo['name'] = $ce->Name;
					$ceInfo['catid'] = $catid;
					$ceInfo['total'] = '??';
					$ceInfo['missing_table'] = false;
					$ceInfo['message'] = '';

					$tablename = $db->getPrefix().$ce->referenceInformation["tablename"];
					
					if (in_array($tablename,$tables)){
						// get total count of table entries
						$sql = "SELECT COUNT(*) FROM " .$tablename;
						if ($ce->getTarget() == "native") {
							$sql .= " WHERE language = '*' OR language = '" . $defaultlang . "'" ;
						}
						$db->setQuery( $sql); 
						$ceInfo['total'] = $db->loadResult();

						if( in_array($tablename,$tablesWithTranslations) ) {
							// get orphans
							$db->setQuery( $ce->createOrphanSQL( -1, null, -1, -1,$tranFilters ) );
							$rows = $db->loadObjectList();
							if ($db->getErrorNum()) {
								$this->_message = $db->stderr();
								return false;
							}
							$ceInfo['orphans'] = count($rows);

							// get number of valid translations
							$ceInfo['valid'] = 0;


							// get number of outdated translations
							$ceInfo['outdated'] = $ceInfo['total'] - $ceInfo['orphans'] - $ceInfo['valid'];

						}else {
							$ceInfo['orphans'] = '0';
						}
					} elseif (!in_array($tablename, $tables)) {
						$ceInfo['missing_table'] = true;
						$ceInfo['message'] = JText::sprintf(TABLE_DOES_NOT_EXIST, $tablename );
					}
					$originalStatus[] = $ceInfo;
				}
				$message = JText::sprintf('ORIGINAL_PHASE1_CHECK', '');
				$phase ++;
				$statecheck_i = 0;
				break;

			case 2:
				if( is_array($originalStatus) && count ($originalStatus)>0 ) {
					if( $statecheck_i>=0 && $statecheck_i<count($originalStatus)) {
						$stateRow =& $originalStatus[$statecheck_i];

						foreach ($languages as $lang) {
							
							$contentElement = $jfManager->getContentElement( $stateRow['catid'] );
							if ($contentElement->getTarget() == "native") {
								$sql = "SELECT * FROM #__jf_translationmap AS jftm" .
										"\n JOIN #__".$stateRow['catid']." AS jtab ON jtab.id = jftm.translation_id" .
										"\n WHERE jftm.reference_table='"  .$stateRow['catid'].  "'" .
										"\n   AND jftm.language ='" .$lang->lang_code . "'" .
										"\n AND jtab.".$contentElement->getPublishedField()." > 0" .
										"\n GROUP BY jftm.reference_id";
							} else {
								$sql = "select *" .
										"\n from #__jf_content as jfc" .
										"\n where jfc.published=1" .
										"\n and jfc.reference_table='" .$stateRow['catid']. "'".
										"\n and jfc.language_id=" .$lang->lang_id.
										"\n group by reference_ID";
							}
							
							$db->setQuery($sql);
							$rows = $db->loadRowList();
							$key = 'langentry_' .$lang->getLanguageCode();
							$stateRow[$key] = count($rows);
						}
					}
					


					if ($statecheck_i<count($originalStatus)-1) {
						$statecheck_i ++;
						$message = JText::sprintf('ORIGINAL_PHASE1_CHECK', ' ('. $originalStatus[$statecheck_i]['name'] .')');
					} else {
						$message = JText::_('ORIGINAL_PHASE2_CHECK');
						$phase = 3;	// exit
					}
				} else {
					$phase = 3; // exit
					$message = JText::_('ORIGINAL_PHASE2_CHECK');
				}
				break;
		}

		return $originalStatus;
	}
}