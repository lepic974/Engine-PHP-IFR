/*
@author CC
@version v1.0 01/2008
@version v1.1 07/2008 ajax support
*/
$.fn.xGrid = function(settings) {
	//no xGrid in DOM 
	if(!$(this).length) return this;
	/*
	xgrid root container:
	all behaviours or DOM updates are made within this container only
	*/
	var xgrid=this;
	//alert( $(this).html() );
	/*
	xgrid result table body container:
	changing part of xgrid.
	*/
	var xgridTbody=$(xgrid).find('tbody:first');
	/*
	xgrid json hidden container:
	this act as a parameter layer over the default xgrid init, it can be read and writen by both client and server side.
	for persistence and refresh purpose each xgrid json container is stored in server php session
	*/
	var xgridHidden=$(this).find(".xgridJSON:first");
	
	var xgridROHidden=$(this).find(".xgridROJSON:first"); 
	var alias; //@see this::initAlias()
	var aliasName; //@see this::initAlias()
	/*	
	xgrid JSON object:
	this object is first loaded into js from json container (@see loadJSON)
	then it can be updated by many xgrid item behaviours,
	eventually it is saved back to json container. (@see saveJSON)
	after taht, json can be sent again to php. (@see postJSON)
	*/
	var	xgridJSON;
	var	xgridROJSON; 
	loadJSON(); //load once, update many, save many.
	
	enableAjaxPost();

	//HTML>
		//default template (js client side)
		html=[];
		html['xgrid_apply']='<input class="xgrid_apply" type="submit" value="Appliquer">';
		html['xgrid_filter_input']='<input class="xgrid_filter_input" style="width:50px;">';	
		html['xgrid_filter_on']='<input class="xgrid_filter_on" type="button" value="+">';
		html['xgrid_filter_off']='<input class="xgrid_filter_off" type="button" value="-">';
		html['xgrid_filter_yes']='<input class="xgrid_filter_yes" type="button" value="1">';
		html['xgrid_filter_no']='<input class="xgrid_filter_no" type="button" value="0">';
		html['xgrid_filter_radio']='<div></div>';
		html['xgrid_filter_radio_yes']='<span><input class="xgrid_filter_radio_yes" type="radio" value="0"> Non</span>';
		html['xgrid_filter_radio_off']='<span><input class="xgrid_filter_radio_off" type="radio" value="0"> Indifferent</span>';
		html['xgrid_edit_profile']='<input class="xgrid_edit_profile" type="button" value="Editer">';
		html['xgrid_show_profile_default_table']='<input class="xgrid_show_profile_default_table" type="button" value="+">';
		
		html['radio_no']='<span><input class="xgrid_bool_filter_no" type="radio"> Non</span>';
		html['radio_yes']='<span><input class="xgrid_bool_filter_yes" type="radio"> Oui</span>';
		html['radio_all']='<span><input class="xgrid_bool_filter_all" type="radio"> Tout</span>';
		
		//html['xgrid_bool_yes']='<span class="xgrid_bool_yes">oui</span>';
		//html['xgrid_bool_no']='<span class="xgrid_bool_no">non</span>';
		html['xgrid_filter_choose']='<input class="xgrid_filter_choose" type="button" value="?">';
		html['xgrid_filter_date_input']='<input class="xgrid_filter_date_input" style="width:70px;" value="">';
		html['xgrid_filter_date_select']='<input class="xgrid_filter_date_select" type="button" value="?">';
		
		//get template (server side generated)
		xgridHTMLJSON=eval('(' +   $(this).find(".xgrid_HTML_JSON").val()   + ')');
		$.each( xgridHTMLJSON, function(i, n){
			html[i]=n;
		});		
		//remove template
		$(xgridHTMLJSON).remove();
		
		
		
	//<HTML
	
	//sortable header must looks clickable
	$(this).find('.xgrid_sort').css('cursor','pointer');	
	//EXPORT||PRINT>
		//export button via JSON temp section
		$.fn.exportOrPrint = function(action) {	
			var xgrid_export=this;

			$(xgrid_export).find('.xgrid_submit_button').click(function(){
				
				xgridJSON._temp={}; // ._temp json will be server side deleted after processing
				
				/*
				pdf cols width will try to look like onscreen width
				header row will be use to populate aliasWidth
				*/
				if(action == 'print'){
					trH1=$(xgrid).find('.trH1:first'); // header row
					xgridJSON._temp.aliasWidth={}; // alias col width
					$(trH1).find('.xgrid_alias').each(function(){
							xgridJSON._temp.aliasWidth[$(this).attr('rel')]=$(this).width();
					});
				}

				// client side col selection
				xgridJSON._temp.alias=[];
				$(xgrid_export).find('input:checkbox:checked').each(
					function(i) {
						alias=$(this).val()
						xgridJSON._temp.alias[i]=alias;						
					}
				);
				
				// empty selection
				if(xgridJSON._temp.alias.length==0){
					alert('ha ha ...');
					return this;
				}
				
				//all rows flag
				if($(xgrid_export).find('input:radio:checked').val() == 1 ) xgridJSON._temp.allrows=true;
				toPhp(xgridJSON, 'do', $(this).attr('rel')); // export_xls | export_csv | print_pdf | print_html
				/*
				// print or export		
				if(action == 'export'){
					setTemp(xgridJSON, $(this).attr('rel'));
					setTemp(xgridJSON, ');
					xgridJSON._temp.exportFormat='CSV';  // Pour IE trouver un autre nom que export car reserv�.
				} else {	
					setTemp(xgridJSON, ');								
					xgridJSON._temp.printFormat='PDF';
				}
				*/
				//update json so that xGRid php class knows we want to print|export
				saveJSON();
				
				//php header has a file doc type with direct download, true post must be done (ajax temporary disabled)
				disableAjaxPost();
				submitJSON();
				enableAjaxPost();
				//as no refresh was done, we clear print|export query and go ahead as if no post was done
				delete xgridJSON._temp;
				//update json
				saveJSON();
			});
		
			return this;			
		};
		$(this).find('.xgrid_export').exportOrPrint('export');
		$(this).find('.xgrid_print').exportOrPrint('print');
	//<EXPORT||PRINT
	//SWITCH>
		
		$.fn.switchTab = function() {
			$(this).find('input').switchAlias();
		};
		$.fn.switchAlias = function() {
			$(this).click(function(){
				initAlias(this);
				if(!xgridJSON[aliasName]) xgridJSON[aliasName]={};
				if($(this).is(':checked')){
					//force show
					xgridJSON[aliasName].switch_show=1;			
					//disable force hide		
					delete xgridJSON[aliasName].switch_hide;
				}else{
					//force hide
					xgridJSON[aliasName].switch_hide=1;
					//disable force show
					delete xgridJSON[aliasName].switch_show;
					//disable filter
					delete xgridJSON[aliasName].filter;
				}
				saveJSON();
			});
		};	
		$(this).find('.xgrid_switch').switchTab();
		/*
		
				$.fn.switchAliasNow = function() {
			$(this).click(function(){
	            var table = this.parentNode.parentNode;
	            var columnNumber = this.cellIndex;
	            alert(columnNumber);
	            var isShowing = (table.rows[0].cells[columnNumber].style.visibility == 'visible');
	
	            var rows = table.tBodies[0].rows;
	            for (var rowLoop=0; rowLoop<rows.length; rowLoop++) {
	                rows[rowLoop].cells[columnNumber].style.visibility = isShowing ? 'hidden' : 'visible';
	            }
				initAlias(this);
				if(!xgridJSON[aliasName]) xgridJSON[aliasName]={};
				//force hide
				xgridJSON[aliasName].switch_hide=1;
				//disable force show
				delete xgridJSON[aliasName].switch_show;
				//disable filter
				delete xgridJSON[aliasName].filter;
				saveJSON();
			});
			
			
		};
		$(xgrid).find('.xgrid_switch_off').switchAliasNow();	
		*/	
	//<SWITCH
	
		//build functions for filter, need use standard function for context
		function buildFilterFunctions()
		{
			$.fn.filterInput = function(applyAndPost) { //update json
				if(applyAndPost){
					$(this).css('background','#ccffcc');
				}
				$(this).click(function(){$(this).select();});
				$(this).bind("keyup", function(){
					$(xgrid).stopTime();
					//update json filter
					aliasName=$(this).parents('.xgrid_alias:first').attr('rel') ;
					if($(this).attr('rel')){
						//multiple filter on same alias, stored as object
						filterIndex=$(this).attr('rel');
						//xgridJSON[aliasName].filter[$(this).attr('rel')]=$(this).val();
						xgridJSON[aliasName]['filter'][filterIndex]=$(this).val();
					}else{
						//single filter for this alias, stored as string
						xgridJSON[aliasName].filter=$(this).val();
					}
					
					xgridJSON._lastinput=aliasName;
					xgridJSON._lastinput_value=$(this).val();
					if(applyAndPost){
						xgridJSON._autocomplete=aliasName;
						xgridJSON._autocomplete_value=$(this).val();
					}
					saveJSON();
					if(applyAndPost){
						if(xgridJSON._autocomplete_value==''){
							// console log only for FIREBUG Firefox pluging
							if(!$.browser.msie) console.log("empty");
						}else{
							$(xgrid).oneTime("1s", function() {							
									postJSON();
								//console.log("post:"+xgridJSON._autocomplete_value);
								//
							});
						}
					
					}
				});
				
				//TODO bind multiple event
				$(this).bind("blur", function(){
					aliasName=$(this).parents('.xgrid_alias:first').attr('rel') ;
					if($(this).attr('rel')){
						filterIndex=$(this).attr('rel');
						xgridJSON[aliasName]['filter'][filterIndex]=$(this).val();
					}else{
						xgridJSON[aliasName].filter=$(this).val();
					}
					saveJSON();
				});
				return this;			
			};
		}
		function filterOff(xgridFilterOff, applyAndPost){ //hide filter, remove json
			delete xgridJSON._autocomplete;
			delete xgridJSON._autocomplete_value;
			//remove filter_input
			//$(xgridFilterOff).prevAll().remove();
			//displayApply($(xgridFilterOff));
			//add filter_on button
			//$(xgridFilterOff).before(html['xgrid_filter_on']).prev().click(function(){filterOn(this);});
			//remove json filter
			
			aliasName=getAlias($(xgridFilterOff));
			delete xgridJSON[aliasName].filter;
			saveJSON();
			//remove filter_off button
			$(xgridFilterOff).parent().remove();
			if(applyAndPost) postJSON();
		}

		
	//PROFILE>	
		//tabs visibility
		$.fn.profile = function() {
			$('.xgrid_edit_profile').hide();
			$(this).change(function(){
				xgridJSON._temp={};
				xgridJSON._temp.profile=$(this).val();
				//update json
				saveJSON();
				if($(this).val() > 0){
					$(this).parent().find('.xgrid_edit_profile').show();
				}else{
					$(this).parent().find('.xgrid_edit_profile').hide();
				}
				$(this).parent().find('.xgrid_edit_profile_form').remove();
			});
			return this;			
		};
		$.fn.addProfile = function() {		
			$(this).click(function(){
				msg = "Sauver les filtres courants sous le nom de profil suivant:";
				def = "profil";
				ask=prompt(msg,def)
				if (ask){
					xgridJSON._temp={};
					xgridJSON._temp.addProfile=ask;
					//update json
					saveJSON();
					submitJSON();
				}
				
			});
			return this;			
		};
		$.fn.removeProfile = function() {		
			$(this).click(function(){
				//only user profile can be deleted
				profileNum=$(this).parent().find('.xgrid_select_profile').val();
				if(profileNum>0){
					msg = "Supprimer le profil selectionné?";
					if (confirm(msg)){
						xgridJSON._temp={};
						xgridJSON._temp.removeProfile=profileNum;
						//update json
						saveJSON();
						submitJSON();
					}
				}else{
					alert('Selectionnez au préalable un profil utilisateur à supprimer.');
				}
			});
			return this;			
		};
		
		$.fn.editProfile = function() {
			$(this).click(function(){
				if($(this).parent().find('.xgrid_select_profile').next().next().hasClass('xgrid_edit_profile_form')){
					$(this).parent().find('.xgrid_edit_profile_form').remove();
				}else{
					var defaultProfileEditable = false;
					var width = 230;
					if($(this).parent().find('.xgrid_select_profile option:selected').attr('alt') != ''){
						defaultProfileEditable = true;
						var width = 310;
					}
					var padding = $(this).parent().find('.xgrid_add_profile').position().left - $(xgrid).position().left;
					var html_profile = '<div class="xgrid_edit_profile_form" style="width:'+width+'px;margin:10px 0px 5px '+padding+'px;padding:5px;border:1px solid grey;">';
					checked = '';
					if($(this).parent().find('.xgrid_select_profile option:selected').attr('alt') != 0) {
						checked = 'checked="checked"';
					}
					if(defaultProfileEditable){
						html_profile+= '<input type="checkbox" class="xgrid_radio_profile_default" '+checked+'>&nbsp;Par défaut&nbsp;&nbsp;';
					}
					html_profile+= '<input type="checkbox" class="xgrid_radio_profile_delete">&nbsp;Supprimer';
					html_profile+= '&nbsp;&nbsp;<input type="checkbox" class="xgrid_radio_profile_replace">&nbsp;Remplacer';
					html_profile+= '&nbsp;&nbsp;<span class="xgrid_profile_apply_button"></span>';
					html_profile+= '</div>';
					$(this).parent().find('.xgrid_select_profile').next().after(html_profile);
					$(this).parent().find('.xgrid_apply:first').clone().removeClass('xgrid_apply').addClass('xgrid_save_profile').prependTo($(this).parent().find('.xgrid_profile_apply_button'));
					$(this).parent().find('.xgrid_save_profile').saveProfile(xgrid);
					if(defaultProfileEditable){
						$(this).parent().find('.xgrid_radio_profile_default').radioProfile();
					}
					$(this).parent().find('.xgrid_radio_profile_delete').radioProfile();
					$(this).parent().find('.xgrid_radio_profile_replace').radioProfile();
				}
			});
			return this;
		};
		
		$.fn.radioProfile = function() {
			$(this).click(function(){
				if($(this).hasClass('xgrid_radio_profile_delete')){
					$(this).parent().find('.xgrid_radio_profile_default').attr('checked',false);
					$(this).parent().find('.xgrid_radio_profile_replace').attr('checked',false);
				}else if($(this).hasClass('xgrid_radio_profile_default')){
					$(this).parent().find('.xgrid_radio_profile_delete').attr('checked',false);
				}else if($(this).hasClass('xgrid_radio_profile_replace')){
					$(this).parent().find('.xgrid_radio_profile_delete').attr('checked',false);
				}
			});
			return this;
		};
		
		$.fn.saveProfile = function(xgrid) {
		//function saveProfile() {
			$(this).click(function(){
				xgridHidden=$(xgrid).find(".xgridJSON:first");
				loadJSON();
				profileNum=$(xgrid).find('.xgrid_select_profile').val();
				if(profileNum>0){
					xgridJSON._temp={};
					if($(xgrid).find('.xgrid_radio_profile_delete:checked').length){
						// deleting profile
						msg = "Supprimer le profil selectionné?";
						if (confirm(msg)){
							xgridJSON._temp.removeProfile=profileNum;
							//update json
							saveJSON();
							submitJSON();
						}
					}else if($(xgrid).find('.xgrid_radio_profile_replace:checked').length){
						// updating profile
						msg = "Remplacer le profil selectionné?";
						if (confirm(msg)){							
							if($(xgrid).find('.xgrid_radio_profile_default:checked').length){
								xgridJSON._temp.replaceProfileDefault=profileNum;
							}else{
								xgridJSON._temp.replaceProfile=profileNum;
							}
							//update json
							saveJSON();
							submitJSON();
						}
					}else if($(xgrid).find('.xgrid_radio_profile_default:checked').length){
						// default profile
						msg = "Définir le profil selectionné comme profil par défaut?";
						ask=confirm(msg)
						if (ask){
							xgridJSON._temp.saveProfileDefault=profileNum;
							xgridJSON._temp.saveProfileDefaultValue=1;
							//update json
							saveJSON();
							submitJSON();
						}
					}else{
						// no default profile
						xgridJSON._temp.saveProfileDefault=profileNum;
						xgridJSON._temp.saveProfileDefaultValue=0;
						//update json
						saveJSON();
						submitJSON();
					}
				}
			});
			return this;
		};
		
		$(this).find('.xgrid_select_profile').profile();
		$(this).find('.xgrid_add_profile').addProfile();
		$(this).find('.xgrid_remove_profile').removeProfile();
		$(this).find('.xgrid_edit_profile').editProfile();
		
	//<PROFILE
	//TOOGLE>
		$.fn.tabFilterToggle = function (){
			$(this).click(function(){
				$(this).parent().find('.xgrid_filter_toggled').toggle();
				
			});
			return this;
		}
		$(this).find('.xgrid_filter_toggle').tabFilterToggle();
	//<TOOGLE	
	//CLICKED>
		//visually (highlight) keep track of clicked row(s), ctrl key must be used for multiple highlight
		$.fn.clickFlag = function() {			
			$(this).click(function(e){
				if(e.ctrlKey){
					if($(this).hasClass('clicked')){
						$(this).removeClass('clicked');
					}else{
						$(this).addClass('clicked');
					}
				}else{
					$(xgrid).find('.tr').removeClass('clicked');
					$(this).addClass('clicked');
				}				
			});
			return this;			
		};
		$(this).find('.tr').clickFlag();
	//<CLICKED
	//TAB>
		//tabs visibility
		$.fn.tab = function() {			
			$(this).click(function(){
				$(this).toggleClass('active');
				//this tab rel attr is the class name of content container to toggle
				$(xgrid).find('.'+ $(this).attr('rel')).toggle('fast');
			});
			return this;			
		};
		$(this).find('.xgrid_tab').tab();
	//<TAB
	//COMBO>
		//manage combo type filters (update JSON on change)
		//s0=pool combo, s1=selection combo.
		$.fn.filterComboMulti = function() {
			var comboMulti=this;
			s0=$(this).find('select:first');
			s1=$(this).find('select:last');
			//update JSON on selection change
			function comboUpdateJSON(s1){
				aliasName=getAlias(s1);
				//hide if selection is empty
				if($(s1).find('option').length>0){
					$(s1).parent().show('normal');
				}else{
					$(s1).parent().hide('normal');
				}
				//get existing sort for this column
				if(!xgridJSON[aliasName]) xgridJSON[aliasName]={};
				xgridJSON[aliasName].combo=[];
				$(s1).find('option').each(
					function(i) {
						xgridJSON[aliasName].combo[i]=$(this).val();
					}
				);
				//update json
				saveJSON();
			}
			//get first select
			function getS0(o){
			}
			//hide selection on load (empty is default)
			$(comboMulti).find('td:last').hide();
			
			//add on combo dblclick
			$(s0).dblclick(function(){
				s0=$(this).parents('.xgrid_filter_combo_multi:first').find('select:first');
				s1=$(this).parents('.xgrid_filter_combo_multi:first').find('select:last');
				$(s0).find('option:selected').remove().appendTo(s1);	
				comboUpdateJSON(s1);	
			});
			//add on button click
			addButton=$(comboMulti).find('.xgrid_combo_add:first');
			$(addButton).click(function(){
				s0=$(this).parents('.xgrid_filter_combo_multi:first').find('select:first');
				s1=$(this).parents('.xgrid_filter_combo_multi:first').find('select:last');
				$(s0).find('option:selected').remove().appendTo(s1);	
				comboUpdateJSON(s1);			
			});
			//add all on button click
			addAllButton=$(comboMulti).find('.xgrid_combo_add_all:first');
			$(addAllButton).click(function(){
				s0=$(this).parents('.xgrid_filter_combo_multi:first').find('select:first');
				s1=$(this).parents('.xgrid_filter_combo_multi:first').find('select:last');
				$(s0).find('option').remove().appendTo(s1);	
				comboUpdateJSON(s1);			
			});
			//remove on combo dblclick			
			$(s1).dblclick(function(){
				s0=$(this).parents('.xgrid_filter_combo_multi:first').find('select:first');
				s1=$(this).parents('.xgrid_filter_combo_multi:first').find('select:last');
				$(s1).find('option:selected').remove().appendTo(s0);
				comboUpdateJSON(s1);
			});
			//remove on button click
			removeButton=$(comboMulti).find('.xgrid_combo_rem:first');
			$(removeButton).click(function(){
				s0=$(this).parents('.xgrid_filter_combo_multi:first').find('select:first');
				s1=$(this).parents('.xgrid_filter_combo_multi:first').find('select:last');
				$(s1).find('option:selected').remove().appendTo(s0);
				comboUpdateJSON(s1);				
			});
			//remove all on button click
			removeAllButton=$(comboMulti).find('.xgrid_combo_rem_all:first');
			$(removeAllButton).click(function(){
				s0=$(this).parents('.xgrid_filter_combo_multi:first').find('select:first');
				s1=$(this).parents('.xgrid_filter_combo_multi:first').find('select:last');
				$(s1).find('option').remove().appendTo(s0);
				comboUpdateJSON(s1);				
			});
			return this;	
					
		};
		
		$.fn.filterCombo = function() {
			s0=$(this).find('select:first');
			if($(s0).find('option').length>0){
				$(s0).parent().show('normal');
			}else{
				$(s0).parent().hide('normal');
			}
			$(s0).change(function(){
				aliasName=getAlias(this);
				if(!xgridJSON[aliasName]) xgridJSON[aliasName]={};
				
				//remove combo filter
				xgridJSON[aliasName].combo=[];
				v=$(this).find('option:selected').val();
				//add a single selection combo filter if not empty (=unfiltered)
				if(v!=''){					
					xgridJSON[aliasName].combo[0]=v;
				}else{
					delete xgridJSON[aliasName].combo;
				}
				//update json
				saveJSON();
			});
			return this;	
					
		};
		
		function filterOffCombo(filterOffButton){ //hide filter, remove json
			//remove filter_input
			//$(xgridFilterOff).prevAll().remove();
			displayApply($(filterOffButton));
			//add filter_on button
			//$(xgridFilterOff).before(html['xgrid_filter_on']).prev().click(function(){filterOn(this);});
			//remove json filter
			
			aliasName=getAlias($(filterOffButton));
			delete xgridJSON[aliasName].combo;
			saveJSON();
			//remove filter_off button
			$(filterOffButton).parent().remove();
		}
		$(this).find('.xgrid_filter_combo').filterCombo();
		$(this).find('.xgrid_filter_combo_multi').filterComboMulti();
		$(this).find('.xgrid_filter_off_combo').each(function(i){  	
			$(this).prepend($(html['xgrid_filter_off']).css('cursor','hand').click(function(){filterOffCombo(this);}));			
		});
	//<COMBO
	
	
	//SORT>
		//manage sorting (update JSON on click then post)
		//sort functions
		$.fn.sortToogle = function() { //allow sorting
			$(this).click(function(){
			
				xgridSortAsc=$(this).find(".xgrid_sort_asc")[0];
				xgridSortDesc=$(this).find(".xgrid_sort_desc")[0];
			
				initAlias(this);
				
				//get existing sort for this column
				if(!xgridJSON[aliasName]) xgridJSON[aliasName]={};
				
				firstSort=$(this).attr('alt');
				if(!xgridJSON[aliasName].sort) xgridJSON[aliasName].sort=firstSort;
				optionSort= xgridJSON[aliasName].sort;
			
				//remove all current sorting
				for (var i in xgridJSON) if(xgridJSON[i].sort) delete xgridJSON[i].sort;
				
				//toogle sort state
				if(optionSort==-1){ // if first click or in DESC state, switch to ASC state
					xgridJSON[aliasName].sort=1;
				}else{  // if ASC state, switch to DESC state
					xgridJSON[aliasName].sort=-1;
				}
				
				//update json
				saveJSON();
				
				/* hide all sort icons*/
				$(xgrid).find('.xgrid_sort_asc').hide();
				$(xgrid).find('.xgrid_sort_desc').hide();
				
				/* show only active sort icons */
				$(xgrid).find('.xgrid_sort').each(
					function(i) {
						iName=$(this).attr('name');
						if(xgridJSON[iName]){
							if(xgridJSON[iName].sort){
								if(xgridJSON[iName].sort==1){
									$(this).find(".xgrid_sort_asc").show();
								}else{
									$(this).find(".xgrid_sort_desc").show();
								}
							}
						}
					}
				);
				
				//$(xgridTbody).remove();
				postJSON();	
			
			});
			return this;			
		};
		//sort behavior
		$(this).find('.xgrid_sort').sortToogle();
	//<SORT
	

	//FULLTEXT>
		//manage plain text type filters (update JSON)
		//fulltext filter functions				
		function filterOn(xgridFilterOn, defaultValue, applyAndPost){ //show filter input, init json to empty
			buildFilterFunctions();
			$(xgrid).stopTime();
			//tr=$(xgridFilterOn).parents('tr:first').parent();
			if(!defaultValue) defaultValue='';
			//displayApply($(xgridFilterOn));
		
			//add filter_input then focus
		  	$(xgridFilterOn).before(html['xgrid_filter_input']).prev()
		  		.filterInput(applyAndPost)
		  		.val(defaultValue).focus();
		  	//add filter_off button
			//$(xgridFilterOn).before(html['xgrid_filter_off']).prev().click(function(){filterOff(this);});
			//add empty json filter		
			aliasName=$(xgridFilterOn).parent().attr('rel');
			initAliasJSON();
			xgridJSON[aliasName].filter=defaultValue;
			saveJSON();
			//remove fitler_on button
			$(xgridFilterOn).remove();
		}
		//fulltext filter former state depending on json existing data
		$(this).find('.xgrid_filter_fulltext').each(function(i){
			aliasName=$(this).attr('rel');
			$(this).prepend(html['xgrid_filter_on']);
			/*
			if(xgridJSON[aliasName]==undefined || xgridJSON[aliasName].filter==undefined){
				$(this).append(html['xgrid_filter_on']);	
			}else{
				$(this).append(html['xgrid_filter_on']);			
				$(html['xgrid_filter_input']).val(xgridJSON[aliasName].filter).appendTo($(this));
				$(this).append(html['xgrid_filter_off']);
			}
			*/
		});
		
		$(this).find('.xgrid_filter_off_fulltext').each(function(i){
			//$(this).prepend(html['xgrid_filter_off']).click(function(){filterOff(this);}).css('cursor','pointer');
			$(this).prepend(
				$(html['xgrid_filter_off'])
				.css('cursor','hand')
				.click(function(){filterOff(this);})
				.rightClick(function(xgridFilterOff){filterOff(xgridFilterOff, true);})
			);
 		});
		//fulltext filter behaviors
		$(this).find('.xgrid_filter_fulltext').each(function(i){  	
			//aliasName=$(this).attr('rel');

			$(this).click(function(e){
				switch($(e.target).attr('class'))
				{
				case 'xgrid_filter_on':
				  //alert(0);
				  filterOn($(e.target));
				  $(this).find('input').focus();
				break;    
				default:
				  //alert(3);
				}
			});
			
	
			
			$(this).find('.xgrid_filter_on').click(function(){
					//not working IE					
				}).rightClick(function(xgridFilterOff){filterOn(xgridFilterOff, '', true);}); //not working IE
			
			//$(this).find('.xgrid_filter_off').click(function(){filterOff(this);});
			//$(this).find('.xgrid_filter_input').filterInput();
		});

		
		if(xgridJSON._autocomplete){
			$(this).find('.xgrid_filter_fulltext').each(function(i){  	
				aliasName=$(this).attr('rel');
				if(xgridJSON._lastinput==aliasName){
				$(this).find('.xgrid_filter_on').each(function(){filterOn(this, xgridJSON._lastinput_value, xgridJSON._autocomplete);});				
				//$(this).find('.xgrid_filter_on').val();
				}				
			});
		}
	//<FULLTEXT
	
	/*
	//NUM>
		//manage num type filters (update JSON)
		//fulltext filter functions				
		function filterNumOn(button, defaultValue, applyAndPost){ //show filter input, init json to empty
			//add filter_input then focus
			html='<SELECT><OPTION VALUE="like">~</OPTION><OPTION VALUE="gt">></OPTION>'
				+'<OPTION VALUE="lt"><</OPTION><OPTION VALUE="egal">=</OPTION><OPTION VALUE="negal">!=</OPTION></SELECT>';
			html+=html['xgrid_filter_input'];
		  	$(button).before(html);
		  	//.before().prev()		  		.filterNumInput().focus());

			//add empty json filter		
			aliasName=$(button).parent().attr('rel');
			initAliasJSON();
			//remove fitler_on button
			$(button).remove();
		}
		$.fn.filterNumInput = function() { //update json
			$(this).click(function(){$(this).select();});
			$(this).bind("keyup", function(){
				$(xgrid).stopTime();
				//update json filter
				aliasName=$(this).parents('.xgrid_alias:first').attr('rel') ;
				if($(this).attr('rel')){
					//multiple filter on same alias, stored as object
					filterIndex=$(this).attr('rel');
					//xgridJSON[aliasName].filter[$(this).attr('rel')]=$(this).val();
					xgridJSON[aliasName]['filter'][filterIndex]=$(this).val();
				}else{
					//single filter for this alias, stored as string
					xgridJSON[aliasName].filter=$(this).val();
				}
				saveJSON();
			});
			
			//TODO bind multiple event
			$(this).bind("blur", function(){
				aliasName=$(this).parents('.xgrid_alias:first').attr('rel') ;
				if($(this).attr('rel')){
					filterIndex=$(this).attr('rel');
					xgridJSON[aliasName]['filter'][filterIndex]=$(this).val();
				}else{
					xgridJSON[aliasName].filter=$(this).val();
				}
				saveJSON();
			});
			return this;			
		};
		function filterNumOff(button){ //hide filter, remove json
			aliasName=getAlias($(button));
			delete xgridJSON[aliasName].filter;
			saveJSON();
			//remove filter_off button
			$(button).parent().remove();
		}
		//fulltext filter former state depending on json existing data
		$(this).find('.xgrid_filter_num').each(function(i){
			aliasName=$(this).attr('rel');
			$(this).prepend(html['xgrid_filter_on']);
		});
		$(this).find('.xgrid_filter_off_num').each(function(i){
			$(this).prepend(
				$(html['xgrid_filter_off'])
				.css('cursor','hand')
				.click(function(){filterNumOff(this);})				
			);
 		});
		//fulltext filter behaviors
		$(this).find('.xgrid_filter_num').each(function(i){  	
			//aliasName=$(this).attr('rel');
			$(this).find('.xgrid_filter_on').click(function(){filterNumOn(this);});
		});
	//<NUM
	*/
	
	
	
	
	//DATE>
		//manage date type filters (update JSON)
		//date filter function, note that fulltext filterInput function is also used by date filter to update json
		$.fn.filterDateOn = function() { //show date filter, init json to empty
			$(this).click(function(){
				initAlias(this);
				initAliasJSON();
				//update json filter
				xgridJSON[aliasName].filter={};
				saveJSON();
				
				fillDateFilter(alias);
				displayApply(this);
				$(this).remove();
				
			});
			return this;			
		};	
		$.fn.filterDateSelect = function() { //allow filter input to work with calendar, update json
			$(this).click(function(){
				initAlias(this);
				//date input must be directly left to date selection button 
				input=$(this).prev();
				//on the fly input id for calendar call
				$(input).attr('id', aliasName+'__'+$(input).attr('rel'));
				//show calendar
				ds_sh(input.attr('id'));
			});
			return this;			
		};
		$.fn.filterDateOff = function() { //hide date filter, remove json
			$(this).click(function(){
				displayApply($(this));
				//initAlias(this);
				//initAliasJSON();
				aliasName=getAlias($(this));
				//remove json filter
				delete xgridJSON[aliasName].filter;
				saveJSON();
				$(this).parent().remove();
			});
			return this;			
		};		

							
		function fillDateFilter(o){ //init date filter inputs
			buildFilterFunctions();
			initAlias(o);		
			//$(o).children().remove();
			if(xgridJSON[aliasName]==undefined || xgridJSON[aliasName].filter==undefined){
				$(html['xgrid_filter_on']).filterDateOn().appendTo($(o));	
			}else{
				if(xgridJSON[aliasName]['filter']['from'])	v=xgridJSON[aliasName]['filter']['from']; else v='';
				$(o).append('<span>du</span>');
				$(html['xgrid_filter_date_input']).attr('rel','from').val(v).filterInput()
					.attr('id','jqCal__from_'+aliasName).addClass('jqCal').appendTo($(o));
		  		//$(html['xgrid_filter_date_select']).attr('rel','from').filterDateSelect().appendTo($(o));
		  		$(o).append('<br/><span>au</span>');
		  		if(xgridJSON[aliasName]['filter']['to'])	v=xgridJSON[aliasName]['filter']['to']; else v='';
		  		$(html['xgrid_filter_date_input']).attr('rel','to').val(v).attr('id','jqCal__to_'+aliasName).addClass('jqCal').filterInput().appendTo($(o));
		  		//$(html['xgrid_filter_date_select']).attr('rel','to').filterDateSelect().appendTo($(o));
		  		
		  		$(o).find('.jqCal').jqCalendar();
		  		
		  		//$(o).append('<br/>');
				//$(html['xgrid_filter_off']).filterDateOff().appendTo($(o));
				
			}
		}	
		//date filter former state depending on json existing data
		$(this).find('.xgrid_filter_date').each(function(i){
			//fillDateFilter(this);
			$(html['xgrid_filter_on']).filterDateOn().appendTo($(this));
		});
		$(this).find('.xgrid_filter_off_date').each(function(i){
			//$(this).prepend(html['xgrid_filter_off']).filterDateOff().css('cursor','pointer');
			//$(this).prepend($(html['xgrid_filter_off']).css('cursor','hand').click(function(){filterDateOff(this);}));	
			$(this).prepend($(html['xgrid_filter_off']).css('cursor','hand').filterDateOff());	
		});
		
	//<DATE
	//BOOL>
		//manage bool type filters (update JSON then post)
		//bool filter former state depending on json existing data
		/*
		$(this).find('.xgrid_filter_bool').each(function(i){
			aliasName=$(this).attr('rel');
			if(xgridJSON[aliasName]==undefined || xgridJSON[aliasName].filter==undefined){
				$(html['xgrid_filter_yes']).hide().appendTo($(this));
				$(html['xgrid_filter_choose']).appendTo($(this));
				$(html['xgrid_filter_no']).hide().appendTo($(this));
			}else{
				if(xgridJSON[aliasName].filter==1){
					$(html['xgrid_filter_yes']).appendTo($(this));
					$(html['xgrid_filter_choose']).hide().appendTo($(this));
					$(html['xgrid_filter_no']).hide().appendTo($(this));
				}else{
					$(html['xgrid_filter_yes']).hide().appendTo($(this));
					$(html['xgrid_filter_choose']).hide().appendTo($(this));
					$(html['xgrid_filter_no']).appendTo($(this));
				}
			}
		});
		
		
		$.fn.filterRadio = function() { //hide date filter, remove json
			$(this).click(function(){
				aliasName=getAlias($(this));				
				if($(this).hasClass('cbox2')){
					$(this).addClass('cbox0').removeClass('cbox2');
					initAliasJSON();
					xgridJSON[aliasName].filter=0;
				}else if($(this).hasClass('cbox0')){
					$(this).addClass('cbox1').removeClass('cbox0');
					xgridJSON[aliasName].filter=1;
				}else if($(this).hasClass('cbox1')){
					$(this).addClass('cbox2').removeClass('cbox1');
					delete xgridJSON[aliasName].filter;
				}
				saveJSON();
			});
			return this;			
		};		
		
		*/
		
		$(this).find('.xgrid_filter_bool_radio').each(function(i){
			$(this).append($(html['xgrid_filter_on']).click(function(){filterBoolBuild(this);}).css('cursor','pointer'));
				//$(html['xgrid_filter_radio']).addClass('cbox2').filterRadio().appendTo($(this));	
				
				
				//$(radio_yes+html['radio_no']+html['radio_all']).appendTo($(this));				
				//$(html['xgrid_filter_radio_no']).appendTo($(this));
				//$(html['xgrid_filter_radio_off']).appendTo($(this));

			
		});
		$(this).find('.xgrid_filter_off_bool').each(function(i){  	
			$(this).prepend(html['xgrid_filter_off']).click(function(){filterBoolOff(this);}).css('cursor','pointer');
			
		});
		/*
		$(this).find('.xgrid_filter_bool').each(function(i){
			
				$(html['xgrid_filter_yes']).appendTo($(this));		
				$(html['xgrid_filter_no']).appendTo($(this));

			
		});
		
		
		
		//bool filter behavior
		$(this).find('.xgrid_filter_bool').each(function(i){  	
			//$(this).find('.xgrid_filter_choose').click(function(){filterBoolChoose(this);});
			$(this).find('.xgrid_filter_yes').click(function(){filterBoolYes(this);});
			$(this).find('.xgrid_filter_no').click(function(){filterBoolNo(this);});
			
		});
		
		
		*/
		/*
		//bool filter functions
		function filterBoolChoose(xgridFilterChoose){
			aliasName=$(xgridFilterChoose).parent().attr('rel');		
			initAliasJSON();
			//remove json filter
			delete xgridJSON[aliasName].filter;
			//toogle hide button
			$(xgridFilterChoose).next().toggle();
			//toogle show button
			$(xgridFilterChoose).prev().toggle();
			saveJSON();
			
		}
		*/
		
		/*
		function filterBoolOn(button){
			aliasName=getAlias(button);		
			//remove json filter
			delete xgridJSON[aliasName].filter;		
			saveJSON();
			// hide filter off button
			$(button).remove();
		}
		*/
		function filterBoolBuild(button){
			aliasName=getAlias(button);
			container=$(button).parent();		
			$(button).hide();
			$(container).append(
				$(html['radio_yes']).click(function(){filterBoolYes(this);})
			);
			$(container).append(
				$(html['radio_no']).click(function(){filterBoolNo(this);})
			);
			$(container).append(
				$(html['radio_all']).click(function(){filterBoolAll(this);})
			);
			//gather radio as a single group
			$(container).find('input').attr('name','radio__'+aliasName);			
		}
		
		function filterBoolOff(button){
			aliasName=getAlias(button);		
			//remove json filter
			delete xgridJSON[aliasName].filter;		
			saveJSON();
			// hide filter off button
			$(button).remove();
		}
		
		function filterBoolYes(button){
			aliasName=getAlias(button);	
			initAliasJSON();
			//update json filter
			xgridJSON[aliasName].filter=1;
			saveJSON();
		}
		function filterBoolNo(button){
			aliasName=getAlias(button);			
			initAliasJSON();
			//update json filter
			xgridJSON[aliasName].filter=0;
			saveJSON();
		}
		function filterBoolAll(button){
			aliasName=getAlias(button);		
			//remove json filter
			delete xgridJSON[aliasName].filter;
			saveJSON();
		}
	//<BOOL
	
	
	
	
	
	
	//EDITABLE>
		$.fn.xGridIsEditableCombo = function() {
			$(this).click(
				function(){
				$(this).hide();
				combo=xgridROJSON.editableCombo;
				
				$(combo).each(function(i){
					alert(i);	
				});
				
				}
			);
			return this;			
		};
		$(this).find('.isEditableCombo').xGridIsEditableCombo();	
		
		
		$.fn.xGridIsEditableNew = function() {
			$(this).click(
				function(){
				$(this).hide();
				tr=$(this).parents("tr:first").prev();
				
				alert( $(tr).html());
				$(tr).find(".xGridAlias").each(function(i){
					alert(i);
					alert($(this).find('input').val());	
				});
				
				}
			);
			return this;			
		};
		$(this).find('.xgrid_editable_new').xGridIsEditableNew();
		
	//<EDITABLE
	


	//PAGE_NUMBERING>
		//manage page numbering (update JSON then post)
		//page numbering functions
		$.fn.pageNum = function() {
			$(this).click(function(){$(this).select();});
			$(this).bind("keyup", function(){
				//update json filter
				xgridJSON._page=$(this).val();
				saveJSON();
			});
			return this;			
		};
		$.fn.pageRows = function() {
			$(this).click(function(){$(this).select();});
			$(this).bind("keyup", function(){
				//update json filter
				xgridJSON._page_rows=$(this).val();
				saveJSON();
			});
			return this;			
		};
		function pageGoFirst(){
			/* update xgrid JSON */
			xgridJSON._page=1;
			saveJSON();
			postJSON();
		}
		function pageGoPrev(){
			/* update xgrid JSON */
			//if(!xgridJSON._page) xgridJSON._page=1;
			xgridJSON._page=xgridJSON._page-1;
			if(xgridJSON._page<1) xgridJSON._page=1;
			saveJSON();
			postJSON();
		}
		function pageGoNext(){
			/* update xgrid JSON */
			xgridJSON._page=parseInt(xgridJSON._page)+1;
			saveJSON();
			postJSON();
		}
		function pageGoLast(){
			/* update xgrid JSON */
			xgridJSON._page=xgridJSON._page_count;
			saveJSON();
			postJSON();
		}			
		
		//page numbering initial state is done by xgrid php class
		//page numbering behaviors
		$(this).find('.xgrid_page_go_first').click(function(){pageGoFirst(this);});
		$(this).find('.xgrid_page_go_prev').click(function(){pageGoPrev(this);});
		$(this).find('.xgrid_page_num').pageNum();
		$(this).find('.xgrid_page_go_next').click(function(){pageGoNext(this);});
		$(this).find('.xgrid_page_go_last').click(function(){pageGoLast(this);});
		$(this).find('.xgrid_page_rows').pageRows();
		
	//<PAGE_NUMBERING
	 
	 
	//DYNAMIC_CSS>
		//kill existing div (unique div for multiple xgrid)
		$('#xgridOverflowDiv').remove();
		var $overflow = $(document.createElement("div"));
		$overflow.attr('id','xgridOverflowDiv');		
		$overflow.css({
			position: 'absolute',
			background: 'white',
			border: '1px solid black',
			padding: '2px',
			'-moz-border-radius' : '4px'
		});
		$overflow.hide().appendTo('body');
		function overflowResetPosition(o) {
			// requires jquery.dimension plugin
			var offset = $(o).offset();
			$('#xgridOverflowDiv').css({
				top: (offset.top + o.offsetHeight) + 'px',
				left: offset.left + 20 + 'px'
			});
			$('#xgridOverflowDiv').html($(o).html());			
		}
		$.fn.overflowToggle = function() {
			$(this).mouseover(function(){
				if( $(this).html() ){				
					overflowResetPosition(this);
					$('#xgridOverflowDiv').show();
					$(this).css('cursor','help');
				}
			});
			$(this).mouseout(function(){	
				if( $(this).html() ){	
					$('#xgridOverflowDiv').hide();	
					$(this).css('cursor','auto');	
				}	
			});
			return this;			
		};
		$(this).find('.width').overflowToggle();
	//<DYNAMIC_CSS 
	 
	//load json from embeded hidden textarea 
	function loadJSON(){
		if($(xgridHidden).val()=='') $(xgridHidden).val('{}');
		xgridJSON=eval('(' + $(xgridHidden).val() + ')');
		if($(xgridROHidden).val()=='') $(xgridROHidden).val('{}');
		xgridROJSON=eval('(' + $(xgridROHidden).val() + ')');
	}
	//save json to embeded hidden textarea
	function saveJSON(){
		$(xgridHidden).val(toJsonString(xgridJSON));
	}
	//show apply button, usually after some filter change to be posted
	function displayApply(o){
		if(!$(o).nextAll().hasClass('xgrid_submit') && 1==0){
			applyTr='<span class="xgrid_submit"><br/>'+html['xgrid_apply']+'</span>';	
			$(o).parents('td:first').append(applyTr);		
		}
		//if(!$(o).nextAll().hasClass('xgrid_submit')){
			//applyTr='<tr class="xgrid_submit trH0" style="height:20px;"><td colspan="'+$(xgridTbody).find('tr:first>td').length+'"><div style="position:absolute;left:100px;background:lightblue;">'+html['xgrid_apply']+'</div></td></tr>';
			//applyTr='<span class="xgrid_submit">'+html['xgrid_apply']+'</span>';
			//$(o).after(applyTr);
		//}
	}
	
	function xGridAjaxReload(responseText, statusText)  { 	
		o=$(xgrid).parents('form:first');
		$(o).html(responseText);
		$(o).find('.xgrid').xGrid();	
   }
	
	//refresh result list, thus calling again xgrid php class.
	function postJSON(){
		//post mode, whole page reload
		$(xgrid).find('.tr').fadeTo('normal',0.2);
		//avoid multiple click
		$(xgrid).find('img:visible , input:visible').unbind().fadeTo('fast',0.5);
		$(xgrid).parents('form:first').submit();
	}
	function submitJSON(){
		//submit without effect
		$(xgrid).parents('form:first').submit();
	}

	// one shoot dialog (will be removed php side)
	function toPhp(xgridJSON, o, v){
		xgridJSON._temp[o]=v;
	}
	
	//alias name is stored in rel attribute of the first parent container having xgrid_alias class
	function getAlias(o){
		if( $(o).hasClass("xgrid_alias") ){
			alias=o;
		}else{
			alias=$(o).parents('.xgrid_alias:first');
		}
		return $(alias).attr('rel');
	}
	//must be defined , presuming its empty by default
	function initAliasJSON(){
		if(!xgridJSON[aliasName]) xgridJSON[aliasName]={};
	}
	  
	//alias name is stored in rel attribute of the first parent container having xgrid_alias class
	function initAlias(o){
		if( $(o).hasClass("xgrid_alias") ){
			alias=o;
		}else{
			alias=$(o).parents('.xgrid_alias:first');
		}
		aliasName=$(alias).attr('rel');
	}
	//must be defined , presuming its empty by default
	function initAliasJSON(){
		if(!xgridJSON[aliasName]) xgridJSON[aliasName]={};
	}
	
	//enable ajax post mode if xGrid ajax mode is ON
	function enableAjaxPost(){
		if($(xgrid).attr('rel')=='ajax'){
			$(xgrid).parents('form:first').ajaxForm({success: xGridAjaxReload});
		}
	}
	/*
	$.fn.forcePost = function() {
		$(this).click(function(){
			disableAjaxPost();
			return true;					
		});
	};
	$(xgrid).find('.forcePost').forcePost();
	*/
	function disableAjaxPost(){
		$(xgrid).parents('form:first').unbind();
	}
}




