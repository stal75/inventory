<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla modelitem library
jimport('joomla.application.component.modelitem');

/**
 * HelloWorld Model
*/
class StalramsModelBuild extends JModelLegacy
{
	
	protected $msg;
 
	function mod_name(){
		return 'Модель строителя';
	}
	
	function log($msg)
	{
		$user = &JFactory::getUser();
	
		$db = JFactory::getDbo();
		$db->setQuery("INSERT INTO #__neq_log (username, msg) VALUES ('".$user->name."','".$msg."')");
		$db->query();
	}
	
	function getRegions() {//Получаем список регионов

		$db = JFactory::getDBO();// Подключаемся к базе.
		$query = "SELECT id AS value, Name AS text FROM #__neq_region ORDER BY 'text' ASC";
		$db->setQuery($query);//Выполняем запрос
	
		
		if ($rList = $db->loadObjectList()) {
			return $rList;
		}
		else {
			return $db->stderr();
		}
	}
	
	function getBases() {
	
		$db = JFactory::getDBO();// Подключаемся к базе.
		$query = "SELECT id AS value, name AS text FROM #__neq_pes WHERE regid = 1 ORDER BY text";//Определяем запрос
		$db->setQuery($query);//Выполняем запрос
	
		if ($rList = $db->loadObjectList()) {
			return $rList;
		}
		else {
			return $db->stderr();
		}
	}
	
	function getPo($basetype, $region) {
	
		$basetype = 1;
		$region = 1;
		$db = JFactory::getDBO();// Подключаемся к базе.
		$query = "SELECT name AS text, number AS value FROM #__neq_po WHERE basetype = ".$basetype." AND pes = ".$region." ORDER BY text";//Выбираем объекты в регионе
		echo $_REQUEST['login'];
		$db->setQuery($query);//Выполняем запрос
	
		if ($rList = $db->loadObjectList()) {
			return $rList;
		}
		else {
			return $db->stderr();
		}
	}
	
	function getBase() {
	
		$db = JFactory::getDBO();// Подключаемся к базе.
		$query = "SELECT code AS value, name AS text FROM #__neq_base ORDER BY text ASC";//Запрос типов и кодов объектов
		$db->setQuery($query);//Выполняем запрос
	
		if ($rList = $db->loadObjectList()) {
			return $rList;
		}
		else {
			return $db->stderr();
		}
	}
			
	function getTypes() {
	
		$db = JFactory::getDBO();// Подключаемся к базе.
		$query = "SELECT #__neq_type.name AS text, #__neq_type.code AS value, #__neq_category.name AS category, #__neq_type.img AS img 
				  FROM #__neq_type
				  INNER JOIN #__neq_category ON #__neq_type.catcode = #__neq_category.id
				  ORDER BY text";//Запрос типов и кодов оборудования
		$db->setQuery($query);//Выполняем запрос
	
		if ($rList = $db->loadObjectList()) {
			return $rList;
		}
		else {
			return $db->stderr();
		}
	}
	public function getTable($type = 'Equipment', $prefix = 'EquipmentTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}

}