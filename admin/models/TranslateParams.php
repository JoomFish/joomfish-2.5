<?php
/**
 * Joom!Fish - Multi Lingual extention and translation manager for Joomla!
 * Copyright (C) 2003 - 2012, Think Network GmbH, Munich
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

	public function showOriginal()
	{
		echo $this->origparams;

	}

	public function showDefault()
	{
		echo "";

	}

	public function editTranslation()
	{
		$returnval = array("editor_" . $this->fieldname, "refField_" . $this->fieldname);
		// parameters : areaname, content, hidden field, width, height, rows, cols
		editorArea("editor_" . $this->fieldname, $this->transparams, "refField_" . $this->fieldname, '100%;', '300', '70', '15');
		echo $this->transparams;
		return $returnval;

	}

}

class TranslateParams_xml extends TranslateParams
{

	function showOriginal()
	{
		$output = "";
		$fieldname = 'orig_' . $this->fieldname;
		$output .= $this->origparams->render($fieldname);
		$output .= <<<SCRIPT
		<script language='javascript'>
		function copyParams(srctype, srcfield){
			var orig = document.getElementsByTagName('select');		
			for (var i=0;i<orig.length;i++){
				if (orig[i].name.indexOf(srctype)>=0 && orig[i].name.indexOf("[")>=0){
					// TODO double check the str replacement only replaces one instance!!!
					targetName = orig[i].name.replace(srctype,"refField");					
					target = document.getElementsByName(targetName);
					if (target.length!=1){
						alert(targetName+" problem "+target.length);
					}
					else {
						target[0].selectedIndex = orig[i].selectedIndex;
					}
				}
			}
			var orig = document.getElementsByTagName('input');		
			for (var i=0;i<orig.length;i++){
				if (orig[i].name.indexOf(srctype)>=0 && orig[i].name.indexOf("[")>=0){				
					// treat radio buttons differently 
					if (orig[i].type.toLowerCase()=="radio"){
						//alert( orig[i].id+" "+orig[i].checked);
						targetId = orig[i].id;
						if (targetId){
							targetId = targetId.replace(srctype,"refField");
							target = document.getElementById(targetId);
							if (!target){
								alert("missing target for radio button "+orig[i].name);
							}
							else {
								target.checked = orig[i].checked;
							}
						}
						else {
							alert("missing id for radio button "+orig[i].name);
						}
					}
					else {
						// TODO double check the str replacement only replaces one instance!!!
						targetName = orig[i].name.replace(srctype,"refField");
						target = document.getElementsByName(targetName);
						if (target.length!=1){
							alert(targetName+" problem "+target.length);
						}
						else {
							target[0].value = orig[i].value;
						}
					}
				}
			}		   
			var orig = document.getElementsByTagName('textarea');		
			for (var i=0;i<orig.length;i++){
				if (orig[i].name.indexOf(srctype)>=0 && orig[i].name.indexOf("[")>=0){				
					// TODO double check the str replacement only replaces one instance!!!
					targetName = orig[i].name.replace(srctype,"refField");
					target = document.getElementsByName(targetName);
					if (target.length!=1){
						alert(targetName+" problem "+target.length);
					}
					else {
						target[0].value = orig[i].value;
					}
				}
			}		   
		}
		
		var orig = document.getElementsByTagName('select');		
		for (var i=0;i<orig.length;i++){
			if (orig[i].name.indexOf("$fieldname")>=0){
				orig[i].disabled = true;
			}
		}
		var orig = document.getElementsByTagName('input');		
		for (var i=0;i<orig.length;i++){
			if (orig[i].name.indexOf("$fieldname")>=0){
				orig[i].disabled = true;
			}
		}
		</script>
SCRIPT;
		echo $output;

	}

	function showDefault()
	{
		$output = "<span style='display:none'>";
		$output .= $this->defaultparams->render("defaultvalue_" . $this->fieldname);
		$output .= "</span>\n";
		echo $output;

	}

	function editTranslation()
	{
		echo $this->transparams->render("refField_" . $this->fieldname);

		return false;

	}

}

class JFMenuParams extends JObject
{

	var $form = null;

	function __construct($form=null, $item=null)
	{
		$this->form = $form;

	}

	function render($type)
	{
		$this->menuform = $this->form;
//		var_dump($this->menuform );
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

		$paramsfieldSets = $this->form->getFieldsets('params');
		if ($paramsfieldSets)
		{
			foreach ($paramsfieldSets as $name => $fieldSet)
			{
				$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_MENUS_' . $name . '_FIELDSET_LABEL';
				echo $sliders->startPanel(JText::_($label), $name . '-options');

				if (isset($fieldSet->description) && trim($fieldSet->description)) :
					echo '<p class="tip">' . $this->escape(JText::_($fieldSet->description)) . '</p>';
				endif;
				?>
				<div class="clr"></div>
				<fieldset class="panelform">
					<ul class="adminformlist">
						<?php foreach ($this->form->getFieldset($name) as $field) : ?>
							<li><?php echo $field->label; ?>
								<?php echo $field->input; ?></li>
						<?php endforeach; ?>
					</ul>
				</fieldset>

				<?php
				echo $sliders->endPanel();
			}
		}
		echo $sliders->endPane();
		return;

	}

}

class TranslateParams_menu extends TranslateParams_xml
{

	var $_menutype;
	var $_menuViewItem;
	var $orig_modelItem;
	var $trans_modelItem;

	function __construct($original, $translation, $fieldname, $fields=null)
	{
		parent::__construct($original, $translation, $fieldname, $fields);
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
		$this->transparams = new JFMenuParams($translationMenuModelForm);

	}

	function showOriginal()
	{
		if ($this->_menutype == "wrapper")
		{
			?>
			<table width="100%" class="paramlist">
				<tr>
					<td width="40%" align="right" valign="top"><span class="editlinktip"><!-- Tooltip -->
							<span onmouseover="return overlib('Link for Wrapper', CAPTION, 'Wrapper Link', BELOW, RIGHT);" onmouseout="return nd();" >Wrapper Link</span></span></td>

					<td align="left" valign="top"><input type="text" name="orig_params[url]" value="<?php echo $this->origparams->get('url', '') ?>" class="text_area" size="30" /></td>
				</tr>
			</table>
			<?php
		}
		parent::showOriginal();

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


class JFModuleParams extends JObject
{

	protected $form = null;
	protected $item = null;

	function __construct($form=null, $item=null)
	{
		$this->form = $form;
		$this->item = $item;

	}

	function render($type)
	{
		$sliders = & JPane::getInstance('sliders');
		echo $sliders->startPane('params');
		
		$paramsfieldSets = $this->form->getFieldsets('params');
		if ($paramsfieldSets)
		{
			foreach ($paramsfieldSets as $name => $fieldSet)
			{
				$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_MODULES_' . $name . '_FIELDSET_LABEL';
				echo $sliders->startPanel(JText::_($label), $name . '-options');

				if (isset($fieldSet->description) && trim($fieldSet->description)) :
					echo '<p class="tip">' . $this->escape(JText::_($fieldSet->description)) . '</p>';
				endif;
				?>
				<div class="clr"></div>
				<fieldset class="panelform">
					<ul class="adminformlist">
						<?php foreach ($this->form->getFieldset($name) as $field) : ?>
							<li><?php echo $field->label; ?>
								<?php echo $field->input; ?></li>
						<?php endforeach; ?>
					</ul>
				</fieldset>

				<?php
				echo $sliders->endPanel();
			}
		}
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
		<?php		
		return;

	}

}

class TranslateParams_modules extends TranslateParams_xml
{

	function __construct($original, $translation, $fieldname, $fields=null)
	{

		parent::__construct($original, $translation, $fieldname, $fields);
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

		$this->transparams = new JFModuleParams($translationModuleModelForm, $this->trans_modelItem->getItem());

	}

	function showOriginal()
	{
		parent::showOriginal();

		$output = "";
		if ($this->origparams->getNumParams('advanced'))
		{
			$fieldname = 'orig_' . $this->fieldname;
			$output .= $this->origparams->render($fieldname, 'advanced');
		}
		if ($this->origparams->getNumParams('other'))
		{
			$fieldname = 'orig_' . $this->fieldname;
			$output .= $this->origparams->render($fieldname, 'other');
		}
		if ($this->origparams->getNumParams('legacy'))
		{
			$fieldname = 'orig_' . $this->fieldname;
			$output .= $this->origparams->render($fieldname, 'legacy');
		}
		echo $output;

	}


	function editTranslation()
	{
		parent::editTranslation();

	}

}

class JFContentParams extends JObject
{

	var $form = null;

	function __construct($form=null, $item=null)
	{
		$this->form = $form;

	}

	function render($type)
	{
		$sliders = & JPane::getInstance('sliders');
		echo $sliders->startPane('params');
		
		$paramsfieldSets = $this->form->getFieldsets('attribs');
		if ($paramsfieldSets)
		{
			foreach ($paramsfieldSets as $name => $fieldSet)
			{	
				if ($name == 'basic-limited') {
					continue;
				}
				$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_CONTENT_' . $name . '_FIELDSET_LABEL';
				if ($name == 'editorConfig') {
					$label = 'COM_CONTENT_SLIDER_EDITOR_CONFIG';
				}
				echo $sliders->startPanel(JText::_($label), $name . '-options');

				if (isset($fieldSet->description) && trim($fieldSet->description)) :
					echo '<p class="tip">' . $this->escape(JText::_($fieldSet->description)) . '</p>';
				endif;
				?>
				<div class="clr"></div>
				<fieldset class="panelform">
					<ul class="adminformlist">
						<?php foreach ($this->form->getFieldset($name) as $field) : ?>
							<li><?php echo $field->label; ?>
								<?php echo $field->input; ?></li>
						<?php endforeach; ?>
					</ul>
				</fieldset>

				<?php
				echo $sliders->endPanel();
			}
		}
		echo $sliders->endPane();
		return;

	}

}

class TranslateParams_content extends TranslateParams_xml
{

	var $orig_contentModelItem;
	var $trans_contentModelItem;
	
	function __construct($original, $translation, $fieldname, $fields=null)
	{

		parent::__construct($original, $translation, $fieldname, $fields);
		$lang = JFactory::getLanguage();
		$lang->load("com_content", JPATH_ADMINISTRATOR);

		$cid = JRequest::getVar('cid', array(0));
		$oldcid = $cid;
		$translation_id = 0;
		if (strpos($cid[0], '|') !== false)
		{
			list($translation_id, $contentid, $language_id) = explode('|', $cid[0]);
		}

		JRequest::setVar("cid", array($contentid));
		JRequest::setVar("edit", true);

		// model's populate state method assumes the id is in the request object!
		$oldid = JRequest::getInt("article_id", 0);
		// Take care of the name of the id for the item
		JRequest::setVar("article_id", $contentid);
		
		JLoader::import('models.JFContentModelItem', JOOMFISH_ADMINPATH);
		$this->orig_contentModelItem = new JFContentModelItem();

		// Get The Original form 
		// JRequest does NOT this for us in articles!!
		$this->orig_contentModelItem->setState('article.id',$contentid);
		$jfcontentModelForm = $this->orig_contentModelItem->getForm();

		// NOW GET THE TRANSLATION - IF AVAILABLE
		$this->trans_contentModelItem = new JFContentModelItem();
		$this->trans_contentModelItem->setState('article.id', $contentid);
		if ($translation != "")
		{
			$translation = json_decode($translation);
		}
		$translationcontentModelForm = $this->trans_contentModelItem->getForm();
		if (isset($translation->jfrequest)){
			$translationcontentModelForm->bind(array("attribs" => $translation, "request" =>$translation->jfrequest));
		}
		else {
			$translationcontentModelForm->bind(array("attribs" => $translation));
		}

		// reset old values in REQUEST array
		$cid = $oldcid;
		JRequest::setVar('cid', $cid);
		JRequest::setVar("article_id", $oldid);

		//	$this->origparams = new JFContentParams( $jfcontentModelForm);
		$this->transparams = new JFContentParams($translationcontentModelForm);


	}

	function showOriginal()
	{
		parent::showOriginal();

		$output = "";
		if ($this->origparams->getNumParams('advanced'))
		{
			$fieldname = 'orig_' . $this->fieldname;
			$output .= $this->origparams->render($fieldname, 'advanced');
		}
		if ($this->origparams->getNumParams('legacy'))
		{
			$fieldname = 'orig_' . $this->fieldname;
			$output .= $this->origparams->render($fieldname, 'legacy');
		}
		echo $output;

	}

	function editTranslation()
	{
		parent::editTranslation();
	}

}

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

	function showOriginal()
	{
		if ($this->_menutype == "wrapper")
		{
			?>
			<table width="100%" class="paramlist">
				<tr>
					<td width="40%" align="right" valign="top"><span class="editlinktip"><!-- Tooltip -->
							<span onmouseover="return overlib('Link for Wrapper', CAPTION, 'Wrapper Link', BELOW, RIGHT);" onmouseout="return nd();" >Wrapper Link</span></span></td>

					<td align="left" valign="top"><input type="text" name="orig_params[url]" value="<?php echo $this->origparams->get('url', '') ?>" class="text_area" size="30" /></td>
				</tr>
			</table>
			<?php
		}
		parent::showOriginal();

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
?>
