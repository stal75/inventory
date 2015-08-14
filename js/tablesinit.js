//------------------------------------------------------------------------------------
//------------------          Инициализация таблиц       -----------------------------
//------------------------------------------------------------------------------------

jQuery(document).ready(function () {

    jQuery("#jqGrid-adm-journal").jqGrid({//------------------------------------------------------- Таблица журнала-------------------------------------------
		datatype: "json",
		 colModel: [
			{ label: 'Время', name: 'date_time', width: 150, search: true,
				searchoptions: {
					dataInit: function (element) {
						jQuery(element).datepicker({
					        dateFormat: 'yy.mm.dd',
					        changeMonth: true,
					        changeYear: true,
					        yearRange: "1940:2040",
					        showOn: 'focus'
					    });
			        }
				}},
			{ label: 'Пользователь', name: 'username', width: 250, search: true},
			{ label: 'Информация', name: 'msg', width: 500, search: true,
				searchoptions: {
					dataInit: function (element) {
			            window.setTimeout(function () {
			            	jQuery(element).autocomplete({//-------------------------Поиск по 
			            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_log&field=msg&group=1",
			            	    maxHeight:150
                            });
			            }, 100);
			        }
				}
			}
		],
		viewrecords: true, 
		sortname: 'date_time',
		width: divwidth-100,
		height: 600,
		rowNum: Math.round(600/23)-1,
		loadonce: false,
		caption: 'Журнал системы',
		pager: "jqGridPager-adm-journal"
	});
	jQuery('#jqGrid-adm-journal').jqGrid('filterToolbar')
                                 .navGrid('#jqGridPager-adm-journal',
            { edit: false, add: false, del: false, search: true, refresh: true, view: true, position: "left", cloneToTop: false }
    );
	
	jQuery("#jqGrid-obj-docs1").jqGrid({//------------------------------------------------------- Таблица Состава объекта-------------------------------------------
		url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=docs&oper=selaccess',
		datatype: "json",
		 colModel: [
		            { label: 'id', name: 'id_doc', width: 100, hidden: true},
		            { label: 'Регион', name: 'reg', width: 5, search: true},
		            { label: 'ПЭС', name: 'pes', width: 5, search: true},		            
					{ label: 'Тип документа', name: 'doc_type', width: 5, search: true, editrules: {required: true},
		            	searchoptions: {
							dataInit: function (element) {
					            window.setTimeout(function () {
					            	jQuery(element).autocomplete({
			                        	source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_doc_types&field=doc_type",
			                    	    maxHeight:150
			                        });
					            }, 100);
					        }
						}
					},
					{ label: 'Название', name: 'doc_name', width: 5, search: true,
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
					{ label: 'Номер', name: 'doc_number', width: 5, search: true,
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
					{ label: 'Дата', name: 'doc_date', width: 5, search: true,
						searchoptions: {
							dataInit: function (element) {
								jQuery(element).datepicker({
							        dateFormat: 'yy.mm.dd',
							        changeMonth: true,
							        changeYear: true,
							        yearRange: "1940:2040",
							        showOn: 'focus'
							    });
							}
						}
					},
					{ label: 'Контрагент', name: 'supplier', width: 5, search: true,
						searchoptions: {
							dataInit: function (element) {
					            window.setTimeout(function () {
					            	jQuery(element).autocomplete({
			                        	source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_supplier&field=supplier",
			                    	    maxHeight:150
			                        });
					            }, 100);
					        }
						}
					},
					{ label: 'От кого', name: 'name_from', width: 5, search: true,
						searchoptions: {
							dataInit: function (element) {
					            window.setTimeout(function () {
					            	jQuery(element).autocomplete({
			                        	source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_users&field=cn&field2=department&id_reg=1",
			                    	    maxHeight:150
			                        });
					            }, 100);
					        }
						}
					},
					{ label: 'Кому', name: 'name_to', width: 5, search: true,
						searchoptions: {
							dataInit: function (element) {
					            window.setTimeout(function () {
					            	jQuery(element).autocomplete({
			                        	source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_users&field=cn&field2=department&id_reg=1",
			                    	    maxHeight:150
			                        });
					            }, 100);
					        }
						}
					},
		            { label: 'Описание', name: 'description', width: 5, search: true,
						searchoptions: {
							dataInit: function (element) {
					            window.setTimeout(function () {
					            	jQuery(element).autocomplete({//-------------------------Поиск по 
					            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_docs&field=description&field2=doc_name",
					            	    maxHeight:150
					            	});	
					            }, 100);
					        }
						}
					}
         ],
		viewrecords: true, 
		sortname: 'doc_date',
		width: 1100,
		height: 250,
		rowNum: 100,
		loadonce: false,
		caption: 'Документы',
		pager: "#jqGridPager-obj-docs1"
	});
	jQuery('#jqGrid-obj-docs1').jqGrid('filterToolbar').navGrid('#jqGridPager-obj-docs1',
            { edit: true, add: true, del: true, search: true, refresh: true, view: true, position: "left", cloneToTop: false },
            {height: 'auto',width: 620, editCaption: "Редактирование",recreateForm: true,closeAfterEdit: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
            {height: 'auto',width: 620,closeAfterAdd: true,recreateForm: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
            {errorTextFormat: function (data) {return 'Error: ' + data.responseText;}
    });
	
	jQuery("#jqGrid-obj-docs1").jqGrid('gridDnD',{connectWith:'#jqGrid-obj-docs2'});
	
	jQuery("#jqGrid-obj-docs2").jqGrid({// -------------------------------------------------Таблица объектов----------------------------------------------------------------
		 data: objdocs,
		 datatype: "local",
		 editurl: 'clientArray',
		 colModel: [
		            { label: 'id', name: 'id_doc', width: 100, hidden: true},
		            { label: 'Регион', name: 'reg', width: 5},
		            { label: 'ПЭС', name: 'pes', width: 5},		            
					{ label: 'Тип документа', name: 'doc_type', width: 5},
					{ label: 'Название', name: 'doc_name', width: 5},
					{ label: 'Номер', name: 'doc_number', width: 5},
					{ label: 'Дата', name: 'doc_date', width: 5},
					{ label: 'Контрагент', name: 'supplier', width: 5},
					{ label: 'От кого', name: 'name_from', width: 5},
					{ label: 'Кому', name: 'name_to', width: 5},
		            { label: 'Описание', name: 'description', width: 5},
				],
		viewrecords: true,
		multiSort: true,
		sortname: 'doc_date',
		width: 1100,
		height: 150,
		rowNum: 20,
		loadonce: false,
		pager: "jqGridPager-obj-docs2"
	});
	jQuery('#jqGrid-obj-docs2').navGrid('jqGridPager-obj-docs2',
            { edit: false, add: false, del: true, search: false, refresh: true, view: true, position: "left", cloneToTop: false }           
    );
	
	jQuery("#jqGrid-obj-info-docs1").jqGrid({//------------------------------------------------------- Таблица Состава объекта-------------------------------------------
		url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=docs&oper=selaccess',
		datatype: "json",
		 colModel: [
		            { label: 'id', name: 'id_doc', width: 100, hidden: true},
		            { label: 'Регион', name: 'reg', width: 5, search: true},
		            { label: 'ПЭС', name: 'pes', width: 5, search: true},		            
					{ label: 'Тип документа', name: 'doc_type', width: 5, search: true, editrules: {required: true},
		            	searchoptions: {
							dataInit: function (element) {
					            window.setTimeout(function () {
					            	jQuery(element).autocomplete({
			                        	source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_doc_types&field=doc_type",
			                    	    maxHeight:150
			                        });
					            }, 100);
					        }
						}
					},
					{ label: 'Название', name: 'doc_name', width: 5, search: true,
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
					{ label: 'Номер', name: 'doc_number', width: 5, search: true,
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
					{ label: 'Дата', name: 'doc_date', width: 5, search: true,
						searchoptions: {
							dataInit: function (element) {
								jQuery(element).datepicker({
							        dateFormat: 'yy.mm.dd',
							        changeMonth: true,
							        changeYear: true,
							        yearRange: "1940:2040",
							        showOn: 'focus'
							    });
							}
						}
					},
					{ label: 'Контрагент', name: 'supplier', width: 5, search: true,
						searchoptions: {
							dataInit: function (element) {
					            window.setTimeout(function () {
					            	jQuery(element).autocomplete({
			                        	source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_supplier&field=supplier",
			                    	    maxHeight:150
			                        });
					            }, 100);
					        }
						}
					},
					{ label: 'От кого', name: 'name_from', width: 5, search: true,
						searchoptions: {
							dataInit: function (element) {
					            window.setTimeout(function () {
					            	jQuery(element).autocomplete({
			                        	source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_users&field=cn&field2=department&id_reg=1",
			                    	    maxHeight:150
			                        });
					            }, 100);
					        }
						}
					},
					{ label: 'Кому', name: 'name_to', width: 5, search: true,
						searchoptions: {
							dataInit: function (element) {
					            window.setTimeout(function () {
					            	jQuery(element).autocomplete({
			                        	source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_users&field=cn&field2=department&id_reg=1",
			                    	    maxHeight:150
			                        });
					            }, 100);
					        }
						}
					},
		            { label: 'Описание', name: 'description', width: 5, search: true,
						searchoptions: {
							dataInit: function (element) {
					            window.setTimeout(function () {
					            	jQuery(element).autocomplete({//-------------------------Поиск по 
					            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_docs&field=description&field2=doc_name",
					            	    maxHeight:150
					            	});	
					            }, 100);
					        }
						}
					}
				],
		viewrecords: true, 
		sortname: 'doc_date',
		width: 1100,
		height: 250,
		rowNum: 100,
		loadonce: false,
		caption: 'Документы',
		pager: "#jqGridPager-obj-info-docs1"
	});
	jQuery('#jqGrid-obj-info-docs1').jqGrid('filterToolbar')
	                                .navGrid('#jqGridPager-obj-info-docs1',
            { edit: true, add: true, del: true, search: true, refresh: true, view: true, position: "left", cloneToTop: false },
            {height: 'auto',width: 620, editCaption: "Редактирование",recreateForm: true,closeAfterEdit: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
            {height: 'auto',width: 620,closeAfterAdd: true,recreateForm: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
            {errorTextFormat: function (data) {return 'Error: ' + data.responseText;}
    });
	
	jQuery("#jqGrid-obj-info-docs1").jqGrid('gridDnD',{connectWith:'#jqGrid-obj-info-docs2'});
	
	jQuery("#jqGrid-obj-info-docs2").jqGrid({// -------------------------------------------------Таблица объектов----------------------------------------------------------------
		 data: objdocs,
		 datatype: "local",
		 editurl: 'clientArray',
		 colModel: [
		            { label: 'id', name: 'id_doc', width: 100, hidden: true},
		            { label: 'Регион', name: 'reg', width: 5},
		            { label: 'ПЭС', name: 'pes', width: 5},		            
					{ label: 'Тип документа', name: 'doc_type', width: 5},
					{ label: 'Название', name: 'doc_name', width: 5},
					{ label: 'Номер', name: 'doc_number', width: 5},
					{ label: 'Дата', name: 'doc_date', width: 5},
					{ label: 'Контрагент', name: 'supplier', width: 5},
					{ label: 'От кого', name: 'name_from', width: 5},
					{ label: 'Кому', name: 'name_to', width: 5},
		            { label: 'Описание', name: 'description', width: 5},
				],
		viewrecords: true,
		multiSort: true,
		sortname: 'doc_date',
		width: 1100,
		height: 150,
		rowNum: 20,
		loadonce: false,
		pager: "#jqGridPager-obj-info-docs2"
	});
	jQuery("#jqGrid-obj-info-docs2").navGrid('#jqGridPager-obj-info-docs2',
            { edit: false, add: false, del: true, search: false, refresh: true, view: true, position: "left", cloneToTop: false }           
    );
	
	jQuery("#jqGrid-print-cartridge").jqGrid({//------------------------------------------------------- Таблица картриджей-------------------------------------------
		//url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=docs&oper=selaccess',
		editurl: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw",
		datatype: "json",
		 colModel: [
		            { label: 'Производитель', name: 'vendor', width: 5, editable: true, search: true,
		            	editrules:{required:true},
		            	editoptions: {
		 		    		dataInit: function (element){
		 					    jQuery(element).autocomplete({//-------------------------Поиск по производителю
		 						    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_vendor&field=vendor",
		 						    maxHeight:150,
		 						    select: function(event, ui) {
		 						    	jQuery("#jqGrid-print-cartridge").jqGrid('setGridParam',{editurl: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=cartridge&id_vendor='+ui.item.id});
		 						    }	 
		 						});	
		 		    		}
		 		    	},
		            	searchoptions: {		            		
							dataInit: function (element) {
					            window.setTimeout(function () {
					            	jQuery(element).autocomplete({//-------------------------Поиск по 
					            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_vendor&field=vendor",
					            	    maxHeight:150
					            	});	
					            }, 100);
					        }
						}
		            },
		            { label: 'Название', name: 'cartr_name', width: 5, search: true, editable: true, editrules:{required:true},
			            searchoptions: {
							dataInit: function (element) {
					            window.setTimeout(function () {
					            	jQuery(element).autocomplete({//-------------------------Поиск по 
					            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_prn_cartridge&field=cartr_name",
					            	    maxHeight:150
					            	});	
					            }, 100);
					        }
						}
		            },  
		            { label: 'Цвет', name: 'id_color', width: 5, search: true, editable: true, editrules:{required:true}, edittype:'select', 
		            	editoptions: {
		 		    		dataInit: function (element){
		 		    			var reg = 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=selectunits&selid='+element+'&func=&select=inv_prn_color&name=color_print';
		 		    			jQuery.ajax({
		 		    		        type:'GET',
		 		    		        cache:false,
		 		    		        dataType:'html',
		 		    		        url:reg,
		 		    		        success:function (data) {
		 		    		        	jQuery(element).html(data);
		 		    		        }
		 		    		    });
		 		    			jQuery(element).attr("name", "id_color");
		 		    		}
		            	},
						searchoptions: {
							dataInit: function (element) {
					            window.setTimeout(function () {
					            	jQuery(element).autocomplete({//-------------------------Поиск по 
					            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_prn_color&field=color_print",
					            	    maxHeight:150
					            	});	
					            }, 100);
					        }
						}
					},
					{ label: 'Ресурс', name: 'resource', width: 5, search: true, editable: true, editrules:{required:true, integer: true}}
				],
		viewrecords: true, 
		sortname: 'vendor',
		width: divwidth-100,
		height: 250,
		rowNum: 100,
		subGrid: true, 
        subGridRowExpanded: showChildGridPrinter, 
		subGridOptions : {plusicon: "ui-icon-triangle-1-e",minusicon: "ui-icon-triangle-1-s",openicon: "ui-icon-arrowreturn-1-e"},
		loadonce: false,
		caption: 'Картриджи',
		pager: "jqGridPager-print-cartridge"
	});
	jQuery('#jqGrid-print-cartridge').jqGrid('filterToolbar')
                                     .navGrid('#jqGridPager-print-cartridge',
			  { edit: true, add: true, del: true, search: true, refresh: true, view: true, position: "left", cloneToTop: false },
	            {height: 'auto',width: 620,editCaption: "Редактирование",recreateForm: true,closeAfterEdit: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
	            {height: 'auto',width: 620,closeAfterAdd: true,recreateForm: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
	            {errorTextFormat: function (data) {return 'Error: ' + data.responseText;}
	});
	function showChildGridPrinter(parentRowID, parentRowKey) {///--------------------------------------Помещение------------------------------------------
        var childGridID = parentRowID + "_table";
        var childGridPagerID = parentRowID + "_pager";
      
        jQuery('#' + parentRowID).append('<table id=' + childGridID + '></table><div id=' + childGridPagerID + ' class=scroll></div>');

        jQuery("#" + childGridID).jqGrid({
            url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=carteridgeforprn&oper=selprn&id_cartridge='+parentRowKey,
            editurl: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=carteridgeforprn&id_cartridge='+parentRowKey,
            mtype: "GET",
            datatype: "json",
            page: 1,
            colModel: [
                { label: 'Производитель', name: 'vendor', width: 150},
                { label: 'Шасси', name: 'chasis', width: 150},                 
                { label: 'Модель', name: 'id_prn', width: 150, editable: true, edittype:'custom',
                	editoptions: {
                		required:true,
                		custom_element: function(value, options) {
            			  var ac = jQuery('<input type="text"/>');
              			  jQuery(ac).val(value);
            			  jQuery(ac).autocomplete({//-------------------------Поиск по 
			            		source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_prn_template&field=model&field2=chasis",
			            	    maxHeight:150,
			            	    select: function(event, ui) {
			            	    	jQuery(ac).data('id_prn', ui.item.id);
			            	    }	
			            	});
            			  return ac;
                		},
                		custom_value: function(elem){
                			return jQuery(elem).data('id_prn');
                		}
				    }
	            }
            ],
            viewrecords: false,
			loadonce: false,
            width: 700,
            height: '100%',
            pager: "#" + childGridPagerID
        });
        jQuery("#" + childGridID).navGrid("#" + childGridPagerID,
                { edit: false, add: true, del: true, search: false, refresh: false, view: false, position: "left", cloneToTop: false },
                { height: 'auto', width: 620,editCaption: "Редактирование",recreateForm: true,closeAfterEdit: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
                { height: 'auto',width: 300,closeAfterAdd: true,recreateForm: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
                {errorTextFormat: function (data) {return 'Error: ' + data.responseText;}
         }); 
	}
	
	jQuery("#jqGrid-print-printers").jqGrid({//------------------------------------------------------- Таблица шаблонов принтеров-------------------------------------
		editurl: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=prntemplate",
		datatype: "json",
		 colModel: [
            { label: 'Производитель', name: 'id_vendor', width: 150, search: true, editable: true,  edittype:'custom', formoptions: {elmsuffix:'(*)', rowpos:1, colpos:1},
            	editoptions: {
               		required: true,
            		custom_element: function(value, options) {
	        			  var ac = jQuery('<input type="text"/>');
	          			  jQuery(ac).val(value);
	        			  jQuery(ac).autocomplete({//-------------------------Поиск по 
	        				    source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_vendor&field=vendor",
			            	    maxHeight:150,
			            	    select: function(event, ui) {
			            	    	jQuery(ac).data('id_inv', ui.item.id);
			            	    	jQuery(ac).data('value_inv', ui.item.value);
			            	    }	
			              });
	        			  jQuery(ac).focusout(function(){
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
            		},
            		custom_value: function(elem){
            			return jQuery(elem).data('id_inv');            			
            		}
			    },
			    editrules:{
			    	custom: true,
			    	custom_func: function(value, colname){
			    		console.log(value);
            			if (value == null) 
            				return [false,'"Введите значение поля "'+colname+'"'];
            			else 
            				return [true,""];            			
            		}
            	}
			},
            { label: 'Шасси', name: 'chasis', width: 250, search: true, editable: true, editrules:{required:true}, formoptions: {elmsuffix:'(*)', rowpos:2, colpos:1},
            	editoptions: {
					dataInit: function (element) {
                        jQuery(element).autocomplete({
                        	source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_prn_template&field=chasis",
                    	    maxHeight:150
                        });
					}
            	}
            },                 
            { label: 'Модель', name: 'model', width: 250, search: true, editable: true, editrules:{required:true}, formoptions: {elmsuffix:'(*)', rowpos:2, colpos:2},
            	editoptions: {
					dataInit: function (element) {
                        jQuery(element).autocomplete({
                        	source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_prn_template&field=model",
                    	    maxHeight:150
                        });
					}
            	}
			},
			{ label: 'Тип', name: 'print_type', width: 150, search: true, editable: true, editrules:{required:true}, edittype:'select', formoptions: {rowpos:3, colpos:1},
            	editoptions: {
 		    		dataInit: function (element){
 		    			var reg = 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=selectunits&selid='+element+'&func=&select=inv_prn_type&name=print_type';
 		    			jQuery.ajax({
 		    		        type:'GET', cache:false, dataType:'html', url:reg,
 		    		        success:function (data) {
 		    		        	jQuery(element).html(data);
 		    		        }
 		    		    });
 		    			jQuery(element).attr("name", "id_type");
 		    		}
            	}
            },
			{ label: 'Тип печати', name: 'print_type_print', width: 200, search: true, editable: true, editrules:{required:true}, edittype:'select', formoptions: {rowpos:4, colpos:1},
            	editoptions: {
 		    		dataInit: function (element){
 		    			var reg = 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=selectunits&selid='+element+'&func=&select=inv_prn_type_print&name=print_type_print';
 		    			jQuery.ajax({
 		    		        type:'GET',
 		    		        cache:false,
 		    		        dataType:'html',
 		    		        url:reg,
 		    		        success:function (data) {
 		    		        	jQuery(element).html(data);
 		    		        }
 		    		    });
 		    			jQuery(element).attr("name", "id_type_print");
 		    		}
            	}
            },
            { label: 'Тип скан.', name: 'type_scan', width: 250, search: true, editable: true, editrules:{required:true}, edittype:'select', formoptions: {rowpos:4, colpos:2},
            	editoptions: {
 		    		dataInit: function (element){
 		    			var reg = 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=selectunits&selid='+element+'&func=&select=inv_prn_type_scan&name=type_scan';
 		    			jQuery.ajax({
 		    		        type:'GET',
 		    		        cache:false,
 		    		        dataType:'html',
 		    		        url:reg,
 		    		        success:function (data) {
 		    		        	jQuery(element).html(data);
 		    		        }
 		    		    });
 		    			jQuery(element).attr("name", "id_type_scan");
 		    		}
            	}
            },
			{ label: 'Цветной', name: 'color', width: 200, search: true, formatter: 'checkbox', editable: true, formoptions: {rowpos:5, colpos:1},
				 edittype: "custom",
                 editoptions: {
                     custom_value: getFreightElementValue,
                     custom_element: createFreightEditElement
                 }
            },
            { label: 'Формат', name: 'id_format', width: 200, search: true, editable: true, editrules:{required:true}, edittype:'select', formoptions: {rowpos:5, colpos:2},
            	editoptions: {
 		    		dataInit: function (element){
 		    			var reg = 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=selectunits&selid='+element+'&func=&select=inv_prn_format&name=format_print';
 		    			jQuery.ajax({
 		    		        type:'GET',
 		    		        cache:false,
 		    		        dataType:'html',
 		    		        url:reg,
 		    		        success:function (data) {
 		    		        	jQuery(element).html(data);
 		    		        }
 		    		    });
 		    			jQuery(element).attr("name", "id_format");
 		    		}
            	}
            },
            { label: 'Фото', name: 'photo', width: 200, search: true, formatter: 'checkbox', editable: true, formoptions: {rowpos:6, colpos:1},
				 edittype: "custom",
                editoptions: {
                    custom_value: getFreightElementValue,
                    custom_element: createFreightEditElement
                }},
			{ label: 'Двухстор.печ.', name: 'duplex_printing', width: 200, search: true, formatter: 'checkbox', editable: true, formoptions: {rowpos:6, colpos:2},
				 edittype: "custom",
                 editoptions: {
                     custom_value: getFreightElementValue,
                     custom_element: createFreightEditElement
                 }},
			{ label: 'Сетевой интерфейс', name: 'ethernet', width: 200, search: true, formatter: 'checkbox', editable: true, formoptions: {rowpos:7, colpos:1},
    				 edittype: "custom",
                     editoptions: {
                         custom_value: getFreightElementValue,
                         custom_element: createFreightEditElement
                     }},
			{ label: 'WIFI', name: 'wifi', width: 200, search: true, formatter: 'checkbox', editable: true, formoptions: {rowpos:7, colpos:2},
        				 edittype: "custom",
                         editoptions: {
                             custom_value: getFreightElementValue,
                             custom_element: createFreightEditElement
                         }},
			{ label: 'Факс', name: 'fax', width: 200, search: true, formatter: 'checkbox', editable: true, formoptions: {rowpos:8, colpos:1},
            				 edittype: "custom",
                             editoptions: {
                                 custom_value: getFreightElementValue,
                                 custom_element: createFreightEditElement
                             }},
			{ label: 'Скор. печ.', name: 'speed_print', width: 150, search: true, editable: true, editrules:{integer:true}, formoptions: {rowpos:9, colpos:1},},
			{ label: 'Скор. скан.', name: 'speed_scan', width: 100, search: true, editable: true, editrules:{integer:true}, formoptions: {rowpos:9, colpos:2},},
			{ label: 'Память', name: 'mem', width: 100, search: true, editable: true, editrules:{integer:true}, formoptions: {rowpos:10, colpos:1},},
			{ label: 'Мес. рес.', name: 'm_resource', width: 100, search: true, editable: true, editrules:{integer:true}, formoptions: {rowpos:10, colpos:2},},
			{ label: 'ОС', name: 'prn_os', width: 100, search: true, editable: true, edittype: 'textarea', formoptions: {rowpos:11, colpos:1},
            	editoptions: {
					dataInit: function (element) {
                        jQuery(element).autocomplete({
                        	source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_prn_template&field=prn_os",
                    	    maxHeight:150,
                        });
					}
            	},
            },
			{ label: 'Описание', name: 'prn_description', width: 100, search: true, editable: true, edittype: 'textarea', formoptions: {rowpos:11, colpos:2},
            	editoptions: {
					dataInit: function (element) {
                        jQuery(element).autocomplete({
                        	source: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=autocomplete&table=inv_prn_template&field=prn_description",
                    	    maxHeight:150,
                        });
					}
            	},
            },
		],
		viewrecords: true, 
		sortname: 'vendor',
		width: divwidth-100,
		height: 350,
		rowNum: 40,
		loadonce: false,
		caption: 'Принтеры',
		pager: "jqGridPager-print-printers"
	});
	jQuery('#jqGrid-print-printers').navGrid('#jqGridPager-print-printers',
		     { edit: true, add: true, del: false, search: false, refresh: true, view: true, position: "left", cloneToTop: false },
             { height: 'auto', width: 900,editCaption: "Редактирование",recreateForm: true,closeAfterEdit: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
             {height: 'auto',width: 900,closeAfterAdd: true,recreateForm: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
             {errorTextFormat: function (data) {return 'Error: ' + data.responseText;}
    });
	
	jQuery("a[href='#tabs-supplies']").click(function () {//Нажали на вкладку журнал
		select('#jqGrid-sup-units', '#jqGridPager-sup-units', 'inv_units_of_measure', 'units_of_measure', 'Еденицы измерения', 'Еденицы', 250, 400);//Инициализируем таблицу едениц измерения
		jQuery("#jqGrid-sup-type").jqGrid({//------------------------------------------------------- Типы комплектующих-------------------------------------------
			url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=suptype&oper=sel',
			editurl: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=suptype',
			datatype: "json",
			 colModel: [
				{ label: 'Название', name: 'sup_type_name', width: 150, search: true, editable: true, editrules:{required:true}},
				{ label: 'Функция', name: 'func', width: 200, search: true, editable: true},
				{ label: 'Еденицы измерения', name: 'id_units_of_measure', width: 200, search: true, editable: true, editrules:{required:true}, edittype:'select',
					editoptions: {
	 		    		dataInit: function (element){
	 		    			var reg = 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=selectunits&selid='+element+'&func=&select=inv_units_of_measure&name=units_of_measure';
	 		    			jQuery.ajax({
	 		    		        type:'GET', cache:false, dataType:'html', url:reg,
	 		    		        success:function (data) { jQuery(element).html(data);}
	 		    		    });
	 		    			jQuery(element).attr("name", "id_units_of_measure");
	 		    		}
	            	},
				},
				{ label: 'Поле1', name: 'field1_name', width: 200, search: true, editable: true},
				{ label: 'Поле2', name: 'field2_name', width: 200, search: true, editable: true},
				{ label: 'Поле3', name: 'field3_name', width: 200, search: true, editable: true},
				{ label: 'Поле4', name: 'field4_name', width: 200, search: true, editable: true},
				{ label: 'Поле5', name: 'field5_name', width: 150, search: true, editable: true},				
			],
			viewrecords: true, 
			sortname: 'sup_type_name',
			width: 1050,
			height: 350,
			rowNum: 40,
			loadonce: false,
			caption: 'Типы комлектующих',
			pager: "jqGridPager-sup-type"
		});
		jQuery('#jqGrid-sup-type').navGrid('#jqGridPager-sup-type',
			    { edit: true, add: true, del: false, search: false, refresh: true, view: true, position: "left", cloneToTop: false },
	            { height: 'auto', width: 900,editCaption: "Редактирование",recreateForm: true,closeAfterEdit: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
	            {height: 'auto',width: 900,closeAfterAdd: true,recreateForm: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
	            {errorTextFormat: function (data) {return 'Error: ' + data.responseText;}
	   });
	});	

	
	jQuery("#jqGrid-info-OCS-soft").jqGrid({//------------------------------------------------------- Таблица OCS ПО-------------------------------------------
		datatype: "json",
		 colModel: [
			{ label: 'Разработчик', name: 'PUBLISHER', width: 150, search: true},
			{ label: 'Название', name: 'NAME', width: 200, search: true},
			{ label: 'Версия', name: 'VERSION', width: 200, search: true},
			{ label: 'Каталог', name: 'FOLDER', width: 200, search: true},
			{ label: 'Коментарии', name: 'COMMENTS', width: 200, search: true},
			{ label: 'GUID', name: 'GUID', width: 200, search: true},
			{ label: 'Язык', name: 'LANGUAGE', width: 200, search: true},
			{ label: 'Дата установки', name: 'INSTALLDATE', width: 150, search: true},
			{ label: 'Разрядность', name: 'BITSWIDTH', width: 100, search: true},
		],
		viewrecords: true, 
		sortname: 'NAME',
		width: 1050,
		height: 350,
		rowNum: 40,
		loadonce: false,
		caption: 'Прогрммное обеспечение',
		pager: "jqGridPager-info-OCS-soft"
	});
	jQuery('#jqGrid-info-OCS-soft').navGrid('#jqGridPager-info-OCS-soft',
            { edit: false, add: false, del: false, search: true, refresh: true, view: true, position: "left", cloneToTop: false }
    );
	
	jQuery("#jqGrid-info-prn-history").jqGrid({//------------------------------------------------------- История печати -------------------------------------------
		datatype: "json",
		 colModel: [
		    { label: 'Принтер', name: 'printer_name', width: 150, search: true},      
			{ label: 'Время', name: 'time', width: 150, search: true},
			{ label: 'Документ', name: 'doc', width: 250, search: true},
			{ label: 'Пользователь', name: 'user', width: 200, search: true},
			{ label: 'Сервер', name: 'server', width: 200, search: true},
			{ label: 'ip адрес', name: 'printer_ip', width: 200, search: true},
			{ label: 'Размер', name: 'size', width: 200, search: true},
			{ label: 'Кол-во', name: 'count', width: 200, search: true},			
		],
		viewrecords: true, 
		sortname: 'time',
		width: 1070,
		height: 420,
		rowNum: 18,
		loadonce: false,
		caption: 'История печати',
		pager: "jqGridPager-info-prn-history"
	});
	jQuery('#jqGrid-info-prn-history').navGrid('#jqGridPager-info-prn-history',
            { edit: false, add: false, del: false, search: true, refresh: true, view: true, position: "left", cloneToTop: false }
    );
	
	jQuery("#jqGrid-stores").jqGrid({// -------------------------------------------------Таблица объектов----------------------------------------------------------------
		datatype: "json",
		 colModel: [
		    { label: 'Склад', name: 'store_name', width: 100, search: true},
		    { label: 'Название', name: 'name', width: 150, search: true},
			{ label: 'Производтель', name: 'vendor', width: 150, search: true},
			{ label: 'Тип расх.мат.', name: 'sup_type_name', width: 150, search: true},
		    { label: 'Модель', name: 'model', width: 150, search: true},
		    { label: 'Приход', name: 'sup_count', width: 50, search: true},
			{ label: 'Остаток', name: 'deb_count', width: 50, search: true},
			{ label: 'Бух.назв.', name: 'buh_name', width: 150, search: true},
			{ label: 'Дата прихода', name: 'buh_date', width: 150, search: true},
		    { label: 'Дата покупки', name: 'purchase_date', width: 100,  search: true, sorttype:'date'},
		    { label: 'Серийный №', name: 's_number', width: 100,  search: true},
		    { label: 'Инв. №1', name: 'i_number', width: 100, search: true},
			{ label: 'Инв. №2', name: 'i_number_adv', width: 100, search: true},
			{ label: 'Штрихкод', name: 'barcode', width: 100, search: true},	
			{ label: 'Поставщик', name: 'supler', width: 150, search: true},			
			{ label: 'id_sup', name: 'id_sup', width: 150, search: true, hidden: true},
		],
		viewrecords: true,
		multiSort: true,
		sortname: 'store_name',
		width: divwidth-30,
		height: 500,
		rowNum: 20,
		loadonce: false,
		shrinkToFit: false,
		subGrid: true, 
        subGridRowExpanded: showChildGridDebit, 
	    subGridOptions : {plusicon: "ui-icon-triangle-1-e",minusicon: "ui-icon-triangle-1-s",openicon: "ui-icon-arrowreturn-1-e"},
		ondblClickRow: function(rowid, selected){
			editobject(rowid);
		},
		pager: "#jqGridPager-stores"
	});
	jQuery('#jqGrid-stores').jqGrid('filterToolbar');
	jQuery('#jqGrid-stores').navGrid('#jqGridPager-stores',
            { edit: false, add: false, del: false, search: false, refresh: true, view: true, position: "left", cloneToTop: false }           
    );
	jQuery('#jqGrid-stores').navButtonAdd('#jqGridPager-stores',
            {
                buttonicon: "ui-icon-calculator",
                title: "Списание материала",
                caption: "Списать",
                position: "last",
                onClickButton: function() {
					// call the column chooser method
                	obj_find_dlg.strdiv = "#inv-dlg-sup-store";//Откуда вызвана функция списания
                	obj_find_dlg.grid = '#jqGrid-stores';//Грид для списания
                	jQuery('#inv-dlg-sup-store input').val('');
                	jQuery('#inv-dlg-sup-store #sup-count').val('1');
                	jQuery('#inv-dlg-sup-store #sup-date').val(NowDate(1));
                	jQuery(obj_find_dlg.strdiv+' #id_object').data('value_id', '');
                	jQuery(obj_find_dlg.strdiv+' #sup-user').data('value_id', '')
                	jQuery('#inv-dlg-sup-store').dialog('open');                	
				}
            }	                
	);
	jQuery('#jqGrid-stores').navButtonAdd('#jqGridPager-stores',
            {
                buttonicon: "ui-icon-calculator",
                title: "Передать на другой склад",
                caption: "Передать",
                position: "last",
                onClickButton: function() {
                	jQuery('#inv-dlg-sup-store input').val('');
                	jQuery('#inv-dlg-sup-store #sup-count').val('1');
                	jQuery('#inv-dlg-sup-store #sup-date').val(NowDate(1));
                	jQuery(obj_find_dlg.strdiv+' #id_object').data('value_id', '');
                	jQuery(obj_find_dlg.strdiv+' #sup-user').data('value_id', '')
                	jQuery('#inv-dlg-sup-store').dialog('open');                	
				}
            }	                
	);
	function showChildGridDebit(parentRowID, parentRowKey) {//.........................................Таблица списания материалов........................................
        var childGridID = parentRowID + "_table";
        var childGridPagerID = parentRowID + "_pager";
        var id_sup = jQuery("#jqGrid-stores").getCell(parentRowKey, 'id_sup');
        if (!id_sup) return false;

        jQuery('#' + parentRowID).append('<table id=' + childGridID + '></table><div id=' + childGridPagerID + ' class=scroll></div>');

        jQuery("#" + childGridID).jqGrid({
        	 url: 'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=debit&oper=sel&_search=true&id_sup='+id_sup,
        	 datatype: "json",
    		 editurl: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw",
    		 colModel: [ 
                { label: 'Кол-во', name: 'deb_count', width: 50, editable: true},
                { label: 'Дата', name: 'deb_date', width: 150, editable: true,editoptions: {
    	               dataInit: function (element) {
    	                   jQuery(element).datepicker({
    	                   	 id: 'orderDate_datePicker',
    	                        dateFormat: 'yy-mm-dd',
    	                        changeMonth: true,
    	                        changeYear: true,
    	                        yearRange: "1940:2040",
    	                        showOn: 'focus'
    	                   });
    	               }
                	}
                },
                { label: 'Пользователь', name: 'user', width: 180},
    			{ label: 'Департамент', name: 'department', width: 180},
    			{ label: 'Название', name: 'name', width: 150},
    			{ label: 'Серийный №', name: 's_number', width: 100},
    			{ label: 'Инвентарный №1', name: 'i_number', width: 100},
    			{ label: 'Инвентарный №2', name: 'i_number_adv', width: 100},
    			{ label: 'Штрихкод', name: 'barcode', width: 100},
    			{ label: 'Поставщик', name: 'supplier', width: 180},
    			{ label: 'id_obj', name: 'id_obj', width: 180, hidden: true},
    								
    		],
    		caption: 'Списание',
    		viewrecords: true,
    		sortname: 'deb_date',
    		width: divwidth-60,
    		height: 'auto',
    		rowNum: 1000,
    		loadonce: false,
    		shrinkToFit: false,
            pager: "#" + childGridPagerID
        });
        jQuery("#" + childGridID).navGrid("#" + childGridPagerID,
                { edit: true, add: true, del: true, search: false, refresh: false, view: false, position: "left", cloneToTop: false },
                { height: 'auto', width: 620,editCaption: "Редактирование",recreateForm: true,closeAfterEdit: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
                {height: 'auto',width: 600,closeAfterAdd: true,recreateForm: true,errorTextFormat: function (data) {return 'Error: ' + data.responseText;}},
                {errorTextFormat: function (data) {return 'Error: ' + data.responseText;}
         });
        jQuery("#" + childGridID).navButtonAdd("#" + childGridPagerID,
                {
                    buttonicon: "ui-icon-calculator",
                    title: "Объект списания",
                    caption: "Объект",
                    position: "last",
                    onClickButton: function() {
                    	var grid = jQuery("#"+childGridID);
                    	var id_obj = grid.getCell(grid.jqGrid('getGridParam',"selrow"), 'id_obj');
                    	if (id_obj){
                    		editobject(id_obj);
                    	}else alert ('Материал не привязан к объекту');                    	             	
    				}
                }	                
    	);
        jQuery("#" + childGridID).navButtonAdd("#" + childGridPagerID,
                {
                    buttonicon: "ui-icon-calculator",
                    title: "Акт списания",
                    caption: "Акт",
                    position: "last",
                    onClickButton: function() {
                    	var strdat;
                    	var grid = jQuery("#" + childGridID);
                    	var rowid = grid.jqGrid('getGridParam',"selrow");
                    	if (rowid){                   		
                    	
	                    	var url_data = {
	                    			department: grid.getCell(rowid, 'department'),
	            					department: grid.getCell(rowid, 'department'),
	            					table: 'inv_vendor',
	            					field: 'vendor',
	            					where: name,
	            			};	
	                    	//var myWindow= open("", "TestWindow","width=300,height=100,status=no,toolbar=no,menubar=no");
	                    	jQuery.ajax({ type:'GET', cache:false, dataType:'html', async: false,
	            		        url:'index.php?option=com_stalrams&task=getINVAjaxData&format=raw&ajaxtype=excel',
	            		        data: url_data,
	            		        success:function (data) {
	            		        	window.open(data.substring(1), '_blank');
	            		        }
	            			});                    	
                    	}
    				}
                }	                
    	);
        
	}
	
	jQuery("#jqGrid-obj-parts1").jqGrid({// -------------------------------------------------Таблица объектов----------------------------------------------------------------
		datatype: "json",
		 colModel: [
		    { label: 'id', name: 'id_obj', width: 100, hidden: true},
		    { label: 'Название', name: 'name', width: 150, search: true},
		    { label: 'Дата покупки', name: 'purchase_date', width: 100,  search: true, sorttype:'date'},
		    { label: 'Серийный №', name: 's_number', width: 100,  search: true},
		    { label: 'Инв. №1', name: 'i_number', width: 100, search: true},
			{ label: 'Инв. №2', name: 'i_number_adv', width: 100, search: true},
			{ label: 'Штрихкод', name: 'barcode', width: 100, search: true},
			{ label: 'Производтель', name: 'vendor', width: 150, search: true},	
			{ label: 'Поставщик', name: 'supplier', width: 150, search: true},
			{ label: 'Пользователь', name: 'user', width: 150, search: true}
		],
		viewrecords: true,
		multiSort: true,
		sortname: 'name',
		width: '100%',
		height: 200,
		rowNum: 20,
		loadonce: false,
		pager: "#jqGridPager-obj-parts1"
	});
	jQuery('#jqGrid-obj-parts1').jqGrid('filterToolbar');
	jQuery('#jqGrid-obj-parts1').navGrid('#jqGridPager-obj-parts1',
            { edit: false, add: false, del: false, search: false, refresh: true, view: true, position: "left", cloneToTop: false }           
    );
	
	jQuery("#jqGrid-obj-parts2").jqGrid({// -------------------------------------------------Таблица составных объектов----------------------------------------------------------------
		 data: objparts,
		 datatype: "local",
		 editurl: 'clientArray',
		 colModel: [
			{ label: 'id', name: 'id_obj', width: 100, hidden: true},
			{ label: 'Название', name: 'name', width: 150},
			{ label: 'Дата покупки', name: 'puchase_date', width: 100},
			{ label: 'Серийный №', name: 's_number', width: 100},
			{ label: 'Инвентарный №1', name: 'i_number', width: 100},
			{ label: 'Инвентарный №2', name: 'i_number_adv', width: 100},
			{ label: 'Штрихкод', name: 'barcode', width: 100},
			{ label: 'Производтель', name: 'vendor', width: 150},	
			{ label: 'Поставщик', name: 'supplier', width: 150}	
		],
		caption: 'Состав объекта',
		viewrecords: true,
		width: '100%',
		height: 160,
		rowNum: 8,
		loadonce: false,
		pager: "#jqGridPager-obj-parts2"
	});
	jQuery('#jqGrid-obj-parts2').navGrid('#jqGridPager-obj-parts2',
            { edit: false, add: false, del: true, search: false, refresh: false, view: true, position: "left", cloneToTop: false }           
    );
	
	jQuery("#jqGrid-obj-parts1").jqGrid('gridDnD',{connectWith:'#jqGrid-obj-parts2'});
	
	jQuery("#jqGrid-obj-info-parts1").jqGrid({// -------------------------------------------------Таблица объектов----------------------------------------------------------------
		datatype: "json",
		 colModel: [
		    { label: 'id', name: 'id_obj', width: 100, hidden: true},
		    { label: 'Название', name: 'name', width: 150, search: true},
		    { label: 'Дата покупки', name: 'purchase_date', width: 100,  search: true, sorttype:'date'},
		    { label: 'Серийный №', name: 's_number', width: 100,  search: true},
		    { label: 'Инв. №1', name: 'i_number', width: 100, search: true},
			{ label: 'Инв. №2', name: 'i_number_adv', width: 100, search: true},
			{ label: 'Штрихкод', name: 'barcode', width: 100, search: true},
			{ label: 'Производтель', name: 'vendor', width: 180, search: true},	
			{ label: 'Поставщик', name: 'supplier', width: 180, search: true},			
			{ label: 'Пользователь', name: 'user', width: 180, search: true},			
		],
		viewrecords: true,
		multiSort: true,
		sortname: 'name',
		width: 1150,
		height: 200,
		rowNum: 20,
		loadonce: false,
		pager: "#jqGridPager-obj-info-parts1"
	});
	jQuery('#jqGrid-obj-info-parts1').jqGrid('filterToolbar');
	jQuery('#jqGrid-obj-info-parts1').navGrid('#jqGridPager-obj-info-parts1',
            { edit: false, add: false, del: false, search: false, refresh: true, view: true, position: "left", cloneToTop: false }           
    );
	
	jQuery("#jqGrid-obj-info-parts2").jqGrid({// -------------------------------------------------Таблица составных объектов----------------------------------------------------------------
		 datatype: "json",
		 editurl: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw",
		 colModel: [
			{ label: 'id', name: 'id_obj', width: 100, hidden: true},
			{ label: 'Название', name: 'name', width: 150},
			{ label: 'Дата покупки', name: 'purchase_date', width: 100},
			{ label: 'Серийный №', name: 's_number', width: 100},
			{ label: 'Инвентарный №1', name: 'i_number', width: 100},
			{ label: 'Инвентарный №2', name: 'i_number_adv', width: 100},
			{ label: 'Штрихкод', name: 'barcode', width: 100},
			{ label: 'Производтель', name: 'vendor', width: 180},	
			{ label: 'Поставщик', name: 'supplier', width: 180},
			{ label: 'Дата добавления', name: 'date', width: 180, search: true,editable: true,editoptions: {
                dataInit: function (element) {
                    jQuery(element).datepicker({
                    	 id: 'orderDate_datePicker',
                         dateFormat: 'yy-mm-dd',
                         changeMonth: true,
                         changeYear: true,
                         yearRange: "1940:2040",
                         showOn: 'focus'
                    });
                }
            	}
			},
			{ label: 'Удален', name: 'remove', width: 180, search: true, formatter: 'checkbox', editable: true, edittype: 'checkbox'},
			{ label: 'Дата удаления', name: 'date_remove', width: 180, search: true, editable: true,editoptions: {
                dataInit: function (element) {
                    jQuery(element).datepicker({
                    	 id: 'orderDate_datePicker',
                         dateFormat: 'yy-mm-dd',
                         changeMonth: true,
                         changeYear: true,
                         yearRange: "1940:2040",
                         showOn: 'focus'
                    });
                }
			}
			},
		],
		caption: 'Состав объекта',
		viewrecords: true,
		width: 1100,
		height: 160,
		rowNum: 1000,
		loadonce: false,
		afterInsertRow: function(rowid) {
			obj.edit.parts = true;			
		},
		onSelectRow: editRow,
		pager: "#jqGridPager-obj-info-parts2"
	});
	var lastSelection;

    function editRow(id) {
        if (id && id !== lastSelection) {
            var grid = jQuery("#jqGrid-obj-info-parts2");
            grid.jqGrid('restoreRow',lastSelection);
            grid.jqGrid('editRow',id, {keys:true, focusField: 4});
            lastSelection = id;
            obj.edit.parts = true;
        }
    }
	jQuery('#jqGrid-obj-info-parts2').navGrid('#jqGridPager-obj-info-parts2',
            { edit: false, add: false, del: true, search: false, refresh: true, view: true, position: "left", cloneToTop: false }           
    );
	jQuery('#jqGrid-obj-info-parts2').inlineNav('#jqGridPager-obj-info-parts2',
             // the buttons to appear on the toolbar of the grid
             { edit: true, add: false, del: false, cancel: true, editParams: { keys: true, }, addParams: { keys: true } });
	
	jQuery("#jqGrid-obj-info-parts1").jqGrid('gridDnD',{connectWith:'#jqGrid-obj-info-parts2'});
	
	jQuery("#jqGrid-obj-find").jqGrid({// -------------------------------------------------Таблица объектов----------------------------------------------------------------
		datatype: "json",
		 colModel: [
		    { label: 'id', name: 'id_obj', width: 100, hidden: true},
		    { label: 'Название', name: 'name', width: 150, search: true},
		    { label: 'Дата покупки', name: 'purchase_date', width: 100,  search: true, sorttype:'date'},
		    { label: 'Серийный №', name: 's_number', width: 100,  search: true},
		    { label: 'Инв. №1', name: 'i_number', width: 100, search: true},
			{ label: 'Инв. №2', name: 'i_number_adv', width: 100, search: true},
			{ label: 'Штрихкод', name: 'barcode', width: 100, search: true},
			{ label: 'Производтель', name: 'vendor', width: 180, search: true},	
			{ label: 'Поставщик', name: 'supplier', width: 180, search: true},			
			{ label: 'Пользователь', name: 'user', width: 180, search: true},			
		],
		viewrecords: true,
		multiSort: true,
		sortname: 'name',
		width: 1150,
		height: 200,
		rowNum: 20,
		loadonce: false,
		pager: "#jqGridPager-obj-find"
	});
	jQuery('#jqGrid-obj-find').jqGrid('filterToolbar');
	jQuery('#jqGrid-obj-find').navGrid('#jqGridPager-obj-find',
            { edit: false, add: false, del: false, search: false, refresh: true, view: true, position: "left", cloneToTop: false }           
    );
	
	jQuery("#jqGrid-info-sup-store").jqGrid({// -------------------------------------------------Таблица списания расходных материалов----------------------------------------------------------------
		 datatype: "json",
		 editurl: "index.php?option=com_stalrams&task=getINVAjaxData&format=raw",
		 colModel: [ 
            { label: 'Количество', name: 'deb_count', width: 150, editable: true},
            { label: 'Дата', name: 'deb_date', width: 150, editable: true,editoptions: {
	               dataInit: function (element) {
	                   jQuery(element).datepicker({
	                   	 id: 'orderDate_datePicker',
	                        dateFormat: 'yy-mm-dd',
	                        changeMonth: true,
	                        changeYear: true,
	                        yearRange: "1940:2040",
	                        showOn: 'focus'
	                   });
	               }
            	}
            },
			{ label: 'Название', name: 'name', width: 150},
			{ label: 'Серийный №', name: 's_number', width: 100},
			{ label: 'Инвентарный №1', name: 'i_number', width: 100},
			{ label: 'Инвентарный №2', name: 'i_number_adv', width: 100},
			{ label: 'Штрихкод', name: 'barcode', width: 100},
			{ label: 'Производтель', name: 'vendor', width: 180},	
			{ label: 'Поставщик', name: 'supplier', width: 180},
			{ label: 'Пользователь', name: 'user', width: 180},
			{ label: 'Департамент', name: 'department', width: 180},					
		],
		caption: 'Состав объекта',
		viewrecords: true,
		width: 1100,
		height: 160,
		rowNum: 1000,
		loadonce: false,
		shrinkToFit: false,
		afterInsertRow: function(rowid) {
			obj.edit.store = true;			
		},
		onSelectRow: editRow,
		pager: "#jqGridPager-info-sup-store"
	});
	var lastSelection;

   function editRow(id) {
       if (id && id !== lastSelection) {
           var grid = jQuery("#jqGrid-info-sup-store");
           grid.jqGrid('restoreRow',lastSelection);
           grid.jqGrid('editRow',id, {keys:true, focusField: 4});
           lastSelection = id;
           obj.edit.parts = true;
       }
   }
   jQuery('#jqGrid-info-sup-store').setGroupHeaders(
           {
               useColSpanStyle: true,
               groupHeaders: [
                   { "numberOfColumns": 2, "titleText": "Склад", "startColumnName": "deb_count" },
                   { "numberOfColumns": 7, "titleText": "Объект", "startColumnName": "name" },
                   { "numberOfColumns": 2, "titleText": "Пользователь", "startColumnName": "user" }]
           });
	jQuery('#jqGrid-info-sup-store').navGrid('#jqGridPager-info-sup-store',
           { edit: false, add: false, del: true, search: false, refresh: true, view: true, position: "left", cloneToTop: false }           
   );
	jQuery('#jqGrid-info-sup-store').navButtonAdd('#jqGridPager-info-sup-store',
            {
                buttonicon: "ui-icon-calculator",
                title: "Списание материала",
                caption: "Списать",
                position: "last",
                onClickButton: function() {
					// call the column chooser method
                	jQuery('#inv-dlg-sup-store').dialog('open');
				}
            }	                
		);
	
	
	jQuery("#jqGrid-obj-info-loc-history").jqGrid({//------------------------------------------------------- История перемещения-------------------------------------------
		datatype: "json",
		 colModel: [
		    { label: 'Дата', name: 'date', width: 150},
			{ label: 'Код', name: 'code', width: 100},
			{ label: 'Регион', name: 'region', width: 200},
			{ label: 'ПЭС', name: 'pes', width: 200},
			{ label: 'Объект', name: 'po', width: 200},
            { label: 'Помещение', name: 'a', width: 200},
		],
		width: 1000,
		height: 300,
		rowNum: 1000,
		loadonce: false
	});
	

	jQuery("#jqGrid-obj-info-user-history").jqGrid({//------------------------------------------------------- История изменения пользователя-------------------------------------------
		datatype: "json",
		 colModel: [
		    { label: 'Дата', name: 'date', width: 150},
			{ label: 'Пользователь', name: 'user', width: 300},
			{ label: 'Департамент', name: 'department', width: 400}
		],
		width: 1000,
		height: 300,
		rowNum: 1000,
		loadonce: false
	});
	jQuery("#jqGrid-obj-info-resp-user-history").jqGrid({//------------------------------------------------------- История изменения пользователя-------------------------------------------
		datatype: "json",
		 colModel: [
		    { label: 'Дата', name: 'date', width: 150},
			{ label: 'Мат. ответственный', name: 'resp_user', width: 300},
			{ label: 'Департамент', name: 'department', width: 400}
		],
		width: 1000,
		height: 300,
		rowNum: 1000,
		loadonce: false
	});
	jQuery("#jqGrid-obj-info-config-history").jqGrid({//------------------------------------------------------- История изменения конфигурации-------------------------------------------
		datatype: "json",
		 colModel: [
		    { label: 'Дата', name: 'date', width: 150},
		    { label: 'Процессор', name: 'processor', width: 150},
			{ label: 'Память', name: 'mem', width: 150},
			{ label: 'Мат. плата', name: 'motherboard', width: 150},
			{ label: 'Жесткие диски', name: 'hdd', width: 150},
			{ label: 'Блок питания', name: 'psu', width: 150},
			{ label: 'Граф. карты', name: 'psu', width: 150},
			{ label: 'Сеть', name: 'graphics', width: 150, formatter: 'checkbox'},
			{ label: 'MAC адрес', name: 'mac', width: 150},
			{ label: 'Примечание', name: 'description', width: 150}
		],
		width: 1000,
		height: 300,
		rowNum: 1000,
		loadonce: false
	});
	
});