toJsonString = function(arg) {
    return toJsonStringArray(arg).join('');
}

toJsonStringArray = function(arg, out) {
    out = out || new Array();
    var u; // undefined

    switch (typeof arg) {
    case 'object':
        if (arg) {
            if (arg.constructor == Array) {
                out.push('[');
                for (var i = 0; i < arg.length; ++i) {
                    if (i > 0)
                        out.push(',\n');
                    toJsonStringArray(arg[i], out);
                }
                out.push(']');
                return out;
            } else if (typeof arg.toString != 'undefined') {
                out.push('{');
                var first = true;
                for (var i in arg) {
                    var curr = out.length; // Record position to allow undo when arg[i] is undefined.
                    if (!first)
                        out.push(',\n');
                    toJsonStringArray(i, out);
                    out.push(':');                    
                    toJsonStringArray(arg[i], out);
                    if (out[out.length - 1] == u)
                        out.splice(curr, out.length - curr);
                    else
                        first = false;
                }
                out.push('}');
                return out;
            }
            return out;
        }
        out.push('null');
        return out;
    case 'unknown':
    case 'undefined':
    case 'function':
        out.push(u);
        return out;
    case 'string':
        out.push('"')
        out.push(arg.replace(/(["\\])/g, '\\$1').replace(/\r/g, '').replace(/\n/g, '\\n'));
        out.push('"');
        return out;
    default:
        out.push(String(arg));
        return out;
    }
    
    
    
    return this;
}





jQuery.fn.extend({
	everyTime: function(interval, label, fn, times, belay) {
		return this.each(function() {
			jQuery.timer.add(this, interval, label, fn, times, belay);
		});
	},
	oneTime: function(interval, label, fn) {
		return this.each(function() {
			jQuery.timer.add(this, interval, label, fn, 1);
		});
	},
	stopTime: function(label, fn) {
		return this.each(function() {
			jQuery.timer.remove(this, label, fn);
		});
	}
});

jQuery.extend({
	timer: {
		guid: 1,
		global: {},
		regex: /^([0-9]+)\s*(.*s)?$/,
		powers: {
			// Yeah this is major overkill...
			'ms': 1,
			'cs': 10,
			'ds': 100,
			's': 1000,
			'das': 10000,
			'hs': 100000,
			'ks': 1000000
		},
		timeParse: function(value) {
			if (value == undefined || value == null)
				return null;
			var result = this.regex.exec(jQuery.trim(value.toString()));
			if (result[2]) {
				var num = parseInt(result[1], 10);
				var mult = this.powers[result[2]] || 1;
				return num * mult;
			} else {
				return value;
			}
		},
		add: function(element, interval, label, fn, times, belay) {
			var counter = 0;
			
			if (jQuery.isFunction(label)) {
				if (!times) 
					times = fn;
				fn = label;
				label = interval;
			}
			
			interval = jQuery.timer.timeParse(interval);

			if (typeof interval != 'number' || isNaN(interval) || interval <= 0)
				return;

			if (times && times.constructor != Number) {
				belay = !!times;
				times = 0;
			}
			
			times = times || 0;
			belay = belay || false;
			
			if (!element.$timers) 
				element.$timers = {};
			
			if (!element.$timers[label])
				element.$timers[label] = {};
			
			fn.$timerID = fn.$timerID || this.guid++;
			
			var handler = function() {
				if (belay && this.inProgress) 
					return;
				this.inProgress = true;
				if ((++counter > times && times !== 0) || fn.call(element, counter) === false)
					jQuery.timer.remove(element, label, fn);
				this.inProgress = false;
			};
			
			handler.$timerID = fn.$timerID;
			
			if (!element.$timers[label][fn.$timerID]) 
				element.$timers[label][fn.$timerID] = window.setInterval(handler,interval);
			
			if ( !this.global[label] )
				this.global[label] = [];
			this.global[label].push( element );
			
		},
		remove: function(element, label, fn) {
			var timers = element.$timers, ret;
			
			if ( timers ) {
				
				if (!label) {
					for ( label in timers )
						this.remove(element, label, fn);
				} else if ( timers[label] ) {
					if ( fn ) {
						if ( fn.$timerID ) {
							window.clearInterval(timers[label][fn.$timerID]);
							delete timers[label][fn.$timerID];
						}
					} else {
						for ( var fn in timers[label] ) {
							window.clearInterval(timers[label][fn]);
							delete timers[label][fn];
						}
					}
					
					for ( ret in timers[label] ) break;
					if ( !ret ) {
						ret = null;
						delete timers[label];
					}
				}
				
				for ( ret in timers ) break;
				if ( !ret ) 
					element.$timers = null;
			}
		}
	}
});

if (jQuery.browser.msie)
	jQuery(window).one("unload", function() {
		var global = jQuery.timer.global;
		for ( var label in global ) {
			var els = global[label], i = els.length;
			while ( --i )
				jQuery.timer.remove(els[i], label);
		}
	});


if(jQuery) (function(){
	
	$.extend($.fn, {
		
		rightClick: function(handler) {
			$(this).each( function() {
				$(this).mousedown( function(e) {
					var evt = e;
					$(this).mouseup( function() {
						$(this).unbind('mouseup');
						if( evt.button == 2 ) {
							handler( $(this) );
							return false;
						} else {
							return true;
						}
					});
				});
				$(this)[0].oncontextmenu = function() {
					return false;
				}
			});
			return $(this);
		},
		
		rightMouseDown: function(handler) {
			$(this).each( function() {
				$(this).mousedown( function(e) {
					if( e.button == 2 ) {
						handler( $(this) );
						return false;
					} else {
						return true;
					}
				});
				$(this)[0].oncontextmenu = function() {
					return false;
				}
			});
			return $(this);
		},
		
		rightMouseUp: function(handler) {
			$(this).each( function() {
				$(this).mouseup( function(e) {
					if( e.button == 2 ) {
						handler( $(this) );
						return false;
					} else {
						return true;
					}
				});
				$(this)[0].oncontextmenu = function() {
					return false;
				}
			});
			return $(this);
		},
		
		noContext: function() {
			$(this).each( function() {
				$(this)[0].oncontextmenu = function() {
					return false;
				}
			});
			return $(this);
		}
		
	});
	
})(jQuery);	

/*
@desc xGrid edit interface layer
@comment auto detect xGrid env then ajax
*/
function xGridEdit(o, type){
	postParam={};

	td=$(o).parents('td:first');
	tr=$(td).parents('tr:first');
	xGrid=$(o).parents('.xgrid:first');
	xGridAjax=$(xGrid).find('.xgrid_editable_ajax:first').val();
	xGridId=$(xGrid).find('input:first').val();
	xGridRowId=$(tr).attr('xGridEditId');
	alias=$(td).attr('xGridAlias');
	//custom post param
	cellParam=eval('(' + $(td).find('.cellEditParam:first').val() + ')');
	for (var i in cellParam) {
		postParam[i]=cellParam[i];
	}
	
	if(!type) type=$(o).attr('type');
	
	switch(type){					
		case 'checkbox':
			if(o.checked) v=1; else v=0;
		break;	
		case 'button':
			v=1;
		break;	
		default:
			v=$(o).val();
		break;
	}
	
	postParam['_xGridEdit']=1;
	postParam['_xGridId']=xGridId;
	if(xGridRowId) postParam['_rowId']=xGridRowId;
	postParam['_alias']=alias;
	postParam['_value']=v;
	
	$.post(xGridAjax, postParam,
		function(data){
	    	if(data.length>0){
	    		$('body').prepend(data);
	    	}
	  	}
	);
}

function xGridEOF(o){
	html='<textarea onblur="xGridEOB(this);" style="width:100%;height:70px;">'+$(o).val()+'</textarea>';
	$(o).hide().after(html);
	$(o).next().focus();
}
function xGridEOB(o){
	$(o).prev().val($(o).val()).show();
	xGridEdit($(o).prev());
	$(o).remove();
}

function xGridEditBtn(o){
	xGridEdit(o, 'button');
}
function xGridEditDelete(o){
	xGridEdit(o, 'button');
	td=$(o).parents('td:first');
	$(td).parents('tr:first').hide();
}


