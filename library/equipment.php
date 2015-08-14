<?php
class Equipment{
	public $region;	//регион
	public $base;	//база
	public $shkaf;	//номер шкафа
	public $tip;	//тип оборудования
	public $nomer;	//номер оборудования в шкафу
	public $mysqli;
	
	function __construct($host, $user, $password, $db){
		$this->mysqli = @new mysqli($host, $user, $password, $db);
		if (mysqli_connect_errno()){
			echo '<font color="red">Ошибка подключения к базе данных! Причина: </font>'.mysqli_connect_error();
			exit();
		}
	}
	
	//Проверка региона
	public function checkRegion($region){
		 $sql = "SELECT opisanie FROM region WHERE name = '$region'";
		 $result_set = $this->mysqli->query($sql);
		 $row = $result_set->fetch_assoc();
		 $result_set->close();
		 if ($row['opisanie'] != '') return $row['opisanie'];
		 else return 'Регион не определен';
	}
	function checkBase($base){
		$sql = "SELECT opisanie FROM base WHERE name = '$base'";
		$result_set = $this->mysqli->query($sql);
		$row = $result_set->fetch_assoc();
		$result_set->close();
		if ($row['opisanie'] != '') return $row['opisanie'];
		else return 'Объект не определен';
	}
	function checkPS($ps){
		$sql = "SELECT opisanie FROM ps WHERE name = '$ps'";
		$result_set = $this->mysqli->query($sql);
		$row = $result_set->fetch_assoc();
		$result_set->close();
		if ($row['opisanie'] != '') return $row['opisanie'];
		else return 'Подстанция не определена';
	}
	function checkTip($tip){
		$sql = "SELECT opisanie FROM tipe WHERE name = '$tip'";
		$result_set = $this->mysqli->query($sql);
		$row = $result_set->fetch_assoc();
		$result_set->close();
		if ($row['opisanie'] != '') return $row['opisanie'];
		else return 'Тип оборудования не определен';
	}
	
	//Разбор именования оборудования
	function razborEq($str){
		$str = strtolower($str);// Переводим все символы в нижний регистр
		$arr = explode("-", $str);//Помещаем каждый код в свой элемент массива
		list($region, $base, $appar, $shkaf, $tip) = $arr;// Каждый элемент массива в свою переменную
		$rez=$this->checkRegion($region).'<br>';// Проверяем регион
		if ($base{0} == 'p') $rez=$rez.$this->checkPS($base).'<br>';//Если первая буква базового объекта 'p' это подстанция проверяем подстанцию
		else $rez=$rez.$this->checkBase($base).'<br>';//Иначе проверяем базовый объект
		if (($appar != '0') or ($appar != '00'))// Проверяем есть ли аппаратная
			if ($appar{0} == 'a') $rez=$rez.'Аппаратная '.$appar.'<br>';//если аппрататная есть и первая буква "а" это аппаратная
		    else $rez=$rez.'Комната '.$appar.'<br>'; //иначе это комната
		else $rez=$rez.'Отсутствует комната или аппаратная <br>';
		$rez=$rez.'Шкаф: '.$shkaf.'<br>';
		$n = strcspn($tip, '1234567890'); //Отделяем тип оборудования от номера оборудования
		$l = strlen($tip);
		$rez=$rez.$this->checkTip(substr($tip, 0, $n)).substr($tip, $n).'<br>'; //Выводим тип оборудования и номер оборудования в стойке
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
		$sql = "CREATE TABLE IF NOT EXISTS `region` (
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
	
}
?>