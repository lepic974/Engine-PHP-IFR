var Xlock;
var benchtime=new Date();
function bench_start(){
	benchtime=new Date();
	//document.title='start:0';
}
function bench_tick(label){
	if(!label) label='';
	var benchtime_now = new Date();
	//document.title=document.title+'/'+label+':'+( (benchtime_now.getTime()-benchtime.getTime())/1000);
}
/*
_ajax_post() allow to do an ajax call (post) with global (_post_onload, applyied on whole page) and local (_post, one shoot apply) passed values,
its usage require the following set up on each page:
-include this .js
-set any global (will be posted on each call), minimum requirement is URL for callback: _post_onload.URL='ajax.php';
-set any local (will be posted then erased), see example below.

usage example, head script section:
_post_onload.URL=ajax.php;
_post_onload.orderId=55;
usage example, body section:
<input type="button" onclick="_post.rowId=55;_ajax_post('deleteRow', this);" />
*/
var _post = new Object();
var _post_onload = new Object();
var _ajax_var = new Object();
_post_onload.URL='';
function _ajax_post(action, o){
	//url not passed as post but as ajax call destination
	
	url='';
	if(_post['URL']){ //call based
		url=_post['URL'];
	}else{ //else page based
		url=_post_onload['URL'];
	}
	
 	
 	
 	post='';
 	if(action){
		//action works like local
		post+='&_ajax_action=' + action;
	}
	//global
	for (var i in _post_onload) if(i!='URL'){	
	 	value=_post_onload[i];
	 	post+='&'+i+'='+value;
	 }
	//local
	for (var i in _post) if(i!='URL'){
	 	value=_post[i];
	 	if(typeof value == 'string'){
	 		value=value.replace(/\+/g,'%2B');
	 		//TODO find better replacement...
	 		value=value.replace(/&/g,' et '); //value=value.replace(/&/g,'%26');
	 	}
	 	post+='&'+i+'='+value;
	 }
	_post = new Object();//reset local
	Xajax(url,  post ,'Xfill', o);	
}

function _ajax_post_confirm(action, msg){
	if(msg==undefined)
		msg = "Etes-vous sûr?";
	if (confirm(msg)){
		_ajax_post(action);
	}
}
function _post(attr,val){
	_post[attr]=val;
}
function _post_node(node){
	_post['_node_id']=node.id;
	_post['_node_value']=node.value;
}

function _ajax_post_sync(action, o){
	url='';
	if(_post['URL']){
		url=_post['URL'];
	}else{
		url=_post_onload['URL'];
	}
	
 	post='';
	post+='&_ajax_action=' + action;
	for (var i in _post_onload) if(i!='URL'){	
	 	value=_post_onload[i];
	 	post+='&'+i+'='+value;
	 }
	for (var i in _post) if(i!='URL'){
	 	value=_post[i];
	 	if(typeof value == 'string'){
	 		value=value.replace(/\+/g,'%2B');
	 		value=value.replace(/&/g,' et ');
	 	}
	 	post+='&'+i+'='+value;
	 }
	_post = new Object();
	XajaxSync(url,  post ,'Xfill', o);	
}

function Xajax(url,post,xfunc,hl) {
	XajaxGeneric(url,post,xfunc,hl,true);
}

function XajaxSync(url,post,xfunc,hl) {
	XajaxGeneric(url,post,xfunc,hl,false);
}

