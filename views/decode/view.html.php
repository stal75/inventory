<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 * HTML View class for the HelloWorld Component
 */
class StalramsViewDecode extends JViewLegacy
{
	public $a = 'переменная';
	// Overwriting JView display method
	function display($tpl = null) 
	{
		
		$model = $this->getModel();
	
		
		//if (JRequest::getVar('name_eq') != ''){
		$str = $model->razborEq(JRequest::getVar('name_eq'));
		if (JRequest::getVar('hide_eq') == '1') $str = $model->razborEq_spec(JRequest::getVar('name_eq'));
		//$str = $str + $model->razborEq(JRequest::getVar('name_eq'));
		$this->str= $str;
		//$this->str=$model->razborEq(JRequest::getVar('name_eq')) + $model->razborEq_spec(JRequest::getVar('name_eq'));
		//$this->str=$this->str + $model->razborEq_spec(JRequest::getVar('name_eq'));
		$this->name = JRequest::getVar('name_eq');
		//}
		//else JRequest::setVar('name_eq', 'Введите именование!');
		
		
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
				
		parent::display($tpl);
	}
}