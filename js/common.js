function scroll_to(id){ $('html, body').stop().animate({scrollTop: $('#'+id).offset().top}, 1500,'easeInOutBack');}


function load_cgu(){
    winprops = 'height=600,width=800,resizable';
    win = window.open('index.php?to=cgu', 'cgu', winprops);
    if (parseInt(navigator.appVersion) >= 4) { win.window.focus(); }
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

function nl2br(string) {return string.split('\n').join('<br>');}
function br2nl(string) {return string.split('<br>').join('\n');}

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


function nalert(mess,type){
	$.post(
		'index2.php?to=ajax_common',
		{_ajax_action: 'load_message', message : mess, type : type},
		function(o){
			info = o.split('#');
			if(info[0]==1){
				$('#inner_message_erreur').html(info[1]);
				$('#message_erreur').fadeIn("slow");
				$('#message_erreur').oneTime("4s", function(i) {
					$(this).fadeOut("slow");
				});
			}
			if(info[0]==2){
				$('#inner_message_info').html(info[1]);
				$('#message_info').fadeIn("slow");
				$('#message_info').oneTime("4s", function(i) {
					$(this).fadeOut("slow");
				});		
			}
			if(info[0]==3){
				$('#inner_message_valid').html(info[1]);
				$('#message_valid').fadeIn("slow");
				$('#message_valid').oneTime("4s", function(i) {
					$(this).fadeOut("slow");
				});		
			}
		}
	);
	
}