<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
/**
 * Install script for Joomfish, inspired by http://itprism.com/blog/multiple-extensions-single-package
 */
class com_JoomfishInstallerScript
{
        /**
         * method to install the component
         *
         * @return void
         */
        public function install($parent) 
        {
            $manifest = $parent->get("manifest");
            $parent = $parent->getParent();
            $source = $parent->getPath("source");
             
            $installer = new JInstaller();
			$app = JFactory::getApplication();
            
            // Install plugins
            foreach($manifest->plugins->plugin as $plugin) {
                $attributes = $plugin->attributes();
                $plg = $source . DS . $attributes['folder'].DS.$attributes['plugin'];
                if ($installer->install($plg) !== false) {
                	$result = JText::_('COM_JOOMFISH_SUCCESS');
                	$mtype = 'information';
                }  else {
                	$result = JText::_('COM_JOOMFISH_FAIL') ;
                	$mtype = 'error';
                }       
                
                $app->enqueueMessage( JText::_('COM_JOOMFISH_INSTALL_EXTENSION') . ' ' . $attributes['name'] . ': ' . $result , $mtype);
            }
            
            // Install modules
            foreach($manifest->modules->module as $module) {
                $attributes = $module->attributes();
                $mod = $source . DS . $attributes['folder'].DS.$attributes['module'];
                if ($installer->install($mod) !== false) {
                	$result = JText::_('COM_JOOMFISH_SUCCESS');
                	$mtype = 'information';
                }  else {
                	$result = JText::_('COM_JOOMFISH_FAIL') ;
                	$mtype = 'error';
                }
                $app->enqueueMessage( JText::_('COM_JOOMFISH_INSTALL_EXTENSION') . ' ' . $attributes['name'] . ': ' . $result , $mtype);
            }
             
            
            $db = JFactory::getDbo();
            $tableExtensions = $db->nameQuote("#__extensions");
            $columnElement   = $db->nameQuote("element");
            $columnType      = $db->nameQuote("type");
            $columnEnabled   = $db->nameQuote("enabled");
            
            // Enable plugins and modules
            $query = $db->getQuery(true);
            $query->update($tableExtensions);
            $query->set($columnEnabled."=1");
            
            $where = null;
			// looptest counters
			$ltp = 0;
			$ltm = 0;
			
			foreach($manifest->plugins->plugin as $plugin) {
				if ($ltp ==1 ) $where .= " OR ";
				$attributes = $plugin->attributes();
				$where .= $columnElement ."='".$attributes['plugin']."'";
				$ltp = 1;
			}
			
			$query2 = $db->getQuery(true);
			$query2->update('#__modules');
			$where2	= null;
			$query2->set($db->nameQuote("published")."=1");
			$query2->set($db->nameQuote("position")."=".$db->quote('position-6'));
			
			$query3 = $db->getQuery(true);
			$query3->select('id');
			$query3->from('#__modules');
			
			foreach($manifest->modules->module as $module) {
				if ($ltp ==1 ) {
					$where .= " OR ";
					$ltp = 0;
				}
				if ($ltm ==1 ) {
					$where .= " OR ";
					$where2 .= " OR ";
					$where3 .= " OR ";
				}
				$attributes = $module->attributes();
				$where .= $columnElement ."='".$attributes['module']."'";
				$where2 .= $db->nameQuote('module') ."='".$attributes['module']."'";
			}			
			
			$query->where($where);
            $query->where("$columnType='plugin' OR $columnType='module'");
            $db->setQuery((string)$query);
            $db->query();
            
            $query2->where($where2);
            $db->setQuery((string)$query2);
            $db->query();
            
            $query3->where($where2);
            $db->setQuery((string)$query3);
            $moduleids = $db->loadAssocList('id', 'id');
            
            $query4 = $db->getQuery(true);
            
            $insert = '#__modules_menu ('.$db->nameQuote("moduleid").','.$db->nameQuote("menuid").')
            VALUES ';
            $values = array();
            foreach($moduleids AS $moduleid) {
            	$values[] = '('.$moduleid.',0)';
            }
            $values = implode(',', $values);
            $insert .= $values .' ON DUPLICATE KEY UPDATE '.$db->nameQuote("menuid").'=0;';
            $query4->insert($insert);
            $db->setQuery($query4);
            $db->query();
            
            // Set plugin ordering: first increase all plugins ordering numbers
            $query = $db->getQuery(true);
            $query->update($tableExtensions);
            $query->set($db->nameQuote("ordering").'='.$db->nameQuote("ordering").'+3');
            $query->where($columnType.'='.$db->quote("plugin"));
            $query->where($db->nameQuote("folder").'='.$db->quote("system"));
            $db->setQuery((string)$query);
            $db->query();
            
            // Now set our plugins to the right order
            $query = "UPDATE ".$tableExtensions."
            SET ".$db->nameQuote("ordering")." = CASE ".$db->nameQuote("element")."
            WHEN ".$db->quote("jfrouter")." THEN 0
            WHEN ".$db->quote("jfdatabase")." THEN 1
            WHEN ".$db->quote("jfoverrides")." THEN 2
            END
            WHERE ".$db->nameQuote("element")." IN (".$db->quote("jfrouter").",".$db->quote("jfdatabase").",".$db->quote("jfoverrides").")";
            $db->setQuery($query);
            $db->query();
            
			// Reorder table #__extensions where type=plugin and folder=system
			$table = JTable::getInstance('extension');
			$whereOrder = $db->nameQuote("folder").'='.$db->quote("system").' AND '.$columnType.'='.$db->quote("plugin");
			$table->reorder($whereOrder);
			
			
			
			// Install library
			$library	= $manifest->library;
			$attributes = $library->attributes();
			$lib = $source . DS . $attributes['folder'];
			if ($installer->install($lib) !== false) {
				$result = JText::_('COM_JOOMFISH_SUCCESS');
				$mtype = 'information';
			}  else {
				$result = JText::_('COM_JOOMFISH_FAIL') ;
				$mtype = 'error';
			}
			$app->enqueueMessage( JText::_('COM_JOOMFISH_INSTALL_EXTENSION') . ' ' . $attributes['name'] . ': ' . $result , $mtype);
        }
 
