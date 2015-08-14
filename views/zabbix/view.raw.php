<?php

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class StalramsViewZabbix extends JViewLegacy
{
	function display($tpl = null)
	{
		$model = $this->getModel();
		
		switch (JRequest::getVar('ajaxtype')){
			case zabbiximport:
				$this->msg = $this->get('ZabbixImport');//Загрузка данных  из zabbix
				break;
            case zabbixtemplate:
               //$this->msg = $this->get('ZabbixTemplate');//Загрузка доступных шаблонов  из zabbix
                $rList = $this->get('ZabbixTemplate');//Получить виды селекта
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