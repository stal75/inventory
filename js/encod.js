/*jQuery(document).ready(function () {
	jQuery('.ajaxSubmit').on('click', function () {
    	//alert('Клик!');
    	jQuery.ajax({
            type:'GET',
            cache:false,
            dataType:'html',
            url:jQuery('#ajaxForm').attr('action'),
            data:jQuery('#ajaxForm').serializeArray(),
            success:function (data) {
            	document.getElementById("encod").innerHTML += data;
                //alert(data);
            }
        });
    });
	jQuery('name="reg"').mouseenter(function() {
    	//alert('Клик!');
    	jQuery.ajax({
            type:'GET',
            cache:false,
            dataType:'html',
            url:jQuery('#ajaxForm').attr('action'),
            data:jQuery('#ajaxForm').serializeArray(),
            success:function (data) {
            	document.getElementById("bases").innerHTML += data;
                //alert(data);
            }
        });
    });
});*/


function reg(){//Получаем список ПО в Регионе
	var reg = 'index.php?option=com_stalrams&view=po&task=getAjaxData&format=raw&region=' + document.getElementById("reg").value+'&ajaxtype=1';
	    	jQuery.ajax({
	            type:'GET',
	            cache:false,
	            dataType:'html',
	            url:reg,
	            data:reg,
	            success:function (data) {
	            	document.getElementById("bases").innerHTML = data;
	            	encod();
	            }
	        });
}

function log(msg){
	var reg = 'index.php?option=com_stalrams&view=po&task=getAjaxData&format=raw&log='+ msg+'&ajaxtype=log';
	jQuery.ajax({
        type:'GET',
        cache:false,
        dataType:'html',
        url:reg,
        success:function (data) {
        	
        }
    });
}

function Appcode(){//Получаем список аппаратных на объекте
	var index = document.getElementById("objectpo").selectedIndex;
	if (index == -1) return false;
	var reg = 'index.php?option=com_stalrams&view=po&task=getAjaxData&format=raw&region=' + document.getElementById("reg").value+'&ajaxtype=3&idpo=' + document.getElementById("idpo").options[index].value;
	jQuery.ajax({
	            type:'GET',
	            cache:false,
	            dataType:'html',
	            url:reg,
	            data:reg,
	            success:function (data) {
	            	document.getElementById("app").innerHTML = data;
	            	encod();
	            }
	        });
}

function ps1() {
	
	var  reg; //reg = 'index.php?option=com_stalrams&view=po&task=getAjaxData&format=raw&region=' + document.getElementById("base").value+'&pes='+document.getElementById("ps").value+'&ajaxtype=2';
	if (document.getElementById("base").value != null){
	 var base = document.getElementById("base").value;
	 reg = 'index.php?option=com_stalrams&view=po&task=getAjaxData&format=raw&region=' + document.getElementById("reg").value+'&ajaxtype=2&pes='+ document.getElementById("ps").value+'&base='+base;
	    	jQuery.ajax({
	            type:'GET',
	            cache:false,
	            dataType:'html',
	            url:reg,
	            data:'',
	            success:function (data) {
	            	document.getElementById("ObjectPO").innerHTML = data;
	            	Appcode();
	            }
	        });
	};
	
}


function zabb(){//Функция обращения к zabbix
	document.getElementById("msg1").innerHTML = '';
	var reg = 'index.php?option=com_stalrams&view=po&task=getAjaxData&format=raw&encod=' + document.getElementById("encod").value+'&region='+document.getElementById("reg").value+'&ajaxtype=4';
	    	jQuery.ajax({
	            type:'GET',
	            cache:false,
	            dataType:'html',
	            url:reg,
	            data:reg,
	            success:function (data) {
	            	document.getElementById("msg1").innerHTML = data;
	            }
	        });
}

