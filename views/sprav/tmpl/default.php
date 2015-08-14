<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');?>

<link rel="stylesheet" type="text/css" href="components/com_stalrams/css/stalram.css">

<?php $url_img= '/components/com_stalrams/assets/images/';?>

<table width="100%"   class="text1" height="100%" cellspacing="10" cellpadding="0">
<tr><td valign=top width=250><br>
<form action="<?php echo JRoute::_('index.php')?>" method=get>
<?php echo $this->names;?>
<?php echo JHTML::_('form.token'); ?>
	<input type="hidden" name="option" value="com_stalrams"/>
	<input type="hidden" name="task" value="sprav"/>
</form>
<p><br><table bgcolor="White" class=tb border="1" cellpadding="2" cellspacing="0"><tr><td colspan="2" align="center">
 
  <b> Коды МРСК: </b></tr></td>
  <tr><td>ИА МРСК</td><td>641</td>
  <tr><td>Владимир</td><td>800</td></tr>
  <tr><td>Тула</td><td>802</td></tr>
  <tr><td>Н.Новгород</td><td>803</td></tr>
  <tr><td>Иваново</td><td>804</td></tr>
  <tr><td>Рязань</td><td>818</td></tr>
  <tr><td>Калуга</td><td>821</td></tr>
  <tr><td>Йошкар-Ола</td><td>860</td></tr>
  <tr><td>Ижевск</td><td>888</td></tr>
  <tr><td>Киров</td><td>889</td></tr></table>
  <p><br>
   </td><td valign=top>
    <div id="com_sprav_hdr""><h3 align=center><?php echo $this->name?></h3></div>
    
   	<div id="com_sprav_img"><img src="<?php echo $url_img.$this->img;?>" alt="<?php echo $this->name?>"></div>
   	
	<div id="com_sprav_form"><form  action="<?php echo JRoute::_('index.php')?>" method=get id="form-login">
	
	<b>Введите фамилию для поиска:</b> <br><input name=person type=Text size="40" > 
	<input type="submit" font-size:16px value="Поиск>>" >
	<?php //<a class="a_demo_four" href="#" onclick="document.getElementById('form-login').submit();">Поиск</a>*/?>
	<br>- Если ничего не введено будут выведены все записи<br>	          
	<?php echo JHTML::_('form.token'); ?>
	<input type="hidden" name="option" value="com_stalrams">
	<input type="hidden" name="task" value="sprav">
	<input type="hidden" name="id" value= "<?php echo JRequest::getVar('id')?>">
	</form>
	
	<form action="<?php echo JRoute::_('index.php')?>"  method=get>
	<b>Выберите службу/управление:</b><br>
	<?php echo $this->lists['Dep'];?>
	<input type="submit" value="Поиск>>" >
	<?php echo JHTML::_('form.token'); ?>
	<input type="hidden" name="option" value="com_stalrams">
	<input type="hidden" name="task" value="sprav">
	<input type="hidden" name="id" value="<?php echo JRequest::getVar('id');?>">
    </form>
    <?php if  ($this->metka<>0): ?>
    	<form action="<?php echo JRoute::_('index.php')?>"  method=get>
   		<b>Выберите отдел:</b><br>
    	<?php echo $this->lists['Divis'];?>
		<?php echo JHTML::_('form.token'); ?>   
		<input type="submit" value="Поиск>>" >
   		<input type=Hidden name=depart value="<?php echo JRequest::getVar('depart')?>">
   		<input type="hidden" name="option" value="com_stalrams" />
		<input type="hidden" name="task" value="sprav">
		<input type="hidden" name="id" value="<?php echo JRequest::getVar('id');?>">
   		</form>
    <?php endif;?>
    
    <form action="<?php echo JRoute::_('index.php')?>"  method=get>
	<input type="submit" value="Полный справочник>>" >
	<?php echo JHTML::_('form.token'); ?>
	<input type="hidden" name="option" value="com_stalrams">
	<input type="hidden" name="task" value="sprav">
	<input type="hidden" name="full" value="1">
	<input type="hidden" name="id" value="<?php echo JRequest::getVar('id');?>">
    </form>
        <form action="<?php echo JRoute::_('index.php')?>"  method=get>
	<input type="submit" value="Экспорт в excel>>" >
	<?php echo JHTML::_('form.token'); ?>
	<input type="hidden" name="option" value="com_stalrams">
	<input type="hidden" name="task" value="sprav">
	<input type="hidden" name="excel" value="1">
	<input type="hidden" name="id" value="<?php echo JRequest::getVar('id');?>">
    </form>
	<?php echo $this->rezSearch;?>
	</div>
	
	<br><br></td></tr></table></body>