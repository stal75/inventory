<?php

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class StalramsViewPo extends JViewLegacy
{
	function display($tpl = null)
	{
		$model = $this->getModel();
		
		switch (JRequest::getVar('ajaxtype')){
			case 1:
				$rList = $this->get('PO');//Получение списка базовых объектов
				$this->lists['po'] = JHTML::_('select.genericlist',
				$rList,
				'base',
				'class="inputbox" size="20" onClick = "base1()"',
				'value',
				'text',
				0,
				'base');
				break;
			case 2:
				$rList = $this->get('ObjectPO');//Получение списка Объектов в ПО
				$this->lists['ObjectPO'] = JHTML::_('select.genericlist',
				$rList,
				'objectpo',
				'class="inputbox" size="20" onClick = "Appcode()"',
				'value',
				'text',
				0,
				'objectpo');
				$this->lists['idpo'] = JHTML::_('select.genericlist',//Получение списка базовых объектов c ID
				$rList,
				'idpo',
				'class="inputbox" size="20" onClick = "base2()"',
				'id',
				'text',
				0,
				'idpo');
				break;
			case 3:
				$rList = $this->get('Appar');
				$this->lists['appar'] =JHTML::_('select.genericlist',
				$rList,
				'appar',
				'class="inputbox" size="5" onClick = "encod()" onkeyup = "encod()"',
				'value',
				'name',
				0,
				'appar');
				break;
			case 4:
				$this->msg = $this->get('Zabbix');;
				break;
			case 5:
				$this->msg1 = $this->get('ZabbixObjects');
				break;
			case log:
				$this->log = $this->get('Log');
				break;
			case zabbiximport:
				$this->msg = $this->get('ZabbixImport');//Загрузка данных из LD
				break;
		}
		
		
		
	
		


		
		parent::display($tpl);
		
	}

}