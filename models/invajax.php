<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla modelitem library
jimport('joomla.application.component.modelitem');


class StalramsModelInvajax extends JModelLegacy
{	
	function getLDAP($id){
		try {
			$db = JFactory::getDBO();// Подключаемся к базе.
			$query = "SELECT
					  #__neq_region.id,
					  #__neq_region.Name,
					  #__neq_region.domen,
					  #__neq_region.ldap,
					  #__neq_region.searh_ad,
					  #__neq_region.login_ad,
					  #__neq_region.password_ad
					FROM #__neq_region
					WHERE #__neq_region.id = {$id}";//Определяем запрос
			$db->setQuery($query);//Выполняем запрос
			if ($rList = $db->loadObjectList()) {
				return $rList;
			}
			else {
				return $db->stderr();
			}
		}
		catch (PDOException $e) {
			return 'Ошибка базы данных: '.$e->getMessage();
		}
	}
	function AD2UTime($dateLargeInt)
	{
		// seconds since jan 1st 1601
		$secsAfterADEpoch = $dateLargeInt / (10000000);
		// unix epoch -
		// AD epoch * number of tropical days * seconds in a day
		$ADToUnixConvertor = 11644505404.704;
		// unix Timestamp version of AD timestamp
		$unixTimeStamp = intval($secsAfterADEpoch-$ADToUnixConvertor);
	
		return $unixTimeStamp;
	}
	
	function log($msg) //...................................... Функиция записи логов...................................
	{
		$user = &JFactory::getUser();
	
		$db = JFactory::getDbo();
		$db->setQuery("INSERT INTO #__inv_log (username, msg) VALUES ('".$user->name."','".$msg."')");
		$db->query();
	}
	
