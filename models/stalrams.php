<?php

//������ �� ������� ��������� � �������
defined('_JEXEC') or die;


//�������� ������ ������ � �������� �����
class StalramsModelStalrams extends JModelLegacy
{
//������� � �� ����� ������� � ����.
function getStalram()
	{
	//����������� � �� joomla
	$db = $this->getDbo();
	
	//�������� �� ����� ������� ����� ����������� ������ ORDER BY ordering ��� ������� ����������� ������ ���� ������� � ����� ������.
	$query = 'SELECT * FROM #__stalrams ORDER BY ordering';
	$db->setQuery($query);
	$row = $db->loadObjectlist();
//������� row	
return $row;	
	
	}
	
}
?>