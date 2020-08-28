function logArray(a) {
	$.each(a,function(index, value){ 
		console.log('index : '+index);
		console.log('value : '+value);
	});
}

function reset_interface(){
	$.ajax({
		async : false,
		type: 'POST',
		url: "./index.php?to=ajax_common",
		data : { 
			"_ajax_action" : "reset_interface",
		},
		success : function (r) {window.location = 'index.php'}
	});
}

function swap_arrow(o){
	if($(o).children().attr("src") == 'pic/arrow.png'){
		$(o).children().attr("src",'pic/arrow_roll.png')
	}else{
		$(o).children().attr("src",'pic/arrow.png')
	}
}

function hide_me(o){
	$(o).fadeOut('slow');
}

function isset(tVar) {
	try	{
		var tmp = eval(tVar);
	}
	catch (e) {
		return false;
	}
	return true;
}

function strip_tags(){
	var re= /<\S[^><]*>/g
	for (i=0; i<arguments.length; i++)
	arguments[i].value=arguments[i].value.replace(re, "")
}
function nl2br(string)
{
	//return string;
	//return string.replace('\n','<br>');
	return string.split('\n').join('<br>');
}
function br2nl(string)
{
	//return string;
	return string.split('<br>').join('\n');
}
function ubtn_name(o,val){
	ubtn_form=o.parentNode;
	while(ubtn_form.nodeName!='FORM'){
		ubtn_form=ubtn_form.parentNode;
	}
		if(document.getElementById('ubtn_post_name')){
			document.getElementById('ubtn_post_name').name=o.name;
		}else{
			ubtn_input = document.createElement("input");
			ubtn_input.id="ubtn_post_name";
			ubtn_input.name=o.name;
			ubtn_input.setAttribute("type", "hidden");
			if(val){
				ubtn_input.value=val;
			}else{
				ubtn_input.value=o.name;
			}			
			ubtn_form.appendChild(ubtn_input);
		}
}
function locationRefresh(){
	document.location.href = document.location.href;
}

function closeWindow(info){
	alert(info);
}

function submitParentForm(o){	
	while(o.nodeName!='FORM'){
		o=o.parentNode;
	}	
	o.submit();
}

function onchange_legend_asterix(o){
	while(o.nodeName!='FIELDSET' && o.nodeName!='BODY'){
		o=o.parentNode;
	}
	if(o.nodeName=='FIELDSET'){
		legend=o.firstChild;
		html=legend.innerHTML;
		if(html.substring(html.length-3,html.length)!='(*)'){
			legend.innerHTML=legend.innerHTML+'(*)';
		}
	}
}

function fadein(i, id) {
	if(i <= 100){
		changeOpac(i, id);
		i=i+10;	
		setTimeout("fadein(" + i + ",'" + id + "')", 50);
	}
}

function changeOpac(opacity, id) {
	var object = document.getElementById(id).style; 
	object.opacity = (opacity / 100);
	object.MozOpacity = (opacity / 100);
	object.KhtmlOpacity = (opacity / 100);
	object.filter = "alpha(opacity=" + opacity + ")";
}

function showhide(object, visibility) // 1 visible, 0 hidden
{
    if(document.layers)	   //NN4+
    {
       document.layers[object].visibility = visibility ? "show" : "hide";
    }
    else if(document.getElementById)	  //gecko(NN6) + IE 5+
    {
        var obj = document.getElementById(object);
        obj.style.visibility = visibility ? "visible" : "hidden";
        obj.style.display= visibility ? '':'none';
    }
    else if(document.all)	// IE 4
    {
        document.all[object].style.visibility = visibility ? "visible" : "hidden";
    }

}