function XajaxGeneric(url,post,xfunc,hl,is_asynchronous){
	document.body.style.cursor = "wait";
	//var d=new Date();document.title='time:'+d.getTime();


	bench_start();
	
	
	if(hl){
		//hl_undo=hl.style.backgroundColor;
		//hl.style.backgroundColor = "#dede87";
		//hl_undo= new Object();
		//hl_undo.image=$('#'+hl.id).css("background-image");
		//hl_undo.repeat=$(hl).css("background-repeat");
		//hl_undo.color=$(hl).css("background-color");
		//$(hl).css("background-image","url('ajaxwait.gif')");
		//$(hl).css("background-repeat","repeat");
		//$(hl).css("background-color", "#de8787");
			
	}
	function ajaxObject(){
		if (document.all && !window.opera) obj = new ActiveXObject("Microsoft.XMLHTTP");
		else obj = new XMLHttpRequest();
		return obj;
	}
	var ajaxHttp = ajaxObject();
	ajaxHttp.open('POST', url, is_asynchronous);
	ajaxHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');	
	//ajaxHttp.setRequestHeader('Content-Type', 'text/plain');
	ajaxHttp.onreadystatechange = 
		function(){
			if(ajaxHttp.readyState == 4){
				
				if(xfunc) {
					eval(xfunc+'(\''+escape(ajaxHttp.responseText)+'\');');
				}
					if(hl){
						//hl.style.backgroundColor = hl_undo;
						//$(hl).css("background-image", hl_undo.image);
						//$(hl).css("background-repeat", hl_undo.repeat);
						//$(hl).css("background-color", hl_undo.color);
					}
					document.body.style.cursor = "auto";
				Xlock = 0;
				bench_tick('ajax_end');
			}
		}

	ajaxHttp.send(post);
	// If synchronous, there's no readystate modification... (Laurent)
	if (!is_asynchronous) {
		if(xfunc) {
			eval(xfunc+'(\''+escape(ajaxHttp.responseText)+'\');');
		}
		if(hl){
			//hl.style.backgroundColor = hl_undo;			
		}
		document.body.style.cursor = "auto";			
		Xlock = 0;		
	}
	else {
		Xlock = 1;
	}	   
}
/*
@desc parse server side callback then do client side actions
@author: CC <christophe.cautere@nexto.fr>
@version 1.3, 11/2007
-jquery version
-added [append],[after], [prepend], [remove], [hide], [show], [wait]
@version 1.2, 11/2007
-added [location]: document redirection, if empty url, document refresh
@version 1.1, 10/2007
-added [set]: support replacing both input value or tag innerHTML, and value select
@version 1.0, 2007
-added [value], [inner], [focus], [select], [class], [altsrc]
@see to_ajax_X php functions
*/
function Xfill(html){
	html=unescape(html); //'<xfill type="start"></xfill>'+
	//alert(html);
	if(html.indexOf('XDBUG')>0 || html.indexOf("( ! )")>0 || html.indexOf(">Call Stack")>0){
		html='<fieldset style="border:5px solid red;"><legend>Debug AJAX</legend>'+html+'</fieldset>';
		$("body").prepend(html);
		/*
		if(document.getElementById('to_ajax_dbug')){
			document.getElementById('to_ajax_dbug').innerHTML=html;
		}else{
			
			alert(html);
		}
		*/
		
	}
	td=html.split('</xfill>');
	bench_tick('fill_start');
	for( i = 0; i < td.length; i++){
		//alert(td[i]);
		td[i]=td[i].replace(/^\s+/g,'').replace(/\s+$/g,''); //trim fix
		if(td[i].length>0){
			//replace innerHTML by id
			if(td[i].indexOf('<xfill type="inner">')>0){
				o=td[i].split('<xfill type="inner">');
				if(document.getElementById(o[0]))
					id=o[0].replace(/:/, "\\\\:"); // jquery colon in id escaping
					$("#"+id).html(o[1]); //new mandatory method for jquery.livequery
					//document.getElementById(o[0]).innerHTML = o[1];
			}
			//set value by id
			else if(td[i].indexOf('<xfill type="value">')>0){
				o=td[i].split('<xfill type="value">');
				if(document.getElementById(o[0]))
					document.getElementById(o[0]).value = o[1];
			}
			//if input tag: set value by id
			//if select tag: set selected value by id
			//else: replace innerHTML by id
			else if(td[i].indexOf('<xfill type="set">')>0){
				o=td[i].split('<xfill type="set">');
				if(document.getElementById(o[0])){
					switch(document.getElementById(o[0]).tagName){					
						case 'INPUT':
							document.getElementById(o[0]).value = o[1];
						break;
						case 'SELECT':
							for (var idx=0;idx<document.getElementById(o[0]).options.length;idx++) {
								if (o[1]==document.getElementById(o[0]).options[idx].value) {
									document.getElementById(o[0]).selectedIndex=idx;
									break;
								}
							}
						break;
						default:
							id=o[0].replace(/:/, "\\\\:"); // jquery colon in id escaping
							$("#"+id).html(o[1]); //new mandatory method for jquery.livequery
							//alert( $("#"+o[0]).parent().html() );
							
							//alert( $("#"+id).parent().html() );
							
							//$("#"+id).html('test');
							//document.getElementById(o[0]).innerHTML = o[1];
							//alert(id);
							//document.getElementById(o[0]).innerHTML = o[1];
						break;
					}
				}		
			}
			// set an _ajax_var property
			else if(td[i].indexOf('<xfill type="var">')>0){
				o=td[i].split('<xfill type="var">');
				_ajax_var[o[0]]=o[1];
				alert(_ajax_var[o[0]]);
			}
			// select (highlight) form element value by id
			else if(td[i].indexOf('<xfill type="select">')>0){
				o=td[i].split('<xfill type="select">');
				if(document.getElementById(o[0])){
					document.getElementById(o[0]).select();
					document.getElementById(o[0]).focus(); //scroll to element
					
				}
			}
			// focus on form element by id 
			else if(td[i].indexOf('<xfill type="focus">')>0){
				o=td[i].split('<xfill type="focus">');
				//alert(o[0]);
				if(document.getElementById(o[0]))
					document.getElementById(o[0]).focus();
			}
			// change node class by id
			else if(td[i].indexOf('<xfill type="class">')>0){
				o=td[i].split('<xfill type="class">');
				if(document.getElementById(o[0]))
					document.getElementById(o[0]).className = o[1];
			}
			else if(td[i].indexOf('<xfill type="altsrc">')>0){
				o=td[i].split('<xfill type="altsrc">');
				if(document.getElementById(o[0])){
					document.getElementById(o[0]).alt = o[1];
					document.getElementById(o[0]).src = o[1]+'.png';
				}
			}
			// document close
			else if(td[i].indexOf('<xfill type="close">')>0){
				//not working yet
			}
			// document new location, if empty, refresh location
			else if(td[i].indexOf('<xfill type="location">')>0){
				o=td[i].split('<xfill type="location">');
				if(o[1]=='') location.reload();
				else location.replace(o[1]);
			}
			// document popup
			else if(td[i].indexOf('<xfill type="popup">')>0){
				o=td[i].split('<xfill type="popup">');
				window.open(o[1]);
			}
			
			
			//DOM append to element by id
			else if(td[i].indexOf('<xfill type="append">')>0){			
				o=td[i].split('<xfill type="append">');				
				$('#'+o[0]).append(o[1]);
			}
			//DOM after to element by id
			else if(td[i].indexOf('<xfill type="after">')>0){			
				o=td[i].split('<xfill type="after">');				
				$('#'+o[0]).after(o[1]);
			}
			//DOM prepend to element by id
			else if(td[i].indexOf('<xfill type="prepend">')>0){				
				o=td[i].split('<xfill type="prepend">');				
				$('#'+o[0]).prepend(o[1]);
			}
			// show modal window
			else if(td[i].indexOf('<xfill type="modal">')>0){			
				o=td[i].split('<xfill type="modal">');		
				showSimpleModal(o[1], true);
			}
			//DOM replace
			else if(td[i].indexOf('<xfill type="html">')>0){				
				o=td[i].split('<xfill type="html">');				
				$('#'+o[0]).html(o[1]);
			}
			//DOM remove element by id
			else if(td[i].indexOf('<xfill type="remove">')>0){	
				o=td[i].split('<xfill type="remove">');			
				$('#'+o[0]).remove();
			}
			//DOM hide element by id
			else if(td[i].indexOf('<xfill type="hide">')>0){				
				o=td[i].split('<xfill type="hide">');				
				$('#'+o[0]).hide();
			}
			//DOM show element by id
			else if(td[i].indexOf('<xfill type="show">')>0){				
				o=td[i].split('<xfill type="show">');				
				$('#'+o[0]).show();
			}
			//trigger alert
			else if(td[i].indexOf('<xfill type="alert">')>0){				
				o=td[i].split('<xfill type="alert">');		
				alert(o[1]);
			}
			//show wait overlay
			else if(td[i].indexOf('<xfill type="wait">')>0){				
				//o=td[i].split('<xfill type="wait">');	
				$.blockUI('<img src="loading.gif" />');
			}
			//hide wait overlay
			else if(td[i].indexOf('<xfill type="endwait">')>0){				
				//o=td[i].split('<xfill type="endwait">');	
				$.unblockUI();
			}
			else if(td[i].indexOf('<xfill type="dbug_clean">')!=-1){
		
				$("#body_ajax_dbug > div").remove();
			}
			// eval
			else if(td[i].indexOf('<xfill type="eval">')>0){
				o=td[i].split('<xfill type="eval">');
				//showSimpleModal(o[1],true);
				eval(o[1]);
			}
			// ajax dbug
			else if(td[i].indexOf('<xfill type="dbug">')!=-1){
				o=td[i].split('<xfill type="dbug">');
				if($('#body_ajax_dbug').length==0){
					html='<fieldset id="body_ajax_dbug" style="z-index:5000;position:absolute;top:32px;right:10%;width:50%;padding:2px;background:#ff7;color:#000border:5px solid red;"><legend id="body_ajax_dbug_handle" ondblclick="$(this).parent().remove();">Debug AJAX</legend></fieldset>';
					$("body").prepend(html);
					/*
					$('#body_ajax_dbug').Draggable(
			{
				opacity : 0.5,
				handle: '#body_ajax_dbug_handle'
			})
			*/
		
				}				
				$("#body_ajax_dbug").append('<div style="margin:5px;border:1px dotted black;" ondblclick="$(this).remove();" >'+o[1]+'</div>');
			}
			
		}
	}
	//document.title=doc_title;
	bench_tick('fill_end');
}

