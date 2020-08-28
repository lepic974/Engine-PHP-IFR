tinyMCE.init({
			// General options
			mode : "textareas",
			language : "fr",
			theme : "advanced",
			skin : "o2k7",
			width: "",
			height:"",
			skin_variant : "silver",
			file_browser_callback : "tinyBrowser",
			relative_urls : true,
			theme_advanced_path : false,
			plugins : "advlink,inlinepopups,contextmenu,fullscreen,save",
			forced_root_block : false,
   			force_br_newlines : true,
   			force_p_newlines : false,
		// Theme options
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,bullist,numlist,|,sub,sup,visualchars,forecolor,backcolor,fullscreen,code",	
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,
    });