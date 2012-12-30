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

jimport('joomfish.translateparams.xml');

class TranslateParams_components extends TranslateParams_xml
{

	var $_menutype;
	var $_menuViewItem;
	var $orig_modelItem;
	var $trans_modelItem;

	function __construct($original, $translation, $fieldname, $fields=null)
	{
		$lang = JFactory::getLanguage();
		$lang->load("com_config", JPATH_ADMINISTRATOR);

		$this->fieldname = $fieldname;
		$content = null;
		foreach ($fields as $field)
		{
			if ($field->Name == "option")
			{
				$comp = $field->originalValue;
				break;
			}
		}
		$lang->load($comp, JPATH_ADMINISTRATOR);

		$path = DS . "components" . DS . $comp . DS . "config.xml";
		$xmlfile = JApplicationHelper::_checkPath($path);


		$this->origparams = new JRegistry($original);
		$this->origparams->loadFile($xmlfile, 'XML');

		$this->transparams = new JRegistry($translation);
		$this->transparams->loadFile($xmlfile, 'XML');
		$this->defaultparams = new JRegistry();
		$this->defaultparams->loadFile($xmlfile, 'XML');
		$this->fields = $fields;

	}


	function editTranslation()
	{
		if ($this->_menutype == "wrapper")
		{
			?>
			<table width="100%" class="paramlist">
				<tr>
					<td width="40%" align="right" valign="top"><span class="editlinktip"><!-- Tooltip -->
							<span onmouseover="return overlib('Link for Wrapper', CAPTION, 'Wrapper Link', BELOW, RIGHT);" onmouseout="return nd();" >Wrapper Link</span></span></td>
					<td align="left" valign="top"><input type="text" name="refField_params[url]" value="<?php echo $this->transparams->get('url', '') ?>" class="text_area" size="30" /></td>
				</tr>
			</table>
			<?php
		}
		parent::editTranslation();

	}

}

