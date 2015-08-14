
var fullUpldPath = '/components/com_stalrams/models/upload/server/php/',
	fileGrid,
	divwidth,
	divheight,
	timeoutID,
    map, //Карта яндексса

    inv_conf = {
        fadeIn: 200,
        fadeOut: 200
    },
	loc_dlg = {//-------------Переменная для кодирования местоположения объекта
		strdiv: null,//Показать диалогу кодирования местоположения куда передавать данные
		lvl: null//До какого уровня нужно закодировать
	},
	obj_find_dlg = {//-------------Переменная для диалога поиска объекта
			strdiv: null
		},
	inv_doc = {
		id_doc_type: 'null',
		id_supplier: 'null',
		id_from: 'null',
		id_to: 'null', 
		id_reg : 'null',
		id_pes :'null',
		id_po : 'null'
	},
    objparts = [],
    objdocs = [],
    objpartsinfo = [],
    user ={
		name: null,
		id_reg: null
	};
    TblZabbix ={
        MyZabbix: null,
        NotZabbix: null
    };
    win = {
        width: jQuery(window).width(),
        height: jQuery(window).height()
    };



function select(grid, pager, select, name, caption, col, width, height){
	jQuery(grid).jqGrid({
		url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=select&oper=sel&select='+select+'&name='+name,
		editurl: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=select&select='+select+'&name='+name,
		datatype: "json",
		 colModel: [{ label: col, name: name, width: 20, editable: true,formoptions: { colpos: 1, rowpos: 1, label: col}}],
		
		viewrecords: false,
		width: width,
		height: height,
		rowNum: 50,
		caption: caption,
		pager: pager
	});
    if (pager != '')
    	{jQuery(grid).navGrid(pager,
            { edit: true, add: true, del: true, search: false, refresh: false, view: false, position: "left", cloneToTop: false },
            { height: 'auto', width: 620, editCaption: "Редактирование", recreateForm: true, closeAfterEdit: true, errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},  
            { height: 'auto',  width: 620,  closeAfterAdd: true,  recreateForm: true,  errorTextFormat: function (data) {return 'Ошибка: ' + data.responseText;}},
            { errorTextFormat: function (data) {return 'Ошибка: ' + data.responseText;}
         });
    };
}

function selectunit(spanid, selid, func, select, name, async){
	var reg = 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=selectunits&selid='+selid+'&func='+func+'&select='+select+'&name='+name;
	jQuery.ajax({
        type:'GET',
        cache:false,
        dataType:'html',
        url:reg,
        async: async,
        success:function (data) {
        	jQuery(spanid).html(data);
        }
    });
}

