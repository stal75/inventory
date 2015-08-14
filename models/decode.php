<?php
defined('_JEXEC') or die('Restricted access');
// import Joomla modelitem library
jimport('joomla.application.component.modelitem');

/**
 * HelloWorld Model
*/
class StalramsModelDecode extends JModelLegacy
{
	
	protected $msg;
 
	function mod_name(){
		return 'Модель Разбора';
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
	function razborEq_spec($str){
		if ($str == ''){
			$rez = '<font color="red">Введите наименование оборудования!</font color>';
		}
		else{	
			
			$str = strtolower($str);// Переводим все символы в нижний регистр
			$arr = explode("-", $str);//Помещаем каждый код в свой элемент массива
			list($region, $base, $appar, $shkaf, $typ) = $arr;// Каждый элемент массива в свою переменную
			$rez = '<div id=vivod>';
			$rez.='Регион: '.$this->checkRegion($region);// Проверяем регион
			$rez.= chr(13);
			$rez.='Производственное отделение: '.$this->checkPO($region);// Проверяем регион
			$rez.= chr(13);
			$rez.='Объект: '.$this->checkBase($base{0});//Проверяем тип объекта
			$rez.= chr(13);
			$rez.='Название объекта: '.$this->checkObjectName($region, $base{0}, 0+substr($base, 1));//Проверяем тип объекта
			if (($appar != '0') or ($appar != '00')){// Проверяем есть ли аппаратная
				if ($appar{0} == 'a') $rez .='Аппаратная: '.$this->checkAppar(0+substr($appar, 1), $region, $base{0}, 0+substr($base, 1));//если аппрататная есть и первая буква "а" это аппаратная
				if ($appar{0} == 'k') $rez .='Комната: '.$appar; //если аппаратная есть и первая буква "k" это комната
				if ($appar{0} == 'f') $rez .='Коридор или этаж: '.$appar; //если аппаратная есть и первая буква "f" это коидор
			}else $rez.='Отсутствует комната или аппаратная ';
			$rez.= chr(13);
			$rez .= 'Шкаф: '.'№ '.$shkaf;
			$rez.= chr(13);
			//echo $typ;
			$n = strcspn($typ, '1234567890'); //Отделяем тип оборудования от номера оборудования
			$l = strlen($typ);
			$rez .= 'Тип оборудования: '.$this->checkType(substr($typ, 0, $n)).' № '.substr($typ, $n); //Выводим тип оборудования и номер оборудования в стойке
			$rez.= chr(13);
			$rez .= 'Категория: '.$region.'-'.$this->checkCategory(substr($typ, 0, $n)).'</div>';
			$rez.= chr(13);
		}
		return $rez;
	}
	
	//Создание БД и таблиц в БД
	function createBase($host, $user, $password, $db){
		$mysqli = @new mysqli($host, $user, $password);
		if (mysqli_connect_errno()){
			echo '<font color="red">Ошибка подключения к базе данных! Причина: </font>'.mysqli_connect_error();
			exit();
		}
		$sql = "CREeATE DATABASE IF NOT EXISTS `equipments` ";
		if ($mysqli->query($sql)) $rez=$rez.'База данных создана<br>';
		else {
			$rez = '<font color="red">Ошибка создания базы данных</font><br>';
			return $rez;
		}
	
		$mysqli = @new mysqli($host, $user, $password, $db);
		if (mysqli_connect_errno()){
			echo '<font color="red">Ошибка подключения к базе данных! Причина: </font>'.mysqli_connect_error();
			exit();
		}
		$sql = "CREATE TABLE IF NOT EXISTS `#__neq_region` (
  				`id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
 				`name` varchar(20) NOT NULL,
  				`opisanie` varchar(250) NOT NULL,
  				PRIMARY KEY (`id`),
  				UNIQUE KEY `name` (`name`),
  				UNIQUE KEY `id` (`id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		if ($mysqli->query($sql)) $rez=$rez.'Таблица регионов создана<br>';
		else {
			$rez = '<font color="red">Ошибка создания таблицы регионов</font><br>';
			return $rez;
		}
		$sql = "CREATE TABLE IF NOT EXISTS `ps` (
  				`id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
 				`name` varchar(20) NOT NULL,
  				`opisanie` varchar(250) NOT NULL,
  				PRIMARY KEY (`id`),
  				UNIQUE KEY `name` (`name`),
  				UNIQUE KEY `id` (`id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		if ($mysqli->query($sql)) $rez=$rez.'Таблица подстанций создана<br>';
		else {
			$rez = '<font color="red">Ошибка создания таблицы подстанций</font><br>';
			return $rez;
		}
		$sql = "CREATE TABLE IF NOT EXISTS `base` (
  				`id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
 				`name` varchar(20) NOT NULL,
  				`opisanie` varchar(250) NOT NULL,
  				PRIMARY KEY (`id`),
  				UNIQUE KEY `name` (`name`),
  				UNIQUE KEY `id` (`id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		if ($mysqli->query($sql)) $rez=$rez.'Таблица базовых объектов создана<br>';
		else {
			$rez = '<font color="red">Ошибка создания таблицы базовых объектов</font><br>';
			return $rez;
		}
		$sql = "CREATE TABLE IF NOT EXISTS `tipe` (
  				`id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
 				`name` varchar(20) NOT NULL,
  				`opisanie` varchar(250) NOT NULL,
  				PRIMARY KEY (`id`),
  				UNIQUE KEY `name` (`name`),
  				UNIQUE KEY `id` (`id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		if ($mysqli->query($sql)) $rez=$rez.'Таблица типов оборудования создана<br>';
		else {
			$rez = '<font color="red">Ошибка создания таблицы типов оборудования</font><br>';
			return $rez;
		}
		$mysqli->close();
		return $rez;
	}
	
	//Добавление данных таблицу регионов
	function add_region($name, $opisanie){
		if ($this->mysqli->query("INSERT INTO REGION(name, opisanie) values('$name', '$opisanie')")) return 'Регион добавлен успешно';
		else return '<font color="red">Ошибка добавления региона</font><br>';
	}
	//������� ��������� �������
	function edit_region($id){
		$mysqli = base_con();
	}
	function razbor(){
	
	}
	
	public function getTable($type = 'Equipment', $prefix = 'EquipmentTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}

}
