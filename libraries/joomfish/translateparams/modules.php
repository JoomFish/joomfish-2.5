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


class TranslateParams_modules extends TranslateParams_xml
{

	function __construct($original, $translation, $fieldname, $fields=null)
	{	
		$options 			= array();
		$options['option']	= 'com_modules';
		$options['ident']	= 'id';
		$options['state_ident']	= 'module.id';
		$options['model_item']	= 'JFModuleModelItem';
		$options['trans_params']	= 'JFModuleParams';
		$options['params_fieldset']	= 'params';
		
		$this->setup($original, $translation, $fieldname, $fields, $options);
		$this->transparams->item = $this->trans_ModelItem->getItem();
		return;
		
		/*parent::__construct($original, $translation, $fieldname, $fields);
		$lang = JFactory::getLanguage();
		$lang->load("com_modules", JPATH_ADMINISTRATOR);

		$cid = JRequest::getVar('cid', array(0));
		$oldcid = $cid;
		$translation_id = 0;
		if (strpos($cid[0], '|') !== false)
		{
			list($translation_id, $contentid, $language_id) = explode('|', $cid[0]);
		}

		// if we have an existing translation then load this directly!
		// This is important for modules to populate the assignement fields 
		$contentid = $translation_id?$translation_id : $contentid;
		
		JRequest::setVar("cid", array($contentid));
		JRequest::setVar("edit", true);

		JLoader::import('models.JFModuleModelItem', JOOMFISH_ADMINPATH);

		// Get The Original State Data
		// model's populate state method assumes the id is in the request object!
		$oldid = JRequest::getInt("id", 0);
		JRequest::setVar("id", $contentid);

		// NOW GET THE TRANSLATION - IF AVAILABLE
		$this->trans_modelItem = new JFModuleModelItem();
		$this->trans_modelItem->setState('module.id', $contentid);
		if ($translation != "")
		{
			$translation = json_decode($translation);
		}
		$translationModuleModelForm = $this->trans_modelItem->getForm();
		if (isset($translation->jfrequest)){
			$translationModuleModelForm->bind(array("params" => $translation, "request" =>$translation->jfrequest));
		}
		else {
			$translationModuleModelForm->bind(array("params" => $translation));
		}

		$cid = $oldcid;
		JRequest::setVar('cid', $cid);
		JRequest::setVar("id", $oldid);

		$this->transparams = new JFModuleParams($translationModuleModelForm, $this->trans_modelItem->getItem());*/

	}

}


class JFModuleParams extends JObject
{

	protected $form = null;
	public $item = null;

	function __construct($form=null, $item=null)
	{
		$this->form = $form;
		//$this->item = $item;
	}

	function render($type)
	{
		$sliders = & JPane::getInstance('sliders');
		echo $sliders->startPane('params');

		TranslateParams_xml::renderDoublecolumnParams ($this->form, 'params', 'com_modules', $sliders);
		echo $sliders->endPane();
		
		
		// menu assignments
		// Initiasile related data.
		if (!class_exists('MenusHelper')) {
				JLoader::register('MenusHelper', JPATH_ADMINISTRATOR.'/components/com_menus/helpers/menus.php', true);	
		}
		
		if (!class_exists('ModulesHelper')) {
			JLoader::register('ModulesHelper', JPATH_ADMINISTRATOR.'/components/com_modules/helpers/modules.php', true);
		}
		
		$menuTypes = MenusHelper::getMenuLinks();
		?>
		<script type="text/javascript">
			window.addEvent('domready', function(){
				validate();
				document.getElements('select').addEvent('change', function(e){validate();});
			});
			function validate(){
				var value	= document.id('jform_assignment').value;
				var list	= document.id('menu-assignment');
				if(value == '-' || value == '0'){
					$$('.jform-assignments-button').each(function(el) {el.setProperty('disabled', true); });
					list.getElements('input').each(function(el){
						el.setProperty('disabled', true);
						if (value == '-'){
							el.setProperty('checked', false);
						} else {
							el.setProperty('checked', true);
						}
					});
				} else {
					$$('.jform-assignments-button').each(function(el) {el.setProperty('disabled', false); });
					list.getElements('input').each(function(el){
						el.setProperty('disabled', false);
					});
				}
			}
		</script>
		<div class="width-100 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_MODULES_MENU_ASSIGNMENT'); ?></legend>
			<label id="jform_menus-lbl" for="jform_menus"><?php echo JText::_('COM_MODULES_MODULE_ASSIGN'); ?></label>

			<fieldset id="jform_menus" class="radio">
				<select name="jform[assignment]" id="jform_assignment">
					<?php echo JHtml::_('select.options', ModulesHelper::getAssignmentOptions($this->item->client_id), 'value', 'text', $this->item->assignment, true);?>
				</select>

			</fieldset>

			<label id="jform_menuselect-lbl" for="jform_menuselect"><?php echo JText::_('JGLOBAL_MENU_SELECTION'); ?></label>

			<button type="button" class="jform-assignments-button jform-rightbtn" onclick="$$('.chk-menulink').each(function(el) { el.checked = !el.checked; });">
				<?php echo JText::_('JGLOBAL_SELECTION_INVERT'); ?>
			</button>

			<button type="button" class="jform-assignments-button jform-rightbtn" onclick="$$('.chk-menulink').each(function(el) { el.checked = false; });">
				<?php echo JText::_('JGLOBAL_SELECTION_NONE'); ?>
			</button>

			<button type="button" class="jform-assignments-button jform-rightbtn" onclick="$$('.chk-menulink').each(function(el) { el.checked = true; });">
				<?php echo JText::_('JGLOBAL_SELECTION_ALL'); ?>
			</button>

			<div class="clr"></div>

			<div id="menu-assignment">

			<?php echo JHtml::_('tabs.start','module-menu-assignment-tabs', array('useCookie'=>1));?>

			<?php foreach ($menuTypes as &$type) :
				echo JHtml::_('tabs.panel', $type->title ? $type->title : $type->menutype, $type->menutype.'-details');

				$count 	= count($type->links);
				$i		= 0;
				if ($count) :
				?>
				<ul class="menu-links">
					<?php
					foreach ($type->links as $link) :
						if (trim($this->item->assignment) == '-'):
							$checked = '';
						elseif ($this->item->assignment == 0):
							$checked = ' checked="checked"';
						elseif ($this->item->assignment < 0):
							$checked = in_array(-$link->value, $this->item->assigned) ? ' checked="checked"' : '';
						elseif ($this->item->assignment > 0) :
							$checked = in_array($link->value, $this->item->assigned) ? ' checked="checked"' : '';
						endif;
					?>
					<li class="menu-link">
						<input type="checkbox" class="chk-menulink" name="jform[assigned][]" value="<?php echo (int) $link->value;?>" id="link<?php echo (int) $link->value;?>"<?php echo $checked;?>/>
						<label for="link<?php echo (int) $link->value;?>">
							<?php echo $link->text; ?>
						</label>
					</li>
					<?php if ($count > 20 && ++$i == ceil($count/2)) :?>
					</ul><ul class="menu-links">
					<?php endif; ?>
					<?php endforeach; ?>
				</ul>
				<div class="clr"></div>
				<?php endif; ?>
			<?php endforeach; ?>

			<?php echo JHtml::_('tabs.end');?>

			</div>
		</fieldset>
		</div>
		<?php		
		return;

	}

}