function addobject(s, rowid){//-----------------------------------------------Диалог добавления объекта-----------------------------------------------------------
	
	obj.clear();// Обнуляем объект
    loc_dlg.lvl = 2;

    jQuery("#tabs-buh").append(jQuery("#obj-buh-param"));
	
	jQuery("#inv-addobject input").val('');//Обнуляем все поля форм
	jQuery("#inv-addobject textarea").val('');//Обнуляем все поля форм	
	
	jQuery("a[href='#tabs-comn-object']").trigger('click');// Переходим на первую вкладку


    autocomplete_inc('.id_vendor', "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_vendor&field=vendor&field2=vendor_eng");

    //-------------------------Поиск по Матответственному
    autocomplete_inc('.id_resp_user', "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=respuser&oper=autocomplete&id_reg=");//TODO: Доделать id региона
    //-------------------------Поиск по Пользователю
    autocomplete_inc('.id_user', "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_users&field=cn&field2=department&id_reg=");//TODO: Доделать id региона

    autocomplete_inc('.id_supplier', "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_supplier&field=supplier");

    selectunit("#inv-addobject #inv-type-accounting-sp", "id_type_accounting", '', 'inv_type_accounting', 'accounting');//------Тип Учета
	
	//---------------Обно-вляем таблицы
	objparts.length=0; //Таблица подобъектов
	jQuery("#jqGrid-obj-region-stores").trigger("reloadGrid");
	jQuery("#jqGrid-obj-conf-stores").trigger("reloadGrid");
	jQuery("#jqGrid-obj-parts1").jqGrid('setGridParam',{url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=objects&oper=sel&objtype=all', datatype: "json"})
                                .jqGrid('clearGridData')
                                .trigger("reloadGrid");
	jQuery("#jqGrid-obj-parts2").jqGrid('clearGridData').trigger("reloadGrid");
    jQuery("#jqGrid-obj-docs1").jqGrid('setGridParam',{url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=docs&oper=selaccess', datatype: "json"})
                               .jqGrid('clearGridData')
                               .trigger("reloadGrid");
    // Проверяем изменение бухгалтерских свойств объекта
    jQuery("#tabs-buh input, #tabs-buh textarea, #tabs-buh select").change (function(){
        obj.edit.buh = true;
    });


	switch (s){
		case 1: // Первый шаг диалога добавления
			document.getElementById("inv-add-object").style.display = "none";
			document.getElementById("inv-add-grid-type").style.display = "block";
            jQuery("#jqGrid-obj-type").jqGrid('setGridParam',{url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=objecttype', datatype: "json"})
                                      .trigger("reloadGrid");
			jQuery('#inv-addobject').dialog('open');// Открываем диалог добавления объекта
			break;
		case 2://Второй шаг диалога добавления
			var grid = jQuery("#jqGrid-obj-type");
		    obj.prop.id_obj_type = rowid;
		    			
		    if (obj.prop.id_obj_type){
		    	
				document.getElementById("inv-add-object").style.display = "block";
				document.getElementById("inv-add-grid-type").style.display = "none";
				
		    	jQuery("#tabs-properties").children('div').each(function (i){// Скрываем все div блоки свойств
					jQuery(this).hide();
				});
		    	jQuery("#tabs-"+grid.getCell(rowid, 'func')).show(); //Показываем div выбранного типа объекта
		    	
		    	obj.prop.obj_type = grid.getCell(rowid, 'func'); // Устанавливаем тип объекта	
				jQuery('#obj_name').val(grid.getCell(rowid, 'type')); // Пишем имя объекта
				jQuery('#obj_name').prop('disabled', true); // Делаем недоступным имя объекта
                jQuery(".non-group").show();//Показываем основные свойства

		    	switch (grid.getCell(rowid, 'func')){ //Проверяем какую функцию выбрал пользователь
                    case 'group'://Диалог добавления группового объекта
                        jQuery(".non-group").hide();//Скрываем основные свойства у групповых объектов
                        selectunit("#inv-addobject #id_gr_type_sp", "id_gr_type", '', 'inv_group_type', 'group_type');//------Тип группы
                        loc_dlg.lvl = 1;
                        break;
                    case 'computer': //Диалог добавления компьютера
                        selectunit("#inv-addobject #inv-pc-type-sp", "inv-pc-type", '', 'inv_comp_type', 'type');//--------Тип ПК
                        break;
                    case 'monitor': //Диалог добавления монитора
                        selectunit("#inv-addobject #inv-mon-matrix-type-sp", "inv-mon-matrix-type", '', 'inv_mon_matrix_type', 'type');//------Тип матрицы
                        break;
                    case 'prn': //Диалог добавления принтера
                        selectunit("#inv-addobject #prn-type-sp", "prn-type", '', 'inv_prn_type', 'print_type');//------Тип устройства печати
                        selectunit("#inv-addobject #prn-type-print-sp", "prn-type-print", '', 'inv_prn_type_print', 'print_type_print');//------Тип печати
                        selectunit("#inv-addobject #prn-format-sp", "prn-format", '', 'inv_prn_format', 'format_print');//------Максимальный формат печати
                        selectunit("#inv-addobject #prn-type-scan-sp", "prn-type-scan", '', 'inv_prn_type_scan', 'type_scan');//------Тип сканирования
                        break;
                    case 'sup': //Диалог добавления расходного материала
                        break;
                    case 'net': //Диалог добавления сетевого устройства
                        selectunit("#inv-addobject #net_type_sp", "net_id_active_type", '', 'inv_net_type', 'active_net_type', true);//------Тип устройства
                        selectunit("#inv-addobject #net_id_base_speed_sp", "net_id_base_speed", '', 'inv_net_speed', 'base_speed', true);//------Базовая скорость
                        selectunit("#inv-addobject #net_id_max_uplink_sp", "net_id_max_uplink", '', 'inv_net_speed', 'base_speed', true);//------Скорость uplink
                        jQuery('.net_zabbix_name').autocomplete("option", "source", "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_zabbix&field=host&field2=name&id_reg="+obj.location.id_reg);
                        jQuery( ".inv_spinner" ).spinner("value", 0);
                        break;
				}
				
				
			}else alert ('Выберите тип объекта!')
			break;	
	}
	
}

function editobject(id, type){
	
	var rez, // Переменная для парсинга свойств объекта
		strdiv = "#inv-info-object";//--- Область видимости диалога редактирования
	function set_object(){// Функция заполнения полей объекта
        obj.read_obj_val(rez);//Читаем свойства объекта
		obj.write_obj_form(strdiv, rez); //Заполняем форму

	}
    loc_dlg.lvl = 2;

    if (type === undefined){ //Если тип объекта не определен, определяем его по id
        type = obj.get_type_by_id(id);
	}

	obj.clear();// Обнуляем объект

    //-------------------------Поиск по Матответственному
    jQuery('.id_resp_user').autocomplete("destroy");
    jQuery('.id_supplier').autocomplete("destroy");

    jQuery("#info-tabs-buh").append(jQuery("#obj-buh-param"));//Переносим форму бухгалтерских свойств в форму добавления объекта

    //-------------------------Поиск по Производителю
    autocomplete_inc('.id_vendor', "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_vendor&field=vendor&field2=vendor_eng");

    autocomplete_inc('.id_resp_user', "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=respuser&oper=autocomplete&id_reg=");//TODO: Доделать id региона
    //-------------------------Поиск по Пользователю
    autocomplete_inc('.id_user', "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_users&field=cn&field2=department&id_reg=");//TODO: Доделать id региона

    autocomplete_inc('.id_supplier', "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_supplier&field=supplier");

    selectunit("#inv-info-object #inv-type-accounting-sp", "id_type_accounting", '', 'inv_type_accounting', 'accounting');//------Тип Учета

    // Проверяем изменение бухгалтерских свойств объекта
    jQuery("#info-tabs-buh input, #info-tabs-buh textarea, #info-tabs-buh select").change (function(){
        obj.edit.buh = true;
    });
    jQuery("#inv-info-tabs-location input").change(function(){// Проверяем изменение местонахождение объекта
        obj.edit.location = true;
    });
	jQuery("#inv-info-computer input, #inv-info-computer select, #inv-info-computer textarea").change(function(){// Проверяем изменение компьютера объекта
		obj.edit.comp = true;
	});	
	jQuery("#inv-info-tabs-comn-object input, #inv-info-tabs-comn-object textarea, #inv-info-tabs-comn-object select").change (function(){// Проверяем изменение объекта в редактировании
		obj.edit.obj = true;
	});
	jQuery("#inv-info-tabs-comn-object #inv-resp-user").change (function(){// Проверяем изменение МОЛ
		obj.edit.resp_user = true;
	});
	jQuery("#inv-info-tabs-comn-object #inv-obj-user").change (function(){// Проверяем изменение Пользователя
		obj.edit.user = true;
	});
	
	jQuery('#inv-info-object #inv-obj-name').prop('disabled', true);// Отключаем изменение названия объекта	
	jQuery("#inv-info-tabs-properties").children('div').each(function (i){// Скрываем все div блоки свойств
		jQuery(this).hide();
	});

	//jQuery("#jqGrid-obj-info-parts1").jqGrid('setGridParam',{url: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=objects&_function=computer&oper=useraccess", datatype: "json"});
	jQuery("#jqGrid-obj-info-parts1").jqGrid('setGridParam',{url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=objects&oper=sel&objtype=all', datatype: "json"})
                                     .jqGrid('clearGridData')
                                     .trigger("reloadGrid");
	jQuery("#jqGrid-obj-info-parts2").jqGrid('setGridParam',{url: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=objparts&oper=sel&id_obj="+id,
                                                            editurl: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=objparts&id_obj="+id, datatype: "json"})
                                     .jqGrid('clearGridData')
                                     .trigger("reloadGrid");
    jQuery("#jqGrid-obj-info-docs1").jqGrid('setGridParam',{url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=docs&oper=selaccess', datatype: "json"})
                                    .jqGrid('clearGridData')
                                    .trigger("reloadGrid");
    jQuery("#jqGrid-obj-info-docs2").jqGrid('setGridParam',{url: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=objdocs&oper=sel&id_obj="+id, datatype: "json"})
                                    .jqGrid('clearGridData')
                                    .trigger("reloadGrid");
    jQuery(".non-group").show();//Показываем основные свойства
    jQuery(strdiv+' #obj_name').prop('disabled', true); // Делаем недоступным имя объекта

	switch (type){
        case 'group'://Диалог добавления группового объекта
            jQuery(".non-group").hide();//Скрываем основные свойства у групповых объектов
            selectunit("#inv-info-object #id_gr_type_sp", "id_gr_type", '', 'inv_group_type', 'group_type');//------Тип группы
            loc_dlg.lvl = 1;//ДОстаточно одного уровня кодирования
            break;
        case 'computer':
            selectunit("#inv-info-object #inv-pc-type-sp", "inv-pc-type", '', 'inv_comp_type', 'type');//--------Тип ПК
            jQuery("#inv-info-tabs-computer-hard input, #inv-info-computer select, #inv-info-computer textarea").change (function(){// Проверяем изменение компьютера объекта
                obj.edit.comp_hardware = true;
            });
            jQuery("#inv-info-mfd input, #inv-info-mfd select, #inv-info-mfd textarea").change (function(){// Проверяем изменение компьютера объекта
                obj.edit.comp = true;
            });

            jQuery("a[href='#inv-info-tabs-computer-OCS']").click(function () {
                if (obj.comp.id_ocs){//Если заполнено поле OCS
                    jQuery.ajax({//Читаем свойства объекта компьютера
                        type:'GET', cache:false, dataType:'html', async: false,
                        url:'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=ocsimport&oper=bios&region='+obj.prop.id_reg+'&id_ocs='+obj.comp.id_ocs,
                        success:function (data) {
                            rez = JSON.parse(data);//Парсим JSON
                            jQuery(strdiv+" #OCS-BIOS-SMANUFACTURER").val(rez.SMANUFACTURER);
                            jQuery(strdiv+" #OCS-BIOS-SMODEL").val(rez.SMODEL);
                            jQuery(strdiv+" #OCS-BIOS-SSN").val(rez.SSN);
                            jQuery(strdiv+" #OCS-BIOS-TYPE").val(rez.TYPE);
                            jQuery(strdiv+" #OCS-BIOS-BMANUFACTURER").val(rez.BMANUFACTURER);
                            jQuery(strdiv+" #OCS-BIOS-BVERSION").val(rez.BVERSION);
                            jQuery(strdiv+" #OCS-BIOS-BDATE").val(rez.BDATE);
                            jQuery(strdiv+" #OCS-BIOS-ASSETTAG").val(rez.ASSETTAG);
                        }
                    });
                    jQuery("#jqGrid-info-OCS-soft").jqGrid('setGridParam',{url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=ocsimport&oper=soft&region='+obj.prop.id_reg+'&id_ocs='+obj.comp.id_ocs, datatype: "json"})
                        .trigger("reloadGrid");
                }
            });

            break
        case 'monitor':
            selectunit("#inv-info-object #inv-mon-matrix-type-sp", "inv-mon-matrix-type", '', 'inv_mon_matrix_type', 'type');//------Тип матрицы
            jQuery("#inv-info-monitor input,#inv-info-monitor select, #inv-info-monitor textarea").change (function(){// Проверяем изменение монитора объекта
                obj.edit.monitor = true;
            });
            break
        case 'prn':
            selectunit("#inv-info-object #prn-type-sp", "prn-type", '', 'inv_prn_type', 'print_type');//------Тип устройства печати
            selectunit("#inv-info-object #prn-type-print-sp", "prn-type-print", '', 'inv_prn_type_print', 'print_type_print');//------Тип печати
            selectunit("#inv-info-object #prn-format-sp", "prn-format", '', 'inv_prn_format', 'format_print');//------Максимальный формат печати
            selectunit("#inv-info-object #prn-type-scan-sp", "prn-type-scan", '', 'inv_prn_type_scan', 'type_scan');//------Тип сканирования
            jQuery("#inv-info-prn input,#inv-info-prn select, #inv-info-prn textarea").change (function(){// Проверяем изменение принтера
                obj.edit.prn = true;
            });
            break;
        case 'net':
            jQuery('.net_zabbix_name').autocomplete("option", "source", "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_zabbix&field=host&field2=name&id_reg="+obj.location.id_reg);
            selectunit(strdiv + " #net_type_sp", "net_id_active_type", '', 'inv_net_type', 'active_net_type', false);//------Тип устройства
            selectunit(strdiv +" #net_id_base_speed_sp", "net_id_base_speed", '', 'inv_net_speed', 'base_speed', false);//------Базовая скорость
            selectunit(strdiv +" #net_id_max_uplink_sp", "net_id_max_uplink", '', 'inv_net_speed', 'base_speed', false);//------Скорость uplink
            jQuery("#inv-info-net input, #inv-info-net select, #inv-info-net textarea").change (function(){// Проверяем изменение сетевого объекта
                obj.edit.net = true;
            });
            break;
        case 'sup':
            jQuery("#inv-info-sup input,#inv-info-sup select, #inv-info-sup textarea").change (function(){// Проверяем изменение расходников
                obj.edit.sup = true;
            });
            break
	}

    obj.read_db(id, strdiv, type);
 	
	jQuery("#inv-info-"+type).show(); //Показываем div выбранного типа объекта	
	jQuery('#inv-info-object').dialog('open');// Открываем диалог обновления объекта

    jQuery("#jqGrid-obj-work").jqGrid('setGridParam',{url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=work&oper=sel&id_obj='+obj.prop.id,
                                                      editurl: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=work&id_obj='+obj.prop.id, datatype: "json"})
                              .jqGrid('clearGridData')
                              .trigger("reloadGrid");
    jQuery("#jqGrid-obj-info-loc-history").jqGrid('setGridParam',{url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=location&oper=sel&id='+id, datatype: "json"})	.jqGrid('clearGridData').trigger("reloadGrid");
	jQuery("#jqGrid-obj-info-user-history").jqGrid('setGridParam',{url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=userchange&oper=sel&id='+id, datatype: "json"}).jqGrid('clearGridData').trigger("reloadGrid");
	jQuery("#jqGrid-obj-info-resp-user-history").jqGrid('setGridParam',{url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=respuserchange&oper=sel&id='+id, datatype: "json"}).jqGrid('clearGridData').trigger("reloadGrid");
	jQuery("#jqGrid-obj-info-config-history").jqGrid('setGridParam',{url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=compconfigchange&oper=sel&id='+id, datatype: "json"}).jqGrid('clearGridData').trigger("reloadGrid");
	jQuery("#jqGrid-info-prn-history").jqGrid('setGridParam',{url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=prnhistory&printer_name='+obj.prn.prn_srv_name+'&region='+obj.location.id_reg, datatype: "json"}).jqGrid('clearGridData').trigger("reloadGrid");

    makeQRCode("#QRCode", 'Тип:'+obj.prop.obj_type+'Сер№:'+obj.prop.s_number+'Инв№1:'+obj.prop.i_number+'Ссылка: http://itforum.mrsk-cp.ru/index.php?&option=com_stalrams&task=decode&id='+obj.prop.id);
	
	if (obj.location.latitude && obj.location.longitude){
        map = new ymaps.Map("map", {
            center: [54.1737,37.6111],
            zoom: 17
        });

        jQuery("li[href='#inv-info-tabs-geo']").show();

		myPlacemark = new ymaps.Placemark([obj.location.latitude, obj.location.longitude], { content: obj.prop.obj_type, balloonContent: obj.prop.obj_type });
		map.geoObjects.add(myPlacemark);
	}else{
        jQuery("li[href='#inv-info-tabs-geo']").hide();
    }
	
}

function docs(){//Открываем окно работы с документацией

	jQuery("#jqGrid-doc-access-region").jqGrid({//-------------------------------------------------------------------Таблица регионов-------------------------------------------------
		url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=accessselect&select=region',
		datatype: "json",
		 colModel: [{ label: 'Регион', name: 'name', width: 20},
		 			{ label: 'Изменение', name: 'reg_write', width: 20, hidden: true},
		 			{ label: 'Чтение', name: 'reg_read', width: 20, hidden: true}],
		
		viewrecords: false,
		width: 300,
		height: 300,
		rowNum: 50,
		subGrid: true, 
        subGridRowExpanded: showChildGridDocPes, 
	    subGridOptions : {
			plusicon: "ui-icon-triangle-1-e",
			minusicon: "ui-icon-triangle-1-s",
			openicon: "ui-icon-arrowreturn-1-e"
		},
		onSelectRow: function(rowid, selected) {
			if(rowid != null) {
				jQuery("#jqGrid-doc-docs").jqGrid('setGridParam',{url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=docs&oper=sel&region='+rowid,
																editurl: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=docs&region='+rowid, datatype: "json"})
				                          .jqGrid('setCaption', 'Регион::'+jQuery(this).getCell(rowid, 'name'))
                                          .trigger("reloadGrid");
				
				inv_doc.id_reg = rowid;
				//document.getElementById("doc-files").style.display = "block";
				
			}					
		}
	});
	function showChildGridDocPes(parentRowID, parentRowKey) {//.........................................Таблица производственных отделенй........................................
        var childGridID = parentRowID + "_table";
        var grid = jQuery("#jqGrid-doc-access-region");
        var select = 'pes',
        	reg_write = grid.getCell(parentRowKey, 'reg_write'),
        	reg_read = grid.getCell(parentRowKey, 'reg_read');
        	
        	
        
        if (reg_write == 1 || reg_read == 1) select = 'pesall&reg_write='+reg_write+'&reg_read='+reg_read;//Если доступ ко всему региону

        jQuery('#' + parentRowID).append('<table id=' + childGridID + '></table>');

        jQuery("#" + childGridID).jqGrid({
            url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=accessselect&select='+select+'&regid='+parentRowKey,
            mtype: "GET",
            datatype: "json",
            page: 1,
            colModel: [{ label: 'Производственное отделение', name: 'name', width: 30},
                       { label: 'Изменение', name: 'reg_write', width: 20, hidden: true}, 
   		 			   { label: 'Чтение', name: 'reg_read', width: 20, hidden: true}],
            viewrecords: false,
			loadonce: false,
            width: 250,
            height: '100%',
            onSelectRow: function(rowid, selected) {
    			if(rowid != null) {
    				jQuery("#jqGrid-doc-docs").jqGrid('setGridParam',{url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=docs&oper=sel&pes='+rowid,
    																editurl: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=docs&pes='+rowid, datatype: "json"})
    				                          .jqGrid('setCaption', 'Регион::'+jQuery(this).getCell(rowid, 'name'))
    				                          .trigger("reloadGrid");
    				inv_doc.id_pes = rowid;
    				//document.getElementById("doc-files").style.display = "block";
    			}					
    		}
      });
	};
	jQuery("#jqGrid-doc-docs").jqGrid({// -------------------------------------------------Таблица Документов----------------------------------------------------------------
		url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw',
		editurl: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=docs',
		datatype: "json",
		 colModel: [
			{ label: 'Тип документа', name: 'doc_type', width: 5, editable: true, search: true, editrules: {required: true},
				searchoptions: {
					dataInit: function (element) {
						window.setTimeout(function () {
							jQuery(element).autocomplete({
	                        	source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_doc_types&field=doc_type",
	                    	    maxHeight:150
	                        });
			            }, 100);
					}
				},
				editoptions: {
					dataInit: function (element) {
                        jQuery(element).autocomplete({
                        	source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_doc_types&field=doc_type",
                    	    maxHeight:150,
                    	    select: function(event, ui) {
                    	    	inv_doc.id_doc_type = ui.item.id;
                    	    	inv_doc.doc_type = ui.item.value;                    	    	
                    	    }
                        });
                        jQuery(element).focusout(function(){
                  		  if (inv_doc.doc_type != jQuery(element).val()){
                  			inv_doc.doc_type = 'null';
                  			inv_doc.id_doc_type = 'null';
                  		  }
                  		  if (inv_doc.id_doc_type == 'null') jQuery(element).val('');
                  		jQuery("#jqGrid-doc-docs").jqGrid('setGridParam',{editurl: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=docs&id_supplier='+inv_doc.id_supplier+
							'&id_from='+inv_doc.id_from+
							'&id_to='+inv_doc.id_to+
							'&id_doc_type='+inv_doc.id_doc_type+
							'&id_reg='+inv_doc.id_reg+
							'&id_pes='+inv_doc.id_pes+
							'&id_po='+inv_doc.id_po});
                  		  
                  	});
                    }
				},},
			{ label: 'Название', name: 'doc_name', width: 5,  editable: true, edittype:"text", editrules: {required: true}, search: true,
				searchoptions: {
					dataInit: function (element) {
						window.setTimeout(function () {
			            	jQuery(element).autocomplete({
	                        	source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_docs&field=doc_name",
	                    	    maxHeight:150
	                        });
			            }, 100);
					}
				}
			},
			{ label: 'Номер', name: 'doc_number', width: 5,  editable: true, edittype:"text",
				searchoptions: {
					dataInit: function (element) {
						window.setTimeout(function () {
			            	jQuery(element).autocomplete({
	                        	source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_docs&field=doc_number",
	                    	    maxHeight:150
	                        });
			            }, 100);
					}
				}
			},
			{ label: 'Дата', name: 'doc_date', width: 5, editable: true, editrules: {required: true}, search: true,
				editoptions: {
					dataInit: function (element) {
						jQuery(element).datepicker({
					        numberOfMonths: 3,
					        dateFormat: 'yy.mm.dd',
					        changeMonth: true,
					        changeYear: true,
					        yearRange: "1940:2040",
					        showOn: 'focus'
						});
					}
				},
				searchoptions: {
					dataInit: function (element) {
						jQuery(element).datepicker({
					        numberOfMonths: 3,
					        dateFormat: 'yy.mm.dd',
					        changeMonth: true,
					        changeYear: true,
					        yearRange: "1940:2040",
					        showOn: 'focus'
						});
					}
				}
			},
			{ label: 'Контрагент', name: 'supplier', width: 5, editable: true, search: true,
				searchoptions: {
					dataInit: function (element) {
			            window.setTimeout(function () {
			            	jQuery(element).autocomplete({
	                        	source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_supplier&field=supplier",
	                    	    maxHeight:150
	                        });
			            }, 100);
			        }
				},
				editoptions: {
					dataInit: function (element) {
                        jQuery(element).autocomplete({
                        	source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_supplier&field=supplier",
                    	    maxHeight:150,
                    	    select: function(event, ui) {
                    	    	inv_doc.id_supplier = ui.item.id;
                    	    	inv_doc.supplier = ui.item.value;
                    	    }
                        });
                        jQuery(element).focusout(function(){
                  		  if (inv_doc.supplier != jQuery(element).val()){
                  			inv_doc.supplier = 'null';
                  			inv_doc.id_supplier = 'null';
                  		  }
                  		  if (inv_doc.id_supplier == 'null') jQuery(element).val('');
                  		jQuery("#jqGrid-doc-docs").jqGrid('setGridParam',{editurl: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=docs&id_supplier='+inv_doc.id_supplier+
							'&id_from='+inv_doc.id_from+
							'&id_to='+inv_doc.id_to+
							'&id_doc_type='+inv_doc.id_doc_type+
							'&id_reg='+inv_doc.id_reg+
							'&id_pes='+inv_doc.id_pes+
							'&id_po='+inv_doc.id_po});
                        });
                    },
				},
			},
			{ label: 'От кого', name: 'name_from', width: 5, editable: true, search: true,
					editoptions: {
						dataInit: function (element) {
	                        jQuery(element).autocomplete({
	                        	source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_users&field=cn&field2=department&id_reg=1",
	                    	    maxHeight:150,
	                    	    select: function(event, ui) {
	                    	    	inv_doc.id_from = ui.item.id;
	                    	    	inv_doc.from = ui.item.value;
	                    	    }
	                        });
	                        jQuery(element).focusout(function(){
	                  		  if (inv_doc.from != jQuery(element).val()){
	                  			inv_doc.from = 'null';
	                  			inv_doc.id_from = 'null';
	                  		  }
	                  		  if (inv_doc.id_from == 'null') jQuery(element).val('');
	                  		jQuery("#jqGrid-doc-docs").jqGrid('setGridParam',{editurl: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=docs&id_supplier='+inv_doc.id_supplier+
								'&id_from='+inv_doc.id_from+
								'&id_to='+inv_doc.id_to+
								'&id_doc_type='+inv_doc.id_doc_type+
								'&id_reg='+inv_doc.id_reg+
								'&id_pes='+inv_doc.id_pes+
								'&id_po='+inv_doc.id_po});
	                  	});
	                    }
					},},
			{ label: 'Кому', name: 'name_to', width: 5, editable: true, search: true,
						editoptions: {
							dataInit: function (element) {
		                        jQuery(element).autocomplete({
		                        	source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_users&field=cn&field2=department&id_reg=1",
		                    	    maxHeight:150,
		                    	    select: function(event, ui) {
		                    	    	inv_doc.id_to = ui.item.id;
		                    	    	inv_doc.to = ui.item.value;
		                    	    }
		                        });
		                        jQuery(element).focusout(function(){
		                  		  if (inv_doc.to != jQuery(element).val()){
		                  			inv_doc.to = 'null';
		                  			inv_doc.id_to = 'null';
		                  		  }
		                  		  if (inv_doc.id_to == 'null') jQuery(element).val('');
		                  		jQuery("#jqGrid-doc-docs").jqGrid('setGridParam',{editurl: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=docs&id_supplier='+inv_doc.id_supplier+
									'&id_from='+inv_doc.id_from+
									'&id_to='+inv_doc.id_to+
									'&id_doc_type='+inv_doc.id_doc_type+
									'&id_reg='+inv_doc.id_reg+
									'&id_pes='+inv_doc.id_pes+
									'&id_po='+inv_doc.id_po});
		                  	});
		                    },
						},},
            { label: 'Описание', name: 'description', width: 5, editable: true, search: true, edittype: 'textarea',
							editoptions: {
								dataInit: function (element) {
			                        jQuery(element).autocomplete({
			                        	source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_docs&field=description&field2=doc_name",
			                    	    maxHeight:150,			                    	    
			                        });			                       
			                    },
							},
							searchoptions: {
								dataInit: function (element) {
						            window.setTimeout(function () {
						            	jQuery(element).autocomplete({//-------------------------Поиск по 
						            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_docs&field=description&field2=doc_name",
						            	    maxHeight:150,
						            	});	
						            }, 100);
						        }
							}
			}
		],
		subGrid: true,
		subGridRowExpanded: showChildGridDocs, 
	    subGridOptions : {plusicon: "ui-icon-triangle-1-e",minusicon: "ui-icon-triangle-1-s",openicon: "ui-icon-arrowreturn-1-e"},
		sortname: 'doc_date',
		viewrecords: true, 
		width: 900,
		height: 'auto',
		rowNum: 30,
		loadonce: false,    	
		caption: 'Выберите регион',
		pager: "#jqGridPager-doc-docs"
	});
	jQuery('#jqGrid-doc-docs').jqGrid('filterToolbar');
	jQuery("#jqGrid-doc-docs").bind("jqGridAddEditBeforeShowForm", function () {
		inv_doc.id_doc_type = 'null';
    	inv_doc.id_supplier = 'null';
    	inv_doc.id_from = 'null';
    	inv_doc.id_to = 'null';
	});
	jQuery('#jqGrid-doc-docs').navGrid('#jqGridPager-doc-docs',
			  { edit: false, add: true, del: true, search: true, refresh: true, view: true, position: "left", cloneToTop: true },
	            {height: 'auto',width: 500,editCaption: "Редактирование",recreateForm: true,closeAfterEdit: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
	            {height: 'auto',width: 500,closeAfterAdd: true,recreateForm: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
	            {errorTextFormat: function (data) {return 'Error: ' + data.responseText;}}           
    );
	function showChildGridDocs(parentRowID, parentRowKey) {//.........................................Таблица производственных отделений........................................
        var childGridID = parentRowID + "_table";
        var childGridPagerID = parentRowID + "_pager";
        var dir = "components/com_stalrams/models/upload/server/php/files/"+parentRowKey+"/";
        jQuery('#' + parentRowID).append('<table id=' + childGridID + '></table><div id=' + childGridPagerID + ' class=scroll></div>');
        jQuery('#fileupload').fileupload(
	            'option', {
	            	url: '/components/com_stalrams/models/upload/server/php/index.php?id='+parentRowKey,            	
	            }
	        );

        jQuery("#" + childGridID).jqGrid({
        	url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=docdir&oper=sel&dir='+dir,
        	editurl: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=docdir&dir='+dir,
    		datatype: "json",
    		 colModel: [
    			{ label: 'Файл', name: 'file', width: 300,  formatter: fileURL},
    			{ label: 'Размер MB', name: 'filetype', width: 150},
    			{ label: 'file_name', name: 'file_name', width: 5, hidden: true},
    		],
    		sortname: 'file',
    		viewrecords: true, 
    		width: 700,
    		height: 'auto',
    		rowNum: 30,
    		loadonce: false,    	
    		pager: "#" + childGridPagerID
        });
        jQuery("#" + childGridID).navGrid("#" + childGridPagerID,
    			 { edit: false, add: false, del: false, search: false, refresh: true, view: false, position: "left", cloneToTop: true }
        );
        jQuery("#" + childGridID).navButtonAdd("#" + childGridPagerID,
                {
                    buttonicon: "ui-icon ui-icon-trash",
                    title: "Удалить",
                    caption: "Удалить",
                    position: "last",
                    onClickButton: function() {
                        var rowKey = jQuery("#" + childGridID).jqGrid('getGridParam',"selrow");
                        var dir = jQuery("#jqGrid-doc-docs").jqGrid('getGridParam',"selrow");
                        if (rowKey){
                        	var reg = 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=docdir&oper=del&file='+jQuery("#" + childGridID).getCell(jQuery("#" + childGridID).jqGrid('getGridParam',"selrow"), 'file_name');
     	        			jQuery.ajax({
    	        		        type:'GET',
    	        		        cache:false,
    	        		        dataType:'html',
    	        		        url:reg,
    	        		        success:function (data) {
    	        		        	jQuery("#" + childGridID).trigger("reloadGrid");
    	        		        }
    	        		    });
                        }else         alert("Выберите объект");		                    	
    				}
                }	                
    	);
    	jQuery("#" + childGridID).navButtonAdd("#" + childGridPagerID,
                {
                    buttonicon: "ui-icon ui-icon-plus",
                    title: "Загрузить",
                    caption: "Загрузить",
                    position: "last",
                    onClickButton: function() {
                    	fileGrid = parentRowKey;
                    	jQuery('#fileupload').trigger('click');        	
    				}
                }	                
    	);
	}
	function fileURL(cellValue, options, rowObject) {
		var arr = cellValue.split('/');
		
        var fileHtml = '<a href="' + cellValue + '" target="_blank">'+arr[arr.length-1]+'</a>';
        return fileHtml;
    }
	
}

function ldapimport(layout){//Перебор алфавита для импорта из AD
	
	var reg = 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=regions&oper=sel';
	jQuery.ajax({
        type:'GET',
        cache:false,
        dataType:'html',
        url:reg,
        success:function (data) {
         	var region = JSON.parse(data);
         	document.getElementById("inv-"+layout).innerHTML += region.rows.length;
        	for (var j=0; j< region.rows.length; j++){
        		document.getElementById("inv-"+layout).innerHTML += 'Регион'+region.rows[j].id;
        		for (var i=65; i<90; i++){
        		  var reg = 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=ldapimport&layout='+layout+'&char='+String.fromCharCode(i)+'&region='+region.rows[j].id;
        			jQuery.ajax({
        		        type:'GET',
        		        cache:false,
        		        dataType:'html',
        		        url:reg,
        		        success:function (data) {
        		        	document.getElementById("inv-"+layout).innerHTML += data;
        		        }
        		    });
        		}
        		for (var i=1072; i<1103; i++){
        		  var reg = 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=ldapimport&layout='+layout+'&char='+String.fromCharCode(i)+'&region='+region.rows[j].id;
        			jQuery.ajax({
        		        type:'GET',
        		        cache:false,
        		        dataType:'html',
        		        url:reg,
        		        success:function (data) {
        		        	document.getElementById("inv-"+layout).innerHTML += data;
        		        }
        		    });
        		}
        	};	
        }
    });	
}

function zabbiximport(){//Перебор регионов для импорта из zabbix
	
	var reg = 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=regions&oper=sel';
	jQuery.ajax({
        type:'GET',
        cache:false,
        dataType:'html',
        url:reg,
        success:function (data) {
         	var region = JSON.parse(data);
         	document.getElementById("inv-zabbiximport-sp").innerHTML += region.rows.length;
        	for (var j=0; j< region.rows.length; j++){
        		if (region.rows[j].cell[0]){
        			document.getElementById("inv-zabbiximport-sp").innerHTML += 'Регион'+region.rows[j].id;
        			var reg = 'index.php?option=com_stalrams&view=zabbix&task=getZabbixAjaxData&format=raw&region='+region.rows[j].id+'&ajaxtype=zabbiximport';
	    			jQuery.ajax({
	    		        type:'GET',
	    		        cache:false,
	    		        dataType:'html',
	    		        url:reg,
	    		        success:function (data) {
	    		        	document.getElementById("inv-zabbiximport-sp").innerHTML += data;
	    		        }
	    		    });
        		}
         	};	
        }
    });	
}

function ocsimport(){//Перебор регионов для импорта из OCS
	
	var reg = 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=regions&oper=sel';
	jQuery.ajax({
        type:'GET',
        cache:false,
        dataType:'html',
        url:reg,
        success:function (data) {
         	var region = JSON.parse(data);
         	document.getElementById("inv-ocsimport-sp").innerHTML += region.rows.length;
        	for (var j=0; j< region.rows.length; j++){
        		if (region.rows[j].cell[0]){
        			document.getElementById("inv-ocsimport-sp").innerHTML += 'Регион'+region.rows[j].id;
        			var reg = 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=ocsimport&oper=import&region='+region.rows[j].id;
	    			jQuery.ajax({
	    		        type:'GET',
	    		        cache:false,
	    		        dataType:'html',
	    		        url:reg,
	    		        success:function (data) {
	    		        	document.getElementById("inv-ocsimport-sp").innerHTML += data;
	    		        }
	    		    });
        		}
         	};	
        }
    });	
}

function compconfsync(){//------------------------Синхронизация параметров компьютера по базам данных
	
	jQuery.ajax({type:'GET',cache:false,dataType:'html',
		url:'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=compconfsync&id_ocs='+obj.comp.id_ocs,
		data: {"id_adpc": obj.comp.id_adpc, "id_zabbix": obj.comp.id_zabbix},
        success:function (data) {
        	var comp = JSON.parse(data);
        	jQuery("#inv-pc-processor").val(comp.processort);
        	jQuery("#inv-pc-mem").val(comp.memory);
        	jQuery("#inv-pc-osname").val(comp.osname);
        	jQuery("#inv-pc-mac").val(comp.mac);
        	jQuery("#inv-pc-hdd").val(comp.hdd);
        	jQuery("#inv-pc-graphics").val(comp.graphics);
        }
    });
	jQuery("#inv-pc-network").prop("checked", true);
	
}

function config(){

	var reg = 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=objeсts&id=1';
	jQuery.ajax({type:'GET',cache:false,dataType:'html',url:reg,
        success:function (data) {
        	document.getElementById("adm-msg").innerHTML = data;
        }
    });
	
	var width = jQuery("#inv-worck").innerWidth() - 80;
	var height = jQuery(document).innerHeight() - 300;
	var row = height/23;
	row = Math.round(row)-1;
	
	jQuery("#jqGrid-ocs").jqGrid({// -------------------------------------------------Таблица OCS----------------------------------------------------------------
		url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=ocs&oper=sel',
		datatype: "json",
		 colModel: [
		    { label: 'Регион', name: 'region', width: 100, search: true},
		    { label: 'ID', name: 'DEVICEID', width: 100, search: true},
		    { label: 'Название', name: 'NAME', width: 100, search: true},
		    { label: 'Группа', name: 'WORKGROUP', width: 100, search: true},
		    { label: 'Домен', name: 'USERDOMAIN', width: 100, search: true},
		    { label: 'OSNAME', name: 'OSNAME', width: 100, search: true}, 
		    { label: 'OSVERSION', name: 'OSVERSION', width: 100, search: true},
		    { label: 'OSCOMMENTS', name: 'OSCOMMENTS', width: 100, search: true},
		    { label: 'PROCESSORT', name: 'PROCESSORT', width: 100, search: true},
		    { label: 'PROCESSORS', name: 'PROCESSORS', width: 100, search: true},
		    { label: 'PROCESSORN', name: 'PROCESSORN', width: 100, search: true},
		    { label: 'MEMORY', name: 'MEMORY', width: 100, search: true},
		    { label: 'SWAP', name: 'SWAP', width: 100, search: true},
		    { label: 'IPADDR', name: 'IPADDR', width: 100, search: true},
		    { label: 'DNS', name: 'DNS', width: 100, search: true},
		    { label: 'DEFAULTGATEWAY', name: 'DEFAULTGATEWAY', width: 100, search: true},
		    { label: 'ETIME', name: 'ETIME', width: 100, hidden: true},
		    { label: 'LASTDATE', name: 'LASTDATE', width: 100, search: true},
		    { label: 'LASTCOME', name: 'LASTCOME', width: 100, search: true},
		    { label: 'QUALITY', name: 'QUALITY', width: 100, search: true},
		    { label: 'FIDELITY', name: 'FIDELITY', width: 100, search: true},
		    { label: 'USERID', name: 'USERID', width: 100, search: true},
		    { label: 'TYPE', name: 'TYPE', width: 100, hidden: true},
		    { label: 'DESCRIPTION', name: 'DESCRIPTION', width: 100, search: true},
		    { label: 'WINCOMPANY', name: 'WINCOMPANY', width: 100, search: true},
		    { label: 'WINOWNER', name: 'WINOWNER', width: 100, search: true},
		    { label: 'WINPRODID', name: 'WINPRODID', width: 100, search: true},
		    { label: 'WINPRODKEY', name: 'WINPRODKEY', width: 100, search: true},
		    { label: 'USERAGENT', name: 'USERAGENT', width: 100, search: true},
		    { label: 'CHECKSUM', name: 'CHECKSUM', width: 100, search: true},
		    { label: 'SSTATE', name: 'SSTATE', width: 100, hidden: true},
		    { label: 'IPSRC', name: 'IPSRC', width: 100, search: true},
		    { label: 'UUID', name: 'UUID', width: 100, search: true},
		    { label: 'ARCH', name: 'ARCH', width: 100, search: true}			
		],
		viewrecords: true,
		sortname: 'id_reg',
		width: width,
		height: height,
		shrinkToFit: false,
		rowNum: row,
		loadonce: false,    	
		caption: 'Компьютеры OCS',
		pager: "#jqGridPager-ocs"
	});
	jQuery('#jqGrid-ocs').jqGrid('filterToolbar');
	jQuery('#jqGrid-ocs').navGrid('#jqGridPager-ocs',
            { edit: false, add: false, del: true, search: true, refresh: true, view: true, position: "left", cloneToTop: false },
            {height: 'auto',width: 1100,editCaption: "Редактирование",recreateForm: true,closeAfterEdit: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
            {height: 'auto',width: 1100,closeAfterAdd: true,recreateForm: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
            {errorTextFormat: function (data) {return 'Error: ' + data.responseText;}
    });
	jQuery("#jqGrid-ad-users").jqGrid({// -------------------------------------------------Таблица сотрудников----------------------------------------------------------------
		url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=adusers&oper=sel',
		editurl: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=adusers',
		datatype: "json",
		 colModel: [
		    { label: 'регион', name: 'reg', width: 100, search: true},
		    { label: 'ФИО', name: 'cn', width: 200, editable: true, search: true,editrules: {required: true}},
            { label: 'Подразделение', name: 'department', width: 200},
            { label: 'Отдел', name: 'division', width: 150},
            { label: 'Должность', name: 'title', width: 150,  editable: true, edittype:"text"},
            { label: 'Скрыть', name: 'hide_sp', width: 50, formatter: 'checkbox', editable: true,
                edittype: "custom",
                editoptions: {
                    custom_value: getFreightElementValue,
                    custom_element: createFreightEditElement
                }},
            { label: 'Уволен', name: 'dismiss', width: 50, formatter: 'checkbox',  editable: true,
                edittype: "custom",
                editoptions: {
                    custom_value: getFreightElementValue,
                    custom_element: createFreightEditElement
                }},
            { label: 'Телефон', name: 'telephonenumber', width: 100, editable: true},
            { label: 'Тел. гор.', name: 'pager', width: 100, editable: true, search: true},
            { label: 'Мобильный', name: 'mobile', width: 100, editable: true, search: true},
            { label: 'mail', name: 'mail', width: 100, editable: true, search: true},
            { label: 'Описание', name: 'description', width: 100,  editable: true, edittype:"text"},
		    { label: 'Фамилия', name: 'sn', width: 100, search: true},
			{ label: 'givenname', name: 'givenname', width: 100, editable: true, edittype:"text"},
            { label: 'whencreated', name: 'whencreated', width: 100},
			{ label: 'whenchanged', name: 'whenchanged', width: 100},
            { label: 'displayname', name: 'displayname', width: 100},
            { label: 'company', name: 'company', width: 100, editable: true},
            { label: 'name', name: 'name', width: 100,editable: true}, 
            { label: 'badpwdcount', name: 'badpwdcount', width: 100},
            { label: 'badpasswordtime', name: 'badpasswordtime', width: 100},
            { label: 'lastlogoff', name: 'lastlogoff', width: 100},
            { label: 'lastlogon', name: 'lastlogon', width: 100},
            { label: 'pwdlastset', name: 'pwdlastset', width: 100},
            { label: 'accountexpires', name: 'accountexpires', width: 100},
            { label: 'logoncount', name: 'logoncount', width: 100},
            { label: 'samaccountname', name: 'samaccountname', width: 100, editable: true, search: true},
            { label: 'userprincipalname', name: 'userprincipalname', width: 100},
            { label: 'lastlogontimestamp', name: 'lastlogontimestamp', width: 100},
            { label: 'dn', name: 'dn', width: 100, editable: true, search: true}
		],
		viewrecords: true,
		sortname: 'id_reg',
		//autowidth: true,
		width: width,
		height: height,
		rowNum: row,
		shrinkToFit: false,
		loadonce: false,    	
		caption: 'Сотрудники',
		pager: "#jqGridPager-ad-users"
	});
	jQuery('#jqGrid-ad-users').jqGrid('filterToolbar').navGrid('#jqGridPager-ad-users',
            { edit: false, add: false, del: true, search: true, refresh: true, view: true, position: "left", cloneToTop: false },
            {height: 'auto',width: 1100,editCaption: "Редактирование",recreateForm: true,closeAfterEdit: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
            {height: 'auto',width: 1100,closeAfterAdd: true,recreateForm: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
            {errorTextFormat: function (data) {return 'Error: ' + data.responseText;}
    }).inlineNav('#jqGridPager-ad-users',
        // the buttons to appear on the toolbar of the grid
        {
            edit: true,
            add: true,
            del: true,
            cancel: true,
            editParams: {
                keys: true,
            },
            addParams: {
                keys: true
            }
        });
	jQuery("#jqGrid-zabbix").jqGrid({// -------------------------------------------------Таблица оборудования zabbix----------------------------------------------------------------
		url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=zabbix&oper=sel',
		editurl: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=zabbix',
		datatype: "json",
		 colModel: [
		    { label: 'Регион', name: 'reg', width: 100, editable: true, search: true, editrules:{required:true}, edittype:'select',formoptions: {rowpos:1, colpos:1},
		    	editoptions: {
 		    		dataInit: function (element){
 		    			var reg = 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=regions&selid='+element+'&func=&oper=selacl';
 		    			jQuery.ajax({
 		    		        type:'GET', cache:false, dataType:'html', url:reg,
 		    		        success:function (data) {
 		    		        	jQuery(element).html(data);
                                updateTemplateCallBack(jQuery(element).val(), false);
 		    		        }
 		    		    });
 		    			jQuery(element).attr("name", "id_reg");
                        jQuery(element).attr("id", "id_reg");
                        jQuery(element).bind("change", function (e) {
                            updateTemplateCallBack(jQuery(element).val(), false);

                        });
 		    		}
            	}
		    },
		    { label: 'hostid', name: 'hostid', width: 50, search: true},
		    { label: 'Имя', name: 'host', width: 150, editable: true, search: true, editrules: {required: true}, formoptions: {rowpos:2, colpos:1},
                editoptions: {
                    dataInit: function (element){
                        jQuery(element).click(function() {	//Читаем состояние таблицы
                            objlocation('div #host',5);
                            jQuery('div[aria-describedby=inv-obj-location]').css({"z-index": 1000})
                        });
                    }
                }
            },
		    { label: 'Видимое имя', name: 'name', width: 200, editable: true, edittype:"text", formoptions: {rowpos:2, colpos:2}},
            { label: 'IP', name: 'ip', width: 100, editable: true, edittype:"text", formoptions: {rowpos:3, colpos:1}},
            { label: 'DNS', name: 'dns', width: 150, editable: true, edittype:"text", formoptions: {rowpos:3, colpos:2}},
			{ label: 'Щаблон', name: 'template', width: 100,  editable: true, edittype:"text", formoptions: {rowpos:4, colpos:2},
                edittype:'select',
                editoptions: {
                    dataInit: function (element){
                       // var current = jQuery("#jqGrid-zabbix").jqGrid('getRowData',jQuery("#jqGrid-zabbix")[0].p.selrow).reg;
                        var current = jQuery('select[name="id_reg"]').val();
                        var reg = 'index.php?option=com_stalrams&view=zabbix&task=getZabbixAjaxData&format=raw&ajaxtype=zabbixtemplate&selid='+element+'&func=&region='+current;
                        jQuery.ajax({
                            type:'GET', cache:false, dataType:'html', async: false, url:reg,
                            success:function (data) {
                                jQuery(element).html(data);
                            }
                        });
                        jQuery(element).attr("name", "templateid");
                    }
                }
            },
            { label: 'Описание', name: 'description', width: 100,  editable: true, edittype:"text", formoptions: {rowpos:5, colpos:1}},
			{ label: 'Прокси', name: 'proxy_hostid', width: 100, editable: true, edittype:"text", formoptions: {rowpos:5, colpos:2}},
            { label: 'SNMP', name: 'snmp_available', width: 40, editable: true, formatter: 'checkbox', formoptions: {rowpos:6, colpos:1}},
			{ label: 'Псевдоним', name: 'alias', width: 100, editable: true, formoptions: {rowpos:6, colpos:2}},
            { label: 'Этикетка владельца', name: 'asset_tag', width: 100, editable: true, formoptions: {rowpos:7, colpos:1}},
            { label: 'Аппаратные средства', name: 'hardware', width: 100, editable: true, formoptions: {rowpos:8, colpos:1}},
            { label: 'Апп. ср. (описание)', name: 'hardware_full', width: 100,editable: true, formoptions: {rowpos:8, colpos:2}},
            { label: 'Маска подсети', name: 'host_netmask', width: 100, editable: true, formoptions: {rowpos:9, colpos:1}},
            { label: 'Сети узла', name: 'host_networks', width: 100, editable: true, formoptions: {rowpos:9, colpos:2}},
            { label: 'Роутер узла', name: 'host_router', width: 100, editable: true, formoptions: {rowpos:10, colpos:1}},
            { label: 'Архитектура HW', name: 'hw_arch', width: 100, editable: true, formoptions: {rowpos:10, colpos:2}},
            { label: 'Имя установщика', name: 'installer_name', width: 100, editable: true, formoptions: {rowpos:11, colpos:1}},
            { label: 'Местоположение', name: 'location', width: 100, editable: true, formoptions: {rowpos:12, colpos:1}},
            { label: 'Размещение (широта)', name: 'location_lat', width: 100, editable: true, search: true, formoptions: {rowpos:13, colpos:1}},
            { label: 'Размещение (долгота)', name: 'location_lon', width: 100, editable: true, formoptions: {rowpos:13, colpos:2}},
            { label: 'MAC адрес A', name: 'macaddress_a', width: 100, editable: true, formoptions: {rowpos:14, colpos:1}},
            { label: 'MAC адрес B', name: 'macaddress_b', width: 100, editable: true, search: true, formoptions: {rowpos:14, colpos:2}},
            { label: 'Модель', name: 'model', width: 100, editable: true, search: true, formoptions: {rowpos:15, colpos:1}},
            { label: 'inv_name', name: 'inv_name', width: 100, editable: true, search: true, formoptions: {rowpos:15, colpos:2}},
            { label: 'Примечания', name: 'notes', width: 100, editable: true, search: true, formoptions: {rowpos:16, colpos:1}},
            { label: 'OOB IP адрес', name: 'oob_ip', width: 100, editable: true, search: true, formoptions: {rowpos:17, colpos:1}},
            { label: 'OOB маска подсети', name: 'oob_netmask', width: 100, editable: true, search: true, formoptions: {rowpos:17, colpos:2}},
            { label: 'OOB роутер', name: 'oob_router', width: 100, editable: true, search: true, formoptions: {rowpos:18, colpos:1}},
            { label: 'ОС', name: 'os', width: 100, editable: true, search: true, formoptions: {rowpos:19, colpos:1}},
            { label: 'ОС (детализация)', name: 'os_full', width: 100, editable: true, search: true, formoptions: {rowpos:20, colpos:1}},
            { label: 'ОС (описание)', name: 'os_short', width: 100, editable: true, search: true, formoptions: {rowpos:20, colpos:2}},
            { label: '1 мобильный для контакта', name: 'poc_1_cell', width: 100, editable: true, search: true},
            { label: '1 email для контакта', name: 'poc_1_email', width: 100, editable: true, search: true},
            { label: '1 имя для контакта', name: 'poc_1_name', width: 100, editable: true, search: true},
            { label: '1 примечания для контакта', name: 'poc_1_notes', width: 100, editable: true, search: true},
            { label: '1 телефон A для контакта', name: 'poc_1_phone_a', width: 100, editable: true, search: true},
            { label: '1 телефон B для контакта', name: 'poc_1_phone_b', width: 100, editable: true, search: true},
            { label: '1 ник-имя для контакта', name: 'poc_1_screen', width: 100, editable: true, search: true},
            { label: '2 мобильный для контакта', name: 'poc_2_cell', width: 100, editable: true, search: true},
            { label: '2 email для контакта', name: 'poc_2_email', width: 100, editable: true, search: true},
            { label: '2 имя для контакта', name: 'poc_2_name', width: 100, editable: true, search: true},
            { label: '2 примечания для контакта', name: 'poc_2_notes', width: 100, editable: true, search: true},
            { label: '2 телефон A для контакта', name: 'poc_2_phone_a', width: 100, editable: true, search: true},
            { label: '2 телефон B для контакта', name: 'poc_2_phone_b', width: 100, editable: true, search: true},
            { label: '2 ник-имя для контакта', name: 'poc_2_screen', width: 100, editable: true, search: true},
            { label: 'Серийный номер A', name: 'serialno_a', width: 100, editable: true, search: true},
            { label: 'Серийный номер B', name: 'serialno_b', width: 100, editable: true, search: true},
            { label: 'Адрес A', name: 'site_address_a', width: 100, editable: true, search: true},
            { label: 'Адрес B', name: 'site_address_b', width: 100, editable: true, search: true},
            { label: 'Адрес C', name: 'site_address_c', width: 100, editable: true, search: true},
            { label: 'Город', name: 'site_city', width: 100, editable: true, search: true},
            { label: 'Страна', name: 'site_country', width: 100, editable: true, search: true},
            { label: 'Заметки', name: 'site_notes', width: 100, editable: true, search: true},
            { label: 'Размещение стойки', name: 'site_rack', width: 100, editable: true, search: true},
            { label: 'Область / район', name: 'site_state', width: 100, editable: true, search: true},
            { label: 'Почтовый индекс', name: 'site_zip', width: 100, editable: true, search: true},
            { label: 'Программное обеспечение', name: 'software', width: 100, editable: true, search: true},
            { label: 'ПО A', name: 'software_app_a', width: 100, editable: true, search: true},
            { label: 'ПО B', name: 'software_app_b', width: 100, editable: true, search: true},
            { label: 'ПО C', name: 'software_app_c', width: 100, editable: true, search: true},
            { label: 'ПО D', name: 'software_app_d', width: 100, editable: true, search: true},
            { label: 'ПО E', name: 'software_app_e', width: 100, editable: true, search: true},
            { label: 'ПО (полная детализация)', name: 'software_full', width: 100, editable: true, search: true},
            { label: 'Этикетка', name: 'tag', width: 100, editable: true, search: true},
            { label: 'Тип', name: 'inv_type', width: 100, editable: true, search: true},
            { label: 'Тип (полная детализация)', name: 'type_full', width: 100, editable: true, search: true},
            { label: 'URL A', name: 'url_a', width: 100, editable: true, search: true},
            { label: 'URL B', name: 'url_b', width: 100, editable: true, search: true},
            { label: 'URL C', name: 'url_c', width: 100, editable: true, search: true},
            { label: 'Поставщик', name: 'vendor', width: 100, editable: true, search: true},
            { label: 'Пользователь', name: 'vendor', width: 100, search: true},
            { label: 'Дата добавления', name: 'user_date', width: 100, search: true}
		],
		viewrecords: true,
		sortname: 'id_reg',
		//autowidth: true,
		width: width,
		height: height,
		rowNum: row,
		shrinkToFit: false,
		loadonce: false,    	
		caption: 'Сотрудники',
        beforeRequest: function(){
            jQuery(this).jqGrid('setGridParam',{url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=zabbix&oper=sel&myzabbix='+jQuery("#myZabbix").prop("checked")+'&notzabbix='+jQuery("#notZabbix").prop("checked")});
        },
		pager: "#jqGridPager-zabbix"
	});
	jQuery('#jqGrid-zabbix').jqGrid('filterToolbar')
	                        .navGrid('#jqGridPager-zabbix',
            { edit: true, add: true, del: true, search: true, refresh: true, view: true, position: "left", cloneToTop: false },
            {height: 'auto',width: 1100,editCaption: "Редактирование",recreateForm: true, afterShowForm: populateRegion, closeAfterEdit: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
            {height: 'auto',width: 1100,closeAfterAdd: true,recreateForm: true, afterShowForm: populateRegion, errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
            {errorTextFormat: function (data) {return 'Error: ' + data.responseText;}
    });
    function populateRegion() {
        // first of all update the city based on the country
       // updateTemplateCallBack(jQuery("#id_reg").val(), true);
        // then hook the change event of the country dropdown so that it updates cities all the time
        jQuery("#id_reg").bind("change", function (e) {
            updateTemplateCallBack(jQuery("#id_reg").val(), false);
        });
    }
    function updateTemplateCallBack(region, setselected) {
        console.log(region);
        //var current = jQuery("#jqGrid-zabbix").jqGrid('getRowData',jQuery("#jqGrid-zabbix")[0].p.selrow).reg;
       // var current = jQuery('select[name="id_reg"]').val();
        var reg = 'index.php?option=com_stalrams&view=zabbix&task=getZabbixAjaxData&format=raw&ajaxtype=zabbixtemplate&selid=templateid&func=&region='+region;
        jQuery.ajax({
            type:'GET', cache:false, dataType:'html', url:reg,
            success:function (data) {
                jQuery('select[name="templateid"]').html(data);
            }
        });
        jQuery("#template").attr("name", "templateid");
    }
	jQuery("#jqGrid-ad-pc").jqGrid({// -------------------------------------------------Таблица сотрудников----------------------------------------------------------------
		url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=adpc&oper=sel',
		datatype: "json",
		 colModel: [
		    { label: 'reg', name: 'id_reg', width: 100, search: true},
		    { label: 'cn', name: 'cn', width: 100, search: true},
		    { label: 'distinguishedname', name: 'sn', width: 100, search: true},
			{ label: 'whencreated', name: 'title', width: 100},
			{ label: 'whenchanged', name: 'description', width: 100},
			{ label: 'displayname', name: 'telephonenumber', width: 100},
			{ label: 'lastlogoff', name: 'givenname', width: 100},
			{ label: 'lastlogon', name: 'givenname', width: 100},
            { label: 'operatingsystem', name: 'whencreated', width: 100},
			{ label: 'operatingsystemversion', name: 'whenchanged', width: 100},
			{ label: 'operatingsystemservicepack', name: 'department', width: 100},
            { label: 'dnshostname', name: 'displayname', width: 100},
            { label: 'dn', name: 'company', width: 100}
		],
		viewrecords: true,
		sortname: 'id_reg',
		//autowidth: true,
		width: '100%',
		height: 'auto',
		rowNum: 30,
		loadonce: false,    	
		caption: 'Сотрудники',
		pager: "#jqGridPager-ad-pc"
	});
	jQuery('#jqGrid-ad-pc').navGrid('#jqGridPager-ad-pc',
            { edit: false, add: false, del: false, search: true, refresh: true, view: true, position: "left", cloneToTop: false }
    );
	
    	jQuery("#jqGrid-region").jqGrid({// -------------------------------------------------Таблица регионов----------------------------------------------------------------
    		url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=regions&oper=sel',
    		editurl: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=regions',
    		datatype: "json",
    		 colModel: [
    			{ label: 'Регион', name: 'name', width: 5, editable: true, search: true,formoptions: {colpos: 1,rowpos: 1,label: "Регион*:"},editrules: {required: true}},
    			{ label: 'Zabbix адрес', name: 'api', width: 5,  editable: true, edittype:"text",formoptions: {colpos: 1,rowpos: 2,label: "API адрес:"}},
    			{ label: 'Учетка Zabbix', name: 'login', width: 5,  editable: true, edittype:"text",formoptions: {colpos: 2,rowpos: 2,label: "Учетка Zabbix:"}},
    			{ label: 'Пароль Zabbix', name: 'password', width: 5, hidden: true, editable: true, edittype:"password",formoptions: {colpos: 3,rowpos: 2,label: "Пароль: Zabbix"}},
    			{ label: 'OCS адрес', name: 'ocs', width: 5, editable: true,formoptions: {colpos: 1,rowpos: 3,label: "OCS адрес:"}},
    			{ label: 'OCS bd', name: 'ocs_bd', width: 5, editable: true,formoptions: {colpos: 2,rowpos: 3,label: "OCS BD:"}},
                { label: 'Учетка OCS', name: 'login_ocs', width: 5, editable: true,formoptions: {colpos: 3,rowpos: 3,label: "Учетка OCS:",}},
    			{ label: 'Пароль OCS', name: 'password_ocs', width: 5, hidden: true,  editable: true, edittype:"password",formoptions: {colpos: 4,rowpos: 3,label: "Пароль OCS:",}},    		
                { label: 'Домен', name: 'domen', width: 5, editable: true, formoptions: {colpos: 1,rowpos: 4,label: "Домен:"}},
                { label: 'LDAP', name: 'ldap', width: 5, editable: true, formoptions: {colpos: 2,rowpos: 4,label: "LDAP:"}},
                { label: 'searh', name: 'searh_ad', width: 5,editable: true, formoptions: {colpos: 3,rowpos: 4,label: "searh:"}},
                { label: 'Учетка AD', name: 'login_ad', width: 5, editable: true, formoptions: {colpos: 4,rowpos: 4,label: "Учетка AD:"}},
                { label: 'Пароль AD', name: 'password_ad', width: 5, hidden: true,  editable: true, edittype:"password",formoptions: {colpos: 5,rowpos: 4,label: "Пароль AD:",}},
            	{ label: 'Принт.срв', name: 'prn', width: 5, editable: true,formoptions: {colpos: 1,rowpos: 5}},
    			{ label: 'Принт.срв bd', name: 'prn_bd', width: 5, editable: true,formoptions: {colpos: 2,rowpos: 5}},
                { label: 'Учетка принт.срв', name: 'login_prn', width: 5, editable: true,formoptions: {colpos: 3,rowpos: 5}},
    			{ label: 'Пароль принт.срв', name: 'password_prn', width: 5, hidden: true,  editable: true, edittype:"password",formoptions: {colpos: 4,rowpos: 5}},
                { label: 'Изображение', name: 'img', width: 5, editable: true,formoptions: {colpos: 1,rowpos: 6}},
                { label: 'Вн.тел.код', name: 'phone_code_in', width: 5, editable: true,formoptions: {colpos: 2,rowpos: 6}},
                { label: 'Межг.тел.код', name: 'phone_code_out', width: 5, editable: true,formoptions: {colpos: 3,rowpos: 6}},
                { label: 'Индекс', name: 'postcode', width: 5, editable: true,formoptions: {colpos: 1,rowpos: 7}},
                { label: 'Область', name: 'region', width: 5, editable: true,formoptions: {colpos: 2,rowpos: 7}},
                { label: 'Район', name: 'area', width: 5, editable: true,formoptions: {colpos: 3,rowpos: 7}},
                { label: 'Насел.пункт', name: 'city', width: 5, editable: true,formoptions: {colpos: 4,rowpos: 7}},
                { label: 'Адрес', name: 'address', width: 5, editable: true,formoptions: {colpos: 5,rowpos: 7}},
                { label: 'Сообщ.в справ.', name: 'sp_msg', edittype: 'textarea', width: 5, editable: true,formoptions: {colpos: 1,rowpos: 8}}
    		],
    		viewrecords: true,
    		autowidth: true,
    		width: 1100,
    		height: 'auto',
    		rowNum: 30,
    		subGrid: true, 
            subGridRowExpanded: showChildGridPes, 
		    subGridOptions : {plusicon: "ui-icon-triangle-1-e",minusicon: "ui-icon-triangle-1-s",openicon: "ui-icon-arrowreturn-1-e"},
    		loadonce: false,    	
    		caption: 'Регионы',
    		pager: "#jqGridPager-region"
    	});
    	jQuery('#jqGrid-region').navGrid('#jqGridPager-region',
                { edit: true, add: true, del: true, search: true, refresh: true, view: true, position: "left", cloneToTop: false },
                {height: 'auto',width: 1200,editCaption: "Редактирование",recreateForm: true,closeAfterEdit: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
                {height: 'auto',width: 1200,closeAfterAdd: true,recreateForm: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
                {errorTextFormat: function (data) {return 'Error: ' + data.responseText;}
        });
    	function showChildGridPes(parentRowID, parentRowKey) {//.........................................Таблица производственных отделений........................................
            var childGridID = parentRowID + "_table";
            var childGridPagerID = parentRowID + "_pager";

            jQuery('#' + parentRowID).append('<table id=' + childGridID + '></table><div id=' + childGridPagerID + ' class=scroll></div>');

            jQuery("#" + childGridID).jqGrid({
                url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=pesedit&oper=sel&id='+parentRowKey,
                editurl: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=pesedit&regid='+parentRowKey,
                mtype: "GET",
                datatype: "json",
                page: 1,
                colModel: [
                    { label: 'Производственное отделение', name: 'name', width: 30, editable: true,formoptions: {colpos: 1,rowpos: 1,label: "Производственное отделение:"}},
                    { label: 'Код', name: 'code', width: 30, editable: true,formoptions: {colpos: 1,rowpos: 2,label: "Код:"}},
                    { label: 'regid', name: 'regid', width: 30, hidden: true},
                    { label: 'Изображение', name: 'img', width: 5, editable: true,formoptions: {colpos: 1,rowpos: 3}},
                    { label: 'Вн.тел.код', name: 'phone_code_in', width: 5, editable: true,formoptions: {colpos: 2,rowpos: 3}},
                    { label: 'Межг.тел.код', name: 'phone_code_out', width: 5, editable: true,formoptions: {colpos: 3,rowpos: 3}},
                    { label: 'Индекс', name: 'postcode', width: 5, editable: true,formoptions: {colpos: 1,rowpos: 4}},
                    { label: 'Область', name: 'region', width: 5, editable: true,formoptions: {colpos: 2,rowpos: 4}},
                    { label: 'Район', name: 'area', width: 5, editable: true,formoptions: {colpos: 3,rowpos: 4}},
                    { label: 'Насел.пункт', name: 'city', width: 5, editable: true,formoptions: {colpos: 4,rowpos: 4}},
                    { label: 'Адрес', name: 'address', width: 5, editable: true,formoptions: {colpos: 5,rowpos: 4}},
                    { label: 'Сообщ.в справ.', name: 'sp_msg', edittype: 'textarea', width: 5, editable: true,formoptions: {colpos: 1,rowpos: 5}},
                    { label: 'Искать в DN', name: 'dn', width: 5, editable: true,formoptions: {colpos: 2,rowpos: 5}}
                ],
                viewrecords: false,
				loadonce: false,
                width: 1000,
                subGrid: true, 
                subGridRowExpanded: showChildGridObj, 
    		    subGridOptions : {plusicon: "ui-icon-triangle-1-e",minusicon: "ui-icon-triangle-1-s",openicon: "ui-icon-arrowreturn-1-e"},
                height: '100%',
                pager: "#" + childGridPagerID
            });
            jQuery("#" + childGridID).navGrid("#" + childGridPagerID,
                    { edit: true, add: true, del: true, search: false, refresh: false, view: false, position: "left", cloneToTop: false },
                    { height: 'auto', width: 1200,editCaption: "Редактирование",recreateForm: true,closeAfterEdit: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
                    {height: 'auto',width: 1200,closeAfterAdd: true,recreateForm: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
                    {errorTextFormat: function (data) {return 'Error: ' + data.responseText;}
             });
            
    	}
    	function showChildGridObj(parentRowID, parentRowKey) {//.........................................Таблица объектов........................................
            var childGridID = parentRowID + "_table";
            var childGridPagerID = parentRowID + "_pager";

            jQuery('#' + parentRowID).append('<table id=' + childGridID + '></table><div id=' + childGridPagerID + ' class=scroll></div>');

            jQuery("#" + childGridID).jqGrid({
                url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=objtypeedit&oper=sel&all=true&id='+parentRowKey,
                editurl: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=objtypeedit',
                mtype: "GET",
                datatype: "json",
                page: 1,
                colModel: [
                    { label: 'Тип объекта', name: 'name', width: 30, editable: true},
                    { label: 'Код', name: 'code', width: 30, editable: true},
                    { label: 'ID ПО', name: 'idpo', width: 30, hidden: false},
                ],
                viewrecords: false,
				loadonce: true,
                width: 900,
                subGrid: true, 
                subGridRowExpanded: showChildGridObject, 
    		    subGridOptions : {plusicon: "ui-icon-triangle-1-e",minusicon: "ui-icon-triangle-1-s",openicon: "ui-icon-arrowreturn-1-e"},
                height: '100%',
                pager: "#" + childGridPagerID
            });
            jQuery("#" + childGridID).navGrid("#" + childGridPagerID,
                    { edit: true, add: true, del: true, search: false, refresh: false, view: false, position: "left", cloneToTop: false },
                    {height: 'auto',width: 620,editCaption: "Редактирование",recreateForm: true,closeAfterEdit: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
                    {height: 'auto',width: 300,closeAfterAdd: true,recreateForm: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
                    {errorTextFormat: function (data) {return 'Error: ' + data.responseText;}
             });
            
    	}
    	function showChildGridObject(parentRowID, parentRowKey) {
            var childGridID = parentRowID + "_table";
            var childGridPagerID = parentRowID + "_pager";
            var id = parentRowID.lastIndexOf('_');
            var gridid = parentRowID.substring(0,id);
            var grid = jQuery("#"+gridid);
            

            jQuery('#' + parentRowID).append('<table id=' + childGridID + '></table><div id=' + childGridPagerID + ' class=scroll></div>');

            jQuery("#" + childGridID).jqGrid({
                url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=objectsedit&oper=sel&basetype='+parentRowKey+'&pes='+grid.getCell(parentRowKey, 'idpo'),
                editurl: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=objectsedit&basetype='+parentRowKey+'&pes='+grid.getCell(parentRowKey, 'idpo'),
                mtype: "GET",
                datatype: "json",
                page: 1,
                colModel: [
                    { label: 'Объект', name: 'name', width: 30, editable: true},
                    { label: 'Код', name: 'number', width: 30, editable: true},                    
                    { label: 'ID ПЭС', name: 'pes', width: 30, hidden: true},
                    { label: 'ID Тип', name: 'basetype', width: 30, hidden: true},
                    { label: 'Щирота', name: 'latitude', width: 30, editable: true},
                    { label: 'Долгота', name: 'longitude', width: 30, editable: true},
                    { label: 'Высота', name: 'altitude', width: 30, editable: true},                  
                ],
                viewrecords: false,
				loadonce: false,
                width: 800,
                subGrid: true, 
                subGridRowExpanded: showChildGridRoom, 
    		    subGridOptions : {plusicon: "ui-icon-triangle-1-e",minusicon: "ui-icon-triangle-1-s",openicon: "ui-icon-arrowreturn-1-e"},
                height: '100%',
                pager: "#" + childGridPagerID
            });
            jQuery("#" + childGridID).navGrid("#" + childGridPagerID,
                    { edit: true, add: true, del: true, search: false, refresh: false, view: false, position: "left", cloneToTop: false },
                    {height: 'auto',width: 620,editCaption: "Редактирование",recreateForm: true,closeAfterEdit: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
                    {height: 'auto',width: 300,closeAfterAdd: true,recreateForm: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
                    {errorTextFormat: function (data) {return 'Error: ' + data.responseText;}
             });
            
    	}
    	function showChildGridRoom(parentRowID, parentRowKey) {///--------------------------------------Помещение------------------------------------------
            var childGridID = parentRowID + "_table";
            var childGridPagerID = parentRowID + "_pager";
          
            jQuery('#' + parentRowID).append('<table id=' + childGridID + '></table><div id=' + childGridPagerID + ' class=scroll></div>');

            jQuery("#" + childGridID).jqGrid({
                url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=roomedit&oper=sel&id='+parentRowKey,
                editurl: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=roomedit&codepo='+parentRowKey,
                mtype: "GET",
                datatype: "json",
                page: 1,
                colModel: [
                    { label: 'Помещение', name: 'name', width: 30, editable: true,formoptions: {colpos: 1,rowpos: 1,label: "Объект:"}},
                    { label: 'Код', name: 'code', width: 30, editable: true,formoptions: {colpos: 1,rowpos: 2,label: "Код:"}}                 
                ],
                viewrecords: false,
				loadonce: false,
                width: 700,
                height: '100%',
                pager: "#" + childGridPagerID
            });
            jQuery("#" + childGridID).navGrid("#" + childGridPagerID,
                    { edit: true, add: true, del: true, search: false, refresh: false, view: false, position: "left", cloneToTop: false },
                    { height: 'auto', width: 620,editCaption: "Редактирование",recreateForm: true,closeAfterEdit: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
                    {height: 'auto',width: 300,closeAfterAdd: true,recreateForm: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
                    {errorTextFormat: function (data) {return 'Error: ' + data.responseText;}
             });  
    	}
    	
    	
    	jQuery("#jqGrid-group-type").jqGrid({// -----------------------------------------------Таблица групп оборудования----------------------------------------
    		url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=select&select=neq_category&name=name&oper=sel',
    		editurl: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=editselect&select=neq_category&name=name',
    		datatype: "json",
    		 colModel: [
    			{ label: 'Группа', name: 'name', width: 5, editable: true, search: true,formoptions: {colpos: 1,rowpos: 1,label: "Группа*:"},editrules: {required: true}}	
    		],
    		viewrecords: true,
    		width: 1100,
    		height: 'auto',
    		rowNum: 30,
    		subGrid: true, 
            subGridRowExpanded: showChildGridAp, 
		    subGridOptions : {plusicon: "ui-icon-triangle-1-e",minusicon: "ui-icon-triangle-1-s",openicon: "ui-icon-arrowreturn-1-e"},
    		loadonce: true,    	
    		caption: 'Группы',
    		pager: "#jqGridPager-group-type"
    	});
    	jQuery('#jqGrid-group-type').navGrid('#jqGridPager-group-type',
             { edit: true, add: true, del: true, search: true, refresh: true, view: true, position: "left", cloneToTop: false },
                {height: 'auto',width: 1100,editCaption: "Редактирование",recreateForm: true,closeAfterEdit: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
                {height: 'auto',width: 1100,closeAfterAdd: true,recreateForm: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
                {errorTextFormat: function (data) {return 'Error: ' + data.responseText;}
          });
    	function showChildGridAp(parentRowID, parentRowKey) {//.........................................Таблица наименования оборудования........................................
            var childGridID = parentRowID + "_table";
            var childGridPagerID = parentRowID + "_pager";

            jQuery('#' + parentRowID).append('<table id=' + childGridID + '></table><div id=' + childGridPagerID + ' class=scroll></div>');

            jQuery("#" + childGridID).jqGrid({
                url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=equipment&catcode='+parentRowKey+'&oper=sel',
                editurl: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=equipment&catcode='+parentRowKey,
                mtype: "GET",
                datatype: "json",
                page: 1,
                colModel: [
                    { label: 'Тип оборудования', name: 'name', width: 30, editable: true, formoptions: {colpos: 1,rowpos: 1,label: "Тип оборудования:"}},
                    { label: 'Код', name: 'code', width: 30, editable: true,formoptions: {colpos: 1,rowpos: 2,label: "Код:"}},
                    { label: 'Имя рисунка', name: 'img', width: 30, editable: true, edittype:'text',formoptions: {colpos: 1,rowpos: 3,label: "Рисунок:"}},
                    {name: 'Рисунок',width: 70,align: 'center',formatter: formatImage},
                ],
                viewrecords: false,
				loadonce: true,
                width: 1000,
                height: '100%',
                pager: "#" + childGridPagerID
            });
            function formatImage(cellValue, options, rowObject) {
                var imageHtml = "<img src='../images/com_stalrams/" + cellValue + "' originalValue='" + cellValue + "' />";
                return imageHtml;
            }
            jQuery("#" + childGridID).navGrid("#" + childGridPagerID,
                    { edit: true, add: true, del: true, search: false, refresh: false, view: false, position: "left", cloneToTop: false },
                    {height: 'auto',width: 620,editCaption: "Редактирование",recreateForm: true,closeAfterEdit: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
                    {height: 'auto',width: 600,closeAfterAdd: true,recreateForm: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
                    {errorTextFormat: function (data) {return 'Error: ' + data.responseText;}
             });
            
    	}
    	
    	select('#jqGrid-cnf-doc-type', '#jqGridPager-cnf-doc-type', 'inv_doc_types', 'doc_type', 'Типы документов', 'Документ', 250, 400);//Типы документов
    	select('#jqGrid-accounting', '#jqGridPager-accounting', 'inv_type_accounting', 'accounting', 'Типы учетов', 'Учет', 250, 400);//Типы учетов
    	select('#jqGrid-mon-matrix-type', '#jqGridPager-mon-matrix-type', 'inv_mon_matrix_type', 'type', 'Типы матрицы', 'Тип', 250, 400);//Типы МФУ
    	
    	jQuery("#jqGrid-supplier").jqGrid({//-------------------------------------------------------------------Таблица регионов-------------------------------------------------
    		url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=supplier&oper=sel',
    		editurl: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=supplier',
    		datatype: "json",
    		 colModel: [{ label: 'Поставщик', name: 'supplier', width: 300, editable: true, search: true},
    		 			{ label: 'Город', name: 'city', width: 300, editable: true, search: true,
			    			 editoptions: {
			 		    		dataInit: function (element){
			 					    jQuery(element).autocomplete({//-------------------------Поиск по странам
			 						    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_cities&field=city&field2=region&strong=1&limit=40",
			 						    maxHeight:150,
			 						    select: function(event, ui) {
			 						    	jQuery("#jqGrid-supplier").jqGrid('setGridParam',{editurl: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=supplier&id_city2='+ui.item.id});
			 						    }	 
			 						});	
			 		    		}
			 		    	}
    		 			},
    		 			{ label: 'Сайт', name: 'website', width: 300,  formatter: 'link', editable: true, search: true},
    		 			{ label: 'id_city', name: 'id_city', width: 300, editable: true, hidden: true} ],    		
 			viewrecords: true,
 			sortname: 'supplier',
 			//autowidth: true,
 			width: 920,
 			height: height,
 			rowNum: row,
 			shrinkToFit: false,
 			loadonce: false,    	
 			caption: 'Поставщики',
  			pager: "#jqGridPager-supplier"
 		});
 		jQuery('#jqGrid-supplier').navGrid('#jqGridPager-supplier',
 	            { edit: true, add: true, del: true, search: true, refresh: true, view: true, position: "left", cloneToTop: false },
 	            {height: 'auto',width: 1100,editCaption: "Редактирование",recreateForm: true,closeAfterEdit: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
 	            {height: 'auto',width: 1100,closeAfterAdd: true,recreateForm: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
 	            {errorTextFormat: function (data) {return 'Error: ' + data.responseText;}
 	    });

    	jQuery("#jqGrid-region-stores").jqGrid({//-------------------------------------------------------------------Таблица регионов-------------------------------------------------
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
    				jQuery("#jqGrid-conf-stores").jqGrid('setGridParam',{url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=stores&oper=sel&id_region='+rowid,
    																editurl: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=stores&id_region='+rowid, datatype: "json"})
                                                 .jqGrid('setCaption', 'Регион::'+jQuery(this).getCell(rowid, 'name'))
    				                             .jqGrid('clearGridData')
    				                             .trigger("reloadGrid");
    			}					
    		},
    	});
    	
    	jQuery("#jqGrid-conf-stores").jqGrid({//-------------------------------------------------------------------Таблица складов-------------------------------------------------
    		url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=stores&oper=sel&id_region=0',
    		datatype: "json",
    		 colModel: [{ label: 'Название', name: 'name', width: 200},
    		           	{ label: 'ПО', name: 'pes', width: 150},
    		           	{ label: 'Код', name: 'code', width: 150},
    		 			{ label: 'Объект', name: 'po', width: 150},
    		 			{ label: 'Помещение', name: 'a', width: 150},
    		 			{ label: 'id_region', name: 'id_region', width: 10, hidden: true},
    		 			{ label: 'id_pes', name: 'id_pes', width: 10, hidden: true},
    		 			{ label: 'id_po', name: 'id_po', width: 10, hidden: true},
    		 			{ label: 'id_a', name: 'id_a', width: 10, hidden: true}],
    		
    		viewrecords: false,
    		width: 650,
    		height: 300,
    		rowNum: 50,
    		onSelectRow: function(rowid, selected) {
    			if(rowid != null) {
    				jQuery("#jqGrid-conf-stores").jqGrid('setGridParam',{url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=docs&oper=sel&region='+rowid,
    																editurl: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=docs&region='+rowid, datatype: "json"})
    				                             .jqGrid('setCaption', 'Регион::'+jQuery(this).getCell(rowid, 'name'))
    				                             .trigger("reloadGrid");
    			}					
    		}
    	});
    	
    	
}

function code(){
    var rowKey = jQuery("#jqGrid-location-region").jqGrid('getGridParam',"selrow");
    var code = jQuery("#jqGrid-location-region").getCell(rowKey, 'code');
    rowKey = jQuery("#jqGrid-location-obj").jqGrid('getGridParam',"selrow");
    code += '-' + jQuery("#jqGrid-location-obj").getCell(rowKey, 'code');
    rowKey = jQuery("#jqGrid-location-object").jqGrid('getGridParam',"selrow");
    if (rowKey){
        var number = jQuery("#jqGrid-location-object").getCell(rowKey, 'number');
        if (number < 100) number = '0' + number;
        if (number < 10) number = '0' + number;
        code += number;
    }

    rowKey = jQuery("#jqGrid-location-a").jqGrid('getGridParam',"selrow");

    if (document.getElementById("loc-room-ra").checked){
        code += '-k';
        var number = jQuery("#loc-room").val();
        if (number < 100) number = '0' + number;
        if (number < 10) number = '0' + number;
        code += number;
        jQuery("#loc-a").val('Комната: '+number);
        if (rowKey) {
            rowKey = false;
            jQuery("#jqGrid-location-a").trigger("reloadGrid");
        }
    }

    if (document.getElementById("loc-floor-ra").checked){
        code += '-f';
        var number = jQuery("#loc-floor").val();
        if (number < 10) number = '0' + number;
        code += number;
        jQuery("#loc-a").val('Этаж: '+number);
        if (rowKey){
            rowKey = false;
            jQuery("#jqGrid-location-a").trigger("reloadGrid");
        }
    }

    if (document.getElementById("loc-floor-null").checked){
        jQuery("#loc-a").val('');
        if (rowKey){
            rowKey = false;
            jQuery("#jqGrid-location-a").trigger("reloadGrid");
        }
    }

    if (rowKey){
        code += '-a';
        var number = jQuery("#jqGrid-location-a").getCell(rowKey, 'code');
        if (number < 10) number = '0' + number;
        code += number;
        jQuery("#loc-a").val(jQuery("#jqGrid-location-a").getCell(rowKey, 'name'));
    }

    if (loc_dlg.lvl == 5){
        rack = jQuery("#loc-rack").val();
        jQuery("#loc-info-rack").val(rack);
        if (rack < 10) rack = '0' + rack;
        code += '-'+rack;

        rowKey = jQuery("#jqGrid-location-equipment").jqGrid('getGridParam',"selrow");
        if (rowKey) {
            jQuery("#loc-info-cat").val(jQuery("#jqGrid-location-equipment").getCell(rowKey, 'category'));
            jQuery("#loc-info-equipment").val(jQuery("#jqGrid-location-equipment").getCell(rowKey, 'name'));
            jQuery("#loc-info-eqnumber").val(jQuery("#loc-eqnumber").val());
            code += '-';
            code += jQuery("#jqGrid-location-equipment").getCell(rowKey, 'code');
            var number = jQuery("#loc-eqnumber").val();
            if (number < 10) number = '0' + number;
            code += number;
        }
    }
    jQuery("#loc-code").val(code);

}

function objlocation(strdiv, lvl){//Кодирование местонахождения

	loc_dlg.strdiv = strdiv;
	loc_dlg.lvl = lvl;
	clearinput();
	
	function clearinput(){
		jQuery("#loc-pes").val('');	
		jQuery("#loc-po").val('');
		jQuery("#loc-a").val('');
		jQuery("#loc-latitude").val('');	
		jQuery("#loc-longitude").val('');
		jQuery("#loc-info-rack").val('');
        jQuery("#loc-info-cat").val('');
        jQuery("#loc-info-equipment").val('');
        jQuery("#loc-info-eqnumber").val('');
	}
    if (lvl <5){
        jQuery(".loc-lvl-5").hide();
        var w = 250, h=400;
        jQuery( "#inv-obj-location" ).dialog({
            height: 600
        });
    }else{
        jQuery(".loc-lvl-5").show();
        var w = 210, h=400;
        jQuery( "#inv-obj-location" ).dialog({
            height: 700
        });
    }
		

	jQuery("#jqGrid-location-region").jqGrid({//-------------------------------------------------------------------Таблица регионов-------------------------------------------------
		url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=regions&oper=regpes',
		datatype: "json",
		 colModel: [{ label: 'Регион', name: 'name', width: 20},
                    { label: 'Производственное отделение', name: 'pes', width: w+20},
		 			{ label: 'reg_id', name: 'reg_id', width: 20, hidden: true},
                    { label: 'code', name: 'code', width: 20, hidden: true}],
		viewrecords: false,
		loadui: 'disable',
		width: w+20,
		height: h,
		rowNum: 50,
        grouping: true,
        groupingView: {
            groupField: ["name"],
            groupColumnShow: [false],
            groupText: ["<b>{0}</b>"],
            groupOrder: ["asc"],
            groupSummary: [false],
            groupCollapse: false
        },
		onSelectRow: function(rowid, selected) {
			if(rowid != null) {
                jQuery("#jqGrid-location-obj").jqGrid('setGridParam',{url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=objtypeedit&oper=sel&id='+rowid, datatype: "json"})
                    .jqGrid('clearGridData')
                    .trigger("reloadGrid");
                clearinput();
                jQuery("#jqGrid-location-object").jqGrid('clearGridData');
                jQuery("#jqGrid-location-a").jqGrid('clearGridData');
                jQuery("#loc-pes").val(jQuery("#jqGrid-location-region").getCell(rowid, 'pes'));
                jQuery("#loc-code").val(jQuery("#jqGrid-location-region").getCell(rowid, 'code'));
                jQuery("#loc-reg").val(jQuery("#jqGrid-location-region").getCell(rowid, 'name'));
			}					
		}
	});
	jQuery("#jqGrid-location-obj").jqGrid({//-----------------------Типы объектов----------------------------------

        mtype: "GET",
        datatype: "json",
        page: 1,
        colModel: [
            { label: 'Тип объекта', name: 'name', width: 30},
            { label: 'Код', name: 'code', width: 30, hidden: true},
            { label: 'ID ПО', name: 'idpo', width: 30, hidden: true}
        ],
        loadui: 'disable',
        viewrecords: false,
		loadonce: false,
		width: w,
		height: h,
		onSelectRow: function(rowid, selected) {
			if(rowid != null) {
				jQuery("#jqGrid-location-object").jqGrid('setGridParam',{url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=objectsedit&oper=sel&basetype='+rowid+'&pes='+jQuery("#jqGrid-location-obj").getCell(rowid, 'idpo'), datatype: "json"})
				                                 .jqGrid('clearGridData')
                                                 .trigger("reloadGrid");
				jQuery("#jqGrid-location-a").jqGrid('clearGridData');
				jQuery("#loc-po").val('');
				jQuery("#loc-a").val('');
				code();				
			}
		}
	});
	jQuery("#jqGrid-location-object").jqGrid({
        url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=objectsedit&oper=sel&basetype=0&pes=0',
        mtype: "GET",
        datatype: "json",
        colModel: [
            { label: 'Объект', name: 'name', width: 30},
            { label: 'Код', name: 'number', width: 30, hidden: true},
            { label: 'ID ПЭС', name: 'pes', width: 30, hidden: true},
            { label: 'ID Тип', name: 'basetype', width: 30, hidden: true},            
            { label: 'latitude', name: 'latitude', width: 30, hidden: true},
            { label: 'longitude', name: 'longitude', width: 30, hidden: true},
            { label: 'altitude', name: 'altitude', width: 30, hidden: true},
        ],
        loadui: 'disable',
        viewrecords: false,
		loadonce: false,
        rowNum: 10000,
		width: w,
		height: h,
		onSelectRow: function(rowid, selected) {
			if(rowid != null) {
				jQuery("#jqGrid-location-a").jqGrid('setGridParam',{url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=roomedit&oper=sel&id='+rowid, datatype: "json"})
				                            .jqGrid('clearGridData')
				                            .trigger("reloadGrid");
				var pes = jQuery("#jqGrid-location-object").getCell(rowid, 'pes'); 
				//jQuery('#jqGrid-location-pes').jqGrid('setSelection',pes);
				jQuery("#loc-pes").val(jQuery("#jqGrid-location-region").getCell(pes, 'pes'));
	            jQuery("#loc-po").val(jQuery("#jqGrid-location-object").getCell(rowid, 'name'));
	            jQuery("#loc-a").val('');
	            jQuery("#loc-latitude").val(jQuery("#jqGrid-location-object").getCell(rowid, 'latitude'));
	            jQuery("#loc-longitude").val(jQuery("#jqGrid-location-object").getCell(rowid, 'longitude'));
	            jQuery("#loc-altitude").val(jQuery("#jqGrid-location-object").getCell(rowid, 'altitude'));
	            code();
			}
		}
	});
	jQuery("#jqGrid-location-a").jqGrid({
        url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=roomedit&oper=sel&id=0',
        mtype: "GET",
        datatype: "json",
        colModel: [
            { label: 'Помещение', name: 'name', width: 30},
            { label: 'Код', name: 'code', width: 30, hidden: true}
        ],
        loadui: 'disable',
        viewrecords: false,
        loadonce: false,
        width: w,
        height: 200,
        onSelectRow: function(rowid, selected) {
            if(rowid != null) {
                jQuery("#loc-a").val(jQuery("#jqGrid-location-a").getCell(rowid, 'name'));
                jQuery("#loc-room-ra").prop("checked", false);
                jQuery("#loc-floor-ra").prop("checked", false);
                jQuery("#lloc-floor-null").prop("checked", false);
                code();
            }
        }
    });
    jQuery("#jqGrid-location-equipment").jqGrid({
        url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=equipment&oper=cateq',
        mtype: "GET",
        datatype: "json",
        colModel: [
            { label: 'Категория', name: 'category', width: 30},
            { label: 'Оборудование', name: 'name', width: w+40},
            { label: 'Код', name: 'code', width: 30, hidden: true},
            { label: 'img', name: 'img', width: 30, hidden: true},
            { label: 'catcode', name: 'catcode', width: 30, hidden: true}
        ],
        loadui: 'disable',
        viewrecords: false,
        loadonce: false,
        width: w+40,
        height: h,
        rowNum: 2000,
        grouping: true,
        groupingView: {
            groupField: ["category"],
            groupColumnShow: [false],
            groupText: ["<b>{0}</b>"],
            groupOrder: ["asc"],
            groupSummary: [false],
            groupCollapse: true
        },
        onSelectRow: function(rowid, selected) {
            if(rowid != null) {
                code();
            }
        }
    });
	jQuery('#inv-obj-location').dialog('open').dialog( "moveToTop" );// Открываем диалог определения местоположения

}

function addobjectdb(clone){// Функция добавления объекта в базу
	var strdiv = "#inv-addobject";

    obj.read_obj_forn(strdiv);//Читаем свойства объекта из формы

    if (!obj.validate()) return false;//Если проверка объекта не прошла

    obj.parts = jQuery(strdiv+ " #jqGrid-obj-parts2").getRowData();//Передаем массив составных объектов
    obj.docs = jQuery(strdiv+ " #jqGrid-obj-docs2").getRowData();//Передаем массив документов объекта

    if (obj.parts.length > 0){
        jQuery( "#dialog-update-loc" ).dialog('open');
    }

    // Изменяем объект
    var intervalID = setInterval( function() {
        if (jQuery( "#dialog-update-loc" ).dialog('isOpen')){

        }else{
            obj.add_db(strdiv, clone);
            clearInterval(intervalID);
        }

    } , 1000);

}
function editobjectdb(){

    var strdiv = "#inv-info-object";

    obj.read_obj_forn(strdiv);//Читаем свойства объекта из формы

    obj.parts = jQuery("#jqGrid-obj-info-parts2").getRowData();//Передаем массив составных объектов
    obj.docs = jQuery("#jqGrid-obj-info-docs2").getRowData();//Передаем массив документов объекта

    if (!obj.validate()) return false;//Если проверка объекта не прошла

    if (obj.parts.length > 0){
        jQuery( "#dialog-update-loc" ).dialog('open');
    }

    // Изменяем объект
    var intervalID = setInterval( function() {
        if (jQuery( "#dialog-update-loc" ).dialog('isOpen')){

        }else{
            obj.edit_db();
            clearInterval(intervalID);
        }

    } , 1000);

}

function autocomplete_inc(elem, source){
    jQuery(elem).autocomplete({//-------------------------Поиск по производителю
        source: source,
        maxHeight:150,
        select: function(event, ui) {
            jQuery(this).data('id_inv', ui.item.id);
            jQuery(this).data('value_inv', ui.item.value);
        }
    });
    jQuery(elem).focusout(function(){
        if (jQuery(this).data('value_inv') != jQuery(this).val()){
            jQuery(this).data('value_inv', null);
            jQuery(this).data('id_inv', null);
        }
        if (jQuery(this).data('id_inv') == null){
            jQuery(this).val('');
        }
    });
    jQuery(elem).prop('title',"Выберите из списка");
    jQuery(elem).tooltip({
        position: {my: "left top",at: "right+5 top-5"},
        show: {effect: "slideDown",delay: 100},
        hide: {effect: "explode",delay: 100}
    });
}

function jqGrid_custom_element(value, options, source) {
	  var ac = jQuery('<input type="text"/>');
	  jQuery(ac).val(value);
	  jQuery(ac).autocomplete({//-------------------------Поиск по 
		    source: source,
  	    maxHeight:150,
  	    select: function(event, ui) {
  	    	jQuery(ac).data('id_inv', ui.item.id);
  	    	jQuery(ac).data('value_inv', ui.item.value);
  	    }	
    });
	  jQuery(ac).focusout(function(){
		  console.log(jQuery(ac).data('value_inv'));
		  console.log(jQuery(ac).data('id_inv'));
		  if (jQuery(ac).data('value_inv') != jQuery(ac).val()){
			  jQuery(ac).data('value_inv', null);
			  jQuery(ac).data('id_inv', null);	       	
		  }
		  if (jQuery(ac).data('id_inv') == null){
			  jQuery(ac).val('');
			  jQuery(ac).parent().append("Выберите из списка")
		  }	        				  	        				  
	  });
	  return ac;	        			  
}

function createFreightEditElement(value, editOptions) {
    if (value ==''){
        var elem = jQuery('<input type="checkbox" value="0">');
    }else{
        var elem = jQuery(value);
        jQuery(elem).prop("disabled", false);
    }


    return elem;
}	
function getFreightElementValue(elem, oper, value) {
    if (oper === "set") {

    	var radioButton = jQuery(elem).find("input:checkbox[value='" + value + "']");
    	if (radioButton.length > 0) {
            radioButton.prop("checked", true);
        }
    }
    if (oper === "get") {        	
        return jQuery(elem).prop("checked");
    }
}

var makeQRCode = function(qrdiv, text) {
    var rst = "";
    
    var addPoint = function(t) {
        rst += 
            "<div class='" + 
            (t ? "qrTrue" : "qrFalse") +
            "'> </div>";                    
    };
    
    var newLine = function() { rst += "<br/>"; }
    var code = qrencode.encodeString(text, 0, 
        qrencode.QR_ECLEVEL_L,
        qrencode.QR_MODE_8, true);
        
    var i;
    var j;
    
    for ( i = 0; i < 2; i++ ) {
        for ( j = 0; j < code.length; j++ ) addPoint(false);
        newLine();
    }
    
    for ( j = 0; j < code.length; j++ ) {
        addPoint(false);
        addPoint(false);
        
        for ( i = 0; i < code.length; i++ )
            addPoint(code[i][j]);
        
        addPoint(false);
        addPoint(false);
        newLine();
    }
    
    for ( i = 0; i < 2; i++ ) {
        for ( j = 0; j < code.length; j++ ) addPoint(false);
        newLine();
    }
    
    jQuery(qrdiv).html(rst);
};

function TimeAlert(str){
	jQuery("#bottom-message-sp").html(str);
	  // Плавно показываем окно. Интервал - пол секунды.    
    jQuery('.bottom-message').fadeIn(500,function(){      
     // Определяем id таймера и устанавливаем его на 2,5 секунды.
     // По истечению 2,5 секунды будет происходить код: $('.bottom-message').fadeOut(500); , а именно будет плавно исчезать окно.   
    timeoutID = setTimeout("jQuery('.bottom-message').fadeOut(500);",2500);    
         
    });     
    // Отменяем таймер, чтобы при нескольких нажатий на кнопку не сбивался таймер.
    clearTimeout(timeoutID);
}

function NowDate(minfree){
	var d=new Date();
	var day=d.getDate();
	var month=d.getMonth() + 1;
	var year=d.getFullYear();
	var hoursr=d.getHours();
	var minutes=d.getMinutes();
	if (minfree) return  year + "." + month + "." + day;
	else return  year + "." + month + "." + day + ' ' + hoursr + ':' + minutes;
	
}