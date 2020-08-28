<?php

function build_apply($value="Appliquer",$name='',$class="button"){
	$param=array();
	$param['submit']=TRUE;
	$param['mypic']='OK';
	$param['label']=$value;
	if(!empty($name)) {
		$param['name']=$name;
		$param['id']=$name;
	}
	return ubtn($param);
}

function build_hidden($name,$value="",$id=''){
	if($id=='') {
		return '<input type="hidden" name="'.$name.'" id="'.$name.'" value="'.$value.'" />';
	}else{
		return '<input type="hidden" name="'.$name.'"  id="'.$id.'" value="'.$value.'" />';
	}
}

function build_calendar($name,$value='',$param=''){	
	if(isset($param['defaultNull']) && empty($value)) { 
		$value = '';		
	} elseif(empty($value)) $value = mktime();
	
	if(!empty($value)){
		if(isset($param['showTime']))
			$value = date("d/m/Y H:i",$value);
		else
			$value = date("d/m/Y",$value);
	}
	
	$v_s_input_option = '';
	if(isset($param['class'])) $v_s_input_option.= ' class="'.$param['class'].'" ';	
	if(isset($param['param'])) $v_s_input_option.= $param['param'];
	
	if(isset($param['notNull'])) {
		if(strpos($v_s_input_option,'onchange=') !== false) {	
			$v_s_input_option = str_replace('onchange="','onchange="if(this.value.length == 0) this.value = \''.date("d/m/Y").'\'; ',$v_s_input_option);
		} else {
			$v_s_input_option.= ' onchange="if(this.value.length == 0) this.value = \''.date("d/m/Y").'\'; " ';	
		}
	}
		
	if(isset($param['showTime']))
		$inputCalendar = '<input type="text" name="'.$name.'" id="'.$name.'" value="'.$value.'" size="17" maxlength="16" '.$v_s_input_option.'/>';
	else
		$inputCalendar = '<input type="text" name="'.$name.'" id="'.$name.'" value="'.$value.'" size="10" maxlength="10" '.$v_s_input_option.'/>';
	
		
	if(!isset($param['readOnly'])){

		if (isset ($param['btn_display']))
		  $inputCalendar.= '<img src="'.PICTURE_FOLDER.'/ico_calendar.png" id="f_trigger_c_'.$name.'" align="absmiddle"	style="cursor: pointer; border: 0px solid black;display: '.$param['btn_display'].';" title="Selectionner la date">';
		else
		  $inputCalendar.= '<img src="'.PICTURE_FOLDER.'/ico_calendar.png" id="f_trigger_c_'.$name.'" align="absmiddle"   style="cursor: pointer; border: 0px solid black;" title="Selectionner la date"/>';
		$inputCalendar.= '<script type="text/javascript">';
		$inputCalendar.= '	Calendar.setup({';
		$inputCalendar.= '		inputField 		: "'.$name.'",';
		if(isset($param['showTime'])){
			$inputCalendar.= '		ifFormat 		: "%d/%m/%Y %H:%M",';
			$inputCalendar.= '		showsTime 		: true,';
		}else{
			$inputCalendar.= '		ifFormat 		: "%d/%m/%Y",';
		}
		$inputCalendar.= '		button 			: "f_trigger_c_'.$name.'",';
		$inputCalendar.= '		align 			: "Tl",';
		$inputCalendar.= '		singleClick 	: true';
		$inputCalendar.= '	});';
		$inputCalendar.= '</script>';
		if (isset($param['eraser'])) {	
			$inputCalendar.='<img src="pic/trash.png"  border="0" style="cursor:pointer;" alt="Effacer la date." align="absmiddle" onclick="document.getElementById(\''.$name.'\').value=\'\';">';
		}
	}	
	return $inputCalendar;	
}

