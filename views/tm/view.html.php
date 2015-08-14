<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');

$document = &JFactory::getDocument();
$baseurl = JURI::base();
$document->addScript(JURI::base(true).'/components/com_stalrams/assets/js/translit.js');


class StalramsViewTm extends JViewLegacy
{
	public $lists = array();
	
	// Overwriting JView display method
	function display($tpl = null) 
	{
		
		$model = $this->getModel();//Получение модели		

		parent::display($tpl);
	}
}