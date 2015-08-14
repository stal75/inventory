<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla modelitem library
jimport('joomla.application.component.modelitem');

/**
 * HelloWorld Model
*/
class StalramsModelTmajax extends JModelLegacy
{

	function getAccesUser() {//Получаем список производственных отделений в регионах
	
		$db = JFactory::getDBO();// Подключаемся к базе.
		$query = "SELECT  #__tm_access.`write` 
					FROM #__tm_access
					INNER JOIN #__tm_pes ON #__tm_access.id_pes = #__tm_pes.id
					WHERE #__tm_access.id_user = 140
					AND #__tm_pes.code = 'ttl'";//Определяем запрос
		$db->setQuery($query);//Выполняем запрос
	
		if ($rList = $db->loadObjectList()) {
			return $rList;
		}
		else {
			return $db->stderr();
		}
	}
	
	function getPS() {//Получаем список производственных отделений в регионах
	
		$pes = JRequest::getVar('pes');//Получаем ПЭС из запроса
		
		$db = JFactory::getDBO();// Подключаемся к базе.
		$query = "SELECT  #__tm_ps.id, #__tm_ps.name,  #__tm_ps.number 
				  FROM #__tm_ps INNER JOIN #__tm_pes ON toh1w_tm_ps.id_po = #__tm_pes.id
				  WHERE #__tm_pes.code = '".$pes."'
				  ORDER BY #__tm_ps.name";
		$db->setQuery($query);//Выполняем запрос
		if (JRequest::getVar('ajaxtype') == '2'){
			$rList = $db->loadObjectList();
			//$response->page = $curPage;
			//$response->total = ceil($totalRows['count'] / $rowsPerPage);
			//$response->records = $totalRows['count'];
			
			$i=0;
			foreach($rList as $row){
				$response->rows[$i]['id']=$row->id;
				$response->rows[$i]['cell']=array($row->name, $row->number);
				$i++;
			}
			return json_encode($response);
			
		}else if ($rList = $db->loadObjectList()) {
					return $rList;
				}
		else {
			return $db->stderr();
		}
	}
}