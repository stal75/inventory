jQuery(document).ready(function () {
	
	jQuery.datepicker.regional['ru'] = {
			closeText: 'Закрыть',
			prevText: '<Пред',
			nextText: 'След>',
			currentText: 'Сегодня',
			monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь',
			'Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
			monthNamesShort: ['Янв','Фев','Мар','Апр','Май','Июн',
			'Июл','Авг','Сен','Окт','Ноя','Дек'],
			dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'],
			dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],
			dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
			weekHeader: 'Не',
			dateFormat: 'dd.mm.yy',
			firstDay: 1,
			isRTL: false,
			showMonthAfterYear: false,
			yearSuffix: ''
		};
	jQuery.datepicker.setDefaults(jQuery.datepicker.regional['ru']);


	jQuery.timepicker.regional['ru'] = {
			timeOnlyTitle: 'Выберите время',
			timeText: 'Время',
			hourText: 'Часы',
			minuteText: 'Минуты',
			secondText: 'Секунды',
			millisecText: 'Миллисекунды',
			timezoneText: 'Часовой пояс',
			currentText: 'Сейчас',
			closeText: 'Закрыть',
			timeFormat: 'HH:mm',
			amNames: ['AM', 'A'],
			pmNames: ['PM', 'P'],
			isRTL: false
		};
	jQuery.timepicker.setDefaults(jQuery.timepicker.regional['ru']);
	
	fileupload(0);
	
	hideall();
	document.getElementById("inv-inventory").style.display = "block";
	
	var spinner = jQuery( "#loc-floor, #loc-eqnumber, #loc-rack" ).spinner({
		change: function(event, ui){code()}
	});

	jQuery('#inv-inv-search').autocomplete({//-------------------------Поиск по базе
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_object&field=name&field2=s_number&field3=i_number&field4=i_number_adv&field5=barcode",
	    maxHeight:150,
	    select: function(event, ui) {
	    	editobject(ui.item.id);
	    }	   
	});	
	
	jQuery("#jqGrid-obj-type").jqGrid({// -------------------------------------------------Таблица типов объектов в диалоге добавления объекта----------------------------------------------------------------
		datatype: "json",
		 colModel: [
			{ label: 'Тип объекта', name: 'type', width: 200},
			{ label: 'Функция', name: 'func', width: 50, hidden: true}
		],
		width: 150,
		height: 'auto',
		rowNum: 30,
		loadonce: true,
		onCellSelect: function (rowid){
			addobject(2, rowid);
		},
	});
	
	function showChildGrid(parentRowID, parentRowKey) {// Показываем компьютер в таблице
		
		var width = jQuery("#inv-worck").innerWidth() - 220;
		 
		var rowdata = jQuery("#jqGrid-dash-objects").getRowData(parentRowKey);
		var objcol;
		jQuery("#jqGrid-dash-objects").getRowData(parentRowKey);
		
		var appstr = '<div style="width: '+ width +'px">';
		for(var key in rowdata){
			console.log( key+':'+rowdata[key]);
			objcol = jQuery("#jqGrid-dash-objects").getColProp(key);
			appstr +='<div class="inv-div-left" style="width: 350px"><div class="inv-div-left"style="width: 100px">'+objcol.label+':</div><div class="inv-div-left"style="width: 200px">'+rowdata[key]+'</div></div>';
		}
		appstr +='</div>';
		jQuery("#" + parentRowID).append(appstr);
		

	};

	jQuery("#savestate").click(function(){// Записываем состояние таблицы
		jQuery.jgrid.saveState("jqGrid-dash-objects");
	});
	jQuery("#loadstate").click(function(){	//Читаем состояние таблицы
		jQuery.jgrid.loadState("jqGrid-dash-objects");
	});
    jQuery("#myZabbix").click(function(){	//Показать записи пользователя для добавления в zabbix

        jQuery.jgrid.loadState("jqGrid-dash-objects");
    });
		
	jQuery("#jqGrid-dash-obj-type").jqGrid({// -------------------------------------------------Таблица типов объектов на дашборде----------------------------------------------------------------
		url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=objecttype',
		datatype: "json",
		 colModel: [
			{ label: 'Тип объекта', name: 'type', width: 200},
			{ label: 'Функция', name: 'func', width: 50, hidden: true}
		],
		width: 150,
		height: 'auto',
		rowNum: 30,
		loadonce: true,
		onSelectRow: function(rowid, selected) {
			if(rowid != null) {
				
				//if (jQuery("#jqGrid-dash-objects").grid) jQuery("#jqGrid-dash-objects").jqGrid('GridUnload');
				
				var colModel2 = [
							{ label: 'id', name: 'id_obj', width: 100, hidden: true},
							{ label: 'Название', name: 'name', width: 150, search: true, hidden: true},
							{ label: 'Дата покупки', name: 'purchase_date', width: 85,  search: true, sorttype:'date'},
							{ label: 'Серийный №', name: 's_number', width: 100,  search: true,
								 searchoptions: {
									dataInit: function (element) {
							                jQuery(element).autocomplete({
							                	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_object&field=s_number",
							                	    maxHeight:150,
							                });	
							        }
								}
							},
							{ label: 'Инв. №1', name: 'i_number', width: 100, search: true,
								searchoptions: {
									dataInit: function (element) {
							            	jQuery(element).autocomplete({//-------------------------Поиск по 
							            	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_object&field=i_number",
							            	    maxHeight:150,
							            	});	
							        }
								}
							},
							{ label: 'Инв. №2', name: 'i_number_adv', width: 100, search: true,
								searchoptions: {
									dataInit: function (element) {
							            window.setTimeout(function () {
							            	jQuery(element).autocomplete({//-------------------------Поиск по 
							            		 source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_object&field=i_number_adv",
							            		    maxHeight:150,
							            	});	
							            }, 100);
							        }
								}
							},
							{ label: 'Штрихкод', name: 'barcode', width: 100, search: true,
								searchoptions: {
									dataInit: function (element) {
							            window.setTimeout(function () {
							            	jQuery(element).autocomplete({//-------------------------Поиск по 
							            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_object&field=barcode",
							            	    maxHeight:150,
							            	});	
							            }, 100);
							        }
								}
							},
							{ label: 'Гарантия', name: 'guaranty', width: 100, search: true, hidden: true,
								searchoptions: {
									dataInit: function (element) {
							            window.setTimeout(function () {
							            	jQuery(element).autocomplete({//-------------------------Поиск по 
							            		 source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_object&field=guaranty",
							            		    maxHeight:150,
							            	});	
							            }, 100);
							        }
								}
							},
							{ label: 'Тип учета', name: 'accounting', width: 100, search: true, hidden: true,
								searchoptions: {
									dataInit: function (element) {
							            window.setTimeout(function () {
							            	jQuery(element).autocomplete({//-------------------------Поиск по 
							            		 source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_object&field=accounting",
							            		    maxHeight:150,
							            	});	
							            }, 100);
							        }
								}
							},
							{ label: 'Производтель', name: 'vendor', width: 150, search: true,
								searchoptions: {
									dataInit: function (element) {
							            window.setTimeout(function () {
							            	jQuery(element).autocomplete({//-------------------------Поиск по 
							            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_vendor&field=vendor",
							            	    maxHeight:150,
							            	});	
							            }, 100);
							        }
								}
							},	
							{ label: 'Страна производителя', name: 'country', width: 150, search: true, hidden: true,
								searchoptions: {
									dataInit: function (element) {
							            window.setTimeout(function () {
							            	jQuery(element).autocomplete({//-------------------------Поиск по 
							            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_country&field=name",
							            	    maxHeight:150,
							            	});	
							            }, 100);
							        }
								}
							},	
							{ label: 'Поставщик', name: 'supplier', width: 150, search: true,
								searchoptions: {
									dataInit: function (element) {
							            window.setTimeout(function () {
							            	jQuery(element).autocomplete({//-------------------------Поиск по 
							            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_supplier&field=supplier",
							            	    maxHeight:150,
							            	});	
							            }, 100);
							        }
								}
							},
							{ label: 'Примечание', name: 'obj_description', width: 100, search: true, hidden: true,
								searchoptions: {
									dataInit: function (element) {
							            window.setTimeout(function () {
							            	jQuery(element).autocomplete({//-------------------------Поиск по 
							            		 source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_object&field=description",
							            		    maxHeight:150,
							            	});	
							            }, 100);
							        }
								}
							},
							{ label: 'Мат. ответственный', name: 'resp_user', width: 100, search: true, hidden: true,
								searchoptions: {
									dataInit: function (element) {
							            window.setTimeout(function () {
							            	jQuery(element).autocomplete({//-------------------------Поиск по 
							            		 source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_object&field=description",
							            		    maxHeight:150,
							            	});	
							            }, 100);
							        }
								}
							},
							{ label: 'Пользователь', name: 'user', width: 100, search: true, hidden: true,
								searchoptions: {
									dataInit: function (element) {
							            window.setTimeout(function () {
							            	jQuery(element).autocomplete({//-------------------------Поиск по 
							            		 source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_object&field=description",
							            		    maxHeight:150,
							            	});	
							            }, 100);
							        }
								}
							},
							{ label: 'Подразделение', name: 'department', width: 100, search: true, hidden: true,
								searchoptions: {
									dataInit: function (element) {
							            window.setTimeout(function () {
							            	jQuery(element).autocomplete({//-------------------------Поиск по 
							            		 source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_object&field=description",
							            		    maxHeight:150,
							            	});	
							            }, 100);
							        }
								}
							},
							{ label: 'Место код', name: 'code', width: 100, search: true, hidden: true,
								searchoptions: {
									dataInit: function (element) {
							            window.setTimeout(function () {
							            	jQuery(element).autocomplete({//-------------------------Поиск по 
							            		 source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_object&field=description",
							            		    maxHeight:150,
							            	});	
							            }, 100);
							        }
								}
							},
							{ label: 'Место регион', name: 'region', width: 100, search: true, hidden: true,
								searchoptions: {
									dataInit: function (element) {
							            window.setTimeout(function () {
							            	jQuery(element).autocomplete({//-------------------------Поиск по 
							            		 source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_object&field=description",
							            		    maxHeight:150,
							            	});	
							            }, 100);
							        }
								}
							},
							{ label: 'Место ПЭС', name: 'pes', width: 100, search: true, hidden: true,
								searchoptions: {
									dataInit: function (element) {
							            window.setTimeout(function () {
							            	jQuery(element).autocomplete({//-------------------------Поиск по 
							            		 source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_object&field=description",
							            		    maxHeight:150,
							            	});	
							            }, 100);
							        }
								}
							},
							{ label: 'Место объект', name: 'po', width: 100, search: true, hidden: true,
								searchoptions: {
									dataInit: function (element) {
							            window.setTimeout(function () {
							            	jQuery(element).autocomplete({//-------------------------Поиск по 
							            		 source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=neq_po&field=name",
							            		    maxHeight:150,
							            	});	
							            }, 100);
							        }
								}
							},
							{ label: 'Место помещ.', name: 'a', width: 100, search: true, hidden: true,
								searchoptions: {
									dataInit: function (element) {
							            window.setTimeout(function () {
							            	jQuery(element).autocomplete({//-------------------------Поиск по 
							            		 source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=neq_a&field=name",
							            		    maxHeight:150,
							            	});	
							            }, 100);
							        }
								}
							},
							];
				
				
				jQuery("#jqGrid-dash-objects").jqGrid('GridUnload');
				var width = jQuery("#inv-worck").innerWidth() - 200;
				var height = jQuery(document).innerHeight() - 240;
				var row = height/23;
				row = Math.round(row);
				switch (jQuery("#jqGrid-dash-obj-type").getCell(rowid, 'func')){
					case 'computer':
						//var gridurl = 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=objects&oper=useraccess&objtype=computer';
						var gridurl = 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=computer&oper=sel';
						var caption = 'Компьютеры';
						colModel2.push({ label: 'Шасси', name: 'chasis', width: 150, search: true,
											searchoptions: {
												dataInit: function (element) {
										            window.setTimeout(function () {
										            	jQuery(element).autocomplete({//-------------------------Поиск по 
										            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_computer&field=chasis&group=1",
										            	    maxHeight:150,
										            	});	
										            }, 100);
										        }
											}
										},	
										{ label: 'Модель', name: 'model', width: 150, search: true,
											searchoptions: {
												dataInit: function (element) {
										            window.setTimeout(function () {
										            	jQuery(element).autocomplete({//-------------------------Поиск по 
										            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_computer&field=model&group=1",
										            	    maxHeight:150,
										            	});	
										            }, 100);
										        }
											}
										},
										{ label: 'OC', name: 'osname', width: 150, search: true,
											searchoptions: {
												dataInit: function (element) {
										            window.setTimeout(function () {
										            	jQuery(element).autocomplete({//-------------------------Поиск по 
										            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_computer&field=osname&group=1",
										            	    maxHeight:150,
										            	});	
										            }, 100);
										        }
											}
										},
										{ label: 'Тип компьютера', name: 'comp_type', width: 150, search: true,
											searchoptions: {
												dataInit: function (element) {
										            window.setTimeout(function () {
										            	jQuery(element).autocomplete({//-------------------------Поиск по 
										            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_comp_type&field=type&group=1",
										            	    maxHeight:150,
										            	});	
										            }, 100);
										        }
											}
										},	
										{ label: 'Процессор', name: 'processor', width: 150, search: true,
											searchoptions: {
												dataInit: function (element) {
										            window.setTimeout(function () {
										            	jQuery(element).autocomplete({//-------------------------Поиск по 
										            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_comp_hardware&field=processor&group=1",
										            	    maxHeight:150,
										            	});	
										            }, 100);
										        }
											}
										},
										{ label: 'Память', name: 'mem', width: 150, search: true,
											searchoptions: {
												dataInit: function (element) {
										            window.setTimeout(function () {
										            	jQuery(element).autocomplete({//-------------------------Поиск по 
										            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_comp_hardware&field=mem&group=1",
										            	    maxHeight:150,
										            	});	
										            }, 100);
										        }
											}
										},
										{ label: 'Блок питания', name: 'psu', width: 150, search: true,
											searchoptions: {
												dataInit: function (element) {
										            window.setTimeout(function () {
										            	jQuery(element).autocomplete({//-------------------------Поиск по 
										            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_comp_hardware&field=psu&group=1",
										            	    maxHeight:150,
										            	});	
										            }, 100);
										        }
											}
										},
										{ label: 'Жестки диски', name: 'hdd', width: 150, search: true,
											searchoptions: {
												dataInit: function (element) {
										            window.setTimeout(function () {
										            	jQuery(element).autocomplete({//-------------------------Поиск по 
										            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_comp_hardware&field=hdd&group=1",
										            	    maxHeight:150,
										            	});	
										            }, 100);
										        }
											}
										},										
										{ label: 'Материнская плата', name: 'motherboard', width: 150, search: true,
											searchoptions: {
												dataInit: function (element) {
										            window.setTimeout(function () {
										            	jQuery(element).autocomplete({//-------------------------Поиск по 
										            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_comp_hardware&field=motherboard&group=1",
										            	    maxHeight:150,
										            	});	
										            }, 100);
										        }
											}
										},
										{ label: 'Графические карты', name: 'graphics', width: 150, search: true,
											searchoptions: {
												dataInit: function (element) {
										            window.setTimeout(function () {
										            	jQuery(element).autocomplete({//-------------------------Поиск по 
										            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_comp_hardware&field=graphics&group=1",
										            	    maxHeight:150,
										            	});	
										            }, 100);
										        }
											}
										},
										{ label: 'Сеть', name: 'network', width: 150, formatter: 'checkbox'},
										{ label: 'MAC адрес', name: 'mac', width: 150, search: true,
											searchoptions: {
												dataInit: function (element) {
										            window.setTimeout(function () {
										            	jQuery(element).autocomplete({//-------------------------Поиск по 
										            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_comp_hardware&field=mac&group=1",
										            	    maxHeight:150,
										            	});	
										            }, 100);
										        }
											}
										},
										{ label: 'Конфигурация', name: 'config', width: 150, search: true,
											searchoptions: {
												dataInit: function (element) {
										            window.setTimeout(function () {
										            	jQuery(element).autocomplete({//-------------------------Поиск по 
										            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_computer&field=config&group=1",
										            	    maxHeight:150,
										            	});	
										            }, 100);
										        }
											}
										},
										{ label: 'Доменное имя', name: 'ad_name', width: 150, search: true,
											searchoptions: {
												dataInit: function (element) {
										            window.setTimeout(function () {
										            	jQuery(element).autocomplete({//-------------------------Поиск по 
										            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_adpc&field=cn",
										            	    maxHeight:150,
										            	});	
										            }, 100);
										        }
											}
										},
										{ label: 'Имя в zabbix', name: 'zabbix_name', width: 150, search: true,
											searchoptions: {
												dataInit: function (element) {
										            window.setTimeout(function () {
										            	jQuery(element).autocomplete({//-------------------------Поиск по 
										            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_zabbix&field=host",
										            	    maxHeight:150,
										            	});	
										            }, 100);
										        }
											}
										},
										{ label: 'Имя в OCS', name: 'ocs_name', width: 150, search: true,
											searchoptions: {
												dataInit: function (element) { 
										            window.setTimeout(function () {
										            	jQuery(element).autocomplete({//-------------------------Поиск по 
										            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_ocs&field=name",
										            	    maxHeight:150,
										            	});	
										            }, 100);
										        }
											}
										}
										);

						break;
					case 'monitor':
						var gridurl = 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=monitor&oper=sel';
						var caption = 'Мониторы';
						colModel2.push(
								{ label: 'Шасси', name: 'chasis', width: 150, search: true,
									searchoptions: {
										dataInit: function (element) {
								            window.setTimeout(function () {
								            	jQuery(element).autocomplete({//-------------------------Поиск по 
								            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_monitor&field=chasis&group=1",
								            	    maxHeight:150,
								            	});	
								            }, 100);
								        }
									}
								},	
								{ label: 'Модель', name: 'model', width: 150, search: true,
									searchoptions: {
										dataInit: function (element) {
								            window.setTimeout(function () {
								            	jQuery(element).autocomplete({//-------------------------Поиск по 
								            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_monitor&field=model&group=1",
								            	    maxHeight:150,
								            	});	
								            }, 100);
								        }
									}
								},
								{ label: 'Диагональ', name: 'size', width: 150, search: true},	
								{ label: 'Широкоформатный', name: 'format', width: 150, formatter: 'checkbox'},
								{ label: 'Разрешение', name: 'resolution', width: 150, search: true,
									searchoptions: {
										dataInit: function (element) {
								            window.setTimeout(function () {
								            	jQuery(element).autocomplete({//-------------------------Поиск по 
								            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_monitor&field=resolution&group=1",
								            	    maxHeight:150,
								            	});	
								            }, 100);
								        }
									}
								},
								{ label: 'ЖК', name: 'led', width: 150, search: true, formatter: 'checkbox'},
								{ label: 'Динамики', name: 'dynamics', width: 150, search: true, formatter: 'checkbox'},
								{ label: 'Видео входы', name: 'video_inputs', width: 150, search: true,
									searchoptions: {
										dataInit: function (element) {
								            window.setTimeout(function () {
								            	jQuery(element).autocomplete({//-------------------------Поиск по 
								            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_monitor&field=video_inputs&group=1",
								            	    maxHeight:150,
								            	});	
								            }, 100);
								        }
									}
								},
								{ label: 'Мощьность', name: 'power', width: 150, search: true,
									searchoptions: {
										dataInit: function (element) {
								            window.setTimeout(function () {
								            	jQuery(element).autocomplete({//-------------------------Поиск по 
								            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_monitor&field=power&group=1",
								            	    maxHeight:150,
								            	});	
								            }, 100);
								        }
									}
								},
								{ label: 'Тип матрицы', name: 'matrix_type', width: 150, search: true,
									searchoptions: {
										dataInit: function (element) {
								            window.setTimeout(function () {
								            	jQuery(element).autocomplete({//-------------------------Поиск по 
								            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_mon_matrix_type&field=type&group=1",
								            	    maxHeight:150,
								            	});	
								            }, 100);
								        }
									}
								},
								{ label: 'Описание', name: 'description', width: 150, search: true,
									searchoptions: {
										dataInit: function (element) {
								            window.setTimeout(function () {
								            	jQuery(element).autocomplete({//-------------------------Поиск по 
								            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_monitor&field=description&group=1",
								            	    maxHeight:150,
								            	});	
								            }, 100);
								        }
									}
								});
						break;
					case 'prn':
						var gridurl = 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=prn&oper=sel';
						var caption = 'Принтеры/МФУ/сканеры';
						colModel2.push(
								{ label: 'Тип устройства', name: 'print_type', width: 150, search: true},
								{ label: 'Шасси', name: 'chasis', width: 150, search: true,
									searchoptions: {
										dataInit: function (element) {
								            window.setTimeout(function () {
								            	jQuery(element).autocomplete({//-------------------------Поиск по 
								            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_prn&field=chasis&group=1",
								            	    maxHeight:150,
								            	});	
								            }, 100);
								        }
									}
								},	
								{ label: 'Модель', name: 'model', width: 150, search: true,
									searchoptions: {
										dataInit: function (element) {
								            window.setTimeout(function () {
								            	jQuery(element).autocomplete({//-------------------------Поиск по 
								            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_prn&field=model&group=1",
								            	    maxHeight:150,
								            	});	
								            }, 100);
								        }
									}
								},
								{ label: 'Имя серв. печ.', name: 'print_srv_name', width: 150, search: true},	
								{ label: 'Имя в zabbix', name: 'zabbix_name', width: 150, search: true},
								{ label: 'Тип печати', name: 'type_print', width: 150, search: true},
								{ label: 'Цветной', name: 'color', width: 150, formatter: 'checkbox'},
								{ label: 'Макс.формат', name: 'format', width: 150, search: true},
								{ label: 'Фото', name: 'photo', width: 150, formatter: 'checkbox'},
								{ label: '2-ч строр.печ.', name: 'duplex_printing', width: 150, formatter: 'checkbox'},
								{ label: 'В сети', name: 'ethernet', width: 150, formatter: 'checkbox'},
								{ label: 'Стетевой инт.', name: 'ethernet', width: 150, formatter: 'checkbox'},
								{ label: 'MAC адрес', name: 'prn_mac', width: 150, search: true},
								{ label: 'WIFI', name: 'wifi', width: 150, formatter: 'checkbox'},
								{ label: 'Скор. печати', name: 'speed_print', width: 150, search: true},
								{ label: 'Скор. сканир', name: 'speed_scan', width: 150, search: true},
								{ label: 'Память', name: 'mem', width: 150, search: true},
								{ label: 'Отпечатано листов', name: 'sheets', width: 150, search: true},
								{ label: 'Дата,отпеч.листов', name: 'date_sheets', width: 150, search: true, hidden: true},
								{ label: 'fax', name: 'fax', width: 150, formatter: 'checkbox'},
								{ label: 'Описание', name: 'prn_description', width: 150, search: true});
						break;
					case 'sup':
						var gridurl = 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=sup&oper=sel';
						var caption = 'Расходные материалы';
						colModel2.push(
								{ label: 'Тип материала', name: 'sup_type_name', width: 150, search: true},
								{ label: 'Название', name: 'model', width: 150, search: true,
									searchoptions: {
										dataInit: function (element) {
								            window.setTimeout(function () {
								            	jQuery(element).autocomplete({//-------------------------Поиск по 
								            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_supplies&field=model&group=1",
								            	    maxHeight:150,
								            	});	
								            }, 100);
								        }
									}
								},	
								{ label: 'Количество', name: 'sup_count', width: 150, search: true},
								{ label: 'Свойство1', name: 'sup_field1', width: 150, search: true,
									searchoptions: {
										dataInit: function (element) {
								            window.setTimeout(function () {
								            	jQuery(element).autocomplete({//-------------------------Поиск по 
								            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_supplies&field=sup_field1&group=1",
								            	    maxHeight:150,
								            	});	
								            }, 100);
								        }
									}
								},
								{ label: 'Свойство2', name: 'sup_field2', width: 150, search: true,
									searchoptions: {
										dataInit: function (element) {
								            window.setTimeout(function () {
								            	jQuery(element).autocomplete({//-------------------------Поиск по 
								            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_supplies&field=sup_field2&group=1",
								            	    maxHeight:150,
								            	});	
								            }, 100);
								        }
									}
								},
								{ label: 'Свойство3', name: 'sup_field3', width: 150, search: true,
									searchoptions: {
										dataInit: function (element) {
								            window.setTimeout(function () {
								            	jQuery(element).autocomplete({//-------------------------Поиск по 
								            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_supplies&field=sup_field3&group=1",
								            	    maxHeight:150,
								            	});	
								            }, 100);
								        }
									}
								},
								{ label: 'Свойство4', name: 'sup_field4', width: 150, search: true,
									searchoptions: {
										dataInit: function (element) {
								            window.setTimeout(function () {
								            	jQuery(element).autocomplete({//-------------------------Поиск по 
								            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_supplies&field=sup_field4&group=1",
								            	    maxHeight:150,
								            	});	
								            }, 100);
								        }
									}
								},
								{ label: 'Свойство5', name: 'sup_field5', width: 150, search: true,
									searchoptions: {
										dataInit: function (element) {
								            window.setTimeout(function () {
								            	jQuery(element).autocomplete({//-------------------------Поиск по 
								            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_supplies&field=sup_field5&group=1",
								            	    maxHeight:150,
								            	});	
								            }, 100);
								        }
									}
								},
								{ label: 'Описание', name: 'sup_description', width: 150, search: true,
									searchoptions: {
										dataInit: function (element) {
								            window.setTimeout(function () {
								            	jQuery(element).autocomplete({//-------------------------Поиск по 
								            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_supplies&field=sup_description&group=1",
								            	    maxHeight:150,
								            	});	
								            }, 100);
								        }
									}
								}
							);
						break;
				}
				jQuery("#jqGrid-dash-objects").jqGrid({// -------------------------------------------------Таблица объектов----------------------------------------------------------------
					url: gridurl,
					editurl: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=objects',
					datatype: "json",
					colModel: colModel2,
					viewrecords: true,
					multiSort: true,
					sortname: 'name',
					width: width,
					height: height,
					rowNum: row,
					loadonce: false,
					subGrid: true,
		            subGridRowExpanded: showChildGrid,
		            ondblClickRow: function(rowid, selected){
		    			editobject(rowid);
		    		},
		            shrinkToFit: false,
					multiselect: true,
					sortable: true,
					caption: caption,
					//gridComplete: initGrid,
					pager: "#jqGridPager-dash-objects"
				});
				jQuery('#jqGrid-dash-objects').jqGrid('filterToolbar');
				jQuery("#jqGrid-dash-objects").jqGrid('gridResize',{minWidth:600,maxWidth:width,minHeight:300, maxHeight:height});;
				jQuery('#jqGrid-dash-objects').navGrid('#jqGridPager-dash-objects',
			            { edit: false, add: false, del: true, search: true, refresh: true, view: true, position: "left", cloneToTop: false }           
			    );
				jQuery('#jqGrid-dash-objects').navButtonAdd('#jqGridPager-dash-objects',
	                {
	                    buttonicon: "ui-icon-calculator",
	                    title: "Выбор столбцов",
	                    caption: "Столбцы",
	                    position: "last",
	                    onClickButton: function() {
							// call the column chooser method
							jQuery("#jqGrid-dash-objects").jqGrid('columnChooser');
						}
	                }	                
				);
				jQuery('#jqGrid-dash-objects').navButtonAdd('#jqGridPager-dash-objects',
		                {
		                    buttonicon: "ui-icon ui-icon-pencil",
		                    title: "Изменить объект",
		                    caption: "Изменить",
		                    position: "last",
		                    onClickButton: function() {
		                        var rowKey = jQuery("#jqGrid-dash-objects").jqGrid('getGridParam',"selrow");
		                        if (rowKey)
		                            editobject(rowKey,jQuery("#jqGrid-dash-obj-type").getCell(rowid, 'func'));
		                        else
		                            alert("Выберите объект");		                    	
							}
		                }	                
				);
				jQuery('#jqGrid-dash-objects').navButtonAdd('#jqGridPager-dash-objects',
		                {
		                    buttonicon: "ui-icon-disk",
		                    title: "Экспорт в excel",
		                    caption: "Excel",
		                    position: "last",
		                    onClickButton: function() {
		                    	var grid = jQuery("#jqGrid-dash-objects");
		                    	var mytemp = grid.setColProp('model', {label: 'asdfa'});
		                    	console.log(mytemp);
		                        	                    	
							}
		                }	                
				);
								
			}
		}
		
	});
	jQuery(function() {//Инициализация радио кнопок основного меню
		jQuery( "#radio" ).buttonset();
	  });
	
	jQuery('#inv-obj-location').dialog({//Диалог определения местоположения
		autoOpen: false,
		height: 700,
		width: 1170,
		modal: true,
		resizable: false,
		open: function( event, ui ) {	
		},
		buttons: {
			'Ок': function(){
				
				obj.locclear();//Отчищаем местоположение объекта
								
				if (jQuery("#loc-pes").val() == ''){//Проверка доставточности ввода
					alert ('Не достаточно данных');
					return false;
				}
				if (jQuery("#loc-obj").val() == '' && loc_dlg.lvl > 1){
					alert ('Не достаточно данных');
					return false;
				}
				if (jQuery("#loc-a").val() == '' && loc_dlg.lvl > 2){
					alert ('Не достаточно данных');
					return false;
				}
				
		        obj.read_location_form('#inv-obj-location');//Заносим данные по местонахождению в соответствующие переменные
		        obj.location.date = NowDate();
		        obj.write_location_form(loc_dlg.strdiv); //Заносим данные по местонахождению в соответствующие поля
		        
				
				var rowKey = jQuery("#jqGrid-location-region").jqGrid('getGridParam',"selrow");//Заносим соответсвующие ID местоположения объекта
				obj.location.id_reg = rowKey;
				
				rowKey = jQuery("#jqGrid-location-pes").jqGrid('getGridParam',"selrow");
				obj.location.id_pes = rowKey;
				obj.location.code_pes = jQuery("#jqGrid-location-pes").getCell(rowKey, 'code');
				
				rowKey = jQuery("#jqGrid-location-obj").jqGrid('getGridParam',"selrow");
				obj.location.id_obj = rowKey;
				obj.location.code_obj = '-' + jQuery("#jqGrid-location-obj").getCell(rowKey, 'code');
				
				rowKey = jQuery("#jqGrid-location-object").jqGrid('getGridParam',"selrow");
				if (rowKey){
					obj.location.id_obj = rowKey;
					obj.location.code_obj = jQuery("#jqGrid-location-object").getCell(rowKey, 'number');							
				}
				
				rowKey = jQuery("#jqGrid-location-a").jqGrid('getGridParam',"selrow");
				if (rowKey){
					obj.location.id_a = rowKey;
					var number = jQuery("#jqGrid-location-a").getCell(rowKey, 'code');
					if (number < 10) number = '0' + number;
					obj.location.code_a = 'a'+number;
				}
				
				obj.edit.location = true;
				obj.prop.id_store = 'null';
				
				jQuery(this).dialog('close');
				
			},			
			'Отмена': function(){
				jQuery(this).dialog('close');
				
			}
		}
		
	});
	
	
	jQuery('#inv-addobject').dialog({//Диалог добавления объекта
		autoOpen: false,
		height: 750,
		width: 1250,
		modal: true,
		resizable: false,
		open: function( event, ui ) {
						
		},
		buttons: {
			'Добавить': function(){
				addobjectdb();
			},
			'Добавить и клонировать': function(){
				addobjectdb('clone');				
			},
			'Отмена': function(){
				jQuery(this).dialog('close');
				
			}
		}
		
	});
	
	jQuery('#inv-dlg-find').dialog({//Диалог поиска объекта
		autoOpen: false,
		height: 500,
		width: 1200,
		modal: true,
		resizable: false,
		open: function( event, ui ) {
						
		},
		buttons: {
			'Ок': function(){
				var grid = jQuery("#jqGrid-obj-find");
				var rowKey = grid.jqGrid('getGridParam',"selrow");
				if (rowKey){
					jQuery(obj_find_dlg.strdiv+' #id_object').data('value_id', rowKey);
					jQuery(obj_find_dlg.strdiv+' #id_object').val(grid.getCell(rowKey, 'name')+' id:'+rowKey);
					jQuery(this).dialog('close');
				}else alert ("Выберите объект");
				 
			},
			'Отмена': function(){
				jQuery(this).dialog('close');
				
			}
		}
		
	});
	
	jQuery('#inv-dlg-sup-store').dialog({//Диалог списания объекта
		autoOpen: false,
		height: 500,
		width: 600,
		modal: true,
		resizable: false,
		open: function( event, ui ) {
						
		},
		buttons: {
			'Списать': function(){
				var rowID = jQuery(obj_find_dlg.grid).jqGrid('getGridParam',"selrow");
				var edit_deb = {
					id_sup: jQuery(obj_find_dlg.grid).getCell(rowID, 'id_sup'),
					id_obj: jQuery(obj_find_dlg.strdiv+' #id_object').data('value_id'),
					id_user_for:  jQuery(obj_find_dlg.strdiv+' #sup-user').data('value_id'),
					deb_count: jQuery(obj_find_dlg.strdiv+' #sup-count').val(),
					deb_date: jQuery(obj_find_dlg.strdiv+' #sup-date').val()
				};
				if (!edit_deb.id_obj && !edit_deb.id_user_for){//Если введер объект или пользователь
					alert ('Введите пользователя или объект');
					return false;
				}				
				if (edit_deb.deb_count === ''){//Если не введено кол-во
					alert ('Введите кол-во списываемого');
					return false;
				}				
				if (edit_deb.deb_date === ''){//Если не введена дата
					alert ('Введите дату');
					return false;
				}
				
				var reg = 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=storparts&oper=debit';
				jQuery.ajax({
			        type:'GET', cache:false, dataType:'html',
			        url:reg,
			        data: edit_deb,
			        success:function (data) {
			        	jQuery('#inv-dlg-sup-store').dialog('close');
			        	jQuery(obj_find_dlg.grid).trigger("reloadGrid");
			        },
			        error: function (data){
			        	alert ('Ошибка списания');
			        }
			    });
				
			},
			'Отмена': function(){
				jQuery(this).dialog('close');
				
			}
		}
		
	});
	
	jQuery('#inv-info-object').dialog({//Диалог информации по объекту
		autoOpen: false,
		height: 750,
		width: 1200,
		modal: true,
		resizable: false,
		open: function( event, ui ) {
						
		},
		buttons: {
			'Обновить': function(){
				editobjectdb();
			},
			'ОК': function(){
				jQuery(this).dialog('close');				
			},
			'Отмена': function(){
				jQuery(this).dialog('close');				
			}
		}
		
	});
	
	jQuery("#inv-obj-date").datepicker({
        id: 'orderDate_datePicker',
        dateFormat: 'yy.mm.dd',
        changeMonth: true,
        changeYear: true,
        yearRange: "1940:2040",
        showOn: 'focus'
    });
	
	
	jQuery( "#inv-info-accordion, #inv-info-accordion-config" ).accordion({//Инициализация аккордион меню
		heightStyle: "content"
	});
		
	//--------------------------------Табы ----------------------------------------
		jQuery( ".inv-tabs, #tabs-adm, #tabs-print, #tabs-conf, #tabs-1, #tab-object, #tabs-computer, #tabs-location, #tabs-ad, #inv-info-tab-object, #inv-info-computer" ).tabs({
			heightStyle: "content"	
		});
		
	jQuery(function() {
		jQuery( ".inv-button" )
	      .button()
	      .click(function( event ) {
	        event.preventDefault();
	      });
	  });
	
	jQuery("#add-obj-form").validate({
	       rules:{
	    	   inv_obj_name:{ required: true, date: true },
	    	   inv_obj_date:{ required: true, date: true },
	           inv_guaranty:{ number: true },
	       },
	       messages:{
	    	   inv_obj_date:{ required: "Это поле обязательно для заполнения", date: "Должна быть дата" },
	            inv_guaranty:{ number: "Гарантия задается числом" },
	       }
	});
	jQuery("#add-pc-form").validate({
	       rules:{
	    	   inv_pc_chasis:{ required: true },
	    	   inv_pc_config:{ required: true },
	       },
	       messages:{
	    	   inv_pc_chasis:{ equired: "Это поле обязательно для заполнения" },
	    	   inv_pc_config:{ equired: "Это поле обязательно для заполнения" }	    	   
	       }
	});
	
	jQuery("#stores").click(function(){	//Читаем состояние таблицы
		hideall();
		document.getElementById("inv-stores").style.display = "block"; 
		jQuery("#jqGrid-stores").jqGrid('setGridParam',{url: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=storparts&oper=sel"});
		jQuery("#jqGrid-stores").trigger("reloadGrid");		
	});
	
	jQuery("#obj-add-loc-btn").click(function () {
		jQuery("#jqGrid-obj-region-stores").trigger("reloadGrid");
		jQuery("#jqGrid-obj-conf-stores").trigger("reloadGrid");
		objlocation('#inv-addobject', '2');
	});
	
	jQuery("#obj-info-loc-btn").click(function () {
		jQuery("#jqGrid-obj-info-region-stores").trigger("reloadGrid");
		jQuery("#jqGrid-obj-info-conf-stores").trigger("reloadGrid");
		objlocation('#inv-info-object', '2');
	});

	jQuery("a[href='#tabs-adm-journal']").click(function () {//Нажали на вкладку журнал
		jQuery("#jqGrid-adm-journal").jqGrid('setGridParam',{url: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=journal&oper=sel"});
		jQuery("#jqGrid-adm-journal").trigger("reloadGrid");
	});	
	jQuery("a[href='#tabs-print']").click(function () {//Нажали на вкладку принтеры
		jQuery("#jqGrid-print-cartridge").jqGrid('setGridParam',{url: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=cartridge&oper=sel"});
		jQuery("#jqGrid-print-cartridge").trigger("reloadGrid");
		jQuery("#jqGrid-print-printers").jqGrid('setGridParam',{url: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=prntemplate&oper=sel"});
		jQuery("#jqGrid-print-printers").trigger("reloadGrid");
		select('#jqGrid-print-color', '#jqGridPager-print-color', 'inv_prn_color', 'color_print', 'Цвета печати', 'Цвет', 250, 400);//Типы МФУ
		select('#jqGrid-print-type', '#jqGridPager-print-type', 'inv_prn_type', 'print_type', 'Тип MФУ', 'Тип', 250, 400);//Типы МФУ
    	select('#jqGrid-print-type-print', '#jqGridPager-prn-type-print', 'inv_prn_type_print', 'print_type_print', 'Тип печати', 'Тип', 250, 400);//Типы МФУ
    	select('#jqGrid-print-format', '#jqGridPager-print-format', 'inv_prn_format', 'format_print', 'Формат печати', 'Формат', 250, 400);//Типы МФУ
    	select('#jqGrid-print-scan', '#jqGridPager-print-scan', 'inv_prn_type_scan', 'type_scan', 'Типы сканера', 'Тип', 250, 400);//Типы сканирования
	});	
	
	jQuery("#jqGrid-obj-region-stores").jqGrid({//-------------------------------------------------------------------Таблица регионов-------------------------------------------------
		url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=accessselect&select=region',
		datatype: "json",
		 colModel: [{ label: 'Регион', name: 'name', width: 20},
		 			{ label: 'Изменение', name: 'reg_write', width: 20, hidden: true},
		 			{ label: 'Чтение', name: 'reg_read', width: 20, hidden: true}],
		
		viewrecords: false,
		width: 300,
		height: 300,
		rowNum: 50,
		onSelectRow: function(rowid, selected) {
			if(rowid != null) {
				jQuery("#jqGrid-obj-conf-stores").jqGrid('setGridParam',{url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=stores&oper=sel&id_region='+rowid});
				jQuery("#jqGrid-obj-conf-stores").jqGrid('setCaption', 'Регион::'+jQuery(this).getCell(rowid, 'name'));
				jQuery("#jqGrid-obj-conf-stores").jqGrid('clearGridData');
				jQuery("#jqGrid-obj-conf-stores").trigger("reloadGrid");    				
			}					
		},
	});
	jQuery("#jqGrid-obj-conf-stores").jqGrid({//-------------------------------------------------------------------Таблица складов-------------------------------------------------
		datatype: "json",
		 colModel: [{ label: 'Название склада', name: 'name', width: 200},
		            { label: 'Код', name: 'code', width: 200},
		 			{ label: 'ПО', name: 'pes', width: 150},
		 			{ label: 'Объект', name: 'po', width: 150},
		 			{ label: 'Помещение', name: 'a', width: 150},
		 			{ label: 'id_region', name: 'id_region', width: 10, hidden: true},
		 			{ label: 'id_pes', name: 'id_pes', width: 10, hidden: true},
		 			{ label: 'id_po', name: 'id_po', width: 10, hidden: true},
		 			{ label: 'id_a', name: 'id_a', width: 10, hidden: true},
		 			{ label: 'latitude', name: 'latitude', width: 10, hidden: true},
		 			{ label: 'longitude', name: 'longitude', width: 10, hidden: true},
		 			{ label: 'altitude', name: 'altitude', width: 10, hidden: true}],
		
		viewrecords: false,
		width: 650,
		height: 300,
		rowNum: 50,
		onSelectRow: function(rowid, selected) {
			if(rowid != null) {
				obj.location.code = jQuery(this).getCell(rowid, 'code');//Заносим данные по местонахождению в соответствующие переменные
				obj.location.reg = jQuery("#jqGrid-obj-region-stores").getCell(jQuery("#jqGrid-obj-region-stores").jqGrid('getGridParam',"selrow"), 'name');
				obj.location.pes = jQuery(this).getCell(rowid, 'pes');
		        obj.location.obj = jQuery(this).getCell(rowid, 'po');
		        obj.location.a = jQuery(this).getCell(rowid, 'a');
		        
		        obj.location.id_reg = jQuery(this).getCell(rowid, 'id_region');
				obj.location.id_pes = jQuery(this).getCell(rowid, 'id_pes');
		        obj.location.id_obj = jQuery(this).getCell(rowid, 'id_po');
		        obj.location.id_a = jQuery(this).getCell(rowid, 'id_a');
		        obj.location.latitude = jQuery(this).getCell(rowid, 'latitude');
		        obj.location.longitude = jQuery(this).getCell(rowid, 'longitude');
		        obj.location.altitude = jQuery(this).getCell(rowid, 'altitude');
		        obj.prop.id_store = rowid;
		        
				obj.write_location_form('#inv-addobject');
			}					
		},
	});
	
	jQuery("#jqGrid-obj-info-region-stores").jqGrid({//-------------------------------------------------------------------Таблица регионов-------------------------------------------------
		url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=accessselect&select=region',
		datatype: "json",
		 colModel: [{ label: 'Регион', name: 'name', width: 20},
		 			{ label: 'Изменение', name: 'reg_write', width: 20, hidden: true},
		 			{ label: 'Чтение', name: 'reg_read', width: 20, hidden: true}],
		
		viewrecords: false,
		width: 300,
		height: 250,
		rowNum: 50,
		onSelectRow: function(rowid, selected) {
			if(rowid != null) {
				jQuery("#jqGrid-obj-info-conf-stores").jqGrid('setGridParam',{url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=stores&oper=sel&id_region='+rowid});
				jQuery("#jqGrid-obj-info-conf-stores").jqGrid('setCaption', 'Регион::'+jQuery(this).getCell(rowid, 'name'));
				jQuery("#jqGrid-obj-info-conf-stores").jqGrid('clearGridData');
				jQuery("#jqGrid-obj-info-conf-stores").trigger("reloadGrid");    				
			}					
		},
	});
	jQuery("#jqGrid-obj-info-conf-stores").jqGrid({//-------------------------------------------------------------------Таблица складов-------------------------------------------------
		url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=stores&oper=sel&id_region=0',
		datatype: "json",
		 colModel: [{ label: 'Название склада', name: 'name', width: 200},
		            { label: 'Код', name: 'code', width: 200},
		 			{ label: 'ПО', name: 'pes', width: 150},
		 			{ label: 'Объект', name: 'po', width: 150},
		 			{ label: 'Помещение', name: 'a', width: 150},
		 			{ label: 'id_region', name: 'id_region', width: 10, hidden: true},
		 			{ label: 'id_pes', name: 'id_pes', width: 10, hidden: true},
		 			{ label: 'id_po', name: 'id_po', width: 10, hidden: true},
		 			{ label: 'id_a', name: 'id_a', width: 10, hidden: true},
		 			{ label: 'latitude', name: 'latitude', width: 10, hidden: true},
		 			{ label: 'longitude', name: 'longitude', width: 10, hidden: true},
		 			{ label: 'altitude', name: 'altitude', width: 10, hidden: true}],
		
		viewrecords: false,
		width: 650,
		height: 250,
		rowNum: 50,
		onSelectRow: function(rowid, selected) {
			if(rowid != null) {
				obj.location.code = jQuery(this).getCell(rowid, 'code');//Заносим данные по местонахождению в соответствующие переменные
				obj.location.reg = jQuery("#jqGrid-obj-info-region-stores").getCell(jQuery("#jqGrid-obj-info-region-stores").jqGrid('getGridParam',"selrow"), 'name');
				obj.location.pes = jQuery(this).getCell(rowid, 'pes');
		        obj.location.obj = jQuery(this).getCell(rowid, 'po');
		        obj.location.a = jQuery(this).getCell(rowid, 'a');
		        
		        obj.location.id_reg = jQuery(this).getCell(rowid, 'id_region');
				obj.location.id_pes = jQuery(this).getCell(rowid, 'id_pes');
		        obj.location.id_obj = jQuery(this).getCell(rowid, 'id_po');
		        obj.location.id_a = jQuery(this).getCell(rowid, 'id_a');
		        
		        obj.location.latitude = jQuery(this).getCell(rowid, 'latitude');
		        obj.location.longitude = jQuery(this).getCell(rowid, 'longitude');
		        obj.location.altitude = jQuery(this).getCell(rowid, 'altitude');
		        obj.prop.id_store = rowid;
		        
		        obj.edit.location = true;
		        obj.location.date = NowDate();
		        
				obj.write_location_form('#inv-info-object');
			}					
		},
	});
	
	//-----------------------------------------------------------------------Инициализация полей форм----------------------------------------------------
	
	jQuery("#inv-obj-name, #inv-info-object #inv-obj-name").autocomplete({//-------------------------Поиск по Имени
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_object&field=name&group=1",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.edit.obj = true;
	    }	
	});
	
	jQuery('#inv-s-number, #inv-info-object #inv-s-number').autocomplete({//-------------------------Поиск по серийному номеру
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_object&field=s_number",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.edit.obj = true;
	    }
	});	
	jQuery('#inv-i-number, #inv-info-object #inv-i-number').autocomplete({//-------------------------Поиск по Инвентарному номеру
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_object&field=i_number",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.edit.obj = true;
	    }
	});	
	jQuery('#inv-i-number-adv, #inv-info-object #inv-i-number-adv').autocomplete({//-------------------------Поиск по Инвентарному номеру
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_object&field=i_number_adv",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.edit.obj = true;
	    }
	});	
	selectunit("#inv-addobject #inv-type-accounting-sp", "inv-type-accounting", '', 'inv_type_accounting', 'accounting');//------Тип Учета	
	
	selectunit("#inv-info-object #inv-type-accounting-sp", "inv-type-accounting", '', 'inv_type_accounting', 'accounting');//------Тип Учета
	
	jQuery('#inv-addobject #inv-id-vendor, #inv-info-object #inv-id-vendor').autocomplete({//-------------------------Поиск по производителю
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_vendor&field=vendor",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.prop.id_vendor = ui.item.id;
	    	obj.edit.obj = true;
	    	obj.prop.vendor = ui.item.value;
	    }	   
	});
	jQuery('#inv-addobject #inv-id-vendor').focusout(function(){
		  if (obj.prop.vendor != jQuery('#inv-addobject #inv-id-vendor').val()){
			  obj.prop.vendor = 'null';
			  obj.prop.id_vendor = 'null';
		  }
		  if (obj.prop.id_vendor == 'null') jQuery('#inv-addobject #inv-id-vendor').val('');
	});
	jQuery('#inv-info-object #inv-id-vendor').focusout(function(){
		  if (obj.prop.vendor != jQuery('#inv-info-object #inv-id-vendor').val()){
			  obj.prop.vendor = 'null';
			  obj.prop.id_vendor = 'null';
		  }
		  if (obj.prop.id_vendor == 'null') jQuery('#inv-info-object #inv-id-vendor').val('');
	});
	
	jQuery('#inv-addobject #inv-id-supplier, #inv-info-object #inv-id-supplier').autocomplete({//-------------------------Поиск по Поставщику
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_supplier&field=supplier",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.prop.id_supplier = ui.item.id;
	    	obj.edit.obj = true;
	    	obj.prop.supplier = ui.item.value;
	    }	   
	});	
	jQuery('#inv-addobject #inv-id-supplier').focusout(function(){
		  if (obj.prop.supplier != jQuery('#inv-addobject #inv-id-supplier').val()){
			  obj.prop.supplier = 'null';
			  obj.prop.id_supplier = 'null';
		  }
		  if (obj.prop.id_vendor == 'null') jQuery('#inv-addobject #inv-id-supplier').val('');
	});
	jQuery('#inv-info-object #inv-id-supplier').focusout(function(){
		  if (obj.prop.supplier != jQuery('#inv-info-object #inv-id-supplier').val()){
			  obj.prop.supplier = 'null';
			  obj.prop.id_supplier = 'null';
		  }
		  if (obj.prop.id_supplier == 'null') jQuery('#inv-info-object #inv-id-supplier').val('');
	});
	
	jQuery('#inv-addobject #inv-resp-user, #inv-info-object #inv-resp-user').autocomplete({//-------------------------Поиск по Матответственному
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=respuser&oper=autocomplete&id_reg=1",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.prop.id_resp_user = ui.item.id;
	    	obj.edit.obj = true;
	    	obj.edit.resp_user = true;
	    	obj.prop.resp_user = ui.item.value;
	    }	   
	});	
	jQuery('#inv-addobject #inv-resp-user').focusout(function(){
		  if (obj.prop.resp_user != jQuery('#inv-addobject #inv-resp-user').val()){
			  obj.prop.resp_user = 'null';
			  obj.prop.id_resp_user = 'null';
		  }
		  if (obj.prop.id_resp_user == 'null') jQuery('#inv-addobject #inv-resp-user').val('');
	});
	jQuery('#inv-info-object #inv-resp-user').focusout(function(){
		  if (obj.prop.resp_user != jQuery('#inv-info-object #inv-resp-user').val()){
			  obj.prop.resp_user = 'null';
			  obj.prop.id_resp_user = 'null';
		  }
		  if (obj.prop.id_resp_user == 'null') jQuery('#inv-info-object #inv-resp-user').val('');
	});

	jQuery('#inv-addobject #inv-obj-user, #inv-info-object #inv-obj-user').autocomplete({//-------------------------Поиск по Пользователю
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_users&field=cn&field2=department&id_reg=1",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.prop.id_user = ui.item.id;
	    	obj.edit.obj = true;
	    	obj.edit.user = true;
	    	obj.prop.user = ui.item.value;
	    }	   
	});	
	jQuery('#inv-addobject #inv-obj-user').focusout(function(){
		  if (obj.prop.user != jQuery('#inv-addobject #inv-obj-user').val()){
			  obj.prop.user = 'null';
			  obj.prop.id_user = 'null';
		  }
		  if (obj.prop.id_user == 'null') jQuery('#inv-addobject #inv-obj-user').val('');
	});
	jQuery('#inv-info-object #inv-obj-user').focusout(function(){
		  if (obj.prop.user != jQuery('#inv-info-object #inv-obj-user').val()){
			  obj.prop.user = 'null';
			  obj.prop.id_user = 'null'; 
		  }
		  if (obj.prop.id_user == 'null') jQuery('#inv-info-object #inv-obj-user').val('');
	});
	
	jQuery('#inv-description, #inv-info-object #inv-description').autocomplete({//-------------------------Поиск по примечанию
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_object&field=obj_description",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.edit.obj = true;
	    }
	});	
	selectunit("#inv-addobject #inv-pc-type-sp", "inv-pc-type", '', 'inv_comp_type', 'type');//--------Тип ПК	
	selectunit("#inv-info-object #inv-pc-type-sp", "inv-pc-type", '', 'inv_comp_type', 'type');//--------Тип ПК
	
	jQuery('.pc-ad-name').autocomplete({//-------------------------Поиск по доменному имени ПК
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_adpc&field=cn&field2=dnshostname&id_reg=1",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.comp.id_adpc = ui.item.id;
	    	obj.edit.comp = true;
	    	obj.comp.adpc = ui.item.value;
	    }	   
	});
	
	jQuery('.pc-ad-name').focusout(function(){
		  if (obj.comp.adpc != jQuery('.pc-ad-name').val()){
			  obj.comp.adpc = 'null';
			  obj.comp.id_adpc = 'null'; 
		  }
		  if (obj.comp.id_adpc == 'null') jQuery('.pc-ad-name').val('');
	});
	
	jQuery('.pc-zabbix-name').autocomplete({//-------------------------Поиск по имени zabbix
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_zabbix&field=host&field2=name&id_reg=1",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.comp.id_zabbix = ui.item.id;
	    	obj.edit.comp = true;
	    }	   
	});
	jQuery('.pc-ocs-name').autocomplete({//-------------------------Поиск по имени OCS
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_ocs&field=name&field2=workgroup&id_reg=1",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.comp.id_ocs = ui.item.id;
	    	obj.edit.comp = true;
	    	compconfsync();
	    }	   
	});	
	jQuery('.pc-chasis').autocomplete({//-------------------------Поиск по Шасси
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_computer&field=chasis&group=1",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.edit.comp = true;
	    }	    
	});
	jQuery('.pc-model').autocomplete({//-------------------------Поиск по Модели
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_computer&field=model&group=1",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.edit.comp_hardware = true;
	    	obj.edit.comp = true;
	    	jQuery.ajax({
	            type:'GET',
	            cache:false,
	            dataType:'html',
	            url:"index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=computer&single=1&oper=sel&id_comp="+ui.item.id,
	            success:function (data) {
	            	var rez = JSON.parse(data);
	            	//jQuery("#inv-id-vendor, #inv-info-object #inv-id-vendor").val(rez.vendor);
	            	jQuery("#inv-pc-chasis, #inv-info-object #inv-pc-chasis").val(rez.chasis);
	            	jQuery("#inv-pc-processor, #inv-info-object #inv-pc-processor").val(rez.processor);
	            	jQuery("#inv-pc-mem, #inv-info-object #inv-pc-mem").val(rez.mem);
	            	jQuery("#inv-pc-motherboard, #inv-info-object #inv-pc-motherboard").val(rez.motherboard);
	            	jQuery("#inv-pc-hdd, #inv-info-object #inv-pc-hdd").val(rez.hdd);
	            	jQuery("#inv-pc-psu, #inv-info-object #inv-pc-psu").val(rez.psu);
	            	jQuery("#inv-pc-graphics, #inv-info-object #inv-pc-graphics").val(rez.graphics);
	            	jQuery("#inv-pc-osname, #inv-info-object #inv-pc-osname").val(rez.osname);
	            	jQuery("#inv-pc-config, #inv-info-object #inv-pc-config").val(rez.config);
	            }
	        });		    	
	    }
	});
	jQuery('.pc-config').autocomplete({//-------------------------Поиск по конфигурации
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_computer&field=config",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.edit.comp = true;
	    }	
	});
	jQuery('.pc-processor').autocomplete({//-------------------------Поиск по Процессору
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_comp_hardware&field=processor&group=1",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.edit.comp = true;
	    	obj.edit.comp_hardware = true;
	    }	
	});
	jQuery('.inv-pc-mem').autocomplete({//-------------------------Поиск по Памяти
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_comp_hardware&field=mem",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.edit.comp = true;
	    	obj.edit.comp_hardware = true;
	    }	
	});
	jQuery('.pc-motherboard').autocomplete({//-------------------------Поиск по Материнской плате
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_comp_hardware&field=motherboard",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.edit.comp = true;
	    	obj.edit.comp_hardware = true;
	    }	
	});
	jQuery('.pc-hdd').autocomplete({//-------------------------Поиск по Жесткому диску
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_comp_hardware&field=hdd",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.edit.comp = true;
	    	obj.edit.comp_hardware = true;
	    }	
	});
	jQuery('.pc-psu').autocomplete({//-------------------------Поиск по Блоку питания
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_comp_hardware&field=psu",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.edit.comp = true;
	    	obj.edit.comp_hardware = true;
	    }	
	});
	jQuery('.pc-graphics').autocomplete({//-------------------------Поиск по графической карте
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_comp_hardware&field=graphics",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.edit.comp = true;
	    	obj.edit.comp_hardware = true;
	    }	
	});
	
	jQuery('.inv-pc-mac').autocomplete({//-------------------------Поиск по mac адресу
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_comp_hardware&field=mac",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.edit.comp = true;
	    	obj.edit.comp_hardware = true;
	    }	
 	});
	
	jQuery('.pc-description').autocomplete({//-------------------------Поиск по примечанию
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_comp_hardware&field=description",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.edit.comp = true;
	    	obj.edit.comp_hardware = true;
	    }	
	});
	
	//------------------Инициализация полей монитора
	
	jQuery('#inv-mon-chasis, #inv-info-object #inv-mon-chasis').autocomplete({//-------------------------Поиск по шасси монитора
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_monitor&field=chasis&group=1",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.edit.monitor = true;
	    }	
	});
	jQuery('#inv-mon-model, #inv-info-object #inv-mon-model').autocomplete({//-------------------------Поиск по модели
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_monitor&field=model&group=1",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.edit.monitor = true;
	    	jQuery.ajax({
	            type:'GET',
	            cache:false,
	            dataType:'html',
	            url:"index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=monitor&oper=sel&page=1&rows=5&sidx=name&sord=asc&_search=true&id_mon="+ui.item.id,
	            success:function (data) {
	            	var rez = JSON.parse(data);
	            	jQuery("#inv-mon-size, #inv-info-object #inv-mon-size").val(rez.rows[0].cell[23]);
	            	if (rez.rows[0].cell[24] == '1' ) jQuery("#inv-mon-format, #inv-info-object #inv-mon-size").prop("checked", true);
	            	else jQuery("#inv-mon-format, #inv-info-object #inv-mon-size").prop("checked", false);
	            	jQuery("#inv-mon-resolution, #inv-info-object #inv-mon-size").val(rez.rows[0].cell[25]);
	            	if (rez.rows[0].cell[26] == '1' ) jQuery("#inv-mon-lid, #inv-info-object #inv-mon-size").prop("checked", true);
	            	else jQuery("#inv-mon-lid, #inv-info-object #inv-mon-size").prop("checked", false);
	            	if (rez.rows[0].cell[27] == '1' ) jQuery("#inv-mon-dynamics, #inv-info-object #inv-mon-size").prop("checked", true);
	            	else jQuery("#inv-mon-dynamics, #inv-info-object #inv-mon-size").prop("checked", false);
	            	jQuery("#inv-mon-matrix-type, #inv-info-object #inv-mon-size").val(rez.rows[0].cell[32]);
	            	jQuery("#inv-mon-video-inputs, #inv-info-object #inv-mon-size").val(rez.rows[0].cell[28]);
	            	jQuery("#inv-mon-power, #inv-info-object #inv-mon-size").val(rez.rows[0].cell[29]);
	            	jQuery("#inv-mon-description, #inv-info-object #inv-mon-size").val(rez.rows[0].cell[31]);
	            }
	        });		    	
	    }
	});
	jQuery('#inv-mon-resolution, #inv-info-object #inv-mon-resolution').autocomplete({//-------------------------Поиск по шасси монитора
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_monitor&field=resolution&group=1",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.edit.monitor = true;
	    }	
	});
	
	selectunit("#inv-addobject #inv-mon-matrix-type-sp", "inv-mon-matrix-type", '', 'inv_mon_matrix_type', 'type');//------Тип матрицы
	selectunit("#inv-info-object #inv-mon-matrix-type-sp", "inv-mon-matrix-type", '', 'inv_mon_matrix_type', 'type');//------Тип матрицы
	
	selectunit("#inv-info-object #prn-type-sp", "prn-type", '', 'inv_prn_type', 'print_type');//------Тип устройства печати
	selectunit("#inv-info-object #prn-type-print-sp", "prn-type-print", '', 'inv_prn_type_print', 'print_type_print');//------Тип печати
	selectunit("#inv-info-object #prn-format-sp", "prn-format", '', 'inv_prn_format', 'format_print');//------Максимальный формат печати
	selectunit("#inv-info-object #prn-type-scan-sp", "prn-type-scan", '', 'inv_prn_type_scan', 'type_scan');//------Тип сканирования
	
	jQuery('#inv-mon-video-inputs, #inv-info-object #inv-mon-video-inputs').autocomplete({//-------------------------Видеовходы
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_monitor&field=video_inputs&group=1",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.edit.monitor = true;
	    }	
	});
	jQuery('#inv-mon-description, #inv-info-object #inv-mon-description').autocomplete({//-------------------------Описание монитора
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_monitor&field=description&group=1",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.edit.monitor = true;
	    }	
	});
	
	jQuery('.prn_chasis').autocomplete({//-------------------------Поиск по шасси принтера
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_printer&field=chasis&group=1",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.edit.prn = true;
	    }	
	});
	jQuery('.prn-model').autocomplete({//-------------------------Поиск по модели
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_prn_template&field=model&field2=chasis",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.edit.prn = true;
	    	jQuery.ajax({
	            type:'GET',
	            cache:false,
	            dataType:'html',
	            url:"index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=prntemplate&oper=sel&page=1&rows=5&sidx=vendor&sord=asc&_search=true&single=1&id="+ui.item.id,
	            success:function (data) {
	            	var rez = JSON.parse(data);
	            	obj.write_prn_form("#inv-info-object", rez, true);
	            	obj.write_prn_form("#inv-addobject", rez, true);
	            	obj.id_template = rez.id_template;	            	
	            }
	        });		    	
	    }
	});
	jQuery('.prn-srv-name').autocomplete({//-------------------------Выбор принтера на принтсервере
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=prnautocomplete&table=win_log&field=printer_name&group=1&region=4",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.edit.monitor = true;
	    }	
	});
	jQuery('.prn-zabbix-name').autocomplete({//-------------------------Поиск по имени zabbix
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_zabbix&field=host&field2=name&id_reg=1",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.prn.id_zabbix = ui.item.id;
	    	obj.edit.prn = true;
	    }	   
	});
	
	jQuery('.sup-type').autocomplete({//-------------------------Поиск по типу расходных материалов
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_sup_type&field=sup_type_name",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.edit.sup = true;
	    	jQuery.ajax({
	            type:'GET',cache:false,dataType:'html',
	            url:"index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=suptype&oper=sel&page=1&rows=5&sidx=id&sord=asc&_search=true&single=1&id="+ui.item.id,
	            success:function (data) {
	            	var rez = JSON.parse(data);	            	
	            	obj.supclear();
	            	for (var i=1; i<6; i++){//Отчищаем поля если выбран новый тип расходника
	            		jQuery(".sup-field"+i).val('');
	            		jQuery(".sup-field"+i).prop('disabled', false);
	            	}	
	            	jQuery(".sup-model").val('');// Очищаем наименование
	            	obj.read_sup_init_val(rez);//Читаем переменные инициализации формы	    	    	
	            	obj.write_sup_init_form();// Инициализируем форму
	            	obj.edit.sup = true;
	            }
	        });		    	
	    }
	});
	jQuery('.sup-type').focusout(function(){
		console.log(obj.sup);
		  if (obj.sup.sup_type_name != jQuery('.sup-type').val()){
			  obj.sup.sup_type_name = 'null';
			  obj.sup.id_supplies_type = 'null';
			  TimeAlert('Выберите значение из списка!');
		  }
		  if (obj.sup.id_supplies_type == 'null') jQuery('.sup-type').val('');
	});
	
	jQuery('.sup-model').autocomplete({//-------------------------Поиск по модели расходника 
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_sup_type&field=sup_type_name",
	    maxHeight:150,
	    select: function(event, ui) {
	    	obj.edit.prn = true;
	    	jQuery.ajax({
	            type:'GET',
	            cache:false,
	            dataType:'html',
	            url:"index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=suptype&oper=sel&page=1&rows=5&sidx=id&sord=asc&_search=true&single=1&id="+ui.item.id,
	            success:function (data) {
	            	var rez = JSON.parse(data);	            	

	            }
	        });		    	
	    }
	});
	
	divwidth = jQuery("#inv-worck").innerWidth();
	divheight = jQuery("#inv-worck").innerHeight();	
	
	jQuery(".inv-date").datepicker({
        dateFormat: 'yy.mm.dd',
        changeMonth: true,
        changeYear: true,
        yearRange: "1940:2040",
        showOn: 'focus'
    });
	
	jQuery(".inv-datetime").datetimepicker({
        dateFormat: 'yy.mm.dd',
        changeMonth: true,
        changeYear: true,
        yearRange: "1940:2040",
        showOn: 'focus'
    });
	
	jQuery(".bottom-message").hover(
		  function() {			    
		   clearTimeout(timeoutID); // Если пользователь НАВЁЛ курсор на окно, то отменяем закрытие окна по таймеру.			              
		  }, function() {			    
		   timeoutID = setTimeout("jQuery('.bottom-message').fadeOut(500);",2500); // Если пользователь ОТВЁЛ курсор от окна, то активируем таймер по новому.			     
		  }
	);
	jQuery('#inv-dlg-sup-store #sup-user').autocomplete({//-------------------------Поиск по Матответственному
	    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_users&field=cn&field2=department&id_reg=1", 
	    maxHeight:150,
	    select: function(event, ui) {
          	    	jQuery('#inv-dlg-sup-store #sup-user').data('value_id', ui.item.id);
          	    	jQuery('#inv-dlg-sup-store #sup-user').data('value_val', ui.item.value);			  
	    }	   
	});
	jQuery('#inv-dlg-sup-store #sup-user').focusout(function(){
		  if (jQuery('#inv-dlg-sup-store #sup-user').data('value_val') != jQuery('#inv-dlg-sup-store #sup-user').val()){
			  jQuery('#inv-dlg-sup-store #sup-user').data('value_val', null);
			  jQuery('#inv-dlg-sup-store #sup-user').data('value_id', null);	       	
		  }
		  if (jQuery('#inv-dlg-sup-store #sup-user').data('value_id') == null){
			  jQuery('#inv-dlg-sup-store #sup-user').val('');
			  jQuery('#inv-dlg-sup-store #sup-user').parent().append("Выберите из списка")
		  }	        				  	        				  
	  });
	jQuery("#id_object").click(function(){    // получение фокуса текстовым полем
		obj_find_dlg.strdiv = '#inv-dlg-sup-store';
		jQuery("#jqGrid-obj-find").jqGrid('setGridParam',{url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=objects&oper=useraccess&objtype=all'});
		jQuery("#jqGrid-obj-find").trigger("reloadGrid");
		jQuery('#inv-dlg-find').dialog('open');		

	});

});