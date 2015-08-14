 <?php 
defined('_JEXEC') or die('Restricted access');

switch (JRequest::getVar('ajaxtype')){
	case 1:
		echo $this->msg;
		//echo 'hello';
		break;
	case 2:
		echo $this->msg;
		break;
}