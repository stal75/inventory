<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
JHtml::_('jquery.framework');

$document = &JFactory::getDocument();
$baseurl = JURI::base();
//$document->addScript($baseurl . "components/com_stalrams/js/encod.js");

//JHtml::_('jquery.framework', false);

 class StalramsViewBuild extends JViewLegacy
{
	public $lists = array();
	
	// Overwriting JView display method
	function display($tpl = null) 
	{
		
		echo 'asdfg';
		
		parent::display($tpl);
	}
}