	function getLDAPImport($comp_neverlogon){
		try {
			
			//setlocale(LC_ALL, 'rus');
			
			//$model = $this->getModel();//Получение модели
			$region = JRequest::getVar('region');  			
			$char = JRequest::getVar('char');
			
			$result=$this->getLDAP($region); //Получаем текущий LDAP

				
			if (count($result) > 0) {
				$id=$result[0]->id;
				$name=$result[0]->Name;
				$domen=$result[0]->domen;
				$ldap=$result[0]->ldap;
				$searh=$result[0]->searh_ad;
				$userPName=$result[0]->login_ad;
				$passw=$result[0]->password_ad;
			}
			
			
			
			if (JRequest::getVar('layout') == null){
				//$this->rezSearch = '';
				
				$ad=ldap_connect($ldap);  // обязан быть правильный LDAP-сервер!
				if ($ad){
					ldap_set_option ($ad, LDAP_OPT_PROTOCOL_VERSION, 3);
					ldap_set_option ($ad, LDAP_OPT_REFERRALS, 0);
					$justthese = array( "lastlogontimestamp");
					if ($r = @ldap_bind ($ad, $userPName, $passw)){
						$sr=@ldap_search($ad, 'DC=tl,DC=MRSK-CP,DC=NET', "(&(cn=*)( sAMAccountType=805306369))",$justthese);
						$zap=@ldap_get_entries($ad, $sr);
						
						for ($i=0; $i<$zap["count"]; $i++){//Перебираем результаты поиска
							if ($zap[$i]['lastlogontimestamp'][0] == null) {$comp_neverlogon++; }
							if (time()-$this->AD2UTime($zap[$i]['lastlogontimestamp'][0]) > 2592000) {$comp_30days++;}//echo $zap[$i]['samaccountname'][0].' '.date( 'Y F d',$this->AD2UTime($zap[$i]['lastlogontimestamp'][0])).'<br>';}
							if (time()-$this->AD2UTime($zap[$i]['lastlogontimestamp'][0]) > 604800) {$comp_7days++;}// echo time()-$zap[$i]['lastlogon'][0].$zap[$i]['cn'][0].'<br>';}
		
						}
					}
					$comp_count = $zap["count"];//Всего учетных записей
					$cn_department = count($this->arrdep);//Всего наименований департаментов
		
					//print_r($zap);
					unset($zap);
					$justthese = array( "lastlogontimestamp","department");
					if ($r = @ldap_bind ($ad, $this->userPName, $this->passw)){
						$sr=@ldap_search($ad, 'DC=tl,DC=MRSK-CP,DC=NET', "(|(sn=*))",$justthese);
						$zap=@ldap_get_entries($ad, $sr);
						for ($i=0; $i<$zap["count"]; $i++){
							$this->arrdep[$i]=$zap[$i]['department'][0];
							if (time()-$this->AD2UTime($zap[$i]['lastlogontimestamp'][0]) > 2592000) {$this->user_30days++;}
							if (time()-$this->AD2UTime($zap[$i]['lastlogontimestamp'][0]) > 604800) {$this->user_7days++;}
							if ($zap[$i]['lastlogontimestamp'][0] == null){$this->user_neverlogon++;}
		
						}
						$this->arrdep=array_unique($this->arrdep);//приводим департаменты в нормальный вид
						sort($this->arrdep);
					}
					$this->user_count = $zap["count"];//Всего учетных записей
					$this->cn_department = count($this->arrdep);//Всего наименований департаментов
					unset($zap);
				}
				ldap_unbind ($ad);
			}
			
			if (JRequest::getVar('layout') == 'list'){
				$ad=ldap_connect($this->ldap);  // обязан быть правильный LDAP-сервер!
				$days=JRequest::getVar('days')*86400;
				$this->arrlist =array();
				if ($ad){
					ldap_set_option ($ad, LDAP_OPT_PROTOCOL_VERSION, 3);
					ldap_set_option ($ad, LDAP_OPT_REFERRALS, 0);
					if ($r = @ldap_bind ($ad, $this->userPName, $this->passw)){
						if (JRequest::getVar('type') == 'user'){
							$justthese = array( "lastlogontimestamp","name","objectsid");
							$sr=@ldap_search($ad, 'DC=tl,DC=MRSK-CP,DC=NET', "(|(sn=*))",$justthese);
						}
						else {
							$justthese = array( "lastlogontimestamp","name","objectsid");
							$sr=@ldap_search($ad, 'DC=tl,DC=MRSK-CP,DC=NET', "(&(cn=*)( sAMAccountType=805306369))");
						}
						$zap=@ldap_get_entries($ad, $sr);
						for ($i=0; $i<$zap["count"]; $i++){
							$this->arrdep[$i]=$zap[$i]['department'][0];
							//echo $zap[$i]['cn'][0].':'.$zap[$i]['samaccountname'][0].'<br> Последний вход'.date( 'Hhi l d F Y', $this->AD2UTime($zap[$i]['lastlogon'][0])).'   Запись создана: '.$zap[$i]['whencreated'][0].'<br>';
							if (time()-$this->AD2UTime($zap[$i]['lastlogontimestamp'][0]) > $days) {
								$this->arrlist[$i][name] = $zap[$i]['name'][0];
								$this->arrlist[$i][lastlogontimestamp] = $this->AD2UTime($zap[$i]['lastlogontimestamp'][0]);
								$this->arrlist[$i][objectsid] = $zap[$i]['objectsid'][0];
							}// echo $zap[$i]['name'][0].'<br>';}
		
						}
						sort($this->arrlist);
						//print_r ($this->arrlist);
					}
					unset($zap);
				}
				ldap_unbind ($ad);
			}
			
			if (JRequest::getVar('layout') == 'usersadd'){				
				
				$ad=ldap_connect($ldap);
				if ($ad){
					
					$db = JFactory::getDbo();//Подключаемся к базе
					
					ldap_set_option ($ad, LDAP_OPT_PROTOCOL_VERSION, 3);
					ldap_set_option ($ad, LDAP_OPT_REFERRALS, 0);
					
					if ($r = @ldap_bind ($ad, $userPName, $passw)){
						
						$justthese = array("cn","sn","title","description","telephonenumber","givenname","whencreated","whenchanged","displayname","department","company","name","badpwdcount","badpasswordtime","lastlogoff",
							"lastlogon","pwdlastset","accountexpires","logoncount","samaccountname","userprincipalname","lastlogontimestamp","mail","pager","dn", "division", "mobile" );
						$seach=  "(&(cn={$char}*)(sAMAccountType=805306368))";
						$sr=@ldap_search($ad, $searh, $seach, $justthese);
						$zap=@ldap_get_entries($ad, $sr);
						
						for ($i=0; $i<$zap["count"]; $i++){
							try {
							
								$zap[$i]['lastlogoff'][0] = date('c',$this->AD2UTime($zap[$i]['lastlogoff'][0]));
								$zap[$i]['lastlogon'][0] = date('c',$this->AD2UTime($zap[$i]['lastlogon'][0]));
								$zap[$i]['badpasswordtime'][0] = date('c',$zap[$i]['badpasswordtime'][0]);
								$zap[$i]['lastlogontimestamp'][0] = date('c',$zap[$i]['lastlogontimestamp'][0]);
								$zap[$i]['accountexpires'][0] = date('c',$zap[$i]['accountexpires'][0]);
								$zap[$i]['pwdlastset'][0] = date('c',$zap[$i]['pwdlastset'][0]);
								$zap[$i]['accountexpires'][0] = date('c',$zap[$i]['accountexpires'][0]);
								
								
								$query = "SELECT COUNT(id) AS count FROM #__inv_users WHERE #__inv_users.samaccountname='{$zap[$i]['samaccountname'][0]}' AND #__inv_users.id_reg = {$region}";
								$db->setQuery($query);
								$rows = $db->loadResult();

                                $rez->region = $region;
                                $rez->update = 0;
                                $rez->insert = 0;
								
								if ($rows > 0) {
                                    $db->setQuery("UPDATE #__inv_users
                                                    SET cn= '{$zap[$i]['cn'][0]}',
                                                        sn = '{$zap[$i]['sn'][0]}',
                                                        title = '{$zap[$i]['title'][0]}',
                                                        description = '{$zap[$i]['description'][0]}',
                                                        telephonenumber = '{$zap[$i]['telephonenumber'][0]}',
                                                        givenname = '{$zap[$i]['givenname'][0]}',
                                                        whencreated = '{$zap[$i]['whencreated'][0]}',
                                                        whenchanged = '{$zap[$i]['whenchanged'][0]}',
                                                        displayname = '{$zap[$i]['displayname'][0]}',
                                                        department = '{$zap[$i]['department'][0]}',
                                                        division = '{$zap[$i]['division'][0]}',
                                                        company = '{$zap[$i]['company'][0]}',
                                                        name = '{$zap[$i]['name'][0]}',
                                                        badpwdcount = '{$zap[$i]['badpwdcount'][0]}',
                                                        badpasswordtime = '{$zap[$i]['badpasswordtime'][0]}',
                                                        lastlogoff = '{$zap[$i]['lastlogoff'][0]}',
                                                        lastlogon = '{$zap[$i]['lastlogon'][0]}',
                                                        pwdlastset = '{$zap[$i]['pwdlastset'][0]}',
                                                        accountexpires = '{$zap[$i]['accountexpires'][0]}',
                                                        logoncount = '{$zap[$i]['logoncount'][0]}',
                                                        userprincipalname = '{$zap[$i]['userprincipalname'][0]}',
                                                        lastlogontimestamp = '{$zap[$i]['lastlogontimestamp'][0]}',
                                                        mail = '{$zap[$i]['mail'][0]}',
                                                        pager = '{$zap[$i]['pager'][0]}',
                                                        dn = '{$zap[$i]['dn']}',
                                                        mobile = '{$zap[$i]['mobile'][0]}'
                                                        WHERE #__inv_users.samaccountname='{$zap[$i]['samaccountname'][0]}' AND #__inv_users.id_reg = {$region}");
                                    $db->query();
                                    $rez->update++;
								}else {
									$db->setQuery("INSERT INTO #__inv_users
									(id_reg, cn,sn,title,description,telephonenumber,givenname,whencreated,whenchanged,displayname,department,division,company,name,badpwdcount,badpasswordtime,lastlogoff,
									lastlogon,pwdlastset,accountexpires,logoncount,samaccountname,userprincipalname,lastlogontimestamp,mail,pager,dn,mobile )
									 VALUES ({$region},
											'{$zap[$i]['cn'][0]}',
											'{$zap[$i]['sn'][0]}',
											'{$zap[$i]['title'][0]}',
											'{$zap[$i]['description'][0]}',
											'{$zap[$i]['telephonenumber'][0]}',
											'{$zap[$i]['givenname'][0]}',
											'{$zap[$i]['whencreated'][0]}',
											'{$zap[$i]['whenchanged'][0]}',
											'{$zap[$i]['displayname'][0]}',
											'{$zap[$i]['department'][0]}',
											'{$zap[$i]['division'][0]}',
											'{$zap[$i]['company'][0]}',
											'{$zap[$i]['name'][0]}',
											'{$zap[$i]['badpwdcount'][0]}',
											'{$zap[$i]['badpasswordtime'][0]}',
											'{$zap[$i]['lastlogoff'][0]}',
											'{$zap[$i]['lastlogon'][0]}',
											'{$zap[$i]['pwdlastset'][0]}',
											'{$zap[$i]['accountexpires'][0]}',
											'{$zap[$i]['logoncount'][0]}',
											'{$zap[$i]['samaccountname'][0]}',
											'{$zap[$i]['userprincipalname'][0]}',
											'{$zap[$i]['lastlogontimestamp'][0]}',
											'{$zap[$i]['mail'][0]}',
											'{$zap[$i]['pager'][0]}',
											'{$zap[$i]['dn']}',
											'{$zap[$i]['mobile'][0]}')");
									$db->query();
                                    $rez->insert++;
								}
							}
							catch (PDOException $e) {
								return 'Ошибка базы данных: '.$e->getMessage();
							}
						}
					}
					unset($zap);
				}
			}
			if (JRequest::getVar('layout') == 'pcadd'){
			
				$ad=ldap_connect($ldap);
				if ($ad){
						
					$db = JFactory::getDbo();//Подключаемся к базе
						
					ldap_set_option ($ad, LDAP_OPT_PROTOCOL_VERSION, 3);
					ldap_set_option ($ad, LDAP_OPT_REFERRALS, 0);
						
					if ($r = @ldap_bind ($ad, $userPName, $passw)){
			
						$justthese = array("cn","distinguishedname", "whencreated", "whenchanged", "displayname","lastlogoff", "lastlogon",
										   "operatingsystem", "operatingsystemversion", "operatingsystemservicepack", "dnshostname", "dn");
						$seach=  "(&(cn={$char}*)(sAMAccountType=805306369))";
						$sr=@ldap_search($ad, $searh, $seach, $justthese);
						$zap=@ldap_get_entries($ad, $sr);
			
						for ($i=0; $i<$zap["count"]; $i++){
							try {
									
								$zap[$i]['lastlogoff'][0] = date('c',$this->AD2UTime($zap[$i]['lastlogoff'][0]));
								$zap[$i]['lastlogon'][0] = date('c',$this->AD2UTime($zap[$i]['lastlogon'][0]));
											
								$query = "SELECT COUNT(id) AS count FROM #__inv_adpc WHERE #__inv_adpc.cn='{$zap[$i]['cn'][0]}' AND #__inv_adpc.id_reg = {$region}";
								$db->setQuery($query);
								$rows = $db->loadResult();
			
								if ($rows > 0) {
										
								}else {
									$db->setQuery("INSERT INTO #__inv_adpc
											(id_reg, cn, distinguishedname, whencreated, whenchanged, displayname,lastlogoff, lastlogon,
										   operatingsystem, operatingsystemversion, operatingsystemservicepack, dnshostname, dn)
											VALUES ({$region},
													'{$zap[$i]['cn'][0]}',
													'{$zap[$i]['distinguishedname'][0]}',
													'{$zap[$i]['whencreated'][0]}',
													'{$zap[$i]['whenchanged'][0]}',
													'{$zap[$i]['displayname'][0]}',
													'{$zap[$i]['lastlogoff'][0]}',
													'{$zap[$i]['lastlogon'][0]}',
													'{$zap[$i]['operatingsystem'][0]}',
													'{$zap[$i]['operatingsystemversion'][0]}',
													'{$zap[$i]['operatingsystemservicepack'][0]}',
													'{$zap[$i]['dnshostname'][0]}',
													'{$zap[$i]['dn']}')");
									$db->query();
								}
							}
							catch (PDOException $e) {
								return 'Ошибка базы данных: '.$e->getMessage();
						}
					}
				
			}
			unset($zap);
			}
			}
			
			ldap_unbind ($ad);
            $rez->char =  $char;
            return json_encode($rez);
	
		}
		catch (PDOException $e) {
			return 'Ошибка базы данных: '.$e->getMessage();
		}
	}
	
	function getDocDir() //...................................... Функиция создания каталога...................................
	{
		
		//return mkdir($dir, 0777);
		
		switch (JRequest::getVar('oper')){
			case sel:
				$dir = JRequest::getVar('dir');
				$filelist = glob($dir."*.*");
				$baseurl = JURI::base();
				$i=0;
				foreach($filelist as $filename){ 
					$response->rows[$i]['id']=$i;
					$response->rows[$i]['cell']=array($baseurl.$filename, round(filesize($filename)/1048576, 3), $filename);
					$i++;
				}
				return json_encode($response);
				break;
			case del:
				$file = JRequest::getVar('file');
				unlink($file);				
				break;
		}		
	}
	
	function getNameToID(){//Получаем id по имени
		
		try{
			$table = JRequest::getVar('table');//Выбираем из таблицы
			$field = JRequest::getVar('field');//Выбираем поля
			$where = JRequest::getVar('where');
			
			$db = JFactory::getDBO();// Подключаемся к базе.			
			
			$query = "SELECT  #__{$table}.id FROM #__{$table} WHERE #__{$table}.{$field} = '{$where}'";
			$db->setQuery($query);
			$id = $db->loadResult();
			
			$rersponse[] = array ('id' =>$id);
		
			return json_encode($id);			
		}
		catch (PDOException $e) {
			return 'Ошибка базы данных: '.$e->getMessage();
		}
		
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
	
	function getSelectUsers(){
		try {
			
			$rList = $this->getAccessUser();//Получаем права пользователей
			
			if ($rList[0]->admin){
				$s_query = JRequest::getVar('term');
				$db = JFactory::getDbo();//Подключаемся к базе
				$query = "SELECT  #__users.name,  CONCAT_WS(' ', #__users.name, #__users.username) AS username FROM #__users WHERE #__users.name LIKE '".$s_query."%' ORDER BY username";
				$db->setQuery($query);//Выполняем запрос
				$rList = $db->loadObjectList();
					
			  $response = array();
			  foreach($rList as $row){
			    $response[]=array('label'=>$row->username, 'value'=>$row->name);
			  }
				return json_encode($response);
			}			
		}
		catch (PDOException $e) {
			return 'Ошибка базы данных: '.$e->getMessage();
		}
	}
	
	
	function getRegions() {//Редактирование регионов
			
		$rList = $this->getAccessUser();//Получаем права пользователей
	
		$oper = JRequest::getVar('oper');//Получаем тип операции
	
		$db = JFactory::getDbo();//Подключаемся к базе
		
		$user = &JFactory::getUser();
		
		$name= JRequest::getVar('name');
		$api=JRequest::getVar('api');
		$login=JRequest::getVar('login');
		$password=JRequest::getVar('password');
		$ocs=JRequest::getVar('ocs');
		$ocs_bd=JRequest::getVar('ocs_bd');
		$login_ocs=JRequest::getVar('login_ocs');
		$password_ocs=JRequest::getVar('password_ocs');
		$domen=JRequest::getVar('domen');
		$ldap=JRequest::getVar('ldap');
		$searh_ad=JRequest::getVar('searh_ad');
		$login_ad=JRequest::getVar('login_ad');
		$password_ad=JRequest::getVar('password_ad');
		$prn=JRequest::getVar('prn');
		$prn_bd=JRequest::getVar('prn_bd');
		$login_prn=JRequest::getVar('login_prn');
		$password_prn=JRequest::getVar('password_prn');

        $img=JRequest::getVar('img');
        $phone_code_in=JRequest::getVar('phone_code_in');
        $phone_code_out=JRequest::getVar('phone_code_out');
        $postcode=JRequest::getVar('postcode');
        $region=JRequest::getVar('region');
        $area=JRequest::getVar('area');
        $city=JRequest::getVar('city');
        $address=JRequest::getVar('address');
        $sp_msg=JRequest::getVar('sp_msg');
	
		if ($rList[0]->admin){
			switch (JRequest::getVar('oper')){
				case sel:
					$db = JFactory::getDbo();//Подключаемся к базе
					$query = "SELECT  #__neq_region.* FROM #__neq_region";
					$db->setQuery($query);//Выполняем запрос
					$rList = $db->loadObjectList();
						
					$i=0;
					foreach($rList as $row){
						$response->rows[$i]['id']=$row->id;
						$response->rows[$i]['cell']=array($row->Name, $row->api, $row->login, $row->password, $row->ocs, $row->ocs_bd, $row->login_ocs,
                                                          $row->password_ocs, $row->domen, $row->ldap, $row->searh_ad, $row->login_ad, $row->password_ad,
                                                          $row->prn, $row->prn_bd, $row->login_prn, $row->password_prn,
                                                          $row->img, $row->phone_code_in, $row->phone_code_out,
                                                          $row->postcode, $row->region, $row->area, $row->city, $row->address, $row->sp_msg  );
						$i++;
					}
					return json_encode($response);
					
					break;
				case selacl:
						$db = JFactory::getDbo();//Подключаемся к базе
						$query = "SELECT
								 #__neq_region.id AS id,
								  #__neq_region.Name AS value
								FROM #__inv_access_region
								  INNER JOIN #__neq_region
								    ON #__inv_access_region.id_access = #__neq_region.id
								WHERE #__inv_access_region.id_user = 140
								AND (#__inv_access_region.reg_read = 1
								OR #__inv_access_region.reg_write = 1)";
						$db->setQuery($query);//Выполняем запрос
				
						if ($rList = $db->loadObjectList()) {
							return $rList;
						}
						else {
							return $db->stderr();
						}
							
						break;
                case regpes:
                    $query = "SELECT
                                  #__neq_pes.id,
                                  #__neq_pes.regid,
                                  #__neq_region.Name AS reg,
                                  #__neq_pes.name AS pes,
                                  #__neq_pes.code
                                FROM #__neq_pes
                                  INNER JOIN #__neq_region
                                    ON #__neq_pes.regid = #__neq_region.id
                                  LEFT OUTER JOIN #__inv_access_region
                                    ON #__inv_access_region.id_access = #__neq_region.id
                                  LEFT OUTER JOIN #__inv_access_pes
                                    ON #__inv_access_pes.id_access = #__neq_pes.id
                                WHERE #__inv_access_region.id_user = {$user->id}
                                OR #__inv_access_pes.id_user = {$user->id}
                                GROUP BY #__neq_pes.name,
                                         #__neq_pes.id,
                                         #__neq_pes.regid
                                ORDER BY reg, pes ";
                    $db->setQuery($query);//Выполняем запрос
                    $rList = $db->loadObjectList();

                    $i=0;
                    foreach($rList as $row){
                        $response->rows[$i]['id']=$row->id;
                        $response->rows[$i]['cell']=array($row->reg, $row->pes, $row->regid, $row->code);
                        $i++;
                    }
                    return json_encode($response);
                    break;
				case add:
                    $img=JRequest::getVar('img');
                    $phone_code_in=JRequest::getVar('phone_code_in');
                    $phone_code_out=JRequest::getVar('phone_code_out');
                    $postcode=JRequest::getVar('postcode');
                    $region=JRequest::getVar('region');
                    $area=JRequest::getVar('area');
                    $city=JRequest::getVar('city');
                    $address=JRequest::getVar('address');
					$this->log("Добавлен регион ".JRequest::getVar('name'));
					$db->setQuery("INSERT INTO #__neq_region
							(name, api, login, password, ocs, ocs_bd, login_ocs, password_ocs, domen, ldap, searh_ad, login_ad, password_ad, prn, prn_bd, login_prn, password_prn,
							      img, phone_code_in, phone_code_out, postcode, region, area, city, address, sp_msg)
							VALUES ('{$name}', '{$api}', '{$login}', '{$password}', '{$ocs}', '{$ocs_bd}', '{$login_ocs}', '{$password_ocs}', '{$domen}', '{$ldap}', '{$searh_ad}', '{$login_ad}', '{$password_ad}',
							        '{$prn}', '{$prn_bd}', '{$login_prn}', '{$password_prn}',
							        '{$img}', '{$phone_code_in}', '{$phone_code_out}', '{$postcode}','{$region}', '{$area}', '{$city}', '{$address}', '{$sp_msg}')");
					$db->query();
					break;
				case del:
					$this->log("Удален регион".JRequest::getVar('id'));
					$id = JRequest::getVar('id');
					$db->setQuery("DELETE FROM #__neq_region WHERE #__neq_region.id = ".$id);
					$db->query();
					break;
				case edit:
					$this->log("Изменен регион ".JRequest::getVar('name'));
					$id = JRequest::getVar('id');
					$db->setQuery("UPDATE #__neq_region
						SET name='{$name}',
							api='{$api}',
							login='{$login}',
							password='{$password}',
							ocs='{$ocs}',
							ocs_bd='{$ocs_bd}',
							login_ocs='{$login_ocs}',
							password_ocs='{$password_ocs}',
							domen='{$domen}',
							ldap='{$ldap}',
							searh_ad='{$searh_ad}',
							login_ad='{$login_ad}',
							password_ad='{$password_ad}',
							prn='{$prn}',
							prn_bd='{$prn_bd}',
							login_prn='{$login_prn}',
							password_prn='{$password_prn}',
							img='{$img}',
							phone_code_in ='{$phone_code_in}',
                            phone_code_out='{$phone_code_out}',
                            postcode ='{$postcode}',
                            region ='{$region}',
                            area ='{$area}',
                            city ='{$city}',
                            address ='{$address}',
                            sp_msg ='{$sp_msg}'
						 	WHERE id = {$id}");
					$db->query();
					break;
			}
		}
	}
	
	function getPesEdit() {//Изменение производственных отделений
			
		$rList = $this->getAccessUser();//Получаем права пользователей

        $img=JRequest::getVar('img');
        $phone_code_in=JRequest::getVar('phone_code_in');
        $phone_code_out=JRequest::getVar('phone_code_out');
        $postcode=JRequest::getVar('postcode');
        $region=JRequest::getVar('region');
        $area=JRequest::getVar('area');
        $dn=JRequest::getVar('dn');
        $city=JRequest::getVar('city');
        $address=JRequest::getVar('address');
        $sp_msg=JRequest::getVar('sp_msg');
	
		$oper = JRequest::getVar('oper');//Получаем тип операции
	
		$db = JFactory::getDbo();//Подключаемся к базе
	
		if ($rList[0]->admin){
			switch (JRequest::getVar('oper')){
				case sel:
					$db = JFactory::getDbo();//Подключаемся к базе
					$query = "SELECT  #__neq_pes.* FROM #__neq_pes WHERE #__neq_pes.regid = ".JRequest::getVar('id');
					$db->setQuery($query);//Выполняем запрос
					$rList = $db->loadObjectList();
					
					$i=0;
					foreach($rList as $row){
						$response->rows[$i]['id']=$row->id;
						$response->rows[$i]['cell']=array($row->name, $row->code, $row->regid, $row->img, $row->phone_code_in, $row->phone_code_out,
                            $row->postcode, $row->region, $row->area, $row->city, $row->address, $row->sp_msg, $row->dn);
						$i++;
					}
					return json_encode($response);
					break;
				case add:
					$this->log("Добавлено ПО ".JRequest::getVar('name'));
					$db->setQuery("INSERT INTO #__neq_pes
							(regid, name, code, img, phone_code_in, phone_code_out, postcode, region, area, city, address, sp_msg, dn)
							VALUES ('".JRequest::getVar('regid')."','".JRequest::getVar('name')."','".JRequest::getVar('code')."','{$img}', '{$phone_code_in}', '{$phone_code_out}', '{$postcode}','{$region}', '{$area}', '{$city}', '{$address}', '{$sp_msg}', '{$dn}')");
					$db->query();
					break;
				case del:
					$this->log("Удалено ПО с id ".JRequest::getVar('id'));
					$id = JRequest::getVar('id');
					$db->setQuery("DELETE FROM #__neq_pes WHERE #__neq_pes.id = ".$id);
					$db->query();
					break;
				case edit:
					$this->log("Изменено ПО ".JRequest::getVar('name'));
					$id = JRequest::getVar('id');
					$db->setQuery("UPDATE #__neq_pes
						SET regid='".JRequest::getVar('regid')."',
						   	name='".JRequest::getVar('name')."',
							code='".JRequest::getVar('code')."',
							img='{$img}',
							phone_code_in ='{$phone_code_in}',
                            phone_code_out='{$phone_code_out}',
                            postcode ='{$postcode}',
                            region ='{$region}',
                            area ='{$area}',
                            city ='{$city}',
                            address ='{$address}',
                            sp_msg ='{$sp_msg}',
                            dn ='{$dn}'
						 	WHERE id = ".$id);
					$db->query();
					break;
			}
		}
	}
	
	function getLocation() {//Перемещение
			
		$rList = $this->getAccessUser();//Получаем права пользователей	
		$oper = JRequest::getVar('oper');//Получаем тип операции	
		$db = JFactory::getDbo();//Подключаемся к базе
		$id = JRequest::getVar('id');
		if ($rList[0]->admin){
			switch (JRequest::getVar('oper')){
				case sel:
					$db = JFactory::getDbo();//Подключаемся к базе
					$query = "SELECT
							  #__inv_obj_moving.id_obj,
							  #__inv_obj_moving.date,
							  #__inv_obj_moving.code,
							  #__neq_region.Name AS region,
							  #__neq_pes.name AS pes,
							  #__neq_po.name AS po,
							  #__neq_a.name AS a
							FROM #__inv_obj_moving
							  INNER JOIN #__neq_region
							    ON #__inv_obj_moving.id_region = #__neq_region.id
							  LEFT OUTER JOIN #__neq_pes
							    ON #__inv_obj_moving.id_pes = #__neq_pes.id
							  LEFT OUTER JOIN #__neq_po
							    ON #__inv_obj_moving.id_po = #__neq_po.id
							  LEFT OUTER JOIN #__neq_a
							    ON #__inv_obj_moving.id_a = #__neq_a.id
							WHERE #__inv_obj_moving.id_obj = {$id}
							ORDER BY #__inv_obj_moving.date DESC";
					$db->setQuery($query);//Выполняем запрос
					$rList = $db->loadObjectList();
						
					$i=0;
					foreach($rList as $row){
						$response->rows[$i]['id']=$row->id_obj;
						$response->rows[$i]['cell']=array($row->date, $row->code, $row->region, $row->pes, $row->po, $row->a);
						$i++;
					}
					return json_encode($response);
					break;
			}
		}
	}
	function getUserChange() {//Перемещение
			
		$rList = $this->getAccessUser();//Получаем права пользователей
		$oper = JRequest::getVar('oper');//Получаем тип операции
		$db = JFactory::getDbo();//Подключаемся к базе
		$id = JRequest::getVar('id');
		if ($rList[0]->admin){
			switch (JRequest::getVar('oper')){
				case sel:
					$db = JFactory::getDbo();//Подключаемся к базе
					$query = "SELECT
							  #__inv_obj_users.date,
							  #__inv_users.cn AS user,
							  #__inv_users.department
							FROM #__inv_obj_users
							  LEFT JOIN #__inv_users
							    ON #__inv_obj_users.id_user = #__inv_users.id
							WHERE #__inv_obj_users.id_obj = {$id}
							ORDER BY #__inv_obj_users.date DESC";
					$db->setQuery($query);//Выполняем запрос
					$rList = $db->loadObjectList();
	
					$i=0;
					foreach($rList as $row){
						$response->rows[$i]['id']=$i;
						$response->rows[$i]['cell']=array($row->date, $row->user, $row->department);
						$i++;
					}
					return json_encode($response);
					break;
			}
		}
	}
	
	function getRespUserChange() {//Перемещение
			
		$rList = $this->getAccessUser();//Получаем права пользователей
		$oper = JRequest::getVar('oper');//Получаем тип операции
		$db = JFactory::getDbo();//Подключаемся к базе
		$id = JRequest::getVar('id');
		if ($rList[0]->admin){
			switch (JRequest::getVar('oper')){
				case sel:
					$db = JFactory::getDbo();//Подключаемся к базе
					$query = "SELECT
							  #__inv_obj_resp_users.date,
							  #__inv_users.cn AS user,
							  #__inv_users.department
							FROM #__inv_obj_resp_users
							  LEFT JOIN #__inv_responsible_user
							    ON #__inv_obj_resp_users.id_user = #__inv_responsible_user.id_user
							  LEFT JOIN #__inv_users
							    ON #__inv_responsible_user.id_user = #__inv_users.id
							WHERE #__inv_obj_resp_users.id_obj = {$id}
							ORDER BY #__inv_obj_resp_users.date DESC";
					$db->setQuery($query);//Выполняем запрос
					$rList = $db->loadObjectList();
	
					$i=0;
					foreach($rList as $row){
						$response->rows[$i]['id']=$i;
						$response->rows[$i]['cell']=array($row->date, $row->user, $row->department);
						$i++;
					}
					return json_encode($response);
					break;
			}
		}
	}
	function getCompConfigChange() {//Перемещение
			
		$rList = $this->getAccessUser();//Получаем права пользователей
		$oper = JRequest::getVar('oper');//Получаем тип операции
		$db = JFactory::getDbo();//Подключаемся к базе
		$id = JRequest::getVar('id');
		if ($rList[0]->admin){
			switch (JRequest::getVar('oper')){
				case sel:
					$db = JFactory::getDbo();//Подключаемся к базе
					$query = "SELECT
							  #__inv_comp_hardware.*
							FROM #__inv_comp_hardware
							  INNER JOIN #__inv_computer
							    ON #__inv_comp_hardware.id_computer = #__inv_computer.id
							  INNER JOIN #__inv_object
							    ON #__inv_computer.id_obj = #__inv_object.id
							WHERE #__inv_object.id = {$id}
							ORDER BY #__inv_comp_hardware.date DESC";
					$db->setQuery($query);//Выполняем запрос
					$rList = $db->loadObjectList();
	
					$i=0;
					foreach($rList as $row){
						$response->rows[$i]['id']=$i;
						$response->rows[$i]['cell']=array($row->date, $row->processor, $row->mem,  $row->moterboard,  $row->hdd,  $row->psu,  $row->graphics,  $row->network,  $row->mac,  $row->description);
						$i++;
					}
					return json_encode($response);
					break;
			}
		}
	}
	
	function getJournal() {//Перемещение
			
		$rList = $this->getAccessUser();//Получаем права пользователей
		$oper = JRequest::getVar('oper');//Получаем тип операции
		$db = JFactory::getDbo();//Подключаемся к базе
		$id = JRequest::getVar('id');
		
		if ($rList[0]->admin){
			switch (JRequest::getVar('oper')){
				case sel:
					$curPage = JRequest::getVar('page');
					$rowsPerPage = JRequest::getVar('rows');
					$sortingField = JRequest::getVar('sidx');
					$sortingOrder =JRequest::getVar('sord');
					$search = JRequest::getVar('_search');
					$stor = JRequest::getVar('stor');
				
					$where = " #__inv_log.id LIKE '%'";
					if ($search == 'true'){
						if (JRequest::getVar('date_time')) $where .= " AND #__inv_log.date_time LIKE '%".JRequest::getVar('date_time')."%' ";
						if (JRequest::getVar('username')) $where .= " AND #__inv_log.username LIKE '%".JRequest::getVar('username')."%' ";
						if (JRequest::getVar('msg')) $where .= " AND #__inv_log.msg LIKE '%".JRequest::getVar('msg')."%' ";
					}
				
					$query = "SELECT COUNT(id) AS count FROM #__inv_log WHERE  ".$where;
					$db->setQuery($query);
					$rows = $db->loadResult();
						
						
					$totalRows = $rows;
					$firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
				
					$query = "SELECT #__inv_log.* FROM #__inv_log
								WHERE ".$where." ORDER BY ".$sortingField." ".$sortingOrder." LIMIT ".$firstRowIndex.', '.$rowsPerPage;
						
					$db->setQuery($query);//Выполняем запрос
					$rList = $db->loadObjectList();
					$response->page = $curPage;
					$response->total = ceil($totalRows / $rowsPerPage);
					$response->records = $totalRows;
	
					$i=0;
					foreach($rList as $row){
						$response->rows[$i]['id']=$id;
						$response->rows[$i]['cell']=array($row->date_time, $row->username, $row->msg);
						$i++;
					}
					return json_encode($response);
					break;
			}
		}
	}

    /**
     * //Списание материалов
     * @return string
     */
    function getDebit() {
			
		$rList = $this->getAccessUser();//Получаем права пользователей
		$oper = JRequest::getVar('oper');//Получаем тип операции
		$db = JFactory::getDbo();//Подключаемся к базе
		$id = JRequest::getVar('id');
	
		if ($rList[0]->admin){
			switch ($oper){
				case sel:
					$curPage = JRequest::getVar('page');
					$rowsPerPage = JRequest::getVar('rows');
					$sortingField = JRequest::getVar('sidx');
					$sortingOrder =JRequest::getVar('sord');
					$search = JRequest::getVar('_search');
					
					$id_sup = JRequest::getVar('id_sup');
					
	
					$where ='';
					
				//	$where = " WHERE #__inv_sup_debit.id LIKE '%'";
					
					if ($search == 'true' || $id_sup){
						$where = " WHERE #__inv_sup_debit.id LIKE '%'";
						if (JRequest::getVar('id_sup')) $where .= " AND #__inv_sup_debit.id_sup = {$id_sup}";
						if (JRequest::getVar('username')) $where .= " AND #__inv_log.username LIKE '%".JRequest::getVar('username')."%' ";
						if (JRequest::getVar('msg')) $where .= " AND #__inv_log.msg LIKE '%".JRequest::getVar('msg')."%' ";
					}
	
					$query = "SELECT COUNT(#__inv_sup_debit.id) AS count
								 FROM #__inv_sup_debit
								  LEFT OUTER JOIN #__inv_object
								    ON #__inv_sup_debit.id_obj = #__inv_object.id
								  LEFT OUTER JOIN #__inv_supplier
								    ON #__inv_object.id_supplier = #__inv_supplier.id
								  LEFT OUTER JOIN #__inv_users
								    ON #__inv_sup_debit.id_user_for = #__inv_users.id {$where}";
					$db->setQuery($query);
					$rows = $db->loadResult();
	
	
					$totalRows = $rows;
					$firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
	
					$query = "SELECT
								  #__inv_sup_debit.id,
								  #__inv_sup_debit.deb_count,
								  #__inv_sup_debit.deb_date,
								  #__inv_object.name,
								  #__inv_object.s_number,
								  #__inv_object.i_number,
								  #__inv_object.i_number_adv,
								  #__inv_object.barcode,
								  #__inv_supplier.supplier,
								  #__inv_users.cn AS user,
								  #__inv_users.department,
								  #__inv_sup_debit.id_obj
								FROM #__inv_sup_debit
								  LEFT OUTER JOIN #__inv_object
								    ON #__inv_sup_debit.id_obj = #__inv_object.id
								  LEFT OUTER JOIN #__inv_supplier
								    ON #__inv_object.id_supplier = #__inv_supplier.id
								  LEFT OUTER JOIN #__inv_users
								    ON #__inv_sup_debit.id_user_for = #__inv_users.id
								{$where} ORDER BY ".$sortingField." ".$sortingOrder." LIMIT ".$firstRowIndex.', '.$rowsPerPage;
	
					$db->setQuery($query);//Выполняем запрос
					$rList = $db->loadObjectList();
					$response->page = $curPage;
					$response->total = ceil($totalRows / $rowsPerPage);
					$response->records = $totalRows;
	
					$i=0;
					foreach($rList as $row){
						$response->rows[$i]['id']=$row->id;
						$response->rows[$i]['cell']=array($row->deb_count, $row->deb_date, $row->user, $row->department, $row->name, $row->s_numbeer, $row->i_number,$row->i_number_adv, $row->barcode,
														$row->supplier, $row->id_obj);
						$i++;
					}
					return json_encode($response);
					break;
			}
		}
	}
	
	function getAdUsers(){
		$rList = $this->getAccessUser();//Получаем права пользователей
		
		$oper = JRequest::getVar('oper');//Получаем тип операции
		

        $id = JRequest::getVar('id');

        $curPage = JRequest::getVar('page');
        $rowsPerPage = JRequest::getVar('rows');
        $sortingField = JRequest::getVar('sidx');
        $sortingOrder =JRequest::getVar('sord');
        $search = JRequest::getVar('_search');
        $reg = JRequest::getVar('reg');
        $cn = JRequest::getVar('cn');
        $department = JRequest::getVar('department');
        $division = JRequest::getVar('division');
        $title = JRequest::getVar('title');
        $hide_sp = JRequest::getVar('hide_sp');
        $dismiss = JRequest::getVar('dismiss');
        $telephonenumber = JRequest::getVar('telephonenumber');
        $pager = JRequest::getVar('pager');
        $mobile = JRequest::getVar('mobile');
        $mail = JRequest::getVar('mail');
        $description = JRequest::getVar('description');
        $sn = JRequest::getVar('sn');
        $givenname = JRequest::getVar('givenname');
        $dn = JRequest::getVar('dn');
        $company = JRequest::getVar('company');

        $db = JFactory::getDbo();//Подключаемся к базе
		
		if ($rList[0]->admin){
			switch ($oper){
				case sel:

					$where = " #__inv_users.id LIKE '%'";
					if ($search == 'true'){
						if ($reg) $where .= " AND #__neq_region.Name LIKE '{$reg}%' ";
                        if ($cn) $where .= " AND sn LIKE '%{$cn}%' ";
                        if ($department) $where .= " AND department LIKE '%{$department}%' ";
                        if ($division) $where .= " AND division LIKE '{$division}%' ";
                        if ($title) $where .= " AND title LIKE '{$title}%' ";
                        if ($hide_sp) $where .= " AND hide_sp = {$hide_sp} ";
                        if ($dismiss) $where .= " AND dismiss = {$dismiss} ";
                        if ($telephonenumber) $where .= " AND telephonenumber LIKE '{$telephonenumber}%' ";
                        if ($pager) $where .= " AND pager LIKE '{$pager}%' ";
                        if ($mobile) $where .= " AND mobile LIKE '{$mobile}%' ";
                        if ($mail) $where .= " AND mail LIKE '{$mail}%' ";
                        if ($description) $where .= " AND description LIKE '{$description}%' ";
                        if ($sn) $where .= " AND sn LIKE '%{$sn}%' ";
                        if ($givenname) $where .= " AND givenname LIKE '%{$givenname}%' ";
                        if ($dn) $where .= " AND dn LIKE '{$dn}%' ";
                        if ($company) $where .= " AND company LIKE '{$company}%' ";
					}
				
					$query = "SELECT COUNT(#__inv_users.id) AS count FROM #__inv_users  INNER JOIN #__neq_region
                                  ON #__inv_users.id_reg = #__neq_region.id WHERE  ".$where;
					$db->setQuery($query);
					$rows = $db->loadResult();
						
						
					$totalRows = $rows;
					$firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
				
					$query = "SELECT #__inv_users.*,
                                     #__neq_region.Name AS reg
                              FROM #__inv_users
                                INNER JOIN #__neq_region
                                  ON #__inv_users.id_reg = #__neq_region.id
								WHERE ".$where." ORDER BY ".$sortingField." ".$sortingOrder." LIMIT ".$firstRowIndex.', '.$rowsPerPage;
					$db->setQuery($query);//Выполняем запрос
					$rList = $db->loadObjectList();
					$response->page = $curPage;
					$response->total = ceil($totalRows / $rowsPerPage);
					$response->records = $totalRows;
						
					$i=0;
					foreach($rList as $row){
						$response->rows[$i]['id']=$row->id;
						$response->rows[$i]['cell']=array(  $row->reg,
															$row->cn,
                                                            $row->department,
                                                            $row->division,
                                                            $row->title,
                                                            $row->hide_sp,
                                                            $row->dismiss,
                                                            $row->telephonenumber,
                                                            $row->pager,
                                                            $row->mobile,
                                                            $row->mail,
															$row->description,
                                                            $row->sn,
															$row->givenname,
															$row->whencreated,
															$row->whenchanged,
															$row->displayname,
															$row->company,
															$row->name,
															$row->badpwdcount,
															$row->badpasswordtime,
															$row->lastlogoff,
															$row->lastlogon,
															$row->pwdlastset,
															$row->accountexpires,
															$row->logoncount,
															$row->samaccountname,
															$row->userprincipalname,
															$row->lastlogontimestamp,
															$row->dn
						);
						$i++;
					}
					return json_encode($response);
				
					break;
				case add:

					$db->setQuery("INSERT INTO #__vet_service_group
							(id_clinic, group_name, veterinarian_percent)
							VALUES ('".$rList[0]->id_clinic."',
									'".JRequest::getVar('group_name')."',
									'".JRequest::getVar('veterinarian_percent')."')");
				
					if ($db->query()) {
						return true;
					}
					else {
						$this->log( $db->stderr());
						return $db->stderr();
					}
                    $this->log("Добавлена группа".JRequest::getVar('id'));
					break;
				case add:
					$db->setQuery("INSERT INTO #__neq_base
							(name, code)
							VALUES ('".JRequest::getVar('name')."','".JRequest::getVar('code')."')");
					$db->query();
                    $this->log("Добавлен тип оборудования ".JRequest::getVar('name'));
					break;
				case del:
					$db->setQuery("DELETE FROM #__inv_users WHERE #__inv_users.id = ".$id);
					$db->query();
                    $this->log("Удален пользователь {$id}");
					break;
				case edit:
					$db->setQuery("UPDATE #__inv_users
						SET hide_sp = {$hide_sp},
							dismiss = {$dismiss}
						 	WHERE id = {$id}");
					$db->query();
                    $this->log("Изменен пользователь {$id}");
					break;
			}
		}		
	}
	
	function getAdPC(){
		$rList = $this->getAccessUser();//Получаем права пользователей
	
		$oper = JRequest::getVar('oper');//Получаем тип операции
	
		$db = JFactory::getDbo();//Подключаемся к базе
	
		if ($rList[0]->admin){
			switch (JRequest::getVar('oper')){
				case sel:
					//читаем параметры
	
					$curPage = JRequest::getVar('page');
					$rowsPerPage = JRequest::getVar('rows');
					$sortingField = JRequest::getVar('sidx');
					$sortingOrder =JRequest::getVar('sord');
					$search = JRequest::getVar('_search');
					$stor = JRequest::getVar('stor');
	
					$where = " #__inv_adpc.id LIKE '%'";
					if ($search == 'true'){
						if (JRequest::getVar('sn')) $where .= " AND #__inv_adpc.cn LIKE '".JRequest::getVar('cn')."%' ";
					}
	
					$query = "SELECT COUNT(id) AS count FROM #__inv_adpc WHERE  ".$where;
					$db->setQuery($query);
					$rows = $db->loadResult();
	
	
					$totalRows = $rows;
					$firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
	
					$query = "SELECT #__inv_adpc.* FROM #__inv_adpc
								WHERE ".$where." ORDER BY ".$sortingField." ".$sortingOrder." LIMIT ".$firstRowIndex.', '.$rowsPerPage;
	
					$db->setQuery($query);//Выполняем запрос
					$rList = $db->loadObjectList();
					$response->page = $curPage;
					$response->total = ceil($totalRows / $rowsPerPage);
					$response->records = $totalRows;
	
					$i=0;
					foreach($rList as $row){
						$response->rows[$i]['id']=$row->id;
						$response->rows[$i]['cell']=array( 
								$row->id_reg,
								$row->cn,
								$row->distinguishedname,
								$row->whencreated,
								$row->whenchanged,
								$row->displayname,
								$row->lastlogoff,
								$row->lastlogon,
								$row->operatingsystem,
								$row->operatingsystemversion,
								$row->operatingsystemservicepack,
								$row->dnshostname,
								$row->dn
						);
						$i++;
					}
					return json_encode($response);
	
					break;
				
			}
		}
	
	}
	
	function  getAutoComplete() {//Получаем список для автозаполнения

		try {
			
			$user = &JFactory::getUser();
				
			$s_query = JRequest::getVar('term');//Первые буквы поля
			$table = JRequest::getVar('table');//Выбираем из таблицы
			$field= JRequest::getVar('field');//Выбираем поля
			$field2= JRequest::getVar('field2');//Выбираем поля
			$field3= JRequest::getVar('field3');//Выбираем поля
			$field4= JRequest::getVar('field4');//Выбираем поля
			$field5= JRequest::getVar('field5');//Выбираем поля
			$id_reg= JRequest::getVar('id_reg');//Если задан регион
			$group = JRequest::getVar('group');//Если задана группировка
			$strong = JRequest::getVar('strong');//Поиск по началу слова
			$limit = JRequest::getVar('limit');//Лимит по кол-ву записей в запросе
			
			if ($limit == '') $limit=15;
			
			$db = JFactory::getDbo();//Подключаемся к базе
			$select = "#__{$table}.id AS id, #__{$table}.{$field} AS label1";
			
			if ($strong == 1) $strong='';
			else $strong='%';
			
			if ($id_reg){
				$where = " #__{$table}.id_reg IN (SELECT  #__inv_access_region.id_access
														FROM #__inv_access_region
														WHERE #__inv_access_region.id_user ={$user->id}
														AND (#__inv_access_region.reg_read = 1 OR #__inv_access_region.reg_write = 1)) AND ";
			}
			
			$where .= "(#__{$table}.{$field} LIKE '{$strong}{$s_query}%'";
			if ($strong != ''){
				if ($field2){
					$select .= ", #__{$table}.{$field2} AS label2";
					$where .= " OR #__{$table}.{$field2} LIKE '{$strong}{$s_query}%'";
				}
				if ($field3){
					$select .= ", #__{$table}.{$field3} AS label3";
					$where .= " OR #__{$table}.{$field3} LIKE '{$strong}{$s_query}%'";
				}
				if ($field4){
					$select .= ", #__{$table}.{$field4} AS label4";
					$where .= " OR #__{$table}.{$field4} LIKE '{$strong}{$s_query}%'";
				}
				if ($field5){
					$select .= ", #__{$table}.{$field5} AS label5";
					$where .= " OR #__{$table}.{$field5} LIKE '{$strong}{$s_query}%'";
				}
				if ($field6){
					$select .= ", #__{$table}.{$field6} AS label6";
					$where .= " OR #__{$table}.{$field6} LIKE '{$strong}{$s_query}%'";
				}
			}
			else{
				if ($field2) $select .= ", #__{$table}.{$field2} AS label2";
				if ($field3) $select .= ", #__{$table}.{$field3} AS label3";
				if ($field4) $select .= ", #__{$table}.{$field4} AS label4";
				if ($field5) $select .= ", #__{$table}.{$field5} AS label5";
				if ($field6) $select .= ", #__{$table}.{$field6} AS label6";
			}
			$where .= ')';
			
			if ($group != '') $query = "SELECT  {$select} FROM #__{$table} WHERE  {$where} GROUP BY {$field}  ORDER BY {$field} LIMIT {$limit}";
			else $query = "SELECT  {$select} FROM #__{$table} WHERE  {$where} ORDER BY {$field} LIMIT 15";
			 
			$db->setQuery($query);//Выполняем запрос
			$rList = $db->loadObjectList();
				
			foreach($rList as $row){				
				if ($field2 != '')
					if ($field3 != '')
						if ($field4 != '')
							if ($field5 != '')
								if ($field6 != '') $response[]=array('id' => $row->id, 'label'=> $row->label1.'-/-'.$row->label2.'-/- '.$row->label3.'-/-'.$row->label4.'-/-'.$row->label5.'-/-'.$row->label6, 'value'=>$row->label1);
								else $response[]=array('id' => $row->id, 'label'=> $row->label1.'-/-'.$row->label2.'-/-'.$row->label3.'-/-'.$row->label4.'-/-'.$row->label5, 'value'=>$row->label1);
				    		else $response[]=array('id' => $row->id, 'label'=> $row->label1.'-/-'.$row->label2.'-/-'.$row->label3.'-/-'.$row->label4, 'value'=>$row->label1);
				    	else $response[]=array('id' => $row->id, 'label'=> $row->label1.'-/-'.$row->label2.'-/-'.$row->label3, 'value'=>$row->label1);
				    else $response[]=array('id' => $row->id, 'label'=> $row->label1.'-/-'.$row->label2, 'value'=>$row->label1);
				else  $response[]=array('id' => $row->id, 'label'=> $row->label1, 'value'=>$row->label1);
			}
			return json_encode($response);
			
		}
		catch (PDOException $e) {
			return 'Ошибка базы данных: '.$e->getMessage();
		}		
	}
	
	function getSelectUnits() {//Получаем список чего либо
	
		try {
			$select = JRequest::getVar('select');//Получаем тип списков
			$name = JRequest::getVar('name');//Получаем тип списков				
			$db = JFactory::getDBO();// Подключаемся к базе.
			$query = "SELECT  #__{$select}.id, #__{$select}.{$name} AS value FROM #__{$select}  ORDER BY #__{$select}.{$name}";
	
			$db->setQuery($query);//Выполняем запрос
	
			if ($rList = $db->loadObjectList()) {
				return $rList;
			}
			else {
				return $db->stderr();
			}
		}
		catch (PDOException $e) {
			return 'Ошибка базы данных: '.$e->getMessage();
		}
	}
	
	function  getOCSAutoComplete() {//Получаем список для автозаполнения
	
		try {
	
			$s_query = JRequest::getVar('term');//Первые буквы поля
			$table = JRequest::getVar('table');//Выбираем из таблицы
			$field= JRequest::getVar('field');//Выбираем поля
			$field2= JRequest::getVar('field2');//Выбираем поля
	
			$db = new mysqli("10.71.6.5", "user", "Qwer1234"); //Подключаемся к базе
			$db->select_db("ocsweb");
			
			$select = "{$table}.{$field} AS label1";
			$where = "{$table}.{$field} LIKE '%{$s_query}%'";
			if ($field2){
				$select .= ", {$table}.{$field2} AS label2";
				$where .= "OR {$table}.{$field2} LIKE '%{$s_query}%'";
			}
			$query = "SELECT  {$select} FROM {$table} WHERE  {$where} ORDER BY {$field} LIMIT 15";
			$rList = $db->query($query);//Выполняем запрос
				
			while ($row = $rList->fetch_assoc()){
				if ($row['label1'] == $row['label2'] || $row['label2'] == '') $response[]=array('label'=> $row['label1'], 'value'=>$row['label1']);
				else $response[]=array('label'=> $row['label1'].' '.$row['label2'], 'value'=>$row['label1']);
			}
			return json_encode($response);
		}
		catch (PDOException $e) {
			return 'Ошибка базы данных: '.$e->getMessage();
		}
	}
	
	function  getPRNAutoComplete() {//Получаем список для автозаполнения
	
	try {
			$region = JRequest::getVar('region');//Пределяем опорный регион
	
			$db = JFactory::getDBO();// Подключаемся к базе.
			$query = "SELECT prn, prn_bd AS bd ,login_prn AS login, password_prn AS password FROM #__neq_region WHERE id = {$region}";//Определяем запрос
			$db->setQuery($query);//Выполняем запрос					
			$rList = $db->loadObjectList();
			
			if ($rList = $db->loadObjectList()) {
				if ($rList[0]->prn == '') return '';
				$db = new mysqli($rList[0]->prn, $rList[0]->login, $rList[0]->password, $rList[0]->bd); //Подключаемся к базе
				$db->select_db();
				$db->set_charset("utf8");
			}
			else {
				return false;
			}
	
			$s_query = JRequest::getVar('term');//Первые буквы поля
			$table = JRequest::getVar('table');//Выбираем из таблицы
			$field= JRequest::getVar('field');//Выбираем поля
			$field2= JRequest::getVar('field2');//Выбираем поля
			
			$select = "{$table}.{$field} AS label1";
			$where = "{$table}.{$field} LIKE '%{$s_query}%'";
			if ($field2){
				$select .= ", {$table}.{$field2} AS label2";
				$where .= "OR {$table}.{$field2} LIKE '%{$s_query}%'";
			}
			$query = "SELECT  {$select} FROM {$table} WHERE  {$where} GROUP BY {$field} ORDER BY {$field} LIMIT 15";
			$rList = $db->query($query);//Выполняем запрос
	
			while ($row = $rList->fetch_assoc()){
				if ($row['label1'] == $row['label2'] || $row['label2'] == '') $response[]=array('label'=> $row['label1'], 'value'=>$row['label1']);
				else $response[]=array('label'=> $row['label1'].' '.$row['label2'], 'value'=>$row['label1']);
			}
			return json_encode($response);
		}
		catch (PDOException $e) {
			return 'Ошибка базы данных: '.$e->getMessage();
		}
	}
	
	function getPrnHistory() {//История печати на принтсервере
			
		$rList = $this->getAccessUser();//Получаем права пользователей
		
		if ($rList[0]->admin){
			try {		
				$region = JRequest::getVar('region');//Пределяем опорный регион
				
				$db = JFactory::getDBO();// Подключаемся к базе.
				$query = "SELECT prn, prn_bd AS bd ,login_prn AS login, password_prn AS password FROM #__neq_region WHERE id = {$region}";//Определяем запрос
				$db->setQuery($query);//Выполняем запрос
				$rList = $db->loadObjectList();
					
				if ($rList = $db->loadObjectList()) {
					if ($rList[0]->prn == '') return '';
					$db = new mysqli($rList[0]->prn, $rList[0]->login, $rList[0]->password, $rList[0]->bd); //Подключаемся к базе
					$db->select_db();
					$db->set_charset("utf8");
				}
				else {
					return false;
				}
					
				$curPage = JRequest::getVar('page');
				$rowsPerPage = JRequest::getVar('rows');
				$sortingField = JRequest::getVar('sidx');
				$sortingOrder =JRequest::getVar('sord');
				$search = JRequest::getVar('_search');
				$stor = JRequest::getVar('stor');
				$prn = JRequest::getVar('printer_name');
	
				$where = "WHERE win_log.printer_name = '{$prn}'";
				if ($search == 'true'){
					if (JRequest::getVar('doc')) $where .= " AND win_log.doc LIKE '".JRequest::getVar('doc')."%' ";
				}
	
				$query = "SELECT COUNT(win_log.id) AS count FROM win_log {$where}";
				$rList = $db->query($query);//Выполняем запрос
				while ($row = $rList->fetch_assoc()){
					$rows = $row['count'];
				}
	

				$totalRows = $rows;
				$firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
	
				$query = "SELECT win_log.* FROM win_log {$where} ORDER BY {$sortingField} {$sortingOrder} LIMIT {$firstRowIndex}, {$rowsPerPage}";
	
				$rList = $db->query($query);
				$response->page = $curPage;
				$response->total = ceil($totalRows / $rowsPerPage);
				$response->records = $totalRows;
	
				$i=0;
				while ($row = $rList->fetch_assoc()){
					$response->rows[$i]['id']=$id;
					$response->rows[$i]['cell']=array($row['printer_name'], $row['time'], $row['doc'], $row['user'], $row['server'], $row['printer_ip'], $row['size'], $row['count']);
					$i++;
				}
				return json_encode($response); 
			}
			catch (PDOException $e) {
				return 'Ошибка базы данных: '.$e->getMessage();
			}
		}
		
	}
	
	function getZabbix() {//Редактирование таблицы zabbix
			
		$rList = $this->getAccessUser();//Получаем права пользователей
        $user = &JFactory::getUser();
	
		try {
			if ($rList[0]->admin){
				
				$oper = JRequest::getVar('oper');//Получаем тип операции				
				$db = JFactory::getDbo();//Подключаемся к базе
				
				switch (JRequest::getVar('oper')){
					case sel:
						//читаем параметры
	
						$curPage = JRequest::getVar('page');
						$rowsPerPage = JRequest::getVar('rows');
						$sortingField = JRequest::getVar('sidx');
						$sortingOrder =JRequest::getVar('sord');
						$search = JRequest::getVar('_search');
						$stor = JRequest::getVar('stor');
                        $myzabbix = JRequest::getVar('myzabbix');
                        $notzabbix = JRequest::getVar('notzabbix');
		
						if ($myzabbix == "true" || $notzabbix == "true"){
                            $search = true;
                        }
                        $where = " #__inv_zabbix.id LIKE '%'";
						if ($search == 'true'){
							if (JRequest::getVar('reg')) $where .= " AND #__neq_region.Name LIKE '".JRequest::getVar('reg')."%' ";
							if (JRequest::getVar('hostid')) $where .= " AND #__inv_zabbix.hostid LIKE '".JRequest::getVar('hostid')."%' ";
							if (JRequest::getVar('host')) $where .= " AND #__inv_zabbix.host LIKE '".JRequest::getVar('host')."%' ";
							if (JRequest::getVar('name')) $where .= " AND #__inv_zabbix.name LIKE '%".JRequest::getVar('name')."%' ";
                            if (JRequest::getVar('username')) $where .= " AND #__users.name LIKE '%".JRequest::getVar('username')."%' ";

                            if ($myzabbix == "true") $where .= " AND #__inv_zabbix.id_user = {$user->id} ";
                            if ($notzabbix == "true") $where .= " AND #__inv_zabbix.hostid = 0 ";
						}
		
						$query = "SELECT COUNT(#__inv_zabbix.id) AS count FROM #__inv_zabbix
									  LEFT OUTER JOIN #__neq_region
									    ON #__inv_zabbix.id_reg = #__neq_region.id
                                      LEFT OUTER JOIN #__users
                                        ON #__inv_zabbix.id_user = #__users.id WHERE  ".$where;
						$db->setQuery($query);
						$rows = $db->loadResult();
		
		
						$totalRows = $rows;
						$firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
		
						$query = "SELECT
									  #__neq_region.Name AS reg,
									  #__inv_zabbix.*,
									  #__users.name AS username
									FROM #__inv_zabbix
									  LEFT OUTER JOIN #__neq_region
									    ON #__inv_zabbix.id_reg = #__neq_region.id
                                      LEFT OUTER JOIN #__users
                                        ON #__inv_zabbix.id_user = #__users.id
									WHERE ".$where." ORDER BY ".$sortingField." ".$sortingOrder." LIMIT ".$firstRowIndex.', '.$rowsPerPage;
		
						$db->setQuery($query);//Выполняем запрос
						$rList = $db->loadObjectList();
						$response->page = $curPage;
						$response->total = ceil($totalRows / $rowsPerPage);
						$response->records = $totalRows;
		
						$i=0;
						foreach($rList as $row){
							$response->rows[$i]['id']=$row->id;
							$response->rows[$i]['cell']=array( 
								$row->reg, $row->hostid, $row->host, $row->name, $row->ip, $row->dns, $row->templateid, $row->description,
                                $row->proxy_hostid, $row->snmp_available, $row->alias, $row->asset_tag, $row->hardware,  $row->hardware_full,
                                $row->host_netmask, $row->host_networks, $row->host_router, $row->hw_arch, $row->installer_name,
                                $row->location, $row->location_lat, $row->location_lon, $row->macaddress_a, $row->macaddress_b, $row->model, $row->inv_name, $row->notes,
                                  $row->oob_ip, $row->oob_netmask, $row->oob_router, $row->os, $row->os_full,
                                  $row->os_short, $row->poc_1_cell, $row->poc_1_email, $row->poc_1_name, $row->poc_1_notes, $row->poc_1_phone_a,
                                  $row->poc_1_phone_b, $row->poc_1_screen, $row->poc_2_cell, $row->poc_2_email, $row->poc_2_name, $row->poc_2_notes,
                                  $row->poc_2_phone_a, $row->poc_2_phone_b, $row->poc_2_screen, $row->serialno_a, $row->serialno_b, $row->site_address_a,
                                  $row->site_address_b, $row->site_address_c, $row->site_city, $row->site_country, $row->site_notes,
                                  $row->site_rack, $row->site_state, $row->site_zip, $row->software, $row->software_app_a, $row->software_app_b,
                                  $row->software_app_c, $row->software_app_d, $row->software_app_e, $row->software_full, $row->tag,
                                  $row->inv_type, $row->type_full, $row->url_a, $row->url_b, $row->url_c, $row->vendor,  $row->username, $row->user_date
							);
							$i++;
						}
						return json_encode($response);
						break;
					case add:
						$id_reg = JRequest::getVar('id_reg');
						$host = JRequest::getVar('host');
						$description = JRequest::getVar('description');
						$name = JRequest::getVar('name');
						$alias = JRequest::getVar('alias');
						$asset_tag = JRequest::getVar('asset_tag');
						$chassis = JRequest::getVar('chassis');
						$contact = JRequest::getVar('contact');
						$contract_number = JRequest::getVar('contract_number');
						$date_hw_decomm = JRequest::getVar('date_hw_decomm');
						$date_hw_expiry = JRequest::getVar('date_hw_expiry');
						$date_hw_install = JRequest::getVar('date_hw_install');
						$date_hw_purchase = JRequest::getVar('date_hw_purchase');
						$deployment_status = JRequest::getVar('deployment_status');
						$hardware = JRequest::getVar('hardware');
						$hardware_full = JRequest::getVar('hardware_full');
						$host_netmask = JRequest::getVar('host_netmask');
						$host_networks = JRequest::getVar('host_networks');
						$host_router = JRequest::getVar('host_router');
						$hw_arch = JRequest::getVar('hw_arch');
						$installer_name = JRequest::getVar('installer_name');
						$inventory_mode = JRequest::getVar('inventory_mode');
						$location = JRequest::getVar('location');
						$location_lat = JRequest::getVar('location_lat');
						$location_lon = JRequest::getVar('location_lon');
						$macaddress_a = JRequest::getVar('macaddress_a');
						$macaddress_b = JRequest::getVar('macaddress_b');
						$model = JRequest::getVar('model');
						$name = JRequest::getVar('name');
						$notes = JRequest::getVar('notes');
						$oob_ip = JRequest::getVar('oob_ip');
						$oob_netmask = JRequest::getVar('oob_netmask');
						$oob_router = JRequest::getVar('oob_router');
						$os = JRequest::getVar('os');
						$os_full = JRequest::getVar('os_full');
						$os_short = JRequest::getVar('os_short');
						$poc_1_cell = JRequest::getVar('poc_1_cell');
						$poc_1_email = JRequest::getVar('poc_1_email');
						$poc_1_name = JRequest::getVar('poc_1_name');
						$poc_1_notes = JRequest::getVar('poc_1_notes');
						$poc_1_phone_a = JRequest::getVar('poc_1_phone_a');
						$poc_1_phone_b = JRequest::getVar('poc_1_phone_b');
						$poc_1_screen = JRequest::getVar('poc_1_screen');
						$poc_2_cell = JRequest::getVar('poc_2_cell');
						$poc_2_email = JRequest::getVar('poc_2_email');
						$poc_2_name = JRequest::getVar('poc_2_name');
						$poc_2_notes = JRequest::getVar('poc_2_notes');
						$poc_2_phone_a = JRequest::getVar('poc_2_phone_a');
						$poc_2_phone_b = JRequest::getVar('poc_2_phone_b');
						$poc_2_screen = JRequest::getVar('poc_2_screen');
						$serialno_a = JRequest::getVar('serialno_a');
						$serialno_b = JRequest::getVar('serialno_b');
						$site_address_a = JRequest::getVar('site_address_a');
						$site_address_b = JRequest::getVar('site_address_b');
						$site_address_c = JRequest::getVar('site_address_c');
						$site_city = JRequest::getVar('site_city');
						$site_country = JRequest::getVar('site_country');
						$site_notes = JRequest::getVar('site_notes');
						$site_rack = JRequest::getVar('site_rack');
						$site_state = JRequest::getVar('site_state');
						$site_zip = JRequest::getVar('site_zip');
						$software = JRequest::getVar('software');
						$software_app_a = JRequest::getVar('software_app_a');
						$software_app_b = JRequest::getVar('software_app_b');
						$software_app_c = JRequest::getVar('software_app_c');
						$software_app_d = JRequest::getVar('software_app_d');
						$software_app_e = JRequest::getVar('software_app_e');
						$software_full = JRequest::getVar('software_full');
						$tag = JRequest::getVar('tag');
						$type = JRequest::getVar('inv_type');
						$type_full = JRequest::getVar('type_full');
						$url_a = JRequest::getVar('url_a');
						$url_b = JRequest::getVar('url_b');
						$url_c = JRequest::getVar('url_c');
						$vendor = JRequest::getVar('vendor');

                        $user_date =date('Y-m-d H:i:s');
						
						$db->setQuery("INSERT INTO #__inv_zabbix
								( id_reg, host,  description, name, alias, asset_tag,
								  chassis, contact,contract_number, date_hw_decomm, date_hw_expiry, date_hw_install, date_hw_purchase,deployment_status,
								  hardware, hardware_full, host_netmask, host_networks, host_router, hw_arch, installer_name, inventory_mode,
								  location, location_lat, location_lon, macaddress_a, macaddress_b, model, inv_name, notes, oob_ip,oob_netmask,
								  oob_router, os, os_full, os_short, poc_1_cell, poc_1_email, poc_1_name, poc_1_notes, poc_1_phone_a,
								  poc_1_phone_b, poc_1_screen, poc_2_cell, poc_2_email, poc_2_name, poc_2_notes, poc_2_phone_a, poc_2_phone_b,
								  poc_2_screen, serialno_a, serialno_b, site_address_a, site_address_b, site_address_c, site_city, site_country,
								  site_notes, site_rack, site_state, site_zip, software, software_app_a, software_app_b, software_app_c,
								  software_app_d, software_app_e, software_full, tag, inv_type, type_full, url_a, url_b, url_c, vendor, id_user, user_date)
							  
							VALUES ({$id_reg},'{$host}','{$description}','{$name}',
								  '{$alias}', '{$asset_tag}',
								  '{$chassis}', '{$contact}', '{$contract_number}', '{$date_hw_decomm}',
								  '{$date_hw_expiry}', '{$date_hw_install}', '{$date_hw_purchase}',
								  '{$deployment_status}', '{$hardware}', '{$hardware_full}',
								  '{$host_netmask}', '{$host_networks}', '{$host_router}',
								  '{$hw_arch}', '{$installer_name}', '{$inventory_mode}',
								  '{$location}', '{$location_lat}', '{$location_lon}',
								  '{$macaddress_a}', '{$macaddress_b}', '{$model}',
								  '{$name}', '{$notes}', '{$oob_ip}',
								  '{$oob_netmask}', '{$oob_router}', '{$os}',
								  '{$os_full}', '{$os_short}', '{$poc_1_cell}',
								  '{$poc_1_email}', '{$poc_1_name}', '{$poc_1_notes}',
								  '{$poc_1_phone_a}', '{$poc_1_phone_b}', '{$poc_1_screen}',
								  '{$poc_2_cell}', '{$poc_2_email}', '{$poc_2_name}',
								  '{$poc_2_notes}', '{$poc_2_phone_a}', '{$poc_2_phone_b}',
								  '{$poc_2_screen}', '{$serialno_a}', '{$serialno_b}',
								  '{$site_address_a}', '{$site_address_b}', '{$site_address_c}',
								  '{$site_city}', '{$site_country}', '{$site_notes}',
								  '{$site_rack}', '{$site_state}', '{$site_zip}',
								  '{$software}', '{$software_app_a}', '{$software_app_b}',
								  '{$software_app_c}', '{$software_app_d}', '{$software_app_e}',
								  '{$software_full}', '{$tag}', '{$type}',
								  '{$type_full}', '{$url_a}', '{$url_b}', '{$url_c}',
								  '{$vendor}', {$user->id}, '{$user_date}')");
						$db->query();
						$this->log("Добавлен объект в zabbix таблицу ".JRequest::getVar('name'));
						break;
					case del:						
						$id = JRequest::getVar('id');
						$db->setQuery("DELETE FROM #__inv_zabbix WHERE #__inv_zabbix.id = ".$id);
						$db->query();
						$this->log("Удалено объект из таблицы zabbix".JRequest::getVar('id'));
						break;
					case edit:
						$this->log("Изменено название помещения ".JRequest::getVar('name'));
						$id = JRequest::getVar('id');
						$db->setQuery("UPDATE #__neq_base
							SET name='".JRequest::getVar('name')."',
								code='".JRequest::getVar('code')."'
							 	WHERE id = ".$id);
						$db->query();
						break;
				}
			}
		}
		catch (PDOException $e) {
			return 'Ошибка базы данных: '.$e->getMessage();
		}
	}
	function getOCS() {//Редактирование таблицы ocs
			
		$rList = $this->getAccessUser();//Получаем права пользователей
	
		try {
			if ($rList[0]->admin){
	
				$oper = JRequest::getVar('oper');//Получаем тип операции
				$db = JFactory::getDbo();//Подключаемся к базе
	
				switch (JRequest::getVar('oper')){
					case sel:
						//читаем параметры	
						$curPage = JRequest::getVar('page');
						$rowsPerPage = JRequest::getVar('rows');
						$sortingField = JRequest::getVar('sidx');
						$sortingOrder =JRequest::getVar('sord');
						$search = JRequest::getVar('_search');
						$stor = JRequest::getVar('stor');
	
						$where = " #__inv_ocs.id LIKE '%'";
						if ($search == 'true'){
							if (JRequest::getVar('region')) $where .= " AND #__neq_region.Name LIKE'".JRequest::getVar('region')."%' ";
							if (JRequest::getVar('NAME')) $where .= " AND #__inv_ocs.NAME LIKE '".JRequest::getVar('NAME')."%' ";
							if (JRequest::getVar('DEVICEID')) $where .= " AND #__inv_ocs.DEVICEID LIKE '".JRequest::getVar('DEVICEID')."%' ";
							if (JRequest::getVar('WORKGROUP')) $where .= " AND #__inv_ocs.WORKGROUP LIKE '".JRequest::getVar('WORKGROUP')."%' ";
							if (JRequest::getVar('USERDOMAIN')) $where .= " AND #__inv_ocs.USERDOMAIN LIKE '".JRequest::getVar('USERDOMAIN')."%' ";
							if (JRequest::getVar('OSNAME')) $where .= " AND #__inv_ocs.OSNAME LIKE '".JRequest::getVar('OSNAME')."%' ";
							if (JRequest::getVar('OSVERSION')) $where .= " AND #__inv_ocs.OSVERSION LIKE '".JRequest::getVar('OSVERSION')."%' ";
							if (JRequest::getVar('OSVERSION')) $where .= " AND #__inv_ocs.NAME LIKE '".JRequest::getVar('OSVERSION')."%' ";
							if (JRequest::getVar('OSCOMMENTS')) $where .= " AND #__inv_ocs.OSCOMMENTS LIKE '".JRequest::getVar('OSCOMMENTS')."%' ";
							if (JRequest::getVar('PROCESSORT')) $where .= " AND #__inv_ocs.PROCESSORT LIKE '".JRequest::getVar('PROCESSORT')."%' ";
							if (JRequest::getVar('PROCESSORS')) $where .= " AND #__inv_ocs.PROCESSORS LIKE '".JRequest::getVar('PROCESSORS')."%' ";
							if (JRequest::getVar('PROCESSORN')) $where .= " AND #__inv_ocs.PROCESSORN LIKE '".JRequest::getVar('PROCESSORN')."%' ";
							if (JRequest::getVar('MEMORY')) $where .= " AND #__inv_ocs.MEMORY LIKE '".JRequest::getVar('MEMORY')."%' ";
							if (JRequest::getVar('IPADDR')) $where .= " AND #__inv_ocs.IPADDR LIKE '".JRequest::getVar('IPADDR')."%' ";
							if (JRequest::getVar('DNS')) $where .= " AND #__inv_ocs.DNS LIKE '".JRequest::getVar('DNS')."%' ";
							if (JRequest::getVar('DEFAULTGATEWAY')) $where .= " AND #__inv_ocs.DEFAULTGATEWAY LIKE '".JRequest::getVar('DEFAULTGATEWAY')."%' ";
							if (JRequest::getVar('ETIME')) $where .= " AND #__inv_ocs.ETIME LIKE '".JRequest::getVar('ETIME')."%' ";
							if (JRequest::getVar('LASTDATE')) $where .= " AND #__inv_ocs.LASTDATE LIKE '".JRequest::getVar('LASTDATE')."%' ";
							if (JRequest::getVar('LASTCOME')) $where .= " AND #__inv_ocs.LASTCOME LIKE '".JRequest::getVar('LASTCOME')."%' ";
							if (JRequest::getVar('QUALITY')) $where .= " AND #__inv_ocs.QUALITY LIKE '".JRequest::getVar('QUALITY')."%' ";
							if (JRequest::getVar('FIDELITY')) $where .= " AND #__inv_ocs.FIDELITY LIKE '".JRequest::getVar('FIDELITY')."%' ";
							if (JRequest::getVar('USERID')) $where .= " AND #__inv_ocs.USERID LIKE '".JRequest::getVar('USERID')."%' ";
							if (JRequest::getVar('TYPE')) $where .= " AND #__inv_ocs.TYPE LIKE '".JRequest::getVar('TYPE')."%' ";
							if (JRequest::getVar('DESCRIPTION')) $where .= " AND #__inv_ocs.DESCRIPTION LIKE '".JRequest::getVar('DESCRIPTION')."%' ";
							if (JRequest::getVar('WINCOMPANY')) $where .= " AND #__inv_ocs.WINCOMPANY LIKE '".JRequest::getVar('WINCOMPANY')."%' ";
							if (JRequest::getVar('WINOWNER')) $where .= " AND #__inv_ocs.WINOWNER LIKE '".JRequest::getVar('WINOWNER')."%' ";
							if (JRequest::getVar('WINPRODID')) $where .= " AND #__inv_ocs.WINPRODID LIKE '".JRequest::getVar('WINPRODID')."%' ";
							if (JRequest::getVar('WINPRODKEY')) $where .= " AND #__inv_ocs.WINPRODKEY LIKE '".JRequest::getVar('WINPRODKEY')."%' ";
							if (JRequest::getVar('USERAGENT')) $where .= " AND #__inv_ocs.USERAGENT LIKE '".JRequest::getVar('USERAGENT')."%' ";
							if (JRequest::getVar('CHECKSUM')) $where .= " AND #__inv_ocs.CHECKSUM LIKE '".JRequest::getVar('CHECKSUM')."%' ";
							if (JRequest::getVar('SSTATE')) $where .= " AND #__inv_ocs.LASTDATE LIKE '".JRequest::getVar('SSTATE')."%' ";
							if (JRequest::getVar('IPSRC')) $where .= " AND #__inv_ocs.IPSRC LIKE '".JRequest::getVar('IPSRC')."%' ";
							if (JRequest::getVar('UUID')) $where .= " AND #__inv_ocs.UUID LIKE '".JRequest::getVar('UUID')."%' ";
							if (JRequest::getVar('ARCH')) $where .= " AND #__inv_ocs.LASTDATE LIKE '".JRequest::getVar('ARCH')."%' ";
							
						}
	
						$query = "SELECT COUNT(#__inv_ocs.id) AS count FROM #__inv_ocs INNER JOIN #__neq_region
								    ON #__inv_ocs.id_reg = #__neq_region.id WHERE  ".$where;
						$db->setQuery($query);
						$rows = $db->loadResult();
	
	
						$totalRows = $rows;
						$firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
	
						$query = "SELECT
								  #__neq_region.Name AS region,
								  #__inv_ocs.*
								FROM #__inv_ocs
								  INNER JOIN #__neq_region
								    ON #__inv_ocs.id_reg = #__neq_region.id
									WHERE ".$where." ORDER BY ".$sortingField." ".$sortingOrder." LIMIT ".$firstRowIndex.', '.$rowsPerPage;
	
						$db->setQuery($query);//Выполняем запрос
						$rList = $db->loadObjectList();
						$response->page = $curPage;
						$response->total = ceil($totalRows / $rowsPerPage);
						$response->records = $totalRows;
	
						$i=0;
						foreach($rList as $row){
							$response->rows[$i]['id']=$row->ID;
							$response->rows[$i]['cell']=array(
									$row->region, $row->DEVICEID, $row->NAME, $row->WORKGROUP, $row->USERDOMAIN, $row->OSNAME, $row->OSVERSION,
									$row->OSCOMMENTS, $row->PROCESSORT, $row->PROCESSORS, $row->PROCESSORN, $row->MEMORY, $row->SWAP,
									$row->IPADDR, $row->DNS, $row->DEFAULTGATEWAY, $row->ETIME, $row->LASTDATE, $row->LASTCOME, $row->QUALITY,
									$row->FIDELITY, $row->USERID, $row->TYPE, $row->DESCRIPTION, $row->WINCOMPANY, $row->WINOWNER,
									$row->WINPRODID, $row->WINPRODKEY, $row->USERAGENT, $row->CHECKSUM, $row->SSTATE, $row->IPSRC, $row->UUID, $row->ARCH
							);
							$i++;
						}
						return json_encode($response);
						break;
					case add:
						$this->log("Добавлен тип оборудования ".JRequest::getVar('name'));
						$db->setQuery("INSERT INTO #__neq_base
								(name, code)
								VALUES ('".JRequest::getVar('name')."','".JRequest::getVar('code')."')");
						$db->query();
						break;
					case del:
						$this->log("Удалено тип оборудования".JRequest::getVar('id'));
						$id = JRequest::getVar('id');
						$db->setQuery("DELETE FROM #__neq_base WHERE #__neq_base.id = ".$id);
						$db->query();
						break;
					case edit:
						$this->log("Изменено название помещения ".JRequest::getVar('name'));
						$id = JRequest::getVar('id');
						$db->setQuery("UPDATE #__neq_base
							SET name='".JRequest::getVar('name')."',
								code='".JRequest::getVar('code')."'
							 	WHERE id = ".$id);
						$db->query();
						break;
				}
			}
		}
		catch (PDOException $e) {
			return 'Ошибка базы данных: '.$e->getMessage();
		}
	}
	function getOCSImport() //...................................... Функиция импорта оборудования из Zabbix...................................
	{
	
		try {
			$region = JRequest::getVar('region');//Пределяем опорный регион
			$id_ocs = JRequest::getVar('id_ocs');//Пределяем опорный регион
	
			$db = JFactory::getDBO();// Подключаемся к базе.
			$query = "SELECT ocs AS ocs, ocs_bd AS bd ,login_ocs AS login, password_ocs AS password FROM #__neq_region WHERE id = ".$region;//Определяем запрос
			$db->setQuery($query);//Выполняем запрос
			
			$rList = $db->loadObjectList();
			if ($rList = $db->loadObjectList()) {
				if ($rList[0]->ocs == '') return '';
				$dbocs = new mysqli($rList[0]->ocs, $rList[0]->login, $rList[0]->password, $rList[0]->bd); //Подключаемся к базе
				$dbocs->select_db();
				$dbocs->set_charset("utf8");
			}
			else {
				return false;
			}
			if (JRequest::getVar('oper') != 'import'){
				$query = "SELECT  #__inv_ocs.id_device FROM #__inv_ocs WHERE #__inv_ocs.ID = {$id_ocs}";
				$db->setQuery($query);
				$id = $db->loadResult();
			}
			
			switch(JRequest::getVar('oper')){
				case import:
					$rList = $dbocs->query("SELECT  hardware.*
									FROM hardware JOIN
  									(SELECT hardware.NAME, MAX(hardware.LASTDATE) AS mtime FROM hardware GROUP BY hardware.NAME)
  									AS tl ON hardware.NAME = tl.NAME AND hardware.LASTDATE = tl.mtime");//Выполняем запрос к OCS
					
					while ($row = $rList->fetch_assoc()){
					
						$query = "SELECT COUNT(id) AS count FROM #__inv_ocs WHERE #__inv_ocs.NAME='{$row['NAME']}' AND  #__inv_ocs.id_reg = {$region}";
						$db->setQuery($query);
						$rows = $db->loadResult();
					
						if ($rows > 0) {
								$db->setQuery("UPDATE #__inv_ocs SET
														id_device ='{$row['ID']}', DEVICEID ='{$row['DEVICEID']}', NAME ='{$row['NAME']}',
														WORKGROUP ='{$row['WORKGROUP']}', USERDOMAIN ='{$row['USERDOMAIN']}', OSNAME ='{$row['OSNAME']}',
														OSVERSION ='{$row['OSVERSION']}', OSCOMMENTS ='{$row['OSCOMMENTS']}', PROCESSORT ='{$row['PROCESSORT']}',
														PROCESSORS ='{$row['PROCESSORS']}', PROCESSORN ='{$row['PROCESSORN']}', MEMORY ='{$row['MEMORY']}',
														SWAP ='{$row['SWAP']}', IPADDR ='{$row['IPADDR']}', DNS ='{$row['DNS']}',
														DEFAULTGATEWAY ='{$row['DEFAULTGATEWAY']}', ETIME ='{$row['ETIME']}', LASTDATE ='{$row['LASTDATE']}',
														LASTCOME ='{$row['LASTCOME']}', QUALITY ='{$row['QUALITY']}', FIDELITY ='{$row['FIDELITY']}',
														USERID ='{$row['USERID']}', TYPE ='{$row['TYPE']}', DESCRIPTION ='{$row['DESCRIPTION']}',
														WINCOMPANY ='{$row['WINCOMPANY']}', WINOWNER ='{$row['WINOWNER']}', WINPRODID ='{$row['WINPRODID']}',
														WINPRODKEY ='{$row['WINPRODKEY']}', USERAGENT ='{$row['USERAGENT']}', CHECKSUM ='{$row['CHECKSUM']}',
														SSTATE ='{$row['SSTATE']}', IPSRC ='{$row['IPSRC']}', UUID ='{$row['UUID']}', ARCH = '{$row['ARCH']}'
												WHERE  #__inv_ocs.NAME='{$row['NAME']}' AND  #__inv_ocs.id_reg = {$region}");
								$db->query();					
							}else {
								$db->setQuery("INSERT INTO #__inv_ocs
														( id_reg, id_device, DEVICEID, NAME, WORKGROUP, USERDOMAIN, OSNAME, OSVERSION, OSCOMMENTS, PROCESSORT, PROCESSORS,
														PROCESSORN, MEMORY, SWAP, IPADDR, DNS, DEFAULTGATEWAY, ETIME, LASTDATE, LASTCOME, QUALITY, FIDELITY, USERID,
														TYPE, DESCRIPTION, WINCOMPANY, WINOWNER, WINPRODID, WINPRODKEY, USERAGENT, CHECKSUM, SSTATE, IPSRC, UUID, ARCH)
														VALUES ({$region}, '{$row['ID']}', '{$row['DEVICEID']}', '{$row['NAME']}', '{$row['WORKGROUP']}', '{$row['USERDOMAIN']}', '{$row['OSNAME']}',
														'{$row['OSVERSION']}', '{$row['OSCOMMENTS']}', '{$row['PROCESSORT']}', '{$row['PROCESSORS']}', '{$row['PROCESSORN']}',
														'{$row['MEMORY']}', '{$row['SWAP']}', '{$row['IPADDR']}', '{$row['DNS']}', '{$row['DEFAULTGATEWAY']}', '{$row['ETIME']}',
														'{$row['LASTDATE']}', '{$row['LASTCOME']}', '{$row['QUALITY']}', '{$row['FIDELITY']}', '{$row['USERID']}', '{$row['TYPE']}',
														'{$row['DESCRIPTION']}', '{$row['WINCOMPANY']}', '{$row['WINOWNER']}', '{$row['WINPRODID']}', '{$row['WINPRODKEY']}',
														'{$row['USERAGENT']}', '{$row['CHECKSUM']}', '{$row['SSTATE']}', '{$row['IPSRC']}', '{$row['UUID']}', '{$row['ARCH']}')");
								$db->query();
							}
						}
					break;
				case bios:
					$rList = $dbocs->query("SELECT bios.*	FROM bios	WHERE bios.HARDWARE_ID = {$id}");//Выполняем запрос к OCS

					while ($row = $rList->fetch_assoc()){
						$response['SMANUFACTURER'] = $row['SMANUFACTURER'];
						$response['SMODEL'] = $row['SMODEL'];
						$response['SSN'] = $row['SSN'];
						$response['TYPE'] = $row['TYPE'];
						$response['BMANUFACTURER'] = $row['BMANUFACTURER'];
						$response['BVERSION'] = $row['BVERSION'];
						$response['BDATE'] = $row['BDATE'];
						$response['ASSETTAG'] = $row['ASSETTAG'];
					}
					return json_encode($response);
					break;
				case soft:
					
					$curPage = JRequest::getVar('page');
					$rowsPerPage = JRequest::getVar('rows');
					$sortingField = JRequest::getVar('sidx');
					$sortingOrder =JRequest::getVar('sord');
					$search = JRequest::getVar('_search');
					$stor = JRequest::getVar('stor');
		
					$where = "WHERE softwares.HARDWARE_ID = '{$id}'";
					if ($search == 'true'){
						if (JRequest::getVar('doc')) $where .= " AND win_log.doc LIKE '".JRequest::getVar('doc')."%' ";
					}
		
					$query = "SELECT COUNT(softwares.HARDWARE_ID) AS count FROM softwares {$where}";
					$rList = $dbocs->query($query);//Выполняем запрос
					while ($row = $rList->fetch_assoc()){
						$rows = $row['count'];
					}
	
					$totalRows = $rows;
					$firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
		
					$query = "SELECT softwares.* FROM softwares {$where} ORDER BY {$sortingField} {$sortingOrder} LIMIT {$firstRowIndex}, {$rowsPerPage}";
		
					$rList = $dbocs->query($query);
					$response->page = $curPage;
					$response->total = ceil($totalRows / $rowsPerPage);
					$response->records = $totalRows;
		
					$i=0;
					while ($row = $rList->fetch_assoc()){
						$response->rows[$i]['id']=$row['ID'];
						$response->rows[$i]['cell']=array($row['PUBLISHER'], $row['NAME'], $row['VERSION'], $row['FOLDER'], $row['COMMENTS'], $row['GUID'], $row['LANGUAGE'], $row['INSTALLDATE'], $row['BITSWIDTH']);
						$i++;
					}
					return json_encode($response);
											
					break;
			}
				
		   
		}
			catch (PDOException $e) {
			return 'Ошибка базы данных: '.$e->getMessage();
		}
	}
	
	function getCompConfSync(){ //Синхронизация параметров компьютера
		
		$id_adpc= JRequest::getVar('id_adpc');
		$id_zabbix = JRequest::getVar('id_zabbix');
		$id_ocs = JRequest::getVar('id_ocs');
		
		if ($id_ocs != ''){
			try {
				$db = JFactory::getDbo();//Подключаемся к базе
				$query = "SELECT #__inv_ocs.id_reg, #__inv_ocs.id_device, #__inv_ocs.DEVICEID,  #__inv_ocs.PROCESSORT,  #__inv_ocs.MEMORY,  #__inv_ocs.OSNAME
							FROM #__inv_ocs
							WHERE #__inv_ocs.ID = {$id_ocs}";
				$db->setQuery($query);//Выполняем запрос				
				$rList = $db->loadObjectList();
				
				foreach($rList as $row){
					$response =array('deviceid' => $row->DEVICEID, 'processort' => $row->PROCESSORT, 'memory' => $row->MEMORY,'osname' => $row->OSNAME);
					$region = $row->id_reg;
					$id_device = $row->id_device;
				}
				
				$query = "SELECT ocs AS ocs, ocs_bd AS bd ,login_ocs AS login, password_ocs AS password FROM #__neq_region WHERE id = ".$region;//Определяем запрос
				$db->setQuery($query);//Выполняем запрос
					
				$rList = $db->loadObjectList();

				if ($rList = $db->loadObjectList()) {
					if ($rList[0]->ocs == '') return '';
					$dbocs = new mysqli($rList[0]->ocs, $rList[0]->login, $rList[0]->password, $rList[0]->bd); //Подключаемся к базе
					$dbocs->select_db();
					$dbocs->set_charset("utf8");
				}
				else {
					return false;
				}
				//Получаем mac адреса
				$rList = $dbocs->query("SELECT  networks.MACADDR
										FROM ocsweb.networks
										WHERE networks.HARDWARE_ID = {$id_device}");//Выполняем запрос к OCS
				
				while ($row = $rList->fetch_assoc()){				
					$response['mac'] .= $row['MACADDR'].'/';
				}
				//Получаем жесткие диски
				$rList = $dbocs->query("SELECT storages.NAME, storages.DISKSIZE 
										FROM ocsweb.storages
										WHERE storages.HARDWARE_ID = {$id_device}
										AND storages.DISKSIZE > 0");//Выполняем запрос к OCS
				
				while ($row = $rList->fetch_assoc()){				
					$response['hdd'] .= $row['NAME'].'-'.$row['DISKSIZE'].'/';
				}
				
				//Получаем жесткие диски
				$rList = $dbocs->query("SELECT  videos.NAME, videos.MEMORY
										FROM videos
										WHERE videos.MEMORY > 0
										AND videos.HARDWARE_ID = {$id_device}");//Выполняем запрос к OCS
				
						while ($row = $rList->fetch_assoc()){
							$response['graphics'] .= $row['NAME'].'-'.$row['MEMORY'].'/';
						}
				
				return json_encode($response);
				
			}catch (PDOException $e) {
				JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			} 
		}
	}

    function getDashboard() {//Получаем минутные значения
        try {
            $regid = JRequest::getVar('regid');//Получаем тип операции

            $dash = JRequest::getVar('dash');//Получаем тип дашборда

            $db = JFactory::getDbo();//Подключаемся к базе
            switch ($dash){
                case spmin:
                    $query = "SELECT
                              COUNT(#__inv_sp_log.id) AS roundTripTime,
                              DATE_FORMAT(#__inv_sp_log.datatime, '%Y-%m-%d %H:%i') AS creat_time
                            FROM #__inv_sp_log
                            WHERE #__inv_sp_log.datatime >= CURDATE()
                            GROUP BY creat_time";
                    break;
                case vendor:
                    $query = "SELECT
                                  #__inv_vendor.vendor,
                                  COUNT(#__inv_object.id) AS ven_count
                                FROM #__inv_object
                                  INNER JOIN #__inv_vendor
                                    ON #__inv_object.id_vendor = #__inv_vendor.id
                                  INNER JOIN #__inv_object_type
                                    ON #__inv_object.id_obj_type = #__inv_object_type.id
                                WHERE #__inv_object_type.func <> 'sup'
                                GROUP BY #__inv_vendor.vendor
                                ORDER BY ven_count DESC
                                LIMIT 5";
                    break;

            }

            $db->setQuery($query);//Выполняем запрос
            $rList = $db->loadObjectList();

            return json_encode($rList);
        }
        catch (PDOException $e) {
            return 'Ошибка базы данных: '.$e->getMessage();
        }
    }

    function updateLocation($value, $rez_arr){
        try{
            if (in_array((int)$value, $rez_arr)) {//Если такое значение уже есть в переданном массиве родителz
                return 0;
            }

            $db = JFactory::getDbo();//Подключаемся к базе
            $query = "SELECT #__inv_obj_parts.id_obj_part FROM #__inv_obj_parts WHERE #__inv_obj_parts.remove <> 1 AND #__inv_obj_parts.id_obj = {$value}";
            $db->setQuery($query);//Выполняем запрос
            $rList = $db->loadObjectList();

            //return json_encode($rList);
            $rez_arr[] = $value;//Добавляем

            foreach ($rList as $row){//Для каждого найденного потомка
                if (in_array((int)$row->id_obj_part, $old)) {//Если такое значение уже есть в переданном массиве родителz
                    return 0; //Возвращаем ошибку
                }else{
                    //$rez_arr[] = (int) $row->id_obj_part;
                    $int_arr = $this->updateLocation($row->id_obj_part, $rez_arr);
                    if ($int_arr != 0){
                        $rez_arr = array_merge($rez_arr, $int_arr );
                    }
                }
            }

            if (count($rez_arr) > 0){
                array_shift($rez_arr);//Убираем родителя
                $rez_arr = array_unique ($rez_arr );
                $rez_arr = array_values($rez_arr);//Переинициализируем массив
                return $rez_arr;
            }else{
                return 0;
            }
        }catch (Exception $e){
            return 'Выброшено исключение: '. $e->getMessage(). "\n";
        }

    }
	
	function getObjects() {//Редактирование типов объектов
			
		$creator = &JFactory::getUser();//Получаем информацию о текущем пользователе
		$rList = $this->getAccessUser();//Получаем права пользователей	
		$oper = JRequest::getVar('oper');//Получаем тип операции
		$user = &JFactory::getUser();
		$objtype = JRequest::getVar('objtype');
        $edit = json_decode(JRequest::getVar('edit'));//Проверяем, что правил пользователь
        $obj_parts = json_decode(JRequest::getVar('obj_parts'));
		
		$db = JFactory::getDbo();//Подключаемся к базе

		if ($rList[0]->admin || $rList[0]->storekeeper){
			
			$prop = json_decode(JRequest::getVar('prop'));
			
			if ($prop->id_vendor == '') $prop->id_vendor = 'null';
			if ($prop->guaranty == '') $prop->guaranty = 'null';
			if ($prop->id_user == '') $prop->id_user = 'null';
			if ($prop->id_store == '') $prop->id_store = 'null';

			$location = json_decode(JRequest::getVar('location'));
			
			
			switch ($oper){
				case sel:
                    try{
                        //читаем параметры
                        if (JRequest::getVar('single') == '1'){//Если нужно выбрать одну запись
                            $curPage=1;
                            $rowsPerPage = 5;
                            $sortingField=name;
                            $sortingOrder = 'asc';
                            $search = 'true';
                        }else{
                            $curPage = JRequest::getVar('page');
                            $rowsPerPage = JRequest::getVar('rows');
                            $sortingField = JRequest::getVar('sidx');
                            $sortingOrder =JRequest::getVar('sord');
                            $search = JRequest::getVar('_search');
                        }

                        $stor = JRequest::getVar('stor');
                        $function = JRequest::getVar('_function');

                        $table= "#__inv_".$objtype;

                        if ($search == 'true'){
                            $where = " WHERE #__inv_object.id LIKE '%' ";
                            if (JRequest::getVar('id')) $where = " WHERE #__inv_object.id = ".JRequest::getVar('id');
                            if (JRequest::getVar('net_id')) $where .= "#__inv_net.id = ".JRequest::getVar('net_id');
                            if (JRequest::getVar('name')) $where .= " AND #__inv_object.name LIKE '%".JRequest::getVar('name')."%' ";
                            if (JRequest::getVar('purchase_date')) $where .= " AND #__inv_obj_buh.purchase_date LIKE '".JRequest::getVar('purchase_date')."%' ";
                            if (JRequest::getVar('s_number')) $where .= " AND #__inv_object.s_number LIKE '%".JRequest::getVar('s_number')."%' ";
                            if (JRequest::getVar('i_number')) $where .= " AND #__inv_object.i_number LIKE '%".JRequest::getVar('i_number')."%' ";
                            if (JRequest::getVar('i_number_adv')) $where .= " AND #__inv_object.i_number_adv LIKE '%".JRequest::getVar('i_number_adv')."%' ";
                            if (JRequest::getVar('barcode')) $where .= " AND #__inv_object.barcode LIKE '%".JRequest::getVar('barcode')."%' ";
                            if (JRequest::getVar('s_number')) $where .= " AND #__inv_object.s_number LIKE '%".JRequest::getVar('s_number')."%' ";
                            if (JRequest::getVar('chasis')) $where .= " AND chasis LIKE '%".JRequest::getVar('chasis')."%' ";
                            if (JRequest::getVar('model')) $where .= " AND {$table}.model LIKE '%".JRequest::getVar('model')."%' ";
                            if (JRequest::getVar('supplier')) $where .= " AND supplier LIKE '%".JRequest::getVar('supplier')."%' ";
                            if (JRequest::getVar('vendor')) $where .= " AND vendor LIKE '%".JRequest::getVar('vendor')."%' ";

                            //Таблица сетевых устройств
                            if (JRequest::getVar('active_net_type')) $where .= " AND #__inv_net_type.active_net_type LIKE '%".JRequest::getVar('active_net_type')."%' ";
                            if ($function) $where .= " AND #__inv_object_type.func = '{$function}' ";

                        }
                        $query = "SELECT COUNT(#__inv_object.id) AS count
                             FROM #__inv_object
                              INNER JOIN #__inv_object_type
                                ON #__inv_object.id_obj_type = #__inv_object_type.id
                              LEFT OUTER JOIN #__inv_obj_moving
                                ON #__inv_obj_moving.id_obj = #__inv_object.id
                              INNER JOIN #__neq_region
                                ON #__inv_obj_moving.id_region = #__neq_region.id
                              INNER JOIN #__neq_pes
                                ON #__inv_obj_moving.id_pes = #__neq_pes.id
                              LEFT OUTER JOIN #__neq_po
                                ON #__inv_obj_moving.id_po = #__neq_po.id
                              LEFT OUTER JOIN #__neq_a
                                ON #__inv_obj_moving.id_a = #__neq_a.id
                              LEFT OUTER JOIN #__inv_vendor
                                ON #__inv_object.id_vendor = #__inv_vendor.id
                              LEFT OUTER JOIN #__inv_countries
                                ON #__inv_vendor.country = #__inv_countries.id
                              INNER JOIN (SELECT
                                  #__inv_obj_moving.id_obj,
                                  MAX(#__inv_obj_moving.date) AS mdate
                                FROM #__inv_obj_moving
                                WHERE #__inv_obj_moving.id_region IN (SELECT
                                    #__inv_access_region.id_access
                                  FROM #__inv_access_region
                                  WHERE #__inv_access_region.id_user = {$user->id}
                                  AND (#__inv_access_region.reg_read = 1
                                  OR #__inv_access_region.reg_write = 1))
                                OR #__inv_obj_moving.id_pes IN (SELECT
                                    #__inv_access_pes.id_access
                                  FROM #__inv_access_pes
                                  WHERE #__inv_access_pes.id_user = {$user->id}
                                  AND (#__inv_access_pes.reg_read = 1
                                  OR #__inv_access_pes.reg_write = 1))
                                GROUP BY #__inv_obj_moving.id_obj) SubQuery
                                ON #__inv_obj_moving.id_obj = SubQuery.id_obj
                                AND #__inv_obj_moving.date = SubQuery.mdate
                              LEFT OUTER JOIN #__inv_users
                                ON #__inv_object.id_user = #__inv_users.id
                              LEFT OUTER JOIN #__inv_obj_buh
                                ON #__inv_obj_buh.id_obj = #__inv_object.id
                              LEFT OUTER JOIN #__inv_type_accounting
                                ON #__inv_obj_buh.id_type_accounting = #__inv_type_accounting.id
                              LEFT OUTER JOIN #__inv_responsible_user
                                ON #__inv_obj_buh.id_resp_user = #__inv_responsible_user.id_user
                              LEFT OUTER JOIN #__inv_users #__inv_users_1
                                ON #__inv_responsible_user.id_user = #__inv_users_1.id
                              LEFT OUTER JOIN #__inv_supplier
                                ON #__inv_obj_buh.id_supplier = #__inv_supplier.id ";

                        switch ($objtype){
                            case "group":
                                $query .= " INNER JOIN #__inv_group
                                    ON #__inv_group.id_obj = #__inv_object.id ";
                                break;
                            case "computer":
                                $query .= " INNER JOIN #__inv_computer
                                    ON #__inv_computer.id_obj = #__inv_object.id
                                  INNER JOIN #__inv_comp_type
                                    ON #__inv_computer.id_type = #__inv_comp_type.id
                                  LEFT OUTER JOIN #__inv_zabbix
                                    ON #__inv_computer.id_zabbix = #__inv_zabbix.id
                                  LEFT OUTER JOIN #__inv_ocs
                                    ON #__inv_computer.id_ocs = #__inv_ocs.ID
                                  LEFT OUTER JOIN #__inv_adpc
                                    ON #__inv_computer.id_adpc = #__inv_adpc.id ";
                                break;
                            case "net":
                                $query .= " INNER JOIN #__inv_net
                                            ON #__inv_net.id_obj = #__inv_object.id
                                          INNER JOIN #__inv_net_type
                                            ON #__inv_net.id_active_type = #__inv_net_type.id
                                          INNER JOIN #__inv_net_speed
                                            ON #__inv_net.id_base_speed = #__inv_net_speed.id
                                          INNER JOIN #__inv_net_speed #__inv_net_speed_1
                                            ON #__inv_net.id_max_uplink = #__inv_net_speed_1.id
                                          LEFT OUTER JOIN #__inv_zabbix
                                            ON #__inv_net.id_zabbix = #__inv_zabbix.id ";
                                break;
                            case "prn":
                                $query .= " INNER JOIN #__inv_prn
									    ON #__inv_prn.id_obj = #__inv_object.id
									  INNER JOIN #__inv_prn_format
									    ON #__inv_prn.id_format = #__inv_prn_format.id
									  INNER JOIN #__inv_prn_type
									    ON #__inv_prn.id_type = #__inv_prn_type.id
									  INNER JOIN #__inv_prn_type_print
									    ON #__inv_prn.id_type_print = #__inv_prn_type_print.id
									  LEFT OUTER JOIN #__inv_zabbix
    									ON #__inv_prn.id_zabbix = #__inv_zabbix.id ";
                                break;
                            case "monitor":
                                $query .= " INNER JOIN #__inv_monitor
                                            ON #__inv_monitor.id_obj = #__inv_object.id
                                          LEFT OUTER JOIN #__inv_mon_matrix_type
                                             ON #__inv_monitor.id_matrix_type = #__inv_mon_matrix_type.id ";
                                break;
                            case "sup":
                                $query .= "  INNER JOIN #__inv_supplies
                                    ON #__inv_supplies.id_obj = #__inv_object.id
                                  INNER JOIN #__inv_sup_type
                                    ON #__inv_supplies.id_supplies_type = #__inv_sup_type.id ";
                                break;
                        }

                        $query .= $where;
                        $db->setQuery($query);
                        $rows = $db->loadResult();

                        $totalRows = $rows;
                        $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;

                        $query = "SELECT  #__inv_object.*, #__inv_object_type.type, #__inv_object_type.func, #__neq_region.Name AS reg, #__neq_pes.name AS pes,
                                          #__neq_po.name AS po, #__neq_a.name AS a, #__inv_obj_moving.date, #__inv_obj_moving.code, #__inv_obj_moving.id_region,
                                          #__inv_obj_moving.id_pes, #__inv_obj_moving.id_a, #__inv_obj_moving.id_po, #__inv_vendor.vendor, #__inv_countries.name AS country,
                                          #__inv_users.cn AS user, #__inv_users.department AS user_dep, #__inv_obj_moving.latitude, #__inv_obj_moving.longitude,
                                          #__inv_obj_moving.altitude, #__inv_obj_buh.buh_name, #__inv_obj_buh.id_type_accounting, #__inv_obj_buh.i_number, #__inv_obj_buh.i_number_adv,
                                          #__inv_obj_buh.id_supplier, #__inv_obj_buh.id_resp_user, #__inv_obj_buh.buh_cost, #__inv_obj_buh.purchase_date,
                                          #__inv_obj_buh.buh_date, #__inv_obj_buh.amortization, #__inv_type_accounting.accounting AS type_accounting, #__inv_users_1.cn AS resp_user,
                                          #__inv_supplier.supplier ";
                        switch ($objtype){
                            case "group":
                                $query .= ",  #__inv_group.id AS id_group, #__inv_group.gr_name, #__inv_group.id_gr_user, COUNT(#__inv_obj_parts.id_obj) AS count_ch ";
                                break;
                            case "computer":
                                $query .= ",  #__inv_computer.id AS id_comp, #__inv_computer.id_type AS id_comp_type, #__inv_computer.id_adpc, #__inv_computer.id_zabbix,
                                      #__inv_computer.id_ocs, #__inv_computer.chasis, #__inv_computer.model, #__inv_computer.osname, #__inv_computer.config,
                                      #__inv_computer.id_template, #__inv_computer.processor, #__inv_computer.mem, #__inv_computer.motherboard, #__inv_computer.hdd,
                                      #__inv_computer.psu, #__inv_computer.graphics, #__inv_computer.network, #__inv_computer.mac, #__inv_computer.description,
                                      #__inv_comp_type.type AS comp_type, #__inv_zabbix.host AS zabbix_name, #__inv_ocs.NAME AS ocs_name, #__inv_adpc.cn AS ad_name ";
                                break;
                            case "net":
                                $query .= ", #__inv_net.id AS id_net, #__inv_net.id_active_type, #__inv_net.chasis, #__inv_net.model, #__inv_net.num_ethernet,
                                          #__inv_net.num_uplink, #__inv_net.num_sfp, #__inv_net.num_poe, #__inv_net.num_fxs, #__inv_net.num_usb,
                                          #__inv_net.id_base_speed, #__inv_net.id_max_uplink, #__inv_net.slots, #__inv_net.rack, #__inv_net.num_unit,
                                          #__inv_net.forwarding_rate, #__inv_net.mac_table, #__inv_net.mem, #__inv_net.flash, #__inv_net.stack,
                                          #__inv_net.console, #__inv_net.web, #__inv_net.telnet_ssh, #__inv_net.snmp, #__inv_net.management,
                                          #__inv_net.outdoors, #__inv_net.poe, #__inv_net.poe_standart, #__inv_net.voice, #__inv_net.voice_standart,
                                          #__inv_net.wifi, #__inv_net.wifi_standart, #__inv_net.power_standart, #__inv_net.backup_power, #__inv_net.os,
                                          #__inv_net.act_description, #__inv_net.id_template, #__inv_net.id_zabbix, #__inv_net_type.active_net_type,
                                          #__inv_net_speed.base_speed, #__inv_net_speed_1.base_speed AS max_uplink, #__inv_zabbix.host AS zabbix_name";
                                break;
                            case "prn":
                                $query .= ", #__inv_prn.id AS id_prn, #__inv_prn.chasis, #__inv_prn.model, #__inv_prn.print_srv_name, #__inv_prn.id_zabbix,
                                      #__inv_prn.id_type, #__inv_prn.id_type_print, #__inv_prn.id_type_scan, #__inv_prn.color, #__inv_prn.id_format,
                                      #__inv_prn.photo, #__inv_prn.duplex_printing, #__inv_prn.ethernet, #__inv_prn.network, #__inv_prn.prn_mac,
                                      #__inv_prn.fax, #__inv_prn.wifi, #__inv_prn.speed_print, #__inv_prn.speed_scan, #__inv_prn.mem, #__inv_prn.sheets,
                                      #__inv_prn.id_template, #__inv_prn.date_sheets, #__inv_prn.m_resource, #__inv_prn.prn_os, #__inv_prn.prn_description,
                                      #__inv_prn_format.format_print, #__inv_zabbix.host AS zabbix_name, #__inv_prn_type.print_type, #__inv_prn_type_print.print_type_print ";
                                break;
                            case "monitor":
                                $query .= ", #__inv_monitor.id AS id_mon, #__inv_monitor.chasis, #__inv_monitor.model, #__inv_monitor.size, #__inv_monitor.format,
                                                #__inv_monitor.resolution, #__inv_monitor.led, #__inv_monitor.dynamics, #__inv_monitor.id_matrix_type,
                                                #__inv_monitor.video_inputs, #__inv_monitor.power, #__inv_monitor.description, #__inv_mon_matrix_type.type AS matrix_type ";
                                break;
                            case "sup":
                                $query .= ", #__inv_supplies.id_supplies_type, #__inv_supplies.model, #__inv_supplies.sup_count, #__inv_supplies.sup_field1,
                                    #__inv_supplies.sup_field2, #__inv_supplies.sup_field3, #__inv_supplies.sup_field4, #__inv_supplies.sup_field5, #__inv_supplies.sup_description,
                                    #__inv_sup_type.sup_type_name, #__inv_sup_type.func AS sup_func, #__inv_sup_type.field1_name, #__inv_sup_type.field2_name,
                                    #__inv_sup_type.field3_name, #__inv_sup_type.field4_name, #__inv_sup_type.field5_name ";
                                break;
                        }

                        $query .= " FROM #__inv_object
                              INNER JOIN #__inv_object_type
                                ON #__inv_object.id_obj_type = #__inv_object_type.id
                              LEFT OUTER JOIN #__inv_obj_moving
                                ON #__inv_obj_moving.id_obj = #__inv_object.id
                              INNER JOIN #__neq_region
                                ON #__inv_obj_moving.id_region = #__neq_region.id
                              INNER JOIN #__neq_pes
                                ON #__inv_obj_moving.id_pes = #__neq_pes.id
                              LEFT OUTER JOIN #__neq_po
                                ON #__inv_obj_moving.id_po = #__neq_po.id
                              LEFT OUTER JOIN #__neq_a
                                ON #__inv_obj_moving.id_a = #__neq_a.id
                              LEFT OUTER JOIN #__inv_vendor
                                ON #__inv_object.id_vendor = #__inv_vendor.id
                              LEFT OUTER JOIN #__inv_countries
                                ON #__inv_vendor.country = #__inv_countries.id
                              INNER JOIN (SELECT
                                  #__inv_obj_moving.id_obj,
                                  MAX(#__inv_obj_moving.date) AS mdate
                                FROM #__inv_obj_moving
                                WHERE #__inv_obj_moving.id_region IN (SELECT
                                    #__inv_access_region.id_access
                                  FROM #__inv_access_region
                                  WHERE #__inv_access_region.id_user = {$user->id}
                                  AND (#__inv_access_region.reg_read = 1
                                  OR #__inv_access_region.reg_write = 1))
                                OR #__inv_obj_moving.id_pes IN (SELECT
                                    #__inv_access_pes.id_access
                                  FROM #__inv_access_pes
                                  WHERE #__inv_access_pes.id_user = {$user->id}
                                  AND (#__inv_access_pes.reg_read = 1
                                  OR #__inv_access_pes.reg_write = 1))
                                GROUP BY #__inv_obj_moving.id_obj) SubQuery
                                ON #__inv_obj_moving.id_obj = SubQuery.id_obj
                                AND #__inv_obj_moving.date = SubQuery.mdate
                              LEFT OUTER JOIN #__inv_users
                                ON #__inv_object.id_user = #__inv_users.id
                              LEFT OUTER JOIN #__inv_obj_buh
                                ON #__inv_obj_buh.id_obj = #__inv_object.id
                              LEFT OUTER JOIN #__inv_type_accounting
                                ON #__inv_obj_buh.id_type_accounting = #__inv_type_accounting.id
                              LEFT OUTER JOIN #__inv_responsible_user
                                ON #__inv_obj_buh.id_resp_user = #__inv_responsible_user.id_user
                              LEFT OUTER JOIN #__inv_users #__inv_users_1
                                ON #__inv_responsible_user.id_user = #__inv_users_1.id
                              LEFT OUTER JOIN #__inv_supplier
                                ON #__inv_obj_buh.id_supplier = #__inv_supplier.id ";


                        switch ($objtype){
                            case "group":
                                $query .= " INNER JOIN #__inv_group
                                    ON #__inv_group.id_obj = #__inv_object.id 
                                     LEFT OUTER JOIN #__inv_obj_parts
                                    ON #__inv_group.id_obj = #__inv_obj_parts.id_obj ";
                                $group = "GROUP BY #__inv_group.id_obj ";
                                
                                break;
                            case "computer":
                                $query .= " INNER JOIN #__inv_computer
                                    ON #__inv_computer.id_obj = #__inv_object.id
                                  INNER JOIN #__inv_comp_type
                                    ON #__inv_computer.id_type = #__inv_comp_type.id
                                  LEFT OUTER JOIN #__inv_zabbix
                                    ON #__inv_computer.id_zabbix = #__inv_zabbix.id
                                  LEFT OUTER JOIN #__inv_ocs
                                    ON #__inv_computer.id_ocs = #__inv_ocs.ID
                                  LEFT OUTER JOIN #__inv_adpc
                                    ON #__inv_computer.id_adpc = #__inv_adpc.id ";
                                break;
                            case "net":
                                $query .= " INNER JOIN #__inv_net
                                    ON #__inv_net.id_obj = #__inv_object.id
                                  INNER JOIN #__inv_net_type
                                    ON #__inv_net.id_active_type = #__inv_net_type.id
                                  INNER JOIN #__inv_net_speed
                                    ON #__inv_net.id_base_speed = #__inv_net_speed.id
                                  INNER JOIN #__inv_net_speed #__inv_net_speed_1
                                    ON #__inv_net.id_max_uplink = #__inv_net_speed_1.id
                                  LEFT OUTER JOIN #__inv_zabbix
                                    ON #__inv_net.id_zabbix = #__inv_zabbix.id ";
                                break;
                            case "prn":
                                $query .= " INNER JOIN #__inv_prn
									    ON #__inv_prn.id_obj = #__inv_object.id
									  INNER JOIN #__inv_prn_format
									    ON #__inv_prn.id_format = #__inv_prn_format.id
									  INNER JOIN #__inv_prn_type
									    ON #__inv_prn.id_type = #__inv_prn_type.id
									  INNER JOIN #__inv_prn_type_print
									    ON #__inv_prn.id_type_print = #__inv_prn_type_print.id
									  LEFT OUTER JOIN #__inv_zabbix
    									ON #__inv_prn.id_zabbix = #__inv_zabbix.id ";
                                break;
                            case "monitor":
                                $query .= " INNER JOIN #__inv_monitor
                                            ON #__inv_monitor.id_obj = #__inv_object.id
                                          LEFT OUTER JOIN #__inv_mon_matrix_type
                                             ON #__inv_monitor.id_matrix_type = #__inv_mon_matrix_type.id ";
                                break;
                            case "sup":
                                $query .= "  INNER JOIN #__inv_supplies
                                    ON #__inv_supplies.id_obj = #__inv_object.id
                                  INNER JOIN #__inv_sup_type
                                    ON #__inv_supplies.id_supplies_type = #__inv_sup_type.id ";
                                break;
                        }


                        $query .= " {$where} {$group} ORDER BY {$sortingField} {$sortingOrder} LIMIT {$firstRowIndex}, {$rowsPerPage}";

                        $db->setQuery($query);//Выполняем запрос
                        $rList = $db->loadObjectList();

                        if (JRequest::getVar('single') == '1' && $rList[0]->id){
                            $response = $rList[0];
                            return json_encode($response);
                        }else{
                            $response->page = $curPage;
                            $response->total = ceil($totalRows / $rowsPerPage);
                            $response->records = $totalRows;
                            $i=0;

                            switch ($objtype){
                                case "group":
                                    foreach($rList as $row){
                                        $response->rows[$i]['id']=$row->id;
                                        $response->rows[$i]['cell']=array(
                                            $row->gr_name, $row->gr_user, $row->obj_description, $row->count_ch,
                                            $row->id, $row->name, $row->purchase_date, $row->i_number, $row->i_number_adv,
                                            $row->accounting, $row->supplier, $row->resp_user,
                                            $row->code, $row->reg, $row->pes, $row->po, $row->a
                                        );
                                        $i++;
                                    }
                                    break;
                                case "computer":
                                    foreach($rList as $row){
                                        $response->rows[$i]['id']=$row->id;
                                        $response->rows[$i]['cell']=array(
                                            $row->id, $row->name, $row->purchase_date, $row->s_number, $row->i_number, $row->i_number_adv, $row->barcode,
                                            $row->guaranty, $row->accounting, $row->vendor, $row->country, $row->supplier, $row->obj_description, $row->resp_user,
                                            $row->user, $row->department, $row->code, $row->reg, $row->pes, $row->po, $row->a,

                                            $row->chasis, $row->model, $row->osname, $row->comp_type, $row->processor,
                                            $row->mem, $row->motherboard, $row->hdd, $row->psu, $row->graphics, $row->network,
                                            $row->mac, $row->config, $row->ad_name, $row->zabbix_name, $row->ocs_name
                                        );
                                        $i++;
                                    }
                                    break;
                                case "net":
                                    foreach($rList as $row){
                                        $response->rows[$i]['id']=$row->id;

                                        $response->rows[$i]['cell']=array(
                                            $row->id, $row->name, $row->purchase_date, $row->s_number, $row->i_number, $row->i_number_adv, $row->barcode,
                                            $row->guaranty, $row->accounting, $row->vendor, $row->country, $row->supplier, $row->obj_description, $row->resp_user,
                                            $row->user, $row->department, $row->code, $row->reg, $row->pes, $row->po, $row->a,

                                            $row->active_net_type, $row->chasis, $row->model,$row->zabbix_name, $row->num_ethernet, $row->num_uplink, $row->num_sfp,
                                            $row->num_poe, $row->num_fxs, $row->num_usb, $row->base_speed, $row->max_uplink,
                                            $row->slots, $row->rack, $row->num_unit, $row->forwarding_rate, $row->mac_table, $row->mem, $row->flash, $row->stack,
                                            $row->console, $row->web, $row->telnet_ssh, $row->snmp, $row->management, $row->outdoors, $row->poe, $row->poe_standart,
                                            $row->voice, $row->voice_standart, $row->wifi, $row->wifi_standart, $row->power_standart, $row->backup_power, $row->os, $row->act_description
                                        );
                                        $i++;
                                    }
                                    break;
                                case "prn":
                                    foreach($rList as $row){
                                        $response->rows[$i]['id']=$row->id;
                                        $response->rows[$i]['cell']=array(
                                            $row->id, $row->name, $row->purchase_date, $row->s_number, $row->i_number, $row->i_number_adv, $row->barcode,
                                            $row->guaranty, $row->accounting, $row->vendor, $row->country, $row->supplier, $row->obj_description, $row->resp_user,
                                            $row->user, $row->department, $row->code, $row->reg, $row->pes, $row->po, $row->a,

                                            $row->print_type,$row->chasis,$row->model,$row->print_srv_name,$row->zabbix_name,$row->print_type_print,$row->color,
                                            $row->format_print, $row->photo, $row->duplex_printing, $row->ethernet, $row->network, $row->prn_mac, $row->wifi,
                                            $row->speed_print, $row->speed_scan, $row->mem, $row->sheets, $row->date_sheets, $row->fax, $row->m_resource,
                                            $row->prn_os, $row->prn_description, $row->id_template,
                                        );
                                        $i++;
                                    }
                                    break;
                                case "monitor":
                                    foreach($rList as $row){
                                        $response->rows[$i]['id']=$row->id;
                                        $response->rows[$i]['cell']=array(
                                            $row->id, $row->name, $row->purchase_date, $row->s_number, $row->i_number, $row->i_number_adv, $row->barcode,
                                            $row->guaranty, $row->accounting, $row->vendor, $row->country, $row->supplier, $row->obj_description, $row->resp_user,
                                            $row->user, $row->department, $row->code, $row->reg, $row->pes, $row->po, $row->a,

                                            $row->chasis, $row->model, $row->size, $row->format, $row->resolution, $row->led, $row->dynamics, $row->video_inputs, $row->power,
                                            $row->matrix_type, $row->description, $row->id_matrix_type
                                        );
                                        $i++;
                                    }
                                    break;
                                case "sup":
                                    foreach($rList as $row){
                                        $response->rows[$i]['id']=$row->id;
                                        $response->rows[$i]['cell']=array(
                                            $row->id, $row->name, $row->purchase_date, $row->s_number, $row->i_number, $row->i_number_adv, $row->barcode,
                                            $row->guaranty, $row->accounting, $row->vendor, $row->country, $row->supplier, $row->obj_description, $row->resp_user,
                                            $row->user, $row->department, $row->code, $row->reg, $row->pes, $row->po, $row->a,

                                            $row->sup_type_name, $row->model, $row->sup_count, $row->sup_field1, $row->sup_field2, $row->sup_field3, $row->sup_field4,
                                            $row->sup_field5,$row->sup_description
                                        );
                                        $i++;
                                    }
                                    break;
                                default :

                                    foreach($rList as $row){
                                        $response->rows[$i]['id']=$row->id;

                                        $response->rows[$i]['cell']=array(
                                            $row->id, $row->name, $row->purchase_date, $row->s_number, $row->i_number, $row->i_number_adv, $row->barcode, $row->vendor, $row->supplier,
                                            $row->user, $row->user_dep, $row->resp_user, $row->id_user, $row->id_resp_user,
                                        );
                                        $i++;
                                    }
                                    break;
                            }

                            return json_encode($response);
                        }

                    }catch (PDOException $e) {
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }

					break;
				case add:
					try{
						$db->transactionStart();//Начинаем транзакцию
							
						//Создаем новый объект
						$db->setQuery("INSERT INTO #__inv_object 
									(id_reg, id_obj_type, name, s_number, id_vendor, guaranty, barcode, obj_description, obj_creator, id_user, id_store)
								VALUES ({$location->id_reg}, {$prop->id_obj_type}, '{$prop->name}', '{$prop->s_number}',
										{$prop->id_vendor}, {$prop->guaranty}, '{$prop->barcode}','{$prop->description}', {$creator->id}, {$prop->id_user}, {$prop->id_store})");
						$db->query();
						
						$body   = "<p>Объект: {$prop->name}</p><p>Серийный номер: {$prop->s_number}</p><p>Инвентарный номер: {$prop->i_number}</p><p>Инвентарный номер: {$prop->i_number_adv}</p>";
						
						$id_object = $db->insertid();//Запоминаем id объекта

                        //Заполняем таблицу бухгалетрских характеристик
                        if ($edit->buh){//Если изменено бухгалтерские характеристики объкта
                            $buh = json_decode(JRequest::getVar('buh'));
                            if ($buh->id_supplier == '') $buh->id_supplier = 'null';
                            if ($buh->id_resp_user == '') $buh->id_resp_user = 'null';
                                $db->setQuery("INSERT INTO #__inv_obj_buh
												(id_obj, buh_name, id_type_accounting, i_number, i_number_adv, id_supplier, id_resp_user, buh_cost, purchase_date, buh_date, amortization)
										VALUES ({$id_object}, '{$buh->buh_name}',{$buh->id_type_accounting},'{$buh->i_number}','{$buh->i_number_adv}',{$buh->id_supplier},{$buh->id_resp_user},'{$buh->buh_cost}',
										        '{$buh->purchase_date}','{$buh->buh_date}','{$buh->amortization}' )");
                                $db->query();
                                $body .= "<p>Добавлены бухгалтерские характеристики</p><p>Название бух:{$buh->buh_name}</p><p>Вид учета:{$buh->type_accounting}</p><p>Инвентарный #1:'{$buh->i_number}'</p><p>Инвентарный #2:'{$buh->i_number_adv}'</p>";
						}

                        //Заполняем таблицу подобъектов
                        if ($obj_parts){
                            $part_date = date("Y-n-j");// Дата добавления подобъектов в объект
                            $query = "INSERT INTO #__inv_obj_parts (id_obj, id_obj_part, date) VALUES ";
                            foreach($obj_parts as $row){
                                $query .= "({$id_object}, {$row->id_obj}, '{$part_date}'),";
                            }
                            $query = substr($query, 0, strlen($query)-1);//Отризаем последнюю запятую
                            $db->setQuery($query);
                            $db->query();
                        }
						
						//Пишем данные о перемещении
						if (!$location->date) $location->date = date('Y-m-d H:i:s');
                        if (!$location->id_obj) $location->id_obj = 'null';
						$db->setQuery("INSERT INTO #__inv_obj_moving
										(id_obj, date, code, id_region, id_pes, id_po, id_a, latitude, longitude, altitude)
								VALUES ({$id_object}, '{$location->date}','{$location->code}',{$location->id_reg},{$location->id_pes},{$location->id_obj},{$location->id_a},'{$location->latitude}','{$location->longitude}','{$location->altitude}' )");
						$db->query();
						$body   .= "<p>Местоположение</p><p>Дата: {$location->date}</p><p>Код: {$location->code}</p><p>Регион: {$location->reg}</p><p>ПЭС: {$location->pes}</p><p>Объект: {$location->po}</p><p>Помещение: {$location->a}</p>";
                        if ($location->subobjects){//Если установлен признак изменения местоположения у подобъектов
                            $arr = $this->updateLocation($id_object);//Находим потомков объекта
                            if (count($arr) > 0) {
                                $query = "INSERT INTO #__inv_obj_moving (id_obj, date, code, id_region, id_pes, id_po, id_a, latitude, longitude, altitude) VALUES ";
                                foreach ($arr as $value) {
                                    $query .= "({$value}, '{$location->date}','{$location->code}',{$location->id_reg},{$location->id_pes},{$location->id_obj},{$location->id_a},'{$location->latitude}','{$location->longitude}','{$location->altitude}' ),";
                                }
                                $query = substr($query, 0, strlen($query) - 1);//Отрезаем последнюю запятую
                                $db->setQuery($query);
                                $db->query();
                            }
                        }
						
						//Заполняем таблицу документов
                        $obj_docs = json_decode(JRequest::getVar('obj_docs'));
						if (count($obj_docs) > 0){

							$query = "INSERT INTO #__inv_obj_docs (id_obj, id_doc) VALUES ";
							foreach($obj_docs as $row){
								$query .= "({$id_object}, {$row->id_doc}),";
							}
							$query = substr($query, 0, strlen($query)-1);//Отризаем последнюю запятую
							$db->setQuery($query);
							$db->query();
						}
					
						//Добавляем пользователя
						if ($prop->id_user){
							if ($id_user_date == '') $id_user_date = date('Y-m-d H:i:s');
							$db->setQuery("INSERT INTO #__inv_obj_users
									(id_obj, id_user, date)
									VALUES ({$id_object}, {$prop->id_user}, '{$id_user_date}')");
							$db->query();														
						}
						//Добавляем Мат. ответственного пользователя
						if ($buh->id_resp_user){
							if ($id_resp_user_date == '') $id_resp_user_date = date('Y-m-d H:i:s');
							$db->setQuery("INSERT INTO #__inv_obj_resp_users
									(id_obj, id_user, date)
									VALUES ({$id_object}, {$buh->id_resp_user}, '{$id_resp_user_date}')");
									$db->query();
						}
						switch ($prop->obj_type){
                            case "group":// Если объект группа

                                $group = json_decode(JRequest::getVar('group'));

                                //Добавляем группу
                                if ($group->gr_user) $id_gr_user = $user->id;
                                else $id_gr_user = 'null';
                                $db->setQuery("INSERT INTO #__inv_group
											(id_obj, gr_name, id_gr_user)
											VALUES ({$id_object}, '{$group->gr_name}', $id_gr_user)");
                                $db->query();
                                break;

                            case "computer":// Если объект компьютер
								
								$computer = json_decode(JRequest::getVar('computer'));
								
								//Добавляем компьютер
								$db->setQuery("INSERT INTO #__inv_computer
											(id_obj, id_type, id_adpc, id_zabbix, id_ocs, chasis, model, osname, config, processor, mem, motherboard, hdd, psu, graphics, network, mac, description)
											VALUES ({$id_object}, {$computer->id_type}, {$computer->id_adpc}, {$computer->id_zabbix}, {$computer->id_ocs}, '{$computer->chasis}', '{$computer->model}', '{$computer->osname}', '{$computer->config}', '{$computer->processor}', '{$computer->mem}', '{$computer->motherboard}',
										 '{$computer->hdd}', '{$computer->psu}', '{$computer->graphics}', {$computer->network}, '{$computer->mac}', '{$computer->description}')");
								$db->query();
								$id_computer = $db->insertid();
								
								//Добавляем в историю изменений аппаратной части компьютера
								$comp_date = date("Y.n.j");
								$db->setQuery("INSERT INTO #__inv_comp_hardware
										(id_computer, date, processor, mem, motherboard, hdd, psu, graphics, network, mac, description)
										VALUES ({$id_computer}, '{$computer->date}', '{$computer->processor}', '{$computer->mem}', '{$computer->motherboard}',
										 '{$computer->hdd}', '{$computer->psu}', '{$computer->graphics}', {$computer->network}, '{$computer->mac}', '{$computer->description}')");
										$db->query();
										
								
								$body   .= "Шасси: {$computer->chasis} Модель: {$computer->model} Операционная система: {$computer->osname} Конфигурация: {$computer->config} ";
								break;	
							case monitor:// Если объект монитор
								
								$monitor = json_decode(JRequest::getVar('monitor'));
								$query = "INSERT INTO #__inv_monitor (id_obj, chasis, model, size, format, resolution, led, dynamics, id_matrix_type, video_inputs, power, description ) VALUES ";
							    $query .= "({$id_object}, '{$monitor->chasis}', '{$monitor->model}', {$monitor->size}, {$monitor->format}, '{$monitor->resolution}', {$monitor->led}, {$monitor->dynamics}, {$monitor->idmatrix_type}, '{$monitor->video_inputs}', {$monitor->power}, '{$monitor->description}' )";
								$db->setQuery($query);
								$db->query();
								
								break;
							case prn://Если объект печатающее, сканирующее устройство		
													
								$prn = json_decode(JRequest::getVar('prn'));
								$query = "INSERT INTO #__inv_prn (id_obj, chasis, model, print_srv_name, id_zabbix, id_type, id_type_print, id_type_scan, color,id_format,
  																  photo, duplex_printing, ethernet, network, prn_mac, fax,  wifi, speed_print, speed_scan, mem, sheets, date_sheets, m_resource, prn_os, prn_description, id_template) VALUES ";
								$query .= "({$id_object}, '{$prn->chasis}', '{$prn->model}', '{$prn->prn_srv_name}', {$prn->id_zabbix}, {$prn->id_type}, {$prn->id_type_print}, {$prn->id_type_scan}, {$prn->color}, {$prn->id_format},
  																  {$prn->photo}, {$prn->duplex_printing}, {$prn->ethernet}, {$prn->network}, '{$prn->prn_mac}', {$prn->fax},  {$prn->wifi}, '{$prn->speed_print}',
  																 '{$prn->speed_scan}', '{$prn->mem}', '{$prn->sheets}', '{$prn->date_sheets}', '{$prn->m_resource}', '{$prn->prn_os}', '{$prn->prn_description}', {$prn->id_template})";
								$db->setQuery($query);
								$db->query();
                                $body   .= "<p>Шасси: {$prn->chasis}</p><p>Модель: {$prn->model}</p><p>Имя на принтсервере: {$prn->prn_srv_name}</p>";
							
								break;
                            case net://Если объект сетевое устройство		

                                $net = json_decode(JRequest::getVar('net'));
                                if ($net->id_template == '') $net->id_template = 'null';
                                if ($net->id_zabbix == '') $net->id_zabbix = 'null';
                                $query = "INSERT INTO #__inv_net (id_obj, id_active_type, chasis, model,  num_ethernet,  num_uplink, num_sfp, num_poe, num_fxs, num_usb,id_base_speed,id_max_uplink,
                                                                slots, rack, num_unit, forwarding_rate, mac_table, mem, flash, stack,
                                                                console, web, telnet_ssh, snmp, management, outdoors,  poe, poe_standart, voice, voice_standart,
                                                                wifi, wifi_standart, power_standart, backup_power, os, act_description,
                                                                id_template, id_zabbix) VALUES ";
                                $query .= "({$id_object}, {$net->id_active_type}, '{$net->chasis}', '{$net->model}', {$net->num_ethernet}, {$net->num_uplink}, {$net->num_sfp},
                                                        {$net->num_poe}, {$net->num_fxs}, {$net->num_usb}, {$net->id_base_speed}, {$net->id_max_uplink},
                                                        '{$net->slots}', {$net->rack}, {$net->num_unit}, '{$net->forwarding_rate}', '{$net->mac_table}', '{$net->mem}', '{$net->flash}', {$net->stack},
                                                        {$net->console}, {$net->web}, {$net->telnet_ssh}, {$net->snmp}, {$net->management}, {$net->outdoors}, {$net->poe}, '{$net->poe_standart}',
                                                        {$net->voice}, '{$net->voice_standart}', {$net->wifi}, '{$net->wifi_standart}', '{$net->power_standart}', {$net->backup_power}, '{$net->os}', '{$net->act_description}',
                                                        {$net->id_template}, {$net->id_zabbix})";
                                $db->setQuery($query);
                                $db->query();
                                $body   .= "<p>Шасси: {$net->chasis}</p><p>Модель: {$net->model}</p>";

                                break;
							case sup://Если объект расходный материал
										
								$sup = json_decode(JRequest::getVar('sup'));
								$query = "INSERT INTO #__inv_supplies (id_obj, id_supplies_type, model, sup_count, sup_field1, sup_field2, sup_field3, sup_field4, sup_field5, sup_description) VALUES ";
								$query .= "({$id_object}, {$sup->id_supplies_type}, '{$sup->model}', {$sup->sup_count}, '{$sup->field1}', '{$sup->field2}', '{$sup->field3}', '{$sup->field4}', '{$sup->field5}', '{$sup->sup_description}')";
								$db->setQuery($query);
								$db->query();
										
								break;
						}	
						
						$mailer =& JFactory::getMailer();
						$mailer->setSender('inventory@mrsk-cp.ru');
						
						if ($buh->id_resp_user){
							$query = "SELECT #__inv_users.mail FROM #__inv_users WHERE #__inv_users.id = {$buh->id_resp_user}";
							$db->setQuery($query);//Выполняем запрос
							$Recipient = $db->loadResult();							
							$mailer->addRecipient($Recipient);
						}
						
						$mailer->setSubject("Добавлен новый объект инвентаризации {$prop->name} id {$id_object}");
						$body   .= "<p>Это письмо составлено роботом. Пожалуйста не отвечайте на него</p>";
						$mailer->isHTML(true);						
						$mailer->setBody($body);
						$send = $mailer->Send();
						if ($send !== true) {
							$this->log("Ошибка отправки письма".$Recipient);
						} else {
							$this->log("Письмо отправлено ".$Recipient);
						}
						
						if  ($prop->id_user){
							$query = "SELECT #__inv_users.mail FROM #__inv_users WHERE #__inv_users.id = {$prop->id_user}";
							$db->setQuery($query);//Выполняем запрос
							$Recipient = $db->loadResult();
							$mailer->addRecipient($Recipient);
							$mailer->setSubject("За вами закреплен объект инвентаризации {$prop->name}");
							$send =& $mailer->Send();
						}
						if ($send !== true) {
							$this->log("Ошибка отправки письма".$Recipient);
						} else {
							$this->log("Письмо отправлено ".$Recipient);
						}
						
						$db->transactionCommit();
						
						$this->log("Добавлен новый объект {$prop->name} {$prop->type} id {$id_object}");
					}
					catch (PDOException $e) {
						$db->transactionRollback();
						JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
					} 
					break;
				case del:
					$id = JRequest::getVar('id');
					$this->log("Удален объект id {$id}");					
					$db->setQuery("DELETE FROM #__inv_object WHERE #__inv_object.id = {$id}");
					$db->query();
					break;
				case edit:
					try{
						$db->transactionStart();//Начинаем транзакцию

						//Редактируем объект									
						$date_edit =date('Y-m-d H:i:s');
						$db->setQuery("UPDATE #__inv_object
										SET
										name = '{$prop->name}',
										s_number = '{$prop->s_number}',
										id_vendor = {$prop->id_vendor},
										id_user = {$prop->id_user},
										guaranty = {$prop->guaranty},
										barcode = '{$prop->barcode}',
										obj_description = '{$prop->description}',
										editor = {$user->id},
										date_edit = '{$date_edit}',
										id_store = {$prop->id_store}
										WHERE id = {$prop->id}");
						$db->query();
					
						$body   = "<p>Объект: {$prop->name}</p><p>Серийный номер: {$prop->s_number}</p>";

						if ($edit->buh){//Если изменено бухгалтерские характеристики объкта
							//Обновляем бухгалтерские характеристики
                            $buh = json_decode(JRequest::getVar('buh'));
                            if ($buh->id_supplier == '') $buh->id_supplier = 'null';
                            if ($buh->id_resp_user == '') $buh->id_resp_user = 'null';
							//Редактируем объект
                            $query = "SELECT COUNT(id_obj) AS count FROM #__inv_obj_buh WHERE #__inv_obj_buh.id_obj={$prop->id}";
                            $db->setQuery($query);
                            $rows = $db->loadResult();
                            if ($rows > 0) {
                                $db->setQuery("UPDATE #__inv_obj_buh
										SET
										buh_name = '{$buh->buh_name}',
										id_type_accounting = {$buh->id_type_accounting},
										i_number = '{$buh->i_number}',
										i_number_adv = '{$buh->i_number_adv}',
										id_supplier = {$buh->id_supplier},
										id_resp_user = {$buh->id_resp_user},
										buh_cost = {$buh->buh_cost},
										purchase_date = '{$buh->purchase_date}',
										buh_date = '{$buh->buh_date}',
										amortization = '{$buh->amortization}'
										WHERE id_obj = {$prop->id}");
                                $db->query();

                                $body .= "<p>Изменены бухгалтерские характеристики</p><p>Бухгалтерское наименование: {$buh->buh_name}</p><p>Инвентарный №1: {$buh->i_number}</p><p>Инвентарный №2: {$buh->i_number_adv}</p>";
                            }else{
                                $db->setQuery("INSERT INTO #__inv_obj_buh
												(id_obj, buh_name, id_type_accounting, i_number, i_number_adv, id_supplier, id_resp_user, buh_cost, purchase_date, buh_date, amortization)
										VALUES ({$prop->id}, '{$buh->buh_name}',{$buh->id_type_accounting},'{$buh->i_number}','{$buh->i_number_adv}',{$buh->id_supplier},{$buh->id_resp_user},'{$buh->buh_cost}',
										        '{$buh->purchase_date}','{$buh->buh_date}','{$buh->amortization}' )");
                                $db->query();
                                $body .= "<p>Добавлены бухгалтерские характеристики</p><p>Бухгалтерское наименование: {$buh->buh_name}</p><p>Инвентарный №1: {$buh->i_number}</p><p>Инвентарный №2: {$buh->i_number_adv}</p>";
                            }
						}

						if ($edit->parts){
							//Заполняем таблицу подобъектов
							if (JRequest::getVar('obj_parts') != 'null'){
								$obj_parts = json_decode(JRequest::getVar('obj_parts'));
								$db->setQuery("DELETE FROM  #__inv_obj_parts WHERE  #__inv_obj_parts.id_obj = {$prop->id}");
								$db->query();								
								$part_date = date("Y-n-j");// Дата добавления подобъектов в объект
								$query = "INSERT INTO #__inv_obj_parts (id_obj, id_obj_part, date, remove, date_remove) VALUES ";
								$i = 0;
								foreach($obj_parts as $row){
									if ($row->date == '') $row->date = date('Y-m-d');//Если нет даты добавления ставим дату
									if ($row->remove === 'No') $row->remove = 0;
									if ($row->remove === 'Yes') $row->remove = 1;
									if (($row->remove == 1) && ($row->date_remove == '')) $row->date_remove = "'".date('Y-m-d')."'";//Если нет даты удаления ставим дату;
									if ($row->date_remove == '') $row->date_remove = "null";
									else{
										$row->date_remove = "'".$row->date_remove."'";
										$row->remove = 1;
									}		
									$query .= "({$prop->id}, {$row->id_obj}, '{$row->date}', {$row->remove}, {$row->date_remove} ),";
									$i++;
								}
								if ($i > 0){
									$query = substr($query, 0, strlen($query)-1);//Отрезаем последнюю запятую
									$db->setQuery($query);
									$db->query();	
								}
							}
						}

                        if ($edit->location){//Если изменено местоположение объкта
                            //Пишем данные о перемещении
                            if (!$location->date) $location->date =date('Y-m-d H:i:s');
                            $db->setQuery("INSERT INTO #__inv_obj_moving
												(id_obj, date, code, id_region, id_pes, id_po, id_a, latitude, longitude, altitude)
										VALUES ({$prop->id}, '{$location->date}','{$location->code}',{$location->id_reg},{$location->id_pes},{$location->id_obj},{$location->id_a},'{$location->latitude}','{$location->longitude}','{$location->altitude}' )");
                            $db->query();
                            $body   .= "<p>Изменено местоположение</p><p>Дата: {$location->date}</p><p>Код: {$location->code}</p><p>Регион: {$location->reg}</p><p>ПЭС: {$location->pes}</p><p>Объект: {$location->po}</p><p>Помещение: {$location->a}</p>";
                            if ($location->subobjects){//Если установлен признак изменения местоположения у подобъектов
                                $arr = $this->updateLocation($prop->id);//Находим потомков объекта
                                if (count($arr) > 0) {
                                    $query = "INSERT INTO #__inv_obj_moving (id_obj, date, code, id_region, id_pes, id_po, id_a, latitude, longitude, altitude) VALUES ";
                                    foreach ($arr as $value) {
                                        $query .= "({$value}, '{$location->date}','{$location->code}',{$location->id_reg},{$location->id_pes},{$location->id_obj},{$location->id_a},'{$location->latitude}','{$location->longitude}','{$location->altitude}' ),";
                                    }
                                    $query = substr($query, 0, strlen($query) - 1);//Отрезаем последнюю запятую
                                    $db->setQuery($query);
                                    $db->query();
                                }
                            }
                        }

						//Заполняем таблицу документов
						if ($edit->docs){
							if (JRequest::getVar('obj_docs') != 'null'){
								$obj_docs = json_decode(JRequest::getVar('obj_docs'));
								$db->setQuery("DELETE FROM  #__inv_obj_docs WHERE  #__inv_obj_docs.id_obj = {$prop->id}");
								$db->query();
								$query = "INSERT INTO #__inv_obj_docs (id_obj, id_doc) VALUES ";
								$i=0;		
								foreach($obj_docs as $row){
									$query .= "({$prop->id}, {$row->id_doc}),";
									$i++;
								}
								if ($i > 0){
									$query = substr($query, 0, strlen($query)-1);//Отрезаем последнюю запятую
									$db->setQuery($query);
									$db->query();
								}
							}
						}
											
						if ($edit->user){
							//Добавляем пользователя
							if ($prop->user_date == 'null') $prop->user_date = date('Y-m-d H:i:s');
							$db->setQuery("INSERT INTO #__inv_obj_users
											(id_obj, id_user, date)
											VALUES ({$prop->id}, {$prop->id_user}, '$prop->user_date')");
							$db->query();		
						}
						$this->log("МОЛ {$edit->resp_user} {$buh->id_resp_user} {$prop->resp_user_date}");
						if ($edit->resp_user){
							//Добавляем Мат. ответственного пользователя
							if ($prop->resp_user_date == 'null') $prop->resp_user_date = date('Y-m-d H:i:s');
							$db->setQuery("INSERT INTO #__inv_obj_resp_users
									(id_obj, id_user, date)
									VALUES ({$prop->id}, {$buh->id_resp_user}, '{$prop->resp_user_date}')");
							$db->query();	
						}
						switch ($prop->obj_type){
							case computer:
								$computer = json_decode(JRequest::getVar('computer'));
								if ($computer->id_adpc == null) $computer->id_adpc = 'null';
								if ($computer->id_zabbix == null) $computer->id_zabbix = 'null';
								if ($computer->id_ocs == null) $computer->id_ocs = 'null';
								if ($edit->comp){					
									//Добавляем компьютер
									$db->setQuery("UPDATE #__inv_computer SET
												 id_type = {$computer->id_type},
												 id_adpc = {$computer->id_adpc},
												 id_zabbix = {$computer->id_zabbix},
												 id_ocs = {$computer->id_ocs},
												 chasis = '{$computer->chasis}',
												 model = '{$computer->model}',
												 osname = '{$computer->osname}',
												 config = '{$computer->config}'
												WHERE id_obj = {$prop->id}");
									$db->query();
								}
								if ($edit->comp_hardware){
									if ($computer->date == 'null') $comp_date = date('Y-m-d H:i:s');
									$db->setQuery("INSERT INTO #__inv_comp_hardware
												(id_computer, date, processor, mem, motherboard, hdd, psu, graphics, network, mac, description)
												VALUES ({$computer->id_comp}, '{$comp_date}', '{$computer->processor}', '{$computer->mem}', '{$computer->motherboard}',
												'{$computer->hdd}', '{$computer->psu}', '{$computer->graphics}', {$computer->network}, '{$computer->mac}', '{$computer->description}')");
									$db->query();
										
									$body   .= "<p>Изменена конфигурация компьютера</p><p>Шасси: {$computer->chasis}</p><p>Модель: {$computer->model}</p><p>Операционная система: {$computer->osname}</p><p>Конфигурация: {$computer->config}</p>";
								}
								break;
							case monitor:
								if ($edit->monitor){
									$monitor = json_decode(JRequest::getVar('monitor'));
									$query = "UPDATE #__inv_monitor SET 
												chasis = '{$monitor->chasis}',
												model = '{$monitor->model}',
											 	size = {$monitor->size},
											 	format = {$monitor->format},
												resolution = '{$monitor->resolution}',
												led = {$monitor->led},
												dynamics = {$monitor->dynamics},
												id_matrix_type = {$monitor->idmatrix_type},
												video_inputs = '{$monitor->video_inputs}',
												power = {$monitor->power},
												description = '{$monitor->description}' 
											WHERE id_obj = {$prop->id}";
									$db->setQuery($query);
									$db->query();
                                    $body   .= "<p>Изменены характеристики устройства</p><p>Шасси: {$net->chasis}</p><p>Модель: {$net->model}</p>";
								}
								break;
							case prn:
								if ($edit->prn){	
									$prn = json_decode(JRequest::getVar('prn'));									
									$query = "UPDATE #__inv_prn SET
													chasis = '{$prn->chasis}',
													model = '{$prn->model}',
													print_srv_name = '{$prn->prn_srv_name}',
													id_zabbix = {$prn->id_zabbix},
													id_type = {$prn->id_type},
													id_type_print = {$prn->id_type_print},
													id_type_scan = {$prn->id_type_scan},
													color = {$prn->color},
													id_format = {$prn->id_format},
  													photo = {$prn->photo},
  													duplex_printing = {$prn->duplex_printing},
  													ethernet = {$prn->ethernet},
  													network = {$prn->network},
  													prn_mac = '{$prn->prn_mac}',
  													fax = {$prn->fax},
  													wifi = {$prn->wifi},
  													speed_print = '{$prn->speed_print}',
  													speed_scan = '{$prn->speed_scan}',
  													mem = '{$prn->mem}',
  													sheets = '{$prn->sheets}',
  													date_sheets = '{$prn->date_sheets}',
  													m_resource = '{$prn->m_resource}',
  													prn_os = '{$prn->prn_os}',
  													prn_description = '{$prn->prn_description}',
  													id_template = {$prn->id_template}
  												WHERE id_obj = {$prop->id}";
									$db->setQuery($query);
									$db->query();
                                    $body   .= "<p>Изменены характеристики устройства</p><p>Шасси: {$net->chasis}</p><p>Модель: {$net->model}</p>";
								}										
								break;
                            case net:
                                if ($edit->net){
                                    $net = json_decode(JRequest::getVar('net'));
                                    if ($net->id_template == '') $net->id_template = 'null';
                                    if ($net->id_zabbix == '') $net->id_zabbix = 'null';
                                    $query = "UPDATE #__inv_net SET
													id_active_type = {$net->id_active_type},
                                                    chasis = '{$net->chasis}',
                                                    model = '{$net->model}',
                                                    num_ethernet = {$net->num_ethernet},
                                                    num_uplink = {$net->num_uplink},
                                                    num_sfp = {$net->num_sfp},
                                                    num_poe = {$net->num_poe},
                                                    num_fxs = {$net->num_fxs},
                                                    num_usb = {$net->num_usb},
                                                    id_base_speed = {$net->id_base_speed},
                                                    id_max_uplink = {$net->id_max_uplink},
                                                    num_uplink = {$net->num_uplink},
                                                    slots = '{$net->slots}',
                                                    rack = {$net->rack},
                                                    num_unit = {$net->num_unit},
                                                    forwarding_rate = '{$net->forwarding_rate}',
                                                    mac_table = '{$net->mac_table}',
                                                    mem = '{$net->mem}',
                                                    flash = '{$net->flash}',
                                                    stack = {$net->stack},
                                                    console = {$net->console},
                                                    web = {$net->web},
                                                    telnet_ssh = {$net->telnet_ssh},
                                                    snmp = {$net->snmp},
                                                    management = {$net->management},
                                                    outdoors = {$net->outdoors},
                                                    poe = {$net->poe},
                                                    poe_standart = '{$net->poe_standart}',
                                                    voice = {$net->voice},
                                                    voice_standart = '{$net->voice_standart}',
                                                    wifi = {$net->wifi},
                                                    wifi_standart = '{$net->wifi_standart}',
                                                    power_standart = '{$net->power_standart}',
                                                    backup_power = {$net->backup_power},
                                                    os = '{$net->os}',
                                                    act_description = '{$net->act_description}',
                                                    id_zabbix = {$net->id_zabbix},
                                                    id_template = {$net->id_template}
  												WHERE id_obj = {$prop->id}";
                                    $db->setQuery($query);
                                    $db->query();
                                    $body   .= "<p>Изменены характеристики устройства</p><p>Шасси: {$net->chasis}</p><p>Модель: {$net->model}</p>";
                                }
                                break;
							case sup:
								if ($edit->sup){
									$sup = json_decode(JRequest::getVar('sup'));
									$query = "UPDATE #__inv_supplies SET
													id_supplies_type = {$sup->id_supplies_type},
													model = '{$sup->model}',
													sup_count = {$sup->sup_count},
													sup_field1 = '{$sup->sup_field1}',
													sup_field2 = '{$sup->sup_field2}',
													sup_field3 = '{$sup->sup_field3}',
													sup_field4 = '{$sup->sup_field4}',
													sup_field5 = '{$sup->sup_field5}',
													sup_description = '{$sup->sup_description}'
											WHERE id_obj = {$prop->id}";
									$db->setQuery($query);
									$db->query();
								}
								break;
                            case "group":
                                    $sup = json_decode(JRequest::getVar('group'));

                                    if ($group->gr_user) $id_gr_user = $user->id;
                                    else $id_gr_user = 'null';

                                    $query = "UPDATE #__inv_group SET
													gr_name = '{$sup->gr_name}',
													id_gr_user = {$id_gr_user}
											WHERE id_obj = {$prop->id}";
                                    $db->setQuery($query);
                                    $db->query();
                                break;
                        }
					
						$mailer =& JFactory::getMailer();
						$mailer->setSender('inventory@mrsk-cp.ru');

						if ($buh->id_resp_user){
							$query = "SELECT #__inv_users.mail FROM #__inv_users WHERE #__inv_users.id = {$buh->id_resp_user}";
							$db->setQuery($query);//Выполняем запрос
							$Recipient = $db->loadResult();
							$mailer->addRecipient($Recipient);
							$mailer->setSubject("Изменен объект инвентаризации {$prop->name} id {$prop->id}");
							$body   .= "<p>Это письмо составлено роботом. Пожалуйста не отвечайте на него</p>";
							$mailer->isHTML(true);
							$mailer->setBody($body);
							$send = $mailer->Send();
							if ($send !== true) $this->log("Ошибка отправки письма".$Recipient);
							else 	$this->log("Письмо отправлено ".$Recipient);
						}
					
						
											
						if  ($prop->id_user){
							$query = "SELECT #__inv_users.mail FROM #__inv_users WHERE #__inv_users.id = {$prop->id_user}";
							$db->setQuery($query);//Выполняем запрос
							$Recipient = $db->loadResult();
							$mailer->addRecipient($Recipient);
							$mailer->setSubject("За вами закреплен объект инвентаризации {$prop->name}");
							$send =& $mailer->Send();
						}
						if ($send !== true)	$this->log("Ошибка отправки письма".$Recipient);
						else $this->log("Письмо отправлено ".$Recipient);
					
						$db->transactionCommit();
					
						$this->log("Изменен объект {$prop->name} id {$prop->id}");
					}
					catch (PDOException $e) {
						$db->transactionRollback();
						JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
					}
					break;
			}
		}
		
	}
		
	function getObjTypeEdit() {//Редактирование типов объектов
			
		$rList = $this->getAccessUser();//Получаем права пользователей
	
		$id = JRequest::getVar('id');//Получаем тип операции
	
		$db = JFactory::getDbo();//Подключаемся к базе

		if ($rList[0]->admin){
			switch (JRequest::getVar('oper')){
				case sel:
					$db = JFactory::getDbo();//Подключаемся к базе
                    if (JRequest::getVar('all') == 'true'){
                        $query = "SELECT
                                  #__neq_base.*
                                FROM #__neq_base
                                ORDER BY #__neq_base.name";
                    }else{
                        $query = "SELECT
                                  #__neq_base.*
                                FROM #__neq_po
                                  INNER JOIN #__neq_base
                                    ON #__neq_po.basetype = #__neq_base.id
                                WHERE #__neq_po.pes = {$id}
                                GROUP BY #__neq_base.id,
                                         #__neq_base.code
                                ORDER BY #__neq_base.name";
                    }

					$db->setQuery($query);//Выполняем запрос
					$rList = $db->loadObjectList();
					
					$i=0;
					foreach($rList as $row){
						$response->rows[$i]['id']=$row->id;
						$response->rows[$i]['cell']=array($row->name, $row->code, JRequest::getVar('id'));
						$i++;
					}
					return json_encode($response);
					break;
				case add:
					$this->log("Добавлен тип оборудования ".JRequest::getVar('name'));
					$db->setQuery("INSERT INTO #__neq_base
							(name, code)
							VALUES ('".JRequest::getVar('name')."','".JRequest::getVar('code')."')");
					$db->query();
					break;
				case del:
					$this->log("Удалено тип оборудования".JRequest::getVar('id'));
					$db->setQuery("DELETE FROM #__neq_base WHERE #__neq_base.id = ".$id);
					$db->query();
					break;
				case edit:
					$this->log("Изменено название помещения ".JRequest::getVar('name'));
					$db->setQuery("UPDATE #__neq_base
						SET name='".JRequest::getVar('name')."',
							code='".JRequest::getVar('code')."'
						 	WHERE id = ".$id);
					$db->query();
					break;
			}
		}
	}

    /**
     * Редактирование таблицы мат. ответственных
     * @return string
     */
    function getRespUser() {
			
		$rList = $this->getAccessUser();//Получаем права пользователей
	
		$oper = JRequest::getVar('oper');//Получаем тип операции
	
		$db = JFactory::getDbo();//Подключаемся к базе
		
		if ($oper == 'autocomplete'){
			try {
			
				$s_query = JRequest::getVar('term');//Первые буквы поля			

				$query = "SELECT  #__inv_responsible_user.id_user AS id,  #__neq_region.Name AS region,  #__inv_users.cn AS user
								FROM #__inv_responsible_user
  								INNER JOIN #__inv_users
    								ON #__inv_responsible_user.id_user = #__inv_users.id
  								INNER JOIN #__neq_region
    								ON #__inv_users.id_reg = #__neq_region.id
								WHERE #__inv_users.cn LIKE '%{$s_query}%'
								ORDER BY region, user
								LIMIT 15";
				$db->setQuery($query);//Выполняем запрос
				$rList = $db->loadObjectList();
			
				foreach($rList as $row){
					$response[]=array('id' => $row->id, 'label'=> $row->user.' '.$row->region, 'value'=>$row->user);
				}
				return json_encode($response);
			}
			catch (PDOException $e) {
				return 'Ошибка базы данных: '.$e->getMessage();
			}
		}
	
		if ($rList[0]->admin){
			switch ($oper){
				case sel:
					$query = "SELECT  #__inv_responsible_user.id_user AS id,  #__neq_region.Name AS region,  #__inv_users.cn AS user
								FROM #__inv_responsible_user
  								INNER JOIN #__inv_users
    								ON #__inv_responsible_user.id_user = #__inv_users.id
  								INNER JOIN #__neq_region
    								ON #__inv_users.id_reg = #__neq_region.id
								ORDER BY region, user";
					$db->setQuery($query);//Выполняем запрос
					$rList = $db->loadObjectList();
						
					$i=0;
					foreach($rList as $row){
						$response->rows[$i]['id']=$row->id;
						$response->rows[$i]['cell']=array($row->region, $row->user);
						$i++;
					}
					return json_encode($response);
					break;
				case add:
					$this->log("Добавлен тип оборудования ".JRequest::getVar('name'));
					$db->setQuery("INSERT INTO #__inv_responsible_user
							(id_user)
							VALUES ('".JRequest::getVar('id_user')."')");
					$db->query();
					break;
				case del:
					$id = JRequest::getVar('id');
					$this->log("Удален мат. ответственный {$id}");
					$db->setQuery("DELETE FROM #__inv_responsible_user WHERE #__inv_responsible_user.id_user = {$id}");
					$db->query();
					break;
				case edit:
					$this->log("Изменено название помещения ".JRequest::getVar('name'));
					$id = JRequest::getVar('id');
					$db->setQuery("UPDATE #__neq_base
						SET name='".JRequest::getVar('name')."',
							code='".JRequest::getVar('code')."'
						 	WHERE id = ".$id);
					$db->query();
					break;
			}
		}
	}
	
	function getObjectsEdit() {//Изменение объектов
			
		$rList = $this->getAccessUser();//Получаем права пользователей	
		$oper = JRequest::getVar('oper');//Получаем тип операции	
		$db = JFactory::getDbo();//Подключаемся к базе
		$pes=JRequest::getVar('pes');
		$basetype=JRequest::getVar('basetype');
		$name=JRequest::getVar('name');
		$number=JRequest::getVar('number');
		$latitude=JRequest::getVar('latitude');
		$longitude=JRequest::getVar('longitude');
		$altitude=JRequest::getVar('altitude');
		
	
		if ($rList[0]->admin){
			switch (JRequest::getVar('oper')){
				case sel:
					$db = JFactory::getDbo();//Подключаемся к базе
					$query = "SELECT  #__neq_po.* FROM #__neq_po WHERE #__neq_po.pes = ".JRequest::getVar('pes')." AND #__neq_po.basetype = ".JRequest::getVar('basetype')." ORDER BY #__neq_po.name";
					$db->setQuery($query);//Выполняем запрос
					$rList = $db->loadObjectList();
					
					$i=0;
					foreach($rList as $row){
						$response->rows[$i]['id']=$row->id;
						$response->rows[$i]['cell']=array($row->name, $row->number, $row->pes, $row->basetype, $row->latitude, $row->longitude, $row->altitude);
						$i++;
					}
					return json_encode($response);
					break;
				case add:					
					$db->setQuery("INSERT INTO #__neq_po
							(pes, basetype, latitude, longitude, altitude, name, number)
							VALUES ('{$pes}', '{$basetype}', '{$latitude}', '{$longitude}', '{$altitude}', '{$name}', '{$number}')");
					$db->query();
					$this->log("Добавлен объект ".JRequest::getVar('name'));
					break;
				case del:					
					$id = JRequest::getVar('id');
					$db->setQuery("DELETE FROM #__neq_po WHERE #__neq_po.id = ".$id);
					$db->query();
					$this->log("Удален объект с id ".JRequest::getVar('id'));
					break;
				case edit:					
					$id = JRequest::getVar('id');
					$db->setQuery("UPDATE #__neq_po
						SET pes='{$pes}',
						   	basetype='{$basetype}',
						   	latitude='{$latitude}',
						   	longitude='{$longitude}',
						   	altitude='{$altitude}',
							name='{$name}',
							number='{$number}' 
						 	WHERE id = {$id}");
					$db->query();
					$this->log("Изменено объект ".JRequest::getVar('name'));
					break;
			}
		}
	}
	
	function getRoomEdit() {//Редактирование помещений на объектах
			
		$rList = $this->getAccessUser();//Получаем права пользователей
		
		$oper = JRequest::getVar('oper');//Получаем тип операции
		
		$db = JFactory::getDbo();//Подключаемся к базе
		
		if ($rList[0]->admin){
			switch (JRequest::getVar('oper')){
				case sel:
					$db = JFactory::getDbo();//Подключаемся к базе
					$query = "SELECT  #__neq_a.* FROM #__neq_a WHERE #__neq_a.codepo = ".JRequest::getVar('id');
					$db->setQuery($query);//Выполняем запрос
					$rList = $db->loadObjectList();
					
					$i=0;
					foreach($rList as $row){
						$response->rows[$i]['id']=$row->id;
						$response->rows[$i]['cell']=array($row->name, $row->code);
						$i++;
					}
					return json_encode($response);
					break;
				case add:
					$this->log("Добавлено помещение ".JRequest::getVar('name'));
					$db->setQuery("INSERT INTO #__neq_a
							(codepo, name, code)
							VALUES ('".JRequest::getVar('codepo')."','".JRequest::getVar('name')."','".JRequest::getVar('code')."')");
					$db->query();
					break;
				case del:
					$this->log("Удалено помещение с id ".JRequest::getVar('id'));
					$id = JRequest::getVar('id');
					$db->setQuery("DELETE FROM #__neq_a WHERE #__neq_a.id = ".$id);
					$db->query();
					break;
				case edit:
					$this->log("Изменено название помещения ".JRequest::getVar('name'));
					$id = JRequest::getVar('id');
					$db->setQuery("UPDATE #__neq_a
						SET codepo='".JRequest::getVar('codepo')."',
						   	name='".JRequest::getVar('name')."',
							code='".JRequest::getVar('code')."'
						 	WHERE id = ".$id);
					$db->query();
					break;
			}
		}
	}
	
	function getCartridge() {//Картриджи
			
		$rList = $this->getAccessUser();//Получаем права пользователей	
		$oper = JRequest::getVar('oper');//Получаем тип операции	
		$db = JFactory::getDbo();//Подключаемся к базе	
		$id_vendor = JRequest::getVar('id_vendor');
		$vendor = JRequest::getVar('vendor');
		$cartr_name = JRequest::getVar('cartr_name');
		$id_color = JRequest::getVar('id_color');
		$resource = JRequest::getVar('resource');
		$id = JRequest::getVar('id');
		
		if ($rList[0]->admin || $rList[0]->storekeeper){
			switch (JRequest::getVar('oper')){
				case sel:
					try {
						$curPage = JRequest::getVar('page');
						$rowsPerPage = JRequest::getVar('rows');
						$sortingField = JRequest::getVar('sidx');
						$sortingOrder =JRequest::getVar('sord');
						$search = JRequest::getVar('_search');
						$where = '';
							
						if ($search == 'true'){
							if (JRequest::getVar('id')) $where .= "WHERE #__inv_prn_cartridge.id = {$id}"; 
							else $where = "WHERE #__inv_prn_cartridge.id LIKE '%'";
							if (JRequest::getVar('vendor')) $where .= " AND vendor LIKE '{$vendor}%' ";
							if (JRequest::getVar('cartr_name')) $where .= " AND #__inv_prn_cartridge.cartr_name LIKE '%".JRequest::getVar('cartr_name')."%' ";
							if (JRequest::getVar('color_print')) $where .= " AND color_print LIKE '".JRequest::getVar('color_print')."%' ";
							if (JRequest::getVar('resource')) $where .= " AND resource LIKE '".JRequest::getVar('resource')."%' ";
						}
	
						$query = "SELECT COUNT(#__inv_prn_cartridge.id) AS count 
									FROM #__inv_prn_cartridge
									  LEFT JOIN #__inv_vendor
									    ON #__inv_prn_cartridge.id_vendor = #__inv_vendor.id
									  LEFT JOIN #__inv_prn_color
									    ON #__inv_prn_cartridge.id_color = #__inv_prn_color.id {$where}";
						$db->setQuery($query);
						$rows = $db->loadResult();
	
						$totalRows = $rows;
						$firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
	
						$query = "SELECT
						  #__inv_prn_cartridge.id,
						  #__inv_vendor.vendor,
						  #__inv_prn_cartridge.cartr_name,
						  #__inv_prn_color.color_print,
						  #__inv_prn_cartridge.resource
						FROM #__inv_prn_cartridge
						  LEFT JOIN #__inv_vendor
						    ON #__inv_prn_cartridge.id_vendor = #__inv_vendor.id
						  LEFT JOIN #__inv_prn_color
						    ON #__inv_prn_cartridge.id_color = #__inv_prn_color.id
						{$where} ORDER BY {$sortingField} {$sortingOrder} LIMIT {$firstRowIndex}, {$rowsPerPage}";
							
						$db->setQuery($query);//Выполняем запрос
						$rList = $db->loadObjectList();
						$response->page = $curPage;
						$response->total = ceil($totalRows / $rowsPerPage);
						$response->records = $totalRows;
							
						if (JRequest::getVar('single') == '1' && $rList[0]->id){
							$response = $rList[0];
						}else{
							$i=0;
							foreach($rList as $row){
								$response->rows[$i]['id']=$row->id;
								$response->rows[$i]['cell']=array($row->vendor, $row->cartr_name, $row->color_print, $row->resource);
								$i++;
							}
						}
						return json_encode($response);
					}
					catch (PDOException $e) {
						return 'Ошибка базы данных: '.$e->getMessage();
					}
					break;
				case add:
					$db->setQuery("INSERT INTO #__inv_prn_cartridge
							(id_vendor, cartr_name, id_color, resource)
							VALUES ({$id_vendor}, '{$cartr_name}', {$id_color}, {$resource})");
					$db->query();
					$this->log("Добавлен картридж производитель:{$vendor} название:{$cartr_name}");
					break;
				case del:
					$db->setQuery("DELETE FROM #__inv_prn_cartridge WHERE #__inv_prn_cartridge.id = {$id}");
					$db->query();
					$this->log("Удален картридж с id {$id}");
					break;
				case edit:
					$id = JRequest::getVar('id');
					$db->setQuery("UPDATE #__inv_prn_cartridge
									SET id_vendor= {$id_vendor},
									   	cartr_name= '{$cartr_name}',
										id_color= {$id_color},
										resource = {$resource}
									WHERE id = {$id}");
					$db->query();
					$this->log("Изменен картридж производитель:{$vendor} название:{$cartr_name} ");
					break;
			}
		}
	}

    function getWork() {//Работы на оборудовании

        $user = &JFactory::getUser();
        $oper = JRequest::getVar('oper');//Получаем тип операции
        $db = JFactory::getDbo();//Подключаемся к базе
        $id_obj = JRequest::getVar('id_obj');//Получаем id объекта
        $date_work = JRequest::getVar('date_work');
        $id_work_type = JRequest::getVar('id_work_type');
        $description_work = JRequest::getVar('description_work');
        $note_work = JRequest::getVar('note_work');
        $manager = JRequest::getVar('manager');
        $id = JRequest::getVar('id');

        switch ($oper){
            case sel:
                try {
                    $curPage = JRequest::getVar('page');
                    $rowsPerPage = JRequest::getVar('rows');
                    $sortingField = JRequest::getVar('sidx');
                    $sortingOrder =JRequest::getVar('sord');
                    $search = JRequest::getVar('_search');
                    $sup_type_name = JRequest::getVar('sup_type_name');
                    $where = '';

                    if ($id_obj) $where .= " WHERE #__inv_obj_work.id_obj = {$id_obj}";
                    if ($search == 'true'){
                        if ($date_work) $where .= " AND date_work LIKE '{$date_work}%' ";
                    }

                    $query = "SELECT COUNT(#__inv_obj_work.id) AS count
                               FROM #__inv_obj_work
                                  INNER JOIN #__inv_work_type
                                    ON #__inv_obj_work.id_work_type = #__inv_work_type.id {$where}";
                    $db->setQuery($query);
                    $rows = $db->loadResult();

                    $totalRows = $rows;
                    $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;

                    $query = "SELECT
                                  #__inv_obj_work.*,
                                  #__inv_work_type.work_type
                                FROM #__inv_obj_work
                                  INNER JOIN #__inv_work_type
                                    ON #__inv_obj_work.id_work_type = #__inv_work_type.id
                    {$where} ORDER BY {$sortingField} {$sortingOrder} LIMIT {$firstRowIndex}, {$rowsPerPage}";

                    $db->setQuery($query);//Выполняем запрос
                    $rList = $db->loadObjectList();
                    $response->page = $curPage;
                    $response->total = ceil($totalRows / $rowsPerPage);
                    $response->records = $totalRows;

                    $i=0;
                    foreach($rList as $row){
                        $response->rows[$i]['id']=$row->id;
                        $response->rows[$i]['cell']=array($row->date_work, $row->work_type, $row->manager, $row->description_work, $row->note_work);
                        $i++;
                    }
                    return json_encode($response);
                }
                catch (PDOException $e) {
                    return 'Ошибка базы данных: '.$e->getMessage();
                }
                break;
            case add:
                $db->setQuery("INSERT INTO #__inv_obj_work
                (id_obj, date_work, id_work_type, manager, description_work, note_work, id_user)
                VALUES ({$id_obj}, '{$date_work}', {$id_work_type}, '{$manager}', '{$description_work}', '{$note_work}', {$user->id})");
                $db->query();
                $this->log("Добавлена работа для объекта:{$id_obj}");
                break;
            case del:
                $db->setQuery("DELETE FROM #__inv_obj_work WHERE #__inv_obj_work.id = {$id}");
                $db->query();
                $this->log("Удалена работа с id {$id} у объекта {$id_obj}");
                break;
            case edit:
                $db->setQuery("UPDATE #__inv_obj_work
                                SET date_work= '{$date_work}',
                                    id_work_type= {$id_work_type},
                                    manager= '{$manager}',
                                    description_work = '{$description_work}',
                                    note_work = '{$note_work}'
                                WHERE id = {$id}");
                $db->query();
                $this->log("Изменена работа {$id} объекта {$id_obj}");
                break;
        }
    }
	
	function getSupType() {//Расходные материалы
			
		$rList = $this->getAccessUser();//Получаем права пользователей
		$oper = JRequest::getVar('oper');//Получаем тип операции
		$db = JFactory::getDbo();//Подключаемся к базе
		$sup_type_name = JRequest::getVar('sup_type_name');
		$func = JRequest::getVar('func');
		$id_units_of_measure = JRequest::getVar('id_units_of_measure');
		$field1_name = JRequest::getVar('field1_name');
		$field2_name = JRequest::getVar('field2_name');
		$field3_name = JRequest::getVar('field3_name');
		$field4_name = JRequest::getVar('field4_name');
		$field5_name = JRequest::getVar('field5_name');
		$id = JRequest::getVar('id');
	
		if ($rList[0]->admin || $rList[0]->storekeeper){
			switch (JRequest::getVar('oper')){
				case sel:
					try {
						$curPage = JRequest::getVar('page');
						$rowsPerPage = JRequest::getVar('rows');
						$sortingField = JRequest::getVar('sidx');
						$sortingOrder =JRequest::getVar('sord');
						$search = JRequest::getVar('_search');
						$sup_type_name = JRequest::getVar('sup_type_name');
						$where = '';
							
						if ($search == 'true'){
							if (JRequest::getVar('id')) $where .= " WHERE #__inv_sup_type.id = {$id}";
							else $where .= " WHERE #__inv_sup_type.id LIKE '%'";
							if (JRequest::getVar('sup_type_name')) $where .= " AND sup_type_name LIKE '{$sup_type_name}%' ";							
						}
	
						$query = "SELECT COUNT(#__inv_sup_type.id) AS count
								  FROM #__inv_sup_type
								  INNER JOIN #__inv_units_of_measure
								    ON #__inv_sup_type.id_units_of_measure = #__inv_units_of_measure.id {$where}";
						$db->setQuery($query);
						$rows = $db->loadResult();
	
						$totalRows = $rows;
						$firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
	
						$query = "SELECT
								  #__inv_sup_type.*,
								  #__inv_units_of_measure.units_of_measure
								FROM #__inv_sup_type
								  INNER JOIN #__inv_units_of_measure
								    ON #__inv_sup_type.id_units_of_measure = #__inv_units_of_measure.id
						{$where} ORDER BY {$sortingField} {$sortingOrder} LIMIT {$firstRowIndex}, {$rowsPerPage}";
							
						$db->setQuery($query);//Выполняем запрос
						$rList = $db->loadObjectList();
						$response->page = $curPage;
						$response->total = ceil($totalRows / $rowsPerPage);
						$response->records = $totalRows;

						if (JRequest::getVar('single') == '1' && $rList[0]->id){							
							$response = $rList[0];
						}else{
							$i=0;
							foreach($rList as $row){
							$response->rows[$i]['id']=$row->id;
							$response->rows[$i]['cell']=array($row->sup_type_name, $row->func, $row->units_of_measure, $row->field1_name, $row->field2_name, $row->field3_name, $row->field4_name, $row->field5_name);
							$i++;
							}
						}
						return json_encode($response);
						}
						catch (PDOException $e) {
						return 'Ошибка базы данных: '.$e->getMessage();
					}
					break;
				case add:
					$db->setQuery("INSERT INTO #__inv_sup_type
					(sup_type_name, func, id_units_of_measure, field1_name, field2_name, field3_name, field4_name, field5_name)
					VALUES ('{$sup_type_name}', '{$func}', {$id_units_of_measure}, '{$field1_name}', '{$field2_name}', '{$field3_name}', '{$field4_name}', '{$field5_name}')");
					$db->query();
					$this->log("Добавлен тип расходного материала:{$sup_type_name}");
					break;
				case del:
					$db->setQuery("DELETE FROM #__inv_sup_type WHERE #__inv_sup_type.id = {$id}");
					$db->query();
					$this->log("Удален тип расходного материала с id {$id}");
					break;
				case edit:
					$id = JRequest::getVar('id');
					$db->setQuery("UPDATE #__inv_sup_type
									SET sup_type_name= '{$sup_type_name}',
										func= '{$func}',
										id_units_of_measure= {$id_units_of_measure},
										field1_name = '{$field1_name}',
										field2_name = '{$field2_name}',
										field3_name = '{$field3_name}',
										field4_name = '{$field4_name}',
										field5_name = '{$field5_name}'
									WHERE id = {$id}");
					$db->query();
					$this->log("Изменен тип расходного материала:{$sup_type_name}");
					break;
			}
		}
	}
	
	function getCarteridgeForPrn() {//Соответсвие картриджей и шаблонов принтеров
			
		$oper = JRequest::getVar('oper');//Получаем тип операции
		$db = JFactory::getDbo();//Подключаемся к базе
		$id_prn = JRequest::getVar('id_prn');
		$id_cartridge = JRequest::getVar('id_cartridge');
		$id = JRequest::getVar('id');
	
		switch ($oper){
			case selprn:					
				$query = "SELECT
						  #__inv_prn_printers_cartridges.id_prn,
						  #__inv_vendor.vendor,
						  #__inv_prn_template.chasis,
						  #__inv_prn_template.model
						FROM #__inv_prn_printers_cartridges
						  INNER JOIN #__inv_prn_template
						    ON #__inv_prn_printers_cartridges.id_prn = #__inv_prn_template.id
						  INNER JOIN #__inv_vendor
						    ON #__inv_prn_template.id_vendor = #__inv_vendor.id
						WHERE #__inv_prn_printers_cartridges.id_cartridge = {$id_cartridge}
						ORDER BY #__inv_vendor.vendor";
	
				$db->setQuery($query);//Выполняем запрос
				$rList = $db->loadObjectList();
					
				$i=0;
				foreach($rList as $row){
					$response->rows[$i]['id']=$row->id_prn;
					$response->rows[$i]['cell']=array($row->vendor, $row->chasis, $row->model);
					$i++;
				}	
				return json_encode($response);
				break;
			case add:
				$db->setQuery("INSERT INTO #__inv_prn_printers_cartridges
						(id_prn, id_cartridge)
						VALUES ({$id_prn}, {$id_cartridge})");
				$db->query();
				$this->log("Добавлено новое соответсвие картриджей и принтеров :{$id_prn} картридж:{$id_cartridge}");
				break;
			case del:
				$db->setQuery("DELETE FROM #__inv_prn_printers_cartridges WHERE #__inv_prn_printers_cartridges.id_prn = {$id} AND #__inv_prn_printers_cartridges.id_cartridge = {$id_cartridge}");
				$db->query();
				$this->log("Удалено соответсвие принтера id {$id_prn} и картриджа id {$id}");
				break;
		}
	
	}
	
	function getTemplate() {//Шаблоны принтеров
			
		$rList = $this->getAccessUser();//Получаем права пользователей 2
		$oper = JRequest::getVar('oper');//Получаем тип операции
		$db = JFactory::getDbo();//Подключаемся к базе
		$id_vendor = JRequest::getVar('id_vendor');
		$vendor = JRequest::getVar('id_vendor');
		$cartr_name = JRequest::getVar('cartr_name');
		$id = JRequest::getVar('id');
		$id_vendor = JRequest::getVar('id_vendor');
		$chasis = JRequest::getVar('chasis');
		$model = JRequest::getVar('model');
		$id_type = JRequest::getVar('id_type');
		$id_type_print = JRequest::getVar('id_type_print');
		$id_type_scan = JRequest::getVar('id_type_print');
		$color = JRequest::getVar('color');
		$id_format = JRequest::getVar('id_format');
		$photo = JRequest::getVar('photo');
		$duplex_printing = JRequest::getVar('duplex_printing');
		$ethernet = JRequest::getVar('ethernet');
 		$fax = JRequest::getVar('fax');
 		$wifi = JRequest::getVar('wifi');
 		$speed_print = JRequest::getVar('speed_print');
 		$speed_scan = JRequest::getVar('speed_scan');
 		$mem = JRequest::getVar('mem');
 		$m_resource = JRequest::getVar('m_resource');
 		$prn_os = JRequest::getVar('prn_os');
 		$prn_description = JRequest::getVar('prn_description');
	
		if ($rList[0]->admin || $rList[0]->storekeeper){
            switch (JRequest::getVar('eqtype')){
                case prn:
                    switch ($oper){
                        case sel:
                            try {
                                $curPage = JRequest::getVar('page');
                                $rowsPerPage = JRequest::getVar('rows');
                                $sortingField = JRequest::getVar('sidx');
                                $sortingOrder =JRequest::getVar('sord');
                                $search = JRequest::getVar('_search');
                                $where = '';

                                if ($search == 'true'){
                                    $where = "WHERE #__inv_prn_template.id LIKE '%'";
                                    if ($id) $where = "WHERE #__inv_prn_template.id = {$id}";
                                    if ($vendor) $where .= " AND vendor LIKE '%{$vendor}%' ";
                                    if ($chasis) $where .= " AND chasis LIKE '%{$chasis}%' ";
                                    if ($model) $where .= " AND model LIKE '%{$model}%' ";
                                }

                                $query = "SELECT COUNT(#__inv_prn_template.id) AS count
								  FROM #__inv_prn_template
								  INNER JOIN #__inv_vendor
								    ON #__inv_prn_template.id_vendor = #__inv_vendor.id
								  INNER JOIN #__inv_prn_type
								    ON #__inv_prn_template.id_type = #__inv_prn_type.id
								  INNER JOIN #__inv_prn_type_print
								    ON #__inv_prn_template.id_type_print = #__inv_prn_type_print.id
							      INNER JOIN #__inv_prn_type_scan
							 	    ON #__inv_prn_template.id_type_scan = #__inv_prn_type_scan.id
								  INNER JOIN #__inv_prn_format
								    ON #__inv_prn_template.id_format = #__inv_prn_format.id {$where}";
                                $db->setQuery($query);
                                $rows = $db->loadResult();

                                $totalRows = $rows;
                                $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;

                                $query = "SELECT
								  #__inv_prn_template.*,
								  #__inv_vendor.vendor,
								  #__inv_prn_type.print_type,
								  #__inv_prn_type_print.print_type_print,
								  #__inv_prn_type_scan.type_scan,
								  #__inv_prn_format.format_print
								FROM #__inv_prn_template
								  INNER JOIN #__inv_vendor
								    ON #__inv_prn_template.id_vendor = #__inv_vendor.id
								  INNER JOIN #__inv_prn_type
								    ON #__inv_prn_template.id_type = #__inv_prn_type.id
								  INNER JOIN #__inv_prn_type_print
								    ON #__inv_prn_template.id_type_print = #__inv_prn_type_print.id
								  INNER JOIN #__inv_prn_type_scan
								    ON #__inv_prn_template.id_type_scan = #__inv_prn_type_scan.id
								  INNER JOIN #__inv_prn_format
								    ON #__inv_prn_template.id_format = #__inv_prn_format.id
						{$where} ORDER BY {$sortingField} {$sortingOrder} LIMIT {$firstRowIndex}, {$rowsPerPage}";

                                $db->setQuery($query);//Выполняем запрос
                                $rList = $db->loadObjectList();
                                $response->page = $curPage;
                                $response->total = ceil($totalRows / $rowsPerPage);
                                $response->records = $totalRows;

                                if (JRequest::getVar('single') == '1' && $rList[0]->id){
                                    $response = $rList[0];
                                }else{
                                    $i=0;
                                    foreach($rList as $row){
                                        $response->rows[$i]['id']=$row->id;
                                        $response->rows[$i]['cell']=array($row->vendor, $row->chasis, $row->model, $row->print_type, $row->print_type_print, $row->type_scan, $row->color, $row->format_print, $row->photo,
                                            $row->duplex_printing, $row->ethernet, $row->wifi, $row->fax, $row->speed_print, $row->speed_scan, $row->mem, $row->m_resource, $row->prn_os, $row->prn_description);
                                        $i++;
                                    }
                                }
                                return json_encode($response);
                            }
                            catch (PDOException $e) {
                                return 'Ошибка базы данных: '.$e->getMessage();
                            }
                            break;
                        case add:
                            $db->setQuery("INSERT INTO #__inv_prn_template
			                                (id_vendor, chasis, model, id_type, id_type_print, id_type_scan, color, id_format, photo, duplex_printing, ethernet, fax, wifi, speed_print, speed_scan, mem, m_resource, prn_os, prn_description)
			                                VALUES ({$id_vendor}, '{$chasis}', '{$model}', {$id_type}, {$id_type_print}, {$id_type_scan}, {$color}, {$id_format}, {$photo}, {$duplex_printing}, {$ethernet},
			 		                                {$fax}, {$wifi}, '{$speed_print}', '{$speed_scan}', '{$mem}', {$m_resource}, '{$prn_os}', '{$prn_description}')");
                            $db->query();
                            $this->log("Добавлен шаблон принтера шасси:{$chasis} модель:{$model}");
                            break;
                        case del:
                            $db->setQuery("DELETE FROM #__inv_prn_template WHERE #__inv_prn_template.id = {$id}");
                            $db->query();
                            $this->log("Удален шаблон принтера с id {$id}");
                            break;
                        case edit:
                            $id = JRequest::getVar('id');
                            $db->setQuery("UPDATE #__inv_prn_template
								SET id_vendor= {$id_vendor},
								    chasis = {$chasis},
								    model = {$model},
								    id_type = {$id_type},
								    id_type_print = {$id_type_print},
								    id_type_scan = {$id_type_scan},
								    color = {$color},
								    id_format = {$id_format},
								    photo = {$photo},
								    duplex_printing = {$duplex_printing},
								    ethernet = {$ethernet},
								    fax = {$fax},
								    wifi = {$wifi},
								    speed_print = {$speed_print},
								    speed_scan = {$speed_scan},
								    mem = {$mem},
								    m_resource = {$m_resource},
								    prn_os = {$prn_os},
								    prn_description = {$prn_description}
								WHERE id = {$id}");
                            $db->query();
                            $this->log("Изменен картридж производитель:{$vendor} название:{$cartr_name} ");
                            break;
                    }
                    break;
                case comp:
                    $osname = JRequest::getVar('osname');
                    $config = JRequest::getVar('config');
                    $processor = JRequest::getVar('processor');
                    $mem = JRequest::getVar('mem');
                    $motherboard = JRequest::getVar('motherboard');
                    $hdd = JRequest::getVar('hdd');
                    $psu = JRequest::getVar('psu');
                    $graphics = JRequest::getVar('graphics');
                    $description = JRequest::getVar('description');
                    switch ($oper){
                        case sel:
                            try {
                                $curPage = JRequest::getVar('page');
                                $rowsPerPage = JRequest::getVar('rows');
                                $sortingField = JRequest::getVar('sidx');
                                $sortingOrder =JRequest::getVar('sord');
                                $search = JRequest::getVar('_search');



                                $where = '';

                                if ($search == 'true'){
                                    $where = "WHERE #__inv_comp_template.id LIKE '%'";
                                    if ($id) $where = "WHERE #__inv_comp_template.id = {$id}";
                                    if ($vendor) $where .= " AND vendor LIKE '%{$vendor}%' ";
                                    if ($chasis) $where .= " AND chasis LIKE '%{$chasis}%' ";
                                    if ($model) $where .= " AND model LIKE '%{$model}%' ";
                                    if ($osname) $where .= " AND osname LIKE '%{$osname}%' ";
                                    if ($config) $where .= " AND config LIKE '%{$config}%' ";
                                    if ($processor) $where .= " AND processor LIKE '%{$processor}%' ";
                                    if ($mem) $where .= " AND mem LIKE '%{$mem}%' ";
                                    if ($motherboard) $where .= " AND motherboard LIKE '%{$motherboard}%' ";
                                    if ($hdd) $where .= " AND hdd LIKE '%{$hdd}%' ";
                                    if ($psu) $where .= " AND psu LIKE '%{$psu}%' ";
                                    if ($graphics) $where .= " AND graphics LIKE '%{$graphics}%' ";
                                    if ($description) $where .= " AND description LIKE '%{$description}%' ";
                                }

                                $query = "SELECT COUNT(#__inv_comp_template.id) AS count
                                          FROM #__inv_comp_template
                                          INNER JOIN #__inv_vendor
                                             ON #__inv_comp_template.id_vendor = #__inv_vendor.id {$where}";
                                $db->setQuery($query);
                                $rows = $db->loadResult();

                                $totalRows = $rows;
                                $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;

                                $query = "SELECT
                                              #__inv_vendor.vendor,
                                              #__inv_comp_template.*
                                            FROM #__inv_comp_template
                                              INNER JOIN #__inv_vendor
                                                ON #__inv_comp_template.id_vendor = #__inv_vendor.id
						                    {$where} ORDER BY {$sortingField} {$sortingOrder} LIMIT {$firstRowIndex}, {$rowsPerPage}";

                                $db->setQuery($query);//Выполняем запрос
                                $rList = $db->loadObjectList();
                                $response->page = $curPage;
                                $response->total = ceil($totalRows / $rowsPerPage);
                                $response->records = $totalRows;

                                if (JRequest::getVar('single') == '1' && $rList[0]->id){
                                    $response = $rList[0];
                                }else{
                                    $i=0;
                                    foreach($rList as $row){
                                        $response->rows[$i]['id']=$row->id;
                                        $response->rows[$i]['cell']=array($row->vendor, $row->chasis, $row->model, $row->osname, $row->config, $row->processor, $row->mem, $row->motherboard,
                                                                         $row->hdd, $row->psu, $row->graphics, $row->description);
                                        $i++;
                                    }
                                }
                                return json_encode($response);
                            }
                            catch (PDOException $e) {
                                return 'Ошибка базы данных: '.$e->getMessage();
                            }
                            break;
                        case add:
                            $db->setQuery("INSERT INTO #__inv_comp_template
			                                (id_vendor, chasis, model, osname, config, processor, mem, motherboard, hdd, psu, graphics, description)
			                                VALUES ({$id_vendor}, '{$chasis}', '{$model}', '{$osname}', '{$config}', '{$processor}', '{$mem}', '{$motherboard}', '{$hdd}', '{$psu}', '{$graphics}', '{$description}')");
                            $db->query();
                            $this->log("Добавлен шаблон принтера компьютера:{$chasis} модель:{$model}");
                            break;
                        case del:
                            $db->setQuery("DELETE FROM #__inv_comp_template WHERE #__inv_comp_template.id = {$id}");
                            $db->query();
                            $this->log("Удален шаблон компьютера с id {$id}");
                            break;
                        case edit:
                            $id = JRequest::getVar('id');
                            $db->setQuery("UPDATE #__inv_comp_template
								SET id_vendor= {$id_vendor},
								    chasis = '{$chasis}',
								    model = '{$model}',
								    osname = '{$osname}',
								    config = '{$config}',
								    processor = '{$processor}',
								    mem = '{$mem}',
								    motherboard = '{$motherboard}',
								    hdd = '{$hdd}',
								    psu = '{$psu}',
								    graphics = '{$graphics}',
								    description = '{$description}'
								WHERE id = {$id}");
                            $db->query();
                            $this->log("Изменен шаблон компьютера:{$vendor} название:{$chasis} ");
                            break;
                    }
                    break;
                case net:
                    $id_active_type = JRequest::getVar('id_active_type');
                    $num_ethernet = JRequest::getVar('num_ethernet');
                    $num_uplink = JRequest::getVar('num_uplink');
                    $num_sfp = JRequest::getVar('num_sfp');
                    $num_poe = JRequest::getVar('num_poe');
                    $num_fxs= JRequest::getVar('num_fxs');
                    $num_usb = JRequest::getVar('num_usb');
                    $id_base_speed = JRequest::getVar('id_base_speed');
                    $id_max_uplink = JRequest::getVar('id_max_uplink');
                    $slots = JRequest::getVar('slots');
                    $rack = JRequest::getVar('rack');
                    $num_unit = JRequest::getVar('num_unit');
                    $forwarding_rate = JRequest::getVar('forwarding_rate');
                    $mac_table = JRequest::getVar('mac_table');
                    $mem = JRequest::getVar('mem');
                    $flash = JRequest::getVar('flash');
                    $stack = JRequest::getVar('stack');
                    $console = JRequest::getVar('console');
                    $web = JRequest::getVar('web');
                    $telnet_ssh = JRequest::getVar('telnet_ssh');
                    $snmp = JRequest::getVar('snmp');
                    $management = JRequest::getVar('management');
                    $outdoors = JRequest::getVar('outdoors');
                    $poe = JRequest::getVar('poe');
                    $poe_standart = JRequest::getVar('poe_standart');
                    $voice = JRequest::getVar('voice');
                    $voice_standart = JRequest::getVar('voice_standart');
                    $wifi = JRequest::getVar('wifi');
                    $wifi_standart = JRequest::getVar('wifi_standart');
                    $power_standart = JRequest::getVar('power_standart');
                    $backup_power = JRequest::getVar('backup_power');
                    $os = JRequest::getVar('os');
                    $act_description = JRequest::getVar('act_description');
                    switch ($oper){
                        case sel:
                            try {
                                $curPage = JRequest::getVar('page');
                                $rowsPerPage = JRequest::getVar('rows');
                                $sortingField = JRequest::getVar('sidx');
                                $sortingOrder =JRequest::getVar('sord');
                                $search = JRequest::getVar('_search');

                                $osname = JRequest::getVar('osname');
                                $config = JRequest::getVar('config');
                                $processor = JRequest::getVar('processor');
                                $mem = JRequest::getVar('mem');
                                $motherboard = JRequest::getVar('motherboard');
                                $hdd = JRequest::getVar('hdd');
                                $psu = JRequest::getVar('psu');
                                $graphics = JRequest::getVar('graphics');
                                $description = JRequest::getVar('description');

                                $where = '';

                                if ($search == 'true'){
                                    $where = "WHERE #__inv_net_template.id LIKE '%'";
                                    if ($id) $where = "WHERE #__inv_net_template.id = {$id}";
                                    if ($vendor) $where .= " AND vendor LIKE '%{$vendor}%' ";
                                    if ($chasis) $where .= " AND chasis LIKE '%{$chasis}%' ";
                                    if ($model) $where .= " AND model LIKE '%{$model}%' ";
                                    if ($osname) $where .= " AND osname LIKE '%{$osname}%' ";
                                    if ($config) $where .= " AND config LIKE '%{$config}%' ";
                                    if ($processor) $where .= " AND processor LIKE '%{$processor}%' ";
                                    if ($mem) $where .= " AND mem LIKE '%{$mem}%' ";
                                    if ($motherboard) $where .= " AND motherboard LIKE '%{$motherboard}%' ";
                                    if ($hdd) $where .= " AND hdd LIKE '%{$hdd}%' ";
                                    if ($psu) $where .= " AND psu LIKE '%{$psu}%' ";
                                    if ($graphics) $where .= " AND graphics LIKE '%{$graphics}%' ";
                                    if ($description) $where .= " AND description LIKE '%{$description}%' ";
                                }

                                $query = "SELECT COUNT(#__inv_net_template.id) AS count
                                           FROM #__inv_net_template
                                          INNER JOIN #__inv_vendor
                                            ON #__inv_net_template.id_vendor = #__inv_vendor.id
                                          INNER JOIN #__inv_net_type
                                            ON #__inv_net_template.id_active_type = #__inv_net_type.id
                                          INNER JOIN #__inv_net_speed
                                            ON #__inv_net_template.id_base_speed = #__inv_net_speed.id
                                          LEFT OUTER JOIN #__inv_net_speed #__inv_net_speed_1
                                            ON #__inv_net_template.id_base_speed = #__inv_net_speed_1.id {$where}";
                                $db->setQuery($query);
                                $rows = $db->loadResult();

                                $totalRows = $rows;
                                $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;

                                $query = "SELECT
                                          #__inv_net_template.*,
                                          #__inv_vendor.vendor,
                                          #__inv_net_type.active_net_type,
                                          #__inv_net_speed.base_speed,
                                          #__inv_net_speed_1.base_speed AS max_uplink
                                        FROM #__inv_net_template
                                          INNER JOIN #__inv_vendor
                                            ON #__inv_net_template.id_vendor = #__inv_vendor.id
                                          INNER JOIN #__inv_net_type
                                            ON #__inv_net_template.id_active_type = #__inv_net_type.id
                                          INNER JOIN #__inv_net_speed
                                            ON #__inv_net_template.id_base_speed = #__inv_net_speed.id
                                          LEFT OUTER JOIN #__inv_net_speed #__inv_net_speed_1
                                            ON #__inv_net_template.id_max_uplink = #__inv_net_speed_1.id
						                    {$where} ORDER BY {$sortingField} {$sortingOrder} LIMIT {$firstRowIndex}, {$rowsPerPage}";

                                $db->setQuery($query);//Выполняем запрос
                                $rList = $db->loadObjectList();
                                $response->page = $curPage;
                                $response->total = ceil($totalRows / $rowsPerPage);
                                $response->records = $totalRows;

                                if (JRequest::getVar('single') == '1' && $rList[0]->id){
                                    $response = $rList[0];
                                }else{
                                    $i=0;
                                    foreach($rList as $row){
                                        $response->rows[$i]['id']=$row->id;
                                        $response->rows[$i]['cell']=array($row->active_net_type, $row->vendor, $row->chasis, $row->model, $row->num_ethernet, $row->num_uplink, $row->num_sfp,
                                                        $row->num_poe, $row->num_fxs, $row->num_usb, $row->base_speed, $row->max_uplink,
                                                        $row->slots, $row->rack, $row->num_unit, $row->forwarding_rate, $row->mac_table, $row->mem, $row->flash, $row->stack,
                                                        $row->console, $row->web, $row->telnet_ssh, $row->snmp, $row->management, $row->outdoors, $row->poe, $row->poe_standart,
                                                        $row->voice, $row->voice_standart, $row->wifi, $row->wifi_standart, $row->power_standart, $row->backup_power, $row->os, $row->act_description);
                                        $i++;
                                    }
                                }
                                return json_encode($response);
                            }
                            catch (PDOException $e) {
                                return 'Ошибка базы данных: '.$e->getMessage();
                            }
                            break;
                        case add:
                            $db->setQuery("INSERT INTO #__inv_net_template
			                                (id_active_type, id_vendor, chasis, model,  num_ethernet,  num_uplink, num_sfp, num_poe, num_fxs, num_usb,id_base_speed,id_max_uplink,
                                            slots, rack, num_unit, forwarding_rate, mac_table, mem, flash, stack,
                                            console, web, telnet_ssh, snmp, management, outdoors,  poe, poe_standart, voice, voice_standart,
                                            wifi, wifi_standart, power_standart, backup_power, os, act_description)
			                                VALUES ({$id_active_type}, {$id_vendor}, '{$chasis}', '{$model}',  {$num_ethernet},  {$num_uplink}, {$num_sfp}, {$num_poe}, {$num_fxs}, {$num_usb}, {$id_base_speed}, {$id_max_uplink},
			                                        '{$slots}', {$rack}, '{$num_unit}','{$forwarding_rate}', '{$mac_table}', '{$mem}', '{$flash}', {$stack},
			                                        {$console},{$web},{$telnet_ssh}, {$snmp},  {$management}, {$outdoors}, {$poe}, '{$poe_standart}', {$voice}, '{$voice_standart}',
                                                    {$wifi}, '{$wifi_standart}', '{$power_standart}', {$backup_power}, '{$os}', '{$act_description}')");
                            $db->query();
                            $this->log("Добавлен шаблон сетевого устройства:{$chasis} модель:{$model}");
                            break;
                        case del:
                            $db->setQuery("DELETE FROM #__inv_net_template WHERE #__inv_net_template.id = {$id}");
                            $db->query();
                            $this->log("Удален шаблон сетевого устройства с id {$id}");
                            break;
                        case edit:
                            $id = JRequest::getVar('id');
                            $db->setQuery("UPDATE #__inv_net_template
								SET id_active_type = {$id_active_type},
								    id_vendor= {$id_vendor},
								    chasis = '{$chasis}',
								    model = '{$model}',
								    num_ethernet = {$num_ethernet},
								    num_uplink = {$num_uplink},
								    num_sfp = {$num_sfp},
								    num_poe = {$num_poe},
								    num_fxs = {$num_fxs},
								    num_usb = {$num_usb},
								    id_base_speed = {$id_base_speed},
								    id_max_uplink = {$id_max_uplink},
								    num_uplink = {$num_uplink},
								    slots = '{$slots}',
								    rack = {$rack},
								    num_unit = {$num_unit},
								    forwarding_rate = '{$forwarding_rate}',
								    mac_table = '{$mac_table}',
								    mem = '{$mem}',
								    flash = '{$flash}',
								    stack = {$stack},
								    console = {$console},
								    web = {$web},
								    telnet_ssh = {$telnet_ssh},
								    snmp = {$snmp},
								    management = {$management},
								    outdoors = {$outdoors},
								    poe = {$poe},
								    poe_standart = '{$poe_standart}',
								    voice = {$voice},
								    voice_standart = '{$voice_standart}',
								    wifi = {$wifi},
								    wifi_standart = '{$wifi_standart}',
                                    power_standart = '{$power_standart}',
                                    backup_power = {$backup_power},
                                    os = '{$os}',
								    act_description = '{$act_description}'
								WHERE id = {$id}");
                            $db->query();
                            $this->log("Изменен шаблон сетевого устройства:{$vendor} название:{$chasis} ");
                            break;
                    }
                    break;
            }

		}
	}
	
	function getVendor() {//Редактирование производителей
			
		$rList = $this->getAccessUser();//Получаем права пользователей
	
		$oper = JRequest::getVar('oper');//Получаем тип операции
	
		$db = JFactory::getDbo();//Подключаемся к базе
	
		if ($rList[0]->admin || $rList[0]->storekeeper){
			switch (JRequest::getVar('oper')){
				case sel:
					try {
						$curPage = JRequest::getVar('page');
						$rowsPerPage = JRequest::getVar('rows');
						$sortingField = JRequest::getVar('sidx');
						$sortingOrder =JRequest::getVar('sord');
						$search = JRequest::getVar('_search');
						$where = '';
															
						if ($search == 'true'){
							$where = 'WHERE';
							if (JRequest::getVar('vendor')) $where .= " AND #__inv_vendor.vendor LIKE '".JRequest::getVar('vendor')."%' ";
						}
	
						$query = "SELECT COUNT(#__inv_vendor.id) AS count FROM #__inv_vendor
									  LEFT OUTER JOIN #__inv_countries
									    ON #__inv_vendor.country = #__inv_countries.id
								   {$where}";
						$db->setQuery($query);
						$rows = $db->loadResult();
						
						$totalRows = $rows;
						$firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
	
						$query = "SELECT
								  #__inv_vendor.*,
								  #__inv_countries.name AS country_name
								FROM #__inv_vendor
								  INNER JOIN #__inv_countries
								    ON #__inv_vendor.country = #__inv_countries.id
								 {$where} ORDER BY {$sortingField} {$sortingOrder} LIMIT {$firstRowIndex}, {$rowsPerPage}";
							
						$db->setQuery($query);//Выполняем запрос
						$rList = $db->loadObjectList();
						$response->page = $curPage;
						$response->total = ceil($totalRows / $rowsPerPage);
						$response->records = $totalRows;
							
						$i=0;
						foreach($rList as $row){
							$response->rows[$i]['id']=$row->id;
							$response->rows[$i]['cell']=array($row->vendor, $row->vendor_eng, $row->country_name, $row->website, $row->country);
							$i++;
						}
					return json_encode($response);
					}
					catch (PDOException $e) {
						return 'Ошибка базы данных: '.$e->getMessage();
					}
					break;
				case add:					
					$db->setQuery("INSERT INTO #__inv_vendor
							(vendor, vendor_eng, country, website)
							VALUES ('".JRequest::getVar('vendor')."','".JRequest::getVar('vendor_eng')."','".JRequest::getVar('id_country')."','".JRequest::getVar('website')."')");
					$db->query();
					$this->log("Добавлен производитель ".JRequest::getVar('vendor'));
					break;
				case del:
					$this->log("Удален производитель с id ".JRequest::getVar('id'));
					$id = JRequest::getVar('id');
					$db->setQuery("DELETE FROM #__inv_vendor WHERE #__inv_vendor.id = {$id}");
					$db->query();
					break;
				case edit:
					$this->log("Изменено производитель ".JRequest::getVar('vendor'));
					$id = JRequest::getVar('id');
					$db->setQuery("UPDATE #__inv_vendor
						SET vendor='".JRequest::getVar('vendor')."',
						    vendor_eng='".JRequest::getVar('vendor_eng')."',
						   	country='".JRequest::getVar('country')."',
							website='".JRequest::getVar('website')."'
						 	WHERE id = {$id}");
					$db->query();
					break;
			}
		}
	}
	
	function getSupplier() {//Редактирование поставщиков
			
		$rList = $this->getAccessUser();//Получаем права пользователей
	
		$oper = JRequest::getVar('oper');//Получаем тип операции
	
		$db = JFactory::getDbo();//Подключаемся к базе
	
		if ($rList[0]->admin || $rList[0]->storekeeper){
			switch ($oper){
				case sel:
					try {
						$curPage = JRequest::getVar('page');
						$rowsPerPage = JRequest::getVar('rows');
						$sortingField = JRequest::getVar('sidx');
						$sortingOrder =JRequest::getVar('sord');
						$search = JRequest::getVar('_search');
						$where = '';
							
						if ($search == 'true'){
							$where = 'WHERE';
							if (JRequest::getVar('supplier')) $where .= " AND #__inv_supplier.supplier LIKE '".JRequest::getVar('supplier')."%' ";
						}
	
						$query = "SELECT COUNT(#__inv_supplier.id) AS count FROM #__inv_supplier
								  LEFT OUTER JOIN #__inv_cities
								    ON #__inv_supplier.id_city = #__inv_cities.id
						{$where}";
						$db->setQuery($query);
						$rows = $db->loadResult();
	
						$totalRows = $rows;
						$firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
	
						$query = "SELECT
								  #__inv_supplier.id,
								  #__inv_supplier.supplier,
								  #__inv_supplier.id_city,
								  #__inv_supplier.website,
								  #__inv_cities.city
								FROM #__inv_supplier
								  LEFT OUTER JOIN #__inv_cities
								    ON #__inv_supplier.id_city = #__inv_cities.id
						{$where} ORDER BY {$sortingField} {$sortingOrder} LIMIT {$firstRowIndex}, {$rowsPerPage}";
							
						$db->setQuery($query);//Выполняем запрос
						$rList = $db->loadObjectList();
						$response->page = $curPage;
						$response->total = ceil($totalRows / $rowsPerPage);
						$response->records = $totalRows;
							
						$i=0;
						foreach($rList as $row){
						$response->rows[$i]['id']=$row->id;
						$response->rows[$i]['cell']=array($row->supplier, $row->city, $row->website, $row->id_city);
						$i++;
						}
						return json_encode($response);
						}
						catch (PDOException $e) {
						return 'Ошибка базы данных: '.$e->getMessage();
				}
				break;
			case add:
				$this->log("Добавлен поставщик ".JRequest::getVar('supplier'));
				$db->setQuery("INSERT INTO #__inv_supplier
							(supplier, id_city, website)
							VALUES ('".JRequest::getVar('supplier')."','".JRequest::getVar('id_city2')."','".JRequest::getVar('website')."')");
				$db->query();
				break;
			case del:
				$this->log("Удален Поставщик с id ".JRequest::getVar('id'));
				$id = JRequest::getVar('id');
				$db->setQuery("DELETE FROM #__inv_supplier WHERE #__inv_supplier.id = {$id}");
				$db->query();
				break;
			case edit:
				$this->log("Изменено производитель ".JRequest::getVar('vendor'));
				$id = JRequest::getVar('id');
				$db->setQuery("UPDATE #__inv_supplier
								SET supplier='".JRequest::getVar('supplier')."',
						 		id_city='".JRequest::getVar('id_city2')."',
								website='".JRequest::getVar('website')."'
								WHERE id = {$id}");
				$db->query();
				break;
			}
		}
	}
	
	function getObjParts() {//Редактирование производителей
			
		$oper = JRequest::getVar('oper');//Получаем тип операции	
		$db = JFactory::getDbo();//Подключаемся к базе
	
		switch (JRequest::getVar('oper')){
			case sel:
				$curPage = JRequest::getVar('page');
				$rowsPerPage = JRequest::getVar('rows');
				$sortingField = JRequest::getVar('sidx');
				$sortingOrder =JRequest::getVar('sord');
				$search = JRequest::getVar('_search');
				$id_obj = JRequest::getVar('id_obj');

				$query = "SELECT
                          #__inv_obj_parts.*,
                          #__inv_object.name,
                          #__inv_obj_buh.purchase_date,
                          #__inv_object.s_number,
                          #__inv_obj_buh.i_number,
                          #__inv_obj_buh.i_number_adv,
                          #__inv_object.barcode,
                          #__inv_vendor.vendor,
                          #__inv_supplier.supplier
                        FROM #__inv_obj_parts
                          INNER JOIN #__inv_object
                            ON #__inv_obj_parts.id_obj_part = #__inv_object.id
                          LEFT OUTER JOIN #__inv_obj_buh
                            ON #__inv_obj_buh.id_obj = #__inv_object.id
                          LEFT OUTER JOIN #__inv_vendor
                            ON #__inv_object.id_vendor = #__inv_vendor.id
                          LEFT OUTER JOIN #__inv_supplier
                            ON #__inv_obj_buh.id_supplier = #__inv_supplier.id
						WHERE #__inv_obj_parts.id_obj = {$id_obj}";

				$db->setQuery($query);//Выполняем запрос
				$rList = $db->loadObjectList();
									
				$i=0;		
				foreach($rList as $row){
					$response->rows[$i]['id']=$row->id;
					$response->rows[$i]['cell']=array($row->id_obj_part, $row->name, $row->purchase_date, $row->s_number,
							$row->i_number, $row->i_number_adv, $row->barcode, $row->vendor, $row->supler, $row->date,
							 $row->remove, $row->date_remove);
					$i++;
				}
				return json_encode($response);
				break;
            case del:

                $id = JRequest::getVar('id');
                $db->setQuery("DELETE FROM #__inv_obj_parts WHERE #__inv_obj_parts.id = {$id}");
                $db->query();
                $this->log("Удален подобъект из объекта с id ");
                break;
		}
	
	}

    function getUserParam() {//Редактирование параметров пользователя

        $oper = JRequest::getVar('oper');//Получаем тип операции
        $db = JFactory::getDbo();//Подключаемся к базе
        $user = &JFactory::getUser();
        $name = JRequest::getVar('name');

        switch ($oper){
            case sel:

                $where = "WHERE #__inv_user_param.id_user = {$user->id}";
                if ($name)  $where .= " AND __inv_user_param.name = {$name}";
                $query = "SELECT
                          #__inv_user_param.*
						{$where}";
                $db->setQuery($query);//Выполняем запрос
                $rList = $db->loadObjectList();

                return json_encode($rList);
                break;
            case edit:
                breack;
        }

    }
	
	function getObjDocs() {//Редактирование производителей
			
		$oper = JRequest::getVar('oper');//Получаем тип операции	
		$db = JFactory::getDbo();//Подключаемся к базе
	
		switch (JRequest::getVar('oper')){
			case sel:
				$curPage = JRequest::getVar('page');
				$rowsPerPage = JRequest::getVar('rows');
				$sortingField = JRequest::getVar('sidx');
				$sortingOrder =JRequest::getVar('sord');
				$search = JRequest::getVar('_search');
				$id_obj = JRequest::getVar('id_obj');

				$query = "SELECT
						  #__inv_docs.*,
						  #__inv_doc_types.doc_type,
						  #__inv_supplier.supplier,
						  #__neq_region.Name AS reg,
						  #__neq_pes.name AS pes,
						  #__inv_users.cn AS name_to,
						  #__inv_users_1.cn AS name_from
						FROM #__inv_obj_docs
						  INNER JOIN #__inv_docs
						    ON #__inv_obj_docs.id_doc = #__inv_docs.id
						  INNER JOIN #__inv_doc_types
						    ON #__inv_docs.id_doc_type = #__inv_doc_types.id
						  LEFT OUTER JOIN #__neq_region
						    ON #__inv_docs.id_reg = #__neq_region.id
						  LEFT OUTER JOIN #__neq_pes
						    ON #__inv_docs.id_pes = #__neq_pes.id
						  LEFT OUTER JOIN #__inv_supplier
						    ON #__inv_docs.id_supplier = #__inv_supplier.id
						  LEFT OUTER JOIN #__inv_users
						    ON #__inv_docs.id_to = #__inv_users.id
						  LEFT OUTER JOIN #__inv_users #__inv_users_1
						    ON #__inv_docs.id_to = #__inv_users_1.id
						WHERE #__inv_obj_docs.id_obj = {$id_obj}";

				$db->setQuery($query);//Выполняем запрос
				$rList = $db->loadObjectList();

				$i=0;
				foreach($rList as $row){
					$response->rows[$i]['id']=$row->id;
					$response->rows[$i]['cell']=$response->rows[$i]['cell']=array($row->id, $row->reg, $row->pes, $row->doc_type, $row->doc_name, $row->doc_number, $row->doc_date, $row->supplier, $row->name_from, $row->_name_to, $row->description);
					$i++;
				}
					
				return json_encode($response);
				break;

		}

	}
	
	function getObjectType() {  //Получаем список типов объектов инвентаризации

		$db = JFactory::getDbo();//Подключаемся к базе
		$query = "SELECT  #__inv_object_type.* FROM #__inv_object_type ORDER BY #__inv_object_type.type";
		$db->setQuery($query);//Выполняем запрос
		$rList = $db->loadObjectList();

		$i=0;
		foreach($rList as $row){
			$response->rows[$i]['id']=$row->id;
			$response->rows[$i]['cell']=array($row->type, $row->func);
			$i++;
		}
		return json_encode($response);
	}
	
	function getEquipment() {//Редактирование помещений на объектах
			
		$rList = $this->getAccessUser();//Получаем права пользователей

		if ($rList[0]->admin){
			$db = JFactory::getDbo();//Подключаемся к базе

			switch (JRequest::getVar('oper')){
				case sel:
					$query = "SELECT  #__neq_type.* FROM #__neq_type WHERE #__neq_type.catcode = ".JRequest::getVar('catcode');
					$db->setQuery($query);//Выполняем запрос
					$rList = $db->loadObjectList();
					
					$i=0;
					foreach($rList as $row){
						$response->rows[$i]['id']=$row->id;
						$response->rows[$i]['cell']=array($row->name, $row->code, $row->img, $row->img);
						$i++;
					}
					return json_encode($response);
					break;
                case cateq://Оборудование с категориями
                    $query = "SELECT
                                  #__neq_category.name AS category,
                                  #__neq_type.*
                                FROM #__neq_type
                                  INNER JOIN #__neq_category
                                    ON #__neq_type.catcode = #__neq_category.id
                                ORDER BY category, #__neq_type.name";
                    $db->setQuery($query);//Выполняем запрос
                    $rList = $db->loadObjectList();

                    $i=0;
                    foreach($rList as $row){
                        $response->rows[$i]['id']=$row->id;
                        $response->rows[$i]['cell']=array($row->category, $row->name, $row->code, $row->img, $row->catcode);
                        $i++;
                    }
                    return json_encode($response);
                    break;
				case add:
					$this->log("Добавлен тип оборудования ".JRequest::getVar('name'));
					$db->setQuery("INSERT INTO #__neq_type
							(catcode, name, code, img)
							VALUES ('".JRequest::getVar('catcode')."','".JRequest::getVar('name')."','".JRequest::getVar('code')."','".JRequest::getVar('img')."')");
					$db->query();
					break;
				case del:
					$this->log("Удален тип оборудования с id ".JRequest::getVar('id'));
					$id = JRequest::getVar('id');
					$db->setQuery("DELETE FROM #__neq_type WHERE #__neq_type.id = ".$id);
					$db->query();
					break;
				case edit:
					$this->log("Изменено тип оборудования ".JRequest::getVar('name'));
					$id = JRequest::getVar('id');
					$db->setQuery("UPDATE #__neq_type
						SET catcode='".JRequest::getVar('catcode')."',
						   	name='".JRequest::getVar('name')."',
							code='".JRequest::getVar('code')."',
							img='".JRequest::getVar('img')."'
						 	WHERE id = ".$id);
					$db->query();
					break;
			}
		}
	}
	
	function getStores() {//Редактирование складов
			
		$rList = $this->getAccessUser();//Получаем права пользователей
		$idreg = JRequest::getVar('id_region');
		$oper = JRequest::getVar('oper');//Получаем тип операции
		
		$db = JFactory::getDbo();//Подключаемся к базе

		if ($rList[0]->admin){					
				
			switch ($oper){
				case sel:	
					$query = "SELECT  #__inv_store.*,  #__neq_region.Name AS region,  #__neq_pes.name AS pes,  #__neq_po.name AS po,  #__neq_a.name AS a
					FROM #__inv_store
					INNER JOIN #__neq_a
					ON #__inv_store.id_a = #__neq_a.id
					INNER JOIN #__neq_region
					ON #__inv_store.id_region = #__neq_region.id
					INNER JOIN #__neq_po
					ON #__inv_store.id_po = #__neq_po.id
					AND #__neq_a.codepo = #__neq_po.id
					INNER JOIN #__neq_pes
					ON #__inv_store.id_pes = #__neq_pes.id
					AND #__neq_pes.regid = #__neq_region.id
					AND #__neq_po.pes = #__neq_pes.id
					WHERE #__inv_store.id_region = {$idreg}
					ORDER BY #__inv_store.name";
					$db->setQuery($query);//Выполняем запрос
					$rList = $db->loadObjectList();
						
					$i=0;
					foreach($rList as $row){
						$response->rows[$i]['id']=$row->id;
						$response->rows[$i]['cell']=array($row->name, $row->code, $row->pes, $row->po, $row->a, $row->id_region, $row->id_pes, $row->id_po, $row->id_a, $row->latitude, $row->longitude, $row->altitude);
						$i++;
					}
					return json_encode($response);
					break;
				case add:
					$this->log("Добавлен тип оборудования ".JRequest::getVar('name'));
					$db->setQuery("INSERT INTO #__neq_type
							(catcode, name, code, img)
							VALUES ('".JRequest::getVar('catcode')."','".JRequest::getVar('name')."','".JRequest::getVar('code')."','".JRequest::getVar('img')."')");
					$db->query();
					break;
				case del:
					$this->log("Удален тип оборудования с id ".JRequest::getVar('id'));
					$id = JRequest::getVar('id');
					$db->setQuery("DELETE FROM #__neq_type WHERE #__neq_type.id = ".$id);
					$db->query();
					break;
				case edit:
					$this->log("Изменено тип оборудования ".JRequest::getVar('name'));
					$id = JRequest::getVar('id');
					$db->setQuery("UPDATE #__neq_type
						SET catcode='".JRequest::getVar('catcode')."',
						   	name='".JRequest::getVar('name')."',
							code='".JRequest::getVar('code')."',
							img='".JRequest::getVar('img')."'
						 	WHERE id = ".$id);
					$db->query();
					break;
			}
		}
	}
	
	function getAccessEdit() {//Редактирование помещений на объектах
			
		$rList = $this->getAccessUser();//Получаем права пользователей
	
		if ($rList[0]->admin){
			try {
				$db = JFactory::getDbo();//Подключаемся к базе
				$oper = JRequest::getVar('oper');//Получаем тип операции
				$table = JRequest::getVar('table');//Выбор из таблицы
				$region = JRequest::getVar('region');// Определяем таблицу названий региона или ПО
				$id_access = JRequest::getVar('id_access'); //id Региона
				
				switch (JRequest::getVar('oper')){
					case sel:
						$query = "SELECT
								  #__{$table}.id,
								  #__{$table}.id_access,
								  #__{$table}.id_user,
								  #__{$region}.Name AS access_name,
								  #__{$table}.reg_write AS access_write,
								  #__{$table}.reg_read AS access_read,
								  #__users.name AS user,
								  #__users.username
								FROM #__{$table}
								  INNER JOIN #__{$region}
								    ON #__{$table}.id_access = #__{$region}.id
								  INNER JOIN #__users
								    ON #__{$table}.id_user = #__users.id
								WHERE #__{$table}.id_access = {$id_access}";
						$db->setQuery($query);//Выполняем запрос
						$rList = $db->loadObjectList();
						
						$i=0;
						foreach($rList as $row){
							$response->rows[$i]['id']=$row->id;
							$response->rows[$i]['cell']=array($row->access_name, $row->user, $row->access_write, $row->access_read);
							$i++;
						}
						if ($i == 0) {
							$query = "SELECT  #__{$region}.name FROM #__{$region} WHERE #__{$region}.id = '{$id_access}'";
							$db->setQuery($query);//Выполняем запрос
							$regname = $db->loadResult();
							
							$response->rows[$i]['id']=0;
							$response->rows[$i]['cell']=array($regname, '---', '---', '---');
						}
						return json_encode($response);
						break;
					case add:
						$name = JRequest::getVar('user'); //Имя пользователя
						$reg_write = JRequest::getVar('reg_write');
						$reg_read = JRequest::getVar('reg_read');
						
						$query = "SELECT  #__users.id FROM #__users WHERE #__users.name = '{$name}'";
						$db->setQuery($query);//Выполняем запрос
						$id_user = $db->loadResult();
						
						$this->log("Добавлены права пользователю {$name}");
						$db->setQuery("INSERT INTO #__{$table}
								(id_access, id_user, reg_write, reg_read)
								VALUES ('{$id_access}','{$id_user}','{$reg_write}','{$reg_read}')");
						$db->query();
						break;
					case del:
						$this->log("Удалены права для пользователя с id ".JRequest::getVar('id'));
						$id = JRequest::getVar('id');
						$db->setQuery("DELETE FROM #__{$table} WHERE #__{$table}.id = ".$id);
						$db->query();
						break;
					case edit:
						$name = JRequest::getVar('user'); //Имя пользователя
						$reg_write = JRequest::getVar('reg_write');
						$reg_read = JRequest::getVar('reg_read');
						
						$query = "SELECT  #__users.id FROM #__users WHERE #__users.name = '{$name}'";
						$db->setQuery($query);//Выполняем запрос
						$id_user = $db->loadResult();
						
						$this->log("Изменены права у пользоателя ".JRequest::getVar('user'));
						$id = JRequest::getVar('id');
						$db->setQuery("UPDATE #__{$table} SET id_user='{$id_user}', reg_write='{$reg_write}', reg_read='{$reg_read}' WHERE id = {$id}");
						$db->query();
						break;
				}
			}
			catch (PDOException $e) {
				return 'Ошибка базы данных: '.$e->getMessage();
			}
		}
	}	
	
	function getComputer() {//Выборки компьютеров
			
			$rList = $this->getAccessUser();//Получаем права пользователей
		try {
				$db = JFactory::getDbo();//Подключаемся к базе
				$oper = JRequest::getVar('oper');//Получаем тип операции
				$table = JRequest::getVar('table');//Выбор из таблицы
				$region = JRequest::getVar('region');// Определяем таблицу названий региона или ПО
				$pes = JRequest::getVar('pes'); //id Региона
				$id_access = JRequest::getVar('id_access'); //id Региона
				$user = &JFactory::getUser();
				
	
				switch (JRequest::getVar('oper')){
					case sel:						
						
						if (JRequest::getVar('single') == '1'){
							$curPage=1;
							$rowsPerPage = 5;
							$sortingField=name;
							$sortingOrder = 'asc';
							$search = 'true';
						}else{
							$curPage = JRequest::getVar('page');
							$rowsPerPage = JRequest::getVar('rows');
							$sortingField = JRequest::getVar('sidx');
							$sortingOrder =JRequest::getVar('sord');
							$search = JRequest::getVar('_search');
						}						
						
						if ($search == 'true'){
							$where = "WHERE #__inv_object.id LIKE '%'";
							if (JRequest::getVar('id')) $where = "WHERE #__inv_object.id = ".JRequest::getVar('id');
							if (JRequest::getVar('name')) $where .= " AND #__inv_object.name LIKE '%".JRequest::getVar('name')."%' ";
							if (JRequest::getVar('purchase_date')) $where .= " AND #__inv_object.purchase_date LIKE '".JRequest::getVar('purchase_date')."%' ";
							if (JRequest::getVar('s_number')) $where .= " AND #__inv_object.s_number LIKE '%".JRequest::getVar('s_number')."%' ";
							if (JRequest::getVar('i_number')) $where .= " AND #__inv_object.i_number LIKE '%".JRequest::getVar('i_number')."%' ";
							if (JRequest::getVar('i_number_adv')) $where .= " AND #__inv_object.i_number_adv LIKE '%".JRequest::getVar('i_number_adv')."%' ";
							if (JRequest::getVar('barcode')) $where .= " AND #__inv_object.barcode LIKE '%".JRequest::getVar('barcode')."%' ";
							if (JRequest::getVar('s_number')) $where .= " AND #__inv_object.s_number LIKE '%".JRequest::getVar('s_number')."%' ";
							if (JRequest::getVar('supplier')) $where .= " AND supplier LIKE '%".JRequest::getVar('supplier')."%' ";
							if (JRequest::getVar('vendor')) $where .= " AND vendor LIKE '%".JRequest::getVar('vendor')."%' ";
							if (JRequest::getVar('chasis')) $where .= " AND chasis LIKE '%".JRequest::getVar('chasis')."%' ";
							if (JRequest::getVar('model')) $where .= " AND model LIKE '%".JRequest::getVar('model')."%' ";
							if (JRequest::getVar('id_comp')) $where .= "AND  #__inv_computer.id = ".JRequest::getVar('id_comp');
							if (JRequest::getVar('id')) $where .= " AND #__inv_object.id = ".JRequest::getVar('id');
						}
						$query = "SELECT COUNT(#__inv_object.id) AS count 									 
									FROM #__inv_object
									  INNER JOIN #__inv_object_type
									    ON #__inv_object.id_obj_type = #__inv_object_type.id
									  LEFT OUTER JOIN #__inv_obj_moving
									    ON #__inv_obj_moving.id_obj = #__inv_object.id
									  INNER JOIN #__neq_region
									    ON #__inv_obj_moving.id_region = #__neq_region.id
									  INNER JOIN #__neq_pes
									    ON #__inv_obj_moving.id_pes = #__neq_pes.id
									  LEFT OUTER JOIN #__neq_po
									    ON #__inv_obj_moving.id_po = #__neq_po.id
									  LEFT OUTER JOIN #__neq_a
									    ON #__inv_obj_moving.id_a = #__neq_a.id
									  LEFT OUTER JOIN #__inv_type_accounting
									    ON #__inv_object.id_type_accounting = #__inv_type_accounting.id
									  LEFT OUTER JOIN #__inv_vendor
									    ON #__inv_object.id_vendor = #__inv_vendor.id
									  LEFT OUTER JOIN #__inv_supplier
									    ON #__inv_object.id_supplier = #__inv_supplier.id
									  LEFT OUTER JOIN #__inv_countries
									    ON #__inv_vendor.country = #__inv_countries.id
									  INNER JOIN (SELECT
									      #__inv_obj_moving.id_obj,
									      MAX(#__inv_obj_moving.date) AS mdate
									    FROM #__inv_obj_moving
									    WHERE #__inv_obj_moving.id_region IN (SELECT
									        #__inv_access_region.id_access
									      FROM #__inv_access_region
									      WHERE #__inv_access_region.id_user = {$user->id}
									      AND (#__inv_access_region.reg_read = 1
									      OR #__inv_access_region.reg_write = 1))
									    OR #__inv_obj_moving.id_pes IN (SELECT
									        #__inv_access_pes.id_access
									      FROM #__inv_access_pes
									      WHERE #__inv_access_pes.id_user = {$user->id}
									      AND (#__inv_access_pes.reg_read = 1
									      OR #__inv_access_pes.reg_write = 1))
									    GROUP BY #__inv_obj_moving.id_obj) SubQuery
									    ON #__inv_obj_moving.id_obj = SubQuery.id_obj
									    AND #__inv_obj_moving.date = SubQuery.mdate
									  INNER JOIN #__inv_computer
									    ON #__inv_computer.id_obj = #__inv_object.id
									  INNER JOIN #__inv_comp_hardware
									    ON #__inv_comp_hardware.id_computer = #__inv_computer.id
									  INNER JOIN #__inv_comp_type
									    ON #__inv_computer.id_type = #__inv_comp_type.id
								
									  LEFT OUTER JOIN #__inv_zabbix
									    ON #__inv_computer.id_zabbix = #__inv_zabbix.id
									  LEFT OUTER JOIN #__inv_adpc
									    ON #__inv_computer.id_adpc = #__inv_adpc.id
									  LEFT OUTER JOIN #__inv_ocs
									    ON #__inv_computer.id_ocs = #__inv_ocs.ID
									  LEFT OUTER JOIN #__inv_users
									    ON #__inv_object.id_user = #__inv_users.id
									  LEFT OUTER JOIN #__inv_users #__inv_users_1
									    ON #__inv_object.id_resp_user = #__inv_users_1.id
								 {$where}";
						
						$db->setQuery($query);
						$rows = $db->loadResult();
								// $rows = 17;
						$totalRows = $rows;
						$firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
									
						$query = " SELECT #__inv_object.*,
									  #__inv_type_accounting.accounting,
									  #__inv_type_accounting.id AS id_accounting,
									  #__inv_object_type.type,
									  #__inv_object_type.func,
									  #__neq_region.Name AS region,
									  #__neq_pes.name AS pes,
									  #__neq_po.name AS po,
									  #__neq_a.name AS a,
									  #__inv_obj_moving.date,
									  #__inv_obj_moving.code,
									  #__inv_obj_moving.id_region,
									  #__inv_obj_moving.id_pes,
									  #__inv_obj_moving.id_a,
									  #__inv_obj_moving.id_po,
									  #__inv_obj_moving.latitude,
									  #__inv_obj_moving.longitude,
									  #__inv_obj_moving.altitude,
									  #__inv_vendor.vendor,
									  #__inv_supplier.supplier,
									  #__inv_countries.name AS country,
									  #__inv_comp_hardware.date AS comp_hardware_date,
									  #__inv_comp_hardware.processor,
									  #__inv_comp_hardware.mem,
									  #__inv_comp_hardware.motherboard,
									  #__inv_comp_hardware.hdd,
									  #__inv_comp_hardware.psu,
									  #__inv_comp_hardware.graphics,
									  #__inv_comp_hardware.network,
									  #__inv_comp_hardware.mac,
									  #__inv_comp_hardware.description AS comp_description,
									  #__inv_computer.id_type AS id_comp_type,
									  #__inv_computer.id_adpc,
									  #__inv_computer.id_zabbix,
									  #__inv_computer.id_ocs,
									  #__inv_computer.chasis,
									  #__inv_computer.model,
									  #__inv_computer.osname,
									  #__inv_computer.config,
									  #__inv_zabbix.host AS zabbix_name,
									  #__inv_adpc.cn AS ad_name,
									  #__inv_ocs.NAME AS ocs_name,
									  #__inv_computer.id AS id_comp,
									  #__inv_comp_type.type AS comp_type,
									  #__inv_users.cn AS user,
									  #__inv_users.department AS user_dep,
									  #__inv_users_1.cn AS resp_user
									FROM #__inv_object
									  INNER JOIN #__inv_object_type
									    ON #__inv_object.id_obj_type = #__inv_object_type.id
									  LEFT OUTER JOIN #__inv_obj_moving
									    ON #__inv_obj_moving.id_obj = #__inv_object.id
									  INNER JOIN #__neq_region
									    ON #__inv_obj_moving.id_region = #__neq_region.id
									  INNER JOIN #__neq_pes
									    ON #__inv_obj_moving.id_pes = #__neq_pes.id
									  LEFT OUTER JOIN #__neq_po
									    ON #__inv_obj_moving.id_po = #__neq_po.id
									  LEFT OUTER JOIN #__neq_a
									    ON #__inv_obj_moving.id_a = #__neq_a.id
									  LEFT OUTER JOIN #__inv_type_accounting
									    ON #__inv_object.id_type_accounting = #__inv_type_accounting.id
									  LEFT OUTER JOIN #__inv_vendor
									    ON #__inv_object.id_vendor = #__inv_vendor.id
									  LEFT OUTER JOIN #__inv_supplier
									    ON #__inv_object.id_supplier = #__inv_supplier.id
									  LEFT OUTER JOIN #__inv_countries
									    ON #__inv_vendor.country = #__inv_countries.id
									  INNER JOIN (SELECT
									      #__inv_obj_moving.id_obj,
									      MAX(#__inv_obj_moving.date) AS mdate
									    FROM #__inv_obj_moving
									    WHERE #__inv_obj_moving.id_region IN (SELECT
									        #__inv_access_region.id_access
									      FROM #__inv_access_region
									      WHERE #__inv_access_region.id_user = {$user->id}
									      AND (#__inv_access_region.reg_read = 1
									      OR #__inv_access_region.reg_write = 1))
									    OR #__inv_obj_moving.id_pes IN (SELECT
									        #__inv_access_pes.id_access
									      FROM #__inv_access_pes
									      WHERE #__inv_access_pes.id_user = {$user->id}
									      AND (#__inv_access_pes.reg_read = 1
									      OR #__inv_access_pes.reg_write = 1))
									    GROUP BY #__inv_obj_moving.id_obj) SubQuery
									    ON #__inv_obj_moving.id_obj = SubQuery.id_obj
									    AND #__inv_obj_moving.date = SubQuery.mdate
									  INNER JOIN #__inv_computer
									    ON #__inv_computer.id_obj = #__inv_object.id
									  INNER JOIN #__inv_comp_hardware
									    ON #__inv_comp_hardware.id_computer = #__inv_computer.id
									  INNER JOIN #__inv_comp_type
									    ON #__inv_computer.id_type = #__inv_comp_type.id
									  INNER JOIN (SELECT
									      #__inv_comp_hardware.id_computer,
									      MAX(#__inv_comp_hardware.date) AS expr1
									    FROM #__inv_comp_hardware
									    GROUP BY #__inv_comp_hardware.id_computer) SubQuery_1
									    ON SubQuery_1.id_computer = #__inv_comp_hardware.id_computer
									    AND #__inv_comp_hardware.date = SubQuery_1.expr1
									  LEFT OUTER JOIN #__inv_zabbix
									    ON #__inv_computer.id_zabbix = #__inv_zabbix.id
									  LEFT OUTER JOIN #__inv_adpc
									    ON #__inv_computer.id_adpc = #__inv_adpc.id
									  LEFT OUTER JOIN #__inv_ocs
									    ON #__inv_computer.id_ocs = #__inv_ocs.ID
									  LEFT OUTER JOIN #__inv_users
									    ON #__inv_object.id_user = #__inv_users.id
									  LEFT OUTER JOIN #__inv_users #__inv_users_1
									    ON #__inv_object.id_resp_user = #__inv_users_1.id
									 {$where} ORDER BY {$sortingField} {$sortingOrder} LIMIT {$firstRowIndex}, {$rowsPerPage}";
							
						$db->setQuery($query);//Выполняем запрос
						$rList = $db->loadObjectList();
						
						if (JRequest::getVar('single') == '1' && $rList[0]->id){
							$query = "SELECT
								#__inv_obj_users.id_user,
								#__inv_users.cn AS user,
								#__inv_users.department,
								#__inv_obj_users.date AS user_date
								FROM #__inv_obj_users
								INNER JOIN #__inv_users
								ON #__inv_obj_users.id_user = #__inv_users.id
								WHERE #__inv_obj_users.id_obj = {$rList[0]->id}
								AND #__inv_obj_users.date = (SELECT
								MAX(#__inv_obj_users.date) AS expr1
								FROM #__inv_obj_users
								WHERE #__inv_obj_users.id_obj = {$rList[0]->id})";
							$db->setQuery($query);//Выполняем запрос
							$rUser = $db->loadObjectList();
							$query = "SELECT
								#__inv_obj_resp_users.id_user AS id_resp_user,
								#__inv_users.cn AS resp_user,
								#__inv_obj_resp_users.date AS resp_user_date
								FROM #__inv_obj_resp_users
								INNER JOIN #__inv_responsible_user
								ON #__inv_obj_resp_users.id_user = #__inv_responsible_user.id_user
								INNER JOIN #__inv_users
								ON #__inv_responsible_user.id_user = #__inv_users.id
								WHERE #__inv_obj_resp_users.id_obj = {$rList[0]->id}
								AND #__inv_obj_resp_users.date = (SELECT
								MAX(#__inv_obj_resp_users.date) AS expr1
								FROM #__inv_obj_resp_users
								WHERE #__inv_obj_resp_users.id_obj = {$rList[0]->id})";
							$db->setQuery($query);//Выполняем запрос
							$rRespUser = $db->loadObjectList();
							$response = $rList[0];
							$response->id_user = $rUser[0]->id_user;
							$response->user_date = $rUser[0]->user_date;
							$response->user = $rUser[0]->user;
							$response->department = $rUser[0]->department;
							$response->id_resp_user = $rRespUser[0]->id_resp_user;
							$response->resp_user = $rRespUser[0]->resp_user;
							$response->resp_user_date = $rRespUser[0]->resp_user_date;							
							return json_encode($response);
						}else{
							$response->page = $curPage;
							$response->total = ceil($totalRows / $rowsPerPage);
							$response->records = $totalRows;
							$i=0;
							foreach($rList as $row){
									$response->rows[$i]['id']=$row->id;
									$response->rows[$i]['cell']=array(
											$row->id,
											$row->name,
											$row->purchase_date,
											$row->s_number,
											$row->i_number,
											$row->i_number_adv,
											$row->barcode,
											$row->guaranty,										
											$row->accounting,
											$row->vendor,
											$row->country,
											$row->supplier,
											$row->obj_description,
											$row->resp_user,
											$row->user,
											$row->department,
											$row->code,
											$row->region,
											$row->pes,
											$row->po,
											$row->a,										
											$row->chasis,
											$row->model,
											$row->osname,
											$row->comp_type,
											$row->processor,
											$row->mem,
											$row->motherboard,
											$row->hdd,
											$row->psu,
											$row->graphics,
											$row->network,
											$row->mac,
											$row->config,
											$row->ad_name,
											$row->zabbix_name,
											$row->ocs_name
									);
									$i++;
								}
								return json_encode($response);
						}						
						break;

				}
			}
			catch (PDOException $e) {
			return 'Ошибка базы данных: '.$e->getMessage();
		}
	}

	function getTypeByID(){//-------------------------Получаем тип объекта из id--------------------------------
		try {
			$db = JFactory::getDbo();//Подключаемся к базе
			$id = JRequest::getVar('id');//Получаем тип операции
			$query = "SELECT #__inv_object_type.func
					FROM #__inv_object
					INNER JOIN #__inv_object_type
					ON #__inv_object.id_obj_type = #__inv_object_type.id
					WHERE #__inv_object.id = {$id}";
			$db->setQuery($query);
			$rows = $db->loadResult();
			return json_encode($rows);
		}
		catch (PDOException $e) {
			return 'Ошибка базы данных: '.$e->getMessage();
		}
	}


	
	function getStorParts() {//Редактирование объектов на складе
			
		$rList = $this->getAccessUser();//Получаем права пользователей
		$user = &JFactory::getUser();
		try {
			$db = JFactory::getDbo();//Подключаемся к базе
			$oper = JRequest::getVar('oper');//Получаем тип операции
			$table = JRequest::getVar('table');//Выбор из таблицы
			$region = JRequest::getVar('region');// Определяем таблицу названий региона или ПО
			$pes = JRequest::getVar('pes'); //id Региона
			$id_access = JRequest::getVar('id_access'); //id Региона
			
			$id_sup  = JRequest::getVar('id_sup');
			$id_obj = JRequest::getVar('id_obj');
			$id_user_for = JRequest::getVar('id_user_for');
			$deb_count = JRequest::getVar('deb_count');
			$deb_date = JRequest::getVar('deb_date');
			if ($id_obj == '') $id_obj = 'null';
			if ($id_user_for == '') $id_user_for = 'null';
	
	
			switch (JRequest::getVar('oper')){
				case sel:
					$curPage = JRequest::getVar('page');
					$rowsPerPage = JRequest::getVar('rows');
					$sortingField = JRequest::getVar('sidx');
					$sortingOrder =JRequest::getVar('sord');
					$search = JRequest::getVar('_search');
					$where = '';
	
					if ($region != '') $where = " #__inv_docs.id_reg = {$region}";
					if ($pes != ''){
						if ($where == '') $where = " #__inv_docs.id_pes = {$pes}";
						else $where .= " AND #__inv_docs.id_pes = {$pes}";
					}
	
					$where = '';
					if ($search == 'true'){
						if (JRequest::getVar('store_name')) $where .= " AND #__inv_store.name LIKE '".JRequest::getVar('store_name')."%' ";
						if (JRequest::getVar('name')) $where .= " AND #__inv_object.name LIKE '".JRequest::getVar('name')."%' ";
						if (JRequest::getVar('vendor')) $where .= " AND #__inv_vendor.vendor LIKE '".JRequest::getVar('vendor')."%' ";
						if (JRequest::getVar('sup_type_name')) $where .= " AND  #__inv_sup_type.sup_type_name LIKE '".JRequest::getVar('sup_type_name')."%' ";
						if (JRequest::getVar('model')) $where .= " AND #__inv_supplies.model LIKE '%".JRequest::getVar('model')."%' ";
						if (JRequest::getVar('sup_count')) $where .= " AND sup_count = '".JRequest::getVar('sup_count')."' ";
						if (JRequest::getVar('deb_count')) $where .= " AND deb_count = '".JRequest::getVar('deb_count')."' ";
						
						
						if (JRequest::getVar('description')) $where .= " AND #__inv_docs.description LIKE '%".JRequest::getVar('description')."%' ";
						//$row->store_name, $row->name, $row->model, $row->sup_count, $row->deb_count, $row->buh_name, $row->buh_date, $row->purchase_date, $row->s_number,
						//$row->i_number, $row->i_number_adv, $row->barcode, $row->vendor, $row->supler, $row->id_sup
					}
	
					$query = "SELECT COUNT(#__inv_object.id) AS count
								FROM #__inv_object
							  INNER JOIN #__inv_object_type
							    ON #__inv_object.id_obj_type = #__inv_object_type.id
							  LEFT OUTER JOIN #__inv_obj_moving
							    ON #__inv_obj_moving.id_obj = #__inv_object.id
							  INNER JOIN #__neq_region
							    ON #__inv_obj_moving.id_region = #__neq_region.id
							  INNER JOIN #__neq_pes
							    ON #__inv_obj_moving.id_pes = #__neq_pes.id
							  LEFT OUTER JOIN #__neq_po
							    ON #__inv_obj_moving.id_po = #__neq_po.id
							  LEFT OUTER JOIN #__neq_a
							    ON #__inv_obj_moving.id_a = #__neq_a.id
							  LEFT OUTER JOIN #__inv_vendor
							    ON #__inv_object.id_vendor = #__inv_vendor.id
							  LEFT OUTER JOIN #__inv_countries
							    ON #__inv_vendor.country = #__inv_countries.id
							  INNER JOIN (SELECT
							      #__inv_obj_moving.id_obj,
							      MAX(#__inv_obj_moving.date) AS mdate
							    FROM #__inv_obj_moving
							    WHERE #__inv_obj_moving.id_region IN (SELECT
							        #__inv_access_region.id_access
							      FROM #__inv_access_region
							      WHERE #__inv_access_region.id_user = {$user->id}
							      AND (#__inv_access_region.reg_read = 1
							      OR #__inv_access_region.reg_write = 1))
							    OR #__inv_obj_moving.id_pes IN (SELECT
							        #__inv_access_pes.id_access
							      FROM #__inv_access_pes
							      WHERE #__inv_access_pes.id_user = {$user->id}
							      AND (#__inv_access_pes.reg_read = 1
							      OR #__inv_access_pes.reg_write = 1))
							    GROUP BY #__inv_obj_moving.id_obj) SubQuery
							    ON #__inv_obj_moving.id_obj = SubQuery.id_obj
							    AND #__inv_obj_moving.date = SubQuery.mdate
							  INNER JOIN #__inv_store
							    ON #__inv_object.id_store = #__inv_store.id
							  LEFT OUTER JOIN #__inv_supplies
							    ON #__inv_supplies.id_obj = #__inv_object.id
							  LEFT OUTER JOIN #__inv_obj_buh
							    ON #__inv_obj_buh.id_obj = #__inv_object.id
							  LEFT OUTER JOIN #__inv_sup_type
							    ON #__inv_supplies.id_supplies_type = #__inv_sup_type.id
							  LEFT OUTER JOIN #__inv_sup_debit
							    ON #__inv_sup_debit.id_sup = #__inv_supplies.id
							WHERE ((SELECT
							    SUM(#__inv_sup_debit.deb_count) AS expr1
							  FROM #__inv_sup_debit) < #__inv_supplies.sup_count
							OR #__inv_sup_debit.deb_count IS NULL)
							 {$where} GROUP BY #__inv_supplies.id_obj ";
					$db->setQuery($query);
					$rows = $db->loadResult();
						
						
					$totalRows = $rows;
					$firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
	
					$query = "SELECT
							  #__inv_object.*,
							  #__inv_object_type.type,
							  #__inv_object_type.func,
							  #__inv_vendor.vendor,
							  #__inv_countries.name AS country,
							  #__inv_store.name AS store_name,
							  #__inv_supplies.model,
							  #__inv_store.code AS stor_code,
							  #__inv_obj_buh.buh_name,
							  #__inv_obj_buh.buh_date,
							  #__inv_sup_type.sup_type_name,
							  #__inv_sup_type.func AS sup_func,
							  #__inv_supplies.sup_count,
							  #__inv_supplies.sup_count - SUM(#__inv_sup_debit.deb_count) AS deb_count

							FROM #__inv_object
							  INNER JOIN #__inv_object_type
							    ON #__inv_object.id_obj_type = #__inv_object_type.id
							  LEFT OUTER JOIN #__inv_obj_moving
							    ON #__inv_obj_moving.id_obj = #__inv_object.id
							  INNER JOIN #__neq_region
							    ON #__inv_obj_moving.id_region = #__neq_region.id
							  INNER JOIN #__neq_pes
							    ON #__inv_obj_moving.id_pes = #__neq_pes.id
							  LEFT OUTER JOIN #__neq_po
							    ON #__inv_obj_moving.id_po = #__neq_po.id
							  LEFT OUTER JOIN #__neq_a
							    ON #__inv_obj_moving.id_a = #__neq_a.id
							  LEFT OUTER JOIN #__inv_vendor
							    ON #__inv_object.id_vendor = #__inv_vendor.id
							  LEFT OUTER JOIN #__inv_countries
							    ON #__inv_vendor.country = #__inv_countries.id
							  INNER JOIN (SELECT
							      #__inv_obj_moving.id_obj,
							      MAX(#__inv_obj_moving.date) AS mdate
							    FROM #__inv_obj_moving
							    WHERE #__inv_obj_moving.id_region IN (SELECT
							        #__inv_access_region.id_access
							      FROM #__inv_access_region
							      WHERE #__inv_access_region.id_user = {$user->id}
							      AND (#__inv_access_region.reg_read = 1
							      OR #__inv_access_region.reg_write = 1))
							    OR #__inv_obj_moving.id_pes IN (SELECT
							        #__inv_access_pes.id_access
							      FROM #__inv_access_pes
							      WHERE #__inv_access_pes.id_user = {$user->id}
							      AND (#__inv_access_pes.reg_read = 1
							      OR #__inv_access_pes.reg_write = 1))
							    GROUP BY #__inv_obj_moving.id_obj) SubQuery
							    ON #__inv_obj_moving.id_obj = SubQuery.id_obj
							    AND #__inv_obj_moving.date = SubQuery.mdate
							  INNER JOIN #__inv_store
							    ON #__inv_object.id_store = #__inv_store.id
							  LEFT OUTER JOIN #__inv_supplies
							    ON #__inv_supplies.id_obj = #__inv_object.id
							  LEFT OUTER JOIN #__inv_obj_buh
							    ON #__inv_obj_buh.id_obj = #__inv_object.id
							  LEFT OUTER JOIN #__inv_sup_type
							    ON #__inv_supplies.id_supplies_type = #__inv_sup_type.id
							  LEFT OUTER JOIN #__inv_sup_debit
							    ON #__inv_sup_debit.id_sup = #__inv_supplies.id
							WHERE ((SELECT
							    SUM(#__inv_sup_debit.deb_count) AS expr1
							  FROM #__inv_sup_debit) < #__inv_supplies.sup_count
							OR #__inv_sup_debit.deb_count IS NULL)
							
								{$where} GROUP BY #__inv_supplies.id_obj ORDER BY {$sortingField} {$sortingOrder} LIMIT {$firstRowIndex}, {$rowsPerPage}";
						
					$db->setQuery($query);//Выполняем запрос
					$rList = $db->loadObjectList();
					$response->page = $curPage;
					$response->total = ceil($totalRows / $rowsPerPage);
					$response->records = $totalRows;
						
					$i=0;
					foreach($rList as $row){
						if (!$row->sup_count) $row->sup_count = 1;
						if (!$row->deb_count) $row->deb_count = $row->sup_count;
						$response->rows[$i]['id']=$row->id;
						$response->rows[$i]['cell']=array($row->store_name, $row->name, $row->vendor, $row->sup_type_name, $row->model, $row->sup_count, $row->deb_count, $row->buh_name, $row->buh_date, $row->purchase_date, $row->s_number,
							$row->i_number, $row->i_number_adv, $row->barcode, $row->supler, $row->id_sup);
						$i++;
					}
					return json_encode($response);
					break;
				case add:
					$id_doc_type = JRequest::getVar('id_doc_type');
					$doc_name = JRequest::getVar('doc_name');
					$doc_number = JRequest::getVar('doc_number');
					$doc_date = JRequest::getVar('doc_date');
					$description = JRequest::getVar('description');
					$id_supplier = JRequest::getVar('id_supplier');
					$id_from = JRequest::getVar('id_from');
					$id_to = JRequest::getVar('id_to');
					$id_reg = JRequest::getVar('id_reg');
					$id_pes = JRequest::getVar('id_pes');
					$id_po = JRequest::getVar('id_po');
	
					$db->setQuery("INSERT INTO #__inv_docs (id_doc_type, doc_name, doc_number, doc_date, description, id_supplier, id_from, id_to, id_reg, id_pes, id_po)
							VALUES ({$id_doc_type}, '{$doc_name}', '{$doc_number}', '{$doc_date}', '{$description}', {$id_supplier}, {$id_from},
							{$id_to}, {$id_reg}, {$id_pes}, {$id_po})");
							if ($db->query()) {
								$this->log("Добавлен документ {$doc_name} № {$doc_number} от {$doc_date}");
								return true;
							}
							break;
				case del:
					$this->log("Удален документ id".JRequest::getVar('id'));
					$id = JRequest::getVar('id');
					$db->setQuery("DELETE FROM #__inv_docs WHERE #__inv_docs.id = {$id}");
					$db->query();
					break;
				case debit:	

					$db->setQuery("INSERT INTO #__inv_sup_debit (id_sup, id_obj, id_user_for, id_user_debit, deb_count, deb_date)
							VALUES ({$id_sup}, {$id_obj}, {$id_user_for}, {$user->id}, {$deb_count}, '{$deb_date}')");
							if ($db->query()) {
								$this->log("Списан материал id: {$id_sup}");
								return true;
							}
					break;
			}
		}
		catch (PDOException $e) {
			return 'Ошибка базы данных: '.$e->getMessage();
		}
	}
	
	function getDocs() {//Редактирование документов
			
		$rList = $this->getAccessUser();//Получаем права пользователей
		$user = &JFactory::getUser();
			try {
				$db = JFactory::getDbo();//Подключаемся к базе
				$oper = JRequest::getVar('oper');//Получаем тип операции
				$table = JRequest::getVar('table');//Выбор из таблицы
				$region = JRequest::getVar('region');// Определяем таблицу названий региона или ПО
				$pes = JRequest::getVar('pes'); //id Региона
				$id_access = JRequest::getVar('id_access'); //id Региона
				
	
				switch (JRequest::getVar('oper')){
					case selaccess:
						$curPage = JRequest::getVar('page');
						$rowsPerPage = JRequest::getVar('rows');
						$sortingField = JRequest::getVar('sidx');
						$sortingOrder =JRequest::getVar('sord');
						$search = JRequest::getVar('_search');
						$where = '';
					
						if ($search == 'true'){
							if (JRequest::getVar('reg')) $where .= " AND #__neq_region.Name LIKE '".JRequest::getVar('reg')."%' ";
							if (JRequest::getVar('pes')) $where .= " AND #__neq_pes.name LIKE '".JRequest::getVar('pes')."%' ";
							if (JRequest::getVar('doc_type')) $where .= " AND #__inv_doc_types.doc_type LIKE '".JRequest::getVar('doc_type')."%' ";
							if (JRequest::getVar('doc_name')) $where .= " AND #__inv_docs.doc_name LIKE '".JRequest::getVar('doc_name')."%' ";
							if (JRequest::getVar('doc_number')) $where .= " AND #__inv_docs.doc_number LIKE '".JRequest::getVar('doc_number')."%' ";
							if (JRequest::getVar('doc_date')) $where .= " AND #__inv_docs.doc_date LIKE '".JRequest::getVar('doc_date')."%' ";
							if (JRequest::getVar('supplier')) $where .= " AND #__inv_supplier.supplier LIKE '".JRequest::getVar('supplier')."%' ";
							if (JRequest::getVar('name_from')) $where .= " AND #__inv_users_1.cn LIKE '".JRequest::getVar('name_from')."%' ";
							if (JRequest::getVar('name_to')) $where .= " AND #__inv_users.cn LIKE '".JRequest::getVar('name_to')."%' ";
							if (JRequest::getVar('description')) $where .= " AND #__inv_docs.description LIKE '%".JRequest::getVar('description')."%' ";
						}
					
						$query = "SELECT COUNT(#__inv_docs.id) AS count
								  FROM #__inv_docs
								  	  INNER JOIN #__inv_doc_types
									    ON #__inv_docs.id_doc_type = #__inv_doc_types.id
									  LEFT OUTER JOIN #__inv_supplier
									    ON #__inv_docs.id_supplier = #__inv_supplier.id
									  LEFT OUTER JOIN #__inv_users
									    ON #__inv_docs.id_to = #__inv_users.id
									  LEFT OUTER JOIN #__inv_users #__inv_users_1
									    ON #__inv_docs.id_from = #__inv_users_1.id
									  LEFT OUTER JOIN #__neq_region
									    ON #__inv_docs.id_reg = #__neq_region.id
									  LEFT OUTER JOIN #__neq_pes
									    ON #__inv_docs.id_pes = #__neq_pes.id
								WHERE (#__inv_docs.id_reg IN (SELECT
								    #__inv_access_region.id_access
								  FROM #__inv_access_region
								  WHERE #__inv_access_region.id_user = {$user->id}
								  AND (#__inv_access_region.reg_read = 1
								  OR #__inv_access_region.reg_write = 1))
								OR #__inv_docs.id_pes IN (SELECT
								    #__inv_access_pes.id_access
								  FROM #__inv_access_pes
								  WHERE #__inv_access_pes.id_user = {$user->id}
								  AND (#__inv_access_pes.reg_read = 1
								  OR #__inv_access_pes.reg_write = 1))) {$where}";
						$db->setQuery($query);
						$rows = $db->loadResult();
							
							
						$totalRows = $rows;
						$firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
					
						$query = "SELECT
								      #__inv_docs.*,
									  #__inv_doc_types.doc_type,
									  #__inv_users.cn AS name_to,
									  #__inv_users.department AS dep_to,
									  #__inv_users_1.cn AS name_from,
									  #__inv_users_1.department AS dep_from,
									  #__neq_region.Name AS reg,
									  #__neq_pes.name AS pes,
									  #__inv_supplier.supplier
									FROM #__inv_docs
									  INNER JOIN #__inv_doc_types
									    ON #__inv_docs.id_doc_type = #__inv_doc_types.id
									  LEFT OUTER JOIN #__inv_supplier
									    ON #__inv_docs.id_supplier = #__inv_supplier.id
									  LEFT OUTER JOIN #__inv_users
									    ON #__inv_docs.id_to = #__inv_users.id
									  LEFT OUTER JOIN #__inv_users #__inv_users_1
									    ON #__inv_docs.id_from = #__inv_users_1.id
									  LEFT OUTER JOIN #__neq_region
									    ON #__inv_docs.id_reg = #__neq_region.id
									  LEFT OUTER JOIN #__neq_pes
									    ON #__inv_docs.id_pes = #__neq_pes.id
								WHERE (#__inv_docs.id_reg IN (SELECT
								    #__inv_access_region.id_access
								  FROM #__inv_access_region
								  WHERE #__inv_access_region.id_user = {$user->id}
								  AND (#__inv_access_region.reg_read = 1
								  OR #__inv_access_region.reg_write = 1))
								OR #__inv_docs.id_pes IN (SELECT
								    #__inv_access_pes.id_access
								  FROM #__inv_access_pes
								  WHERE #__inv_access_pes.id_user = {$user->id}
								  AND (#__inv_access_pes.reg_read = 1
								  OR #__inv_access_pes.reg_write = 1)))
								{$where} ORDER BY ".$sortingField." ".$sortingOrder." LIMIT ".$firstRowIndex.', '.$rowsPerPage;
							
						$db->setQuery($query);//Выполняем запрос
						$rList = $db->loadObjectList();
						$response->page = $curPage;
						$response->total = ceil($totalRows / $rowsPerPage);
						$response->records = $totalRows;
							
						$i=0;
						foreach($rList as $row){
							$response->rows[$i]['id']=$row->id;
							$response->rows[$i]['cell']=array($row->id, $row->reg, $row->pes, $row->doc_type, $row->doc_name, $row->doc_number, $row->doc_date, $row->supplier, $row->name_from, $row->_name_to, $row->description);
							$i++;
						}
						return json_encode($response);
						break;
					case sel:
						$curPage = JRequest::getVar('page');
						$rowsPerPage = JRequest::getVar('rows');
						$sortingField = JRequest::getVar('sidx');
						$sortingOrder =JRequest::getVar('sord');
						$search = JRequest::getVar('_search');
						$where = '';
						
						if ($region != '') $where = " #__inv_docs.id_reg = {$region}";
						if ($pes != ''){
							if ($where == '') $where = " #__inv_docs.id_pes = {$pes}";
							else $where .= " AND #__inv_docs.id_pes = {$pes}";
						}
	
						
						if ($search == 'true'){
							if (JRequest::getVar('doc_type')) $where .= " AND #__inv_doc_types.doc_type LIKE '".JRequest::getVar('doc_type')."%' ";
							if (JRequest::getVar('doc_name')) $where .= " AND #__inv_docs.doc_name LIKE '".JRequest::getVar('doc_name')."%' ";
							if (JRequest::getVar('doc_number')) $where .= " AND #__inv_docs.doc_number LIKE '".JRequest::getVar('doc_number')."%' ";
							if (JRequest::getVar('doc_date')) $where .= " AND #__inv_docs.doc_date LIKE '".JRequest::getVar('doc_date')."%' ";
							if (JRequest::getVar('supplier')) $where .= " AND #__inv_supplier.supplier LIKE '".JRequest::getVar('supplier')."%' ";
							if (JRequest::getVar('name_from')) $where .= " AND #__inv_users_1.cn LIKE '".JRequest::getVar('name_from')."%' ";
							if (JRequest::getVar('name_to')) $where .= " AND #__inv_users.cn LIKE '".JRequest::getVar('name_to')."%' ";
							if (JRequest::getVar('description')) $where .= " AND #__inv_docs.description LIKE '%".JRequest::getVar('description')."%' ";
						}
	
						$query = "SELECT COUNT(#__inv_docs.id) AS count 
								  FROM #__inv_docs
								  LEFT OUTER JOIN #__inv_users
								    ON #__inv_docs.id_to = #__inv_users.id
								  LEFT OUTER JOIN #__inv_users #__inv_users_1
								    ON #__inv_docs.id_from = #__inv_users_1.id
								  LEFT OUTER JOIN #__inv_supplier
								    ON #__inv_docs.id_supplier = #__inv_supplier.id
								  INNER JOIN #__inv_doc_types
								    ON #__inv_docs.id_doc_type = #__inv_doc_types.id WHERE  ".$where;
						$db->setQuery($query);
						$rows = $db->loadResult();
							
							
						$totalRows = $rows;
						$firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
	
						$query = "SELECT
								  #__inv_docs.*,
								  #__inv_users_1.cn AS `from`,
								  #__inv_users.cn AS `to`,
								  #__inv_supplier.supplier,
								  #__inv_doc_types.doc_type
								FROM #__inv_docs
								  LEFT OUTER JOIN #__inv_users
								    ON #__inv_docs.id_to = #__inv_users.id
								  LEFT OUTER JOIN #__inv_users #__inv_users_1
								    ON #__inv_docs.id_from = #__inv_users_1.id
								  LEFT OUTER JOIN #__inv_supplier
								    ON #__inv_docs.id_supplier = #__inv_supplier.id
								  INNER JOIN #__inv_doc_types
								    ON #__inv_docs.id_doc_type = #__inv_doc_types.id
								WHERE ".$where." ORDER BY ".$sortingField." ".$sortingOrder." LIMIT ".$firstRowIndex.', '.$rowsPerPage;
							
						$db->setQuery($query);//Выполняем запрос
						$rList = $db->loadObjectList();
						$response->page = $curPage;
						$response->total = ceil($totalRows / $rowsPerPage);
						$response->records = $totalRows;
							
						$i=0;
						foreach($rList as $row){
							$response->rows[$i]['id']=$row->id;
							$response->rows[$i]['cell']=array($row->doc_type, $row->doc_name, $row->doc_number, $row->doc_date, $row->supplier, $row->from, $row->to, $row->description);
							$i++;
						}
						return json_encode($response);
						break;
					case add:
							$id_doc_type = JRequest::getVar('id_doc_type');
							$doc_name = JRequest::getVar('doc_name');
							$doc_number = JRequest::getVar('doc_number');
							$doc_date = JRequest::getVar('doc_date');
							$description = JRequest::getVar('description');
							$id_supplier = JRequest::getVar('id_supplier');
							$id_from = JRequest::getVar('id_from');
							$id_to = JRequest::getVar('id_to');
							$id_reg = JRequest::getVar('id_reg');
							$id_pes = JRequest::getVar('id_pes');
							$id_po = JRequest::getVar('id_po');
												
							$db->setQuery("INSERT INTO #__inv_docs (id_doc_type, doc_name, doc_number, doc_date, description, id_supplier, id_from, id_to, id_reg, id_pes, id_po)
									                        VALUES ({$id_doc_type}, '{$doc_name}', '{$doc_number}', '{$doc_date}', '{$description}', {$id_supplier}, {$id_from},
																	{$id_to}, {$id_reg}, {$id_pes}, {$id_po})");
							if ($db->query()) {
								$this->log("Добавлен документ {$doc_name} № {$doc_number} от {$doc_date}");
								return true;
							}
							break;
					case del:
						$this->log("Удален документ id".JRequest::getVar('id'));
						$id = JRequest::getVar('id');
						$db->setQuery("DELETE FROM #__inv_docs WHERE #__inv_docs.id = {$id}");
						$db->query();
						break;
				}
			}
			catch (PDOException $e) {
			return 'Ошибка базы данных: '.$e->getMessage();
		}
	}
	
	function getSelect() {//Типовой список
	
		$rList = $this->getAccessUser();
		$select = JRequest::getVar('select');//Получаем таблицу
		$name = JRequest::getVar('name');//Получаем поле в таблице
		$oper = JRequest::getVar('oper');//Получаем тип операции
		$cel = JRequest::getVar($name);
	
		$db = JFactory::getDBO();// Подключаемся к базе.
		if ($rList[0]->admin){
			switch (JRequest::getVar('oper')){
				case sel:
					$query = "SELECT  #__{$select}.id, #__{$select}.{$name} AS name  FROM #__{$select} ORDER BY #__{$select}.{$name}";
					$db->setQuery($query);//Выполняем запрос
						
					if ($rList = $db->loadObjectList()) {
						$i=0;
						foreach($rList as $row){
							$response->rows[$i]['id']=$row->id;
							$response->rows[$i]['cell']=array($row->name);							
							$i++;
						}
							
						return json_encode($response);
					}
					else {
						return $db->stderr();
					}
					break;
				case add:
					$this->log("Добавлен в список".JRequest::getVar('select'));
					
					$db->setQuery("INSERT INTO #__{$select} ({$name}) VALUES ('{$cel}')");
					if ($db->query()) {
						return true;
					}
					else {
						return false;
					}
					break;
				case del:
					$this->log("Удален в списке ".JRequest::getVar('select'));
					$id = JRequest::getVar('id');
					$db->setQuery("DELETE FROM #__{$select} WHERE #__{$select}.id = {$id}");
					$db->query();
					break;
				case edit:
					$this->log("Изменен селект ".JRequest::getVar('select'));
					$id = JRequest::getVar('id');
					$db->setQuery("UPDATE #__{$select} SET {$name}='{$cel}' WHERE id = {$id}");
					$db->query();
					break;
			}
		}
	}
	
	function getExcel() {//Экспорт в Excel
	
		$objPHPExcel = new PHPExcel();
		$objReader = PHPExcel_IOFactory::createReader('Excel5'); //Задаем ридер
		$objPHPExcel = $objReader->load('media/com_stalram/templates/akt.xls'); //Загружаем "шаблонный" xl

		// Add some data
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Hello');
		$objPHPExcel->getActiveSheet()->SetCellValue('B2', 'world!');
		$objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Hello');
		$objPHPExcel->getActiveSheet()->SetCellValue('D2', 'world!');
		
				
		// Save Excel 2007 file
		$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
		//$objWriter->save(str_replace('.php', '.xlsx', __FILE__));
		
		$objWriter->save('media/com_stalram/acts/cdr.xlsx'); 
		$file = "/media/com_stalram/acts/cdr.xlsx";

		return $file;
	}
	
	function getAccessSelect() {//Список доступа к ПЭС
	
		$user = &JFactory::getUser();
		
		$select = JRequest::getVar('select');//Получаем тип списков
		try {
			$db = JFactory::getDBO();// Подключаемся к базе.
			switch ($select){
				case 'region':
					$query = "SELECT
							  #__neq_region.Name AS name,
							  #__neq_region.id,
							  #__inv_access_region.reg_write,
							  #__inv_access_region.reg_read
							FROM #__inv_access_region
							  INNER JOIN #__neq_region
							    ON #__inv_access_region.id_access = #__neq_region.id
							WHERE #__inv_access_region.id_user = {$user->id}";
					break;
                case 'pes':
					$regid = JRequest::getVar('regid');
					$query = "SELECT #__neq_pes.id, #__inv_access_pes.id_access,  #__neq_pes.name,  #__inv_access_pes.reg_write,  #__inv_access_pes.reg_read
							FROM #__inv_access_region, #__inv_access_pes
       						INNER JOIN #__neq_pes ON #__inv_access_pes.id_access = #__neq_pes.id
							WHERE #__inv_access_pes.id_user = {$user->id} AND #__neq_pes.regid = {$regid}
							GROUP BY #__inv_access_pes.id_access
							ORDER BY #__neq_pes.name";
					break;
				case 'pesall':
					$regid = JRequest::getVar('regid');
					$reg_write = JRequest::getVar('reg_write');
					$reg_read = JRequest::getVar('reg_read');
					
					$query = "SELECT
							  #__neq_pes.name,
							  #__neq_pes.id
							FROM #__neq_pes
							  INNER JOIN #__neq_region
							    ON #__neq_pes.regid = #__neq_region.id
							WHERE #__neq_region.id = {$regid}
							ORDER BY #__neq_pes.name";
					break;

			}
					
			$db->setQuery($query);//Выполняем запрос
				
			if ($rList = $db->loadObjectList()) {
				$i=0;
				foreach($rList as $row){
					$response->rows[$i]['id']=$row->id;					
					$response->rows[$i]['cell']=array($row->name, $row->reg_write, $row->reg_read);
					if ($reg_write || $reg_read) $response->rows[$i]['cell']=array($row->name, $reg_write, $reg_read);
					$i++;
				}
				return json_encode($response);
			}
			else {
				return $db->stderr();
			}
			break;
		}
		catch (PDOException $e) {
			return 'Ошибка базы данных: '.$e->getMessage();
		}

	}

}