function Xurl(url){
	location.replace(unescape(url));
}
function Xdbug(html){
	alert(unescape(html));
}

function Eajax(caller, engine_page, main_id, action, param, func, hl, is_asynchronous){
	$is_asynchronous = (is_asynchronous == undefined) || is_asynchronous;
	
	url='engine.php?to=' + engine_page;
	uri='eajax=1&ajax_id=' + main_id + '&ajax_action='+ action+ '&ajax_value='+ caller.value;
	if(param instanceof Array){ // multiple params
		for ( var n=0;n<param.length;n++ )
    {
       uri+='&ajax_'+n+'='+param[n];
    }
	}else{
		uri+='&ajax_0='+param;
	}
	XajaxGeneric( url , uri, func, hl, $is_asynchronous);
}

var edit_lock=0; // avoid ajax collision
//FOCUSTRACE>
	function trace_focus(o){
		document.getElementById("trace_focus").value = o.id;
	}
	function trace_blur(o){
		document.getElementById("trace_blur").value = o.id;
	}
//<FOCUSTRACE


function ajax_select(o,record){
	Xajax('engine.php?to=ajax_select','val='+o.value+'&record='+record,false,o);
}


function Xform(xform,func,is_asynchronous){
	if (is_asynchronous == undefined)
		is_asynchronous = true;
		//if(xform.value && xform.value.charAt(xform.value.length-1)=='*'){
		// url: ajax query post
		var url = 'xform=1';
		// xform: find form to process
		while(xform.nodeName!='FORM')	xform=xform.parentNode;
		// build url while parsing form elements
		var count = xform.elements.length;
		for (i=0; i<count; i++){
			var f = xform.elements[i];
			
			if(f.name!='void'){
				if (f.type == "text" || f.type=="textarea" || f.type=="select-one"){ 
					url+='&'+f.name+'='+ f.value.replace(/&/ , escape('&'));
					//alert(f.name+' / '+f.value);
				}
				if (f.type == "radio")
					if(f.checked)
						url+='&'+f.name+'='+f.value;
					 	
				if (f.type == "checkbox")
					if(f.checked)
						url+='&'+f.value+'=1';
					else 
					 	url+='&'+f.value+'=0';
			}
		}
		if(func==undefined) func='';
		//alert(url);
		if (is_asynchronous) {
			Xajax('engine.php?to=ajax_xform',url ,func,xform);
		}
		else {
			XajaxSync('engine.php?to=ajax_xform',url ,func,xform);
		}
	//}
}

