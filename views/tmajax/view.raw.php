<?php

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class StalramsViewTmajax extends JViewLegacy
{
	function display($tpl = null)
	{
		$model = $this->getModel();
		
		switch (JRequest::getVar('ajaxtype')){
			case 1:
				$rList = $this->get('PS');//Получение списка базовых объектов
				$this->msg = 'Подстанций : '.count($rList);//Строим таблицу подстанций
				$this->msg .= '<div style = "width: 200px; overflow: auto; height: 200px; background-color: white;">';
				$this->msg .= '<table>
						<thead>
							<tr>
								<th width="50"></th>
								<th width="200">Название</th>
								<th width="50">Номер</th>
							</tr>
						</thead>
						<tbody>';
				foreach($rList as $ps){
					$this->msg .= '<tr>
										<td><input type="radio" name="name" /></td>
										<td>' .$ps->name .'</td>
										<td>'. $ps->number .'</td>
									</tr>';
				}
				$this->msg .= '</tbody></table><button onclick="psadd()">+</button><button onclick="psdel()">-</button><button onclick="psedit()">...</button><div>';
				$this->msg;
				break;
			case 2:
				$this->msg = $this->get('PS');//Получение списка базовых объектов
				break;			
		}
		
		parent::display($tpl);
		
	}

}