function datetime2mktime($date){
	$date = explode(' ',$date);

	if(count($date) != 2)
		return 0;
	
	//---< Gestion des jours >---\\
	$day = explode('/',$date[0]);

	//---< Gestion des heures >---\\
	$hour = explode(":",$date[1]);

	if($day[0]>0 && $day[1]>0 && $day[2]>0 && $hour[0]>=0 && $hour[0]>=0)
		return mktime($hour[0],$hour[1],0,$day[1],$day[0],$day[2]);
	else
		return 0;
}

function date2mktime($date){
	$date=explode('/',$date);
	foreach($date as $key => $val){
		$date[$key]=(int)$val; 
	}
	if(count($date) == 3) {
		if($date[0]>0 && $date[1]>0 && $date[2]>0){	
			return mktime(0,0,0,$date[1],$date[0],$date[2]);
		}
	} 
	return 0;	
}

function mb_ucfirst($p_s_text,$p_s_encoding='UTF-8') {
	$p_s_text = mb_strtolower($p_s_text,$p_s_encoding);
	return mb_strtoupper(substr($p_s_text,0,1),$p_s_encoding).substr($p_s_text,1);
}

function label($value,$class='label',$is_div=FALSE){
	if($is_div){
		$tag='div';
	}
	else{
		$tag='span';
	}
	$class= ' class="'.$class.'"';
	return '<'.$tag.$class.'>'.$value.'</'.$tag.'>';
}

function build_input($name='',$value='',$type=FALSE,$param=FALSE){
	$append='';
	if(is_bool($value)){
		global ${$name};
		$value=${$name};
	}
	
	// this is a way to append some content after <input/>
	global ${$name.'_append'};
		if(isset(${$name.'_append'})) $append=${$name.'_append'};
	
	$secure_value=htmlspecialchars(stripslashes(trim($value)));
	$secure_param=strip_tags(trim($param));
	if(!$type) $type='text';
	if($type=='submit' OR empty($name)){
		if(empty($name)) $name="void";
		$input='<input name="'.$name.'" type="'.$type.'" value="'.$secure_value.'" ';
	}
	else
		$input='<input id="'.$name.'" name="'.$name.'" type="'.$type.'" value="'.$secure_value.'" ';
	if($param) $input.=$secure_param;
	$input.=' />'.$append;
	global ${$name.'_error'};
	if(isset(${$name.'_error'}))	$input='<span class="'.(empty(${$name.'_error'}) ? ${$name.'_error'}:'error').'">'.$input.'</span>';
	return $input;
}

function build_textarea($name,$value='',$param=FALSE){
	$secure_value=stripslashes(trim($value));
	$secure_param=strip_tags(trim($param));

	$input='<textarea id="'.$name.'" name="'.$name.'" ';
	if($param) $input.=$secure_param;
	$input.='>'.trim($secure_value).'</textarea>';
	return $input;
}

function build_textarea_rich($name,$value='',$Width='',$Height='',$Toolbar='Default',$magicQuotes=true) {
	 $File = 'fckeditor.html' ;
	 $Link = PATH_TO_CLASS_FOLDER . 'fckeditor/editor/' . $File . '?InstanceName=' . $name;
     if ($Toolbar != '') {
     $Link .= '&amp;Toolbar=' . $Toolbar;
     } 
     $input = '<input type="hidden" id="' . $name . '" name="' . $name . '" value="' . htmlspecialchars($magicQuotes ? stripslashes($value) : $value) . '" style="display: none;" />'; 
     $input .= "<iframe id=\"{$name}___Frame\" src=\"{$Link}\" width=\"{$Width}\" height=\"{$Height}\" frameborder=\"no\" scrolling=\"no\" style=\"z-index: 0;\"></iframe>"; 
	 return $input;
}

