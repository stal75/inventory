<?php
defined('_JEXEC') or die('Restricted access');?>

<!-- The link to the CSS that the grid needs -->

<link rel="stylesheet" type="text/css" href="components/com_stalrams/css/inv.css">
<!-- A link to a jQuery UI ThemeRoller theme, more than 22 built-in and many more custom -->

<link rel="stylesheet" type="text/css" media="screen" href="components/com_stalrams/css/ui.jqgrid.css">
<link rel="stylesheet" type="text/css" media="screen" href="components/com_stalrams/css/ui.multiselect.css" />
<link rel="stylesheet" type="text/css" media="screen" href="components/com_stalrams/css/jquery-ui.css" />

<link rel="stylesheet" type="text/css" media="screen" href="//blueimp.github.io/Gallery/css/blueimp-gallery.min.css" />
<link rel="stylesheet" type="text/css" media="screen" href="components/com_stalrams/css/jquery.fileupload.css" />
<link rel="stylesheet" type="text/css" media="screen" href="components/com_stalrams/css/jquery.fileupload-ui.css" />
<link rel="stylesheet" type="text/css" media="screen" href="components/com_stalrams/css/jquery.fileupload-noscript.css" />
<link rel="stylesheet" type="text/css" media="screen" href="components/com_stalrams/css/jquery.fileupload-ui-noscript.css" />
<link rel="stylesheet" type="text/css" media="screen" href="components/com_stalrams/css/jquery-ui-timepicker-addon.css" />


<div id="inv-top">
	<div id="top-menu"><?php echo $this->topmenu;?> </div> 
	<div id="top-logo"><?php echo $this->logo;?> </div>	
