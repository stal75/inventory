<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
jimport('phpexcel.library.PHPExcel');
 

class StalramsViewSprav extends JViewLegacy
{
	function Clear_array_empty($array)
	{
		$ret_arr = array();
		foreach($array as $val)
		{
			if (!empty($val))
			{
				$ret_arr[] = trim($val);
			}
		}
		return $ret_arr;
	}
	
	// Overwriting JView display method
	function display($tpl = null) 
	{
		
		$model = $this->getModel();
		$document =& JFactory::getDocument();
		$document->addStyleSheet('components/com_stalrams/assets/css/stalrams.css');
		
		$Uv = 'СѓРІ';
		
		$id= JRequest::getVar('id');
		if ($id == '') $id=26;

		$names=$this->get('LDAPs');
		
		foreach($names as $row) {
		    $this->names .= "<p><a href=".JRoute::_('index.php')."&task=sprav&id=$row->id class=men3>".$row->name.'</a>';
		}
			
	$result=$model->getLDAP($id); //Получаем текущий LDAP
 		
		if (count($result) > 0) {
		 	$this->id=$result[0]->id;
		 	$this->name=$result[0]->name;
		 	$this->domen=$result[0]->domen;
		 	$this->ldap=$result[0]->ldap;
		 	$this->searh=$result[0]->searh;
		 	$this->userPName=$result[0]->login;
		 	$this->passw=$result[0]->passw;
		 	$this->img=$result[0]->img;
		}
		
		$ad=ldap_connect($this->ldap);  // обязан быть правильный LDAP-сервер!
		if ($ad){
			ldap_set_option ($ad, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_set_option ($ad, LDAP_OPT_REFERRALS, 0);
			if ($r = @ldap_bind ($ad, $this->userPName, $this->passw)){
			   $justthese = array("department");
               $sr=@ldap_search($ad, $this->searh, "(|(sn=*))", $justthese);
               $zap=@ldap_get_entries($ad, $sr);
  			   for ($i=0; $i<$zap["count"]; $i++){
		   		  $arrdep[$i]=$zap[$i]['department'][0];
			   }
		$arrdep=array_unique($arrdep);
		sort($arrdep);

		$arrdep = $this->Clear_array_empty($arrdep);//Чистим массив от пустых департаментов
		$this->lists['Dep'] = JHTML::_('select.genericlist', //Формируем список департаментов
				$arrdep,
				'depart',
				'id = "depart" width=100 size="1"',
				'text',
				'text',
				JRequest::getVar('depart'),
				'reg',
				true);

		unset($zap);
    	@ldap_free_result($sr);
    	$this->rezSearch = '';
  		if (JRequest::getVar('depart')!=''){
  			$depart=$arrdep[JRequest::getVar('depart')];
  			$this->rezSearch .= "Искали: <b>".$depart."</b>";
  			$depart=str_replace('\"', '"',  $depart);
        	$filter="(&(department=$depart)(!(telephoneNumber=$Uv*)))";
			$justthese = array( "ou", "sn", "cn", "company", "department", "division","telephoneNumber", "pager", "mobile", "mail", "title" );
			$sr=@ldap_search($ad,  $this->searh, $filter, $justthese);
			$q=@ldap_count_entries($ad, $sr);
		
			////////////////////////////////////////////// подотделы
			$zapd=@ldap_get_entries($ad, $sr);
			$this->metka=0;
			for ($i=0; $i<$zapd["count"]; $i++){
				$arrdepd[$i]=$zapd[$i]['division'][0];
				if ($arrdepd[$i]<>'') $this->metka=1;
			}
		
			if  ($this->metka<>0){
				$arrdepd=array_unique($arrdepd);
				sort($arrdepd);
		
				$this->lists['Divis'] = JHTML::_('select.genericlist',
					$arrdepd,
					'divis',
					'name = "divis" id = "divis" width=100 size="1"',
					'value',
					'text',
					JRequest::getVar('divis'),
					'reg');
			
				unset($zapd);
      		} else 
				if (JRequest::getVar('divis')=='') {
					$this->rezSearch .="<br>Найдено ".$q." записи";
				}
		}
		
		
		if (JRequest::getVar('divis')!=''){
			$divis=$arrdepd[JRequest::getVar('divis')];
			$divis=str_replace('\"', '"',  $divis);
			$this->rezSearch .="/<b>".$divis."</b>";
			$filter="(&(division=$divis)(!(telephoneNumber=$Uv*)))";
			$justthese = array( "ou", "sn", "cn", "company", "department", "division","telephoneNumber", "pager", "mobile", "mail", "title" );
			$sr=@ldap_search($ad,  $this->searh, $filter, $justthese);
			$q=@ldap_count_entries($ad, $sr);
 	    	$this->rezSearch .= "<br>Найдено ".$q." записи";
		}
		
		if (JRequest::getVar('full')=='1'){
			
			$this->rezSearch .="Искали все записи в справочнике";
			$filter="(&(sn=$person*)(!(telephoneNumber=$Uv*)))";
			$justthese = array("cn", "company", "department", "division","telephoneNumber", "pager", "mobile", "mail", "title" );
			$sr=@ldap_search($ad,  $this->searh, $filter, $justthese);
			$q=@ldap_count_entries($ad, $sr);
			$this->rezSearch .="<br>Найдено ".$q." записи";
			
			
		}
		if (JRequest::getVar('person')!=''){////////////////////////// введено фио
			$person=trim(JRequest::getVar('person'));
			$this->rezSearch .="Искали: ".$person;
			$filter="(&(sn=$person*)(!(telephoneNumber=$Uv*)))";
			$justthese = array("cn", "company", "department", "division","telephoneNumber", "pager", "mobile", "mail", "title" );
			$sr=@ldap_search($ad,  $this->searh, $filter, $justthese);
			$q=@ldap_count_entries($ad, $sr);
			$this->rezSearch .="<br>Найдено ".$q." записи";
		}
		
    	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		if (JRequest::getVar('excel')=='1'){
			$objPHPExcel = new PHPExcel();
			
			// Set properties
			echo date('H:i:s') . " Set properties\n";
			$objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
			$objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
			$objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
			$objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
			$objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");
			
			
			// Add some data
			echo date('H:i:s') . " Add some data\n";
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Hello');
			$objPHPExcel->getActiveSheet()->SetCellValue('B2', 'world!');
			$objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Hello');
			$objPHPExcel->getActiveSheet()->SetCellValue('D2', 'world!');
			
			// Rename sheet
			echo date('H:i:s') . " Rename sheet\n";
			$objPHPExcel->getActiveSheet()->setTitle('Simple');
			
					
			// Save Excel 2007 file
			echo date('H:i:s') . " Write to Excel2007 format\n";
			$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
			$objWriter->save('output.xls');
			
			
	
		}
		
		if ($q<>0){
			$entry = ldap_first_entry($ad, $sr);
			//   вывод всех данных из базы
			Do {
				$ar = ldap_get_attributes ($ad,  $entry);
		    	$this->rezSearch .= "<hr>";
			    $this->rezSearch .= "<br> <b>". $ar['cn'][0]."</b>";
			    $this->rezSearch .= "<br> ". $ar['company'][0];
			    if (isset($ar['department'][0])) $this->rezSearch .= "<br> ".$ar['department'][0];
		    	if (isset($ar['division'][0]))  $this->rezSearch .= "<br>". $ar['division'][0];
			    $this->rezSearch .= "<br> ". $ar['title'][0];
			    $this->rezSearch .= "<table>";
			    if ($ar['telephoneNumber'][0]!='')$this->rezSearch .= "<tr><td align=center width='10'><img src='/components/com_stalrams/assets/images/tel-rab.png'></td><td>  Телефон внутренний: </td><td><b>".$ar['telephoneNumber'][0]."</b></td></tr>";
			    if ($ar['pager'][0]!='')$this->rezSearch .= "<tr><td align=center width='10'><img src='/components/com_stalrams/assets/images/tel-rab.png'></td><td>   Телефон городской: </td><td><b>".$ar['pager'][0]."<b></td></tr>";
			    if ($ar['mobile'][0]!='') $this->rezSearch .= "<tr><td align=center width='10'><img src='/components/com_stalrams/assets/images/tel-sot.png'></td><td>  Телефон мобильный: </td><td><b>".$ar['mobile'][0]."<b></td></tr>";
			    
			    if ($ar['mail'][0]!='') $this->rezSearch .= "<tr><td align=center width='10'><img src='/components/com_stalrams/assets/images/icon-mail2.png'></td><td colspan='2'>  E-mail: <a href=mailto:". $ar['mail'][0]." >". $ar['mail'][0]."</a></td></tr>";
			    //print_r($ar);
			    $this->rezSearch .= "</table>";
    	        for ($j=1; $j<$ar['mail']["count"]; $j++){
			        if (isset($ar['mail'][$j])) {
		    	        $this->rezSearch .= ",  <a href=mailto:". $ar['mail'][$j]." class=men4u>".$ar['mail'][$j].'</a>';
		    		}
		    	}//// for j
				$entry=ldap_next_entry($ad, $entry);
			} while ($entry);
		}/////// if ($q<>0){
		ldap_unbind ($ad);
		}///////if ($r
		
		}///////if ($ad){
		parent::display($tpl);
	}
}