function showhideSwitch(object)
{
   if(document.layers)	   //NN4+
    {
        if(document.layers[object].visibility == 'hidden') visibility = 1;
        else visibility = 0;
        document.layers[object].visibility = visibility ? "show" : "hide";
    }
    else if(document.getElementById)	  //gecko(NN6) + IE 5+
    {
        var obj = document.getElementById(object);
        if (obj == null) return null;
        if(obj.style.visibility == 'hidden') visibility = 1;
        else visibility = 0;
        obj.style.visibility = visibility ? "visible" : "hidden";
        obj.style.display= visibility ? '':'none';
    }
    else if(document.all)	// IE 4
    {
    	if(document.all[object].style.visibility == 'hidden') visibility = 1;
        else visibility = 0;
        document.all[object].style.visibility = visibility ? "visible" : "hidden";
    }
	return visibility;
}


function infobul(js_text)
{
	document.getElementById("infobul").innerHTML =  js_text;
	document.getElementById("infobul").style.visibility = "visible";
	document.onmousemove = get_mouse;
}

function get_bodyHeight(){
	var myHeight = 0;
	  if( typeof( window.innerWidth ) == 'number' ) {
	    myHeight = window.innerHeight;
	  } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
	    myHeight = document.documentElement.clientHeight;
	  } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {   
	    myHeight = document.body.clientHeight;
	  }
return  myHeight;
}
function get_bodyWidth(){
	var myWidth = 0;
	  if( typeof( window.innerWidth ) == 'number' ) {
	    myWidth = window.innerWidth;
	  } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
	    myWidth = document.documentElement.clientWidth;
	  } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) { 
	    myWidth = document.body.clientWidth;
	  }
return  myWidth;
}
function get_mouse(e)
{
	var x = (navigator.appName.substring(0,3) == "Net") ? e.pageX : event.x+document.body.scrollLeft;
    var y = (navigator.appName.substring(0,3) == "Net") ? e.pageY : event.y+document.body.scrollTop;
    if(document.getElementById("infobul").offsetWidth+ x >get_bodyWidth()){
    	document.getElementById("infobul").style.left = get_bodyWidth()  - document.getElementById("infobul").offsetWidth + 'px';
    	y=y+20;
    	}
    else
    	document.getElementById("infobul").style.left = x + 20 + 'px';
    	
    if(document.getElementById("infobul").offsetHeight+y>get_bodyHeight())
    	document.getElementById("infobul").style.top  = get_bodyHeight() - document.getElementById("infobul").offsetHeight +'px';
    else
    	document.getElementById("infobul").style.top  = y - 10 +'px';
    
}
function kill_infobul()
{
    document.getElementById("infobul").style.visibility = "hidden";
}


function tab_asterisk(){
	document.getElementById("active_asterisk").innerHTML = '*';
}

function field_focus(id){
	// document.getElementById(id).focus();
}

function setFocus(id){
	if(document.getElementById(id)!=null){
		document.getElementById(id).focus();
	}else{
		if(document.getElementsByName(id)[0]!=undefined)		document.getElementsByName(id)[0].focus();
	}
}


function pop(url,mult){
	h=screen.availHeight*mult;
	w=screen.availWidth*mult;
	
	
	h=h-80;
	w=w-80;
	if(h>700) h=700;
	if(w>1000) w=1000;
	
	x=Math.round((screen.availHeight-h)/2);
	y=Math.round((screen.availWidth-w)/2);
	var uniqueid = new Date(); 
	var uniqueid = uniqueid.getTime(); 
	this_pop=window.open(url,'pop'+uniqueid, "toolbar=0,location=1,directories=0,status=0, scrollbars=1,resizable=1,menubar=0,top="+x+",left="+y+",width="+w+",height="+h);
	if(this_pop.window.focus){
		this_pop.window.focus();
	}
}

function trunc_url(url,toremove){
	trunc_pos=url.indexOf(toremove);
	if(trunc_pos>0)
		url=url.substring(0,trunc_pos);
	return url;
}

function js_confirm(id,action,msg){
	url =trunc_url(document.location.href,'&js_id');
	if(action==undefined)
		action = 'delete';
	if(msg==undefined)
		msg = "Confirmez vous la suppression ?";
	if (confirm(msg)){
		if (action==undefined){
			location.replace(url+'&js_id='+id);
		}else{			
			location.replace(url+'&js_id='+id+'&js_action='+action);
		}
	}
}