</div>
<div style="clear:both;"></div>
<div id="inv-content">
<!--<div id="left-menu"><?php echo $this->leftmenu;?></div>-->
	<div id="inv-worck">
		<div id = "inv-inventory"><!----------------------------------------- ИНВЕНТАРИЗАЦИЯ --------------------------------------------------->
    		<p><button onclick="addobject(1)" class="inv-button">Новый объект</button>
    		<input id="inv-inv-search" type="text">
    		<button id="savestate"class="inv-button">Сохр.табл.</button><button id="loadstate"class="inv-button">Загр.табл.</button>
	</p>
	    	<div class="inv-div-left" id="inv-dash-obj-type">
	    		<table id="jqGrid-dash-obj-type"></table>
	    	</div>
	    	<div class="inv-div-left" id="inv-dash-obj">
				<table id="jqGrid-dash-objects"></table>
				<div id="jqGridPager-dash-objects"></div>
			</div>
			<div style="clear:both;"></div>	
	    </div>
	    <div style="clear:both;"></div>
	    
	    <div id = "inv-docs"><!----------------------------------Документы---------------------------------------->
	    	<div class="inv-div-left">
	    		<table id="jqGrid-doc-access-region"></table>
	    	</div>
	    	<div class="inv-div-left">
	    		<table id="jqGrid-doc-docs"></table>
	    		<div id="jqGridPager-doc-docs"></div>
	    		<div id="progress">
					 <div class="bar" style="width: 0%;"></div>
				</div>
	    		<div id="doc-files">
					<input id="fileupload" type="file" name="files[]"  multiple>				
				</div>	    		
			</div>
		</div>
	    
	    <div id="inv-stores"> <!---------------------------------- Склады ------------------------------------------->
	    	<table id="jqGrid-stores"></table>
	    	<div id="jqGridPager-stores"></div>
	    </div>		
	    
		<div id = "inv-config"><!----------------------------------Настройка---------------------------------------->
      		<div id="tabs-conf">
			  <ul>
			    <li><a href="#tabs-1">Справочник</a></li>
			    <li><a href="#tabs-ad">AD</a></li>
			    <li><a href="#tabs-zabbix">Zabbix</a></li>
			    <li><a href="#tabs-ocs">OCS</a></li>
			    <li><a href="#tabs-stores">Склады</a></li>
			  </ul>
			  
			  <div id="tabs-1"><!-- ------------------------------Справочники------------------------ -->
			     <ul>
				    <li><a href="#tabs-names">Именование</a></li>
				    <li><a href="#tabs-group-type">Группы/типы оборудования</a></li>
				    <li><a href="#tabs-doc-type">Типы документов</a></li>
				    <li><a href="#tabs-supplier">Поставщики</a></li>
				    <li><a href="#tabs-vendor">Производители</a></li>
				    <li><a href="#tabs-accounting">Тип учета</a></li>
				    <li><a href="#tabs-comp-type">Тип ПК</a></li>
				    <li><a href="#tabs-print">Переферия</a></li>
				    <li><a href="#tabs-mon-matrix-type">Типы матрицы</a></li>
				    <li><a href="#tabs-supplies">Расход. мат.</a></li>
				    <li><a href="#tabs-net">Сетевое оборудование</a></li>
				  </ul> 
			    
			    <div id="tabs-names"><!-- ------------------------------Именование ------------------------ -->
				    <table id="jqGrid-region"></table>
	    			<div id="jqGridPager-region"></div>
	   	
        		</div>
        		<div id="tabs-group-type"><!-- ------------------------------Группы оборудования------------------------ -->
        			<table id="jqGrid-group-type"></table>
	    			<div id="jqGridPager-group-type"></div>
	    		</div>
        		<div id="tabs-doc-type"><!-- ------------------------------Типы документов------------------------ -->
        			<table id="jqGrid-cnf-doc-type"></table>
	    			<div id="jqGridPager-cnf-doc-type"></div>
        		</div>
        		<div id="tabs-supplier"><!-- ------------------------------Поставщики------------------------ -->
        			<table id="jqGrid-supplier"></table>
	    			<div id="jqGridPager-supplier"></div>
        		</div>
        		<div id="tabs-vendor"><!-- ------------------------------Производители------------------------ -->
        			<table id="jqGrid-vendor"></table>
	    			<div id="jqGridPager-vendor"></div>
        		</div>
        		<div id="tabs-accounting"><!-- ------------------------------Виды учета------------------------ -->
        			<table id="jqGrid-accounting"></table>
	    			<div id="jqGridPager-accounting"></div>
        		</div>
        		<div id="tabs-comp-type"><!-- ------------------------------Типы ПК------------------------ -->
        			<table id="jqGrid-comp-type"></table>
	    			<div id="jqGridPager-comp-type"></div>
        		</div>
        		<div id="tabs-print"><!-- ------------------------------Принтеры МФУ------------------------ -->
	        		 <ul>
	        		  	<li><a href="#tabs-print-cartridge">Картриджи</a></li>
	        		  	<li><a href="#tabs-print-printers">Шаблоны</a></li>
	        		  	<li><a href="#tabs-print-color">Цвета печати</a></li>
					    <li><a href="#tabs-print-type">Тип принтера МФУ</a></li>
					    <li><a href="#tabs-print-type-print">Тип печати</a></li>
					    <li><a href="#tabs-print-format">Формат печати</a></li>
					    <li><a href="#tabs-print-scan">Тип сканирования</a></li>
					 </ul> 
					<div id="tabs-print-cartridge"><!-- ------------------------------Картриджи------------------------ -->
	        			<table id="jqGrid-print-cartridge"></table>
		    			<div id="jqGridPager-print-cartridge"></div>
	        		</div>
	        		<div id="tabs-print-printers"><!-- ------------------------------Шаблоны принтеров------------------------ -->
	        			<table id="jqGrid-print-printers"></table>
		    			<div id="jqGridPager-print-printers"></div>
	        		</div>
	        		<div id="tabs-print-color"><!-- ------------------------------Цвета печати------------------------ -->
	        			<table id="jqGrid-print-color"></table>
		    			<div id="jqGridPager-print-color"></div>
	        		</div>
	        		<div id="tabs-print-type"><!-- ------------------------------Тип принтеров МФУ------------------------ -->
	        			<table id="jqGrid-print-type"></table>
		    			<div id="jqGridPager-print-type"></div>
	        		</div>
	        		<div id="tabs-print-type-print"><!-- ------------------------------Тип принтеров МФУ------------------------ -->
	        			<table id="jqGrid-print-type-print"></table>
		    			<div id="jqGridPager-print-type-print"></div>
	        		</div>
	        		<div id="tabs-print-format"><!-- ------------------------------Тип принтеров МФУ------------------------ -->
	        			<table id="jqGrid-print-format"></table>
		    			<div id="jqGridPager-print-format"></div>
	        		</div>	     
	        		<div id="tabs-print-scan"><!-- ------------------------------Тип принтеров МФУ------------------------ -->
	        			<table id="jqGrid-print-scan"></table>
		    			<div id="jqGridPager-print-scan"></div>
	        		</div>	   		
        		</div>
        		<div id="tabs-mon-matrix-type"><!-- ------------------------------Тип матрицы------------------------ -->
	        			<table id="jqGrid-mon-matrix-type"></table>
		    			<div id="jqGridPager-mon-matrix-type"></div>
	        	</div>    
	        	<div id="tabs-supplies" class="inv-tabs"><!-- ------------------------------Комплектующие------------------------ -->
	        		<ul>
	        		  	<li><a href="#tabs-sup-type">Типы</a></li>
	        		  	<li><a href="#tabs-sup-units">Еденицы измерений</a></li>	        		  		        		  	
					</ul> 
					<div id="tabs-sup-type"><!-- ------------------------------Тип комплектующих------------------------ -->
	        			<table id="jqGrid-sup-type"></table>
		    			<div id="jqGridPager-sup-type"></div>
	        		</div>
	        		<div id="tabs-sup-units"><!-- ------------------------------Еденицы измерения------------------------ -->
	        			<table id="jqGrid-sup-units"></table>
		    			<div id="jqGridPager-sup-units"></div>
	        		</div> 
	        	</div> 
	        	<div id="tabs-net"  class="inv-tabs"><!-- ------------------------------Сетевое оборудование------------------------ -->
	        		<ul>
	        		  	<li><a href="#tabs-net-template">Шаблоны</a></li>
	        		  	<li><a href="#tabs-net-type">Типы</a></li>
	        		  	<li><a href="#tabs-net-speed">Базовые скорости</a></li>	        		  		        		  	
					</ul> 
					<div id="tabs-net-template"><!-- ------------------------------Шаблоны коммутаторов, маршрутизаторов------------------------ -->
	        			<table id="jqGrid-net-template"></table>
		    			<div id="jqGridPager-net-template"></div>
	        		</div> 
	        		<div id="tabs-net-type"><!-- ------------------------------Типы коммутаторов, маршрутизаторов------------------------ -->
	        			<table id="jqGrid-net-type"></table>
		    			<div id="jqGridPager-net-type"></div>
	        		</div> 
	        		<div id="tabs-net-speed"><!-- ------------------------------Базовые скорости коммутаторов, маршрутизаторов------------------------ -->
	        			<table id="jqGrid-net-speed"></table>
		    			<div id="jqGridPager-net-speed"></div>
	        		</div> 
	        	</div>      		
			  </div>
			  
			  <div id="tabs-ad"><!-- ------------------------------Настройка AD------------------------ -->
			  	  <ul>
				    <li><a href="#tabs-ad-users">Пользователи</a></li>
				    <li><a href="#tabs-ad-pc">Компьютеры</a></li>
				  </ul> 
			  	<div id="tabs-ad-users"><!-- ------------------------------Пользователи- AD----------------------- -->
	        		<button onclick="ldapimport('usersadd')" class="inv-button">Импорт из AD</button>
				    <table id="jqGrid-ad-users"></table>
	    			<div id="jqGridPager-ad-users"></div>
	    			<span id = "inv-usersadd"></span>
        		</div>
			  	<div id="tabs-ad-pc"><!-- ------------------------------Компьютеры AD------------------------ -->
	        		<button onclick="ldapimport('pcadd')" class="inv-button">Импорт из AD</button>
				    <table id="jqGrid-ad-pc"></table>
	    			<div id="jqGridPager-ad-pc"></div>
	    			<span id = "inv-pcadd"></span>
        		</div>
    			
			  </div>
			  
			  <div id="tabs-zabbix"><!-- ------------------------------Оборудование в zabbix------------------------ -->
        			<button onclick="zabbiximport()" class="inv-button">Импорт из Zabbix</button> Мои<input type="checkbox" id="myZabbix"> Не добавленные<input type="checkbox" id="notZabbix">
        			<table id="jqGrid-zabbix"></table>
	    			<div id="jqGridPager-zabbix"></div>
	    			<span id = "inv-zabbiximport-sp"></span>
	    	  </div>
	    	  
	    	  <div id="tabs-ocs"><!-- ------------------------------Оборудование в OCSx------------------------ -->
        			<button onclick="ocsimport()" class="inv-button">Импорт из OCS</button>
        			<table id="jqGrid-ocs"></table>
	    			<div id="jqGridPager-ocs"></div>
	    			<span id = "inv-ocsimport-sp"></span>
	    	  </div>
	    	  
			  <div id="tabs-stores"><!-- ------------------------------Склады----------------------- -->
			    <div>
				    <div class="inv-div-left">
		    			<table id="jqGrid-region-stores"></table>
		    		</div>
		    		<div class="inv-div-left">
		    			<table id="jqGrid-conf-stores"></table>
		    		</div>
		    		<div style="clear:both;"></div>
	    		</div>
			  </div>
			  
			</div>	
      		
      		
    	</div>
    	<div style="clear:both;"></div>
    	    	
       	<div id = "inv-admin"><!-------------------------------------- АДМИНИСТРИРОВАНИЕ ------------------------------------------>
    		<div id="tabs-adm">
			  <ul>
			    <li><a href="#tabs-adm-users">Пользователи</a></li>
			    <li><a href="#tabs-adm-access">Права</a></li>
			    <li><a href="#tabs-adm-resp-user">Мат. ответственные</a></li>
			    <li><a href="#tabs-adm-journal">Журнал</a></li>
			  </ul>
			  
			  <div id="tabs-adm-users">
			  	<div class="inv-div-left">
			  		<table id="jqGrid-adm-region"></table>
			  	</div>
			  	<div class="inv-div-left">
			  		<table id="jqGrid-adm-access"></table>
			  		<div id="jqGrid-adm-access-Pager"></div>
			  	</div>
			  	<div style="clear:both;"></div>
			  	
			    <span id = "adm-msg"></span>
			  </div>
			  
			  <div id="tabs-adm-access">
			    <p>ывапыпыв</p>
			  </div>
			  <div id="tabs-adm-resp-user">
			    <table id="jqGrid-adm-resp-user"></table>
			  	<div id="jqGridPager-adm-resp-user"></div>
			  </div>
			  <div id="tabs-adm-journal">
			    <table id="jqGrid-adm-journal"></table>
			  	<div id="jqGridPager-adm-journal"></div>
			  </div>
			</div>	
    			   				
    	</div>
    	<div style="clear:both;"></div>
    	
    	<div id = "inv-obj-location" title="Местонахождение"><!----------------------- Окно Кодирования местонахождения ------------------------------->
    		<table class="inv-object-table">
		    	<tr><td>Код:</td><td><input id="loc-code" type="text", disabled></td>
		    	<td>Широта:</td><td><input id="loc-latitude" clase="loc-latitude" type="text", disabled></td>
				<td>Долгота:</td><td><input id="loc-longitude" clase="loc-latitude" type="text", disabled></td>
				<td>Высота:</td><td><input id="loc-altitude" clase="loc-latitude" type="text", disabled></td></tr>
				
	    		<tr>
                    <td>Регион:</td><td><input id="loc-reg" type="text", disabled></td>
                    <td>ПЭС:</td><td><input id="loc-pes" type="text", disabled></td>
                    <td>Объект:</td><td><input id="loc-obj" type="text", disabled></td>
                    <td>Помещение:</td><td><input id="loc-a" type="text", disabled></td>
                </tr>

                <tr>
                    <td>№ стойки:</td><td><input id="loc-info-rack" type="text", disabled></td>
                    <td>Категория:</td><td><input id="loc-info-cat" type="text", disabled></td>
                    <td>Тип:</td><td><input id="loc-info-equipment" type="text", disabled></td>
                    <td>№ в стойке:</td><td><input id="loc-info-eqnumber" type="text", disabled></td>
                </tr>
		    </table>
    		<div>
	    		<div class="inv-div-left">
	    			<table id="jqGrid-location-region"></table>
	    		</div>
	    		<!--<div class="inv-div-left">
	    			<table id="jqGrid-location-pes"></table>
	    		</div>-->
	    		<div class="inv-div-left">
	    			<table id="jqGrid-location-obj"></table>
	    		</div>
	    		<div class="inv-div-left">
	    			<table id="jqGrid-location-object"></table>
	    		</div>
	    		<div class="inv-div-left">
                    <table id="jqGrid-location-a"></table>
                    <table><tr><th><b></b></th><tr>
                      <tr><td></td><td></td></tr>
                      <tr><td><input type="radio" name="apparatn" value="k" id="loc-room-ra" onClick = "code()"> Комната</td><td><input name="kom" type="text" size="3" id="loc-room" onKeyUp = "code()" value="1"></td></tr>
                      <tr><td><input type="radio" name="apparatn" value="f" id="loc-floor-ra" onClick = "code()"> Коридор на этаже</td><td><input id="loc-floor" size="3" name="value" onKeyUp = "code()" onchange = "code()" value="0"></td></tr>
                      <tr><td><input type="radio" name="apparatn" value="" id="loc-floor-null" onClick = "code()"> Отсутствует<br><br><br></td><td></td></tr>
                    </table>
                    № cтойки<input id="loc-rack" size="2" name="value" onKeyUp = "code()" onchange = "code()" value="0">
	    		</div>
                <div class="inv-div-left">
                    <table id="jqGrid-location-equipment"></table>
                    № оборудования<input id="loc-eqnumber" size="2" name="value" onKeyUp = "code()" onchange = "code()" value="1">
                </div>
	    		
	    		<div style="clear:both;"></div>
	    	</div>
    	</div>
    	
    	<div id = "inv-addobject" title="Новый объект инвентаризации"><!----------------------- Окно добавления объекта ------------------------------->
    		
    		<div id = "inv-add-grid-type"> 
    			<table id="jqGrid-obj-type"></table>
    		</div>
    		
    		<div id = "inv-add-object">
    			<div id="tab-object">
				  <ul>
				    <li><a href="#tabs-comn-object">Объект</a></li>
				    <li><a href="#tabs-location">Местонахождение</a></li>
				    <li><a href="#tabs-properties">Свойства</a></li>
				    <li><a href="#tabs-docs">Документы</a></li>
				    <li><a href="#tabs-accessories">Состав</a></li>
				    <li><a href="#tabs-accounting">Бухгалтерия</a></li>
				  </ul>
				  <div id="tabs-comn-object">
		    		<form id="add-obj-form" action="">
		    		<table id="inv-object-table">
		    				<tr><td>Название:</td><td><input id="inv-obj-name" name="inv_obj_name" type="text"></td></tr>
		    				<tr><td>Дата покупки:</td><td><input id="inv-obj-date" name="inv_obj_date" type="text"></td></tr>
		    				<tr><td>Серийный №:</td><td><input id="inv-s-number" type="text"></td></tr>
		    				<tr><td>Инвентарный №1:</td><td><input id="inv-i-number" type="text"></td></tr>
		    				<tr><td>Инвентарный №2</td><td><input id="inv-i-number-adv" type="text"></td></tr>
		    				<tr><td>Вид учета</td><td><span id="inv-type-accounting-sp"></span></td></tr>
		    				<tr><td>Производитель:</td><td><input id="inv-id-vendor" type="text"></td></tr>
		    				<tr><td>Поставщик:</td><td><input id="inv-id-supplier" type="text"></td></tr>
		    				<tr><td>Мат. ответственный:</td><td><input id="inv-resp-user" type="text"></td></tr>
		    				<tr><td>Пользователь:</td><td><input id="inv-obj-user" type="text"></td></tr>
		    				<tr><td>Гарантия в мес:</td><td><input id="inv-guaranty" name="inv_guaranty" type="text"></td></tr>
		    				<tr><td>Штрихкод:</td><td><input id="inv-barcode" type="text"></td></tr>
		    				<tr><td>Примечание:</td><td><textarea id="inv-description"></textarea></td></tr>
		    		</table>
		    		</form>
		    	</div>
		    	<div id="tabs-location">
			    	<button " id="obj-add-loc-btn" class="inv-button">Закодировать</button>
			    	<table class="inv-object-table">
				    	<tr><td>Код:</td><td><input id="loc-code" type="text", disabled></td>
				    	<td>Широта</td><td><input id="loc-latitude" clase="loc-latitude" type="text"></td>
						<td>Долгота</td><td><input id="loc-longitude" clase="loc-latitude" type="text"></td>
						<td>Высота</td><td><input id="loc-altitude" clase="loc-latitude" type="text"</td></tr>
						
			    		<tr><td>Регион:</td><td><input id="loc-reg" type="text", disabled></td>
			    		<td>ПЭС:</td><td><input id="loc-pes" type="text", disabled></td>
			    		<td>Объект:</td><td><input id="loc-obj" type="text", disabled></td>
			    		<td>Помещение:</td><td><input id="loc-a" type="text", disabled></td></tr>
		    		</table>
		    		<div class="inv-div-left">
			    			<table id="jqGrid-obj-region-stores"></table>
			    		</div>
			    		<div class="inv-div-left">
			    			<table id="jqGrid-obj-conf-stores"></table>
			    		</div>
			    		<div style="clear:both;"></div>	
		    	</div>
		    	<div id="tabs-properties">
		    		<div id="tabs-computer">
			    		<ul>
					    	<li><a href="#tabs-computer-comn">Общие</a></li>
					    	<li><a href="#tabs-computer-hard">Конфигурация</a></li>
					  	</ul>
					  	<form id="add-pc-form" action="">
					  	<div id="tabs-computer-comn">
				    	  	<table id="inv-object-table">
				    	  		<tr><td>Шасси:</td><td><input id="inv-pc-chasis" class="pc-chasis" type="text" name="inv_pc_chasis"></td></tr>
			    				<tr><td>Модель:</td><td><input id="inv-pc-model" class="pc-model" type="text"></td></tr>
				    	  		<tr><td>Тип ПК:</td><td><span id="inv-pc-type-sp" ></span></td></tr>
			    				<tr><td>Доменное имя:</td><td><input id="inv-pc-ad-name" class="pc-ad-name" type="text"></td></tr>
			    				<tr><td>Имя в zabbix:</td><td><input id="inv-pc-zabbix-name" class="pc-zabbix-name" type="text"></td></tr>
			    				<tr><td>Имя в OCS:</td><td><input id="inv-pc-ocs-name" class="pc-ocs-name" type="text"></td></tr>
			    				<tr><td>Конфигурация:</td><td><input id="inv-pc-config" class="pc-config" type="text" name="inv_pc_config"></td></tr>
				    		</table>		    	
				    	</div>
				    	<div id="tabs-computer-hard"> 
				    		<table id="inv-object-table">
					    		<tr><td>Процессор:</td><td><input id="inv-pc-processor" class="comp-config pc-processor" type="text"></td><td><button onclick="compconfsync()" id="pc-config-sync" class="inv-button">Синхронизировать</button></td></tr>
			    				<tr><td>Память:</td><td><input id="inv-pc-mem" class="comp-config pc-mem" type="text"></td><td></td></tr>
			    				<tr><td>Материнская плата:</td><td><input id="inv-pc-motherboard" class="comp-config pc-motherboard" type="text"></td><td></td></tr>
			    				<tr><td>Жесткие диски:</td><td><input id="inv-pc-hdd" class="comp-config pc-hdd" type="text"></td><td></td></tr>
			    				<tr><td>Блок питания:</td><td><input id="inv-pc-psu" class="comp-config pc-psu" type="text"></td><td></td></tr>
			    				<tr><td>Графические карты:</td><td><input id="inv-pc-graphics" class="comp-config pc-graphics" type="text"></td><td></td></tr>
			    				<tr><td>Подключен к сети?:</td><td>Да<input id="inv-pc-network" class="pc-network" type="checkbox"></td><td></td></tr>
			    				<tr><td>MAC адрес:</td><td><input id="inv-pc-mac" class="comp-config pc-mac" type="text" ></td><td></td></tr>
			    				<tr><td>ОС:</td><td><input id="inv-pc-osname" class="comp-config pc-osname" type="text" ></td><td></td></tr>
			    				<tr><td>Примечание:</td><td><textarea id="inv-pc-description" class="comp-config pc-description"></textarea></td><td></td></tr>
		    				</table>	
				    	</div>
				    	</form>
			    	</div>
			    	<div id="tabs-monitor">
			    		<form id="add-mon-form" action="">
				    	  	<table id="inv-object-table">
				    	  	    <tr><td>Шасси*:</td><td><input id="inv-mon-chasis" type="text" name="inv_mon_chasis"></td></tr>
			    				<tr><td>Модель*:</td><td><input id="inv-mon-model" type="text"></td></tr>
				    	  		<tr><td>Диагональ "*</td><td><input id="inv-mon-size" type="text"></td></tr>
			    				<tr><td>Широкоформатный*</td><td>Да<input id="inv-mon-format" type="checkbox" checked></td></tr>
			    				<tr><td>Разрешение:</td><td><input id="inv-mon-resolution" type="text"></td></tr>
			    				<tr><td>ЖК:</td><td>Да<input id="inv-mon-lid" type="checkbox" checked></td></tr>
			    				<tr><td>Встроенные динамики:</td><td>Да<input id="inv-mon-dynamics" type="checkbox"></td></tr>
			    				<tr><td>Тип матрицы:</td><td><span id="inv-mon-matrix-type-sp" ></span></td></tr>
			    				<tr><td>Видео входы:</td><td><input id="inv-mon-video-inputs" type="text" name="inv_pc_config"></td></tr>
			    				<tr><td>Потребляемая мощность:</td><td><input id="inv-mon-power" type="text" name="inv_pc_config">Вт</td></tr>
			    				<tr><td>Описание:</td><td><textarea id="inv-mon-description"></textarea></td></tr>
				    		</table>		    	
			    	</div>
			    	<div id="tabs-prn">
			    		<form id="add-prn-form" action="">
				    	  	<table id="inv-object-table">
								<tr><td>Тип устройства:</td><td><span id="prn-type-sp" class=prn-type-sp ></span></td><td></td><td></td></tr>
				    	  		<tr><td>Шасси*:</td><td><input id="prn-chasis" class="prn-chasis" type="text" name="prn_chasis"></td><td></td><td></td></tr>
			    				<tr><td>Модель*:</td><td><input id="prn-model" class="prn-model" type="text"></td><td></td><td></td></tr>
			    				<tr><td>Имя на сервере печати:</td><td><input id="prn-srv-name" class="prn-srv-name" type="text"></td><td></td><td></td></tr>
			    				<tr><td>Имя в zabbix:</td><td><input id="prn-zabbix-name" class="prn-zabbix-name" type="text"></td><td></td><td></td></tr>
			    				<tr><td>Тип печати:</td><td><span id="prn-type-print-sp" class="prn-type-print-sp" ></span></td><td></td><td></td></tr>
			    				<tr><td>Тип сканирования:</td><td><span id="prn-type-scan-sp" class="prn-type-scan-sp" ></span></td></tr>
			    				<tr><td>Цветной</td><td>Да<input id="prn-color" class="prn-color" type="checkbox"></td><td></td><td></td></tr>
			    				<tr><td>Максимальный формат:</td><td><span id="prn-format-sp" class="prn-format-sp" ></span></td><td></td><td></td></tr>
			    				<tr><td>Фото</td><td>Да<input id="prn-photo" class="prn-photo" type="checkbox"></td><td></td><td></td></tr>
			    				<tr><td>Двухсторонняя печать</td><td>Да<input id="prn-duplex-printing" class="prn-duplex-printing" type="checkbox"></td><td></td><td></td></tr>
			    				<tr><td>Подключен к сети</td><td>Да<input id="prn-network" class="prn-network" type="checkbox" checked></td><td></td><td></td></tr>
			    				<tr><td>Сетевой интерфейс</td><td>Да<input id="prn-ethernet" class="prn-ethernet" type="checkbox" checked></td><td></td><td></td></tr>
			    				<tr><td>MAC адрес:</td><td><input id="prn-mac" class="prn-mac" type="text"></td><td></td><td></td></tr>
			    				<tr><td>WIFI</td><td>Да<input id="prn-wifi" class="prn-wifi" type="checkbox"></td><td></td><td></td></tr>
			    				<tr><td>Скорость печати стр/м</td><td><input id="prn-speed-print" class="prn-speed-print" type="text"></td><td></td><td></td></tr>			
			    				<tr><td>Скорость сканирования стр/м</td><td><input id="prn-speed-scan" class="prn-speed-scan" type="text"></td><td></td><td></td></tr>
			    				<tr><td>Память</td><td><input id="prn-mem" class="prn-mem" type="text"></td><td></td><td></td></tr>
			    				<tr><td>Отпечатано листов</td><td><input id="prn-sheets" class="prn-sheets" type="text">Дата<input id="prn-date-sheets" class="inv-date prn-date-sheets" type="text"></td><td></td><td></td></tr>
			    				<tr><td>fax</td><td>Да<input id="prn-fax" class="prn-fax" type="checkbox"></td><td></td><td></td></tr>
			    				<tr><td>Месячный ресурс</td><td><input id="prn-m-resource" class="prn-m-resource" type="text"></td></tr>
			    				<tr><td>Поддерживаемые OS:</td><td><textarea id="prn-os" class="comp-config prn-os"></textarea></td></tr>
			    				<tr><td>Описание:</td><td><textarea id="prn-description" class="comp-config prn-description"></textarea></td><td></td><td></td></tr> 
				    		</table>
				    	</form>		    	 
			    	</div>
			    	<div id="tabs-sup"><!----------------------- Окно Расходные материалы ------------------------------->
			    		<table id="inv-object-table">
			    			<tr><td>Тип материала:</td><td><input id="sup-type" class="sup-type" type="text" name="sup-type"></td><td></td><td></td></tr>
			    			<tr><td>Название*:</td><td><input id="sup-model" class="sup-model" type="text" name="sup-model"></td><td></td><td></td></tr>
			    			<tr><td>Количество*:</td><td><input id="sup-count" class="sup-count" type="text" name="sup-count"><span id="sup-count-sp" class="sup-count-sp"></span></td><td></td><td></td></tr>
			    			<tr><td><span id="sup-field1-sp" class="sup-field1-sp"></span></td><td><input id="sup-field1" class="sup-field1" type="hidden"></td><td></td><td></td></tr>
			    			<tr><td><span id="sup-field2-sp" class="sup-field2-sp"></span></td><td><input id="sup-field2" class="sup-field2" type="hidden"></td><td></td><td></td></tr>
			    			<tr><td><span id="sup-field3-sp" class="sup-field3-sp"></span></td><td><input id="sup-field3" class="sup-field3" type="hidden"></td><td></td><td></td></tr>
			    			<tr><td><span id="sup-field4-sp" class="sup-field4-sp"></span></td><td><input id="sup-field4" class="sup-field4" type="hidden"></td><td></td><td></td></tr>
			    			<tr><td><span id="sup-field5-sp" class="sup-field5-sp"></span></td><td><input id="sup-field5" class="sup-field5" type="hidden"></td><td></td><td></td></tr>
			    			<tr><td>Описание:</td><td><textarea id="sup-description" class="sup-description"></textarea></td><td></td><td></td></tr>
			    		</table>
			    	</div>
		    	</div>
		    	
		    	<div id="tabs-docs">
		    		<table id="jqGrid-obj-docs1"></table>
		    		<div id="jqGridPager-obj-docs1"></div>
		    		<table id="jqGrid-obj-docs2"></table>
		    		<div id="jqGridPager-obj-docs2"></div>
		    	</div>
		    	<div id="tabs-accessories">
		    		<table id="jqGrid-obj-parts1"></table>
		    		<div id="jqGridPager-obj-parts1"></div>
		    		<p></p>
		    		<table id="jqGrid-obj-parts2"></table>
		    		<div id="jqGridPager-obj-parts2"></div>
		    	</div>
		    	<div id="tabs-accounting">
		    		<form id="add-obj-form" action="">
		    		<table id="inv-object-table">
		    				<tr><td>Название бух.:</td><td><input id="buh-name" name="inv_obj_buh_name" type="text"></td></tr>
		    				<tr><td>Дата прихода:</td><td><input id="buh-date" name="inv_obj_buh_date" type="text"></td></tr>
		    				<tr><td>Первн. стоимость:</td><td><input id="buh-cost" type="text"></td></tr>
		    				<tr><td>Амортизация:</td><td><input id="buh-amortization" type="text"></td></tr>		    				
		    		</table>
		    		</form>
		    	</div>
    		</div>
    	</div>
    	<div style="clear:both;"></div>
    	
	</div>
		<div id = "inv-info-object" title="Ниформация по объекту инвентаризации"><!----------------------- Окно информации объекта ------------------------------->
    		
    		<div id = "inv-info-object">
    			<div id="inv-info-tab-object">
				  <ul>
				    <li><a href="#inv-info-tabs-comn-object">Объект</a></li>
				    <li><a href="#inv-info-tabs-location">Местонахождение</a></li>
				    <li><a href="#inv-info-tabs-properties">Свойства</a></li>
				    <li><a href="#inv-info-tabs-docs">Документы</a></li>
				    <li><a href="#inv-info-tabs-accessories">Состав</a></li>
				    <li><a href="#info-tabs-accounting">Бухгалтерия</a></li>
				    <li><a href="#inv-info-tabs-geo">Гео</a></li>
				  </ul>
				  <div id="inv-info-tabs-comn-object">
		    		<form id="add-obj-form" action="">
		    		<table>
		    		<tr><td>
		    		<table id="inv-object-table">
		    				<tr><td>Название:</td><td><input id="inv-obj-name" name="inv_obj_name" type="text"></td><td></td></tr>
		    				<tr><td>Дата покупки:</td><td><input id="inv-obj-date" name="inv_obj_date" type="text"></td><td></td></tr>
		    				<tr><td>Серийный №:</td><td><input id="inv-s-number" type="text"></td><td></td></tr>
		    				<tr><td>Инвентарный №1:</td><td><input id="inv-i-number" type="text"></td><td></td></tr>
		    				<tr><td>Инвентарный №2</td><td><input id="inv-i-number-adv" type="text"></td><td></td></tr>
		    				<tr><td>Вид учета</td><td><span id="inv-type-accounting-sp"></span></td><td></td></tr>
		    				<tr><td>Производитель:</td><td><input id="inv-id-vendor" type="text"></td><td></td></tr>
		    				<tr><td>Поставщик:</td><td><input id="inv-id-supplier" type="text"></td><td></td></tr>
		    				<tr><td>Мат. ответственный:</td><td><input id="inv-resp-user" type="text"></td><td></td></tr>
		    				<tr><td>Пользователь:</td><td><input id="inv-obj-user" type="text"></td><td></td></tr>
		    				<tr><td>Гарантия в мес:</td><td><input id="inv-guaranty" name="inv_guaranty" type="text"></td><td></td></tr>
		    				<tr><td>Штрихкод:</td><td><input id="inv-barcode" type="text"></td><td></td></tr>
		    				<tr><td>Примечание:</td><td><textarea id="inv-description"></textarea></td><td></div></td></tr>
		    		</table>
		    		</td><td>
		    		QR код<div id="QRCode">
		    		</td>
		    		</tr>
		    		</table>
		    		</form>
		    	</div>
		    	<div id="inv-info-tabs-location">
		    		<div id="inv-info-accordion">
		    			<h3>Текущее местонахождение</h3>
  						<div>	
					    	<button " id="obj-info-loc-btn" class="inv-button">Закодировать</button>Дата/время:</td><td><input id="loc-date" class="inv-datetime" type="text">
				    		<table class="inv-object-table">
						    	<tr><td>Код:</td><td><input id="loc-code" type="text", disabled></td>
						    	<td>Широта</td><td><input id="loc-latitude" clase="loc-latitude" type="text"></td>
								<td>Долгота</td><td><input id="loc-longitude" clase="loc-latitude" type="text"></td>
								<td>Высота</td><td><input id="loc-altitude" clase="loc-latitude" type="text"</td></tr>
								
					    		<tr><td>Регион:</td><td><input id="loc-reg" type="text", disabled></td>
					    		<td>ПЭС:</td><td><input id="loc-pes" type="text", disabled></td>
					    		<td>Объект:</td><td><input id="loc-obj" type="text", disabled></td>
					    		<td>Помещение:</td><td><input id="loc-a" type="text", disabled></td></tr>
		    				</table>
				    		<div class="inv-div-left">
					    		<table id="jqGrid-obj-info-region-stores"></table>
					    	</div>
					    	<div class="inv-div-left">
					    		<table id="jqGrid-obj-info-conf-stores"></table>
					    	</div>
				    	</div>
				    	<h3>История перемещения</h3>
				    	<div>
				    		<table id="jqGrid-obj-info-loc-history"></table>
				    	</div>
				    	<h3>История изменения пользователя</h3>
				    	<div>
				    		<table id="jqGrid-obj-info-user-history"></table>
				    	</div>
				    	<h3>История изменения мат. ответственного</h3>
				    	<div>
				    		<table id="jqGrid-obj-info-resp-user-history"></table>
				    	</div>
			    	</div>
			    	<div style="clear:both;"></div>	
		    	</div>
		    	<div id="inv-info-tabs-properties">
		    		<div id="inv-info-computer">
			    		<ul>
					    	<li><a href="#inv-info-tabs-computer-comn">Общие</a></li>
					    	<li><a href="#inv-info-tabs-computer-hard">Конфигурация</a></li>
					    	<li><a href="#inv-info-tabs-computer-OCS">OCS</a></li>
					  	</ul>
					  	<form id="add-pc-form" action="">
					  	 <div id="inv-info-tabs-computer-comn">
				    	  	<table id="inv-object-table">
				    	  		<tr><td>Шасси:</td><td><input id="inv-pc-chasis" class="pc-chasis" type="text" name="inv_pc_chasis"></td></tr>
			    				<tr><td>Модель:</td><td><input id="inv-pc-model" class="pc-model" type="text"></td></tr>
				    	  		<tr><td>Тип ПК:</td><td><span id="inv-pc-type-sp" ></span></td></tr>
			    				<tr><td>Доменное имя:</td><td><input id="inv-pc-ad-name" class="pc-ad-name" type="text"></td></tr>
			    				<tr><td>Имя в zabbix:</td><td><input id="inv-pc-zabbix-name" class="pc-zabbix-name" type="text"></td></tr>
			    				<tr><td>Имя в OCS:</td><td><input id="inv-pc-ocs-name" class="pc-ocs-name" type="text"></td></tr>
			    				<tr><td>Конфигурация:</td><td><input id="inv-pc-config" class="pc-config" type="text" name="inv_pc_config"></td></tr>
				    		</table> 	    	
				    	</div>
				    	<div id="inv-info-tabs-computer-hard"> 
				    		<div id="inv-info-accordion-config">
		    					<h3>Текущая конфигурация</h3>
  								<div id="inv-info-comp-hard">	
	  								<table id="inv-object-table">
							    		<tr><td>Процессор:</td><td><input id="inv-pc-processor" class="comp-config pc-processor" type="text"></td><td><button onclick="compconfsync()" id="pc-config-sync" class="inv-button">Синхронизировать</button></td></tr>
					    				<tr><td>Память:</td><td><input id="inv-pc-mem" class="comp-config pc-mem" type="text"></td><td></td></tr>
					    				<tr><td>Материнская плата:</td><td><input id="inv-pc-motherboard" class="comp-config pc-motherboard" type="text"></td><td></td></tr>
					    				<tr><td>Жесткие диски:</td><td><input id="inv-pc-hdd" class="comp-config pc-hdd" type="text"></td><td></td></tr>
					    				<tr><td>Блок питания:</td><td><input id="inv-pc-psu" class="comp-config pc-psu" type="text"></td><td></td></tr>
					    				<tr><td>Графические карты:</td><td><input id="inv-pc-graphics" class="comp-config pc-graphics" type="text"></td><td></td></tr>
					    				<tr><td>Подключен к сети?:</td><td>Да<input id="inv-pc-network" class="pc-network" type="checkbox"></td><td></td></tr>
					    				<tr><td>MAC адрес:</td><td><input id="inv-pc-mac" class="comp-config pc-mac" type="text" ></td><td></td></tr>
					    				<tr><td>ОС:</td><td><input id="inv-pc-osname" class="comp-config pc-osname" type="text" ></td><td></td></tr>
					    				<tr><td>Примечание:</td><td><textarea id="inv-pc-description" class="comp-config pc-description"></textarea></td><td></td></tr>
			    					</table>
		    					</div>
		    					<h3>История изменения конфигурации</h3>
  								<div>
  									<table id="jqGrid-obj-info-config-history"></table>
  								</div>
		    				</div>	
				    	</div>
				    	</form>
				    	<div id="inv-info-tabs-computer-OCS" class="inv-tabs">
					    	<ul>
						    	<li><a href="#info-computer-OCS-BIOS">BIOS</a></li>
						    	<li><a href="#info-computer-OCS-soft">ПО</a></li>
						    	<li><a href="#info-computer-OCS-hard">OCS</a></li>
						  	</ul>
						  	<div id="info-computer-OCS-BIOS">
						  		<table id="inv-object-table">
							  		<tr><td>Производитель платы:</td><td><input id="OCS-BIOS-SMANUFACTURER" class="comp-config" type="text"></td><td></td></tr>
				    				<tr><td>Модель:</td><td><input id="OCS-BIOS-SMODEL" class="comp-config" type="text"></td><td></td></tr>
				    				<tr><td>Серийный №:</td><td><input id="OCS-BIOS-SSN" class="comp-config" type="text"></td><td></td></tr>
				    				<tr><td>Тип:</td><td><input id="OCS-BIOS-TYPE" class="comp-config" type="text"></td><td></td></tr>
				    				<tr><td>Производитель BIOS:</td><td><input id="OCS-BIOS-BMANUFACTURER" class="comp-config" type="text"></td><td></td></tr>
				    				<tr><td>Версия:</td><td><input id="OCS-BIOS-BVERSION" class="comp-config" type="text"></td><td></td></tr>
				    				<tr><td>Дата:</td><td><input id="OCS-BIOS-BDATE" class="comp-config" type="text"></td><td></td></tr>
				    				<tr><td>Примечание:</td><td><input id="OCS-BIOS-ASSETTAG" class="comp-config" type="text"></td><td></td></tr>
				    			</table>
						  	</div>
						  	<div id="info-computer-OCS-soft">
							  	<table id="jqGrid-info-OCS-soft"></table>
					    		<div id="jqGridPager-info-OCS-soft"></div>
						  	</div>
						  	<div id="info-computer-OCS-hard">
						  	</div>
				    	</div>
			    	</div>
			    	<div id="inv-info-monitor">
			    		<form id="add-mon-form" action="">
				    	  	<table id="inv-object-table">
				    	  	    <tr><td>Шасси*:</td><td><input id="inv-mon-chasis" type="text" name="inv_mon_chasis"></td></tr>
			    				<tr><td>Модель*:</td><td><input id="inv-mon-model" type="text"></td></tr>
				    	  		<tr><td>Диагональ "*</td><td><input id="inv-mon-size" type="text"></td></tr>
			    				<tr><td>Широкоформатный*</td><td>Да<input id="inv-mon-format" type="checkbox" checked></td></tr>
			    				<tr><td>Разрешение:</td><td><input id="inv-mon-resolution" type="text"></td></tr>
			    				<tr><td>ЖК:</td><td>Да<input id="inv-mon-lid" type="checkbox" checked></td></tr>
			    				<tr><td>Встроенные динамики:</td><td>Да<input id="inv-mon-dynamics" type="checkbox"></td></tr>
			    				<tr><td>Тип матрицы:</td><td><span id="inv-mon-matrix-type-sp" ></span></td></tr>
			    				<tr><td>Видео входы:</td><td><input id="inv-mon-video-inputs" type="text" name="inv_pc_config"></td></tr>
			    				<tr><td>Потребляемая мощность:</td><td><input id="inv-mon-power" type="text" name="inv_pc_config">Вт</td></tr>
			    				<tr><td>Описание:</td><td><textarea id="inv-mon-description"></textarea></td></tr>
				    		</table>		    	
			    	</div>
			    	<div id="inv-info-prn" class="inv-tabs">
				    	<ul>
						    <li><a href="#inv-prn-comn">Характеристики</a></li>
						    <li><a href="#inv-prn-history">История печати</a></li>
					  </ul>
					  <div id="inv-prn-comn">
			    		<form id="add-prn-form" action="">
				    	  	<table id="inv-object-table">
								<tr><td>Тип устройства:</td><td><span id="prn-type-sp" class=prn-type-sp ></span></td><td></td><td></td></tr>
				    	  		<tr><td>Шасси*:</td><td><input id="prn-chasis" class="prn-chasis" type="text" name="prn_chasis"></td><td></td><td></td></tr>
			    				<tr><td>Модель*:</td><td><input id="prn-model" class="prn-model" type="text"></td><td></td><td></td></tr>
			    				<tr><td>Имя на сервере печати:</td><td><input id="prn-srv-name" class="prn-srv-name" type="text"></td><td></td><td></td></tr>
			    				<tr><td>Имя в zabbix:</td><td><input id="prn-zabbix-name" class="prn-zabbix-name" type="text"></td><td></td><td></td></tr>
			    				<tr><td>Тип печати:</td><td><span id="prn-type-print-sp" class="prn-type-print-sp" ></span></td><td></td><td></td></tr>
			    				<tr><td>Тип сканирования:</td><td><span id="prn-type-scan-sp" class="prn-type-scan-sp" ></span></td></tr>
			    				<tr><td>Цветной</td><td>Да<input id="prn-color" class="prn-color" type="checkbox"></td><td></td><td></td></tr>
			    				<tr><td>Максимальный формат:</td><td><span id="prn-format-sp" class="prn-format-sp" ></span></td><td></td><td></td></tr>
			    				<tr><td>Фото</td><td>Да<input id="prn-photo" class="prn-photo" type="checkbox"></td><td></td><td></td></tr>
			    				<tr><td>Двухсторонняя печать</td><td>Да<input id="prn-duplex-printing" class="prn-duplex-printing" type="checkbox"></td><td></td><td></td></tr>
			    				<tr><td>Подключен к сети</td><td>Да<input id="prn-network" class="prn-network" type="checkbox" checked></td><td></td><td></td></tr>
			    				<tr><td>Сетевой интерфейс</td><td>Да<input id="prn-ethernet" class="prn-ethernet" type="checkbox" checked></td><td></td><td></td></tr>
			    				<tr><td>MAC адрес:</td><td><input id="prn-mac" class="prn-mac" type="text"></td><td></td><td></td></tr>
			    				<tr><td>WIFI</td><td>Да<input id="prn-wifi" class="prn-wifi" type="checkbox"></td><td></td><td></td></tr>
			    				<tr><td>Скорость печати стр/м</td><td><input id="prn-speed-print" class="prn-speed-print" type="text"></td><td></td><td></td></tr>			
			    				<tr><td>Скорость сканирования стр/м</td><td><input id="prn-speed-scan" class="prn-speed-scan" type="text"></td><td></td><td></td></tr>
			    				<tr><td>Память</td><td><input id="prn-mem" class="prn-mem" type="text"></td><td></td><td></td></tr>
			    				<tr><td>Отпечатано листов</td><td><input id="prn-sheets" class="prn-sheets" type="text">Дата<input id="prn-date-sheets" class="inv-date prn-date-sheets" type="text"></td><td></td><td></td></tr>
			    				<tr><td>fax</td><td>Да<input id="prn-fax" class="prn-fax" type="checkbox"></td><td></td><td></td></tr>
			    				<tr><td>Месячный ресурс</td><td><input id="prn-m-resource" class="prn-m-resource" type="text"></td></tr>
			    				<tr><td>Поддерживаемые OS:</td><td><textarea id="prn-os" class="comp-config prn-os"></textarea></td></tr>
			    				<tr><td>Описание:</td><td><textarea id="prn-description" class="comp-config prn-description"></textarea></td><td></td><td></td></tr>	    	  		
				    		</table>
				    	</form>
				    </div>
				    <div id="inv-prn-history">
				    	<table id="jqGrid-info-prn-history"></table>
				    	<div id="jqGridPager-info-prn-history"></div>
				    </div>	    	 
			    	</div>
			    	<div id="inv-info-sup"  class="inv-tabs"><!----------------------- Окно Расходные материалы ------------------------------->
			    		<ul>
						    <li><a href="#inv-info-sup-prop">Характеристики</a></li>
						    <li><a href="#inv-info-sup-store">Списание</a></li>
					   </ul>
						<div id="inv-info-sup-prop">
			    			<table id="inv-object-table">
			    			<tr><td>Тип материала:</td><td><input id="sup-type" class="sup-type" type="text" name="sup-type"></td><td></td><td></td></tr>
				    			<tr><td>Название*:</td><td><input id="sup-model" class="sup-model" type="text" name="sup-model"></td><td></td><td></td></tr>
				    			<tr><td>Количество*:</td><td><input id="sup-count" class="sup-count" type="text" name="sup-count"><span id="sup-count-sp" class="sup-count-sp"></span></td><td></td><td></td></tr>
				    			<tr><td><span id="sup-field1-sp" class="sup-field1-sp"></span></td><td><input id="sup-field1" class="sup-field1" type="hidden"></td><td></td><td></td></tr>
				    			<tr><td><span id="sup-field2-sp" class="sup-field2-sp"></span></td><td><input id="sup-field2" class="sup-field2" type="hidden"></td><td></td><td></td></tr>
				    			<tr><td><span id="sup-field3-sp" class="sup-field3-sp"></span></td><td><input id="sup-field3" class="sup-field3" type="hidden"></td><td></td><td></td></tr>
				    			<tr><td><span id="sup-field4-sp" class="sup-field4-sp"></span></td><td><input id="sup-field4" class="sup-field4" type="hidden"></td><td></td><td></td></tr>
				    			<tr><td><span id="sup-field5-sp" class="sup-field5-sp"></span></td><td><input id="sup-field5" class="sup-field5" type="hidden"></td><td></td><td></td></tr>
				    			<tr><td>Описание:</td><td><textarea id="sup-description" class="sup-description"></textarea></td><td></td><td></td></tr>
			    			</table>
			    		</div>
			    		<div id="inv-info-sup-store">
			    			<table id="jqGrid-info-sup-store"></table>
				    		<div id="jqGridPager-info-sup-store"></div>
			    		</div>
			    	</div>
		    	</div>
		    	
		    	<div id="inv-info-tabs-docs">
		    		<table id="jqGrid-obj-info-docs1"></table>
		    		<div id="jqGridPager-obj-info-docs1"></div>
		    		<table id="jqGrid-obj-info-docs2"></table>
		    		<div id="jqGridPager-obj-info-docs2"></div>
		    	</div>
		    	<div id="inv-info-tabs-accessories">
		    		<table id="jqGrid-obj-info-parts1"></table>
		    		<div id="jqGridPager-obj-info-parts1"></div>
		    		<p></p>
		    		<table id="jqGrid-obj-info-parts2"></table>
		    		<div id="jqGridPager-obj-info-parts2"></div>
		    	</div>
		    	<div id="info-tabs-accounting">
		    		<form id="add-obj-form" action="">
		    		<table id="inv-object-table">
		    				<tr><td>Название бух.:</td><td><input id="buh-name" name="inv_obj_buh_name" type="text"></td></tr>
		    				<tr><td>Дата прихода:</td><td><input id="buh-date" name="inv_obj_buh_date" type="text"></td></tr>
		    				<tr><td>Первн. стоимость:</td><td><input id="buh-cost" type="text"></td></tr>
		    				<tr><td>Амортизация:</td><td><input id="buh-amortization" type="text"></td></tr>		    				
		    		</table>
		    		</form>
		    	</div>
		    	<div id="inv-info-tabs-geo">
		    		<div id="map" style="width: 1100px; height: 500px"></div>
		    	</div>
    		</div>
    	</div>
    	<div style="clear:both;"></div>
    	
	</div>
</div>
<div class="bottom-message">
<p><span id="bottom-message-sp"></span></p>
</div>
<div id="inv-dlg-find" title="Поиск объекта">
		<table id="jqGrid-obj-find"></table>
		<div id="jqGridPager-obj-find"></div>
</div>
<div id="inv-dlg-sup-store" title="Списать материал">
	<table id="inv-object-table">
    	<tr><td>Количество:</td><td><input id="sup-count" class="sup-count" type="text" name="sup-count"></td><td></td><td></td></tr>
	    <tr><td>Дата*:</td><td><input id="sup-date" class="inv-date" type="text" name="inv-date"></td><td></td><td></td></tr>
	    <tr><td>Объект:</td><td><input id="id_object" class="id_object" type="text" name="id_object"></span></td><td></td><td></td></tr>
	    <tr><td>Пользователь:</td><td><input id="sup-user" class="sup-user" type="text" name="sup-user"></span></td><td></td><td></td></tr>	    
    </table>
</div>

<div id="ohsnap"></div><!----------------------- Окно Всплывающие уведомления ------------------------------->