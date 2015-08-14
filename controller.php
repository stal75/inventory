
<?php
//Запрет к прямому обращению 
defined('_JEXEC') or die;

class StalramsController extends JControllerLegacy
{
//Возвращение способа отображения, кешируемый или нет
  public function display($cachable = false, $urlparams = false)
  {
 
 
    $view   = $this->input->get('view', 'genpass');
    $layout = $this->input->get('layout', 'default');
 
    // Проверка формы редактирования
    
     
    // $this->setRedirect(JRoute::_('index.php?option=com_stalrams&view=genpass', false));
 
// Отображаем представление
//echo 'contr-deff';
    parent::display();
//Вернуть значение
    return $this;
  }
public function genpass($cachable = false, $urlparams = false)
  {
	$this->input->set('view', 'genpass');
	parent::display();
  }
  
public function translit($cachable = false, $urlparams = false)
  {
	$this->input->set('view', 'translit');
	parent::display();
  }
  
public function zabbix($cachable = false, $urlparams = false)
  {
  	$this->input->set('view', 'zabbix');
  	parent::display();
  }
  
public function sprav($cachable = false, $urlparams = false)
  {
  	$this->input->set('view', 'sprav');
  	parent::display();
  }
public function build($cachable = false, $urlparams = false)
  {
  	$this->input->set('view', 'build');
  	parent::display();
  }
public function tm($cachable = false, $urlparams = false)
  {
  	$this->input->set('view', 'tm');
  	parent::display();
  }
public function tmsprav($cachable = false, $urlparams = false)
  {
  	$this->input->set('view', 'tmsprav');
  	parent::display();
  }
  
public function decode($cachable = false, $urlparams = false)
  {
  	$this->input->set('view', 'decode');
  	parent::display();
  }
public function equipment($cachable = false, $urlparams = false)
  {
  	$this->input->set('view', 'equipment');
  	parent::display();
  }
  
public  function getAjaxData()
  {
  	$this->input->set('view', 'po');
  	//echo 'controller';
  	parent::display();
  	
  }
    public  function getZabbixAjaxData(){
        $this->input->set('view', 'zabbix');
        //echo 'controller';
        parent::display();

    }

public  function getTMAjaxData()
  {
  	$this->input->set('view', 'tmajax');
  	//echo 'controller';
  	parent::display();
  	 
  } 

public function inventory($cachable = false, $urlparams = false)
  {
  	$this->input->set('view', 'inventory');
  	parent::display();
  }

public  function getINVAjaxData()
  {
  	$this->input->set('view', 'invajax');
  	//echo 'controller';
  	parent::display();
  
  }
  
  
}

?>