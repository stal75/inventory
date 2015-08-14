<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
JHtml::_('jquery.framework');


$document = &JFactory::getDocument();
$baseurl = JURI::base();
$document->addScript($baseurl . "components/com_stalrams/js/encod.js");
$document->addScript($baseurl . "components/com_stalrams/js/ZeroClipboard.js");
$document->addScript($baseurl . "components/com_stalrams/js/jquery-ui.js");

$document->addScript($baseurl . "components/com_stalrams/js/jquery.jqGrid.min.js");
//<!-- This is the localization file of the grid controlling messages, labels, etc. -->
//<!-- We support more than 40 localizations -->
$document->addScript($baseurl . "components/com_stalrams/js/i18n/grid.locale-ru.js");

 class StalramsViewBuild extends JViewLegacy
{
	public $lists = array();
	
	// Overwriting JView display method
	function display($tpl = null) 
	{
		
		$model = $this->getModel();//Получение модели
		
		$rList = $this->get('Regions');//Получение списка регионов
		$this->lists['regions'] = JHTML::_('select.genericlist',
				   $rList,
				   'reg',
				   'id = "reg" width=100 size="1" onClick = "reg()"',
				   'value',
				   'text',
					0,
				    'reg');
	
		$rList = $this->get('Bases');//Получение списка базовых объектов
		$this->lists['bases'] = JHTML::_('select.genericlist',
				$rList,
				'base',
				'class="inputbox" size="20" onClick = "base1()"',
				'value',
				'text',
				0,
				'base');
				
		$rList = $this->get('Base');//Получение списка базовых объектов
		$this->lists['Base'] = JHTML::_('select.genericlist',
				$rList,
				'ps',
				'class="inputbox" size="20" onClick = "ps1()"',
				'value',
				'text',
				0,
				'ps');
		
		$rTypes = $this->get('Types');//Получение списка базовых объектов
		$this->lists['tipes'] = JHTML::_('select.genericlist',
				$rTypes,
				'tipe',
				'class="inputbox" size="20" onClick = "typecl()" onkeyup = "typecl()"',
				'value',
				'text',
				0,
				'tipe');
		$this->lists['imgcat'] = JHTML::_('select.genericlist',
				$rTypes,
				'cat',
				'class="inputbox" size="20" onClick = "cat()"',
				'img',
				'category',
				0,
				'cat');
		$rList = $this->get('Po');//Получение списка базовых объектов
		$this->lists['po'] = JHTML::_('select.genericlist',
				$rList,
				'tipe',
				'class="inputbox" size="20" onClick = "Po()"',
				'value',
				'text',
				0,
				'po');
		
		//$this->lists['appar'] = JHTML::_('select.integerlist', 1, 20, 1, 'appar', 'id = "appar" onClick = "encod()"', $selected = null, $format = "%d");
		$this->lists['appar'] =JHTML::_('select.genericlist',
				null,
				'tipe',
				'class="inputbox" size="5" onClick = "encod()"',
				'value',
				'text',
				0,
				'po');
		
		$this->lists['pom'] = JHTML::_('select.integerlist', -3, 10, 1, 'appar', 'id = "pom" onClick = "encod()"', $selected = 1, $format = "%d");
		$this->lists['stoika'] = JHTML::_('select.integerlist', 0, 99, 1, 'stoika','id = "stoika" onClick = "encod()"', $selected = null, $format = "%d");
		$this->lists['obnom'] = JHTML::_('select.integerlist', 1, 300, 1, 'obnom', 'id = "obnom" onClick = "encod()"', $selected = null, $format = "%d");
		//$this->test = $rTypes;
		
		parent::display($tpl);
	}
}