<?php

//������ �� ������� ��������� � �������
defined('_JEXEC') or die;

class StalramsViewStalrams extends JViewLegacy
{
//������� ������� ������� ������ �� ������. function getStalram() ��� ������� ������, � ��� �� ��������� � $rows = $model->getStalram();
function display ($tpl = null)
	{
	$model = $this->getModel();
	$rows = $model->getStalram();
	$this->assignRef('rows',$rows);
	
parent::display($tp1);
	}


}

?>