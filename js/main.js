function fileupload(rowid) {
    'use strict';

    jQuery('#fileupload').fileupload({
        dataType: 'json',
        url: '/components/com_stalrams/models/upload/server/php/index.php?id='+rowid,
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            jQuery('#progress .bar').css(
                'width',
                progress + '%'
            );
        },
        add: function (e, data) {
        	/*data.context = jQuery('<button/>').text('Загрузить выбранное')
            					.appendTo('#progress')
            					.click(function () {
            						data.context = jQuery('<p/>').text('Загрузка...').replaceAll(jQuery(this));
            						data.submit();
            });*/
           // data.context = jQuery('<p/>').text('Загрузка...').appendTo('#progress');
            data.submit(); 
        },
        done: function (e, data) {
        	if(data.result.error != undefined){
        		allert(data.result.error); // выводим на страницу сообщение об ошибке если оно есть        
            }else{
            	/*data.context.text('Загрузка закончена.');
            	jQuery.each(data.result.files, function (index, file) {
            		jQuery('<p/>').text(file.name).appendTo('#progress');
            	});*/            	
            	jQuery("#jqGrid-doc-docs_"+fileGrid+"_table").trigger("reloadGrid");
            	jQuery('#progress .bar').css('width',  0 + '%');
            }
        }
    });        
}