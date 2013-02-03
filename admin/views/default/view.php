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
 * @package joomfish
 * @subpackage Views
 *
*/
// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');
jimport('joomla.html.pane');

/**
 * Method to translate with a pre_reg function call
 *
 * @param array $matches
 * @return string
 */
function jfTranslate($matches){
	$translation = '!!!' .JText::_($matches[1]);
	return $translation;
}


/**
 * HTML Abstract View class for the Joomfish never used directly
 *
 * @since 2.0
 */
class JoomfishViewDefault extends JView {
	protected $showVersion = true;
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->_addPath('template', $this->_basePath.DS.'views'.DS.'default'.DS.'tmpl');
	}

	public function display($tpl=null)
	{
		$document = JFactory::getDocument();
		$livesite = JURI::base();
		$document->addStyleSheet($livesite.'components/com_joomfish/assets/css/joomfish.css');
		JHTML::script('com_joomfish/joomfish.mootools.js', true, true);
		
		// Get data from the model
		$state		= $this->get('State');
		// Are there messages to display ?
		$showMessage	= false;
		$message = $this->get('message');
				
		if ( is_object($state) )
		{
			$message1		= $state->get('message') == null ? $message : $state->get('message');
			$state->set('message', $message1);
			$message2		= $state->get('extension.message');
			$showMessage	= ( $message1 || $message2 );
		}

		$this->assign('showMessage',	$showMessage);
		$this->assignRef('state',		$state);

		JHTML::_('behavior.tooltip');
		parent::display($tpl);

		$this->versionInfo();
	}

	public function versionInfo(){
		if ($this->showVersion){
		$version = new JoomFishVersion();
		?><div align="center"><span class="smallgrey">Joom!Fish Version <?php echo $version->getVersion() .', '. $version->getCopyright();?> Copyright by <a href="http://www.ThinkNetwork.com" target="_blank" class="smallgrey">Think Network</a> and <a href="http://www.joomfish.net/en/the-project/the-team" target="_blank" class="smallgrey">other contributors</a>, all rights reserved.</span></div>
	<?php		
		}
	}
	
	/** 
	 * Returns the path of the help file to be included as output for the page
	 * The path is used as include statement within the view template  
	 */
	protected function getHelpPathL($ref) {
		$lang = JFactory::getLanguage();
		if (!preg_match( '#\.html$#i', $ref )) {
			$ref = $ref . '.html';
		}

		$url = 'components/com_joomfish/help';
		$tag =  $lang->getTag();

		// Check if the file exists within a different language!
		if( $lang->getTag() != 'en-GB' ) {
			$localeURL = JPATH_BASE.DS.$url.DS.$tag.DS.$ref;
			jimport( 'joomla.filesystem.file' );
			if( !JFile::exists( $localeURL ) ) {
				$tag = 'en-GB';
			}
		}
		return $url.'/'.$tag.'/'.$ref;
		
	}
	
	/**
	 * Routine to hide submenu suing CSS since there are no paramaters for doing so without hiding the main menu
	 *
	 */
	protected function _hideSubmenu(){
		JHTML::stylesheet( 'hidesubmenu.css', 'administrator/components/com_joomfish/assets/css/' );	 	
	}

	 /**
	 * This method creates a standard cpanel button
	 *
	 * @param string $link
	 * @param string $image
	 * @param string $text
	 * @param string $path
	 * @param string $target
	 * @param string $onclick
	 * @access protected
	 */
	 protected function _quickiconButton( $link, $image, $text, $path=null, $target='', $onclick='' ) {
	 	if( $target != '' ) {
	 		$target = 'target="' .$target. '"';
	 	}
	 	if( $onclick != '' ) {
	 		$onclick = 'onclick="' .$onclick. '"';
	 	}
	 	if( $path === null || $path === '' ) {
	 		$path = 'components/com_joomfish/assets/images/';
	 	}
		?>
		<div style="float:left;">
			<div class="icon">
				<a href="<?php echo $link; ?>" <?php echo $target;?>  <?php echo $onclick;?>>
					<?php echo JHTML::_('image.administrator', $image, $path, NULL, NULL, $text ); ?>
					<span><?php echo $text; ?></span>
				</a>
			</div>
		</div>
		<?php
	 }
	 
	/**
	 * Method to use a tooltip independ from JElements
	 *
	 * @param string $label	title of the label
	 * @param string $description	of the label
	 * @param string $control_name	name of the control the label is related to
	 * @param string $name	of the control
	 * @return string
	 */
	protected function fetchTooltip($label, $description, $control_name='', $name='')
	{
		$output = '<label id="'.$control_name.$name.'-lbl" for="'.$control_name.$name.'"';
		if ($description) {
			$output .= ' class="hasTip" title="'.JText::_($label).'::'.JText::_($description).'">';
		} else {
			$output .= '>';
		}
		$output .= JText::_( $label ).'</label>';

		return $output;
	}
	
	private function showSplashInfo() {
		JHTML::_('behavior.modal');
		
/*		
		SqueezeBox.initialize({});
		SqueezeBox.setOptions(SqueezeBox.presets,{'handler': 'iframe','size': {'x': 1000, 'y': 600},'closeWithOverlay': 0});
		SqueezeBox.url = target;

		SqueezeBox.setContent('iframe', SqueezeBox.url );*/
	}
}
?>
