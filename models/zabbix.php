<?php
defined('_JEXEC') or die('Restricted access');
// import Joomla modelitem library
jimport('joomla.application.component.modelitem');

class StalramsModelZabbix extends JModelLegacy
{
	protected $msg; 
	
	function mod_name(){
		return 'Модель Разбора';
	}
	
	function getZabbix() {//Получаем список аппаратных на объекте
	
		try {
			$region = JRequest::getVar('region');//Пределяем опорный регион
	
			$region = '1';
			$db = JFactory::getDBO();// Подключаемся к базе.
			$query = "SELECT api AS api, login AS login, password AS password FROM #__neq_region WHERE id = ".$region;//Определяем запрос
			//echo $query;
			$db->setQuery($query);//Выполняем запрос
			$rList = $db->loadObjectList();
			if ($rList = $db->loadObjectList()) {
				if ($rList[0]->api == '') return '';
				$api = new ZabbixApi($rList[0]->api, $rList[0]->login, $rList[0]->password);
			}
			else {
				return $db->stderr();
			}
	
			$hostExists = $api->hostExists(array('host' => JRequest::getVar('encod')), '');
			if ($hostExists == 1) {return "<font color='#FF0000'>Такой объект есть в базе Zabbix";}
			else {return "Объекта нет в базе Zabbix";};
		} catch(Exception $e) {
			// Exception in ZabbixApi catched
			return $e->getMessage();
		}
	}
	
	function getZabbixObjects() {//Получаем список хостов из забикса
	
		try {
			$region = JRequest::getVar('region');//Пределяем опорный регион
	
			$db = JFactory::getDBO();// Подключаемся к базе.
			$query = "SELECT api AS api, login AS login, password AS password FROM #__neq_region WHERE id = ".$region;//Определяем запрос
			//echo $query;
			$db->setQuery($query);//Выполняем запрос
			$rList = $db->loadObjectList();
			if ($rList = $db->loadObjectList()) {
				if ($rList[0]->api == '') return '';
				$api = new ZabbixApi($rList[0]->api, $rList[0]->login, $rList[0]->password);
			}
			else {
				return $db->stderr();
			}
	
			// get all graphs named "CPU"
			$rList = $api->hostGet(array('output' => 'extend','search' => array('host' => JRequest::getVar('object')), 'sortfield'=> 'host'));
	
			$msg = '  Найдено : '.count($rList);
			$msg .= '<div style = "width: 600px; overflow: auto; height: 300px; background-color: white;">';
			$msg .= '<table>
							<thead>
								<tr>
									<th width="50">id</th>
									<th width="200">Код</th>
									<th width="300">Видимое имя</th>
								</tr>
							</thead>
							<tbody>';
			foreach($rList as $graph){
				$msg .= '<tr><td>' .$graph->hostid .'</td><td>'. $graph->host .'</td><td>'. $graph->name .'</td></tr>';
			}
			$msg .= '</tbody></table><div>';
			return $msg;
		} catch(Exception $e) {
			// Exception in ZabbixApi catched
			return $e->getMessage();
		}
	}
	
	public function checkRegion($region){
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query = "SELECT #__neq_region.Name AS Name FROM #__neq_pes INNER JOIN #__neq_region ON #__neq_pes.regid = #__neq_region.id WHERE #__neq_pes.code = '".$region."'";
		$db->setQuery($query);
		$row = $db->loadAssoc();
		if ($row['Name'] != '') return $row['Name'];
		else return false;
	}
	public function checkPO($region){
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query = "SELECT #__neq_pes.name AS Name FROM #__neq_pes WHERE #__neq_pes.code = '".$region."'";
		$db->setQuery($query);
		$row = $db->loadAssoc();
		if ($row['Name'] != '') return $row['Name'];
		else return false;
	}
	function checkBase($base){
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query = "SELECT #__neq_base.name AS Name FROM #__neq_base WHERE #__neq_base.code = '".$base."'";
		$db->setQuery($query);
		$row = $db->loadAssoc();
		if ($row['Name'] != '') return $row['Name'];
		else return false;
	}
	
	function checkObjectName($region, $basetype,  $base){
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query = "SELECT #__neq_po.name AS Name FROM #__neq_po INNER JOIN #__neq_base ON #__neq_po.basetype = #__neq_base.id
				  INNER JOIN #__neq_pes ON #__neq_po.pes = #__neq_pes.id
				  WHERE #__neq_base.code = '".$basetype."'
				  AND #__neq_pes.code = '".$region."'
				  AND #__neq_po.number = ".$base;
		$db->setQuery($query);
		$row = $db->loadAssoc();
		if ($row['Name'] != '') return $row['Name'];
		else return false;
	}
	
	
	function checkType($typ){
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query = "SELECT #__neq_type.name AS name FROM #__neq_type WHERE #__neq_type.code = '".$typ."'";
		//echo $query;
		$db->setQuery($query);
		$row = $db->loadAssoc();
		if ($row['name'] != '') return $row['name'];
		else return false;
	}
	
	function checkCategory($obtype){
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		//$query = "SELECT #__neq_type.name AS name FROM #__neq_type WHERE #__neq_type.code = '".$typ."'";
		$query = "SELECT #__neq_category.name AS name FROM #__neq_type
		          INNER JOIN #__neq_category ON #__neq_type.catcode = #__neq_category.id
				  WHERE #__neq_type.code = '".$obtype."'";
		//echo $query;
		$db->setQuery($query);
		$row = $db->loadAssoc();
		if ($row['name'] != '') return $row['name'];
		else return false;
	}
	
	function checkAppar($appar, $region, $basetype,  $base){
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query = "SELECT  #__neq_a.name AS name FROM #__neq_a
  				  INNER JOIN #__neq_po  ON #__neq_a.codepo = #__neq_po.id  INNER JOIN #__neq_base   ON #__neq_po.basetype = #__neq_base.id INNER JOIN #__neq_pes  ON #__neq_po.pes = #__neq_pes.id
				  WHERE #__neq_base.code = '".$basetype."'
				  AND #__neq_pes.code = '".$region."'
				  AND #__neq_po.number = ".$base."
				  AND #__neq_a.code = ".$appar;
		//echo $query;
		$db->setQuery($query);
		$row = $db->loadAssoc();
		if ($row['name'] != '') return $row['name'];
		else return false;
	}
	
	
	//Разбор именования оборудования
	function group($str, $oper){
		if ($str == ''){
			$group = '<font color="red">Введите наименование оборудования!</font color>';
		}
		else{
	
			$str = strtolower($str);// Переводим все символы в нижний регистр
			$arr = explode("-", $str);//Помещаем каждый код в свой элемент массива
			list($region, $base, $appar, $shkaf, $typ) = $arr;// Каждый элемент массива в свою переменную
			$n = strcspn($typ, '1234567890'); //Отделяем тип оборудования от номера оборудования
			$l = strlen($typ);
			$to = $this->checkType(substr($typ, 0, $n));
			
			if ($to == false) $to = '<font color="red">Тип оборудования не определен</font>';
			
			$msg = $this->checkCategory(substr($typ, 0, $n));
			if ($msg == false) $rez .= 'Категория не определена';
			else{
				if ($oper)	return $region.'-'.$msg;
				else  return $to; 
			}
		}
	}
	