function Xcbox(o, proc_code, proc_id) {
    Xajax('engine.php?to=ajax_xcbox', 'proc_code=' + proc_code + '&proc_id=' + proc_id + '&flag=' + o.alt ,'Xfill');
    o.src='wait.png';
}


function Xform_onchange(xform){
	// xform: find form to process
	while(xform.nodeName!='FORM')	xform=xform.parentNode;
	// build url while parsing form elements
	var count = xform.elements.length;
	for (i=0; i<count; i++){
		var f = xform.elements[i];
		if (f.name == "xform_apply"){ 
			f.value = "Appliquer *";
			//f.style.background='#FF7777';
			f.style.background='url(./pic/bblink.gif)'; 
			//f.disabled= 0;
		}	
	}
}
function Xform_onvalid(xform){
	// xform: find form to process
	while(xform.nodeName!='FORM')	xform=xform.parentNode;
	// build url while parsing form elements
	var count = xform.elements.length;
	for (i=0; i<count; i++){
		var f = xform.elements[i];
		if (f.name == "xform_apply"){ 
			f.value = "Appliquer";	
			f.style.background='url(./pic/b.gif)'; 
			//f.disabled= 1;
			}	
	}
}
function Xform_onvalid_refresh(){
	location.reload();
}



/*
* ajax functions appears in order of use
*/

