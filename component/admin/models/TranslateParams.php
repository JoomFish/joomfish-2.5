<?php
/**
 * Joom!Fish - Multi Lingual extention and translation manager for Joomla!
 * Copyright (C) 2003 - 2011, Think Network GmbH, Munich
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
 * $Id: TranslateParams.php 225M 2011-05-26 16:40:14Z (local) $
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

		$fieldSets = $this->form->getFieldsets('params');
		$sliders = & JPane::getInstance('sliders');
		echo $sliders->startPane('params');
		foreach ($fieldSets as $name => $fieldSet)
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
		echo $sliders->endPane();
		return;

	}

}

class TranslateParams_menu extends TranslateParams_xml
{

	var $_menutype;
	var $_menuViewItem;
	var $orig_menuModelItem;
	var $trans_menuModelItem;

	function TranslateParams_menu($original, $translation, $fieldname, $fields=null)
	{
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
		$this->orig_menuModelItem = new JFMenusModelItem();


		// Get The Original State Data
		// model's populate state method assumes the id is in the request object!
		$oldid = JRequest::getInt("id", 0);
		JRequest::setVar("id", $contentid);
		// JRequest does this for us!
		//$this->orig_menuModelItem->setState('item.id',$contentid);
		$jfMenuModelForm = $this->orig_menuModelItem->getForm();

		// NOW GET THE TRANSLATION - IF AVAILABLE
		$this->trans_menuModelItem = new JFMenusModelItem();
		$this->trans_menuModelItem->setState('item.id', $contentid);
		if ($translation != "")
		{
			$translation = json_decode($translation);
		}
		$translationMenuModelForm = $this->trans_menuModelItem->getForm();
		$translationMenuModelForm->bind(array("params" => $translation));

		// NOW GET THE Default- IF AVAILABLE
		//	$this->default_menuModelItem = new JFDefaultMenusModelItem();
		//$this->default_menuModelItem->setTranslation(false);
		//	$defaultMenuModelForm =$this->default_menuModelItem->getForm();
		// reset old values in REQUEST array
		$cid = $oldcid;
		JRequest::setVar('cid', $cid);
		JRequest::setVar("id", $oldid);

		//	$this->origparams = new JFMenuParams( $jfMenuModelForm);
		$this->transparams = new JFMenuParams($translationMenuModelForm);

		// This is tricky!!
		//	$this->defaultparams = new JFMenuParams( $defaultMenuModelForm);

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

class TranslateParams_modules extends TranslateParams_xml
{

	function TranslateParams_modules($original, $translation, $fieldname, $fields=null)
	{

		$this->fieldname = $fieldname;
		$module = null;
		foreach ($fields as $field)
		{
			if ($field->Name == "module")
			{
				$module = $field->originalValue;
				break;
			}
		}
		if (is_null($module))
		{
			echo JText::_('PROBLEMS_WITH_CONTENT_ELEMENT_FILE');
			exit();
		}
		$lang = JFactory::getLanguage();
		$lang->load($module, JPATH_SITE);

		// xml file for module
		if ($module == 'custom')
		{
			$xmlfile = JApplicationHelper::getPath('mod0_xml', 'mod_custom');
		}
		else
		{
			$xmlfile = JApplicationHelper::getPath('mod0_xml', $module);
		}

		$this->origparams = new JParameter($original, $xmlfile, 'module');
		$this->transparams = new JParameter($translation, $xmlfile, 'module');
		$this->defaultparams = new JParameter("", $xmlfile, 'component');
		$this->fields = $fields;

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

	function showDefault()
	{
		parent::showDefault();

		$output = "<span style='display:none'>";
		if ($this->origparams->getNumParams('advanced'))
		{
			$fieldname = 'defaultvalue_' . $this->fieldname;
			$output .= $this->defaultparams->render($fieldname, 'advanced');
		}
		if ($this->origparams->getNumParams('other'))
		{
			$fieldname = 'defaultvalue_' . $this->fieldname;
			$output .= $this->defaultparams->render($fieldname, 'other');
		}
		if ($this->origparams->getNumParams('legacy'))
		{
			$fieldname = 'defaultvalue_' . $this->fieldname;
			$output .= $this->defaultparams->render($fieldname, 'legacy');
		}
		$output .= "</span>\n";
		echo $output;

	}

	function editTranslation()
	{
		parent::editTranslation();

		$output = "";
		if ($this->origparams->getNumParams('advanced'))
		{
			$fieldname = 'refField_' . $this->fieldname;
			$output .= $this->transparams->render($fieldname, 'advanced');
		}
		if ($this->origparams->getNumParams('other'))
		{
			$fieldname = 'refField_' . $this->fieldname;
			$output .= $this->transparams->render($fieldname, 'other');
		}
		if ($this->origparams->getNumParams('legacy'))
		{
			$fieldname = 'refField_' . $this->fieldname;
			$output .= $this->transparams->render($fieldname, 'legacy');
		}
		echo $output;

	}

}

class TranslateParams_content extends TranslateParams_xml
{

	function TranslateParams_content($original, $translation, $fieldname, $fields=null)
	{

		$this->fieldname = $fieldname;
		$content = null;
		foreach ($fields as $field)
		{
			if ($field->Type == "params")
			{
				$content = $field->originalValue;
				break;
			}
		}
		if (is_null($content))
		{
			echo JText::_('PROBLEMS_WITH_CONTENT_ELEMENT_FILE');
			exit();
		}
		$lang = JFactory::getLanguage();
		$lang->load("com_content", JPATH_SITE);

		$this->origparams = new JParameter($original, JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_content' . DS . 'models' . DS . 'article.xml');
		$this->transparams = new JParameter($translation, JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_content' . DS . 'models' . DS . 'article.xml');
		$this->defaultparams = new JParameter("", JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_content' . DS . 'models' . DS . 'article.xml');
		$this->fields = $fields;

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

	function showDefault()
	{
		parent::showDefault();

		$output = "<span style='display:none'>";
		if ($this->origparams->getNumParams('advanced'))
		{
			$fieldname = 'defaultvalue_' . $this->fieldname;
			$output .= $this->defaultparams->render($fieldname, 'advanced');
		}
		if ($this->origparams->getNumParams('legacy'))
		{
			$fieldname = 'defaultvalue_' . $this->fieldname;
			$output .= $this->defaultparams->render($fieldname, 'legacy');
		}
		$output .= "</span>\n";
		echo $output;

	}

	function editTranslation()
	{
		parent::editTranslation();

		$output = "";
		if ($this->origparams->getNumParams('advanced'))
		{
			$fieldname = 'refField_' . $this->fieldname;
			$output .= $this->transparams->render($fieldname, 'advanced');
		}
		if ($this->origparams->getNumParams('legacy'))
		{
			$fieldname = 'refField_' . $this->fieldname;
			$output .= $this->transparams->render($fieldname, 'legacy');
		}
		echo $output;

	}

}

class TranslateParams_components extends TranslateParams_xml
{

	var $_menutype;
	var $_menuViewItem;
	var $orig_menuModelItem;
	var $trans_menuModelItem;

	function TranslateParams_components($original, $translation, $fieldname, $fields=null)
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

		$this->origparams = new JParameter($original, $xmlfile, "component");
		$this->transparams = new JParameter($translation, $xmlfile, "component");
		$this->defaultparams = new JParameter("", $xmlfile, "component");
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
