 <?php 
defined('_JEXEC') or die('Restricted access');

switch (JRequest::getVar('ajaxtype')){
	case 1:
		echo $this->lists['po'];//Список производственных отделений региона
		break;
	case 2:
		echo $this->lists['ObjectPO'];//Список объектов ПО по типу объекта
		echo "<span style='display:none'>".$this->lists['idpo']."</span>";//Список объектов ПО по типу объекта c id
		break;
	case 3:
		echo $this->lists['appar'];//Получаем список аппаратных на объекте
		break;		
	case 4:
		echo $this->msg;//Получаем список аппаратных на объекте
		break;
	case 5:
		echo $this->msg1;//Получаем список аппаратных на объекте
		break;
	case 101:
		echo $this->text;
		break;
	case log:
		echo $this->log;
		break;
}