/*
* called from <input> generated with func_common>build_ajax_input()
* will trigger ajax_AC_query() if minlen and after a delay.
* 
*/
//document.write('<link rel="stylesheet" type="text/css" href="css/ajax.css" />');
document.write('<span id="ajax_ac_result" class="ajax_result"></span>');
ie4=document.all
ns6=document.getElementById&&!document.all
ns4=document.layers
function ajax_place_selectbox(caller){
	
	x=caller.offsetLeft+caller.offsetWidth;
	y=15;
	if (ie4) {
		ajax_ac_result.style.pixelTop=document.body.scrollTop+y;
		ajax_ac_result.style.pixelLeft=x;
	}
	else if (ns6) {
		document.getElementById("ajax_ac_result").style.top=window.pageYOffset+y+'px';
		document.getElementById("ajax_ac_result").style.left=x+'px';
	}
	else if (ns4) {
		eval(document.ajax_ac_result.top=eval(window.pageYOffset+y));
		eval(document.ajax_ac_result.left=eval(x));
	}
	// if(ie4 || ns6 || ns4) 	ajax_place_selectbox(caller);
}



function ajax_AC(id,caller,minlen,delay)
{ 
	// @param
	if(!minlen) minlen=3;
	// @param
	if(!minlen) minlen=500; 
	if(caller.value.length>=minlen){
		ajax_place_selectbox(caller);
		if ( typeof( askTimer ) != "undefined" ) {
	  		clearTimeout(askTimer);
	  	}
	  	alt=caller.alt;
	  	askTimer = setTimeout("ajax_AC_query('"+id+"','"+caller.name+"','"+caller.value+"',alt)",delay);
  	}
}
// query php then ajax_submit_id() OR ajax_refresh_selectbox()
function ajax_AC_query(id,f_name,f_value,f_alt) {
    var xmlHttpReq = false;
    var self = this; 
    if (window.XMLHttpRequest) {self.xmlHttpReq = new XMLHttpRequest();} // Mozilla/Safari 
    else if (window.ActiveXObject) {self.xmlHttpReq = new ActiveXObject("Microsoft.XMLHTTP");} // IE 5.5+
	// @param
    self.xmlHttpReq.open('POST', 'engine.php?to=ajax_ac', true);
    self.xmlHttpReq.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    self.xmlHttpReq.onreadystatechange = function() {
    if (self.xmlHttpReq.readyState == 4) {
    	// only one result
		if(self.xmlHttpReq.responseText.charAt(0)=='!'){
			one=self.xmlHttpReq.responseText.split(',');
			ajax_submit_id(one[1],one[2]);
		}
	else{
		// update result list
			
            ajax_refresh_selectbox(self.xmlHttpReq.responseText);
         }
        }
    }
    qstr = 'ajax=1&id=' + escape(id)+ '&f_name=' + escape(f_name)+ '&find=' + escape(f_value)+ '&alt=' + escape(f_alt);  // NOTE: no '?' before querystring
    self.xmlHttpReq.send(qstr);
}

// set ajax_id value then submit.
function ajax_submit_id(id,idx){
	anode=document.getElementById('ajax_'+idx);
	document.getElementById('ajax_'+idx).value = id;
	// find the parent <form>
	while(anode.nodeName!='FORM'){
		anode=anode.parentNode;
	}
	anode.submit();
}
// change content of ajax_ac_result div
function ajax_refresh_selectbox(html){
	showhide('ajax_ac_result',1);
	document.getElementById("ajax_ac_result").innerHTML = html;
}

// appel dynamique d'un fichier (ajax) avec retour de code HTML dans le inner du idtag
function ajax_query_html(url, post, id_tag) {	
    var xmlHttpReqh= false;
    var self = this;
     	
	// apppel ajax en fonction du navigateur 
    if (window.XMLHttpRequest) { // Mozilla/Safari 
		self.xmlHttpReqh = new XMLHttpRequest();
	} else if (window.ActiveXObject) { // IE 5.5+
		self.xmlHttpReqh = new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	// @param
    self.xmlHttpReqh.open('POST', url, true);
    self.xmlHttpReqh.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    self.xmlHttpReqh.onreadystatechange = function() {
		if (self.xmlHttpReqh.readyState == 4) {						
        	document.getElementById(id_tag).innerHTML = self.xmlHttpReqh.responseText;         	
        }
    }
	self.xmlHttpReqh.send(post);
    
	return(self.xmlHttpReqh);
}


function trigPop(id,type){
//type=1: selection fournisseur
h=500;l=800;
hauteur=Math.round((screen.availHeight-h)/2);
largeur=Math.round((screen.availWidth-l)/2);
ask=window.open('pop2.php?type='+type+'&id='+id,'S�lection', "toolbar=0,location=0,directories=0,status=0, scrollbars=0,resizable=0,menubar=0,top="+hauteur+",left="+largeur+",width="+l+",height="+h);
 
  if(ask.window.focus){ask.window.focus();}
}