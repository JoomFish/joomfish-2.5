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
 *
 */
defined('_JEXEC') or die('Restricted access');

class TranslateParams
{
	protected $origparams;
	protected $defaultparams;
	protected $transparams;
	protected $fields;
	protected $fieldname;

	public function __construct($original, $translation, $fieldname, $fields=null)
	{
		$this->origparams = $original;
		$this->transparams = $translation;
		$this->fieldname = $fieldname;
		$this->fields = $fields;

	}
	
	public static function getTranslateParams($type = '', $original, $translation, $fieldname, $fields = null) {

			JoomFishManager::addIncludePath(JOOMFISH_LIBPATH .DS. 'translateparams', 'translateparams');
			JoomFishManager::addIncludePath(JOOMFISH_LIBPATH .DS. 'contentelement' .DS. 'contentelements', 'translateparams');

			$paramsclass = "TranslateParams_".$type;

			if (!class_exists($paramsclass))
			{

				// Search for the class file in the include paths.
				jimport('joomla.filesystem.path');

				if ($path = JPath::find(JoomFishManager::addIncludePath('','translateparams'), strtolower($type) . '.php'))
				{
					include_once $path;
				}
				else if ($path = JPath::find(JoomFishManager::addIncludePath('','translateparams'), $paramsclass . '.php'))
				{
					include_once $path;
				}
			}

			if (!class_exists($paramsclass)){
				jimport('joomfish.translateparams.translateparams');
				$paramsclass = "TranslateParams";
			}

		return new $paramsclass($original, $translation, $fieldname, $fields);
		
	}


	public function editTranslation()
	{
		$returnval = array("editor_" . $this->fieldname, "refField_" . $this->fieldname);
		// parameters : areaname, content, hidden field, width, height, rows, cols
		 JFactory::getEditor()->display("editor_" . $this->fieldname, $this->transparams, "refField_" . $this->fieldname, '100%;', '300', '70', '15');
		echo $this->transparams;
		return $returnval;

	}
	
	protected function _nodesort(&$nodes, $order = SORT_ASC)
	{
		$final = array();
	
		/*$sort_proxy = array();
	
		foreach ($nodes as $k => $node)
		{
		$sort_proxy[$k] = (string) $node->attributes()->name;
		}
	
		array_multisort($sort_proxy, $order, $nodes);*/
	
		foreach ($nodes as $k => $node)
		{
			$name 		= (string) $node->attributes()->name;
			if (strstr($name, "_orig")) break; // originals come after this point
			$final[] 	= $node; // translation
			foreach ($nodes as $f => $fnode) {
				$fname 		= (string) $fnode->attributes()->name;
				if (!strstr($fname, "_orig")) continue;
				if ($fname == $name.'_orig') { // original
					$final[] 	= $fnode;
				}
			}
		}
		$nodes = $final;
	
	}

}
