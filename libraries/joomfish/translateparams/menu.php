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

class TranslateParams_menu extends TranslateParams_xml
{

	var $_menutype;
	var $_menuViewItem;
	var $orig_modelItem;
	var $trans_modelItem;

	function __construct($original, $translation, $fieldname, $fields=null)
	{
		
		$options 			= array();
		$options['option']	= 'com_menus';
		$options['ident']	= 'id';
		$options['state_ident']	= 'item.id';
		$options['model_item']	= 'JFMenusModelItem';
		$options['trans_params']	= 'JFMenuParams';
		$options['params_fieldset']	= 'params';
		
		return $this->setup($original, $translation, $fieldname, $fields, $options);
		
		
		/*parent::__construct($original, $translation, $fieldname, $fields);
		$lang = JFactory::getLanguage();
		$lang->load("com_menus", JPATH_ADMINISTRATOR);

		$cid = JRequest::getVar('cid', array(0));
		$oldcid = $cid;
		$translation_id = 0;
		if (strpos($cid[0], '|') !== false)
		{
			list($translation_id, $contentid, $language_id) = explode('|', $cid[0]);
		}

		JRequest::setVar("cid", array($contentid));
		JRequest::setVar("edit", true);

		JLoader::import('models.JFMenusModelItem', JOOMFISH_ADMINPATH);
		$this->orig_modelItem = new JFMenusModelItem();


		// Get The Original State Data
		// model's populate state method assumes the id is in the request object!
		$oldid = JRequest::getInt("id", 0);
		JRequest::setVar("id", $contentid);
		// JRequest does this for us!
		//$this->orig_modelItem->setState('item.id',$contentid);
		$jfMenuModelForm = $this->orig_modelItem->getForm();

		// NOW GET THE TRANSLATION - IF AVAILABLE
		$this->trans_modelItem = new JFMenusModelItem();
		$this->trans_modelItem->setState('item.id', $contentid);
		if ($translation != "")
		{
			$translation = json_decode($translation);
		}
		$translationMenuModelForm = $this->trans_modelItem->getForm();
		if (isset($translation->jfrequest)){
			$translationMenuModelForm->bind(array("params" => $translation, "request" =>$translation->jfrequest));
		}
		else {
			$translationMenuModelForm->bind(array("params" => $translation));
		}

		$cid = $oldcid;
		JRequest::setVar('cid', $cid);
		JRequest::setVar("id", $oldid);

		//	$this->origparams = new JFMenuParams( $jfMenuModelForm);
		$this->transparams = new JFMenuParams($translationMenuModelForm);*/

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

class JFMenuParams extends JObject
{

	protected $form = null;

	function __construct($form=null, $item=null)
	{
		$this->form = $form;

	}

	function render($type)
	{

		$sliders = JPane::getInstance('sliders');
		echo $sliders->startPane('params');

		$fieldSets = $this->form->getFieldsets('request');
		if ($fieldSets)
		{
			foreach ($fieldSets as $name => $fieldSet)
			{
				$hidden_fields = '';
				$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_MENUS_' . $name . '_FIELDSET_LABEL';
				echo $sliders->startPanel(JText::_($label), $name . '-options');

				if (isset($fieldSet->description) && trim($fieldSet->description)) :
				echo '<p class="tip">' . $this->escape(JText::_($fieldSet->description)) . '</p>';
				endif;
				?>
				<div class="clr"></div>
				<fieldset class="panelform">
					<ul class="adminformlist">
						<?php foreach ($this->form->getFieldset($name) as $field)
						{ ?>
							<?php if (!$field->hidden)
							{
								echo $field->value;
								?>
								<li><?php echo $field->label; ?>
									<?php echo $field->input; ?></li>
								<?php
							}
							else
							{
								$hidden_fields.= $field->input;
								?>
							<?php } ?>

						<?php } ?>
					</ul>
					<?php echo $hidden_fields; ?>
				</fieldset>

				<?php
				echo $sliders->endPanel();
			}
		}
		
		TranslateParams_xml::renderDoublecolumnParams ($this->form, 'params', 'com_menus', $sliders);
		
		echo $sliders->endPane();
		return;

	}

}
