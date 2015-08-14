<?php
defined('_JEXEC') or die;

class StalramsModelInventory extends JModelLegacy
{
	function getAccessUser()
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

}
?>