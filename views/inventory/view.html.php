<?php
defined('_JEXEC') or die;

// import Joomla view library
jimport('joomla.application.component.view');

//JHtml::_('jquery.framework');

$document = &JFactory::getDocument();
$baseurl = JURI::base();
$document->addScript($baseurl . "components/com_stalrams/js/jquery.min.js");
$document->addScript($baseurl . "components/com_stalrams/js/jquery-ui.min.js");
$document->addScript($baseurl . "components/com_stalrams/js/ui.multiselect.js");
$document->addScript($baseurl . "components/com_stalrams/js/i18n/grid.locale-ru.js");
$document->addScript($baseurl . "components/com_stalrams/js/jquery.jqGrid.min.js");
$document->addScript($baseurl . "components/com_stalrams/js/grid.custom.js");
//$document->addScript($baseurl . "components/com_stalrams/js/context-menu.js");

//$document->addScript($baseurl . "components/com_stalrams/js/jquery.jqGrid.src.js");



/*  $document->addScript("//blueimp.github.io/JavaScript-Templates/js/tmpl.min.js");
$document->addScript("//blueimp.github.io/JavaScript-Load-Image/js/load-image.all.min.js");
$document->addScript("//blueimp.github.io/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js");
$document->addScript("//blueimp.github.io/Gallery/js/jquery.blueimp-gallery.min.js");

$document->addScript($baseurl . "components/com_stalrams/js/jquery.iframe-transport.js");
$document->addScript($baseurl . "components/com_stalrams/js/jquery.fileupload-process.js");
$document->addScript($baseurl . "components/com_stalrams/js/jquery.fileupload-image.js");
$document->addScript($baseurl . "components/com_stalrams/js/jquery.fileupload-audio.js");
$document->addScript($baseurl . "components/com_stalrams/js/jquery.fileupload-video.js");
$document->addScript($baseurl . "components/com_stalrams/js/jquery.fileupload-validate.js");
$document->addScript($baseurl . "components/com_stalrams/js/jquery.fileupload-ui.js");
$document->addScript($baseurl . "components/com_stalrams/js/jquery.fileupload-jquery-ui.js"); */

$document->addScript("http://api-maps.yandex.ru/2.1/?lang=ru_RU");

$document->addScript($baseurl . "components/com_stalrams/js/chosen.jquery.min.js");
$document->addScript($baseurl . "components/com_stalrams/js/jquery-ui-timepicker-addon.js");


$document->addScript($baseurl . "components/com_stalrams/js/libqrencode-latest.min.js");// Генерация QR кода

$document->addScript($baseurl . "components/com_stalrams/js/vendor/jquery.ui.widget.js");
$document->addScript($baseurl . "components/com_stalrams/js/jquery.iframe-transport.js");
$document->addScript($baseurl . "components/com_stalrams/js/jquery.fileupload.js");

$document->addScript($baseurl . "components/com_stalrams/js/ohsnap.js");//Всплывающие уведомления
$document->addScript($baseurl . "components/com_stalrams/js/messagebox.min.js");//Замена стандартных алертов 

$document->addScript($baseurl . "components/com_stalrams/js/main.js");

$document->addScript($baseurl . "components/com_stalrams/js/jquery.validate.min.js");//Проверка форм

$document->addScript($baseurl . "components/com_stalrams/js/var.js");//Инициализация переменных
$document->addScript($baseurl . "components/com_stalrams/js/inv.js");
$document->addScript($baseurl . "components/com_stalrams/js/init.js");
$document->addScript($baseurl . "components/com_stalrams/js/tablesinit.js");//Инициализация таблиц



class StalramsViewInventory extends JViewLegacy
{
public $lists = array();

	function display ($tpl = null)
	{
	
	$model = $this->getModel();
		$rList = $this->get('AccessUser');
		
		$this->logo = '<div><b>'.$rList[0]->name.'</b></div>';
		$this->topmenu .= '<div id="radio">';
		$this->topmenu .= '<input type="radio" id="dashboard" name="radio" onclick="dashboard()" checked="checked"><label for="dashboard">Панель</label>';
		$this->topmenu .= '<input type="radio" id="inventory" name="radio" onclick="inventory()" checked="checked"><label for="inventory">Инвентаризация</label>';
		$this->topmenu .= '<input type="radio" id="docs" name="radio" onclick="docs()"><label for="docs">Документы</label>';
 		$this->topmenu .= '<input type="radio" id="stores" name="radio"><label for="stores">Склад</label>';
		if ($rList[0]->admin == 1){
			$this->topmenu .= '<input type="radio" id="radio3" name="radio" onclick="config()"><label for="radio3">Настройка</label>';
			$this->topmenu .= '<input type="radio" id="radio4" name="radio" onclick="invadmin()"><label for="radio4">Администрирование</label>';
		};
		$this->topmenu .= '</div>';
	
	parent::display($tpl);
	
	}

}

?>