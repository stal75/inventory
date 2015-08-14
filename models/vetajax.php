<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla modelitem library
jimport('joomla.application.component.modelitem');

/**
 * HelloWorld Model
*/
class StalramsModelVetajax extends JModelLegacy
{
	function log($msg) //...................................... Функиция записи логов...................................
	{
		$user = &JFactory::getUser();
	
		$db = JFactory::getDbo();
		$db->setQuery("INSERT INTO #__inv_log (username, description) VALUES ('".$user->name."','".$msg."')");
		$db->query();
	}
	
	function getAccessUser() //...................................... Функиция проверки прав...................................
	{
	$user = &JFactory::getUser();

		$db = JFactory::getDBO();// Подключаемся к базе.
		$query = "SELECT #__users.name,	#__inv_acces.admin, #__inv_acces.storekeeper, #__inv_acces.technician
					FROM #__inv_acces
  					INNER JOIN #__users
    				ON #__inv_acces.id_user = #__users.id
					WHERE #__inv_acces.id_user = ".$user->id;//Определяем запрос
		$db->setQuery($query);//Выполняем запрос

		if ($rList = $db->loadObjectList()) {
			return $rList;
		}
		else {
			return $db->stderr();
		}
	}
	
	function getGetRegions() {//Получаем настройки клиники
			
		$rList = $this->getAccessUser();//Получаем права пользователей
	
		if ($rList[0]->admin){
			$db = JFactory::getDbo();//Подключаемся к базе
			$query = "SELECT  #__neq_region.* FROM #__neq_region";
			$rList->setQuery($query);
			$i=0;
			foreach($rList as $row){
				$response->rows[$i]['id']=$row->id;
				$response->rows[$i]['cell']=array($row->type);
				$i++;
			}
			return json_encode($response);
		}
	}
}