	//Разбор именования оборудования
	function razborEq($str){
		if ($str == ''){
			$rez = '<font color="red">Введите наименование оборудования!</font color>';
		}
		else{
	
			$str = strtolower($str);// Переводим все символы в нижний регистр
			$arr = explode("-", $str);//Помещаем каждый код в свой элемент массива
			list($region, $base, $appar, $shkaf, $typ) = $arr;// Каждый элемент массива в свою переменную
			$rez = '<div id=viv> <table><tr><td>';
			$msg = $this->checkRegion($region);// Проверяем регион
			if ( $msg == false) $msg = '<font color="red">Регион не определен</font>';
			$rez.='Регион:</td><td>'.$msg.'</td></tr>';
			$msg = $this->checkPO($region);//Проверяем производственное отделение
			if ($msg == false) $msg = '<font color="red">Производственное отеделение не определено</font>';
			$rez.='<tr><td>Производственное отделение:</td><td>'.$msg.'</td></tr>';// Проверяем регион
			$msg = $this->checkBase($base{0});//Проверяем тип объекта
			if ($msg == false) $msg = '<font color="red">Объект не определен</font>';
			$rez.='<tr><td>Объект:</td><td>'.$msg.'</td></tr>';
			$msg = $this->checkObjectName($region, $base{0}, 0+substr($base, 1));
			if ($msg == false) $msg = '<font color="red">Объект не определен</font>';
			$rez.='<tr><td>Название объекта:</td><td>'.$msg.'</td></tr>';//Проверяем тип объекта
			if (($appar != '0') or ($appar != '00')){// Проверяем есть ли аппаратная
				if ($appar{0} == 'a') {
					$msg = $this->checkAppar(0+substr($appar, 1), $region, $base{0}, 0+substr($base, 1));//Проверяем навание аппаратной
					if ($msg == false) $msg = '<font color="red">Аппаратная не определена</font>';
					$rez .='<tr><td>Аппаратная: </td><td>'.$msg.'</td></tr>';//если аппрататная есть и первая буква "а" это аппаратная
				}
				if ($appar{0} == 'k') $rez .='<tr><td>Комната: <td>'.$appar.'</td></tr>'; //если аппаратная есть и первая буква "k" это комната
				if ($appar{0} == 'f') $rez .='<tr><td>Коридор или этаж: <td>'.$appar.'</td></tr>'; //если аппаратная есть и первая буква "f" это коидор
			}else $rez.='<tr><td>Отсутствует комната или аппаратная </td></tr>';
			$rez .= '<tr><td>Шкаф:</td><td>'.'№ '.$shkaf.'</td></tr>';
			//echo $typ;
			$n = strcspn($typ, '1234567890'); //Отделяем тип оборудования от номера оборудования
			$l = strlen($typ);
			$msg = $this->checkType(substr($typ, 0, $n));
			if ($msg == false) $msg = '<font color="red">Тип оборудования не определен</font>';
			$rez .= '<tr><td>Тип оборудования:<br><br></td><td>'.$msg.' № '.substr($typ, $n).'<br><br></tb><tr>'; //Выводим тип оборудования и номер оборудования в стойке
			$msg = $this->checkCategory(substr($typ, 0, $n));
			if ($msg == false) $rez .= '<tr><td>Категория:</td><td><font color="red">Категория не определена</font></tb><tr></table><br><br></div>';
			else $rez .= '<tr><td>Категория:</td><td>'.$region.'-'.$msg.'</tb><tr></table><br><br></div>';
		}
		return $rez;
	}
    /**
     * Получаем список темплейтов из Zabbix
     * @return string
     */
    function getZabbixTemplate() {

        try {
            $region = JRequest::getVar('region');//Пределяем опорный регион

            $db = JFactory::getDBO();// Подключаемся к базе.
            $query = "SELECT api AS api, login AS login, password AS password FROM #__neq_region WHERE id = ".$region;//Определяем запрос
            $db->setQuery($query);//Выполняем запрос
            $rList = $db->loadObjectList();
            if ($rList = $db->loadObjectList()) {
                if ($rList[0]->api == '') return '';
                $api = new ZabbixApi($rList[0]->api, $rList[0]->login, $rList[0]->password);
            }
            else {
                return $db->stderr();
            }

            $rList = $api->templateGet(array('output' => 'extend','filter' => array('host' => JRequest::getVar('object')), 'sortfield'=> 'host'));

            //$rez = array();
            foreach($rList as $row){
                $response[]=array('id'=> $row->templateid, 'value'=> $row->name);
            }
            return $response;
        } catch(Exception $e) {
            // Exception in ZabbixApi catched
            return $e->getMessage();
        }
    }
    /**
     * Функиция импорта оборудования из Zabbix
     * @return string
     */
    function getZabbixImport()
    {

        try {
            $region = JRequest::getVar('region');//Пределяем опорный регион

            $db = JFactory::getDBO();// Подключаемся к базе.
            $query = "SELECT api AS api, login AS login, password AS password FROM #__neq_region WHERE id = ".$region;//Определяем запрос
            //echo $query;
            $db->setQuery($query);//Выполняем запрос
            $rList = $db->loadObjectList();
            if ($rList = $db->loadObjectList()) {
                if ($rList[0]->api == '') return '';
                $api = new ZabbixApi($rList[0]->api, $rList[0]->login, $rList[0]->password);
            }
            else {
                return $db->stderr();
            }

            $rList = $api->hostGet(array('output' => 'extend','search' => array('host' => ''), 'sortfield'=> 'host', 'selectInventory' => array(
                'alias', 'asset_tag', 'chassis', 'contact', 'contract_number', 'date_hw_decomm', 'date_hw_expiry',
                'date_hw_install', 'date_hw_purchase', 'deployment_status', 'hardware', 'hardware_full',
                'host_netmask', 'host_networks', 'host_router', 'hw_arch', 'installer_name', 'inventory_mode',
                'location', 'location_lat', 'location_lon', 'macaddress_a', 'macaddress_b', 'model',
                'name_inv', 'notes', 'oob_ip', 'oob_netmask', 'oob_router', 'os', 'os_full', 'os_short',
                'poc_1_cell', 'poc_1_email', 'poc_1_name', 'poc_1_notes', 'poc_1_phone_a', 'poc_1_phone_b',
                'poc_1_screen', 'poc_2_cell', 'poc_2_email', 'poc_2_name', 'poc_2_notes', 'poc_2_phone_a',
                'poc_2_phone_b', 'poc_2_screen', 'serialno_a', 'serialno_b', 'site_address_a',
                'site_address_b', 'site_address_c', 'site_city', 'site_country', 'site_notes', 'site_rack',
                'site_state', 'site_zip', 'software', 'software_app_a', 'software_app_b', 'software_app_c',
                'software_app_d', 'software_app_e', 'software_full', 'tag', 'inv_type', 'type_full',
                'url_a', 'url_b', 'url_c', 'vendor')));

            $rez->region = $region;
            $rez->update = 0;
            $rez->insert = 0;

            foreach($rList as $host){

                $query = "SELECT COUNT(id) AS count FROM #__inv_zabbix WHERE #__inv_zabbix.hostid='{$host->hostid}'  AND #__inv_zabbix.id_reg = {$region}";
                $db->setQuery($query);
                $rows = $db->loadResult();


                if ($rows > 0) {
                    $db->setQuery("UPDATE #__inv_zabbix SET
							      host='{$host->host}', available='{$host->available}', description='{$host->description}',
							      name='{$host->name}', proxy_hostid='{$host->proxy_hostid}', snmp_available='{$host->snmp_available}', status='{$host->status}',
							      alias='{$host->inventory->alias}', asset_tag='{$host->inventory->asset_tag}', chassis='{$host->inventory->chassis}',
							      contact='{$host->inventory->contact}', contract_number='{$host->inventory->contract_number}',
							      date_hw_decomm='{$host->inventory->date_hw_decomm}', date_hw_expiry='{$host->inventory->date_hw_expiry}',
							      date_hw_install='{$host->inventory->date_hw_install}', date_hw_purchase='{$host->inventory->date_hw_purchase}',
							      deployment_status='{$host->inventory->deployment_status}', hardware='{$host->inventory->hardware}',
							      hardware_full='{$host->inventory->hardware_full}', host_netmask='{$host->inventory->host_netmask}',
							      host_networks='{$host->inventory->host_networks}', host_router='{$host->inventory->host_router}',
							      hw_arch='{$host->inventory->hw_arch}', installer_name='{$host->inventory->installer_name}',
							      inventory_mode='{$host->inventory->inventory_mode}', location='{$host->inventory->location}',
							      location_lat='{$host->inventory->location_lat}', location_lon='{$host->inventory->location_lon}',
							      macaddress_a='{$host->inventory->macaddress_a}', macaddress_b='{$host->inventory->macaddress_b}',
							      model='{$host->inventory->model}', inv_name='{$host->inventory->inv_name}', notes='{$host->inventory->notes}',
							      oob_ip='{$host->inventory->oob_ip}',oob_netmask='{$host->inventory->oob_netmask}',
								  oob_router='{$host->inventory->oob_router}', os='{$host->inventory->os}', os_full='{$host->inventory->os_full}',
								  os_short='{$host->inventory->os_short}', poc_1_cell='{$host->inventory->poc_1_cell}',
								  poc_1_email='{$host->inventory->poc_1_email}', poc_1_name='{$host->inventory->poc_1_name}',
								  poc_1_notes='{$host->inventory->poc_1_notes}', poc_1_phone_a='{$host->inventory->poc_1_phone_a}',
								  poc_1_phone_b='{$host->inventory->poc_1_phone_b}', poc_1_screen='{$host->inventory->poc_1_screen}',
								  poc_2_cell='{$host->inventory->poc_2_cell}', poc_2_email='{$host->inventory->poc_2_email}',
								  poc_2_name='{$host->inventory->poc_2_name}', poc_2_notes='{$host->inventory->poc_2_notes}',
								  poc_2_phone_a='{$host->inventory->poc_2_phone_a}', poc_2_phone_b='{$host->inventory->poc_2_phone_b}',
								  poc_2_screen='{$host->inventory->poc_2_screen}', serialno_a='{$host->inventory->serialno_a}',
								  serialno_b='{$host->inventory->serialno_b}', site_address_a='{$host->inventory->site_address_a}',
								  site_address_b='{$host->inventory->site_address_b}', site_address_c='{$host->inventory->site_address_c}',
								  site_city='{$host->inventory->site_city}', site_country='{$host->inventory->site_country}',
								  site_notes='{$host->inventory->site_notes}', site_rack='{$host->inventory->site_rack}',
								  site_state='{$host->inventory->site_state}', site_zip='{$host->inventory->site_zip}',
								  software='{$host->inventory->software}', software_app_a='{$host->inventory->software_app_a}',
								  software_app_b='{$host->inventory->software_app_b}', software_app_c='{$host->inventory->software_app_c}',
								  software_app_d='{$host->inventory->software_app_d}', software_app_e='{$host->inventory->software_app_e}',
								  software_full='{$host->inventory->software_full}', tag='{$host->inventory->tag}',
								  inv_type='{$host->inventory->inv_type}', type_full='{$host->inventory->type_full}',
								  url_a='{$host->inventory->url_a}', url_b='{$host->inventory->url_b}', url_c='{$host->inventory->url_c}',
								  vendor='{$host->inventory->vendor}'
								WHERE #__inv_zabbix.hostid='{$host->hostid}'  AND #__inv_zabbix.id_reg = {$region}");
                    $db->query();
                    $rez->update++;

                }else {
                    $db->setQuery("INSERT INTO #__inv_zabbix
								( id_reg, hostid, host, available, description, name, proxy_hostid, snmp_available, status, alias, asset_tag,
								  chassis, contact,contract_number, date_hw_decomm, date_hw_expiry, date_hw_install, date_hw_purchase,deployment_status,
								  hardware, hardware_full, host_netmask, host_networks, host_router, hw_arch, installer_name, inventory_mode,
								  location, location_lat, location_lon, macaddress_a, macaddress_b, model, inv_name, notes, oob_ip,oob_netmask,
								  oob_router, os, os_full, os_short, poc_1_cell, poc_1_email, poc_1_name, poc_1_notes, poc_1_phone_a,
								  poc_1_phone_b, poc_1_screen, poc_2_cell, poc_2_email, poc_2_name, poc_2_notes, poc_2_phone_a, poc_2_phone_b,
								  poc_2_screen, serialno_a, serialno_b, site_address_a, site_address_b, site_address_c, site_city, site_country,
								  site_notes, site_rack, site_state, site_zip, software, software_app_a, software_app_b, software_app_c,
								  software_app_d, software_app_e, software_full, tag, inv_type, type_full, url_a, url_b, url_c, vendor)

							VALUES ({$region},'{$host->hostid}','{$host->host}','{$host->available}','{$host->description}','{$host->name}','{$host->proxy_hostid}',
								  '{$host->snmp_available}','{$host->status}','{$host->inventory->alias}', '{$host->inventory->asset_tag}',
								  '{$host->inventory->chassis}', '{$host->inventory->contact}', '{$host->inventory->contract_number}', '{$host->inventory->date_hw_decomm}',
								  '{$host->inventory->date_hw_expiry}', '{$host->inventory->date_hw_install}', '{$host->inventory->date_hw_purchase}',
								  '{$host->inventory->deployment_status}', '{$host->inventory->hardware}', '{$host->inventory->hardware_full}',
								  '{$host->inventory->host_netmask}', '{$host->inventory->host_networks}', '{$host->inventory->host_router}',
								  '{$host->inventory->hw_arch}', '{$host->inventory->installer_name}', '{$host->inventory->inventory_mode}',
								  '{$host->inventory->location}', '{$host->inventory->location_lat}', '{$host->inventory->location_lon}',
								  '{$host->inventory->macaddress_a}', '{$host->inventory->macaddress_b}', '{$host->inventory->model}',
								  '{$host->inventory->name}', '{$host->inventory->notes}', '{$host->inventory->oob_ip}',
								  '{$host->inventory->oob_netmask}', '{$host->inventory->oob_router}', '{$host->inventory->os}',
								  '{$host->inventory->os_full}', '{$host->inventory->os_short}', '{$host->inventory->poc_1_cell}',
								  '{$host->inventory->poc_1_email}', '{$host->inventory->poc_1_name}', '{$host->inventory->poc_1_notes}',
								  '{$host->inventory->poc_1_phone_a}', '{$host->inventory->poc_1_phone_b}', '{$host->inventory->poc_1_screen}',
								  '{$host->inventory->poc_2_cell}', '{$host->inventory->poc_2_email}', '{$host->inventory->poc_2_name}',
								  '{$host->inventory->poc_2_notes}', '{$host->inventory->poc_2_phone_a}', '{$host->inventory->poc_2_phone_b}',
								  '{$host->inventory->poc_2_screen}', '{$host->inventory->serialno_a}', '{$host->inventory->serialno_b}',
								  '{$host->inventory->site_address_a}', '{$host->inventory->site_address_b}', '{$host->inventory->site_address_c}',
								  '{$host->inventory->site_city}', '{$host->inventory->site_country}', '{$host->inventory->site_notes}',
								  '{$host->inventory->site_rack}', '{$host->inventory->site_state}', '{$host->inventory->site_zip}',
								  '{$host->inventory->software}', '{$host->inventory->software_app_a}', '{$host->inventory->software_app_b}',
								  '{$host->inventory->software_app_c}', '{$host->inventory->software_app_d}', '{$host->inventory->software_app_e}',
								  '{$host->inventory->software_full}', '{$host->inventory->tag}', '{$host->inventory->type}',
								  '{$host->inventory->type_full}', '{$host->inventory->url_a}', '{$host->inventory->url_b}', '{$host->inventory->url_c}',
								  '{$host->inventory->vendor}')");
                    $db->query();
                    $rez->insert++;
                }
            }
            return json_encode($rez);
        }
        catch (PDOException $e) {
            return 'Ошибка базы данных: '.$e->getMessage();
        }
    }
}	

/**
 * @file    ZabbixApi.class.php
 * @brief   Class file for the implementation of the class ZabbixApi.
 *
 * Implement your customizations in this file.
 *
 * This file is part of PhpZabbixApi.
 *
 * PhpZabbixApi is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PhpZabbixApi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PhpZabbixApi.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright   GNU General Public License
 * @author      confirm IT solutions GmbH, Rathausstrase 14, CH-6340 Baar
 *
 * @version     $Id: ZabbixApi.class.php 138 2012-10-08 08:00:24Z dbarton $
 */

/**
 * @brief   Concrete class for the Zabbix API.
 */

class ZabbixApi extends ZabbixApiAbstract
{

}

/**
 * @file        ZabbixApiAbstract.class.php
 *
 * @brief       Class file for the implementation of the class ZabbixApiAbstract.
 *
 * __        ___    ____  _   _ ___ _   _  ____
 * \ \      / / \  |  _ \| \ | |_ _| \ | |/ ___|
 *  \ \ /\ / / _ \ | |_) |  \| || ||  \| | |  _
 *   \ V  V / ___ \|  _ <| |\  || || |\  | |_| |
 *    \_/\_/_/   \_\_| \_\_| \_|___|_| \_|\____|
 *
 * This class was automatically generated by a script and will be replaced,
 * as soon as the script is invoked again.
 * Please do not modify this file. All modification will be lost once the script
 * is executed again. To customize the API class, please implement your changes
 * in the concrete class "ZabbixApi".
 *
 * This file is part of PhpZabbixApi.
 *
 * PhpZabbixApi is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PhpZabbixApi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PhpZabbixApi.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright   GNU General Public License
 * @author      confirm IT solutions GmbH, Rathausstrase 14, CH-6340 Baar
 *
 * @version     $Id: abstract.tpl.php 190 2013-05-07 14:18:57Z dbarton $
 */

/**
 * @brief   Abstract class for the Zabbix API.
 */

abstract class ZabbixApiAbstract
{

	/**
	 * @brief   Boolean if requests/responses should be printed out (JSON).
	 */

	private $printCommunication = FALSE;

	/**
	 * @brief   API URL.
	 */

	private $apiUrl = '';

	/**
	 * @brief   Default params.
	 */

	private $defaultParams = array();

	/**
	 * @brief   Auth string.
	*/

	private $auth = '';

	/**
	 * @brief   Request ID.
	 */

	private $id = 0;

	/**
	 * @brief   Request array.
	 */

	private $request = array();

	/**
	 * @brief   JSON encoded request string.
	*/

	private $requestEncoded = '';

	/**
	 * @brief   JSON decoded response string.
	 */

	private $response = '';

	/**
	 * @brief   Response object.
	 */

	private $responseDecoded = NULL;

	/**
	 * @brief   Class constructor.
	 *
	 * @param   $apiUrl     API url (e.g. http://FQDN/zabbix/api_jsonrpc.php)
	 * @param   $user       Username.
	 * @param   $password   Password.
	 */

	public function __construct($apiUrl='', $user='', $password='')
	{
		if($apiUrl)
			$this->setApiUrl($apiUrl);

		if($user && $password)
			$this->userLogin(array('user' => $user, 'password' => $password));
	}

	/**
	 * @brief   Returns the API url for all requests.
	 *
	 * @retval  string  API url.
	 */

	public function getApiUrl()
	{
		return $this->apiUrl;
	}


	/**
	 * @brief   Sets the API url for all requests.
	 *
	 * @param   $apiUrl     API url.
	 *
	 * @retval  ZabbixApiAbstract
	 */

	public function setApiUrl($apiUrl)
	{
		$this->apiUrl = $apiUrl;
		return $this;
	}

	/**
	 * @brief   Returns the default params.
	 *
	 * @retval  array   Array with default params.
	 */

	public function getDefaultParams()
	{
		return $this->defaultParams;
	}

	/**
	 * @brief   Sets the default params.
	 *
	 * @param   $defaultParams  Array with default params.
	 *
	 * @retval  ZabbixApiAbstract
	 *
	 * @throws  Exception
	 */

	public function setDefaultParams($defaultParams)
	{

		if(is_array($defaultParams))
			$this->defaultParams = $defaultParams;
		else
			throw new Exception('The argument defaultParams on setDefaultParams() has to be an array.');

		return $this;
	}

	/**
	 * @brief   Sets the flag to print communication requests/responses.
	 *
	 * @param   $print  Boolean if requests/responses should be printed out.
	 *
	 * @retval  ZabbixApiAbstract
	 */
	public function printCommunication($print = TRUE)
	{
		$this->printCommunication = (bool) $print;
		return $this;
	}

	/**
	 * @brief   Sends are request to the zabbix API and returns the response
	 *          as object.
	 *
	 * @param   $method     Name of the API method.
	 * @param   $params     Additional parameters.
	 * @param   $auth       Enable auth string (default TRUE).
	 *
	 * @retval  stdClass    API JSON response.
	 */

	public function request($method, $params=NULL, $resultArrayKey='', $auth=TRUE)
	{

		// sanity check and conversion for params array
		if(!$params)                $params = array();
		elseif(!is_array($params))  $params = array($params);

		// generate ID
		$this->id = number_format(microtime(true), 4, '', '');

		// build request array
		if ($auth)
		{
			$this->request = array(
					'jsonrpc' => '2.0',
					'method'  => $method,
					'params'  => $params,
					'auth'    => ($auth ? $this->auth : ''),
					'id'      => $this->id
			);
		}
		else
		{
			$this->request = array(
					'jsonrpc' => '2.0',
					'method'  => $method,
					'params'  => $params,
					'id'      => $this->id
			);
		}

		// encode request array
		$this->requestEncoded = json_encode($this->request);

		// debug logging
		if($this->printCommunication)
			echo 'API request: '.$this->requestEncoded;

		// do request
		$streamContext = stream_context_create(array('http' => array(
				'method'  => 'POST',
				'header'  => 'Content-type: application/json-rpc'."\r\n",
				'content' => $this->requestEncoded
		)));

		// get file handler
		$fileHandler = fopen($this->getApiUrl(), 'rb', false, $streamContext);
		if(!$fileHandler)
			throw new Exception('Could not connect to "'.$this->getApiUrl().'"');

		// get response
		$this->response = @stream_get_contents($fileHandler);

		// debug logging
		if($this->printCommunication)
			echo $this->response."\n";

		// response verification
		if($this->response === FALSE)
			throw new Exception('Could not read data from "'.$this->getApiUrl().'"');

		// decode response
		$this->responseDecoded = json_decode($this->response);

		// validate response
		if(!is_object($this->responseDecoded) && !is_array($this->responseDecoded))
			throw new Exception('Could not decode JSON response.');
		if(array_key_exists('error', $this->responseDecoded))
			throw new Exception('API error '.$this->responseDecoded->error->code.': '.$this->responseDecoded->error->data);

		// return response
		if($resultArrayKey && is_array($this->responseDecoded->result))
			return $this->convertToAssociatveArray($this->responseDecoded->result, $resultArrayKey);
		else
			return $this->responseDecoded->result;
	}

	/**
	 * @brief   Returns the last JSON API request.
	 *
	 * @retval  string  JSON request.
	 */

	public function getRequest()
	{
		return $this->requestEncoded;
	}

	/**
	 * @brief   Returns the last JSON API response.
	 *
	 * @retval  string  JSON response.
	 */

	public function getResponse()
	{
		return $this->response;
	}

	/**
	 * @brief   Convertes an indexed array to an associative array.
	 *
	 * @param   $indexedArray           Indexed array with objects.
	 * @param   $useObjectProperty      Object property to use as array key.
	 *
	 * @retval  associative Array
	 */

	private function convertToAssociatveArray($objectArray, $useObjectProperty)
	{
		// sanity check
		if(count($objectArray) == 0 || !property_exists($objectArray[0], $useObjectProperty))
			return $objectArray;

		// loop through array and replace keys
		foreach($objectArray as $key => $object)
		{
			unset($objectArray[$key]);
			$objectArray[$object->{$useObjectProperty}] = $object;
		}

		// return associative array
		return $objectArray;
	}

	/**
	 * @brief   Returns a params array for the request.
	 *
	 * This method will automatically convert all provided types into a correct
	 * array. Which means:
	 *
	 *      - arrays will not be converted (indexed & associatve)
	 *      - scalar values will be converted into an one-element array (indexed)
	 *      - other values will result in an empty array
	 *
	 * Afterwards the array will be merged with all default params, while the
	 * default params have a lower priority (passed array will overwrite default
	 * params). But there is an exception for merging: If the passed array is an
	 * indexed array, the default params will not be merged. This is because
	 * there are some API methods, which are expecting a simple JSON array (aka
	 * PHP indexed array) instead of an object (aka PHP associative array).
	 * Example for this behaviour are delete operations, which are directly
	 * expecting an array of IDs '[ 1,2,3 ]' instead of '{ ids: [ 1,2,3 ] }'.
	 *
	 * @param   $params     Params array.
	 *
	 * @retval  Array
	 */

	private function getRequestParamsArray($params)
	{
		// if params is a scalar value, turn it into an array
		if(is_scalar($params))
			$params = array($params);

		// if params isn't an array, create an empty one (e.g. for booleans, NULL)
		elseif(!is_array($params))
		$params = array();

		// if array isn't indexed, merge array with default params
		if(count($params) == 0 || array_keys($params) !== range(0, count($params) - 1))
			$params = array_merge($this->getDefaultParams(), $params);

		// return params
		return $params;
	}

	/**
	 * @brief   Login into the API.
	 *
	 * This will also retreive the auth Token, which will be used for any
	 * further requests.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	final public function userLogin($params=array(), $arrayKeyProperty='')
	{
		$params = $this->getRequestParamsArray($params);
		$this->auth = $this->request('user.login', $params, $arrayKeyProperty, FALSE);
		return $this->auth;
	}

	/**
	 * @brief   Logout from the API.
	 *
	 * This will also reset the auth Token.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	final public function userLogout($params=array(), $arrayKeyProperty='')
	{
		$params = $this->getRequestParamsArray($params);
		$this->auth = '';
		return $this->request('user.logout', $params, $arrayKeyProperty);
	}


	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method action.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function actionGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('action.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method action.exists.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function actionExists($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('action.exists', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method action.create.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function actionCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('action.create', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method action.update.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function actionUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('action.update', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method action.delete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function actionDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('action.delete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method action.validateOperationsIntegrity.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function actionValidateOperationsIntegrity($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('action.validateOperationsIntegrity', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method action.validateOperationConditions.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function actionValidateOperationConditions($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('action.validateOperationConditions', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method action.validateCreate.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function actionValidateCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('action.validateCreate', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method action.validateUpdate.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function actionValidateUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('action.validateUpdate', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method action.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function actionTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('action.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method action.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function actionPk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('action.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method action.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function actionPkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('action.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method alert.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function alertGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('alert.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method alert.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function alertTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('alert.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method alert.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function alertPk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('alert.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method alert.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function alertPkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('alert.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method apiinfo.version.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function apiinfoVersion($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('apiinfo.version', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method apiinfo.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function apiinfoTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('apiinfo.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method apiinfo.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function apiinfoPk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('apiinfo.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method apiinfo.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function apiinfoPkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('apiinfo.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method application.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function applicationGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('application.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method application.exists.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function applicationExists($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('application.exists', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method application.checkInput.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function applicationCheckInput($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('application.checkInput', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method application.create.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function applicationCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('application.create', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method application.update.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function applicationUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('application.update', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method application.delete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function applicationDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('application.delete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method application.massAdd.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function applicationMassAdd($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('application.massAdd', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method application.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function applicationTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('application.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method application.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function applicationPk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('application.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method application.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function applicationPkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('application.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method configuration.export.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function configurationExport($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('configuration.export', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method configuration.import.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function configurationImport($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('configuration.import', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method configuration.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function configurationTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('configuration.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method configuration.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function configurationPk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('configuration.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method configuration.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function configurationPkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('configuration.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method dcheck.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function dcheckGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('dcheck.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method dcheck.isReadable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function dcheckIsReadable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('dcheck.isReadable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method dcheck.isWritable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function dcheckIsWritable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('dcheck.isWritable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method dcheck.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function dcheckTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('dcheck.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method dcheck.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function dcheckPk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('dcheck.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method dcheck.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function dcheckPkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('dcheck.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method dhost.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function dhostGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('dhost.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method dhost.exists.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function dhostExists($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('dhost.exists', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method dhost.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function dhostTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('dhost.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method dhost.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function dhostPk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('dhost.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method dhost.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function dhostPkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('dhost.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method discoveryrule.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function discoveryruleGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('discoveryrule.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method discoveryrule.exists.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function discoveryruleExists($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('discoveryrule.exists', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method discoveryrule.create.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function discoveryruleCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('discoveryrule.create', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method discoveryrule.update.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function discoveryruleUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('discoveryrule.update', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method discoveryrule.delete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function discoveryruleDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('discoveryrule.delete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method discoveryrule.copy.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function discoveryruleCopy($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('discoveryrule.copy', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method discoveryrule.syncTemplates.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function discoveryruleSyncTemplates($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('discoveryrule.syncTemplates', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method discoveryrule.isReadable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function discoveryruleIsReadable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('discoveryrule.isReadable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method discoveryrule.isWritable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function discoveryruleIsWritable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('discoveryrule.isWritable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method discoveryrule.findInterfaceForItem.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function discoveryruleFindInterfaceForItem($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('discoveryrule.findInterfaceForItem', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method discoveryrule.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function discoveryruleTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('discoveryrule.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method discoveryrule.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function discoveryrulePk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('discoveryrule.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method discoveryrule.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function discoveryrulePkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('discoveryrule.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method drule.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function druleGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('drule.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method drule.exists.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function druleExists($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('drule.exists', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method drule.checkInput.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function druleCheckInput($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('drule.checkInput', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method drule.create.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function druleCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('drule.create', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method drule.update.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function druleUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('drule.update', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method drule.delete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function druleDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('drule.delete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method drule.isReadable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function druleIsReadable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('drule.isReadable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method drule.isWritable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function druleIsWritable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('drule.isWritable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method drule.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function druleTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('drule.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method drule.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function drulePk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('drule.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method drule.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function drulePkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('drule.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method dservice.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function dserviceGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('dservice.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method dservice.exists.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function dserviceExists($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('dservice.exists', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method dservice.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function dserviceTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('dservice.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method dservice.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function dservicePk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('dservice.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method dservice.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function dservicePkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('dservice.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method event.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function eventGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('event.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method event.acknowledge.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function eventAcknowledge($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('event.acknowledge', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method event.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function eventTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('event.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method event.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function eventPk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('event.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method event.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function eventPkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('event.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method graph.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function graphGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('graph.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method graph.syncTemplates.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function graphSyncTemplates($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('graph.syncTemplates', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method graph.delete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function graphDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('graph.delete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method graph.update.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function graphUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('graph.update', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method graph.create.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function graphCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('graph.create', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method graph.exists.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function graphExists($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('graph.exists', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method graph.getObjects.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function graphGetObjects($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('graph.getObjects', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method graph.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function graphTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('graph.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method graph.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function graphPk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('graph.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method graph.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function graphPkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('graph.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method graphitem.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function graphitemGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('graphitem.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method graphitem.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function graphitemTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('graphitem.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method graphitem.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function graphitemPk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('graphitem.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method graphitem.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function graphitemPkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('graphitem.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method graphprototype.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function graphprototypeGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('graphprototype.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method graphprototype.syncTemplates.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function graphprototypeSyncTemplates($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('graphprototype.syncTemplates', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method graphprototype.delete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function graphprototypeDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('graphprototype.delete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method graphprototype.update.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function graphprototypeUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('graphprototype.update', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method graphprototype.create.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function graphprototypeCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('graphprototype.create', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method graphprototype.exists.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function graphprototypeExists($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('graphprototype.exists', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method graphprototype.getObjects.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function graphprototypeGetObjects($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('graphprototype.getObjects', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method graphprototype.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function graphprototypeTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('graphprototype.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method graphprototype.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function graphprototypePk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('graphprototype.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method graphprototype.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function graphprototypePkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('graphprototype.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method host.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('host.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method host.getObjects.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostGetObjects($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('host.getObjects', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method host.exists.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostExists($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('host.exists', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method host.create.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('host.create', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method host.update.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('host.update', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method host.massAdd.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostMassAdd($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('host.massAdd', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method host.massUpdate.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostMassUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('host.massUpdate', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method host.massRemove.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostMassRemove($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('host.massRemove', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method host.delete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('host.delete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method host.isReadable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostIsReadable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('host.isReadable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method host.isWritable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostIsWritable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('host.isWritable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method host.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('host.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method host.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostPk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('host.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method host.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostPkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('host.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostgroup.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostgroupGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostgroup.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostgroup.getObjects.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostgroupGetObjects($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostgroup.getObjects', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostgroup.exists.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostgroupExists($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostgroup.exists', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostgroup.create.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostgroupCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostgroup.create', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostgroup.update.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostgroupUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostgroup.update', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostgroup.delete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostgroupDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostgroup.delete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostgroup.massAdd.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostgroupMassAdd($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostgroup.massAdd', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostgroup.massRemove.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostgroupMassRemove($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostgroup.massRemove', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostgroup.massUpdate.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostgroupMassUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostgroup.massUpdate', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostgroup.isReadable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostgroupIsReadable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostgroup.isReadable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostgroup.isWritable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostgroupIsWritable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostgroup.isWritable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostgroup.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostgroupTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostgroup.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostgroup.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostgroupPk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostgroup.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostgroup.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostgroupPkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostgroup.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostprototype.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostprototypeGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostprototype.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostprototype.create.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostprototypeCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostprototype.create', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostprototype.update.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostprototypeUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostprototype.update', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostprototype.syncTemplates.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostprototypeSyncTemplates($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostprototype.syncTemplates', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostprototype.delete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostprototypeDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostprototype.delete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostprototype.isReadable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostprototypeIsReadable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostprototype.isReadable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostprototype.isWritable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostprototypeIsWritable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostprototype.isWritable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostprototype.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostprototypeTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostprototype.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostprototype.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostprototypePk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostprototype.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostprototype.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostprototypePkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostprototype.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method history.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function historyGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('history.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method history.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function historyTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('history.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method history.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function historyPk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('history.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method history.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function historyPkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('history.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostinterface.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostinterfaceGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostinterface.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostinterface.exists.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostinterfaceExists($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostinterface.exists', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostinterface.checkInput.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostinterfaceCheckInput($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostinterface.checkInput', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostinterface.create.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostinterfaceCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostinterface.create', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostinterface.update.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostinterfaceUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostinterface.update', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostinterface.delete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostinterfaceDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostinterface.delete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostinterface.massAdd.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostinterfaceMassAdd($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostinterface.massAdd', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostinterface.massRemove.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostinterfaceMassRemove($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostinterface.massRemove', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostinterface.replaceHostInterfaces.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostinterfaceReplaceHostInterfaces($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostinterface.replaceHostInterfaces', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostinterface.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostinterfaceTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostinterface.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostinterface.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostinterfacePk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostinterface.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method hostinterface.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function hostinterfacePkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('hostinterface.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method image.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function imageGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('image.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method image.getObjects.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function imageGetObjects($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('image.getObjects', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method image.exists.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function imageExists($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('image.exists', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method image.create.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function imageCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('image.create', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method image.update.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function imageUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('image.update', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method image.delete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function imageDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('image.delete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method image.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function imageTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('image.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method image.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function imagePk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('image.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method image.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function imagePkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('image.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method iconmap.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function iconmapGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('iconmap.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method iconmap.create.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function iconmapCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('iconmap.create', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method iconmap.update.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function iconmapUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('iconmap.update', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method iconmap.delete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function iconmapDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('iconmap.delete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method iconmap.isReadable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function iconmapIsReadable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('iconmap.isReadable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method iconmap.isWritable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function iconmapIsWritable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('iconmap.isWritable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method iconmap.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function iconmapTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('iconmap.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method iconmap.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function iconmapPk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('iconmap.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method iconmap.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function iconmapPkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('iconmap.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method item.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function itemGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('item.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method item.getObjects.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function itemGetObjects($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('item.getObjects', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method item.exists.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function itemExists($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('item.exists', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method item.create.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function itemCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('item.create', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method item.update.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function itemUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('item.update', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method item.delete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function itemDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('item.delete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method item.syncTemplates.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function itemSyncTemplates($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('item.syncTemplates', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method item.validateInventoryLinks.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function itemValidateInventoryLinks($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('item.validateInventoryLinks', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method item.addRelatedObjects.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function itemAddRelatedObjects($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('item.addRelatedObjects', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method item.findInterfaceForItem.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function itemFindInterfaceForItem($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('item.findInterfaceForItem', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method item.isReadable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function itemIsReadable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('item.isReadable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method item.isWritable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function itemIsWritable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('item.isWritable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method item.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function itemTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('item.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method item.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function itemPk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('item.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method item.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function itemPkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('item.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method itemprototype.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function itemprototypeGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('itemprototype.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method itemprototype.exists.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function itemprototypeExists($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('itemprototype.exists', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method itemprototype.create.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function itemprototypeCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('itemprototype.create', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method itemprototype.update.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function itemprototypeUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('itemprototype.update', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method itemprototype.delete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function itemprototypeDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('itemprototype.delete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method itemprototype.syncTemplates.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function itemprototypeSyncTemplates($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('itemprototype.syncTemplates', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method itemprototype.addRelatedObjects.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function itemprototypeAddRelatedObjects($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('itemprototype.addRelatedObjects', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method itemprototype.findInterfaceForItem.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function itemprototypeFindInterfaceForItem($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('itemprototype.findInterfaceForItem', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method itemprototype.isReadable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function itemprototypeIsReadable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('itemprototype.isReadable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method itemprototype.isWritable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function itemprototypeIsWritable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('itemprototype.isWritable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method itemprototype.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function itemprototypeTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('itemprototype.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method itemprototype.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function itemprototypePk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('itemprototype.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method itemprototype.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function itemprototypePkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('itemprototype.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method maintenance.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function maintenanceGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('maintenance.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method maintenance.exists.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function maintenanceExists($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('maintenance.exists', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method maintenance.create.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function maintenanceCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('maintenance.create', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method maintenance.update.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function maintenanceUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('maintenance.update', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method maintenance.delete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function maintenanceDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('maintenance.delete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method maintenance.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function maintenanceTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('maintenance.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method maintenance.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function maintenancePk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('maintenance.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method maintenance.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function maintenancePkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('maintenance.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method map.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function mapGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('map.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method map.getObjects.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function mapGetObjects($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('map.getObjects', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method map.exists.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function mapExists($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('map.exists', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method map.checkInput.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function mapCheckInput($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('map.checkInput', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method map.create.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function mapCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('map.create', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method map.update.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function mapUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('map.update', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method map.delete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function mapDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('map.delete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method map.isReadable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function mapIsReadable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('map.isReadable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method map.isWritable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function mapIsWritable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('map.isWritable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method map.checkCircleSelementsLink.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function mapCheckCircleSelementsLink($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('map.checkCircleSelementsLink', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method map.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function mapTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('map.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method map.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function mapPk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('map.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method map.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function mapPkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('map.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method mediatype.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function mediatypeGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('mediatype.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method mediatype.create.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function mediatypeCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('mediatype.create', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method mediatype.update.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function mediatypeUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('mediatype.update', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method mediatype.delete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function mediatypeDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('mediatype.delete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method mediatype.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function mediatypeTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('mediatype.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method mediatype.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function mediatypePk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('mediatype.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method mediatype.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function mediatypePkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('mediatype.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method proxy.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function proxyGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('proxy.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method proxy.create.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function proxyCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('proxy.create', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method proxy.update.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function proxyUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('proxy.update', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method proxy.delete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function proxyDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('proxy.delete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method proxy.isReadable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function proxyIsReadable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('proxy.isReadable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method proxy.isWritable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function proxyIsWritable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('proxy.isWritable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method proxy.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function proxyTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('proxy.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method proxy.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function proxyPk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('proxy.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method proxy.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function proxyPkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('proxy.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method service.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function serviceGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('service.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method service.create.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function serviceCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('service.create', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method service.validateUpdate.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function serviceValidateUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('service.validateUpdate', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method service.update.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function serviceUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('service.update', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method service.validateDelete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function serviceValidateDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('service.validateDelete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method service.delete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function serviceDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('service.delete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method service.addDependencies.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function serviceAddDependencies($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('service.addDependencies', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method service.deleteDependencies.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function serviceDeleteDependencies($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('service.deleteDependencies', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method service.validateAddTimes.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function serviceValidateAddTimes($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('service.validateAddTimes', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method service.addTimes.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function serviceAddTimes($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('service.addTimes', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method service.getSla.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function serviceGetSla($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('service.getSla', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method service.deleteTimes.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function serviceDeleteTimes($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('service.deleteTimes', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method service.isReadable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function serviceIsReadable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('service.isReadable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method service.isWritable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function serviceIsWritable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('service.isWritable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method service.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function serviceTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('service.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method service.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function servicePk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('service.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method service.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function servicePkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('service.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method screen.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function screenGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('screen.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method screen.exists.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function screenExists($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('screen.exists', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method screen.create.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function screenCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('screen.create', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method screen.update.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function screenUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('screen.update', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method screen.delete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function screenDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('screen.delete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method screen.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function screenTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('screen.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method screen.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function screenPk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('screen.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method screen.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function screenPkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('screen.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method screenitem.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function screenitemGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('screenitem.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method screenitem.create.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function screenitemCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('screenitem.create', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method screenitem.update.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function screenitemUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('screenitem.update', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method screenitem.updateByPosition.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function screenitemUpdateByPosition($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('screenitem.updateByPosition', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method screenitem.delete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function screenitemDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('screenitem.delete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method screenitem.isReadable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function screenitemIsReadable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('screenitem.isReadable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method screenitem.isWritable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function screenitemIsWritable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('screenitem.isWritable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method screenitem.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function screenitemTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('screenitem.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method screenitem.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function screenitemPk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('screenitem.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method screenitem.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function screenitemPkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('screenitem.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method script.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function scriptGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('script.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method script.create.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function scriptCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('script.create', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method script.update.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function scriptUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('script.update', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method script.delete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function scriptDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('script.delete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method script.execute.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function scriptExecute($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('script.execute', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method script.getScriptsByHosts.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function scriptGetScriptsByHosts($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('script.getScriptsByHosts', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method script.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function scriptTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('script.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method script.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function scriptPk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('script.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method script.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function scriptPkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('script.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method template.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function templatePkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('template.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method template.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function templateGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('template.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method template.getObjects.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function templateGetObjects($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('template.getObjects', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method template.exists.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function templateExists($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('template.exists', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method template.create.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function templateCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('template.create', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method template.update.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function templateUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('template.update', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method template.delete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function templateDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('template.delete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method template.massAdd.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function templateMassAdd($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('template.massAdd', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method template.massUpdate.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function templateMassUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('template.massUpdate', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method template.massRemove.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function templateMassRemove($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('template.massRemove', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method template.isReadable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function templateIsReadable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('template.isReadable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method template.isWritable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function templateIsWritable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('template.isWritable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method template.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function templateTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('template.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method template.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function templatePk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('template.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method templatescreen.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function templatescreenGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('templatescreen.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method templatescreen.exists.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function templatescreenExists($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('templatescreen.exists', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method templatescreen.copy.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function templatescreenCopy($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('templatescreen.copy', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method templatescreen.update.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function templatescreenUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('templatescreen.update', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method templatescreen.create.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function templatescreenCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('templatescreen.create', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method templatescreen.delete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function templatescreenDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('templatescreen.delete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method templatescreen.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function templatescreenTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('templatescreen.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method templatescreen.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function templatescreenPk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('templatescreen.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method templatescreen.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function templatescreenPkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('templatescreen.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method templatescreenitem.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function templatescreenitemGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('templatescreenitem.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method templatescreenitem.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function templatescreenitemTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('templatescreenitem.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method templatescreenitem.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function templatescreenitemPk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('templatescreenitem.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method templatescreenitem.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function templatescreenitemPkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('templatescreenitem.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method trigger.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function triggerGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('trigger.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method trigger.getObjects.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function triggerGetObjects($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('trigger.getObjects', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method trigger.exists.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function triggerExists($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('trigger.exists', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method trigger.checkInput.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function triggerCheckInput($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('trigger.checkInput', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method trigger.create.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function triggerCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('trigger.create', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method trigger.update.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function triggerUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('trigger.update', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method trigger.delete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function triggerDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('trigger.delete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method trigger.addDependencies.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function triggerAddDependencies($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('trigger.addDependencies', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method trigger.deleteDependencies.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function triggerDeleteDependencies($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('trigger.deleteDependencies', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method trigger.syncTemplates.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function triggerSyncTemplates($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('trigger.syncTemplates', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method trigger.syncTemplateDependencies.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function triggerSyncTemplateDependencies($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('trigger.syncTemplateDependencies', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method trigger.isReadable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function triggerIsReadable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('trigger.isReadable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method trigger.isWritable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function triggerIsWritable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('trigger.isWritable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method trigger.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function triggerTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('trigger.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method trigger.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function triggerPk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('trigger.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method trigger.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function triggerPkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('trigger.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method triggerprototype.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function triggerprototypeGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('triggerprototype.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method triggerprototype.create.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function triggerprototypeCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('triggerprototype.create', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method triggerprototype.update.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function triggerprototypeUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('triggerprototype.update', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method triggerprototype.delete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function triggerprototypeDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('triggerprototype.delete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method triggerprototype.syncTemplates.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function triggerprototypeSyncTemplates($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('triggerprototype.syncTemplates', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method triggerprototype.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function triggerprototypeTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('triggerprototype.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method triggerprototype.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function triggerprototypePk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('triggerprototype.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method triggerprototype.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function triggerprototypePkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('triggerprototype.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method user.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function userGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('user.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method user.create.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function userCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('user.create', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method user.update.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function userUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('user.update', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method user.updateProfile.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function userUpdateProfile($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('user.updateProfile', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method user.delete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function userDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('user.delete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method user.addMedia.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function userAddMedia($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('user.addMedia', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method user.updateMedia.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function userUpdateMedia($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('user.updateMedia', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method user.deleteMedia.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function userDeleteMedia($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('user.deleteMedia', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method user.deleteMediaReal.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function userDeleteMediaReal($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('user.deleteMediaReal', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method user.checkAuthentication.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function userCheckAuthentication($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('user.checkAuthentication', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method user.isReadable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function userIsReadable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('user.isReadable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method user.isWritable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function userIsWritable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('user.isWritable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method user.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function userTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('user.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method user.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function userPk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('user.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method user.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function userPkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('user.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method usergroup.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function usergroupGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('usergroup.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method usergroup.getObjects.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function usergroupGetObjects($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('usergroup.getObjects', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method usergroup.exists.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function usergroupExists($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('usergroup.exists', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method usergroup.create.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function usergroupCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('usergroup.create', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method usergroup.update.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function usergroupUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('usergroup.update', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method usergroup.massAdd.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function usergroupMassAdd($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('usergroup.massAdd', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method usergroup.massUpdate.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function usergroupMassUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('usergroup.massUpdate', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method usergroup.delete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function usergroupDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('usergroup.delete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method usergroup.isReadable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function usergroupIsReadable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('usergroup.isReadable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method usergroup.isWritable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function usergroupIsWritable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('usergroup.isWritable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method usergroup.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function usergroupTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('usergroup.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method usergroup.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function usergroupPk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('usergroup.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method usergroup.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function usergroupPkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('usergroup.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method usermacro.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function usermacroGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('usermacro.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method usermacro.createGlobal.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function usermacroCreateGlobal($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('usermacro.createGlobal', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method usermacro.updateGlobal.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function usermacroUpdateGlobal($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('usermacro.updateGlobal', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method usermacro.deleteGlobal.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function usermacroDeleteGlobal($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('usermacro.deleteGlobal', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method usermacro.create.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function usermacroCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('usermacro.create', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method usermacro.update.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function usermacroUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('usermacro.update', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method usermacro.delete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function usermacroDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('usermacro.delete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method usermacro.replaceMacros.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function usermacroReplaceMacros($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('usermacro.replaceMacros', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method usermacro.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function usermacroTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('usermacro.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method usermacro.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function usermacroPk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('usermacro.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method usermacro.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function usermacroPkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('usermacro.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method usermedia.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function usermediaGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('usermedia.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method usermedia.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function usermediaTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('usermedia.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method usermedia.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function usermediaPk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('usermedia.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method usermedia.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function usermediaPkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('usermedia.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method httptest.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function httptestGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('httptest.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method httptest.create.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function httptestCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('httptest.create', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method httptest.update.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function httptestUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('httptest.update', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method httptest.delete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function httptestDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('httptest.delete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method httptest.isReadable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function httptestIsReadable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('httptest.isReadable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method httptest.isWritable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function httptestIsWritable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('httptest.isWritable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method httptest.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function httptestTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('httptest.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method httptest.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function httptestPk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('httptest.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method httptest.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function httptestPkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('httptest.pkOption', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method webcheck.get.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function webcheckGet($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('webcheck.get', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method webcheck.create.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function webcheckCreate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('webcheck.create', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method webcheck.update.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function webcheckUpdate($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('webcheck.update', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method webcheck.delete.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function webcheckDelete($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('webcheck.delete', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method webcheck.isReadable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function webcheckIsReadable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('webcheck.isReadable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method webcheck.isWritable.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function webcheckIsWritable($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('webcheck.isWritable', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method webcheck.tableName.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function webcheckTableName($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('webcheck.tableName', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method webcheck.pk.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function webcheckPk($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('webcheck.pk', $params, $arrayKeyProperty);
	}

	/**
	 * @brief   Reqeusts the Zabbix API and returns the response of the API
	 *          method webcheck.pkOption.
	 *
	 * The $params Array can be used, to pass through params to the Zabbix API.
	 * For more informations about this params, check the Zabbix API
	 * Documentation.
	 *
	 * The $arrayKeyProperty is "PHP-internal" and can be used, to get an
	 * associatve instead of an indexed array as response. A valid value for
	 * this $arrayKeyProperty is any property of the returned JSON objects
	 * (e.g. name, host, hostid, graphid, screenitemid).
	 *
	 * @param   $params             Parameters to pass through.
	 * @param   $arrayKeyProperty   Object property for key of array.
	 *
	 * @retval  stdClass
	 *
	 * @throws  Exception
	 */

	public function webcheckPkOption($params=array(), $arrayKeyProperty='')
	{
		// get params array for request
		$params = $this->getRequestParamsArray($params);

		// request
		return $this->request('webcheck.pkOption', $params, $arrayKeyProperty);
	}


}
