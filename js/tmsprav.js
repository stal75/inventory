
function showtab(id){
	names = new Array ("tabname_1","tabname_2","tabname_3","tabname_4"); //массив id заголовков табов
	conts= new Array ("tabcontent_1","tabcontent_2","tabcontent_3","tabcontent_4"); //массив id табов
	for(i=0;i<names.length;i++) {
		document.getElementById(names[i]).className = 'nonactive';
	}
	for(i=0;i<conts.length;i++) {
		document.getElementById(conts[i]).className = 'hide';
	}
	document.getElementById('tabname_' + id).className = 'active';
	document.getElementById('tabcontent_' + id).className = 'show';
}

function base(i){//Получаем список ПО в Регионе
	var reg = 'index.php?option=com_stalrams&view=po&task=getTMAjaxData&format=raw&pes=' + document.getElementById("base"+i).value+'&ajaxtype=1';
	    	jQuery.ajax({
	            type:'GET',
	            cache:false,
	            dataType:'html',
	            url:reg,
	            data:'',
	            success:function (data) {
	            	//alert(data);
	            	document.getElementById("text"+i).innerHTML = data;
	            	//encod();
	            }
	        });
	    	jQuery("#jqGrid").jqGrid({
	    		url: 'index.php?option=com_stalrams&view=po&task=getTMAjaxData&format=raw&pes=ttl&ajaxtype=2',
	    		datatype: "json",
	    		 colModel: [
	    			{ label: 'Название', name: 'name', width: 20 },
	    			{ label: 'Номер', name: 'number', width: 10, sorttype: 'integer' },
	    		],
	    		viewrecords: true, // show the current page, data rang and total records on the toolbar
	    		width: 800,
	    		height: 200,
	    		rowNum: 30,
	    		loadonce: true, // this is just for the demo
	    		pager: "#jqGridPager"
	    	});
	    	jQuery('#jqGrid').navGrid('#jqGridPager',
	                {
	                    edit: false,
	                    add: false,
	                    del: false,
	                    search: true,
	                    refresh: true,
	                    view: true,
	                    position: "left",
	                    cloneToTop: false
	                });
}

