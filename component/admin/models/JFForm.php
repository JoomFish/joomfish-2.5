<?php
/**
*/
// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.form.form' );


/**

 */
class JFForm extends JForm {

	//protected $trans_form;
	
	public function __construct($name, array $options = array(),$childForm=null)
	{
		parent::__construct($name, $options);
		if(isset($options['childForm']))//$childForm)
		{
			$childForm = $options['childForm'];
			if($jform = self::$forms[$childForm->getName()])
			{
				//$this->trans_form = $jform; 
				//$this->data = $jform->data; //???
				//$this->xml = $jform->xml;
				
				$this->load($jform->xml);
			}
		}
	}
}

?>