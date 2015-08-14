<?php defined('_JEXEC') or die('Access Restricted'); ?>
<link rel="stylesheet" type="text/css" href="components/com_stalrams/css/stalram.css">
<!-- A link to a jQuery UI ThemeRoller theme, more than 22 built-in and many more custom -->
<link rel="stylesheet" type="text/css" media="screen" href="components/com_stalrams/css/jquery-ui.css" />
<!-- The link to the CSS that the grid needs -->
<link rel="stylesheet" type="text/css" media="screen" href="components/com_stalrams/css/ui.jqgrid.css">
<h1>Справочник объектов</h1>

<div class="tabbed">  
	
  <?php	echo $this->tabs;?>  

  <div class="tabs">
   <?php echo $this->tabstext;?>;
  </div>  
</div>
<div style="clear:both;"></div>  

<table id="jqGrid"></table>
    <div id="jqGridPager"></div>

<?php


$user = &JFactory::getUser();
echo $id = $user->id;

?>