function encod() {	
	

	var base;//ПО в регионе
	var ps = document.getElementById("ps").value;//Тип объекта в ПО
	var objectpo; //Объект в ПО (РЭС, ПС)
	var index = document.getElementById("tipe").selectedIndex;
	if (index == -1) return false;

	base = document.getElementById("base").value;//Определяем код производственного отделения
	    var appar =  document.getElementById("appar").value;
	    var kom1 =  document.getElementById("kom").value;
	    var pom1 =  document.getElementById("pom").value;
	    var tipe = document.getElementById("tipe").value;
	    var stoika = document.getElementById("stoika").value;
	    var obnom = document.getElementById("obnom").value;
	    objectpo = document.getElementById("objectpo").value;
	    document.getElementById("ponum").innerHTML = jQuery("select[id=base] option").size();
	    document.getElementById("objectnum").innerHTML = jQuery("select[id=objectpo] option").size();
	    document.getElementById("encod").value ='';
 	if (base != ''){
		document.getElementById("encod").value += base;
	    document.getElementById("encod").value += '-';
	}
 	if (ps != ''){
	    document.getElementById("encod").value += ps;
	    if (objectpo<10) document.getElementById("encod").value += '00';
	    if (objectpo<100 && objectpo>10) document.getElementById("encod").value += '0';
	    document.getElementById("encod").value += objectpo;
	    document.getElementById("encod").value += '-';
 	}
 	
 	if (document.getElementById("appar1").checked){
 		document.getElementById("encod").value += document.getElementById("appar1").value;
 	 	document.getElementById("encod").value += appar;
 	}
 	if (document.getElementById("appar2").checked){
 		document.getElementById("encod").value += document.getElementById("appar2").value;
 		if (kom1<10) document.getElementById("encod").value += '00';
	    if (kom1<100 && kom1>10) document.getElementById("encod").value += '0';
 		document.getElementById("encod").value += kom1;
 	}
 	if (document.getElementById("appar3").checked){
 		document.getElementById("encod").value += document.getElementById("appar3").value;
 	 	document.getElementById("encod").value += pom1;
 	}
 	if (document.getElementById("appar4").checked){
 	 	document.getElementById("encod").value += 0;
 	}
  	document.getElementById("encod").value += '-';
	if (stoika != ''){
		if (stoika<10) document.getElementById("encod").value += '0';
	    document.getElementById("encod").value += stoika;
	    document.getElementById("encod").value += '-';
 	}
  	document.getElementById("encod").value += tipe;
  	if (obnom<10) document.getElementById("encod").value += '0';
  	document.getElementById("encod").value += obnom;
  	
  	if (document.getElementById("cat").options[index].text){
  		if ((document.getElementById("base").value != '') && (document.getElementById("cat").options[index].text != '')){
  			document.getElementById("category").innerHTML = document.getElementById("base").value+'-'+document.getElementById("cat").options[index].text;
  			document.getElementById("typeimg").innerHTML = "<img src='/images/com_stalrams/"+document.getElementById("cat").options[index].value+"'>";
  		}
  	}
  	if ((document.getElementById("base").value == '') || //Если не выбрано ПО
  		(document.getElementById("objectpo").value == '') || //Или не выбран тип объекта
  		(document.getElementById("tipe").value == '') || //Или не выбран вид оборудования
  		((document.getElementById("appar1").checked == false) && (document.getElementById("appar2").checked == false) && (document.getElementById("appar3").checked == false) && (document.getElementById("appar4").checked == false)) ||//Если не выбрана аппаратная и не выбрана комната и не выбран этаж и не отмечено отсутствие объекта 
  		((document.getElementById("appar2").checked == true) && (document.getElementById("kom").value == '')) || // или выбран коридор, но не выбран этаж
  		((document.getElementById("appar1").checked == true) && (document.getElementById("appar").value == ''))) {//или выбрана аппаратная, но не выбрана сама аппаратная
		document.getElementById("msg").innerHTML = "<font color='#FF0000'>Кодирование не завершено!";// тогда пишем, что кодирование не завершено
		return false;
	} else{
		document.getElementById("msg").innerHTML = "<font color='#0000ff'>Объект закодирован!";
		log('Закодирован объект: '+ jQuery("#encod").val());
		zabb();
	};
  	
}
function typecl() {
	
	encod();
}

function base1() {

	ps1();
	Appcode();
}

