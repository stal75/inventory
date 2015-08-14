<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
JHtml::_('jquery.framework');

$document = &JFactory::getDocument();
$baseurl = JURI::base();
$document->addScript($baseurl . "components/com_stalrams/js/tmsprav.js");
$document->addScript($baseurl . "components/com_stalrams/js/jquery.jqGrid.min.js");

$document->addScript($baseurl . "components/com_stalrams/js/i18n/grid.locale-ru.js");


// <!-- A link to a jQuery UI ThemeRoller theme, more than 22 built-in and many more custom -->
//    <link rel="stylesheet" type="text/css" media="screen" href="../../../css/jquery-ui.css" />

class StalramsViewTmsprav extends JViewLegacy
{
	public $lists = array();
	
	// Overwriting JView display method
	function display($tpl = null) 
	{		
		$model = $this->getModel();//Получение модели
		
		$rList = $this->get('Regions');//Получение списка регионов
		$this->regions = $rList;
	  	$i=1;
	  	$this->tabs = '';
   		foreach($this->regions as $region){
	  		$this->tabs .= '<input type="radio" name="tabs" id="tab-nav-'.$i.'" checked>';
  			$this->tabs .= '<label for="tab-nav-'.$i.'">'.$region->text.'</label>';
  			$i += 1;
   		}
		
		
   		$this->tabstext = '';
   		foreach($this->regions as $region){
   			//$this->region = $region->value;

   			$model->setState('region', $region->value);
   			
   			$rList = $this->get('PO');
   			$msg = JHTML::_('select.genericlist',
				$rList,
				'base'.$region->value,
				'class="inputbox" size="20" onClick = "base('.$region->value.')"',
				'value',
				'text',
				0,
				'base'.$region->value);
   			$this->tabstext .= '<div><table>
   										<thead>
											<tr>
												<th width="250">ПЭС</th>
												<th width="250">Подстанции</th>
												<th width="250">Источники</th>
   												<th width="250">Сигналы</th>
											</tr>
										</thead>
										<tbody>
   											<tr>
												<td>'.$msg.'</td>
												<td><span id="text'.$region->value.'"></span></td>
												<td></td>
												<td></td>
											</tr>
										</tbody>
									</table></div>';
   		}
   		   		
		parent::display($tpl);
	}
}