// Gestion des rollover pour les icones //
function changeiOne(obj, etat,url){
	if (etat == 'on')
		document.getElementById('icone'+obj).src = url + '.png'; 
	if (etat == 'off')
		document.getElementById('icone'+obj).src = url + '.png';
}

function icon_header(obj, path_over){
	path = 'pic/iconesiOne/';
	
	if(!path_over){	
		path_over = '_default/' + path;
	}else{
		path_over = path_over + path
	}

	fullPath = $(obj).attr('src')
    var startIndex = (fullPath.indexOf('\\') >= 0 ? fullPath.lastIndexOf('\\') : fullPath.lastIndexOf('/'));
    var filename = fullPath.substring(startIndex);
    if (filename.indexOf('\\') === 0 || filename.indexOf('/') === 0) {
            filename = filename.substring(1);
    }

	$(obj).bind('mouseover', 
		function(){
			$(this).attr('src', path_over+filename);
		}
	);
	
	$(obj).bind('mouseout', 
		function(){
			$(this).attr('src', path+filename);
		}
	);
}

function closeParentModal(){
	
}
function showSimpleModal(url,reloadPage) {
	if (typeof reloadPage != "undefined"){
		$('<iframe class="iFrameContainer"  src="'+url+'"></iframe>').modal({onClose: function (dialog) {
			  dialog.data.fadeOut('slow', function () {
				    dialog.container.slideUp('slow', function () {
				      dialog.overlay.fadeOut('slow', function () {
				        $.modal.close(); // must call this!
				        locationRefresh();
				      });
				    });
				  });
				}});
	}else{
		$('<iframe class="iFrameContainer" src="'+url+'"></iframe>').modal({onClose: function (dialog) {
			  dialog.data.fadeOut('slow', function () {
				    dialog.container.slideUp('slow', function () {
				      dialog.overlay.fadeOut('slow', function () {
				        $.modal.close(); // must call this!
				      });
				    });
				  });
				}});
	}
}

function showSimpleModalReloadParent(url,reloadPage,urlParent) {
	
	if (typeof reloadPage != "undefined"){
	
		$('<iframe class="iFrameContainer"  src="'+url+'"></iframe>').modal({
			onClose: function (dialog) {
				dialog.data.fadeOut('slow', function () {	
					dialog.container.slideUp('slow', function () {
						dialog.overlay.fadeOut('slow', function () {
							$.modal.close(); // must call this!
						});
					});
				});
				if(typeof(urlParent)=='undefined'){
					locationRefresh();
				}
				else{
					document.location.href = urlParent;
				}
			}
		});
	}else{
		$('<iframe class="iFrameContainer" src="'+url+'"></iframe>').modal();
	}
}

function showSimpleModalParam(url,width,height) {
	//$('<iframe class="iFrameContainer" style="width:'+width+'px; height:'+height+'px;"  src="'+url+'"></iframe>').modal();
	$('<iframe class="iFrameContainer" style="width:'+width+'px;" src="'+url+'"></iframe>').modal();
}

function reloadWindow(){
	window.location.reload();
}
	jQuery.fn.log = function (msg) {
  	console.log("%s", msg, this);
  	return this;
};


/* Checkbox ajax */
function ajax_checkbox(table,field,id,value,idTag) {
	$.post(
		'engine.php?to=ajax_update_checkbox',
		{ajax_wrap : 'true', nom_table : table, nom_champ : field, nom_id : id, valeur_id : value},
		function(data) {
			$('#'+idTag).html(data);
		},
		'html'
	);
}

