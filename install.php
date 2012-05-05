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
        function install($parent) 
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
                
                $app->enqueueMessage( JText::_('COM_JOOMFISH_INSTALL_EXTENSION') . ' ' . $attributes['plugin'] . ': ' . $result , $mtype);
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
                $app->enqueueMessage( JText::_('COM_JOOMFISH_INSTALL_EXTENSION') . ' ' . $attributes['module'] . ': ' . $result , $mtype);
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
			
			foreach($manifest->modules->module as $module) {
				if ($ltp ==1 ) {
					$where .= " OR ";
					$ltp = 0;
				}
				if ($ltm ==1 ) $where .= " OR ";
				$attributes = $module->attributes();
				$where .= $columnElement ."='".$attributes['module']."'";
			}
			
			
			$query->where($where);
            $query->where("$columnType='plugin' OR $columnType='module'");
            $db->setQuery((string)$query);
            $db->query();
            
            // set plugin ordering, first increase all plugins ordering numbers
            $query = $db->getQuery(true);
            $query->update($tableExtensions);
            $query->set($db->nameQuote("ordering").'='.$db->nameQuote("ordering").'+5');
            $db->setQuery((string)$query);
            $db->query();
            // now set out plugins to the right order
            $query = "UPDATE ".$tableExtensions."
            SET ".$db->nameQuote("ordering")." = CASE ".$db->nameQuote("element")."
            WHEN ".$db->quote("jfrouter")." THEN 0
            WHEN ".$db->quote("jfdatabase")." THEN 1
            WHEN ".$db->quote("jfoverrides")." THEN 2
            END
            WHERE ".$db->nameQuote("element")." IN (".$db->quote("jfrouter").",".$db->quote("jfdatabase").",".$db->quote("jfoverrides").")";
            $db->setQuery($query);
            $db->query();
            
        }
 
        /**
         * method to uninstall the component
         *
         * @return void
         */
        function uninstall($parent)
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
	       		$app->enqueueMessage( JText::_('COM_JOOMFISH_UNINSTALL_EXTENSION') . ' ' . $attributes['plugin'] . ': ' . $result , $mtype);
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
        		$app->enqueueMessage( JText::_('COM_JOOMFISH_UNINSTALL_EXTENSION') . ' ' . $attributes['module'] . ': ' . $result , $mtype);
        	}
        	 
        }
 
        /**
         * method to update the component
         *
         * @return void
         *
        function update($parent) 
        {
                // $parent is the class calling this method
                echo '<p>' . JText::_('COM_JOOMFISH_UPDATE_TEXT') . '</p>';
        }*/
 
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
        function postflight($type, $parent) 
        {		
                // $parent is the class calling this method
                // $type is the type of change (install, update or discover_install)
                if ($type == 'install' || $type == 'update') {
                	JLoader::import( 'classes.JCacheStorageJFDB',JPATH_ADMINISTRATOR.'/components/com_joomfish');
                	$result = JCacheStorageJfdb::setupDB();
                }
        }
}