        /**
         * method to uninstall the component
         *
         * @return void
         */
        public function uninstall($parent)
        {
        	$manifest = $parent->get("manifest");
        	$parent = $parent->getParent();
        	$source = $parent->getPath("source");
        	 
        	$installer = new JInstaller();
        	$app = JFactory::getApplication();
        	
        	$db = JFactory::getDbo();
        	$query = $db->getQuery(true);
        	$columnElement   = $db->nameQuote("element");
        	
			$query->select($db->nameQuote("extension_id").",".$columnElement);
			$query->from($db->nameQuote("#__extensions"));
			
			$where = null;
			// looptest counters
			$ltp = 0;
			$ltm = 0;
			
			foreach($manifest->plugins->plugin as $plugin) {
				if ($ltp ==1 ) $where .= " OR ";
				$attributes = $plugin->attributes();
				$where .= $columnElement ."='".$attributes['plugin']."'";
				$ltp = 1;
			}
			
			foreach($manifest->modules->module as $module) {
				if ($ltp ==1 ) {
					$where .= " OR ";
					$ltp = 0;
				}
				if ($ltm ==1 ) $where .= " OR ";
				$attributes = $module->attributes();
				$where .= $columnElement ."='".$attributes['module']."'";
			}
			$attributes = $manifest->library->attributes();
			$where .= " OR ".$columnElement ."='".'lib_'.$attributes['library']."'";
			
			$query->where($where);
			$db->setQuery((string)$query);
			
			$ids = $db->loadObjectList('element');

        	// Uninstall plugins
        	foreach($manifest->plugins->plugin as $plugin) {
        		$attributes = $plugin->attributes();
        		$plgID = $ids[(string)$attributes['plugin']]->extension_id;
	       	    if ($installer->uninstall('plugin', (int)$plgID) !== false) {
                	$result = JText::_('COM_JOOMFISH_SUCCESS');
                	$mtype = 'information';
                }  else {
                	$result = JText::_('COM_JOOMFISH_FAIL') ;
                	$mtype = 'error';
                }
	       		$app->enqueueMessage( JText::_('COM_JOOMFISH_UNINSTALL_EXTENSION') . ' ' . $attributes['name'] . ': ' . $result , $mtype);
        	}

        	// Uninstall modules
        	foreach($manifest->modules->module as $module) {
        		$attributes = $module->attributes();
        		$modID = $ids[(string)$attributes['module']]->extension_id;
        	    if ($installer->uninstall('module', (int)$modID) !== false) {
                	$result = JText::_('COM_JOOMFISH_SUCCESS');
                	$mtype = 'information';
                }  else {
                	$result = JText::_('COM_JOOMFISH_FAIL') ;
                	$mtype = 'error';
                }
        		$app->enqueueMessage( JText::_('COM_JOOMFISH_UNINSTALL_EXTENSION') . ' ' . $attributes['name'] . ': ' . $result , $mtype);
        	}
        	
        	// Uninstall library
        	$library	= $manifest->library;
        	$attributes = $library->attributes();
        	$libID = $ids[(string)'lib_'.$attributes['library']]->extension_id;
        	if ($installer->uninstall('library', (int)$libID) !== false) {
        		$result = JText::_('COM_JOOMFISH_SUCCESS');
        		$mtype = 'information';
        	}  else {
        		$result = JText::_('COM_JOOMFISH_FAIL') ;
        		$mtype = 'error';
        	}
        	$app->enqueueMessage( JText::_('COM_JOOMFISH_UNINSTALL_EXTENSION') . ' ' . $attributes['name'] . ': ' . $result , $mtype);
        	
        }
 
        /**
         * method to update the component
         *
         * @return void
         */
        public function update($parent) 
        {
                $this->install($parent);
        }
 
        /**
         * method to run before an install/update/uninstall method
         *
         * @return void
         *
        function preflight($type, $parent) 
        {
                // $parent is the class calling this method
                // $type is the type of change (install, update or discover_install)
                echo '<p>' . JText::_('COM_JOOMFISH_PREFLIGHT_' . $type . '_TEXT') . '</p>';
        }*/
 
        /**
         * method to run after an install/update/uninstall method
         *
         * @return void
         **/
        public function postflight($type, $parent) 
        {		
                // $parent is the class calling this method
                // $type is the type of change (install, update or discover_install)
                if ($type == 'install' || $type == 'update') {
                	jimport('joomfish.cache.jfdb');
                	$result = JCacheStorageJfdb::setupDB();
                }
        }
}
