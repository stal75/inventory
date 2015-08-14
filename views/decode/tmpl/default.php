<?php defined('_JEXEC') or die('Access Restricted'); ?>

<table>
  <tr>
    <td><h2><a href="/index.php?option=com_stalrams&view=equipment&task=build&Itemid=592">Шифратор</a></h2></td>
    <td><h2> - Дешифратор</h2></td>
  </tr>
</table>

<h2>Расшифровка наименования оборудования</h2>
<form action="index.php?option=com_stalrams" method="get" name="formseach">
 Введите наименование оборудования: <input type="text" name="name_eq" value = <?php echo '"'.$this->name.'"';?> id='eq'/>
 <input type="submit" name "apply" value='Определить'><br>

	<?php echo JHTML::_('form.token'); ?>
	<input type="hidden" name="option" value="com_stalrams" />
	<input type="hidden" name="task" value="decode" />
</form>
<br>

<?php
echo $this->str;?>