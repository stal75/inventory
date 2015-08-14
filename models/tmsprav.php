<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla modelitem library
jimport('joomla.application.component.modelitem');

/**
 * HelloWorld Model
*/
class StalramsModelTmsprav extends JModelLegacy
{

	protected $msg;

	function mod_name(){
		return 'Модель строителя';
	}

	function getRegions() {//Получаем список регионов

		$db = JFactory::getDBO();// Подключаемся к базе.
		$query = "SELECT id AS value, Name AS text FROM #__tm_region ORDER BY text ASC";
		$db->setQuery($query);//Выполняем запрос


		if ($rList = $db->loadObjectList()) {
			return $rList;
		}
		else {
			return $db->stderr();
		}
	}
	
	function getPO($region) {//Получаем список производственных отделений в регионах
		
		$region = $this->getState('region', 'state test');;
	
		$db = JFactory::getDBO();// Подключаемся к базе.
		$query = "SELECT code AS value, name AS text FROM #__tm_pes WHERE id_reg = ".$region." ORDER BY text";//Определяем запрос
		$db->setQuery($query);//Выполняем запрос

		if ($rList = $db->loadObjectList()) {
			return $rList;
		}
		else {
			return $db->stderr();
		}
	}

}