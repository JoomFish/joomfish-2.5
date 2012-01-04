<?php
/**
*/
// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.form.form' );


/**
 class to have own formControl for originale and copy the xml from the translation Form (is created from models)
 
 */
class JFTranslateForm extends JForm {

	
	public function __construct($name, array $options = array(),$childForm=null)
	{
		parent::__construct($name, $options);
		if(isset($options['childForm']) )
		{
			$childForm = $options['childForm'];
			if($jform = self::$forms[$childForm->getName()])
			{
				$this->load($jform->xml->asXML()); //load the xml without reference
			}
		}
		$componentpath = JOOMFISH_ADMINPATH;
		//JForm::addFormPath($componentpath.'/models/forms');
		JForm::addFieldPath($componentpath.'/models/fields');
	}
}

?>