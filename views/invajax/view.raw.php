<?php

defined('_JEXEC') or die;

jimport('joomla.application.component.view');
jimport('phpexcel.library.PHPExcel');

class StalramsViewInvajax extends JViewLegacy
{
	function display($tpl = null)
	{
		$model = $this->getModel();
		
		switch (JRequest::getVar('ajaxtype')){
			case regions:				
				if (JRequest::getVar('oper') == 'selacl'){
					$rList = $this->get('Regions');//Получить селект
					$selid = JRequest::getVar('selid');
					$func = JRequest::getVar('func');
					if ($func != '') $func = 'onClick = "'.$func.'()"';
					$this->msg = JHTML::_('select.genericlist',
							$rList,
							$selid,
							'class="inputbox" '.'id="'.$selid.'" size="1" '.$func,
							'id',
							'value',
							0,
							$selid);
					//$this->msg = $rList;
				}else $this->msg = $this->get('Regions');// Получаем таблицу регионов
				
				break;
			case pesedit:
				$this->msg = $this->get('PesEdit');// Изменяем таблицу производственных отделений
				break;
			case objecttype:
				$this->msg = $this->get('ObjectType');// Получаем типов объектов
				break;
			case objtypeedit:
				$this->msg = $this->get('ObjTypeEdit');// Изменяем таблицу типов объектов
				break;
			case objectsedit:
				$this->msg = $this->get('ObjectsEdit');// Редактируем таблицу объектов
				break;
			case roomedit:
				$this->msg = $this->get('RoomEdit');// Редактируем таблицу объектов
				break;
			case select:
				$this->msg = $this->get('Select');// Получаем таблицу объектов
				break;
			case equipment:
				$this->msg = $this->get('Equipment');// Получаем таблицу объектов
				break;
			case categoryedit:
				$this->msg = $this->get('CategoryEdit');// Редактируем таблицу объектов
				break;
			case accessedit:
				$this->msg = $this->get('AccessEdit');//Получаем права пользователей				
				break;
			case selectusers:
				$this->msg = $this->get('SelectUsers');//Получение списка пользователь
				break;
			case accessselect:
				$this->msg = $this->get('AccessSelect');//Список ПЭС к которым есть доступ
				break;
			case ldapimport:
				$this->msg = $this->get('LDAPImport');//Список ПЭС к которым есть доступ
				break;
			case docs:
				$this->msg = $this->get('Docs');//Список ПЭС к которым есть доступ
				break;
			case docdir:
				$this->msg = $this->get('DocDir');//Создать каталог на сервере
				break;
			case adusers:
				$this->msg = $this->get('AdUsers');//Редактирование таблицы содтрудников
				break;
			case adpc:
				$this->msg = $this->get('AdPC');//Редактирование таблицы содтрудников
				break;
			case stores:
				$this->msg = $this->get('Stores');//Редактирование таблицы складов
				break;
			case autocomplete:
				$this->msg = $this->get('AutoComplete');//Список автозаполнения
				break;
			case ocsautocomplete:
				$this->msg = $this->get('OCSAutoComplete');//Список автозаполнения
				break;
			case prnautocomplete:
				$this->msg = $this->get('PRNAutoComplete');//Список автозаполнения
				break;
			case ocsimport:
				$this->msg = $this->get('OCSImport');//Список автозаполнения
				break;
			case nametoid:
				$this->msg = $this->get('NameToID');//Преобразование имени в ID
				break;
			case zabbix:
				$this->msg = $this->get('Zabbix');//Работа с таблицей zabbix
				break;
			case ocs:
				$this->msg = $this->get('OCS');//Работа с таблицей ocs
				break;	
			case objects:
				$this->msg = $this->get('Objects');//Работа с таблицей объектов инвентаризации
				break;
			case respuser:
				$this->msg = $this->get('RespUser');//Работа с таблицей Мат. ответственных
				break;
			case computer:
				$this->msg = $this->get('Computer');//Информация по компьютеру
				break;
			case monitor:
				$this->msg = $this->get('Monitor');//Информация по мониторам
				break;
			case prn:
				$this->msg = $this->get('Prn');//Информация по МФУ принтерам
				break;
			case carteridgeforprn:
				$this->msg = $this->get('CarteridgeForPrn');//Соответствие принтеров и кратриджей
				break;
			case compconfsync:
				$this->msg = $this->get('CompConfSync');//Синхронизация параметров компьютера
				break;
			case location:
				$this->msg = $this->get('Location');//Перемещение
				break;
			case userchange:
				$this->msg = $this->get('UserChange');//Перемещение
				break;
			case respuserchange:
				$this->msg = $this->get('RespUserChange');//Перемещение
				break;
			case compconfigchange:
				$this->msg = $this->get('CompConfigChange');//Перемещение
				break;
			case journal:
				$this->msg = $this->get('Journal');//Журналирование
				break;
			case prnhistory:
				$this->msg = $this->get('PrnHistory');//История печати
				break;
			case supplier:
				$this->msg = $this->get('Supplier');//Поставщики
				break;
			case vendor:
				$this->msg = $this->get('Vendor');//Производители
				break;
			case cartridge:
				$this->msg = $this->get('Cartridge');//Картриджи
				break;
			case sup:
				$this->msg = $this->get('Sup');//Картриджи
				break;
			case objparts:
				$this->msg = $this->get('ObjParts');//Подобъекты объекта
				break;
			case objdocs:
				$this->msg = $this->get('ObjDocs');//Документы объекта
				break;
			case prntemplate:
				$this->msg = $this->get('PrnTemplate');//Шаблоны принтеров
				break;
			case suptype:
				$this->msg = $this->get('SupType');//Типы расходников
				break;
			case supplies:
				$this->msg = $this->get('Supplies');//Выбор из расходников
				break;
			case typebyid:
				$this->msg = $this->get('TypeByID');//Получаем Тип объекта из ID
				break;
			case storparts:
				$this->msg = $this->get('StorParts');//Оборудование на складе
				break;
			case debit:
				$this->msg = $this->get('Debit');//Списанные расходные материалы
				break;
			case excel:
				$this->msg = $this->get('Excel');//Ссылка на файл
				break;
			case selectunits:
				$rList = $this->get('SelectUnits');//Получить виды селекта
				$selid = JRequest::getVar('selid');
				$func = JRequest::getVar('func');
				if ($func != '') $func = 'onClick = "'.$func.'()"';
				$this->msg = JHTML::_('select.genericlist',
						$rList,
						$selid,
						'class="inputbox" '.'id="'.$selid.'" size="1" '.$func,
						'id',
						'value',
						0,
						$selid);
				break;
		}
		
		parent::display($tpl);
		
	}

}