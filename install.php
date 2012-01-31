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
            
            // Install plugins
            foreach($manifest->plugins->plugin as $plugin) {
                $attributes = $plugin->attributes();
                $plg = $source . DS . $attributes['folder'].DS.$attributes['plugin'];
                $result = ($installer->install($plg) !== false) ? JText::_('COM_JOOMFISH_SUCCESS') : JText::_('COM_JOOMFISH_FAIL') ;;
                echo '<p>' . JText::_('COM_JOOMFISH_INSTALL_EXTENSION') . ' ' . $attributes['plugin'] . ': ' . $result . '</p>';
            }
            
            // Install modules
            foreach($manifest->modules->module as $module) {
                $attributes = $module->attributes();
                $mod = $source . DS . $attributes['folder'].DS.$attributes['module'];
                $result = ($installer->install($mod) !== false) ? JText::_('COM_JOOMFISH_SUCCESS') : JText::_('COM_JOOMFISH_FAIL') ;
                echo '<p>' . JText::_('COM_JOOMFISH_INSTALL_EXTENSION') . ' ' . $attributes['module'] . ': ' . $result . '</p>';;
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
			
			$where = 
			
			$query->where($where);
            $query->where("$columnType='plugin' OR $columnType='module'");
            $db->setQuery((string)$query);
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
	       		$result = ($installer->uninstall('plugin', (int)$plgID) !== false) ? JText::_('COM_JOOMFISH_SUCCESS') : JText::_('COM_JOOMFISH_FAIL') ;
	       		echo '<p>' . JText::_('COM_JOOMFISH_UNINSTALL_EXTENSION') . ' ' . $attributes['plugin'] . ': ' . $result . '</p>';
        	}

        	// Uninstall modules
        	foreach($manifest->modules->module as $module) {
        		$attributes = $module->attributes();
        		$modID = $ids[(string)$attributes['module']]->extension_id;
        		$result = ($installer->uninstall('module', (int)$modID) !== false) ? JText::_('COM_JOOMFISH_SUCCESS') : JText::_('COM_JOOMFISH_FAIL') ;
        		echo '<p>' . JText::_('COM_JOOMFISH_UNINSTALL_EXTENSION') . ' ' . $attributes['module'] . ': ' . $result . '</p>';
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
         *
        function postflight($type, $parent) 
        {
                // $parent is the class calling this method
                // $type is the type of change (install, update or discover_install)
                echo '<p>' . JText::_('COM_JOOMFISH_POSTFLIGHT_' . $type . '_TEXT') . '</p>';
        }*/
}