/*function ajust_modal() { //resize height of modal
	AjustWidthModal(482);
}*/
function ajust_modal() 
//resize height of modal
{
	dout=parent.document.getElementById('modalContainer');
	din=document.getElementById('innerModal'); //must have a padding>=1px
	if (dout&&din)
	{
		ioh=din.offsetHeight;
		if (ioh>parent.innerHeight-50)
			ioh=parent.innerHeight-50;
			
		iow=din.offsetWidth;
		if (iow>parent.innerWidth-50)
			iow=parent.innerWidth-50;	

		iot='15%'; // top in %
		if (ioh+(parent.innerHeight*0.15)>parent.innerHeight){
			iot=Math.floor((parent.innerHeight-(ioh+20))/2)+'px';
		}
		
		//alert('\n\n---------------\n\n'+'out='+parent.innerHeight+'px\nin='+din.offsetHeight+'px\nresult H='+ioh+'px\nresult T='+iot);
		
		dout.style.height=ioh+'px';
		dout.style.top=iot;
		
		dout.style.left='50%';
		dout.style.marginLeft=Math.floor(-iow/2)+'px';		
		dout.style.width=iow+'px';
	}
}


function AjustWidthModal(width) {
	dout=parent.document.getElementById('modalContainer');
	din=document.getElementById('innerModal'); //must have a padding>=1px
	if (dout&&din)
	{
		ioh=din.offsetHeight+10;
		iow=din.offsetWidth;
		
		if (ioh>parent.innerHeight-50)
			ioh=parent.innerHeight-50;
		if (iow>parent.innerWidth-50)
			iow=parent.innerWidth-50;	
		
		
		//dout.style.top='50%';
		//dout.style.position ='absolute';
		//dout.style.marginTop='-'+(ioh/2)+'px';
		//dout.style.height=ioh+'px';
		
		dout.style.top='15%';	// not centered
		dout.style.marginTop='0px';
		dout.style.width=iow+'px'; //default size 482
		if(width>0) {
			dout.style.width=width+'px';
		}
		else {
			dout.style.width='482px';
		}	
		dout.style.left='50%';
	}
}

function number_fr_format(val)
{
	val=Number(String(val).replace(new RegExp(' ', 'g'),'').replace(new RegExp(',', 'g'),'.'));
	if (isNaN(val))
		return '0.00';
	ret='';
	if (val<0)
	{
		ret='-';
		val=Math.abs(val);
	}
	val=val.toFixed(2);
	str=String(val);
	di=str.length-3;
	dec=di%3;
	for(i=0;i<di;i++)
	{
		if (dec==0)
		{
			if (i!=0)
				ret+=' ';
			dec=3;
		}
		dec--;
		ret+=String(str.charAt(i));
	}
	ret+='.';
	for(j=1;j<3;j++)
		ret+=String(str.charAt(i+j));
	return ret;
}


function extractUrlParams(){	
	var t = location.search.substring(1).split('&');
	var f = [];
	for (var i=0; i<t.length; i++){
		var x = t[ i ].split('=');
		f[x[0]]=x[1];
	}
	return f;
}
function getUrlParam(param){	
	f = extractUrlParams();
	return f[param];
}

// required
// alert('<? echo ucftw('ohd_message_valid_form'); ?>');
function verif_form(form,verifClass){
	//---< Verification of different form for the RFC part >---\\
	//---< require field must have "required" class >---\\
	
	var $obj_required = $(form).find("."+verifClass);
	var verif_result = true;
	
	$obj_required.each(
		function(i){
			var is_empty = false;

			switch(this.type){
				case 'text':
				case 'textarea':
				case 'hidden':
				case 'file':
					if($(this).val() == ''){ is_empty = true; }
					break;
				case 'select-one':
					if($(this).val() == 0 || $(this).val() == null){ is_empty = true; }
					break;
				case 'select-multiple':
					if($(this).val() == null){ is_empty = true; }	
					break;
				case 'checkbox':
					if(!this.checked){is_empty = true; }
					break;
				case 'radio':
					var radio_name = $(this).attr("name");
					var radio_group = document.getElementsByName(radio_name);

					is_empty = true;
					for (j=0; j < radio_group.length; j++) {
						if(radio_group[j].checked){
							is_empty = false;
						}
					}
					break;
				default:
				break;
			}
			//---< Gestion input >---\\
			if(is_empty){				
				$(this).focus();
				verif_result = false;
			}
		}
	);
	
	return verif_result;
}


