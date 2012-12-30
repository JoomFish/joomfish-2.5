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
 * @subpackage	Model.manage
 */
class ManageModelManage extends JModel
{
	protected $_modelName = 'manage';

	/**
	 * return the model name
	 */
	public function getName() {
		return $this->_modelName;
	}
	
	/**
	 * returns the list of available languages
	 */
	public function getLanguageList() {
		$jfManager = JoomFishManager::getInstance();
		$languages = $jfManager->getLanguages( false );		// all languages even non active once
		$defaultLang = $jfManager->getDefaultLanguage();
		$params = JComponentHelper::getParams( 'com_joomfish' );
		$showDefaultLanguageAdmin = $params->get("showDefaultLanguageAdmin", false);
		$langOptions = array();
		$langOptions[] = array('value' => -1, 'text' => JText::_( 'DO_NOT_COPY' ) );

		if ( count($languages)>0 ) {
			foreach( $languages as $language )
			{
				if($language->lang_code != $defaultLang || $showDefaultLanguageAdmin) {
					$langOptions[] = array('value' => $language->lang_id, 'text' => $language->title );
				}
			}
		}
		return $langOptions;
	}

	/**
	 * This method copies originals content items to one selected language
	 *
	 * @param unknown_type $original2languageInfo
	 * @param unknown_type $phase
	 * @param unknown_type $statecheck_i
	 * @param unknown_type $message
	 * @return array	Information result array
	 */
	public function copyOriginalToLanguage($original2languageInfo, &$phase, &$state_catid, $language_id, $overwrite, &$message) {
		$db = JFactory::getDBO();
		$jfManager = JoomFishManager::getInstance();
		$sql = '';

		switch ($phase) {
			case 1:
				$original2languageInfo = array();

				$sql = "select distinct CONCAT('".$db->getPrefix()."',reference_table) from #__jf_content";
				$db->setQuery( $sql );
				$tablesWithTranslations = $db->loadResultArray();

				$sql = "SHOW TABLES";
				$db->setQuery( $sql );
				$tables = $db->loadResultArray();

				$allContentElements = $jfManager->getContentElements();

				foreach ($allContentElements as $catid=>$ce){
					$ceInfo = array();
					$ceInfo['name'] = $ce->Name;
					$ceInfo['catid'] = $catid;
					$ceInfo['total'] = '??';
					$ceInfo['existing'] = '??';
					$ceInfo['processed'] = '0';
					$ceInfo['copied'] = '0';
					$ceInfo['copy'] = false;

					$contentTable = $ce->getTable();
					$tablename = $db->getPrefix() . $contentTable->Name;
					if (in_array($tablename,$tables)){
						// get total count of table entries
						$sql = 'SELECT COUNT(*) FROM ' .$tablename. ' AS c';
						if( $contentTable->Filter != ''){
							$sql .= ' WHERE ' .$contentTable->Filter;
						}

						$db->setQuery( $sql );
						$ceInfo['total'] = $db->loadResult();
					}
					$original2languageInfo[$catid] = $ceInfo;
				}
				$phase = 1;		// stays with 1 as the second phase needs the bottom to be clicked
				$message = JText::_('COPY2LANGUAGE_INFO');
				break;

			case 2:
				if( $state_catid != '' ) {
					// removing all content information which are not to be copied!
					$celements = explode(',', $state_catid);
					if( count($celements) < count($original2languageInfo)) {
						$shortList = array();
						foreach ($celements as $element) {
							$shortList[$element] = $original2languageInfo[$element];
						}
						$original2languageInfo = $shortList;
					}
				}
				$phase = 3;

			case 3:
				if( $state_catid != '' ) {
					$celements = explode(',', $state_catid);
					// copy the information per content element file, starting with the first in the list
					$catid = array_shift($celements);
					$catidCompleted = false;

					// coyping the information from the selected content element
					if($catid!='' && $language_id!=0) {
						// get's the config settings on how to store original files
						$storeOriginalText = ($jfManager->getCfg('storageOfOriginal') == 'md5') ? false : true;

						// make sure we are only transfering data within parts (max 100 items at a time)
						$ceInfo =& $original2languageInfo[$catid];
						if(intval($ceInfo['processed']) < intval($ceInfo['total'])) {
							$contentElement = $jfManager->getContentElement( $catid );
							$db->setQuery( $contentElement->createContentSQL( $language_id, null, $ceInfo['processed'], 10,array() ) );

							$rows = $db->loadObjectList();
							if ($db->getErrorNum()) {
								JError::raiseError( 500,JTEXT::_('Invalid Content SQL : ') .$db->getErrorMsg());
								return false;
							} else {
								for( $i=0; $i<count($rows); $i++ ) {
									$translationClass = $contentElement->getTranslationObjectClass();
									$translationObject = new $translationClass( $language_id, $contentElement );
									if( $overwrite || $translationObject->translation_id == 0) {
										$translationObject->copyContentToTranslation( $rows[$i], $rows[$i] );
										$translationObject->store();
										$ceInfo['copied'] += 1;
									}
									$rows[$i] = $translationObject;
								}
								$ceInfo['processed'] += $i;
								if($ceInfo['processed'] >= $ceInfo['total']) {
									$catidCompleted = true;
								}
							}
						}
					}
					if( $catidCompleted ) {
						if(count($celements)>0) {
							$state_catid = implode(',', $celements);
						} else {
							$state_catid = '';
						}
					}
				}

				$message = JText::_('COPY2LANGUAGE_PROCESS');
				if( $state_catid == '') {
					$phase = 4;		// Successfully finished phase 3
					$message = JText::_('COPY2LANGUAGE_COMPLETED');
				}
				break;
		}

		return $original2languageInfo;
	}
}
?>
