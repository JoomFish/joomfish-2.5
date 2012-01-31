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
                $installer->install($plg);
            }
            
            // Install modules
            foreach($manifest->modules->module as $module) {
                $attributes = $module->attributes();
                $mod = $source . DS . $attributes['folder'].DS.$attributes['module'];
                $installer->install($mod);
            }
            
            $db = JFactory::getDbo();
            $tableExtensions = $db->nameQuote("#__extensions");
            $columnElement   = $db->nameQuote("element");
            $columnType      = $db->nameQuote("type");
            $columnEnabled   = $db->nameQuote("enabled");
            
            // Enable plugins and modules
            $db->setQuery(
                "UPDATE 
                    $tableExtensions
                SET
                    $columnEnabled=1
                WHERE
                    $columnElement='jfrouter' OR $columnElement='jfdatabase' OR $columnElement='mod_jflanguageselection'
                AND
                    $columnType='plugin' OR $columnType='module'"
            );
            
            $db->query();
            
        }
 
        /**
         * method to uninstall the component
         *
         * @return void
         */
        function uninstall($parent) 
        {
                // $parent is the class calling this method
                echo '<p>' . JText::_('COM_JOOMFISH_UNINSTALL_TEXT') . '</p>';
        }
 
        /**
         * method to update the component
         *
         * @return void
         */
        function update($parent) 
        {
                // $parent is the class calling this method
                echo '<p>' . JText::_('COM_JOOMFISH_UPDATE_TEXT') . '</p>';
        }
 
        /**
         * method to run before an install/update/uninstall method
         *
         * @return void
         */
        function preflight($type, $parent) 
        {
                // $parent is the class calling this method
                // $type is the type of change (install, update or discover_install)
                echo '<p>' . JText::_('COM_JOOMFISH_PREFLIGHT_' . $type . '_TEXT') . '</p>';
        }
 
        /**
         * method to run after an install/update/uninstall method
         *
         * @return void
         */
        function postflight($type, $parent) 
        {
                // $parent is the class calling this method
                // $type is the type of change (install, update or discover_install)
                echo '<p>' . JText::_('COM_JOOMFISH_POSTFLIGHT_' . $type . '_TEXT') . '</p>';
        }
}
