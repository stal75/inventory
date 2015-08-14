<?php
defined('_JEXEC') or die;


$document = &JFactory::getDocument();

jimport('joomla.application.component.view');


class StalramsViewZabbix extends JViewLegacy
{
public $lists = array();

    /**
     * Функция вывода нужной AJAX функции
     * @param null $tpl
     */
    function display ($tpl = null){
	
        $model = $this->getModel();

        switch (JRequest::getVar('ajaxtype')){
            case Template:
                $this->msg = $this->get('ZabbixTemplate');//Получаем список шаблонов
                break;
            case zabbiximport:
                $this->msg = $this->get('ZabbixImport');//Загрузка данных из LD
                break;
            case massadd:
                try {

                    $api = new ZabbixApi('https://dc-zabbix.dc.mrsk-cp.net/api_jsonrpc.php', 'API', 'Y#46?&otXt&v');
                    //$api = new ZabbixApi('http://10.71.6.46/api_jsonrpc.php', 'API', 'V!PVw8N!uh1u');

                    $db = JFactory::getDBO();// Подключаемся к базе.
                    $query = "SELECT  #__neq_zabbix.* FROM #__neq_zabbix";//Определяем запрос
                    $db->setQuery($query);//Выполняем запрос
                    $rList = $db->loadObjectList();
                    $i=0;
                    foreach ($rList as $row){

                        $group = $model->group($row->host, 1);//Получаем название группы оборудования
                        $obj = $api->hostgroupGet(array('output' => 'extend', 'filter' => array('name' => $group)));
                        $groupid = $obj['0']->groupid;

                        $to = $model->group($row->host, 0);
                        echo $row->host.'       '.$to.'  ';

                        if ($groupid == null) echo "Группа не определена для объекта <br>";
                        else echo $group.'     ';


                        $interfaces = array ('type' => '1', 'main' => '1', 'useip' => '1', 'ip' => $row->ip, 'dns' => '', 'port' => '10050');
                        $group = array ('groupid' => '5');
                        $inventory = array ('type' => $to, 'vendor' => $row->vendor, 'model' => $row->inv_name, 'poc_1_name' => $row->contact, 'poc_1_phone_a' => $row->contact_number, 'notes' => $row->description, 'location' => $row->location);
                        $templates = array ('1' => array ('templateid' => '13125'));

                        $host = array ('host' => $row->host, 'name' => $row->name, 'interfaces' => $interfaces, 'groups' => array('1' => array('groupid'=> $groupid)), 'templates' => $templates, "inventory" => $inventory);

                        try {
                            $result=$api->hostCreate($host);
                            if ($result){$i++; echo '   Хост добавлен  '.$i.'<br>'; }
                            else echo $result.'<br>';
                        } catch(Exception $e) {
                            echo $e->getMessage().'<br>';
                        }
                    }



                } catch(Exception $e) {
                    echo $e->getMessage();
                }
                break;

        }
    parent::display($tpl);
    }

}

?>