function obbase(i) {

	document.getElementById("obobj").innerHTML = '';
	
	var encod;
	var base;//ПО в регионе
	var ps = document.getElementById("ps").value;//Тип объекта в ПО
	var objectpo; //Объект в ПО (РЭС, ПС)
	var index = document.getElementById("tipe").selectedIndex;
	

	base = document.getElementById("base").value;//Определяем код производственного отделения
if (i != 4){
	var appar =  document.getElementById("appar").value;
	    var kom1 =  document.getElementById("kom").value;
	    var pom1 =  document.getElementById("pom").value;
	    var tipe = document.getElementById("tipe").value;
	    var stoika = document.getElementById("stoika").value;
	    var obnom = document.getElementById("obnom").value;
	    objectpo = document.getElementById("objectpo").value;
	    document.getElementById("ponum").innerHTML = jQuery("select[id=base] option").size();
	    document.getElementById("objectnum").innerHTML = jQuery("select[id=objectpo] option").size();
	    encod ='';
 	if (base != ''){
		encod += base;
	    encod += '-';
	}else return 0;
 	if ((ps != '')){
	    encod += ps;
	    if (objectpo<10) encod += '00';
	    if (objectpo<100 && objectpo>10) encod += '0';
	    encod += objectpo;
	    encod += '-';
 	}else return 0;
 	switch (i){
 		case 1:
 			break;
 		case 2:
 			if (document.getElementById("appar1").checked){
 				encod += document.getElementById("appar1").value;
 				encod += appar;
 			}
 			if (document.getElementById("appar2").checked){
 				encod += document.getElementById("appar2").value;
 				if (kom1<10) encod += '00';
 				if (kom1<100 && kom1>10) encod += '0';
 				encod += kom1;
 			}
 			if (document.getElementById("appar3").checked){
 				encod += document.getElementById("appar3").value;
 				encod += pom1;
 			}
 			if (document.getElementById("appar4").checked){
 				encod += 0;
 			}
 			encod += '-';
 			break;
 		case 3:
 			if (document.getElementById("appar1").checked){
 				encod += document.getElementById("appar1").value;
 				encod += appar;
 			}
 			if (document.getElementById("appar2").checked){
 				encod += document.getElementById("appar2").value;
 				if (kom1<10) encod += '00';
 				if (kom1<100 && kom1>10) encod += '0';
 				encod += kom1;
 			}
 			if (document.getElementById("appar3").checked){
 				encod += document.getElementById("appar3").value;
 				encod += pom1;
 			}
 			if (document.getElementById("appar4").checked){
 				encod += 0;
 			}
 			encod += '-';
 			if (stoika != ''){
 				if (stoika<10) encod += '0';
 			    encod += stoika;
 			    encod += '-';
 		 	}
 		  	encod += tipe;
  			break;
 		case 4:
 			break;	
  			
 		}
}else encod = document.getElementById("neqname").value;
  	
  	//alert('Функционал не реализован - '+encod);
  	
  	document.getElementById("obobj").innerHTML = '';
	var reg = 'index.php?option=com_stalrams&view=po&task=getAjaxData&format=raw&object=' + encod +'&region='+document.getElementById("reg").value+'&ajaxtype=5';
	    	jQuery.ajax({
	            type:'GET',
	            cache:false,
	            dataType:'html',
	            url:reg,
	            data:reg,
	            success:function (data) {
	            	document.getElementById("neqname").value = encod;
	            	document.getElementById("obobj").innerHTML = data;
	            }
	        });

}

jQuery(function() {
	jQuery( "button" )
      .button()
      .click(function( event ) {
        event.preventDefault();
      });
  });

jQuery(document).ready(function(){
		
if (checkFlash()){
	var client2 = new ZeroClipboard(jQuery("#target-to-copy"), {
	  moviePath: "/components/com_stalrams/js/ZeroClipboard.swf"
	});
	//alert (client2.moviePath);

	client2.on("load", function(client2) {  
	  client2.on("complete", function(client2, args) {
		  
	  });
	});
}else{
	jQuery('#target-to-copy').hide();
}
});

function checkFlash() {
	var flashinstalled = false;
	if (navigator.plugins) {
		if (navigator.plugins["Shockwave Flash"]) {
			flashinstalled = true;
		}
		else if (navigator.plugins["Shockwave Flash 2.0"]) {
			flashinstalled = true;
		}
	}
	else if (navigator.mimeTypes) {
		var x = navigator.mimeTypes['application/x-shockwave-flash'];
		if (x && x.enabledPlugin) {
			flashinstalled = true;
		}
	}
	else {
		// на всякий случай возвращаем true в случае некоторых экзотических браузеров
		flashinstalled = true;
	}
	return flashinstalled;
}

 