 <?php 
defined('_JEXEC') or die('Restricted access');?>
 
 <link rel="stylesheet" type="text/css" media="screen" href="components/com_stalrams/css/jquery-ui.css" />
 
<table>
  <tr>
    <td><h2>Шифратор - </h2></td>
    <td><h2><a href="/index.php?option=com_stalrams&view=equipment&task=decode&Itemid=593">Дешифратор</a></h2></td>
  </tr></td>
</table>

<table>
  <tr>
	<td style="height:70px"><b>Код оборудования: </b></td>
	<td><input name="encod" type="text" size="10" id="encod" onKeyUp = "kodd()"></td>
	<td> <button data-clipboard-target="encod" id="target-to-copy" alt="Должен быть разрешен flash!">Скопировать</button></td>
	<td align ="center" width="100"><span id="typeimg"></span>   </td>
	<td> <span id="msg"></span><br><span id="msg1"></span></td>
  </tr>
  <tr>
  	<td style="height:60px" valign="top"><b>Категория: </b></td>
  	<td valign="top"><b><span id="category"></span></b></td>
  	<td></td>
  	<td></td>
  	<td></td>
  </tr>
</table>
<p>Регион: <?php echo $this->lists['regions'];?></p>
<table>
  <tr>
    <th>Производственное отделение: <span id="ponum"></span></th>
    <th>Тип объекта ПО</th>
    <th>Объект: <span id="objectnum"></span></th>
    <th>Оборудование</th>
    <th>Тип помещения</th>
  </tr>
  <tr>
	<td valign="top"><span id="bases"><select id="base" name="base" class="inputbox" size="20" onclick="base1()"></select></span></td>
	<td valign="top"><?php echo $this->lists['Base'];?></td>
	<td valign="top"><span id="ObjectPO"><select id="objectpo" name="objectpo" class="inputbox" size="20" onclick="encod()"></select></span></td>
	<td valign="top"><?php echo $this->lists['tipes'];?></td>
    <td><table><tr><th><b></b></th><tr>
      <tr><td><input type="radio" name="apparatn" value="a" id="appar1" onClick = "Appcode()"> Аппаратная</td><td><span id="app"><select id="appar" name="base" class="inputbox" size="5" "></span></td></tr>
      <tr><td><input type="radio" name="apparatn" value="k" id="appar2" onClick = "encod()"> Комната</td><td><input name="kom" type="text" size="3" id="kom" onKeyUp = "encod()"></td></tr>
      <tr><td><input type="radio" name="apparatn" value="f" id="appar3" onClick = "encod()"> Коридор на этаже</td><td><?php echo $this->lists['pom'];?></td></tr>
      <tr><td><input type="radio" name="apparatn" value="" id="appar4" onClick = "encod()"> Отсутствует<br><br><br></td><td></td></tr>
      <tr><td><b>Номер стойки: </b><br><br></td><td><?php echo $this->lists['stoika'];?></td></tr>
      <tr><td><b>Номер оборудования: </b></td><td><?php echo $this->lists['obnom'];?></td></tr>
    </table><br></td> 
  </tr>
  <tr>
  	<td></td>
  	<td></td>
  	<td><button onclick="obbase(1)">Оборудование на объекте</button></td>
  	<td><button onclick="obbase(3)">Оборудование по типу</button></td>
  	<td><button onclick="obbase(2)">Оборудование в помещении</button></td>
</table>
<br>
<p>Поиск по шаблону: <input id="neqname"></input><button onclick="obbase(4)">Поиск</button>
<span id="obobj"></span>
<span id="test" style="display:none"><?php echo $this->lists['imgcat']?></span>
<p></p>
