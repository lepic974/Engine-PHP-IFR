$(document).ready(function(){
	/* Drag & Drop no more Use ...

	$( "#zone_menu" ).draggable({
		handle: "div.handle",
		start: function(){
			$("#zone_menu").css('z-index',10);
			$("#zone_info_login").css('z-index',1);
		},
		stop: function() {
			var offset = $(this).offset();
			
			$.ajax({
				async : false,
				type: 'POST',
				url: "./index.php?to=ajax_common",
				data : { 
					"_ajax_action" : "save_interface",
					"div" : "zone_menu",
					"offset_x" : offset.left,
					"offset_y" : offset.top
				},
				success : function (r) {}
			});
		} 
	});
    
	$( "#zone_info_login" ).draggable({ 
		handle: "div.handle",
		start: function(){
			$("#zone_menu").css('z-index',1);
			$("#zone_info_login").css('z-index',10);		
		},
		stop: function() {
			var offset = $(this).offset();
			$.ajax({
				async : false,
				type: 'POST',
				url: "./index.php?to=ajax_common",
				data : { 
					"_ajax_action" : "save_interface",
					"div" : "zone_info_login",
					"offset_x" : offset.left,
					"offset_y" : offset.top
				},
				success : function (r) {}
			});
		} 
	});

	$( "#main_div" ).draggable({ 
		handle: "div.handle",
		stop: function() {
			var offset = $(this).offset();
			$.ajax({
				async : false,
				type: 'POST',
				url: "./index.php?to=ajax_common",
				data : { 
					"_ajax_action" : "save_interface",
					"div" : "main_div",
					"offset_x" : offset.left,
					"offset_y" : offset.top
				},
				success : function (r) {}
			});
		} 
	});	*/
	
		
});