function build_select($list, $name, $default = '', $param = ''){
	if(is_bool($default)){
		global ${$name};
		$default=${$name};
	}	
	if (empty($param)){
		if(!empty($name)){
			$select='<select id="'.$name.'" name="'.$name.'" >';
		}else{
			$select='<select>';
		}
	}else{
		$param=strip_tags(trim($param));
		if(!empty($name)){
			$select='<select id="'.$name.'" name="'.$name.'" '.$param.' >';
		}else{
			$select='<select '.$param.' >';
		}
		
	}
	foreach($list as $key => $value){
		$value=htmlspecialchars(stripslashes(trim($value)));		
		if(empty($value) && $value != 0){
			$value='&nbsp;';
		}				
		if(is_array($default)){
			if(in_array($key, $default)){
				$select.='<option value="'.$key.'" selected="selected">'.$value.'</option>';
			}else{
				$select.='<option value="'.$key.'">'.$value.'</option>';
			}
		}
		else{			
			if(strcmp($key, $default)==0){
				$select.='<option value="'.$key.'" selected="selected">'.$value.'</option>';
			}else{
				$select.='<option value="'.$key.'">'.$value.'</option>';				
			}
		}
	}
	$select.='</select>';
	return $select;
}

function build_multiSelect($list, $name, $defaultList = "", $param = 'size="8"'){	
	if(empty($param)){
		$multiSelect = '<select id="'.$name.'" name="'.$name.'" multiple="multiple">';
	}else{
		$param=strip_tags(trim($param));
		$multiSelect = '<select id="'.$name.'" name="'.$name.'" '.$param.' multiple="multiple">';
	}

	if(is_array($list)){
		foreach($list as $key => $value){
			if(is_array($defaultList)){
				$selected = "";
				foreach($defaultList as $myKey => $default){
					if(strcmp($key, $myKey)==0){
						$selected = 'selected="selected"';
					}
				}
				$multiSelect .= '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
			}else{
				if(strcmp($key, $defaultList) == 0){
					$multiSelect .= '<option value="'.$key.'" selected="selected">'.$value.'</option>';
				}else{
					$multiSelect .= '<option value="'.$key.'">'.$value.'</option>';
				}
			}
		}
	}else{
		$multiSelect .= '<option></option>';
	}
	$multiSelect .= "</select>\n";
	return $multiSelect;
}

function build_radio($list, $name, $default = '',  $param='', $inline=true){
	$radio='';
	if (is_array($list)){	
		foreach($list as $key => $value){
			$radio.='<nobr>';
			if (strcmp($key,$default)){
				$radio.=build_input($name,$key,$type='radio',$param);
			}else{
				$radio.=build_input($name,$key,$type='radio',$param.' checked="checked"');
			}
			$radio.=$value;
			$radio.='</nobr>';
			if (!$inline){
				$radio.='<br/>';
			}				
		}
	}
	return $radio;
}

function build_checkbox($name, $value, $param='', $readonly=false){
	$checkbox = '';
	if ($value==1) {
		$param .= ' checked="checked"';
	}
	if($readonly){
		$checkbox=build_input($name,$value,$type='checkbox',$param.' disabled="disabled"');	
	}else{
		$checkbox=build_input($name,$value,$type='checkbox',$param);
	}
	return $checkbox;
}

function build_checkbox_ajax($p_s_table_name, $p_s_field_name, $p_i_id_value, $p_i_state, $p_s_id_name='id') {
	$v_s_image = '';
	
	$src_image = 'pic/check'.$p_i_state.'_16.png';
	
	$v_s_image .= '<div id="state_ico_'.$p_s_table_name.'_'.$p_s_field_name.'_'.$p_i_id_value.'" >';
	$v_s_image .= '<img width="16" height="16" src="'.$src_image.'" ';
	$v_s_image .= ' onclick="ajax_checkbox(\''.$p_s_table_name.'\',\''.$p_s_field_name.'\',\''.$p_s_id_name.'\',\''.$p_i_id_value.'\',\'state_ico_'.$p_s_table_name.'_'.$p_s_field_name.'_'.$p_i_id_value.'\');" ';		
	$v_s_image .= ' style="cursor: pointer; vertical-align: middle;" />';
	$v_s_image .= '</div>';
	
	return $v_s_image;	
}

?>