// Cette fonction permet de vérifier la validité d'une date au format jj/mm/aa ou jj/mm/aaaa
function verif_date(d) {
	if (d == "") // si la variable est vide on retourne faux
 	return false;
	
	e = new RegExp("^[0-9]{1,2}\/[0-9]{1,2}\/([0-9]{2}|[0-9]{4})$");

 	if (!e.test(d)) // On teste l'expression régulière pour valider la forme de la date
 	return false; // Si pas bon, retourne faux

 	// On sépare la date en 3 variables pour vérification, parseInt() converti du texte en entier
 	j = parseInt(d.split("/")[0], 10); // jour
 	m = parseInt(d.split("/")[1], 10); // mois
 	a = parseInt(d.split("/")[2], 10); // année

	// Si l'année n'est composée que de 2 chiffres on complète automatiquement
 	if (a < 1000) {
 		if (a < 89) a+=2000; // Si a < 89 alors on ajoute 2000 sinon on ajoute 1900
 		else a+=1900;
 	}

	// Définition du dernier jour de février
	// Année bissextile si annnée divisible par 4 et que ce n'est pas un siècle, ou bien si divisible par 400
	if (a%4 == 0 && a%100 !=0 || a%400 == 0) fev = 29;
	else fev = 28;

	// Nombre de jours pour chaque mois
	nbJours = new Array(31,fev,31,30,31,30,31,31,30,31,30,31);

	// Enfin, retourne vrai si le jour est bien entre 1 et le bon nombre de jours, idem pour les mois, sinon retourn faux
	return ( m >= 1 && m <=12 && j >= 1 && j <= nbJours[m-1] );
} 

function dbug(sObjName, windows){
	content = dump(sObjName);
	$('#dbug_javascript'+windows).remove();
	$('body').append('<div id="dbug_javascript'+windows+'" style="position:absolute; top:0px; left: 0px; background-color:#FF0000; padding:2px; z-index:999999;"><div style="position:relative;" id="dbug_header">DBUG JAVASCRIPT '+windows+'</div><div style="position:absolute; top:0px; right:0px;" onclick="$(\'#dbug_javascript'+windows+'\').remove();">FERMER</div><textarea cols="100" rows="40">'+content+'</textarea></div>');
	$('#dbug_javascript'+windows).Draggable({handle:'#dbug_header', cursor: 'crosshair'});
}

function dump (sObjName, sTab) {
  var Obj = eval(sObjName);
  //
  if (sTab==null) sTab='';
  if (typeof(Obj)!='object')
    return sTab+sObjName+': '+typeof(Obj)+' = '+Obj+'\n';
  else if (Obj.length!=null)
    var sResult = sTab+sObjName+': array length '+Obj.length+'\n';
  else
    var sResult = sTab+sObjName+': object\n';
  //
  for (sProp in Obj)
    sResult += dump (sObjName+'[\''+sProp+'\']', sTab+'  ');
  return sResult;
}

function affichage_popup(pageName, internalName, width, height){
	if(!width){ width = 400}
	if(!height){ height = 500}
	
	var w = window.open (pageName, internalName, 
	config='height=' + height + ', width=' + width + ', toolbar=no, menubar=no, scrollbars=yes, resizable=yes, location=yes, directories=no, status=no')
	w.focus(); //rend le focus a la fenetre
}

/*
@desc will simulate a form post (on itself) with a single button
@param string idx button value (as shown in php $_POST)
@comment may improve this function by taking several idx, and an optional url @see xPostForm()
*/
function xPostButton(idx){
	//build "silent" form
	html='<form method="post" action="'+document.location.href+'" id="postButtonForm"><input name="'+idx+'"/></form>';
	$('body').append(html).hide();
	//submit!
	$('#postButtonForm').submit();
}

/*
@desc TODO
@param string|JSON json single post or multiple in JSON format
@param string url optional, self by default
*/
function xPostForm(json, url){
	//build "silent" form
	//html='<form method="post" action="'+document.location.href+'" id="postButtonForm"><input name="'+idx+'"/></form>';
	$('body').append(html).hide();
	//submit!
	$('#postButtonForm').submit();
}

function load_page(url){
	window.location = url;
}